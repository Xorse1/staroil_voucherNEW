<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helper.php';
require_once __DIR__ . '/includes/wallet_api_client.php'; 
require_once __DIR__ . '/access_token.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: wallet');
    exit;
}

$amount = (float) sanitize($_POST['amount'] ?? '0');
$gateway = sanitize($_POST['gateway'] ?? 'Hubtel');

if ($amount <= 0) {
    wallet_api_fail('Enter a valid top-up amount.');
}

if (!in_array($gateway, ['Hubtel', 'Tingg'], true)) {
    wallet_api_fail('Select a valid payment gateway.');
}

try {
    $topup = wallet_create_topup((int) $_SESSION['user_id'], $amount, $gateway);
    $topupReference = $topup['data']['topup_reference'] ?? '';

    if ($topupReference === '') {
        wallet_api_fail('Wallet top-up reference was not returned.');
    }

    $hex = bin2hex(random_bytes(32));

    if ($gateway === 'Hubtel') {
        global $hubtelAPIusername, $hubtelAPIpassword;
        if ($hubtelAPIusername === '' || $hubtelAPIpassword === '') {
            wallet_api_fail('Hubtel credentials are not configured.');
        }

        $payload = [
            'totalAmount' => $amount,
            'description' => 'StarOil Voucher Wallet Top-up',
            'callbackUrl' => wallet_public_url('wallet_webhook_hubtel?' . $hex . '&auth=' . urlencode($topupReference)),
            'returnUrl' => wallet_public_url('wallet_success?' . $hex . '&auth=' . urlencode($topupReference) . '&amount=' . urlencode((string) $amount) . '&gateway=Hubtel'),
            'merchantAccountNumber' => '2023580',
            'cancellationUrl' => wallet_public_url('failed'),
            'clientReference' => $topupReference
        ];

        $ch = curl_init('https://payproxyapi.hubtel.com/items/initiate');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($hubtelAPIusername . ':' . $hubtelAPIpassword)
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            wallet_api_fail('Hubtel top-up request failed: ' . $error);
        }

        $decoded = json_decode((string) $response, true);
        $checkoutUrl = $decoded['data']['checkoutUrl'] ?? '';
        if ($checkoutUrl === '') {
            wallet_api_fail('Hubtel did not return a checkout URL.');
        }

        header('Location: ' . $checkoutUrl);
        exit;
    }

    if (empty($access_token)) {
        wallet_api_fail('Tingg access token is not configured.');
    }

    $phone = ltrim((string) ($_SESSION['phone'] ?? ''), '0');
    $payload = [
        'customer_first_name' => $_SESSION['name'] ?? 'Customer',
        'customer_last_name' => $_SESSION['name'] ?? 'Customer',
        'customer_email' => $_SESSION['email'] ?? '',
        'msisdn' => $phone,
        'account_number' => $topupReference,
        'request_amount' => $amount,
        'merchant_transaction_id' => $topupReference,
        'service_code' => 'STAROILVOUCHERCHECKO',
        'country_code' => 'GHA',
        'currency_code' => 'GHS',
        'raise_invoice' => true,
        'callback_url' => wallet_public_url('wallet_webhook_tingg?' . $hex . '&auth=' . urlencode($topupReference)),
        'fail_redirect_url' => wallet_public_url('failed'),
        'success_redirect_url' => wallet_public_url('wallet_success?' . $hex . '&auth=' . urlencode($topupReference) . '&amount=' . urlencode((string) $amount) . '&gateway=Tingg')
    ];

    $ch = curl_init('https://checkout.tingg.africa/request-service/checkout-request/express-request');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        wallet_api_fail('Tingg top-up request failed: ' . $error);
    }

    $decoded = json_decode((string) $response, true);
    $longUrl = $decoded['results']['long_url'] ?? '';
    if ($httpCode !== 200 || $longUrl === '') {
        wallet_api_fail('Tingg did not return a checkout URL.');
    }

    header('Location: ' . $longUrl);
    exit;
} catch (Throwable $exception) {
    wallet_api_fail($exception->getMessage());
}
