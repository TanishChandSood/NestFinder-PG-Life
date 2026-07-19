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

if (isset($_GET['property_id']) && isset($_GET['receiver_id'])) {
    $property_id = intval($_GET['property_id']);
    $receiver_id = intval($_GET['receiver_id']);
    $current_user = intval($_SESSION['user_id']);


    $query = "SELECT m.*, u.full_name FROM messages m 
              INNER JOIN users u ON m.user_id = u.id 
              WHERE m.property_id = ? 
              AND (
                  (m.user_id = ? AND (m.receiver_id = ? OR m.receiver_id = 0)) 
                  OR 
                  (m.user_id = ? AND (m.receiver_id = ? OR m.receiver_id = 0))
              ) 
              ORDER BY m.created_at ASC";

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "iiiii", $property_id, $current_user, $receiver_id, $receiver_id, $current_user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $messages = [];

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $row['is_me'] = (intval($row['user_id']) === $current_user);


                $row['message'] = htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8');

                $messages[] = $row;
            }
        }

        echo json_encode($messages);
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to fetch messages.']);
    }
    exit;
}
