<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Fetch all categories
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage Categories</h1>
        <a href="categories/add_category.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
            <i class="fas fa-plus mr-2"></i> Add Category
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($category['name']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($category['description']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="categories/edit_category.php?id=<?= $category['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="categories/delete_category.php?id=<?= $category['id'] ?>" onclick="return confirmDelete(<?= $category['id'] ?>)" class="text-red-600 hover:text-red-900">
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
function confirmDelete(categoryId) {
    if (confirm('Are you sure you want to delete this category?')) {
        window.location.href = 'delete_category.php?id=' + categoryId;
        return true;
    }
    return false;
}
</script>

<?php require_once '../includes/footer.php'; ?>