<?php
// Start output buffering
ob_start();

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];
$page_title = "Earnings - AutoParts Seller Portal";

// Get earnings data
try {
    // Total earnings
    $stmt = $pdo->prepare("SELECT SUM(price * quantity) as total FROM products WHERE seller_id = ?");
    $stmt->execute([$seller_id]);
    $total_earnings = $stmt->fetchColumn() ?: 0;

    // Monthly earnings
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(price * quantity) as amount
        FROM products
        WHERE seller_id = ?
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6
    ");
    $stmt->execute([$seller_id]);
    $monthly_earnings = $stmt->fetchAll();

    // Recent transactions
    $stmt = $pdo->prepare("
        SELECT p.name, p.price, p.quantity, p.created_at, b.name as buyer_name
        FROM products p
        LEFT JOIN buyers b ON p.buyer_id = b.id
        WHERE p.seller_id = ?
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$seller_id]);
    $recent_transactions = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = "Error fetching earnings data: " . $e->getMessage();
}
?>

<div class="main-content flex-1 bg-white p-7">
    <div class="header flex justify-between items-center mb-7 pb-5 border-b border-black/5">
        <div class="page-title flex items-center gap-3 text-2xl font-bold text-dark">
            <i class="fa-solid fa-money-bill text-primary"></i>
            <h1>Earnings</h1>
        </div>
        <div class="header-actions">
            <button class="btn">
                <i class="fa-solid fa-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success mb-5 p-4 bg-success/10 text-success border border-success/20 rounded-lg animate-fadeIn">
            <i class="fa-solid fa-circle-check mr-2"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error mb-5 p-4 bg-danger/10 text-danger border border-danger/20 rounded-lg animate-fadeIn">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <!-- Earnings Overview -->
    <div class="stats-container grid grid-cols-1 sm:grid-cols-3 gap-5 mb-7">
        <div class="stat-card bg-white rounded-xl p-5 shadow-md border-l-4 border-primary transition-transform duration-300 hover:-translate-y-1 animate-fadeIn" style="animation-delay: 0.1s">
            <div class="stat-title text-sm text-gray mb-2">Total Earnings</div>
            <div class="stat-value text-2xl font-bold text-dark">₹<?= number_format($total_earnings, 2) ?></div>
            <div class="stat-change flex items-center gap-1 text-xs text-success mt-1">
                <i class="fa-solid fa-arrow-up"></i>
                <span>12% from last month</span>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl p-5 shadow-md border-l-4 border-primary transition-transform duration-300 hover:-translate-y-1 animate-fadeIn" style="animation-delay: 0.2s">
            <div class="stat-title text-sm text-gray mb-2">This Month</div>
            <div class="stat-value text-2xl font-bold text-dark">₹<?= isset($monthly_earnings[0]) ? number_format($monthly_earnings[0]['amount'], 2) : '0.00' ?></div>
            <div class="stat-change flex items-center gap-1 text-xs text-success mt-1">
                <i class="fa-solid fa-arrow-up"></i>
                <span>8% from last month</span>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl p-5 shadow-md border-l-4 border-primary transition-transform duration-300 hover:-translate-y-1 animate-fadeIn" style="animation-delay: 0.3s">
            <div class="stat-title text-sm text-gray mb-2">Total Products Sold</div>
            <div class="stat-value text-2xl font-bold text-dark"><?= array_reduce($recent_transactions, function($carry, $item) { 
                return $carry + $item['quantity']; 
            }, 0) ?></div>
            <div class="stat-change flex items-center gap-1 text-xs text-success mt-1">
                <i class="fa-solid fa-arrow-up"></i>
                <span>5% from last month</span>
            </div>
        </div>
    </div>

    <!-- Earnings Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-7 mb-7">
        <!-- Monthly Earnings Chart -->
        <div class="chart-container bg-white rounded-2xl shadow-lg overflow-hidden p-6 animate-fadeIn" style="animation-delay: 0.4s">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-lg font-semibold text-dark">Monthly Earnings</h2>
                <select class="form-select text-sm border border-gray-200 rounded-lg px-3 py-1">
                    <option>Last 6 Months</option>
                    <option>This Year</option>
                    <option>Last Year</option>
                </select>
            </div>
            <div class="chart-placeholder h-64 bg-gray-50 rounded-lg flex items-center justify-center">
                <canvas id="monthlyEarningsChart"></canvas>
            </div>
        </div>
        
        <!-- Earnings by Category -->
        <div class="chart-container bg-white rounded-2xl shadow-lg overflow-hidden p-6 animate-fadeIn" style="animation-delay: 0.5s">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-lg font-semibold text-dark">Earnings by Category</h2>
                <select class="form-select text-sm border border-gray-200 rounded-lg px-3 py-1">
                    <option>All Time</option>
                    <option>This Year</option>
                    <option>This Month</option>
                </select>
            </div>
            <div class="chart-placeholder h-64 bg-gray-50 rounded-lg flex items-center justify-center">
                <canvas id="categoryEarningsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="transactions-container bg-white rounded-2xl shadow-lg overflow-hidden animate-fadeIn" style="animation-delay: 0.6s">
        <div class="flex justify-between items-center p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-dark">Recent Transactions</h2>
            <a href="transactions.php" class="text-primary text-sm font-medium hover:underline">View All</a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buyer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recent_transactions as $transaction): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-dark"><?= htmlspecialchars($transaction['name']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($transaction['buyer_name'] ?? 'N/A') ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500"><?= date('M d, Y', strtotime($transaction['created_at'])) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500"><?= $transaction['quantity'] ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-dark">₹<?= number_format($transaction['price'] * $transaction['quantity'], 2) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Completed
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Earnings Chart
    const monthlyCtx = document.getElementById('monthlyEarningsChart');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map(function($m) { 
                return date('M Y', strtotime($m['month'].'-01')); 
            }, $monthly_earnings)) ?>,
            datasets: [{
                label: 'Monthly Earnings',
                data: <?= json_encode(array_column($monthly_earnings, 'amount')) ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Category Earnings Chart (sample data)
    const categoryCtx = document.getElementById('categoryEarningsChart');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: ['Batteries', 'Lighting', 'Suspension', 'Electrical', 'Other'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    '#3b82f6',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            },
            cutout: '70%'
        }
    });
</script>

<?php 
require_once 'includes/footer.php';
ob_end_flush();
?>