<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = 1; // Default quantity

    // Database connection
    require_once 'db.php';
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name 
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Clear cart and add only this product
            $_SESSION['cart'] = [];
            $product['quantity'] = $quantity;
            $_SESSION['cart'][] = $product;
            
            // Update cart count
            $_SESSION['cart_count'] = $quantity;
            
            // Redirect to checkout
            header('Location: checkout.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

// Fallback redirection
header('Location: buyer.php');
exit;
?>