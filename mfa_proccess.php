<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();

foreach ([
    __DIR__ . '/sms_new_arkesel.php',
    __DIR__ . '/includes/helper.php',
    __DIR__ . '/log.php'
] as $optionalFile) {
    if (is_file($optionalFile)) {
        require_once $optionalFile;
    }
}

function mfa_clean($value) {
    if (function_exists('sanitize')) {
        return sanitize($value);
    }

    return trim(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
}

function mfa_fail($type) {
    $_SESSION['mfa_failed'] = time();
    staroil_redirect('mfa?type=' . urlencode($type));
}

function mfa_success_redirect() {
    $next = trim((string) ($_SESSION['post_login_redirect'] ?? ''));
    unset($_SESSION['post_login_redirect']);

    if ($next !== '' && preg_match('/^(cart|store|vouchers|profile|faqs)(\?.*)?$/', $next)) {
        staroil_redirect($next);
    }

    staroil_redirect('store');
}

$user_id = $_SESSION['user_id'] ?? null;

if (!isset($_POST['verify'], $_POST['type'])) {
    staroil_redirect('mfa?type=Mobile-OTP');
}

$type = $_POST['type'] === 'Authenticator' ? 'Authenticator' : 'Mobile-OTP';

try {
    $codeInput = mfa_clean($_POST['codeInput'] ?? '');
} catch (Throwable $exception) {
    error_log('MFA sanitization failed: ' . $exception->getMessage());
    mfa_fail($type);
}

if ($type === 'Authenticator') {
    if (empty($user_id)) {
        mfa_fail($type);
    }

    $apiKey = UPDATE_BENE_PROFILE;
    if ($apiKey === '') {
        error_log('Google Authenticator MFA failed: UPDATE_BENE_PROFILE is not configured.');
        mfa_fail($type);
    }

    $curl = curl_init();
    if ($curl === false) {
        mfa_fail($type);
    }

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://fms.kayxappstaroil.com/APIs/voucher_api/authenticate_google_auth.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            'user_id' => (int) $user_id,
            'codeInput' => $codeInput
        ]),
        CURLOPT_HTTPHEADER => [
            'X-API-KEY: ' . $apiKey,
            'Content-Type: application/json'
        ],
    ]);

    $response = curl_exec($curl);

    if ($response === false) {
        error_log('Google Authenticator MFA cURL error: ' . curl_error($curl));
        curl_close($curl);
        mfa_fail($type);
    }

    curl_close($curl);
    $response_data = json_decode($response, true);

    if (
        isset($response_data['status'], $response_data['message'])
        && $response_data['status'] === 'success'
        && $response_data['message'] === 'Login Success'
    ) {
        session_regenerate_id(true);
        $_SESSION['otp_check'] = time();
        unset($_SESSION['mfa_pending']);
        mfa_success_redirect();
    }

    mfa_fail($type);
}

if ($type === 'Mobile-OTP') {
    $loginOtp = isset($_SESSION['login_otp']) ? (string) $_SESSION['login_otp'] : '';

    if ($loginOtp !== '' && hash_equals($loginOtp, $codeInput)) {
        session_regenerate_id(true);
        $_SESSION['otp_check'] = time();
        unset($_SESSION['login_otp'], $_SESSION['mfa_pending']);
        mfa_success_redirect();
    }

    mfa_fail($type);
}
