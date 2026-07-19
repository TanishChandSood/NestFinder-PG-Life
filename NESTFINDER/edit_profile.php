<?php
session_start();
require "includes/database_connect.php";


if (!isset($_SESSION["user_id"])) {
    header("location: index.php");
    die();
}
$user_id = intval($_SESSION['user_id']);


$sql = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $college_name = mysqli_real_escape_string($conn, trim($_POST['college_name']));


    $update_sql = "UPDATE users SET full_name='$full_name', phone='$phone', college_name='$college_name' WHERE id=$user_id";

    if (mysqli_query($conn, $update_sql)) {

        $_SESSION['user_name'] = $full_name;

        echo "<script>
                alert('Mubarak ho! Profile ekdum mast update ho gayi!'); 
                window.location.href='dashboard.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('Oops! Kuch gadbad ho gayi.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profile | PG Life</title>
    <?php include "includes/head_links.php"; ?>
</head>

<body>
    <?php include "includes/header.php"; ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
        </ol>
    </nav>

    <div class="container my-4">
        <div class="edit-profile-container card p-4 shadow-sm" style="max-width: 500px; margin: 0 auto;">
            <h2 class="text-center mb-4">Edit Profile</h2>

            <form method="POST" action="edit_profile.php">
                <div class="form-group">
                    <label class="font-weight-bold">Full Name</label>
                    <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Phone Number</label>
                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required maxlength="10">
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">College Name</label>
                    <input type="text" class="form-control" name="college_name" value="<?= htmlspecialchars($user['college_name'] ?? '') ?>">
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-info text-whitepx-4">Save Changes</button>
                    <a href="dashboard.php" class="btn btn-secondary px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>
</body>

</html>