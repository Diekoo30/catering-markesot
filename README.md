# Catering Markesot 🍲

Catering Markesot adalah sistem manajemen pemesanan catering modern yang dibangun menggunakan kerangka kerja Laravel dan Filament PHP. Aplikasi ini dirancang untuk memudahkan proses pemesanan oleh pelanggan, serta mempermudah admin dalam mengelola pesanan, menu, metode pembayaran, hingga pengaturan informasi perusahaan.

## ✨ Fitur Utama

### 🧑‍💻 Untuk Pelanggan (Customer)
*   **Landing Page Dinamis:** Antarmuka pelanggan yang modern dan responsif.
*   **Pemesanan Online:** Fitur keranjang belanja (shopping cart) dan checkout yang mudah digunakan.
*   **Login & Registrasi:** Mendukung autentikasi standar maupun menggunakan **Google Sign-In** (Socialite).
*   **Lupa Password (OTP):** Sistem reset password yang aman menggunakan verifikasi OTP via Email.
*   **Berita & Aktivitas:** Menampilkan update terbaru dari catering dengan fitur *infinite auto-scroll* untuk pengalaman yang menyenangkan.
*   **Pelacakan Pesanan:** Pelanggan dapat melihat status pesanan secara real-time dari "Menunggu Konfirmasi" hingga "Selesai".

### 🛡️ Untuk Admin (Filament Panel)
*   **Manajemen Pesanan:** Verifikasi bukti pembayaran (Proof of Payment), update status pesanan, dan konversi data pesanan ke format Excel.
*   **Manajemen Menu & Kategori:** Menambah, mengubah, dan menghapus menu beserta kategorinya.
*   **Pengaturan Sistem (Manage Settings):** Mengatur informasi perusahaan (No. Telp, Alamat, Jam Operasional), konfigurasi persentase DP, hingga metode pembayaran (Bank string).
*   **Manajemen Pengguna:** Mengatur hak akses pengguna dan mengelola password registrasi admin.
*   **Log Aktivitas:** Menggunakan `spatie/laravel-activitylog` untuk memantau perubahan data pada sistem secara rinci.

---

## 🛠️ Persyaratan Sistem (Prerequisites)

Sebelum menjalankan project ini, pastikan sistem Anda sudah terinstall:

*   **PHP:** Versi 8.2 atau lebih baru
*   **Composer:** Untuk manajemen package PHP
*   **Node.js & npm:** Untuk kompilasi asset frontend (Vite)
*   **MySQL / MariaDB:** Sebagai sistem manajemen database

---

## 🚀 Cara Menjalankan Project (Instalasi)

Ikuti langkah-langkah di bawah ini untuk menjalankan project dari awal (scratch) di *local machine* Anda:

### 1. Clone Repositori
```bash
git clone https://github.com/username-anda/catering-markesot.git
cd catering-markesot
```

### 2. Install Dependencies PHP (Composer)
Jalankan perintah berikut untuk mengunduh semua package Laravel dan Filament yang dibutuhkan:
```bash
composer install
```

### 3. Install Dependencies Frontend (NPM)
Untuk mengunduh package frontend (TailwindCSS, Vite, dll):
```bash
npm install
```

### 4. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env` dan generate application key:
```bash
cp .env.example .env
php artisan key:generate
```
*(Catatan Windows: Anda bisa menggunakan perintah `copy .env.example .env` di Command Prompt)*

Buka file `.env` dan atur koneksi database Anda, misalnya:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=catering_markesot  # Pastikan database ini sudah dibuat di MySQL Anda
DB_USERNAME=root
DB_PASSWORD=
```
**Opsional:**
*   **Email:** Atur kredensial SMTP (Mailtrap/Gmail/dll) agar notifikasi email & OTP berjalan.
*   **Google Login:** Atur kredensial `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` jika ingin fitur Google Sign-In berfungsi.

### 5. Migrasi Database
Jalankan migrasi untuk membuat tabel di database, serta seeder (jika ada) untuk mengisi data awal (seperti admin default):
```bash
php artisan migrate --seed
```

### 6. Build Assets
Kompilasi asset frontend menggunakan Vite:
```bash
npm run build
```
*(Gunakan `npm run dev` jika Anda ingin melakukan perubahan pada tampilan dan melihat hasilnya secara real-time).*

### 7. Buat Symbolic Link Storage (Penting untuk Gambar)
Aplikasi ini banyak menggunakan fitur upload gambar (Menu, Bukti Transfer). Jalankan perintah berikut agar gambar dapat diakses oleh publik:
```bash
php artisan storage:link
```

### 8. Jalankan Local Server
```bash
php artisan serve
```

Aplikasi sekarang dapat diakses melalui browser di: `http://localhost:8000`

---

## 💻 Cara Penggunaan (Usage)

### 🧑‍💼 Mengakses Halaman Utama (Pelanggan)
Buka `http://localhost:8000` di browser Anda. Pelanggan dapat:
1.  Melihat menu makanan.
2.  Mendaftar (Register) atau login.
3.  Menambahkan menu ke keranjang (Add to Cart).
4.  Melakukan checkout dan mengunggah bukti transfer.

### 🛠️ Mengakses Panel Admin
Akses panel admin melalui URL: `http://localhost:8000/admin` (atau sesuai konfigurasi path Filament Anda).

**Akun Admin:**
*   Silakan login menggunakan akun admin yang ada di database. Jika Anda belum memilikinya, Anda bisa mendaftar akun baru dan mengubah `role`-nya menjadi `admin` secara langsung di database.

**Fitur Unggulan Admin:**
*   Arahkan ke menu **Pengaturan** di sidebar untuk mengubah Nomor Telepon, Jam Operasional, Rekening Bank, dan Persentase Down Payment (DP).
*   Arahkan ke menu **Transaksi / Pesanan** untuk melihat daftar pesanan masuk dan melakukan validasi pembayaran pelanggan.

---

## 📦 Teknologi yang Digunakan
*   **Laravel 12.x** - Backend Framework
*   **Filament PHP 5.x** - Admin Panel Framework
*   **Tailwind CSS** - Styling Framework
*   **MySQL** - Relational Database
*   **Vite** - Asset Bundler

---
*Dibuat dengan ❤️ untuk Catering Markesot.*
