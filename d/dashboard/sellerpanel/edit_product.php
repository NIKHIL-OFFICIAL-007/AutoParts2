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

// Fetch product details for editing
$query = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_products.php");
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Form submission handling
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $vehicle_type = mysqli_real_escape_string($conn, $_POST['vehicle_type']);
    $compatible_models = mysqli_real_escape_string($conn, $_POST['compatible_models']);
    $price = floatval($_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $availability = mysqli_real_escape_string($conn, $_POST['availability']);
    $warranty = mysqli_real_escape_string($conn, $_POST['warranty']);
    $seller_name = mysqli_real_escape_string($conn, $_POST['seller']);
    $delivery_time = mysqli_real_escape_string($conn, $_POST['delivery_time']);
    
    // Handle file upload if a new image is provided
    $image_path = $product['image_path'];
    
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check === false) {
            $error_message = "File is not an image.";
        } elseif ($_FILES["image"]["size"] > 5000000) { // 5MB limit
            $error_message = "Sorry, your file is too large.";
        } elseif(!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        } else {
            // Generate unique filename to prevent overwriting
            $new_filename = uniqid() . '.' . $imageFileType;
            $target_path = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_path)) {
                // Delete old image
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
                $image_path = $target_path;
            } else {
                $error_message = "Sorry, there was an error uploading your file.";
            }
        }
    }
    
    if (empty($error_message)) {
        // Update product in database
        $sql = "UPDATE products SET 
                name = ?, brand = ?, vehicle_type = ?, compatible_models = ?, 
                price = ?, category = ?, description = ?, image_path = ?, 
                availability = ?, warranty = ?, seller_name = ?, delivery_time = ?
                WHERE id = ? AND seller_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdsssssssii", $name, $brand, $vehicle_type, $compatible_models, 
                          $price, $category, $description, $image_path, $availability, 
                          $warranty, $seller_name, $delivery_time, $product_id, $seller_id);
        
        if ($stmt->execute()) {
            $success_message = "Product updated successfully!";
            // Refresh product data
            $product = [
                'name' => $name,
                'brand' => $brand,
                'vehicle_type' => $vehicle_type,
                'compatible_models' => $compatible_models,
                'price' => $price,
                'category' => $category,
                'description' => $description,
                'image_path' => $image_path,
                'availability' => $availability,
                'warranty' => $warranty,
                'seller_name' => $seller_name,
                'delivery_time' => $delivery_time
            ];
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Product - AutoParts Seller Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1abc9c',
            'primary-dark': '#16a085',
            secondary: '#3498db',
            dark: '#2c3e50',
            light: '#ecf0f1',
            gray: '#95a5a6',
            danger: '#e74c3c',
            success: '#2ecc71',
          },
          fontFamily: {
            inter: ['Inter', 'sans-serif'],
          },
          animation: {
            fadeIn: 'fadeIn 0.5s ease forwards',
          },
          keyframes: {
            fadeIn: {
              '0%': { opacity: '0', transform: 'translateY(20px)' },
              '100%': { opacity: '1', transform: 'translateY(0)' },
            }
          }
        }
      }
    }
  </script>
  <style type="text/tailwindcss">
    @layer utilities {
      .form-input:focus {
        border-color: #1abc9c;
        background-color: #fff;
        outline: none;
        box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.15);
      }
      .file-upload:hover {
        border-color: #1abc9c;
        background: rgba(26, 188, 156, 0.05);
      }
    }
  </style>
</head>
<body class="font-inter bg-gradient-to-br from-[#f5f7fa] to-[#e4efe9] min-h-screen flex text-gray-800 leading-relaxed">

<div class="seller-container flex w-full max-w-[1400px] mx-5 my-5 rounded-2xl overflow-hidden shadow-xl">
  <!-- Seller Sidebar -->
  <div class="sidebar w-64 bg-dark text-white py-6 transition-all duration-300">
    <div class="sidebar-header px-6 pb-5 border-b border-white/10 mb-5">
      <div class="logo flex items-center gap-2.5 text-2xl font-bold mb-7">
        <i class="fa-solid fa-car text-primary"></i>
        <span>AutoParts</span>
      </div>
      
      <div class="seller-info flex items-center gap-3">
        <div class="seller-avatar w-12 h-12 rounded-full bg-primary flex items-center justify-center text-xl font-semibold">
          <?php echo substr($_SESSION['full_name'], 0, 1); ?>
        </div>
        <div>
          <div class="seller-name font-semibold text-base"><?php echo $_SESSION['full_name']; ?></div>
          <div class="seller-status flex items-center gap-1 text-xs text-primary">
            <div class="status-dot w-2 h-2 rounded-full bg-primary"></div>
            <span>Online</span>
          </div>
        </div>
      </div>
    </div>
    
    <ul class="nav-menu list-none px-4">
      <li class="nav-item mb-1">
        <a href="dashboard.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-gauge w-6 text-center"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="#" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-plus w-6 text-center"></i>
          <span>Sell Parts</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="my_products.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 bg-white/10 text-white">
          <i class="fa-solid fa-box w-6 text-center"></i>
          <span>My Products</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="#" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-chart-line w-6 text-center"></i>
          <span>Analytics</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="#" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-money-bill w-6 text-center"></i>
          <span>Earnings</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="#" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-gear w-6 text-center"></i>
          <span>Settings</span>
        </a>
      </li>
      <li class="nav-item mb-1">
        <a href="logout.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
          <i class="fa-solid fa-right-from-bracket w-6 text-center"></i>
          <span>Logout</span>
        </a>
      </li>
    </ul>
  </div>
  
  <!-- Main Content -->
  <div class="main-content flex-1 bg-white p-7 overflow-y-auto">
    <div class="header flex justify-between items-center mb-7 pb-5 border-b border-black/5">
      <div class="page-title flex items-center gap-3 text-2xl font-bold text-dark">
        <i class="fa-solid fa-pen-to-square text-primary"></i>
        <h1>Edit Product</h1>
      </div>
      <div class="header-actions">
        <a href="product_details.php?id=<?php echo $product_id; ?>" class="btn bg-gray-200 text-dark px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
          <i class="fa-solid fa-arrow-left mr-2"></i> Back
        </a>
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
    
    <!-- Edit Product Form -->
    <div class="sell-container bg-white rounded-2xl shadow-lg overflow-hidden animate-fadeIn" style="animation-delay: 0.5s">
      <div class="form-header bg-gradient-to-r from-primary to-secondary text-white py-6 px-7">
        <h2 class="flex items-center gap-4 font-bold text-2xl">
          <i class="fa-solid fa-pen"></i> Edit Product: <?php echo htmlspecialchars($product['name']); ?>
        </h2>
      </div>
      
      <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data" class="sell-form grid grid-cols-1 md:grid-cols-2 gap-6 p-7">
        <!-- name (from JSON) matches product_name -->
        <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.1s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="product_name">
            <i class="fa-solid fa-box text-primary text-lg"></i>
            Product Name
          </label>
          <input type="text" id="product_name" name="name" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. Exide Car Battery" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        
        <!-- brand (from JSON) matches part_brand -->
        <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.2s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="part_brand">
            <i class="fa-solid fa-trademark text-primary text-lg"></i>
            Product Brand
          </label>
          <select id="part_brand" name="brand" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" required>
            <option value="">- Select Brand -</option>
            <option value="Exide" <?php echo ($product['brand'] === 'Exide') ? 'selected' : ''; ?>>Exide</option>
            <option value="Bosch" <?php echo ($product['brand'] === 'Bosch') ? 'selected' : ''; ?>>Bosch</option>
            <option value="Amaron" <?php echo ($product['brand'] === 'Amaron') ? 'selected' : ''; ?>>Amaron</option>
            <option value="Philips" <?php echo ($product['brand'] === 'Philips') ? 'selected' : ''; ?>>Philips</option>
            <option value="Mann" <?php echo ($product['brand'] === 'Mann') ? 'selected' : ''; ?>>Mann</option>
            <option value="Hella" <?php echo ($product['brand'] === 'Hella') ? 'selected' : ''; ?>>Hella</option>
          </select>
        </div>
        
        <!-- vehicle_type (from JSON) - added new field -->
        <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.3s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="vehicle_type">
            <i class="fa-solid fa-car text-primary text-lg"></i>
            Vehicle Type
          </label>
          <select id="vehicle_type" name="vehicle_type" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" required>
            <option value="">- Select Vehicle Type -</option>
            <option value="Car" <?php echo ($product['vehicle_type'] === 'Car') ? 'selected' : ''; ?>>Car</option>
            <option value="Bike" <?php echo ($product['vehicle_type'] === 'Bike') ? 'selected' : ''; ?>>Bike</option>
            <option value="Truck" <?php echo ($product['vehicle_type'] === 'Truck') ? 'selected' : ''; ?>>Truck</option>
            <option value="SUV" <?php echo ($product['vehicle_type'] === 'SUV') ? 'selected' : ''; ?>>SUV</option>
          </select>
        </div>
        
        <!-- compatible_models (from JSON) matches model -->
        <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.4s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="compatible_models">
            <i class="fa-solid fa-car-side text-primary text-lg"></i>
            Compatible Models (comma separated)
          </label>
          <input type="text" id="compatible_models" name="compatible_models" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. Maruti Swift, Hyundai i20, Honda Amaze" value="<?php echo htmlspecialchars($product['compatible_models']); ?>" required>
        </div>
        
        <!-- price (from JSON) -->
        <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.5s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="price">
            <i class="fa-solid fa-indian-rupee-sign text-primary text-lg"></i>
            Price (₹)
          </label>
          <input type="number" id="price" name="price" step="0.01" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 4250.00" min="1" value="<?php echo htmlspecialchars($product['price']); ?>" required>
        </div>
        
        <!-- category (from JSON) -->
        <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.6s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="category">
            <i class="fa-solid fa-list text-primary text-lg"></i>
            Category
          </label>
          <select id="category" name="category" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" required>
            <option value="">- Select Category -</option>
            <option value="Batteries" <?php echo ($product['category'] === 'Batteries') ? 'selected' : ''; ?>>Batteries</option>
            <option value="Engine" <?php echo ($product['category'] === 'Engine') ? 'selected' : ''; ?>>Engine Parts</option>
            <option value="Brakes" <?php echo ($product['category'] === 'Brakes') ? 'selected' : ''; ?>>Brakes</option>
            <option value="Electrical" <?php echo ($product['category'] === 'Electrical') ? 'selected' : ''; ?>>Electrical</option>
            <option value="Lighting" <?php echo ($product['category'] === 'Lighting') ? 'selected' : ''; ?>>Lighting</option>
            <option value="Suspension" <?php echo ($product['category'] === 'Suspension') ? 'selected' : ''; ?>>Suspension</option>
            <option value="Interior" <?php echo ($product['category'] === 'Interior') ? 'selected' : ''; ?>>Interior</option>
          </select>
        </div>
        
        <!-- description (from JSON) -->
        <div class="form-group col-span-full mb-0 opacity-0 translate-y-5" style="animation-delay: 0.7s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="description">
            <i class="fa-solid fa-file-lines text-primary text-lg"></i>
            Product Description
          </label>
          <textarea id="description" name="description" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15 min-h-[120px]" placeholder="Describe the part, its features, and condition..." required><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        
        <!-- image (from JSON) -->
        <div class="form-group col-span-full mb-0 opacity-0 translate-y-5" style="animation-delay: 0.8s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="part_image">
            <i class="fa-solid fa-image text-primary text-lg"></i>
            Product Images
          </label>
          <div class="file-upload relative flex flex-col items-center justify-center p-7 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 cursor-pointer transition-all duration-300">
            <?php if (!empty($product['image_path'])): ?>
              <img src="<?php echo $product['image_path']; ?>" alt="Current Product Image" class="h-24 w-auto mb-4">
              <div class="file-upload-text text-center mb-4">
                <h3 class="text-lg text-dark mb-1">Current Product Image</h3>
                <p class="text-sm text-gray">Upload a new image to replace the current one</p>
              </div>
            <?php else: ?>
              <i class="fa-solid fa-cloud-arrow-up text-primary text-4xl mb-4"></i>
              <div class="file-upload-text text-center mb-4">
                <h3 class="text-lg text-dark mb-1">Upload Product Images</h3>
                <p class="text-sm text-gray">Click to browse or drag & drop your images here</p>
                <p class="text-sm text-gray">PNG, JPG, JPEG up to 5MB</p>
              </div>
            <?php endif; ?>
            <input type="file" id="part_image" name="image" class="file-input absolute w-full h-full top-0 left-0 opacity-0 cursor-pointer" accept="image/*">
          </div>
        </div>
        
        <!-- availability (from JSON) - added new field -->
        <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.9s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="availability">
            <i class="fa-solid fa-box-open text-primary text-lg"></i>
            Availability
          </label>
          <select id="availability" name="availability" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" required>
            <option value="">- Select Availability -</option>
            <option value="In Stock" <?php echo ($product['availability'] === 'In Stock') ? 'selected' : ''; ?>>In Stock</option>
            <option value="Out of Stock" <?php echo ($product['availability'] === 'Out of Stock') ? 'selected' : ''; ?>>Out of Stock</option>
            <option value="Pre-order" <?php echo ($product['availability'] === 'Pre-order') ? 'selected' : ''; ?>>Pre-order</option>
          </select>
        </div>
        
        <!-- warranty (from JSON) - added new field -->
        <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 1.0s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="warranty">
            <i class="fa-solid fa-shield-alt text-primary text-lg"></i>
            Warranty
          </label>
          <input type="text" id="warranty" name="warranty" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 24 months" value="<?php echo htmlspecialchars($product['warranty']); ?>" required>
        </div>
        
        <!-- seller (from JSON) matches seller_name -->
        <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 1.1s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="seller_name">
            <i class="fa-solid fa-store text-primary text-lg"></i>
            Seller Name
          </label>
          <input type="text" id="seller_name" name="seller" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. AutoZone India" value="<?php echo htmlspecialchars($product['seller_name']); ?>" required>
        </div>
        
        <!-- delivery_time (from JSON) - added new field -->
        <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 1.2s">
          <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="delivery_time">
            <i class="fa-solid fa-truck text-primary text-lg"></i>
            Delivery Time
          </label>
          <input type="text" id="delivery_time" name="delivery_time" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 2-4 Days" value="<?php echo htmlspecialchars($product['delivery_time']); ?>" required>
        </div>
        
        <button type="submit" class="submit-btn col-span-full bg-gradient-to-r from-primary to-secondary text-white border-none py-4 rounded-xl text-base font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-0.5 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40 active:translate-y-0 flex items-center justify-center gap-2.5">
          <i class="fa-solid fa-save"></i>
          Update Product
        </button>
      </form>
    </div>
  </div>
</div>

<script>
  // Simple animation for form elements
  document.addEventListener('DOMContentLoaded', function() {
    const formGroups = document.querySelectorAll('.form-group');
    
    formGroups.forEach((group, index) => {
      group.style.animationDelay = `${index * 0.1 + 0.1}s`;
      group.classList.add('animate-fadeIn');
    });
    
    // File upload hover effect
    const fileUpload = document.querySelector('.file-upload');
    const fileInput = document.querySelector('.file-input');
    
    fileInput.addEventListener('change', function() {
      if(this.files.length > 0) {
        fileUpload.innerHTML = `
          <i class="fa-solid fa-check-circle text-success text-4xl"></i>
          <div class="file-upload-text text-center">
            <h3 class="text-lg text-dark">${this.files.length} file(s) selected</h3>
            <p class="text-sm text-gray">${this.files[0].name}</p>
          </div>
        `;
      }
    });
  });
</script>

</body>
</html>