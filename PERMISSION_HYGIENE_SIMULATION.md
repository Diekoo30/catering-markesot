# Permission Hygiene Simulation

Status: simulasi saja. Belum ada command `chmod` yang dijalankan.

## Ringkasan Temuan

Dari audit read-only, ada sekitar 266 item yang permission-nya bisa dirapikan.

Pola utama:

| Sebelum | Sesudah | Jenis | Jumlah |
|---|---:|---|---:|
| 777 | 644 | file source/config/view/test | 172 |
| 777 | 755 | folder | 71 |
| 664 | 644 | file source/config biasa | 10 |
| 775 | 755 | folder non-writable biasa | 4 |
| 777 | 600 | `.env` | 1 |
| 777 | 755 | executable script | 1 |

## Kenapa Ini Penting

Permission `777` berarti semua user bisa membaca, menulis, dan menjalankan file. Untuk source code Laravel, ini terlalu longgar.

Analogi sederhana:

- `777`: semua orang boleh masuk dan mengubah isi ruangan.
- `755` folder: semua orang boleh lewat/membaca, tapi hanya owner yang boleh mengubah.
- `644` file: semua orang boleh membaca, hanya owner yang boleh mengubah.
- `600` `.env`: hanya owner yang boleh membaca/menulis karena berisi secret.

## File/Folder Yang Terkena

Target utama:

- `app/` source Laravel
- `config/` konfigurasi aplikasi
- `database/` migration, factory, seeder
- `resources/` Blade, CSS, JS source
- `routes/` route Laravel
- `tests/` test
- `public/` asset publik
- `docker/` script/config Docker
- `ops/` config Nginx host/reverse proxy
- `.env`
- `artisan`

Contoh before/after:

```txt
app/Models/User.php                         777 -> 644
app/Http/Controllers/AuthController.php     777 -> 644
config/database.php                         777 -> 644
database/seeders/AdminUserSeeder.php        777 -> 644
resources/views/landing/index.blade.php     777 -> 644
routes/web.php                              777 -> 644
app/Filament                                777 -> 755
config/                                     777 -> 755
.env                                        777 -> 600
docker/entrypoint.sh                        644/777 -> 755
artisan                                     644 -> 755
```

## Command Yang Direkomendasikan Jika Di-approve

```bash
find app config database lang ops public resources routes tests -type d -exec chmod 755 {} \;
find app config database lang ops public resources routes tests -type f -exec chmod 644 {} \;
find bootstrap -type d -exec chmod 755 {} \;
find bootstrap -type f -exec chmod 644 {} \;
chmod 755 artisan docker/entrypoint.sh
chmod 600 .env
chmod -R ug=rwX,o= storage bootstrap/cache
```

Catatan:

- `storage` dan `bootstrap/cache` tetap writable karena Laravel memang butuh menulis cache, session, log, dan compiled view.
- Tidak disarankan menjalankan chmod ke `vendor/`, `node_modules/`, `.git/`, atau `temp-laravel/` untuk scope ini.

## Impact Potensial

Positif:

- Source code tidak world-writable.
- `.env` lebih aman.
- Permission lebih rapi untuk server/deployment.

Risiko kecil:

- Jika web server/container berjalan dengan user yang berbeda dari owner file, folder writable Laravel harus tetap benar.
- Setelah chmod, perlu cek halaman utama, login, upload bukti pembayaran, dan log Laravel.

## Rollback Plan

Jika ada masalah permission setelah chmod:

```bash
chmod -R 777 app config database lang public resources routes tests
chmod -R 775 storage bootstrap/cache
chmod 777 .env
```

Rollback di atas bukan best practice, tetapi bisa mengembalikan kondisi longgar sebelumnya untuk recovery cepat saat demo. Setelah masalah jelas, permission sebaiknya dirapikan lagi.

## Approval Required

Permission hygiene belum dieksekusi. Jalankan hanya setelah ada approval khusus, misalnya:

```txt
approve permission chmod
```
