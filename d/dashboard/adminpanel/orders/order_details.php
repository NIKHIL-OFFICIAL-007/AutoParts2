<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php'; // Database connection

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_orders.php");
    exit();
}

$order_id = $_GET['id'];

try {
    // Get order details
    $order_stmt = $pdo->prepare("
        SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = :order_id
    ");
    $order_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $order_stmt->execute();
    $order = $order_stmt->fetch();

    if (!$order) {
        header("Location: manage_orders.php");
        exit();
    }

    // Get order items
    $items_stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.price as product_price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id
    ");
    $items_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $items_stmt->execute();
    $order_items = $items_stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Order Details #<?= htmlspecialchars($order['order_number']) ?></h1>
        <a href="manage_orders.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Order Number:</span>
                    <span class="font-medium">#<?= htmlspecialchars($order['order_number']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Date:</span>
                    <span><?= date('M j, Y h:i A', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-2 py-1 rounded-full text-xs 
                        <?= $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                           ($order['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Method:</span>
                    <span><?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></span>
                </div>
                <div class="flex justify-between font-bold border-t pt-3 mt-3">
                    <span>Total Amount:</span>
                    <span>$<?= number_format($order['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Customer Information</h2>
            <div class="space-y-3">
                <div>
                    <p class="font-medium"><?= htmlspecialchars($order['customer_name']) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Email:</p>
                    <p><?= htmlspecialchars($order['customer_email']) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Phone:</p>
                    <p><?= htmlspecialchars($order['customer_phone'] ?? 'N/A') ?></p>
                </div>
            </div>
        </div>

        <!-- Order Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Order Actions</h2>
            <div class="space-y-3">
                <a href="edit_order.php?id=<?= $order_id ?>" class="block w-full text-center bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                    <i class="fas fa-edit mr-2"></i> Edit Order
                </a>
                <button onclick="printOrder()" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-print mr-2"></i> Print Invoice
                </button>
                <button onclick="confirmStatusChange('cancelled')" class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-times mr-2"></i> Cancel Order
                </button>
                <?php if ($order['status'] !== 'completed'): ?>
                <button onclick="confirmStatusChange('completed')" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    <i class="fas fa-check mr-2"></i> Mark as Completed
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <h2 class="text-xl font-semibold p-6 border-b">Order Items</h2>
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($order_items as $item): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($item['product_name']) ?></div>
                                <?php if (!empty($item['notes'])): ?>
                                <div class="text-sm text-gray-500"><?= htmlspecialchars($item['notes']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        $<?= number_format($item['product_price'], 2) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $item['quantity'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        $<?= number_format($item['product_price'] * $item['quantity'], 2) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <!-- Order Totals -->
                <tr class="bg-gray-50">
                    <td colspan="3" class="px-6 py-4 text-right font-medium">Subtotal</td>
                    <td class="px-6 py-4 whitespace-nowrap">$<?= number_format($order['subtotal'] ?? $order['total_amount'], 2) ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <td colspan="3" class="px-6 py-4 text-right font-medium">Shipping</td>
                    <td class="px-6 py-4 whitespace-nowrap">$<?= number_format($order['shipping_cost'] ?? 0, 2) ?></td>
                </tr>
                <tr class="bg-gray-50">
                    <td colspan="3" class="px-6 py-4 text-right font-medium">Tax</td>
                    <td class="px-6 py-4 whitespace-nowrap">$<?= number_format($order['tax_amount'] ?? 0, 2) ?></td>
                </tr>
                <tr class="bg-gray-50 font-bold">
                    <td colspan="3" class="px-6 py-4 text-right">Total</td>
                    <td class="px-6 py-4 whitespace-nowrap">$<?= number_format($order['total_amount'], 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Shipping Information -->
    <?php if (!empty($order['shipping_address'])): ?>
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Shipping Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h3 class="font-medium mb-2">Shipping Address</h3>
                <p class="text-gray-600 whitespace-pre-line"><?= htmlspecialchars($order['shipping_address']) ?></p>
            </div>
            <div>
                <h3 class="font-medium mb-2">Shipping Method</h3>
                <p><?= htmlspecialchars($order['shipping_method'] ?? 'Standard Shipping') ?></p>
                <?php if (!empty($order['tracking_number'])): ?>
                <div class="mt-2">
                    <h3 class="font-medium mb-1">Tracking Number</h3>
                    <p><?= htmlspecialchars($order['tracking_number']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function printOrder() {
    window.open('print_order.php?id=<?= $order_id ?>', '_blank');
}

function confirmStatusChange(newStatus) {
    if (confirm(`Are you sure you want to mark this order as ${newStatus}?`)) {
        window.location.href = `update_order_status.php?id=<?= $order_id ?>&status=${newStatus}`;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>