# Catatan Presentasi Sistem Catering Markesot

## 1. Pembukaan

Assalamualaikum warahmatullahi wabarakatuh.

Pada presentasi ini saya akan menjelaskan sistem yang saya buat, yaitu **Catering Markesot**. Sistem ini adalah aplikasi pemesanan catering berbasis web yang dibuat menggunakan **Laravel** sebagai backend, **Filament** sebagai panel admin, **MySQL** sebagai database, dan **Docker** untuk menjalankan aplikasi secara lebih konsisten.

Tujuan utama sistem ini adalah mempermudah pelanggan dalam melakukan pemesanan catering secara online, serta membantu admin mengelola menu, pesanan, pembayaran, berita, pengguna, dan pengaturan sistem.

## 2. Gambaran Umum Sistem

Sistem ini memiliki dua sisi utama:

1. **Sisi pelanggan**
   Pelanggan bisa melihat menu, memilih makanan atau minuman, checkout, memilih metode pembayaran, mengunggah bukti transfer jika memakai transfer bank, dan melihat status pesanan.

2. **Sisi admin**
   Admin menggunakan panel Filament di `/admin` untuk mengelola data menu, kategori, pesanan, pembayaran, berita, pengguna, dan pengaturan seperti DP, rekening bank, jam operasional, dan informasi perusahaan.

Kalimat presentasi:

"Secara umum, aplikasi ini terbagi menjadi dua bagian. Bagian pertama adalah halaman pelanggan untuk melakukan pemesanan catering. Bagian kedua adalah panel admin untuk mengelola data operasional seperti menu, pesanan, pembayaran, dan pengaturan sistem."

## 3. Teknologi Yang Digunakan

Teknologi utama yang digunakan:

- **Laravel 12**
  Digunakan sebagai framework backend. Laravel menangani routing, controller, model, validasi, autentikasi, migration, dan proses bisnis pesanan.

- **Filament 5**
  Digunakan untuk membuat admin panel. Dengan Filament, admin bisa mengelola data melalui dashboard tanpa harus mengakses database secara langsung.

- **MySQL 8**
  Digunakan sebagai database utama untuk menyimpan data pengguna, menu, kategori, pesanan, item pesanan, pembayaran, berita, dan pengaturan.

- **Redis**
  Disiapkan untuk cache atau queue agar sistem lebih siap dikembangkan.

- **Vite dan Tailwind CSS**
  Digunakan untuk build asset frontend seperti CSS dan JavaScript.

- **Docker**
  Digunakan agar aplikasi berjalan dalam container terpisah, misalnya container aplikasi, database, web server, Redis, dan queue worker.

- **Prometheus dan Grafana**
  Disiapkan pada konfigurasi monitoring untuk memantau kondisi service atau server.

Kalimat presentasi:

"Saya menggunakan Laravel untuk backend karena strukturnya rapi dan mendukung fitur seperti routing, model, migration, validasi, dan autentikasi. Untuk admin panel saya memakai Filament agar pengelolaan data lebih cepat dibuat dan lebih mudah digunakan oleh admin."

## 4. Fitur Untuk Pelanggan

Fitur pada sisi pelanggan:

- Melihat daftar menu yang aktif dan tersedia.
- Menu dikelompokkan berdasarkan kategori.
- Sistem menandai menu best seller berdasarkan jumlah item yang paling sering dipesan.
- Pelanggan bisa melakukan pemesanan online.
- Jika belum login, pelanggan bisa dibuatkan akun saat melakukan pemesanan.
- Metode pembayaran mendukung tunai dan transfer bank.
- Untuk transfer bank, pelanggan perlu mengunggah bukti pembayaran.
- Pelanggan bisa melihat pesanan miliknya melalui halaman `/my-orders`.
- Pelanggan bisa membatalkan pesanan selama masih memenuhi kondisi tertentu.
- Tersedia login biasa, registrasi, login Google, ubah password, dan lupa password menggunakan OTP email.

Kalimat presentasi:

"Pada sisi pelanggan, sistem dibuat supaya proses pemesanan bisa dilakukan dari halaman utama. Pelanggan memilih menu, mengisi data acara, memilih metode pembayaran, lalu sistem membuat pesanan beserta detail itemnya. Jika pembayaran menggunakan transfer bank, pelanggan mengunggah bukti transfer yang nanti diverifikasi oleh admin."

## 5. Fitur Untuk Admin

Admin mengakses panel melalui `/admin`.

Fitur admin:

- Dashboard statistik pesanan.
- Manajemen pesanan.
- Verifikasi pembayaran.
- Menandai pesanan selesai.
- Mengelola menu dan kategori.
- Mengelola berita.
- Mengelola user.
- Mengatur persentase DP.
- Mengatur minimal waktu pemesanan.
- Mengatur informasi perusahaan.
- Mengatur rekening bank.
- Mengatur jam operasional.
- Mengatur password/token khusus untuk registrasi admin.
- Export data pesanan ke Excel.

Kalimat presentasi:

"Untuk admin, saya menggunakan Filament karena cocok untuk membuat panel administrasi Laravel. Admin dapat melihat pesanan masuk, mengecek bukti pembayaran, memverifikasi atau menolak pembayaran, lalu mengubah status pesanan sampai selesai."

## 6. Alur Pemesanan

Alur pemesanan pada sistem:

1. Pelanggan membuka halaman utama.
2. Sistem mengambil data menu yang tersedia dari tabel `menu_items`.
3. Pelanggan memilih menu dan jumlah pesanan.
4. Pelanggan mengisi data nama, telepon, alamat, dan tanggal acara.
5. Sistem melakukan validasi, misalnya tanggal pesanan tidak boleh terlalu dekat dari waktu sekarang.
6. Sistem membuat data order di tabel `orders`.
7. Sistem membuat detail item di tabel `order_items`.
8. Sistem menghitung total, DP, dan sisa pembayaran.
9. Sistem membuat data pembayaran di tabel `payments`.
10. Admin memverifikasi pembayaran melalui panel admin.
11. Status pesanan berubah sesuai kondisi pembayaran.
12. Jika selesai, admin menandai pesanan sebagai `completed` dan sistem dapat mengirim email notifikasi.

Kalimat presentasi:

"Alur pentingnya ada di proses checkout. Saat pelanggan submit pesanan, sistem menggunakan database transaction. Artinya, data order, detail item, dan pembayaran dibuat sebagai satu proses. Jika ada error di tengah proses, data akan dibatalkan agar tidak ada data yang setengah masuk."

## 7. Status Pesanan dan Pembayaran

Status pesanan utama:

- `pending`: pesanan baru dibuat dan menunggu pembayaran atau verifikasi.
- `dp_paid`: DP sudah dibayar.
- `confirmed`: pesanan sudah dikonfirmasi.
- `completed`: pesanan selesai.
- `cancelled`: pesanan dibatalkan.

Status pembayaran:

- `pending`: pembayaran menunggu verifikasi admin.
- `verified`: pembayaran diterima.
- `rejected`: pembayaran ditolak.

Kalimat presentasi:

"Saya memisahkan status pesanan dan status pembayaran. Tujuannya agar admin bisa membedakan antara proses produksi pesanan dan proses validasi pembayaran. Dengan begitu, pesanan bisa dilacak lebih jelas."

## 8. Logika Otomatis Dengan Observer dan Service

Sistem ini memiliki beberapa logika otomatis:

- `OrderObserver`
  Membuat nomor pesanan otomatis dengan format seperti `ORD-YYYYMMDD-0001`, serta menghitung DP dan sisa pembayaran ketika total berubah.

- `PaymentObserver`
  Membuat nomor pembayaran otomatis dan memperbarui status pesanan ketika pembayaran diverifikasi.

- `OrderService`
  Berisi fungsi untuk menghitung ulang total, mengkonfirmasi, menyelesaikan, dan membatalkan pesanan.

- `SettingService`
  Mengambil pengaturan sistem seperti DP, rekening bank, best seller count, dan minimal waktu persiapan. Beberapa setting disimpan dengan cache agar lebih efisien.

Kalimat presentasi:

"Saya memisahkan logika bisnis ke service dan observer agar controller tidak terlalu penuh. Controller menangani request, sedangkan perhitungan total, perubahan status, dan nomor otomatis ditangani oleh service atau observer."

## 9. Pembayaran Transfer Bank dan Tunai

Pada sistem ini, pembayaran yang dijelaskan adalah **tunai** dan **transfer bank**.

Untuk metode tunai:

1. Pelanggan memilih metode pembayaran tunai.
2. Sistem membuat data pembayaran dengan status `pending`.
3. Admin dapat mengonfirmasi ketika pembayaran sudah diterima.

Untuk metode transfer bank:

1. Pelanggan memilih metode transfer bank.
2. Sistem menampilkan informasi rekening bank dari pengaturan admin.
3. Pelanggan mengunggah bukti transfer.
4. Bukti pembayaran disimpan di storage aplikasi.
5. Admin memeriksa bukti tersebut melalui panel admin.
6. Admin bisa menerima atau menolak pembayaran.

Kalimat presentasi:

"Pada bagian pembayaran, sistem saya menggunakan dua metode, yaitu tunai dan transfer bank. Jika transfer bank, pelanggan mengunggah bukti pembayaran, lalu admin melakukan verifikasi secara manual dari panel admin. Dengan cara ini, pembayaran tidak langsung dianggap valid sebelum dicek oleh admin."

## 10. Struktur Database Penting

Tabel utama yang digunakan:

- `users`
  Menyimpan data user, role admin/user, data kontak, Google ID, dan OTP reset password.

- `categories`
  Menyimpan kategori menu.

- `menu_items`
  Menyimpan data menu, harga, gambar, status tersedia, dan kategori.

- `orders`
  Menyimpan data pesanan, data pelanggan, tanggal acara, total, DP, sisa pembayaran, dan status.

- `order_items`
  Menyimpan detail menu yang dipesan dalam satu order.

- `payments`
  Menyimpan data pembayaran, nominal, metode pembayaran, bukti transfer, status verifikasi.

- `settings`
  Menyimpan pengaturan sistem yang bisa diubah admin tanpa mengubah kode.

- `news`
  Menyimpan berita atau aktivitas yang ditampilkan di landing page.

Kalimat presentasi:

"Database saya pisahkan berdasarkan kebutuhan data. Pesanan disimpan di `orders`, detail menunya di `order_items`, sedangkan pembayarannya di `payments`. Dengan pemisahan ini, satu pesanan bisa memiliki banyak item dan bisa dikembangkan untuk beberapa pembayaran."

## 11. Penjelasan Docker

Docker digunakan agar sistem bisa berjalan konsisten tanpa harus mengatur semua dependency secara manual di komputer host.

Pada `docker-compose.yml`, ada beberapa service:

- `app`
  Container utama Laravel yang dibuild dari `Dockerfile`. Container ini menjalankan PHP-FPM.

- `nginx`
  Web server yang menerima request dari browser. Port host `8080` diarahkan ke port container `80`, jadi aplikasi bisa diakses melalui `http://localhost:8080`.

- `mysql`
  Database MySQL 8. Di host menggunakan port `3307`, lalu diarahkan ke port `3306` di container.

- `redis`
  Digunakan untuk cache atau queue.

- `queue`
  Container khusus untuk menjalankan `php artisan queue:work`, sehingga pekerjaan background bisa berjalan terpisah dari aplikasi utama.

Ada juga volume:

- `mysql_data`
  Digunakan agar data database tetap tersimpan walaupun container dimatikan atau dibuat ulang.

Dan network:

- `catering`
  Digunakan agar container bisa saling berkomunikasi menggunakan nama service, misalnya Nginx meneruskan request PHP ke service `app:9000`.

Kalimat presentasi:

"Di Docker, saya memisahkan komponen aplikasi menjadi beberapa container. Nginx bertugas menerima request dari browser, lalu meneruskan request PHP ke container app. Container app menjalankan Laravel dengan PHP-FPM. Database berjalan di container MySQL, dan Redis serta queue worker disiapkan untuk kebutuhan cache dan pekerjaan background."

## 12. Penjelasan Dockerfile

`Dockerfile` digunakan untuk membuat image aplikasi Laravel.

Isi pentingnya:

- Base image menggunakan `php:8.4-fpm`.
- Menginstall dependency sistem seperti `git`, `curl`, `zip`, `unzip`, library image, Node.js, dan npm.
- Menginstall ekstensi PHP seperti `pdo_mysql`, `gd`, `zip`, `intl`, dan `mbstring`.
- Mengcopy Composer dari image composer resmi.
- Menentukan working directory di `/var/www`.
- Mengcopy semua file project ke container.
- Mengatur permission folder `storage` dan `bootstrap/cache`.
- Menjalankan `composer install`.
- Menjalankan `npm install`.
- Menjalankan `npm run build`.
- Menjalankan PHP-FPM sebagai proses utama.

Kalimat presentasi:

"Dockerfile ini berfungsi sebagai resep untuk membangun environment Laravel. Jadi semua kebutuhan seperti PHP extension, Composer dependency, dan asset frontend sudah disiapkan saat image dibuat."

## 13. Penjelasan Nginx

Konfigurasi Nginx ada di `docker/nginx/default.conf`.

Bagian penting:

- Root diarahkan ke `/var/www/public`, karena pada Laravel file publik ada di folder `public`.
- `try_files` digunakan agar semua request yang bukan file langsung diarahkan ke `index.php`.
- Request PHP diteruskan ke `app:9000`, yaitu container Laravel/PHP-FPM.
- File tersembunyi seperti `.env` diblokir agar tidak bisa diakses dari browser.

Kalimat presentasi:

"Nginx tidak menjalankan Laravel secara langsung. Nginx hanya menerima request HTTP, lalu untuk file PHP diteruskan ke PHP-FPM di container app. Ini adalah pola umum deployment Laravel."

## 14. Monitoring

Project memiliki `docker-compose.monitoring.yml` untuk monitoring.

Service yang disediakan:

- `prometheus`
  Mengambil dan menyimpan metrik.

- `grafana`
  Menampilkan dashboard visual dari metrik.

- `node-exporter`
  Mengambil metrik dari host atau container seperti CPU, memory, dan resource lain.

Kalimat presentasi:

"Selain aplikasi utama, saya juga menyiapkan konfigurasi monitoring menggunakan Prometheus dan Grafana. Tujuannya agar performa aplikasi atau server bisa dipantau, misalnya penggunaan resource dan kondisi service."

## 15. Alur Request Secara Teknis

Alur request dari browser:

1. User membuka `http://localhost:8080`.
2. Request masuk ke container `nginx`.
3. Nginx mengecek file di folder `public`.
4. Jika request membutuhkan Laravel, Nginx meneruskan ke `app:9000`.
5. PHP-FPM menjalankan Laravel.
6. Laravel membaca route di `routes/web.php`.
7. Controller memproses request.
8. Model mengambil atau menyimpan data ke MySQL.
9. View Blade dikembalikan ke user.

Kalimat presentasi:

"Jadi alurnya tidak langsung dari browser ke Laravel. Browser masuk ke Nginx dulu, lalu Nginx meneruskan request PHP ke PHP-FPM. Laravel kemudian menentukan controller berdasarkan route dan mengambil data dari database."

## 16. Keamanan dan Validasi

Beberapa hal keamanan yang ada:

- Password disimpan menggunakan hash, bukan plain text.
- Form pesanan divalidasi sebelum masuk database.
- Upload bukti pembayaran dibatasi tipe file dan ukuran file.
- Halaman `/my-orders` hanya bisa diakses user yang login.
- Admin panel dilindungi autentikasi.
- File tersembunyi seperti `.env` diblokir oleh Nginx.
- Reset password menggunakan OTP yang memiliki masa berlaku 10 menit.
- Data penting seperti pembayaran diverifikasi manual oleh admin.

Kalimat presentasi:

"Untuk keamanan, sistem menggunakan validasi Laravel, password hashing, middleware auth, dan pembatasan upload file. Selain itu, pembayaran tidak langsung dianggap sah, tetapi harus diverifikasi admin."

## 17. Kelebihan Sistem

Kelebihan dari sistem ini:

- Memudahkan pelanggan melakukan pemesanan catering online.
- Admin bisa mengelola operasional dari satu panel.
- Status pesanan dan pembayaran lebih jelas.
- Pengaturan seperti DP dan rekening bisa diubah tanpa mengubah kode.
- Sudah mendukung Docker sehingga deployment lebih konsisten.
- Ada queue worker untuk pekerjaan background.
- Ada monitoring dengan Prometheus dan Grafana.
- Ada fitur export Excel untuk laporan pesanan.

## 18. Kekurangan atau Pengembangan Selanjutnya

Beberapa pengembangan yang bisa ditambahkan:

- Integrasi payment gateway otomatis agar pembayaran tidak perlu diverifikasi manual.
- Notifikasi WhatsApp untuk pelanggan dan admin.
- Tracking pengiriman.
- Role permission admin yang lebih detail.
- Dashboard monitoring bisnis yang lebih lengkap.
- Testing fitur utama yang lebih banyak.
- Deployment ke server production dengan HTTPS asli.

Kalimat presentasi:

"Untuk pengembangan selanjutnya, sistem ini bisa diintegrasikan dengan payment gateway agar pembayaran otomatis terverifikasi. Selain itu, notifikasi WhatsApp juga bisa ditambahkan agar pelanggan mendapat update pesanan lebih cepat."

## 19. Contoh Jawaban Jika Ditanya

**Apa fungsi Docker di project ini?**

Docker membuat environment aplikasi menjadi konsisten. Jadi aplikasi tidak terlalu bergantung pada konfigurasi komputer masing-masing. Laravel, Nginx, MySQL, Redis, dan queue worker bisa dijalankan sebagai container terpisah.

**Kenapa menggunakan Nginx?**

Nginx digunakan sebagai web server. Nginx menerima request dari browser, menyajikan file statis, dan meneruskan request PHP ke PHP-FPM di container app.

**Kenapa port aplikasi 8080?**

Karena di `docker-compose.yml`, port `8080` di komputer host diarahkan ke port `80` di container Nginx. Jadi akses aplikasinya melalui `http://localhost:8080`.

**Kenapa MySQL memakai port 3307?**

Port host `3307` digunakan agar tidak bentrok dengan MySQL lokal yang biasanya memakai port `3306`. Di dalam container, MySQL tetap berjalan di port `3306`.

**Kenapa ada container queue?**

Queue digunakan untuk menjalankan pekerjaan background secara terpisah. Misalnya nanti email, notifikasi, atau proses berat bisa dijalankan tanpa membuat user menunggu terlalu lama.

**Apa fungsi volume `mysql_data`?**

Volume menyimpan data database secara permanen. Jika container MySQL dimatikan atau dibuat ulang, data tetap aman karena disimpan di volume.

**Kenapa memakai Filament?**

Karena Filament mempercepat pembuatan admin panel Laravel. Fitur CRUD, table, form, filter, action, dan dashboard bisa dibuat lebih rapi dan terstruktur.

**Bagaimana cara sistem menghitung DP?**

Persentase DP diambil dari setting admin. Saat order dibuat, total pesanan dikalikan persentase DP. Hasilnya menjadi `dp_amount`, sedangkan sisanya menjadi `remaining_amount`.

**Bagaimana nomor pesanan dibuat?**

Nomor pesanan dibuat otomatis oleh `OrderObserver` dengan format tanggal dan nomor urut harian, contohnya `ORD-20260519-0001`.

**Bagaimana pembayaran diverifikasi?**

Pelanggan mengirim pembayaran, lalu data masuk ke tabel `payments` dengan status `pending`. Admin membuka panel admin, mengecek bukti pembayaran, lalu memilih verifikasi atau tolak.

**Apakah sistem ini memakai pembayaran otomatis?**

Belum. Sistem ini masih menggunakan verifikasi manual oleh admin, terutama untuk transfer bank. Ini membuat admin tetap bisa mengecek bukti pembayaran sebelum pesanan dikonfirmasi.

## 20. Script Penutup

Jadi kesimpulannya, sistem Catering Markesot ini dibuat untuk membantu proses pemesanan catering dari sisi pelanggan dan pengelolaan operasional dari sisi admin.

Dari sisi teknis, sistem ini menggunakan Laravel sebagai backend, Filament sebagai admin panel, MySQL sebagai database, dan Docker untuk menjalankan service secara terpisah dan konsisten.

Dengan sistem ini, proses pemesanan, pembayaran, verifikasi, pengelolaan menu, dan laporan bisa dilakukan dalam satu aplikasi.

Sekian penjelasan dari saya. Terima kasih.


## 21. Security Hygiene Final

Security hygiene yang sudah diterapkan dan bisa dijelaskan saat presentasi:

- File `.env` tidak dimasukkan ke Git. File ini berisi konfigurasi sensitif seperti password database, SMTP, dan Google OAuth secret.
- Backup SQL seperti `backup-catering.sql` tidak boleh ikut repo karena bisa berisi data user, pesanan, dan credential database.
- `.env.example` hanya berisi contoh key dan placeholder, bukan credential asli.
- Password database di `docker-stack.yml` tidak ditulis langsung. Nilainya diambil dari environment saat deploy.
- Monitoring Prometheus dan Grafana tidak langsung dibuka dari container ke publik, tetapi lewat reverse proxy dan HTTPS.
- Credential admin default tetap ada untuk bootstrap/demo, tetapi harus diganti jika benar-benar masuk production nyata.

Kalimat presentasi:

"Untuk security hygiene, saya memisahkan konfigurasi sensitif dari source code. File `.env` tidak masuk repository, sedangkan `.env.example` hanya berisi contoh konfigurasi. Backup database juga tidak disimpan di Git karena isinya bisa sensitif. Untuk kebutuhan demo, credential bootstrap admin tetap tersedia, tetapi pada production nyata credential tersebut harus diganti."

Analogi sederhana:

"Source code seperti buku resep, sedangkan `.env` seperti kunci dapur. Buku resep boleh dibagikan ke tim, tetapi kunci dapur tidak boleh ditempel di buku resep."

## 22. DevOps Architecture Reasoning

Arsitektur mini production project ini bisa dijelaskan seperti ini:

- Cloudflare berada di depan sebagai DNS/proxy dan membantu lapisan akses HTTPS dari sisi publik.
- Nginx host menerima request dari domain dan subdomain, lalu meneruskan request ke service internal.
- Container Nginx aplikasi menerima request web dan meneruskan request PHP ke Laravel PHP-FPM.
- Container app menjalankan Laravel.
- MySQL menyimpan data utama.
- Redis disiapkan untuk cache atau queue.
- Docker Swarm menjaga service tetap berjalan, mendukung update bertahap, healthcheck, dan rollback.
- Prometheus mengambil metric server/container, lalu Grafana menampilkannya dalam dashboard.

Kalimat presentasi:

"Saya membagi sistem menjadi beberapa lapisan. Pengguna masuk lewat domain dan HTTPS, request diterima Nginx reverse proxy, lalu diteruskan ke service aplikasi di Docker. Dengan Docker Swarm, aplikasi bisa dijalankan sebagai service yang punya healthcheck dan rollback. Monitoring dipisahkan dengan Prometheus dan Grafana agar kondisi server bisa dilihat."

## 23. Monitoring Readiness

Monitoring saat ini sudah cukup untuk presentasi mini production karena sudah ada:

- Prometheus untuk mengambil metric.
- Node Exporter untuk membaca kondisi server seperti CPU, memory, disk, dan network.
- Grafana untuk dashboard visual.
- Reverse proxy HTTPS untuk akses dashboard melalui subdomain.

Alerting belum wajib untuk scope project ini. Alerting bisa dijelaskan sebagai roadmap, misalnya nanti sistem bisa mengirim notifikasi jika CPU terlalu tinggi, disk hampir penuh, atau service down.

Kalimat presentasi:

"Monitoring di project ini fokus pada observability dasar. Saya bisa melihat kondisi server melalui dashboard Grafana yang datanya diambil oleh Prometheus. Untuk project mini production, ini sudah cukup untuk menunjukkan production mindset. Alerting belum saya wajibkan karena scope project masih demo akademik, tetapi bisa dikembangkan sebagai roadmap."

## 24. Testing Prioritas Minimum

Testing yang paling realistis untuk waktu sekarang:

1. Health endpoint test: memastikan `/health` mengembalikan status OK.
2. Landing page test: memastikan halaman utama bisa dibuka.
3. Auth basic test: login gagal dengan credential salah dan login berhasil dengan user valid.
4. Order validation test: pesanan tidak bisa dibuat tanpa item atau tanggal tidak valid.
5. Payment flow test: pembayaran transfer wajib upload bukti.

Prioritas untuk CI/CD demo:

- Jalankan test Laravel dasar di Jenkins sebelum build/push image.
- Minimal test tidak perlu banyak, yang penting menyentuh jalur utama aplikasi.

Kalimat presentasi:

"Untuk testing, saya memprioritaskan jalur paling penting: aplikasi bisa dibuka, healthcheck berjalan, login bekerja, dan order tidak bisa dibuat dengan data yang salah. Jadi test tidak harus banyak, tetapi harus menjaga fitur utama tidak rusak saat ada perubahan."

## 25. Catatan Jujur Untuk Dosen

Hal yang sudah cukup untuk mini production:

- Dockerized Laravel app.
- Reverse proxy Nginx.
- HTTPS dan domain/subdomain.
- Docker Swarm dengan healthcheck dan rollback.
- Monitoring dasar Prometheus, Grafana, dan Node Exporter.
- CI/CD basic dengan Jenkins.

Hal yang masih acceptable untuk demo mahasiswa:

- Credential bootstrap admin masih ada untuk kebutuhan setup awal.
- Verifikasi pembayaran masih manual oleh admin.
- Alerting monitoring belum dibuat.
- Testing masih minimum.

Roadmap production nyata:

- Ganti semua credential demo.
- Tambahkan backup database terjadwal di server, bukan di repo.
- Tambahkan alerting.
- Tambahkan test untuk checkout, pembayaran, dan admin action.
- Pertimbangkan Docker secret untuk credential sensitif.
