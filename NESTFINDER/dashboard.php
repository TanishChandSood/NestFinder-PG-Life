<?php
session_start();
require "includes/database_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("location: index.php");
    die();
}
$user_id = $_SESSION['user_id'];


$sql_1 = "SELECT * FROM users WHERE id = ?";
$stmt_1 = mysqli_prepare($conn, $sql_1);
mysqli_stmt_bind_param($stmt_1, "i", $user_id);
mysqli_stmt_execute($stmt_1);
$result_1 = mysqli_stmt_get_result($stmt_1);

if (!$result_1) {
    echo "Something went wrong!";
    return;
}
$user = mysqli_fetch_assoc($result_1);
if (!$user) {
    echo "Something went wrong!";
    return;
}


if (isset($user['role']) && $user['role'] === 'owner') {
    header("Location: owner_dashboard.php");
    exit;
}


$sql_2 = "SELECT * FROM interested_users_properties iup
            INNER JOIN properties p ON iup.property_id = p.id
            WHERE iup.user_id = ?";
$stmt_2 = mysqli_prepare($conn, $sql_2);
mysqli_stmt_bind_param($stmt_2, "i", $user_id);
mysqli_stmt_execute($stmt_2);
$result_2 = mysqli_stmt_get_result($stmt_2);

$interested_properties = mysqli_fetch_all($result_2, MYSQLI_ASSOC);


$sql_roommates = "SELECT id, full_name FROM users WHERE id != ?";
$stmt_roommates = mysqli_prepare($conn, $sql_roommates);
mysqli_stmt_bind_param($stmt_roommates, "i", $user_id);
mysqli_stmt_execute($stmt_roommates);
$result_roommates = mysqli_stmt_get_result($stmt_roommates);
$roommates = mysqli_fetch_all($result_roommates, MYSQLI_ASSOC);


$sql_expenses = "SELECT e.*, u1.full_name AS payer_name, u2.full_name AS shared_name 
                 FROM expenses e
                 JOIN users u1 ON e.payer_id = u1.id
                 JOIN users u2 ON e.shared_with_id = u2.id
                 WHERE e.payer_id = ? OR e.shared_with_id = ?
                 ORDER BY e.created_at DESC";
$stmt_expenses = mysqli_prepare($conn, $sql_expenses);
mysqli_stmt_bind_param($stmt_expenses, "ii", $user_id, $user_id);
mysqli_stmt_execute($stmt_expenses);
$result_expenses = mysqli_stmt_get_result($stmt_expenses);
$expenses = mysqli_fetch_all($result_expenses, MYSQLI_ASSOC);


$you_are_owed = 0;
$you_owe = 0;
foreach ($expenses as $exp) {
    if ($exp['payer_id'] == $user_id) {
        $you_are_owed += $exp['split_amount'];
    } elseif ($exp['shared_with_id'] == $user_id) {
        $you_owe += $exp['split_amount'];
    }
}
$net_balance = $you_are_owed - $you_owe;


$sql_tours = "SELECT tb.*, p.name AS property_name, p.address, p.owner_id 
              FROM tour_bookings tb 
              INNER JOIN properties p ON tb.property_id = p.id 
              WHERE tb.user_id = ? 
              ORDER BY tb.booking_date DESC";
$stmt_tours = mysqli_prepare($conn, $sql_tours);
mysqli_stmt_bind_param($stmt_tours, "i", $user_id);
mysqli_stmt_execute($stmt_tours);
$result_tours = mysqli_stmt_get_result($stmt_tours);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | PG Life</title>

    <?php
    include "includes/head_links.php";
    ?>
    <link href="css/dashboard.css" rel="stylesheet" />
    <style>
        .balance-card {
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            color: white;
            margin-bottom: 15px;
        }

        .bg-owed {
            background-color: #28a745;
        }

        .bg-owe {
            background-color: #dc3545;
        }

        .bg-net {
            background-color: #17a2b8;
        }

        .expense-list-box {
            max-height: 300px;
            overflow-y: auto;
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }

        .expense-item {
            border-bottom: 1px solid #e9ecef;
            padding: 8px 0;
        }

        .expense-item:last-child {
            border: none;
        }
    </style>
</head>

<body>
    <?php
    include "includes/header.php";
    ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="index.php">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Dashboard
            </li>
        </ol>
    </nav>

    <div class="my-profile page-container">
        <h1>My Profile</h1>
        <div class="row">
            <div class="col-md-3 profile-img-container">
                <i class="fas fa-user profile-img"></i>
            </div>
            <div class="col-md-9">
                <div class="row no-gutters justify-content-between align-items-end">
                    <div class="profile">
                        <div class="name"><?= htmlspecialchars($user['full_name']) ?></div>
                        <div class="email"><?= htmlspecialchars($user['email']) ?></div>
                        <div class="phone"><?= htmlspecialchars($user['phone']) ?></div>
                        <div class="college"><?= htmlspecialchars($user['college_name']) ?></div>
                    </div>
                    <div class="edit">
                        <a href="edit_profile.php" class="edit-profile" style="text-decoration: none; color: inherit; display: block; cursor: pointer;">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="expense-splitter-section page-container my-5">
        <hr>
        <h1 class="my-4"><i class="fas fa-calculator"></i> Roommate Expense Splitter & Bills</h1>

        <div class="row">
            <div class="col-md-4">
                <div class="balance-card bg-owed">
                    <h5>You are Owed</h5>
                    <h3>₹ <?= number_format($you_are_owed, 2) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="balance-card bg-owe">
                    <h5>You Owe</h5>
                    <h3>₹ <?= number_format($you_owe, 2) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="balance-card bg-net">
                    <h5>Net Balance</h5>
                    <h3>₹ <?= number_format($net_balance, 2) ?></h3>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm p-4">
                    <h4><i class="fas fa-plus-circle"></i> Add New Bill/Expense</h4>
                    <form id="addExpenseForm" class="mt-3">
                        <div class="form-group">
                            <label>PG Property Context</label>
                            <select name="property_id" class="form-control" required>
                                <option value="">Select Property</option>
                                <?php foreach ($interested_properties as $prop) { ?>
                                    <option value="<?= $prop['id'] ?>"><?= htmlspecialchars($prop['name']) ?></option>
                                <?php } ?>
                                <?php if (empty($interested_properties)) {
                                    echo '<option value="1">General Dummy PG</option>';
                                } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Split With (Roommate)</label>
                            <select name="shared_with_id" class="form-control" required>
                                <option value="">Choose Roommate</option>
                                <?php foreach ($roommates as $rm) { ?>
                                    <option value="<?= $rm['id'] ?>"><?= htmlspecialchars($rm['full_name']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Bill Title/Description</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g., WiFi Bill, Room Rent, Swiggy" required>
                        </div>
                        <div class="form-group">
                            <label>Total Amount (₹)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                            <small class="text-muted">Note: It will automatically split 50/50 with the roommate.</small>
                        </div>
                        <button type="submit" class="btn btn-success btn-block mt-3">Add & Split Expense</button>
                    </form>
                    <div id="formResponse" class="mt-2"></div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm p-4">
                    <h4><i class="fas fa-history"></i> Expense History Logs</h4>
                    <div class="expense-list-box mt-3">
                        <?php if (count($expenses) > 0) {
                            foreach ($expenses as $exp) {
                                $is_payer = ($exp['payer_id'] == $user_id);
                        ?>
                                <div class="expense-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($exp['title']) ?></strong>
                                        <br>
                                        <small class="text-muted">Paid by: <?= $is_payer ? 'You' : htmlspecialchars($exp['payer_name']) ?> | Date: <?= date('d M Y', strtotime($exp['created_at'])) ?></small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-secondary">Total: ₹<?= number_format($exp['amount'], 2) ?></span>
                                        <br>
                                        <?php if ($is_payer) { ?>
                                            <small class="text-success"><strong><?= htmlspecialchars($exp['shared_name']) ?></strong> owes you ₹<?= number_format($exp['split_amount'], 2) ?></small>
                                        <?php } else { ?>
                                            <small class="text-danger">You owe <strong><?= htmlspecialchars($exp['payer_name']) ?></strong> ₹<?= number_format($exp['split_amount'], 2) ?></small>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php }
                        } else { ?>
                            <p class="text-muted text-center p-3">No expenses recorded yet. Start splitting!</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <hr>
    </div>
    <div class="container mt-5 mb-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="font-weight-bold" style="font-size: 24px; color: #333;">🗓️ My Scheduled Tours</h2>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 10px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="vertical-align: middle;">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th class="border-0 pl-4">Property Details</th>
                                <th class="border-0">Date</th>
                                <th class="border-0">Time Slot</th>
                                <th class="border-0">Tour Type</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result_tours) > 0) {
                                while ($row = mysqli_fetch_assoc($result_tours)) {

                                    $badge_color = 'background-color: #ffc107; color: #fff;'; // Pending
                                    if ($row['status'] == 'Confirmed') $badge_color = 'background-color: #28a745; color: #fff;';
                                    if ($row['status'] == 'Cancelled') $badge_color = 'background-color: #dc3545; color: #fff;';
                            ?>
                                    <tr>
                                        <td class="pl-4 py-3">
                                            <span class="font-weight-bold text-dark" style="font-size: 16px;"><?= htmlspecialchars($row['property_name']) ?></span>
                                            <br><small class="text-muted"><?= htmlspecialchars($row['address']) ?></small>
                                        </td>
                                        <td><?= date('d-m-Y', strtotime($row['booking_date'])) ?></td>
                                        <td><strong><?= htmlspecialchars($row['booking_slot']) ?></strong></td>
                                        <td>
                                            <span class="badge badge-light p-2 border"><?= htmlspecialchars($row['tour_type']) ?> Tour</span>
                                        </td>
                                        <td>
                                            <span class="badge p-2" style="font-size: 13px; border-radius: 20px; <?= $badge_color ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                        </td>
                                        <td class="pr-4">
                                            <?php if ($row['status'] == 'Confirmed') { ?>
                                                <a href="chatroom.php?property_id=<?= $row['property_id'] ?>&receiver_id=<?= $row['owner_id'] ?>"
                                                    class="btn btn-sm btn-primary" style="border-radius: 20px;">
                                                    💬 Chat with Owner
                                                </a>
                                            <?php } else { ?>
                                                <span class="text-muted" style="font-size: 13px;">🔒 Waiting</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <p class="mb-0" style="font-size: 16px;">Aapne abhi tak koi tour schedule nahi kiya hai.</p>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php

    if (count($interested_properties) > 0) {
    ?>
        <div class="my-interested-properties">
            <div class="page-container">
                <h1>My Interested Properties</h1>

                <?php
                foreach ($interested_properties as $property) {
                    $property_images = glob("img/properties/" . $property['id'] . "/*");
                ?>
                    <div class="property-card property-id-<?= $property['id'] ?> row">
                        <div class="image-container col-md-4">
                            <img src="<?= $property_images[0] ?>" />
                        </div>
                        <div class="content-container col-md-8">
                            <div class="row no-gutters justify-content-between">
                                <?php
                                $total_rating = ($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3;
                                $total_rating = round($total_rating, 1);
                                ?>
                                <div class="star-container" title="<?= $total_rating ?>">
                                    <?php
                                    $rating = $total_rating;
                                    for ($i = 0; $i < 5; $i++) {
                                        if ($rating >= $i + 0.8) {
                                    ?>
                                            <i class="fas fa-star"></i>
                                        <?php
                                        } elseif ($rating >= $i + 0.3) {
                                        ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php
                                        } else {
                                        ?>
                                            <i class="far fa-star"></i>
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="interested-container">
                                    <i class="is-interested-image fas fa-heart" property_id="<?= $property['id'] ?>"></i>
                                </div>
                            </div>
                            <div class="detail-container">
                                <div class="property-name"><?= htmlspecialchars($property['name']) ?></div>
                                <div class="property-address"><?= htmlspecialchars($property['address']) ?></div>
                                <div class="property-gender">
                                    <?php
                                    if ($property['gender'] == "male") {
                                    ?>
                                        <img src="img/male.png">
                                    <?php
                                    } elseif ($property['gender'] == "female") {
                                    ?>
                                        <img src="img/female.png">
                                    <?php
                                    } else {
                                    ?>
                                        <img src="img/unisex.png">
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="row no-gutters">
                                <div class="rent-container col-6">
                                    <div class="rent">₹ <?= number_format($property['rent']) ?>/-</div>
                                    <div class="rent-unit">per month</div>
                                </div>
                                <div class="button-container col-6">
                                    <a href="property_detail.php?property_id=<?= $property['id'] ?>" class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    <?php
    }
    ?>

    <?php
    include "includes/footer.php";
    ?>

    <script type="text/javascript" src="js/dashboard.js"></script>

    <script>
        document.getElementById('addExpenseForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const responseDiv = document.getElementById('formResponse');

            fetch('add_expense.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        responseDiv.innerHTML = `<div class="alert alert-success">${data.message} Page reloading...</div>`;
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        responseDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    responseDiv.innerHTML = `<div class="alert alert-danger">Something went wrong with AJAX pipeline!</div>`;
                });
        });
    </script>
</body>

</html>