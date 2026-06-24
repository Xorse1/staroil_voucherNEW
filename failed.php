<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();
require_once __DIR__ . '/includes/frontend_log.php';

$reason = isset($_GET['reason']) ? trim((string) $_GET['reason']) : '';
unset($_SESSION['shopping_cart']);
?>
<!doctype html>
<html lang="en">
  <head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><title>Payment Failed | Star Oil Fuel Voucher System</title><link rel="preconnect" href="https://fonts.bunny.net" /><link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" /><script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script></head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
    <main class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-8">
      <section class="w-full rounded-ui border border-brand-line bg-white p-6 text-center shadow-soft">
        <img class="mx-auto h-12 w-auto" src="images/alogo_light.png" alt="StarOil logo" />
        <div class="mx-auto mt-6 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-2xl font-bold text-red-800">!</div>
        <p class="mt-5 text-sm font-semibold text-red-700">Payment Failed</p>
        <h1 class="mt-2 text-2xl font-bold sm:text-3xl">Voucher Purchase Failed</h1>
        <p class="mt-3 text-sm leading-6 text-brand-muted">The payment could not be completed. No successful payment confirmation was received for this checkout.</p>
        <?php if ($reason !== ''): ?><p class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800"><?= htmlspecialchars($reason) ?></p><?php endif; ?>
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
          <a class="rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white" href="cart">Return to Cart</a>
          <a class="rounded-ui border border-brand-line px-4 py-2.5 text-sm font-semibold text-brand-ink" href="store">Back to Store</a>
        </div>
      </section>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div><script src="assets/app.js"></script>
  </body>
</html>
