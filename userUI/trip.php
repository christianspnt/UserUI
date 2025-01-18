<?php
session_start();

// Example: Mark a trip as completed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tripId = $_POST['trip_id']; // Assume trip_id is passed from the form
    if (!empty($_SESSION['completedBookings'])) {
        foreach ($_SESSION['completedBookings'] as &$booking) {
            if ($booking['id'] === $tripId) {
                $booking['status'] = 'completed'; // Update status
            }
        }
        unset($booking); // Break reference with the last element
    }
}

// Check if the required session data exists
if (!isset($_SESSION['tripDetails']) || !isset($_SESSION['groupDetails']) || !isset($_SESSION['bookedBy'])) {
    echo "Error: Required booking details are missing.";
    exit;
}

// Fetch the session data
$tripDetails = $_SESSION['tripDetails'];
$groupDetails = $_SESSION['groupDetails'];
$bookedBy = $_SESSION['bookedBy'];

// Calculate the total fare and final fare with discounts
$totalFare = $tripDetails['farePerPassenger'] * $tripDetails['numPassengers'];
$discount = 0;

// Check for discounts
if (isset($tripDetails['discounts'])) {
    foreach ($tripDetails['discounts'] as $type => $details) {
        $discount += $details['amount'];
    }
}
$finalFare = $totalFare - $discount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Details</title>
    <link rel="stylesheet" href="busStyle.css">
</head>
<body class="body-trip">
    <button class="back-button-trip" onclick="location.href='bus.php'">&#8592; Back</button>
    <div class="container-trip">
        <h2 class="h2-trip">Trip Details</h2>
        <div class="input-row-trip">
            <input type="text" class="readonly-trip" value="<?= htmlspecialchars($tripDetails['destination']) . ' (' . htmlspecialchars($tripDetails['distance']) . ' km)' ?>" readonly>
            <input type="text" class="readonly-trip" value="<?= htmlspecialchars($tripDetails['dateTime']) ?>" readonly id="date-time">
        </div>
        <div class="input-row-trip">
            <input type="text" class="readonly-trip" value="<?= htmlspecialchars($tripDetails['bookingType']) ?>" readonly>
            <input type="text" class="readonly-trip" value="<?= htmlspecialchars($tripDetails['numPassengers']) ?>" readonly>
        </div>
        <input type="text" class="readonly-trip" value="<?= htmlspecialchars($tripDetails['paymentMethod']) ?>" readonly>
        <h3>Passenger Details</h3>
        <?php foreach ($groupDetails['passengers'] as $type => $name): ?>
            <label><?= htmlspecialchars($type) ?>:</label>
            <input type="text" class="readonly-trip" value="<?= htmlspecialchars($name) ?>" readonly>
        <?php endforeach; ?>
        <h3>Payment Details</h3>
        <p><strong>Trip Fare:</strong> <?= number_format($tripDetails['farePerPassenger'], 2) ?> PHP</p>
        <p><strong>No. of Passengers:</strong> <?= htmlspecialchars($tripDetails['numPassengers']) ?></p>
        <p><strong>Discount:</strong> <?= $discount > 0 ? number_format($discount, 2) . ' PHP' : 'None' ?></p>
        <p><strong>Total Fare:</strong> <?= number_format($totalFare, 2) ?> PHP</p>
        <p><strong>Final Fare:</strong> <span id="final-fare"><?= number_format($finalFare, 2) ?> PHP</span></p>
        <form method="POST">
            <input type="hidden" name="trip_id" value="<?= $tripDetails['id'] ?? '0' ?>"> <!-- Replace with actual ID -->
            <?php if ($tripDetails['status'] === 'upcoming'): ?>
                <button type="submit">Mark as Completed</button>
            <?php else: ?>
                <button onclick="exportToPDF()">Export as PDF</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
