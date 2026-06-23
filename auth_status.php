<?php
require_once __DIR__ . '/includes/session_config.php';
session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$loggedIn = isset($_SESSION['user_id'], $_SESSION['phone_verify'])
    && (int) $_SESSION['phone_verify'] === 1
    && empty($_SESSION['mfa_pending']);

$otpStatus = (int) ($_SESSION['otp_status'] ?? 0);
$authenticatorStatus = (int) ($_SESSION['auth_status'] ?? 0);
$showMfaSetupReminder = $loggedIn
    && !empty($_SESSION['mfa_setup_reminder'])
    && $otpStatus !== 1
    && $authenticatorStatus !== 1;

echo json_encode([
    'status' => 'success',
    'loggedIn' => $loggedIn,
    'name' => $loggedIn ? ($_SESSION['name'] ?? '') : '',
    'otpStatus' => $loggedIn ? $otpStatus : 0,
    'authenticatorStatus' => $loggedIn ? $authenticatorStatus : 0,
    'showMfaSetupReminder' => $showMfaSetupReminder
]);

if ($showMfaSetupReminder) {
    unset($_SESSION['mfa_setup_reminder']);
}
