<?php
// Set base URL
$base_url = '/d/dashboard/adminpanel/';

?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <base href="<?php echo $base_url; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Admin Dashboard'; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        .dark ::-webkit-scrollbar-track {
            background: #374151;
        }
        
        .dark ::-webkit-scrollbar-thumb {
            background: #4b5563;
        }
        
        .dark ::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
        
        /* Sidebar transitions */
        .sidebar-collapsed {
            width: 80px;
        }
        
        .sidebar-collapsed .nav-text,
        .sidebar-collapsed .logo-text,
        .sidebar-collapsed .badge {
            display: none;
        }
        
        .sidebar-collapsed .logo-icon {
            margin-right: 0;
        }
        
        /* Loading animation */
        @keyframes pulse {
            50% {
                opacity: .5;
            }
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <!-- Main Container -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white dark:bg-gray-800 shadow-sm z-10 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between px-6 py-3">
                    <!-- Left side - Hamburger and Search -->
                    <div class="flex items-center space-x-4">
                        <!-- Mobile menu button -->
                        <button id="sidebarToggle" class="text-gray-500 dark:text-gray-400 focus:outline-none lg:hidden">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        
                        <!-- Search bar -->
                        <div class="relative hidden md:block w-64">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-search text-gray-400 dark:text-gray-500"></i>
                            </span>
                            <input 
                                type="text" 
                                class="w-full pl-10 pr-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 border border-transparent focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent text-gray-700 dark:text-gray-300 placeholder-gray-500 dark:placeholder-gray-400"
                                placeholder="Search..."
                            >
                        </div>
                    </div>
                    
                    <!-- Right side - User and Notifications -->
                    <div class="flex items-center space-x-4">
                        <!-- Dark mode toggle -->
                        <button id="darkModeToggle" class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <i class="fas fa-moon dark:hidden"></i>
                            <i class="fas fa-sun hidden dark:block"></i>
                        </button>
                        
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors relative">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute top-0 right-0 h-2.5 w-2.5 rounded-full bg-red-500 border-2 border-white dark:border-gray-800"></span>
                            </button>
                        </div>
                        
                        <!-- Messages -->
                        <button class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors relative">
                            <i class="fas fa-envelope text-xl"></i>
                            <span class="absolute top-0 right-0 h-2.5 w-2.5 rounded-full bg-blue-500 border-2 border-white dark:border-gray-800"></span>
                        </button>
                        
                        <!-- User Profile Dropdown -->
                        <div class="relative">
                            <button id="userMenuButton" class="flex items-center space-x-2 focus:outline-none">
                                <div class="h-9 w-9 rounded-full bg-primary-600 flex items-center justify-center text-white font-semibold shadow-md">
                                    <?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 1)) : 'A'; ?>
                                </div>
                                <span class="hidden md:inline-block font-medium text-gray-700 dark:text-gray-300">
                                    <?php echo $_SESSION['username'] ?? 'Admin'; ?>
                                </span>
                                <i class="fas fa-chevron-down text-xs hidden md:inline-block text-gray-500 dark:text-gray-400"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-user mr-2 text-gray-500 dark:text-gray-400"></i> Profile
                                </a>
                                <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-cog mr-2 text-gray-500 dark:text-gray-400"></i> Settings
                                </a>
                                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-2 text-gray-500 dark:text-gray-400"></i> Sign out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="mb-6 px-4 py-3 rounded-lg <?php echo $_SESSION['flash_type'] === 'success' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'; ?>">
                        <?php echo $_SESSION['flash_message']; ?>
                        <?php unset($_SESSION['flash_message']); unset($_SESSION['flash_type']); ?>
                    </div>
                <?php endif; ?>