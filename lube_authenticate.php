<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();
require_once __DIR__ . '/includes/frontend_log.php';
?>
<!doctype html>
<html lang="en">
  <head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><title>Validate Lubricant | Star Oil Fuel Voucher System</title><link rel="preconnect" href="https://fonts.bunny.net" /><link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" /><script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script></head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
    <main class="mx-auto flex min-h-screen max-w-xl items-center px-4 py-8">
      <section class="w-full rounded-ui border border-brand-line bg-white p-6 shadow-soft">
        <a class="mb-5 inline-flex text-sm font-semibold text-brand-blue" href="store">Back to Store</a>
        <div class="mb-5 flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-ui bg-brand-yellow text-brand-ink">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 3h6v4l3 3v9a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-9l3-3V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9 7h6M9 13h6M9 17h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
          </div>
          <div><p class="text-sm font-semibold text-brand-blue">Product Authentication</p><h1 class="text-2xl font-bold">Validate Lubricant</h1></div>
        </div>
        <?php if (isset($_SESSION['true'])): ?><div class="mb-4 rounded-ui border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status"><?= $_SESSION['true'] ?></div><?php unset($_SESSION['true']); endif; ?>
        <?php if (isset($_SESSION['false'])): ?><div class="mb-4 rounded-ui border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert"><?= $_SESSION['false'] ?></div><?php unset($_SESSION['false']); endif; ?>
        <p class="text-sm leading-6 text-brand-muted">Please enter your StarOil lubricant code below to confirm product authenticity.</p>
        <form action="lube_code_authentication" method="post" class="mt-5 grid gap-4">
          <label><span class="text-sm font-medium text-brand-muted">Validation Code <span class="text-red-600">*</span></span><input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-brand-blue" type="text" name="lube_code" placeholder="Enter 6-char code" maxlength="20" required /></label>
          <label><span class="text-sm font-medium text-brand-muted">Customer Phone Number <span class="text-xs">(Optional)</span></span><span class="mt-1 block text-xs leading-5 text-brand-muted">Add your phone number to receive StarOil fuel discounts, lubricant promotions, and customer reward offers.</span><input class="mt-2 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" type="text" name="customer_phone_no" placeholder="e.g. 024123456" /></label>
          <button type="submit" name="validate" class="rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1A659F]">Authenticate</button>
        </form>
      </section>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div><script src="assets/app.js"></script>
  </body>
</html>
