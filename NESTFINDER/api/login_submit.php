<?php
session_start();
include('../includes/database_connect.php');

header('Content-Type: application/json'); // JavaScript ko batane ke liye ki hum JSON bhej rahe hain

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!empty($email) && !empty($password)) {


        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);


                if (password_verify($password, $row['password'])) {


                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['full_name'] = isset($row['full_name']) ? $row['full_name'] : $row['name'];
                    $_SESSION['role'] = isset($row['role']) ? $row['role'] : 'user';

                    echo json_encode([
                        "success" => true,
                        "message" => "Login successful!",
                        "role" => $_SESSION['role']
                    ]);

                    mysqli_stmt_close($stmt);
                    mysqli_close($conn);
                    exit;
                } else {

                    echo json_encode(["success" => false, "message" => "Invalid email or password!"]);
                    mysqli_stmt_close($stmt);
                    mysqli_close($conn);
                    exit;
                }
            } else {

                echo json_encode(["success" => false, "message" => "Invalid email or password!"]);
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                exit;
            }
        } else {
            echo json_encode(["success" => false, "message" => "Database error during login!"]);
            mysqli_close($conn);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "message" => "Please fill all fields!"]);
        exit;
    }
}
