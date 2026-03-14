<?php
require_once __DIR__ . '/auth.php';
require_admin_page();
require_once __DIR__ . '/config.php';

$pdo = db();
$msg = '';

// Handle Deletions
if (isset($_GET['delete_news'])) {
    $stmt = $pdo->prepare("DELETE FROM school_news WHERE id = ?");
    $stmt->execute([(int)$_GET['delete_news']]);
    header("Location: AdminCMS.php?msg=deleted");
    exit;
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_news'])) {
        $title = $_POST['title'];
        $category = $_POST['category'];
        $author = $_POST['author'];
        $date_posted = $_POST['date_posted'];
        $excerpt = $_POST['excerpt'];
        $content = $_POST['content'];
        
        // Handle Image Upload
        $image_path = 'https://placehold.co/800x450/800000/FFF?text=News'; // default fallback
        if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/news/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $_FILES['news_image']['name']);
            if (move_uploaded_file($_FILES['news_image']['tmp_name'], $upload_dir . $filename)) {
                $image_path = $upload_dir . $filename;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO school_news (title, category, excerpt, content, image_path, author, date_posted) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $category, $excerpt, $content, $image_path, $author, $date_posted]);
        $msg = "News article published successfully!";
    }
}

$news_items = $pdo->query("SELECT * FROM school_news ORDER BY date_posted DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Content Management | BNIS Admin</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f0; margin: 0; padding: 2rem; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { color: #800000; margin-top: 0; border-bottom: 2px solid #f0c040; padding-bottom: 10px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-weight: bold; margin-bottom: .5rem; font-size: 0.9rem; }
        input[type="text"], input[type="date"], select, textarea { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { background: #800000; color: #fff; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        button:hover { background: #5a0000; }
        .msg { background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 2rem; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9fafb; }
        .del-btn { color: red; text-decoration: none; font-size: 0.85rem; font-weight: bold; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #666; text-decoration: none; }
        .back-link:hover { color: #800000; }
    </style>
</head>
<body>

<div class="container">
    <a href="AdminDashboard.php" class="back-link">← Back to Dashboard</a>
    <h1>Manage Website Content</h1>
    
    <?php if($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?><div class="msg">Item deleted successfully!</div><?php endif; ?>

    <h2>Add New School News</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" required placeholder="e.g. Foundation Day 2026">
        </div>
        <div style="display:flex; gap:1rem;">
            <div class="form-group" style="flex:1">
                <label>Category</label>
                <select name="category">
                    <option value="academic">Academic</option>
                    <option value="sports">Sports</option>
                    <option value="community">Community</option>
                    <option value="culture">Culture</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group" style="flex:1">
                <label>Author / Department</label>
                <input type="text" name="author" required placeholder="e.g. SSG Adviser">
            </div>
            <div class="form-group" style="flex:1">
                <label>Date</label>
                <input type="date" name="date_posted" required value="<?= date('Y-m-d') ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Upload Display Picture</label>
            <input type="file" name="news_image" accept="image/*">
        </div>
        <div class="form-group">
            <label>Short Excerpt (Shows on the card)</label>
            <textarea name="excerpt" rows="2" required></textarea>
        </div>
        <div class="form-group">
            <label>Full Content / Article (Shows when clicked)</label>
            <textarea name="content" rows="6" required></textarea>
        </div>
        <button type="submit" name="add_news">Publish News</button>
    </form>

    <h2 style="margin-top:3rem;">Published News</h2>
    <table>
        <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Category</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php foreach($news_items as $n): ?>
        <tr>
            <td><img src="<?= htmlspecialchars($n['image_path']) ?>" width="60" style="border-radius:4px;"></td>
            <td><?= htmlspecialchars($n['title']) ?></td>
            <td><span style="text-transform:capitalize"><?= htmlspecialchars($n['category']) ?></span></td>
            <td><?= htmlspecialchars($n['date_posted']) ?></td>
            <td><a href="?delete_news=<?= $n['id'] ?>" class="del-btn" onclick="return confirm('Delete this news?')">Delete</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>