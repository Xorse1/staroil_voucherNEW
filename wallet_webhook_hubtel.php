<?php
require_once __DIR__ . '/includes/wallet_api_client.php';

$rawBody = file_get_contents('php://input') ?: '';
$payload = json_decode($rawBody, true) ?: [];
file_put_contents(__DIR__ . '/wallet_hubtel_webhook.log', date('Y-m-d H:i:s') . ' - ' . $rawBody . PHP_EOL, FILE_APPEND);

$responseCode = $payload['ResponseCode'] ?? '';
$topupReference = $payload['Data']['ClientReference'] ?? ($_GET['auth'] ?? '');
$gatewayReference = $payload['Data']['CheckoutId'] ?? ($payload['Data']['SalesInvoiceId'] ?? '');

if ($responseCode !== '0000' || $topupReference === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid Hubtel wallet payload.']);
    exit;
}

try {
    wallet_confirm_topup($topupReference, (string) $gatewayReference, $payload);
    echo json_encode(['status' => 'success', 'message' => 'Wallet top-up confirmed.']);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $exception->getMessage()]);
}
