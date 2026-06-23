<?php
require_once __DIR__ . '/includes/wallet_api_client.php';

$rawBody = file_get_contents('php://input') ?: '';
$payload = json_decode($rawBody, true) ?: [];
file_put_contents(__DIR__ . '/wallet_tingg_webhook.log', date('Y-m-d H:i:s') . ' - ' . $rawBody . PHP_EOL, FILE_APPEND);

$statusCode = (string) ($payload['request_status_code'] ?? '');
$topupReference = $payload['merchant_transaction_id'] ?? ($_GET['auth'] ?? '');
$gatewayReference = $payload['checkout_request_id'] ?? '';

if ($statusCode !== '178' || $topupReference === '') {
    http_response_code(400);
    echo json_encode([
        'status_code' => 180,
        'status_description' => 'failed payment',
        'merchant_transaction_id' => $topupReference
    ]);
    exit;
}

try {
    wallet_confirm_topup((string) $topupReference, (string) $gatewayReference, $payload);
    echo json_encode([
        'status_code' => 183,
        'status_description' => 'Successful payment',
        'checkout_request_id' => $gatewayReference,
        'merchant_transaction_id' => $topupReference,
        'receipt_number' => ''
    ]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'status_code' => 180,
        'status_description' => $exception->getMessage(),
        'merchant_transaction_id' => $topupReference
    ]);
}
