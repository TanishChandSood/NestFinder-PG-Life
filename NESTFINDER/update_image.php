<?php
session_start();

require "includes/database_connect.php";

if (isset($_POST['update_photo'])) {

    $property_id = intval($_POST['property_id']);

    if (isset($_FILES['pg_image']) && $_FILES['pg_image']['error'] == 0) {
        $image_name = $_FILES['pg_image']['name'];
        $image_tmp = $_FILES['pg_image']['tmp_name'];


        $unique_image_name = time() . "_" . $image_name;
        $upload_folder = "img/" . $unique_image_name;


        if (!is_dir('img')) {
            mkdir('img', 0777, true);
        }


        if (move_uploaded_file($image_tmp, $upload_folder)) {


            $sql = "UPDATE properties SET image = '$unique_image_name' WHERE id = $property_id";

            if (mysqli_query($conn, $sql)) {

                header("Location: owner_dashboard.php?upload=success");
                exit;
            } else {
                echo "Database Query Error: " . mysqli_error($conn);
            }
        } else {
            echo "Error: Photo folder mein upload nahi ho payi.";
        }
    } else {
        echo "Error: Koi valid photo select nahi ki gayi.";
    }
} else {

    header("Location: owner_dashboard.php");
    exit;
}
