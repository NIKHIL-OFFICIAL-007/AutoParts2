
<?php
include 'db.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "❌ Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "❌ Email already exists. Please login.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed, $role);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['full_name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;

                if ($role === 'admin') {
                    header("Location: dashboard/adminpanel/dashboard.php");
                } elseif ($role === 'seller') {
                    header("Location: dashboard/dashboard.php");
                } else {
                    header("Location: dashboard/buyer.php");
                }
                exit;
            } else {
                $message = "❌ Signup failed: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>AutoParts – Sign Up</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    .form-control {
      position: relative;
    }
    .form-control input[type="password"] {
      width: 100%;
      padding-right: 40px;
    }
    .toggle-password {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #333;
    }
  </style>
</head>
<body>

  <!-- Header Logo -->
  <header class="main-header">
    <img src="images/logo.png" alt="AutoParts Logo" class="site-logo">
  </header>

  <!-- Hero Background -->
  <div class="hero">
    <div class="hero-content">
      <h1>Log in to<br>buy and sell<br>auto parts</h1>
      <div class="buttons">
        <a href="login.php" class="btn login">Log in</a>
        <a href="signup.php" class="btn signup">Sign up</a>
      </div>
    </div>
  </div>

  <!-- Signup Modal -->
  <div class="login-modal" role="dialog" aria-modal="true">
    <div class="modal-header">
      <button class="close-btn" onclick="window.history.back()">&times;</button>
      <h1>Create Account</h1>
    </div>
    <p class="subtext">Sign up and start trading auto parts</p>

    <?php if (!empty($message)) echo "<p style='color:#fff; text-align:center;'>$message</p>"; ?>

    <form class="login-form" method="POST" action="">
      <div class="form-control">
        <input type="text" name="full_name" placeholder="Full Name" required />
      </div>
      <div class="form-control">
        <input type="email" name="email" placeholder="Email" required />
      </div>
      <div class="form-control">
        <input type="password" name="password" id="password" placeholder="Password" required />
        <i class="fa-solid fa-eye-slash toggle-password" toggle="#password"></i>
      </div>
      <div class="form-control">
        <input type="password" name="confirm" id="confirm" placeholder="Confirm Password" required />
        <i class="fa-solid fa-eye-slash toggle-password" toggle="#confirm"></i>
      </div>
      <div class="form-control">
        <select name="role" required>
          <option value="">Select Role</option>
          <option value="buyer">Buyer</option>
          <option value="seller">Seller</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <button class="login-btn" type="submit">Sign Up</button>
    </form>

    <p class="signup-link">Already have an account? <a href="login.php">Login</a></p>
  </div>

  <!-- 👁️ Toggle Password Script -->
  <script>
    const toggles = document.querySelectorAll('.toggle-password');
    toggles.forEach(icon => {
      icon.addEventListener('click', () => {
        const input = document.querySelector(icon.getAttribute('toggle'));
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
      });
    });
  </script>

</body>
</html> 
