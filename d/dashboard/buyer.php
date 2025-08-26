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

// Database connection (PDO expected)
require_once 'db.php';

// Fetch products from database
$partsData = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE availability != 'Out of Stock'");
    $stmt->execute();
    $partsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Extract unique category_ids and brand_ids for filters
    $categories = array_values(array_unique(array_filter(array_column($partsData, 'category_id'))));
    $brands     = array_values(array_unique(array_filter(array_column($partsData, 'brand_id'))));

    // Get category and brand names as id => name
    $categoryNames = [];
    $brandNames = [];

    if (!empty($categories)) {
        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE id IN ($placeholders)");
        $stmt->execute($categories);
        $categoryNames = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    if (!empty($brands)) {
        $placeholders = implode(',', array_fill(0, count($brands), '?'));
        $stmt = $pdo->prepare("SELECT id, name FROM brands WHERE id IN ($placeholders)");
        $stmt->execute($brands);
        $brandNames = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    // Get min and max price from database
    $stmt = $pdo->prepare("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products");
    $stmt->execute();
    $priceRange = $stmt->fetch(PDO::FETCH_ASSOC);
    $minPrice = floor($priceRange['min_price'] / 100) * 100; // Round down to nearest 100
    $maxPrice = ceil($priceRange['max_price'] / 100) * 100; // Round up to nearest 100

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $partsData = [];
    $categories = [];
    $brands = [];
    $categoryNames = [];
    $brandNames = [];
    $minPrice = 0;
    $maxPrice = 20000;
}
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
    .price-slider {
      -webkit-appearance: none;
      height: 6px;
      background: #d1d5db;
      border-radius: 5px;
      background-image: linear-gradient(#1e40af, #1e40af);
      background-size: 70% 100%;
      background-repeat: no-repeat;
    }
    .price-slider::-webkit-slider-thumb {
      -webkit-appearance: none;
      height: 20px;
      width: 20px;
      border-radius: 50%;
      background: #1e40af;
      cursor: pointer;
      box-shadow: 0 0 2px 0 #555;
    }
    .price-slider::-moz-range-thumb {
      height: 20px;
      width: 20px;
      border-radius: 50%;
      background: #1e40af;
      cursor: pointer;
      box-shadow: 0 0 2px 0 #555;
      border: none;
    }
    .filter-active {
      background-color: #dbeafe;
      border-color: #3b82f6;
    }
    .loading-indicator {
      display: none;
      text-align: center;
      padding: 10px;
    }
    .search-suggestions {
      max-height: 300px;
      overflow-y: auto;
    }
    /* Dual range slider styles */
    .price-slider-container {
      position: relative;
      height: 20px;
      margin: 20px 0;
    }
    .price-slider-track {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 100%;
      height: 6px;
      background: #d1d5db;
      border-radius: 5px;
      z-index: 1;
    }
    .price-slider-range {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      height: 6px;
      background: #1e40af;
      border-radius: 5px;
      z-index: 2;
    }
    .price-slider-thumb {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 20px;
      height: 20px;
      background: #1e40af;
      border-radius: 50%;
      cursor: pointer;
      z-index: 3;
    }
    .price-slider-thumb::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 12px;
      height: 12px;
      background: white;
      border-radius: 50%;
    }
    .price-input-container {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }
    .price-input {
      width: 45%;
      padding: 8px;
      border: 1px solid #d1d5db;
      border-radius: 4px;
      text-align: center;
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
    <form id="searchForm" class="flex">
      <input type="text" id="searchInput" placeholder="Search for parts, models, brands..."
        class="w-full p-2 rounded-l border-none outline-none text-gray-800"
        onfocus="showSuggestions()" onblur="hideSuggestions()" oninput="handleSearchInput()" autocomplete="off" />
      <button type="submit" class="bg-secondary px-4 rounded-r text-white" onclick="handleSearch(event)">
        <i class="fas fa-search"></i>
      </button>
    </form>
    <div id="searchSuggestions" class="absolute hidden bg-white shadow-md rounded w-full mt-1 z-50 search-suggestions">

      <div class="p-2 border-b">
        <div class="text-xs text-gray-500 mb-1">Categories:</div>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($categories as $categoryId): 
            $catName = $categoryNames[$categoryId] ?? 'Unknown';
            $catSlug = strtolower(preg_replace('/\s+/', '-', $catName));
          ?>
            <span class="bg-blue-100 text-primary text-xs px-2 py-1 rounded cursor-pointer hover:bg-blue-200" 
                  onclick="applyCategoryFilter('<?= htmlspecialchars($catName) ?>')">
              <?= htmlspecialchars($catName) ?>
            </span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  
  <div class="flex items-center space-x-6">
    <!-- User Dropdown (First) -->
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
      <?php if (!empty($_SESSION['cart_count'])): ?>
        <span class="cart-badge"><?= htmlspecialchars($_SESSION['cart_count']) ?></span>
      <?php endif; ?>
    </a>
  </div>
</nav>

  <!-- Mobile Search -->
  <div class="md:hidden bg-white p-3 shadow">
    <form class="flex">
      <input type="text" id="mobileSearchInput" placeholder="Search..." 
             class="w-full p-2 rounded-l border border-gray-300 outline-none"
             oninput="handleMobileSearch()">
      <button class="bg-secondary px-4 rounded-r text-white" onclick="handleMobileSearch(event)">
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
        <button id="clearFilters" class="text-xs text-primary hover:underline mt-1" onclick="clearFilters()">Clear all filters</button>
      </div>
      
      <!-- Price Range Filter -->
      <div class="sidebar-section">
        <div class="flex justify-between items-center mb-1">
          <h3 class="font-semibold text-gray-700 flex items-center">
            <i class="fas fa-tag text-sm text-gray-500 mr-2"></i>
            Price Range
          </h3>
        </div>
        <p class="text-xs text-gray-500 mb-2">Use slider or enter min and max price</p>
        
        <!-- Dual Range Slider -->
        <div class="price-slider-container mb-6">
          <div class="price-slider-track"></div>
          <div class="price-slider-range" id="priceRange"></div>
          <input type="range" min="<?= $minPrice ?>" max="<?= $maxPrice ?>" value="<?= $minPrice ?>" 
                 class="price-slider-thumb absolute" id="minPriceSlider" 
                 oninput="updateMinPrice(this.value)">
          <input type="range" min="<?= $minPrice ?>" max="<?= $maxPrice ?>" value="<?= $maxPrice ?>" 
                 class="price-slider-thumb absolute" id="maxPriceSlider" 
                 oninput="updateMaxPrice(this.value)">
        </div>
        
        <div class="price-input-container">
          <div class="flex-1 mr-2">
            <label for="minPriceInput" class="block text-xs text-gray-600 mb-1">Min</label>
            <input type="number" id="minPriceInput" min="<?= $minPrice ?>" max="<?= $maxPrice ?>" value="<?= $minPrice ?>" 
                   class="price-input w-full" onchange="validateMinPrice()">
          </div>
          <div class="flex-1 ml-2">
            <label for="maxPriceInput" class="block text-xs text-gray-600 mb-1">Max</label>
            <input type="number" id="maxPriceInput" min="<?= $minPrice ?>" max="<?= $maxPrice ?>" value="<?= $maxPrice ?>" 
                   class="price-input w-full" onchange="validateMaxPrice()">
          </div>
        </div>
      </div>
      
      <!-- Categories Filter -->
      <div class="sidebar-section">
        <h3 class="font-semibold text-gray-700 mb-1 flex items-center">
          <i class="fas fa-list text-sm text-gray-500 mr-2"></i>
          Categories
        </h3>
        <div class="space-y-1 max-h-60 overflow-y-auto scrollbar-hide">
          <?php foreach ($categories as $categoryId): 
            $catName = $categoryNames[$categoryId] ?? 'Unknown';
            $catSlug = strtolower(preg_replace('/\s+/', '-', $catName));
          ?>
            <div class="flex items-center">
              <input
                type="checkbox"
                id="cat-<?= htmlspecialchars($catSlug) ?>"
                class="category-filter h-4 w-4 text-primary rounded focus:ring-primary"
                value="<?= htmlspecialchars($catName) ?>"
                onchange="debouncedFilter()"
              >
              <label for="cat-<?= htmlspecialchars($catSlug) ?>"
                     class="ml-2 text-sm text-gray-700 hover:text-primary cursor-pointer">
                <?= htmlspecialchars($catName) ?>
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
          <?php foreach ($brands as $brandId): 
            $brandName = $brandNames[$brandId] ?? 'Unknown';
            $brandSlug = strtolower(preg_replace('/\s+/', '-', $brandName));
          ?>
            <div class="flex items-center">
              <input
                type="checkbox"
                id="brand-<?= htmlspecialchars($brandSlug) ?>"
                class="brand-filter h-4 w-4 text-primary rounded focus:ring-primary"
                value="<?= htmlspecialchars($brandName) ?>"
                onchange="debouncedFilter()"
              >
              <label for="brand-<?= htmlspecialchars($brandSlug) ?>"
                     class="ml-2 text-sm text-gray-700 hover:text-primary cursor-pointer">
                <?= htmlspecialchars($brandName) ?>
              </label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      
      <!-- Availability Filter -->
      <div class="sidebar-section">
        <h3 class="font-semibold text-gray-700 mb-1 flex items-center">
          <i class="fas fa-box text-sm text-gray-500 mr-2"></i>
          Availability
        </h3>
        <div class="flex items-center">
          <input type="checkbox" id="inStockOnly" class="h-4 w-4 text-primary rounded focus:ring-primary"
                 onchange="debouncedFilter()">
          <label for="inStockOnly" class="ml-2 text-sm text-gray-700 hover:text-primary cursor-pointer">
            In Stock Only
          </label>
        </div>
      </div>
      
      <!-- Rating Filter -->
      <div class="sidebar-section">
        <h3 class="font-semibold text-gray-700 mb-1 flex items-center">
          <i class="fas fa-star text-sm text-gray-500 mr-2"></i>
          Minimum Rating
        </h3>
        <select id="ratingFilter" class="w-full border rounded px-2 py-1 text-sm" onchange="debouncedFilter()">
          <option value="0">Any Rating</option>
          <option value="4">4 Stars & Up</option>
          <option value="3">3 Stars & Up</option>
          <option value="2">2 Stars & Up</option>
          <option value="1">1 Star & Up</option>
        </select>
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
            <select id="sortSelect" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary" onchange="debouncedFilter()">
              <option value="default">Featured</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="rating">Top Rated</option>
              <option value="name">Name (A-Z)</option>
            </select>
          </div>
        </div>
        
        <!-- Active Filters Display -->
        <div id="activeFilters" class="mb-4 flex flex-wrap gap-2 hidden">
          <span class="text-sm text-gray-600 mr-2">Active filters:</span>
        </div>
        
        <!-- Loading Indicator -->
        <div id="filterLoader" class="loading-indicator">
          <i class="fas fa-spinner fa-spin text-primary mr-2"></i> Filtering products...
        </div>
        
        <!-- Products Container -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="productsContainer">
          <?php if (!empty($partsData)): ?>
            <?php foreach ($partsData as $part): 
              $categoryName = $categoryNames[$part['category_id']] ?? 'Unknown';
              $brandName = $brandNames[$part['brand_id']] ?? 'Unknown';
            ?>
              <div class="product-card bg-white rounded-xl shadow-md p-4 flex flex-col items-start hover:shadow-lg transition-all duration-300 hover:-translate-y-1"
                   data-category="<?= htmlspecialchars($categoryName) ?>"
                   data-brand="<?= htmlspecialchars($brandName) ?>"
                   data-price="<?= htmlspecialchars($part['price']) ?>"
                   data-rating="4.5"
                   data-name="<?= htmlspecialchars($part['name']) ?>"
                   data-availability="<?= htmlspecialchars($part['availability']) ?>">
                <a href="product_details.php?id=<?= urlencode($part['id']) ?>" class="w-full">
                  <div class="relative w-full">
                    <img src="<?= htmlspecialchars($part['image_path']) ?>" alt="<?= htmlspecialchars($part['name']) ?>" class="w-full h-48 object-contain rounded-md mb-3">
                    <span class="absolute top-2 left-2 bg-primary text-white text-xs font-bold px-2 py-1 rounded">
                      <?= htmlspecialchars($part['availability']) ?>
                    </span>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($part['name']) ?></h3>
                  <p class="text-sm text-gray-600 mb-1">Brand: <span class="font-medium"><?= htmlspecialchars($brandName) ?></span></p>
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
                  <p class="text-green-600 font-bold text-lg mt-1">₹<?= number_format($part['price'], 2) ?></p>
                  <p class="text-xs text-gray-500 mb-2">Warranty: <?= htmlspecialchars($part['warranty']) ?></p>
                </a>
                <form method="post" action="add_to_cart.php" class="w-full mt-auto">
                  <input type="hidden" name="product_id" value="<?= htmlspecialchars($part['id']) ?>">
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
    // Global search text variable
    let globalSearchText = '';

    // Price range variables
    let minPrice = <?= $minPrice ?>;
    let maxPrice = <?= $maxPrice ?>;
    let minPriceSlider = document.getElementById('minPriceSlider');
    let maxPriceSlider = document.getElementById('maxPriceSlider');
    let minPriceInput = document.getElementById('minPriceInput');
    let maxPriceInput = document.getElementById('maxPriceInput');
    let priceRange = document.getElementById('priceRange');

    // Initialize price range slider
    function initPriceRange() {
      // Set initial position of range
      updatePriceRange();
      
      // Add event listeners for manual input
      minPriceInput.addEventListener('change', validateMinPrice);
      maxPriceInput.addEventListener('change', validateMaxPrice);
    }

    // Update the price range visual
    function updatePriceRange() {
      const minVal = parseInt(minPriceSlider.value);
      const maxVal = parseInt(maxPriceSlider.value);
      
      if (minVal > maxVal) {
        minPriceSlider.value = maxVal;
        minPriceInput.value = maxVal;
        maxPriceSlider.value = minVal;
        maxPriceInput.value = minVal;
      } else {
        minPriceInput.value = minVal;
        maxPriceInput.value = maxVal;
      }
      
      const minPercent = ((minVal - minPrice) / (maxPrice - minPrice)) * 100;
      const maxPercent = ((maxVal - minPrice) / (maxPrice - minPrice)) * 100;
      
      priceRange.style.left = minPercent + '%';
      priceRange.style.width = (maxPercent - minPercent) + '%';
      
      // Trigger filter
      debouncedFilter();
    }

    // Update min price from slider
    function updateMinPrice(value) {
      updatePriceRange();
    }

    // Update max price from slider
    function updateMaxPrice(value) {
      updatePriceRange();
    }

    // Validate min price input
    function validateMinPrice() {
      let value = parseInt(minPriceInput.value);
      
      if (isNaN(value)) value = minPrice;
      if (value < minPrice) value = minPrice;
      if (value > maxPrice) value = maxPrice;
      
      minPriceSlider.value = value;
      updatePriceRange();
    }

    // Validate max price input
    function validateMaxPrice() {
      let value = parseInt(maxPriceInput.value);
      
      if (isNaN(value)) value = maxPrice;
      if (value < minPrice) value = minPrice;
      if (value > maxPrice) value = maxPrice;
      
      maxPriceSlider.value = value;
      updatePriceRange();
    }

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

    // Debounce function for better performance
    let filterTimeout;
    function debouncedFilter() {
      clearTimeout(filterTimeout);
      document.getElementById('filterLoader').style.display = 'block';
      filterTimeout = setTimeout(filterProducts, 300);
    }

    // Filter products based on selections
    function filterProducts() {
      const minPriceValue = parseInt(minPriceSlider.value);
      const maxPriceValue = parseInt(maxPriceSlider.value);
      const selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked'))
                                     .map(el => el.value.toLowerCase().trim());
      const selectedBrands = Array.from(document.querySelectorAll('.brand-filter:checked'))
                                 .map(el => el.value.toLowerCase().trim());
      const sortOption = document.getElementById('sortSelect').value;
      const inStockOnly = document.getElementById('inStockOnly').checked;
      const minRating = parseInt(document.getElementById('ratingFilter').value);
      
      const products = Array.from(document.querySelectorAll('.product-card'));
      const container = document.getElementById('productsContainer');
      const activeFiltersContainer = document.getElementById('activeFilters');
      
      let hasVisibleProducts = false;
      let activeFilters = [];
      
      products.forEach(product => {
        const productPrice = parseFloat(product.dataset.price);
        const productCategory = (product.dataset.category || '').toLowerCase().trim();
        const productBrand = (product.dataset.brand || '').toLowerCase().trim();
        const productName = (product.dataset.name || '').toLowerCase().trim();
        const productAvailability = (product.dataset.availability || '').toLowerCase().trim();
        const productRating = parseFloat(product.dataset.rating);
        
        // Check if product matches all filters
        const priceMatch = productPrice >= minPriceValue && productPrice <= maxPriceValue;
        const categoryMatch = selectedCategories.length === 0 || 
                             selectedCategories.includes(productCategory);
        const brandMatch = selectedBrands.length === 0 || 
                          selectedBrands.includes(productBrand);
        const textMatch = globalSearchText === '' || 
                         productName.includes(globalSearchText) ||
                         productCategory.includes(globalSearchText) ||
                         productBrand.includes(globalSearchText);
        const stockMatch = !inStockOnly || productAvailability.includes('in stock');
        const ratingMatch = minRating === 0 || productRating >= minRating;
        
        // Show or hide based on filters
        if (priceMatch && categoryMatch && brandMatch && textMatch && stockMatch && ratingMatch) {
          product.style.display = 'flex';
          hasVisibleProducts = true;
        } else {
          product.style.display = 'none';
        }
      });
      
      // Update active filters display
      activeFiltersContainer.innerHTML = '<span class="text-sm text-gray-600 mr-2">Active filters:</span>';
      
      if (minPriceValue > minPrice || maxPriceValue < maxPrice) {
        activeFilters.push(`Price: ₹${minPriceValue} - ₹${maxPriceValue}`);
      }
      
      if (selectedCategories.length > 0) {
        activeFilters.push(`Categories: ${selectedCategories.join(', ')}`);
      }
      
      if (selectedBrands.length > 0) {
        activeFilters.push(`Brands: ${selectedBrands.join(', ')}`);
      }
      
      if (globalSearchText !== '') {
        activeFilters.push(`Search: "${globalSearchText}"`);
      }
      
      if (inStockOnly) {
        activeFilters.push('In Stock Only');
      }
      
      if (minRating > 0) {
        activeFilters.push(`Rating: ${minRating}+ Stars`);
      }
      
      if (activeFilters.length > 0) {
        activeFiltersContainer.classList.remove('hidden');
        activeFilters.forEach(filter => {
          const badge = document.createElement('span');
          badge.className = 'bg-blue-100 text-primary text-xs px-2 py-1 rounded-full flex items-center';
          badge.innerHTML = `${filter} <button class="ml-1 text-primary hover:text-blue-800" onclick="removeFilter('${filter}')">&times;</button>`;
          activeFiltersContainer.appendChild(badge);
        });
      } else {
        activeFiltersContainer.classList.add('hidden');
      }
      
      // Show message if no products match
      const noProductsMsg = document.getElementById('noProductsMessage');
      if (!hasVisibleProducts) {
        if (!noProductsMsg) {
          const messageDiv = document.createElement('div');
          messageDiv.id = 'noProductsMessage';
          messageDiv.className = 'col-span-full text-center text-gray-500 py-12';
          messageDiv.innerHTML = `
            <i class="fas fa-box-open text-4xl mb-4 text-gray-300"></i>
            <p>No products match your filters. Try adjusting your criteria.</p>
            <button onclick="clearFilters()" class="mt-4 bg-primary text-white px-4 py-2 rounded">
              Clear All Filters
            </button>
          `;
          container.appendChild(messageDiv);
        }
      } else if (noProductsMsg) {
        noProductsMsg.remove();
      }
      
      // Sort functionality
      if (sortOption !== 'default') {
        sortProducts(sortOption);
      }
      
      // Hide loader
      document.getElementById('filterLoader').style.display = 'none';
    }
    
    // Sort products
    function sortProducts(sortOption) {
      const container = document.getElementById('productsContainer');
      const products = Array.from(container.querySelectorAll('.product-card[style*="display: flex"]'));
      
      products.sort((a, b) => {
        const aPrice = parseFloat(a.dataset.price);
        const bPrice = parseFloat(b.dataset.price);
        const aRating = parseFloat(a.dataset.rating);
        const bRating = parseFloat(b.dataset.rating);
        const aName = (a.dataset.name || '').toLowerCase();
        const bName = (b.dataset.name || '').toLowerCase();
        
        switch (sortOption) {
          case 'price-asc': return aPrice - bPrice;
          case 'price-desc': return bPrice - aPrice;
          case 'rating': return bRating - aRating;
          case 'name': return aName.localeCompare(bName);
          default: return 0;
        }
      });
      
      // Reorder products in DOM
      products.forEach(product => container.appendChild(product));
    }
    
    // Remove specific filter
    function removeFilter(filterText) {
      if (filterText.startsWith('Price:')) {
        minPriceSlider.value = minPrice;
        maxPriceSlider.value = maxPrice;
        updatePriceRange();
      } else if (filterText.startsWith('Categories:')) {
        document.querySelectorAll('.category-filter:checked').forEach(checkbox => {
          checkbox.checked = false;
        });
      } else if (filterText.startsWith('Brands:')) {
        document.querySelectorAll('.brand-filter:checked').forEach(checkbox => {
          checkbox.checked = false;
        });
      } else if (filterText.startsWith('Search:')) {
        globalSearchText = '';
        document.getElementById('searchInput').value = '';
        document.getElementById('mobileSearchInput').value = '';
      } else if (filterText === 'In Stock Only') {
        document.getElementById('inStockOnly').checked = false;
      } else if (filterText.startsWith('Rating:')) {
        document.getElementById('ratingFilter').value = '0';
      }
      
      filterProducts();
    }

    // Clear all filters
    function clearFilters() {
      document.querySelectorAll('.category-filter, .brand-filter').forEach(checkbox => {
        checkbox.checked = false;
      });
      minPriceSlider.value = minPrice;
      maxPriceSlider.value = maxPrice;
      updatePriceRange();
      document.getElementById('sortSelect').value = 'default';
      globalSearchText = '';
      document.getElementById('searchInput').value = '';
      document.getElementById('mobileSearchInput').value = '';
      document.getElementById('inStockOnly').checked = false;
      document.getElementById('ratingFilter').value = '0';
      filterProducts();
    }

    // Search functionality
    function showSuggestions() {
      document.getElementById("searchSuggestions").classList.remove("hidden");
    }

    function hideSuggestions() {
      setTimeout(() => {
        document.getElementById("searchSuggestions").classList.add("hidden");
      }, 200);
    }

    function handleSearchInput() {
      globalSearchText = document.getElementById('searchInput').value.toLowerCase().trim();
      debouncedFilter();
    }

    function handleMobileSearch(e) {
      if (e) e.preventDefault();
      globalSearchText = document.getElementById('mobileSearchInput').value.toLowerCase().trim();
      debouncedFilter();
    }

    function handleSearch(e) {
      if (e) e.preventDefault();
      globalSearchText = document.getElementById('searchInput').value.toLowerCase().trim();
      debouncedFilter();
      document.getElementById("searchSuggestions").classList.add("hidden");
    }

    function quickSearch(term) {
      globalSearchText = term.toLowerCase().trim();
      document.getElementById('searchInput').value = term;
      document.getElementById('mobileSearchInput').value = term;
      document.getElementById("searchSuggestions").classList.add("hidden");
      filterProducts();
    }

    function applyCategoryFilter(category) {
      // Uncheck all category filters first
      document.querySelectorAll('.category-filter').forEach(checkbox => {
        checkbox.checked = false;
      });
      
      // Find and check the specific category
      const categorySlug = category.toLowerCase().replace(/\s+/g, '-');
      const categoryCheckbox = document.getElementById(`cat-${categorySlug}`);
      if (categoryCheckbox) {
        categoryCheckbox.checked = true;
      }
      
      document.getElementById("searchSuggestions").classList.add("hidden");
      filterProducts();
    }

    // Initialize event listeners when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
      initPriceRange();
      filterProducts();
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
    
  </script>
</body>
</html>