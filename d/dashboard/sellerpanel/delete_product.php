<?php
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];

// Check if product ID is provided
if (!isset($_POST['product_id'])) {
    $_SESSION['error_message'] = "Product ID not provided";
    header('Location: my_products.php');
    exit();
}

$product_id = (int)$_POST['product_id'];

try {
    // First get the product details to delete the image
    $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error_message'] = "Product not found or you don't have permission to delete it";
        header('Location: my_products.php');
        exit();
    }

    // Delete the product image if it exists and isn't a URL
    if (!empty($product['image_path']) && strpos($product['image_path'], 'http') === false) {
        $image_path = __DIR__ . '/' . $product['image_path'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Delete the product from database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);

    $_SESSION['success_message'] = "Product deleted successfully!";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error deleting product: " . $e->getMessage();
}

header('Location: my_products.php');
exit();
?>