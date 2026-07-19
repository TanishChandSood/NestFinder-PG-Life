<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "includes/database_connect.php";


header('Content-Type: application/json; charset=utf-8');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access! Please login first.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_SESSION['user_id']);
    $property_id = intval($_POST['property_id']);
    $receiver_id = intval($_POST['receiver_id']);
    $message = trim($_POST['message']);

    if (!empty($message) && $property_id > 0 && $receiver_id > 0) {


        $query = "INSERT INTO messages (property_id, user_id, receiver_id, message) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "iiis", $property_id, $user_id, $receiver_id, $message);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to insert message.']);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['success' => false, 'error' => 'Query preparation failed.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid Data! Empty message fields not allowed.']);
    }
    exit;
}
