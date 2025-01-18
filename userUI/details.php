<?php
session_start();
require 'connect.php'; // Include the database connection file

// Check if user details are stored in session
if (!isset($_SESSION['user_details'])) {
    // If session is not set, redirect to register.php
    header("Location: register.php");
    exit();
}

$user_details = $_SESSION['user_details']; // Get user details from session

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the remaining details that need to be saved
    $address = $_POST['address'];
    $sex = $_POST['sex'];
    $dob = $_POST['dob'];
    $username = $_POST['username']; // Get username from form

    // Calculate age based on date of birth
    $age = date_diff(date_create($dob), date_create('today'))->y;

    try {
        // Update the user's additional details in the database
        $stmt = $conn->prepare("UPDATE passenger_accounts SET address = :address, sex = :sex, DOB = :dob, age = :age, username = :username WHERE email = :email");
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':sex', $sex);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':username', $username); // Bind the username
        $stmt->bindParam(':email', $user_details['email']);

        if ($stmt->execute()) {
            $message = "Your details have been updated successfully!";
            // Clear session data to avoid redundancy
            unset($_SESSION['user_details']);
            // Redirect to cebus.php after successful registration
            header("Location: cebus.php");
            exit();
        } else {
            $message = "An error occurred while saving your details. Please try again.";
        }
    } catch (PDOException $e) {
        $message = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Details</title>
    <link rel="stylesheet" href="busStyle.css">
</head>
<body class="body-details">
    <h2 class="h2-details">COMPLETE YOUR DETAILS</h2>
    <hr class="hr-details">
    <form action="details.php" method="POST">
        <div class="form-section-details">
            <!-- Left Side -->
            <div class="form-left-details">
                <div class="form-group-details">
                    <input type="text" id="first-name" name="first_name" value="<?php echo htmlspecialchars($user_details['fName']); ?>" readonly>
                    <label for="first-name">FIRST NAME</label>
                </div>
                <div class="form-group-details">
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_details['email']); ?>" readonly>
                    <label for="email">EMAIL</label>
                </div>
                <div class="form-group-details">
                    <input type="text" id="address" name="address" placeholder="Address" required>
                    <label for="address">ADDRESS</label>
                </div>
                <div class="age-sex-container-details">
                    <div class="form-group-details">
                        <input type="number" id="age" name="age" readonly>
                        <label for="age">AGE</label>
                    </div>
                    <div class="form-group-details sex-details">
                        <input type="text" id="sex" name="sex" list="sex-options" placeholder="Sex" required>
                        <datalist id="sex-options">
                            <option value="Male">
                            <option value="Female">
                        </datalist>
                        <label for="sex">SEX</label>
                    </div>
                </div>
            </div>
            <!-- Right Side -->
            <div class="form-right-details">
                <div class="form-group-details">
                    <input type="text" id="last-name" name="last_name" value="<?php echo htmlspecialchars($user_details['lName']); ?>" readonly>
                    <label for="last-name">LAST NAME</label>
                </div>
                <div class="form-group-details">
                    <input type="text" id="username" name="username" value="<?php echo isset($user_details['username']) ? htmlspecialchars($user_details['username']) : ''; ?>" placeholder="USERNAME" required>
                    <label for="username">USERNAME</label>
                </div>
                <div class="form-group-details">
                    <input type="tel" id="phone-number" name="phone_number" value="<?php echo htmlspecialchars($user_details['phone_num']); ?>" readonly>
                    <label for="phone-number">PHONE NUMBER</label>
                </div>
                <div class="form-group-details">
                    <input type="date" id="dob" name="dob" oninput="calculateAge()" required>
                    <label for="dob">DATE OF BIRTH</label>
                </div>
            </div>
        </div>
        <!-- Submit Button -->
        <button class="submit-btn-details" type="submit">REGISTER</button>
    </form>

    <script>
        function calculateAge() {
            const dob = document.getElementById("dob").value;
            const ageField = document.getElementById("age");
            
            if (dob) {
                const today = new Date();
                const birthDate = new Date(dob);
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                ageField.value = age; // Set the calculated age in the 'age' field
            }
        }
    </script>
</body>
</html>
