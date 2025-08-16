<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit;
}

require_once 'db.php';

$success_message = '';
$error_message   = '';

/* ===========================
   HANDLE FORM SUBMISSION
   ===========================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate and sanitize the inputs
    $name              = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $brand             = mysqli_real_escape_string($conn, $_POST['brand'] ?? '');
    $vehicle_type      = mysqli_real_escape_string($conn, $_POST['vehicle_type'] ?? '');
    $compatible_models = mysqli_real_escape_string($conn, $_POST['compatible_models'] ?? '');
    $price             = floatval($_POST['price'] ?? 0);
    $category          = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
    $description       = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $availability      = mysqli_real_escape_string($conn, $_POST['availability'] ?? '');
    $warranty          = mysqli_real_escape_string($conn, $_POST['warranty'] ?? '');
    $seller_name       = mysqli_real_escape_string($conn, $_POST['seller'] ?? '');
    $delivery_time     = mysqli_real_escape_string($conn, $_POST['delivery_time'] ?? '');
    $seller_id         = $_SESSION['user_id'];

    /* =========================
       FILE UPLOAD
       =========================*/
    $upload_dir   = __DIR__ . '/uploads';
    $max_size     = 5 * 1024 * 1024; // 5MB
    $allowed_types = ['image/jpeg','image/png','image/gif'];
    $image_path   = '';

    // Make sure folder exists
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir,0755,true);
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $error_message = "Please select an image file";

    } else {

        $file_error = $_FILES['image']['error'];
        $upload_errors = [
           UPLOAD_ERR_OK => 'No error',
           UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
           UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
           UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
           UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
           UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
           UPLOAD_ERR_EXTENSION => 'PHP extension stopped the upload'
        ];

        if ($file_error !== UPLOAD_ERR_OK) {
            $error_message = $upload_errors[$file_error] ?? 'Unknown upload error';

        } else {
            $tmp_name = $_FILES['image']['tmp_name'];
            $detected_type = mime_content_type($tmp_name);

            if (!in_array($detected_type, $allowed_types)) {
                $error_message = "Only JPG, PNG and GIF files are allowed";

            } elseif ($_FILES['image']['size'] > $max_size) {
                $error_message = "File size exceeds 5MB";

            } else {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('img_',true) . '.' . strtolower($ext);
                $target_path = $upload_dir . '/' . $filename;

                if (move_uploaded_file($tmp_name,$target_path)) {
                    $image_path = $target_path;
                } else {
                    $error_message = "Failed to move uploaded file";
                }
            }
        }
    }

    /* ===========================
       INSERT INTO DATABASE
       ===========================*/
    if (empty($error_message)) {
        $sql  = "INSERT INTO products 
                 (seller_id,name,brand,vehicle_type,compatible_models,price,
                  category,description,image_path,availability,warranty,seller_name,delivery_time)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issssdsssssss",
            $seller_id,$name,$brand,$vehicle_type,$compatible_models,
            $price,$category,$description,$image_path,$availability,
            $warranty,$seller_name,$delivery_time
        );

        if ($stmt->execute()) {
            $success_message = "Product added successfully!";
            $_POST = array(); // clear form
        } else {
            $error_message = "Error: ".$stmt->error;
        }
        $stmt->close();
    }
}

/* ====================================
   FETCH SELLER STATS FOR DASHBOARD
   ====================================*/
$seller_id      = $_SESSION['user_id'];
$products_count = 0;
$earnings       = 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM products WHERE seller_id = ?");
$stmt->bind_param("i",$seller_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $products_count = $row['count'];
}
$stmt->close();

$stmt = $conn->prepare("SELECT SUM(price) AS total FROM products WHERE seller_id = ?");
$stmt->bind_param("i",$seller_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $earnings = $row['total'] ? $row['total'] : 0;
}
$stmt->close();

// Calculate earnings (this would normally come from orders table)
$earnings_query = "SELECT SUM(price) as total FROM products WHERE seller_id = ?";
$stmt = $conn->prepare($earnings_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $earnings = $row['total'] ? $row['total'] : 0;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sell a Part - AutoParts Seller Portal</title>
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
        <a href="dashboard.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 bg-white/10 text-white">
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
        <a href="my_products.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 hover:bg-white/10 hover:text-white">
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
  
  <form action="dashboard.php" method="POST" enctype="multipart/form-data"
        class="sell-form grid grid-cols-1 md:grid-cols-2 gap-6 p-7"
        id="productForm">
      
        <!-- name (from JSON) matches product_name -->
    <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.1s">
      <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="product_name">
        <i class="fa-solid fa-box text-primary text-lg"></i>
        Product Name
      </label>
      <input type="text" id="product_name" name="name" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. Exide Car Battery" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
    </div>
    
    <!-- brand (from JSON) matches part_brand -->
    <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.2s">
      <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="part_brand">
        <i class="fa-solid fa-trademark text-primary text-lg"></i>
        Product Brand
      </label>
      <select id="part_brand" name="brand" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" required>
        <option value="">- Select Brand -</option>
        <option value="Exide" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Exide') ? 'selected' : ''; ?>>Exide</option>
        <option value="Bosch" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Bosch') ? 'selected' : ''; ?>>Bosch</option>
        <option value="Amaron" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Amaron') ? 'selected' : ''; ?>>Amaron</option>
        <option value="Philips" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Philips') ? 'selected' : ''; ?>>Philips</option>
        <option value="Mann" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Mann') ? 'selected' : ''; ?>>Mann</option>
        <option value="Hella" <?php echo (isset($_POST['brand']) && $_POST['brand'] === 'Hella') ? 'selected' : ''; ?>>Hella</option>
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
        <option value="Car" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'Car') ? 'selected' : ''; ?>>Car</option>
        <option value="Bike" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'Bike') ? 'selected' : ''; ?>>Bike</option>
        <option value="Truck" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'Truck') ? 'selected' : ''; ?>>Truck</option>
        <option value="SUV" <?php echo (isset($_POST['vehicle_type']) && $_POST['vehicle_type'] === 'SUV') ? 'selected' : ''; ?>>SUV</option>
      </select>
    </div>
    
    <!-- compatible_models (from JSON) matches model -->
    <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.4s">
      <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="compatible_models">
        <i class="fa-solid fa-car-side text-primary text-lg"></i>
        Compatible Models (comma separated)
      </label>
      <input type="text" id="compatible_models" name="compatible_models" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. Maruti Swift, Hyundai i20, Honda Amaze" value="<?php echo isset($_POST['compatible_models']) ? htmlspecialchars($_POST['compatible_models']) : ''; ?>" required>
    </div>
    
    <!-- price (from JSON) -->
    <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.5s">
      <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="price">
        <i class="fa-solid fa-indian-rupee-sign text-primary text-lg"></i>
        Price (₹)
      </label>
      <input type="number" id="price" name="price" step="0.01" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 4250.00" min="1" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
    </div>
    
    <!-- category (from JSON) -->
    <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 0.6s">
      <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="category">
        <i class="fa-solid fa-list text-primary text-lg"></i>
        Category
      </label>
      <select id="category" name="category" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" required>
        <option value="">- Select Category -</option>
        <option value="Batteries" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Batteries') ? 'selected' : ''; ?>>Batteries</option>
        <option value="Engine" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Engine') ? 'selected' : ''; ?>>Engine Parts</option>
        <option value="Brakes" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Brakes') ? 'selected' : ''; ?>>Brakes</option>
        <option value="Electrical" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Electrical') ? 'selected' : ''; ?>>Electrical</option>
        <option value="Lighting" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Lighting') ? 'selected' : ''; ?>>Lighting</option>
        <option value="Suspension" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Suspension') ? 'selected' : ''; ?>>Suspension</option>
        <option value="Interior" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Interior') ? 'selected' : ''; ?>>Interior</option>
      </select>
    </div>
    
    <!-- description (from JSON) -->
    <div class="form-group col-span-full mb-0 opacity-0 translate-y-5" style="animation-delay: 0.7s">
      <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="description">
        <i class="fa-solid fa-file-lines text-primary text-lg"></i>
        Product Description
      </label>
      <textarea id="description" name="description" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15 min-h-[120px]" placeholder="Describe the part, its features, and condition..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
    </div>
    
    <!-- image (from JSON) -->
    <div class="form-group col-span-full mb-0 opacity-0 translate-y-5" style="animation-delay: 0.8s">
      <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="part_image">
        <i class="fa-solid fa-image text-primary text-lg"></i>
        Product Images
      </label>
      <div class="file-upload relative flex flex-col items-center justify-center p-7 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 cursor-pointer transition-all duration-300">
        <i class="fa-solid fa-cloud-arrow-up text-primary text-4xl mb-4"></i>
        <div class="file-upload-text text-center mb-4">
          <h3 class="text-lg text-dark mb-1">Upload Product Images</h3>
          <p class="text-sm text-gray">Click to browse or drag & drop your images here</p>
          <p class="text-sm text-gray">PNG, JPG, JPEG up to 5MB</p>
        </div>
        <input type="file" id="part_image" name="image" class="file-input absolute w-full h-full top-0 left-0 opacity-0 cursor-pointer" accept="image/*" required>
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
        <option value="In Stock" <?php echo (isset($_POST['availability']) && $_POST['availability'] === 'In Stock') ? 'selected' : ''; ?>>In Stock</option>
        <option value="Out of Stock" <?php echo (isset($_POST['availability']) && $_POST['availability'] === 'Out of Stock') ? 'selected' : ''; ?>>Out of Stock</option>
        <option value="Pre-order" <?php echo (isset($_POST['availability']) && $_POST['availability'] === 'Pre-order') ? 'selected' : ''; ?>>Pre-order</option>
      </select>
    </div>
    
    <!-- warranty (from JSON) - added new field -->
    <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 1.0s">
      <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="warranty">
        <i class="fa-solid fa-shield-alt text-primary text-lg"></i>
        Warranty
      </label>
      <input type="text" id="warranty" name="warranty" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. 24 months" value="<?php echo isset($_POST['warranty']) ? htmlspecialchars($_POST['warranty']) : ''; ?>" required>
    </div>
    
    <!-- seller (from JSON) matches seller_name -->
    <div class="form-group mb-0 opacity-0 translate-y-5" style="animation-delay: 1.1s">
      <label class="form-label flex items-center gap-2 font-medium text-dark mb-2" for="seller_name">
        <i class="fa-solid fa-store text-primary text-lg"></i>
        Seller Name
      </label>
      <input type="text" id="seller_name" name="seller" class="form-input w-full font-inter text-base px-4 py-3.5 border border-gray-200 rounded-xl bg-gray-50 transition-all duration-200 text-gray-800 focus:border-primary focus:bg-white focus:ring-3 focus:ring-primary/15" placeholder="e.g. AutoZone India" value="<?php echo isset($_POST['seller']) ? htmlspecialchars($_POST['seller']) : (isset($_SESSION['full_name']) ? $_SESSION['full_name'] : ''); ?>" required>
    </div>
    
    <!-- delivery_time (from JSON) - added new field -->
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

    // Client-side form validation
    document.getElementById('productForm').addEventListener('submit', function(e) {
      const fileInput = document.getElementById('part_image');
      if (fileInput.files.length === 0) {
        alert('Please select a product image');
        e.preventDefault();
        return false;
      }
      return true;
    });
  });
</script>

</body>
</html>