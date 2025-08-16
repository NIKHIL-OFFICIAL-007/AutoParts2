<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-8">Admin Dashboard</h2>

    <!-- Stats Cards with Loading Skeletons -->
    <div id="statsContainer">
        <!-- Loading Skeleton -->
        <div id="loadingSkeleton" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6 animate-pulse">
                    <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-3/4 mb-4"></div>
                    <div class="h-8 bg-gray-200 dark:bg-gray-600 rounded w-1/2"></div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Actual Stats Content -->
        <div id="statsContent" class="hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Users -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:scale-105">
                    <div class="p-6 flex items-center">
                        <div class="flex-grow">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Users</p>
                            <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                                <?php 
                                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                                echo $stmt->fetchColumn();
                                ?>
                            </p>
                        </div>
                        <div class="flex-shrink-0 ml-4">
                            <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                                <i class="fas fa-users text-blue-600 dark:text-blue-300 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Products -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:scale-105">
                    <div class="p-6 flex items-center">
                        <div class="flex-grow">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Products</p>
                            <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                                <?php 
                                $stmt = $pdo->query("SELECT COUNT(*) FROM products");
                                echo $stmt->fetchColumn();
                                ?>
                            </p>
                        </div>
                        <div class="flex-shrink-0 ml-4">
                            <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                                <i class="fas fa-box-open text-green-600 dark:text-green-300 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:scale-105">
                    <div class="p-6 flex items-center">
                        <div class="flex-grow">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pending Orders</p>
                            <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                                <?php 
                                $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
                                echo $stmt->fetchColumn();
                                ?>
                            </p>
                        </div>
                        <div class="flex-shrink-0 ml-4">
                            <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-full">
                                <i class="fas fa-shopping-cart text-yellow-600 dark:text-yellow-300 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Open Complaints -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:scale-105">
                    <div class="p-6 flex items-center">
                        <div class="flex-grow">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Open Complaints</p>
                            <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                                <?php 
                                $stmt = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'open'");
                                echo $stmt->fetchColumn();
                                ?>
                            </p>
                        </div>
                        <div class="flex-shrink-0 ml-4">
                            <div class="bg-red-100 dark:bg-red-900 p-3 rounded-full">
                                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-300 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- User Growth Chart -->
        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">User Growth (Last 6 Months)</h3>
            <canvas id="userChart" height="250"></canvas>
        </div>
        
        <!-- Order Status Chart -->
        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Order Status Distribution</h3>
            <canvas id="orderChart" height="250"></canvas>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Orders -->
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                    <i class="fas fa-shopping-cart text-blue-500 mr-2"></i>
                    Recent Orders
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-200 uppercase tracking-wider">Order ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-200 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-200 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-200 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                        <?php
                        $stmt = $pdo->query("
                            SELECT o.id, u.full_name, o.total_amount, o.status 
                            FROM orders o
                            JOIN users u ON o.user_id = u.id
                            ORDER BY o.created_at DESC LIMIT 5
                        ");
                        while ($order = $stmt->fetch()):
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"><?php echo $order['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300"><?php echo htmlspecialchars($order['full_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    switch($order['status']) {
                                        case 'pending': echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'; break;
                                        case 'processing': echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'; break;
                                        case 'shipped': echo 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200'; break;
                                        case 'delivered': echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                                        case 'cancelled': echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; break;
                                        default: echo 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200';
                                    }
                                    ?>
                                ">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-600 text-right text-sm">
                <a href="orders.php" class="text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-400 font-medium">View all orders →</a>
            </div>
        </div>

        <!-- Recent Complaints -->
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    Recent Complaints
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-200 uppercase tracking-wider">Complaint ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-200 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-200 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                        <?php
                        $stmt = $pdo->query("
                            SELECT c.id, c.subject, c.status 
                            FROM complaints c
                            ORDER BY c.created_at DESC LIMIT 5
                        ");
                        while ($complaint = $stmt->fetch()):
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"><?php echo $complaint['id']; ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300"><?php echo htmlspecialchars($complaint['subject']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    switch($complaint['status']) {
                                        case 'open': echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; break;
                                        case 'in_progress': echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'; break;
                                        case 'resolved': echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                                        case 'rejected': echo 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200'; break;
                                        default: echo 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200';
                                    }
                                    ?>
                                ">
                                    <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-600 text-right text-sm">
                <a href="complaints.php" class="text-blue-600 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-400 font-medium">View all complaints →</a>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js and Custom Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize charts after page load
    document.addEventListener('DOMContentLoaded', function() {
        // Hide skeleton and show content
        setTimeout(() => {
            document.getElementById('loadingSkeleton').classList.add('hidden');
            document.getElementById('statsContent').classList.remove('hidden');
        }, 800);

        // User Growth Chart
        const userCtx = document.getElementById('userChart').getContext('2d');
        new Chart(userCtx, {
            type: 'line',
            data: {
                labels: <?php 
                    $months = [];
                    for ($i = 5; $i >= 0; $i--) {
                        $months[] = date('M', strtotime("-$i months"));
                    }
                    echo json_encode($months);
                ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?php 
                        $counts = [];
                        for ($i = 5; $i >= 0; $i--) {
                            $month = date('Y-m', strtotime("-$i months"));
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
                            $stmt->execute([$month]);
                            $counts[] = $stmt->fetchColumn();
                        }
                        echo json_encode($counts);
                    ?>,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#6B7280'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(209, 213, 219, 0.3)'
                        },
                        ticks: {
                            color: '#6B7280'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(209, 213, 219, 0.3)'
                        },
                        ticks: {
                            color: '#6B7280'
                        }
                    }
                }
            }
        });

        // Order Status Chart
        const orderCtx = document.getElementById('orderChart').getContext('2d');
        new Chart(orderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
                datasets: [{
                    data: <?php 
                        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
                        $counts = [];
                        foreach ($statuses as $status) {
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = ?");
                            $stmt->execute([$status]);
                            $counts[] = $stmt->fetchColumn();
                        }
                        echo json_encode($counts);
                    ?>,
                    backgroundColor: [
                        '#F59E0B', // yellow
                        '#3B82F6', // blue
                        '#8B5CF6', // purple
                        '#10B981', // green
                        '#EF4444'  // red
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#6B7280'
                        }
                    }
                }
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>