<?php
require_once __DIR__ . '/includes/auth_guard.php';
define('SECURE_ACCESS', true);
require_once __DIR__ . '/config/config.php';

$userId = $_SESSION['user_id'];
$curl = curl_init('https://fms.kayxappstaroil.com/APIs/voucher_api/update_phoneOTP_status.php?user_id=' . urlencode((string) $userId));
$data = [
    'status' => 'error',
    'message' => 'Unable to update Phone OTP status.'
];

if ($curl !== false) {
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'X-API-KEY: ' . UPDATE_BENE_PROFILE
        ],
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        $data['message'] = 'Phone OTP setup failed: ' . $error;
    } else {
        $decoded = json_decode((string) $response, true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }
}

if (($data['status'] ?? '') === 'success') {
    $_SESSION['successotpactivate'] = $data['message'] ?? 'Phone OTP setup updated successfully.';
} else {
    $_SESSION['errorotpactivate'] = $data['message'] ?? 'Phone OTP setup failed.';
}

header('Location: profile');
exit;
