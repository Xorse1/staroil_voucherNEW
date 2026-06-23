<?php
$autoload = __DIR__ . '/vendor/autoload.php';

if (is_file($autoload)) {
    require_once $autoload;
}

if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} elseif (is_file(__DIR__ . '/.env')) {
    foreach (file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $trimmedLine = trim($line);
        if (strpos($trimmedLine, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        $_ENV[$key] = $_ENV[$key] ?? $value;
        putenv($key . '=' . $value);
    }
}

if (!function_exists('env_value')) {
    function env_value($key, $default = '') {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

$apiKey = env_value('API_KEY');
$clientSecret = env_value('CLIENT_SECRET');
$clientId = env_value('CLIENT_ID');

$hubtelAPIusername = env_value('HUBTEL_API_USERNAME');
$hubtelAPIpassword = env_value('HUBTEL_API_PASSWORD');

$add_beneficiary_api_key = env_value('ADD_BENEFICIARY_API_KEY');
$add_voucher_api_key = env_value('ADD_VOUCHER_ORDER_API_KEY');
$sms_arkesel_api_key = env_value('ARKESEL_API_KEY');

$wallet_api_base_url = rtrim(env_value('VOUCHER_WALLET_API_BASE_URL', 'https://fms.kayxappstaroil.com/APIs/voucher_api/voucher_wallet'), '/');
$wallet_api_bearer_token = env_value('VOUCHER_WALLET_BEARER_TOKEN', env_value('VOUCHER_WALLET_API_TOKEN'));
