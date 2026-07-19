<?php
session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner') {
    header("Location: owner_dashboard.php");
    exit;
}
require "includes/database_connect.php";


$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;


$city_name = isset($_GET["city"]) ? trim($_GET["city"]) : "Delhi";


$sql_1 = "SELECT * FROM cities WHERE name = ?";
$stmt = mysqli_prepare($conn, $sql_1);
mysqli_stmt_bind_param($stmt, "s", $city_name);
mysqli_stmt_execute($stmt);
$result_1 = mysqli_stmt_get_result($stmt);

if (!$result_1) {
    echo "Something went wrong!";
    return;
}
$city = mysqli_fetch_assoc($result_1);
if (!$city) {
    echo "Sorry! We do not have any PG listed in this city.";
    return;
}
$city_id = $city['id'];


$sql_map = "SELECT id, name, rent, lat, lng FROM properties WHERE lat IS NOT NULL AND lng IS NOT NULL";
$result_map = mysqli_query($conn, $sql_map);

$map_properties = [];
while ($row = mysqli_fetch_assoc($result_map)) {
    $map_properties[$row['id']] = [
        "lat"  => floatval($row['lat']),
        "lng"  => floatval($row['lng']),
        "name" => $row['name'],
        "rent" => "₹" . number_format($row['rent'])
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Best PG's in <?php echo htmlspecialchars($city_name, ENT_QUOTES, 'UTF-8'); ?> | PG Life</title>
    <?php
    include "includes/head_links.php";
    ?>
    <link href="css/property_list.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        .compare-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #2c3e50;
            color: #fff;
            padding: 15px;
            z-index: 9999;
            box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.3);
            display: none;
        }

        .compare-item-thumb {
            background: #34495e;
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 10px;
            display: inline-block;
        }

        .remove-compare-item {
            color: #e74c3c;
            cursor: pointer;
            margin-left: 8px;
            font-weight: bold;
        }


        .ai-chat-master-container {
            position: fixed;
            bottom: 80px;
            right: 25px;
            width: 330px;
            height: 430px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            display: none;
            flex-direction: column;
            z-index: 10000;
            border: 1px solid #dee2e6;
            overflow: hidden;
        }

        .ai-chat-header {
            background: linear-gradient(135deg, #ff5a5f, #d9393e);
            color: white;
            padding: 12px 15px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .ai-chat-body {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f8f9fa;
            font-size: 0.9rem;
        }

        .chat-bubble {
            padding: 8px 12px;
            border-radius: 15px;
            margin-bottom: 10px;
            max-width: 85%;
            word-wrap: break-word;
        }

        .chat-bubble.bot {
            background: #e9ecef;
            color: #333;
            align-self: flex-start;
            border-top-left-radius: 2px;
        }

        .chat-bubble.user {
            background: #ff5a5f;
            color: white;
            margin-left: auto;
            border-top-right-radius: 2px;
        }

        .ai-chat-footer {
            padding: 10px;
            background: white;
            border-top: 1px solid #eee;
            display: flex;
        }

        .ai-chat-btn-trigger {
            position: fixed;
            bottom: 20px;
            right: 25px;
            background: #ff5a5f;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(255, 90, 95, 0.4);
            z-index: 10000;
            transition: transform 0.2s ease;
        }

        .ai-chat-btn-trigger:hover {
            transform: scale(1.08);
            color: white;
        }

        .sort-active {
            color: #ff5a5f !important;
            font-weight: bold;
        }


        #liveMapToggleViewZone {
            width: 100%;
            height: 500px;
            border-radius: 12px;
            margin-top: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            border: 2px solid #dee2e6;
            display: none;
        }


        @media (max-width: 576px) {
            .action-btn-mobile {
                width: 100% !important;
                margin-bottom: 10px;
                font-size: 0.95rem !important;
                padding: 10px 0 !important;
            }
        }
    </style>
</head>

<body id="property-list-page"
    data-city-id="<?php echo $city_id; ?>"
    data-map-properties="<?php echo htmlspecialchars(json_encode($map_properties), ENT_QUOTES, 'UTF-8'); ?>">

    <?php
    include "includes/header.php";
    ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="index.php">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars($city_name, ENT_QUOTES, 'UTF-8'); ?>
            </li>
        </ol>
    </nav>

    <div class="page-container">
        <div class="container my-3">
            <div class="row align-items-center">
                <div class="col-sm-6 col-12 text-sm-left text-center mb-2 mb-sm-0">
                    <button class="btn btn-dark shadow-sm font-weight-bold btn-sm rounded-pill px-3 action-btn-mobile" id="toggleMapDashboardViewBtn" style="width: auto;">
                        <i class="fas fa-map-marked-alt text-warning"></i> Switch to Map View
                    </button>
                </div>
                <div class="col-sm-6 col-12 text-sm-right text-center">
                    <button class="btn btn-info shadow-sm font-weight-bold btn-sm rounded-pill px-3 action-btn-mobile" id="getDistanceMasterBtn" style="width: auto;">
                        <i class="fas fa-street-view"></i> Calculate Live Distance
                    </button>
                </div>
            </div>
        </div>

        <div class="container">
            <div id="liveMapToggleViewZone"></div>
        </div>

        <div class="filter-bar row justify-content-around" id="standardFilterBarLayout">
            <div class="col-auto" data-toggle="modal" data-target="#filter-modal" style="cursor: pointer;">
                <img src="img/filter.png" alt="filter" />
                <span>Filter</span>
            </div>
            <div class="col-auto" id="sortDescBtn" style="cursor: pointer;">
                <img src="img/desc.png" alt="sort-desc" />
                <span>Highest rent first</span>
            </div>
            <div class="col-auto" id="sortAscBtn" style="cursor: pointer;">
                <img src="img/asc.png" alt="sort-asc" />
                <span>Lowest rent first</span>
            </div>
        </div>

        <div class="container">
            <div id="dynamic-properties-wrapper" class="row">
                <div class="w-100 text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading PGs...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ai-chat-btn-trigger" id="openChatbotBubble">
        <i class="fas fa-comments"></i>
    </div>

    <div class="ai-chat-master-container" id="chatbotWindowBox">
        <div class="ai-chat-header">
            <span><i class="fas fa-robot"></i> PG Life Smart AI</span>
            <span style="cursor: pointer;" id="closeChatbotBox">&times;</span>
        </div>
        <div class="ai-chat-body d-flex flex-column" id="chatMessageFlowArea">
            <div class="chat-bubble bot">
                Hello! 👋 Main aapki perfect PG dhoodhne mein madad kar sakta hoon. Mujhe batayein aapko kis budget ya facility ka PG chahiye?
            </div>
        </div>
        <div class="ai-chat-footer">
            <input type="text" id="userChatInput" class="form-control form-control-sm" placeholder="Ask me anything (e.g., wifi, male)..." style="border-radius: 20px;">
            <button class="btn btn-sm btn-danger ml-2 rounded-circle px-2" id="sendChatMsgBtn"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <div class="compare-bar" id="compareFloatingBar">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold mr-3">Compare PGs (<span id="compareCount">0</span>/3 selected):</span>
                <div id="compareBadges" class="d-inline-block"></div>
            </div>
            <div>
                <button class="btn btn-warning btn-sm mr-2" id="clearCompareBtn">Clear All</button>
                <button class="btn btn-success btn-sm" id="launchCompareModalBtn">Compare Now</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="filter-modal" tabindex="-1" role="dialog" aria-labelledby="filter-heading" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="filter-heading">Filters</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <h5>Gender</h5>
                    <hr />
                    <div id="gender-btn-group" class="mb-4">
                        <button class="btn btn-outline-dark btn-active gender-filter-btn" data-gender="No Filter">No Filter</button>
                        <button class="btn btn-outline-dark gender-filter-btn" data-gender="unisex"><i class="fas fa-venus-mars"></i> Unisex</button>
                        <button class="btn btn-outline-dark gender-filter-btn" data-gender="male"><i class="fas fa-mars"></i> Male</button>
                        <button class="btn btn-outline-dark gender-filter-btn" data-gender="female"><i class="fas fa-venus"></i> Female</button>
                    </div>

                    <h5>Rent Range</h5>
                    <hr />
                    <div class="mb-4">
                        <label for="rentSlider" class="form-label">Max Rent: <span id="rentValue" class="font-weight-bold text-primary">₹25,000</span></label>
                        <input type="range" class="form-control-range" id="rentSlider" min="2000" max="25000" step="500" value="25000">
                    </div>

                    <h5 class="mt-4">Amenities</h5>
                    <hr />
                    <div class="form-check mb-2">
                        <input class="form-check-input amenity-filter-chk" type="checkbox" value="1" id="chk-wifi">
                        <label class="form-check-label" for="chk-wifi">Wifi</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input amenity-filter-chk" type="checkbox" value="2" id="chk-ac">
                        <label class="form-check-label" for="chk-ac">AC</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input amenity-filter-chk" type="checkbox" value="3" id="chk-food">
                        <label class="form-check-label" for="chk-food">Food / Mess</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button data-dismiss="modal" class="btn btn-success px-4">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="compare-results-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">PG Side-by-Side Comparison</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body table-responsive" id="compareMatrixTableBody">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="roommateMatcherModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-handshake"></i> Roommate Matcher Profiles</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <p class="text-muted small px-2">
                        Yeh users is PG (<b id="matchTargetPgName"></b>) mein interested hain aur inki preferences aapki profile se match karti hain!
                    </p>
                    <hr>
                    <div id="roommateProfilesContainer" class="text-left"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="list-group">
        <?php
        $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;


        $sql_default_prop = "SELECT id FROM properties WHERE city_id = '$city_id' LIMIT 1";
        $res_default_prop = mysqli_query($conn, $sql_default_prop);
        $default_prop = mysqli_fetch_assoc($res_default_prop);
        $property_id = $default_prop ? $default_prop['id'] : 1;

        $sql_roommates = "SELECT * FROM users WHERE role = 'user' AND id != '$current_user_id' LIMIT 5";
        $result_roommates = mysqli_query($conn, $sql_roommates);

        if (mysqli_num_rows($result_roommates) > 0) {
            while ($roommate = mysqli_fetch_assoc($result_roommates)) {
                $target_id = $roommate['id'];

                $btn_text = "Connect";
                $btn_class = "btn-danger";
                $disabled = "";

                $is_connected = false;
                $is_received = false;

                if ($current_user_id > 0) {

                    $sql_status = "SELECT * FROM roommate_connections 
               WHERE (user_id = ? AND target_user_id = ?) 
                  OR (user_id = ? AND target_user_id = ?)";

                    $stmt_status = mysqli_prepare($conn, $sql_status);

                    if ($stmt_status) {

                        mysqli_stmt_bind_param($stmt_status, "iiii", $current_user_id, $target_id, $target_id, $current_user_id);

                        mysqli_stmt_execute($stmt_status);



                        $res_status = mysqli_stmt_get_result($stmt_status);
                    } else {

                        $res_status = false;
                    }
                    if ($row_status = mysqli_fetch_assoc($res_status)) {
                        if ($row_status['status'] == 'pending') {
                            if ($row_status['user_id'] == $current_user_id) {
                                $btn_text = "Requested";
                                $btn_class = "btn-warning";
                                $disabled = "disabled";
                            } else {
                                $btn_text = "Accept Request";
                                $btn_class = "btn-success";
                                $is_received = true;
                            }
                        } elseif ($row_status['status'] == 'accepted') {
                            $btn_text = "💬 Chat";
                            $btn_class = "btn-info";
                            $is_connected = true;
                        }
                    }
                }
        ?>

                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-left">
                    <div>
                        <h6 class="mb-1 font-weight-bold">
                            <?php echo htmlspecialchars($roommate['name'] ?? $roommate['full_name']); ?>
                            <span class="badge badge-success ml-2">95% Match</span>
                        </h6>
                        <span class="badge badge-secondary">Late Night Owl</span>
                        <span class="badge badge-secondary">Studious</span>
                        <span class="badge badge-secondary">Non-Smoker</span>
                    </div>

                    <?php if ($is_connected): ?>
                        <a href="chatroom.php?roommate_id=<?php echo $target_id; ?>" class="btn btn-sm <?php echo $btn_class; ?> rounded-pill px-3">
                            <?php echo $btn_text; ?>
                        </a>
                    <?php elseif ($is_received): ?>
                        <button class="btn btn-sm <?php echo $btn_class; ?> rounded-pill px-3 accept-vibe-btn"
                            data-roommate-id="<?php echo $target_id; ?>"
                            data-property-id="<?php echo $property_id; ?>">
                            <?php echo $btn_text; ?>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-sm <?php echo $btn_class; ?> rounded-pill px-3 connect-vibe-btn"
                            data-roommate-id="<?php echo $target_id; ?>"
                            data-property-id="<?php echo $property_id; ?>"
                            <?php echo $disabled; ?>>
                            <?php echo $btn_text; ?>
                        </button>
                    <?php endif; ?>
                </div>

        <?php
            }
        }
        ?>
    </div>

    <?php
    include "includes/signup_modal.php";
    include "includes/login_modal.php";
    include "includes/footer.php";
    ?>

    <script type="text/javascript" src="js/property_list.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        $(document).ready(function() {

            const currentCityId = $('#property-list-page').data('city-id');
            const propertyCoordinates = $('#property-list-page').data('map-properties');

            let selectedGender = "No Filter";
            let compareList = [];
            let userLat = null,
                userLng = null;
            let currentSortOrder = "";
            let mapObject = null;
            let isMapActive = false;


            $('#toggleMapDashboardViewBtn').on('click', function() {
                if (!isMapActive) {
                    $(this).html('<i class="fas fa-th-list text-warning"></i> Switch to Cards View');
                    $('#dynamic-properties-wrapper, #standardFilterBarLayout').hide();
                    $('#liveMapToggleViewZone').fadeIn();
                    isMapActive = true;

                    if (mapObject === null) {
                        let firstPropId = Object.keys(propertyCoordinates)[0];
                        let centerLat = firstPropId ? propertyCoordinates[firstPropId].lat : 28.6289;
                        let centerLng = firstPropId ? propertyCoordinates[firstPropId].lng : 77.2152;

                        mapObject = L.map('liveMapToggleViewZone').setView([centerLat, centerLng], 11);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(mapObject);

                        Object.keys(propertyCoordinates).forEach(function(id) {
                            let pg = propertyCoordinates[id];
                            L.marker([pg.lat, pg.lng]).addTo(mapObject)
                                .bindPopup(`
                            <div style='font-family:sans-serif;'>
                                <b>${pg.name}</b><br/>
                                Rent: <span style='color:#ff5a5f;font-weight:bold;'>${pg.rent}/mo</span><br/>
                                <a href='property_detail.php?property_id=${id}' style='display:inline-block; margin-top:5px; background:#ff5a5f; color:white; padding:2px 8px; border-radius:4px; font-size:11px; text-decoration:none;'>View Details</a>
                            </div>
                            `);
                        });
                    }
                    setTimeout(() => {
                        mapObject.invalidateSize();
                    }, 200);
                } else {
                    $(this).html('<i class="fas fa-map-marked-alt text-warning"></i> Switch to Map View');
                    $('#liveMapToggleViewZone').hide();
                    $('#dynamic-properties-wrapper, #standardFilterBarLayout').fadeIn();
                    isMapActive = false;
                }
            });


            $(document).on('click', '.open-roommate-matcher', function() {
                let targetedPg = $(this).data('propname');
                let vibeCount = parseInt($(this).data('vibe-count')) || 1;

                $('#matchTargetPgName').text(targetedPg);

                let profilePool = [{
                        name: "Rahul Sharma",
                        match: "95% Match",
                        class: "badge-success",
                        tags: ["Late Night Owl", "Studious", "Non-Smoker"]
                    },
                    {
                        name: "Aman Verma",
                        match: "82% Match",
                        class: "badge-info",
                        tags: ["Early Bird", "Veg Only", "Gym Enthusiast"]
                    },
                    {
                        name: "Sneha Reddy",
                        match: "78% Match",
                        class: "badge-warning text-dark",
                        tags: ["Music Lover", "Coding", "Friendly"]
                    }
                ];

                let profilesHtml = '';

                for (let i = 0; i < vibeCount; i++) {
                    let profile = profilePool[i % profilePool.length];

                    let tagsHtml = '';
                    profile.tags.forEach(function(tag) {
                        tagsHtml += `<span class="badge badge-secondary mr-1 py-1 px-2 small font-weight-normal text-white" style="background-color: #6c757d;">${tag}</span>`;
                    });

                    profilesHtml += `
                <div class="p-3 mb-3 border rounded shadow-sm d-flex justify-content-between align-items-center bg-white">
                    <div>
                        <h6 class="font-weight-bold mb-1" style="color: #333;">${profile.name} 
                            <span class="badge ${profile.class} ml-1" style="font-size: 0.75rem;">${profile.match}</span>
                        </h6>
                        <div class="mt-2">
                            ${tagsHtml}
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-sm text-white font-weight-bold px-3 shadow-sm" style="background-color: #ff6f3c; border-radius: 20px;">Connect</button>
                    </div>
                </div>
            `;
                }

                $('#roommateProfilesContainer').html(profilesHtml);
                $('#roommateMatcherModal').modal('show');
            });

            $(document).on('click', '.connect-vibe-btn', function() {
                var $btn = $(this);
                var roommateId = $btn.attr('data-roommate-id');
                var propertyId = $btn.attr('data-property-id');

                $.ajax({
                    url: "connect_roommate.php",
                    method: "POST",
                    dataType: "json",
                    data: {
                        roommate_id: roommateId,
                        property_id: propertyId
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $btn.removeClass('btn-danger').addClass('btn-success').html('<i class="fas fa-check"></i> Requested').attr('disabled', true);
                            alert(response.message);
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("Server Error!");
                    }
                });
            });

            $(document).on('click', '.accept-vibe-btn', function() {
                var btn = $(this);
                var roommateId = btn.data('roommate-id');
                var propertyId = btn.data('property-id');

                $.ajax({
                    url: 'accept_request.php',
                    type: 'POST',
                    data: {
                        roommate_id: roommateId,
                        property_id: propertyId
                    },
                    success: function(response) {
                        if (response.trim() === 'success') {
                            location.reload();
                        } else {
                            alert("Error: Request accept nahi ho payi. Response: " + response);
                        }
                    },
                    error: function() {
                        alert("Server se connection fail ho gaya!");
                    }
                });
            });

            $('#sortDescBtn').on('click', function() {
                $('#sortAscBtn').removeClass('sort-active');
                $(this).addClass('sort-active');
                currentSortOrder = "DESC";
                fetchFilteredData();
            });

            $('#sortAscBtn').on('click', function() {
                $('#sortDescBtn').removeClass('sort-active');
                $(this).addClass('sort-active');
                currentSortOrder = "ASC";
                fetchFilteredData();
            });

            $('#openChatbotBubble').on('click', function() {
                $('#chatbotWindowBox').css('display', 'flex').fadeIn();
                $(this).hide();
            });

            $('#closeChatbotBox').on('click', function() {
                $('#chatbotWindowBox').fadeOut(function() {
                    $('#openChatbotBubble').show();
                });
            });

            function processingUserChatMessage() {
                let userMsg = $('#userChatInput').val().trim();
                if (userMsg === "") return;


                $('#chatMessageFlowArea').append(`<div class="chat-bubble user">${userMsg}</div>`);
                $('#userChatInput').val('');
                $('#chatMessageFlowArea').scrollTop($('#chatMessageFlowArea')[0].scrollHeight);



                let loadingId = 'loading-' + Math.floor(Math.random() * 10000);
                $('#chatMessageFlowArea').append(
                    `<div id="${loadingId}" class="chat-bubble bot text-muted">
            <small><i>🤖 AI is thinking (it may take 10-15s)...</i></small>
        </div>`
                );
                $('#chatMessageFlowArea').scrollTop($('#chatMessageFlowArea')[0].scrollHeight);


                $.ajax({
                    url: 'chat_helper.php',
                    method: 'POST',
                    data: {
                        msg: userMsg,
                        city_id: typeof currentCityId !== 'undefined' ? currentCityId : 1
                    },
                    success: function(botReply) {

                        $('#' + loadingId).remove();


                        $('#chatMessageFlowArea').append(`<div class="chat-bubble bot">${botReply}</div>`);
                        $('#chatMessageFlowArea').scrollTop($('#chatMessageFlowArea')[0].scrollHeight);
                    },
                    error: function() {

                        $('#' + loadingId).remove();
                        $('#chatMessageFlowArea').append(`<div class="chat-bubble bot text-danger">Server timeout. Cannot reach AI right now.</div>`);
                        $('#chatMessageFlowArea').scrollTop($('#chatMessageFlowArea')[0].scrollHeight);
                    }
                });
            }

            $('#sendChatMsgBtn').on('click', function() {
                processingUserChatMessage();
            });
            $('#userChatInput').on('keypress', function(e) {
                if (e.which === 13) processingUserChatMessage();
            });

            function getHaversineDistance(lat1, lon1, lat2, lon2) {
                const R = 6371;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c;
            }

            function triggerDistanceLoop() {
                if (!userLat || !userLng) return;
                $('.property-card').each(function() {
                    let pid = $(this).data('pid');
                    $(this).find('.distance-badge-zone').show();
                    if (propertyCoordinates[pid]) {
                        let pgLat = propertyCoordinates[pid].lat;
                        let pgLng = propertyCoordinates[pid].lng;
                        let calculatedDist = getHaversineDistance(userLat, userLng, pgLat, pgLng);
                        $(this).find('.distance-val-span').text(calculatedDist.toFixed(1) + ' KM');
                    } else {
                        let randomFallbackDist = (Math.random() * (4.5 - 1.2) + 1.2).toFixed(1);
                        $(this).find('.distance-val-span').text(randomFallbackDist + ' KM');
                    }
                });
            }

            $('#getDistanceMasterBtn').on('click', function() {
                let buttonObj = $(this);
                if (navigator.geolocation) {
                    buttonObj.html('<span class="spinner-border spinner-border-sm"></span> Locating...').prop('disabled', true);
                    navigator.geolocation.getCurrentPosition(function(position) {
                        userLat = position.coords.latitude;
                        userLng = position.coords.longitude;

                        $.post('save_session_location.php', {
                            lat: userLat,
                            lng: userLng
                        });

                        buttonObj.html('<i class="fas fa-check-circle"></i> Distance Active').removeClass('btn-info').addClass('btn-success');
                        triggerDistanceLoop();
                    }, function(error) {
                        alert("Geolocation permissions rejected.");
                        buttonObj.html('<i class="fas fa-street-view"></i> Calculate Live Distance').prop('disabled', false);
                    });
                }
            });

            function fetchFilteredData() {
                let maxRentVal = $('#rentSlider').val();
                $('#rentValue').text('₹' + Number(maxRentVal).toLocaleString('en-IN'));

                let checkedAmenities = [];
                $('.amenity-filter-chk:checked').each(function() {
                    checkedAmenities.push($(this).val());
                });

                $.ajax({
                    url: 'fetch_filtered_pgs.php',
                    method: 'POST',
                    data: {
                        city_id: currentCityId,
                        max_rent: maxRentVal,
                        gender: selectedGender,
                        amenities: checkedAmenities,
                        sort_order: currentSortOrder
                    },
                    success: function(dataResponse) {
                        $('#dynamic-properties-wrapper').html(dataResponse);
                        updateCheckboxStates();
                        triggerDistanceLoop();
                    },
                    error: function() {
                        $('#dynamic-properties-wrapper').html('<div class="w-100 text-center text-danger my-4">Error loading data.</div>');
                    }
                });
            }

            function updateCheckboxStates() {
                $('.compare-chk').each(function() {
                    let id = $(this).data('id');
                    if (compareList.some(item => item.id == id)) {
                        $(this).prop('checked', true);
                    }
                });
            }

            function renderCompareBar() {
                if (compareList.length === 0) {
                    $('#compareFloatingBar').slideUp();
                    return;
                }
                $('#compareCount').text(compareList.length);
                $('#compareBadges').empty();
                compareList.forEach(item => {
                    $('#compareBadges').append(`
                    <span class="compare-item-thumb">${item.name} <span class="remove-compare-item" data-id="${item.id}">&times;</span></span>
                `);
                });
                $('#compareFloatingBar').slideDown();
            }

            $(document).on('change', '.compare-chk', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                if ($(this).is(':checked')) {
                    if (compareList.length >= 3) {
                        alert("Maximum 3 PGs can be selected.");
                        $(this).prop('checked', false);
                        return;
                    }
                    compareList.push({
                        id: id,
                        name: name
                    });
                } else {
                    compareList = compareList.filter(item => item.id != id);
                }
                renderCompareBar();
            });

            $(document).on('click', '.remove-compare-item', function() {
                let id = $(this).data('id');
                compareList = compareList.filter(item => item.id != id);
                $(`.compare-chk[data-id="${id}"]`).prop('checked', false);
                renderCompareBar();
            });

            $('#clearCompareBtn').on('click', function() {
                compareList = [];
                $('.compare-chk').prop('checked', false);
                renderCompareBar();
            });

            $('#launchCompareModalBtn').on('click', function() {
                let targetIds = compareList.map(item => item.id);
                $('#compareMatrixTableBody').html('<div class="text-center py-4"><div class="spinner-border text-success"></div></div>');
                $('#compare-results-modal').modal('show');
                $.ajax({
                    url: 'get_compare_matrix.php',
                    method: 'POST',
                    data: {
                        ids: targetIds
                    },
                    success: function(tableMarkup) {
                        $('#compareMatrixTableBody').html(tableMarkup);
                    },
                    error: function() {
                        $('#compareMatrixTableBody').html('<p class="text-danger">Failed to process matrices.</p>');
                    }
                });
            });

            $(document).on('click', '.gender-filter-btn', function() {
                $('.gender-filter-btn').removeClass('btn-active');
                $(this).addClass('btn-active');
                selectedGender = $(this).data('gender');
                fetchFilteredData();
            });

            $(document).on('change', '#rentSlider, .amenity-filter-chk', function() {
                fetchFilteredData();
            });

            fetchFilteredData();
        });
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (typeof window.performance != 'undefined' && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });
    </script>
</body>

</html>