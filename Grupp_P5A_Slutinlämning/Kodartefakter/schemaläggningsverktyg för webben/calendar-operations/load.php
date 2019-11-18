<?php

//load.php

$connect = new PDO('mysql:host=stbemanning.com.mysql;dbname=stbemanning_com', 'stbemanning_com', 'LEcYBDi6KsdveveuDuy9ePCA');

if(isset($_POST["user_id"])) {
    $data = array();
    $query = "SELECT work_shift.work_shift_id, customers.customer_name, work_shift.shift_start, work_shift.shift_end 
        FROM work_shift JOIN customers ON work_shift.customer_id=customers.customer_id WHERE work_shift.user_id=:user_id ORDER BY work_shift.work_shift_id";
    $statement = $connect->prepare($query);

    $statement->execute(array(':user_id' => $_POST['user_id']));

    $result = $statement->fetchAll();

    foreach($result as $row) {
        $data[] = array( 
            'id' => $row["work_shift_id"],
            'title' => $row["customer_name"],
            'start' => $row["shift_start"],
            'end' => $row["shift_end"]
        );
    }
    echo json_encode($data);
}

?>
