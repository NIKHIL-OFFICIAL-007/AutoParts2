<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';



$error = '';
$success = '';
$name = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    try {
        // Validate input
        if (empty($name)) {
            throw new Exception("Category name is required");
        }

        // Check if category already exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            throw new Exception("Category '$name' already exists");
        }

        // Insert new category
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);

        $success = "Category '$name' added successfully!";
        // Clear form
        $name = $description = '';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Add New Category</h1>
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
            <!-- Category Name Field -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                       placeholder="Enter category name" required>
            </div>

            <!-- Description Field -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="4" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                          placeholder="[Please fill in this field.]"><?= htmlspecialchars($description) ?></textarea>
            </div>

            <!-- Save Button -->
            <div class="pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md w-full">
                    <i class="fas fa-save mr-2"></i> Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>