<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();

//require_once __DIR__ . '/config.php';
require_once __DIR__ . '/access_token.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['phone_verify'])) {
    header('Location: store');
    exit;
}

$orderCode = isset($_GET['auth']) ? trim((string) $_GET['auth']) : '';
$amount = isset($_GET['amount']) ? trim((string) $_GET['amount']) : '0';
$acknowledgementError = '';

if ($orderCode !== '' && !empty($access_token)) {
    $curl = curl_init();

    if ($curl !== false) {
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.tingg.africa/v3/checkout-api/acknowledgement/request',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'acknowledgement_amount' => $amount,
                'acknowledgement_type' => 'Full',
                'acknowledgement_narration' => 'Payment Acknowledged',
                'acknowledgment_reference' => $orderCode,
                'merchant_transaction_id' => $orderCode,
                'service_code' => 'STAROILVOUCHERCHECKO',
                'status_code' => '183',
                'currency_code' => 'GHS'
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $access_token,
                'accept: application/json',
                'apikey: ' . $apiKey,
                'content-type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            $acknowledgementError = $error;
        } else {
            file_put_contents(__DIR__ . '/tingg_success_ack.log', date('Y-m-d H:i:s') . ' - ' . $response . PHP_EOL, FILE_APPEND);
        }
    }
}

unset($_SESSION['shopping_cart']);
?>
<!doctype html>
<html lang="en">
  <head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><title>Payment Successful | Star Oil Fuel Voucher System</title><link rel="preconnect" href="https://fonts.bunny.net" /><link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" /><script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script></head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
    <main class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-8">
      <section class="w-full rounded-ui border border-brand-line bg-white p-6 text-center shadow-soft">
        <img class="mx-auto h-12 w-auto" src="images/alogo_light.png" alt="StarOil logo" />
        <div class="mx-auto mt-6 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-2xl font-bold text-emerald-800">✓</div>
        <p class="mt-5 text-sm font-semibold text-brand-blue">Tingg Payment</p>
        <h1 class="mt-2 text-2xl font-bold sm:text-3xl">Voucher Purchase Successful</h1>
        <p class="mt-3 text-sm leading-6 text-brand-muted">Your payment was completed. Use the order code below to identify this purchase.</p>
        <?php if ($orderCode !== ''): ?><div class="mt-5 rounded-ui border border-brand-line bg-brand-soft p-4"><p class="text-xs font-semibold uppercase text-brand-muted">Order Code</p><p class="mt-1 break-all text-2xl font-bold"><?= htmlspecialchars($orderCode) ?></p></div><?php endif; ?>
        <?php if ($acknowledgementError !== ''): ?><p class="mt-4 rounded-ui border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800">Payment succeeded, but the Tingg acknowledgement request returned an error.</p><?php endif; ?>
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
          <a class="rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white" href="vouchers">View My Vouchers</a>
          <a class="rounded-ui border border-brand-line px-4 py-2.5 text-sm font-semibold text-brand-ink" href="store">Back to Store</a>
        </div>
      </section>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div><script>localStorage.removeItem("staroil:cart"); localStorage.removeItem("staroil:cartId"); localStorage.removeItem("staroil:payment"); localStorage.removeItem("staroil:paymentLabel");</script><script src="assets/app.js"></script>
  </body>
</html>
