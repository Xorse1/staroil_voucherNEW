<?php

date_default_timezone_set('Africa/Accra');


define('WAF_ENABLED', true);
define('WAF_DEBUG', false);

define('WAF_LOG_DIR', __DIR__ . '/waf_logs');
define('WAF_LOG_FILE', WAF_LOG_DIR . '/blocked.log');
define('WAF_RATE_FILE', WAF_LOG_DIR . '/rate_limit.json');
define('WAF_BAN_FILE', WAF_LOG_DIR . '/banned_ips.json');

define('WAF_MAX_POST_MB', 10);
define('WAF_DEFAULT_RATE_LIMIT', 120);
define('WAF_BAN_AFTER_BLOCKS', 8);
define('WAF_TEMP_BAN_SECONDS', 3600);

$WAF_WHITELIST_IPS = [
    // '154.xxx.xxx.xxx',
];

$WAF_BLACKLIST_IPS = [
    // '1.2.3.4',
];

$WAF_ROUTE_LIMITS = [
    'login' => 20,
    'admin' => 80,
    'api'   => 180,
    'upload'=> 30,
    'default' => WAF_DEFAULT_RATE_LIMIT,
];

$WAF_ALLOWED_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

$WAF_BLOCKED_EXTENSIONS = [
    'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
    'exe', 'sh', 'bat', 'cmd', 'com',
    'js', 'html', 'htm',
    'htaccess', 'env', 'ini', 'sql', 'pl', 'py', 'rb'
];

$WAF_ALLOWED_UPLOAD_MIME = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif',
    'application/pdf',
    'text/plain',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];



if (!WAF_ENABLED) {
    return;
}

if (!is_dir(WAF_LOG_DIR)) {
    mkdir(WAF_LOG_DIR, 0755, true);
}



function waf_ip()
{
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? 'unknown';

    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    return $ip;
}

function waf_uri()
{
    return $_SERVER['REQUEST_URI'] ?? '';
}

function waf_method()
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

function waf_user_agent()
{
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

function waf_is_cli()
{
    return PHP_SAPI === 'cli';
}

function waf_json_read($file)
{
    if (!file_exists($file)) {
        return [];
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}

function waf_json_write($file, array $data)
{
    $fp = fopen($file, 'c+');

    if (!$fp) {
        return false;
    }

    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    fwrite($fp, json_encode($data));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    return true;
}

function waf_log($reason, $data = '', $severity = 'medium')
{
    $entry = [
        'time'       => date('Y-m-d H:i:s'),
        'ip'         => waf_ip(),
        'method'     => waf_method(),
        'uri'        => waf_uri(),
        'user_agent' => waf_user_agent(),
        'reason'     => $reason,
        'severity'   => $severity,
        'data'       => is_string($data) ? substr($data, 0, 1000) : json_encode($data)
    ];

    file_put_contents(WAF_LOG_FILE, json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function waf_block($reason, $data = '', $severity = 'medium')
{
    waf_log($reason, $data, $severity);
    waf_register_block_attempt();

    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('403 Forbidden');
}

function waf_clean($value)
{
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = waf_clean($v);
        }
        return $value;
    }

    return trim((string)$value);
}

function waf_normalize($value)
{
    $value = (string)$value;
    $value = urldecode($value);
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = strtolower($value);

    return $value;
}

function waf_route_type()
{
    $uri = strtolower(waf_uri());

    if (strpos($uri, 'login') !== false) {
        return 'login';
    }

    if (strpos($uri, 'admin') !== false) {
        return 'admin';
    }

    if (strpos($uri, 'api') !== false) {
        return 'api';
    }

    if (strpos($uri, 'upload') !== false) {
        return 'upload';
    }

    return 'default';
}



function waf_security_headers()
{
    if (headers_sent()) {
        return;
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('X-XSS-Protection: 0');

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}



function waf_ip_allowed()
{
    global $WAF_WHITELIST_IPS;
    return in_array(waf_ip(), $WAF_WHITELIST_IPS, true);
}

function waf_check_blacklist()
{
    global $WAF_BLACKLIST_IPS;

    if (in_array(waf_ip(), $WAF_BLACKLIST_IPS, true)) {
        waf_block('IP permanently blacklisted', waf_ip(), 'high');
    }
}

function waf_check_temp_ban()
{
    $ip = waf_ip();
    $now = time();
    $bans = waf_json_read(WAF_BAN_FILE);

    if (isset($bans[$ip])) {
        if ($bans[$ip]['until'] > $now) {
            waf_block('IP temporarily banned', $bans[$ip], 'high');
        }

        unset($bans[$ip]);
        waf_json_write(WAF_BAN_FILE, $bans);
    }
}

function waf_register_block_attempt()
{
    $ip = waf_ip();
    $now = time();

    $bans = waf_json_read(WAF_BAN_FILE);

    if (!isset($bans[$ip])) {
        $bans[$ip] = [
            'count' => 1,
            'until' => 0,
            'last_block' => $now
        ];
    } else {
        $bans[$ip]['count']++;
        $bans[$ip]['last_block'] = $now;
    }

    if ($bans[$ip]['count'] >= WAF_BAN_AFTER_BLOCKS) {
        $bans[$ip]['until'] = $now + WAF_TEMP_BAN_SECONDS;
    }

    waf_json_write(WAF_BAN_FILE, $bans);
}



function waf_rate_limit()
{
    global $WAF_ROUTE_LIMITS;

    $ip = waf_ip();
    $route = waf_route_type();
    $limit = $WAF_ROUTE_LIMITS[$route] ?? $WAF_ROUTE_LIMITS['default'];

    $now = time();
    $window = 60;
    $key = $ip . '|' . $route;

    $data = waf_json_read(WAF_RATE_FILE);

    foreach ($data as $storedKey => $record) {
        if (($now - $record['start']) > $window) {
            unset($data[$storedKey]);
        }
    }

    if (!isset($data[$key])) {
        $data[$key] = [
            'start' => $now,
            'count' => 1,
            'route' => $route
        ];
    } else {
        $data[$key]['count']++;
    }

    waf_json_write(WAF_RATE_FILE, $data);

    if ($data[$key]['count'] > $limit) {
        waf_block('Rate limit exceeded', $data[$key], 'high');
    }
}



function waf_check_method()
{
    global $WAF_ALLOWED_METHODS;

    if (!in_array(waf_method(), $WAF_ALLOWED_METHODS, true)) {
        waf_block('HTTP method not allowed', waf_method(), 'medium');
    }
}

function waf_check_post_size()
{
    $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
    $maxBytes = WAF_MAX_POST_MB * 1024 * 1024;

    if ($contentLength > $maxBytes) {
        waf_block('POST body too large', $contentLength, 'medium');
    }
}

function waf_block_bad_user_agents()
{
    $ua = strtolower(waf_user_agent());

    $badAgents = [
        'sqlmap',
        'nikto',
        'acunetix',
        'masscan',
        'nessus',
        'wpscan',
        'dirbuster',
        'havij',
        'zgrab',
        'nmap',
        'fuzz',
        'dirsearch'
    ];

    foreach ($badAgents as $bad) {
        if ($ua !== '' && strpos($ua, $bad) !== false) {
            waf_block('Bad user agent blocked', $ua, 'high');
        }
    }
}


function waf_scan_value($value, $source = 'input')
{
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            waf_scan_value($k, $source . '_key');
            waf_scan_value($v, $source);
        }
        return;
    }

    $raw = (string)$value;

    if ($raw === '') {
        return;
    }

    $v = waf_normalize($raw);

    $patterns = [
        'SQL Injection UNION SELECT' => '/\bunion\b.{0,80}\bselect\b/i',
        'SQL Injection SELECT FROM'  => '/\bselect\b.{0,80}\bfrom\b/i',
        'SQL Injection INSERT INTO'  => '/\binsert\b.{0,80}\binto\b/i',
        'SQL Injection UPDATE SET'   => '/\bupdate\b.{0,80}\bset\b/i',
        'SQL Injection DELETE FROM'  => '/\bdelete\b.{0,80}\bfrom\b/i',
        'SQL Injection DROP TABLE'   => '/\bdrop\b.{0,80}\btable\b/i',
        'SQL Injection OR 1=1'       => '/\bor\b\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?/i',

        'XSS Script Tag'             => '/<\s*script\b/i',
        'XSS JavaScript URI'         => '/javascript\s*:/i',
        'XSS Event Handler'          => '/on(error|load|click|mouseover|focus)\s*=/i',
        'XSS Iframe'                 => '/<\s*iframe\b/i',
        'XSS Object Embed'           => '/<\s*(object|embed)\b/i',

        'Path Traversal'             => '/\.\.[\/\\\\]/',
        'Linux Password File'        => '/\/etc\/passwd/i',
        'Windows Boot File'          => '/boot\.ini/i',

        'PHP Code Execution'         => '/\b(eval|system|shell_exec|passthru|exec|proc_open|popen)\s*\(/i',
        'PHP Base64 Decode Abuse'    => '/base64_decode\s*\(/i',
        'PHP Wrapper Abuse'          => '/php:\/\/(input|filter|memory|temp)/i'
    ];

    foreach ($patterns as $name => $pattern) {
        if (preg_match($pattern, $v)) {
            waf_block($name, [
                'source' => $source,
                'value' => substr($raw, 0, 500)
            ], 'high');
        }
    }
}

function waf_scan_json_body()
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (stripos($contentType, 'application/json') === false) {
        return;
    }

    $body = file_get_contents('php://input');

    if (!$body) {
        return;
    }

    waf_scan_value($body, 'json_body');

    $json = json_decode($body, true);

    if (is_array($json)) {
        waf_scan_value($json, 'json_decoded');
    }
}



function waf_flatten_files($files)
{
    $result = [];

    foreach ($files as $field => $file) {
        if (is_array($file['name'])) {
            foreach ($file['name'] as $i => $name) {
                $result[] = [
                    'name' => $name,
                    'type' => $file['type'][$i] ?? '',
                    'tmp_name' => $file['tmp_name'][$i] ?? '',
                    'error' => $file['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $file['size'][$i] ?? 0
                ];
            }
        } else {
            $result[] = $file;
        }
    }

    return $result;
}

function waf_check_uploads()
{
    global $WAF_BLOCKED_EXTENSIONS, $WAF_ALLOWED_UPLOAD_MIME;

    if (empty($_FILES)) {
        return;
    }

    $files = waf_flatten_files($_FILES);

    foreach ($files as $file) {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            continue;
        }

        $name = $file['name'] ?? '';
        $tmp  = $file['tmp_name'] ?? '';
        $size = (int)($file['size'] ?? 0);

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (in_array($ext, $WAF_BLOCKED_EXTENSIONS, true)) {
            waf_block('Dangerous file extension blocked', $name, 'high');
        }

        if ($size > (WAF_MAX_POST_MB * 1024 * 1024)) {
            waf_block('Uploaded file too large', $name, 'medium');
        }

        if (is_uploaded_file($tmp)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmp);
            finfo_close($finfo);

            if (!in_array($mime, $WAF_ALLOWED_UPLOAD_MIME, true)) {
                waf_block('Upload MIME type blocked', [
                    'file' => $name,
                    'mime' => $mime
                ], 'high');
            }

            $sample = file_get_contents($tmp, false, null, 0, 2048);

            if (preg_match('/<\?php|<script|eval\s*\(|base64_decode\s*\(/i', $sample)) {
                waf_block('Suspicious upload content blocked', $name, 'high');
            }
        }
    }
}



function waf_run()
{
    if (waf_is_cli()) {
        return;
    }

    if (waf_ip_allowed()) {
        waf_security_headers();
        return;
    }

    $_GET = waf_clean($_GET);
    $_POST = waf_clean($_POST);
    $_COOKIE = waf_clean($_COOKIE);

    waf_security_headers();

    waf_check_blacklist();
    waf_check_temp_ban();

    waf_check_method();
    waf_check_post_size();
    waf_block_bad_user_agents();
    waf_rate_limit();

    waf_scan_value(waf_uri(), 'uri');
    waf_scan_value($_GET, 'get');
    waf_scan_value($_POST, 'post');
    waf_scan_value($_COOKIE, 'cookie');
    waf_scan_json_body();

    waf_check_uploads();
}

waf_run();