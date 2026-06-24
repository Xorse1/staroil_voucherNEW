<?php
require_once __DIR__ . '/session_config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once dirname(__DIR__) . '/log.php';
