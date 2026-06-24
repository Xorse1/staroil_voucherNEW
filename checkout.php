<?php require_once __DIR__ . '/includes/auth_guard.php'; ?>
<?php require_once __DIR__ . '/includes/frontend_log.php'; ?>
<!doctype html>
<html lang="en">
  <head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><title>Checkout | Star Oil Fuel Voucher System</title><link rel="preconnect" href="https://fonts.bunny.net" /><link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" /><script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script></head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
    <header class="sticky top-0 z-40 border-b border-brand-line bg-white/95 backdrop-blur">
  <nav class="mx-auto max-w-7xl px-4 py-3 sm:px-6 lg:px-8" aria-label="Primary navigation">
    <div class="flex items-center gap-3">
      <a class="mr-auto flex items-center gap-3" href="store">
        <img class="h-9 w-auto" src="images/alogo_light.png" alt="StarOil logo" />
        <span class="sr-only">Star Oil Fuel Voucher System</span>
      </a>
      <button class="rounded-ui border border-brand-line bg-white p-2 text-brand-ink shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue lg:hidden" data-menu-toggle type="button" aria-controls="primary-menu" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
        </svg>
      </button>
    </div>
    <div id="primary-menu" data-menu class="hidden mt-3 flex-col gap-2 rounded-ui border border-brand-line bg-white p-2 shadow-soft lg:mt-0 lg:flex lg:flex-row lg:items-center lg:justify-end lg:gap-2 lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
      <a data-nav class="block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="store">Store</a>
      <a data-nav data-auth-only class="hidden block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="vouchers">My Vouchers</a>
      <a data-nav class="block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="faqs">FAQs</a>
      <a data-nav data-auth-only class="hidden block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="profile">Profile</a>
      <a data-nav data-auth-only class="hidden block w-full rounded-ui border border-brand-line px-3 py-2 text-sm font-semibold lg:w-auto" href="cart">Cart <span data-cart-count class="ml-1 rounded-full bg-brand-yellow px-2 py-0.5 text-xs text-brand-ink">0</span></a>
      <label class="block w-full lg:w-auto"><span class="sr-only">Theme</span><select data-theme-select class="w-full rounded-ui border border-brand-line bg-white px-3 py-2 text-sm font-semibold text-brand-ink lg:w-auto"><option value="system">System</option><option value="white">White</option><option value="dark">Dark</option></select></label><span data-auth-only data-user-welcome class="hidden block w-full rounded-ui bg-brand-soft px-3 py-2 text-sm font-semibold text-brand-ink lg:w-auto lg:max-w-[220px] lg:truncate"></span>
      <a data-auth-only class="hidden block w-full rounded-ui px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 lg:w-auto" href="logout">Logout</a>
      <a data-guest-only class="block w-full rounded-ui bg-brand-blue px-3 py-2 text-sm font-semibold text-white hover:bg-[#1A659F] lg:w-auto" href="signin">Sign in</a>
    </div>
  </nav>
</header>
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <div class="mb-5"><p class="text-sm font-semibold text-brand-blue">Checkout</p><h1 class="mt-1 text-2xl font-bold sm:text-3xl">Confirm Purchase</h1><p class="mt-2 text-sm leading-6 text-brand-muted">Review customer details and payment before placing the order.</p></div>
      <?php if (!empty($_SESSION['checkout_error'])): ?><div class="mb-5 rounded-ui border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert"><?= htmlspecialchars($_SESSION['checkout_error']) ?></div><?php unset($_SESSION['checkout_error']); endif; ?>
      <form id="checkout-form" action="checkout_process" method="POST" data-tingg-action="checkout_process" data-hubtel-action="checkout_process_hubtel">
      <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]"><div class="space-y-6"><section class="rounded-ui border border-brand-line bg-white p-4 shadow-soft"><h2 class="font-semibold">Personal Information</h2><div class="mt-4 grid gap-4 sm:grid-cols-2"><label><span class="text-sm font-medium text-brand-muted">Name</span><input class="mt-1 w-full rounded-ui border border-brand-line bg-brand-soft px-3 py-2.5 text-sm" value="<?= htmlspecialchars($_SESSION['name'] ?? '') ?>" readonly /></label><label><span class="text-sm font-medium text-brand-muted">Beneficiary ID</span><input class="mt-1 w-full rounded-ui border border-brand-line bg-brand-soft px-3 py-2.5 text-sm" value="<?= htmlspecialchars((string) ($_SESSION['user_id'] ?? '')) ?>" readonly /></label><label><span class="text-sm font-medium text-brand-muted">Email</span><input class="mt-1 w-full rounded-ui border border-brand-line bg-brand-soft px-3 py-2.5 text-sm" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" readonly /></label><label><span class="text-sm font-medium text-brand-muted">Phone</span><input class="mt-1 w-full rounded-ui border border-brand-line bg-brand-soft px-3 py-2.5 text-sm" value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>" readonly /></label></div><input type="hidden" name="beneficiary_id" value="<?= htmlspecialchars((string) ($_SESSION['user_id'] ?? '')) ?>" /><input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" /><input type="hidden" name="phone" value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>" /><input type="hidden" name="name" value="<?= htmlspecialchars($_SESSION['name'] ?? '') ?>" /><input type="hidden" name="totalamount" data-totalamount-field value="0" /><input type="hidden" name="discounted_total" data-discounted-total-field value="0" /><input type="hidden" name="payment_gateway" data-payment-gateway-field value="Tingg" /><input type="hidden" name="cart_payload" data-cart-payload-field value="" /></section><section class="rounded-ui border border-brand-line bg-white p-4 shadow-soft"><h2 class="font-semibold">Payment Method</h2><div class="mt-4 rounded-ui border border-brand-line bg-brand-soft p-4"><p data-payment-method class="text-sm font-bold">Tingg (MoMo)</p><p class="mt-1 text-sm text-brand-muted">The order will be submitted to the selected payment processor after placement.</p></div></section></div><aside class="h-fit rounded-ui border border-brand-line bg-white p-4 shadow-soft"><h2 class="font-semibold">Final Order Summary</h2><div id="checkout-items" class="mt-4 space-y-3"></div><dl class="mt-4 space-y-3 border-t border-brand-line pt-4 text-sm"><div class="flex justify-between"><dt class="text-brand-muted">Subtotal</dt><dd data-subtotal class="font-bold">GHS 0.00</dd></div><div class="flex justify-between"><dt class="text-brand-muted">Total Discount <span data-discount-rate class="text-xs">(0%)</span></dt><dd data-discount class="font-bold text-emerald-700">GHS 0.00</dd></div><div class="flex justify-between border-t border-brand-line pt-3"><dt class="text-base font-semibold">Final total</dt><dd data-total class="text-xl font-bold">GHS 0.00</dd></div></dl><button id="place-order" name="checkout" class="mt-5 w-full rounded-ui bg-brand-yellow px-4 py-2.5 text-sm font-bold" type="submit">Pay Now</button></aside></div>
      </form>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div><script src="https://cdn.cellulant.africa/js/tingg-checkout-library.js"></script><script src="assets/app.js"></script>
  </body>
</html>
