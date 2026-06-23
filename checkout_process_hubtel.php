<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helper.php';
require_once __DIR__ . '/includes/function_discount_calc.php'; 
require_once __DIR__ . '/includes/checkout_payload.php';

if (!isset($_POST['checkout'])) {
    header('Location: cart');
    exit;
}

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$beneficiary_id = sanitize($_POST['beneficiary_id'] ?? '');

$cartItems = checkout_cart_items_from_request();
if (empty($cartItems)) {
    checkout_fail('Your cart is empty.', 'cart');
}

$overallTotal = checkout_cart_subtotal($cartItems);
$discount = calculateDiscount($overallTotal);
$discountedTotal = (float) $discount['discounted_amount'];
$items = checkout_build_items($cartItems, $discountedTotal);

if (empty($items)) {
    checkout_fail('No valid voucher items were found.', 'cart');
}

$order = checkout_create_order($beneficiary_id, $items, 'Hubtel', $add_voucher_api_key);
$generated_order_code = $order['order_code'];
$total_amount = $order['total_amount'] ?? $overallTotal;

if ($hubtelAPIusername === '' || $hubtelAPIpassword === '') {
    checkout_fail('Hubtel credentials are not configured.');
}

$hex_value = bin2hex(random_bytes(64));
$data = [
    'totalAmount' => $discountedTotal,
    'description' => 'Staroil Voucher Online Checkout',
    'callbackUrl' => checkout_public_url('webhook_hubtel?' . $hex_value . '&auth=' . urlencode($generated_order_code)),
    'returnUrl' => checkout_public_url('success_hubtel?' . $hex_value . '&auth=' . urlencode($generated_order_code) . '&amount=' . urlencode((string) $total_amount)),
    'merchantAccountNumber' => '2023580',
    'cancellationUrl' => checkout_public_url('failed'),
    'clientReference' => $generated_order_code
];

$authString = $hubtelAPIusername . ':' . $hubtelAPIpassword;
$ch = curl_init('https://payproxyapi.hubtel.com/items/initiate');
if ($ch === false) {
    checkout_fail('Unable to start Hubtel checkout request.');
}

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($authString)
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    checkout_fail('Hubtel checkout request failed: ' . $error);
}

curl_close($ch);
$decoded = json_decode($response, true);
$checkoutUrl = $decoded['data']['checkoutUrl'] ?? '';

if ($checkoutUrl === '') {
    checkout_fail('Hubtel did not return a checkout URL.');
}

header('Location: ' . $checkoutUrl);
exit;
