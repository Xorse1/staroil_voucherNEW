<?php
require_once __DIR__ . '/includes/auth_guard.php';
define('SECURE_ACCESS', true);
require_once __DIR__ . '/config/config.php';

$beneficiaryId = $_SESSION['user_id'];
$profileLoaded = false;
$profileResponse = [
    'success' => false,
    'message' => 'Profile could not be loaded.', 
    'data' => []
];

$apiUrl = 'https://fms.kayxappstaroil.com/APIs/voucher_api/fetch_bene_profile.php?beneficiary_id=' . urlencode((string) $beneficiaryId);
$ch = curl_init($apiUrl);

if ($ch !== false) {
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-API-KEY: ' . FETCH_BENE_PROFILE,
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $profileResponse = $decoded;
        }
    } elseif ($response === false) {
        $profileResponse['message'] = 'cURL Error: ' . curl_error($ch);
    } else {
        $profileResponse['message'] = 'HTTP Error: ' . $httpCode;
    }

    curl_close($ch);
}

$successValue = $profileResponse['success'] ?? $profileResponse['status'] ?? false;
$profileLoaded = $successValue === true || $successValue === 'true' || $successValue === 'success' || $successValue === 200 || $successValue === '200';
$profileData = $profileResponse['data'] ?? [];
if (is_array($profileData) && isset($profileData[0]) && is_array($profileData[0])) {
    $profileData = $profileData[0];
}

$profile = is_array($profileData) ? $profileData : [];
$hasSessionFallback = !empty($_SESSION['name']) || !empty($_SESSION['email']) || !empty($_SESSION['phone']);
if (!$profileLoaded && $hasSessionFallback) {
    $profileResponse['message'] = $profileResponse['message'] ?? 'Live profile could not be loaded. Showing session details.';
}
$joined = 'N/A';
if (!empty($profile['date'])) {
    try {
        $joined = (new DateTime($profile['date']))->format('l, jS \of F, Y');
    } catch (Throwable $error) {
        $joined = $profile['date'];
    }
}

$name = $profile['name'] ?? ($_SESSION['name'] ?? '');
$email = $profile['email'] ?? ($_SESSION['email'] ?? '');
$phone = $profile['phone'] ?? ($_SESSION['phone'] ?? '');
$phoneVerify = (int) ($profile['phone_verify'] ?? ($_SESSION['phone_verify'] ?? 0));
$accountStatus = (int) ($profile['status'] ?? 0);
?>
<!doctype html>
<html lang="en">
  <head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><title>Profile | Star Oil Fuel Voucher System</title><link rel="preconnect" href="https://fonts.bunny.net" /><link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" /><script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script></head>
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
        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" /></svg>
      </button>
    </div>
    <div id="primary-menu" data-menu class="hidden mt-3 flex-col gap-2 rounded-ui border border-brand-line bg-white p-2 shadow-soft lg:mt-0 lg:flex lg:flex-row lg:items-center lg:justify-end lg:gap-2 lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
      <a data-nav class="block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="store">Store</a>
      <a data-nav data-auth-only class="hidden block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="vouchers">My Vouchers</a>
      <a data-nav class="block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="faqs">FAQs</a>
      <a data-nav data-auth-only class="hidden block w-full rounded-ui px-3 py-2 text-sm font-medium lg:w-auto" href="profile">Profile</a>
      <a data-nav data-auth-only class="hidden block w-full rounded-ui border border-brand-line px-3 py-2 text-sm font-semibold lg:w-auto" href="cart">Cart <span data-cart-count class="ml-1 rounded-full bg-brand-yellow px-2 py-0.5 text-xs text-brand-ink">0</span></a>
      <label class="block w-full lg:w-auto"><span class="sr-only">Theme</span><select data-theme-select class="w-full rounded-ui border border-brand-line bg-white px-3 py-2 text-sm font-semibold text-brand-ink lg:w-auto"><option value="system">System</option><option value="white">White</option><option value="dark">Dark</option></select></label><span data-auth-only data-user-welcome class="hidden block w-full rounded-ui bg-brand-soft px-3 py-2 text-sm font-semibold text-brand-ink lg:w-auto lg:max-w-[220px] lg:truncate"></span>
      <a data-auth-only class="hidden block w-full rounded-ui px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 lg:w-auto" href="logout">Logout</a>
    </div>
  </nav>
</header>
    <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
      <div class="mb-5"><p class="text-sm font-semibold text-brand-blue">Account</p><h1 class="mt-1 text-2xl font-bold sm:text-3xl">My Account</h1><p class="mt-2 text-sm leading-6 text-brand-muted">Manage profile information, account status, password, and multi-factor authentication.</p></div>

      <?php foreach ([
          'successprofile' => ['success', 'Success'],
          'successprofilepassword' => ['success', 'Success'],
          'errorprofilepassword' => ['error', 'Error'],
          'successotpactivate' => ['success', 'Success'],
          'errorotpactivate' => ['error', 'Error'],
          'errorgoogleauth' => ['error', 'Error'],
          'successgoogleauth' => ['success', 'Success']
      ] as $key => [$type, $title]): ?>
        <?php if (!empty($_SESSION[$key])): ?>
          <div class="mb-4 rounded-ui border px-4 py-3 text-sm font-semibold <?= $type === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' ?>" role="alert"><?= htmlspecialchars($_SESSION[$key]) ?></div>
          <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if (!$profileLoaded): ?>
        <div class="mb-5 rounded-ui border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800" role="alert"><?= htmlspecialchars($profileResponse['message'] ?? 'Live profile could not be loaded. Showing session details.') ?></div>
      <?php endif; ?>
      <?php if ($profileLoaded || $hasSessionFallback): ?>
      <section class="rounded-ui border border-brand-line bg-white shadow-soft">
        <div class="border-b border-brand-line p-3">
          <div class="grid grid-cols-3 gap-2 rounded-ui bg-brand-soft p-1" role="tablist" aria-label="Account tabs">
            <button data-profile-tab="profile" class="rounded-ui bg-brand-blue px-3 py-2 text-sm font-semibold text-white" type="button">Profile</button>
            <button data-profile-tab="settings" class="rounded-ui px-3 py-2 text-sm font-semibold text-brand-muted" type="button">Settings</button>
            <button data-profile-tab="security" class="rounded-ui px-3 py-2 text-sm font-semibold text-brand-muted" type="button">Security</button>
          </div>
        </div>

        <div class="p-5">
          <div data-profile-panel="profile">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
              <div data-avatar-preview class="flex h-28 w-28 shrink-0 items-center justify-center rounded-full border border-brand-line bg-brand-blue text-5xl font-bold text-white" aria-label="Profile avatar">⛽</div>
              <div><h2 class="text-2xl font-bold"><?= htmlspecialchars($name) ?></h2><p class="mt-1 text-sm text-brand-muted"><?= htmlspecialchars($email) ?></p></div>
            </div>
            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
              <div class="rounded-ui border border-brand-line p-4"><dt class="text-xs font-semibold uppercase text-brand-muted">Phone</dt><dd class="mt-1 font-bold"><?= htmlspecialchars($phone) ?></dd></div>
              <div class="rounded-ui border border-brand-line p-4"><dt class="text-xs font-semibold uppercase text-brand-muted">Joined</dt><dd class="mt-1 font-bold"><?= htmlspecialchars($joined) ?></dd></div>
              <div class="rounded-ui border border-brand-line p-4"><dt class="text-xs font-semibold uppercase text-brand-muted">Phone Verify Status</dt><dd class="mt-2"><?= $phoneVerify === 1 ? '<span class="badge-activated inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Verified</span>' : '<span class="badge-expired inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800">Not Verified</span>' ?></dd></div>
              <div class="rounded-ui border border-brand-line p-4"><dt class="text-xs font-semibold uppercase text-brand-muted">Account Status</dt><dd class="mt-2"><?php if ($accountStatus === 1): ?><span class="badge-activated inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Active</span><?php elseif ($accountStatus === 2): ?><span class="badge-pending inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Suspended</span><?php else: ?><span class="badge-expired inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800">Blocked</span><?php endif; ?></dd></div>
            </dl>
            <div class="mt-6 rounded-ui border border-[#BFD8EF] bg-[#EEF7FF] p-4">
              <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                  <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-yellow text-2xl font-bold text-brand-ink" aria-hidden="true">₵</span>
                  <div>
                    <h3 class="font-semibold text-brand-ink">Voucher Wallet</h3>
                    <p class="mt-1 text-sm text-brand-muted">View balance, top up, and pay for vouchers with wallet credit.</p>
                  </div>
                </div>
                <a class="inline-flex justify-center rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white" href="wallet">Open Wallet</a>
              </div>
            </div>
          </div>

          <div data-profile-panel="settings" class="hidden">
            <form method="post" action="update_account_profile" class="grid gap-4">
              <div class="rounded-ui border border-brand-line bg-brand-soft p-4">
                <div data-avatar-preview class="mx-auto flex h-28 w-28 items-center justify-center rounded-full border border-brand-line bg-brand-blue text-5xl font-bold text-white" aria-label="Profile avatar">⛽</div>
                <p class="mt-4 text-center text-sm font-semibold">Choose Avatar</p>
                <div class="mt-3 grid grid-cols-4 gap-2 sm:grid-cols-8" aria-label="Avatar choices">
                  <button class="avatar-choice flex h-11 w-11 items-center justify-center rounded-full bg-brand-blue text-xl font-bold text-white ring-offset-2 focus:outline-none focus:ring-2 focus:ring-brand-blue" data-avatar="⛽" data-avatar-bg="#2178BD" type="button" aria-label="Fuel pump avatar">⛽</button>
                  <button class="avatar-choice flex h-11 w-11 items-center justify-center rounded-full bg-slate-700 text-xl font-bold text-white ring-offset-2 focus:outline-none focus:ring-2 focus:ring-brand-blue" data-avatar="🚘" data-avatar-bg="#334155" type="button" aria-label="Car avatar">🚘</button>
                  <button class="avatar-choice flex h-11 w-11 items-center justify-center rounded-full bg-emerald-600 text-xl font-bold text-white ring-offset-2 focus:outline-none focus:ring-2 focus:ring-brand-blue" data-avatar="🛡️" data-avatar-bg="#059669" type="button" aria-label="Shield avatar">🛡️</button>
                  <button class="avatar-choice flex h-11 w-11 items-center justify-center rounded-full bg-amber-500 text-xl font-bold text-brand-ink ring-offset-2 focus:outline-none focus:ring-2 focus:ring-brand-blue" data-avatar="💳" data-avatar-bg="#F59E0B" data-avatar-color="#15253A" type="button" aria-label="Payment card avatar">💳</button>
                  <button class="avatar-choice flex h-11 w-11 items-center justify-center rounded-full bg-cyan-600 text-xl font-bold text-white ring-offset-2 focus:outline-none focus:ring-2 focus:ring-brand-blue" data-avatar="📍" data-avatar-bg="#0891B2" type="button" aria-label="Location avatar">📍</button>
                  <button class="avatar-choice flex h-11 w-11 items-center justify-center rounded-full bg-indigo-600 text-xl font-bold text-white ring-offset-2 focus:outline-none focus:ring-2 focus:ring-brand-blue" data-avatar="🔐" data-avatar-bg="#4F46E5" type="button" aria-label="Security avatar">🔐</button>
                  <button class="avatar-choice flex h-11 w-11 items-center justify-center rounded-full bg-rose-600 text-xl font-bold text-white ring-offset-2 focus:outline-none focus:ring-2 focus:ring-brand-blue" data-avatar="🎁" data-avatar-bg="#E11D48" type="button" aria-label="Gift avatar">🎁</button>
                  <button class="avatar-choice flex h-11 w-11 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white ring-offset-2 focus:outline-none focus:ring-2 focus:ring-brand-blue" data-avatar="👤" data-avatar-bg="#18181B" type="button" aria-label="Person avatar">👤</button>
                </div>
                <p class="mt-3 text-center text-xs text-brand-muted">Avatar choice is saved only on this browser.</p>
              </div>
              <label><span class="text-sm font-medium text-brand-muted">Name</span><input type="text" class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm" name="name" value="<?= htmlspecialchars($name) ?>" required /></label>
              <label><span class="text-sm font-medium text-brand-muted">Email</span><input type="email" class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm" name="email" value="<?= htmlspecialchars($email) ?>" required /></label>
              <label><span class="text-sm font-medium text-brand-muted">Phone</span><input type="text" class="mt-1 w-full rounded-ui border border-brand-line bg-brand-soft px-3 py-2.5 text-sm" value="<?= htmlspecialchars($phone) ?>" disabled /><span class="mt-1 block text-xs font-semibold text-red-700">Contact support for phone number updates.</span></label>
              <button class="w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white sm:w-fit" name="save" type="submit">Save Changes</button>
            </form>
          </div>

          <div data-profile-panel="security" class="hidden">
            <form method="post" action="update_account_profile_password" class="grid gap-4">
              <h2 class="text-lg font-semibold">Password Change</h2>
              <input class="rounded-ui border border-brand-line px-3 py-2.5 text-sm" type="password" name="current_password" placeholder="Current password" required />
              <input class="rounded-ui border border-brand-line px-3 py-2.5 text-sm" type="password" name="new_password" placeholder="New password" required />
              <input class="rounded-ui border border-brand-line px-3 py-2.5 text-sm" type="password" name="confirm_new_password" placeholder="Confirm new password" required />
              <div class="rounded-ui border border-red-100 bg-red-50 p-3 text-xs font-semibold leading-5 text-red-800">Password must be 8-20 characters and include uppercase, lowercase, digit, and special character.</div>
              <button class="w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white sm:w-fit" name="change_password" type="submit">Change Password</button>
            </form>
            <div class="mt-6 rounded-ui border border-[#BFD8EF] bg-[#EEF7FF] p-4">
              <h2 class="font-semibold">Multi-factor Authentication <span class="ml-2 rounded-full bg-brand-yellow px-2 py-0.5 text-xs text-brand-ink">Recommended</span></h2>
              <div class="mt-3 grid gap-2 text-sm font-semibold">
                <a class="text-brand-blue" href="otp_googleauth">Set up Google Authenticator</a>
                <a class="text-brand-blue" href="otp_activation">Set up Phone OTP</a>
              </div>
            </div>
          </div>
        </div>
      </section>
      <?php else: ?>
        <div class="rounded-ui border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert">Profile could not be loaded and no session profile details are available.</div>
      <?php endif; ?>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div><script src="assets/app.js"></script><script>
      document.querySelectorAll("[data-profile-tab]").forEach((button) => {
        button.addEventListener("click", () => {
          const tab = button.dataset.profileTab;
          document.querySelectorAll("[data-profile-panel]").forEach((panel) => panel.classList.toggle("hidden", panel.dataset.profilePanel !== tab));
          document.querySelectorAll("[data-profile-tab]").forEach((item) => {
            const active = item.dataset.profileTab === tab;
            item.classList.toggle("bg-brand-blue", active);
            item.classList.toggle("text-white", active);
            item.classList.toggle("text-brand-muted", !active);
          });
        });
      });
      const avatarKey = "staroil:profileAvatar";
      const defaultAvatar = { text: "⛽", bg: "#2178BD", color: "#FFFFFF" };
      function getAvatar() {
        try { return JSON.parse(localStorage.getItem(avatarKey)) || defaultAvatar; } catch (error) { return defaultAvatar; }
      }
      function applyAvatar(avatar) {
        document.querySelectorAll("[data-avatar-preview]").forEach((node) => {
          node.textContent = avatar.text;
          node.style.backgroundColor = avatar.bg;
          node.style.color = avatar.color || "#FFFFFF";
        });
        document.querySelectorAll(".avatar-choice").forEach((button) => {
          const active = button.dataset.avatar === avatar.text && button.dataset.avatarBg === avatar.bg;
          button.classList.toggle("ring-2", active);
          button.classList.toggle("ring-brand-blue", active);
        });
      }
      applyAvatar(getAvatar());
      document.querySelectorAll(".avatar-choice").forEach((button) => {
        button.addEventListener("click", () => {
          const avatar = {
            text: button.dataset.avatar,
            bg: button.dataset.avatarBg,
            color: button.dataset.avatarColor || "#FFFFFF"
          };
          localStorage.setItem(avatarKey, JSON.stringify(avatar));
          applyAvatar(avatar);
          document.dispatchEvent(new CustomEvent("staroil:avatarChange", { detail: avatar }));
        });
      });
    </script>
  </body>
</html>
