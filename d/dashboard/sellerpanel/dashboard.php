<?php
// Start output buffering at the very beginning
ob_start();

$page_title = "Sell a Part - AutoParts Seller Portal";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'includes/db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success_message = '';
$error_message = '';

// Initialize form fields
$formData = [
    'name' => '',
    'description' => '',
    'price' => 0,
    'brand_id' => '',
    'vehicle_type' => '',
    'compatible_models' => '',
    'image_path' => '',
    'availability' => 'In Stock',
    'category_id' => '',
    'warranty' => '',
    'delivery_time' => '2-4 Days',
    'quantity' => 1
];

// Fetch brands and categories for dropdowns
try {
    $brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll();
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

/* ===========================
   HANDLE FORM SUBMISSION
   ===========================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $formData = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'brand_id' => (int)($_POST['brand_id'] ?? 0),
            'vehicle_type' => trim($_POST['vehicle_type'] ?? ''),
            'compatible_models' => trim($_POST['compatible_models'] ?? ''),
            'availability' => $_POST['availability'] ?? 'In Stock',
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'warranty' => trim($_POST['warranty'] ?? ''),
            'delivery_time' => trim($_POST['delivery_time'] ?? '2-4 Days'),
            'quantity' => (int)($_POST['quantity'] ?? 1),
            'seller_id' => $_SESSION['user_id']
        ];

        // Validate required fields
        $requiredFields = ['name', 'price', 'brand_id', 'vehicle_type', 'category_id'];
        foreach ($requiredFields as $field) {
            if (empty($formData[$field])) {
                throw new Exception(ucfirst($field) . " is required");
            }
        }

/* =========================
   FILE UPLOAD HANDLING - FIXED VERSION
   =========================*/
// Handle file upload
$imagePath = '';
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    // Use absolute paths for better reliability
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/d/uploads/products/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $fileName;
    
    // Validate file
    $imageFileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($imageFileType, $allowedTypes)) {
        throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed");
    }
    
    if ($_FILES['image']['size'] > 2000000) {
        throw new Exception("File size must be less than 2MB");
    }
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        // Store web-accessible path (relative to document root)
        $imagePath = '/d/uploads/products/' . $fileName;
    } else {
        // Get detailed error message
        $errorCode = $_FILES['image']['error'];
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
        ];
        
        $errorMsg = $errorMessages[$errorCode] ?? 'Unknown upload error';
        throw new Exception("Error uploading file: $errorMsg");
    }
} elseif (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    // Handle specific file upload errors
    $errorCode = $_FILES['image']['error'];
    throw new Exception("File upload error (code $errorCode)");
} elseif (!empty($_POST['image_url'])) {
    $imagePath = trim($_POST['image_url']);
}
    

        /* ===========================
           INSERT INTO DATABASE
           ===========================*/
        $sql = "INSERT INTO products (
            seller_id, name, brand_id, vehicle_type, compatible_models, price,
            category_id, description, image_path, availability, warranty, 
            delivery_time, quantity
        ) VALUES (
            :seller_id, :name, :brand_id, :vehicle_type, :compatible_models, :price,
            :category_id, :description, :image_path, :availability, :warranty,
            :delivery_time, :quantity
        )";
        
        $stmt = $pdo->prepare($sql);
        $params = array_merge($formData, ['image_path' => $imagePath]);
        
        if ($stmt->execute($params)) {
            $success_message = "Product added successfully!";
            // Reset form
            $formData = [
                'name' => '',
                'description' => '',
                'price' => 0,
                'brand_id' => '',
                'vehicle_type' => '',
                'compatible_models' => '',
                'image_path' => '',
                'availability' => 'In Stock',
                'category_id' => '',
                'warranty' => '',
                'delivery_time' => '2-4 Days',
                'quantity' => 1
            ];
        } else {
            throw new Exception("Database error: Could not save product");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

/* ====================================
   FETCH SELLER STATS FOR DASHBOARD
   ====================================*/
try {
    $seller_id = $_SESSION['user_id'];
    
    // Products count
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM products WHERE seller_id = ?");
    $stmt->execute([$seller_id]);
    $result = $stmt->fetch();
    $products_count = $result['count'] ?? 0;
    
    // Earnings
    $stmt = $pdo->prepare("SELECT SUM(price * quantity) AS total FROM products WHERE seller_id = ?");
    $stmt->execute([$seller_id]);
    $result = $stmt->fetch();
    $earnings = $result['total'] ? $result['total'] : 0;
} catch (PDOException $e) {
    $error_message = "Error fetching stats: " . $e->getMessage();
}
?>


<div class="main-content flex-1 bg-white p-7">
    <div class="header flex justify-between items-center mb-7 pb-5 border-b border-black/5">
        <div class="page-title flex items-center gap-3 text-2xl font-bold text-dark">
            <i class="fa-solid fa-car-wrench text-primary"></i>
            <h1>Sell Vehicle Parts</h1>
        </div>
        <div class="header-actions">
            <button class="btn">
                <i class="fa-solid fa-bell"></i>
            </button>
        </div>
    </div>
    
    <!-- Success/Error Messages -->
    <?php if ($success_message): ?>
    <div class="alert alert-success mb-5 p-4 bg-success/10 text-success border border-success/20 rounded-lg animate-fadeIn">
        <i class="fa-solid fa-circle-check mr-2"></i> <?php echo $success_message; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div class="alert alert-error mb-5 p-4 bg-danger/10 text-danger border border-danger/20 rounded-lg animate-fadeIn">
        <i class="fa-solid fa-circle-exclamation mr-2"></i> <?php echo $error_message; ?>
    </div>
    <?php endif; ?>
    
    <div class="stats-container grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-7">
        <div class="stat-card bg-white rounded-xl p-5 shadow-md border-l-4 border-primary transition-transform duration-300 hover:-translate-y-1 animate-fadeIn" style="animation-delay: 0.1s">
            <div class="stat-title text-sm text-gray mb-2">Active Listings</div>
            <div class="stat-value text-2xl font-bold text-dark"><?php echo $products_count; ?></div>
            <div class="stat-change flex items-center gap-1 text-xs text-success mt-1">
                <i class="fa-solid fa-arrow-up"></i>
                <span>12% from last month</span>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl p-5 shadow-md border-l-4 border-primary transition-transform duration-300 hover:-translate-y-1 animate-fadeIn" style="animation-delay: 0.2s">
            <div class="stat-title text-sm text-gray mb-2">Total Earnings</div>
            <div class="stat-value text-2xl font-bold text-dark">₹<?php echo number_format($earnings, 2); ?></div>
            <div class="stat-change flex items-center gap-1 text-xs text-success mt-1">
                <i class="fa-solid fa-arrow-up"></i>
                <span>₹12,450 this month</span>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl p-5 shadow-md border-l-4 border-primary transition-transform duration-300 hover:-translate-y-1 animate-fadeIn" style="animation-delay: 0.3s">
            <div class="stat-title text-sm text-gray mb-2">Conversion Rate</div>
            <div class="stat-value text-2xl font-bold text-dark">42%</div>
            <div class="stat-change flex items-center gap-1 text-xs text-danger mt-1">
                <i class="fa-solid fa-arrow-down"></i>
                <span>3% from last month</span>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl p-5 shadow-md border-l-4 border-primary transition-transform duration-300 hover:-translate-y-1 animate-fadeIn" style="animation-delay: 0.4s">
            <div class="stat-title text-sm text-gray mb-2">Customer Rating</div>
            <div class="stat-value text-2xl font-bold text-dark">4.8/5</div>
            <div class="stat-change flex items-center gap-1 text-xs text-success mt-1">
                <i class="fa-solid fa-star"></i>
                <span>98% positive</span>
            </div>
        </div>
    </div>

    <!-- Add Product Form -->
    <div class="sell-container bg-white rounded-2xl shadow-lg overflow-hidden animate-fadeIn" style="animation-delay: 0.5s">
        <div class="form-header bg-gradient-to-r from-primary to-secondary text-white py-6 px-7">
            <h2 class="flex items-center gap-4 font-bold text-2xl">
                <i class="fa-solid fa-circle-plus"></i> Add New Product
            </h2>
        </div>
        
        <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="sell-form grid grid-cols-1 md:grid-cols-2 gap-6 p-7" id="productForm">
            <!-- Product Name -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.1s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="product_name">
                    <i class="fa-solid fa-box text-primary text-lg"></i>
                    Product Name
                </label>
                <input type="text" id="product_name" name="name" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. Exide Car Battery" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
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
                        <option value="<?php echo $brand['id']; ?>" <?php echo (isset($_POST['brand_id']) && $_POST['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand['name']); ?>
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
                    <option value="Car" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'Car') ? 'selected' : ''; ?>>Car</option>
                    <option value="Bike" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'Bike') ? 'selected' : ''; ?>>Bike</option>
                    <option value="Truck" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'Truck') ? 'selected' : ''; ?>>Truck</option>
                    <option value="SUV" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'SUV') ? 'selected' : ''; ?>>SUV</option>
                </select>
            </div>
            
            <!-- Compatible Models -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.4s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="compatible_models">
                    <i class="fa-solid fa-car-side text-primary text-lg"></i>
                    Compatible Models (comma separated)
                </label>
                <input type="text" id="compatible_models" name="compatible_models" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. Maruti Swift, Hyundai i20, Honda Amaze" value="<?php echo isset($_POST['compatible_models']) ? htmlspecialchars($_POST['compatible_models']) : ''; ?>" required>
            </div>
            
            <!-- Price -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.5s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="price">
                    <i class="fa-solid fa-indian-rupee-sign text-primary text-lg"></i>
                    Price (₹)
                </label>
                <input type="number" id="price" name="price" step="0.01" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 4250.00" min="1" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
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
                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
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
                <input type="number" id="quantity" name="quantity" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 10" min="1" value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '1'; ?>" required>
            </div>
            
            <!-- Description -->
            <div class="form-group col-span-full mb-0 opacity-0 translate-y-5" style="animation-delay: 0.8s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="description">
                    <i class="fa-solid fa-file-lines text-primary text-lg"></i>
                    Product Description
                </label>
                <textarea id="description" name="description" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15 min-h-[120px]" placeholder="Describe the part, its features, and condition..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>
            
<!-- Replace your current image display with this: -->
<div class="md:col-span-2">
    <h3 class="text-md font-medium text-gray-800 mb-3">Product Image</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if (!empty($formData['image_path'])): ?>
            <div>
                <p class="text-sm font-medium text-gray-700 mb-1">Current Image</p>
                <img src="<?= htmlspecialchars($formData['image_path']) ?>" 
                    alt="Product Image" 
                    class="h-40 object-contain border border-gray-300 rounded-md"
                    onerror="this.src='/d/images/default-product.jpg'">
            </div>
        <?php endif; ?>
        
        <div>
            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Upload New Image</label>
            <input type="file" id="image" name="image" accept="image/*"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
            <p class="text-xs text-gray-500 mt-1">Max file size: 2MB (JPG, JPEG, PNG, GIF)</p>
        </div>
        
        <div class="md:col-span-2">
            <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">OR Image URL</label>
            <input type="url" id="image_url" name="image_url" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                placeholder="https://example.com/image.jpg"
                value="<?= isset($formData['image_url']) ? htmlspecialchars($formData['image_url']) : '' ?>">
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
                    <option value="In Stock" <?php echo (isset($_POST['availability']) && $_POST['availability'] === 'In Stock') ? 'selected' : ''; ?>>In Stock</option>
                    <option value="Out of Stock" <?php echo (isset($_POST['availability']) && $_POST['availability'] === 'Out of Stock') ? 'selected' : ''; ?>>Out of Stock</option>
                    <option value="Pre-order" <?php echo (isset($_POST['availability']) && $_POST['availability'] === 'Pre-order') ? 'selected' : ''; ?>>Pre-order</option>
                </select>
            </div>
            
            <!-- Warranty -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 1.1s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="warranty">
                    <i class="fa-solid fa-shield-alt text-primary text-lg"></i>
                    Warranty
                </label>
                <input type="text" id="warranty" name="warranty" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 24 months" value="<?php echo isset($_POST['warranty']) ? htmlspecialchars($_POST['warranty']) : ''; ?>" required>
            </div>
            
            <!-- Delivery Time -->
            <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 1.2s">
                <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="delivery_time">
                    <i class="fa-solid fa-truck text-primary text-lg"></i>
                    Delivery Time
                </label>
                <input type="text" id="delivery_time" name="delivery_time" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 2-4 Days" value="<?php echo isset($_POST['delivery_time']) ? htmlspecialchars($_POST['delivery_time']) : ''; ?>" required>
            </div>
            
            <button type="submit" class="submit-btn col-span-full bg-gradient-to-r from-primary to-secondary text-white border-none py-4 rounded-xl text-base font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-0.5 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40 active:translate-y-0 flex items-center justify-center gap-2.5">
                <i class="fa-solid fa-paper-plane"></i>
                Post Product
            </button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>