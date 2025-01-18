<?php
// Start the session to fetch stored data
session_start();

// Check if required session data exists
if (!isset($_SESSION['tripDetails']) || !isset($_SESSION['groupDetails']) || !isset($_SESSION['bookedBy'])) {
    echo "Error: Required booking details are missing.";
    exit;
}

// Fetch the data from the session
$tripDetails = $_SESSION['tripDetails'];
$groupDetails = $_SESSION['groupDetails'];
$bookedBy = $_SESSION['bookedBy'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link your CSS -->
</head>
<body>
    <button class="back-button" onclick="location.href='inputBookingDetails.php'">&#8592;</button>
    <div class="booking-container">
        <div class="booking-details">
            <div class="trip-details">
                <h2>Trip Details</h2>
                <p><strong>Route:</strong> <?= htmlspecialchars($tripDetails['route']) ?></p>
                <p><strong>Date & Time:</strong> <?= htmlspecialchars($tripDetails['dateTime']) ?></p>
                <p><strong>Distance:</strong> <?= htmlspecialchars($tripDetails['distance']) ?></p>
                <p><strong>Booking Type:</strong> <?= htmlspecialchars($tripDetails['bookingType']) ?></p>
                <p><strong>No. of Passengers:</strong> <?= htmlspecialchars($tripDetails['numPassengers']) ?></p>
                <p><strong>Fare per Passenger:</strong> <?= number_format($tripDetails['farePerPassenger'], 2) ?> PHP</p>
                <p><strong>Total Fare:</strong> <?= number_format($tripDetails['totalFare'], 2) ?> PHP</p>
                <p><strong>Payment Method:</strong> <?= htmlspecialchars($tripDetails['paymentMethod']) ?></p>
                <p><strong>Payment Status:</strong> <?= htmlspecialchars($tripDetails['paymentStatus']) ?></p>
            </div>
            <div class="passenger-details">
                <h2>Passenger Details</h2>
                <?php foreach ($groupDetails['passengers'] as $type => $name): ?>
                    <p><strong><?= htmlspecialchars($type) ?>:</strong> <?= htmlspecialchars($name) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
        <hr>
        <div class="booked-by">
            <h2>Booked By</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($bookedBy['name']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($bookedBy['phone']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($bookedBy['email']) ?></p>
        </div>
        <div class="action-row">
            <button class="edit-button" onclick="location.href='inputBookingDetails.php'">Edit</button>
            <button class="proceed-button" onclick="location.href='qr.php'">Proceed</button>
        </div>
    </div>
</body>
</html>
