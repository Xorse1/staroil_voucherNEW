<?php include "config.php";

//$url = "https://api-approval.tingg.africa/v1/oauth/token/request";//dev
$url = "https://api.tingg.africa/v1/oauth/token/request"; //prod

$data = [
    "client_id" => $clientId ,
    "client_secret" => $clientSecret,
    "grant_type" => "client_credentials"
];

$headers = [
    "apiKey: $apiKey",
    "Content-Type: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

curl_close($ch);

//echo "Response Code: " . $httpCode . "\n";
//echo "Response Body: " . $response;

$decoded_response = json_decode($response, true);
$access_token = $decoded_response['access_token'];	


?>
