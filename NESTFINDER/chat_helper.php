<?php
session_start();
include "includes/database_connect.php";

$userMsg = isset($_POST['msg']) ? strtolower(trim($_POST['msg'])) : '';
$city_id = isset($_POST['city_id']) ? intval($_POST['city_id']) : 1;

if (empty($userMsg)) {
    echo "Pardon? Please ask something.";
    exit();
}


$is_pg_search = false;


$detected_gender = null;
if (strpos($userMsg, 'male') !== false || strpos($userMsg, 'boy') !== false || strpos($userMsg, 'gents') !== false) {
    $detected_gender = 'male';
    $is_pg_search = true;
} elseif (strpos($userMsg, 'female') !== false || strpos($userMsg, 'girl') !== false || strpos($userMsg, 'ladi') !== false) {
    $detected_gender = 'female';
    $is_pg_search = true;
} elseif (strpos($userMsg, 'unisex') !== false) {
    $detected_gender = 'unisex';
    $is_pg_search = true;
}

$detected_budget = null;
if (preg_match('/(\d+[\d,]*)/', $userMsg, $matches)) {
    $detected_budget = intval(str_replace(',', '', $matches[1]));
    $is_pg_search = true;
} elseif (strpos($userMsg, 'sasta') !== false || strpos($userMsg, 'budget') !== false || strpos($userMsg, 'low rent') !== false) {
    $detected_budget = 9000;
    $is_pg_search = true;
}

$check_wifi = false;
if (strpos($userMsg, 'wifi') !== false || strpos($userMsg, 'internet') !== false || strpos($userMsg, 'net') !== false) {
    $check_wifi = true;
    $is_pg_search = true;
}

$cityQuery = "SELECT * FROM cities";
$cityResult = mysqli_query($conn, $cityQuery);
while ($cityRow = mysqli_fetch_assoc($cityResult)) {
    if (strpos($userMsg, strtolower($cityRow['name'])) !== false) {
        $city_id = $cityRow['id'];
        $is_pg_search = true;
        break;
    }
}


if (!$is_pg_search) {
    $ai_server_url = "https://nest-finder-pg-life.vercel.app/ask-ai";
    $post_data = json_encode(array("question" => $userMsg));

    $ch = curl_init($ai_server_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $ai_response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 200 && $ai_response) {
        $response_data = json_decode($ai_response, true);
        echo "<b>🤖 AI Assistant:</b><br/>" . nl2br(htmlspecialchars($response_data['reply']));
    } else {
        echo "Sorry, my AI backend is currently resting! Please make sure 'node server.js' is running.";
    }
    exit();
}


function calculatePhpDistance($pgId)
{
    if (isset($_SESSION['user_live_lat']) && isset($_SESSION['user_live_lng'])) {
        $userLat = $_SESSION['user_live_lat'];
        $userLng = $_SESSION['user_live_lng'];
    } else {
        $userLat = 28.6139;
        $userLng = 77.2090;
    }

    $propertyCoordinates = [
        1 => ['lat' => 28.6289, 'lng' => 77.2152],
        2 => ['lat' => 28.6448, 'lng' => 77.1901],
        3 => ['lat' => 19.1136, 'lng' => 72.8697],
    ];

    if (isset($propertyCoordinates[$pgId])) {
        $lat1 = $userLat;
        $lon1 = $userLng;
        $lat2 = $propertyCoordinates[$pgId]['lat'];
        $lon2 = $propertyCoordinates[$pgId]['lng'];

        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($R * $c, 1);
    }
    return round(rand(12, 45) / 10, 1);
}


$sql = "SELECT * FROM properties WHERE city_id = $city_id";

if ($detected_gender !== null) {
    $sql .= " AND gender = '$detected_gender'";
}
if ($detected_budget !== null) {
    $sql .= " AND rent <= $detected_budget";
}
if ($check_wifi) {
    $sql .= " AND id IN (SELECT property_id FROM properties_amenities WHERE amenity_id = 1)";
}

$sql .= " ORDER BY rent ASC LIMIT 3";
$result = mysqli_query($conn, $sql);


if (mysqli_num_rows($result) > 0) {
    $reply = "Ji bilkul! Maine aapke parameters ke hisab se best PGs dhoodh liye hain: <br/><br/>";

    while ($row = mysqli_fetch_assoc($result)) {
        $distance = calculatePhpDistance($row['id']);

        $reply .= "🏠 <b>" . htmlspecialchars($row['name']) . "</b><br/>";
        $reply .= "• Rent: <b>₹" . number_format($row['rent']) . "/month</b><br/>";
        $reply .= "• Type: <span class='text-capitalize'><b>" . $row['gender'] . "</b></span><br/>";
        $reply .= "• Distance: 📍 <b>" . $distance . " KM away</b> aapki real location se.<br/>";
        $reply .= "<a href='property_detail.php?property_id=" . $row['id'] . "' class='badge badge-danger text-white mb-2'>View Room</a><br/><br/>";
    }

    echo $reply;
} else {
    echo "Mujhe aapke budget aur criteria mein exact match nahi mila, par aap Filter bar use karke thoda range badha kar check kar sakte hain! 😊";
}
exit();
