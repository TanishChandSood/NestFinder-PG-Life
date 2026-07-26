<?php
session_start();
require "includes/database_connect.php";

if (isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
    $property_id = intval($_POST['property_id']);
    $user_id = $_SESSION['user_id'];
    $rating_clean = floatval($_POST['rating_clean']);
    $rating_food = floatval($_POST['rating_food']);
    $rating_safety = floatval($_POST['rating_safety']);
    $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);


    $overall_rating = ($rating_clean + $rating_food + $rating_safety) / 3;


    $review_query = "INSERT INTO reviews (property_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $review_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iids", $property_id, $user_id, $overall_rating, $review_text);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }


    $user_name = "Anonymous Guest";
    $user_q = "SELECT full_name FROM users WHERE id = ?";
    $stmt_u = mysqli_prepare($conn, $user_q);
    if ($stmt_u) {
        mysqli_stmt_bind_param($stmt_u, "i", $user_id);
        mysqli_stmt_execute($stmt_u);
        $res_u = mysqli_stmt_get_result($stmt_u);
        if ($row_u = mysqli_fetch_assoc($res_u)) {
            $user_name = $row_u['full_name'];
        }
        mysqli_stmt_close($stmt_u);
    }


    $testim_query = "INSERT INTO testimonials (property_id, user_name, content) VALUES (?, ?, ?)";
    $stmt_t = mysqli_prepare($conn, $testim_query);
    if ($stmt_t) {
        mysqli_stmt_bind_param($stmt_t, "iss", $property_id, $user_name, $review_text);
        mysqli_stmt_execute($stmt_t);
        mysqli_stmt_close($stmt_t);
    }


    $avg_query = "SELECT AVG(rating) as avg_rating FROM reviews WHERE property_id = ?";
    $stmt_avg = mysqli_prepare($conn, $avg_query);
    mysqli_stmt_bind_param($stmt_avg, "i", $property_id);
    mysqli_stmt_execute($stmt_avg);
    $result = mysqli_stmt_get_result($stmt_avg);
    $row = mysqli_fetch_assoc($result);
    $new_rating = round($row['avg_rating'], 1);
    mysqli_stmt_close($stmt_avg);

    $update_query = "UPDATE properties SET rating_clean = ?, rating_food = ?, rating_safety = ? WHERE id = ?";
    $stmt_up = mysqli_prepare($conn, $update_query);
    if ($stmt_up) {
        mysqli_stmt_bind_param($stmt_up, "dddi", $new_rating, $new_rating, $new_rating, $property_id);
        mysqli_stmt_execute($stmt_up);
        mysqli_stmt_close($stmt_up);
    }


    header("Location: property_detail.php?property_id=" . $property_id);
    exit();
} else {
    header("Location: index.php");
    exit();
}
