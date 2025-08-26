<?php
// db.php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "autoparts";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("We're experiencing technical difficulties. Please try again later.");
}

// Set charset to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

