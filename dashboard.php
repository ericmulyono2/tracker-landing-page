<?php
// Stargroup Tracker Link — Dashboard
define('SG_TRACKER_INTERNAL', 1);
require __DIR__ . '/bootstrap.php';
sg_require_auth();

$pdo = sg_db();
$trackers = $pdo->query('SELECT id, label, url FROM trackers ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);

$brand    = htmlspecialchars($SG_CONFIG['brand_name']     ?? 'Stargroup', ENT_QUOTES, 'UTF-8');
$subtitle = htmlspecialchars($SG_CONFIG['brand_subtitle'] ?? 'Tracker Link', ENT_QUOTES, 'UTF-8');
$baseUrl  = sg_base_url();
$csrf     = sg_csrf_token();
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?php echo $brand . ' ' . $subtitle; ?> — Dashboard</title>
<link rel="stylesheet" href="assets/style.css?v=1">
</head>
<body class="dash-body">
<div class="bg-grid"></div>
<div class="bg-orb orb-a"></div>
<div class="bg-orb orb-b"></div>
<div class="bg-orb orb-c"></div>

<header class="topbar">
    <div class="brand">
        <div class="brand-mark">
            <svg viewBox="0 0 64 64" width="34" height="34" aria-hidden="true">
                <defs>
                    <linearGradient id="b-grad" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#22d3ee"/>
                        <stop offset="0.5" stop-color="#a855f7"/>
                        <stop offset="1" stop-color="#f472b6"/>
                    </linearGradient>
                </defs>
                <path fill="url(#b-grad)" d="M32 4l5.6 17.2 18.1.1-14.7 10.6 5.6 17.2L32 38.6l-14.6 10.5 5.6-17.2L8.3 21.3l18.1-.1z"/>
            </svg>
        </div>
        <div>
            <h1><?php echo $brand; ?> <span><?php echo $subtitle; ?></span></h1>
            <p>Live tracker — pengunjung &amp; klik CTA</p>
        </div>
    </div>
    <div class="topbar-actions">
        <span class="status-dot" id="liveDot" title="Live"></span>
        <span class="status-text">Realtime</span>
        <a class="ghost-btn" href="logout.php">Keluar</a>
    </div>
</header>

<section class="metrics">
    <div class="metric metric-cyan">
        <div class="metric-label">Total Pengunjung</div>
        <div class="metric-value" id="m-visits">—</div>
        <div class="metric-sub">Hari ini: <b id="m-visits-today">—</b></div>
    </div>
    <div class="metric metric-amber">
        <div class="metric-label">Total Duplikat</div>
        <div class="metric-value" id="m-dups">—</div>
        <div class="metric-sub">Pengunjung unik yang balik lagi</div>
    </div>
    <div class="metric metric-pink">
        <div class="metric-label">Total Klik CTA</div>
        <div class="metric-value" id="m-ctas">—</div>
        <div class="metric-sub">Hari ini: <b id="m-ctas-today">—</b></div>
    </div>
    <div class="metric metric-purple">
        <div class="metric-label">Konversi Rata-rata</div>
        <div class="metric-value" id="m-conv">—</div>
        <div class="metric-sub">CTA / Visit (semua tracker)</div>
    </div>
    <div class="metric metric-lime">
        <div class="metric-label">Tracker Aktif</div>
        <div class="metric-value" id="m-active">—</div>
        <div class="metric-sub">Dari <?php echo count($trackers); ?> container</div>
    </div>
</section>

<section class="cards" id="cards">
<?php foreach ($trackers as $tr):
    $id = (int)$tr['id'];
    $palette = ['cyan','pink','purple','lime','orange','blue','rose','emerald','amber','violet'];
    $clr = $palette[($id - 1) % count($palette)];
    $label = htmlspecialchars($tr['label'], ENT_QUOTES, 'UTF-8');
    $url   = htmlspecialchars($tr['url']   ?? '', ENT_QUOTES, 'UTF-8');
?>
    <article class="card neon-<?php echo $clr; ?>" data-id="<?php echo $id; ?>">
        <header>
            <span class="card-id">#<?php echo str_pad($id, 2, '0', STR_PAD_LEFT); ?></span>
            <h3 class="card-label" data-label><?php echo $label; ?></h3>
            <button class="icon-btn" data-edit title="Rename">
                <svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
            </button>
        </header>

        <div class="stats stats-3">
            <div class="stat">
                <div class="stat-label">Pengunjung</div>
                <div class="stat-val stat-primary" data-visits>—</div>
                <div class="stat-sub">Hari ini <b data-visits-today>—</b></div>
            </div>
            <div class="stat">
                <div class="stat-label">Duplikat</div>
                <div class="stat-val stat-amber" data-duplicates>—</div>
                <div class="stat-sub">Unik balik lagi</div>
            </div>
            <div class="stat">
                <div class="stat-label">Klik CTA</div>
                <div class="stat-val" data-ctas>—</div>
                <div class="stat-sub">Hari ini <b data-ctas-today>—</b></div>
            </div>
        </div>

        <div class="bar"><span data-bar></span></div>
        <div class="card-foot">
            <span class="conv">Konversi <b data-conv>—</b></span>
            <span class="last" data-last>Belum ada aktivitas</span>
        </div>

        <div class="card-tools">
            <button class="chip" data-snippet>Embed Code</button>
            <button class="chip" data-detail>Detail</button>
            <button class="chip danger" data-reset>Reset</button>
        </div>
    </article>
<?php endforeach; ?>
</section>

<footer class="foot">
    <span><?php echo $brand; ?> <?php echo $subtitle; ?> · <?php echo date('Y'); ?></span>
    <span id="last-updated"></span>
</footer>

<!-- Embed code modal -->
<div class="modal" id="snippetModal" hidden>
    <div class="modal-card">
        <header>
            <h3 id="snippetTitle">Embed Code</h3>
            <button class="icon-btn" data-close>&times;</button>
        </header>
        <p>Salin <b>satu</b> baris berikut, paste sebelum <code>&lt;/body&gt;</code> di landing page Anda.</p>
        <div class="code-wrap">
            <pre id="snippetCode"></pre>
            <button class="neon-btn small" id="copySnippet">Copy</button>
        </div>
        <p class="mini">Cara hitung CTA klik: tambahkan atribut <code>data-sg-cta</code> ke tombol/link, atau pakai class <code>cta</code>. Semua link ke domain lain otomatis dihitung sebagai CTA.</p>
        <div class="example">
            <div class="example-title">Contoh tombol CTA:</div>
            <pre>&lt;a href="https://wa.me/62..." class="cta"&gt;Order Sekarang&lt;/a&gt;
&lt;a href="https://link.kamu.com" data-sg-cta data-sg-label="hero-button"&gt;Daftar&lt;/a&gt;</pre>
        </div>
    </div>
</div>

<!-- Edit modal -->
<div class="modal" id="editModal" hidden>
    <div class="modal-card">
        <header>
            <h3>Ubah Tracker</h3>
            <button class="icon-btn" data-close>&times;</button>
        </header>
        <form id="editForm">
            <input type="hidden" name="id" id="editId">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <label>Nama Tracker</label>
            <input type="text" name="label" id="editLabel" maxlength="80" required>
            <label>URL Landing Page <span class="mini">(opsional)</span></label>
            <input type="url" name="url" id="editUrl" maxlength="300" placeholder="https://contoh.com/landingpage">
            <div class="modal-actions">
                <button type="button" class="ghost-btn" data-close>Batal</button>
                <button type="submit" class="neon-btn">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Detail modal -->
<div class="modal" id="detailModal" hidden>
    <div class="modal-card wide">
        <header>
            <h3 id="detailTitle">Detail Tracker</h3>
            <button class="icon-btn" data-close>&times;</button>
        </header>
        <div class="detail-grid">
            <div>
                <div class="mini">Aktivitas 24 jam terakhir</div>
                <div class="hourly" id="hourlyChart"></div>
            </div>
            <div>
                <div class="mini">Event terbaru</div>
                <div class="recent-list" id="recentList"></div>
            </div>
        </div>
    </div>
</div>

<script>
window.SG_BOOT = {
    csrf:   <?php echo json_encode($csrf); ?>,
    base:   <?php echo json_encode($baseUrl, JSON_UNESCAPED_SLASHES); ?>,
    api:    'api.php',
};
</script>
<script src="assets/dashboard.js?v=1"></script>
</body>
</html>
