<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Calculate order totals
$cartTotal = 0;
$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
    $cartCount += $item['quantity'];
}

$shipping = 299.00;
$tax = $cartTotal * 0.18;
$total = $cartTotal + $shipping + $tax;

// Generate order number
$orderNumber = 'ORD-' . strtoupper(uniqid());
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Confirmation - AutoParts</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e7eb 100%);
    }
    
    .confirmation-icon {
      width: 80px;
      height: 80px;
      background-color: #10b981;
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 36px;
      margin: 0 auto;
      box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
    }
    
    .order-card {
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .order-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    
    .continue-btn {
      background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(245, 158, 11, 0.3);
    }
    
    .continue-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 10px rgba(245, 158, 11, 0.4);
    }
    
    .track-btn {
      background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(30, 64, 175, 0.3);
    }
    
    .track-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 10px rgba(30, 64, 175, 0.4);
    }
  </style>
</head>
<body class="flex flex-col min-h-screen">
  
  <!-- Navbar -->
  <nav class="bg-[#1e40af] text-white px-4 py-3 flex justify-between items-center sticky top-0 z-50 shadow-lg">
    <div class="flex items-center space-x-4">
      <div class="flex items-center">
        <img src="../images/logo.png" alt="AutoParts Logo" class="h-14 md:h-24 w-auto">
      </div>
    </div>
    
    <div class="flex items-center space-x-6">
      <!-- User Dropdown -->
      <div class="relative group">
        <div class="flex items-center cursor-pointer">
          <i class="fas fa-user-circle text-xl mr-2"></i>
          <span class="text-sm font-medium">
            <?= isset($_SESSION['full_name']) ? htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]) : 'Buyer' ?>
          </span>
          <i class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200 group-hover:rotate-180"></i>
        </div>
        
        <div class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-50 hidden group-hover:block">
          <div class="py-1">
            <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
              <i class="fas fa-user mr-2 text-gray-500"></i> My Profile
            </a>
            <a href="orders.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
              <i class="fas fa-box-open mr-2 text-orange-500"></i> Orders
            </a>
            <a href="wishlist.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
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
        <?php if ($cartCount > 0): ?>
          <span class="absolute -top-2 left-4 md:left-8 bg-red-500 text-xs rounded-full h-5 w-5 flex items-center justify-center">
            <?= htmlspecialchars($cartCount) ?>
          </span>
        <?php endif; ?>
      </a>
      
      <!-- Become a Seller -->
      <a href="#" class="flex items-center text-white hover:text-gray-200 space-x-2">
        <i class="fas fa-store text-xl"></i>
        <span class="hidden md:inline">Become a Seller</span>
      </a>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="flex-1 py-12">
    <div class="max-w-4xl mx-auto px-4">
      <div class="text-center mb-10">
        <div class="confirmation-icon mb-6">
          <i class="fas fa-check"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Confirmed!</h1>
        <p class="text-gray-600 text-lg">Thank you for your purchase. Your order has been received.</p>
        <p class="text-gray-500 mt-4">Order Number: <span class="font-semibold text-primary"><?= $orderNumber ?></span></p>
      </div>
      
      <div class="bg-white rounded-xl shadow-md p-6 mb-8 order-card">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <i class="fas fa-info-circle text-primary mr-3"></i>
          Order Details
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="font-medium text-gray-700 mb-2">Shipping Information</h3>
            <address class="text-gray-600 not-italic">
              <p class="font-medium"><?= isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Your Name' ?></p>
              <p>123 Main Street</p>
              <p>Kochi, Kerala 682001</p>
              <p>India</p>
              <p class="mt-2">Phone: +91 9876543210</p>
            </address>
          </div>
          
          <div>
            <h3 class="font-medium text-gray-700 mb-2">Payment Method</h3>
            <div class="flex items-center bg-gray-50 p-3 rounded-lg">
              <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                <i class="fas fa-money-bill-wave text-green-600"></i>
              </div>
              <div>
                <p class="font-medium">Cash on Delivery</p>
                <p class="text-sm text-gray-500">Pay when you receive the order</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-md p-6 mb-8 order-card">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
          <i class="fas fa-box-open text-primary mr-3"></i>
          Order Items
        </h2>
        
        <div class="space-y-4">
          <?php foreach ($_SESSION['cart'] as $item): ?>
            <div class="flex items-center border-b pb-4">
              <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-16 h-16 object-contain rounded-md">
              <div class="ml-4 flex-1">
                <h4 class="font-medium text-gray-900"><?= htmlspecialchars($item['name']) ?></h4>
                <p class="text-sm text-gray-600">Brand: <?= htmlspecialchars($item['brand']) ?></p>
                <div class="flex justify-between items-center mt-1">
                  <p class="text-green-600 font-bold">₹<?= number_format($item['price'], 2) ?></p>
                  <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <div class="border-t pt-4 mt-4">
          <div class="flex justify-between mb-2">
            <span class="text-gray-600">Subtotal</span>
            <span class="font-medium">₹<?= number_format($cartTotal, 2) ?></span>
          </div>
          <div class="flex justify-between mb-2">
            <span class="text-gray-600">Shipping</span>
            <span class="font-medium">₹<?= number_format($shipping, 2) ?></span>
          </div>
          <div class="flex justify-between mb-2">
            <span class="text-gray-600">Tax</span>
            <span class="font-medium">₹<?= number_format($tax, 2) ?></span>
          </div>
          <div class="flex justify-between text-lg font-bold mt-3 pt-3 border-t">
            <span>Total</span>
            <span>₹<?= number_format($total, 2) ?></span>
          </div>
        </div>
      </div>
      
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="buyer.php" class="continue-btn py-3 px-6 rounded-lg text-white font-bold text-center">
          Continue Shopping
        </a>
        
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white py-8 mt-auto">
    <div class="max-w-7xl mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <img src="../images/logo.png" alt="AutoParts Logo" class="h-32 mb-4">
          <p class="text-gray-400">Your one-stop shop for all automotive needs. Quality parts at competitive prices.</p>
        </div>
        <div>
          <h4 class="font-semibold mb-4">Quick Links</h4>
          <ul class="space-y-2">
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
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
</body>
</html>