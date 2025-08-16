<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit;
}

require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: my_products.php");
    exit;
}

$product_id = $_GET['id'];
$seller_id = $_SESSION['user_id'];

// First get the image path to delete the file
$query = "SELECT image_path FROM products WHERE id = ? AND seller_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_products.php");
    exit;
}

$product = $result->fetch_assoc();
$image_path = $product['image_path'];
$stmt->close();

// Delete the product from database
$delete_query = "DELETE FROM products WHERE id = ? AND seller_id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();

// Delete the image file if it exists
if (file_exists($image_path)) {
    unlink($image_path);
}

header("Location: my_products.php?deleted=1");
exit;
?>