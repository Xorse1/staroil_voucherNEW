<?php
require_once __DIR__ . '/session_config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$loggedIn = isset($_SESSION['user_id'], $_SESSION['phone_verify'])
    && (int) $_SESSION['phone_verify'] === 1
    && empty($_SESSION['mfa_pending']);

if (!$loggedIn) {
    staroil_redirect('index');
}
