<?php
session_start();
include "includes/database_connect.php";

$userMsg = isset($_POST['msg']) ? strtolower(trim($_POST['msg'])) : '';
$city_id = isset($_POST['city_id']) ? intval($_POST['city_id']) : null;

if (empty($userMsg)) {
    echo "Pardon? Please ask something.";
    exit();
}

$explicit_city_found = false;


$detected_gender = null;
if (preg_match('/\b(male|boy|boys|gents)\b/i', $userMsg)) {
    $detected_gender = 'male';
} elseif (preg_match('/\b(female|girl|girls|ladies|ladi)\b/i', $userMsg)) {
    $detected_gender = 'female';
} elseif (strpos($userMsg, 'unisex') !== false) {
    $detected_gender = 'unisex';
}


$detected_budget = null;
if (preg_match('/(\d+[\d,]*)/', $userMsg, $matches)) {
    $detected_budget = intval(str_replace(',', '', $matches[1]));
} elseif (strpos($userMsg, 'sasta') !== false || strpos($userMsg, 'budget') !== false || strpos($userMsg, 'low rent') !== false) {
    $detected_budget = 9000;
}


$check_wifi = false;
if (strpos($userMsg, 'wifi') !== false || strpos($userMsg, 'internet') !== false || strpos($userMsg, 'net') !== false) {
    $check_wifi = true;
}


$cityQuery = "SELECT * FROM cities";
$cityResult = mysqli_query($conn, $cityQuery);
if ($cityResult) {
    while ($cityRow = mysqli_fetch_assoc($cityResult)) {
        if (strpos($userMsg, strtolower($cityRow['name'])) !== false) {
            $city_id = $cityRow['id'];
            $explicit_city_found = true;
            break;
        }
    }
}






$question_indicators = ['?', 'kya', 'kaise', 'kaisa', 'kesa', 'kare', 'karein', 'chahiye', 'batao', 'bataiye', 'tips', 'guide', 'how', 'what', 'why', 'where', 'suggest'];
$is_question_or_advice = false;
foreach ($question_indicators as $qi) {
    if (preg_match('/\b' . preg_quote($qi, '/') . '\b/i', $userMsg) || strpos($userMsg, '?') !== false) {
        $is_question_or_advice = true;
        break;
    }
}


$explicit_search_phrases = ['pg in', 'room in', 'hostel in', 'flat in', 'stay in', 'pg near', 'room near', 'hostel near', 'find pg', 'search pg', 'book pg', 'book room'];
$has_search_phrase = false;
foreach ($explicit_search_phrases as $sp) {
    if (strpos($userMsg, $sp) !== false) {
        $has_search_phrase = true;
        break;
    }
}


$clean_msg = trim(preg_replace('/[^\w\s]/', '', $userMsg));
$word_count = count(explode(' ', $clean_msg));

$has_pg_keyword = false;
$explicit_pg_keywords = ['pg', 'hostel', 'rooms', 'room', 'flat', 'flats', 'paying guest'];
foreach ($explicit_pg_keywords as $kw) {
    if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $userMsg)) {
        $has_pg_keyword = true;
        break;
    }
}

$is_short_pg_query = ($has_pg_keyword && $word_count <= 3 && !$is_question_or_advice);
$has_filters = ($detected_gender !== null) || ($detected_budget !== null) || $check_wifi;
$is_only_city_name = ($explicit_city_found && $word_count <= 2 && !$has_pg_keyword);



if ($is_question_or_advice && !$has_search_phrase) {
    $is_pg_search = false;
} elseif ($has_search_phrase || $is_short_pg_query || $is_only_city_name || ($has_filters && $has_pg_keyword)) {
    $is_pg_search = true;
} else {
    $is_pg_search = false;
}





if (!$is_pg_search) {
    $ai_server_url = "http://localhost:3000/ask-ai";
    $post_data = json_encode(array("question" => $userMsg, "msg" => $userMsg));

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
        $rawReply = $response_data['reply'] ?? "Sorry, no response from AI.";


        $cleanReply = str_replace('\n', "\n", $rawReply);
        $formattedText = preg_replace('/\*\*(.*?)\*\*/s', '<b>$1</b>', $cleanReply);
        $formattedText = nl2br($formattedText);

        echo "<div style='background: #f0f4f8; margin: -10px -12px; padding: 10px 12px; border-left: 4px solid #fe5b5d; border-radius: 8px; font-size: 13.5px; line-height: 1.5; color: #222;'>";
        echo "<div style='color: #fe5b5d; font-weight: bold; font-size: 13.5px; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;'>🤖 NestFinder AI</div>";
        echo "<div>" . $formattedText . "</div>";
        echo "</div>";
    } else {
        echo "<div style='background: #f0f4f8; margin: -10px -12px; padding: 10px 12px; border-left: 4px solid #fe5b5d; border-radius: 8px; font-size: 13px; color: #d32f2f;'>⚠️ AI Backend is offline! Please run <b>node server.js</b> on port 3000.</div>";
    }
    exit();
}






function calculatePhpDistance($row)
{
    $userLat = (isset($_POST['user_lat']) && $_POST['user_lat'] !== 'null' && $_POST['user_lat'] !== '')
        ? floatval($_POST['user_lat'])
        : ($_SESSION['lat'] ?? $_SESSION['user_live_lat'] ?? 31.1048);

    $userLng = (isset($_POST['user_lng']) && $_POST['user_lng'] !== 'null' && $_POST['user_lng'] !== '')
        ? floatval($_POST['user_lng'])
        : ($_SESSION['lng'] ?? $_SESSION['user_live_lng'] ?? 77.1734);

    $cityCoordinates = [
        1 => ['lat' => 28.6139, 'lng' => 77.2090],
        2 => ['lat' => 19.0760, 'lng' => 72.8777],
        3 => ['lat' => 12.9347, 'lng' => 77.6141],
        4 => ['lat' => 17.3850, 'lng' => 78.4867],
        5 => ['lat' => 31.1048, 'lng' => 77.1734]
    ];

    $pgLat = !empty($row['latitude']) ? floatval($row['latitude']) : (!empty($row['lat']) ? floatval($row['lat']) : null);
    $pgLng = !empty($row['longitude']) ? floatval($row['longitude']) : (!empty($row['lng']) ? floatval($row['lng']) : null);

    if (!$pgLat || !$pgLng) {
        $cId = intval($row['city_id']);
        if (isset($cityCoordinates[$cId])) {
            $pgLat = $cityCoordinates[$cId]['lat'];
            $pgLng = $cityCoordinates[$cId]['lng'];
        } else {
            $pgLat = 12.9347;
            $pgLng = 77.6141;
        }
    }

    $R = 6371;
    $dLat = deg2rad($pgLat - $userLat);
    $dLon = deg2rad($pgLng - $userLng);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($userLat)) * cos(deg2rad($pgLat)) *
        sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($R * $c, 1);
}


$whereClauses = ["1=1"];

if ($explicit_city_found && $city_id) {
    $whereClauses[] = "city_id = " . intval($city_id);
}

if ($detected_gender !== null) {
    $whereClauses[] = "gender = '" . mysqli_real_escape_string($conn, $detected_gender) . "'";
}

if ($detected_budget !== null) {
    $whereClauses[] = "rent <= " . intval($detected_budget);
}

if ($check_wifi) {
    $whereClauses[] = "id IN (SELECT property_id FROM properties_amenities pa JOIN amenities a ON pa.amenity_id = a.id WHERE a.name LIKE '%wifi%')";
}

$sql = "SELECT * FROM properties WHERE " . implode(" AND ", $whereClauses) . " ORDER BY rent ASC LIMIT 3";
$result = mysqli_query($conn, $sql);


if ($result && mysqli_num_rows($result) > 0) {
    $reply = "Ji bilkul! Maine aapke parameters ke hisab se best PGs dhoondh liye hain: <br/><br/>";

    while ($row = mysqli_fetch_assoc($result)) {
        $distance = calculatePhpDistance($row);

        $reply .= "<div style='background: #f9f9f9; padding: 10px; border-radius: 8px; margin-bottom: 10px; border-left: 3px solid #fe5b5d;'>";
        $reply .= "🏠 <b>" . htmlspecialchars($row['name']) . "</b><br/>";
        $reply .= "• Rent: <b>₹" . number_format($row['rent']) . "/month</b><br/>";
        $reply .= "• Type: <span class='text-capitalize'><b>" . htmlspecialchars($row['gender']) . "</b></span><br/>";
        $reply .= "• Distance: 📍 <b>" . $distance . " KM away</b> aapki real location se.<br/>";
        $reply .= "<a href='property_detail.php?property_id=" . $row['id'] . "' class='badge badge-danger text-white mt-1'>View Room</a></div>";
    }

    echo $reply;
} else {
    echo "Mujhe aapke budget aur criteria mein exact match nahi mila, par aap Filter bar use karke thoda range badha kar check kar sakte hain! 😊";
}
exit();
