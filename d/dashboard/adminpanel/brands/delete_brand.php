<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Check admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get brand ID from URL
$brand_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate brand ID
if ($brand_id <= 0) {
    $_SESSION['error'] = "Invalid brand ID";
    header('Location: manage_brands.php');
    exit;
}

// Check if brand exists
try {
    $stmt = $pdo->prepare("SELECT id FROM brands WHERE id = ?");
    $stmt->execute([$brand_id]);
    $brand = $stmt->fetch();

    if (!$brand) {
        $_SESSION['error'] = "Brand not found";
        header('Location: manage_brands.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header('Location: manage_brands.php');
    exit;
}

// Check if brand is used in products
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE brand_id = ?");
    $stmt->execute([$brand_id]);
    $product_count = $stmt->fetchColumn();

    if ($product_count > 0) {
        $_SESSION['error'] = "Cannot delete brand - it is used by products";
        header('Location: manage_brands.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error checking products: " . $e->getMessage();
    header('Location: manage_brands.php');
    exit;
}

// Perform deletion
try {
    $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
    $stmt->execute([$brand_id]);
    
    $_SESSION['success'] = "Brand deleted successfully";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting brand: " . $e->getMessage();
}

// Redirect back to manage brands
header('Location: manage_brands.php');
exit;
?>