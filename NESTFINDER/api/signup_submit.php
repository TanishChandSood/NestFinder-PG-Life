<?php
include('../includes/database_connect.php');


$full_name = $_POST['full_name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$password = $_POST['password'];


$role = isset($_POST['role']) ? $_POST['role'] : 'user';
if (!in_array($role, ['user', 'owner'])) {
    $role = 'user'; // Default safety fallback
}

$password_hashed = password_hash($password, PASSWORD_DEFAULT);
$college_name = $_POST['college_name'];
$gender = $_POST['gender'];


$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(array("success" => false, "message" => "This email id is already registered!"));
    $stmt->close();
    return;
}
$stmt->close();


$sql = "INSERT INTO users (email, password, full_name, phone, gender, college_name, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(array("success" => false, "message" => "Something went wrong during preparation!"));
    return;
}


$stmt->bind_param("sssssss", $email, $password_hashed, $full_name, $phone, $gender, $college_name, $role);
$execution_success = $stmt->execute();

if (!$execution_success) {
    echo json_encode(array("success" => false, "message" => "Execution failed!"));
    $stmt->close();
    return;
}

echo json_encode(array("success" => true, "message" => "Your account has been created successfully!"));
$stmt->close();
mysqli_close($conn);
