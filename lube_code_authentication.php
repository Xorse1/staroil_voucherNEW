<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();
define('SECURE_ACCESS', true);
require_once __DIR__ . '/config/config.php';

if (!isset($_POST['validate'])) {
    header('Location: lube_authenticate');
    exit;
}

$inputCode = trim((string) ($_POST['lube_code'] ?? ''));
$phoneNo = trim((string) ($_POST['customer_phone_no'] ?? ''));

if ($inputCode === '') {
    $_SESSION['false'] = '<strong>Failed!</strong> Validation code is required.';
    header('Location: lube_authenticate');
    exit;
}

$payload = [
    'lube_code' => $inputCode,
    'customer_phone_no' => $phoneNo
];

$curl = curl_init('https://fms.kayxappstaroil.com/APIs/lubricants_api/authenticate_lube.php');
if ($curl === false) {
    $_SESSION['false'] = '<strong>Failed!</strong> Unable to start authentication request.';
    header('Location: lube_authenticate');
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
        'X-API-KEY: ' . LUBE_AUTHENTICATE,
        'Content-Type: application/json'
    ],
]);

$response = curl_exec($curl);
$curlError = curl_error($curl);
curl_close($curl);

if ($curlError) {
    $_SESSION['false'] = '<strong>Failed!</strong> ' . htmlspecialchars($curlError);
    header('Location: lube_authenticate');
    exit;
}

$result = json_decode((string) $response, true);
$status = $result['status'] ?? false;
$message = htmlspecialchars((string) ($result['message'] ?? 'Unable to authenticate lubricant code.'));

if ($status === true || $status === 'true' || $status === 'success') {
    $_SESSION['true'] = '<strong>Success!</strong> ' . $message;
} else {
    $_SESSION['false'] = '<strong>Failed!</strong> ' . $message;
}

header('Location: lube_authenticate');
exit;
