<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');
require "includes/database_connect.php";


if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Session expired! Please login again."]);
    exit;
}

$payer_id = intval($_SESSION['user_id']);


$property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;
$shared_with_id = isset($_POST['shared_with_id']) ? intval($_POST['shared_with_id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.00;


if ($property_id === 0 || $shared_with_id === 0 || empty($title) || $amount <= 0) {
    echo json_encode(["success" => false, "message" => "All fields are required and amount must be greater than 0."]);
    exit;
}

$split_amount = $amount / 2;


$sql = "INSERT INTO expenses (property_id, payer_id, shared_with_id, title, amount, split_amount) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {


    mysqli_stmt_bind_param($stmt, "iiisdd", $property_id, $payer_id, $shared_with_id, $title, $amount, $split_amount);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true, "message" => "Expense logged and split successfully!"]);
    } else {

        echo json_encode(["success" => false, "message" => "Failed to save expense due to a database error."]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Query preparation failed."]);
}

mysqli_close($conn);
exit;
