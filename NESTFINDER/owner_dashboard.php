<?php
session_start();
require "includes/database_connect.php";


if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);


$role_sql = "SELECT role FROM users WHERE id = $user_id";
$role_res = mysqli_query($conn, $role_sql);
if ($role_res && $role_row = mysqli_fetch_assoc($role_res)) {
    if ($role_row['role'] !== 'owner') {
        header("Location: dashboard.php");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}


if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);
    $action = $_GET['action'];
    $status = ($action == 'confirm') ? 'Confirmed' : 'Cancelled';


    $update_sql = "UPDATE tour_bookings tb
                   INNER JOIN properties p ON tb.property_id = p.id
                   SET tb.status = '$status' 
                   WHERE tb.id = $booking_id AND p.owner_id = $user_id";
    mysqli_query($conn, $update_sql);
    header("Location: owner_dashboard.php");
    exit;
}


$sql = "SELECT tb.*, p.name AS property_name, u.full_name AS user_name, u.phone 
        FROM tour_bookings tb
        INNER JOIN properties p ON tb.property_id = p.id
        INNER JOIN users u ON tb.user_id = u.id
        WHERE p.owner_id = $user_id
        ORDER BY tb.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Tour Requests</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4 mt-md-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 text-center text-md-left">
            <h2 class="mb-2 mb-md-0">👑 PG Owner Dashboard</h2>
            
         
            <div class="d-flex flex-column flex-md-row align-items-center">
                <h4 class="text-muted mb-3 mb-md-0 mr-md-3">Tour Booking Management</h4>
                <div class="dashboard-buttons">
                    <a href="logout.php" class="btn btn-danger btn-sm" style="font-weight: 500;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 text-nowrap">
                        <thead class="thead-dark">
                            <tr>
                                <th>User Details</th>
                                <th>Property</th>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Current Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['user_name']) ?></strong><br><small><?= htmlspecialchars($row['phone']) ?></small></td>
                                        <td><?= htmlspecialchars($row['property_name']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($row['booking_date'])) ?> <br><?= htmlspecialchars($row['booking_slot']) ?></td>
                                        <td><?= htmlspecialchars($row['tour_type']) ?></td>

                                        <td>
                                            <span class="badge p-2 <?= ($row['status'] == 'Confirmed') ? 'badge-success' : (($row['status'] == 'Cancelled') ? 'badge-danger' : 'badge-warning') ?>">
                                                <?= htmlspecialchars($row['status']) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if ($row['status'] == 'Pending') { ?>
                                                <a href="owner_dashboard.php?action=confirm&booking_id=<?= intval($row['id']) ?>" class="btn btn-sm btn-success mb-1">Accept</a>
                                                <a href="owner_dashboard.php?action=reject&booking_id=<?= intval($row['id']) ?>" class="btn btn-sm btn-danger mb-1">Reject</a>
                                            <?php } else if ($row['status'] == 'Confirmed') { ?>
                                                <a href="chatroom.php?property_id=<?= intval($row['property_id']) ?>&receiver_id=<?= intval($row['user_id']) ?>" class="btn btn-sm btn-info" style="border-radius: 20px; font-size: 12px; font-weight: bold;">
                                                    💬 Chat
                                                </a>
                                            <?php } else { ?>
                                                <span class="text-muted">No actions</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No tour requests received yet.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5 d-flex flex-column flex-md-row justify-content-between align-items-center">
        <h3 class="mb-3 mb-md-0">My Properties</h3>
        <button type="button" class="btn btn-primary shadow-sm"
            data-toggle="modal" data-target="#addPropertyModal"
            data-bs-toggle="modal" data-bs-target="#addPropertyModal">
            ➕ Add New PG
        </button>
    </div>

    <div class="container mt-4">
        <div class="row">
            <?php

            $owner_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 1;


            $prop_sql = "SELECT p.*, c.name AS city_name 
                         FROM properties p 
                         INNER JOIN cities c ON p.city_id = c.id 
                         WHERE p.owner_id = $owner_id 
                         ORDER BY p.id DESC";

            $prop_result = mysqli_query($conn, $prop_sql);

            if (mysqli_num_rows($prop_result) > 0) {
                while ($prop_row = mysqli_fetch_assoc($prop_result)) { ?>
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <?php
                            if (!empty($prop_row['image']) && file_exists("img/" . $prop_row['image'])) {
                                $image_path = "img/" . $prop_row['image'];
                            } else {

                                $property_id = intval($prop_row['id']);
                                $folder_path = "img/properties/" . $property_id . "/";


                                $folder_images = glob($folder_path . "*.{jpg,jpeg,png,webp}", GLOB_BRACE);

                                if (!empty($folder_images)) {
                                    $image_path = $folder_images[0];
                                } else {

                                    $image_path = "https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=500&q=80";
                                }
                            }
                            ?>
                            <img src="<?= $image_path ?>" class="card-img-top" alt="PG Image" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title text-primary"><?= htmlspecialchars($prop_row['name']) ?></h5>
                                <p class="card-text mb-1"><strong>📍 Location:</strong> <?= htmlspecialchars($prop_row['address']) ?>, <?= htmlspecialchars($prop_row['city_name']) ?></p>
                                <p class="card-text mb-1"><strong>💰 Rent:</strong> ₹<?= htmlspecialchars($prop_row['rent']) ?>/month</p>
                                <p class="card-text mb-2"><strong>👫 For:</strong> <span class="badge badge-info text-capitalize"><?= htmlspecialchars($prop_row['gender']) ?></span></p>
                                <small class="text-muted d-block text-truncate" style="max-width: 100%;"><?= htmlspecialchars($prop_row['description']) ?></small>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-warning font-weight-bold">⭐
                                        <?php
                                        $avg_rating = ($prop_row['rating_clean'] + $prop_row['rating_food'] + $prop_row['rating_safety']) / 3;
                                        echo round($avg_rating, 1);
                                        ?>
                                    </span>
                                    <a href="property_analytics.php?id=<?= intval($prop_row['id']) ?>" class="btn btn-sm btn-outline-secondary">📊 View Analytics</a>
                                </div>

                                <form action="update_image.php" method="POST" enctype="multipart/form-data" class="pt-3 border-top">
                                    <label class="small text-muted mb-1" style="font-size: 12px;">🔄 Change PG Photo:</label>
                                    <div class="input-group input-group-sm">
                                        <input type="hidden" name="property_id" value="<?= intval($prop_row['id']) ?>">
                                        <input type="file" name="pg_image" class="form-control form-control-sm" accept="image/*" required>
                                        <div class="input-group-append">
                                            <button type="submit" name="update_photo" class="btn btn-success btn-sm">💾 Save</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                <?php }
            } else { ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted lead">You haven't listed any PG properties yet.</p>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="modal fade" id="addPropertyModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalTitle">List a New PG Property</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="add_property.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label>PG Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g., Elite Residency PG">
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label>Rent per Month (₹)</label>
                                <input type="number" name="rent" class="form-control" required placeholder="e.g., 8500">
                            </div>
                            <div class="form-group col-12 mb-3">
                                <label>Upload PG Image</label>
                                <input type="file" name="pg_image" class="form-control-file" accept="image/*" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label>City</label>
                                <select name="city_id" class="form-control" required>
                                    <option value="">-- Select City --</option>
                                    <?php
                                    $city_query = "SELECT * FROM cities ORDER BY name ASC";
                                    $city_result = mysqli_query($conn, $city_query);

                                    if (mysqli_num_rows($city_result) > 0) {
                                        while ($city_row = mysqli_fetch_assoc($city_result)) {
                                            echo "<option value='" . intval($city_row['id']) . "'>" . htmlspecialchars($city_row['name']) . "</option>";
                                        }
                                    } else {
                                        echo "<option value=''>No cities found in database</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label>Preferred Gender</label>
                                <select name="gender" class="form-control" required>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="unisex">Unisex</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Full Address</label>
                            <input type="text" name="address" class="form-control" required placeholder="Street, Landmark, Sector...">
                        </div>

                        <div class="form-row row mb-3">
                            <div class="col-6">
                                <label>Latitude</label>
                                <input type="text" id="pg_lat" name="lat" class="form-control" placeholder="Automatic fill hoga" readonly>
                            </div>
                            <div class="col-6">
                                <label>Longitude</label>
                                <input type="text" id="pg_lng" name="lng" class="form-control" placeholder="Automatic fill hoga" readonly>
                            </div>
                        </div>

                        <button type="button" id="fetchLocationBtn" class="btn btn-warning btn-sm mb-3">
                            📍 Fetch My Current Location
                        </button>
                        <span id="locationStatus" class="d-block d-md-inline text-muted mt-2 mt-md-0 ml-md-2" style="font-size: 13px;"></span>

                        <div class="form-group mt-2">
                            <label>Description & Amenities</label>
                            <textarea name="description" class="form-control" rows="4" required placeholder="Tell tenants about Wi-Fi, Food, AC, Laundry, Electricity bills, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit_property" class="btn btn-success">Publish PG</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const fetchBtn = document.getElementById("fetchLocationBtn");
            const latInput = document.getElementById("pg_lat");
            const lngInput = document.getElementById("pg_lng");
            const statusSpan = document.getElementById("locationStatus");

            if (fetchBtn) {
                fetchBtn.addEventListener("click", function(e) {
                    e.preventDefault();

                    statusSpan.textContent = "⏳ Fetching your coordinates...";
                    statusSpan.style.color = "#ffc107";

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                latInput.value = position.coords.latitude;
                                lngInput.value = position.coords.longitude;

                                statusSpan.textContent = "✅ Location auto-filled successfully!";
                                statusSpan.style.color = "#28a745";
                            },
                            function(error) {
                                statusSpan.style.color = "#dc3545";
                                switch (error.code) {
                                    case error.PERMISSION_DENIED:
                                        statusSpan.textContent = "❌ Error: Please allow Location permission in your browser.";
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        statusSpan.textContent = "❌ Error: Location network unavailable.";
                                        break;
                                    case error.TIMEOUT:
                                        statusSpan.textContent = "❌ Error: Location request timed out.";
                                        break;
                                    default:
                                        statusSpan.textContent = "❌ Error: Something went wrong.";
                                }
                            }, {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );
                    } else {
                        statusSpan.textContent = "❌ Error: Your browser doesn't support Geolocation.";
                        statusSpan.style.color = "#dc3545";
                    }
                });
            }
        });
    </script>
    <?php include "includes/footer_scripts.php"; ?>
</body>

</html>