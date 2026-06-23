<?php
ob_start();
define('SECURE_ACCESS', true);
require_once __DIR__ . '/includes/session_config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (empty($_SESSION['user_id']) || empty($_SESSION['phone_verify'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required.'
    ]);
    exit;
}

$beneficiary_id = urlencode((string) $_SESSION['user_id']);
$api_url = 'https://fms.kayxappstaroil.com/APIs/voucher_api/fetch_bene_vouchers.php?beneficiary_id=' . $beneficiary_id;

$ch = curl_init();
if ($ch === false) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to start voucher request.'
    ]);
    exit;
}

curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);

if ($response === false) {
    $message = curl_error($ch);
    curl_close($ch);
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to fetch purchased vouchers.',
        'detail' => $message
    ]);
    exit;
}

curl_close($ch);

$response_data = json_decode($response, true);
if (!is_array($response_data)) {
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid voucher response.'
    ]);
    exit;
}

echo json_encode($response_data);
