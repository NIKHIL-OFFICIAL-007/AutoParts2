<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access";
    header('Location: ../login.php');
    exit;
}

// Validate user ID
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    $_SESSION['error'] = "Invalid user ID";
    header('Location: manage_users.php');
    exit;
}

// Prevent self-deletion
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account";
    header('Location: manage_users.php');
    exit;
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "User not found";
        header('Location: manage_users.php');
        exit;
    }

    // Check if user has important relations (e.g., orders, products)
    // Example for sellers - prevent deletion if they have products
    if ($user['role'] === 'seller') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
        $stmt->execute([$user_id]);
        $product_count = $stmt->fetchColumn();

        if ($product_count > 0) {
            $_SESSION['error'] = "Cannot delete seller - they have associated products. Reassign products first.";
            header('Location: manage_users.php');
            exit;
        }
    }

    // For buyers - check if they have orders
    if ($user['role'] === 'buyer') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $order_count = $stmt->fetchColumn();

        if ($order_count > 0) {
            $_SESSION['error'] = "Cannot delete buyer - they have order history";
            header('Location: manage_users.php');
            exit;
        }
    }

    // Perform deletion
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "User deleted successfully";
    } else {
        $_SESSION['error'] = "No user was deleted";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log("Delete user error: " . $e->getMessage());
}

// Redirect back to manage categories
header('Location: /c/dashboard/adminpanel/users/manage_users.php');
exit;
?>