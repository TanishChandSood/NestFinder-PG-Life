<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "includes/database_connect.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {


        $query = "SELECT id, password, role, full_name FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);


                if (password_verify($password, $row['password'])) {


                    $_SESSION['user_id'] = intval($row['id']);
                    $_SESSION['full_name'] = isset($row['full_name']) ? $row['full_name'] : 'User';
                    $_SESSION['role'] = $row['role'];


                    session_regenerate_id(true);


                    if ($row['role'] === 'owner') {
                        header("Location: owner_dashboard.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit;
                } else {
                    $error = "⚠️ Galat Email ya Password dala hai bhai!";
                }
            } else {
                $error = "⚠️ Galat Email ya Password dala hai bhai!";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "⚠️ Kuch gadbad hui, kripya baad mein prayas karein.";
        }
    } else {
        $error = "⚠️ Dono dabbe bharo pehle!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NestFinder</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: #f4f7f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 15px;
            border: none;
        }
    </style>
</head>

<body>

    <div class="card login-card shadow">
        <h3 class="text-center font-weight-bold mb-4 text-dark">🔑 NestFinder Login</h3>

        <?php if (!empty($error)) { ?>
            <div class="alert alert-danger text-center py-2" style="font-size: 14px;"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
            <div class="form-group">
                <label class="font-weight-bold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email" style="border-radius: 8px;" required autocomplete="off">
            </div>

            <div class="form-group mb-4">
                <label class="font-weight-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" style="border-radius: 8px;" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold" style="border-radius: 8px;">Login</button>
        </form>
    </div>

</body>

</html>