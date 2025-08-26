<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Check admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Unauthorized access';
    header('Location: ../login.php');
    exit;
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate product ID
if ($product_id <= 0) {
    $_SESSION['error'] = 'Invalid product ID';
    header('Location: manage_products.php');
    exit;
}

// Check if product exists
try {
    $stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error'] = 'Product not found';
        header('Location: manage_products.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header('Location: manage_products.php');
    exit;
}

// Check if product has related orders (optional protection)
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $order_count = $stmt->fetchColumn();

    if ($order_count > 0) {
        $_SESSION['error'] = 'Cannot delete product - it has associated orders';
        header('Location: manage_products.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error checking orders: ' . $e->getMessage();
    header('Location: manage_products.php');
    exit;
}

// Delete the product
try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "Product '{$product['name']}' deleted successfully";
    } else {
        $_SESSION['error'] = 'No product was deleted';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error deleting product: ' . $e->getMessage();
}

// Redirect back to product management
header('Location: manage_products.php');
exit;
?>