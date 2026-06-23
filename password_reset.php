<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();

foreach ([
    __DIR__ . '/includes/function_sanitize_pass.php',
    __DIR__ . '/log.php'
] as $optionalFile) {
    if (is_file($optionalFile)) {
        require_once $optionalFile;
    }
}

if (isset($_SESSION['user_id'], $_SESSION['phone_verify']) && (int) $_SESSION['phone_verify'] === 1) {
    header('Location: store');
    exit;
}

$id = $_GET['user'] ?? ($_POST['user'] ?? '');
$hex_value = bin2hex(random_bytes(64));

function reset_redirect($hexValue, $userId) {
    header('Location: password_reset?' . $hexValue . '&user=' . urlencode((string) $userId));
    exit;
}

function reset_password_is_valid($password) {
    if (function_exists('sanitizePassword')) {
        return sanitizePassword($password) === $password;
    }

    return strlen($password) >= 8
        && strlen($password) <= 20
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        && preg_match('/[\W_]/', $password);
}

if (isset($_POST['reset'])) {
    $user_id = $_POST['user'] ?? '';
    $password = trim((string) ($_POST['password'] ?? ''));
    $cpassword = trim((string) ($_POST['cpassword'] ?? ''));
    $verify_phone_otp = trim((string) ($_POST['verify_phone_otp'] ?? ''));
    $sessionOtp = isset($_SESSION['verify_phone_otp']) ? (string) $_SESSION['verify_phone_otp'] : '';

    if ($verify_phone_otp === '' || $sessionOtp === '' || !hash_equals($sessionOtp, $verify_phone_otp)) {
        $_SESSION['otp_failed'] = time();
        reset_redirect($hex_value, $user_id ?: $id);
    }

    if ($password !== $cpassword) {
        $_SESSION['password_failed'] = time();
        reset_redirect($hex_value, $user_id ?: $id);
    }

    if (!reset_password_is_valid($password)) {
        $_SESSION['sanipassincorrect'] = time();
        reset_redirect($hex_value, $user_id ?: $id);
    }

    $url = getenv('UPDATE_USER_PASS_API_URL') ?: 'https://fms.kayxappstaroil.com/APIs/voucher_api/update_user_pass.php';
    $data = [
        'user' => $user_id,
        'password' => $password
    ];

    $ch = curl_init($url);
    if ($ch === false) {
        $_SESSION['passupderror'] = 'Unable to start password update request.';
        reset_redirect($hex_value, $user_id ?: $id);
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);

    if ($response === false) {
        $_SESSION['passupderror'] = 'Unable to update password. Please try again.';
        error_log('Password reset cURL error: ' . curl_error($ch));
        curl_close($ch);
        reset_redirect($hex_value, $user_id ?: $id);
    }

    curl_close($ch);
    $result = json_decode($response, true);

    if (is_array($result) && ($result['status'] ?? '') === 'success') {
        unset($_SESSION['verify_phone_otp']);
        $_SESSION['passupdsuccess'] = $result['message'] ?? 'Password updated successfully.';
        header('Location: signin');
        exit;
    }

    $_SESSION['passupderror'] = is_array($result) ? ($result['message'] ?? 'Unable to update password.') : 'Unable to update password.';
    reset_redirect($hex_value, $user_id ?: $id);
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Password Reset | Star Oil Fuel Voucher System</title>
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script>
  </head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink antialiased">
    <label class="fixed right-4 top-4 z-50 block w-32"><span class="sr-only">Theme</span><select data-theme-select class="w-full rounded-ui border border-brand-line bg-white px-3 py-2 text-sm font-semibold text-brand-ink"><option value="system">System</option><option value="white">White</option><option value="dark">Dark</option></select></label>
    <main class="mx-auto flex min-h-screen max-w-md items-center px-4 py-6">
      <section class="w-full rounded-ui border border-brand-line bg-white p-6 shadow-soft" aria-labelledby="reset-title">
        <div class="mb-6 flex items-center gap-3">
          <img class="h-10 w-auto" src="images/alogo_light.png" alt="StarOil logo" />
          <div><p class="text-xs font-medium text-brand-muted">Password reset</p></div>
        </div>

        <h1 id="reset-title" class="text-2xl font-bold">Reset Password</h1>

        <?php if (!empty($_SESSION['otp_sent_success'])): ?>
          <div class="mt-4 rounded-ui border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800" role="status">
            A verification code has been sent to your phone.
          </div>
          <?php unset($_SESSION['otp_sent_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['otp_failed'])): ?>
          <div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" role="alert">Invalid OTP.</div>
          <?php unset($_SESSION['otp_failed']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['password_failed'])): ?>
          <div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" role="alert">Passwords do not match.</div>
          <?php unset($_SESSION['password_failed']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['sanipassincorrect'])): ?>
          <div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" role="alert">Password does not meet the required format.</div>
          <?php unset($_SESSION['sanipassincorrect']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['passupderror'])): ?>
          <div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" role="alert">
            <?= htmlspecialchars($_SESSION['passupderror']) ?>
          </div>
          <?php unset($_SESSION['passupderror']); ?>
        <?php endif; ?>

        <form action="password_reset?<?= htmlspecialchars($hex_value . '&user=' . $id) ?>" method="POST" class="mt-5 space-y-4">
          <label class="block" for="verify_phone_otp">
            <span class="text-sm font-medium text-brand-muted">Authenticate OTP <span class="text-red-600">*</span></span>
            <input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" type="text" name="verify_phone_otp" id="verify_phone_otp" required />
          </label>

          <label class="block" for="password">
            <span class="text-sm font-medium text-brand-muted">New Password <span class="text-red-600">*</span></span>
            <input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" type="password" name="password" id="password" required />
          </label>

          <label class="block" for="cpassword">
            <span class="text-sm font-medium text-brand-muted">Confirm Password <span class="text-red-600">*</span></span>
            <input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" type="password" name="cpassword" id="cpassword" required />
          </label>

          <div class="space-y-1 rounded-ui border border-red-100 bg-red-50 p-3 text-xs font-semibold leading-5 text-red-800">
            <p>The password is required to be between 8 and 20 characters.</p>
            <p>At least one uppercase letter ([A-Z]).</p>
            <p>At least one lowercase letter ([a-z]).</p>
            <p>At least one digit ([0-9]).</p>
            <p>At least one special character (non-alphanumeric, [\W_]).</p>
          </div>

          <input type="hidden" name="user" value="<?= htmlspecialchars((string) $id) ?>" />
          <button type="submit" name="reset" class="w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1A659F] focus:outline-none focus:ring-2 focus:ring-brand-blue focus:ring-offset-2">Reset Password</button>
        </form>
      </section>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div>
    <script src="assets/app.js"></script>
  </body>
</html>
