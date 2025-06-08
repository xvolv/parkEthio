<?php
session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $org_name = trim($_POST['org_name']);
    $password = $_POST['password'];

    // Construct DB filename based on org_name
    $db_filename = $org_name . '.db';

    if (!file_exists($db_filename)) {
        $message = "❌ No database found for this organization.";
    } else {
        try {
            $pdo = new PDO("sqlite:" . $db_filename);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check admin_data table for matching org_name
            $stmt = $pdo->prepare("SELECT * FROM admin_data WHERE org_name = :org_name");
            $stmt->execute(['org_name' => $org_name]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                if (password_verify($password, $admin['password'])) {
                    $_SESSION['admin_org'] = $admin['org_name'];
                    header("Location: admin_dashboard.php"); // Redirect to dashboard
                    exit;
                } else {
                    $message = "❌ Incorrect password.";
                }
            } else {
                $message = "❌ Admin not found in admin_data table.";
            }
        } catch (PDOException $e) {
            $message = "❌ Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            width: 320px;
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-box label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .login-box input {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .login-box button {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 4px;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }

    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>
        <?php if (!empty($message)): ?>
            <div class="error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label>Organization Name (org_name)</label>
            <input type="text" name="org_name" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
