<?php

session_start();
$db =$_SESSION['admin_db'];

// DB credentials
$host = 'localhost';
$user = 'root';
$pass = '';

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);
//$conn = new mysqli($host, $user, $pass, $db);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['org_name'])) {
    $orgName = $_GET['org_name'];
 $orgName = strtoupper($orgName);
 
   

    
} else {
    echo "No organization selected.";
}
// Hardcoded available spots count and price

$totalSpotsAvailable  = $conn->query("SELECT COUNT(*) AS available FROM spots_info WHERE status='available'")->fetch_assoc()['available'];;
$pricePerHour = $_GET['price'] ?? 0; // Default price per hour if not set

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $conn->real_escape_string($_POST['phone']);
    $duration = intval($_POST['duration']);
    $license_plate = $conn->real_escape_string($_POST['license_plate']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);
  
  
    // Calculate total payment
    $total_payment = $duration * $pricePerHour;
    
    $reservation_message = "Reservation successful!<br>
                          License Plate: $license_plate<br>
                          Duration: $duration hours<br>
                          Total Payment: $total_payment ETB<br>
                          Payment Method: $payment_method";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Spot Reservation  </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --success: #4cc9a0;
            --error: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: var(--dark);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        h2 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        .info-card {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-card .label {
            font-weight: 500;
            color: var(--gray);
        }

        .info-card .value {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .payment-options {
            display: flex;
            gap: 10px;
            margin-bottom: 1rem;
        }

        .payment-option {
            flex: 1;
            text-align: center;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-option label {
            display: block;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .payment-option input[type="radio"]:checked + label {
            background-color: var(--primary);
            color: white;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 1rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .message {
            margin: 20px 0;
            padding: 15px;
            border-radius: var(--border-radius);
            text-align: center;
        }

        .success {
            background-color: rgba(76, 201, 160, 0.2);
            border-left: 4px solid var(--success);
            color: #1b7052;
        }

        .error {
            background-color: rgba(247, 37, 133, 0.1);
            border-left: 4px solid var(--error);
            color: #a4133c;
        }

        .total-payment {
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            color: var(--primary);
        }

        @media (max-width: 640px) {
            .container {
                padding: 20px;
            }
            
            .payment-options {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <div class="container">
 <?php if ($orgName): ?>
            <div class="message success">
                <?php echo $orgName; ?>
            </div>
        <?php endif; ?>
        <h2>Parking Reservation</h2>
        
        <div class="info-card">
            <div>
                <span class="label">Available Spots</span>
                <span class="value"><?php echo $totalSpotsAvailable; ?></span>
            </div>
            <div>
                <span class="label">Price per Hour</span>
                <span class="value"><?php echo $pricePerHour; ?> ETB</span>
            </div>
        </div>
        

        <?php if (isset($reservation_message)): ?>
            <div class="message success">
                <?php echo $reservation_message; ?>
            </div>
        <?php endif; ?>

        <form id="reservation-form" method="POST" action="reserveInfo.php">
            <div>
                <label for="license_plate">Vehicle Plate Number</label>
                <input type="text" id="license_plate" name="license_plate" placeholder="e.g., 3ABC123" required>
            </div>
            <div>
                <label for="customer_name">Your name</label>
                <input type="text" id="customer_name" name="customer_name" placeholder="e.g., salhadin"  required>
            </div>
            
            <div>
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="e.g., 0912345678" required>
            </div>
            
            <div>
                <label for="duration">Duration (hours)</label>
                <input type="number" id="duration" name="duration" min="1" max="24" value="1" required>
                <input type="hidden" name="price" value="<?php echo $pricePerHour; ?>">

            </div>
            
          
            
            <div>
                <label>Payment Method</label>
                <div class="payment-options">
                
                    <div class="payment-option">
                        <input type="radio" id="telebirr" name="payment_method" value="Telebirr">
                       
                        <label for="telebirr"><i class="fas fa-money-bill-wave"></i> Telebirr</label>
                       
                    </div>
                    <div class="payment-option">
                        <input type="radio" id="cash" name="payment_method" value="Cash">
                        <label for="cash"><i class="fas fa-hand-holding-usd"></i> Cash</label>
                    </div>
                </div>
            </div>
            
            <div class="total-payment" id="total-payment">
                Total: <?php echo $pricePerHour; ?> ETB
            </div>
            
            <button type="submit" id="reserve-button">Reserve Parking Spot</button>
        </form>
    </div>

    <script>
        // Calculate and update total payment when duration changes
        document.getElementById('duration').addEventListener('input', function() {
            const duration = this.value;
            const pricePerHour = <?php echo $pricePerHour; ?>;
            const total = duration * pricePerHour;
            document.getElementById('total-payment').textContent = `Total: ${total} ETB`;
        });
    </script>
</body>
</html>
