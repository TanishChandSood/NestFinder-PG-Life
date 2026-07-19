<?php
session_start();
include "includes/database_connect.php";

header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // 401 Unauthorized status
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];


$query = "SELECT e.*, u1.full_name AS payer_name, u2.full_name AS shared_name 
          FROM expenses e
          JOIN users u1 ON e.payer_id = u1.id
          JOIN users u2 ON e.shared_with_id = u2.id
          WHERE e.payer_id = ? OR e.shared_with_id = ?
          ORDER BY e.created_at DESC";

$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $expenses = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $expenses[] = $row;
        }

        echo json_encode(['success' => true, 'data' => $expenses]);
    } else {

        error_log("Database execution error in get_expenses.php: " . mysqli_error($conn));
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Something went wrong on the server.']);
    }


    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Query preparation failed.']);
}
exit;
