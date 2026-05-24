<?php
// Stargroup Tracker Link — endpoint penerima event dari landing page
define('SG_TRACKER_INTERNAL', 1);
require __DIR__ . '/bootstrap.php';

$origin = isset($_SERVER['HTTP_ORIGIN']) ? trim($_SERVER['HTTP_ORIGIN']) : '';
if ($origin !== '' && preg_match('#^https?://[A-Za-z0-9\-\.]+(?::\d+)?$#', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$payload = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) $payload = $decoded;
    }
    $payload = array_merge($_POST, $payload);
} else {
    $payload = $_GET;
}

$trackerId = isset($payload['id']) ? (int)$payload['id'] : 0;
$type      = isset($payload['t']) ? strtolower((string)$payload['t']) : '';
$ref       = isset($payload['r']) ? (string)$payload['r'] : '';
$visitor   = isset($payload['v']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$payload['v']) : '';
$metaUrl   = isset($payload['u']) ? (string)$payload['u'] : '';
$metaLabel = isset($payload['l']) ? (string)$payload['l'] : '';

$wantPixel = isset($_GET['p']) && $_GET['p'] == '1';

if ($trackerId <= 0 || !in_array($type, ['visit', 'cta'], true)) {
    if ($wantPixel) {
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }
    sg_json(['ok' => false, 'error' => 'invalid'], 400);
}

$pdo = sg_db();
$tracker = $pdo->prepare('SELECT id FROM trackers WHERE id = :id');
$tracker->execute([':id' => $trackerId]);
if (!$tracker->fetchColumn()) {
    if ($wantPixel) {
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }
    sg_json(['ok' => false, 'error' => 'tracker_not_found'], 404);
}

$ip = sg_client_ip();
$ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : '';

if ($visitor === '') {
    $visitor = substr(sha1(($ip ?: 'noip') . '|' . $ua . '|' . date('Ymd')), 0, 16);
}

$ua  = sg_truncate($ua,  500);
$ref = sg_truncate($ref, 500);

$meta = json_encode([
    'u' => sg_truncate($metaUrl,   500),
    'l' => sg_truncate($metaLabel, 200),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if ($type === 'visit') {
    $minutes = (int)($GLOBALS['SG_CONFIG']['visit_session_minutes'] ?? 0);
    if ($minutes > 0) {
        $check = $pdo->prepare(
            "SELECT 1 FROM events
              WHERE tracker_id = :id AND visitor_id = :v AND type = 'visit'
                AND datetime(created_at) >= datetime('now', :win)
              LIMIT 1"
        );
        $check->execute([
            ':id'  => $trackerId,
            ':v'   => $visitor,
            ':win' => '-' . $minutes . ' minutes',
        ]);
        if ($check->fetchColumn()) {
            if ($wantPixel) {
                header('Content-Type: image/gif');
                echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
                exit;
            }
            sg_json(['ok' => true, 'deduped' => true]);
        }
    }
}

$ins = $pdo->prepare(
    'INSERT INTO events (tracker_id, type, visitor_id, ip, ua, ref, meta)
     VALUES (:id, :t, :v, :ip, :ua, :ref, :meta)'
);
$ins->execute([
    ':id'   => $trackerId,
    ':t'    => $type,
    ':v'    => $visitor,
    ':ip'   => $ip,
    ':ua'   => $ua,
    ':ref'  => $ref,
    ':meta' => $meta,
]);

if ($wantPixel) {
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

sg_json(['ok' => true]);
