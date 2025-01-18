<?php
session_start();
include 'connect.php'; // Include the database connection

// Establish database connection
$db = new Database();
$conn = $db->dbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get passenger details from form
    $firstNames = $_POST['firstName'];
    $lastNames = $_POST['lastName'];
    $discounts = $_POST['discount'];
    $verificationDocs = $_FILES['verificationDoc'];

    // Example: Assuming the booking_ID is available from the session
    $bookingID = $_SESSION['bookingID']; // Replace with actual session variable or data source

    // Loop through each passenger and insert data into the database
    foreach ($firstNames as $index => $firstName) {
        $lastName = $lastNames[$index];
        $discount = $discounts[$index];
        $verificationDoc = $verificationDocs['name'][$index] ?? ''; // Get uploaded file name, if any

        // Prepare and execute the SQL query to insert data into the online_passenger_details table
        $query = "INSERT INTO online_passenger_details (booking_ID, first_name, last_name, passenger_type, discount, attachment_doc) 
                  VALUES (:booking_ID, :first_name, :last_name, :passenger_type, :discount, :attachment_doc)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':booking_ID', $bookingID);
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':passenger_type', $passengerTypes[$index]); // Assuming passengerTypes is predefined
        $stmt->bindParam(':discount', $discount);
        $stmt->bindParam(':attachment_doc', $verificationDoc);
        $stmt->execute();
    }

    // Redirect back to the inputBookingDetails.php after saving the passenger details
    header('Location: inputBookingDetails.php');
    exit;
}

// Get quantities from query parameters
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 0;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$infants = isset($_GET['infants']) ? intval($_GET['infants']) : 0;

// Exclude 1 adult, and adjust total passenger count accordingly
if ($adults > 0) {
    $adults--; // Exclude 1 adult
}
$totalPassengers = $adults + $children + $infants;

$passengerTypes = [];
while ($adults > 0) {
    $passengerTypes[] = 'Adult';
    $adults--;
}
while ($children > 0) {
    $passengerTypes[] = 'Child';
    $children--;
}
while ($infants > 0) {
    $passengerTypes[] = 'Infant';
    $infants--;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Details</title>
    <link rel="stylesheet" href="busStyle.css">
</head>
<body class="body-gd">
    <button class="btn-back-gd" onclick="goBack()">←</button>
    <div class="container-gd">
        <h1>Group Details</h1>
        <form action="groupDetails.php" method="POST" enctype="multipart/form-data">
            <?php foreach ($passengerTypes as $index => $type): ?>
                <div class="input-field-set-gd">
                    <h3><?= htmlspecialchars($type) . " " . ($index + 1); ?></h3>
                    <div>
                        <label for="firstName_<?= $index; ?>">First Name:</label>
                        <input type="text" id="firstName_<?= $index; ?>" name="firstName[]" required>
                    </div>
                    <div>
                        <label for="lastName_<?= $index; ?>">Last Name:</label>
                        <input type="text" id="lastName_<?= $index; ?>" name="lastName[]" required>
                    </div>
                    <div>
                        <label for="discount_<?= $index; ?>">Discount:</label>
                        <select id="discount_<?= $index; ?>" name="discount[]" onchange="toggleFileUpload(this, <?= $index; ?>)">
                            <option value="None">None</option>
                            <option value="Student">Student</option>
                            <option value="PWD">PWD</option>
                            <option value="Senior Citizen">Senior Citizen</option>
                        </select>
                    </div>
                    <div id="fileUpload_<?= $index; ?>" style="display: none;">
                        <label for="verificationDoc_<?= $index; ?>">Attach Verification Document:</label>
                        <input type="file" id="verificationDoc_<?= $index; ?>" name="verificationDoc[]">
                    </div>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="confirm-button-gd">Confirm Details</button>
        </form>
    </div>

    <script>
        function toggleFileUpload(selectElement, index) {
            const fileUpload = document.getElementById(`fileUpload_${index}`);
            if (selectElement.value === 'None') {
                fileUpload.style.display = 'none';
            } else {
                fileUpload.style.display = 'block';
            }
        }

        function goBack() {
            const urlParams = new URLSearchParams(window.location.search);
            const adults = urlParams.get('adults') || 0;
            const children = urlParams.get('children') || 0;
            const infants = urlParams.get('infants') || 0;
            window.location.href = `inputbookingdetails.php?adults=${adults}&children=${children}&infants=${infants}`;
        }
    </script>
</body>
</html>
