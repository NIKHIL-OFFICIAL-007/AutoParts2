<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';
require_once '../../sellerpanel/includes/path_helper.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product data
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['error'] = 'Product not found';
        header('Location: manage_products.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch categories and brands for dropdowns
try {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
    $brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$errors = [];
$formData = [
    'name' => $product['name'],
    'description' => $product['description'],
    'price' => $product['price'],
    'brand_id' => $product['brand_id'],
    'vehicle_type' => $product['vehicle_type'],
    'compatible_models' => $product['compatible_models'],
    'availability' => $product['availability'],
    'category_id' => $product['category_id'],
    'warranty' => $product['warranty'],
    'delivery_time' => $product['delivery_time'],
    'quantity' => $product['quantity']
];

// Initialize image_path variable
$image_path = $product['image_path'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'price' => (float)$_POST['price'],
        'brand_id' => (int)$_POST['brand_id'],
        'vehicle_type' => trim($_POST['vehicle_type']),
        'compatible_models' => trim($_POST['compatible_models']),
        'availability' => $_POST['availability'],
        'category_id' => (int)$_POST['category_id'],
        'warranty' => trim($_POST['warranty']),
        'delivery_time' => trim($_POST['delivery_time']),
        'quantity' => (int)$_POST['quantity']
    ];

    // Validation
    if (empty($formData['name'])) {
        $errors['name'] = 'Product name is required';
    }
    if (empty($formData['brand_id'])) {
        $errors['brand_id'] = 'Brand is required';
    }
    if (empty($formData['category_id'])) {
        $errors['category_id'] = 'Category is required';
    }
    if ($formData['price'] <= 0) {
        $errors['price'] = 'Price must be greater than 0';
    }

    // Handle file upload if needed
    if (!empty($_FILES['image']['name'])) {
        $new_image_path = saveUploadedImage($_FILES['image']);
        if ($new_image_path) {
            // Delete old image if it exists and is a local file
            if (!empty($product['image_path']) && strpos($product['image_path'], 'http') === false) {
                $old_path = getUploadDir() . basename($product['image_path']);
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
            $image_path = $new_image_path;
        } else {
            $errors['image'] = 'Error uploading new image';
        }
    } elseif (!empty($_POST['image_url'])) {
        $image_path = trim($_POST['image_url']);
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET 
                name = :name, 
                description = :description, 
                price = :price, 
                brand_id = :brand_id, 
                vehicle_type = :vehicle_type, 
                compatible_models = :compatible_models,
                image_path = :image_path,
                availability = :availability,
                category_id = :category_id,
                warranty = :warranty,
                delivery_time = :delivery_time,
                quantity = :quantity,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id");
            
            $params = array_merge($formData, [
                'image_path' => $image_path,
                'id' => $product_id
            ]);
            
            $stmt->execute($params);
            
            $_SESSION['success'] = 'Product updated successfully';
            header('Location: manage_products.php');
            exit;
        } catch (PDOException $e) {
            $errors['database'] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-100 px-4 py-3 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Edit Product</h2>
        </div>
        
        <form method="post" enctype="multipart/form-data" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php if (isset($errors['database'])): ?>
                <div class="md:col-span-2 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?= $errors['database'] ?>
                </div>
            <?php endif; ?>
            
            <!-- Basic Information Section -->
            <div class="md:col-span-2">
                <h3 class="text-md font-medium text-gray-800 mb-3">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($formData['name']) ?>"
                            class="w-full px-3 py-2 border <?= isset($errors['name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md shadow-sm">
                        <?php if (isset($errors['name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['name'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="brand_id" class="block text-sm font-medium text-gray-700 mb-1">Brand *</label>
                        <select id="brand_id" name="brand_id" class="w-full px-3 py-2 border <?= isset($errors['brand_id']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md shadow-sm">
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= $brand['id'] ?>" <?= $formData['brand_id'] == $brand['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($brand['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['brand_id'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['brand_id'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select id="category_id" name="category_id" class="w-full px-3 py-2 border <?= isset($errors['category_id']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md shadow-sm">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $formData['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['category_id'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['category_id'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type *</label>
                        <select id="vehicle_type" name="vehicle_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                            <option value="">Select Type</option>
                            <option value="Car" <?= $formData['vehicle_type'] === 'Car' ? 'selected' : '' ?>>Car</option>
                            <option value="Bike" <?= $formData['vehicle_type'] === 'Bike' ? 'selected' : '' ?>>Bike</option>
                            <option value="Truck" <?= $formData['vehicle_type'] === 'Truck' ? 'selected' : '' ?>>Truck</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"><?= htmlspecialchars($formData['description']) ?></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="compatible_models" class="block text-sm font-medium text-gray-700 mb-1">Compatible Models</label>
                        <input type="text" id="compatible_models" name="compatible_models" value="<?= htmlspecialchars($formData['compatible_models']) ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>
            </div>
            
            <!-- Pricing & Inventory Section -->
            <div class="md:col-span-2">
                <h3 class="text-md font-medium text-gray-800 mb-3">Pricing & Inventory</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                        <input type="number" step="0.01" min="0" id="price" name="price" value="<?= htmlspecialchars($formData['price']) ?>"
                            class="w-full px-3 py-2 border <?= isset($errors['price']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md shadow-sm">
                        <?php if (isset($errors['price'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['price'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" min="0" id="quantity" name="quantity" value="<?= htmlspecialchars($formData['quantity']) ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    
                    <div>
                        <label for="availability" class="block text-sm font-medium text-gray-700 mb-1">Availability</label>
                        <select id="availability" name="availability" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                            <option value="In Stock" <?= $formData['availability'] === 'In Stock' ? 'selected' : '' ?>>In Stock</option>
                            <option value="Out of Stock" <?= $formData['availability'] === 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                            <option value="Pre-order" <?= $formData['availability'] === 'Pre-order' ? 'selected' : '' ?>>Pre-order</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="delivery_time" class="block text-sm font-medium text-gray-700 mb-1">Delivery Time</label>
                        <input type="text" id="delivery_time" name="delivery_time" value="<?= htmlspecialchars($formData['delivery_time']) ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    
                    <div>
                        <label for="warranty" class="block text-sm font-medium text-gray-700 mb-1">Warranty</label>
                        <input type="text" id="warranty" name="warranty" value="<?= htmlspecialchars($formData['warranty']) ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>
            </div>
            
            <!-- Image Section with URL support -->
            <div class="md:col-span-2">
                <h3 class="text-md font-medium text-gray-800 mb-3">Product Image</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php if (!empty($image_path)): ?>
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-1">Current Image</p>
                            <img src="<?= getWebImagePath($image_path) ?>" 
                                alt="Product Image" 
                                class="h-40 object-contain border border-gray-300 rounded-md"
                                onerror="this.src='../images/default-product.jpg'">
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
                        <input type="file" id="image" name="image" accept="image/*"
                            class="w-full px-3 py-2 border <?= isset($errors['image']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md shadow-sm">
                        <?php if (isset($errors['image'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['image'] ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-500 mt-1">Max file size: 2MB (JPG, JPEG, PNG, GIF)</p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">OR Image URL</label>
                        <input type="url" id="image_url" name="image_url" 
                            class="w-full px-3 py-2 border <?= isset($errors['image_url']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md shadow-sm"
                            placeholder="https://example.com/image.jpg"
                            value="<?= (strpos($image_path, 'http') === 0) ? htmlspecialchars($image_path) : '' ?>">
                        <?php if (isset($errors['image_url'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?= $errors['image_url'] ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="md:col-span-2 flex justify-end space-x-4 pt-4">
                <a href="products/manage_products.php" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update Product
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>