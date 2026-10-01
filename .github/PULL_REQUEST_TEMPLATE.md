## Ringkasan

<!-- Jelasin singkat perubahan apa dan kenapa -->

## Checklist Sebelum Minta Review/Merge

- [ ] `./vendor/bin/pint --test` sudah dijalankan dan lolos
- [ ] `./vendor/bin/phpstan analyse --memory-limit=1G` sudah dijalankan dan lolos
- [ ] `php artisan test` sudah dijalankan dan semua PASS
- [ ] Kalau ada perubahan ke Model dengan `SoftDeletes`, sudah dicek relasi `withTrashed()`-nya (lihat `AGENTS.md` poin 3)
- [ ] Kalau nambah file PHP baru, sudah dipastikan tidak ada baris yang belum ke-format Pint
- [ ] `AGENTS.md` sudah diupdate kalau ada keputusan desain baru

## Jenis Perubahan

- [ ] Bug fix
- [ ] Fitur baru
- [ ] Perbaikan performa
- [ ] Keamanan
- [ ] Dokumentasi / tooling
- [ ] Lainnya: ___

## Testing Manual (kalau relevan)

<!-- Jelasin gimana cara kamu verifikasi perubahan ini selain automated test -->
