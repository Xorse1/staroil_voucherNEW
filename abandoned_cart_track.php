<?php
require_once __DIR__ . '/includes/session_config.php';
session_start();
header('Content-Type: application/json');

foreach ([
    //__DIR__ . '/config.php', 
    __DIR__ . '/sms_new_arkesel.php'
] as $optionalFile) {
    if (is_file($optionalFile)) {
        require_once $optionalFile;
    }
}

function cart_signature($items) {
    $normalized = [];
    foreach ($items as $item) {
        $normalized[] = [
            'id' => (string) ($item['id'] ?? ''),
            'amount' => (float) ($item['amount'] ?? 0),
            'quantity' => (int) ($item['quantity'] ?? 0)
        ];
    }

    usort($normalized, function ($a, $b) {
        return strcmp($a['id'] . '-' . $a['amount'], $b['id'] . '-' . $b['amount']);
    });

    return hash('sha256', json_encode($normalized));
}

function cart_sms_sent_count($cartId, $userId, $signature) {
    $file = __DIR__ . '/storage/cart_sms_sent.jsonl';
    if ($cartId === '' || $signature === '' || !is_file($file)) return 0;

    $count = 0;

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $row = json_decode($line, true);
        if (!is_array($row)) continue;
        if (
            ($row['cart_id'] ?? '') === $cartId
            && (string) ($row['user_id'] ?? '') === (string) $userId
            && ($row['cart_signature'] ?? '') === $signature
        ) {
            $count++;
        }
    }

    return $count;
}

function cart_sms_debug($record, $reason, $extra = []) {
    $debug = array_merge([
        'checked_at' => gmdate('c'),
        'reason' => $reason,
        'event' => $record['event'] ?? null,
        'route' => $record['route'] ?? null,
        'cart_id' => $record['cart_id'] ?? null,
        'user_id' => $record['user_id'] ?? null,
        'has_phone' => !empty($record['phone']),
        'item_count' => is_array($record['items'] ?? null) ? count($record['items']) : 0
    ], $extra);

    file_put_contents(__DIR__ . '/storage/cart_sms_debug.jsonl', json_encode($debug, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function cart_build_sms_message($record) {
    $parts = [];
    foreach ($record['items'] as $item) {
        $amount = number_format((float) ($item['amount'] ?? 0), 2);
        $qty = (int) ($item['quantity'] ?? 0);
        if ($qty > 0) {
            $parts[] = 'GHS ' . $amount . ' x' . $qty;
        }
    }

    $summary = implode(', ', array_slice($parts, 0, 4));
    if (count($parts) > 4) {
        $summary .= ', more';
    }

    return 'StarOil reminder: Your fuel voucher cart is still waiting'
        . ($summary !== '' ? ' (' . $summary . ')' : '')
        . '. You were close to securing your fuel. Complete your purchase soon so you are ready when you need to refuel.';
}

function cart_send_reminder_sms($record) {
    if (($record['event'] ?? '') !== 'cart_page_left') {
        cart_sms_debug($record, 'not_cart_page_left');
        return;
    }
    if (($record['route'] ?? '') !== 'cart') {
        cart_sms_debug($record, 'not_cart_route');
        return;
    }
    if (empty($record['phone']) || empty($record['items']) || empty($record['cart_id'])) {
        cart_sms_debug($record, 'missing_phone_items_or_cart');
        return;
    }

    $signature = cart_signature($record['items']);
    $sentCount = cart_sms_sent_count($record['cart_id'], $record['user_id'], $signature);
    if ($sentCount >= 3) {
        cart_sms_debug($record, 'max_messages_reached', ['sent_count' => $sentCount]);
        return;
    }

    $message = cart_build_sms_message($record);

    if (function_exists('send_sms_arkesel')) {
        send_sms_arkesel($record['phone'], $message);
    } else {
        cart_sms_debug($record, 'sms_function_missing');
    }

    $sent = [
        'sent_at' => gmdate('c'),
        'cart_id' => $record['cart_id'],
        'user_id' => $record['user_id'],
        'phone_hash' => hash('sha256', (string) $record['phone'] . '|staroil'),
        'item_count' => count($record['items']),
        'cart_signature' => $signature,
        'message_number' => $sentCount + 1,
        'max_messages' => 3
    ];
    file_put_contents(__DIR__ . '/storage/cart_sms_sent.jsonl', json_encode($sent, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode((string) $raw, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid cart payload']);
    exit;
}

$items = $payload['items'] ?? [];
if (!is_array($items)) {
    $items = [];
}

$safeItems = [];
foreach ($items as $item) {
    if (!is_array($item)) continue;
    $safeItems[] = [
        'id' => substr((string) ($item['id'] ?? ''), 0, 80),
        'amount' => (float) ($item['amount'] ?? 0),
        'quantity' => max(0, (int) ($item['quantity'] ?? 0)),
        'status' => substr((string) ($item['status'] ?? ''), 0, 40),
        'image' => substr((string) ($item['image'] ?? ''), 0, 220),
        'stock' => max(0, (int) ($item['stock'] ?? 0))
    ];
}

$record = [
    'recorded_at' => gmdate('c'),
    'event' => substr((string) ($payload['event'] ?? 'cart_update'), 0, 40),
    'cart_id' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($payload['cartId'] ?? '')),
    'route' => substr((string) ($payload['route'] ?? ''), 0, 160),
    'subtotal' => (float) ($payload['subtotal'] ?? 0),
    'total_quantity' => (int) ($payload['totalQuantity'] ?? 0),
    'items' => $safeItems,
    'user_id' => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['name'] ?? null,
    'phone' => $_SESSION['phone'] ?? null,
    'email' => $_SESSION['email'] ?? null,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'ip_hash' => hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|staroil')
];

$file = __DIR__ . '/storage/abandoned_carts.jsonl';
$line = json_encode($record, JSON_UNESCAPED_SLASHES) . PHP_EOL;

if (file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Could not store cart event']);
    exit;
}

cart_send_reminder_sms($record);

echo json_encode(['status' => 'success']);
