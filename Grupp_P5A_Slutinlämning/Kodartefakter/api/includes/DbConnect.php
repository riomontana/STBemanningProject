<?php 
/*****************************************************************************
* DbConnect.php: Class that connects to ST-Bemannings MariaDB SQL database.  *
* @author Alex Giang, Sanna Roengaard, Simon Borjesson,                      *
* Lukas Persson, Nikola Pajovic, Linus Forsberg                              *
******************************************************************************/

    class DbConnect {
        // store database link in variable
        private $con;
 
        // class constructor
        function __construct()  {
        }
 
        // connect to the database
        function connect()  {  
        // including the constants.php file to get the database constants
            include_once dirname(__FILE__) . '/Constants.php';
 
            // connect to database
            $this->con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
 
            // check if any error occured while connecting
            if (mysqli_connect_errno()) {
                echo "Failed to connect to MySQL: " . mysqli_connect_error();
            }
 
            // returning the connection link 
            return $this->con;
            }
    }
 ?>