<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();

$type = $_GET['type'] ?? 'Mobile-OTP';
$type = $type === 'Authenticator' ? 'Authenticator' : 'Mobile-OTP';
$authenticatorImage = '../images/authenticatornew.png';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MFA | Star Oil Fuel Voucher System</title>
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script>
  </head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
    <label class="fixed right-4 top-4 z-50 block w-32"><span class="sr-only">Theme</span><select data-theme-select class="w-full rounded-ui border border-brand-line bg-white px-3 py-2 text-sm font-semibold text-brand-ink"><option value="system">System</option><option value="white">White</option><option value="dark">Dark</option></select></label>
    <main class="mx-auto flex min-h-screen max-w-md items-center px-4 py-6">
      <section class="w-full rounded-ui border border-brand-line bg-white p-6 shadow-soft" aria-labelledby="mfa-title">
        <div class="mb-6 flex items-center gap-3">
          <img class="h-10 w-auto" src="images/alogo_light.png" alt="StarOil logo" />
          <div><p class="text-xs font-medium text-brand-muted">Multi-factor authentication</p></div>
        </div>

        <h1 id="mfa-title" class="text-2xl font-bold">Enter <?= htmlspecialchars($type); ?> code</h1>
        <p class="mt-2 text-sm leading-6 text-brand-muted">Complete this verification step to continue to the voucher store.</p>

        <?php if (!empty($_SESSION['mfa_failed'])): ?>
          <div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" role="alert">
            Invalid verification code. Please try again.
          </div>
          <?php unset($_SESSION['mfa_failed']); ?>
        <?php endif; ?>

        <form action="mfa_proccess" method="POST" class="mt-5 space-y-4">
          <label class="block" for="code">
            <span class="text-sm font-medium text-brand-muted">Enter code <span class="text-red-600">*</span></span>
            <input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-3 text-center text-2xl font-bold tracking-[0.35em] focus:outline-none focus:ring-2 focus:ring-brand-blue" type="text" name="codeInput" id="code" inputmode="numeric" required />
          </label>
          <input type="hidden" name="type" value="<?= htmlspecialchars($type); ?>" />
          <button type="submit" name="verify" class="w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1A659F] focus:outline-none focus:ring-2 focus:ring-brand-blue focus:ring-offset-2">Verify</button>

          <div class="pt-1 text-sm font-semibold">
            <?php if (isset($_SESSION['auth_status']) && (string) $_SESSION['auth_status'] === '0'): ?>
              <a class="inline-flex items-center gap-2 text-brand-blue hover:text-[#1A659F]" href="otp_googleauth">
                <img class="h-5 w-5" src="<?= htmlspecialchars($authenticatorImage); ?>" alt="" />
                Setup Google Authenticator
              </a>
            <?php elseif (isset($_SESSION['auth_status']) && (string) $_SESSION['auth_status'] === '1' && $type === 'Mobile-OTP'): ?>
              <a class="inline-flex items-center gap-2 text-brand-blue hover:text-[#1A659F]" href="mfa?type=Authenticator">
                <img class="h-5 w-5" src="<?= htmlspecialchars($authenticatorImage); ?>" alt="" />
                Use Google Authenticator
              </a>
            <?php elseif ($type === 'Authenticator'): ?>
              <a class="inline-flex items-center gap-2 text-brand-blue hover:text-[#1A659F]" href="mfa?type=Mobile-OTP">
                Use Mobile OTP
              </a>
            <?php endif; ?>
          </div>
        </form>
      </section>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div>
    <script src="assets/app.js"></script>
  </body>
</html>
