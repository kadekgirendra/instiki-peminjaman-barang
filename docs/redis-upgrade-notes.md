# Catatan: Rencana Upgrade ke Redis (Cache & Queue)

Dokumen ini mencatat evaluasi teknis untuk memindahkan cache dan queue
aplikasi dari driver `database` (MySQL) ke Redis, sebagai referensi kalau
sistem ini suatu saat perlu di-scale untuk jumlah pengguna yang lebih besar.

## Kondisi Saat Ini

Konfigurasi di `.env`:
CACHE_STORE=database
QUEUE_CONNECTION=database


Setiap operasi cache (`Cache::remember()`, dipakai di `Item::categories()`
dan laporan admin) dan setiap job queue (notifikasi email approve/reject)
disimpan sebagai baris di tabel MySQL (`cache`, `jobs`). Ini valid dan
cukup untuk skala pengguna saat ini (civitas kampus, ratusan mahasiswa
aktif), tapi punya keterbatasan yang perlu disadari.

## Kenapa Redis Jadi Kandidat Upgrade

1. **Kecepatan** — Redis adalah in-memory data store. Baca/tulis cache
   lewat Redis pada dasarnya adalah operasi memori, jauh lebih cepat
   dibanding query SQL ke disk (meski MySQL juga punya buffer pool sendiri,
   overhead-nya tetap lebih besar dari akses memori langsung).

2. **Mengurangi beban MySQL** — Saat ini, setiap baca/tulis cache dan
   setiap job queue = 1 query tambahan ke MySQL yang sama yang juga
   melayani seluruh data aplikasi (users, items, transactions). Pada beban
   tinggi, ini bisa jadi bottleneck yang tidak perlu — cache/queue idealnya
   tidak berebut resource dengan data transaksional utama.

3. **Fitur bawaan yang lebih matang** — Redis mendukung expiry per-key
   secara native (tidak perlu query `DELETE WHERE expiration < NOW()`
   seperti cache driver database), dan punya struktur data (list, sorted
   set) yang lebih efisien untuk kebutuhan queue dibanding tabel SQL biasa.

## Apa yang Perlu Diubah (Kalau Upgrade Ini Dilakukan)

1. Install extension PHP `redis` (atau `predis` sebagai alternatif pure-PHP
   tanpa perlu compile extension) di server.
2. Install & jalankan Redis server di VPS (`apt install redis-server`).
3. Ubah `.env`:

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

4. **Tidak ada perubahan kode aplikasi** yang diperlukan — ini keuntungan
   besar dari cara Laravel mengabstraksi cache & queue lewat driver. Semua
   pemanggilan `Cache::remember()`, `Mail::to()->send()` (yang otomatis
   masuk antrian lewat `ShouldQueue`), dan `DB::table('jobs')` konsepnya
   tetap sama — cuma "pipa" di baliknya yang berganti.
5. Setup **Supervisor** di VPS untuk menjaga `php artisan queue:work`
   tetap berjalan 24/7 (catatan: ini juga berlaku untuk queue driver
   `database` yang dipakai saat ini — bukan kebutuhan baru khusus Redis).

## Kapan Upgrade Ini Layak Dilakukan

Belum mendesak untuk skala penggunaan saat ini. Pertimbangkan upgrade
kalau salah satu terjadi:
- Jumlah user aktif bersamaan meningkat signifikan (ratusan → ribuan).
- Tabel `cache`/`jobs` di MySQL mulai terlihat menjadi bottleneck lewat
  monitoring query lambat.
- Fitur notifikasi email (atau job async lain) mulai butuh throughput
  lebih tinggi dari yang bisa ditangani queue driver `database`.

## Kesimpulan

`CACHE_STORE=database` dan `QUEUE_CONNECTION=database` adalah pilihan yang
tepat untuk skala proyek ini saat ini — sederhana, tidak butuh service
tambahan di server, dan performanya cukup. Redis adalah *upgrade path*
yang sudah dipetakan, bukan kebutuhan mendesak.
