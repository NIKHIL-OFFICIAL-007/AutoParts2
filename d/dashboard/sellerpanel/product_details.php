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

// Fetch product details
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?php echo htmlspecialchars($product['name']); ?> - AutoParts Seller Portal</title>
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
          }
        }
      }
    }
  </script>
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
        <i class="fa-solid fa-box-open text-primary"></i>
        <h1>Product Details</h1>
      </div>
      <div class="header-actions">
        <a href="my_products.php" class="btn bg-gray-200 text-dark px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors">
          <i class="fa-solid fa-arrow-left mr-2"></i> Back
        </a>
      </div>
    </div>
    
    <!-- Product Details -->
    <div class="product-details grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Product Image -->
      <div class="product-image bg-white rounded-xl shadow-md overflow-hidden">
        <img src="<?php echo $product['image_path']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-auto object-cover">
      </div>
      
      <!-- Product Info -->
      <div class="product-info">
        <div class="product-header mb-6">
          <h2 class="text-3xl font-bold text-dark mb-2"><?php echo htmlspecialchars($product['name']); ?></h2>
          <div class="flex items-center gap-4">
            <span class="text-xl font-semibold text-primary">₹<?php echo number_format($product['price'], 2); ?></span>
            <?php 
            $status_class = '';
            if ($product['availability'] === 'In Stock') {
                $status_class = 'bg-success/10 text-success';
            } elseif ($product['availability'] === 'Out of Stock') {
                $status_class = 'bg-danger/10 text-danger';
            } else {
                $status_class = 'bg-warning/10 text-warning';
            }
            ?>
            <span class="px-3 py-1 text-sm font-semibold rounded-full <?php echo $status_class; ?>">
              <?php echo htmlspecialchars($product['availability']); ?>
            </span>
          </div>
        </div>
        
        <div class="product-meta grid grid-cols-2 gap-4 mb-6">
          <div class="meta-item">
            <span class="text-sm text-gray-500">Brand</span>
            <p class="font-medium"><?php echo htmlspecialchars($product['brand']); ?></p>
          </div>
          <div class="meta-item">
            <span class="text-sm text-gray-500">Category</span>
            <p class="font-medium"><?php echo htmlspecialchars($product['category']); ?></p>
          </div>
          <div class="meta-item">
            <span class="text-sm text-gray-500">Vehicle Type</span>
            <p class="font-medium"><?php echo htmlspecialchars($product['vehicle_type']); ?></p>
          </div>
          <div class="meta-item">
            <span class="text-sm text-gray-500">Warranty</span>
            <p class="font-medium"><?php echo htmlspecialchars($product['warranty']); ?></p>
          </div>
        </div>
        
        <div class="product-description mb-6">
          <h3 class="text-lg font-semibold text-dark mb-2">Description</h3>
          <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
        
        <div class="product-compatibility mb-6">
          <h3 class="text-lg font-semibold text-dark mb-2">Compatible Models</h3>
          <div class="compatible-models flex flex-wrap gap-2">
            <?php 
            $models = explode(',', $product['compatible_models']);
            foreach ($models as $model): 
              $model = trim($model);
              if (!empty($model)):
            ?>
              <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm"><?php echo htmlspecialchars($model); ?></span>
            <?php 
              endif;
            endforeach; 
            ?>
          </div>
        </div>
        
        <div class="product-actions flex gap-3">
          <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn bg-secondary text-white px-4 py-2 rounded-lg hover:bg-secondary-dark transition-colors">
            <i class="fa-solid fa-pen-to-square mr-2"></i> Edit Product
          </a>
          <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn bg-danger text-white px-4 py-2 rounded-lg hover:bg-danger-dark transition-colors" onclick="return confirm('Are you sure you want to delete this product?');">
            <i class="fa-solid fa-trash mr-2"></i> Delete
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>