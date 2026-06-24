<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();

foreach ([
    __DIR__ . '/log.php',
    __DIR__ . '/config.php',
    __DIR__ . '/includes/helper.php',
    __DIR__ . '/includes/function_send_sms.php',
    //__DIR__ . '/includes/function_sanitize_pass.php', 
    __DIR__ . '/sms_new_arkesel.php'
] as $optionalFile) {
    if (is_file($optionalFile)) {
        require_once $optionalFile;
    }
}

function clean_input($value) {
    if (function_exists('sanitize')) {
        return sanitize($value);
    }

    return trim(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
}

function password_is_valid($password) {
    if (function_exists('sanitizePassword')) {
        return sanitizePassword($password) === $password;
    }

    return strlen($password) >= 8
        && strlen($password) <= 20
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        && preg_match('/[^A-Za-z0-9]/', $password);
}

function keep_old_registration_values() {
    $_SESSION['old_name'] = clean_input($_POST['name'] ?? '');
    $_SESSION['old_email'] = clean_input($_POST['email'] ?? '');
    $_SESSION['old_phone'] = clean_input($_POST['phone'] ?? '');
    $_SESSION['old_tin'] = clean_input($_POST['tin'] ?? '0');
}

function redirect_to_signup() {
    header('Location: signup');
    exit;
}

function redirect_to_otp() {
    header('Location: auth');
    exit;
}

if (!isset($_POST['register'])) {
    header('Location: signup');
    exit;
}

try {
    $name = clean_input($_POST['name'] ?? '');
    $email = clean_input($_POST['email'] ?? '');
    $phone = clean_input($_POST['phone'] ?? '');
    $tin = clean_input($_POST['tin'] ?? '0');
} catch (Throwable $exception) {
    keep_old_registration_values();
    $_SESSION['failedtoadd'] = true;
    error_log('Registration sanitization failed: ' . $exception->getMessage());
    redirect_to_signup();
}

$pass = trim((string) ($_POST['pass'] ?? ''));
$cpass = trim((string) ($_POST['cpass'] ?? ''));

if ($cpass !== $pass) {
    keep_old_registration_values();
    $_SESSION['passmismarch'] = true;
    redirect_to_signup();
}

if (!password_is_valid($pass)) {
    keep_old_registration_values();
    $_SESSION['passincorrect'] = true;
    redirect_to_signup();
}

$apiUrl = getenv('ADD_BENEFICIARY_API_URL') ?: 'https://fms.kayxappstaroil.com/APIs/voucher_api/add_beneficiary_api.php';
$apiKey = $add_beneficiary_api_key ?? (getenv('ADD_BENEFICIARY_API_KEY') ?: '');

if ($apiKey === '') {
    keep_old_registration_values();
    $_SESSION['failedtoadd'] = true;
    error_log('Registration failed: ADD_BENEFICIARY_API_KEY is not configured.');
    redirect_to_signup();
}

$payload = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'tin_or_ghcard' => $tin,
    'password' => $pass
];

$ch = curl_init();
if ($ch === false) {
    keep_old_registration_values();
    $_SESSION['failedtoadd'] = true;
    error_log('Registration failed: cURL is unavailable.');
    redirect_to_signup();
}

curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: ' . $apiKey
]);

$response = curl_exec($ch);

if ($response === false) {
    keep_old_registration_values();
    $_SESSION['failedtoadd'] = true;
    error_log('Registration cURL error: ' . curl_error($ch));
    curl_close($ch);
    redirect_to_signup();
}

curl_close($ch);

$decodedResponse = json_decode($response, true);
if (!is_array($decodedResponse)) {
    keep_old_registration_values();
    $_SESSION['failedtoadd'] = true;
    error_log('Registration failed: invalid API JSON response.');
    redirect_to_signup();
}
$message = $decodedResponse['message'] ?? 'Failed to add beneficiary. Please try again.';
$_SESSION['reg_error'] = $message;

if ($message === 'Email or phone number already exists.') {
    keep_old_registration_values();
    $_SESSION['emailphonenotexist'] = true;
    redirect_to_signup();
}

if ($message === 'Failed to add beneficiary. Please try again.') {
    keep_old_registration_values();
    $_SESSION['failedtoadd'] = true;
    redirect_to_signup();
}

if ($message === 'Email domain not allowed.') {
    keep_old_registration_values();
    $_SESSION['invalidemaildomain'] = true;
    redirect_to_signup();
}

if ($message === 'Beneficiary successfully added!') {
    $_SESSION['phone'] = $phone;
    $_SESSION['email'] = $email;
    $_SESSION['otp'] = random_int(100000, 999999);
    $_SESSION['authphone'] = $phone;
    $_SESSION['successadded'] = time();

    $messageText = 'Star Oil Voucher Auth-' . $_SESSION['otp'];

    if (function_exists('send_sms_arkesel')) {
            send_sms_arkesel($_SESSION['authphone'], $messageText);
    }

    if (function_exists('send_sms_backup')) {
            send_sms_backup($_SESSION['authphone'], $messageText);
    }

    /*$smsDetailsUrl = getenv('SMS_DETAILS_API_URL') ?: 'https://fms.kayxappstaroil.com/APIs/voucher_api/get_sms_details.php';
    $smsResponse = @file_get_contents($smsDetailsUrl);
    $smsDetails = $smsResponse ? json_decode($smsResponse, true) : null;

    if (($smsDetails['status'] ?? '') === 'success' && !empty($smsDetails['data'][0])) {
        $record = $smsDetails['data'][0];
        $messageText = 'Star Oil Voucher Auth-' . $_SESSION['otp'];

        if (function_exists('mnotify_sms')) {
            mnotify_sms(
                $record['api_key'] ?? '',
                $_SESSION['authphone'],
                $record['sender'] ?? '',
                $messageText,
                '',
                $record['fromm'] ?? '',
                $record['subject'] ?? '',
                $record['endpoint'] ?? '',
                $record['endpoint2'] ?? '',
                1
            );
        }

        $_SESSION['sms_ready'] = true;
    }*/

    redirect_to_otp();
}

keep_old_registration_values();
$_SESSION['failedtoadd'] = true;
redirect_to_signup();
