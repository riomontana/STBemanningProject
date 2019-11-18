<?php

//importing required files
require_once 'DbOperation.php';
require_once 'Firebase.php';
require_once 'Push.php';

$db = new DbOperation();

$response = array();

if($_SERVER['REQUEST_METHOD']=='POST'){
    //checking the required params
    if(isset($_POST['title']) and isset($_POST['message']) and isset($_POST['user_id'])){

        //creating a new push

        $push = new Push(
            $_POST['title'],
            $_POST['message']
            );


        //getting the push from push object
        $mPushNotification = $push->getPush();

        //getting the token from database object
        $devicetoken = $db->getTokenByUserId($_POST['user_id']);

        //creating firebase class object
        $firebase = new Firebase();

        //sending push notification and displaying result
        echo $firebase->send($devicetoken, $mPushNotification);
    }else{
        $response['error']=true;
        $response['message']='Parameters missing';
    }
}else{
    $response['error']=true;
    $response['message']='Invalid request';
}

echo json_encode($response);