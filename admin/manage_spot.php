<?php
session_start();
$dbName = $_SESSION['admin_db'];

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = $dbName;

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the new table if not exists
$createTableSQL = "CREATE TABLE IF NOT EXISTS spots_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status ENUM('available','reserved') NOT NULL DEFAULT 'available',
    duration INT DEFAULT 0,
    fee DECIMAL(10, 2) DEFAULT 0.00,
    customer_name VARCHAR(100) NULL,
    customer_phone VARCHAR(20) NULL,
    license_plate VARCHAR(20) NULL,
    generatedId VARCHAR(6) NULL,
    reserved VARCHAR(30)  NULL
)";
$conn->query($createTableSQL);

// Add a new spot (default values only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_new_spot'])) {
    $insertSQL = "INSERT INTO spots_info () VALUES ()";
    $conn->query($insertSQL);
}

// Free a spot (change reserved to available)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['free_spot_id'])) {
    $id = intval($_POST['free_spot_id']);
    $updateSQL = "UPDATE spots_info 
                  SET status='available', duration=0, fee=0.00, customer_name=NULL, customer_phone=NULL, license_plate=NULL, generatedId=NULL 
                  WHERE id=$id";
    $conn->query($updateSQL);
}

// Remove the last spot (highest ID)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_last_spot'])) {
    // Get the highest ID
    $result = $conn->query("SELECT MAX(id) as max_id FROM spots_info");
    $maxId = $result->fetch_assoc()['max_id'];
    
    if ($maxId) {
        // Check if the last spot is available (safety measure)
        $statusResult = $conn->query("SELECT status FROM spots_info WHERE id=$maxId");
        $status = $statusResult->fetch_assoc()['status'];
        
        if ($status == 'available') {
            $deleteSQL = "DELETE FROM spots_info WHERE id=$maxId";
            $conn->query($deleteSQL);
        }
    }
}

// Count stats
$totalSpots     = $conn->query("SELECT COUNT(*) AS total FROM spots_info")->fetch_assoc()['total'];
$availableSpots = $conn->query("SELECT COUNT(*) AS available FROM spots_info WHERE status='available'")->fetch_assoc()['available'];
$reservedSpots  = $conn->query("SELECT COUNT(*) AS reserved FROM spots_info WHERE status='reserved'")->fetch_assoc()['reserved'];

// Fetch all spots
$spots = $conn->query("SELECT * FROM spots_info ORDER BY id ASC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Parking Spot Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
        }
        h1 { 
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2.2rem;
        }
        .dashboard {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            width: 200px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin-top: 0;
            color: #7f8c8d;
            font-size: 1rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .total-spots .stat-value { color: #2c3e50; }
        .available-spots .stat-value { color: #27ae60; }
        .reserved-spots .stat-value { color: #f39c12; }

        table {
            width: 100%;
            max-width: 1000px;
            margin: auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ecf0f1;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f1f8fe;
        }
        .status-available { color: #27ae60; font-weight: bold; }
        .status-reserved { color: #f39c12; font-weight: bold; }

        .free-btn {
            padding: 6px 12px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .free-btn:hover {
            background-color: #27ae60;
        }

        .add-btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            margin: 0 5px 20px;
        }
        .add-btn:hover {
            background-color: #2980b9;
        }
        
        .remove-last-btn {
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            margin: 0 5px 20px;
        }
        .remove-last-btn:hover {
            background-color: #c0392b;
        }
        
        .button-container {
            margin-bottom: 20px;
        }
    </style>
    <script>
        function confirmAddSpot() {
            return confirm("Are you sure you want to add a new parking spot?");
        }
        function confirmFreeSpot() {
            return confirm("Are you sure you want to free this reserved spot?");
        }
        function confirmRemoveLastSpot() {
            return confirm("Are you sure you want to remove the last added spot?\nThis will delete the spot with highest ID number.");
        }
    </script>
</head>
<body>

<h1>Parking Spot Management</h1>

<div class="button-container">
    <form method="POST" onsubmit="return confirmAddSpot();" style="display: inline;">
        <button type="submit" name="add_new_spot" class="add-btn">+ Add New Spot</button>
    </form>
    <form method="POST" onsubmit="return confirmRemoveLastSpot();" style="display: inline;">
        <button type="submit" name="remove_last_spot" class="remove-last-btn">- Remove Last Spot</button>
    </form>
</div>

<div class="dashboard">
    <div class="stat-card total-spots"><h3>Total Spots</h3><div class="stat-value"><?= $totalSpots ?></div></div>
    <div class="stat-card available-spots"><h3>Available Spots</h3><div class="stat-value"><?= $availableSpots ?></div></div>
    <div class="stat-card reserved-spots"><h3>Reserved Spots</h3><div class="stat-value"><?= $reservedSpots ?></div></div>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Status</th>
            <th>Duration (min)</th>
            <th>Fee ($)</th>
            <th>Customer Name</th>
            <th>Phone</th>
            <th>License Plate</th>
            <th>Generated ID</th>
            <th>Reserved At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $spots->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td class="status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></td>
                <td><?= $row['duration'] ?></td>
                <td><?= number_format($row['fee'], 2) ?></td>
                <td><?= $row['customer_name'] ?? '-' ?></td>
                <td><?= $row['customer_phone'] ?? '-' ?></td>
                <td><?= $row['license_plate'] ?? '-' ?></td>
                <td><?= $row['generatedId'] ?? '-' ?></td>
                <td><?= $row['reserved'] ?? '-' ?></td>
                <td>
                    <?php if ($row['status'] == 'reserved'): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirmFreeSpot();">
                            <input type="hidden" name="free_spot_id" value="<?= $row['id'] ?>">
                            <button class="free-btn" type="submit">Free</button>
                        </form>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>