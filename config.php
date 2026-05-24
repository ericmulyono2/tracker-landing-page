<?php
// Stargroup Tracker Link — konfigurasi utama
// Edit nilai di bawah ini setelah upload ke cPanel.

return [
    // Password untuk masuk ke dashboard. WAJIB diganti setelah deploy.
    'admin_password' => 'stargroup2026',

    // Salt rahasia (boleh dibiarkan default).
    'tracker_salt'   => 'sg-neon-tracker',

    // Zona waktu untuk timestamp di dashboard.
    'timezone'       => 'Asia/Jakarta',

    // Brand di header dashboard.
    'brand_name'     => 'Stargroup',
    'brand_subtitle' => 'Tracker Link',

    // Jumlah tracker (1..N). Default 10 (sesuai 10 landing page).
    'tracker_count'  => 10,

    // Nama default tiap tracker. Bisa di-rename langsung dari dashboard.
    'default_labels' => [
        'Landing Page 1',
        'Landing Page 2',
        'Landing Page 3',
        'Landing Page 4',
        'Landing Page 5',
        'Landing Page 6',
        'Landing Page 7',
        'Landing Page 8',
        'Landing Page 9',
        'Landing Page 10',
    ],

    // Dedupe visit: 0 = hitung SEMUA page load (paling akurat dengan tracker
    // sederhana lain). Set ke 30 kalau Anda tidak mau refresh dihitung 2x.
    'visit_session_minutes' => 0,
];
