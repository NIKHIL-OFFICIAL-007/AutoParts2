<aside id="sidebar" class="bg-gray-800 text-white w-64 min-h-screen flex-shrink-0 transition-all duration-300 ease-in-out hidden lg:block">
    <div class="flex flex-col h-full">
        <!-- Logo Section -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
            <a href="dashboard.php" class="flex items-center">
                <i class="fas fa-shield-alt text-blue-400 text-2xl mr-3"></i>
                <span class="logo-text text-xl font-bold">AdminPanel</span>
            </a>
            <button id="sidebarCollapse" class="text-gray-400 hover:text-white focus:outline-none">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto py-4 sidebar-scrollbar">
            <div class="px-2 space-y-1">
                <!-- Dashboard -->
                <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-gray-700 text-white shadow-md' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-tachometer-alt mr-3 text-blue-400"></i>
                    <span class="nav-text">Dashboard</span>
                </a>

                <!-- Orders Section -->
                <div x-data="{ ordersOpen: <?= in_array(basename($_SERVER['PHP_SELF']), ['manage_orders.php', 'order_details.php']) ? 'true' : 'false' ?> }">
                    <button @click="ordersOpen = !ordersOpen" class="w-full flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200">
                        <i class="fas fa-shopping-cart mr-3 text-yellow-400"></i>
                        <span class="nav-text text-left flex-1">Orders</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'transform rotate-180': ordersOpen}"></i>
                    </button>
                    <div x-show="ordersOpen" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="orders/manage_orders.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'manage_orders.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Manage Orders
                        </a>
                        <a href="orders/order_details.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'order_details.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Order Details
                        </a>
                    </div>
                </div>

                <!-- Products Section -->
                <div x-data="{ productsOpen: <?= in_array(basename($_SERVER['PHP_SELF']), ['manage_products.php', 'add_product.php', 'edit_product.php']) ? 'true' : 'false' ?> }">
                    <button @click="productsOpen = !productsOpen" class="w-full flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200">
                        <i class="fas fa-box-open mr-3 text-purple-400"></i>
                        <span class="nav-text text-left flex-1">Products</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'transform rotate-180': productsOpen}"></i>
                    </button>
                    <div x-show="productsOpen" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="products/manage_products.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'manage_products.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Manage Products
                        </a>
                        <a href="products/add_product.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'add_product.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Add Product
                        </a>
                    </div>
                </div>

                <!-- Categories Section -->
                <div x-data="{ categoriesOpen: <?= in_array(basename($_SERVER['PHP_SELF']), ['manage_categories.php', 'add_category.php', 'edit_category.php']) ? 'true' : 'false' ?> }">
                    <button @click="categoriesOpen = !categoriesOpen" class="w-full flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200">
                        <i class="fas fa-tags mr-3 text-green-400"></i>
                        <span class="nav-text text-left flex-1">Categories</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'transform rotate-180': categoriesOpen}"></i>
                    </button>
                    <div x-show="categoriesOpen" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="categories/manage_categories.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'manage_categories.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Manage Categories
                        </a>
                        <a href="categories/add_category.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'add_category.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Add Category
                        </a>
                    </div>
                </div>

                <!-- Brand Section -->
                <div x-data="{ brandsOpen: <?= in_array(basename($_SERVER['PHP_SELF']), ['manage_brands.php', 'add_brand.php', 'edit_brand.php']) ? 'true' : 'false' ?> }">
                    <button @click="brandsOpen = !brandsOpen" class="w-full flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200">
                        <i class="fas fa-copyright mr-3 text-orange-400"></i> <!-- Changed icon for brands -->
                        <span class="nav-text text-left flex-1">Brands</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'transform rotate-180': brandsOpen}"></i>
                    </button>
                    <div x-show="brandsOpen" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="brands/manage_brands.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'manage_brands.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Manage Brands
                        </a>
                        <a href="brands/add_brand.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'add_brand.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Add Brand
                        </a>
                    </div>
                </div>

                <!-- Users Section -->
                <div x-data="{ usersOpen: <?= in_array(basename($_SERVER['PHP_SELF']), ['manage_users.php', 'add_user.php', 'edit_user.php']) ? 'true' : 'false' ?> }">
                    <button @click="usersOpen = !usersOpen" class="w-full flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200">
                        <i class="fas fa-users mr-3 text-blue-400"></i>
                        <span class="nav-text text-left flex-1">Users</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'transform rotate-180': usersOpen}"></i>
                    </button>
                    <div x-show="usersOpen" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="users/manage_users.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Manage Users
                        </a>
                        <a href="users/add_user.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'add_user.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Add User
                        </a>
                    </div>
                </div>

                <!-- Complaints Section -->
                <div x-data="{ complaintsOpen: <?= in_array(basename($_SERVER['PHP_SELF']), ['manage_complaints.php', 'complaint_details.php']) ? 'true' : 'false' ?> }">
                    <button @click="complaintsOpen = !complaintsOpen" class="w-full flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200">
                        <i class="fas fa-exclamation-circle mr-3 text-red-400"></i>
                        <span class="nav-text text-left flex-1">Complaints</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'transform rotate-180': complaintsOpen}"></i>
                    </button>
                    <div x-show="complaintsOpen" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="complaints/manage_complaints.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'manage_complaints.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Manage Complaints
                        </a>
                        <a href="complaints/complaint_details.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'complaint_details.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Complaint Details
                        </a>
                    </div>
                </div>

                <!-- Reports Section -->
                <div x-data="{ reportsOpen: <?= in_array(basename($_SERVER['PHP_SELF']), ['inventory_report.php', 'sales_report.php']) ? 'true' : 'false' ?> }">
                    <button @click="reportsOpen = !reportsOpen" class="w-full flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200">
                        <i class="fas fa-chart-bar mr-3 text-indigo-400"></i>
                        <span class="nav-text text-left flex-1">Reports</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'transform rotate-180': reportsOpen}"></i>
                    </button>
                    <div x-show="reportsOpen" x-collapse class="ml-8 mt-1 space-y-1">
                        <a href="reports/sales_report.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'sales_report.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Sales Report
                        </a>
                        <a href="reports/inventory_report.php" class="block px-3 py-2 rounded-lg text-sm transition-all duration-200 <?= basename($_SERVER['PHP_SELF']) == 'inventory_report.php' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                            Inventory Report
                        </a>
                    </div>
                </div>

                <!-- System Links -->
                <div class="mt-4 pt-4 border-t border-gray-700">
                    <a href="logout.php" class="flex items-center px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-200">
                        <i class="fas fa-sign-out-alt mr-3 text-gray-400"></i>
                        <span class="nav-text">Logout</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Sidebar Footer -->
        <div class="px-6 py-4 border-t border-gray-700 mt-auto">
            <div class="flex items-center">
                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold shadow-md">
                    <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium"><?= $_SESSION['username'] ?? 'Admin' ?></p>
                    <p class="text-xs text-gray-400">
                        <?= $_SESSION['role'] ?? 'Administrator' ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- AlpineJS for interactive components -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
    // Collapse/expand sidebar
    document.getElementById('sidebarCollapse').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('sidebar-collapsed');
        
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-chevron-left');
        icon.classList.toggle('fa-chevron-right');
        
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('sidebar-collapsed'));
    });

    // Restore collapsed state
    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.getElementById('sidebar').classList.add('sidebar-collapsed');
            const icon = document.querySelector('#sidebarCollapse i');
            icon.classList.remove('fa-chevron-left');
            icon.classList.add('fa-chevron-right');
        }
    });
</script>

<style>
    .sidebar-collapsed {
        width: 80px;
    }
    .sidebar-collapsed .nav-text,
    .sidebar-collapsed .logo-text {
        display: none;
    }
    .sidebar-collapsed .logo-icon {
        margin-right: 0;
    }
    [x-cloak] { display: none !important; }
</style>