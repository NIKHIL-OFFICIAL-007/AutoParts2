<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = 1; // Default quantity

    // Load product data from JSON
    $jsonPath = __DIR__ . "/parts.json";
    if (file_exists($jsonPath)) {
        $jsonContent = file_get_contents($jsonPath);
        $decoded = json_decode($jsonContent, true);
        if (isset($decoded['data']) && is_array($decoded['data'])) {
            $partsData = $decoded['data'];
            
            // Find the product by ID
            $product = null;
            foreach ($partsData as $item) {
                if ($item['id'] == $product_id) {
                    $product = $item;
                    break;
                }
            }
            
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
        }
    }
}

// Fallback redirection
header('Location: buyer.php');
exit;
?>