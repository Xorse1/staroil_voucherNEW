<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function vr_money($value) {
    return 'GHS ' . number_format((float) $value, 2);
}

function vr_image_for_amount($amount) {
    $amount = (int) $amount;
    $path = __DIR__ . '/images/' . $amount . 'cedisvoucher.png';
    return is_file($path) ? 'images/' . $amount . 'cedisvoucher.png' : 'images/50cedisvoucher.png';
}

function vr_read_jsonl($file) {
    $rows = [];
    if (!is_file($file)) return $rows;

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $row = json_decode($line, true);
        if (is_array($row)) $rows[] = $row;
    }

    return $rows;
}

function vr_time_ago($timestamp) {
    $diff = max(0, time() - $timestamp);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}

function vr_cart_amounts() {
    $raw = $_GET['cart'] ?? '';
    if ($raw === '') return [];
    $amounts = [];
    foreach (explode(',', (string) $raw) as $amount) {
        $value = (float) trim($amount);
        if ($value > 0) $amounts[] = $value;
    }
    return array_values(array_unique($amounts));
}

$fallbackAmounts = [10, 20, 30, 40, 50, 100, 200, 500, 1000];
$records = vr_read_jsonl(__DIR__ . '/storage/abandoned_carts.jsonl');
$today = gmdate('Y-m-d');
$stats = [];
$recentPurchases = [];
$purchaseSeen = [];

foreach ($fallbackAmounts as $amount) {
    $stats[(string) $amount] = [
        'amount' => (float) $amount,
        'score' => 0,
        'interest' => 0,
        'purchased_today' => 0,
        'checkout_started' => 0,
        'last_seen' => 0
    ];
}

foreach ($records as $record) {
    $items = is_array($record['items'] ?? null) ? $record['items'] : [];
    if (empty($items)) continue;

    $event = (string) ($record['event'] ?? 'cart_update');
    $recordedAt = strtotime((string) ($record['recorded_at'] ?? '')) ?: 0;
    $recordDate = $recordedAt ? gmdate('Y-m-d', $recordedAt) : '';
    $weight = match ($event) {
        'checkout_success' => 8,
        'checkout_started' => 4,
        'cart_page_left' => 2,
        default => 1,
    };

    foreach ($items as $item) {
        if (!is_array($item)) continue;
        $amount = (float) ($item['amount'] ?? 0);
        $qty = max(1, (int) ($item['quantity'] ?? 1));
        if ($amount <= 0) continue;

        $key = (string) (int) $amount;
        $stats[$key] ??= [
            'amount' => $amount,
            'score' => 0,
            'interest' => 0,
            'purchased_today' => 0,
            'checkout_started' => 0,
            'last_seen' => 0
        ];

        $stats[$key]['score'] += $weight * $qty;
        $stats[$key]['interest'] += $qty;
        $stats[$key]['last_seen'] = max($stats[$key]['last_seen'], $recordedAt);

        if ($event === 'checkout_started') {
            $stats[$key]['checkout_started'] += $qty;
        }

        if ($event === 'checkout_success') {
            if ($recordDate === $today) {
                $stats[$key]['purchased_today'] += $qty;
            }

            $recentKey = $key . '|' . ($record['cart_id'] ?? '') . '|' . $recordedAt;
            if (empty($purchaseSeen[$recentKey])) {
                $purchaseSeen[$recentKey] = true;
                $recentPurchases[] = [
                    'amount' => $amount,
                    'quantity' => $qty,
                    'timestamp' => $recordedAt,
                    'message' => 'A customer bought ' . vr_money($amount) . ' voucher' . ($qty > 1 ? ' x' . $qty : ''),
                    'time_ago' => $recordedAt ? vr_time_ago($recordedAt) : 'recently',
                    'image' => vr_image_for_amount($amount)
                ];
            }
        }
    }
}

usort($stats, function ($a, $b) {
    if ($a['score'] === $b['score']) return $b['amount'] <=> $a['amount'];
    return $b['score'] <=> $a['score'];
});

$popular = array_map(function ($row, $index) {
    $tag = $index === 0 ? 'Best Seller' : ($row['purchased_today'] > 0 ? 'Trending Today' : 'Popular Pick');
    return [
        'amount' => $row['amount'],
        'label' => vr_money($row['amount']),
        'tag' => $tag,
        'score' => (int) $row['score'],
        'interest' => (int) $row['interest'],
        'purchased_today' => (int) $row['purchased_today'],
        'checkout_started' => (int) $row['checkout_started'],
        'image' => vr_image_for_amount($row['amount'])
    ];
}, array_slice(array_values($stats), 0, 6), array_keys(array_slice(array_values($stats), 0, 6)));

$cartAmounts = vr_cart_amounts();
$cartLookup = array_flip(array_map(fn($amount) => (string) (int) $amount, $cartAmounts));
$recommendations = [];

foreach ($stats as $row) {
    $key = (string) (int) $row['amount'];
    if (isset($cartLookup[$key])) continue;
    $recommendations[] = [
        'amount' => $row['amount'],
        'label' => vr_money($row['amount']),
        'reason' => $row['purchased_today'] > 0
            ? 'Purchased ' . (int) $row['purchased_today'] . ' time' . ((int) $row['purchased_today'] === 1 ? '' : 's') . ' today'
            : ($row['checkout_started'] > 0 ? 'Frequently selected at checkout' : 'Useful add-on denomination'),
        'tag' => empty($cartAmounts) ? 'Recommended' : 'Add-on Pick',
        'image' => vr_image_for_amount($row['amount']),
        'purchased_today' => (int) $row['purchased_today']
    ];
    if (count($recommendations) >= 4) break;
}

usort($recentPurchases, fn($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));
$recentPurchases = array_map(function ($purchase) {
    unset($purchase['timestamp']);
    return $purchase;
}, array_slice($recentPurchases, 0, 8));

echo json_encode([
    'status' => 'success',
    'generated_at' => gmdate('c'),
    'popular' => $popular,
    'recommendations' => $recommendations,
    'recent_purchases' => $recentPurchases,
    'privacy_note' => 'Recent purchase activity is anonymous and does not include customer names, phone numbers, or emails.'
], JSON_UNESCAPED_SLASHES);
