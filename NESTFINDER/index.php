<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache");                                   // HTTP 1.0
header("Expires: 0");                                          // Proxies

if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner') {
    header("Location: owner_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <link rel="icon" type="image/png" sizes="32x16" href="favicon.png">
    <link rel="icon" type="image/png" sizes="48x16" href="favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | PG Life</title>

    <?php
    include "includes/head_links.php";
    ?>
    <link href="css/home.css" rel="stylesheet" />
</head>

<body>
    <?php
    include "includes/header.php";
    ?>

    <div class="banner-container">
        <h2 class="white pb-3">Happiness per Square Foot</h2>

        <form id="search-form" action="property_list.php" method="GET">
            <div class="input-group city-search">
                <input type="text" class="form-control input-city" id='city' name='city' placeholder="Enter your city to search for PGs" required />
                <div class="input-group-append">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="page-container">
        <h1 class="city-heading text-center">
            Major Cities
        </h1>

        <div class="row justify-content-center">

            <div class="city-card-container col-6 col-md-4 mb-4">
                <a href="property_list.php?city=Delhi">
                    <div class="city-card rounded-circle">
                        <img src="img/delhi.png" class="city-img" alt="Delhi" />
                    </div>
                </a>
            </div>

            <div class="city-card-container col-6 col-md-4 mb-4">
                <a href="property_list.php?city=Mumbai">
                    <div class="city-card rounded-circle">
                        <img src="img/mumbai.png" class="city-img" alt="Mumbai" />
                    </div>
                </a>
            </div>

            <div class="city-card-container col-6 col-md-4 mb-4">
                <a href="property_list.php?city=Bengaluru">
                    <div class="city-card rounded-circle">
                        <img src="img/bangalore.png" class="city-img" alt="Bengaluru" />
                    </div>
                </a>
            </div>

            <div class="city-card-container col-6 col-md-4 mb-4">
                <a href="property_list.php?city=Hyderabad">
                    <div class="city-card rounded-circle">
                        <img src="img/hyderabad.png" class="city-img" alt="Hyderabad" />
                    </div>
                </a>
            </div>


            <div class="city-card-container col-6 col-md-4 mb-4">
                <a href="property_list.php?city=Shimla">
                    <div class="city-card rounded-circle">
                        <img src="img/shimla.png" class="city-img" alt="Shimla" />
                    </div>
                </a>
            </div>

        </div>
    </div>

    <?php
    include "includes/signup_modal.php";
    include "includes/login_modal.php";
    include "includes/footer.php";
    ?>

    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (typeof window.performance != 'undefined' && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });


        document.getElementById('search-form').addEventListener('submit', function(e) {
            const cityInput = document.getElementById('city');
            const cleanPattern = /^[a-zA-Z\s\-]+$/;

            if (!cleanPattern.test(cityInput.value.trim())) {
                e.preventDefault();
                alert("❌ Please enter a valid city name! Special characters ya HTML tags allowed nahi hain.");
                cityInput.focus();
            }
        });
    </script>

</body>

</html>