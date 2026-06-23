<?php
function checkout_fail($message, $target = 'cart') {
    $_SESSION['checkout_error'] = $message;
    header('Location: ' . $target);
    exit;
}

function checkout_public_url($path) {
    $configured = function_exists('env_value') ? env_value('APP_PUBLIC_URL') : (getenv('APP_PUBLIC_URL') ?: '');
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

function checkout_cart_items_from_request() {
    if (!empty($_SESSION['shopping_cart']) && is_array($_SESSION['shopping_cart'])) {
        return $_SESSION['shopping_cart'];
    }

    $payload = $_POST['cart_payload'] ?? '';
    $decoded = json_decode($payload, true);
    if (!is_array($decoded)) {
        return [];
    }

    return array_map(function ($item) {
        $amount = (float) ($item['amount'] ?? 0);
        return [
            'item_id' => $item['id'] ?? '',
            'item_price' => (string) $amount,
            'item_quantity' => (int) ($item['quantity'] ?? 0),
            'item_image' => $item['image'] ?? ''
        ];
    }, $decoded);
}

function checkout_build_items($cartItems, $discountedTotal) {
    $items = [];

    foreach ($cartItems as $values) {
        $price = (string) ($values['item_price'] ?? 0);
        $amountParts = explode('.', $price);
        $amount = (float) ($amountParts[0] ?? $price);
        $quantity = (int) ($values['item_quantity'] ?? 0);

        if ($amount <= 0 || $quantity <= 0) {
            continue;
        }

        $items[] = [
            'denomination' => $amount,
            'discounted_amount' => (float) $discountedTotal,
            'qty' => $quantity
        ];
    }

    return $items;
}

function checkout_cart_subtotal($cartItems) {
    $total = 0;

    foreach ($cartItems as $values) {
        $total += (float) ($values['item_price'] ?? 0) * (int) ($values['item_quantity'] ?? 0);
    }

    return round($total, 2);
}

function checkout_create_order($beneficiaryId, $items, $gateway, $apiKey) {
    if ($apiKey === '') {
        checkout_fail('Voucher order API key is not configured.');
    }

    $api_url = 'https://fms.kayxappstaroil.com/APIs/voucher_api/add_voucher_order_api.php';
    $data = [
        'beneficiary_id' => $beneficiaryId,
        'items' => json_encode($items),
        'order_date' => gmdate('Y-m-d H:i:s'),
        'check_date' => gmdate('Y-m-d'),
        'payment_gateway' => $gateway
    ];

    $ch = curl_init($api_url);
    if ($ch === false) {
        checkout_fail('Unable to start voucher order request.');
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-KEY: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        checkout_fail('Voucher order request failed: ' . $error);
    }

    curl_close($ch);

    $responseData = json_decode($response, true);
    if ($httpCode !== 200 || !is_array($responseData) || empty($responseData['order_code'])) {
        $message = is_array($responseData) ? ($responseData['error'] ?? $responseData['message'] ?? 'Unable to create voucher order.') : 'Unable to create voucher order.';
        checkout_fail($message);
    }

    return $responseData;
}
