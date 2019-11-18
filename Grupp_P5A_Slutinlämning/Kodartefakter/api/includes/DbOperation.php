<?php
/**********************************************************************
* DbOperation.php: The CRUD operations are performed from this file.  *
* @author Alex Giang, Sanna Roengaard, Simon Borjesson,               *
* Lukas Persson, Nikola Pajovic, Linus Forsberg                       *
***********************************************************************/

    class DbOperation  {
        // database connection link
        private $con;
  
        // class constructor
        function __construct() {
            // get the DbConnect.php file
            require_once dirname(__FILE__) . '/DbConnect.php';
  
            // create DbConnect object to connect to the database
            $db = new DbConnect();
  
            // initialize connection link of this class
            // by calling the method connect of DbConnect class
            $this->con = $db->connect();
        }
  
        /*********************************************************
        * THESE OPERATIONS WILL BE CALLED FROM THE ADMIN WEBPAGE *
        **********************************************************/

        /*
        * creates a new work shift
        */
        function createWorkShift($shift_start, $shift_end, $customer_id)  {
            $stmt = $this->con->prepare("INSERT INTO work_shift (shift_start, shift_end, customer_id) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $shift_start, $shift_end, $customer_id);
            if($stmt->execute())
                return true; 
            return false; 
        }
        
        function createSpecialWorkShift($user_id, $work_shift_id)  {
            $stmt = $this->con->prepare("INSERT INTO special_work_shift (user_id, work_shift_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $work_shift_id);
            if($stmt->execute())
                return true; 
            return false; 
        }
        
        function getLatestWorkShiftId() {
             $stmt = $this->con->prepare('SELECT work_shift_id FROM `work_shift` ORDER BY work_shift_id DESC LIMIT 1');
             $stmt->execute();
             $stmt->bind_result($work_shift_id);
             
            $work_shifts = array();
            
            while($stmt->fetch()) {
                $work_shift = array();
                $work_shift['work_shift_id'] = $work_shift_id;
                
                array_push($work_shifts, $work_shift);
            }
             
            return $work_shifts;
        }
        
        /*
        * Collects all users from the database
        */
        function getAllUsers()  {
            $stmt = $this->con->prepare("SELECT user_id, firstname, lastname FROM users ORDER by firstname");
           
            $stmt->execute();
            $stmt->bind_result($user_id, $first_name, $last_name);
            $users  = array();

            while($stmt->fetch()) {
                $user = array();
                $user['user_id'] = $user_id; 
                $user['first_name'] = $first_name; 
                $user['last_name'] = $last_name;
                
                array_push($users, $user); 
            }
 
            return $users; 
        }
        
        /*
        * Collects all customers from the database
        */
        function getAllCustomers() {
            $stmt = $this->con->prepare("SELECT customer_id, customer_name FROM customers");
           
            $stmt->execute();
            $stmt->bind_result($customer_id, $customer_name);
  
            $customers  = array();

            while($stmt->fetch()) {
                $customer = array();
                $customer['customer_id'] = $customer_id; 
                $customer['customer_name'] = $customer_name; 
                
                array_push($customers, $customer); 
            }
  
            return $customers; 
        }
        
        function getAppToken($user_id) {
            $stmt = $this->con->prepare("SELECT token FROM app_token WHERE app_token.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($token);
            
            $app_tokens = array();
            
            while($stmt->fetch()){
                $app_token = array();
                $app_token['token'] = $token;
                
                array_push($app_tokens, $app_token);
            }
            
            return $app_tokens;
        }
        
         /*
        * Collects all users with installed apps from the database
        */
        function getAllUsersWithTokens()  {
            $stmt = $this->con->prepare("SELECT users.user_id, users.firstname, users.lastname FROM users JOIN app_token ON users.user_id = app_token.user_id ORDER by users.firstname");
           
            $stmt->execute();
            $stmt->bind_result($user_id, $first_name, $last_name);
            $users  = array();

            while($stmt->fetch()) {
                $user = array();
                $user['user_id'] = $user_id; 
                $user['first_name'] = $first_name; 
                $user['last_name'] = $last_name;
                
                array_push($users, $user); 
            }
 
            return $users; 
        }
        

        /***************************************************************
        * THESE OPERATIONS WILL BE CALLED FROM THE ANDROID APPLICATION *
        ****************************************************************/
        
        function updateAppToken($app_token, $user_id){
            $stmt = $this->con->prepare("UPDATE app_token SET app_token.token = ? WHERE app_token.user_id = ?");
            $stmt->bind_param("si", $app_token, $user_id);
            if($stmt->execute())
                return true; 
            return false; 
        }
        
        function addAppToken($app_token, $user_id){
            $stmt = $this->con->prepare("INSERT INTO app_token (token, user_id) VALUES (?, ?)");
            $stmt->bind_param("si", $app_token, $user_id);
            if($stmt->execute())
                return true; 
            return false; 
        }
        
        function deleteAppToken($user_id){
            $stmt = $this->con->prepare("DELETE FROM app_token WHERE user_id = ?");
            $stmt->bind_param("i",$user_id);
            if($stmt->execute())
                return true; 
            return false; 
        }
        
        /*
        * checks if there is a user with a matching password in the database
        * returns user ID, first name and last name
        */
        function getUser($email, $password)  {
            $stmt = $this->con->prepare("SELECT user_id, password, firstname, lastname FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            
            if($stmt->execute())
            $stmt->bind_result($user_id, $db_password, $first_name, $last_name);
  
            $users  = array();
            while($stmt->fetch()) {
                $user = array();
                $user['user_id'] = $user_id; 
                $user['password'] = $db_password; 
                $user['first_name'] = $first_name; 
                $user['last_name'] = $last_name; 
            }
            array_push($users, $user); 
            
            if(password_verify($password, $db_password))
                return $users; 
            return null;
        }

        
        /*
        * gets all work shifts for a given user from the database
        */
        function getWorkShifts($user_id)  {
            $stmt = $this->con->prepare("SELECT work_shift.work_shift_id,
                work_shift.user_id, work_shift.shift_start, work_shift.shift_end, 
                customers.customer_name FROM work_shift 
                JOIN customers ON work_shift.customer_id=customers.customer_id
                WHERE work_shift.user_id = ?");
            $stmt->bind_param("i", $user_id);
            
            if($stmt->execute())
            $stmt->bind_result($work_shift_id, $user_id, $shift_start, $shift_end, $customer_name);
  
            $work_shifts = array(); 
  
            while($stmt->fetch()){
            $work_shift  = array();
            $work_shift['work_shift_id'] = $work_shift_id; 
            $work_shift['user_id'] = $user_id; 
            $work_shift['shift_start'] = $shift_start; 
            $work_shift['shift_end'] = $shift_end; 
            $work_shift['customer_name'] = $customer_name; 
  
            array_push($work_shifts, $work_shift); 
            }
  
            return $work_shifts; 
        }
        
        /*
        * gets all special work shifts for a given user from the database
        */
        
        function getSpecialWorkShifts($user_id)  {
            $stmt = $this->con->prepare("SELECT work_shift.work_shift_id, work_shift.user_id, work_shift.shift_start, work_shift.shift_end, customers.customer_name 
                FROM special_work_shift 
                JOIN work_shift ON special_work_shift.work_shift_id=work_shift.work_shift_id
                JOIN customers ON work_shift.customer_id=customers.customer_id
                WHERE special_work_shift.user_id = ?");
            $stmt->bind_param("i", $user_id);
            
            if($stmt->execute())
            $stmt->bind_result($work_shift_id, $user_id, $shift_start, $shift_end, $customer_name);
  
            $special_work_shifts = array(); 
  
            while($stmt->fetch()){
            $work_shift  = array();
            $work_shift['work_shift_id'] = $work_shift_id; 
            $work_shift['user_id'] = $user_id; 
            $work_shift['shift_start'] = $shift_start; 
            $work_shift['shift_end'] = $shift_end; 
            $work_shift['customer_name'] = $customer_name; 
  
            array_push($special_work_shifts, $work_shift); 
            }
  
            return $special_work_shifts; 
        }
        
  
        /*
        * updates a work shift to connect with the user id that has accepted the work shift
        */
        function updateWorkShift($work_shift_id, $user_id)   {
            $stmt = $this->con->prepare("UPDATE work_shift SET user_id = ? WHERE work_shift_id = ? AND user_id IS NULL");
            $stmt->bind_param("ii", $user_id, $work_shift_id);
            if($stmt->execute())
                return true; 
            return false; 
        }
        
        /*
        * Deletes all special work-shifts connected to a work shift id.
        */
        function deleteSpecialWorkShifts($work_shift_id)   {
            $stmt = $this->con->prepare("DELETE from special_work_shift WHERE work_shift_id = ?");
            $stmt->bind_param("i", $work_shift_id);
            if($stmt->execute())
                return true; 
            return false; 
        }
    }
?>