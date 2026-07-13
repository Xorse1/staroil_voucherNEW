<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();

//require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/helper.php';
require_once __DIR__ . '/includes/function_discount_calc.php';
require_once __DIR__ . '/includes/checkout_payload.php';
require_once __DIR__ . '/access_token.php';

if (!isset($_POST['checkout'])) {
    header('Location: cart');
    exit;
}

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$beneficiary_id = sanitize($_POST['beneficiary_id'] ?? '');
$phonewithoutZero = ltrim($phone, '0');

$cartItems = checkout_cart_items_from_request();
if (empty($cartItems)) {
    checkout_fail('Your cart is empty.', 'cart');
}

$overallTotal = checkout_cart_subtotal($cartItems);
$discount = calculateDiscount($overallTotal);
$discountedTotal = (float) $discount['discounted_amount'];
$partnerFeePercent = checkout_partner_fee_percent();
$partnerFeeAmount = checkout_partner_fee_amount($discountedTotal);
$customerPayableTotal = checkout_customer_payable_total($discountedTotal);
$items = checkout_build_items($cartItems, $discountedTotal);

if (empty($items)) {
    checkout_fail('No valid voucher items were found.', 'cart');
}

$order = checkout_create_order($beneficiary_id, $items, 'Tingg', $add_voucher_api_key, checkout_partner_app_code(), $partnerFeePercent, $partnerFeeAmount, $customerPayableTotal);
$generated_order_code = $order['order_code'];
$total_amount = $order['total_amount'] ?? $overallTotal;

if (empty($access_token)) {
    checkout_fail('Tingg access token is not configured.');
}

$hex_value = bin2hex(random_bytes(64));
$data_pay = [
    'customer_first_name' => $name,
    'customer_last_name' => $name,
    'customer_email' => $email,
    'msisdn' => $phonewithoutZero,
    'account_number' => $generated_order_code,
    'request_amount' => $customerPayableTotal,
    'merchant_transaction_id' => $generated_order_code,
    'service_code' => 'STAROILVOUCHERCHECKO',
    'country_code' => 'GHA',
    'currency_code' => 'GHS',
    'raise_invoice' => true,
    'callback_url' => checkout_public_url('webhook?' . $hex_value . '&auth=' . urlencode($generated_order_code)),
    'fail_redirect_url' => checkout_public_url('failed'),
    'success_redirect_url' => checkout_public_url('success?' . $hex_value . '&auth=' . urlencode($generated_order_code) . '&amount=' . urlencode((string) $customerPayableTotal) . '&voucher_amount=' . urlencode((string) $total_amount) . '&partner_fee=' . urlencode((string) $partnerFeeAmount) . '&partner_fee_percent=' . urlencode((string) $partnerFeePercent))
];

$ch = curl_init('https://checkout.tingg.africa/request-service/checkout-request/express-request');
if ($ch === false) {
    checkout_fail('Unable to start Tingg checkout request.');
}

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_pay));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    checkout_fail('Tingg checkout request failed: ' . $error);
}

curl_close($ch);
$decoded = json_decode($response, true);
$long_url = $decoded['results']['long_url'] ?? '';

if ($httpCode !== 200 || $long_url === '') {
    checkout_fail('Tingg did not return a checkout URL.');
}

header('Location: ' . $long_url);
exit;
