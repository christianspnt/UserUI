<?php
// Include database connection
session_start();
require('connect.php');

// Get the logged-in user's account ID
$account_ID = $_SESSION['user_id'];

// Fetch the user's full name and phone number from the passenger_accounts table
$query = "SELECT fName, lName, phone_num FROM passenger_accounts WHERE account_ID = :account_ID AND status = 'Active'";
$stmt = $conn->prepare($query);
$stmt->bindParam(':account_ID', $account_ID, PDO::PARAM_INT);
$stmt->execute();
$userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

// If user details are not found, show an error
if (!$userDetails) {
    echo "Error: User details not found.";
    exit;
}

$fullName = $userDetails['fName'] . ' ' . $userDetails['lName'];
$phoneNum = $userDetails['phone_num'];

// Handle form submission to insert the tap card purchase details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone_number = $_POST['phone_num'];
    $address = $_POST['address'];
    $payment_method = $_POST['payment_mode'];
    $amount = 250.00; // Fixed amount for the tap card purchase (150 + 100 for delivery)
    $date_purchase = date('Y-m-d');

    // Insert the tap card purchase details into the tap_card table
    $query = "INSERT INTO tap_card (first_Name, last_Name, phone_Num, amount, date_purchase, payment_method, address, account_ID, status) 
              VALUES (:first_Name, :last_Name, :phone_Num, :amount, :date_purchase, :payment_method, :address, :account_ID, 'Pending')";
    $stmt = $conn->prepare($query);

    // Bind the form data to the query parameters
    $stmt->bindParam(':first_Name', $userDetails['fName']);
    $stmt->bindParam(':last_Name', $userDetails['lName']);
    $stmt->bindParam(':phone_Num', $phone_number);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':date_purchase', $date_purchase);
    $stmt->bindParam(':payment_method', $payment_method);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':account_ID', $account_ID);
   

    // Execute the query
    if ($stmt->execute()) {
        echo "<script>alert('Tap card purchased successfully!'); window.location.href = 'cebus.php';</script>";
    } else {
        echo "<script>alert('Error purchasing tap card.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Tap Card</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at top left, #4f7198, #f9ce80, #4f7198);
            background-position: top left;
            background-size: cover;
            height: 100vh;
            overflow: hidden;
        }

        .header {
            display: flex;
            align-items: center;
            padding: 20px;
        }

        .header .back-arrow {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            margin-right: 10px;
        }

        .header .title {
            font-size: 20px;
            font-weight: bold;
        }

        .content {
            display: flex;
            flex-wrap: nowrap;
            padding: 20px;
            gap: 20px;
        }

        .left-div {
            flex: 0 0 39%;
            padding: 20px;
        }

        .right-div {
            flex: 0 0 55%;
            padding: 20px;
        }

        .tap-card-box {
            background: radial-gradient(circle at top left, #4f7198, #f9ce80, #4f7198);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            width: 75%;
            height: 230px;
            border-radius: 10px;
            display: flex;
            right: 200px;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;
            color: #fff;
        }

        .tap-card-title {
            font-size: 24px;
            font-weight: bold;
        }

        .tap-card-subtitle {
            font-size: 18px;
            text-align: right;
            margin-top: -75px;
        }

        .nfc-icon-card {
            position: relative;
            top: 50%;
            right: -410px;
            transform: translateY(-290%);
            width: 40px;
            height: 40px;
        }

        .passenger-name {
            font-size: 16px;
        }

        .form-group-left {
            font-weight: bold;
            margin-bottom: 5px;
            margin-top: 70px;
        }

        .form-group-left label {
            display: block; 
            margin-bottom: 7px;
        }

        .form-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }


        input[type="text"],
        input[type="radio"] {
            width: 80%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #fff;
            border-radius: 5px;
            font-size: 16px;
            background-color: transparent;
            color: #fff;
        }

        input[readonly] {
            background-color: transparent;
            color: #fff;
        }

        input[type="radio"] {
            width: auto;
            margin-right: 10px;
        }

        .right-div label {
            display: block;
            margin-bottom: 7px;
        }

        .note {
            font-size: 15px;
            color: #fff;
            margin-bottom: 10px;
        }

        .summary {
            margin-top: 20px;
            color: #fff;
        }
        .summary p {
            margin-right: 150px;
        }

        .summary .total {
            font-weight: bold;
        }

        .place-order-btn {
            background-color: #4f7198;
            color: #fff;
            padding: 13px 100px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .place-order-btn:hover {
            background-color: #3e5a7d;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <button class="back-arrow" onclick="window.location.href='card.php'">&larr;</button>
        <span class="title">Checkout</span>
    </div>

    
    <!-- Content Section -->
    <div class="content">
        <!-- Left Div -->
        <div class="left-div">
        <form method="POST" action="">
            <div class="tap-card-box">
                <div class="tap-card-title">CeBUS</div>
                <div class="tap-card-subtitle">Tap Card</div>
                <img src="images/nfc.png" alt="NFC Icon" class="nfc-icon-card">
                <div class="passenger-name"><?= htmlspecialchars($fullName) ?></div>
            </div>
            <div class="form-group-left">
                <h2>Contact</h2>
                <label>FULL NAME</label>
                <input type="text" value="<?= htmlspecialchars($fullName) ?>" readonly>
                <label>PHONE NUMBER</label>
                <input type="text" name="phone_num" placeholder="Enter your phone number" value="<?= htmlspecialchars($phoneNum) ?>" required>
            </div>
        </div>

        <!-- Right Div -->
        <div class="right-div">
        
            <h2>Address</h2>
            <p class="note">Note: Delivery is available only to addresses within Cebu.</p>
            <label>Delivery Address</label>
            <input type="text" name="address" placeholder="Enter your delivery address" required>

            <div class="form-group">
                <label>Mode of Payment</label>
                <input type="radio" name="payment_mode" value="Cash on Delivery" required> Cash on Delivery<br>
                <input type="radio" name="payment_mode" value="Cashless" required> Cashless
            </div>

            <div class="summary">
                <p>Tap Card Fee: <span style="float: right;">150.00</span></p>
                <p>Delivery Fee: <span style="float: right;">100.00</span></p>
                <p class="total">Total Fee: <span style="float: right;">250.00</span></p>
            </div>

            <!-- Submit Form -->
            
                <button type="submit" class="place-order-btn">Place Order</button>
            </form>
        </div>
    </div>

</body>
</html>
