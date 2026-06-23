<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();

foreach ([
    __DIR__ . '/log.php',
    __DIR__ . '/config.php',
    __DIR__ . '/includes/function_send_sms.php'
] as $optionalFile) {
    if (is_file($optionalFile)) {
        require_once $optionalFile;
    }
}

$phone = $_GET['phonesend'] ?? ($_SESSION['authphone'] ?? $_SESSION['phone'] ?? '');
$phone = trim((string) $phone);

if ($phone === '') {
    $_SESSION['otp_error'] = 'Phone number is missing.';
    header('Location: auth');
    exit;
}

$_SESSION['otp'] = random_int(100000, 999999);
$_SESSION['authphone'] = $phone;

$smsDetailsUrl = getenv('SMS_DETAILS_API_URL') ?: 'https://fms.kayxappstaroil.com/APIs/voucher_api/get_sms_details.php';
$smsResponse = @file_get_contents($smsDetailsUrl);
$smsDetails = $smsResponse ? json_decode($smsResponse, true) : null;

if (($smsDetails['status'] ?? '') === 'success' && !empty($smsDetails['data'][0]) && function_exists('mnotify_sms')) {
    $record = $smsDetails['data'][0];
    mnotify_sms(
        $record['api_key'] ?? '',
        $_SESSION['authphone'],
        $record['sender'] ?? '',
        'Star Oil Voucher Auth-' . $_SESSION['otp'],
        '',
        $record['fromm'] ?? '',
        $record['subject'] ?? '',
        $record['endpoint'] ?? '',
        $record['endpoint2'] ?? '',
        1
    );
    $_SESSION['otp_resent'] = time();
}

header('Location: auth');
exit;
