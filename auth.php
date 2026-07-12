<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();

foreach ([
    __DIR__ . '/log.php',
    __DIR__ . '/config.php',
    __DIR__ . '/includes/function_send_sms.php'
] as $optionalFile) {
    if (is_file($optionalFile)) {
        require_once $optionalFile;
    }
}

function check_verify() {
    /*if (empty($_SESSION['phone']) || empty($_SESSION['email'])) {
        return false;
    }*/

      if (empty($_SESSION['phone'])) {
        return false;
    }

    $phone = urlencode($_SESSION['phone']);
    //$email = urlencode($_SESSION['email']);
    $url = "https://fms.kayxappstaroil.com/APIs/voucher_api/update_bene_phone.php?phone={$phone}";

    $ch = curl_init();
    if ($ch === false) {
        error_log('Phone verification failed: cURL is unavailable.');
        return false;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);

    if ($response === false) {
        error_log('Phone verification cURL error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    $responseData = json_decode($response, true);
    $_SESSION['phone_verify'] = 1;
    unset($_SESSION['mfa_pending']);
    $_SESSION['phone_verified_response'] = is_array($responseData) ? $responseData : [];

    return true;
}

$verifyMessage = '';
$verifyType = '';

if (isset($_POST['verify_otp'])) {
    $submittedOtp = trim((string) ($_POST['otp'] ?? ''));
    $sessionOtp = isset($_SESSION['otp']) ? (string) $_SESSION['otp'] : '';

    if ($submittedOtp === '' || $sessionOtp === '') {
        $verifyType = 'error';
        $verifyMessage = 'OTP is missing or expired. Please resend the code.';
    } elseif (hash_equals($sessionOtp, $submittedOtp)) {
        if (check_verify()) {
            unset($_SESSION['otp']);
            $_SESSION['successverified'] = time();
            $verifyType = 'success';
            $verifyMessage = 'Phone number verified successfully. You can now sign in.';
            $next = trim((string) ($_SESSION['post_login_redirect'] ?? ''));
            if ($next !== '' && preg_match('/^(cart|store|vouchers|profile|faqs)(\?.*)?$/', $next)) {
                staroil_redirect('signin?next=' . urlencode($next));
            } else {
                staroil_redirect('signin');
            }
            exit;
        } else {
            $verifyType = 'error';
            $verifyMessage = 'OTP matched, but phone verification could not be completed. Please try again.';
        }
    } else {
        $verifyType = 'error';
        $verifyMessage = 'Invalid OTP. Please check the code and try again.';
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify Phone | Star Oil Fuel Voucher System</title>
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script>
  </head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
    <label class="fixed right-4 top-4 z-50 block w-32"><span class="sr-only">Theme</span><select data-theme-select class="w-full rounded-ui border border-brand-line bg-white px-3 py-2 text-sm font-semibold text-brand-ink"><option value="system">System</option><option value="white">White</option><option value="dark">Dark</option></select></label>
    <main class="mx-auto flex min-h-screen max-w-md items-center px-4 py-6">
      <section class="w-full rounded-ui border border-brand-line bg-white p-6 shadow-soft" aria-labelledby="auth-title">
        <div class="mb-6 flex items-center gap-3">
          <img class="h-10 w-auto" src="images/alogo_light.png" alt="StarOil logo" />
          <div><p class="text-xs font-medium text-brand-muted">Phone verification</p></div>
        </div>

        <h1 id="auth-title" class="text-2xl font-bold">Verify Phone</h1>

        <?php if (!empty($_SESSION['successadded'])): ?>
          <div class="mt-4 rounded-ui border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800" role="status">
            Signup was successful. Enter the OTP sent to your phone to complete verification.
          </div>
          <?php unset($_SESSION['successadded']); ?>
        <?php endif; ?>

        <?php if ($verifyMessage !== ''): ?>
          <div class="mt-4 rounded-ui border px-3 py-2 text-sm font-semibold <?= $verifyType === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' ?>" role="alert">
            <?= htmlspecialchars($verifyMessage) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['otp_resent'])): ?>
          <div class="mt-4 rounded-ui border border-sky-200 bg-sky-50 px-3 py-2 text-sm font-semibold text-sky-900" role="status">
            OTP has been resent to your phone.
          </div>
          <?php unset($_SESSION['otp_resent']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['otp_error'])): ?>
          <div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" role="alert">
            <?= htmlspecialchars($_SESSION['otp_error']) ?>
          </div>
          <?php unset($_SESSION['otp_error']); ?>
        <?php endif; ?>

        <form action="auth" method="POST" class="mt-5 space-y-4">
          <label class="block" for="otp">
            <span class="text-sm font-medium text-brand-muted">Enter OTP</span>
            <input type="text" class="mt-1 w-full rounded-ui border border-brand-line px-3 py-3 text-center text-2xl font-bold tracking-[0.35em] focus:outline-none focus:ring-2 focus:ring-brand-blue" name="otp" id="otp" inputmode="numeric" maxlength="6" required />
          </label>
          <button type="submit" name="verify_otp" class="w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1A659F] focus:outline-none focus:ring-2 focus:ring-brand-blue focus:ring-offset-2">Verify Phone Number</button>
        </form>

        <div class="mt-4 text-sm font-medium text-brand-muted">
          <?php if (isset($_GET['signinphone'])): ?>
            <span>Click here to send code</span>
            <a id="resendLink" class="font-semibold text-brand-blue hover:text-[#1A659F]" href="resend_otp?phonesend=<?= htmlspecialchars($_GET['signinphone']); ?>" onclick="return checkTimer()">Send OTP</a>
          <?php else: ?>
            <span>Not receiving OTP?</span>
            <a id="resendLink" class="font-semibold text-brand-blue hover:text-[#1A659F]" href="resend_otp" onclick="return checkTimer()">Resend OTP</a>
          <?php endif; ?>
          <span id="timer" class="ml-2 text-red-700"></span>
        </div>

        <?php if ($verifyType === 'success'): ?>
          <a class="mt-5 flex w-full justify-center rounded-ui border border-brand-line px-4 py-2.5 text-sm font-semibold text-brand-ink hover:border-brand-blue" href="signin">Go to Sign In</a>
        <?php endif; ?>
      </section>
    </main>

    <script>
      function checkTimer() {
        const savedUntil = Number(localStorage.getItem("staroilOtpWaitUntil") || 0);
        const now = Date.now();
        if (savedUntil > now) {
          return false;
        }
        localStorage.setItem("staroilOtpWaitUntil", String(now + 60000));
        return true;
      }

      function updateTimer() {
        const timer = document.getElementById("timer");
        const link = document.getElementById("resendLink");
        if (!timer || !link) return;
        const remaining = Math.ceil((Number(localStorage.getItem("staroilOtpWaitUntil") || 0) - Date.now()) / 1000);
        if (remaining > 0) {
          timer.textContent = `${remaining}s`;
          link.classList.add("pointer-events-none", "opacity-50");
        } else {
          timer.textContent = "";
          link.classList.remove("pointer-events-none", "opacity-50");
        }
      }

      updateTimer();
      setInterval(updateTimer, 1000);
    </script>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div>
    <script src="assets/app.js"></script>
  </body>
</html>
