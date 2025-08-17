<?php
// orders/manage_orders.php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php'; // Include the database connection
require_once '../includes/header.php';

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Search and filter handling
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Base query with parameter placeholders
$query = "SELECT o.*, u.full_name as customer_name 
          FROM orders o
          JOIN users u ON o.user_id = u.id
          WHERE 1=1";

$params = [];

// Add search condition
if (!empty($search)) {
    $query .= " AND (o.id LIKE :search OR u.full_name LIKE :search OR o.order_number LIKE :search)";
    $params[':search'] = "%$search%";
}

// Add status filter
if (!empty($statusFilter) && $statusFilter !== 'all') {
    $query .= " AND o.status = :status";
    $params[':status'] = $statusFilter;
}

// Count total records
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM ($query) as derived");
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalOrders = $countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $perPage);

// Add pagination to main query
$query .= " ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";
$params[':limit'] = $perPage;
$params[':offset'] = $offset;

// Fetch orders
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($key, $value, $paramType);
}
$stmt->execute();
$orders = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manage Orders</h1>
        <a href="add_order.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            <i class="fas fa-plus mr-2"></i>Add Order
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block mb-1">Search</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       class="w-full p-2 border rounded" placeholder="Order ID, Customer...">
            </div>
            <div>
                <label class="block mb-1">Status</label>
                <select name="status" class="w-full p-2 border rounded">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left">Order #</th>
                    <th class="py-3 px-4 text-left">Customer</th>
                    <th class="py-3 px-4 text-left">Date</th>
                    <th class="py-3 px-4 text-left">Amount</th>
                    <th class="py-3 px-4 text-left">Status</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr class="border-t hover:bg-gray-50">
                    <td class="py-3 px-4">#<?= htmlspecialchars($order['order_number']) ?></td>
                    <td class="py-3 px-4"><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td class="py-3 px-4"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                    <td class="py-3 px-4">$<?= number_format($order['total_amount'], 2) ?></td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 rounded-full text-xs 
                            <?= $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <a href="order_details.php?id=<?= $order['id'] ?>" class="text-blue-500 hover:text-blue-700 mr-2">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="edit_order.php?id=<?= $order['id'] ?>" class="text-yellow-500 hover:text-yellow-700 mr-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" onclick="confirmDelete(<?= $order['id'] ?>)" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="bg-gray-50 px-6 py-3 flex justify-between items-center">
            <div>
                Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $totalOrders) ?> of <?= $totalOrders ?> entries
            </div>
            <div class="flex space-x-1">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" 
                       class="px-3 py-1 border rounded hover:bg-gray-100">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" 
                       class="px-3 py-1 border rounded <?= $i === $page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" 
                       class="px-3 py-1 border rounded hover:bg-gray-100">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(orderId) {
    if (confirm('Are you sure you want to delete this order?')) {
        window.location.href = 'delete_order.php?id=' + orderId;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>