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

if (($response_data['status'] ?? '') === 'success' && is_array($response_data['data'] ?? null)) {
    $originalCount = count($response_data['data']);
    $seen = [];
    $deduped = [];

    foreach ($response_data['data'] as $voucher) {
        if (!is_array($voucher)) {
            continue;
        }

        $keyParts = [
            $voucher['id'] ?? '',
            $voucher['voucher_code'] ?? '',
            $voucher['voucher_auth'] ?? '',
            $voucher['order_code'] ?? '',
            $voucher['amount'] ?? '',
        ];
        $key = hash('sha256', implode('|', array_map('strval', $keyParts)));

        if (isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $deduped[] = $voucher;
    }

    $response_data['data'] = $deduped;
    $response_data['original_count'] = $originalCount;
    $response_data['deduped_count'] = count($deduped);
    $response_data['duplicate_count'] = $originalCount - count($deduped);
}

echo json_encode($response_data);
