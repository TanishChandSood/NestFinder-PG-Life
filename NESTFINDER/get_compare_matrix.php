<?php
session_start();
include "includes/database_connect.php";


if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized access");
}

if (!isset($_POST['ids']) || !is_array($_POST['ids']) || empty($_POST['ids'])) {
    echo "<p class='text-danger text-center'>No properties selected.</p>";
    exit();
}


$ids = array_map('intval', $_POST['ids']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));


$query = "SELECT id, name, rent, gender, rating_clean, rating_food, rating_safety, address 
          FROM properties WHERE id IN ($placeholders)";

$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    $types = str_repeat('i', count($ids));
    mysqli_stmt_bind_param($stmt, $types, ...$ids);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $properties = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} else {
    exit("<p class='text-danger'>Database Error.</p>");
}

if (empty($properties)) {
    echo "<p class='text-muted text-center'>Properties not found.</p>";
    exit();
}
?>

<table class="table table-bordered table-striped text-center bg-white shadow-sm">
    <thead class="thead-dark">
        <tr>
            <th style="width: 25%;">Features</th>
            <?php foreach ($properties as $pg) { ?>
                <th style="width: 25%;" class="text-primary font-weight-bold"><?= htmlspecialchars($pg['name']) ?></th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="font-weight-bold align-middle">Monthly Rent</td>
            <?php foreach ($properties as $pg) { ?>
                <td class="text-danger font-weight-bold align-middle">₹<?= number_format($pg['rent']) ?>/-</td>
            <?php } ?>
        </tr>

        <tr>
            <td class="font-weight-bold align-middle">Allowed Gender</td>
            <?php foreach ($properties as $pg) { ?>
                <td class="text-capitalize align-middle"><?= htmlspecialchars($pg['gender']) ?></td>
            <?php } ?>
        </tr>

        <tr>
            <td class="font-weight-bold align-middle">Average Rating</td>
            <?php foreach ($properties as $pg) { ?>
                <td class="align-middle">
                    <?php
                    $avg = ($pg['rating_clean'] + $pg['rating_food'] + $pg['rating_safety']) / 3;
                    echo "<span class='badge badge-warning p-2'>" . round($avg, 1) . " / 5.0</span>";
                    ?>
                </td>
            <?php } ?>
        </tr>

        <tr>
            <td class="font-weight-bold align-middle">Address Location</td>
            <?php foreach ($properties as $pg) { ?>
                <td class="small align-middle text-muted"><?= htmlspecialchars($pg['address']) ?></td>
            <?php } ?>
        </tr>
    </tbody>
</table>