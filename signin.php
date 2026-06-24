<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();
require_once __DIR__ . '/includes/frontend_log.php';

$hex_value = $_SESSION['signin_token'] ?? bin2hex(random_bytes(8));
$_SESSION['signin_token'] = $hex_value;
$next = (string) ($_GET['next'] ?? ($_SESSION['post_login_redirect'] ?? ''));
if ($next !== '' && !preg_match('/^(cart|store|vouchers|profile|faqs)(\?.*)?$/', $next)) {
    $next = '';
}
if ($next !== '') {
    $_SESSION['post_login_redirect'] = $next;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | Star Oil Fuel Voucher System</title>
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
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink antialiased">
    <label class="fixed right-4 top-4 z-50 block w-32"><span class="sr-only">Theme</span><select data-theme-select class="w-full rounded-ui border border-brand-line bg-white px-3 py-2 text-sm font-semibold text-brand-ink"><option value="system">System</option><option value="white">White</option><option value="dark">Dark</option></select></label>
    <main class="mx-auto grid min-h-screen max-w-6xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[420px_minmax(0,1fr)] lg:items-center lg:px-8">
      <section class="rounded-ui border border-brand-line bg-white p-6 shadow-soft" aria-labelledby="login-title">
        <div class="mb-6 flex items-center gap-3">
          <img class="h-10 w-auto" src="images/alogo_light.png" alt="StarOil logo" />
          <div><p class="text-xs font-medium text-brand-muted">User Login</p></div>
        </div>
        <div class="mb-5 grid grid-cols-2 rounded-ui border border-brand-line bg-brand-soft p-1">
          <span class="rounded-ui bg-brand-blue px-3 py-2 text-center text-sm font-semibold text-white">Login</span>
          <a class="rounded-ui px-3 py-2 text-center text-sm font-semibold text-brand-muted" href="user_registration">Signup</a>
        </div>
        <h1 id="login-title" class="text-2xl font-bold">Login</h1>
        <p class="mt-2 text-sm leading-6 text-brand-muted">Sign in with your registered phone number to purchase fuel vouchers.</p>

        <?php if (!empty($_SESSION['user_not_found'])): ?>
          <div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" role="alert">
            Phone number or password is incorrect.
          </div>
          <?php unset($_SESSION['user_not_found']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['signin_error'])): ?>
          <div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" role="alert">
            <?= htmlspecialchars($_SESSION['signin_error']) ?>
          </div>
          <?php unset($_SESSION['signin_error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['successverified'])): ?>
          <div class="mt-4 rounded-ui border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800" role="status">
            Phone number verified successfully. Please sign in.
          </div>
          <?php unset($_SESSION['successverified']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['passupdsuccess'])): ?>
          <div class="mt-4 rounded-ui border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800" role="status">
            <?= htmlspecialchars($_SESSION['passupdsuccess']) ?>
          </div>
          <?php unset($_SESSION['passupdsuccess']); ?>
        <?php endif; ?>

        <form action="signin_process?<?= htmlspecialchars($hex_value); ?>" method="POST" class="mt-5 space-y-4">
          <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>" />
          <label class="block" for="phone">
            <span class="text-sm font-medium text-brand-muted">Phone Number <span class="text-red-600">*</span></span>
            <input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" type="tel" value="<?= isset($_SESSION['phone']) ? htmlspecialchars($_SESSION['phone']) : '' ?>" name="phone" id="phone" required />
          </label>
          <label class="block" for="password">
            <span class="text-sm font-medium text-brand-muted">Password <span class="text-red-600">*</span></span>
            <span class="relative mt-1 block">
              <input class="w-full rounded-ui border border-brand-line px-3 py-2.5 pr-16 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" type="password" name="password" id="password" data-password-field required />
              <button class="absolute inset-y-1 right-1 rounded-ui px-3 text-xs font-semibold text-brand-blue hover:bg-brand-soft focus:outline-none focus:ring-2 focus:ring-brand-blue" type="button" data-toggle-password aria-controls="password" aria-pressed="false">Show</button>
            </span>
          </label>
          <button type="submit" name="login" class="w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1A659F] focus:outline-none focus:ring-2 focus:ring-brand-blue focus:ring-offset-2">Login</button>
        </form>

        <div class="mt-4 mb-2 flex flex-col gap-2 text-sm font-medium text-brand-muted sm:flex-row sm:items-center sm:justify-between">
          <span>Not registered? <a class="font-semibold text-brand-blue hover:text-[#1A659F]" href="user_registration">Signup</a></span>
          <a class="font-semibold text-brand-blue hover:text-[#1A659F]" href="forgot_password">Forgot Password?</a>
        </div>
        
        <small class="mt-4 text-xs text-brand-muted font-semibold">
          By signing in, you agree to our 
          <a href="terms-of-use" class="text-brand-blue hover:underline" target="_blank">Terms of Service</a>
           and 
          <a href="privacy" class="text-brand-blue hover:underline" target="_blank">Privacy Policy</a>.
        </small>
      </section>
      <aside class="rounded-ui border border-brand-line bg-white p-6 shadow-soft">
        <p class="text-sm font-semibold text-brand-blue">Secure voucher access</p>
        <h2 class="mt-2 text-3xl font-bold">Fuel vouchers with layered verification</h2>
        <div class="mt-6 grid gap-3">
          <div class="rounded-ui border border-brand-line p-4"><p class="text-sm font-semibold">Verified customer identity</p><p class="mt-1 text-sm leading-6 text-brand-muted">Customers sign in before purchasing, activating, or managing fuel vouchers.</p></div>
          <div class="rounded-ui border border-brand-line p-4"><p class="text-sm font-semibold">Mobile approval channel</p><p class="mt-1 text-sm leading-6 text-brand-muted">Phone verification supports activation approvals and transaction notices.</p></div>
          <div class="rounded-ui border border-brand-line p-4"><p class="text-sm font-semibold">OTP and authenticator support</p><p class="mt-1 text-sm leading-6 text-brand-muted">Users can verify with one-time codes or an authenticator app.</p></div>
        </div>
      </aside>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div>
    <script src="assets/app.js"></script>
  </body>
</html>
