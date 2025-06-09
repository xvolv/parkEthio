<?php
// db_connection.php example:
// Replace with your actual DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "parking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch parking spots (where banned = 0)
$sql = "SELECT id, org_name, latitude, longitude, price, info FROM companies WHERE banned = 0 ORDER BY id ASC";
$result = $conn->query($sql);

$parkingSpots = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Only include spots with valid coordinates
        if (!empty($row['latitude']) && !empty($row['longitude'])) {
            $parkingSpots[] = $row;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>vehicle</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="style.css" />
    <style>
        /* Your existing styles unchanged */
        .container { background-color: #112941; padding-top: 5%; padding-bottom: 5%; text-align: center; }
        .container p { color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-weight: lighter; font-size: 1.2rem; margin-top: 20px; margin:auto; }
        .container h2 { color: whitesmoke; font-family: 'Times New Roman', Times, serif; animation: forwards 1s ease-in-out; font-weight: bold; font-style: italic; font-size: 1.8rem; padding: 20px; }
        p { font-size: 1rem; margin-top: 15px; max-width: 800px; margin-left: auto; margin-right: auto; }
        main { padding: 40px 0 0 0; background-color: #f4f4f4; }
        h4 { font-size: 1.5rem; color: #333; text-align: center; margin-bottom: 30px; }
        .our-services { display: flex; flex-wrap: wrap; justify-content: center; }
        .our-services li { width: 70%; background-color: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); text-align: center; margin-bottom: 20px; }
        .our-services h5 { font-size: 1.2rem; color: #333; margin-bottom: 10px; }
        .our-services img { min-width: 80%; height: auto; border-radius: 8px; margin-bottom: 15px; }
        details { background-color: #f9f9f9; padding: 10px; border-radius: 5px; border: 1px solid #ddd; margin-top: 15px; }
        summary { font-size: 1rem; font-weight: bold; color: rgb(17, 121, 87); cursor: pointer; }
        details p { font-size: 0.95rem; color: #282bf0; }
        ul { list-style-type: none; padding-left: 0; }
        ul li { color: #777; }
        @media screen and (max-width: 768px) {
            .container { padding-top: 15%; }
            .container h2 { font-size: 1.5rem; font-weight: lighter; font-family: 'Times New Roman', Times, serif; }
            .container p { color: rgb(229, 182, 65); font-size: 1.1rem; word-break: break-word; text-align: left; font-family: 'Times New Roman', Times, serif; padding-left: 9px; padding-right: 5px; }
            .our-services li { width: 90%; }
            .our-services li p { font-size: 0.9rem; word-break: break-word; text-align: left; font-family: 'Times New Roman', Times, serif; padding-left: 9px; padding-right: 5px; }
        }
        .search-map { display: flex; }
        .search-map .option { display: flex; flex-direction: column; padding: 20px; margin-top: 20px; background-color: #f4f4f4; }
        .search-map .option li { list-style-type: disc; }
        .map { width: 100%; height: 500px; margin: 20px 20px 0 0; }
    </style>
</head>
<body>
    <div class="navigation">
        <nav class="nav-bar" id="nav-bar">
            <img class="logo" id="logo" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRZlau7ss4kuWz3N0c8VZ-xvwswfkG74dsouw&s" alt="Logo" style="width: 100px; height: auto;">
            <li id="menu-icon" class="menu-icon"><i class="fas fa-bars"></i></li>
            <ul class="nav-list" id="nav-list">
                <li><a href="home.html"><i class="fas fa-home"></i>Home</a></li>
                <li><a href="login.html"><i class="fas fa-user"></i>Check Your spot</a></li>
            </ul>
        </nav>
    </div>

    <div class="container" id="container">
        <div class="h2">
            <h2>Welcome to Your Smart, Secure, and Safe Vehicle Parking Solution</h2>
        </div>
        <p>
            We are delighted to welcome you to a new era of parking! Our platform offers a smart, secure, and safe parking experience designed to give you peace of mind every time you park.
        </p>
    </div>

    <script src="NavBar.js"></script>

    <div class="search-map">
        <div class="option">
            <input type="search" placeholder="Search parking area" />
            <ul>
                <li>Piassa Parking</li>
                <li>Piassa Parking</li>
                <li>Piassa Parking</li>
                <li>Bole Parking</li>
            </ul>
        </div>
        <div id="map" class="map"> map loading ...</div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script>
        const map = L.map('map').setView([9.03, 38.74], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let userLat, userLng;
        let routingControl;

        // Dynamic spots injected by PHP
        const parkingSpots = <?php echo json_encode($parkingSpots, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        parkingSpots.forEach(spot => {
            const lat = parseFloat(spot.latitude);
            const lng = parseFloat(spot.longitude);
            if (isNaN(lat) || isNaN(lng)) return; // skip invalid

            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(`
                <strong>${spot.org_name}</strong><br>
                Price: $${spot.price}<br>
                Info: ${spot.info}<br>
                <button onclick="showRoute(${lat}, ${lng})">Want to Go</button><br>
          <a href="reserve.php?org_name=${encodeURIComponent(spot.org_name)}&price=${encodeURIComponent(spot.price)}">Reserve Spot</a>

            `);
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                userLat = position.coords.latitude;
                userLng = position.coords.longitude;

                L.marker([userLat, userLng])
                    .addTo(map)
                    .bindPopup('You are here')
                    .openPopup();

                map.setView([userLat, userLng], 14);
            }, function() {
                alert("Location access denied.");
            });
        } else {
            alert("Geolocation not supported.");
        }

        function showRoute(destLat, destLng) {
            if (!userLat || !userLng) {
                alert("User location not available yet.");
                return;
            }

            if (routingControl) {
                map.removeControl(routingControl);
            }

            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(userLat, userLng),
                    L.latLng(destLat, destLng)
                ],
                routeWhileDragging: true
            }).addTo(map);
        }
    </script>
</body>
</html>
