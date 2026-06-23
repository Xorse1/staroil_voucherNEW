<?php define('SECURE_ACCESS', true);
    include('config/config.php'); 

function generatePresignedUrl($contentType, $category, $stationNo, $fileName)
{   
    $API_KEY = UPLOAD_TO_AWS;
    
    $url = 'https://fms.kayxappstaroil.com/APIs/aws_presigned_url.php';

    $payload = json_encode([
        "content_type" => $contentType,
        "category"     => $category,
        "station_no"   => $stationNo,
        "file_name"    => $fileName
    ]);

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'X-API-KEY: '.$API_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return ['status' => 'error', 'message' => 'API connection failed'];
    }

    curl_close($ch);

    return json_decode($response, true);
}

?>