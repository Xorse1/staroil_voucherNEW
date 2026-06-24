<?php
require_once __DIR__ . '/includes/session_config.php';
session_start();
require_once __DIR__ . '/includes/frontend_log.php';

const MARKETING_SMS_PASSWORD = 'Ee10252667#';
const MARKETING_SMS_SUBJECT = '*Star Oil Voucher*';

function ms_h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function ms_money($value) {
    return 'GHS ' . number_format((float) $value, 2);
}

function ms_clean_phone($phone) {
    return preg_replace('/[^0-9+]/', '', (string) $phone);
}

function ms_read_jsonl($file) {
    $rows = [];
    if (!is_file($file)) return $rows;

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $row = json_decode($line, true);
        if (is_array($row)) $rows[] = $row;
    }

    return $rows;
}

function ms_cart_signature(array $items) {
    $clean = [];
    foreach ($items as $item) {
        $clean[] = [
            'amount' => (float) ($item['amount'] ?? 0),
            'quantity' => (int) ($item['quantity'] ?? 0),
        ];
    }
    usort($clean, fn($a, $b) => $b['amount'] <=> $a['amount']);
    return hash('sha256', json_encode($clean));
}

function ms_item_summary(array $items) {
    $parts = [];
    foreach ($items as $item) {
        $amount = (float) ($item['amount'] ?? 0);
        $quantity = (int) ($item['quantity'] ?? 0);
        if ($amount > 0 && $quantity > 0) {
            $parts[] = ms_money($amount) . ' x' . $quantity;
        }
    }
    return implode(', ', array_slice($parts, 0, 4));
}

function ms_build_profiles() {
    $records = ms_read_jsonl(__DIR__ . '/storage/abandoned_carts.jsonl');
    $profiles = [];

    foreach ($records as $record) {
        $phone = ms_clean_phone($record['phone'] ?? '');
        if ($phone === '') continue;

        $time = strtotime((string) ($record['recorded_at'] ?? '')) ?: 0;
        $phoneKey = $phone;
        $profiles[$phoneKey] ??= [
            'phone' => $phone,
            'name' => $record['name'] ?? 'Customer',
            'email' => $record['email'] ?? '',
            'user_id' => $record['user_id'] ?? null,
            'last_seen' => 0,
            'last_record' => null,
            'last_nonempty_cart' => null,
            'last_checkout_started' => null,
            'last_checkout_success' => null,
            'amount_counts' => [],
            'cart_success' => []
        ];

        if ($time >= $profiles[$phoneKey]['last_seen']) {
            $profiles[$phoneKey]['last_seen'] = $time;
            $profiles[$phoneKey]['last_record'] = $record;
            $profiles[$phoneKey]['name'] = $record['name'] ?? $profiles[$phoneKey]['name'];
        }

        $items = is_array($record['items'] ?? null) ? $record['items'] : [];
        foreach ($items as $item) {
            $amount = (float) ($item['amount'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 0);
            if ($amount > 0 && $qty > 0) {
                $key = (string) $amount;
                $profiles[$phoneKey]['amount_counts'][$key] = ($profiles[$phoneKey]['amount_counts'][$key] ?? 0) + $qty;
            }
        }

        if (!empty($items) && (float) ($record['subtotal'] ?? 0) > 0) {
            $profiles[$phoneKey]['last_nonempty_cart'] = $record;
        }

        if (($record['event'] ?? '') === 'checkout_started') {
            $profiles[$phoneKey]['last_checkout_started'] = $record;
        }

        if (($record['event'] ?? '') === 'checkout_success') {
            $profiles[$phoneKey]['last_checkout_success'] = $record;
            $cartId = (string) ($record['cart_id'] ?? '');
            if ($cartId !== '') $profiles[$phoneKey]['cart_success'][$cartId] = true;
        }
    }

    return $profiles;
}

function ms_campaigns() {
    return [
        'abandoned_cart' => [
            'name' => 'Abandoned cart SMS',
            'description' => 'Users with vouchers left in cart and no later success event for that cart.',
        ],
        'payment_dropoff' => [
            'name' => 'Payment drop-off SMS',
            'description' => 'Users who clicked Pay Now or started checkout but did not complete payment.',
        ],
        'denomination_interest' => [
            'name' => 'Denomination interest SMS',
            'description' => 'Users repeatedly showing interest in a voucher amount.',
        ],
        'best_time' => [
            'name' => 'Best-time sales SMS',
            'description' => 'Recent identifiable users, for sending during your peak traffic window.',
        ],
        'returning_buyer' => [
            'name' => 'Returning buyer SMS',
            'description' => 'Users with a previous successful checkout who have not returned recently.',
        ],
    ];
}

function ms_segment_recipients($segment, array $profiles) {
    $now = time();
    $recipients = [];

    foreach ($profiles as $profile) {
        $cart = $profile['last_nonempty_cart'];
        $checkout = $profile['last_checkout_started'];
        $success = $profile['last_checkout_success'];
        $lastSeenAgeDays = $profile['last_seen'] ? (($now - $profile['last_seen']) / 86400) : 9999;

        if ($segment === 'abandoned_cart' && $cart) {
            $cartId = (string) ($cart['cart_id'] ?? '');
            if ($cartId !== '' && empty($profile['cart_success'][$cartId])) {
                $recipients[] = ms_recipient($profile, $cart);
            }
        }

        if ($segment === 'payment_dropoff' && $checkout) {
            $cartId = (string) ($checkout['cart_id'] ?? '');
            if ($cartId !== '' && empty($profile['cart_success'][$cartId])) {
                $recipients[] = ms_recipient($profile, $checkout);
            }
        }

        if ($segment === 'denomination_interest' && !empty($profile['amount_counts'])) {
            arsort($profile['amount_counts']);
            $topAmount = (float) array_key_first($profile['amount_counts']);
            if ($topAmount > 0) {
                $recipient = ms_recipient($profile, $cart ?: $profile['last_record']);
                $recipient['top_amount'] = $topAmount;
                $recipients[] = $recipient;
            }
        }

        if ($segment === 'best_time' && $lastSeenAgeDays <= 30) {
            $recipients[] = ms_recipient($profile, $profile['last_record']);
        }

        if ($segment === 'returning_buyer' && $success && $lastSeenAgeDays >= 7) {
            $recipients[] = ms_recipient($profile, $success);
        }
    }

    $unique = [];
    foreach ($recipients as $recipient) {
        $unique[$recipient['phone']] = $recipient;
    }

    return array_values($unique);
}

function ms_recipient(array $profile, $record) {
    $items = is_array($record['items'] ?? null) ? $record['items'] : [];
    return [
        'phone' => $profile['phone'],
        'name' => $profile['name'] ?: 'Customer',
        'user_id' => $profile['user_id'],
        'last_seen' => $profile['last_seen'],
        'subtotal' => (float) ($record['subtotal'] ?? 0),
        'quantity' => (int) ($record['total_quantity'] ?? 0),
        'items' => $items,
        'summary' => ms_item_summary($items),
        'cart_signature' => ms_cart_signature($items),
    ];
}

function ms_message($segment, array $recipient) {
    $subject = MARKETING_SMS_SUBJECT;
    $firstName = trim(explode(' ', (string) $recipient['name'])[0]);
    $firstName = $firstName !== '' && strtolower($firstName) !== 'customer' ? $firstName . ', ' : '';
    $summary = $recipient['summary'] !== '' ? ' (' . $recipient['summary'] . ')' : '';
    $amount = isset($recipient['top_amount']) ? ms_money($recipient['top_amount']) : '';

    $messages = [
        'abandoned_cart' => "{$subject}: {$firstName}your fuel voucher cart is still waiting{$summary}. Complete it before your next refill catches you unprepared. Visit app.staroil.services/store",
        'payment_dropoff' => "{$subject}: {$firstName}you were one step away from securing your fuel voucher{$summary}. Finish payment now and keep your refill covered. Visit app.staroil.services/cart",
        'denomination_interest' => "{$subject}: {$firstName}your preferred {$amount} fuel voucher is available. Buy ahead now and avoid last-minute fuel pressure. Visit app.staroil.services/store",
        'best_time' => "{$subject}: Fuel plans are easier before the rush. Buy your StarOil voucher now and stay ready for your next journey. Visit app.staroil.services/store",
        'returning_buyer' => "{$subject}: {$firstName}it has been a while since your last voucher purchase. Stay ready for your next refuel with a fresh StarOil voucher. Visit app.staroil.services/store",
    ];

    return $messages[$segment] ?? $messages['best_time'];
}

function ms_send_log_count($segment, $phone, $signature) {
    $file = __DIR__ . '/storage/marketing_sms_sent.jsonl';
    if (!is_file($file)) return 0;
    $count = 0;
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $row = json_decode($line, true);
        if (!is_array($row)) continue;
        if (($row['segment'] ?? '') === $segment && ($row['phone_hash'] ?? '') === hash('sha256', $phone . '|staroil-marketing') && ($row['signature'] ?? '') === $signature) {
            $count++;
        }
    }
    return $count;
}

function ms_log_send($segment, array $recipient, $status) {
    $file = __DIR__ . '/storage/marketing_sms_sent.jsonl';
    $row = [
        'sent_at' => gmdate('c'),
        'segment' => $segment,
        'phone_hash' => hash('sha256', $recipient['phone'] . '|staroil-marketing'),
        'user_id' => $recipient['user_id'],
        'signature' => $recipient['cart_signature'] ?: hash('sha256', $segment . '|' . $recipient['phone']),
        'status' => $status
    ];
    file_put_contents($file, json_encode($row, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

if (isset($_GET['logout'])) {
    unset($_SESSION['marketing_sms_access']);
    header('Location: marketing_sms');
    exit;
}

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (hash_equals(MARKETING_SMS_PASSWORD, (string) $_POST['password'])) {
        $_SESSION['marketing_sms_access'] = true;
        header('Location: marketing_sms');
        exit;
    }
    $loginError = 'Invalid password.';
}

$authorized = !empty($_SESSION['marketing_sms_access']);
$campaigns = ms_campaigns();
$profiles = $authorized ? ms_build_profiles() : [];
$selectedSegment = isset($_GET['segment'], $campaigns[$_GET['segment']]) ? $_GET['segment'] : 'abandoned_cart';
$recipients = $authorized ? ms_segment_recipients($selectedSegment, $profiles) : [];
$sendResult = null;

if ($authorized && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_campaign'])) {
    require_once __DIR__ . '/sms_new_arkesel.php';
    $selectedSegment = isset($_POST['segment'], $campaigns[$_POST['segment']]) ? $_POST['segment'] : 'abandoned_cart';
    $limit = max(1, min(200, (int) ($_POST['limit'] ?? 50)));
    $recipients = array_slice(ms_segment_recipients($selectedSegment, $profiles), 0, $limit);
    $sent = 0;
    $skipped = 0;

    foreach ($recipients as $recipient) {
        $signature = $recipient['cart_signature'] ?: hash('sha256', $selectedSegment . '|' . $recipient['phone']);
        if (ms_send_log_count($selectedSegment, $recipient['phone'], $signature) >= 1) {
            $skipped++;
            continue;
        }

        $message = ms_message($selectedSegment, $recipient);
        if (function_exists('send_sms_arkesel')) {
            send_sms_arkesel($recipient['phone'], $message);
            $sent++;
            ms_log_send($selectedSegment, $recipient, 'sent');
        } else {
            ms_log_send($selectedSegment, $recipient, 'sms_function_missing');
            $skipped++;
        }
    }

    $sendResult = "Sent {$sent} SMS. Skipped {$skipped}.";
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Marketing SMS | StarOil Voucher System</title>
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"}}}}</script>
</head>
<body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
<?php if (!$authorized): ?>
  <main class="mx-auto flex min-h-screen max-w-md items-center px-4 py-10">
    <section class="w-full rounded-ui border border-brand-line bg-white p-6 shadow-sm">
      <img class="h-10 w-auto" src="images/alogo_light.png" alt="StarOil logo">
      <h1 class="mt-6 text-2xl font-bold">Marketing SMS Access</h1>
      <p class="mt-2 text-sm leading-6 text-brand-muted">Enter the access password to prepare and send campaign SMS messages.</p>
      <?php if ($loginError !== ''): ?><div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800"><?= ms_h($loginError) ?></div><?php endif; ?>
      <form class="mt-5 space-y-4" method="POST" action="marketing_sms">
        <label class="block"><span class="text-sm font-semibold">Password</span><input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" type="password" name="password" required autofocus></label>
        <button class="w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-bold text-white" type="submit">Open SMS Tool</button>
      </form>
    </section>
  </main>
<?php else: ?>
  <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-bold text-brand-blue">Marketing Automation</p>
        <h1 class="mt-1 text-3xl font-bold">StarOil Voucher SMS Campaigns</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-brand-muted">Send targeted SMS messages based on cart and activity data. Messages start with <strong>*Star Oil Voucher*</strong>.</p>
      </div>
      <a class="rounded-ui border border-brand-line bg-white px-4 py-2.5 text-sm font-bold text-red-700" href="marketing_sms?logout=1">Lock Page</a>
    </header>

    <?php if ($sendResult): ?><div class="mb-5 rounded-ui border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800"><?= ms_h($sendResult) ?></div><?php endif; ?>

    <section class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
      <?php foreach ($campaigns as $key => $campaign): ?>
        <?php $count = count(ms_segment_recipients($key, $profiles)); ?>
        <a class="rounded-ui border <?= $selectedSegment === $key ? 'border-brand-blue bg-[#EEF7FF]' : 'border-brand-line bg-white' ?> p-4 shadow-sm" href="marketing_sms?segment=<?= ms_h($key) ?>">
          <p class="text-sm font-bold text-brand-blue"><?= ms_h($campaign['name']) ?></p>
          <p class="mt-2 text-3xl font-bold"><?= (int) $count ?></p>
          <p class="mt-2 text-xs leading-5 text-brand-muted"><?= ms_h($campaign['description']) ?></p>
        </a>
      <?php endforeach; ?>
    </section>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_380px]">
      <section class="rounded-ui border border-brand-line bg-white shadow-sm">
        <div class="border-b border-brand-line px-4 py-3">
          <h2 class="font-bold"><?= ms_h($campaigns[$selectedSegment]['name']) ?> Recipients</h2>
          <p class="text-sm text-brand-muted"><?= count($recipients) ?> eligible recipient(s). Already-sent matching messages are skipped during send.</p>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-brand-line text-left text-sm">
            <thead class="bg-slate-50 text-xs font-bold uppercase text-brand-muted">
              <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Phone</th><th class="px-4 py-3">Cart/Interest</th><th class="px-4 py-3">Message Preview</th></tr>
            </thead>
            <tbody class="divide-y divide-brand-line">
              <?php if (empty($recipients)): ?><tr><td class="px-4 py-8 text-center text-brand-muted" colspan="4">No eligible recipients for this campaign yet.</td></tr><?php endif; ?>
              <?php foreach (array_slice($recipients, 0, 100) as $recipient): ?>
                <tr>
                  <td class="whitespace-nowrap px-4 py-3 font-semibold"><?= ms_h($recipient['name']) ?></td>
                  <td class="whitespace-nowrap px-4 py-3"><?= ms_h($recipient['phone']) ?></td>
                  <td class="px-4 py-3"><?= ms_h($recipient['summary'] ?: (isset($recipient['top_amount']) ? ms_money($recipient['top_amount']) : 'Recent activity')) ?></td>
                  <td class="max-w-lg px-4 py-3 text-xs text-brand-muted"><?= ms_h(ms_message($selectedSegment, $recipient)) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <aside class="h-fit rounded-ui border border-brand-line bg-white p-4 shadow-sm">
        <h2 class="font-bold">Send Campaign</h2>
        <p class="mt-2 text-sm leading-6 text-brand-muted">This sends real SMS messages. Start with a small limit for testing.</p>
        <form class="mt-4 space-y-4" method="POST" action="marketing_sms?segment=<?= ms_h($selectedSegment) ?>">
          <input type="hidden" name="segment" value="<?= ms_h($selectedSegment) ?>">
          <label class="block"><span class="text-sm font-semibold text-brand-muted">Maximum recipients</span><input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm" type="number" min="1" max="200" name="limit" value="25"></label>
          <div class="rounded-ui border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900">
            Confirm that recipients have agreed to receive promotional messages before sending.
          </div>
          <button class="w-full rounded-ui bg-brand-yellow px-4 py-2.5 text-sm font-bold text-brand-ink" name="send_campaign" value="1" type="submit">Send SMS Campaign</button>
        </form>
      </aside>
    </div>
  </main>
<?php endif; ?>
</body>
</html>
