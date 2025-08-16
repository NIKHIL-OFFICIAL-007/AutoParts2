<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit;
}

require_once 'db.php';

// Fetch seller's products
$seller_id = $_SESSION['user_id'];
$products = array();

$query = "SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Products - AutoParts Seller Portal</title>
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
        <i class="fa-solid fa-boxes-stacked text-primary"></i>
        <h1>My Products</h1>
      </div>
      <div class="header-actions">
        <a href="dashboard.php" class="btn bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
          <i class="fa-solid fa-plus mr-2"></i> Add Product
        </a>
      </div>
    </div>
    
    <!-- Products Table -->
    <div class="products-table bg-white rounded-xl shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($products)): ?>
              <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No products found. Start by adding your first product.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($products as $product): ?>
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <img class="h-10 w-10 rounded-md object-cover" src="<?php echo $product['image_path']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                      <div class="text-sm text-gray-500"><?php echo htmlspecialchars($product['vehicle_type']); ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <?php echo htmlspecialchars($product['brand']); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  ₹<?php echo number_format($product['price'], 2); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <?php echo htmlspecialchars($product['category']); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
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
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                    <?php echo htmlspecialchars($product['availability']); ?>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <a href="product_details.php?id=<?php echo $product['id']; ?>" class="text-primary hover:text-primary-dark mr-3">
                    <i class="fa-solid fa-eye"></i> View
                  </a>
                  <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="text-secondary hover:text-secondary-dark mr-3">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                  </a>
                  <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="text-danger hover:text-danger-dark" onclick="return confirm('Are you sure you want to delete this product?');">
                    <i class="fa-solid fa-trash"></i> Delete
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</body>
</html>