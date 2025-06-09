<?php
session_start();

// Database info from session
$dbName = $_SESSION['admin_db'];
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = $dbName;
$conn = new mysqli($host, $user, $pass, $db);
$mainDb='parking';
$mainSql= new mysqli($host, $user, $pass, $mainDb);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

// Update admin info
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update password
    if (!empty($_POST['new_password'])) {
        $newPassword = $_POST['new_password'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $conn->query("UPDATE admin_data SET password='$hashedPassword' LIMIT 1");
        $message .= "Password updated successfully. ";
    }

    // Update price per hour
    if (isset($_POST['new_price'])) {
         $orgName = $_SESSION['admin_org'];
        $newPrice = floatval($_POST['new_price']);
        $mainSql->query("UPDATE companies SET price=$newPrice WHERE org_name='$orgName' LIMIT 1");
        $message .= "Price per hour updated successfully.";
    }
}

// Fetch current price
$currentPrice = 0.0;
$result = $conn->query("SELECT price FROM admin_data LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $currentPrice = $row['price'];
}

// Close connections
$mainSql->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Admin Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fb;
            padding: 40px;
            text-align: center;
        }
        h1 {
            color: #2c3e50;
        }
        form {
            background-color: #fff;
            padding: 25px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 400px;
        }
        input[type="password"], input[type="number"] {
            width: 90%;
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        .msg {
            margin-top: 15px;
            color: green;
        }
    </style>
</head>
<body>

<h1>Edit Admin Settings</h1>

<form method="POST">
    <label><strong>Change Password:</strong></label><br>
    <input type="password" name="new_password" placeholder="Enter new password"><br>

    <label><strong>Set Price Per Hour ($):</strong></label><br>
    <input type="number" name="new_price" step="0.01" min="0" value="<?= htmlspecialchars($currentPrice) ?>"><br>

    <button type="submit">Update</button>
</form>

<?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

</body>
</html>