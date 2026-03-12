<?php
// ============================================================
//  api/enrollees.php
//  Returns paginated enrollee list with filters
//
//  GET params:
//    sy      = school_year_id
//    level   = all | Elementary | JHS | SHS
//    strand  = all | STEM | ABM | HUMSS | TVL | GAS
//    grade   = 0 (all) | 1-12
//    gender  = all | Male | Female
//    status  = all | Enrolled | Pending | Dropped | Transferred
//    q       = search (name, LRN, section)
//    page    = page number (default 1)
//    limit   = rows per page (default 25, max 100)
//    sort    = last_name | grade | strand | enrolled_at (default enrolled_at)
//    dir     = asc | desc (default desc)
// ============================================================
require_once __DIR__ . '/config.php';

$pdo = db();

// ── Resolve school year ─────────────────────────────────────
$syId = intParam('sy', 0);
if ($syId === 0) {
    $row  = $pdo->query("SELECT id FROM school_years WHERE is_active=1 LIMIT 1")->fetch();
    $syId = $row ? (int)$row['id'] : 1;
}

// ── Pagination ──────────────────────────────────────────────
$page  = max(1, intParam('page', 1));
$limit = min(100, max(5, intParam('limit', 25)));
$offset = ($page - 1) * $limit;

// ── Sort ────────────────────────────────────────────────────
$sortAllowed = ['last_name','first_name','grade','strand','gender','enrolled_at','status'];
$sort = strParam('sort', $sortAllowed, 'enrolled_at');
$dir  = strParam('dir',  ['asc','desc'], 'desc');

// ── Filters ─────────────────────────────────────────────────
$level  = strParam('level',  ['all','Elementary','JHS','SHS'], 'all');
$strand = strParam('strand', ['all','STEM','ABM','HUMSS','TVL','GAS'], 'all');
$gender = strParam('gender', ['all','Male','Female','Other'], 'all');
$status = strParam('status', ['all','Enrolled','Pending','Dropped','Transferred'], 'all');
$grade  = intParam('grade', 0);
$q      = trim($_GET['q'] ?? '');

$where  = ['school_year_id = :sy'];
$params = [':sy' => $syId];

if ($level  !== 'all') { $where[] = 'level = :level';   $params[':level']  = $level; }
if ($strand !== 'all') { $where[] = 'strand = :strand'; $params[':strand'] = $strand; }
if ($gender !== 'all') { $where[] = 'gender = :gender'; $params[':gender'] = $gender; }
if ($status !== 'all') { $where[] = 'status = :status'; $params[':status'] = $status; }
if ($grade  >  0)      { $where[] = 'grade = :grade';   $params[':grade']  = $grade; }
if ($q !== '') {
    $where[] = "(last_name LIKE :q OR first_name LIKE :q OR lrn LIKE :q OR section LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// ── Count ───────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollees $whereSQL");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();

// ── Rows ────────────────────────────────────────────────────
$sql = "SELECT id, last_name, first_name, middle_name, gender, level,
               grade, strand, section, lrn, status,
               DATE_FORMAT(enrolled_at,'%b %d, %Y %h:%i %p') AS enrolled_at
        FROM enrollees $whereSQL
        ORDER BY $sort $dir
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

json_out([
    'ok'         => true,
    'total'      => $total,
    'page'       => $page,
    'limit'      => $limit,
    'pages'      => (int)ceil($total / $limit),
    'enrollees'  => $rows,
]);
