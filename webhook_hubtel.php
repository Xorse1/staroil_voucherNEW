<?php
header('Content-Type: application/json');

$logFile = __DIR__ . '/hubtel_webhook.log';
$rawBody = file_get_contents('php://input');
file_put_contents($logFile, date('Y-m-d H:i:s') . ' - WEBHOOK RECEIVED: ' . $rawBody . PHP_EOL, FILE_APPEND);

$payload = json_decode($rawBody, true);
if (!is_array($payload) || !isset($payload['ResponseCode'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid payload'
    ]);
    exit;
}

$responseCode = (string) $payload['ResponseCode'];
$data = is_array($payload['Data'] ?? null) ? $payload['Data'] : [];
$orderCode = (string) ($data['ClientReference'] ?? $_GET['auth'] ?? '');

file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ORDER CODE: ' . $orderCode . PHP_EOL, FILE_APPEND);

if ($responseCode === '0000' && $orderCode !== '') {
    $apiUrl = 'https://fms.kayxappstaroil.com/APIs/voucher_api/update_voucher_order.php?reference=' . urlencode($orderCode);
    $ch = curl_init($apiUrl);

    if ($ch === false) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ERROR STARTING CURL' . PHP_EOL, FILE_APPEND);
    } else {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $apiResponse = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ERROR UPDATING VOUCHER API: ' . $curlError . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents($logFile, date('Y-m-d H:i:s') . ' - VOUCHER API RESPONSE: ' . $apiResponse . PHP_EOL, FILE_APPEND);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Webhook received and processed'
    ]);
    exit;
}

file_put_contents($logFile, date('Y-m-d H:i:s') . ' - PAYMENT FAILED OR INVALID: ' . $rawBody . PHP_EOL, FILE_APPEND);

http_response_code(400);
echo json_encode([
    'status' => 'error',
    'message' => 'Invalid payment payload'
]);
