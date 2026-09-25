# Backup Setup Notes

Dokumentasi konfigurasi backup otomatis untuk aplikasi
`instiki-peminjaman-barang`.

## Status

Backup Laravel sudah dikonfigurasi menggunakan Spatie Laravel Backup.

Backup mencakup:

- Database `mysql`
- File pada `storage/app/public`
- Backup disimpan pada disk `local`

Jadwal Laravel Scheduler:

- `01:00` — `backup:clean`
- `02:00` — `backup:run`

> Jadwal tersebut baru berjalan otomatis setelah Laravel Scheduler
> dikonfigurasi pada VPS.

## Setelah VPS Aktif

Masuk ke VPS dan buka cron:

```bash
crontab -e
```

Tambahkan:

```cron
* * * * * cd /var/www/instiki_peminjaman_barang && php artisan schedule:run >> /dev/null 2>&1
```

Cron tersebut menjalankan Laravel Scheduler setiap menit.

Laravel kemudian akan menjalankan command sesuai jadwal yang
didefinisikan di `routes/console.php`.

## Jadwal Backup

Konfigurasi pada `routes/console.php`:

```php
Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('02:00');
```

Dengan konfigurasi tersebut:

```text
01:00 → backup:clean
02:00 → backup:run
```

Retensi backup mengikuti konfigurasi pada:

```text
config/backup.php
```

## Verifikasi Scheduler

Setelah cron aktif, scheduler dapat diuji secara manual:

```bash
php artisan schedule:run
```

Untuk melihat daftar jadwal Laravel:

```bash
php artisan schedule:list
```

Pastikan jadwal `backup:clean` dan `backup:run` muncul pada daftar.

## Verifikasi Backup

Backup dapat dijalankan secara manual:

```bash
php artisan backup:run
```

Jika berhasil, akan muncul output seperti:

```text
Starting backup...
Dumping database...
Determining files to backup...
Created zip...
Successfully copied zip to disk named local.
Backup completed!
```

Backup pada disk `local` tersimpan di:

```text
storage/app/private/Laravel/
```

Untuk melihat file backup:

```bash
ls -lh storage/app/private/Laravel/
```

## Catatan

Jangan menggunakan `storage/app/public` sebagai lokasi penyimpanan
file backup.

`storage/app/public` merupakan sumber file yang ikut dibackup,
sedangkan hasil backup disimpan pada disk `local`.

Pastikan cron hanya dikonfigurasi **satu kali** agar scheduler tidak
dijalankan secara duplikat.

Jika VPS menggunakan user tertentu untuk menjalankan aplikasi,
pastikan cron dibuat menggunakan user yang memiliki akses ke:

```text
/var/www/instiki_peminjaman_barang
```
