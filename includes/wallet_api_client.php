<?php
require_once __DIR__ . '/../config.php';

function wallet_api_fail($message, $target = 'wallet') {
    $_SESSION['wallet_error'] = $message;
    header('Location: ' . $target);
    exit;
}

function wallet_api_request($endpoint, $method = 'GET', array $payload = [], array $query = []) {
    global $wallet_api_base_url, $wallet_api_bearer_token;

    $baseUrl = rtrim((string) ($wallet_api_base_url ?? ''), '/');
    if ($baseUrl === '' || !filter_var($baseUrl, FILTER_VALIDATE_URL) || parse_url($baseUrl, PHP_URL_HOST) === null) {
        throw new RuntimeException('Wallet API base URL is not configured correctly.');
    }

    if ($wallet_api_bearer_token === '') {
        throw new RuntimeException('Wallet bearer token is not configured.');
    }

    $url = $baseUrl . '/' . ltrim($endpoint, '/');
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('Unable to start wallet API request.');
    }

    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $wallet_api_bearer_token,
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if (strtoupper($method) !== 'GET') {
        $json = json_encode($payload);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen((string) $json);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Wallet API request failed: ' . $error);
    }

    curl_close($ch);
    $decoded = json_decode((string) $response, true);

    if (!is_array($decoded)) {
        throw new RuntimeException('Wallet API returned an invalid response.');
    }

    if ($httpCode < 200 || $httpCode >= 300 || ($decoded['status'] ?? '') !== 'success') {
        throw new RuntimeException($decoded['message'] ?? 'Wallet API request was not successful.');
    }

    return $decoded;
}

function wallet_fetch_balance($beneficiaryId) {
    return wallet_api_request('voucher_fetch_wallet_balance.php', 'GET', [], [
        'beneficiary_id' => $beneficiaryId
    ]);
}

function wallet_create_topup($beneficiaryId, $amount, $gateway) {
    return wallet_api_request('voucher_create_wallet_topup.php', 'POST', [
        'beneficiary_id' => $beneficiaryId,
        'amount' => $amount,
        'gateway' => $gateway
    ]);
}

function wallet_confirm_topup($topupReference, $gatewayReference = '', array $webhookPayload = []) {
    return wallet_api_request('voucher_confirm_wallet_topup.php', 'POST', [
        'topup_reference' => $topupReference,
        'gateway_reference' => $gatewayReference,
        'webhook_payload' => $webhookPayload
    ]);
}

function wallet_pay_order($beneficiaryId, $orderCode, $amount) {
    return wallet_api_request('voucher_pay_order_with_wallet.php', 'POST', [
        'beneficiary_id' => $beneficiaryId,
        'order_code' => $orderCode,
        'amount' => $amount
    ]);
}

function wallet_public_url($path) {
    $configured = getenv('APP_PUBLIC_URL') ?: '';
    if ($configured !== '') {
        return rtrim($configured, '/') . '/' . ltrim($path, '/');
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
    $scheme = $secure ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');

    return $scheme . '://' . $host . ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
}
