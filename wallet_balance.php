<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/wallet_api_client.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    echo json_encode(wallet_fetch_balance((int) $_SESSION['user_id']));
} catch (Throwable $exception) {
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => $exception->getMessage()
    ]);
}
