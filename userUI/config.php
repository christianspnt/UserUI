<?php
$servername = "localhost:3307";
$username = "root";
$password = "summitWATER2024!";
$dbname = "cebus";

try{
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    //setting PDO error to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e){

    /*/ Log the error for debugging and show a generic message to the user

    error_log("Database connection failed: " . $e->getMessage(), 0);
    echo "We are experiencing technical difficulties. Please try again later.";
    die(); // Stop script execution after connection failure

    */
    
    echo"Connection failed! ".$e->getMessage();
    die();
}

?>