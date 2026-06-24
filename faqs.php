<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();
require_once __DIR__ . '/includes/frontend_log.php';

$promodate = gmdate('Y-m-d');
if ($promodate >= '2025-12-23' && $promodate <= '2025-12-31' && file_exists(__DIR__ . '/includes/discount_banner.php')) {
    include __DIR__ . '/includes/discount_banner.php';
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#2178BD" />
    <meta property="og:title" content="Frequently Asked Questions - StarOil Voucher Store" />
    <meta property="og:description" content="Learn how to sign up, purchase, activate, and redeem StarOil fuel vouchers." />
    <meta property="og:image" content="https://staroil.services/images/black-logo_2_512x512.png" />
    <meta property="og:url" content="https://staroil.services/faqs" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="StarOil Vouchers" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="StarOil Voucher FAQs" />
    <meta name="twitter:description" content="Operational guidance for voucher purchase, activation, and redemption." />
    <meta name="twitter:image" content="images/alogo_light.png" />
    <meta name="twitter:url" content="https://staroil.services/faqs" />
    <link rel="canonical" href="https://staroil.services/faqs" />
    <title>FAQs | Star Oil Fuel Voucher System</title>
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="icon" href="images/alogo_light.png" type="image/x-icon" />
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
          <label class="block w-full lg:w-auto">
            <span class="sr-only">Theme</span>
            <select data-theme-select class="w-full rounded-ui border border-brand-line bg-white px-3 py-2 text-sm font-semibold text-brand-ink lg:w-auto">
              <option value="system">System</option>
              <option value="white">White</option>
              <option value="dark">Dark</option>
            </select>
          </label>
          <span data-auth-only data-user-welcome class="hidden block w-full rounded-ui bg-brand-soft px-3 py-2 text-sm font-semibold text-brand-ink lg:w-auto lg:max-w-[220px] lg:truncate"></span>
          <a data-auth-only class="hidden block w-full rounded-ui px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 lg:w-auto" href="logout">Logout</a>
          <a data-guest-only class="block w-full rounded-ui bg-brand-blue px-3 py-2 text-sm font-semibold text-white hover:bg-[#1A659F] lg:w-auto" href="signin">Sign in</a>
        </div>
      </nav>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
      <section class="mb-6 rounded-ui border border-brand-line bg-white p-5 shadow-soft sm:p-6">
        <p class="text-sm font-semibold text-brand-blue">Support</p>
        <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <h1 class="text-2xl font-bold sm:text-3xl">Frequently Asked Questions</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-muted">Everything you need to know about StarOil fuel vouchers, from account setup to purchase, activation, and redemption.</p>
          </div>
          <a class="inline-flex items-center justify-center rounded-ui border border-brand-line bg-white px-4 py-2 text-sm font-semibold text-brand-ink hover:border-brand-blue hover:text-brand-blue focus:outline-none focus:ring-2 focus:ring-brand-blue" href="store">Back to store</a>
        </div>
      </section>

      <section class="space-y-3" aria-label="Frequently asked questions">
        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft" open>
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>How do I sign up to the StarOil e-voucher store?</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary>
          <ol class="mt-4 space-y-3 text-sm leading-6 text-brand-muted">
            <li><strong class="text-brand-blue">Step 1:</strong> Visit <a class="font-semibold text-brand-blue underline-offset-4 hover:underline" href="store">www.staroil.services/store</a>.</li>
            <li><strong class="text-brand-blue">Step 2:</strong> Click the registration button and start creating your account.</li>
            <li><strong class="text-brand-blue">Step 3:</strong> Fill in your details. Your password should be 8 to 20 characters with at least one uppercase letter, one lowercase letter, one number, and one special character.</li>
            <li><strong class="text-brand-blue">Step 4:</strong> You will receive a One Time Password (OTP) on your phone. Enter it to verify your account.</li>
            <li><strong class="text-brand-blue">Step 5:</strong> Log in with your phone number to access your account and start using the StarOil e-voucher service.</li>
          </ol>
        </details>

        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>How do I purchase StarOil's e-voucher?</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary>
          <ol class="mt-4 space-y-3 text-sm leading-6 text-brand-muted">
            <li><strong class="text-brand-blue">Step 1:</strong> Log into your StarOil voucher portal through <a class="font-semibold text-brand-blue underline-offset-4 hover:underline" href="store">www.staroil.services/store</a> using your phone number and password.</li>
            <li><strong class="text-brand-blue">Step 2:</strong> Choose your preferred e-voucher and click the purchase button.</li>
            <li><strong class="text-brand-blue">Step 3:</strong> Enter the quantity, then click Add to Cart. To add a different denomination, return to the store and add the next voucher to the same order.</li>
            <li><strong class="text-brand-blue">Step 4:</strong> Adjust quantity or remove items as needed, then continue to checkout and place your order.</li>
            <li><strong class="text-brand-blue">Step 5:</strong> Choose a payment method such as Mobile Money, Bank Transfer, or Card, depending on the options shown at checkout.</li>
            <li><strong class="text-brand-blue">Step 6:</strong> For Mobile Money, select your network, enter your phone number, and input the OTP sent to you.</li>
            <li><strong class="text-brand-blue">Step 7:</strong> Click Proceed, send the prompt, and enter your Mobile Money PIN to complete payment.</li>
            <li><strong class="text-brand-blue">Step 8:</strong> After payment, follow the payment provider's return link back to the merchant page.</li>
            <li><strong class="text-brand-blue">Step 9:</strong> You will receive an order confirmation and a link to access your vouchers.</li>
          </ol>
        </details>

        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>How do I activate and redeem my voucher?</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary>
          <ol class="mt-4 space-y-3 text-sm leading-6 text-brand-muted">
            <li><strong class="text-brand-blue">Step 1:</strong> After logging in, click My Vouchers to view the list of vouchers you have purchased.</li>
            <li><strong class="text-brand-blue">Step 2:</strong> Click the edit action to open the voucher settings.</li>
            <li><strong class="text-brand-blue">Step 3:</strong> Choose your preferred start date and expiry date.</li>
            <li><strong class="text-brand-blue">Step 4:</strong> Review the voucher status and choose Activate to enable the voucher for use.</li>
            <li><strong class="text-brand-blue">Step 5:</strong> Click Update Voucher to apply and save your changes.</li>
            <li><strong class="text-brand-blue">Step 6:</strong> Click the print action to access the digital version of your voucher.</li>
            <li><strong class="text-brand-blue">Step 7:</strong> Save the voucher as an image or PDF.</li>
            <li><strong class="text-brand-blue">Step 8:</strong> Present the downloaded or printed e-voucher at any StarOil station to redeem.</li>
            <li><strong class="text-brand-blue">Step 9:</strong> For added security, the redeemer's phone number will be collected and an OTP will be sent to complete the transaction.</li>
          </ol>
        </details>
      </section>

      <section class="mt-6 rounded-ui border border-brand-line bg-white p-5 shadow-soft">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-lg font-bold">Need more help?</h2>
            <p class="mt-1 text-sm leading-6 text-brand-muted">Sign in to review your cart, manage your vouchers, or update account security settings.</p>
          </div>
          <div class="flex flex-col gap-2 sm:flex-row">
            <a class="inline-flex items-center justify-center rounded-ui bg-brand-blue px-4 py-2 text-sm font-semibold text-white hover:bg-[#1A659F] focus:outline-none focus:ring-2 focus:ring-brand-blue" href="signin">Sign in</a>
            <a class="inline-flex items-center justify-center rounded-ui border border-brand-line bg-white px-4 py-2 text-sm font-semibold text-brand-ink hover:border-brand-blue hover:text-brand-blue focus:outline-none focus:ring-2 focus:ring-brand-blue" href="signup">Create account</a>
          </div>
        </div>
      </section>
    </main>

    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div>
    <script src="assets/app.js"></script>
  </body>
</html>
