<?php
// ============================================================
//  analytics.php
//  Main endpoint — returns all dashboard data as one JSON blob
// ============================================================
require_once __DIR__ . '/config.php';

$pdo = db();

// ── Resolve school year ─────────────────────────────────────
$syId = intParam('sy', 0);
if ($syId === 0) {
    $row  = $pdo->query("SELECT id FROM school_years WHERE is_active = 1 LIMIT 1")->fetch();
    $syId = $row ? (int)$row['id'] : 1;
}

// ── Filters ─────────────────────────────────────────────────
$levelAllowed  = ['all','Elementary','JHS','SHS'];
$strandAllowed = ['all','STEM','ABM','HUMSS','TVL','GAS'];

$level  = strParam('level',  $levelAllowed,  'all');
$strand = strParam('strand', $strandAllowed, 'all');
$q      = trim($_GET['q'] ?? '');

// Base WHERE (using alias 'e' for enrollees, 's' for sections)
$where  = ['e.school_year_id = :sy', "e.status = 'Enrolled'"];
$params = [':sy' => $syId];

// The DB is SHS only (Grades 11 & 12). So hide data if looking for Elem/JHS
if ($level === 'Elementary' || $level === 'JHS') {
    $where[] = '1 = 0'; 
}
if ($strand !== 'all') {
    $where[] = 'e.strand_code = :strand';
    $params[':strand'] = $strand;
}
if ($q !== '') {
    $where[] = "(CONCAT('Grade ', e.grade) LIKE :q OR e.strand_code LIKE :q OR s.section_name LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// ── 1. KPI totals ───────────────────────────────────────────
$kpiSQL = "
    SELECT
        COUNT(*) AS total,
        0 AS elem,
        0 AS jhs,
        COUNT(*) AS shs,
        SUM(e.gender = 'Male') AS male,
        SUM(e.gender = 'Female') AS female
    FROM enrollees e
    LEFT JOIN sections s ON e.section_id = s.id 
    $whereSQL";
$stmt = $pdo->prepare($kpiSQL);
$stmt->execute($params);
$kpi = $stmt->fetch();

// ── 2. Grade breakdown ──────────────────────────────────────
$gradeSQL = "
    SELECT
        'SHS' AS level,
        e.grade,
        CONCAT('Grade ', e.grade) AS grade_label,
        COUNT(*) AS count,
        SUM(e.gender='Male') AS male,
        SUM(e.gender='Female') AS female
    FROM enrollees e 
    LEFT JOIN sections s ON e.section_id = s.id 
    $whereSQL
    GROUP BY e.grade
    ORDER BY e.grade";
$stmt = $pdo->prepare($gradeSQL);
$stmt->execute($params);
$grades = $stmt->fetchAll();

// ── 3. Strand breakdown (SHS only) ─────────────────────────
$strandSQL = "
    SELECT
        e.strand_code AS strand,
        e.grade,
        COUNT(*) AS count,
        SUM(e.gender='Male') AS male,
        SUM(e.gender='Female') AS female,
        COUNT(DISTINCT e.section_id) AS sections
    FROM enrollees e
    WHERE e.school_year_id = :sy AND e.status = 'Enrolled'
    " . ($strand !== 'all' ? "AND e.strand_code = :strand2" : "") . "
    GROUP BY e.strand_code, e.grade
    ORDER BY e.strand_code, e.grade";

$strandParams = [':sy' => $syId];
if ($strand !== 'all') $strandParams[':strand2'] = $strand;
$stmt = $pdo->prepare($strandSQL);
$stmt->execute($strandParams);
$strands = $stmt->fetchAll();

// ── 4. Monthly trend ────────────────────────────────────────
$monthSQL = "
    SELECT
        MONTH(e.enrolled_at) AS mon,
        YEAR(e.enrolled_at)  AS yr,
        'SHS' AS level,
        COUNT(*) AS count
    FROM enrollees e
    WHERE e.school_year_id = :sy AND e.status = 'Enrolled'
    GROUP BY YEAR(e.enrolled_at), MONTH(e.enrolled_at)
    ORDER BY yr, mon";
$stmt = $pdo->prepare($monthSQL);
$stmt->execute([':sy' => $syId]);
$monthlyRaw = $stmt->fetchAll();

$monthOrder = [6,7,8,9,10,11,12,1,2,3,4,5];
$monthLabels = ['Jun','Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar','Apr','May'];
$trendAll = array_fill(0, 12, 0);
$trendSHS = array_fill(0, 12, 0);
$trendJHS = array_fill(0, 12, 0);

foreach ($monthlyRaw as $r) {
    $idx = array_search((int)$r['mon'], $monthOrder);
    if ($idx !== false) {
        $trendAll[$idx] += (int)$r['count'];
        $trendSHS[$idx] += (int)$r['count'];
    }
}

// ── 5. Section capacity ─────────────────────────────────────
$capacitySQL = "
    SELECT
        s.grade,
        s.strand_code AS strand,
        SUM(s.capacity) AS capacity,
        COUNT(DISTINCT s.id) AS section_count
    FROM sections s
    WHERE s.school_year_id = :sy
    GROUP BY s.grade, s.strand_code
    ORDER BY s.grade, s.strand_code";
$stmt = $pdo->prepare($capacitySQL);
$stmt->execute([':sy' => $syId]);
$capacityData = $stmt->fetchAll();

// ── 6. Recent enrollees ─────────────────────────────────────
$recentSQL = "
    SELECT
        CONCAT(e.first_name, ' ', e.last_name) AS name,
        'SHS' AS level, e.grade, e.strand_code AS strand, e.gender, e.enrolled_at
    FROM enrollees e
    WHERE e.school_year_id = :sy AND e.status = 'Enrolled'
    ORDER BY e.enrolled_at DESC
    LIMIT 8";
$stmt = $pdo->prepare($recentSQL);
$stmt->execute([':sy' => $syId]);
$recent = $stmt->fetchAll();

// ── 7. School years list ─────────────────────────────────────
$syList = $pdo->query("SELECT id, label, is_active FROM school_years ORDER BY year_from DESC")->fetchAll();

// ── 8. Top sections ───────────────
$topSectionSQL = "
    SELECT s.section_name AS section, COUNT(*) AS count, e.grade, e.strand_code AS strand, 'SHS' AS level
    FROM enrollees e 
    LEFT JOIN sections s ON e.section_id = s.id
    $whereSQL AND e.section_id IS NOT NULL
    GROUP BY s.section_name, e.grade, e.strand_code
    ORDER BY count DESC
    LIMIT 10";
$stmt = $pdo->prepare($topSectionSQL);
$stmt->execute($params);
$topSections = $stmt->fetchAll();

// ── Assemble response ────────────────────────────────────────
json_out([
    'ok'          => true,
    'generated'   => date('Y-m-d H:i:s'),
    'school_year' => $syId,
    'filters'     => compact('level','strand','q'),
    'kpi'         => $kpi,
    'grades'      => $grades,
    'strands'     => $strands,
    'trend'       => [
        'labels' => $monthLabels,
        'all'    => $trendAll,
        'shs'    => $trendSHS,
        'jhs'    => $trendJHS,
    ],
    'capacity'    => $capacityData,
    'recent'      => $recent,
    'school_years'=> $syList,
    'top_sections'=> $topSections,
]);