<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must be logged in to perform this action";
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

    // Delete the product image if it exists and is a local file
    if (!empty($product['image_path']) && strpos($product['image_path'], 'http') !== 0) {
        // Construct the absolute path to the file
        $image_path = $_SERVER['DOCUMENT_ROOT'] . $product['image_path'];
        
        // Verify the file exists and is within our uploads directory
        if (file_exists($image_path)) {
            // Additional security: check if the path is within our uploads directory
            $uploads_dir = $_SERVER['DOCUMENT_ROOT'] . '/d/uploads/products/';
            if (strpos(realpath($image_path), realpath($uploads_dir)) === 0) {
                unlink($image_path);
            } else {
                error_log("Attempted to delete file outside uploads directory: $image_path");
            }
        }
    }

    // Delete the product from database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);

    $_SESSION['success_message'] = "Product deleted successfully!";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error deleting product: " . $e->getMessage();
} catch (Exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
}

header('Location: my_products.php');
exit();
?>