<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];
$page_title = "My Products - AutoParts Seller Portal";

// Handle product deletion FIRST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    require_once 'includes/db.php';
    
    $product_id = (int)($_POST['product_id'] ?? 0);

    try {
        // First get the product image path
        $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$product_id, $seller_id]);
        $product = $stmt->fetch();

        if ($product) {
            // Delete the image file if it exists and is local
            if (!empty($product['image_path']) && strpos($product['image_path'], 'http') === false) {
                $image_path = __DIR__ . '/' . $product['image_path'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }

            // Delete the product from the database
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
            $stmt->execute([$product_id, $seller_id]);

            $_SESSION['success_message'] = "Product deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Product not found or you don't have permission to delete it.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting product: " . $e->getMessage();
    }

    // Redirect back to my_products.php
    header('Location: my_products.php');
    exit();
}

// Now include other files
require_once 'includes/db.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';


$seller_id = $_SESSION['user_id'];
$page_title = "My Products - AutoParts Seller Portal";

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $per_page) - $per_page : 0;

// Get total number of products for pagination
$total = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
$total->execute([$seller_id]);
$total_results = $total->fetchColumn();

// Calculate total pages
$total_pages = ceil($total_results / $per_page);

// Get products with pagination
$stmt = $pdo->prepare("
    SELECT p.*, b.name as brand_name, c.name as category_name 
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.seller_id = ?
    ORDER BY p.created_at DESC
    LIMIT ?, ?
");
$stmt->bindValue(1, $seller_id, PDO::PARAM_INT);
$stmt->bindValue(2, $start, PDO::PARAM_INT);
$stmt->bindValue(3, $per_page, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['product_id'];
    
    try {
        // First delete the product image if it exists
        $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$product_id, $seller_id]);
        $product = $stmt->fetch();
        
        if ($product && !empty($product['image_path']) && strpos($product['image_path'], 'http') === false) {
            $image_path = __DIR__ . '/' . $product['image_path'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Then delete the product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
        $stmt->execute([$product_id, $seller_id]);
        
        $_SESSION['success_message'] = "Product deleted successfully!";
        header('Location: my_products.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting product: " . $e->getMessage();
        header('Location: my_products.php');
        exit();
    }
}
?>

<div class="main-content flex-1 bg-white p-7">
    <div class="header flex justify-between items-center mb-7 pb-5 border-b border-black/5">
        <div class="page-title flex items-center gap-3 text-2xl font-bold text-dark">
            <i class="fa-solid fa-boxes-stacked text-primary"></i>
            <h1>My Products</h1>
        </div>
        <div class="header-actions">
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fa-solid fa-plus mr-2"></i> Add New Product
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success mb-5 p-4 bg-success/10 text-success border border-success/20 rounded-lg animate-fadeIn">
            <i class="fa-solid fa-circle-check mr-2"></i> <?= $_SESSION['success_message'] ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error mb-5 p-4 bg-danger/10 text-danger border border-danger/20 rounded-lg animate-fadeIn">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> <?= $_SESSION['error_message'] ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Products Table -->
    <div class="products-table bg-white rounded-xl shadow-md overflow-hidden animate-fadeIn">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No products found. <a href="dashboard.php" class="text-primary hover:underline">Add your first product</a></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <?php if (!empty($product['image_path'])): ?>
                                            <img class="h-10 w-10 rounded object-cover" 
                                                 src="<?= strpos($product['image_path'], 'http') === 0 ? $product['image_path'] : htmlspecialchars($product['image_path']) ?>" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                                 onerror="this.src='images/default-product.jpg'">
                                        <?php else: ?>
                                            <div class="h-10 w-10 rounded bg-gray-200 flex items-center justify-center">
                                                <i class="fa-solid fa-box-open text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($product['vehicle_type']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($product['brand_name'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($product['category_name'] ?? 'N/A') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ₹<?= number_format($product['price'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $product['quantity'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $product['quantity'] > 0 ? 'In Stock (' . $product['quantity'] . ')' : 'Out of Stock' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="text-primary hover:text-primary-dark mr-3">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    <form action="my_products.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" name="delete_product" class="text-red-600 hover:text-red-900">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <nav class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?= $start + 1 ?></span> to 
                        <span class="font-medium"><?= min($start + $per_page, $total_results) ?></span> of 
                        <span class="font-medium"><?= $total_results ?></span> results
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?>" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium <?= $page == $i ? 'bg-primary text-white border-primary' : 'text-gray-700 hover:bg-gray-50' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Next
                            </a>
                        <?php endif; ?>
                    </div>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>