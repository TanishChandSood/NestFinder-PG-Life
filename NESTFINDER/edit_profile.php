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


$vibes_sql = "SELECT vibe_tag FROM user_vibes WHERE user_id = $user_id";
$vibes_result = mysqli_query($conn, $vibes_sql);
$current_vibes = [];
if ($vibes_result) {
    while ($v_row = mysqli_fetch_assoc($vibes_result)) {
        $current_vibes[] = $v_row['vibe_tag'];
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $college_name = mysqli_real_escape_string($conn, trim($_POST['college_name']));
    $selected_vibes = $_POST['vibes'] ?? [];

    $update_sql = "UPDATE users SET full_name='$full_name', phone='$phone', college_name='$college_name' WHERE id=$user_id";

    if (mysqli_query($conn, $update_sql)) {



        mysqli_query($conn, "DELETE FROM user_vibes WHERE user_id = $user_id");


        if (!empty($selected_vibes)) {
            foreach ($selected_vibes as $vibe) {
                $vibe_clean = mysqli_real_escape_string($conn, trim($vibe));
                mysqli_query($conn, "INSERT INTO user_vibes (user_id, vibe_tag) VALUES ($user_id, '$vibe_clean')");
            }
        }

        $_SESSION['user_name'] = $full_name;

        echo "<script>
                alert('Mubarak ho! Profile aur Vibe Tags ekdum mast update ho gaye!'); 
                window.location.href='dashboard.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('Oops! Kuch gadbad ho gayi.');</script>";
    }
}


$available_vibes = [
    "Late Night Owl" => "🌙 Late Night Owl",
    "Early Bird"     => "🌅 Early Bird",
    "Studious"       => "📚 Studious",
    "Non-Smoker"     => "🚭 Non-Smoker",
    "Veg Only"       => "🥗 Veg Only",
    "Gamer"          => "🎮 Gamer",
    "Fitness Freak"  => "🏋️ Fitness Freak",
    "Clean Freak"    => "🧹 Clean Freak"
];
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
        <div class="edit-profile-container card p-4 shadow-sm" style="max-width: 550px; margin: 0 auto;">
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


                <div class="form-group">
                    <label class="font-weight-bold d-block mb-1">Select Your Vibes (Max 3):</label>
                    <small class="text-muted d-block mb-2">Yeh tags doosro ko roommate matching list mein dikhenge.</small>

                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($available_vibes as $value => $label): ?>
                            <?php $isChecked = in_array($value, $current_vibes) ? 'checked' : ''; ?>
                            <div class="custom-control custom-checkbox mr-3 mb-2">
                                <input type="checkbox" class="custom-control-input vibe-checkbox" id="vibe_<?= md5($value) ?>" name="vibes[]" value="<?= $value ?>" <?= $isChecked ?>>
                                <label class="custom-control-label" for="vibe_<?= md5($value) ?>"><?= $label ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-info text-white px-4">Save Changes</button>
                    <a href="dashboard.php" class="btn btn-secondary px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const vibeCheckboxes = document.querySelectorAll('.vibe-checkbox');
            const maxLimit = 3;

            vibeCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checkedCount = document.querySelectorAll('.vibe-checkbox:checked').length;
                    if (checkedCount > maxLimit) {
                        this.checked = false;
                        alert('Aap maximum 3 vibes hi select kar sakte hain!');
                    }
                });
            });
        });
    </script>
</body>

</html>