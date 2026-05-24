<?php
// Stargroup Tracker Link — Login / Landing
define('SG_TRACKER_INTERNAL', 1);
require __DIR__ . '/bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = $_POST['password'] ?? '';
    if (sg_check_password($pw)) {
        session_regenerate_id(true);
        $_SESSION['sg_authed'] = true;
        $_SESSION['sg_csrf']   = bin2hex(random_bytes(16));
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Password salah.';
}

if (sg_is_authed()) {
    header('Location: dashboard.php');
    exit;
}

$brand    = htmlspecialchars($SG_CONFIG['brand_name']     ?? 'Stargroup', ENT_QUOTES, 'UTF-8');
$subtitle = htmlspecialchars($SG_CONFIG['brand_subtitle'] ?? 'Tracker Link', ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?php echo $brand . ' ' . $subtitle; ?> — Login</title>
<link rel="stylesheet" href="assets/style.css?v=1">
</head>
<body class="auth-body">
<div class="auth-bg"><span></span><span></span><span></span></div>
<main class="auth-card">
    <div class="auth-brand">
        <div class="auth-logo">
            <svg viewBox="0 0 64 64" width="44" height="44" aria-hidden="true">
                <defs>
                    <linearGradient id="sg-grad" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#22d3ee"/>
                        <stop offset="0.5" stop-color="#a855f7"/>
                        <stop offset="1" stop-color="#f472b6"/>
                    </linearGradient>
                </defs>
                <path fill="url(#sg-grad)" d="M32 4l5.6 17.2 18.1.1-14.7 10.6 5.6 17.2L32 38.6l-14.6 10.5 5.6-17.2L8.3 21.3l18.1-.1z"/>
            </svg>
        </div>
        <div class="auth-title">
            <h1><?php echo $brand; ?></h1>
            <p><?php echo $subtitle; ?></p>
        </div>
    </div>

    <form method="post" autocomplete="off" class="auth-form">
        <label for="pw">Password Dashboard</label>
        <input id="pw" type="password" name="password" placeholder="••••••••••" required autofocus>
        <?php if ($error): ?>
            <div class="auth-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <button type="submit" class="neon-btn">Masuk</button>
    </form>

    <p class="auth-hint">Privat. Akses hanya untuk admin.</p>
</main>
</body>
</html>
