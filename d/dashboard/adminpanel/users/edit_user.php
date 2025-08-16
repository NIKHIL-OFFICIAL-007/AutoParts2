<?php
require_once '../includes/auth.php';
require_once '../includes/db_connection.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = 'User not found';
        header('Location: manage_users.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$errors = [];
$full_name = $user['full_name'];
$email = $user['email'];
$role = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validation
if (empty($full_name)) {
    $errors['full_name'] = 'Full name is required';
}

if (empty($email)) {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format';
}

// Modified password validation - only checks if empty
if (empty($password)) {
    $errors['password'] = 'Password is required';
}

if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Passwords do not match';
}

if (empty($role)) {
    $errors['role'] = 'Role is required';
}

    // Check if email exists (excluding current user)
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            
            if ($stmt->fetch()) {
                $errors['email'] = 'Email already exists';
            }
        } catch (PDOException $e) {
            $errors['database'] = 'Database error: ' . $e->getMessage();
        }
    }

    // Update user
    if (empty($errors)) {
        try {
            $update_data = [
                'full_name' => $full_name,
                'email' => $email,
                'role' => $role,
                'id' => $user_id
            ];
            
            $sql = "UPDATE users SET full_name = :full_name, email = :email, role = :role";
            
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = :password";
                $update_data['password'] = $hashed_password;
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($update_data);
            
            $_SESSION['success'] = 'User updated successfully';
            header('Location: manage_users.php');
            exit;
        } catch (PDOException $e) {
            $errors['database'] = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-100 px-4 py-3 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Edit User</h2>
        </div>
        
        <form method="post" class="p-4">
            <?php if (isset($errors['database'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $errors['database'] ?>
                </div>
            <?php endif; ?>
            
            <div class="mb-4">
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($full_name) ?>"
                    class="w-full px-3 py-2 border <?= isset($errors['full_name']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md">
                <?php if (isset($errors['full_name'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['full_name'] ?></p>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>"
                    class="w-full px-3 py-2 border <?= isset($errors['email']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md">
                <?php if (isset($errors['email'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['email'] ?></p>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password (leave blank to keep current)</label>
                <input type="password" id="password" name="password"
                    class="w-full px-3 py-2 border <?= isset($errors['password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md">
                <?php if (isset($errors['password'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['password'] ?></p>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password"
                    class="w-full px-3 py-2 border <?= isset($errors['confirm_password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md">
                <?php if (isset($errors['confirm_password'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['confirm_password'] ?></p>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select id="role" name="role" class="w-full px-3 py-2 border <?= isset($errors['role']) ? 'border-red-500' : 'border-gray-300' ?> rounded-md">
                    <option value="">Select Role</option>
                    <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="seller" <?= $role === 'seller' ? 'selected' : '' ?>>Seller</option>
                    <option value="buyer" <?= $role === 'buyer' ? 'selected' : '' ?>>Buyer</option>
                </select>
                <?php if (isset($errors['role'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= $errors['role'] ?></p>
                <?php endif; ?>
            </div>
            
            <div class="flex justify-end mt-6">
                <a href="manage_users.php" class="mr-3 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>