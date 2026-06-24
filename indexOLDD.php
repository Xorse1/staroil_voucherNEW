<?php
ob_start();

require_once __DIR__ . '/config.php';

function index_https_request() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
}

function index_allowed_redirect_hosts() {
    $hosts = [
        'app.staroil.services',
        'staroil.services',
    ];

    $currentHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $currentHost = preg_replace('/:\d+$/', '', $currentHost);
    if ($currentHost !== '' && preg_match('/(^|\.)staroil\.services$/', $currentHost)) {
        $hosts[] = $currentHost;
    }

    return array_values(array_unique($hosts));
}

function index_safe_base_url() {
    $configured = function_exists('env_value') ? env_value('APP_PUBLIC_URL', 'https://app.staroil.services') : 'https://app.staroil.services';
    $configured = rtrim((string) $configured, '/');

    if (!filter_var($configured, FILTER_VALIDATE_URL)) {
        return 'https://app.staroil.services';
    }

    $parts = parse_url($configured);
    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    $host = strtolower((string) ($parts['host'] ?? ''));

    if ($scheme !== 'https' || $host === '' || !in_array($host, index_allowed_redirect_hosts(), true)) {
        return 'https://app.staroil.services';
    }

    return $configured;
}

function index_safe_redirect($path = 'store') {
    $path = trim((string) $path);
    if ($path === '' || !preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_\/-]*(\?[a-zA-Z0-9_=&%.,:-]*)?$/', $path)) {
        $path = 'store';
    }

    $target = index_safe_base_url() . '/' . ltrim($path, '/');

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('X-Content-Type-Options: nosniff');
    header('Location: ' . $target, true, 302);
    exit;
}

if (index_https_request()) {
    index_safe_redirect('store');
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Location: store', true, 302);
exit;
