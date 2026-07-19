<?php
session_start();

require "includes/database_connect.php";


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: login.php");
    exit();
}


if (isset($_POST['submit_property'])) {
    $owner_id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $rent = intval($_POST['rent']);
    $city_id = intval($_POST['city_id']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);


    $lat = (!empty($_POST['lat'])) ? floatval($_POST['lat']) : null;
    $lng = (!empty($_POST['lng'])) ? floatval($_POST['lng']) : null;


    $rating_clean = 5.0;
    $rating_food = 5.0;
    $rating_safety = 5.0;


    $unique_image_name = "";

    if (isset($_FILES['pg_image']) && $_FILES['pg_image']['error'] == 0) {
        $image_name = $_FILES['pg_image']['name'];
        $image_tmp = $_FILES['pg_image']['tmp_name'];


        $unique_image_name = time() . "_" . $image_name;
        $upload_folder = "img/" . $unique_image_name;


        move_uploaded_file($image_tmp, $upload_folder);
    }


    $query = "INSERT INTO properties (city_id, name, address, description, gender, rent, rating_clean, rating_food, rating_safety, owner_id, image, lat, lng) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "issssddddisdd", $city_id, $name, $address, $description, $gender, $rent, $rating_clean, $rating_food, $rating_safety, $owner_id, $unique_image_name, $lat, $lng);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('PG Property successfully listed with Map Coordinates!'); window.location.href='owner_dashboard.php';</script>";
            exit();
        } else {
            echo "Error executing query: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }
} else {

    header("Location: owner_dashboard.php");
    exit();
}
