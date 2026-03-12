<?php
// ============================================================
//  enrollees.php
//  Returns paginated enrollee list with filters
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

$where  = ['e.school_year_id = :sy'];
$params = [':sy' => $syId];

if ($level  !== 'all') { $where[] = 'e.level = :level';   $params[':level']  = $level; }
if ($strand !== 'all') { $where[] = 'e.strand = :strand'; $params[':strand'] = $strand; }
if ($gender !== 'all') { $where[] = 'e.gender = :gender'; $params[':gender'] = $gender; }
if ($status !== 'all') { $where[] = 'e.status = :status'; $params[':status'] = $status; }
if ($grade  >  0)      { $where[] = 'e.grade = :grade';   $params[':grade']  = $grade; }
if ($q !== '') {
    $where[] = "(e.last_name LIKE :q OR e.first_name LIKE :q OR e.lrn LIKE :q OR s.section_name LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// ── Count ───────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollees e LEFT JOIN sections s ON e.section_id = s.id $whereSQL");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();

// ── Rows ────────────────────────────────────────────────────
$sql = "SELECT e.id, e.last_name, e.first_name, e.middle_name, e.gender, e.level,
               e.grade, e.strand, s.section_name AS section, e.lrn, e.status,
               DATE_FORMAT(e.enrolled_at,'%b %d, %Y %h:%i %p') AS enrolled_at
        FROM enrollees e
        LEFT JOIN sections s ON e.section_id = s.id
        $whereSQL
        ORDER BY e.$sort $dir
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
