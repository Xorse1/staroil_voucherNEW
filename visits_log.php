<?php
require_once __DIR__ . '/includes/session_config.php';
session_start();

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

$availableDates = array_values(array_unique(array_column($allRows, 'date')));
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
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visit Patterns | StarOil Voucher System</title>
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
  <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-bold text-brand-blue">Visit Analytics</p>
        <h1 class="mt-1 text-3xl font-bold">User Pattern Report</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-brand-muted">Simple charts and a visit list generated from <code>visits.log</code>.</p>
      </div>
      <a class="rounded-ui border border-brand-line bg-white px-4 py-2.5 text-sm font-bold text-red-700" href="visits_log?logout=1">Lock Page</a>
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

    <section class="rounded-ui border border-brand-line bg-white shadow-sm">
      <div class="flex flex-col gap-2 border-b border-brand-line px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="font-bold">Visit List</h2>
          <p class="text-sm text-brand-muted">Showing <?= count($tableRows) ?> of <?= count($filteredRows) ?> visit records for the selected range.</p>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-brand-line text-left text-sm">
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
  </main>
<?php endif; ?>
</body>
</html>
