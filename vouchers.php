<?php require_once __DIR__ . '/includes/auth_guard.php'; ?>
<!doctype html>
<html lang="en">
  <head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><title>My Vouchers | Star Oil Fuel Voucher System</title><link rel="preconnect" href="https://fonts.bunny.net" /><link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" /><script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script></head>
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
      <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
          <p class="text-sm font-semibold text-brand-blue">Voucher Management</p>
          <h1 class="mt-1 text-2xl font-bold sm:text-3xl">My Vouchers</h1>
          <p class="mt-2 text-sm leading-6 text-brand-muted">Search, filter, view, edit, print, or export purchased vouchers.</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
          <label class="sr-only" for="voucher-search">Search vouchers</label>
          <input id="voucher-search" class="rounded-ui border border-brand-line bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" type="search" placeholder="Search code, auth, station, phone" />
          <label class="sr-only" for="status-filter">Filter by status</label>
          <select id="status-filter" class="rounded-ui border border-brand-line bg-white px-3 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <option value="all">All statuses</option>
            <option>Pending</option>
            <option>Activated</option>
            <option>Redeemed</option>
            <option>Not Redeemed</option>
            <option>Expired</option>
          </select>
          <button id="export-vouchers" class="rounded-ui border border-brand-blue bg-white px-3 py-2 text-sm font-semibold text-brand-blue hover:bg-[#EEF7FF]" type="button">Export CSV</button>
          <div class="grid grid-cols-2 rounded-ui border border-brand-line bg-white p-1" aria-label="Voucher view">
            <button data-voucher-view="grid" class="rounded-ui bg-brand-blue px-3 py-2 text-sm font-semibold text-white" type="button">Grid</button>
            <button data-voucher-view="table" class="rounded-ui px-3 py-2 text-sm font-semibold text-brand-muted" type="button">Table</button>
          </div>
        </div>
      </div>
      <?php if (!empty($_SESSION['successupdated'])): ?>
        <div class="mb-5 rounded-ui border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status"><?= htmlspecialchars($_SESSION['successupdated']) ?></div>
        <?php unset($_SESSION['successupdated']); endif; ?>
      <?php if (!empty($_SESSION['successerrorupdated'])): ?>
        <div class="mb-5 rounded-ui border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert"><?= htmlspecialchars($_SESSION['successerrorupdated']) ?></div>
        <?php unset($_SESSION['successerrorupdated']); endif; ?>
      <section id="voucher-table-view" class="hidden rounded-ui border border-brand-line bg-white shadow-soft">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-brand-line">
            <thead class="bg-brand-soft">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-brand-muted">Actions</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-brand-muted">Order Code</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-brand-muted">Voucher Code</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-brand-muted">Auth</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-brand-muted">Amount</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-brand-muted">Order Date</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-brand-muted">Expiry</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-brand-muted">Station</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-brand-muted">Redeemed</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-brand-muted">Redeemed Phone</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-brand-muted">Status</th>
              </tr>
            </thead>
            <tbody id="voucher-table" class="divide-y divide-brand-line"></tbody>
          </table>
        </div>
      </section>
      <section id="voucher-grid-view" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-live="polite"></section>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div><script src="assets/app.js"></script>
  </body>
</html>
