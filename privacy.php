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
        <p class="text-sm font-semibold text-brand-blue">Legal</p>
        <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <h1 class="text-2xl font-bold sm:text-3xl">Star Oil Voucher System - Privacy Policy</h1>
            <small class="text-sm font-semibold text-brand-muted">Last updated: June 2026</small><br>
            <small class="text-sm font-semibold text-brand-muted">Application Portal: https://staroil.services</small><br>
            <small class="text-sm font-semibold text-brand-muted">Corporate Website: https://staroil.com.gh</small>
          </div>
          <a class="inline-flex items-center justify-center rounded-ui border border-brand-line bg-white px-4 py-2 text-sm font-semibold text-brand-ink hover:border-brand-blue hover:text-brand-blue focus:outline-none focus:ring-2 focus:ring-brand-blue" href="store">Back to store</a>
        </div>
      </section>

      <section class="space-y-3" aria-label="Terms of use">
        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft" open>
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>Introduction </span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br> 
          Star Oil Company Limited ("Company", "we", "us", or "our") respects your privacy and is fully committed to protecting your personal data. This Privacy Policy outlines how we collect, process, store, and safeguard your information when you use the Star Oil Voucher System hosted at staroil.services (the "Application") and its corresponding digital wallet and voucher delivery platforms.<br><br>

          This policy is designed to meet our statutory transparency requirements under the Data Protection Act, 2012 (Act 843) of Ghana.
        </details>

        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>1. Data Controller and Platform Ecosystem</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          <b>1.1 Data Controller:</b> Star Oil Company Limited acts as the Data Controller for the personal data collected during your registration and use of the Application. For any questions regarding your data privacy, please contact our Compliance Officer at customercomplaints@staroil.live.<br>
          <b>1.2 Domain Structure:</b> The voucher system application, ledger database, and user dashboard operate exclusively under the domain [https://staroil.services](https://staroil.services). This infrastructure is separate from, but officially affiliated with, our primary corporate web presence located at [https://staroil.com.gh](https://staroil.com.gh).
        </details>

        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>2. Information We Collect</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          To provide a seamless digital wallet and automated voucher redemption experience, we collect the following categories of information via staroil.services:<br><br>
        <ul class="list-disc pl-5">
            <li class="font-bold">Information You Provide Directly</li>
                1. Identity and Account Data: Full name, mobile phone number, email address, and account login credentials (PINs).<br>
                2. Profile Data: Your preferences, saved settings, and voucher usage history.<br><br>
            <li class="font-bold">Transaction and Financial Data</li>
                1. Transaction Records: Details of wallet top-ups, transaction amounts, timestamps, voucher issuance codes, and voucher redemption statuses.<br>
                2. Payment Gateway Data: When making purchases or topping up your in-app wallet, your transactions are processed directly by our licensed partner, Hubtel.<br>
                3. Important Note on Payment Security: Star Oil does not collect, read, or store your mobile money wallet PINs, bank account numbers, or debit/credit card sensitive details. All payment processing runs entirely within Hubtel’s secure infrastructure. Hubtel only shares transaction status parameters (Success, Reference ID, Amount) with our servers to credit your in-app wallet balance or issue your voucher.<br><br>
            <li class="font-bold">Technical and Usage Data</li>
                1. Device Information: IP addresses, unique device identifiers (IMEI/UUID), operating system, and mobile network operator details.<br>
                2. Location Data: If authorized by your mobile device or browser settings on staroil.services, we may collect location details to help you find the nearest authorized Star Oil filling station for voucher redemption.<br>

        </ul>
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>3. How We Use Your Data</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          We process your personal data strictly for lawful purposes under Act 843, including:
        <ul class="list-disc pl-5">
            <li class="font-bold">Service Delivery: </li> Creating your account, managing your closed-loop in-app wallet balance, generating secure digital fuel vouchers, and recording pump-side station redemptions.
            <li class="font-bold">Authentication and Security: </li> Sending One-Time Passwords (OTPs) via SMS to verify your identity during account login and at the point of voucher redemption.
            <li class="font-bold">Customer Support: </li> Resolving failed transaction reversals, managing account queries, and addressing app performance complaints.
            <li class="font-bold">Regulatory Compliance: </li> Maintaining accurate digital transaction ledgers for accounting, anti-fraud verifications, and compliance monitoring.
        </ul>
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>4. Legal Basis for Processing</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          Under Ghana’s Data Protection Act, we process your information based on:

            <b>1. Contractual Performance:</b> The data is necessary to execute the services you requested (e.g., fulfilling a voucher purchase).

            <b>2. Consent:</b> Where you have explicitly authorized specific actions, such as enabling location tracking to locate filling stations.

            <b>3. Legal Obligation:</b> To comply with national financial tracking and anti-money laundering reporting requirements.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>5. Sharing and Disclosure of Information</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          We do not sell, rent, or trade your personal data to third-party advertisers. Your information is shared only with trusted entities crucial to our service delivery:

            <b>Payment Processors (Hubtel):</b> To handle secure inbound mobile money and card transaction routing.

            <b>Star Oil Service Stations:</b> Outlets nationwide access voucher system validation records to verify and extinguish active vouchers using OTPs when you purchase fuel.

            <b>Law Enforcement or Regulatory Authorities:</b> If requested formally under prevailing Ghanaian law or by an explicit court order.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>6. Data Security and Storage</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          6.1 We implement highly strict technical and organizational measures to shield your data from unauthorized access, accidental alteration, disclosure, or destruction.<br>
            6.2 All data transmissions between your device browser, our host servers at staroil.services, and Hubtel’s APIs are encrypted using secure end-to-end transport protocols (HTTPS/TLS).<br>
            6.3 Access to your internal user records is strictly restricted to authorized Star Oil technicians and compliance personnel on a need-to-know basis.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>7. Data Retention</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          We retain your personal data and transaction ledgers for as long as your account remains active or as required to fulfill the operational purposes outlined in this policy. If you choose to close your account, we are legally required to retain basic transaction and invoice metadata for a minimum period mandated by Ghanaian tax and financial recording laws before permanent deletion.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>8. Your Legal Data Rights</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          In accordance with Act 843, Ghanaian users hold the following rights regarding their data:
            <ul class="list-disc pl-5">
                <li class="font-bold">Right to Access:</li>You can request a clear copy of the personal data we hold about you.
                <li class="font-bold">Right to Rectification:</li> You can update or correct any inaccurate profile information inside the app at any time.
                <li class="font-bold">Right to Erasure ("Right to be Forgotten"):</li> You can request the total deletion of your account and related profile details, provided there are no active, unextinguished wallet balances or pending legal holds on your ledger.
                <li class="font-bold">Right to Object:</li> You can withdraw your consent for future location tracking or optional notification messages via your device settings.<br>

                To exercise any of these rights, please lodge a formal request via email to our support team.
            </ul>
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>9. Changes to This Privacy Policy</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          We reserve the right to review and update this Privacy Policy to reflect changing system workflows or updates to national fintech and privacy laws. We will notify you of any structural modifications by posting the updated text within the staroil.services web interface and changing the "Last Updated" date at the top of the page.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>10. Contact and Complaints</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          If you believe your data has been handled incorrectly or have queries about this document, please reach out to us at:<br>
          <ul class="list-disc pl-5">
            <li>Email: <a href="mailto:info@staroil.com.gh">info@staroil.com.gh</a></li>
            <li>Phone: +233 55 144 4522 | +233 55 144 4511 </li>
            <li>Headquarters: Off La-Tebu Road (Ghana Water Co. Road), East Cantonments, Accra, Ghana.</li>
          </ul><br>
          You also hold the legal right to lodge complaints directly with the Data Protection Commission (DPC) of Ghana at www.dataprotection.org.gh.
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
