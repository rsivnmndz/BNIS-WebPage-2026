<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

// Override the JSON header from config.php so the browser renders the login form
header('Content-Type: text/html; charset=UTF-8');

auth_boot();

if (is_admin_authed()) {
    header('Location: AdminDashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = :u AND is_active = 1 LIMIT 1');
        $stmt->execute([':u' => $username]);
        $row = $stmt->fetch();

        if ($row && password_verify($password, (string)$row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$row['id'];
            $_SESSION['admin_username'] = (string)$row['username'];

            header('Location: AdminDashboard.php');
            exit;
        }

        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login | BNIS</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--maroon:#800000;--maroon-dark:#5a0000;--gold:#f0c040;--bg:#f4f4f0;--white:#fff;--g900:#111827;--g700:#374151;--g500:#6b7280;--g200:#e5e7eb;--shadow:0 18px 60px rgba(0,0,0,.12);--r-lg:18px;--r-md:12px;--r-full:9999px}
        *{box-sizing:border-box} body{margin:0;font-family:'DM Sans',system-ui,-apple-system,Segoe UI,sans-serif;background:var(--bg);color:var(--g900);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem}
        .card{width:min(460px,100%);background:var(--white);border:1.5px solid var(--g200);border-radius:var(--r-lg);box-shadow:var(--shadow);overflow:hidden}
        .top{padding:1.6rem 1.8rem;border-bottom:1px solid var(--g200);background:linear-gradient(135deg, rgba(128,0,0,0.06), rgba(240,192,64,0.10))}
        .title{font-family:'Playfair Display',serif;font-size:1.35rem;font-weight:800;letter-spacing:-.02em;margin:0}
        .sub{margin:.35rem 0 0;color:var(--g500);font-size:.85rem}
        form{padding:1.6rem 1.8rem;display:flex;flex-direction:column;gap:1rem}
        label{font-size:.7rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--g700)}
        input{width:100%;padding:.8rem .95rem;border-radius:var(--r-md);border:1.5px solid var(--g200);background:#fafafa;outline:none;font-size:.95rem}
        input:focus{border-color:var(--maroon);box-shadow:0 0 0 3px rgba(128,0,0,.12);background:#fff}
        .row{display:flex;flex-direction:column;gap:.45rem}
        .err{background:#fff5f5;border:1.5px solid #fecaca;color:#b91c1c;border-radius:var(--r-md);padding:.75rem .9rem;font-size:.85rem}
        .btn{appearance:none;border:none;border-radius:var(--r-full);padding:.9rem 1.2rem;background:var(--maroon);color:#fff;font-weight:800;letter-spacing:.12em;text-transform:uppercase;font-size:.78rem;cursor:pointer}
        .btn:hover{background:var(--maroon-dark)}
        .foot{padding:0 1.8rem 1.6rem;color:var(--g500);font-size:.78rem}
        .foot a{color:var(--maroon);font-weight:700;text-decoration:none}
        .foot a:hover{text-decoration:underline}
    </style>
</head>
<body>
    <div class="card">
        <div class="top">
            <h1 class="title">BNIS Admin Login</h1>
            <p class="sub">Sign in to access the private enrollment dashboard.</p>
        </div>

        <form method="post" autocomplete="off">
            <?php if ($error !== ''): ?>
                <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <div class="row">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" inputmode="text" autocapitalize="none" value="<?= htmlspecialchars((string)($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required />
            </div>
            <div class="row">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required />
            </div>
            <button class="btn" type="submit">Sign in</button>
        </form>

        <div class="foot">
            <a href="index.html">Back to public site</a>
        </div>
    </div>
</body>
</html>
