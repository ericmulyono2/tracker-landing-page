<?php
// Stargroup Tracker Link — bootstrap (DB init, helpers, auth)

if (!defined('SG_TRACKER_INTERNAL') && basename($_SERVER['SCRIPT_NAME']) === 'bootstrap.php') {
    http_response_code(403);
    exit('Forbidden');
}

@error_reporting(0);
@ini_set('display_errors', '0');

if (session_status() === PHP_SESSION_NONE) {
    session_name('SGTRACKERSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    @session_start();
}

$SG_BASE_DIR = __DIR__;
$SG_CONFIG   = require $SG_BASE_DIR . '/config.php';

if (!empty($SG_CONFIG['timezone'])) {
    @date_default_timezone_set($SG_CONFIG['timezone']);
}

$SG_DATA_DIR = $SG_BASE_DIR . '/data';
if (!is_dir($SG_DATA_DIR)) {
    @mkdir($SG_DATA_DIR, 0755, true);
}

// Hardening: jangan biarkan folder data dilihat dari web walau .htaccess hilang.
$dataHtaccess = $SG_DATA_DIR . '/.htaccess';
if (!file_exists($dataHtaccess)) {
    @file_put_contents($dataHtaccess, "Require all denied\nDeny from all\n");
}
$dataIndex = $SG_DATA_DIR . '/index.html';
if (!file_exists($dataIndex)) {
    @file_put_contents($dataIndex, '');
}

function sg_db() {
    global $SG_DATA_DIR;
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dbFile = $SG_DATA_DIR . '/tracker.sqlite';
    try {
        $pdo = new PDO('sqlite:' . $dbFile);
    } catch (Throwable $e) {
        http_response_code(500);
        echo 'Database error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        exit;
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA synchronous = NORMAL');
    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec("CREATE TABLE IF NOT EXISTS trackers (
        id          INTEGER PRIMARY KEY,
        label       TEXT NOT NULL,
        url         TEXT,
        created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        tracker_id  INTEGER NOT NULL,
        type        TEXT NOT NULL,
        visitor_id  TEXT,
        ip          TEXT,
        ua          TEXT,
        ref         TEXT,
        meta        TEXT,
        created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_events_tracker_type ON events(tracker_id, type)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_events_created     ON events(created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_events_visitor     ON events(tracker_id, visitor_id, type, created_at)");

    return $pdo;
}

function sg_seed_trackers() {
    global $SG_CONFIG;
    $pdo = sg_db();
    $count = max(1, (int)($SG_CONFIG['tracker_count'] ?? 10));
    $labels = $SG_CONFIG['default_labels'] ?? [];
    $existing = $pdo->query('SELECT id FROM trackers')->fetchAll(PDO::FETCH_COLUMN);
    $existing = array_map('intval', $existing);

    $stmt = $pdo->prepare('INSERT INTO trackers (id, label) VALUES (:id, :label)');
    for ($i = 1; $i <= $count; $i++) {
        if (in_array($i, $existing, true)) continue;
        $label = $labels[$i - 1] ?? ('Tracker ' . $i);
        $stmt->execute([':id' => $i, ':label' => $label]);
    }
}

sg_seed_trackers();

function sg_is_authed() {
    return !empty($_SESSION['sg_authed']);
}

function sg_require_auth() {
    if (!sg_is_authed()) {
        header('Location: index.php');
        exit;
    }
}

function sg_check_password($input) {
    global $SG_CONFIG;
    $expected = (string)($SG_CONFIG['admin_password'] ?? '');
    if ($expected === '') return false;
    return hash_equals($expected, (string)$input);
}

function sg_csrf_token() {
    if (empty($_SESSION['sg_csrf'])) {
        $_SESSION['sg_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['sg_csrf'];
}

function sg_csrf_check($t) {
    return is_string($t) && !empty($_SESSION['sg_csrf']) && hash_equals($_SESSION['sg_csrf'], $t);
}

function sg_client_ip() {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '';
}

function sg_base_url() {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path  = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $proto . '://' . $host . $path;
}

function sg_truncate($s, $max) {
    $s = (string)$s;
    if (function_exists('mb_substr')) return mb_substr($s, 0, $max, 'UTF-8');
    return substr($s, 0, $max);
}

function sg_json($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
