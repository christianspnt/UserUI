<?php
session_start();
require 'connect.php'; // Include the database connection file

$error = ""; // Initialize an error message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Use the $conn variable from connect.php
        $stmt = $conn->prepare("SELECT * FROM passenger_accounts WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // Fetch the user data
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['account_ID'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fName'] = $user['fName'];

                // Redirect to cebus.php
                header("Location: cebus.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Username not found.";
        }
    } catch (PDOException $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Panel</title>
    <link rel="stylesheet" href="busStyle.css">
</head>
<body class="body-login">
    <div class="image-section-login"></div>
    <div class="form-section-login">
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="USERNAME" required>
            <input type="password" name="password" placeholder="PASSWORD" required>
            <a class="forgot-pass-login" href="forgot_password.php">FORGOT PASSWORD?</a>
            <button class="login-button1" type="submit">LOGIN</button>
        </form>
        <div class="separator-login"><span>OR</span></div>
        <button class="social-btn-login" onclick="location.href='SocialLogin.php';">
            <img class="img1" src="images/Google.jpeg" alt="Google">
            <img class="img2" src="images/Facebook.png" alt="Facebook">
            <img class="img3" src="images/apple.png" alt="Apple">
        </button>
        <p class="centered-register-login">Don't have an account yet? <a href="register.php">REGISTER</a></p>
    </div>
</body>
</html>
