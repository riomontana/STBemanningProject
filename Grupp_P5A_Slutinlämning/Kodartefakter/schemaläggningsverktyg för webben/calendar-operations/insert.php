<?php

//update.php

$connect = new PDO('mysql:host=stbemanning.com.mysql;dbname=stbemanning_com', 'stbemanning_com', 'LEcYBDi6KsdveveuDuy9ePCA');

if(isset($_POST["title"])) {
    $query = "INSERT INTO work_shift 
        (customer_id, shift_start, shift_end, user_id) 
        VALUES (:customer_id, :shift_start, :shift_end, :user_id)";
    
    $statement = $connect->prepare($query);
    $statement->execute(
        array(
            ':customer_id'  => $_POST['title'],
            ':shift_start' => $_POST['start'],
            ':shift_end' => $_POST['end'],
            ':user_id' => $_POST['user_id']
        )
    );
}
    
?>
    