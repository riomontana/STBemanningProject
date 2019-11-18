<?php

class DbOperation
{
    //Database connection link
    private $con;

    //Class constructor
    function __construct()
    {
        //Getting the DbConnect.php file
        require_once dirname(__FILE__) . '/DbConnect.php';

        //Creating a DbConnect object to connect to the database
        $db = new DbConnect();

        //Initializing our connection link of this class
        //by calling the method connect of DbConnect class
        $this->con = $db->connect();
    }

    //storing token in database
    public function registerDevice($user_id,$token){
        if(!$this->isUserIdExist($user_id)){
            $stmt = $this->con->prepare("INSERT INTO app_token (user_id, token) VALUES (?,?) ");
            $stmt->bind_param("is",$user_id,$token);
            if($stmt->execute())
                return 0; //return 0 means success
            return 1; //return 1 means failure
        }else{
            return 2; //returning 2 means email already exist
        }
    }

    //the method will check if email already exist
    private function isUserIdExist($user_id){
        $stmt = $this->con->prepare("SELECT id FROM app_token WHERE user_id = ?");
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    //getting all tokens to send push to all devices
    public function getAllTokens(){
        $stmt = $this->con->prepare("SELECT token FROM app_token");
        $stmt->execute();
        $result = $stmt->get_result();
        $tokens = array();
        while($token = $result->fetch_assoc()){
            array_push($tokens, $token['token']);
        }
        return $tokens;
    }

    //getting a specified token to send push to selected device
    public function getTokenByUserId($user_id){
        $stmt = $this->con->prepare("SELECT token FROM app_token WHERE user_id = ?");
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return array($result['token']);
    }

    //getting all the registered devices from database
    public function getAllDevices(){
        $stmt = $this->con->prepare("SELECT * FROM app_token");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }
}