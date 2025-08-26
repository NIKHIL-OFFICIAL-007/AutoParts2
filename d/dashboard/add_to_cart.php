<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = $_POST['product_id'];
    
    // Fetch product details from database
    $stmt = $pdo->prepare("
        SELECT p.*, b.name as brand_name, c.name as category_name 
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Check if product already exists in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity'] += 1;
                $found = true;
                break;
            }
        }
        
        // If not found, add new item to cart
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'brand' => $product['brand_name'],
                'price' => $product['price'],
                'image' => $product['image_path'],
                'quantity' => 1
            ];
        }
        
        // Update cart count
        $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
        
        // Redirect back to the previous page instead of cart.php
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?added_to_cart=true");
        exit;
    } else {
        // Product not found
        header("Location: buyer.php?error=product_not_found");
        exit;
    }
} else {
    header("Location: buyer.php");
    exit;
}