<?php

//delete.php

if(isset($_POST["id"])) {
    $connect = new PDO('mysql:host=stbemanning.com.mysql;dbname=stbemanning_com', 'stbemanning_com', 'LEcYBDi6KsdveveuDuy9ePCA');
    $query = "DELETE from work_shift WHERE work_shift_id=:id";

    $statement = $connect->prepare($query);
    $statement->execute(array(':id' => $_POST['id']));
}

if(isset($_POST["date_from"],
        $_POST["date_to"],
        $_POST["user_id"])) {
    $connect = new PDO('mysql:host=stbemanning.com.mysql;dbname=stbemanning_com', 'stbemanning_com', 'LEcYBDi6KsdveveuDuy9ePCA');
    
    $query = "DELETE FROM work_shift WHERE user_id = :user_id AND shift_start BETWEEN :date_from AND :date_to";

    $statement = $connect->prepare($query);
    $statement->execute(array(':date_from' => $_POST['date_from'],
                                ':date_to' => $_POST['date_to'],
                                ':user_id' => $_POST['user_id'],));
}

?>