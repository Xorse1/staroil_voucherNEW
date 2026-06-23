<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();

foreach ([
    __DIR__ . '/log.php',
    __DIR__ . '/config.php',
    __DIR__ . '/sms_new_arkesel.php'
] as $optionalFile) {
    if (is_file($optionalFile)) {
        require_once $optionalFile;
    }
}

if (isset($_SESSION['user_id'], $_SESSION['phone_verify']) && (int) $_SESSION['phone_verify'] === 1) {
    header('Location: store');
    exit;
}

$phone = '';

if (isset($_POST['verify'])) {
    $phone = trim((string) ($_POST['phone'] ?? ''));

    if ($phone === '') {
        $_SESSION['otp_sent_error'] = 'Phone number is required.';
        header('Location: forgot_password');
        exit;
    }

    $hex_value = bin2hex(random_bytes(64));
    $url = getenv('VERIFY_PHONE_API_URL') ?: 'https://fms.kayxappstaroil.com/APIs/voucher_api/verify_phone_api.php';
    $postData = [
        'phone' => $phone
    ];

    $ch = curl_init();
    if ($ch === false) {
        $_SESSION['otp_sent_error'] = 'Unable to start verification request.';
        header('Location: forgot_password');
        exit;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);

    if ($response === false) {
        $_SESSION['otp_sent_error'] = 'Unable to verify phone number. Please try again.';
        error_log('Forgot password cURL error: ' . curl_error($ch));
        curl_close($ch);
        header('Location: forgot_password');
        exit;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if (is_array($result) && (int) ($result['status'] ?? 0) === 200) {
        $verify_otp = random_int(222, 99999);
        $_SESSION['verify_phone_otp'] = $verify_otp;
        $_SESSION['otp_sent_success'] = time();

        $phone_verify = $result['data']['phone'] ?? $phone;
        $id = $result['data']['id'] ?? '';
        $message = 'Use this code to verify your phone number: ' . $verify_otp;

        if (function_exists('send_sms_arkesel')) {
            send_sms_arkesel($phone_verify, $message);
        }

        if (function_exists('send_sms_backup')) {
            send_sms_backup($phone_verify, $message);
        }

        header('Location: password_reset?' . $hex_value . '&user=' . urlencode((string) $id));
        exit;
    }

    $_SESSION['otp_sent_error'] = $result['message'] ?? 'Unable to verify phone number.';
    header('Location: forgot_password');
    exit;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password | Star Oil Fuel Voucher System</title>
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script>
  </head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink antialiased">
    <label class="fixed right-4 top-4 z-50 block w-32"><span class="sr-only">Theme</span><select data-theme-select class="w-full rounded-ui border border-brand-line bg-white px-3 py-2 text-sm font-semibold text-brand-ink"><option value="system">System</option><option value="white">White</option><option value="dark">Dark</option></select></label>
    <main class="mx-auto flex min-h-screen max-w-md items-center px-4 py-6">
      <section class="w-full rounded-ui border border-brand-line bg-white p-6 shadow-soft" aria-labelledby="forgot-title">
        <div class="mb-6 flex items-center gap-3">
          <img class="h-10 w-auto" src="images/alogo_light.png" alt="StarOil logo" />
          <div><p class="text-xs font-medium text-brand-muted">Password recovery</p></div>
        </div>

        <h1 id="forgot-title" class="text-2xl font-bold">Forgot Password?</h1>
        <p class="mt-2 text-sm leading-6 text-brand-muted">Enter your registered phone number to verify your account.</p>

        <?php if (!empty($_SESSION['otp_sent_error'])): ?>
          <div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" role="alert">
            <?= htmlspecialchars($_SESSION['otp_sent_error']) ?>
          </div>
          <?php unset($_SESSION['otp_sent_error']); ?>
        <?php endif; ?>

        <form action="forgot_password" method="POST" class="mt-5 space-y-4">
          <label class="block" for="phone">
            <span class="text-sm font-medium text-brand-muted">Phone Number <span class="text-red-600">*</span></span>
            <input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" type="tel" name="phone" id="phone" value="<?= htmlspecialchars($phone) ?>" required />
          </label>

          <button type="submit" name="verify" class="w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1A659F] focus:outline-none focus:ring-2 focus:ring-brand-blue focus:ring-offset-2">Verify</button>
        </form>

        <a class="mt-4 inline-flex text-sm font-semibold text-brand-blue hover:text-[#1A659F]" href="signin">Back to Login</a>
      </section>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div>
    <script src="assets/app.js"></script>
  </body>
</html>
