<?php
session_start();


// Redirect if admin_org not set
// if (!isset($_SESSION['admin_org'])) {
//     header("Location: login.php");
//     exit;
// }


if (!isset($_SESSION['admin_org']) || !isset($_SESSION['admin_db'])) {
    // User not logged in properly
    header("Location: login.php");
    exit;
}

// $org = $_SESSION['admin_org'];
$dbName = $_SESSION['admin_db'];





$org_name_raw = $_SESSION['admin_org'];

// Sanitize org name for folder creation (allow only letters, digits, dash, underscore)
$org_name = preg_replace("/[^a-zA-Z0-9_-]/", "", $org_name_raw);
$org_folder = "uploads/$org_name/";




// Create org folder if it doesn't exist
if (!is_dir($org_folder)) {
    mkdir($org_folder, 0755, true);
}

// Default image path
$default_image = "uploads/default.png";

// Load last uploaded image path for this specific organization, else default
$uploadedImagePath = $_SESSION['profile_image_path_' . $org_name] ?? $default_image;

// Handle upload on POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["profile_image"])) {
    $imageFile = $_FILES["profile_image"];
    $imageFileType = strtolower(pathinfo($imageFile["name"], PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $uploadOk = true;
    $uploadMessage = '';

    // Validate image
    if (getimagesize($imageFile["tmp_name"]) === false) {
        $uploadMessage = "File is not a valid image.";
        $uploadOk = false;
    } elseif ($imageFile["size"] > 2 * 1024 * 1024) {
        $uploadMessage = "File too large. Max size is 2MB.";
        $uploadOk = false;
    } elseif (!in_array($imageFileType, $allowedTypes)) {
        $uploadMessage = "Invalid file type. Allowed: JPG, JPEG, PNG, GIF.";
        $uploadOk = false;
    }

    if ($uploadOk) {
        // Fixed filename: orgname.png (force png extension to keep consistency)
        $fixedFileName = $org_name . ".png";
        $targetFile = $org_folder . $fixedFileName;

        // Delete previous profile picture if exists
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }

        // Keep original extension to avoid format mismatch
        $fixedFileName = $org_name . "." . $imageFileType;
        $targetFile = $org_folder . $fixedFileName;

        // Delete previous files with other extensions to clean up old files
        foreach ($allowedTypes as $ext) {
            $oldFile = $org_folder . $org_name . "." . $ext;
            if ($ext !== $imageFileType && file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        if (move_uploaded_file($imageFile["tmp_name"], $targetFile)) {
            $uploadMessage = "Profile picture uploaded successfully!";
            $uploadedImagePath = $targetFile;
            // Store with org-specific key
            $_SESSION['profile_image_path_' . $org_name] = $uploadedImagePath;
        } else {
            $uploadMessage = "Error occurred while uploading.";
        }
    }

    $_SESSION['upload_message'] = $uploadMessage;

    // Redirect to clear POST and avoid resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get and clear upload message after redirect
$uploadMessage = $_SESSION['upload_message'] ?? '';
unset($_SESSION['upload_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
        }
        .nav-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #4CAF50;
            padding: 10px 20px;
        }
        .nav-bar img.logo {
            width: 100px;
        }
        .nav-list {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0; padding: 0;
        }
        .nav-list li a {
            color: white;
            text-decoration: none;
        }
        .admin-home {
            text-align: center;
            padding: 30px;
            max-width: 900px;
            margin: auto;
        }
        h3 {
            font-size: 2.5rem;
            color: #333;
        }
        .profile-container {
            margin: 30px 0;
        }
        .profile-pic {
            width: 400px;
            height: 400px;
            object-fit: cover;
            border-radius: 2px;
            border: 4px solid #4CAF50;
        }
        .upload-section {
            margin-top: 15px;
        }
        .upload-section input[type="file"] {
            margin: 10px 0;
        }
        .upload-section button {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .upload-message {
            color: green;
            margin-top: 10px;
        }
        canvas {
            margin: 30px auto;
            max-width: 100%;
        }
        footer {
            text-align: center;
            padding: 20px;
            background: #f1f1f1;
        }
        @media (max-width: 768px) {
            .nav-list {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="nav-bar">
        <img class="logo" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRZlau7ss4kuWz3N0c8VZ-xvwswfkG74dsouw&s" alt="Logo" />
        <ul class="nav-list">
            <li><a href="adminPage.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="manage_spot.php"><i class="fas fa-info-circle"></i> Spot Status</a></li>
            <li><a href="price_password.php"><i class="fas fa-dollar-sign"></i> Set Price</a></li>
            <li><a href="price_password.php"><i class="fas fa-edit"></i> Edit</a></li>
            <li><a href="login.php"><i class="fas fa-user"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="admin-home">
        <h3>Welcome to Smart Parking Admin Panel</h3>

        <!-- Profile Picture -->
        <div class="profile-container">
            <!-- Add cache buster to force reload on upload -->
            <img src="<?= htmlspecialchars($uploadedImagePath) . '?v=' . time() ?>" class="profile-pic" alt="Profile Picture" />
        </div>

        <!-- Upload Form -->
        <div class="upload-section">
            <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="file" name="profile_image" accept="image/*" required /><br />
                <button type="submit">Upload Profile Picture</button>
            </form>
            <?php if ($uploadMessage): ?>
                <div class="upload-message"><?= htmlspecialchars($uploadMessage) ?></div>
            <?php endif; ?>
        </div>

        <!-- Charts Section -->
        <h3 style="margin-top: 60px;">Customer Analytics</h3>
        <canvas id="dailyChart" width="400" height="200"></canvas>
        <canvas id="monthlyChart" width="400" height="200"></canvas>
    </div>

    <!-- Footer -->
    <footer>
        <p>© 2025 Vehicle Parking Solutions. All rights reserved.</p>
    </footer>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const dailyLabels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
        const dailyData = [8, 12, 9, 14, 10, 17, 15];

        const monthlyLabels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const monthlyData = [120, 140, 130, 160, 180, 190, 200, 210, 175, 160, 150, 170];

        new Chart(document.getElementById('dailyChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Daily Customers',
                    data: dailyData,
                    backgroundColor: 'rgba(76, 175, 80, 0.2)',
                    borderColor: '#4CAF50',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        new Chart(document.getElementById('monthlyChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Monthly Customers',
                    data: monthlyData,
                    backgroundColor: '#4CAF50'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>