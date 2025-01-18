<?php
// Include the database connection file
session_start();
require('connect.php');


// Get the logged-in user's account ID from the session
$account_ID = $_SESSION['user_id']; 


// Query to get passenger details based on account ID
$query = "SELECT fName, lName FROM passenger_accounts WHERE account_ID = :account_ID AND status = 'Active'";
$stmt = $conn->prepare($query);
$stmt->bindParam(':account_ID', $account_ID, PDO::PARAM_INT);
$stmt->execute();
$userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle case when user details are not found
if (!$userDetails) {
    echo "<script>alert('Error: User details not found.');</script>";
    exit;
}

$fullName = $userDetails['fName'] . " " . $userDetails['lName']; // Full name of the passenger
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tap Card</title>
    <link rel="stylesheet" href="busStyle.css">
</head>
<body class="body-card">
    <div class="header-card">
        <button class="back-button-card" onclick="window.location.href='cebus.php'">←</button>
        <div class="title-card">Tap Card</div>
    </div>

    <div class="tap-card-box-card">
        <div class="tap-card-title-card">CeBUS</div>
        <div class="tap-card-subtitle-card">Tap Card</div>
        <img src="images/nfc.png" alt="NFC Icon" class="nfc-icon-card">
        <div class="passenger-name-card"><?= htmlspecialchars($fullName) ?></div> <!-- Display passenger's name -->
    </div>

    <button class="order-button-card" onclick="window.location.href='buytapcard.php'">
        Order a Card
        <span>Order your tap card now and have it conveniently delivered to your registered address.</span>
    </button>
</body>
</html>
