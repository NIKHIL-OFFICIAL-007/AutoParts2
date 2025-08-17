<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['index'])) {
    $index = intval($_GET['index']);
    if (isset($_SESSION['cart'][$index])) {
        array_splice($_SESSION['cart'], $index, 1);
        
        // Update cart count
        $_SESSION['cart_count'] = empty($_SESSION['cart']) ? 0 : array_sum(array_column($_SESSION['cart'], 'quantity'));
    }
}

header('Location: cart.php');
exit;
?>