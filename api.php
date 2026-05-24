<?php
// Stargroup Tracker Link — API JSON internal untuk dashboard.
define('SG_TRACKER_INTERNAL', 1);
require __DIR__ . '/bootstrap.php';

if (!sg_is_authed()) sg_json(['ok' => false, 'error' => 'unauthorized'], 401);

$action = $_GET['action'] ?? $_POST['action'] ?? 'summary';
$pdo    = sg_db();

if ($action === 'summary') {
    $rows = $pdo->query(
        "SELECT t.id, t.label, t.url,
                COALESCE(SUM(CASE WHEN e.type='visit' THEN 1 ELSE 0 END), 0) AS visits,
                COALESCE(SUM(CASE WHEN e.type='cta'   THEN 1 ELSE 0 END), 0) AS ctas,
                COALESCE(SUM(CASE WHEN e.type='visit' AND date(e.created_at, 'localtime') = date('now','localtime') THEN 1 ELSE 0 END), 0) AS visits_today,
                COALESCE(SUM(CASE WHEN e.type='cta'   AND date(e.created_at, 'localtime') = date('now','localtime') THEN 1 ELSE 0 END), 0) AS ctas_today,
                MAX(e.created_at) AS last_event
           FROM trackers t
           LEFT JOIN events e ON e.tracker_id = t.id
          GROUP BY t.id
          ORDER BY t.id ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    // duplicates per tracker = jumlah pengunjung unik yang datang lebih dari 1 kali
    $dupRows = $pdo->query(
        "SELECT tracker_id, COUNT(*) AS dups FROM (
            SELECT tracker_id, visitor_id
              FROM events
             WHERE type='visit' AND IFNULL(visitor_id,'') <> ''
             GROUP BY tracker_id, visitor_id
            HAVING COUNT(*) > 1
         ) GROUP BY tracker_id"
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    // unique visitors per tracker
    $uniqRows = $pdo->query(
        "SELECT tracker_id, COUNT(DISTINCT visitor_id) AS u
           FROM events
          WHERE type='visit' AND IFNULL(visitor_id,'') <> ''
          GROUP BY tracker_id"
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    $totalVisits = 0; $totalCta = 0; $totalVisitsToday = 0; $totalCtaToday = 0; $totalDup = 0; $totalUnique = 0;
    foreach ($rows as &$r) {
        $r['duplicates'] = (int)($dupRows[$r['id']] ?? 0);
        $r['unique']     = (int)($uniqRows[$r['id']] ?? 0);
        $totalVisits      += (int)$r['visits'];
        $totalCta         += (int)$r['ctas'];
        $totalVisitsToday += (int)$r['visits_today'];
        $totalCtaToday    += (int)$r['ctas_today'];
        $totalDup         += $r['duplicates'];
        $totalUnique      += $r['unique'];
    }
    unset($r);

    $sparkRows = $pdo->query(
        "SELECT date(created_at, 'localtime') AS d,
                SUM(CASE WHEN type='visit' THEN 1 ELSE 0 END) AS v,
                SUM(CASE WHEN type='cta'   THEN 1 ELSE 0 END) AS c
           FROM events
          WHERE datetime(created_at) >= datetime('now','-13 days')
          GROUP BY d ORDER BY d ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    sg_json([
        'ok'       => true,
        'trackers' => $rows,
        'totals'   => [
            'visits'       => $totalVisits,
            'ctas'         => $totalCta,
            'visits_today' => $totalVisitsToday,
            'ctas_today'   => $totalCtaToday,
            'duplicates'   => $totalDup,
            'unique'       => $totalUnique,
            'conversion'   => $totalVisits > 0 ? round(($totalCta / $totalVisits) * 100, 2) : 0,
        ],
        'spark'    => $sparkRows,
        'now'      => date('Y-m-d H:i:s'),
    ]);
}

if ($action === 'detail') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) sg_json(['ok' => false, 'error' => 'bad_id'], 400);

    $tr = $pdo->prepare('SELECT id, label, url FROM trackers WHERE id = :id');
    $tr->execute([':id' => $id]);
    $info = $tr->fetch(PDO::FETCH_ASSOC);
    if (!$info) sg_json(['ok' => false, 'error' => 'not_found'], 404);

    $hourly = $pdo->prepare(
        "SELECT strftime('%Y-%m-%d %H:00', created_at, 'localtime') AS h,
                SUM(CASE WHEN type='visit' THEN 1 ELSE 0 END) AS v,
                SUM(CASE WHEN type='cta'   THEN 1 ELSE 0 END) AS c
           FROM events
          WHERE tracker_id = :id
            AND datetime(created_at) >= datetime('now','-23 hours')
          GROUP BY h ORDER BY h ASC"
    );
    $hourly->execute([':id' => $id]);

    $recent = $pdo->prepare(
        "SELECT type, ref, ip, ua, meta, created_at
           FROM events
          WHERE tracker_id = :id
          ORDER BY id DESC LIMIT 30"
    );
    $recent->execute([':id' => $id]);

    sg_json([
        'ok'     => true,
        'info'   => $info,
        'hourly' => $hourly->fetchAll(PDO::FETCH_ASSOC),
        'recent' => $recent->fetchAll(PDO::FETCH_ASSOC),
    ]);
}

if ($action === 'rename') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') sg_json(['ok' => false, 'error' => 'method'], 405);
    $token = $_POST['csrf'] ?? '';
    if (!sg_csrf_check($token)) sg_json(['ok' => false, 'error' => 'csrf'], 403);

    $id    = (int)($_POST['id'] ?? 0);
    $label = trim((string)($_POST['label'] ?? ''));
    $url   = trim((string)($_POST['url']   ?? ''));
    if ($id <= 0 || $label === '') sg_json(['ok' => false, 'error' => 'bad_input'], 400);
    $label = sg_truncate($label, 80);
    $url   = sg_truncate($url,   300);

    $stmt = $pdo->prepare('UPDATE trackers SET label = :l, url = :u WHERE id = :id');
    $stmt->execute([':l' => $label, ':u' => $url, ':id' => $id]);
    sg_json(['ok' => true]);
}

if ($action === 'reset') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') sg_json(['ok' => false, 'error' => 'method'], 405);
    $token = $_POST['csrf'] ?? '';
    if (!sg_csrf_check($token)) sg_json(['ok' => false, 'error' => 'csrf'], 403);

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) sg_json(['ok' => false, 'error' => 'bad_input'], 400);

    $stmt = $pdo->prepare('DELETE FROM events WHERE tracker_id = :id');
    $stmt->execute([':id' => $id]);
    sg_json(['ok' => true, 'deleted' => $stmt->rowCount()]);
}

sg_json(['ok' => false, 'error' => 'unknown_action'], 400);
