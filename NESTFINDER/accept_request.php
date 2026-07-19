<?php
session_start();

include 'includes/database_connect.php';

if (isset($_POST['roommate_id']) && isset($_POST['property_id']) && isset($_SESSION['user_id'])) {

    $current_user_id = $_SESSION['user_id'];
    $sender_id = mysqli_real_escape_string($conn, $_POST['roommate_id']);
    $property_id = mysqli_real_escape_string($conn, $_POST['property_id']);


    $sql_update = "UPDATE roommate_connections 
                   SET status = 'accepted' 
                   WHERE user_id = '$sender_id' 
                   AND target_user_id = '$current_user_id' 
                   AND property_id = '$property_id' 
                   AND status = 'pending'";

    if (mysqli_query($conn, $sql_update)) {
        echo 'success';
    } else {
        echo 'error_db';
    }
} else {
    echo 'invalid_data';
}
