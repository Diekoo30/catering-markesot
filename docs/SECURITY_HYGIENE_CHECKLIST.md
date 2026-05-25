# Security Hygiene Checklist - Catering Markesot

Dokumen ini berisi catatan final sebelum presentasi dan deployment. Fokusnya hygiene dasar, bukan menambah teknologi besar baru.

## HIGH - Wajib Sekarang

### Backup SQL tidak masuk repo

Kenapa penting:
Backup SQL bisa berisi data user, order, email, nomor telepon, alamat, dan hash password.

Status:
`*.sql` dan `backup-*.sql` sudah ditambahkan ke ignore file.

Impact:
Mengurangi risiko data sensitif ikut commit atau ikut Docker build context.

### Secret tidak ditulis langsung di stack production

Kenapa penting:
Password database tidak ideal jika terlihat langsung di `docker-stack.yml`.

Status:
`docker-stack.yml` memakai variable dari environment deployment untuk `DB_PASSWORD` dan `MYSQL_ROOT_PASSWORD`.

Impact:
File deployment bisa tetap dibaca sebagai arsitektur tanpa membocorkan credential asli.

## MEDIUM - Bagus Jika Sempat

### Permission hygiene

Kenapa penting:
File permission `777` berarti semua user bisa membaca, menulis, dan menjalankan file. Untuk source code, ini terlalu longgar.

Status:
Belum dieksekusi. Perlu approval khusus sebelum menjalankan `chmod`.

Impact jika diperbaiki:
File source lebih aman dan lebih rapi untuk server.

### Testing minimum

Prioritas realistis:

1. `/health` mengembalikan status OK.
2. Landing page bisa dibuka.
3. Login basic berjalan.
4. Validasi order mencegah data kosong/tidak valid.
5. Upload bukti pembayaran wajib untuk transfer bank.

Impact:
CI/CD demo lebih meyakinkan karena ada bukti otomatis bahwa fitur utama tidak rusak.

## LOW - Roadmap Masa Depan

### Alerting monitoring

Kenapa belum wajib:
Prometheus dan Grafana sudah cukup untuk presentasi observability dasar.

Roadmap:
Tambahkan alert jika server down, disk hampir penuh, atau CPU/memory terlalu tinggi.

### Docker secret atau secret manager

Kenapa belum wajib:
Untuk mini production mahasiswa, environment variable dari server/Jenkins masih acceptable.

Roadmap:
Gunakan Docker secret atau secret manager jika project benar-benar dipakai production jangka panjang.

## Catatan Demo/Admin Bootstrap

Credential bootstrap admin tidak diubah karena masih dibutuhkan untuk demo/setup awal.

Catatan penting:
Credential bootstrap hanya acceptable untuk demo atau bootstrap awal. Untuk production nyata, admin harus mengganti password dan menonaktifkan credential demo jika tidak diperlukan.
