<?php
require_once __DIR__ . '/includes/session_config.php';
session_start();
require_once __DIR__ . '/includes/frontend_log.php';

const VISITS_LOG_PASSWORD = 'Ee10252667#';

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function parse_visit_line($line) {
    $pattern = '/^\[(?<time>[^\]]+)\]\s+IP:\s+(?<ip>.*?)\s+\|\s+Page:\s+(?<page>.*?)\s+\|\s+Browser:\s+(?<browser>.*?)\s+\|\s+User:\s+(?<user>.*)$/';
    if (!preg_match($pattern, trim((string) $line), $matches)) {
        return null;
    }

    $timestamp = strtotime($matches['time']);
    if ($timestamp === false) {
        return null;
    }

    $page = trim($matches['page']);
    $pagePath = parse_url($page, PHP_URL_PATH);
    $pageName = $pagePath ?: $page;
    $pageName = preg_replace('#^/staroil_voucherNEW/?#', '/', $pageName);
    $pageName = trim($pageName, '/');
    $pageName = $pageName === '' ? 'home' : $pageName;

    return [
        'time' => $matches['time'],
        'timestamp' => $timestamp,
        'date' => date('Y-m-d', $timestamp),
        'hour' => date('H:00', $timestamp),
        'ip' => trim($matches['ip']),
        'page' => $page,
        'page_name' => $pageName,
        'browser' => trim($matches['browser']),
        'browser_family' => browser_family($matches['browser']),
        'device' => device_family($matches['browser']),
        'user' => trim($matches['user']) === '' ? 'Guest' : trim($matches['user']),
    ];
}

function browser_family($browser) {
    $browser = (string) $browser;
    if (stripos($browser, 'Edg/') !== false || stripos($browser, 'Edge/') !== false) return 'Edge';
    if (stripos($browser, 'Chrome/') !== false && stripos($browser, 'Chromium') === false) return 'Chrome';
    if (stripos($browser, 'Firefox/') !== false) return 'Firefox';
    if (stripos($browser, 'Safari/') !== false && stripos($browser, 'Chrome/') === false) return 'Safari';
    if (stripos($browser, 'Opera') !== false || stripos($browser, 'OPR/') !== false) return 'Opera';
    if (stripos($browser, 'curl') !== false) return 'curl';
    if (stripos($browser, 'UNKNOWN') !== false || trim($browser) === '') return 'Unknown';
    return 'Other';
}

function device_family($browser) {
    $browser = (string) $browser;
    if (preg_match('/Android|iPhone|iPad|iPod|Mobile|IEMobile|Opera Mini|webOS|BlackBerry/i', $browser)) {
        return 'Mobile';
    }
    if (stripos($browser, 'UNKNOWN') !== false || trim($browser) === '') {
        return 'Unknown';
    }
    return 'Desktop';
}

function count_by($rows, $key) {
    $counts = [];
    foreach ($rows as $row) {
        $value = $row[$key] ?? 'Unknown';
        $counts[$value] = ($counts[$value] ?? 0) + 1;
    }
    arsort($counts);
    return $counts;
}

function date_range_labels($startDate, $endDate) {
    $labels = [];
    $start = strtotime($startDate);
    $end = strtotime($endDate);
    if ($start === false || $end === false || $start > $end) {
        return $labels;
    }

    for ($day = $start; $day <= $end; $day = strtotime('+1 day', $day)) {
        $labels[] = date('Y-m-d', $day);
    }

    return $labels;
}

function bar_chart($title, array $data, $limit = 8) {
    $data = array_slice($data, 0, $limit, true);
    $max = max(array_values($data) ?: [1]);
    ob_start();
    ?>
    <section class="rounded-ui border border-slate-200 bg-white p-4 shadow-sm">
      <h2 class="text-base font-bold text-slate-950"><?= h($title) ?></h2>
      <div class="mt-4 space-y-3">
        <?php if (empty($data)): ?>
          <p class="text-sm text-slate-500">No data available.</p>
        <?php endif; ?>
        <?php foreach ($data as $label => $value): ?>
          <?php $width = $max > 0 ? max(4, round(($value / $max) * 100)) : 0; ?>
          <div>
            <div class="mb-1 flex items-center justify-between gap-3 text-xs font-semibold">
              <span class="truncate text-slate-700"><?= h($label) ?></span>
              <span class="text-slate-950"><?= (int) $value ?></span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
              <div class="h-full rounded-full bg-[#2178BD]" style="width: <?= $width ?>%"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

function daily_line_chart(array $dailyCounts) {
    $max = max(array_values($dailyCounts) ?: [1]);
    $points = [];
    $labels = array_keys($dailyCounts);
    $values = array_values($dailyCounts);
    $count = count($values);

    foreach ($values as $index => $value) {
        $x = $count <= 1 ? 50 : 8 + (($index / ($count - 1)) * 84);
        $y = 88 - (($value / $max) * 76);
        $points[] = round($x, 2) . ',' . round($y, 2);
    }

    ob_start();
    ?>
    <section class="rounded-ui border border-slate-200 bg-white p-4 shadow-sm lg:col-span-2">
      <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-base font-bold text-slate-950">Visits Over Time</h2>
        <p class="text-xs font-semibold text-slate-500"><?= h(reset($labels) ?: 'No date') ?> to <?= h(end($labels) ?: 'No date') ?></p>
      </div>
      <div class="mt-4 rounded-ui bg-slate-50 p-3">
        <?php if (empty($dailyCounts)): ?>
          <p class="text-sm text-slate-500">No visits found for this date range.</p>
        <?php else: ?>
          <svg viewBox="0 0 100 100" class="h-56 w-full" role="img" aria-label="Line chart showing visits over time" preserveAspectRatio="none">
            <line x1="6" y1="88" x2="96" y2="88" stroke="#CBD5E1" stroke-width="0.6" />
            <line x1="6" y1="12" x2="6" y2="88" stroke="#CBD5E1" stroke-width="0.6" />
            <polyline points="<?= h(implode(' ', $points)) ?>" fill="none" stroke="#2178BD" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />
            <?php foreach ($points as $index => $point): ?>
              <?php [$x, $y] = explode(',', $point); ?>
              <circle cx="<?= h($x) ?>" cy="<?= h($y) ?>" r="1.5" fill="#FDCD21" stroke="#2178BD" stroke-width="0.7" vector-effect="non-scaling-stroke" />
            <?php endforeach; ?>
          </svg>
          <div class="mt-2 flex justify-between text-xs font-semibold text-slate-500">
            <span><?= h($labels[0] ?? '') ?></span>
            <span>Peak: <?= (int) $max ?> visits</span>
            <span><?= h($labels[count($labels) - 1] ?? '') ?></span>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

function activity_log_dates() {
    $dates = [];
    foreach (glob(__DIR__ . '/storage/user_activity/*.jsonl') ?: [] as $file) {
        $name = basename($file, '.jsonl');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $name)) {
            $dates[] = $name;
        }
    }
    sort($dates);
    return $dates;
}

function metric_value(array $event, $key, $default = 0) {
    return $event['metrics'][$key] ?? $default;
}

function read_activity_events($startDate, $endDate) {
    $events = [];
    foreach (glob(__DIR__ . '/storage/user_activity/*.jsonl') ?: [] as $file) {
        $fileDate = basename($file, '.jsonl');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fileDate) || $fileDate < $startDate || $fileDate > $endDate) {
            continue;
        }

        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $record = json_decode($line, true);
            if (!is_array($record) || empty($record['events']) || !is_array($record['events'])) {
                continue;
            }

            foreach ($record['events'] as $event) {
                if (!is_array($event)) continue;
                $time = $event['client_time'] ?? $record['recorded_at'] ?? '';
                $timestamp = strtotime((string) $time);
                if ($timestamp === false) {
                    $timestamp = strtotime((string) ($record['recorded_at'] ?? ''));
                }
                if ($timestamp === false) continue;

                $date = date('Y-m-d', $timestamp);
                if ($date < $startDate || $date > $endDate) continue;

                $events[] = [
                    'time' => date('Y-m-d H:i:s', $timestamp),
                    'timestamp' => $timestamp,
                    'date' => $date,
                    'type' => $event['type'] ?? 'unknown',
                    'route' => $event['route'] ?? 'unknown',
                    'path' => $event['path'] ?? '',
                    'target' => $event['target'] ?? '',
                    'target_text' => $event['target_text'] ?? '',
                    'target_role' => $event['target_role'] ?? '',
                    'metrics' => is_array($event['metrics'] ?? null) ? $event['metrics'] : [],
                    'user_id' => $record['user_id'] ?? null,
                    'user_name' => $record['user_name'] ?? 'Guest',
                    'visitor_id' => $record['visitor_id'] ?? '',
                    'session_id' => $record['session_id'] ?? '',
                    'page_id' => $record['page_id'] ?? '',
                    'ip_hash' => $record['ip_hash'] ?? '',
                    'user_agent' => $record['user_agent'] ?? ''
                ];
            }
        }
    }

    usort($events, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
    return $events;
}

function average_activity_metric(array $events, $eventType, $metricKey) {
    $values = [];
    foreach ($events as $event) {
        if (($event['type'] ?? '') !== $eventType) continue;
        $value = metric_value($event, $metricKey, null);
        if (is_numeric($value)) {
            $values[] = (float) $value;
        }
    }

    return $values ? array_sum($values) / count($values) : 0;
}

function page_engagement_summary(array $events) {
    $pages = [];
    foreach ($events as $event) {
        if (($event['type'] ?? '') !== 'page_leave') continue;
        $route = $event['route'] ?: 'unknown';
        $pages[$route] ??= ['views' => 0, 'total_ms' => 0, 'active_ms' => 0, 'scroll' => 0];
        $pages[$route]['views']++;
        $pages[$route]['total_ms'] += (float) metric_value($event, 'total_time_ms', 0);
        $pages[$route]['active_ms'] += (float) metric_value($event, 'active_ms', 0);
        $pages[$route]['scroll'] += (float) metric_value($event, 'max_scroll_percent', 0);
    }

    uasort($pages, fn($a, $b) => $b['views'] <=> $a['views']);
    return $pages;
}

if (isset($_GET['logout'])) {
    unset($_SESSION['visits_log_access']);
    header('Location: visits_log');
    exit;
}

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    if (hash_equals(VISITS_LOG_PASSWORD, $password)) {
        $_SESSION['visits_log_access'] = true;
        header('Location: visits_log');
        exit;
    }

    $loginError = 'Invalid password.';
}

$authorized = !empty($_SESSION['visits_log_access']);

$logFile = __DIR__ . '/visits.log';
$allRows = [];
if ($authorized && is_file($logFile)) {
    foreach (file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $row = parse_visit_line($line);
        if ($row !== null) {
            $allRows[] = $row;
        }
    }
}

$availableDates = array_values(array_unique(array_merge(array_column($allRows, 'date'), activity_log_dates())));
sort($availableDates);
$defaultStart = $availableDates[0] ?? date('Y-m-d');
$defaultEnd = $availableDates ? $availableDates[count($availableDates) - 1] : date('Y-m-d');
$startDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['start'] ?? '') ? $_GET['start'] : $defaultStart;
$endDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['end'] ?? '') ? $_GET['end'] : $defaultEnd;

$filteredRows = array_values(array_filter($allRows, function ($row) use ($startDate, $endDate) {
    return $row['date'] >= $startDate && $row['date'] <= $endDate;
}));

usort($filteredRows, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

$dailyCounts = array_fill_keys(date_range_labels($startDate, $endDate), 0);
foreach ($filteredRows as $row) {
    $dailyCounts[$row['date']] = ($dailyCounts[$row['date']] ?? 0) + 1;
}

$pageCounts = count_by($filteredRows, 'page_name');
$browserCounts = count_by($filteredRows, 'browser_family');
$deviceCounts = count_by($filteredRows, 'device');
$hourCounts = count_by($filteredRows, 'hour');
ksort($hourCounts);

$totalVisits = count($filteredRows);
$uniqueIps = count(array_unique(array_column($filteredRows, 'ip')));
$uniqueUsers = count(array_unique(array_filter(array_column($filteredRows, 'user'), fn($user) => $user !== 'Guest')));
$guestVisits = count(array_filter($filteredRows, fn($row) => $row['user'] === 'Guest'));
$topPage = $pageCounts ? array_key_first($pageCounts) : 'None';
$tableRows = array_slice($filteredRows, 0, 500);

$activityEvents = $authorized ? read_activity_events($startDate, $endDate) : [];
$activityTypeCounts = count_by($activityEvents, 'type');
$activityRouteCounts = count_by($activityEvents, 'route');
$activityClickTargets = [];
foreach ($activityEvents as $event) {
    if (($event['type'] ?? '') !== 'click') continue;
    $label = trim(($event['target_text'] ?: $event['target']) ?: 'Unlabelled click');
    $activityClickTargets[$label] = ($activityClickTargets[$label] ?? 0) + 1;
}
arsort($activityClickTargets);
$activityTableRows = array_slice($activityEvents, 0, 500);
$pageEngagement = page_engagement_summary($activityEvents);
$engagementRows = array_slice($pageEngagement, 0, 12, true);
$totalActivityEvents = count($activityEvents);
$uniqueActivitySessions = count(array_unique(array_filter(array_column($activityEvents, 'session_id'))));
$uniqueActivityVisitors = count(array_unique(array_filter(array_column($activityEvents, 'visitor_id'))));
$avgPageSeconds = round(average_activity_metric($activityEvents, 'page_leave', 'total_time_ms') / 1000, 1);
$avgActiveSeconds = round(average_activity_metric($activityEvents, 'page_leave', 'active_ms') / 1000, 1);
$avgScroll = round(average_activity_metric($activityEvents, 'page_leave', 'max_scroll_percent'), 1);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visit Patterns | StarOil Voucher System</title>
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{brand:{blue:"#2178BD",yellow:"#FDCD21",ink:"#15253A",muted:"#64748B",line:"#D8E0EA",soft:"#F5F8FB"}},fontFamily:{sans:["Instrument Sans","ui-sans-serif","system-ui","sans-serif"]},borderRadius:{ui:"8px"}}}}</script>
  <style>
    .dt-container {
      padding: 1rem;
      font-family: Instrument Sans, ui-sans-serif, system-ui, sans-serif;
      color: #15253A;
    }

    .dt-container .dt-search input,
    .dt-container .dt-length select {
      border: 1px solid #D8E0EA;
      border-radius: 8px;
      padding: 0.45rem 0.7rem;
      outline: none;
    }

    .dt-container .dt-search input:focus,
    .dt-container .dt-length select:focus {
      border-color: #2178BD;
      box-shadow: 0 0 0 3px rgba(33, 120, 189, 0.15);
    }

    .dt-container .dt-paging .dt-paging-button {
      border-radius: 8px !important;
      border: 1px solid #D8E0EA !important;
      margin: 0 2px;
      padding: 0.35rem 0.7rem !important;
    }

    .dt-container .dt-paging .dt-paging-button.current {
      background: #2178BD !important;
      color: #fff !important;
      border-color: #2178BD !important;
    }

    @keyframes skeleton-pulse {
      0%, 100% { opacity: .52; }
      50% { opacity: 1; }
    }

    .refresh-shell {
      position: fixed;
      inset: 0;
      z-index: 80;
      display: none;
      align-items: flex-start;
      justify-content: center;
      background: rgba(245, 248, 251, .72);
      backdrop-filter: blur(2px);
      padding: 88px 16px 16px;
    }

    .refresh-shell.is-visible {
      display: flex;
    }

    .skeleton-panel {
      width: min(960px, 100%);
      border: 1px solid #D8E0EA;
      border-radius: 8px;
      background: #fff;
      box-shadow: 0 18px 42px rgba(21, 37, 58, .14);
      padding: 18px;
    }

    .skeleton-line,
    .skeleton-card {
      border-radius: 8px;
      background: linear-gradient(90deg, #E8EEF5 25%, #F7FAFC 37%, #E8EEF5 63%);
      background-size: 400% 100%;
      animation: skeleton-pulse 1.15s ease-in-out infinite;
    }

    .skeleton-line {
      height: 12px;
    }

    .skeleton-card {
      height: 78px;
    }

    .refresh-status {
      position: fixed;
      right: 16px;
      bottom: 16px;
      z-index: 70;
      border: 1px solid #D8E0EA;
      border-radius: 999px;
      background: rgba(255,255,255,.94);
      color: #15253A;
      box-shadow: 0 10px 28px rgba(21, 37, 58, .12);
      padding: 8px 12px;
      font-size: 12px;
      font-weight: 800;
    }
  </style>
</head>
<body class="min-h-screen bg-brand-soft font-sans text-brand-ink">
<?php if (!$authorized): ?>
  <main class="mx-auto flex min-h-screen max-w-md items-center px-4 py-10">
    <section class="w-full rounded-ui border border-brand-line bg-white p-6 shadow-sm">
      <img class="h-10 w-auto" src="images/alogo_light.png" alt="StarOil logo">
      <h1 class="mt-6 text-2xl font-bold">Visit Pattern Access</h1>
      <p class="mt-2 text-sm leading-6 text-brand-muted">Enter the access password to view web app visit charts and logs.</p>
      <?php if ($loginError !== ''): ?>
        <div class="mt-4 rounded-ui border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800"><?= h($loginError) ?></div>
      <?php endif; ?>
      <form class="mt-5 space-y-4" method="POST" action="visits_log">
        <label class="block">
          <span class="text-sm font-semibold">Password</span>
          <input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-blue" type="password" name="password" required autofocus>
        </label>
        <button class="w-full rounded-ui bg-brand-blue px-4 py-2.5 text-sm font-bold text-white" type="submit">View Visits</button>
      </form>
    </section>
  </main>
<?php else: ?>
  <div id="visits-refresh-skeleton" class="refresh-shell" aria-live="polite" aria-hidden="true">
    <div class="skeleton-panel">
      <div class="mb-5 flex items-center justify-between gap-4">
        <div class="w-full max-w-sm space-y-3">
          <div class="skeleton-line w-24"></div>
          <div class="skeleton-line w-64"></div>
          <div class="skeleton-line w-full"></div>
        </div>
        <div class="skeleton-line hidden w-28 sm:block"></div>
      </div>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
      </div>
      <div class="mt-5 grid gap-4 lg:grid-cols-2">
        <div class="skeleton-card" style="height:180px"></div>
        <div class="skeleton-card" style="height:180px"></div>
      </div>
    </div>
  </div>
  <div id="visits-refresh-status" class="refresh-status">Auto refresh: 60s</div>
  <main id="visits-dashboard" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-bold text-brand-blue">Visit Analytics</p>
        <h1 class="mt-1 text-3xl font-bold">User Pattern Report</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-muted">One protected dashboard for <code>visits.log</code> page visits and detailed user activity captured in <code>storage/user_activity</code>.</p>
      </div>
      <div class="flex flex-col gap-2 sm:flex-row">
        <a class="rounded-ui bg-brand-yellow px-4 py-2.5 text-sm font-bold text-brand-ink" href="marketing_sms">SMS Campaigns</a>
        <a class="rounded-ui border border-brand-line bg-white px-4 py-2.5 text-sm font-bold text-red-700" href="visits_log?logout=1">Lock Page</a>
      </div>
    </header>

    <form class="mb-6 grid gap-3 rounded-ui border border-brand-line bg-white p-4 shadow-sm sm:grid-cols-[1fr_1fr_auto]" method="GET" action="visits_log">
      <label>
        <span class="text-sm font-semibold text-brand-muted">Start date</span>
        <input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm" type="date" name="start" value="<?= h($startDate) ?>">
      </label>
      <label>
        <span class="text-sm font-semibold text-brand-muted">End date</span>
        <input class="mt-1 w-full rounded-ui border border-brand-line px-3 py-2.5 text-sm" type="date" name="end" value="<?= h($endDate) ?>">
      </label>
      <button class="self-end rounded-ui bg-brand-yellow px-5 py-2.5 text-sm font-bold text-brand-ink" type="submit">Filter</button>
    </form>

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
      <article class="rounded-ui border border-brand-line bg-white p-4 shadow-sm"><p class="text-sm font-bold text-brand-muted">Total Visits</p><p class="mt-2 text-3xl font-bold"><?= (int) $totalVisits ?></p></article>
      <article class="rounded-ui border border-brand-line bg-white p-4 shadow-sm"><p class="text-sm font-bold text-brand-muted">Unique IPs</p><p class="mt-2 text-3xl font-bold"><?= (int) $uniqueIps ?></p></article>
      <article class="rounded-ui border border-brand-line bg-white p-4 shadow-sm"><p class="text-sm font-bold text-brand-muted">Known Users</p><p class="mt-2 text-3xl font-bold"><?= (int) $uniqueUsers ?></p></article>
      <article class="rounded-ui border border-brand-line bg-white p-4 shadow-sm"><p class="text-sm font-bold text-brand-muted">Guest Visits</p><p class="mt-2 text-3xl font-bold"><?= (int) $guestVisits ?></p></article>
      <article class="rounded-ui border border-brand-line bg-white p-4 shadow-sm"><p class="text-sm font-bold text-brand-muted">Top Page</p><p class="mt-2 truncate text-xl font-bold" title="<?= h($topPage) ?>"><?= h($topPage) ?></p></article>
    </section>

    <section class="mb-6 grid gap-4 lg:grid-cols-2">
      <?= daily_line_chart($dailyCounts) ?>
      <?= bar_chart('Most Visited Pages', $pageCounts, 8) ?>
      <?= bar_chart('Browser Pattern', $browserCounts, 6) ?>
      <?= bar_chart('Device Pattern', $deviceCounts, 6) ?>
      <?= bar_chart('Visits by Hour', $hourCounts, 24) ?>
    </section>

    <section class="mb-6">
      <div class="mb-4">
        <p class="text-sm font-bold text-brand-blue">Detailed Activity</p>
        <h2 class="mt-1 text-2xl font-bold">What Users Actually Do</h2>
        <p class="mt-2 text-sm leading-6 text-brand-muted">This combines clicks, scrolling, page time, form submits, drag actions, and page-leave events captured by the browser tracker.</p>
      </div>

      <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <article class="rounded-ui border border-brand-line bg-white p-4 shadow-sm"><p class="text-sm font-bold text-brand-muted">Activity Events</p><p class="mt-2 text-3xl font-bold"><?= (int) $totalActivityEvents ?></p></article>
        <article class="rounded-ui border border-brand-line bg-white p-4 shadow-sm"><p class="text-sm font-bold text-brand-muted">Tracked Sessions</p><p class="mt-2 text-3xl font-bold"><?= (int) $uniqueActivitySessions ?></p></article>
        <article class="rounded-ui border border-brand-line bg-white p-4 shadow-sm"><p class="text-sm font-bold text-brand-muted">Tracked Visitors</p><p class="mt-2 text-3xl font-bold"><?= (int) $uniqueActivityVisitors ?></p></article>
        <article class="rounded-ui border border-brand-line bg-white p-4 shadow-sm"><p class="text-sm font-bold text-brand-muted">Avg Page Time</p><p class="mt-2 text-3xl font-bold"><?= h($avgPageSeconds) ?>s</p><p class="mt-1 text-xs font-semibold text-brand-muted">Active: <?= h($avgActiveSeconds) ?>s</p></article>
        <article class="rounded-ui border border-brand-line bg-white p-4 shadow-sm"><p class="text-sm font-bold text-brand-muted">Avg Scroll Depth</p><p class="mt-2 text-3xl font-bold"><?= h($avgScroll) ?>%</p></article>
      </div>

      <div class="grid gap-4 lg:grid-cols-2">
        <?= bar_chart('Activity Types', $activityTypeCounts, 10) ?>
        <?= bar_chart('Most Active Pages', $activityRouteCounts, 10) ?>
        <?= bar_chart('Most Clicked Items', $activityClickTargets, 10) ?>
      </div>
    </section>

    <section class="mb-6 rounded-ui border border-brand-line bg-white shadow-sm">
      <div class="border-b border-brand-line px-4 py-3">
        <h2 class="font-bold">Page Engagement Summary</h2>
        <p class="text-sm text-brand-muted">Average time, active time, and scroll depth by page. Based on page-leave events.</p>
      </div>
      <div class="overflow-x-auto">
        <table id="page-engagement-table" class="min-w-full divide-y divide-brand-line text-left text-sm">
          <thead class="bg-slate-50 text-xs font-bold uppercase text-brand-muted">
            <tr>
              <th class="px-4 py-3">Page</th>
              <th class="px-4 py-3">Completed Views</th>
              <th class="px-4 py-3">Avg Total Time</th>
              <th class="px-4 py-3">Avg Active Time</th>
              <th class="px-4 py-3">Avg Scroll</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-brand-line">
            <?php if (empty($engagementRows)): ?>
              <tr><td class="px-4 py-8 text-center text-brand-muted" colspan="5">No page engagement data found for this date range.</td></tr>
            <?php endif; ?>
            <?php foreach ($engagementRows as $page => $row): ?>
              <?php $views = max(1, (int) $row['views']); ?>
              <tr>
                <td class="px-4 py-3 font-semibold"><?= h($page) ?></td>
                <td class="whitespace-nowrap px-4 py-3"><?= (int) $row['views'] ?></td>
                <td class="whitespace-nowrap px-4 py-3"><?= h(round(($row['total_ms'] / $views) / 1000, 1)) ?>s</td>
                <td class="whitespace-nowrap px-4 py-3"><?= h(round(($row['active_ms'] / $views) / 1000, 1)) ?>s</td>
                <td class="whitespace-nowrap px-4 py-3"><?= h(round($row['scroll'] / $views, 1)) ?>%</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="rounded-ui border border-brand-line bg-white shadow-sm">
      <div class="flex flex-col gap-2 border-b border-brand-line px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="font-bold">Visit List</h2>
          <p class="text-sm text-brand-muted">Showing <?= count($tableRows) ?> of <?= count($filteredRows) ?> visit records for the selected range.</p>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table id="visit-list-table" class="min-w-full divide-y divide-brand-line text-left text-sm">
          <thead class="bg-slate-50 text-xs font-bold uppercase text-brand-muted">
            <tr>
              <th class="px-4 py-3">Time</th>
              <th class="px-4 py-3">User</th>
              <th class="px-4 py-3">Page</th>
              <th class="px-4 py-3">IP</th>
              <th class="px-4 py-3">Device</th>
              <th class="px-4 py-3">Browser</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-brand-line">
            <?php if (empty($tableRows)): ?>
              <tr><td class="px-4 py-8 text-center text-brand-muted" colspan="6">No visits found for this date range.</td></tr>
            <?php endif; ?>
            <?php foreach ($tableRows as $row): ?>
              <tr>
                <td class="whitespace-nowrap px-4 py-3 font-semibold"><?= h($row['time']) ?></td>
                <td class="whitespace-nowrap px-4 py-3"><?= h($row['user']) ?></td>
                <td class="max-w-sm px-4 py-3"><span class="block truncate" title="<?= h($row['page']) ?>"><?= h($row['page_name']) ?></span></td>
                <td class="whitespace-nowrap px-4 py-3"><?= h($row['ip']) ?></td>
                <td class="whitespace-nowrap px-4 py-3"><?= h($row['device']) ?></td>
                <td class="px-4 py-3"><span class="block max-w-xs truncate" title="<?= h($row['browser']) ?>"><?= h($row['browser_family']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="mt-6 rounded-ui border border-brand-line bg-white shadow-sm">
      <div class="flex flex-col gap-2 border-b border-brand-line px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="font-bold">Detailed Activity List</h2>
          <p class="text-sm text-brand-muted">Showing <?= count($activityTableRows) ?> of <?= count($activityEvents) ?> activity events for the selected range.</p>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table id="activity-list-table" class="min-w-full divide-y divide-brand-line text-left text-sm">
          <thead class="bg-slate-50 text-xs font-bold uppercase text-brand-muted">
            <tr>
              <th class="px-4 py-3">Time</th>
              <th class="px-4 py-3">Event</th>
              <th class="px-4 py-3">Page</th>
              <th class="px-4 py-3">Target</th>
              <th class="px-4 py-3">User</th>
              <th class="px-4 py-3">Useful Metrics</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-brand-line">
            <?php if (empty($activityTableRows)): ?>
              <tr><td class="px-4 py-8 text-center text-brand-muted" colspan="6">No activity events found for this date range.</td></tr>
            <?php endif; ?>
            <?php foreach ($activityTableRows as $event): ?>
              <?php
                $metrics = $event['metrics'];
                $metricParts = [];
                foreach (['elapsed_ms', 'active_ms', 'total_time_ms', 'max_scroll_percent', 'scroll_percent', 'cart_items', 'duration_ms'] as $metricKey) {
                    if (isset($metrics[$metricKey])) {
                        $label = str_replace('_', ' ', $metricKey);
                        $value = $metrics[$metricKey];
                        if (substr($metricKey, -3) === '_ms') {
                            $value = round(((float) $value) / 1000, 1) . 's';
                        } elseif (strpos($metricKey, 'percent') !== false) {
                            $value = $value . '%';
                        }
                        $metricParts[] = $label . ': ' . $value;
                    }
                }
              ?>
              <tr>
                <td class="whitespace-nowrap px-4 py-3 font-semibold"><?= h($event['time']) ?></td>
                <td class="whitespace-nowrap px-4 py-3"><span class="rounded-full bg-brand-soft px-2.5 py-1 text-xs font-bold"><?= h($event['type']) ?></span></td>
                <td class="whitespace-nowrap px-4 py-3"><?= h($event['route']) ?></td>
                <td class="max-w-xs px-4 py-3"><span class="block truncate" title="<?= h(trim($event['target'] . ' ' . $event['target_text'])) ?>"><?= h($event['target_text'] ?: $event['target'] ?: 'N/A') ?></span></td>
                <td class="whitespace-nowrap px-4 py-3"><?= h($event['user_name'] ?: 'Guest') ?></td>
                <td class="max-w-md px-4 py-3 text-xs text-brand-muted"><?= h(implode(' | ', $metricParts) ?: 'N/A') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
<?php endif; ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script>
  const visitsRefresh = {
    intervalMs: 60000,
    timer: null,
    refreshing: false,
    lastUpdatedAt: new Date()
  };

  function visitsSharedDataTableOptions() {
    return {
      pageLength: 25,
      lengthMenu: [10, 25, 50, 100],
      autoWidth: false,
      responsive: false,
      language: {
        search: "Search:",
        lengthMenu: "Show _MENU_ rows",
        info: "Showing _START_ to _END_ of _TOTAL_ rows",
        emptyTable: "No records available"
      }
    };
  }

  function initVisitDataTables() {
    if (!window.DataTable) return;

    [
      ["#page-engagement-table", 1, "desc"],
      ["#visit-list-table", 0, "desc"],
      ["#activity-list-table", 0, "desc"]
    ].forEach(function (config) {
      const table = document.querySelector(config[0]);
      if (!table || table.dataset.datatableReady === "true") return;
      table.dataset.datatableReady = "true";
      new DataTable(table, {
        ...visitsSharedDataTableOptions(),
        order: [[config[1], config[2]]]
      });
    });
  }

  function destroyVisitDataTables() {
    if (!window.DataTable || !window.jQuery?.fn?.dataTable) return;

    ["#page-engagement-table", "#visit-list-table", "#activity-list-table"].forEach(function (selector) {
      const table = document.querySelector(selector);
      if (!table || table.dataset.datatableReady !== "true") return;
      if (jQuery.fn.dataTable.isDataTable(table)) {
        jQuery(table).DataTable().destroy();
      }
      delete table.dataset.datatableReady;
    });
  }

  function setRefreshLoading(loading) {
    const skeleton = document.getElementById("visits-refresh-skeleton");
    if (skeleton) {
      skeleton.classList.toggle("is-visible", loading);
      skeleton.setAttribute("aria-hidden", loading ? "false" : "true");
    }
  }

  function updateRefreshStatus(message) {
    const status = document.getElementById("visits-refresh-status");
    if (status) status.textContent = message;
  }

  async function refreshVisitsDashboard() {
    if (visitsRefresh.refreshing || document.hidden) return;
    const dashboard = document.getElementById("visits-dashboard");
    if (!dashboard) return;

    visitsRefresh.refreshing = true;
    setRefreshLoading(true);
    updateRefreshStatus("Refreshing...");

    try {
      const url = new URL(window.location.href);
      url.searchParams.set("_refresh", String(Date.now()));
      const response = await fetch(url.toString(), {
        headers: { "X-Requested-With": "XMLHttpRequest" },
        cache: "no-store",
        credentials: "same-origin"
      });
      if (!response.ok) throw new Error("Refresh failed");

      const html = await response.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");
      const nextDashboard = doc.getElementById("visits-dashboard");
      if (!nextDashboard) throw new Error("Dashboard not found");

      destroyVisitDataTables();
      dashboard.innerHTML = nextDashboard.innerHTML;
      initVisitDataTables();
      visitsRefresh.lastUpdatedAt = new Date();
      updateRefreshStatus("Updated " + visitsRefresh.lastUpdatedAt.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) + " | Auto refresh: 60s");
    } catch (error) {
      updateRefreshStatus("Auto refresh paused after error");
    } finally {
      setRefreshLoading(false);
      visitsRefresh.refreshing = false;
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    initVisitDataTables();
    updateRefreshStatus("Updated now | Auto refresh: 60s");
    visitsRefresh.timer = setInterval(refreshVisitsDashboard, visitsRefresh.intervalMs);
  });
</script>
</body>
</html>
