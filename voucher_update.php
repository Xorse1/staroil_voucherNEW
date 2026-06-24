<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/frontend_log.php';
require_once __DIR__ . '/includes/helper.php';

$voucherId = isset($_GET['title']) ? sanitize($_GET['title']) : '';
if ($voucherId === '') {
    $_SESSION['successerrorupdated'] = 'Voucher ID is required.';
    header('Location: vouchers');
    exit;
}

$api_url = 'https://fms.kayxappstaroil.com/APIs/voucher_api/fetch_voucher_single.php?voucher_id=' . urlencode($voucherId);
$ch = curl_init($api_url);
$responseData = [];

if ($ch !== false) {
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);
    $responseData = json_decode((string) $response, true) ?: [];
}

$voucher = $responseData['data'][0] ?? [];
if (empty($voucher)) {
    $_SESSION['successerrorupdated'] = 'Voucher could not be loaded.';
    header('Location: vouchers');
    exit;
}

$startDate = $voucher['start_date'] ?? '';
$expiryDate = $voucher['expiry_date'] ?? '';
$status = (string) ($voucher['status'] ?? '0');
$recipientName = $voucher['gift_recipient_name'] ?? '';
$recipientPhoto = $voucher['gift_recipient_photo'] ?? '';

function voucher_status_label($status, $expiryDate) {
    $today = gmdate('Y-m-d');
    if ((string) $status === '1' && $expiryDate >= $today) return 'Activated';
    if ($expiryDate < $today) return 'Expired';
    return 'Pending';
}
?>
<!doctype html>
<html lang="en">
  <head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" /><title>Update Voucher | Star Oil Fuel Voucher System</title><link rel="preconnect" href="https://fonts.bunny.net" /><link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" /><script src="https://cdn.tailwindcss.com"></script><script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"},boxShadow:{soft:"0 12px 28px rgba(21,37,58,.08)"}}}}</script></head>
  <body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
    <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="text-sm font-semibold text-brand-blue">Voucher Management</p><h1 class="mt-1 text-2xl font-bold sm:text-3xl">Update Voucher</h1><p class="mt-2 text-sm leading-6 text-brand-muted">Adjust voucher dates, activation status, and optional gift customization.</p></div>
        <a class="rounded-ui border border-brand-line bg-white px-4 py-2.5 text-center text-sm font-semibold" href="vouchers">Back to Vouchers</a>
      </div>
      <?php if (!empty($_SESSION['successerrorupdated'])): ?><div class="mb-5 rounded-ui border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert"><?= htmlspecialchars($_SESSION['successerrorupdated']) ?></div><?php unset($_SESSION['successerrorupdated']); endif; ?>
      <section class="rounded-ui border border-[#BFD8EF] bg-[#EEF7FF] p-4 text-sm leading-6 text-brand-muted">
        <strong class="text-brand-ink">Note:</strong> Maximum image size is 5MB. Image type must be JPEG, JPG, or PNG. Use a clear portrait or square passport-style photo.
      </section>
      <section class="mt-5 rounded-ui border border-brand-line bg-white p-5 shadow-soft">
        <form action="voucher_update_process" method="POST" enctype="multipart/form-data" class="grid gap-5">
          <div class="grid gap-4 sm:grid-cols-2">
            <label><span class="text-sm font-medium text-brand-muted">Start Date</span><input type="date" class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required /></label>
            <label><span class="text-sm font-medium text-brand-muted">Expiry Date</span><input type="date" class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" name="expiry_date" value="<?= htmlspecialchars($expiryDate) ?>" required /></label>
          </div>
          <label><span class="text-sm font-medium text-brand-muted">Voucher Status</span><select class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" name="status"><option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(voucher_status_label($status, $expiryDate)) ?></option><option value="1">Activate</option><option value="0">Pending</option></select></label>
          <div class="rounded-ui border border-brand-line bg-brand-soft p-4">
            <h2 class="text-base font-semibold">Customize voucher</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
              <label><span class="text-sm font-medium text-brand-muted">Gift message <small class="text-red-700">(25 characters)</small></span><input type="text" class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" name="rec_name" maxlength="25" value="<?= htmlspecialchars($recipientName) ?>" /></label>
              <label><span class="text-sm font-medium text-brand-muted">Gift recipient photo <small class="text-red-700">(5MB max)</small></span><input type="file" id="fileInput" class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm" name="rec_photo" accept="image/jpeg,image/png" /></label>
            </div>
            <div id="previewContainer" class="mt-4 flex flex-wrap gap-3">
              <?php if ($recipientPhoto !== ''): ?><img class="h-28 w-28 rounded-ui border border-brand-line object-cover" src="<?= htmlspecialchars($recipientPhoto) ?>" alt="Gift recipient photo" crossorigin="anonymous" /><?php endif; ?>
            </div>
          </div>
          <input type="hidden" name="voucher_id" value="<?= htmlspecialchars((string) ($voucher['id'] ?? $voucherId)) ?>" />
          <button type="submit" name="update_voucher" class="rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#1A659F]">Update Voucher</button>
        </form>
      </section>
    </main>
    <div id="toast-region" class="fixed right-4 top-20 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3" aria-live="polite"></div><script src="assets/app.js"></script><script>
      const fileInput = document.getElementById("fileInput");
      const previewContainer = document.getElementById("previewContainer");
      fileInput?.addEventListener("change", () => {
        previewContainer.innerHTML = "";
        Array.from(fileInput.files || []).forEach((file) => {
          const reader = new FileReader();
          reader.onload = (event) => {
            const image = document.createElement("img");
            image.src = event.target.result;
            image.alt = "Image preview";
            image.className = "h-28 w-28 rounded-ui border border-brand-line object-cover";
            previewContainer.appendChild(image);
          };
          reader.readAsDataURL(file);
        });
      });
    </script>
  </body>
</html>
