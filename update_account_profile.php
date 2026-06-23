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

if (!isset($_POST['save'])) {
    header('Location: profile');
    exit;
}

try {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $userId = sanitize((string) $_SESSION['user_id']);
} catch (Throwable $error) {
    $_SESSION['successprofile'] = 'Invalid profile details submitted.';
    header('Location: profile');
    exit;
}

$payload = [
    'user_id' => (int) $userId,
    'name' => $name,
    'email' => $email
];

$curl = curl_init('https://fms.kayxappstaroil.com/APIs/voucher_api/update_bene_profile.php');
if ($curl === false) {
    $_SESSION['successprofile'] = 'Unable to start profile update request.';
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
    $_SESSION['successprofile'] = 'Profile update failed: ' . $curlError;
    header('Location: profile');
    exit;
}

$data = json_decode((string) $response, true);

if (isset($data['status']) && $data['status'] === 'success') {
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['successprofile'] = $data['message'] ?? 'Profile updated successfully.';
} else {
    $_SESSION['successprofile'] = is_array($data) ? ($data['message'] ?? 'Profile update failed.') : 'Profile update failed.';
}

header('Location: profile');
exit;
