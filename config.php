<?php
// ============================================================
//  api/config.php
//  Database connection — edit credentials to match your setup
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // ← change to your MySQL username
define('DB_PASS', '');            // ← change to your MySQL password
define('DB_NAME', 'bnis_db');
define('DB_PORT', 3306);

// Allow cross-origin requests (needed if dashboard is served
// from a different port than PHP during local development)
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                DB_HOST, DB_PORT, DB_NAME);
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

// Helper: send JSON and exit
function json_out(mixed $data): void {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    exit;
}

// Helper: safe integer from $_GET
function intParam(string $key, int $default = 0): int {
    return isset($_GET[$key]) ? (int)$_GET[$key] : $default;
}

// Helper: safe string from $_GET (whitelist)
function strParam(string $key, array $allowed, string $default = ''): string {
    $v = $_GET[$key] ?? $default;
    return in_array($v, $allowed, true) ? $v : $default;
}
