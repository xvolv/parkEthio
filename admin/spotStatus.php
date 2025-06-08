<?php
// DB credentials - replace with your actual info
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'ambassador_db';

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create spots table if not exists
// Columns: id (auto_increment), spot_name (unique), status ('available' or 'occupied')
$createTableSQL = "CREATE TABLE IF NOT EXISTS parking_spots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    spot_name VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('available', 'occupied') NOT NULL DEFAULT 'available'
)";
$conn->query($createTableSQL);

// Handle adding a new spot if form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_spot'])) {
    $new_spot = $conn->real_escape_string(trim($_POST['new_spot']));
    if ($new_spot != '') {
        // Insert new spot, default status 'available'
        $insertSQL = "INSERT IGNORE INTO parking_spots (spot_name, status) VALUES ('$new_spot', 'available')";
        $conn->query($insertSQL);
    }
}

// Fetch total spots count
$totalSpotsResult = $conn->query("SELECT COUNT(*) as total FROM parking_spots");
$totalSpots = 0;
if ($row = $totalSpotsResult->fetch_assoc()) {
    $totalSpots = $row['total'];
}

// Fetch occupied spots count
$occupiedSpotsResult = $conn->query("SELECT COUNT(*) as occupied FROM parking_spots WHERE status='occupied'");
$occupiedSpots = 0;
if ($row = $occupiedSpotsResult->fetch_assoc()) {
    $occupiedSpots = $row['occupied'];
}

// Calculate available spots
$availableSpotsCount = $totalSpots - $occupiedSpots;

// Fetch list of available spots
$availableSpots = [];
$availableResult = $conn->query("SELECT spot_name FROM parking_spots WHERE status='available' ORDER BY spot_name");
while ($row = $availableResult->fetch_assoc()) {
    $availableSpots[] = $row['spot_name'];
}

// Fetch list of occupied spots
$occupiedSpotsList = [];
$occupiedResult = $conn->query("SELECT spot_name FROM parking_spots WHERE status='occupied' ORDER BY spot_name");
while ($row = $occupiedResult->fetch_assoc()) {
    $occupiedSpotsList[] = $row['spot_name'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Spot Status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        /* Your CSS styles from before, keep it or customize */
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
        }
        h1 {
            font-style: italic;
            color: #333;
        }
        #available-spots, #occupied-spots {
            background-color: #eee;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            width: 50%;
            max-width: 400px;
            list-style-type: none;
        }
        #available-spots {
            background-color: rgb(22, 186, 106);
            color: white;
        }
        #occupied-spots {
            background-color: rgb(186, 22, 22);
            color: white;
        }
        li {
            padding: 5px 0;
        }
        form {
            margin-top: 30px;
        }
        input[type="text"] {
            padding: 8px;
            width: 250px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            padding: 8px 16px;
            font-size: 1rem;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .summary {
            margin: 20px auto;
            max-width: 400px;
            font-size: 1.1rem;
            color: #333;
        }
    </style>
</head>
<body>

<h1>Spot Status</h1>

<div class="summary">
    <p><strong>Total spots:</strong> <?php echo $totalSpots; ?></p>
    <p><strong>Available spots:</strong> <?php echo $availableSpotsCount; ?></p>
    <p><strong>Occupied spots:</strong> <?php echo $occupiedSpots; ?></p>
</div>

<div>
    <h2>Available Spots</h2>
    <ul id="available-spots">
        <?php
        if (count($availableSpots) > 0) {
            foreach ($availableSpots as $spot) {
                echo "<li>" . htmlspecialchars($spot) . "</li>";
            }
        } else {
            echo "<li>No available spots</li>";
        }
        ?>
    </ul>
</div>

<div>
    <h2>Occupied Spots</h2>
    <ul id="occupied-spots">
        <?php
        if (count($occupiedSpotsList) > 0) {
            foreach ($occupiedSpotsList as $spot) {
                echo "<li>" . htmlspecialchars($spot) . "</li>";
            }
        } else {
            echo "<li>No occupied spots</li>";
        }
        ?>
    </ul>
</div>

<!-- Add new spot form -->
<form method="POST" action="">
    <input type="text" name="new_spot" placeholder="Enter new spot name (e.g. Spot 8)" required />
    <input type="submit" value="Add Spot" />
</form>

</body>
</html>
