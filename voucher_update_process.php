<?php
ob_start();
require_once __DIR__ . '/includes/session_config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/includes/helper.php';

if (empty($_SESSION['user_id']) || (int) ($_SESSION['phone_verify'] ?? 0) !== 1 || !empty($_SESSION['mfa_pending'])) {
    staroil_redirect('index');
}

function voucher_update_redirect($voucherId = '') {
    header('Location: ' . ($voucherId !== '' ? 'voucher_update?title=' . urlencode($voucherId) : 'vouchers'));
    exit;
}

if (!isset($_POST['update_voucher'])) {
    header('Location: vouchers');
    exit;
}

$startDate = sanitize($_POST['start_date'] ?? '');
$expiryDate = sanitize($_POST['expiry_date'] ?? '');
$status = sanitize($_POST['status'] ?? '0');
$voucherId = sanitize($_POST['voucher_id'] ?? '');
$beneficiaryId = sanitize((string) $_SESSION['user_id']);
$recipientName = sanitize($_POST['rec_name'] ?? '');
$recipientPhotoUrl = '';

if ($voucherId === '') {
    $_SESSION['successerrorupdated'] = 'Voucher ID is required.';
    voucher_update_redirect();
}

if (isset($_FILES['rec_photo']) && $_FILES['rec_photo']['error'] === UPLOAD_ERR_OK) {
    $presignedHelper = __DIR__ . '/includes/function_get_presigned_url.php';
    if (!is_file($presignedHelper)) {
        $_SESSION['successerrorupdated'] = 'Image upload helper is not configured.';
        voucher_update_redirect($voucherId);
    }

    require_once $presignedHelper;

    $tmpName = $_FILES['rec_photo']['tmp_name'];
    $fileName = basename($_FILES['rec_photo']['name']);
    $fileSize = (int) $_FILES['rec_photo']['size'];
    $maxSize = 5 * 1024 * 1024;

    if ($fileSize > $maxSize) {
        $_SESSION['successerrorupdated'] = 'Image must not exceed 5MB.';
        voucher_update_redirect($voucherId);
    }

    $imageInfo = getimagesize($tmpName);
    if ($imageInfo === false) {
        $_SESSION['successerrorupdated'] = 'Invalid image file.';
        voucher_update_redirect($voucherId);
    }

    $fileType = $imageInfo['mime'];
    $width = (int) $imageInfo[0];
    $height = (int) $imageInfo[1];

    if (!in_array($fileType, ['image/jpeg', 'image/png'], true)) {
        $_SESSION['successerrorupdated'] = 'Only JPG and PNG images are allowed.';
        voucher_update_redirect($voucherId);
    }

    if ($width < 300 || $height < 300) {
        $_SESSION['successerrorupdated'] = 'Image resolution is too low. Please upload a clear passport-size photo.';
        voucher_update_redirect($voucherId);
    }

    if (($width / $height) > 1.2) {
        $_SESSION['successerrorupdated'] = 'Please upload a portrait or square passport-style photo.';
        voucher_update_redirect($voucherId);
    }

    $presigned = generatePresignedUrl($fileType, 'Voucher_Gift_Recipient_', 'voucherID_' . $voucherId, $fileName);
    if (!$presigned || ($presigned['status'] ?? '') !== 'success') {
        $_SESSION['successerrorupdated'] = 'Failed to prepare image upload.';
        voucher_update_redirect($voucherId);
    }

    $uploadUrl = $presigned['upload_url'];
    $recipientPhotoUrl = $presigned['file_url'];
    $fileHandle = fopen($tmpName, 'r');
    $ch = curl_init($uploadUrl);
    curl_setopt_array($ch, [
        CURLOPT_PUT => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: ' . $fileType],
        CURLOPT_INFILE => $fileHandle,
        CURLOPT_INFILESIZE => $fileSize,
        CURLOPT_TIMEOUT => 60
    ]);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fileHandle);

    if (!in_array($httpCode, [200, 201, 204], true)) {
        $_SESSION['successerrorupdated'] = 'Image upload failed. Please try again.';
        voucher_update_redirect($voucherId);
    }
}

$apiUrl = 'https://fms.kayxappstaroil.com/APIs/voucher_api/update_voucher_status.php';
$payload = [
    'start_date' => $startDate,
    'expiry_date' => $expiryDate,
    'status' => $status,
    'id' => $voucherId,
    'beneficiary_id' => $beneficiaryId,
    'rec_name' => $recipientName,
    'rec_photo' => $recipientPhotoUrl
];

$json = json_encode($payload);
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $json,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json)
    ],
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    $_SESSION['successerrorupdated'] = 'Voucher update failed: ' . $curlError;
    voucher_update_redirect($voucherId);
}

$responseData = json_decode((string) $response, true);
if (is_array($responseData) && ($responseData['status'] ?? '') === 'success') {
    $_SESSION['successupdated'] = $responseData['message'] ?? 'Voucher updated successfully.';
    header('Location: vouchers');
    exit;
}

$_SESSION['successerrorupdated'] = is_array($responseData) ? ($responseData['message'] ?? 'Voucher update failed.') : 'Voucher update failed.';
voucher_update_redirect($voucherId);
