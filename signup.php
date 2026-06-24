<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';
session_start();
require_once __DIR__ . '/includes/frontend_log.php';

$hex_value = $_SESSION['register_token'] ?? bin2hex(random_bytes(8));
$_SESSION['register_token'] = $hex_value;

$messages = [
    'passmismarch' => 'Passwords do not match.',
    'passincorrect' => 'Password does not meet the required format.',
    'emailphonenotexist' => 'Email or phone number already exists.',
    'failedtoadd' => 'Failed to add beneficiary. Please try again.',
    'invalidemaildomain' => 'Email domain not allowed.'
];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Signup | Star Oil Fuel Voucher System</title>
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script>
  </head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
    <label class="fixed right-4 top-4 z-50 block w-32"><span class="sr-only">Theme</span><select data-theme-select class="w-full rounded-ui border border-brand-line bg-white px-3 py-2 text-sm font-semibold text-brand-ink"><option value="system">System</option><option value="white">White</option><option value="dark">Dark</option></select></label>
    <main class="mx-auto flex min-h-screen max-w-xl items-center px-4 py-6">
      <section class="w-full rounded-ui border border-brand-line bg-white p-6 shadow-soft" aria-labelledby="signup-title">
        <div class="mb-6 flex items-center gap-3">
          <img class="h-10 w-auto" src="images/alogo_light.png" alt="StarOil logo" />
          <div><p class="text-xs font-medium text-brand-muted">User registration</p></div>
        </div>
        <div class="mb-5 grid grid-cols-2 rounded-ui border border-brand-line bg-brand-soft p-1">
          <a class="rounded-ui px-3 py-2 text-center text-sm font-semibold text-brand-muted" href="signin">Login</a>
          <span class="rounded-ui bg-brand-blue px-3 py-2 text-center text-sm font-semibold text-white">Signup</span>
        </div>
        <h1 id="signup-title" class="text-2xl font-bold">Create user account</h1>
        <p class="mt-2 text-sm leading-6 text-brand-muted">Register to access the fuel voucher system.</p>

        <?php foreach ($messages as $key => $message): ?>
          <?php if (!empty($_SESSION[$key])): ?>
            <div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800" role="alert">
              <?= htmlspecialchars($message) ?>
            </div>
            <?php unset($_SESSION[$key]); ?>
          <?php endif; ?>
        <?php endforeach; ?>

        <form action="register_process?<?= htmlspecialchars($hex_value); ?>" method="POST" class="mt-5 space-y-4">
          <label class="block" for="name">
            <span class="text-sm font-medium text-brand-muted">Name <span class="text-red-600">*</span></span>
            <input type="text" class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" name="name" id="name" value="<?= isset($_SESSION['old_name']) ? htmlspecialchars($_SESSION['old_name']) : '' ?>" required />
          </label>

          <label class="block" for="email">
            <span class="text-sm font-medium text-brand-muted">Email</span>
            <input type="email" class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" name="email" id="email" value="<?= isset($_SESSION['old_email']) ? htmlspecialchars($_SESSION['old_email']) : '' ?>" required />
          </label>

          <label class="block" for="phone">
            <span class="text-sm font-medium text-brand-muted">Phone Number <span class="text-red-600">*</span></span>
            <input type="tel" class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" maxlength="10" name="phone" id="phone" value="<?= isset($_SESSION['old_phone']) ? htmlspecialchars($_SESSION['old_phone']) : '' ?>" required />
          </label>

          <input type="hidden" name="tin" value="0" id="tin" />

          <label class="block" for="pass">
            <span class="text-sm font-medium text-brand-muted">Password <span class="text-red-600">*</span></span>
            <span class="relative mt-1 block">
              <input type="password" class="w-full rounded-ui border border-brand-line px-3 py-2.5 pr-16 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" name="pass" id="pass" data-password-field required />
              <button class="absolute inset-y-1 right-1 rounded-ui px-3 text-xs font-semibold text-brand-blue hover:bg-brand-soft focus:outline-none focus:ring-2 focus:ring-brand-blue" type="button" data-toggle-password aria-controls="pass" aria-pressed="false">Show</button>
            </span>
          </label>

          <label class="block" for="cpass">
            <span class="text-sm font-medium text-brand-muted">Confirm Password <span class="text-red-600">*</span></span>
            <span class="relative mt-1 block">
              <input type="password" class="w-full rounded-ui border border-brand-line px-3 py-2.5 pr-16 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" name="cpass" id="cpass" data-password-field required />
              <button class="absolute inset-y-1 right-1 rounded-ui px-3 text-xs font-semibold text-brand-blue hover:bg-brand-soft focus:outline-none focus:ring-2 focus:ring-brand-blue" type="button" data-toggle-password aria-controls="cpass" aria-pressed="false">Show</button>
            </span>
          </label>

          <div class="space-y-1 mb-2 rounded-ui border border-red-100 bg-red-50 p-3 text-xs font-semibold leading-5 text-red-800">
            <p>The password is required to be between 8 and 20 characters.</p>
            <p>At least one uppercase letter ([A-Z]).</p>
            <p>At least one lowercase letter ([a-z]).</p>
            <p>At least one digit ([0-9]).</p>
            <p>At least one special character (non-alphanumeric).</p>
          </div><br>
          
          <small class="mt-4 text-xs text-brand-muted font-semibold">
          By signing up, you agree to our 
          <a href="terms-of-use" class="text-brand-blue hover:underline" target="_blank">Terms of Service</a>
           and 
          <a href="privacy" class="text-brand-blue hover:underline" target="_blank">Privacy Policy</a>.
        </small>

          <button type="submit" name="register" class="w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1A659F] focus:outline-none focus:ring-2 focus:ring-brand-blue focus:ring-offset-2">Register</button>
        </form>
      </section>
    </main>
    <div id="toast-region" class="fixed right-4 top-4 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div>
    <script src="assets/app.js"></script>
  </body>
</html>
<?php
unset($_SESSION['old_name'], $_SESSION['old_email'], $_SESSION['old_phone'], $_SESSION['old_tin']);
?>
