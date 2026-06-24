<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();
require_once __DIR__ . '/includes/frontend_log.php';

$oldSystemUrl = getenv('STAROIL_OLD_SYSTEM_URL') ?: 'https://staroil.services/store';
$newSystemUrl = getenv('STAROIL_NEW_SYSTEM_URL') ?: 'store';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#2178BD" />
    
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
    
    <title>Star Oil Voucher Systems</title>
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="icon" href="https://staroil.services/images/alogo_light.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              brand: {
                blue: "#2178BD",
                yellow: "#FDCD21",
                ink: "#15253A",
                muted: "#64748B",
                line: "#D8E0EA",
                soft: "#F5F8FB"
              }
            },
            fontFamily: {
              sans: ["Instrument Sans", "ui-sans-serif", "system-ui", "sans-serif"]
            },
            borderRadius: { ui: "8px" },
            boxShadow: { soft: "0 12px 28px rgba(21,37,58,.08)" }
          }
        }
      };
    </script>
  </head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink antialiased">
    <label class="fixed right-4 top-4 z-50 block w-32">
      <span class="sr-only">Theme</span>
      <select data-theme-select class="w-full rounded-ui border border-brand-line bg-white px-3 py-2 text-sm font-semibold text-brand-ink">
        <option value="system">System</option>
        <option value="white">White</option>
        <option value="dark">Dark</option>
      </select>
    </label>

    <main class="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-4 py-10 sm:px-6 lg:px-8">
      <section class="mb-6 rounded-ui border border-brand-line bg-white p-6 shadow-soft">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-3">
            <img class="h-11 w-auto" src="images/alogo_light.png" alt="StarOil logo" />
            <div>
              <p class="text-sm font-semibold text-brand-blue">Fuel Voucher Access</p>
              <h1 class="text-2xl font-bold sm:text-3xl">Choose your voucher system</h1>
            </div>
          </div>
          <a class="inline-flex items-center justify-center rounded-ui border border-brand-line bg-white px-4 py-2.5 text-sm font-semibold text-brand-ink hover:border-brand-blue hover:text-brand-blue focus:outline-none focus:ring-2 focus:ring-brand-blue" href="faqs">FAQs</a>
        </div>
        <p class="mt-4 max-w-3xl text-sm leading-6 text-brand-muted">Both options connect customers to the StarOil voucher service. Customers who are used to the old interface can continue with it while they adjust, and they can also start using the new interface at any time.</p>
      </section>

      <section class="grid gap-5 lg:grid-cols-2" aria-label="Voucher system options">
        <article class="rounded-ui border border-brand-line bg-white p-6 shadow-soft">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-brand-muted">Familiar interface</p>
              <h2 class="mt-1 text-2xl font-bold">Old Voucher System</h2>
            </div>
            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-900">Still available</span>
          </div>
          <p class="mt-4 text-sm leading-6 text-brand-muted">Continue here if you prefer the familiar voucher interface while getting comfortable with the new one.</p>
          <div class="mt-6 grid gap-3 sm:grid-cols-2">
            <a class="inline-flex items-center justify-center rounded-ui bg-brand-blue px-4 py-3 text-sm font-semibold text-white hover:bg-[#1A659F] focus:outline-none focus:ring-2 focus:ring-brand-blue" href="<?= htmlspecialchars($oldSystemUrl) ?>">Open old system</a>
            <a class="inline-flex items-center justify-center rounded-ui border border-brand-line bg-white px-4 py-3 text-sm font-semibold text-brand-ink hover:border-brand-blue hover:text-brand-blue focus:outline-none focus:ring-2 focus:ring-brand-blue" href="https://staroil.services/signin">Old system login</a>
          </div>
        </article>

        <article class="rounded-ui border-2 border-brand-blue bg-white p-6 shadow-soft">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-brand-blue">Updated interface</p>
              <h2 class="mt-1 text-2xl font-bold">New Voucher System</h2>
            </div>
            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-900">Recommended</span>
          </div>
          <p class="mt-4 text-sm leading-6 text-brand-muted">Use the new interface to browse vouchers, purchase through the updated checkout, manage vouchers, and set up OTP or Google Authenticator security.</p>
          <div class="mt-6 grid gap-3 sm:grid-cols-2">
            <a class="inline-flex items-center justify-center rounded-ui bg-brand-yellow px-4 py-3 text-sm font-bold text-brand-ink hover:bg-[#E8BB1E] focus:outline-none focus:ring-2 focus:ring-brand-blue" href="<?= htmlspecialchars($newSystemUrl) ?>">Open new system</a>
            <a class="inline-flex items-center justify-center rounded-ui border border-brand-line bg-white px-4 py-3 text-sm font-semibold text-brand-ink hover:border-brand-blue hover:text-brand-blue focus:outline-none focus:ring-2 focus:ring-brand-blue" href="signin">New system login</a>
          </div>
        </article>
      </section>

      <section class="mt-6 rounded-ui border border-brand-line bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-base font-bold">Need to validate a lubricant?</h2>
            <p class="mt-1 text-sm leading-6 text-brand-muted">You can still authenticate StarOil lubricant products from the new platform.</p>
          </div>
          <a class="inline-flex items-center justify-center rounded-ui border border-brand-line bg-white px-4 py-2.5 text-sm font-semibold text-brand-ink hover:border-brand-blue hover:text-brand-blue focus:outline-none focus:ring-2 focus:ring-brand-blue" href="lube_authenticate">Validate lubricant</a>
        </div>
      </section>
    </main>

    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div>
    <script src="assets/app.js"></script>
  </body>
</html>
