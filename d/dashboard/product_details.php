<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['cart_count'] = 0;
}

// Database connection
require_once 'db.php';

// Get the product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$relatedProducts = [];

try {
    $stmt = $pdo->prepare("
        SELECT p.*, b.name as brand_name, c.name as category_name 
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get related products
    if ($product) {
        $stmt = $pdo->prepare("
            SELECT p.*, b.name as brand_name, c.name as category_name 
            FROM products p 
            LEFT JOIN brands b ON p.brand_id = b.id 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.category_id = ? AND p.id != ? AND p.availability != 'Out of Stock'
            LIMIT 4
        ");
        $stmt->execute([$product['category_id'], $productId]);
        $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

// If product not found, redirect back
if (!$product) {
    header("Location: buyer.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($product['name']) ?> - AutoParts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1e40af',
            secondary: '#f59e0b',
          }
        }
      }
    }
  </script>
  <style>
    .product-gallery {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
      margin-top: 1rem;
    }
    .product-gallery img {
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .product-gallery img:hover {
      opacity: 0.8;
    }
    .main-image {
      height: 400px;
      object-fit: contain;
    }
    .specs-table td {
      padding: 0.75rem;
      border-bottom: 1px solid #e5e7eb;
    }
    .specs-table tr:last-child td {
      border-bottom: none;
    }
    .cart-badge {
      position: absolute;
      top: -8px;
      left: 20px;
      background-color: #ef4444;
      color: white;
      border-radius: 9999px;
      font-size: 0.75rem;
      width: 1.25rem;
      height: 1.25rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    @media (min-width: 768px) {
      .cart-badge {
        left: 32px;
      }
    }
  </style>
</head>
<body class="bg-gray-50 font-sans">

<!-- Navbar -->
<nav class="bg-primary text-white px-4 py-3 flex justify-between items-center sticky top-0 z-50 shadow-lg">
  <div class="flex items-center space-x-4">
    <a href="buyer.php" class="flex items-center">
      <img src="../images/logo.png" alt="AutoParts Logo" class="h-14 md:h-24 w-auto">
    </a>
  </div>
  
  <div class="flex items-center space-x-6">
    <!-- User Dropdown -->
    <div class="relative group">
      <div class="flex items-center cursor-pointer">
        <i class="fas fa-user-circle text-xl mr-2"></i>
        <span class="text-sm font-medium">
          <?php
          if (isset($_SESSION['full_name'])) {
            $nameParts = explode(' ', $_SESSION['full_name']);
            echo htmlspecialchars($nameParts[0]);
          } else {
            echo 'Buyer';
          }
          ?>
        </span>
        <i class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200 group-hover:rotate-180"></i>
      </div>
      
      <div class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-50 hidden group-hover:block">
        <div class="py-1">
          <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            <i class="fas fa-user mr-2 text-gray-500"></i> My Profile
          </a>
          <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            <i class="fas fa-box-open mr-2 text-orange-500"></i> Orders
          </a>
          <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
            <i class="fas fa-heart mr-2 text-red-500"></i> Wishlist
          </a>
          <div class="border-t border-gray-200"></div>
          <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </div>
      </div>
    </div>
    
    <!-- Cart -->
    <a href="cart.php" class="relative flex items-center text-white hover:text-gray-200 space-x-2">
      <i class="fas fa-shopping-cart text-xl"></i>
      <span class="hidden md:inline">Cart</span>
      <?php if ($_SESSION['cart_count'] > 0): ?>
        <span class="cart-badge"><?= htmlspecialchars($_SESSION['cart_count']) ?></span>
      <?php endif; ?>
    </a>
  </div>
</nav>

<!-- Breadcrumb Navigation -->
<div class="bg-gray-100 py-3 px-4">
  <div class="max-w-7xl mx-auto">
    <nav class="flex" aria-label="Breadcrumb">
      <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
          <a href="buyer.php" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
            <i class="fas fa-home mr-2"></i>
            Home
          </a>
        </li>
        <li>
          <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            <a href="#" class="ml-1 text-sm font-medium text-gray-700 hover:text-primary md:ml-2">
              <?= htmlspecialchars($product['category_name']) ?>
            </a>
          </div>
        </li>
        <li aria-current="page">
          <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
              <?= htmlspecialchars($product['name']) ?>
            </span>
          </div>
        </li>
      </ol>
    </nav>
  </div>
</div>

<!-- Main Product Content -->
<main class="max-w-7xl mx-auto px-4 py-8">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Product Images -->
    <div>
      <div class="bg-white rounded-lg shadow-md p-4">
        <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full main-image">
        
        <!-- Gallery Thumbnails (if available) -->
        <?php if (isset($product['gallery']) && is_array($product['gallery'])): ?>
          <div class="product-gallery">
            <?php foreach ($product['gallery'] as $image): ?>
              <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-24 object-cover rounded border border-gray-200">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Product Details -->
    <div>
      <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($product['name']) ?></h1>
        
        <div class="flex items-center mb-4">
          <div class="flex text-yellow-400 mr-2">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <?php if ($i <= 4): ?>
                <i class="fas fa-star"></i>
              <?php elseif ($i - 0.5 <= 4.5): ?>
                <i class="fas fa-star-half-alt"></i>
              <?php else: ?>
                <i class="far fa-star"></i>
              <?php endif; ?>
            <?php endfor; ?>
          </div>
          <span class="text-sm text-gray-500">(4.5)</span>
          <span class="mx-2 text-gray-300">|</span>
          <span class="text-sm text-green-600 font-medium"><?= htmlspecialchars($product['availability']) ?></span>
        </div>
        
        <div class="mb-6">
          <p class="text-3xl font-bold text-gray-900 mb-2">₹<?= number_format($product['price'], 2) ?></p>
        </div>
        
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Highlights</h3>
          <ul class="list-disc pl-5 text-gray-700 space-y-1">
            <li>Premium quality auto part</li>
            <li>Compatible with multiple vehicle models</li>
            <li>Manufacturer warranty included</li>
          </ul>
        </div>
        
        <div class="mb-6">
          <div class="flex items-center space-x-4">
            <form method="post" action="add_to_cart.php" class="flex-1">
              <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
              <button type="submit" class="w-full bg-secondary hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg transition-colors flex items-center justify-center">
                <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
              </button>
            </form>
            <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition-colors">
              <i class="fas fa-heart"></i>
            </button>
          </div>
        </div>
        
        <div class="border-t border-gray-200 pt-4">
          <div class="flex items-center space-x-4 text-sm text-gray-600">
            <div class="flex items-center">
              <i class="fas fa-shield-alt text-gray-500 mr-2"></i>
              <span><?= htmlspecialchars($product['warranty']) ?> Warranty</span>
            </div>
            <div class="flex items-center">
              <i class="fas fa-undo text-gray-500 mr-2"></i>
              <span>7-Day Returns</span>
            </div>
            <div class="flex items-center">
              <i class="fas fa-truck text-gray-500 mr-2"></i>
              <span>Free Shipping</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Product Description and Specifications -->
  <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-8">
    <!-- Description -->
    <div class="md:col-span-2 bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Description</h2>
      <div class="prose max-w-none text-gray-700">
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
      </div>
    </div>
    
    <!-- Specifications -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Specifications</h2>
      <table class="w-full specs-table">
        <tbody>
          <tr>
            <td class="font-medium text-gray-500">Brand</td>
            <td class="text-gray-900"><?= htmlspecialchars($product['brand_name']) ?></td>
          </tr>
          <tr>
            <td class="font-medium text-gray-500">Category</td>
            <td class="text-gray-900"><?= htmlspecialchars($product['category_name']) ?></td>
          </tr>
          <tr>
            <td class="font-medium text-gray-500">Vehicle Type</td>
            <td class="text-gray-900"><?= htmlspecialchars($product['vehicle_type']) ?></td>
          </tr>
          <tr>
            <td class="font-medium text-gray-500">Compatible Models</td>
            <td class="text-gray-900"><?= htmlspecialchars($product['compatible_models']) ?></td>
          </tr>
          <tr>
            <td class="font-medium text-gray-500">Delivery Time</td>
            <td class="text-gray-900"><?= htmlspecialchars($product['delivery_time']) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  
  <!-- Related Products -->
  <?php if (!empty($relatedProducts)): ?>
  <div class="mt-12">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php foreach ($relatedProducts as $related): ?>
        <a href="product_details.php?id=<?= urlencode($related['id']) ?>" class="bg-white rounded-xl shadow-md p-4 flex flex-col items-start hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
          <div class="relative w-full">
            <img src="<?= htmlspecialchars($related['image_path']) ?>" alt="<?= htmlspecialchars($related['name']) ?>" class="w-full h-48 object-contain rounded-md mb-3">
            <span class="absolute top-2 left-2 bg-primary text-white text-xs font-bold px-2 py-1 rounded">
              <?= htmlspecialchars($related['availability']) ?>
            </span>
          </div>
          <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($related['name']) ?></h3>
          <p class="text-sm text-gray-600 mb-1">Brand: <span class="font-medium"><?= htmlspecialchars($related['brand_name']) ?></span></p>
          <div class="flex items-center mb-2">
            <div class="flex text-yellow-400">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php if ($i <= 4): ?>
                  <i class="fas fa-star"></i>
                <?php elseif ($i - 0.5 <= 4.5): ?>
                  <i class="fas fa-star-half-alt"></i>
                <?php else: ?>
                  <i class="far fa-star"></i>
                <?php endif; ?>
              <?php endfor; ?>
            </div>
            <span class="text-sm text-gray-500 ml-1">(4.5)</span>
          </div>
          <p class="text-green-600 font-bold text-lg mt-1">₹<?= number_format($related['price'], 2) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</main>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-8 mt-12">
  <div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      <div>
        <img src="../images/logo.png" alt="AutoParts Logo" class="h-32 mb-4">
        <p class="text-gray-400">Your one-stop shop for all automotive needs. Quality parts at competitive prices.</p>
      </div>
      <div>
        <h4 class="font-semibold mb-4">Quick Links</h4>
        <ul class="space-y-2">
          <li><a href="buyer.php" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
          <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Shop</a></li>
          <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Categories</a></li>
          <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Deals</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-semibold mb-4">Customer Service</h4>
        <ul class="space-y-2">
          <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Contact Us</a></li>
          <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Shipping Policy</a></li>
          <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Returns & Refunds</a></li>
          <li><a href="#" class="text-gray-400 hover:text-white transition-colors">FAQ</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-semibold mb-4">Newsletter</h4>
        <p class="text-gray-400 mb-2">Subscribe to get special offers and updates</p>
        <form class="flex">
          <input type="email" placeholder="Your email" class="px-3 py-2 text-gray-800 rounded-l w-full focus:outline-none">
          <button class="bg-secondary hover:bg-yellow-600 px-4 rounded-r">
            <i class="fas fa-paper-plane"></i>
          </button>
        </form>
      </div>
    </div>
    <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
      <p>&copy; 2025 AutoParts. All rights reserved.</p>
    </div>
  </div>
</footer>

<script>
  // Image gallery functionality
  document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.querySelector('.main-image');
    const thumbnails = document.querySelectorAll('.product-gallery img');
    
    if (thumbnails.length > 0) {
      thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
          mainImage.src = this.src;
        });
      });
    }
  });
</script>
</body>
</html>