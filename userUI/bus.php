<?php
// Start the session
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus</title>
    <link rel="stylesheet" href="busStyle.css">
</head>
<body class="body-bus">
    <!-- Back Button -->
    <button class="back-button-bus" onclick="location.href='cebus.php'">&#8592;</button>

    <!-- Button to Trip Page -->
    <div class="centered-trip-button-bus">
    <button class="trip-button-bus" onclick="location.href='trip.php'">Trip Details</button>
</div>


    <!-- Separator -->
    <hr class="hr-bus">

    <!-- Booking History Section -->
    <!-- Booking History Section -->
<div class="booking-container-bus">
    <h2>View Booking History</h2>
    <div class="scrollable-box-bus">
        <?php
        // Check for completed bookings
        if (!isset($_SESSION['completedBookings']) || empty($_SESSION['completedBookings'])) {
            // No completed bookings
            echo '<img src="images/Error.png" alt="Error" class="error-icon">';
        } else {
            // Display only completed bookings
            $hasCompletedBookings = false;
            foreach ($_SESSION['completedBookings'] as $booking) {
                if (isset($booking['status']) && $booking['status'] === 'completed') {
                    $hasCompletedBookings = true;
                    echo '<button class="history-button" onclick="location.href=\'bookingHistory.php\'">' . htmlspecialchars($booking['destination']) . '</button>';
                }
            }
            // Show an error if no completed bookings exist
            if (!$hasCompletedBookings) {
                echo '<img src="images/Error.png" alt="No Completed Bookings" class="error-icon">';
            }
        }
        ?>
    </div>
</div>

</body>
</html>
