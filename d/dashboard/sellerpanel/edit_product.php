<?php
// Start output buffering at the very beginning
ob_start();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'includes/db.php';
require_once 'includes/path_helper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header('Location: login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];
$page_title = "Edit Product - AutoParts Seller Portal";

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$stmt = $pdo->prepare("
    SELECT p.*, b.name as brand_name, c.name as category_name 
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.seller_id = ?
");
$stmt->execute([$product_id, $seller_id]);
$product = $stmt->fetch();

// If product doesn't exist or doesn't belong to seller
if (!$product) {
    ob_end_clean();
    $_SESSION['error_message'] = "Product not found or you don't have permission to edit it";
    header('Location: my_products.php');
    exit();
}

// Fetch brands and categories for dropdowns
$brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $brand_id = (int)$_POST['brand_id'];
        $vehicle_type = trim($_POST['vehicle_type']);
        $compatible_models = trim($_POST['compatible_models']);
        $category_id = (int)$_POST['category_id'];
        $quantity = (int)$_POST['quantity'];
        $warranty = trim($_POST['warranty'] ?? '');
        $delivery_time = trim($_POST['delivery_time'] ?? '');
        $availability = trim($_POST['availability'] ?? 'In Stock');
        
        // Preserve current image path by default
        $image_path = $product['image_path'];

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $new_image_path = saveUploadedImage($_FILES['image']);
            if (!$new_image_path) {
                throw new Exception("Error uploading file");
            }
            // Delete old image if it exists and is a local file
            if (!empty($product['image_path']) && strpos($product['image_path'], 'http') === false) {
                $old_path = getUploadDir() . basename($product['image_path']);
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
            $image_path = $new_image_path;
        } elseif (!empty($_POST['image_url'])) {
            // Validate URL
            if (!filter_var($_POST['image_url'], FILTER_VALIDATE_URL)) {
                throw new Exception("Please provide a valid image URL");
            }
            $image_path = $_POST['image_url'];
        }

        // Update product in database
        $stmt = $pdo->prepare("
            UPDATE products SET
                name = ?,
                description = ?,
                price = ?,
                brand_id = ?,
                vehicle_type = ?,
                compatible_models = ?,
                image_path = ?,
                category_id = ?,
                quantity = ?,
                warranty = ?,
                delivery_time = ?,
                availability = ?
            WHERE id = ? AND seller_id = ?
        ");
        
        $stmt->execute([
            $name, $description, $price, $brand_id, $vehicle_type,
            $compatible_models, $image_path, $category_id,
            $quantity, $warranty, $delivery_time, $availability,
            $product_id, $seller_id
        ]);

        ob_end_clean();
        $_SESSION['success_message'] = "Product updated successfully!";
        header('Location: my_products.php');
        exit();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<div class="main-content flex-1 bg-white p-7">
    <div class="header flex justify-between items-center mb-7 pb-5 border-b border-black/5">
        <div class="page-title flex items-center gap-3 text-2xl font-bold text-dark">
            <i class="fa-solid fa-pen-to-square text-primary"></i>
            <h1>Edit Product</h1>
        </div>
        <div class="header-actions">
            <a href="my_products.php" class="btn bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md transition duration-300">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Products
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success mb-5 p-4 bg-success/10 text-success border border-success/20 rounded-lg animate-fadeIn">
            <i class="fa-solid fa-circle-check mr-2"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error mb-5 p-4 bg-danger/10 text-danger border border-danger/20 rounded-lg animate-fadeIn">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <!-- Edit Product Form -->
    <div class="edit-container bg-white rounded-2xl shadow-lg overflow-hidden animate-fadeIn">
        <div class="form-header bg-gradient-to-r from-primary to-secondary text-white py-6 px-7">
            <h2 class="flex items-center gap-4 font-bold text-2xl">
                <i class="fa-solid fa-pen-to-square"></i> Edit Product
            </h2>
        </div>
        
        <form action="edit_product.php?id=<?= $product_id ?>" method="POST" enctype="multipart/form-data" class="edit-form grid grid-cols-1 md:grid-cols-2 gap-6 p-7" id="productForm">
            <!-- Product Name -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.1s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="product_name">
                    <i class="fa-solid fa-box text-primary text-lg"></i>
                    Product Name
                </label>
                <input type="text" id="product_name" name="name" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. Exide Car Battery" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            
            <!-- Product Brand -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.2s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="brand_id">
                    <i class="fa-solid fa-trademark text-primary text-lg"></i>
                    Product Brand
                </label>
                <select id="brand_id" name="brand_id" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" required>
                    <option value="">- Select Brand -</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?= $brand['id'] ?>" <?= $brand['id'] == $product['brand_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($brand['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Vehicle Type -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.3s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="vehicle_type">
                    <i class="fa-solid fa-car text-primary text-lg"></i>
                    Vehicle Type
                </label>
                <select id="vehicle_type" name="vehicle_type" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" required>
                    <option value="">- Select Vehicle Type -</option>
                    <option value="Car" <?= $product['vehicle_type'] == 'Car' ? 'selected' : '' ?>>Car</option>
                    <option value="Bike" <?= $product['vehicle_type'] == 'Bike' ? 'selected' : '' ?>>Bike</option>
                    <option value="Truck" <?= $product['vehicle_type'] == 'Truck' ? 'selected' : '' ?>>Truck</option>
                    <option value="SUV" <?= $product['vehicle_type'] == 'SUV' ? 'selected' : '' ?>>SUV</option>
                </select>
            </div>
            
            <!-- Compatible Models -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.4s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="compatible_models">
                    <i class="fa-solid fa-car-side text-primary text-lg"></i>
                    Compatible Models (comma separated)
                </label>
                <input type="text" id="compatible_models" name="compatible_models" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. Maruti Swift, Hyundai i20, Honda Amaze" value="<?= htmlspecialchars($product['compatible_models']) ?>" required>
            </div>
            
            <!-- Price -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.5s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="price">
                    <i class="fa-solid fa-indian-rupee-sign text-primary text-lg"></i>
                    Price (₹)
                </label>
                <input type="number" id="price" name="price" step="0.01" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 4250.00" min="1" value="<?= htmlspecialchars($product['price']) ?>" required>
            </div>
            
            <!-- Category -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.6s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="category_id">
                    <i class="fa-solid fa-list text-primary text-lg"></i>
                    Category
                </label>
                <select id="category_id" name="category_id" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" required>
                    <option value="">- Select Category -</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Quantity -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.7s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="quantity">
                    <i class="fa-solid fa-boxes-stacked text-primary text-lg"></i>
                    Quantity
                </label>
                <input type="number" id="quantity" name="quantity" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 10" min="1" value="<?= htmlspecialchars($product['quantity']) ?>" required>
            </div>
            
            <!-- Description -->
            <div class="form-group col-span-full mb-0 opacity-0 translate-y-5" style="animation-delay: 0.8s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="description">
                    <i class="fa-solid fa-file-lines text-primary text-lg"></i>
                    Product Description
                </label>
                <textarea id="description" name="description" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15 min-h-[120px]" placeholder="Describe the part, its features, and condition..." required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            
            <!-- Product Image -->
            <div class="form-group col-span-full">
                <label class="form-label block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa-solid fa-image text-primary mr-2"></i>Product Image
                </label>
                <div class="space-y-4">
                    <?php if (!empty($product['image_path'])): ?>
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Current Image</p>
                            <div class="flex items-center justify-center p-2 border border-gray-300 rounded-md">
                                <img src="<?= getWebImagePath($product['image_path']) ?>" 
                                     alt="Product Image" 
                                     class="h-40 object-contain"
                                     onerror="this.src='/d/images/default-product.jpg'">
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                        <input type="file" id="image" name="image" accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                        <p class="text-xs text-gray-500 mt-1">Max file size: 2MB (JPG, JPEG, PNG, GIF)</p>
                    </div>
                    
                    <div>
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">OR Image URL</label>
                        <input type="url" id="image_url" name="image_url" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                            placeholder="https://example.com/image.jpg"
                            value="<?= isset($product['image_path']) && strpos($product['image_path'], 'http') === 0 ? htmlspecialchars($product['image_path']) : '' ?>">
                    </div>
                </div>
            </div>
            
            <!-- Availability -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 1.0s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="availability">
                    <i class="fa-solid fa-box-open text-primary text-lg"></i>
                    Availability
                </label>
                <select id="availability" name="availability" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" required>
                    <option value="">- Select Availability -</option>
                    <option value="In Stock" <?= $product['availability'] == 'In Stock' ? 'selected' : '' ?>>In Stock</option>
                    <option value="Out of Stock" <?= $product['availability'] == 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                    <option value="Pre-order" <?= $product['availability'] == 'Pre-order' ? 'selected' : '' ?>>Pre-order</option>
                </select>
            </div>
            
            <!-- Warranty -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 1.1s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="warranty">
                    <i class="fa-solid fa-shield-alt text-primary text-lg"></i>
                    Warranty
                </label>
                <input type="text" id="warranty" name="warranty" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 24 months" value="<?= htmlspecialchars($product['warranty']) ?>" required>
            </div>
            
            <!-- Delivery Time -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 1.2s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="delivery_time">
                    <i class="fa-solid fa-truck text-primary text-lg"></i>
                    Delivery Time
                </label>
                <input type="text" id="delivery_time" name="delivery_time" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 2-4 Days" value="<?= htmlspecialchars($product['delivery_time']) ?>" required>
            </div>
            
            <button type="submit" class="submit-btn col-span-full bg-gradient-to-r from-primary to-secondary text-white border-none py-4 rounded-xl text-base font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-0.5 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40 active:translate-y-0 flex items-center justify-center gap-2.5">
                <i class="fa-solid fa-save mr-2"></i> Update Product
            </button>
        </form>
    </div>
</div>

<?php 
require_once 'includes/footer.php';
ob_end_flush(); // Send the output buffer and turn off output buffering
?>