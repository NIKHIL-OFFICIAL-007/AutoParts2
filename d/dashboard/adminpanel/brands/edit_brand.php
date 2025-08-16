<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Check admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get brand ID from URL
$brand_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch brand data
try {
    $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->execute([$brand_id]);
    $brand = $stmt->fetch();
    
    if (!$brand) {
        $_SESSION['error'] = 'Brand not found';
        header('Location: manage_brands.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$errors = [];
$name = $brand['name'];
$description = $brand['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Brand name is required';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE brands SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $brand_id]);
            
            $_SESSION['success'] = 'Brand updated successfully';
            header('Location: manage_brands.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors['name'] = 'Brand name already exists';
            } else {
                $errors['database'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-100 px-4 py-3 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Edit Brand</h2>
        </div>
        
        <form method="post" class="p-4">
            <?php if (isset($errors['database'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $errors['database'] ?>
                </div>
            <?php endif; ?>
            
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Brand Name *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>"
                    class="w-full px-3 py-2 border <?= isset($errors['name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md">
                <?php if (isset($errors['name'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['name'] ?></p>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?= htmlspecialchars($description) ?></textarea>
            </div>
            
            <div class="flex justify-end mt-6">
                <a href="brands/manage_brands.php" class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update Brand
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>