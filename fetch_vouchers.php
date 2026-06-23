<?php
ob_start();
define('SECURE_ACCESS', true);
require_once __DIR__ . '/includes/session_config.php';
session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/config/conn.php';

$sql = "
    SELECT vd.id, vd.deno, vd.images, vd.status,
           IFNULL(vc.available_vouchers, 0) AS available_vouchers
    FROM voucher_denominations_cache vd
    LEFT JOIN voucher_counts_cache vc ON vd.deno = vc.amount
    ORDER BY vd.deno ASC
";

try {
    $result = $conn->query($sql);
    $vouchers = [];
    $loggedIn = isset($_SESSION['user_id'], $_SESSION['phone_verify'])
        && (int) $_SESSION['phone_verify'] === 1;

    while ($row = $result->fetch_assoc()) {
        $row['loggedIn'] = $loggedIn;
        $vouchers[] = $row;
    }

    ob_clean();
    echo json_encode([
        'status' => 'success',
        'data' => $vouchers
    ]);
} catch (mysqli_sql_exception $exception) {
    error_log('Fetch vouchers failed: ' . $exception->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to fetch vouchers'
    ]);
} finally {
    if (isset($result) && $result instanceof mysqli_result) {
        $result->free();
    }

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
