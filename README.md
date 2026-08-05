# SiPinjam INSTIKI - Sistem Peminjaman Barang INSTIKI

**SiPinjam INSTIKI** adalah aplikasi web untuk mengelola proses peminjaman barang/inventaris di lingkungan kampus INSTIKI. Project ini dibuat sebagai implementasi tugas proyek mata kuliah Human Computer Interaction (HCI) & Manajemen Proyek Sistem Informasi (ManPro SI)  bukan merupakan sistem resmi milik kampus.

🔗 **Demo:** http://202.155.16.96

---

## 📋 Fitur

### Untuk Pengguna (Mahasiswa/Staff)
- Registrasi & login akun
- Melihat katalog barang yang tersedia
- Melihat detail barang beserta kalender ketersediaan
- Menambahkan barang ke keranjang peminjaman
- Mengajukan permintaan peminjaman
- Melihat status & riwayat transaksi peminjaman
- Melakukan pengembalian barang

### Untuk Admin
- Dashboard admin dengan ringkasan aktivitas
- Approve / reject / selesaikan pengajuan peminjaman
- Kelola data barang (tambah, edit, hapus)
- Laporan peminjaman (dengan fitur export)
- Kalender peminjaman keseluruhan

---

## 🛠️ Tech Stack

| Kategori | Teknologi |
|---|---|
| Backend | Laravel 13 (PHP 8.3) |
| Database | MySQL |
| Frontend | Blade, Tailwind CSS 4, Alpine.js |
| Build Tool | Vite |
| Kalender | FullCalendar |
| Hosting | VPS (Ubuntu 24.04, Nginx) |
| CI/CD | GitHub Actions (auto-deploy) |

---

## 👥 Anggota Kelompok

| Nama | NIM | Tugas |
|---|---|---|
| [Kadek Girendra Ari Astika] | 2401010325 | [Alur User (Login , Resgister, Dashboard, Katalog, Detail barang, Form Peminjaman, Form pengembalian, Status Pinjaman,Riwayat Transaksi)] |
| [I Wayan Yordi Ari Muliantara ] | [2401010306] | [Alur Admin (Dashboard, Aprrove, Reject, Inventaris, Laporan Peminjaman, Kalender Peminjaman] |


---

## 🚀 Instalasi Lokal

Panduan ini untuk menjalankan project di komputer lokal (development), bukan di server produksi.

### Prasyarat
- PHP >= 8.3
- Composer
- Node.js & npm
- MySQL

### Langkah-langkah

```bash
# 1. Clone repository
git clone https://github.com/<username-pemilik-repo>/instiki-peminjaman-barang.git
cd instiki-peminjaman-barang

# 2. Install dependency PHP
composer install

# 3. Install dependency frontend
npm install

# 4. Salin file environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Sesuaikan konfigurasi database di file .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=instiki_peminjaman_barang
DB_USERNAME=root
DB_PASSWORD=

# 7. Jalankan migrasi database
php artisan migrate --seed

# 8. Build asset frontend
npm run build

# 9. Jalankan server lokal
php artisan serve
```

Aplikasi bisa diakses di `http://127.0.0.1:8000`

---

## 📁 Struktur Project (Ringkas)

```
app/
├── Http/Controllers/       # Controller user & admin
├── Models/                 # Model Eloquent
database/
├── migrations/             # Struktur tabel database
├── seeders/                 # Data awal (seeding)
resources/
├── views/                  # Tampilan Blade
├── js/ & css/               # Asset frontend (Vite)
routes/
├── web.php                 # Definisi routing
```

---

## 📸 Screenshot

_(akan ditambahkan)_

---

## 📄 Lisensi

Project ini dibuat untuk keperluan akademik/tugas kampus.
