<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helper.php';
require_once __DIR__ . '/includes/function_discount_calc.php';
require_once __DIR__ . '/includes/checkout_payload.php';
require_once __DIR__ . '/includes/wallet_api_client.php'; 

if (!isset($_POST['checkout'])) {
    header('Location: cart');
    exit;
}

$beneficiaryId = sanitize($_POST['beneficiary_id'] ?? ($_SESSION['user_id'] ?? ''));
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

try {
    global $add_voucher_api_key;
    $wallet = wallet_fetch_balance((int) $beneficiaryId);
    $walletBalance = (float) ($wallet['data']['balance'] ?? 0);

    if ($walletBalance < $discountedTotal) {
        $shortfall = max(0, $discountedTotal - $walletBalance);
        checkout_fail(
            'Insufficient wallet balance. Wallet balance is GHS ' . number_format($walletBalance, 2) .
            ', voucher total is GHS ' . number_format($discountedTotal, 2) .
            '. Please top up GHS ' . number_format($shortfall, 2) . ' or choose Hubtel/Tingg.',
            'cart'
        );
    }

    $order = checkout_create_order($beneficiaryId, $items, 'Wallet', $add_voucher_api_key);
    $orderCode = $order['order_code'];
    $totalAmount = (float) ($order['total_amount'] ?? $discountedTotal);

    wallet_pay_order((int) $beneficiaryId, $orderCode, $discountedTotal);

    $updateUrl = 'https://fms.kayxappstaroil.com/APIs/voucher_api/update_voucher_order.php?reference=' . urlencode($orderCode);
    $ch = curl_init($updateUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    curl_exec($ch);
    curl_close($ch);

    unset($_SESSION['shopping_cart']);
    header('Location: success_wallet?auth=' . urlencode($orderCode) . '&amount=' . urlencode((string) $totalAmount));
    exit;
} catch (Throwable $exception) {
    checkout_fail($exception->getMessage(), 'cart');
}
