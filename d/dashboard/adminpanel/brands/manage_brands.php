<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Check admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch all brands
try {
    $stmt = $pdo->query("SELECT * FROM brands ORDER BY name");
    $brands = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage Brands</h1>
        <a href="brands/add_brand.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
            <i class="fas fa-plus mr-2"></i> Add Brand
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($brands as $brand): ?>
<tr>
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-medium text-gray-900">
            <?= htmlspecialchars($brand['name'] ?? '') ?>
        </div>
    </td>
    <td class="px-6 py-4">
        <div class="text-sm text-gray-500">
            <?= htmlspecialchars($brand['description'] ?? '') ?>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-500">
            <?= date('M j, Y', strtotime($brand['created_at'])) ?>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
        <a href="brands/edit_brand.php?id=<?= $brand['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="brands/delete_brand.php?id=<?= $brand['id'] ?>" onclick="return confirmDelete(<?= $brand['id'] ?>)" class="text-red-600 hover:text-red-900">
            <i class="fas fa-trash"></i> Delete
        </a>
    </td>
</tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDelete(brandId) {
    if (confirm('Are you sure you want to delete this brand? Products using this brand will need to be updated.')) {
        window.location.href = 'brands/delete_brand.php?id=' + brandId;
        return true;
    }
    return false;
}
</script>

<?php require_once '../includes/footer.php'; ?>