<?php
session_start(); // Start the session

// Include the database connection file
require("connect.php");

// Fetch bus routes for the destination datalist
$queryRoutes = $conn->prepare("SELECT destination FROM bus_route WHERE status = 'available'");
$queryRoutes->execute();
$routes = $queryRoutes->fetchAll(PDO::FETCH_ASSOC);

// Set timezone to match the database/server
date_default_timezone_set('Asia/Manila');

try {
    // Query to fetch upcoming trips
    $queryTrips = $conn->prepare("
        SELECT r.destination, t.departure_Time, r.base_fare
        FROM trip t
        JOIN bus_route r ON t.route_ID = r.route_ID
        WHERE t.status = 'Scheduled' 
        AND t.departure_Time > NOW()
        ORDER BY t.departure_Time ASC
    ");
    $queryTrips->execute();
    $trips = $queryTrips->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching trips: " . $e->getMessage();
}

// Process search request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedDestination = $_POST['destination'] ?? '';
    $isAvailable = false;

    // Check if selected destination is valid and available
    foreach ($routes as $route) {
        if ($route['destination'] === $selectedDestination) {
            $isAvailable = true;
            break;
        }
    }

    if ($isAvailable) {
        $_SESSION['selected_destination'] = $selectedDestination; // Store the destination in session
        header("Location: booking.php"); // Redirect to booking page
        exit();
    } else {
        $error = "Selected destination is either unavailable or invalid.";
    }
}

// Initialize a variable to hold the full name
$fullName = $_SESSION['fName'];

// Check if the user is logged in
if (isset($_SESSION['account_ID'])) {
    $accountID = $_SESSION['account_ID'];

    // Fetch the first name and last name of the logged-in user
    $query = "SELECT fName, lName FROM passenger_accounts WHERE account_ID = :accountID AND status = 'Active'";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':accountID', $accountID, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the full name
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $fullName = htmlspecialchars($row['fName'] . ' ' . $row['lName']); // Combine first and last name
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cebu Bus Terminal - Booking</title>
    <link rel="stylesheet" href="busStyle.css">
</head>
<body>
    <!-- Image Container -->
    <div class="image-container">
        <img src="images/img1.jpeg" alt="Banner Image">
        <div class="icons">
            <a href="bus.php"><img src="images/busIcon.png" alt="Bus Icon"></a>
            <hr>
            <img src="images/profileIcon.png" alt="Profile Icon" id="profile-icon">
            <img src="images/activeProfile.png" alt="Active Profile Icon" id="profile-icon-active" style="display: none;">
        </div>
        <div class="profile-box" id="profile-box">
            <button onclick="location.href='profile.php'" class="profile-header">
                <img src="images/profilePicIcon.png" alt="Profile Picture">
                <div class="profile-info">
                    <p class="profile-name"><?php echo $fullName; ?></p>
                    <p class="profile-manage">Manage Profile</p>
                </div>
            </button>
            <hr>
            <button class="buttons" onclick="location.href='settings.php'"><img src="images/settingIcon.png" alt="">Settings</button>
            <button class="buttons" onclick="location.href='card.php'"><img src="images/cardIcon.png" alt="">Tap Card</button>
            <button class="buttons" onclick="location.href='paymentmethod.php'"><img src="images/paymentIcon.png" alt="">Payment Settings</button>
            <hr>
            <button class="Outbuttons" onclick="location.href='logout.php'">Log Out</button>
        </div>
    </div>

    <!-- Body -->
    <div class="body">
        <h2 class="heading">Seamless Journeys, One Scan Away!</h2>
        <div class="box">
            <div class="trip-title">One-way Trip Ticket</div>
            <div class="trip-info">
                <span>CSB Terminal</span>
                <span style="color: #3bada4;"> ➜ </span>
                <form class="form-bus" method="POST" action="">
                    <input type="text" list="destinations" name="destination" placeholder="Select Destination" required>
                    <datalist id="destinations">
                        <?php foreach ($routes as $route): ?>
                            <option value="<?php echo htmlspecialchars($route['destination']); ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </form>
            </div>
            <div class="date">
                Date: <span id="current-date"></span>
            </div>
        </div>
        <button class="search-btn" id="search-btn">Search</button>
    </div>

    <!-- Trip Display Section -->
<div class="trips-container-bkng">
        <?php if (!empty($trips)) { ?>
            <?php foreach ($trips as $trip) { ?>
                <div class="trips-container-bkng">
                    <a href="inputBookingDetails.php?destination=<?= urlencode($trip['destination']) ?>&departure_time=<?= urlencode($trip['departure_Time']) ?>&base_fare=<?= urlencode($trip['base_fare']) ?>" class="trip-link">
                        <div class="trip-bkng">
                            <div class="trip-info-bkng">
                                <span>CSB Terminal - <?= htmlspecialchars($trip['destination']) ?></span>
                                <div class="trip-time-bkng"><?= date("Y-m-d") ?> | Trip Time: <?= date("H:i", strtotime($trip['departure_Time'])) ?></div>
                            </div>
                            <div class="trip-price-bkng">₱<?= number_format($trip['base_fare'], 2) ?></div>
                        </div>
                    </a>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="no-trips-container">
                <div class="no-trips-bkng">
                    No trips available within the next hour.
                </div>
            </div>
        <?php } ?>
    </div>

    <script>
        // Format the date as dd-Mon-yyyy
        function formatDate() {
            const date = new Date();
            const months = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
            const day = date.getDate();
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            document.getElementById('current-date').textContent = `${day < 10 ? '0' + day : day}-${month}-${year}`;
        }
        window.onload = formatDate;

        // Search button validation
        const searchButton = document.getElementById('search-btn');
        const destinationInput = document.querySelector('.trip-info input[type="text"]');
        searchButton.addEventListener('click', function (event) {
            if (!destinationInput.value.trim()) {
                event.preventDefault();
                alert('Please enter a destination before searching.');
            }
        }); 

        // Profile toggle
        const profileIcon = document.getElementById('profile-icon');
        const profileIconActive = document.getElementById('profile-icon-active');
        const profileBox = document.getElementById('profile-box');
        profileIcon.addEventListener('click', () => {
            profileBox.style.display = profileBox.style.display === 'block' ? 'none' : 'block';
            profileIcon.style.display = 'none';
            profileIconActive.style.display = 'block';
        });
        profileIconActive.addEventListener('click', () => {
            profileBox.style.display = 'none';
            profileIcon.style.display = 'block';
            profileIconActive.style.display = 'none';
        });
    </script>
</body>
</html>
