<?php
session_start(); // Start the session

// Include the database connection file
require_once 'connect.php';

// Query to fetch bus routes
$stmt = $conn->prepare("SELECT * FROM bus_route");
$stmt->execute();
$routes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process search request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedDestination = $_POST['destination'];
    $isAvailable = false;

    // Check if selected destination is available
    foreach ($routes as $route) {
        if ($route['destination'] === $selectedDestination && $route['status'] === 'available') {
            $isAvailable = true;
            break;
        }
    }

    if ($isAvailable) {
        $_SESSION['selected_destination'] = $selectedDestination; // Store the destination in the session
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
    $stmt = $pdo->prepare($query);
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
    <title>Cebu Bus Terminal - Landing Page</title>
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
            <img src="images/activeProfile.png" alt="Profile Icon Active" id="profile-icon-active" style="display: none;">
        </div>
        <div class="profile-box" id="profile-box">
            <button onclick="location.href='profile.php' " class="profile-header">
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

    <div class="body">
        <h2 class="heading">Seamless Journeys, One Scan Away!</h2>
        <div class="box">
            <!-- Title "One-way Trip Ticket" at the top -->
            <div class="trip-title">One-way Trip Ticket</div>

            <!-- Trip information (CSB Terminal ➜ and input field) -->
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

            <!-- Date on a separate line -->
            <div class="date">
                Date: <span id="current-date"></span>
            </div>
        </div>
        <!-- Search Button Outside the Box -->
        <button class="search-btn" onclick="location.href='booking.php'">Search</button>
    </div>

    <div class="second-box">
        <div class="availability-header">
            <h2>BUS AVAILABILITY</h2>
        </div>
        <div class="availability-content">
            <?php foreach ($routes as $route): ?>
                <div class="destination-wrapper">
                    <div class="destination-card">
                        <span class="destination-name"><?php echo htmlspecialchars($route['starting_point']) . ' - ' . htmlspecialchars($route['destination']); ?></span>
                    </div>
                    <div class="availability-status <?php echo $route['status'] == 'available' ? 'available' : 'not-available'; ?>">
                        <?php echo strtoupper($route['status']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="wave"></div>
        <p>&copy; 2024 Cebu Bus Terminal. All Rights Reserved.</p>
    </footer>

    <script>
        // Function to format the date as dd-Mon-yyyy
        function formatDate() {
            const date = new Date(); // Get the current date
            const months = ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"];
            const day = date.getDate();
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            const formattedDate = `${day < 10 ? '0' + day : day}-${month}-${year}`;
            document.getElementById('current-date').textContent = formattedDate;
        }

        window.onload = formatDate;

        const profileIcon = document.getElementById('profile-icon');
        const profileIconActive = document.getElementById('profile-icon-active');
        const profileBox = document.getElementById('profile-box');

        profileIcon.addEventListener('click', () => {
            profileBox.style.display = profileBox.style.display === 'block' ? 'none' : 'block';
            profileIcon.style.display = 'none';
            profileIconActive.style.display = 'block';
        });

        profileIconActive.addEventListener('click', () => {
            profileIcon.style.display = 'block';
            profileIconActive.style.display = 'none';
            profileBox.style.display = 'none';
        });
    </script>
</body>
</html>
