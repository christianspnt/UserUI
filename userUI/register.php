<?php
session_start();
require 'connect.php'; // Include the database connection file

$message = ""; // Initialize a message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $phonenumber = $_POST['phonenumber'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Split the full name into first name and last name
    $name_parts = explode(' ', trim($fullname));
    
    // If there are multiple names, first name is everything except the last part
    $fName = implode(' ', array_slice($name_parts, 0, -1)); // Everything except the last part
    $lName = end($name_parts); // The last part will be the last name

    try {
        // Check if the email already exists
        $stmt = $conn->prepare("SELECT * FROM passenger_accounts WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $message = "An account with this email already exists.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the user into the database
            $stmt = $conn->prepare("INSERT INTO passenger_accounts (fName, lName, phone_num, email, password, date_created, status) VALUES (:fName, :lName, :phone_num, :email, :password, NOW(), 'Active')");
            $stmt->bindParam(':fName', $fName);
            $stmt->bindParam(':lName', $lName);
            $stmt->bindParam(':phone_num', $phonenumber);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);

            if ($stmt->execute()) {
                // Registration successful, redirect to details.php
                $_SESSION['user_details'] = [
                    'email' => $email,
                    'fName' => $fName,
                    'lName' => $lName,
                    'phone_num' => $phonenumber
                ];
                $message = "Registration successful! Please complete your details.";
                header("Location: details.php");
                exit();
            } else {
                $message = "An error occurred while registering. Please try again.";
            }
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
    <title>Full-Screen Register Panel</title>
    <link rel="stylesheet" href="busStyle.css">
</head>
<body class="body-register">
    <div class="form-section-rgstr">
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <input type="text" name="fullname" placeholder="FULL NAME" required>
            <input type="text" name="phonenumber" placeholder="PHONE NUMBER" required>
            <input type="email" name="email" placeholder="EMAIL" required>
            <input type="password" name="password" placeholder="PASSWORD" required>
            <button class="sign-up-button-rgstr" type="submit">SIGN UP</button>
        </form>
        <div class="separator-rgstr"><span>OR</span></div>
        <button class="social-btn2" onclick="location.href='SocialLogin.php';">
            <img class="img1" src="images/Google.jpeg" alt="Google">
            <img class="img2" src="images/Facebook.png" alt="Facebook">
            <img class="img3" src="images/apple.png" alt="Apple">
        </button>
        <p class="centered-login-rgstr">Already have an account? <a href="login.php">LOGIN</a></p>
    </div>
    <div class="image-section-rgstr"></div>
</body>
</html>
