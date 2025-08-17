<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

$error = '';
$success = '';

// Validate category ID
$categoryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$categoryId) {
    header('Location: manage_categories.php');
    exit;
}

// Fetch category data
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();

    if (!$category) {
        header('Location: manage_categories.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    try {
        // Validate input
        if (empty($name)) {
            throw new Exception("Category name is required");
        }

        // Check for duplicate category names (excluding current one)
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $stmt->execute([$name, $categoryId]);
        if ($stmt->fetch()) {
            throw new Exception("Category name already exists");
        }

        // Update category
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $categoryId]);

        $success = "Category updated successfully!";
        // Refresh category data
        $category['name'] = $name;
        $category['description'] = $description;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Category</h1>
        <a href="categories/manage_categories.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md">
            <i class="fas fa-arrow-left mr-2"></i> Back to Categories
        </a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block"><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="post" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($category['description']) ?></textarea>
            </div>

            <div class="pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                    <i class="fas fa-save mr-2"></i> Update Category
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
require_once '../includes/footer.php';
?>