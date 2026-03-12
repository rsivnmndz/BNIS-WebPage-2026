<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// Basic validation: only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed.";
    exit;
}

// Collect form data (SHS only)
$school_year       = trim((string)($_POST['school_year'] ?? ''));
$student_name      = trim($_POST['student_name'] ?? '');
$lrn               = trim($_POST['lrn'] ?? '');
$student_type      = trim($_POST['student_type'] ?? '');
$grade_level       = trim($_POST['grade_level'] ?? '');
$strand            = trim($_POST['strand'] ?? '');
$guardian_contact  = trim($_POST['guardian_contact'] ?? '');
$email             = trim((string)($_POST['email'] ?? ''));
$sex               = trim((string)($_POST['sex'] ?? ''));
$birthdate         = trim((string)($_POST['birthdate'] ?? ''));

// Simple required checks
if (
    $student_name === '' || $lrn === '' ||
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

// Handle file uploads (Files will be saved to the server, but paths won't be in the DB)
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

// ── Save to BNIS analytics DB (bnis_db.enrollees) ─────────────
$pdo = db();

// Resolve school year (prefer active; if a label exists, match it)
$syId = 0;
if ($school_year !== '') {
    $raw = trim($school_year);
    $lbl = str_starts_with(strtoupper($raw), 'SY') ? preg_replace('/\s+/', ' ', $raw) : ('SY ' . $raw);
    $stmt = $pdo->prepare("SELECT id FROM school_years WHERE label = :lbl LIMIT 1");
    $stmt->execute([':lbl' => $lbl]);
    $syId = (int)($stmt->fetchColumn() ?: 0);
}
if ($syId === 0) {
    $syId = (int)($pdo->query("SELECT id FROM school_years WHERE is_active = 1 LIMIT 1")->fetchColumn() ?: 1);
}

// Parse "Last, First, Middle" format
$last = $student_name;
$first = $student_name;
$middle = null;
$parts = array_map('trim', explode(',', $student_name));
if (count($parts) >= 2) {
    $last = $parts[0] !== '' ? $parts[0] : $student_name;
    $first = $parts[1] !== '' ? $parts[1] : $student_name;
    $middle = isset($parts[2]) && $parts[2] !== '' ? $parts[2] : null;
} else {
    // fallback: "First Last"
    $sp = preg_split('/\s+/', trim($student_name));
    if (is_array($sp) && count($sp) >= 2) {
        $last = array_pop($sp);
        $first = implode(' ', $sp);
    }
}

$gradeNum = ($grade_level === 'Grade 11') ? 11 : 12;
$gender = in_array($sex, ['Male','Female'], true) ? $sex : 'Male'; // DB enum only allows Male/Female
$status = 'Pending';

// Map strand to dashboard taxonomy
$strand = strtoupper($strand);
if ($strand === 'ICT' || str_contains($strand, 'EIM')) $strand = 'TVL';
if ($strand === '') $strand = 'GAS'; // Fallback

// NOTE: Adjusted query to only insert fields that ACTUALLY exist in bnis_database.sql
$stmt = $pdo->prepare("
    INSERT INTO enrollees
        (school_year_id, last_name, first_name, middle_name, gender, birth_date, grade, strand_code, lrn, status)
    VALUES
        (:sy, :ln, :fn, :mn, :g, :bd, :gr, :st, :lrn, :stt)
");

$stmt->execute([
    ':sy'  => $syId,
    ':ln'  => $last,
    ':fn'  => $first,
    ':mn'  => $middle,
    ':g'   => $gender,
    ':bd'  => ($birthdate !== '' ? $birthdate : null),
    ':gr'  => $gradeNum,
    ':st'  => $strand,
    ':lrn' => ($lrn !== '' ? $lrn : null),
    ':stt' => $status,
]);

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