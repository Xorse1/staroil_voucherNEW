<?php require_once __DIR__ . '/includes/auth_guard.php'; ?>
<?php require_once __DIR__ . '/includes/frontend_log.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Analytics | Star Oil Fuel Voucher System</title>
    
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
          <a class="mr-auto flex items-center gap-3" href="store"><img class="h-9 w-auto" src="images/alogo_light.png" alt="StarOil logo" /><span class="sr-only">Star Oil Fuel Voucher System</span></a>
          <button class="rounded-ui border border-brand-line bg-white p-2 text-brand-ink shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-blue lg:hidden" data-menu-toggle type="button" aria-controls="primary-menu" aria-expanded="false"><span class="sr-only">Toggle navigation</span><svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" /></svg></button>
        </div>
        <div id="primary-menu" data-menu class="hidden mt-3 flex-col gap-2 rounded-ui border border-brand-line bg-white p-2 shadow-soft lg:mt-0 lg:flex lg:flex-row lg:items-center lg:justify-end lg:gap-2 lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
          <a data-nav class="block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="store">Store</a>
          <a data-nav data-auth-only class="hidden block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="vouchers">My Vouchers</a>
          <a data-nav data-auth-only class="hidden block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="wallet">Wallet</a>
          <a data-nav class="block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="faqs">FAQs</a>
          <a data-nav data-auth-only class="hidden block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="profile">Profile</a>
          <a data-nav data-auth-only class="hidden block w-full rounded-ui border border-brand-line px-3 py-2 text-sm font-semibold lg:w-auto" href="cart">Cart <span data-cart-count class="ml-1 rounded-full bg-brand-yellow px-2 py-0.5 text-xs text-brand-ink">0</span></a>
          <label class="block w-full lg:w-auto"><span class="sr-only">Theme</span><select data-theme-select class="w-full rounded-ui border border-brand-line bg-white px-3 py-2 text-sm font-semibold text-brand-ink lg:w-auto"><option value="system">System</option><option value="white">White</option><option value="dark">Dark</option></select></label>
          <span data-auth-only data-user-welcome class="hidden block w-full rounded-ui bg-brand-soft px-3 py-2 text-sm font-semibold text-brand-ink lg:w-auto lg:max-w-[220px] lg:truncate"></span>
          <a data-auth-only class="hidden block w-full rounded-ui px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 lg:w-auto" href="logout">Logout</a>
        </div>
      </nav>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
      <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-sm font-semibold text-brand-blue">Analytics</p>
          <h1 class="mt-1 text-2xl font-bold sm:text-3xl">Voucher Insights</h1>
          <p class="mt-2 max-w-3xl text-sm leading-6 text-brand-muted">Track your purchased vouchers, activation readiness for redemption, wallet balance, and personal voucher usage trends.</p>
        </div>
        <button class="rounded-ui border border-brand-blue bg-white px-4 py-2.5 text-sm font-semibold text-brand-blue hover:bg-[#EEF7FF]" type="button" data-analytics-refresh>Refresh</button>
      </div>

      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-analytics-tiles>
        <div class="rounded-ui border border-brand-line bg-white p-4 shadow-soft"><p class="text-sm text-brand-muted">Loading analytics...</p></div>
      </section>

      <section class="mt-6 grid gap-6 xl:grid-cols-2">
        <article class="rounded-ui border border-brand-line bg-white p-5 shadow-soft">
          <div class="flex items-center justify-between gap-3">
            <div><h2 class="font-semibold">Voucher Status</h2><p class="mt-1 text-sm text-brand-muted">Pending means purchased but not yet activated for redemption.</p></div>
          </div>
          <div class="mt-5" data-chart-status></div>
        </article>

        <article class="rounded-ui border border-brand-line bg-white p-5 shadow-soft">
          <div><h2 class="font-semibold">Monthly Purchase Value</h2><p class="mt-1 text-sm text-brand-muted">Voucher value grouped by start month.</p></div>
          <div class="mt-5" data-chart-monthly></div>
        </article>

        <article class="rounded-ui border border-brand-line bg-white p-5 shadow-soft">
          <div><h2 class="font-semibold">Purchased Denominations</h2><p class="mt-1 text-sm text-brand-muted">Most common voucher amounts in your purchases.</p></div>
          <div class="mt-5" data-chart-denominations></div>
        </article>

        <article class="rounded-ui border border-brand-line bg-white p-5 shadow-soft">
          <div><h2 class="font-semibold">Cart Activity</h2><p class="mt-1 text-sm text-brand-muted">Current cart value and selected voucher mix on this device.</p></div>
          <div class="mt-5" data-chart-cart></div>
        </article>
      </section>

      <section class="mt-6 rounded-ui border border-brand-line bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div><h2 class="font-semibold">Personal Voucher Signals</h2><p class="mt-1 text-sm text-brand-muted">Quick scan of your activation, redemption, and expiry follow-up areas.</p></div>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-analytics-signals></div>
      </section>
    </main>

    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div>
    <script src="assets/app.js?v=analytics-user-20260614"></script>
  </body>
</html>
