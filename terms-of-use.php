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
            <h1 class="text-2xl font-bold sm:text-3xl">Star Oil Voucher System - Terms and Conditions</h1>
            <small class="text-sm font-semibold text-brand-muted">Last updated: June 2026</small>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-muted">Please read these terms of use carefully before using the StarOil e-voucher service.</p>
          </div>
          <a class="inline-flex items-center justify-center rounded-ui border border-brand-line bg-white px-4 py-2 text-sm font-semibold text-brand-ink hover:border-brand-blue hover:text-brand-blue focus:outline-none focus:ring-2 focus:ring-brand-blue" href="store">Back to store</a>
        </div>
      </section>

      <section class="space-y-3" aria-label="Terms of use">
        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft" open>
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>Welcome to the Star Oil Voucher System (the "Application"). </span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br> 
          These Terms and Conditions ("Terms") form a legally binding agreement between you ("User", "you", or "your") and Star Oil Voucher System ("Company", "we", "us", or "our") governing your access to and use of the Application, including the purchase of digital fuel vouchers and the utilization of our in-app wallet feature.<br><br>

          By registering an account, topping up your in-app wallet, or purchasing a voucher, you explicitly agree to be bound by these Terms. If you do not agree to these Terms, do not use the Application.
        </details>

        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>1. Nature of the Service and Regulatory Compliance</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          1.1 Closed-Loop Ecosystem: The Application provides a proprietary, closed-loop digital ecosystem. The virtual "Wallet" contained within the Application is not a bank account, an electronic money deployment service, a mobile money wallet, or a public remittance tool.<br>
          1.2 Exclusivity of Use: Stored value balances inside the Application Wallet represent strictly non-refundable, non-transferable advanced-payment store credits. This balance can only be used to purchase proprietary fuel and lubricant vouchers issued by Star Oil within the Application.<br>
          1.3 Licensing Exemption: In accordance with the Payment Systems and Services Act, 2019 (Act 987) of Ghana, this service operates as a Closed-Loop Stored Value Facility and does not offer third-party transfers, public billing services, or cash withdrawals. Consequently, it does not function as an independent Payment Service Provider (PSP).
        </details>

        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>2. Account Registration and Security</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          2.1 Eligibility: You must be at least 18 years of age and possess a valid mobile money account or banking asset in Ghana to register an account.<br>
          2.2 Account Security: You are entirely responsible for maintaining the confidentiality of your login credentials, including Personal Identification Numbers (PINs) or One-Time Passwords (OTPs) generated during access or voucher activation.<br>
          2.3 Liability: Any transaction initiated through your authenticated account is legally deemed to have been authorized by you. The Company will not be liable for any losses resulting from unauthorized account access due to user negligence.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>3. Payment Processing and In-App Wallet Top-Ups</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          <b>3.1 Payment Intermediary:</b> All inbound payments, including direct voucher purchases and in-app wallet top-ups, are processed securely through our licensed third-party payment gateway partners, Hubtel and Cellulant (Tingg).<br>
          <b>3.2 Irreversibility of Voluntary Top-Ups:</b> When you voluntarily choose to top up your in-app wallet using Hubtel and Tingg, your fiat currency is instantly converted into non-withdrawable, non-transferable in-app credits dedicated exclusively to buying Star Oil products. You cannot cash out, withdraw, or transfer wallet balances back to your Mobile Money or Bank Account.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>4. Voucher Purchase, Activation, and Redemption</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          <b>4.1 Voucher Issuance:</b> Vouchers purchased via the Application are unique digital tokens containing distinct validation data (e.g., QR codes, alphanumeric strings, or OTP requirements).<br>
          <b>4.2 Activation Requirement:</b> For security purposes, all purchased vouchers must be explicitly activated within the portal/app interface before they can be presented for redemption at an authorized Star Oil filling station.<br>
          <b>4.3 Redemption Verification:</b> At the point of redemption, the redeeming station personnel will collect the redeemer’s phone number, and a One-Time Password (OTP) will be sent to confirm and extinguish the voucher balance. The Company is not responsible if a user shares their voucher imagery or code with an unauthorized third party who subsequently redeems it.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>5. Refunds, Failed Transactions, and Reversals</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          <b>5.1 Failed Direct Purchases:</b> If you attempt to purchase a voucher directly through the Hubtel or Tingg gateway (bypassing the wallet balance) and your payment is successfully processed but the system fails to deliver the voucher, the Company will issue a transaction reversal. This reversal will be processed back to the original funding source (your Mobile Money or Bank Account) via Hubtel or Tingg. It will not be forcibly retained in your app wallet.<br>
          <b>5.2 Wallet Purchases:</b> If a voucher purchase made using an existing wallet balance fails to deliver, the underlying credits will be reversed immediately back to your in-app wallet balance. No cash or mobile money refunds will be offered for transactions funded by wallet credit.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>6. Prohibited Activities</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          <b>6.1 Prohibited Activities:</b> You agree not to engage in any of the following activities:<br>
          <ul class="list-disc pl-5">
            <li>Attempt to reverse-engineer, exploit, or extract cash from the wallet system.</li>
            <li>Use the Application or voucher system to facilitate unauthorized currency exchange, debt collection, or money laundering.</li>
            <li>Create multiple accounts using fraudulent or unverified phone numbers or identities.</li>
            <li>Use any automated script, bot, or scraper to mass-purchase or scrape vouchers from our servers.</li>
          </ul>
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>7. Limitation of Liability and Service Interruptions</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          <b>7.1 As-Is Basis:</b> The Application is provided on an "as-is" and "as-available" basis. While we strive for uninterrupted operations, the Company does not guarantee that the voucher platform or payment gateway integrations will always be free from delays, network downtime, or system timeouts.<br>
          <b>7.2 Indirect Losses:</b> To the maximum extent permitted under Ghanaian law, Star Oil and its technical partners shall not be held liable for any indirect, incidental, or consequential losses, including but not limited to fuel price fluctuations occurring between the time of voucher purchase and the time of physical station redemption.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>8. Amendments and Termination</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          <b>8.1 Right to Amend:</b> The Company reserves the right to amend these Terms at any time. Changes take effect immediately upon being posted within the Application. Continued usage of the app following changes constitutes acceptance of the updated terms.<br>
          <b>8.2 Suspension of Service:</b> The Company reserves the absolute right to suspend or terminate any user account without prior notice if fraudulent activity, compliance warnings from Hubtel or Cellulant (Tingg), or violations of these Terms are detected. Any legitimate, untainted wallet credits held at termination will be reviewed on a case-by-case basis.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>9. Governing Law and Dispute Resolution</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          These Terms shall be governed by, interpreted, and enforced in accordance with the laws of the Republic of Ghana. Any dispute arising out of or in connection with these Terms shall first be subjected to amicable, mutual settlement discussions. If a resolution cannot be reached within thirty (30) days, the matter shall be referred to the exclusive jurisdiction of the competent courts in Ghana.
        </details>


        <details class="group rounded-ui border border-brand-line bg-white p-4 shadow-soft">
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-bold text-brand-ink focus:outline-none focus:ring-2 focus:ring-brand-blue">
            <span>10. Contact Information</span>
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-ui bg-brand-soft text-brand-blue transition group-open:rotate-45" aria-hidden="true">+</span>
          </summary><br>
          For assistance, billing inquiries, or transaction disputes, please contact our support team via the following channels:<br>
          <ul class="list-disc pl-5">
            <li>Email: <a href="mailto:info@staroil.com.gh">info@staroil.com.gh</a></li>
            <li>Phone: +233 55 144 4522 | +233 55 144 4511 </li>
          </ul>
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
