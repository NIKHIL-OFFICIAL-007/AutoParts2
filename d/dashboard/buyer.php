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

// Load and decode the JSON
$jsonPath = __DIR__ . "/parts.json";
$partsData = [];

if (file_exists($jsonPath)) {
    $jsonContent = file_get_contents($jsonPath);
    $decoded = json_decode($jsonContent, true);
    if (isset($decoded['data']) && is_array($decoded['data'])) {
        $partsData = $decoded['data'];
    }
}

// Extract unique categories and brands for filters
$categories = array_unique(array_column($partsData, 'category'));
$brands = array_unique(array_column($partsData, 'brand'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AutoParts - Buyer Portal</title>
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
    #sidebar{
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      z-index: 40;
      transform: translateX(-100%);
      transition: transform 0.3s ease-in-out;
    }
    #sidebar.open {
      transform: translateX(0);
    }
    @media (min-width: 768px) {
      #sidebar {
        position: static;
        height: auto;
        transform: translateX(0) !important;
      }
    }
    .scrollbar-hide::-webkit-scrollbar {
      display: none;
    }
    .scrollbar-hide {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    .banner-animation {
      animation: fadeIn 1s ease-in-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .main-content-container {
      min-height: calc(100vh - 16rem);
    }
    .sidebar-section {
      padding: 0.75rem;
      border-bottom: 1px solid #e5e7eb;
    }
    .sidebar-section:last-child {
      border-bottom: none;
    }
    #sidebarOverlay {
      display: none;
    }
    @media (max-width: 767px) {
      #sidebarOverlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0,0,0,0.5);
        z-index: 30;
      }
    }
    .dropdown:hover .dropdown-menu {
      display: block;
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
<body class="bg-gray-50 font-sans flex flex-col min-h-screen">

<!-- Navbar -->
<nav class="bg-primary text-white px-4 py-3 flex justify-between items-center sticky top-0 z-50 shadow-lg">
  <div class="flex items-center space-x-4">
    <button id="sidebarToggle" class="md:hidden text-white">
      <i class="fas fa-bars text-xl"></i>
    </button>
    <div class="flex items-center">
      <img src="../images/logo.png" alt="AutoParts Logo" class="h-14 md:h-24 w-auto">
    </div>
  </div>
  
  <div class="relative w-1/2 hidden md:block">
    <form id="searchForm" onsubmit="handleSearch(event)" class="flex">
      <input type="text" id="searchInput" placeholder="Search for parts, models, brands..."
        class="w-full p-2 rounded-l border-none outline-none text-gray-800"
        onfocus="showTrending()" onblur="hideTrending()" autocomplete="off" />
      <button type="submit" class="bg-secondary px-4 rounded-r text-white">
        <i class="fas fa-search"></i>
      </button>
    </form>
    <div id="trendingBox" class="absolute hidden bg-white shadow-md rounded w-full mt-1 z-50">
      <div class="px-4 py-2 font-semibold border-b bg-gray-100">🔥 Trending Searches</div>
      <a href="battery.php" class="block px-4 py-2 hover:bg-gray-100">Battery</a>
      <a href="brakepads.php" class="block px-4 py-2 hover:bg-gray-100">Brake Pads</a>
      <a href="airfilter.php" class="block px-4 py-2 hover:bg-gray-100">Air Filter</a>
      <a href="headlight.php" class="block px-4 py-2 hover:bg-gray-100">Headlight</a>
    </div>
  </div>
  
  <div class="flex items-center space-x-6">
    <!-- User Dropdown (First) -->
    <div class="relative group">
      <div class="flex items-center cursor-pointer">
        <i class="fas fa-user-circle text-xl mr-2"></i>

        <span class="text-sm font-medium">
          <?php
          // Check if full_name exists in session
          if (isset($_SESSION['full_name'])) {
            // Split full_name into parts and get first name
            $nameParts = explode(' ', $_SESSION['full_name']);
            echo htmlspecialchars($nameParts[0]);
          } else {
            // Fallback to 'Buyer' if no name is available
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
            <i class="fas fa-heart mr-2 text-red-500"></i> Wishlist (4)
          </a>
          
          <div class="border-t border-gray-200"></div>
          <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </div>
      </div>
    </div>
    
    <!-- Cart (Second) -->
    <a href="cart.php" class="relative flex items-center text-white hover:text-gray-200 space-x-2">
      <i class="fas fa-shopping-cart text-xl"></i>
      <span class="hidden md:inline">Cart</span>
      <?php if ($_SESSION['cart_count'] > 0): ?>
        <span class="cart-badge"><?= htmlspecialchars($_SESSION['cart_count']) ?></span>
      <?php endif; ?>
    </a>
    
    
  </div>
</nav>

  <!-- Mobile Search -->
  <div class="md:hidden bg-white p-3 shadow">
    <form class="flex">
      <input type="text" placeholder="Search..." class="w-full p-2 rounded-l border border-gray-300 outline-none">
      <button class="bg-secondary px-4 rounded-r text-white">
        <i class="fas fa-search"></i>
      </button>
    </form>
  </div>

  <!-- Main Content -->
  <div class="flex flex-1">
    <!-- Sidebar - Fixed under navbar on desktop -->
    <div id="sidebar" class="w-64 bg-white shadow-lg overflow-y-auto z-40 scrollbar-hide">
      <div class="p-3 border-b sticky top-0 bg-white z-10">
        <h2 class="text-lg font-bold text-gray-800 flex items-center">
          <i class="fas fa-filter text-secondary mr-2"></i>
          Filters
        </h2>
        <button id="clearFilters" class="text-xs text-primary hover:underline mt-1">Clear all filters</button>
      </div>
      
      <!-- Price Range Filter -->
      <div class="sidebar-section">
        <div class="flex justify-between items-center mb-1">
          <h3 class="font-semibold text-gray-700 flex items-center">
            <i class="fas fa-tag text-sm text-gray-500 mr-2"></i>
            Price Range
          </h3>
          <span id="priceRangeValue" class="text-xs bg-blue-100 text-primary px-2 py-1 rounded">₹0 - ₹20000</span>
        </div>
        <input type="range" id="priceRange" min="0" max="20000" value="20000" 
               class="w-full h-2 bg-blue-200 rounded-lg appearance-none cursor-pointer"
               oninput="updatePriceValue(this.value)">
      </div>
      
      <!-- Categories Filter -->
      <div class="sidebar-section">
        <h3 class="font-semibold text-gray-700 mb-1 flex items-center">
          <i class="fas fa-list text-sm text-gray-500 mr-2"></i>
          Categories
        </h3>
        <div class="space-y-1 max-h-60 overflow-y-auto scrollbar-hide">
          <?php foreach ($categories as $category): ?>
            <div class="flex items-center">
              <input type="checkbox" id="cat-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $category))) ?>" 
                     class="category-filter h-4 w-4 text-primary rounded focus:ring-primary"
                     value="<?= htmlspecialchars($category) ?>">
              <label for="cat-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $category))) ?>" 
                     class="ml-2 text-sm text-gray-700 hover:text-primary cursor-pointer">
                <?= htmlspecialchars($category) ?>
              </label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      
      <!-- Brands Filter -->
      <div class="sidebar-section">
        <h3 class="font-semibold text-gray-700 mb-1 flex items-center">
          <i class="fas fa-copyright text-sm text-gray-500 mr-2"></i>
          Brands
        </h3>
        <div class="space-y-1 max-h-60 overflow-y-auto scrollbar-hide">
          <?php foreach ($brands as $brand): ?>
            <div class="flex items-center">
              <input type="checkbox" id="brand-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $brand))) ?>" 
                     class="brand-filter h-4 w-4 text-primary rounded focus:ring-primary"
                     value="<?= htmlspecialchars($brand) ?>">
              <label for="brand-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $brand))) ?>" 
                     class="ml-2 text-sm text-gray-700 hover:text-primary cursor-pointer">
                <?= htmlspecialchars($brand) ?>
              </label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-30"></div>

    <!-- Main Content Area -->
    <div class="flex-1 main-content-container">
      <!-- Banner Carousel -->
      <div class="w-full overflow-hidden relative">
        <div class="flex transition-transform duration-700" id="bannerTrack">
          <div class="w-full flex-shrink-0">
            <div class="bg-gradient-to-r from-blue-900 to-primary h-64 flex items-center justify-center">
              <div class="text-center text-white px-4">
                <h2 class="text-3xl font-bold mb-2 banner-animation">Premium Auto Parts</h2>
                <p class="text-lg mb-4 banner-animation">Quality parts for your vehicle at competitive prices</p>
                <button class="bg-secondary hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-full transition-all banner-animation">
                  Shop Now
                </button>
              </div>
            </div>
          </div>
          <div class="w-full flex-shrink-0">
            <div class="bg-gradient-to-r from-gray-800 to-gray-600 h-64 flex items-center justify-center">
              <div class="text-center text-white px-4">
                <h2 class="text-3xl font-bold mb-2">Summer Special Offers</h2>
                <p class="text-lg mb-4">Up to 30% off on selected items</p>
                <button class="bg-secondary hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-full transition-all">
                  View Deals
                </button>
              </div>
            </div>
          </div>
          <div class="w-full flex-shrink-0">
            <div class="bg-gradient-to-r from-green-900 to-green-600 h-64 flex items-center justify-center">
              <div class="text-center text-white px-4">
                <h2 class="text-3xl font-bold mb-2">Free Shipping</h2>
                <p class="text-lg mb-4">On orders over ₹5000</p>
                <button class="bg-secondary hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-full transition-all">
                  Learn More
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
          <button class="w-3 h-3 rounded-full bg-white bg-opacity-50 hover:bg-opacity-100" onclick="goToSlide(0)"></button>
          <button class="w-3 h-3 rounded-full bg-white bg-opacity-50 hover:bg-opacity-100" onclick="goToSlide(1)"></button>
          <button class="w-3 h-3 rounded-full bg-white bg-opacity-50 hover:bg-opacity-100" onclick="goToSlide(2)"></button>
        </div>
      </div>

      <!-- Product Section -->
      <div class="max-w-7xl mx-auto p-4 md:p-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4 md:mb-0">
            <i class="fas fa-car text-secondary mr-2"></i>
            Popular Vehicle Parts
          </h2>
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Sort by:</span>
            <select id="sortSelect" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
              <option value="default">Featured</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="rating">Top Rated</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="productsContainer">
          <?php if (!empty($partsData)): ?>
            <?php foreach ($partsData as $part): ?>
              <div class="product-card bg-white rounded-xl shadow-md p-4 flex flex-col items-start hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
                   data-category="<?= htmlspecialchars($part['category']) ?>"
                   data-brand="<?= htmlspecialchars($part['brand']) ?>"
                   data-price="<?= htmlspecialchars($part['price']) ?>"
                   data-rating="<?= htmlspecialchars($part['rating']) ?>">
                <a href="product_details.php?id=<?= urlencode($part['name']) ?>" class="w-full">
                  <div class="relative w-full">
                    <img src="<?= htmlspecialchars($part['image']) ?>" alt="<?= htmlspecialchars($part['name']) ?>" class="w-full h-48 object-contain rounded-md mb-3">
                    <span class="absolute top-2 left-2 bg-primary text-white text-xs font-bold px-2 py-1 rounded">
                      <?= htmlspecialchars($part['availability']) ?>
                    </span>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($part['name']) ?></h3>
                  <p class="text-sm text-gray-600 mb-1">Brand: <span class="font-medium"><?= htmlspecialchars($part['brand']) ?></span></p>
                  <div class="flex items-center mb-2">
                    <div class="flex text-yellow-400">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= floor($part['rating'])): ?>
                          <i class="fas fa-star"></i>
                        <?php elseif ($i - 0.5 <= $part['rating']): ?>
                          <i class="fas fa-star-half-alt"></i>
                        <?php else: ?>
                          <i class="far fa-star"></i>
                        <?php endif; ?>
                      <?php endfor; ?>
                    </div>
                    <span class="text-sm text-gray-500 ml-1">(<?= htmlspecialchars($part['rating']) ?>)</span>
                  </div>
                  <p class="text-green-600 font-bold text-lg mt-1">₹<?= number_format($part['price'], 2) ?></p>
                  <p class="text-xs text-gray-500 mb-2">Warranty: <?= htmlspecialchars($part['warranty']) ?></p>
                </a>
                <form method="post" action="add_to_cart.php" class="w-full mt-auto">
                  <input type="hidden" name="product_id" value="<?= htmlspecialchars($part['name']) ?>">
                  <button type="submit" class="w-full bg-secondary hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                    <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
                  </button>
                </form>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-span-full text-center text-gray-500 py-12">
              <i class="fas fa-box-open text-4xl mb-4 text-gray-300"></i>
              <p>No parts available. Please check again later.</p>
            </div>
          <?php endif; ?>
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

  <!-- Scripts -->
  <script>
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    sidebarToggle.addEventListener('click', (e) => {
      e.preventDefault();
      sidebar.classList.toggle('open');
      sidebarOverlay.classList.toggle('hidden');
      document.body.classList.toggle('overflow-hidden');
    });

    sidebarOverlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      sidebarOverlay.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    });

    // Price range display
    function updatePriceValue(value) {
      document.getElementById('priceRangeValue').textContent = `₹0 - ₹${value}`;
    }

    // Filter products based on selections
    function filterProducts() {
      const maxPrice = parseFloat(document.getElementById('priceRange').value);
      const selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked')).map(el => el.value);
      const selectedBrands = Array.from(document.querySelectorAll('.brand-filter:checked')).map(el => el.value);
      const sortOption = document.getElementById('sortSelect').value;

      const products = Array.from(document.querySelectorAll('.product-card'));
      
      // First filter the products
      const filteredProducts = products.filter(product => {
        const productPrice = parseFloat(product.dataset.price);
        const productCategory = product.dataset.category;
        const productBrand = product.dataset.brand;

        // Check price
        if (productPrice > maxPrice) return false;
        
        // Check categories if any are selected
        if (selectedCategories.length > 0 && !selectedCategories.includes(productCategory)) return false;
        
        // Check brands if any are selected
        if (selectedBrands.length > 0 && !selectedBrands.includes(productBrand)) return false;
        
        return true;
      });

      // Then sort them
      filteredProducts.sort((a, b) => {
        const aPrice = parseFloat(a.dataset.price);
        const bPrice = parseFloat(b.dataset.price);
        const aRating = parseFloat(a.dataset.rating);
        const bRating = parseFloat(b.dataset.rating);

        switch(sortOption) {
          case 'price-asc': return aPrice - bPrice;
          case 'price-desc': return bPrice - aPrice;
          case 'rating': return bRating - aRating;
          default: return 0;
        }
      });

      // Hide all products first
      products.forEach(product => {
        product.style.display = 'none';
      });

      // Show only filtered products in sorted order
      const container = document.getElementById('productsContainer');
      filteredProducts.forEach(product => {
        product.style.display = 'flex';
        container.appendChild(product); // This repositions the element
      });

      // Show message if no products match
      if (filteredProducts.length === 0) {
        const noProductsMsg = document.createElement('div');
        noProductsMsg.className = 'col-span-full text-center text-gray-500 py-12';
        noProductsMsg.innerHTML = `
          <i class="fas fa-box-open text-4xl mb-4 text-gray-300"></i>
          <p>No products match your filters. Try adjusting your criteria.</p>
        `;
        container.appendChild(noProductsMsg);
      }
    }

    // Clear all filters
    document.getElementById('clearFilters').addEventListener('click', function(e) {
      e.preventDefault();
      document.querySelectorAll('.category-filter, .brand-filter').forEach(checkbox => {
        checkbox.checked = false;
      });
      document.getElementById('priceRange').value = 20000;
      document.getElementById('priceRangeValue').textContent = '₹0 - ₹20000';
      document.getElementById('sortSelect').value = 'default';
      filterProducts();
    });

    // Initialize event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
      // Add event listeners to all filters
      document.querySelectorAll('.category-filter, .brand-filter').forEach(element => {
        element.addEventListener('change', filterProducts);
      });
      
      document.getElementById('priceRange').addEventListener('input', function() {
        updatePriceValue(this.value);
        filterProducts();
      });
      
      document.getElementById('sortSelect').addEventListener('change', filterProducts);
    });

    // Banner carousel
    let currentSlide = 0;
    const bannerTrack = document.getElementById("bannerTrack");
    const bannerSlides = bannerTrack.children;

    function showSlide(index) {
      bannerTrack.style.transform = `translateX(-${index * 100}%)`;
    }

    function goToSlide(index) {
      currentSlide = index;
      showSlide(currentSlide);
    }

    setInterval(() => {
      currentSlide = (currentSlide + 1) % bannerSlides.length;
      showSlide(currentSlide);
    }, 5000);

    // Search functionality
    function showTrending() {
      document.getElementById("trendingBox").classList.remove("hidden");
    }

    function hideTrending() {
      setTimeout(() => {
        document.getElementById("trendingBox").classList.add("hidden");
      }, 200);
    }

    function handleSearch(e) {
      e.preventDefault();
      const query = document.getElementById("searchInput").value.trim().toLowerCase();
      if (query.includes("battery")) {
        window.location.href = "battery.php";
      } else if (query.includes("brake")) {
        window.location.href = "brakepads.php";
      } else if (query.includes("air")) {
        window.location.href = "airfilter.php";
      } else if (query.includes("headlight") || query.includes("light")) {
        window.location.href = "headlight.php";
      } else {
        alert("No results found. Try 'Battery', 'Brake Pads', 'Air Filter', or 'Headlight'.");
      }
    }
  </script>
</body>
</html>