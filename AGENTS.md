# Aturan untuk AI Agent — Wajib Dibaca Sebelum Mengubah Kode

Dokumen ini berisi keputusan desain yang SUDAH FINAL dan alasan di baliknya.
Sebelum mengubah file manapun yang disebut di sini, AI agent WAJIB membaca
alasannya dulu — jangan "menyederhanakan" atau "merapikan" tanpa memahami
kenapa kode ini ditulis seperti ini.

## 1. Autentikasi & Identitas User

- **Login pakai `username`, BUKAN email.** Tabel `users` TIDAK punya kolom
  `email` sama sekali (dan sengaja begitu — sistem berbasis NIM/NIDN).
  JANGAN pernah menyarankan/menambahkan `Mail::to($user)` tanpa guard
  `if ($user->email)` — ini akan crash (`Address::__construct(): $address
  must be of type string, null given`).
- JANGAN ganti session-based auth ke JWT. Ini monolith server-rendered,
  bukan API terpisah. Sanctum sudah terpasang tapi sengaja belum dipakai.
- `username` dan `nim_nidn` di tabel `users` TIDAK boleh bisa dipakai ulang
  setelah user di-soft-delete (keputusan sengaja — NIM adalah identitas
  permanen). JANGAN tambahkan `->withoutTrashed()` di validasi unique-nya.

## 2. Race Condition & Locking — JANGAN DIHAPUS

File: `app/Services/AvailabilityService.php`,
`app/Http/Controllers/LoanRequestController.php` (method `store`),
`app/Http/Controllers/Admin/TransactionController.php` (method `approve`,
`complete`, `markPaid`)

- SEMUA method di atas WAJIB tetap dibungkus `DB::transaction()` dengan
  `lockForUpdate()` (lewat `AvailabilityService::lockItems()`).
- Ini mencegah race condition nyata: 2 admin approve pengajuan berbeda
  untuk barang yang sama secara bersamaan → overbooking. Sudah dibuktikan
  lewat testing manual (2 terminal, `lockForUpdate` terbukti memblokir
  proses kedua sampai proses pertama commit).
- JANGAN hapus `lockForUpdate()` "demi performa" — trade-off ini disengaja.
- Urutan lock SELALU `orderBy('id')` ascending di `lockItems()` — JANGAN
  diubah urutannya, ini mencegah deadlock antar transaction yang berebut
  banyak item sekaligus.

## 3. Soft Deletes — Efek Sampingnya Wajib Diperhitungkan

Model `Item` dan `User` pakai `SoftDeletes`. Ini artinya:

- Relasi `Transaction::item()`, `Transaction::user()`, `LoanRequest::user()`
  WAJIB pakai `->withTrashed()`. Tanpa ini, transaksi yang barang/usernya
  sudah di-soft-delete akan mengembalikan `null` → 500 di ReportController,
  CalendarController, Dashboard, dan reminder navbar.
- `Admin\ItemController::destroy()` dan `Admin\UserController::destroy()`
  TIDAK boleh hapus file gambar fisik (`Storage::disk('public')->delete(...)`)
  — riwayat transaksi lama (completed/rejected) tetap butuh menampilkan
  foto barang yang benar.
- Guard "tolak hapus kalau masih ada transaksi aktif" HARUS tetap ada,
  tapi SCOPE-nya cuma untuk status `booked`/`pending` (bukan blokir total
  seperti sebelum ada SoftDeletes) — barang/user yang cuma punya riwayat
  `completed`/`rejected` boleh dihapus (soft).

## 4. Cache — Invalidasi Wajib Konsisten

- Setiap perubahan yang mempengaruhi daftar kategori (`Item::create()`,
  `update()`, `delete()`) WAJIB diikuti `Item::forgetCategoriesCache()`.
- Cache laporan admin (`ReportController`) pakai TTL PENDEK (5 menit),
  BUKAN cache permanen — datanya berubah tiap ada approve/reject/complete.
  JANGAN samakan pola cache-nya dengan `Item::categories()` yang TTL 1 jam.
- Kalau `Item::categories()` error `__PHP_Incomplete_Class` — itu cache
  korup, sudah ada auto-recovery lewat try-catch + `Cache::forget()`.
  JANGAN dihapus fallback ini.

## 5. Intervention Image v4 — API Berbeda dari v3/Tutorial Lama

Package `intervention/image` versi 4.x yang dipakai project ini PUNYA
method yang beda nama dari versi lama yang sering muncul di tutorial:

| Yang BENAR (v4) | Yang SALAH (v2/v3, jangan dipakai) |
| --- | --- |
| `Image::decode($file)` | `Image::read($file)` / `Image::make($file)` |
| `$image->encodeUsingFormat(Format::JPEG, quality: 80)` | `$image->toJpeg(80)` / `$image->encode('jpg', 80)` |

Kalau AI agent (termasuk versi training data lama) menyarankan `read()`
atau `toJpeg()`, itu SALAH untuk versi package ini — akan error
"Call to undefined method".

## 6. Testing — Non-Negosiable

- SETIAP perubahan logika bisnis WAJIB dijalankan lewat `php artisan test`
  sebelum dianggap selesai. Jangan asumsi "harusnya jalan" tanpa run test.
- Test suite jalan di SQLite (`phpunit.xml`), BUKAN MySQL. Ini artinya
  `lockForUpdate()` di test TIDAK benar-benar menguji row-level locking
  (SQLite tidak mendukungnya) — cuma menguji logika matematika. Locking
  sungguhan cuma terverifikasi lewat testing manual di MySQL.
- Kalau menambah fitur baru yang mirip fitur lama (misal `update()` mirip
  `store()`), WAJIB buat test terpisah untuk masing-masing — jangan asumsi
  1 test yang cover `store()` otomatis membuktikan `update()` juga benar.
  (Ini persis penyebab bug `$$validated['image']` di `update()` lolos lama.)

## 7. Deployment — JANGAN Otomatis Aktifkan Ulang

- Workflow `.github/workflows/deploy.yml` SEDANG DI-DISABLE (VPS tidak
  berlangganan lagi sejak Agustus 2026). JANGAN aktifkan ulang tanpa
  konfirmasi eksplisit dari pemilik project — cek dulu apakah subscription
  VPS sudah aktif lagi.
- `.github/workflows/tests.yml` TETAP AKTIF dan WAJIB tetap jalan di setiap
  PR — jangan pernah disable ini atau bypass step `--test` (Pint).

## 8. Rate Limiting — Jangan Dihapus

Endpoint berikut WAJIB tetap punya middleware `throttle`:
`/register`, `/loan-cart/add`, `/loan-requests` (POST), `/returns/{id}`
(POST), seluruh grup `admin/*`. Ini mencegah spam/abuse, jangan dihapus
"karena mengganggu testing manual" — kalau kena limit saat testing,
itu perilaku yang benar (429), bukan bug.

## 9. Konvensi File & Struktur

- Config Intervention Image ada di `config/intervention-image.php`,
  BUKAN `config/image.php` (beda dari versi package lama).
- Halaman error (`resources/views/errors/*.blade.php`) SENGAJA tidak
  pakai `x-app-layout` atau komponen Blade apapun — murni HTML+CSS inline,
  supaya tetap render meski auth/session/database sedang bermasalah.
  JANGAN "rapikan" jadi pakai komponen layout biasa.
- File debug (`app/Console/Commands/TestItemLock.php`, kalau pernah dibuat
  ulang untuk keperluan testing manual) TIDAK BOLEH ikut ter-commit ke
  production.

## 10. Sebelum Mengubah Kode yang Sudah Ada

1. Baca komentar yang sudah ada di kode — kalau ada penjelasan "kenapa"
   ditulis begini, itu bukan basa-basi, itu keputusan yang sudah diuji.
2. Jalankan `php artisan test` SEBELUM dan SESUDAH perubahan — bandingkan
   hasilnya, bukan cuma jalankan sesudah.
3. Kalau menambah fitur yang menyentuh Model dengan `SoftDeletes` (`Item`,
   `User`), audit ULANG semua relasi Eloquent yang mengarah ke Model itu.
4. Kalau ragu apakah suatu guard/validasi masih relevan, JANGAN dihapus
   duluan atas asumsi "sudah tidak perlu" — tanyakan dulu, atau cek riwayat
   commit/dokumentasi kenapa itu ditambahkan.

## 11. Rate Limiting Login — Hit Cuma Boleh 1x per Percobaan Gagal

`RateLimiter::hit()` di `LoginRequest.php` HANYA boleh dipanggil di dalam
blok `if (! Auth::attempt(...))` pada method `authenticate()`. JANGAN
tambahkan `hit()` di `ensureIsNotRateLimited()` — method itu cuma boleh
CEK (`tooManyAttempts()`), tidak boleh ikut menghitung. Kalau ada `hit()`
di kedua tempat, counter naik 2x per percobaan gagal, user ke-lockout
di percobaan ke-3, bukan ke-5 seperti seharusnya.

## 12. Jangan Andalkan `\RuntimeException`/`\PDOException` Generik untuk Membedakan Error Bisnis vs Sistem

`Illuminate\Database\QueryException` (dilempar Eloquent untuk error DB
seperti deadlock) EXTENDS `\RuntimeException`, BUKAN `\PDOException`.
Kalau butuh membedakan "error bisnis yang sengaja dilempar sendiri" dari
"error sistem tak terduga", WAJIB pakai custom Exception class
(`App\Exceptions\StockUnavailableException` dkk), JANGAN pakai
`\RuntimeException` polos — itu akan ketiban tertangkap bareng dengan
`QueryException` dan berisiko membocorkan detail SQL mentah ke user.

## 13. Jalankan Pint Sebelum Commit File Baru

Setiap kali menambahkan file PHP baru (controller, test, exception, dll),
WAJIB jalankan `./vendor/bin/pint` sebelum commit — CI (`tests.yml`) akan
menolak PR yang formatnya belum sesuai standar Laravel Pint. File yang
dibuat lewat editor manual atau AI assistant SERING tidak otomatis
mengikuti format ini.

## 14. Jangan Percaya Diagnosis "Genuine Bug" Tanpa Cek Root Cause Log Dulu

Kalau SEMUA test gagal dengan exception yang SAMA PERSIS (misal
`MissingAppKeyException`), itu tanda kegagalan infrastruktur/environment
CI, BUKAN bukti banyak bug logika berbeda. Jangan re-write kode fitur
(login, cache, policy, dst) berdasarkan nama test yang gagal tanpa
memverifikasi assertion-nya BENAR-BENAR sempat dijalankan (bukan crash
duluan di tahap boot). Cek dulu apakah fitur itu sudah pernah lolos test
lokal sebelumnya sebelum menyimpulkan ada regresi baru.

**Kasus nyata (September 2026):** CI gagal total dengan
`MissingAppKeyException` di 35 test. Beberapa laporan otomatis
menyimpulkan ada 5-6 bug terpisah (rate limiter, cache, policy, session,
soft-delete) dan menyarankan re-write kode fitur tersebut. Setelah
`APP_KEY` ditambahkan ke `phpunit.xml`, SEMUA 35 test langsung hijau
tanpa satu baris pun kode fitur diubah — membuktikan akarnya cuma 1
(environment CI), bukan banyak bug logika seperti yang diklaim.

## 15. `APP_KEY` untuk Testing Ada di `phpunit.xml`, BUKAN `.env.example`

- `phpunit.xml` punya `APP_KEY` statis khusus testing — ini AMAN dan
  MEMANG HARUS begitu, karena test selalu jalan di SQLite `:memory:` yang
  fresh tiap run, tidak pernah mengenkripsi data sungguhan siapapun.
- `.env.example` HARUS tetap `APP_KEY=` (kosong) — JANGAN pernah diisi
  key sungguhan di situ. Itu template untuk clone baru; tiap orang wajib
  generate key sendiri lewat `php artisan key:generate`. Mengisi key
  valid di `.env.example` berisiko banyak instalasi berbeda memakai
  key yang sama kalau developer lupa generate ulang.

## 16. Dependabot — Major Version Bump Tetap Wajib Diverifikasi Manual

CI hijau untuk PR Dependabot TIDAK menjamin semuanya aman, khususnya untuk
package yang perilakunya tidak sepenuhnya tercakup oleh `php artisan test`
atau `npm run build` — misalnya `concurrently` (cuma dipakai script `npm run
dev`, tidak pernah dijalankan lewat CI). Untuk PR dengan LONCATAN MAJOR
VERSION (bukan minor/patch), tetap jalankan skenario penggunaan aslinya
secara manual di lokal sebelum benar-benar yakin aman, meski checks GitHub
sudah hijau.

**Kasus nyata (September 2026):** Dependabot mengusulkan `concurrently`
9.x → 10.x dan `actions/checkout` v4 → v7 (loncat beberapa major version).
Checks CI hijau untuk keduanya, tapi tetap diverifikasi manual: `npm run
dev` dijalankan langsung untuk memastikan `concurrently` versi baru tidak
breaking (package ini tidak tersentuh sama sekali oleh `npm run build` di
CI), dan changelog resmi `actions/checkout` v7 dicek untuk memastikan
breaking change-nya (soal trigger `pull_request_target`) tidak relevan
karena workflow ini cuma pakai trigger `pull_request` biasa.

## 17. Cache Driver Testing — `array` vs `database` Punya Perilaku Beda

Test suite pakai `CACHE_STORE=array` (in-memory, tidak pernah serialize),
sedangkan lokal/produksi pakai `CACHE_STORE=database` (serialize/unserialize
lewat PHP `serialize()`). Bug korupsi cache (`__PHP_Incomplete_Class`, atau
tipe data berubah jadi string) HANYA bisa terjadi di driver `database`,
TIDAK akan pernah ketahuan lewat test biasa. SETIAP method yang pakai
`Cache::remember()` untuk data terstruktur (array/Collection) WAJIB punya
defensive fallback (validasi tipe + `Cache::forget()` + hitung ulang),
bukan cuma andalkan try-catch exception — kadang cache korup tidak
melempar exception sampai datanya benar-benar dipakai di tempat lain.

**Kasus nyata (September 2026):** `ReportController::buildItemRows()`
melempar `TypeError: Cannot access offset of type string on string` di
`resources/views/admin/reports/index.blade.php` — cache laporan korup jadi
string mentah, bukan Collection, tapi TIDAK ada exception saat
`Cache::remember()` dipanggil, baru meledak saat Blade mengakses
`$row['name']`. `ReportCacheTest` (yang jalan di `CACHE_STORE=array`) tidak
pernah menangkap ini. Fix: tambahkan pengecekan `is_iterable($result)`
eksplisit di `reportCacheRemember()`, bukan cuma try-catch, plus test baru
yang taruh string mentah langsung ke cache untuk mensimulasikan korupsi
tanpa perlu ganti `CACHE_STORE`.

## 18. CSP — Alpine.js Butuh `unsafe-eval` + `unsafe-inline`, Chart.js dari CDN

`script-src` WAJIB punya `'unsafe-eval'` (Alpine.js pakai `new Function()`
untuk evaluasi directive) dan `'unsafe-inline'` (untuk inline event handler
seperti `@click`). JANGAN dihapus, akan mematikan semua interaksi Alpine
di project ini (dropdown, filter, kalender). `https://cdn.jsdelivr.net`
WAJIB ada di `script-src` (Chart.js dashboard admin), dan `data:` WAJIB
ada di `font-src` (icon font base64). Kalau nambah library CDN baru,
WAJIB tes dulu di mode Report-Only sebelum aktifkan CSP enforced, cek
Console browser di SEMUA halaman utama.

## 19. Pertahanan XSS Utama Adalah Blade Auto-Escape, Bukan CSP

Audit (September 2026) mengonfirmasi NOL penggunaan `{!! !!}` (raw output)
di seluruh `resources/views/` — semua output pakai `{{ }}` yang otomatis
escape HTML. INI yang jadi pertahanan XSS sesungguhnya di project ini,
BUKAN CSP (yang terpaksa pakai `unsafe-inline` demi Alpine.js, sehingga
lemah terhadap inline-script XSS). JANGAN PERNAH tambahkan `{!! $variable !!}`
untuk data yang berasal dari input user tanpa sanitasi eksplisit — itu
akan membuka celah XSS nyata yang CSP saat ini TIDAK akan mencegah.
