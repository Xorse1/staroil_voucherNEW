<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();
define('SECURE_ACCESS', true);

require_once __DIR__ . '/config/config.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['phone_verify'])) {
    header('Location: index');
    exit;
}

if (!isset($_POST['submit'])) {
    header('Location: profile');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$codeInput = trim((string) ($_POST['codeInput'] ?? ''));

if ($codeInput === '') {
    $_SESSION['errorgoogleauth'] = 'Authenticator code is required.';
    header('Location: profile');
    exit;
}

$payload = [
    'user_id' => $userId,
    'codeInput' => $codeInput
];

$curl = curl_init('https://fms.kayxappstaroil.com/APIs/voucher_api/verify_googleauth.php');
if ($curl === false) {
    $_SESSION['errorgoogleauth'] = 'Unable to start Google Authenticator verification.';
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
    $_SESSION['errorgoogleauth'] = 'Google Authenticator verification failed: ' . $curlError;
    header('Location: profile');
    exit;
}

$responseData = json_decode((string) $response, true);

if (isset($responseData['status']) && $responseData['status'] === 'success') {
    $_SESSION['auth_status'] = 1;
    $_SESSION['successgoogleauth'] = $responseData['message'] ?? 'Google Authenticator setup completed.';
} else {
    $_SESSION['errorgoogleauth'] = is_array($responseData) ? ($responseData['message'] ?? 'Google Authenticator verification failed.') : 'Google Authenticator verification failed.';
}

header('Location: profile');
exit;
