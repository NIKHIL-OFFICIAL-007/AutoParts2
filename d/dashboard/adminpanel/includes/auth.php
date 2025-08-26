<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

// Check if admin role
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../../index.php"); // Redirect to main site if not admin
    exit();
}

// Include database connection
require_once 'db_connection.php';

// Get current admin details
$admin_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();
?>