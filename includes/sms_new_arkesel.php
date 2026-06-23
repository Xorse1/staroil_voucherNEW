<?php define('SECURE_ACCESS', true); 
include "config/config.php";

function send_sms_arkesel($to, $message){
  require_once "config.php";  
 $api_key = $sms_arkesel_api_key;   
// SEND SMS
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://sms.kayxtremetechnologies.com/api/v2/sms/send',
    CURLOPT_HTTPHEADER => ['api-key: ' . $api_key],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => http_build_query([
        'sender' => 'STAR OIL',
        'message' => $message,
        'recipients' => [$to],
        // When sending SMS to Nigerian recipients, specify the use_case field
        // 'use_case' => 'transactional'
    ]),
]);

$response = curl_exec($curl);
    
    // Decode JSON into associative array
$responseData = json_decode($response, true);

// Get the status
$status = $responseData['status'];

// Output the status
if($status == 'success'){
    'Auth code sent successfully <br>';
}else{
    'Auth code not sent';
}

curl_close($curl);

    
//echo get_response = json_encode($response, true);
    
    
}

function send_sms_backup ($phone, $message){
    require_once "config.php"; 
$api_key = MNOTIFY_API_KEY;
$endPoint = 'https://api.mnotify.com/api/sms/quick';
$apiKey = $api_key;
$url = $endPoint . '?key=' . $apiKey;

$data = [
  'recipient' => [$phone],
  'sender' => 'STAR OIL',
  'message' => $message,
  'is_schedule' => false,
  'schedule_date' => '',
  //'sms_type' => 'otp', // please do not include in payload when the perpose of the blast is not for otp
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$result = curl_exec($ch);
$result = json_decode($result, TRUE);
curl_close($ch);


}

?>


