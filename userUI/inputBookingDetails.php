<?php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the posted data
    $route = $_SESSION['tripDetails']['route'];
    $dateTime = $_SESSION['tripDetails']['dateTime'];
    $distance = $_SESSION['tripDetails']['distance'];
    $bookingType = $_SESSION['tripDetails']['bookingType'];
    $numPassengers = $_SESSION['tripDetails']['numPassengers'];
    $farePerPassenger = $_SESSION['tripDetails']['farePerPassenger'];
    $totalFare = $_SESSION['tripDetails']['totalFare'];
    $paymentMethod = $_SESSION['tripDetails']['paymentMethod'];
    $paymentStatus = $_SESSION['tripDetails']['paymentStatus'];

    // Insert into online_bookings table
    $query = "INSERT INTO online_bookings (account_ID, terminal_ID, trip_ID, starting_point, destination, total_distance, booking_type, no_of_passenger, total_price, payment_method_ID, payment_status, date_booked) 
              VALUES (:account_ID, :terminal_ID, :trip_ID, :starting_point, :destination, :total_distance, :booking_type, :no_of_passenger, :total_price, :payment_method_ID, :payment_status, :date_booked)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':account_ID', $_SESSION['user_id'], PDO::PARAM_INT); // Assuming user_id is stored in the session
    $stmt->bindParam(':terminal_ID', $_SESSION['terminal_id'], PDO::PARAM_INT); // Assuming terminal_id is stored in the session
    $stmt->bindParam(':trip_ID', $_SESSION['trip_id'], PDO::PARAM_INT); // Assuming trip_id is stored in the session
    $stmt->bindParam(':starting_point', $_SESSION['starting_point'], PDO::PARAM_STR);
    $stmt->bindParam(':destination', $_SESSION['destination'], PDO::PARAM_STR);
    $stmt->bindParam(':total_distance', $distance, PDO::PARAM_STR);
    $stmt->bindParam(':booking_type', $bookingType, PDO::PARAM_STR);
    $stmt->bindParam(':no_of_passenger', $numPassengers, PDO::PARAM_INT);
    $stmt->bindParam(':total_price', $totalFare, PDO::PARAM_STR);
    $stmt->bindParam(':payment_method_ID', $_SESSION['payment_method_id'], PDO::PARAM_INT); // Assuming payment method ID is stored in the session
    $stmt->bindParam(':payment_status', $paymentStatus, PDO::PARAM_STR);
    $stmt->bindParam(':date_booked', $dateTime, PDO::PARAM_STR);

    $stmt->execute();
    $booking_ID = $conn->lastInsertId(); // Get the last inserted booking ID
    
    // Insert into online_passenger_details table
    for ($i = 0; $i < $numPassengers; $i++) {
        // Assuming passenger details come from form inputs
        $firstName = $_POST["first_name_{$i}"];
        $lastName = $_POST["last_name_{$i}"];
        $phoneNumber = $_POST["phone_number_{$i}"];
        $passengerType = $_POST["passenger_type_{$i}"];
        $discount = $_POST["discount_{$i}"];
        $price = $_POST["price_{$i}"];
        $attachmentDoc = $_POST["attachment_doc_{$i}"];

        $query = "INSERT INTO online_passenger_details (booking_ID, first_name, last_name, phone_number, passenger_type, discount, price, attachment_doc) 
                  VALUES (:booking_ID, :first_name, :last_name, :phone_number, :passenger_type, :discount, :price, :attachment_doc)";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':booking_ID', $booking_ID, PDO::PARAM_INT);
        $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':phone_number', $phoneNumber, PDO::PARAM_STR);
        $stmt->bindParam(':passenger_type', $passengerType, PDO::PARAM_STR);
        $stmt->bindParam(':discount', $discount, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':attachment_doc', $attachmentDoc, PDO::PARAM_STR);
        
        $stmt->execute();
    }

    // Redirect to cebus.php after saving
    header('Location: cebus.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Booking Details</title>
    <link rel="stylesheet" href="busStyle.css">
    <script>
        function toggleBookingForOthers() {
            const isBookingForOthers = document.getElementById('bookingSwitch').checked;
            const inputs = document.querySelectorAll('.input-row-inputBooking input');

            inputs.forEach((input, index) => {
                if (index === 2) {
                    // Always keep the +63 field disabled
                    input.disabled = true;
                    input.value = '+63';
                } else {
                    // Enable/disable other fields based on the switch
                    input.disabled = !isBookingForOthers;
                    if (!isBookingForOthers) {
                        input.value = index === 1 ? '' : `Auto-filled ${index}`; // Example values
                    } else {
                        input.value = '';
                    }
                }
            });
        }

        function updateBookingType() {
    // Calculate the total number of passengers
    const totalPassengers = parseInt(document.getElementById('adultQuantity').value) +
                            parseInt(document.getElementById('childrenQuantity').value) +
                            parseInt(document.getElementById('infantQuantity').value);

    // Check the total number of passengers
    if (totalPassengers <= 1) {
        document.querySelector('input[name="bookingType"][value="Individual"]').checked = true;
        document.querySelector('input[name="bookingType"][value="Group"]').checked = false;
    } else {
        document.querySelector('input[name="bookingType"][value="Individual"]').checked = false;
        document.querySelector('input[name="bookingType"][value="Group"]').checked = true;
    }
}


        function updateGroupDetailsButton() {
            const adults = parseInt(document.getElementById('adultQuantity').value);
            const children = parseInt(document.getElementById('childrenQuantity').value);
            const infants = parseInt(document.getElementById('infantQuantity').value);
            const total = adults + children + infants;
            const groupDetailsButton = document.getElementById('groupDetailsButton');
            groupDetailsButton.disabled = total <= 1;  // Disables button if total passengers is <= 1
        }

        function validatePhoneNumber(input) {
            input.value = input.value.replace(/\D/g, ''); // Remove non-numeric characters
            if (input.value.length > 10) {
                input.value = input.value.slice(0, 10); // Limit to 10 digits
            }
        }

        function increment(id) {
            let adultQuantity = parseInt(document.getElementById("adultQuantity").value);
            let childrenQuantity = parseInt(document.getElementById("childrenQuantity").value);
            let infantQuantity = parseInt(document.getElementById("infantQuantity").value);
            let total = adultQuantity + childrenQuantity + infantQuantity;

            let input = document.getElementById(id);
            let value = parseInt(input.value);

            if (id === "adultQuantity" && value < 10 && total < 10) {
                input.value = value + 1;
            } else if (id === "childrenQuantity" && value < 8 && total < 10) {
                input.value = value + 1;
            } else if (id === "infantQuantity" && value < 8 && total < 10) {
                input.value = value + 1;
            }

            updateLimits();
            updateBookingType();
        }

        function decrement(id) {
            let input = document.getElementById(id);
            let value = parseInt(input.value);

            if (value > 0) {
                input.value = value - 1;
            }

            updateLimits();
            updateBookingType();
        }

        function updateLimits() {
            let adultQuantity = parseInt(document.getElementById("adultQuantity").value);
            let childrenQuantity = parseInt(document.getElementById("childrenQuantity").value);
            let infantQuantity = parseInt(document.getElementById("infantQuantity").value);
            let total = adultQuantity + childrenQuantity + infantQuantity;

            // Disable fields based on max conditions
            document.getElementById("adultQuantity").disabled = (total >= 10 || childrenQuantity === 8 || infantQuantity === 8);
            document.getElementById("childrenQuantity").disabled = (childrenQuantity === 8 || total >= 10 || infantQuantity === 8);
            document.getElementById("infantQuantity").disabled = (infantQuantity === 8 || total >= 10 || childrenQuantity === 8);

            // Ensure dependency between Adults and Children
            if (childrenQuantity === 0) {
                document.getElementById("adultQuantity").value = Math.max(adultQuantity, 1);
            }

            if (adultQuantity === 0 && childrenQuantity === 0) {
                document.getElementById("adultQuantity").value = 1;
            }

            // Enable or disable the button based on total
            updateGroupDetailsButton();
        }

        function redirectToGroupDetails() {
            const adultQuantity = parseInt(document.getElementById("adultQuantity").value);
            const childrenQuantity = parseInt(document.getElementById("childrenQuantity").value);
            const infantQuantity = parseInt(document.getElementById("infantQuantity").value);

            const queryParams = new URLSearchParams({
                adults: adultQuantity,
                children: childrenQuantity,
                infants: infantQuantity,
            });

            window.location.href = `groupDetails.php?${queryParams.toString()}`;
        }

        function updateBookingType() {
            const totalPassengers = parseInt(document.getElementById('adultQuantity').value) +
                                    parseInt(document.getElementById('childrenQuantity').value) +
                                    parseInt(document.getElementById('infantQuantity').value);

            document.querySelector('input[name="bookingType"][value="Individual"]').checked = totalPassengers <= 1;
            document.querySelector('input[name="bookingType"][value="Group"]').checked = totalPassengers > 1;
        }
        

        function updatePaymentMethod() {
            const paymentMethod = document.getElementById('paymentMethod');
            const paymentDisplay = document.getElementById('paymentDisplay');
            const finalFare = parseFloat(<?php echo $base_fare; ?>);

            if (paymentMethod.value === 'Cash') {
                paymentDisplay.value = `Amount: PHP ${finalFare}`;
            } else {
                paymentDisplay.value = 'GCash'; // Example for cashless
            }
        }
    </script>
</head>
<body class="body-inputBooking">
    <button class="back-button-inputBooking" onclick="location.href='booking.php'">&#8592;</button>
    <div class="booking-container-inputBooking">
        <div class="switch-container-inputBooking">
            <label class="switch-inputBooking">
                <input type="checkbox" id="bookingSwitch" onchange="toggleBookingForOthers()">
                <span class="slider-inputBooking"></span>
            </label>
            <label for="bookingSwitch">Book for someone else</label>
        </div>

        <div class="input-row-inputBooking">
            <input type="text" id="guestFirstName" placeholder="Guest First Name" disabled>
            <input type="text" id="guestLastName" placeholder="Guest Last Name" disabled>
        </div>

        <div class="input-row-inputBooking">
    <input type="text" class="readonly-field-inputBooking phonenum" value="+63" placeholder="+63" disabled>
    <input type="tel" id="guestPhoneNumber" placeholder="900 0000 000" maxlength="10" disabled oninput="validatePhoneNumber(this)">
    <input type="email" id="guestEmail" placeholder="guest@example.com" disabled>
</div>

        <input type="text" class="readonly-field-inputBooking" value="CSB Terminal - Destination" disabled>

        <div class="checkbox-container-inputBooking">
    <label style="font-weight: bold;">Booking Type:</label>
    <label class="custom-radio-inputBooking">
        <input type="radio" name="bookingType" value="Individual" checked>
        <span class="custom-radio-box">✔</span> Individual
    </label>
    <label class="custom-radio-inputBooking">
        <input type="radio" name="bookingType" value="Group">
        <span class="custom-radio-box">✔</span> Group (Max. 10 pax)
    </label>
</div>


<div class="quantity-container">
    <div class="form-group quantity">
        <label>Adults:</label>
        <div class="quantity-wrapper">
            <button type="button" onclick="decrement('adultQuantity')">-</button>
            <input type="text" id="adultQuantity" value="1" readonly>
            <button type="button" onclick="increment('adultQuantity')">+</button>
        </div>
    </div>

    <div class="form-group quantity">
        <label>Children:</label>
        <div class="quantity-wrapper">
            <button type="button" onclick="decrement('childrenQuantity')">-</button>
            <input type="text" id="childrenQuantity" value="0" readonly>
            <button type="button" onclick="increment('childrenQuantity')">+</button>
        </div>
    </div>

    <div class="form-group quantity">
        <label>Infants:</label>
        <div class="quantity-wrapper">
            <button type="button" onclick="decrement('infantQuantity')">-</button>
            <input type="text" id="infantQuantity" value="0" readonly>
            <button type="button" onclick="increment('infantQuantity')">+</button>
        </div>
    </div>
</div>

<div class="dropdown-row-inputBooking">
    <label for="discountDropdown">Discount:</label>
    <div class="dropdown-container-inputBooking">
        <select id="discountDropdown" onchange="toggleDiscountFileRequirement()">
            <option value="">Select</option>
            <option value="Student">Student</option>
            <option value="Senior">Senior Citizen</option>
            <option value="PWD">PWD</option>
        </select>
        <input type="file" id="discountDocument">
    </div>
</div>


<div class="action-row-inputBooking">
    <button id="groupDetailsButton" class="confirm-button-inputBooking" onclick="redirectToGroupDetails()" disabled>Group Details</button>
    <select id="paymentMethod">
        <option value="Cash">Cash</option>
        <option value="Cashless">Cashless</option>
    </select>
    <input type="text" readonly id="totalPassengers" value="1">
</div>


<form action="bookingDetails.php" method="post" style="text-align: center;">
    <button class="confirm-button-inputBooking" type="submit">Confirm</button>
</form>
</body>
</html>
