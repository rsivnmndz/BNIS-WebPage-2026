<?php
// ============================================================
//  api/analytics.php
//  Main endpoint — returns all dashboard data as one JSON blob
//
//  GET params:
//    sy     = school_year_id  (default: active year)
//    level  = all | Elementary | JHS | SHS
//    strand = all | STEM | ABM | HUMSS | TVL | GAS
//    q      = search string (grade name / strand)
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

// Base WHERE
$where  = ['e.school_year_id = :sy', "e.status = 'Enrolled'"];
$params = [':sy' => $syId];

if ($level !== 'all') {
    $where[] = 'e.level = :level';
    $params[':level'] = $level;
}
if ($strand !== 'all') {
    $where[] = 'e.strand = :strand';
    $params[':strand'] = $strand;
}
if ($q !== '') {
    $where[] = "(CONCAT('Grade ', e.grade) LIKE :q OR e.strand LIKE :q OR e.section LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

// ── 1. KPI totals ───────────────────────────────────────────
$kpiSQL = "
    SELECT
        COUNT(*) AS total,
        SUM(level = 'Elementary') AS elem,
        SUM(level = 'JHS')        AS jhs,
        SUM(level = 'SHS')        AS shs,
        SUM(gender = 'Male')      AS male,
        SUM(gender = 'Female')    AS female
    FROM enrollees e $whereSQL";
$stmt = $pdo->prepare($kpiSQL);
$stmt->execute($params);
$kpi = $stmt->fetch();

// ── 2. Grade breakdown ──────────────────────────────────────
$gradeSQL = "
    SELECT
        e.level,
        e.grade,
        CONCAT('Grade ', e.grade) AS grade_label,
        COUNT(*) AS count,
        SUM(gender='Male') AS male,
        SUM(gender='Female') AS female
    FROM enrollees e $whereSQL
    GROUP BY e.level, e.grade
    ORDER BY e.grade";
$stmt = $pdo->prepare($gradeSQL);
$stmt->execute($params);
$grades = $stmt->fetchAll();

// ── 3. Strand breakdown (SHS only) ─────────────────────────
// We always show full SHS strand data regardless of level filter for the doughnut
$strandSQL = "
    SELECT
        e.strand,
        e.grade,
        COUNT(*) AS count,
        SUM(gender='Male') AS male,
        SUM(gender='Female') AS female,
        COUNT(DISTINCT e.section) AS sections
    FROM enrollees e
    WHERE e.school_year_id = :sy AND e.status = 'Enrolled' AND e.level = 'SHS'
    " . ($strand !== 'all' ? "AND e.strand = :strand2" : "") . "
    GROUP BY e.strand, e.grade
    ORDER BY e.strand, e.grade";

$strandParams = [':sy' => $syId];
if ($strand !== 'all') $strandParams[':strand2'] = $strand;
$stmt = $pdo->prepare($strandSQL);
$stmt->execute($strandParams);
$strands = $stmt->fetchAll();

// ── 4. Monthly trend ────────────────────────────────────────
$monthSQL = "
    SELECT
        MONTH(enrolled_at)   AS mon,
        YEAR(enrolled_at)    AS yr,
        e.level,
        COUNT(*) AS count
    FROM enrollees e
    WHERE e.school_year_id = :sy AND e.status = 'Enrolled'
    GROUP BY YEAR(enrolled_at), MONTH(enrolled_at), e.level
    ORDER BY yr, mon";
$stmt = $pdo->prepare($monthSQL);
$stmt->execute([':sy' => $syId]);
$monthlyRaw = $stmt->fetchAll();

// Build 12-slot arrays (Jun=1 → May=12 in school-year order)
$monthOrder = [6,7,8,9,10,11,12,1,2,3,4,5];
$monthLabels = ['Jun','Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar','Apr','May'];
$trendAll = array_fill(0, 12, 0);
$trendSHS = array_fill(0, 12, 0);
$trendJHS = array_fill(0, 12, 0);

foreach ($monthlyRaw as $r) {
    $idx = array_search((int)$r['mon'], $monthOrder);
    if ($idx !== false) {
        $trendAll[$idx] += (int)$r['count'];
        if ($r['level'] === 'SHS') $trendSHS[$idx] += (int)$r['count'];
        if ($r['level'] === 'JHS') $trendJHS[$idx] += (int)$r['count'];
    }
}

// ── 5. Section capacity ─────────────────────────────────────
$capacitySQL = "
    SELECT
        s.grade,
        s.strand,
        SUM(s.capacity) AS capacity,
        COUNT(DISTINCT s.id) AS section_count
    FROM sections s
    WHERE s.school_year_id = :sy
    GROUP BY s.grade, s.strand
    ORDER BY s.grade, s.strand";
$stmt = $pdo->prepare($capacitySQL);
$stmt->execute([':sy' => $syId]);
$capacityData = $stmt->fetchAll();

// ── 6. Recent enrollees ─────────────────────────────────────
$recentSQL = "
    SELECT
        CONCAT(first_name, ' ', last_name) AS name,
        level, grade, strand, gender, enrolled_at
    FROM enrollees e
    WHERE e.school_year_id = :sy AND e.status = 'Enrolled'
    ORDER BY enrolled_at DESC
    LIMIT 8";
$stmt = $pdo->prepare($recentSQL);
$stmt->execute([':sy' => $syId]);
$recent = $stmt->fetchAll();

// ── 7. School years list ─────────────────────────────────────
$syList = $pdo->query("SELECT id, label, is_active FROM school_years ORDER BY year_from DESC")->fetchAll();

// ── 8. Top sections ─────────────────────────────────────────
$topSectionSQL = "
    SELECT section, COUNT(*) AS count, grade, strand, level
    FROM enrollees e $whereSQL AND e.section IS NOT NULL
    GROUP BY section, grade, strand, level
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
