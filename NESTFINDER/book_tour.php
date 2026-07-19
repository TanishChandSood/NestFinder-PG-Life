<?php
session_start();


include "includes/database_connect.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login first to book a tour slot!']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $tour_type = $_POST['tour_type'];

    if (empty($booking_date) || empty($booking_time) || empty($tour_type) || $property_id == 0) {
        echo json_encode(['success' => false, 'message' => 'All fields are required!']);
        exit;
    }


    $check_query = "SELECT * FROM tour_bookings WHERE property_id = ? AND booking_date = ? AND booking_slot = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("iss", $property_id, $booking_date, $booking_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Oops! This time slot is already booked for this property. Please choose another time.']);
        exit;
    }


    $book_query = "INSERT INTO tour_bookings (property_id, user_id, booking_date, booking_slot, tour_type, status) VALUES (?, ?, ?, ?, ?, 'Pending')";
    $book_stmt = $conn->prepare($book_query);
    $book_stmt->bind_param("iisss", $property_id, $user_id, $booking_date, $booking_time, $tour_type);

    if ($book_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '🎉 Tour slot booked successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: Unable to save booking.']);
    }
    exit;
}
