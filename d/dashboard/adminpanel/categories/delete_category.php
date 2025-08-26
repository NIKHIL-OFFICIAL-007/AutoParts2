<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Check if user is authorized
if (!isset($_SESSION['user_id'])) {
    header('Location: /d/dashboard/adminpanel/login.php');
    exit;
}

// Validate category ID
$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($categoryId <= 0) {
    $_SESSION['error'] = "Invalid category ID";
    header('Location: manage_categories.php');
    exit;
}

// Check if category exists
try {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();

    if (!$category) {
        $_SESSION['error'] = "Category not found";
        header('Location: manage_categories.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header('Location: manage_categories.php');
    exit;
}

// Perform deletion
try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    
    $_SESSION['success'] = "Category deleted successfully";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting category: " . $e->getMessage();
}

// Redirect back to manage categories
header('Location: /d/dashboard/adminpanel/categories/manage_categories.php');
exit;
?>