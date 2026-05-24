# dashboard Tracker Link

Dashboard mandiri untuk memantau **pengunjung** & **klik CTA** dari 10 landing page.
Pure PHP + SQLite — **tinggal upload ke cPanel**, tanpa setup database manual,
tanpa Node, tanpa framework, tanpa logo pihak ketiga.

---

## 1. Cara Pasang di cPanel

1. **Upload** seluruh folder `dashboard-tracker/` ke `public_html/` (atau subdomain) di File Manager cPanel.
   - Contoh akhirnya: `https://situsmu.com/-tracker/`
2. **Pastikan PHP** versi 7.4+ aktif (default di hampir semua cPanel).
   - Ekstensi yang dipakai: `pdo_sqlite` (sudah aktif default).
3. **Pastikan folder `data/` writable** — biasanya otomatis. Jika perlu, set chmod ke `755`.
4. **Buka** `https://situsmu.com/dashboard-tracker/` — Anda akan melihat halaman login.
5. **Login** dengan password default `dashboard2026`.
6. **Ganti password** dengan mengedit file `config.php` baris `'admin_password' => '...'`.

> Folder `data/` dilindungi dengan `.htaccess` — tidak bisa diakses dari web,
> tapi dapat ditulisi oleh PHP sendiri.

---

## 2. Cara Pasang Tracker di Landing Page

Setiap container di dashboard punya tombol **"Embed Code"**.
Klik → copy → tempel **sebelum `</body>`** di file `index.html` landing page Anda.

Contoh untuk Tracker #1:

```html
<script src="https://situsmu.com/dashboard-tracker/t.php?id=1" async></script>
```

Tracker #2 menggunakan `?id=2`, dan seterusnya sampai `?id=10`.
Jadi setiap landing page hanya butuh **satu** baris `<script>`.

### Bagaimana Klik CTA Dihitung?

Setelah snippet terpasang, semua klik berikut otomatis dihitung sebagai **CTA klik**:

- Tombol/link dengan atribut `data-sg-cta`
- Tombol/link dengan class `cta` atau `sg-cta`
- Semua link `<a>` yang menuju **domain lain** (mis. `wa.me`, `t.me`, link affiliate)

Contoh:

```html
<a href="https://wa.me/628123456789" class="cta">Order via WhatsApp</a>
<a href="https://link.kamu.com/promo" data-sg-cta data-sg-label="hero-button">Daftar Sekarang</a>
<button data-sg-cta data-sg-label="cta-bawah">Klik Saya</button>
```

Atribut `data-sg-label` opsional — muncul di panel **Detail** sebagai keterangan klik.

### Manual API (opsional)

Anda juga bisa memicu event dari kode sendiri:

```html
<script>
    SGTracker.t1.cta('beli-paket-A'); // tracker #1, klik dengan label kustom
    SGTracker.t1.visit();             // hitung visit lagi (jarang dipakai)
</script>
```

---

## 3. Fitur Dashboard

- **10 container neon** — satu per landing page, dengan total pengunjung & klik CTA
- **Realtime** — auto-refresh tiap 6 detik
- **Konversi** — rasio CTA / pengunjung
- **Rename** tracker langsung dari kartu (klik ikon pensil)
- **Detail** per tracker — chart 24 jam terakhir + 30 event terakhir
- **Reset** data per tracker
- **Embed Code** copy-paste lengkap

---

## 4. Anti-Spam & Anti Double-count

- Visitor diidentifikasi via `localStorage` (fallback: hash IP+UA+tanggal)
- Visit yang sama dalam **30 menit** tidak dihitung lagi (bisa diatur di `config.php` → `visit_session_minutes`)
- CTA klik selalu dihitung (sengaja, untuk audit)

---

## 5. Struktur File

```
dashboard-tracker/
├── index.php          ← halaman login
├── dashboard.php      ← UI utama (10 container neon)
├── api.php            ← endpoint JSON internal (auth required)
├── track.php          ← endpoint penerima event dari landing page
├── t.php              ← serving JS tracker (di-embed di landing page)
├── logout.php
├── bootstrap.php      ← init DB, helper, session
├── config.php         ← password, brand, jumlah tracker
├── .htaccess
├── assets/
│   ├── style.css      ← UI neon kekinian
│   └── dashboard.js
└── data/
    ├── tracker.sqlite ← dibuat otomatis
    └── .htaccess      ← lock from web
```

---

## 6. Mengganti Password / Branding

Edit `config.php`:

```php
return [
    'admin_password' => 'GantiPasswordKuat123',
    'brand_name'     => 'dashboard',
    'brand_subtitle' => 'Tracker Link',
    'tracker_count'  => 10,   // bisa diubah
    'visit_session_minutes' => 30,
    // ...
];
```

Nama tiap container bisa di-rename dari dashboard (klik ikon pensil pada kartu).

---

## 7. Backup

Cukup unduh file `data/tracker.sqlite`. Itu adalah seluruh data Anda.

---
