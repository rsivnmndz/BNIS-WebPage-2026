<?php
// --- CONFIG: Update these to match your phpMyAdmin / MySQL setup ---
$db_host = 'localhost';
$db_user = 'root@localhost';
$db_pass = '';
$db_name = 'bnis_enrollment';

// Example SQL you can run in phpMyAdmin to create the database and table:
//
// CREATE DATABASE IF NOT EXISTS bnis_enrollment CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
// USE bnis_enrollment;
// CREATE TABLE IF NOT EXISTS shs_enrollments (
//   id INT AUTO_INCREMENT PRIMARY KEY,
//   student_name VARCHAR(255) NOT NULL,
//   lrn VARCHAR(20) NOT NULL,
//   email VARCHAR(255) NOT NULL,
//   student_type VARCHAR(50) NOT NULL,
//   grade_level VARCHAR(20) NOT NULL,
//   strand VARCHAR(50) NOT NULL,
//   guardian_contact VARCHAR(50) NOT NULL,
//   good_moral_path VARCHAR(255) DEFAULT NULL,
//   psa_birth_path VARCHAR(255) DEFAULT NULL,
//   report_card_path VARCHAR(255) DEFAULT NULL,
//   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// );

// Basic validation: only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed.";
    exit;
}

// Collect form data (SHS only)
$student_name      = trim($_POST['student_name'] ?? '');
$lrn               = trim($_POST['lrn'] ?? '');
$email             = trim($_POST['email'] ?? '');
$student_type      = trim($_POST['student_type'] ?? '');
$grade_level       = trim($_POST['grade_level'] ?? '');
$strand            = trim($_POST['strand'] ?? '');
$guardian_contact  = trim($_POST['guardian_contact'] ?? '');

// Simple required checks
if (
    $student_name === '' || $lrn === '' || $email === '' ||
    $student_type === '' || $grade_level === '' || $strand === '' ||
    $guardian_contact === ''
) {
    echo "Missing required fields. Please go back and complete the form.";
    exit;
}

// Enforce SHS only
if ($grade_level !== 'Grade 11' && $grade_level !== 'Grade 12') {
    echo "This form is only for Senior High School (Grade 11 or 12).";
    exit;
}

// Handle file uploads
$upload_base = __DIR__ . '/uploads';
if (!is_dir($upload_base)) {
    mkdir($upload_base, 0755, true);
}

function save_uploaded_file(string $field, string $baseDir, string $studentName): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $safeName = preg_replace('/[^a-zA-Z0-9_]/', '_', $studentName);
    $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
    $filename = $field . '_' . time() . '_' . $safeName . ($ext ? ('.' . $ext) : '');
    $targetPath = rtrim($baseDir, '/') . '/' . $filename;

    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $targetPath)) {
        return null;
    }

    // Return relative path for storage
    return 'uploads/' . $filename;
}

$good_moral_path  = save_uploaded_file('good_moral', $upload_base, $student_name);
$psa_birth_path   = save_uploaded_file('psa_birth', $upload_base, $student_name);
$report_card_path = save_uploaded_file('report_card', $upload_base, $student_name);

// Save to MySQL
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . htmlspecialchars($mysqli->connect_error);
    exit;
}

$stmt = $mysqli->prepare("
    INSERT INTO shs_enrollments
        (student_name, lrn, email, student_type, grade_level, strand, guardian_contact,
         good_moral_path, psa_birth_path, report_card_path)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    echo "Database error (prepare): " . htmlspecialchars($mysqli->error);
    exit;
}

$stmt->bind_param(
    'ssssssssss',
    $student_name,
    $lrn,
    $email,
    $student_type,
    $grade_level,
    $strand,
    $guardian_contact,
    $good_moral_path,
    $psa_birth_path,
    $report_card_path
);

if (!$stmt->execute()) {
    echo "Database error (execute): " . htmlspecialchars($stmt->error);
    $stmt->close();
    $mysqli->close();
    exit;
}

$stmt->close();
$mysqli->close();

// Simple confirmation page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrollment Submitted</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
               background:#f9fafb; color:#111827; display:flex; align-items:center;
               justify-content:center; min-height:100vh; margin:0; }
        .card { background:white; padding:2rem 2.5rem; border-radius:12px;
                box-shadow:0 10px 30px rgba(0,0,0,0.08); max-width:420px; text-align:center; }
        h1 { font-size:1.5rem; margin-bottom:0.75rem; color:#065f46; }
        p { font-size:0.95rem; margin-bottom:1.5rem; color:#4b5563; }
        a { display:inline-block; padding:0.6rem 1.4rem; border-radius:999px;
            background:#800000; color:white; text-decoration:none; font-size:0.85rem;
            text-transform:uppercase; letter-spacing:0.12em; }
        a:hover { background:#5a0000; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Enrollment Submitted</h1>
        <p>Your Senior High School pre-registration has been recorded successfully.</p>
        <a href="Enrollment.html">Back to Enrollment Page</a>
    </div>
</body>
</html>

