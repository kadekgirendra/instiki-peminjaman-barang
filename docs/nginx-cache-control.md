# Rekomendasi: Cache-Control Header untuk Asset Statis (Nginx)

Dokumen ini mencatat konfigurasi Nginx yang direkomendasikan untuk
mengaktifkan browser caching jangka panjang pada asset hasil build Vite
(`public/build/`), belum diterapkan di konfigurasi produksi saat ini.

## Kenapa Aman untuk Cache Jangka Panjang

Vite secara otomatis menambahkan hash unik ke nama setiap file output
(contoh: `app-a1b2c3d4.css`, bukan `app.css`). Kalau isi file berubah
(misalnya setelah `npm run build` ulang), nama filenya **ikut berubah**
juga. Ini disebut *cache busting* otomatis — browser tidak akan pernah
"nyangkut" memakai file lama yang sudah tidak relevan, karena URL file
yang baru selalu berbeda dari yang lama.

Konsekuensinya: aman memberi instruksi "simpan file ini selamanya" ke
browser pengunjung, karena kalaupun isinya berubah di server, browser
otomatis akan diminta file dengan nama (URL) yang berbeda.

## Konfigurasi yang Direkomendasikan

Tambahkan blok ini di dalam `server { ... }` pada file konfigurasi Nginx
(`/etc/nginx/sites-available/<nama-file>`), di luar blok `location ~ \.php$`:

```nginx
location /build/ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

- `expires 1y` — instruksikan browser untuk tidak mengecek ulang file ini
  selama 1 tahun.
- `Cache-Control: public, immutable` — `public` mengizinkan cache di
  level browser maupun proxy/CDN perantara (kalau ada); `immutable`
  memberi tahu browser file ini **tidak akan pernah berubah**, sehingga
  browser bahkan tidak perlu melakukan request "cek apakah masih valid"
  (conditional request) — permintaan halaman berikutnya langsung pakai
  salinan lokal tanpa round-trip ke server sama sekali.

## Cara Menerapkan

1. SSH ke VPS, edit file konfigurasi Nginx yang relevan.
2. Tambahkan blok `location /build/ { ... }` di atas.
3. Uji konfigurasi sebelum reload:
```bash
   sudo nginx -t
```
4. Reload (bukan restart, supaya tidak memutus koneksi aktif):
```bash
   sudo systemctl reload nginx
```
5. Verifikasi lewat browser DevTools (tab Network) — buka salah satu file
   CSS/JS di `/build/`, cek response header harus muncul
   `Cache-Control: public, immutable`.

## Dampak yang Diharapkan

Pengunjung yang kembali ke situs (kunjungan kedua dan seterusnya) tidak
perlu mengunduh ulang CSS/JS yang sudah pernah di-download sebelumnya,
selama belum ada `npm run build` baru yang mengubah nama filenya. Ini
mempercepat waktu muat halaman terutama untuk pengguna dengan koneksi
mobile/lambat.
