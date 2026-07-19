<?php

$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "pglife";


mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);


if (!$conn) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed! Please check configurations or contact admin."
    ]);
    exit;
}


mysqli_set_charset($conn, "utf8mb4");
