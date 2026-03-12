<?php
session_start();
require_once 'config.php'; // Ensure this points to your DB connection file

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = db()->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            // Verify password
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                header("Location: AdminDashboard.php");
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please enter both username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | BNIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f4f4f0; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-card { background: #fff; width: 100%; max-width: 400px; padding: 2.5rem; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border-top: 5px solid #800000; }
        .logo-area { text-align: center; margin-bottom: 2rem; }
        .logo-area h1 { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #111827; margin: 0 0 0.5rem 0; }
        .logo-area p { color: #6b7280; font-size: 0.85rem; margin: 0; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; }
        .form-group input { width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #d1d5db; border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; box-sizing: border-box; transition: border-color 0.2s; }
        .form-group input:focus { outline: none; border-color: #800000; }
        .btn { width: 100%; padding: 0.85rem; background: #800000; color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: background 0.2s; font-family: 'DM Sans', sans-serif; }
        .btn:hover { background: #5a0000; }
        .error-msg { background: #fee2e2; color: #dc2626; padding: 0.75rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.5rem; text-align: center; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-area">
        <h1>BNIS Portal</h1>
        <p>Admin Login</p>
    </div>

    <?php if ($error): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autocomplete="off" placeholder="Enter your username">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
        </div>
        <button type="submit" class="btn">Log In</button>
    </form>
</div>

</body>
</html>
