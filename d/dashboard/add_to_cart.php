<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1; // Default quantity

    // Load product data from JSON
    $jsonPath = __DIR__ . "/parts.json";
    if (file_exists($jsonPath)) {
        $jsonContent = file_get_contents($jsonPath);
        $decoded = json_decode($jsonContent, true);
        if (isset($decoded['data']) && is_array($decoded['data'])) {
            $partsData = $decoded['data'];
            
            // Find the product
            $product = null;
            foreach ($partsData as $item) {
                if ($item['name'] === $product_id) {
                    $product = $item;
                    break;
                }
            }
            
            if ($product) {
                // Initialize cart if not exists
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }
                
                // Add to cart or update quantity
                $found = false;
                foreach ($_SESSION['cart'] as &$cartItem) {
                    if ($cartItem['name'] === $product_id) {
                        $cartItem['quantity'] += $quantity;
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $product['quantity'] = $quantity;
                    $_SESSION['cart'][] = $product;
                }
                
                // Update cart count
                $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
            }
        }
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'buyer.php'));
exit;
?>