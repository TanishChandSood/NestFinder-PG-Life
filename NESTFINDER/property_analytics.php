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


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: owner_dashboard.php");
    exit;
}

$property_id = intval($_GET['id']);


$sql_property = "SELECT name, address, views FROM properties WHERE id = $property_id AND owner_id = $user_id";
$result_property = mysqli_query($conn, $sql_property);

if (mysqli_num_rows($result_property) == 0) {

    echo "<div style='color:red; font-weight:bold; text-align:center; margin-top:50px;'>❌ Unauthorized Access: Aapko is property ke analytics dekhne ki permission nahi hai!</div>";
    exit;
}

$property = mysqli_fetch_assoc($result_property);
$total_views = intval($property['views']);


$sql_interested = "SELECT COUNT(*) as total FROM interested_users_properties WHERE property_id = $property_id";
$res_interested = mysqli_query($conn, $sql_interested);
$row_interested = mysqli_fetch_assoc($res_interested);
$interested_count = intval($row_interested['total']);


$sql_student_list = "SELECT u.full_name, u.email, u.phone 
                     FROM interested_users_properties iup
                     JOIN users u ON iup.user_id = u.id
                     WHERE iup.property_id = $property_id";
$res_student_list = mysqli_query($conn, $sql_student_list);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - <?= htmlspecialchars($property['name']) ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📈 Performance Analytics</h2>
            <a href="owner_dashboard.php" class="btn btn-secondary">🔙 Back to Dashboard</a>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h4 class="card-title text-primary"><?= htmlspecialchars($property['name']) ?></h4>
                <p class="card-text text-muted">📍 <?= htmlspecialchars($property['address']) ?></p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card bg-info text-white shadow">
                    <div class="card-body text-center">
                        <h5>👀 Total Page Views</h5>
                        <h1 class="display-4 font-weight-bold"><?= $total_views ?></h1>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card bg-success text-white shadow">
                    <div class="card-body text-center">
                        <h5>❤️ Total Interested Clicks</h5>
                        <h1 class="display-4 font-weight-bold"><?= $interested_count ?></h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white font-weight-bold text-muted">Conversion Graph</div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="width: 100%; max-width: 300px;">
                            <canvas id="analyticsPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white font-weight-bold text-muted">👥 Interested Students Leads</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone No.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($res_student_list) > 0): ?>
                                        <?php while ($student = mysqli_fetch_assoc($res_student_list)): ?>
                                            <tr>
                                                <td class="font-weight-bold text-secondary"><?= htmlspecialchars($student['full_name']) ?></td>
                                                <td><?= htmlspecialchars($student['email']) ?></td>
                                                <td>
                                                    <a href="tel:<?= htmlspecialchars($student['phone']) ?>" class="text-success font-weight-bold">
                                                        📞 <?= htmlspecialchars($student['phone']) ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Abhi tak kisi student ne interest show nahi kiya hai.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('analyticsPieChart').getContext('2d');


        const viewsCount = <?= intval($total_views) ?>;
        const interestedCount = <?= intval($interested_count) ?>;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Page Views', 'Interested Clicks'],
                datasets: [{
                    data: [viewsCount, interestedCount],
                    backgroundColor: ['#17a2b8', '#28a745'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>

</html>