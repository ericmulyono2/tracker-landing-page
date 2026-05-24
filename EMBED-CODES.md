# Stargroup Tracker Link — 10 Embed Code Siap Pakai

> Ganti `https://situsmu.com/tracker-landing-page` di bawah ini dengan **URL aktual** lokasi folder ini di cPanel Anda.
>
> Contoh kalau folder Anda upload ke `public_html/tracker-landing-page/`, maka URL-nya `https://situsmu.com/tracker-landing-page` (tanpa `/` di akhir).
>
> Setiap snippet hanya tinggal **paste sebelum `</body>`** di file `index.html` landing page yang bersangkutan.
> Setelah dideploy, snippet yang sama (dengan URL otomatis) juga bisa di-copy dari dashboard via tombol **"Embed Code"** pada masing-masing kartu.

---

## 📌 Cara Kerja (SIMPLE)

Setelah snippet terpasang, **otomatis**:

- ✅ Setiap page load → **1 visit dihitung** (tanpa filter, tanpa dedupe)
- ✅ Setiap klik **tombol/link apapun** (`<a>` atau `<button>`) → **1 CTA dihitung**

Anda **tidak perlu menambah class atau atribut apapun** ke tombol CTA. Tombol yang sudah ada langsung terlacak.

### Optional — kalau ingin TIDAK menghitung tombol tertentu

Tambahkan atribut `data-sg-no-track` pada elemen yang ingin di-skip:

```html
<a href="#bagian-bawah" data-sg-no-track>Scroll ke bawah</a>
<button data-sg-no-track>Tombol non-CTA</button>
```

### Optional — kasih label custom untuk klik tertentu

```html
<a href="https://wa.me/628..." data-sg-label="hero-whatsapp">WhatsApp</a>
```

Label ini muncul di panel **Detail** dashboard, memudahkan analisa CTA mana yang paling sering diklik.

---

## 🚀 10 Snippet Tracker

### Tracker 1 — Landing Page 1
```html
<script src="https://situsmu.com/tracker-landing-page/t.php?id=1" async></script>
```

### Tracker 2 — Landing Page 2
```html
<script src="https://situsmu.com/tracker-landing-page/t.php?id=2" async></script>
```

### Tracker 3 — Landing Page 3
```html
<script src="https://situsmu.com/tracker-landing-page/t.php?id=3" async></script>
```

### Tracker 4 — Landing Page 4
```html
<script src="https://situsmu.com/tracker-landing-page/t.php?id=4" async></script>
```

### Tracker 5 — Landing Page 5
```html
<script src="https://situsmu.com/tracker-landing-page/t.php?id=5" async></script>
```

### Tracker 6 — Landing Page 6
```html
<script src="https://situsmu.com/tracker-landing-page/t.php?id=6" async></script>
```

### Tracker 7 — Landing Page 7
```html
<script src="https://situsmu.com/tracker-landing-page/t.php?id=7" async></script>
```

### Tracker 8 — Landing Page 8
```html
<script src="https://situsmu.com/tracker-landing-page/t.php?id=8" async></script>
```

### Tracker 9 — Landing Page 9
```html
<script src="https://situsmu.com/tracker-landing-page/t.php?id=9" async></script>
```

### Tracker 10 — Landing Page 10
```html
<script src="https://situsmu.com/tracker-landing-page/t.php?id=10" async></script>
```

---

## 💡 Contoh Pemasangan Lengkap di Landing Page

```html
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Promo Lebaran Spesial</title>
</head>
<body>

    <!-- Konten landing page Anda di sini -->
    <h1>Promo Lebaran 50% OFF!</h1>

    <!-- Semua tombol/link di bawah ini otomatis terhitung sebagai CTA -->
    <a href="https://wa.me/628123456789">Order via WhatsApp</a>
    <a href="https://shopee.co.id/yourstore">Beli di Shopee</a>
    <button>Hubungi Kami</button>

    <!-- Tambah data-sg-label kalau mau ada keterangan di panel Detail -->
    <a href="https://wa.me/628..." data-sg-label="hero-button">Order Sekarang</a>

    <!-- ============================== -->
    <!-- TRACKER STARGROUP — taruh sebelum </body> -->
    <script src="https://situsmu.com/tracker-landing-page/t.php?id=1" async></script>
</body>
</html>
```

Selesai. Buka dashboard di `https://situsmu.com/tracker-landing-page/` untuk melihat data realtime.
