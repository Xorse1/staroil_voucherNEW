<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();
define('SECURE_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helper.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['phone_verify'])) {
    header('Location: logout');
    exit;
}

if (!isset($_POST['change_password'])) {
    header('Location: profile');
    exit;
}

try {
    $currentPassword = sanitize($_POST['current_password'] ?? '');
    $newPassword = sanitize($_POST['new_password'] ?? '');
    $confirmNewPassword = sanitize($_POST['confirm_new_password'] ?? '');
    $userId = sanitize((string) $_SESSION['user_id']);
} catch (Throwable $error) {
    $_SESSION['errorprofilepassword'] = 'Invalid password details submitted.';
    header('Location: profile');
    exit;
}

$payload = [
    'user_id' => (int) $userId,
    'current_password' => $currentPassword,
    'new_password' => $newPassword,
    'confirm_new_password' => $confirmNewPassword
];

$curl = curl_init('https://fms.kayxappstaroil.com/APIs/voucher_api/update_bene_profile_pass.php');
if ($curl === false) {
    $_SESSION['errorprofilepassword'] = 'Unable to start password update request.';
    header('Location: profile');
    exit;
}

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'X-API-KEY: ' . UPDATE_BENE_PROFILE,
        'Content-Type: application/json'
    ],
]);

$response = curl_exec($curl);
$curlError = curl_error($curl);
curl_close($curl);

if ($curlError) {
    $_SESSION['errorprofilepassword'] = 'Password update failed: ' . $curlError;
    header('Location: profile');
    exit;
}

$data = json_decode((string) $response, true);

if (isset($data['status']) && $data['status'] === 'success') {
    $_SESSION['successprofilepassword'] = $data['message'] ?? 'Password updated successfully.';
} else {
    $_SESSION['errorprofilepassword'] = is_array($data) ? ($data['message'] ?? 'Password update failed.') : 'Password update failed.';
}

header('Location: profile');
exit;
