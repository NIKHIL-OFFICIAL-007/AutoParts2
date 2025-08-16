<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
    $quantities = $_POST['quantities'];
    
    foreach ($quantities as $index => $quantity) {
        $qty = max(1, intval($quantity));
        if (isset($_SESSION['cart'][$index])) {
            $_SESSION['cart'][$index]['quantity'] = $qty;
        }
    }
    
    // Update cart count
    $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
}

header('Location: cart.php');
exit;
?>