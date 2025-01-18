<?php
session_start(); // Start the session

// Include the database connection file
require_once 'connect.php';

$account_ID = $_SESSION['user_id'];

try {
    $query = "SELECT fName, lName, email, phone_num, address, DOB, age, sex 
              FROM passenger_accounts 
              WHERE account_ID = :account_ID AND status = 'Active'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':account_ID', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Redirect if no active user is found
    if (!$user) {
        header("Location: logout.php");
        exit();
    }

    // Combine first and last name for the profile name
    $fullName = htmlspecialchars($user['fName'] . ' ' . $user['lName']);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="busStyle.css">
</head>
<body class="body-prfl">
    <div class="profile-container-prfl">
        <!-- Upper Navigation -->
        <div class="nav-bar-prfl">
            <button class="back-button-prfl" onclick="location.href='cebus.php'">&larr;</button>
            <h1>Profile</h1>
        </div>

        <!-- Profile Layout -->
        <div class="profile-content-prfl">
            <!-- Left Section -->
            <div class="left-section-prfl">
                <div class="profile-picture-prfl">
                    <img src="images/profilePicIcon.png" alt="Profile Picture">
                </div>
                <div class="passenger-name-prfl">
                    <?php echo $fullName; ?>
                </div>
                <button class="update-btn-prfl" onclick="location.href='settings.php'">Update Information</button>
            </div>

            <!-- Vertical Divider -->
            <hr class="vertical-divider-prfl">

            <!-- Right Section -->
            <div class="right-section-prfl">
                <div class="info-row-prfl">
                    <label>EMAIL</label>
                    <input type="text" readonly value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="info-row-prfl">
                    <label>PHONE NUMBER</label>
                    <input type="text" readonly value="<?php echo htmlspecialchars($user['phone_num']); ?>">
                </div>
                <div class="info-row-prfl">
                    <label>ADDRESS</label>
                    <input type="text" readonly value="<?php echo htmlspecialchars($user['address']); ?>">
                </div>
                <div class="info-row-prfl">
                    <label>DATE OF BIRTH</label>
                    <input type="text" readonly value="<?php echo htmlspecialchars(date('F j, Y', strtotime($user['DOB']))); ?>">
                </div>
                <div class="info-row-prfl">
                    <label>AGE</label>
                    <input type="text" readonly value="<?php echo htmlspecialchars($user['age']); ?>">
                </div>
                <div class="info-row-prfl">
                    <label>SEX</label>
                    <input type="text" readonly value="<?php echo htmlspecialchars($user['sex']); ?>">
                </div>
            </div>
        </div>
    </div>
</body>
</html>
