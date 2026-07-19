<?php
session_start();
require "includes/database_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first to connect with roommates!']);
    exit;
}

$user_id = $_SESSION['user_id'];
$target_roommate_id = isset($_POST['roommate_id']) ? intval($_POST['roommate_id']) : 0;
$property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;

if ($target_roommate_id == 0 || $property_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
    exit;
}
if ($user_id == $target_roommate_id) {
    echo json_encode(['status' => 'error', 'message' => 'You cannot send a connection request to yourself!']);
    exit;
}

$sql_check = "SELECT * FROM roommate_connections WHERE user_id = ? AND target_user_id = ? AND property_id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "iii", $user_id, $target_roommate_id, $property_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'You have already sent a connection request to this user!']);
    exit;
}


$sql_insert = "INSERT INTO roommate_connections (user_id, target_user_id, property_id, status) VALUES (?, ?, ?, 'pending')";
$stmt_insert = mysqli_prepare($conn, $sql_insert);
mysqli_stmt_bind_param($stmt_insert, "iii", $user_id, $target_roommate_id, $property_id);

if (mysqli_stmt_execute($stmt_insert)) {
    echo json_encode(['status' => 'success', 'message' => 'Connection invitation sent successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again later.']);
}
