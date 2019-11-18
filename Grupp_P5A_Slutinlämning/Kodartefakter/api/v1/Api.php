<?php
/*********************************************************
 * Api.php: The API calls are processed from this file.   *
 * @author Alex Giang, Sanna Roengaard, Simon Borjesson,  *
 * Lukas Persson, Nikola Pajovic, Linus Forsberg          *
 **********************************************************/

// get the dboperation class
require_once '../includes/DbOperation.php';

// function validating all the parameters are available
// we will pass the required parameters to this function 
function isTheseParametersAvailable($params)    {
    // assuming all parameters are available 
    $available = true;
    $missingparams = "";

   // foreach($params as $param)  {
    //    if(!isset($_POST[$param]) || strlen($_POST[$param])<=0) {
     //       $available = false;
      //      $missingparams = $missingparams . ", " . $param;
    //    }
    //}

    // if parameters are missing 
    if(!$available) {
        $response = array();
        $response['error'] = true;
        $response['message'] = 'Parameters ' . substr($missingparams, 1, strlen($missingparams)) . ' missing';

        // display error
        echo json_encode($response);

        // stop further execution
        die();
    }
}

// an array to display response 
$response = array();

// if it is an api call 
// that means a get parameter named api call is set in the URL 
// and with this parameter we are concluding that it is an api call
if(isset($_GET['apicall'])) {

    switch($_GET['apicall'])    {

        // if the api call value is 'createWorkShift' create a record in the database
        case 'createWorkShift':
            // check if the parameters required for this request are available or not 
            isTheseParametersAvailable(array('shift_start','shift_end','customer_id'));

            // create a new dboperation object
            $db = new DbOperation();

            // create a new record in the database
            $result = $db->createWorkShift(
                $_POST['shift_start'],
                $_POST['shift_end'],
                $_POST['customer_id']
            );

            // if the record is created adding success to response
            if($result) {
                // record is created means there is no error
                $response['work_shifts'] = $db->getLatestWorkShiftId();

                // in message we have a success message
                //$response['message'] = 'work shift added successfully';
            } else   {
                //if record is not added that means there is an error 
                $response['error'] = true;
                $response['message'] = 'Some error occurred please try again';
            }

            break;

        case 'createSpecialWorkShift':
            isTheseParametersAvailable(array('user_id','work_shift_id'));

            // create a new dboperation object
            $db = new DbOperation();

            // create a new record in the database
            $result = $db->createSpecialWorkShift(
                $_POST['user_id'],
                $_POST['work_shift_id']
            );

            // if the record is created adding success to response
            if($result) {
                // record is created means there is no error
                $response['error'] = false;

                // in message we have a success message
                $response['message'] = 'special work shift added successfully';
            } else   {
                //if record is not added that means there is an error 
                $response['error'] = true;
                $response['message'] = 'Some error occurred please try again';
            }

            break;

        case 'getLatestWorkShiftId':
            $db = new DbOperation();
            $response['work_shifts'] = $db->getLatestWorkShiftId();

            break;

        case 'getAllUsers':
            $db = new DbOperation();
            $response['users'] = $db->getAllUsers();

            break;
            
        case 'getAllUsersWithTokens':
            $db = new DbOperation();
            $response['users'] = $db->getAllUsersWithTokens();

            break;

        case 'getAllCustomers':
            $db = new DbOperation();
            $response['customers'] = $db->getAllCustomers();

            break;

        //if the call is getWorkShifts
        case 'getWorkShifts':
            isTheseParametersAvailable(array('user_id'));
            $db = new DbOperation();

            $response['work_shifts'] = $db->getWorkShifts(
                $_POST['user_id']
            );
            break;

        case 'getSpecialWorkShifts':
            isTheseParametersAvailable(array('user_id'));
            $db = new DbOperation();

            $response['special_work_shifts'] = $db->getSpecialWorkShifts(
               $_POST['user_id']
            );
            break;
            
        case 'getAppToken':
            isTheseParametersAvailable(array('user_id'));
            $db = new DbOperation();

            $response['token'] = $db->getAppToken(
                $_POST['user_id']
            );
            break;

        // if the call is getUser
        case 'getUser':
            isTheseParametersAvailable(array('email', 'password'));
            $db = new DbOperation();

            $response['user'] = $db->getUser(
                $_POST['email'],
                $_POST['password']
            );
            break;

        case 'updateAppToken':
            isTheseParametersAvailable('token', 'user_id');
                    $db = new DbOperation();
                    
                    $result = $db->updateAppToken(
                        $_POST['token'],
                        $_POST['user_id']
                    );
                        
                        if($result){
                            $response['error'] = false;
                            $response['message'] = 'App token updated successfully';
                        }else{
                            $response['error'] = true;
                            $response['message'] = 'Some error occurred token is not updated';
                        }
            break;
        
        case 'addAppToken':
            isTheseParametersAvailable('token', 'user_id');
                    $db = new DbOperation();
                    
                    $result = $db->AddAppToken(
                        $_POST['token'],
                        $_POST['user_id']
                    );
                        
                        if($result){
                            $response['error'] = false;
                            $response['message'] = 'App token added successfully';
                        }else{
                            $response['error'] = true;
                            $response['message'] = 'Some error occurred token is not updated';
                        }
            break;
        
         case 'deleteAppToken':
            isTheseParametersAvailable('user_id');
                    $db = new DbOperation();
                    
                    $result = $db->deleteAppToken(
                        $_POST['user_id']
                    );
                        
                        if($result){
                            $response['error'] = false;
                            $response['message'] = 'App token deleted successfully';
                        }else{
                            $response['error'] = true;
                            $response['message'] = 'Some error occurred token is not updated';
                        }
            break;
            
        // if the call is updateWorkShift
        case 'updateWorkShift':
            isTheseParametersAvailable(array('work_shift_id','user_id'));
            $db = new DbOperation();
            $result = $db->updateWorkShift(
                $_POST['work_shift_id'],
                $_POST['user_id']
            );
            break;

        case 'deleteSpecialWorkShifts':
            isTheseParametersAvailable(array('work_shift_id'));
            $db = new DbOperation();
            $result = $db->deleteSpecialWorkShifts(
                $_POST['work_shift_id']
            );
            break;
    }
}  else  {
    // if it is not api call 
    // pushing appropriate values to response array 
    $response['error'] = true;
    $response['message'] = 'Invalid API Call';
}

// displaying the response in json structure 
echo json_encode($response);
?>