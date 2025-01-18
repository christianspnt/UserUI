<?php
// Start the session
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to the login page or homepage
header("Location: login.php"); // Change 'login.php' to the desired redirect page
exit();
?>
