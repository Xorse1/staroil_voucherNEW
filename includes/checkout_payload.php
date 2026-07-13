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

function checkout_partner_fee_percent() {
    $percent = (float) ($_SESSION['partner_fee'] ?? 0);
    if (!is_finite($percent) || $percent < 0) {
        return 0.0;
    }

    return round($percent, 4);
}

function checkout_partner_app_code() {
    return trim((string) ($_SESSION['app_code'] ?? ''));
}

function checkout_partner_fee_amount($voucherTotal) {
    return round(((float) $voucherTotal * checkout_partner_fee_percent()) / 100, 2);
}

function checkout_customer_payable_total($voucherTotal) {
    return round((float) $voucherTotal + checkout_partner_fee_amount($voucherTotal), 2);
}

function checkout_create_order($beneficiaryId, $items, $gateway, $apiKey, $appCode, $partnerFeePercent, $partnerFeeAmount, $customerPayableTotal) {
    if ($apiKey === '') {
        checkout_fail('Voucher order API key is not configured.');
    }

    $api_url = 'https://fms.kayxappstaroil.com/APIs/voucher_api/add_voucher_order_api.php';
    $data = [
        'beneficiary_id' => $beneficiaryId,
        'items' => json_encode($items),
        'order_date' => gmdate('Y-m-d H:i:s'),
        'check_date' => gmdate('Y-m-d'),
        'payment_gateway' => $gateway,
        'app_code' => $appCode,
        'partner_fee' => $partnerFeePercent,
        'partner_fee_amount' => $partnerFeeAmount,
        'customer_payable_total' => $customerPayableTotal
    ];

    $discountedAmount = (float) ($items[0]['discounted_amount'] ?? 0);
    $partnerFeePercent = checkout_partner_fee_percent();
    $partnerFeeAmount = checkout_partner_fee_amount($discountedAmount);
    if ($partnerFeePercent > 0) {
        $data['app_code'] = checkout_partner_app_code();
        $data['partner_fee'] = $partnerFeePercent;
        $data['partner_fee_amount'] = $partnerFeeAmount;
        $data['customer_payable_total'] = checkout_customer_payable_total($discountedAmount);
    }

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
