<?php require_once __DIR__ . '/includes/frontend_log.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Store | Star Oil Fuel Voucher System</title>
    
    <!-- Essential Open Graph tags for Facebook, WhatsApp, LinkedIn, etc. -->
  <meta property="og:title" content="StarOil Voucher Store" />
  <meta property="og:description" content="Browse and shop premium fuel vouchers at StarOil's trusted online store." />
  <meta property="og:image" content="https://staroil.services/images/black-logo_2_512x512.png" />
  <meta property="og:url" content="https://staroil.services/store" />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="StarOil Vouchers" />

  <!-- Twitter Card tags -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="StarOil Online Store" />
  <meta name="twitter:description" content="Browse and shop premium fuel vouchers at StarOil's trusted online store." />
  <meta name="twitter:image" content="https://staroil.services/images/alogo_light.jpg" />
  <meta name="twitter:url" content="https://staroil.services/store" />

  <!-- Optional: Canonical URL to clean up parameters -->
  <link rel="canonical" href="https://staroil.services/store" />
    
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="icon" href="https://staroil.services/images/alogo_light.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script>
  </head>

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
    <section class="border-b border-brand-line bg-white/95 px-4 py-4 shadow-sm backdrop-blur sm:px-6 lg:hidden" data-mobile-recommendations-panel data-recommendation-context="store">
      <div class="mx-auto max-w-7xl">
        <div class="mb-3 flex items-end justify-between gap-3">
          <div>
            <p class="text-xs font-bold uppercase text-brand-blue">Recommended</p>
            <h2 class="text-base font-bold">Popular fuel vouchers</h2>
          </div>
          <span class="rounded-full bg-brand-yellow px-2.5 py-1 text-xs font-bold text-brand-ink">Swipe</span>
        </div>
        <div class="-mx-4 flex snap-x gap-3 overflow-x-auto px-4 pb-1 sm:-mx-6 sm:px-6" data-mobile-recommendations-list aria-label="Recommended vouchers"></div>
      </div>
    </section>
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <section>
          <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <p class="text-sm font-semibold text-brand-blue">Voucher Store</p>
              <h1 class="mt-1 text-2xl font-bold sm:text-3xl">Purchase Fuel Vouchers</h1>
              <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-muted sm:text-base">Select prepaid fuel denominations, add them to your cart, and complete checkout through a secure purchase workflow.
              </p>
            </div>
            <a data-auth-only class="hidden inline-flex justify-center rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white" href="cart">Review Cart</a>
            <a data-guest-only class="inline-flex justify-center rounded-ui bg-brand-yellow px-4 py-2.5 text-sm font-bold text-brand-ink" href="signin">Sign in to Purchase</a>
          </div>
          <div class="security-notice mb-6 rounded-ui border border-red-950 bg-[#8B0000] p-4 text-sm leading-6 text-white shadow-soft">
            <h2 class="text-sm font-semibold text-white">Activation and redemption security</h2>
            <p class="mt-1 text-sm leading-6 text-white/90">
              Purchased vouchers remain pending until activated. Redemption requires voucher code validation and customer authentication at an approved Star Oil station.
            </p>
          </div>
          <div id="voucher-grid" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>
          <section class="mt-6 hidden rounded-ui border border-brand-line bg-white p-4 shadow-soft lg:block" data-recommendations-panel data-recommendation-context="store">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
              <div>
                <p class="text-sm font-bold text-brand-blue">Recommended for You</p>
                <h2 class="text-xl font-bold">Add more fuel cover before checkout</h2>
              </div>
              <span class="text-xs font-semibold text-brand-muted">Based on current customer activity</span>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" data-recommendations-list></div>
          </section>
        </section>

        <!-- Aside -->
        <aside class="space-y-4">
          <section data-auth-only class="hidden rounded-ui border border-brand-line bg-white p-4 shadow-soft">
            <h2 class="text-base font-semibold">Quick Summary</h2>
            <dl class="mt-4 space-y-3 text-sm">
              <div class="flex justify-between"><dt class="text-brand-muted">Cart items</dt><dd data-items class="font-bold">0</dd></div>
              <div class="flex justify-between"><dt class="text-brand-muted">Subtotal</dt><dd data-subtotal class="font-bold">GHS 0.00</dd></div>
              <div class="flex justify-between"><dt class="text-brand-muted">Discount</dt><dd data-discount class="font-bold text-emerald-700">GHS 0.00</dd></div>
            </dl>
            <div class="mt-4 border-t border-brand-line pt-4">
              <div class="flex justify-between"><span class="text-sm font-semibold">Estimated total</span><span data-total class="text-xl font-bold">GHS 0.00</span></div>
              <a class="mt-4 flex w-full justify-center rounded-ui bg-brand-yellow px-4 py-2.5 text-sm font-bold" href="cart">Proceed to Cart</a>
            </div>
          </section>
          <section data-guest-only class="rounded-ui border border-brand-line bg-white p-4 shadow-soft">
            <h2 class="text-base font-semibold">Purchase Access</h2>
            <p class="mt-2 text-sm leading-6 text-brand-muted">Voucher browsing is available without an account. Sign in to add vouchers to cart and complete a purchase.</p>
            <a class="mt-4 flex w-full justify-center rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white" href="signin">Sign in to Purchase</a>
          </section>
          <section class="hidden rounded-ui border border-brand-line bg-white p-4 shadow-soft lg:block" data-popular-vouchers-panel>
            <div class="mb-3 flex items-center justify-between gap-3">
              <h2 class="text-base font-semibold">Popular Vouchers</h2>
              <span class="rounded-full bg-brand-yellow px-2.5 py-1 text-xs font-bold text-brand-ink">Trending</span>
            </div>
            <div class="space-y-3" data-popular-vouchers-list></div>
          </section>
          <section class="hidden rounded-ui border border-brand-line bg-white p-4 shadow-soft lg:block" data-recent-purchases-panel>
            <div class="mb-3">
              <h2 class="text-base font-semibold">Recent Customer Purchases</h2>
              <p class="mt-1 text-xs leading-5 text-brand-muted">Anonymous activity from recent voucher purchases.</p>
            </div>
            <div class="space-y-3" data-recent-purchases-list></div>
          </section>
          <section class="rounded-ui border border-brand-line bg-white p-4 shadow-soft" aria-labelledby="offers-title">
            <div class="mb-3 flex items-center justify-between gap-3">
              <h2 id="offers-title" class="text-base font-semibold">Offers and Ads</h2>
              <span class="rounded-full bg-brand-yellow px-2.5 py-1 text-xs font-bold text-brand-ink">Promos</span>
            </div>
            <div class="grid gap-4">
              <article class="overflow-hidden rounded-ui border border-brand-line bg-white shadow-sm">
                <img class="h-32 w-full object-cover" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS4RkFO9bZEl83tJWtMFoCjSYda620nDd9HYA&s" alt="Star Oil station promotional image" loading="lazy" />
                <div class="p-4">
                  <p class="text-xs font-bold uppercase text-brand-blue">Station network</p>
                  <h3 class="mt-2 text-lg font-bold">Redeem across Star Oil outlets</h3>
                  <p class="mt-1 text-sm leading-5 text-brand-muted">Use prepaid vouchers for faster, controlled fuel purchases at approved stations.</p>
                </div>
              </article>
              <article class="overflow-hidden rounded-ui border border-brand-line bg-white shadow-sm">
                <img class="h-28 w-full object-cover" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS4RkFO9bZEl83tJWtMFoCjSYda620nDd9HYA&s" alt="Star Oil fuel service promotional image" loading="lazy" />
                <div class="p-4">
                  <p class="text-xs font-bold uppercase text-brand-blue">Business fueling</p>
                  <h3 class="mt-2 text-lg font-bold">Controlled purchase flow</h3>
                  <p class="mt-1 text-sm leading-5 text-brand-muted">Activation, redemption status, and MFA keep each transaction accountable.</p>
                </div>
              </article>
              <article class="overflow-hidden rounded-ui border border-brand-line bg-white shadow-sm">
                <img class="h-28 w-full object-cover" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS4RkFO9bZEl83tJWtMFoCjSYda620nDd9HYA&s" alt="Star Oil services promotional image" loading="lazy" />
                <div class="p-4">
                  <p class="text-xs font-bold uppercase text-brand-blue">Security reminder</p>
                  <h3 class="mt-2 text-lg font-bold">Enable MFA</h3>
                  <p class="mt-1 text-sm leading-5 text-brand-muted">Protect voucher purchases with OTP or Google Authenticator verification.</p>
                </div>
              </article>
            </div>
          </section>
        </aside>
      </div>
      <footer class="mt-12 border-t border-brand-line bg-dark/95 px-4 py-6 text-center text-sm text-brand-muted backdrop-blur sm:px-6 lg:px-8">
        <p class="font-bold">&copy; <?= date('Y') ?> Star Oil. All rights reserved.</p>
        <a href="https://www.staroil.com.gh" class="text-brand-white hover:underline font-bold">Official Website</a>
        <span class="mx-1">|</span>
        <a href="privacy" class="text-brand-white hover:underline font-bold" target="_blank">Privacy Policy</a>
        <span class="mx-1">|</span>
        <a href="terms-of-use" class="text-brand-white hover:underline font-bold" target="_blank">Terms of Service</a>
      </footer>
    </main>
    <!-- <a class="lube-floating-link fixed right-4 top-1/2 z-40 hidden -translate-y-1/2 items-center gap-2 rounded-ui border border-brand-line bg-brand-yellow px-4 py-3 text-sm font-bold text-brand-ink shadow-soft hover:bg-[#E8BB1E] lg:flex" href="lube_authenticate" aria-label="Validate lubricant">
      <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M9 3h6v4l3 3v9a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-9l3-3V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
        <path d="M9 7h6M9 13h6M9 17h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      </svg>
      <span>Validate Lubricant</span>
    </a>
    <a class="lube-floating-link fixed bottom-4 right-4 z-40 flex items-center gap-2 rounded-ui border border-brand-line bg-brand-yellow px-4 py-3 text-sm font-bold text-brand-ink shadow-soft hover:bg-[#E8BB1E] lg:hidden" href="lube_authenticate" aria-label="Validate lubricant">
      <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M9 3h6v4l3 3v9a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-9l3-3V3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
        <path d="M9 7h6M9 13h6M9 17h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      </svg>
      <span>Validate Lubricant</span>
    </a>-->
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div><script src="assets/app.js"></script>
  </body>
</html>
