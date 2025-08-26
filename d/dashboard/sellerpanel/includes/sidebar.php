
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
      <a href="dashboard.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'bg-white/10 text-white' : 'hover:bg-white/10 hover:text-white'; ?>">
        <i class="fa-solid fa-gauge w-6 text-center"></i>
        <span>Dashboard</span>
      </a>
    </li>
    <li class="nav-item mb-1">
      <a href="my_products.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 <?php echo basename($_SERVER['PHP_SELF']) === 'my_products.php' ? 'bg-white/10 text-white' : 'hover:bg-white/10 hover:text-white'; ?>">
        <i class="fa-solid fa-box w-6 text-center"></i>
        <span>My Products</span>
      </a>
    </li>
    <li class="nav-item mb-1">
      <a href="orders.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'bg-white/10 text-white' : 'hover:bg-white/10 hover:text-white'; ?>">
        <i class="fa-solid fa-truck-fast w-6 text-center"></i>
        <span>Orders</span>
      </a>
    </li>
    <li class="nav-item mb-1">
      <a href="earnings.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 <?php echo basename($_SERVER['PHP_SELF']) === 'earnings.php' ? 'bg-white/10 text-white' : 'hover:bg-white/10 hover:text-white'; ?>">
        <i class="fa-solid fa-money-bill w-6 text-center"></i>
        <span>Earnings</span>
      </a>
    </li>
    <li class="nav-item mb-1">
      <a href="analytics.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 <?php echo basename($_SERVER['PHP_SELF']) === 'analytics.php' ? 'bg-white/10 text-white' : 'hover:bg-white/10 hover:text-white'; ?>">
        <i class="fa-solid fa-chart-line w-6 text-center"></i>
        <span>Analytics</span>
      </a>
    </li>
    <li class="nav-item mb-1">
      <a href="settings.php" class="nav-link flex items-center px-4 py-3 rounded-lg text-white/70 no-underline transition-all duration-300 gap-3 <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'bg-white/10 text-white' : 'hover:bg-white/10 hover:text-white'; ?>">
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