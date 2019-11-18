<?php

require_once 'DbOperation.php';

$db = new DbOperation();

$devices = $db->getAllDevices();

$response = array();

$response['error'] = false;
$response['devices'] = array();

while($device = $devices->fetch_assoc()){
    $temp = array();
    $temp['app_token_id']=$device['app_token_id'];
    $temp['user_id']=$device['user_id'];
    $temp['token']=$device['token'];
    array_push($response['devices'],$temp);
}

echo json_encode($response);