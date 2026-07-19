<?php
session_start();
header("Content-Type: application/json");

require "../includes/database_connect.php";


if (!isset($_SESSION['user_id'])) {
    echo json_encode(array("success" => false, "is_logged_in" => false));
    return;
}

$user_id = intval($_SESSION['user_id']);


$property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : 0;

if ($property_id <= 0) {
    echo json_encode(array("success" => false, "message" => "Invalid Property ID"));
    return;
}


$sql_check = "SELECT id FROM interested_users_properties WHERE user_id = ? AND property_id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $property_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {

    $sql_delete = "DELETE FROM interested_users_properties WHERE user_id = ? AND property_id = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "ii", $user_id, $property_id);
    mysqli_stmt_execute($stmt_delete);
    mysqli_stmt_close($stmt_delete);

    echo json_encode(array("success" => true, "is_interested" => false));
} else {

    $sql_insert = "INSERT INTO interested_users_properties (user_id, property_id) VALUES (?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "ii", $user_id, $property_id);
    mysqli_stmt_execute($stmt_insert);
    mysqli_stmt_close($stmt_insert);

    echo json_encode(array("success" => true, "is_interested" => true));
}

mysqli_stmt_close($stmt_check);
mysqli_close($conn);
