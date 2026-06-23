<?php
require_once __DIR__ . '/includes/auth_guard.php';
define('SECURE_ACCESS', true);
require_once __DIR__ . '/config/config.php';

$userId = $_SESSION['user_id'];
$setup = [
    'status' => 'error',
    'message' => 'Unable to start Google Authenticator setup.'
];

$curl = curl_init('https://fms.kayxappstaroil.com/APIs/voucher_api/update_googleauth_status.php?user_id=' . urlencode((string) $userId));
if ($curl !== false) {
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'X-API-KEY: ' . UPDATE_BENE_PROFILE
        ],
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        $setup['message'] = 'Google Authenticator setup failed: ' . $error;
    } else {
        $decoded = json_decode((string) $response, true);
        if (is_array($decoded)) {
            $setup = $decoded;
        }
    }
}

if (($setup['status'] ?? '') !== 'success') {
    $_SESSION['errorgoogleauth'] = $setup['message'] ?? 'Unable to start Google Authenticator setup.';
    header('Location: profile');
    exit;
}

$secret = $setup['data']['secret'] ?? '';
$qrCodeUrl = $setup['data']['qrcodeurl'] ?? '';
?>
<!doctype html>
<html lang="en">
  <head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><title>Google Authenticator | Star Oil Fuel Voucher System</title><link rel="preconnect" href="https://fonts.bunny.net" /><link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" /><script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script></head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
    <main class="mx-auto flex min-h-screen max-w-xl items-center px-4 py-8">
      <section class="w-full rounded-ui border border-brand-line bg-white p-6 shadow-soft">
        <a class="mb-5 inline-flex text-sm font-semibold text-brand-blue" href="profile">Back to Profile</a>
        <h1 class="text-2xl font-bold">Google Authenticator Setup</h1>
        <p class="mt-2 text-sm leading-6 text-brand-muted">Scan the QR code with Google Authenticator, or enter the secret key manually.</p>
        <div class="mt-5 grid gap-5 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center">
          <div class="rounded-ui border border-brand-line bg-white p-4 text-center shadow-sm">
            <?php if ($qrCodeUrl !== ''): ?><img class="mx-auto h-36 w-36" src="<?= htmlspecialchars($qrCodeUrl) ?>" alt="Google Authenticator QR code" /><?php else: ?><div class="grid h-36 w-36 place-items-center text-sm text-brand-muted">QR unavailable</div><?php endif; ?>
          </div>
          <div class="rounded-ui border border-brand-line bg-brand-soft p-4">
            <p class="text-sm font-semibold">Secret Key</p>
            <p class="mt-2 break-all rounded-ui bg-white px-3 py-2 font-mono text-sm ring-1 ring-brand-line"><?= htmlspecialchars($secret) ?></p>
            <p class="mt-3 text-xs font-semibold text-red-700">Clear any previous setup from your phone before saving this new setup.</p>
          </div>
        </div>
        <form method="post" action="verify_googleauth" class="mt-5 grid gap-4">
          <label><span class="text-sm font-medium text-brand-muted">Verify code from authenticator app</span><input type="text" class="mt-1 w-full rounded-ui border border-brand-line px-3 py-3 text-center text-2xl font-bold tracking-[0.35em] focus:outline-none focus:ring-2 focus:ring-brand-blue" id="codeInput" name="codeInput" placeholder="000000" inputmode="numeric" required /></label>
          <button type="submit" class="rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white" name="submit">Save</button>
        </form>
      </section>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div><script src="assets/app.js"></script>
  </body>
</html>
