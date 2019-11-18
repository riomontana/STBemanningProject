<?php

//update.php

$connect = new PDO('mysql:host=stbemanning.com.mysql;dbname=stbemanning_com', 'stbemanning_com', 'LEcYBDi6KsdveveuDuy9ePCA');

if(isset($_POST["id"])) {
    $query = "UPDATE work_shift 
    SET shift_start=:shift_start, shift_end=:shift_end 
    WHERE work_shift_id=:work_shift_id";
    
    $statement = $connect->prepare($query);
    $statement->execute(
        array(
        ':shift_start' => $_POST['start'],
        ':shift_end' => $_POST['end'],
        ':work_shift_id'   => $_POST['id'])
    );
}

?>
