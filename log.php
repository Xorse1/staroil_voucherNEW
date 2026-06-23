<?php ob_start(); //session_start();

// File to store logs
$logFile = __DIR__ . "/visits.log";

// Collect visitor info
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$browser  = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
$page     = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';
$time     = date("Y-m-d H:i:s");
if(isset($_SESSION['email'])){
    $user = $_SESSION['email'];
}else{
    $user = 'Guest';
}

// Prepare log line
$logLine = "[$time] IP: $ip | Page: $page | Browser: $browser | User: $user" . PHP_EOL;

// Save log
file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);

/* === Basic Security Settings === */
$blockedFile = __DIR__ . '/blocked_ips.txt'; // store blocked IPs
$maxRequests = 30; // allowed requests per minute
$blockDuration = 3600; // 1 hour block

$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$uri = $_SERVER['REQUEST_URI'] ?? '';

/* === Step 1: Block common scanner user-agents === */
$badAgents = ['gobuster', 'dirbuster', 'nikto', 'sqlmap', 'acunetix', 'wpscan', 'nmap', 'curl', 'wget'];
foreach ($badAgents as $bad) {
    if (stripos($ua, $bad) !== false) {
        header('HTTP/1.1 403 Forbidden');
        exit('Access denied.');
    }
}

/* === Step 2: Block already-banned IPs === */
if (file_exists($blockedFile)) {
    $blocked = file($blockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($blocked as $line) {
        list($bip, $expires) = explode('|', $line);
        if ($bip == $ip && time() < (int)$expires) {
            header('HTTP/1.1 403 Forbidden');
            exit('Access denied.');
        }
    }
}

/* === Step 3: Simple rate-limiting in session === */
if (!isset($_SESSION['reqs'])) {
    $_SESSION['reqs'] = [];
}
$_SESSION['reqs'][] = time();

// keep only last 60 seconds
$_SESSION['reqs'] = array_filter($_SESSION['reqs'], fn($t) => $t > time() - 60);

if (count($_SESSION['reqs']) > $maxRequests) {
    // block this IP
    file_put_contents($blockedFile, "$ip|" . (time() + $blockDuration) . PHP_EOL, FILE_APPEND);
    header('HTTP/1.1 429 Too Many Requests');
    exit('Rate limit exceeded. You are temporarily blocked.');
}
$logFile = __DIR__ . '/scan_log.txt';
$logLine = date('Y-m-d H:i:s') . " | $ip | $ua | $uri\n";
file_put_contents($logFile, $logLine, FILE_APPEND);
