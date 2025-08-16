<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

// Calculate cart total and count
$cartTotal = 0;
$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
    $cartCount += $item['quantity'];
}
$_SESSION['cart_count'] = $cartCount;

// Calculate totals
$shipping = 299.00;
$tax = $cartTotal * 0.18;
$total = $cartTotal + $shipping + $tax;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - AutoParts</title>
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
    
    .step-circle {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .step-active {
      background-color: #1e40af;
      color: white;
      box-shadow: 0 4px 6px rgba(30, 64, 175, 0.3);
    }
    
    .step-inactive {
      background-color: #e5e7eb;
      color: #4b5563;
    }
    
    .step-line {
      height: 2px;
      flex-grow: 1;
      background-color: #d1d5db;
    }
    
    .step-line-active {
      background-color: #1e40af;
    }
    
    .card-hover {
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .card-hover:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    
    .payment-card {
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .payment-card:hover, .payment-card.active {
      border-color: #1e40af;
      background-color: #eff6ff;
    }
    
    .payment-card.active {
      box-shadow: 0 4px 6px rgba(30, 64, 175, 0.2);
    }
    
    .checkout-btn {
      background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(245, 158, 11, 0.3);
    }
    
    .checkout-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 10px rgba(245, 158, 11, 0.4);
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
    
    .input-focus:focus {
      border-color: #93c5fd;
      box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.5);
    }
    
    @media (min-width: 768px) {
      .cart-badge {
        left: 32px;
      }
    }
  </style>
</head>
<body class="flex flex-col min-h-screen">
  
  <!-- Navbar -->
  <nav class="bg-primary text-white px-4 py-3 flex justify-between items-center sticky top-0 z-50 shadow-lg">
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
        <?php if ($_SESSION['cart_count'] > 0): ?>
          <span class="absolute -top-2 left-4 md:left-8 bg-red-500 text-xs rounded-full h-5 w-5 flex items-center justify-center">
            <?= htmlspecialchars($_SESSION['cart_count']) ?>
          </span>
        <?php endif; ?>
      </a>
      

    </div>
  </nav>


  <!-- Main Content -->
  <div class="flex-1 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <h1 class="text-3xl font-bold text-gray-900 mb-1">Checkout</h1>
      <p class="text-gray-600 mb-8">Review your order and complete your purchase</p>
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Forms -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Shipping Information -->
          <div class="bg-white rounded-xl shadow-md p-6 card-hover">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <i class="fas fa-truck text-primary mr-3"></i>
              Shipping Information
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus" value="">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus" value="">
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus" value="">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus" value="">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus">
                  <option>KERALA</option>
                  <option>TAMIL NAIDU</option>
                  <option>ASSAM</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ZIP Code</label>
                <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus" value="">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg input-focus" value="">
              </div>
            </div>
          </div>
          
          <!-- Payment Method -->
          <div class="bg-white rounded-xl shadow-md p-6 card-hover">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <i class="fas fa-credit-card text-primary mr-3"></i>
              Payment Method
            </h2>
            
            <div class="space-y-4">
              <div class="payment-card" onclick="selectPayment(this, 'cod')">
                <div class="p-4">
                  <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                      <i class="fas fa-money-bill-wave text-green-600"></i>
                    </div>
                    <span class="font-medium">Cash on Delivery</span>
                  </div>
                </div>
              </div>
              
              <!-- COD Form -->
              <div id="cod-form" class="hidden mt-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                  <p class="text-green-800">Pay with cash when your order is delivered. An additional ₹50 processing fee applies.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Right Column - Order Summary -->
        <div>
          <div class="bg-white rounded-xl shadow-md p-6 sticky top-24 card-hover">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <i class="fas fa-receipt text-primary mr-3"></i>
              Order Summary
            </h2>
            
            <!-- Cart Items -->
            <div class="space-y-4 mb-6 max-h-80 overflow-y-auto pr-2">
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
            
            <!-- Order Totals -->
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium">₹<?= number_format($cartTotal, 2) ?></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Shipping</span>
                <span class="font-medium">₹<?= number_format($shipping, 2) ?></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Tax</span>
                <span class="font-medium">₹<?= number_format($tax, 2) ?></span>
              </div>
              <div class="flex justify-between text-lg font-bold mt-3 pt-3 border-t">
                <span>Total</span>
                <span>₹<?= number_format($total, 2) ?></span>
              </div>
            </div>
            
            
            <!-- Place Order Button -->
              <a href="placeorder.php" class="block w-full bg-secondary hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded-md text-center transition-colors">
                Place order
              </a>
            
            <p class="text-center text-sm text-gray-500 mt-4">
              By placing your order, you agree to our <a href="#" class="text-primary hover:underline">Terms of Service</a>
            </p>
          </div>
          
          <!-- Security Info -->
          <div class="mt-6 bg-white rounded-xl shadow-md p-6 card-hover">
            <div class="flex items-center mb-3">
              <i class="fas fa-shield-alt text-primary text-xl mr-3"></i>
              <h3 class="font-semibold text-gray-900">Secure Payment</h3>
            </div>
            <p class="text-sm text-gray-600 mb-4">Your payment information is encrypted and securely processed.</p>
            
            <div class="flex space-x-4">
              <div class="w-12 h-8 bg-gray-200 rounded flex items-center justify-center">
                <i class="fab fa-cc-visa text-blue-900"></i>
              </div>
              <div class="w-12 h-8 bg-gray-200 rounded flex items-center justify-center">
                <i class="fab fa-cc-mastercard text-red-700"></i>
              </div>
              <div class="w-12 h-8 bg-gray-200 rounded flex items-center justify-center">
                <i class="fab fa-cc-amex text-blue-600"></i>
              </div>
              <div class="w-12 h-8 bg-gray-200 rounded flex items-center justify-center">
                <i class="fab fa-cc-paypal text-blue-700"></i>
              </div>
            </div>
          </div>
        </div>
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

  <script>
    // Payment method selection
    function selectPayment(element, type) {
      // Remove active class from all payment cards
      document.querySelectorAll('.payment-card').forEach(card => {
        card.classList.remove('active');
      });
      
      // Add active class to clicked card
      element.classList.add('active');
      
      // Hide all forms
      document.getElementById('cod-form').classList.add('hidden');
      
      // Show selected form
      if (type === 'cod') {
        document.getElementById('cod-form').classList.remove('hidden');
      }
    }
    
    // Initialize with COD selected
    document.addEventListener('DOMContentLoaded', function() {
      // Select COD by default
      const codCard = document.querySelector('.payment-card');
      if (codCard) {
        selectPayment(codCard, 'cod');
      }
    });
  </script>
</body>
</html>