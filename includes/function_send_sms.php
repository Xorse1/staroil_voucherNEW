<?php


function mnotify_sms($apiKey,$desination,$sender,$message,$toemail,$from,$subject,$endPoint,$endPoint2,$call){
  
  //call for sms and email
if($call=='1'){
        //require "classes/writetofile.php";
      
     $url = $endPoint . '?key=' . $apiKey;
    $data = [
      'recipient' => [$desination],
      'sender' => $sender,
      'message' => $message,
      'is_schedule' => 'false',
      'schedule_date' => ''
    ];

    $ch = curl_init();
    $headers = array();
    $headers[] = "Content-Type: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $result = curl_exec($ch);
    $msg_response = json_decode($result, TRUE);
    curl_close($ch);

 // $msg_status=$msg_response['status'];
  //$msg_status=$msg_response['code'];
  $msg_id =$msg_response['summary']['_id'];


$endPoint2 = $endPoint2.''.$msg_id;
    $url2 = $endPoint2 . '?key=' . $apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $result = curl_exec($ch);
    $smsdvl_response = json_decode($result, TRUE);
    curl_close($ch);

if(strpos($result, 'DELIVERED')!== false || strpos($result, 'SUBMITTED')!== false ){

    
    
    
    
}
                                 
 return $result;   
    
}
}