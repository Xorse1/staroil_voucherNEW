<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();

foreach ([
    __DIR__ . '/log.php',
    __DIR__ . '/config.php',
    __DIR__ . '/includes/helper.php',
    __DIR__ . '/includes/function_send_sms.php',
    __DIR__ . '/sms_new_arkesel.php'
] as $optionalFile) {
    if (is_file($optionalFile)) {
        require_once $optionalFile;
    }
}

function signin_clean($value) {
    if (function_exists('sanitize')) {
        return sanitize($value);
    }

    return trim(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
}

function signin_fail($message = '') {
    if ($message !== '') {
        $_SESSION['signin_error'] = $message;
    }

    staroil_redirect('signin');
}

function signin_safe_next($value) {
    $next = trim((string) $value);
    if ($next === '') {
        return '';
    }

    if (preg_match('/^(cart|store|vouchers|profile|faqs)(\?.*)?$/', $next)) {
        return $next;
    }

    return '';
}

function signin_success_redirect() {
    $next = signin_safe_next($_SESSION['post_login_redirect'] ?? '');
    unset($_SESSION['post_login_redirect']);

    staroil_redirect($next !== '' ? $next : 'store');
}

$plainToken = $_GET['token'] ?? '';

if (empty($plainToken)) {
    die("Invalid SSO request");
}

$bearerToken = $sso_login_api_bearer_token;

$payload = json_encode([
    'token' => $plainToken
]);

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://fms.kayxappstaroil.com/APIs/voucher_api/integrate_voucher/sso-login.php',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload),
        'Authorization: Bearer ' . $bearerToken
    ],
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($curl);

if (curl_errno($curl)) {
    die('cURL Error: ' . curl_error($curl));
}

$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

curl_close($curl);

$responseData = json_decode($response, true);
if (!is_array($responseData) || !isset($responseData['message'])) {
    error_log('Sign-in failed: invalid API response.');
    signin_fail('Invalid sign-in response. Please try again.');
}


if ($responseData['message'] === 'SSO token verified') {
    $user = $responseData['data'];

    $_SESSION['phone'] = $user['phone'] ?? '';
    $_SESSION['email'] = $user['email'] ?? '';
    $_SESSION['name'] = $user['name'] ?? '';
    $_SESSION['user_id'] = $user['id'] ?? '';
    $_SESSION['phone_verify'] = (int) ($user['phone_verify'] ?? 0);
    $_SESSION['otp_status'] = (int) ($user['otp_status'] ?? 0);
    $_SESSION['auth_status'] = (int) ($user['auth_status'] ?? 0);

    if ((int) $_SESSION['phone_verify'] === 0) {
        $_SESSION['mfa_pending'] = 'phone-verification';
        staroil_redirect('auth?signinphone=' . urlencode($_SESSION['phone']));
    }

    if ((int) $_SESSION['otp_status'] === 1) {
        $verifyLoginOtp = random_int(222, 99999);
        $_SESSION['login_otp'] = $verifyLoginOtp;
        $message = 'Auth Code: ' . $verifyLoginOtp;

        if (function_exists('send_sms_arkesel')) {
            send_sms_arkesel($_SESSION['phone'], $message);
        }

        if (function_exists('send_sms_backup')) {
            send_sms_backup($_SESSION['phone'], $message);
        }

        $_SESSION['mfa_pending'] = 'Mobile-OTP';
        staroil_redirect('mfa?type=Mobile-OTP');
    }

    if ((int) $_SESSION['auth_status'] === 1) {
        $_SESSION['mfa_pending'] = 'Authenticator';
        staroil_redirect('mfa?type=Authenticator');
    }

    unset($_SESSION['mfa_pending']);

    if ((int) $_SESSION['otp_status'] !== 1 && (int) $_SESSION['auth_status'] !== 1) {
        $_SESSION['mfa_setup_reminder'] = time();
    }

    signin_success_redirect();
}

