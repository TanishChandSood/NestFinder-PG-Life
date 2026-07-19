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

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : NULL;


$property_id = isset($_GET["property_id"]) ? intval($_GET["property_id"]) : 0;

if ($property_id <= 0) {
    echo "Something went wrong! Invalid Property ID.";
    return;
}


$sql_update = "UPDATE properties SET views = views + 1 WHERE id = ?";
$stmt_update = mysqli_prepare($conn, $sql_update);
mysqli_stmt_bind_param($stmt_update, "i", $property_id);
mysqli_stmt_execute($stmt_update);
mysqli_stmt_close($stmt_update);


$sql_1 = "SELECT *, p.id AS property_id, p.name AS property_name, c.name AS city_name 
            FROM properties p
            INNER JOIN cities c ON p.city_id = c.id 
            WHERE p.id = ?";
$stmt_1 = mysqli_prepare($conn, $sql_1);
mysqli_stmt_bind_param($stmt_1, "i", $property_id);
mysqli_stmt_execute($stmt_1);
$result_1 = mysqli_stmt_get_result($stmt_1);

if (!$result_1) {
    echo "Something went wrong!";
    return;
}
$property = mysqli_fetch_assoc($result_1);
if (!$property) {
    echo "Property not found!";
    return;
}
mysqli_stmt_close($stmt_1);



$sql_2 = "SELECT * FROM testimonials WHERE property_id = ?";
$stmt_2 = mysqli_prepare($conn, $sql_2);
mysqli_stmt_bind_param($stmt_2, "i", $property_id);
mysqli_stmt_execute($stmt_2);
$result_2 = mysqli_stmt_get_result($stmt_2);
$testimonials = mysqli_fetch_all($result_2, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_2);



$sql_3 = "SELECT a.* FROM amenities a
            INNER JOIN properties_amenities pa ON a.id = pa.amenity_id
            WHERE pa.property_id = ?";
$stmt_3 = mysqli_prepare($conn, $sql_3);
mysqli_stmt_bind_param($stmt_3, "i", $property_id);
mysqli_stmt_execute($stmt_3);
$result_3 = mysqli_stmt_get_result($stmt_3);
$amenities = mysqli_fetch_all($result_3, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_3);



$sql_4 = "SELECT * FROM interested_users_properties WHERE property_id = ?";
$stmt_4 = mysqli_prepare($conn, $sql_4);
mysqli_stmt_bind_param($stmt_4, "i", $property_id);
mysqli_stmt_execute($stmt_4);
$result_4 = mysqli_stmt_get_result($stmt_4);
$interested_users = mysqli_fetch_all($result_4, MYSQLI_ASSOC);
$interested_users_count = mysqli_num_rows($result_4);
mysqli_stmt_close($stmt_4);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($property['property_name']); ?> | PG Life</title>

    <?php
    include "includes/head_links.php";
    ?>
    <link href="css/property_detail.css" rel="stylesheet" />
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
            <li class="breadcrumb-item">
                <a href="property_list.php?city=<?= urlencode($property['city_name']); ?>"><?= htmlspecialchars($property['city_name']); ?></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= htmlspecialchars($property['property_name']); ?>
            </li>
        </ol>
    </nav>

    <div id="property-images" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php
            $property_images = glob("img/properties/" . intval($property['property_id']) . "/*");
            foreach ($property_images as $index => $property_image) {
            ?>
                <li data-target="#property-images" data-slide-to="<?= $index ?>" class="<?= $index == 0 ? "active" : ""; ?>"></li>
            <?php
            }
            ?>
        </ol>
        <div class="carousel-inner">
            <?php
            foreach ($property_images as $index => $property_image) {
            ?>
                <div class="carousel-item <?= $index == 0 ? "active" : ""; ?>">
                    <img class="d-block w-100" src="<?= htmlspecialchars($property_image) ?>" alt="slide">
                </div>
            <?php
            }
            ?>
        </div>
        <a class="carousel-control-prev" href="#property-images" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#property-images" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <div class="property-summary page-container">
        <div class="row no-gutters justify-content-between align-items-center mb-2">
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
                <?php
                $is_interested = false;
                foreach ($interested_users as $interested_user) {
                    if ($interested_user['user_id'] == $user_id) {
                        $is_interested = true;
                    }
                }

                if ($is_interested) {
                ?>
                    <i class="is-interested-image fas fa-heart"></i>
                <?php
                } else {
                ?>
                    <i class="is-interested-image far fa-heart"></i>
                <?php
                }
                ?>
                <div class="interested-text">
                    <span class="interested-user-count"><?= intval($interested_users_count) ?></span> interested
                </div>
            </div>
        </div>

        <div class="row no-gutters mb-3">
            <div class="detail-container col-12">
                <div class="property-name"><?= htmlspecialchars($property['property_name']) ?></div>
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
        </div>

        <div class="row no-gutters justify-content-between align-items-center w-100 m-0">
            <div class="rent-container col-auto p-0">
                <div class="rent">₹ <?= number_format($property['rent']) ?>/-</div>
                <div class="rent-unit">per month</div>
            </div>

            <div class="button-container col-auto d-flex align-items-center p-0" style="gap: 10px; width: auto !important; max-width: none !important;">
                <button type="button" class="btn btn-teal" data-toggle="modal" data-target="#bookingSuccessModal" style="background-color: #4dbda5; color: white; border: none; padding: 8px 20px; border-radius: 4px;">
                    Book Now
                </button>
                <button type="button" class="btn text-nowrap" style="background-color: #4db6ac; color: white; font-weight: 500; padding: 8px 16px; font-size: 14px; border-radius: 4px; white-space: nowrap !important; width: auto !important; max-width: none !important; min-width: max-content !important;" data-toggle="modal" data-target="#bookTourModal">
                    🗓️ Book a Tour Slot
                </button>
            </div>
        </div>

        <div class="modal fade" id="bookTourModal" tabindex="-1" role="dialog" aria-labelledby="bookTourModalLabel" aria-hidden="true" style="color: #333; text-align: left;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                    <div class="modal-header" style="background-color: #f8f9fa;">
                        <h5 class="modal-title font-weight-bold" id="bookTourModalLabel" style="color: #2c3e50;">Schedule Your Tour Slot</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="tour-booking-form">
                        <div class="modal-body p-4">
                            <input type="hidden" name="property_id" value="<?= intval($property_id); ?>">

                            <div class="form-group mb-3">
                                <label for="booking_date" class="font-weight-bold small text-uppercase text-muted">Select Date</label>
                                <input type="date" id="booking_date" name="booking_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="form-group mb-3">
                                <label for="booking_time" class="font-weight-bold small text-uppercase text-muted">Select Custom Time</label>
                                <input type="time" id="booking_time" name="booking_time" class="form-control" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-uppercase text-muted">Tour Type</label>
                                <select name="tour_type" class="form-control" required>
                                    <option value="Virtual">💻 Virtual Tour (Video Call)</option>
                                    <option value="Physical">🚶 Physical Visit (On-site)</option>
                                </select>
                            </div>

                            <div id="booking-alert-msg" class="alert d-none mt-3 py-2 small"></div>
                        </div>
                        <div class="modal-footer" style="background-color: #f8f9fa;">
                            <button type="button" class="btn btn-light btn-sm font-weight-bold" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm" style="background-color: #34495e; color: white; font-weight: bold; padding: 6px 15px;">Confirm Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="property-amenities">
        <div class="page-container">
            <h1>Amenities</h1>
            <div class="row justify-content-between">
                <div class="col-md-auto">
                    <h5>Building</h5>
                    <?php
                    foreach ($amenities as $amenity) {
                        if ($amenity['type'] == "Building") {
                    ?>
                            <div class="amenity-container">
                                <img src="img/amenities/<?= htmlspecialchars($amenity['icon']) ?>.svg">
                                <span><?= htmlspecialchars($amenity['name']) ?></span>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>

                <div class="col-md-auto">
                    <h5>Common Area</h5>
                    <?php
                    foreach ($amenities as $amenity) {
                        if ($amenity['type'] == "Common Area") {
                    ?>
                            <div class="amenity-container">
                                <img src="img/amenities/<?= htmlspecialchars($amenity['icon']) ?>.svg">
                                <span><?= htmlspecialchars($amenity['name']) ?></span>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>

                <div class="col-md-auto">
                    <h5>Bedroom</h5>
                    <?php
                    foreach ($amenities as $amenity) {
                        if ($amenity['type'] == "Bedroom") {
                    ?>
                            <div class="amenity-container">
                                <img src="img/amenities/<?= htmlspecialchars($amenity['icon']) ?>.svg">
                                <span><?= htmlspecialchars($amenity['name']) ?></span>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>

                <div class="col-md-auto">
                    <h5>Washroom</h5>
                    <?php
                    foreach ($amenities as $amenity) {
                        if ($amenity['type'] == "Washroom") {
                    ?>
                            <div class="amenity-container">
                                <img src="img/amenities/<?= htmlspecialchars($amenity['icon']) ?>.svg">
                                <span><?= htmlspecialchars($amenity['name']) ?></span>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="property-about page-container">
        <h1>About the Property</h1>
        <p><?= htmlspecialchars($property['description']) ?></p>
    </div>

    <div class="property-rating">
        <div class="page-container">
            <h1>Property Rating</h1>
            <div class="row align-items-center justify-content-between">
                <div class="col-md-6">
                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fas fa-broom"></i>
                            <span class="rating-criteria-text">Cleanliness</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="<?= $property['rating_clean'] ?>">
                            <?php
                            $rating = $property['rating_clean'];
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
                    </div>

                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fas fa-utensils"></i>
                            <span class="rating-criteria-text">Food Quality</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="<?= $property['rating_food'] ?>">
                            <?php
                            $rating = $property['rating_food'];
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
                    </div>

                    <div class="rating-criteria row">
                        <div class="col-6">
                            <i class="rating-criteria-icon fa fa-lock"></i>
                            <span class="rating-criteria-text">Safety</span>
                        </div>
                        <div class="rating-criteria-star-container col-6" title="<?= $property['rating_safety'] ?>">
                            <?php
                            $rating = $property['rating_safety'];
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
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="rating-circle">
                        <?php
                        $total_rating = ($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3;
                        $total_rating = round($total_rating, 1);
                        ?>
                        <div class="total-rating"><?= $total_rating ?></div>
                        <div class="rating-circle-star-container">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="property-testimonials page-container">
        <h1>What people say</h1>
        <?php
        foreach ($testimonials as $testimonial) {
        ?>
            <div class="testimonial-block">
                <div class="testimonial-image-container">
                    <img class="testimonial-img" src="img/man.png">
                </div>
                <div class="testimonial-text">
                    <i class="fa fa-quote-left" aria-hidden="true"></i>
                    <p><?= htmlspecialchars($testimonial['content']) ?></p>
                </div>
                <div class="testimonial-name">- <?= htmlspecialchars($testimonial['user_name']) ?></div>
            </div>
        <?php
        }
        ?>
    </div>
    <div class="modal fade" id="bookingSuccessModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 10px; border: none; box-shadow: 0px 5px 15px rgba(0,0,0,0.2);">

                <div class="modal-header" style="border-bottom: none; background-color: #f8f9fa; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                    <h5 class="modal-title" id="bookingModalLabel" style="color: #4dbda5; font-weight: bold;">
                        <i class="fas fa-check-circle"></i> Booking Request Sent!
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center" style="padding: 30px 20px;">
                    <div style="font-size: 50px; color: #4dbda5; margin-bottom: 15px;">🎉</div>
                    <p style="font-size: 16px; color: #333; margin-bottom: 5px;">Your request for this PG has been successfully shared with the landlord.</p>
                    <p style="font-size: 14px; color: #666; font-weight: 500;">They will contact you on your registered phone number shortly!</p>
                </div>

                <div class="modal-footer" style="border-top: none; justify-content: center; background-color: #f8f9fa; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                    <a href="dashboard.php" class="btn btn-success" style="background-color: #4dbda5; border: none; padding: 8px 25px; font-weight: bold; width: 80%;">
                        Go to Dashboard
                    </a>
                </div>

            </div>
        </div>
    </div>

    <?php
    include "includes/signup_modal.php";
    include "includes/login_modal.php";
    include "includes/footer.php";
    ?>

    <script type="text/javascript" src="js/property_detail.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tour-booking-form').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();
                var alertMsg = $('#booking-alert-msg');

                alertMsg.addClass('d-none').removeClass('alert-success alert-danger');

                $.ajax({
                    url: 'book_tour.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alertMsg.removeClass('d-none').addClass('alert-success').text(response.message);

                            setTimeout(function() {
                                $('#bookTourModal').modal('hide');
                                $('#tour-booking-form')[0].reset();
                                alertMsg.addClass('d-none');
                            }, 2000);
                        } else {
                            alertMsg.removeClass('d-none').addClass('alert-danger').text(response.message);
                        }
                    },
                    error: function() {
                        alertMsg.removeClass('d-none').addClass('alert-danger').text('Kuch gadbad hui h, connection check karo!');
                    }
                });
            });
        });
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (typeof window.performance != 'undefined' && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });
    </script>
</body>

</html>