<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "includes/database_connect.php";

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : NULL;


$city_id = isset($_POST['city_id']) ? intval($_POST['city_id']) : 0;
$max_rent = isset($_POST['max_rent']) ? intval($_POST['max_rent']) : 25000;
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
$amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];
$sort_order = isset($_POST['sort_order']) ? strtoupper(trim($_POST['sort_order'])) : '';


$query = "SELECT * FROM properties WHERE city_id = ? AND rent <= ?";
$params = [$city_id, $max_rent];
$types = "ii";

if (!empty($gender) && $gender !== 'No Filter') {
    $query .= " AND gender = ?";
    $params[] = $gender;
    $types .= "s";
}

if (!empty($amenities)) {
    $placeholders = implode(',', array_fill(0, count($amenities), '?'));


    $query .= " AND id IN (
        SELECT property_id FROM properties_amenities 
        WHERE amenity_id IN ($placeholders) 
        GROUP BY property_id 
        HAVING COUNT(DISTINCT amenity_id) = ?
    )";

    foreach ($amenities as $amenity) {
        $params[] = intval($amenity);
        $types .= "i";
    }
    $params[] = count($amenities);
    $types .= "i";
}


if ($sort_order === "ASC" || $sort_order === "DESC") {

    $query .= " ORDER BY rent $sort_order";
}

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);



$sql_3 = "SELECT iup.property_id, iup.user_id FROM interested_users_properties iup
          INNER JOIN properties p ON iup.property_id = p.id
          WHERE p.city_id = ?";

$stmt_interested = mysqli_prepare($conn, $sql_3);
mysqli_stmt_bind_param($stmt_interested, "i", $city_id);
mysqli_stmt_execute($stmt_interested);
$result_3 = mysqli_stmt_get_result($stmt_interested);

$interested_cache = [];
while ($row_interested = mysqli_fetch_assoc($result_3)) {
    $p_id = intval($row_interested['property_id']);
    $u_id = intval($row_interested['user_id']);

    if (!isset($interested_cache[$p_id])) {
        $interested_cache[$p_id] = ['count' => 0, 'user_interested' => false];
    }
    $interested_cache[$p_id]['count']++;
    if ($user_id !== NULL && $u_id === $user_id) {
        $interested_cache[$p_id]['user_interested'] = true;
    }
}
mysqli_stmt_close($stmt_interested);


if (mysqli_num_rows($result) > 0) {
    while ($property = mysqli_fetch_assoc($result)) {
        $prop_id = intval($property['id']);


        if (!empty($property['image']) && file_exists("img/" . basename($property['image']))) {
            $img_src = "img/" . $property['image'];
        } else {
            $all_files = glob("img/properties/" . $prop_id . "/*");
            $valid_images = [];
            if ($all_files !== false) {
                foreach ($all_files as $file) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $valid_images[] = $file;
                    }
                }
            }
            $img_src = (count($valid_images) > 0) ? $valid_images[0] : "img/properties/1/1d4f0757fdb86d5f.jpg";
        }

        $total_rating = round(($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3, 1);
        $interested_users_count = isset($interested_cache[$prop_id]) ? $interested_cache[$prop_id]['count'] : 0;
        $is_interested = isset($interested_cache[$prop_id]) ? $interested_cache[$prop_id]['user_interested'] : false;

        $heart_class = $is_interested ? "is-interested-image fas fa-heart" : "is-interested-image far fa-heart";
        $gender_img = ($property['gender'] == "male") ? "img/male.png" : (($property['gender'] == "female") ? "img/female.png" : "img/unisex.png");
        $fake_vibe_count = ($interested_users_count > 0) ? $interested_users_count : rand(1, 2);


        echo '
        <div class="property-card property-id-' . $prop_id . ' row w-100 mx-0" data-pid="' . $prop_id . '">
            <div class="image-container col-md-4" style="padding: 0;">
                <img src="' . htmlspecialchars($img_src) . '" class="img-fluid" alt="Property Image" style="width: 100%; height: 100%; object-fit: cover;" />
            </div>
            <div class="content-container col-md-8">
                <div class="row no-gutters justify-content-between">
                    <div class="star-container" title="' . $total_rating . '">';
        for ($i = 0; $i < 5; $i++) {
            echo ($total_rating >= $i + 0.8) ? '<i class="fas fa-star"></i>' : (($total_rating >= $i + 0.3) ? '<i class="fas fa-star-half-alt"></i>' : '<i class="far fa-star"></i>');
        }
        echo '</div>
                    <div class="interested-container">
                        <i class="' . $heart_class . '" property_id="' . $prop_id . '"></i>
                        <div class="interested-text">
                            <span class="interested-user-count">' . $interested_users_count . '</span> interested
                        </div>
                    </div>
                </div>
                <div class="detail-container">
                    <div class="property-name">' . htmlspecialchars($property['name']) . '</div>
                    <div class="property-address">' . htmlspecialchars($property['address']) . '</div>
                    <div class="distance-badge-zone my-1" style="display:none;">
                        <span class="badge badge-info p-1 px-2 text-white"><i class="fas fa-map-marker-alt"></i> Away: <span class="distance-val-span">Calculating...</span></span>
                    </div>
                    <div class="my-2">
                        <span class="badge badge-warning text-dark p-2 open-roommate-matcher" data-propname="' . htmlspecialchars($property['name']) . '" data-vibe-count="' . $fake_vibe_count . '" style="cursor:pointer; font-size:0.8rem; border-radius:15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                           <i class="fas fa-users text-danger"></i> <b>' . $fake_vibe_count . ' Users</b> matching your vibe here!
                        </span>
                    </div>
                    <div class="property-gender">
                        <img src="' . htmlspecialchars($gender_img) . '" />
                    </div>
                </div>
                <div class="my-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input compare-chk" id="comp-chk-' . $prop_id . '" data-id="' . $prop_id . '" data-name="' . htmlspecialchars($property['name']) . '">
                        <label class="custom-control-label font-weight-bold text-secondary" for="comp-chk-' . $prop_id . '" style="cursor:pointer;">Add to Compare</label>
                    </div>
                </div>
                <div class="row no-gutters">
                    <div class="rent-container col-6">
                        <div class="rent">₹ ' . number_format($property['rent']) . '/-</div>
                        <div class="rent-unit">per month</div>
                    </div>
                    <div class="button-container col-6">
                        <a href="property_detail.php?property_id=' . $prop_id . '" class="btn btn-primary">View</a>
                    </div>
                </div>
            </div>
        </div>';
    }
} else {
    echo '<div class="no-property-container text-center py-5 w-100"><p class="text-muted">No PG found matching choices.</p></div>';
}


mysqli_stmt_close($stmt);
mysqli_close($conn);
