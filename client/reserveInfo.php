<?php
session_start();
$db = $_SESSION['admin_db'] ?? 'your_database_name'; // fallback default

// DB connection
$host = 'localhost';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle reservation request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $license_plate = $conn->real_escape_string($_POST['license_plate']);
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $duration_hours = intval($_POST['duration']);

    // Get price per hour from query string
   
    $pricePerHour = floatval($_POST['price'] ?? 0);
    $total_fee = $duration_hours * $pricePerHour;

    $reserved_at = date('Y-m-d H:i:s');
    $generatedId = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);

    // Step 1: Find the first available spot
    $result = $conn->query("SELECT id FROM spots_info WHERE status = 'available' ORDER BY id ASC LIMIT 1");

    if ($row = $result->fetch_assoc()) {
        $spot_id = $row['id'];

        // Step 2: Reserve the spot with all details
        $update_sql = "UPDATE spots_info SET 
                       status = 'reserved',
                       duration = $duration_hours,
                       fee = $total_fee,
                       customer_name = '$customer_name',
                       customer_phone = '$phone',
                       license_plate = '$license_plate',
                       generatedId = '$generatedId',
                       reserved = '$reserved_at'
                       WHERE id = $spot_id";

        $update = $conn->query($update_sql);

        if ($update) {
            echo "<h2>Reservation Successful</h2>";
            echo "<table border='1' cellpadding='8'>";
            echo "<tr><th>Customer Name</th><th>Phone</th><th>License Plate</th><th>Duration (hr)</th><th>Total Fee ($)</th><th>Reserved At</th><th>Spot ID</th><th>Generated ID</th></tr>";
            echo "<tr>";
            echo "<td>$customer_name</td>";
            echo "<td>$phone</td>";
            echo "<td>$license_plate</td>";
            echo "<td>$duration_hours</td>";
            echo "<td>$total_fee</td>";
            echo "<td>$reserved_at</td>";
            echo "<td>$spot_id</td>";
            echo "<td>$generatedId</td>";
            echo "</tr>";
            echo "</table>";
        } else {
            echo "<p>Failed to update spot status: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>No available spots at the moment.</p>";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>