<?php
require_once __DIR__ . '/includes/session_config.php';
session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function activity_json_response($statusCode, array $payload) {
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function activity_clean_string($value, $maxLength = 240) {
    $value = trim((string) $value);
    $value = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return substr($value, 0, $maxLength);
}

function activity_clean_route($value) {
    $value = activity_clean_string($value, 180);
    $value = preg_replace('/[?#].*$/', '', $value);
    return $value === '' ? 'unknown' : $value;
}

function activity_clean_event(array $event) {
    $allowedTypes = [
        'page_view',
        'page_heartbeat',
        'page_leave',
        'click',
        'form_submit',
        'scroll',
        'drag_start',
        'drag_end',
        'visibility_change',
        'error'
    ];

    $type = activity_clean_string($event['type'] ?? '', 40);
    if (!in_array($type, $allowedTypes, true)) {
        return null;
    }

    $metrics = [];
    foreach (($event['metrics'] ?? []) as $key => $value) {
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $key);
        if ($key === '') continue;

        if (is_numeric($value)) {
            $metrics[$key] = round((float) $value, 2);
        } elseif (is_bool($value)) {
            $metrics[$key] = $value;
        } else {
            $metrics[$key] = activity_clean_string($value, 120);
        }
    }

    return [
        'type' => $type,
        'client_time' => activity_clean_string($event['clientTime'] ?? '', 40),
        'route' => activity_clean_route($event['route'] ?? ''),
        'path' => activity_clean_route($event['path'] ?? ''),
        'target' => activity_clean_string($event['target'] ?? '', 120),
        'target_text' => activity_clean_string($event['targetText'] ?? '', 120),
        'target_role' => activity_clean_string($event['targetRole'] ?? '', 60),
        'metrics' => $metrics
    ];
}

function activity_same_origin_request() {
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $host = preg_replace('/:\d+$/', '', $host);
    if ($host === '') {
        return true;
    }

    foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $header) {
        $value = (string) ($_SERVER[$header] ?? '');
        if ($value === '') {
            continue;
        }

        $sourceHost = strtolower((string) parse_url($value, PHP_URL_HOST));
        $sourceHost = preg_replace('/:\d+$/', '', $sourceHost);
        if ($sourceHost !== '' && $sourceHost !== $host) {
            return false;
        }
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    activity_json_response(405, ['status' => 'error', 'message' => 'Method not allowed']);
}

if (!activity_same_origin_request()) {
    activity_json_response(403, ['status' => 'error', 'message' => 'Cross-origin activity logging is not allowed']);
}

$raw = file_get_contents('php://input');
if ($raw === false || strlen($raw) > 262144) {
    activity_json_response(413, ['status' => 'error', 'message' => 'Activity payload is too large']);
}

$payload = json_decode((string) $raw, true);
if (!is_array($payload)) {
    activity_json_response(400, ['status' => 'error', 'message' => 'Invalid activity payload']);
}

$events = $payload['events'] ?? [];
if (!is_array($events)) {
    activity_json_response(400, ['status' => 'error', 'message' => 'Invalid activity event list']);
}

$events = array_slice($events, 0, 60);
$safeEvents = [];
foreach ($events as $event) {
    if (!is_array($event)) continue;
    $clean = activity_clean_event($event);
    if ($clean !== null) {
        $safeEvents[] = $clean;
    }
}

if (empty($safeEvents)) {
    activity_json_response(200, ['status' => 'success', 'stored' => 0]);
}

$storageDir = __DIR__ . '/storage/user_activity';
if (!is_dir($storageDir) && !mkdir($storageDir, 0750, true) && !is_dir($storageDir)) {
    activity_json_response(500, ['status' => 'error', 'message' => 'Could not create activity storage']);
}

$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
$record = [
    'recorded_at' => gmdate('c'),
    'visitor_id' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($payload['visitorId'] ?? '')),
    'session_id' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($payload['activitySessionId'] ?? '')),
    'page_id' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($payload['pageId'] ?? '')),
    'user_id' => $_SESSION['user_id'] ?? null,
    'user_name' => $_SESSION['name'] ?? null,
    'user_email_hash' => !empty($_SESSION['email']) ? hash('sha256', strtolower((string) $_SESSION['email']) . '|staroil') : null,
    'ip_hash' => $ip !== '' ? hash('sha256', $ip . '|staroil') : null,
    'user_agent' => activity_clean_string($_SERVER['HTTP_USER_AGENT'] ?? '', 320),
    'events' => $safeEvents
];

$file = $storageDir . '/' . gmdate('Y-m-d') . '.jsonl';
$line = json_encode($record, JSON_UNESCAPED_SLASHES) . PHP_EOL;

if (file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
    activity_json_response(500, ['status' => 'error', 'message' => 'Could not store activity']);
}

activity_json_response(200, ['status' => 'success', 'stored' => count($safeEvents)]);
