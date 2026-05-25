# Panduan Deploy Docker Swarm untuk Catering Markesot

Panduan ini dibuat untuk menjalankan project Catering Markesot di VPS menggunakan Docker Swarm. File ini hanya panduan operasional, bukan perubahan kode aplikasi.

Project saat ini sudah punya beberapa file yang relevan:

- `Dockerfile`
- `docker-compose.yml`
- `docker-compose.monitoring.yml`
- `docker/nginx/default.conf`
- `docker/prometheus/prometheus.yml`

Namun ada satu hal penting: `docker stack deploy` di Docker Swarm tidak melakukan `build` image dari `Dockerfile`. Image harus dibuild lebih dulu, lalu dipush ke registry, baru dipakai oleh stack Swarm.

## 1. Gambaran Arsitektur

Service yang disarankan untuk Swarm:

- `app`: container Laravel PHP-FPM.
- `nginx`: web server yang mengarah ke `app:9000`.
- `mysql`: database utama.
- `redis`: cache atau queue backend.
- `queue`: worker Laravel untuk menjalankan job queue.
- `prometheus`, `grafana`, `node-exporter`: opsional untuk monitoring.

Untuk production, jangan mengandalkan bind mount seluruh project seperti `./:/var/www`, karena di Swarm container bisa pindah node. Lebih aman image sudah berisi source code aplikasi, lalu data persistent disimpan di volume.

## 2. Persiapan VPS

Install Docker Engine dan pastikan service aktif:

```bash
docker --version
docker compose version
docker info
```

Pastikan port berikut terbuka sesuai kebutuhan:

- `80` dan `443`: akses web.
- `2377/tcp`: komunikasi manager Swarm.
- `7946/tcp` dan `7946/udp`: komunikasi antar node.
- `4789/udp`: overlay network Swarm.
- `9090`: Prometheus, jika dipakai.
- `3000`: Grafana, jika dipakai.

Untuk server single node, port internal Swarm tetap perlu aman, tapi tidak harus dibuka ke publik. Buka ke publik hanya port web yang dibutuhkan.

## 3. Inisialisasi Docker Swarm

Di VPS utama:

```bash
docker swarm init
```

Jika VPS punya beberapa IP, pakai advertise address:

```bash
docker swarm init --advertise-addr IP_SERVER
```

Cek node:

```bash
docker node ls
```

Jika nanti ada worker node, ambil token join:

```bash
docker swarm join-token worker
```

## 4. Siapkan Registry Image

Swarm butuh image yang bisa ditarik oleh node. Pilihan registry:

- Docker Hub.
- GitHub Container Registry.
- Registry private.

Contoh memakai Docker Hub:

```bash
docker login
docker build -t username/catering-markesot:1.0.0 .
docker push username/catering-markesot:1.0.0
```

Gunakan tag versi yang jelas, misalnya `1.0.0`, `2026-05-20`, atau hash commit. Hindari hanya mengandalkan `latest` untuk production karena sulit rollback.

## 5. Siapkan File Environment

Di server, siapkan file `.env` production. Jangan commit `.env` ke Git.

Minimal pastikan nilai ini benar:

```dotenv
APP_NAME="Catering Markesot"
APP_ENV=production
APP_KEY=base64:ISI_DENGAN_KEY_PRODUCTION
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=catering
DB_USERNAME=markesot
DB_PASSWORD=password-kuat

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

Buat `APP_KEY` jika belum ada:

```bash
php artisan key:generate --show
```

Untuk Swarm, ada dua pendekatan:

- Simpan `.env` di VPS lalu inject sebagai Docker config atau secret.
- Tulis environment langsung di stack file, tapi jangan isi password sensitif di repo.

Untuk awal, cara paling sederhana adalah menaruh `.env` di server dan membuat Docker config dari file itu.

```bash
docker config create catering_env .env
```

Jika `.env` berubah, Docker config tidak bisa diedit langsung. Buat config baru, misalnya:

```bash
docker config create catering_env_v2 .env
```

Lalu update stack file agar memakai config baru.

## 6. Contoh Stack File untuk Swarm

Buat file deployment khusus, misalnya `docker-stack.yml`, di server atau di repo jika sudah siap. Contoh berikut memakai image yang sudah dipush.

Ganti `username/catering-markesot:1.0.0` dengan image milik Anda.

```yaml
version: "3.8"

services:
  app:
    image: username/catering-markesot:1.0.0
    working_dir: /var/www
    configs:
      - source: catering_env
        target: /var/www/.env
    volumes:
      - app_storage:/var/www/storage
      - app_cache:/var/www/bootstrap/cache
    networks:
      - catering
    deploy:
      replicas: 1
      restart_policy:
        condition: on-failure
      update_config:
        order: start-first
        failure_action: rollback

  nginx:
    image: nginx:latest
    ports:
      - target: 80
        published: 80
        protocol: tcp
        mode: ingress
    configs:
      - source: nginx_default_conf
        target: /etc/nginx/conf.d/default.conf
    volumes:
      - app_storage:/var/www/storage:ro
    networks:
      - catering
    deploy:
      replicas: 1
      restart_policy:
        condition: on-failure

  mysql:
    image: mysql:8
    environment:
      MYSQL_DATABASE: catering
      MYSQL_ROOT_PASSWORD: root-password-kuat
      MYSQL_USER: markesot
      MYSQL_PASSWORD: password-kuat
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - catering
    deploy:
      replicas: 1
      placement:
        constraints:
          - node.role == manager
      restart_policy:
        condition: on-failure

  redis:
    image: redis:latest
    volumes:
      - redis_data:/data
    networks:
      - catering
    deploy:
      replicas: 1
      restart_policy:
        condition: on-failure

  queue:
    image: username/catering-markesot:1.0.0
    command: php artisan queue:work --sleep=3 --tries=3 --timeout=90
    working_dir: /var/www
    configs:
      - source: catering_env
        target: /var/www/.env
    volumes:
      - app_storage:/var/www/storage
      - app_cache:/var/www/bootstrap/cache
    networks:
      - catering
    deploy:
      replicas: 1
      restart_policy:
        condition: on-failure

networks:
  catering:
    driver: overlay

volumes:
  mysql_data:
  redis_data:
  app_storage:
  app_cache:

configs:
  catering_env:
    external: true
  nginx_default_conf:
    external: true
```

Catatan penting untuk config Nginx:

- `docker stack deploy` tidak bisa langsung memakai bind mount file config dengan nyaman jika multi-node.
- Lebih rapi buat Docker config dari file Nginx:

```bash
docker config create nginx_default_conf docker/nginx/default.conf
```

Jika config berubah, buat nama baru:

```bash
docker config create nginx_default_conf_v2 docker/nginx/default.conf
```

Lalu update stack file.

## 7. Deploy Stack

Deploy stack:

```bash
docker stack deploy -c docker-stack.yml catering
```

Cek service:

```bash
docker stack services catering
docker stack ps catering
```

Lihat log:

```bash
docker service logs catering_app
docker service logs catering_nginx
docker service logs catering_queue
docker service logs catering_mysql
```

## 8. Jalankan Perintah Laravel di Swarm

Cari container `app` yang sedang jalan:

```bash
docker ps --filter name=catering_app
```

Masuk ke container:

```bash
docker exec -it CONTAINER_ID bash
```

Lalu jalankan:

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan optimize
```

Jika seeder perlu dijalankan hanya saat awal:

```bash
php artisan db:seed --force
```

Hati-hati menjalankan seeder di production jika data sudah ada.

## 9. Storage Upload

Aplikasi memakai upload gambar dan bukti pembayaran. Karena itu folder `storage` harus persistent.

Di contoh stack, `app_storage` dipakai bersama oleh `app`, `queue`, dan `nginx`. Untuk single-node ini cukup. Untuk multi-node, volume lokal biasa bisa bermasalah karena data hanya ada di satu node.

Jika ingin multi-node yang benar-benar aman, gunakan storage terpusat:

- S3 compatible storage.
- NFS.
- GlusterFS atau driver volume distributed lain.

Untuk Laravel, storage publik harus bisa diakses lewat `/storage`. Pastikan `php artisan storage:link` sudah jalan di image atau container.

## 10. HTTPS dan Reverse Proxy

Ada dua pilihan:

1. Terminate HTTPS di luar stack, misalnya Nginx host, Caddy, atau Traefik.
2. Masukkan reverse proxy HTTPS ke Swarm.

Untuk awal, yang paling sederhana:

- Stack expose port `80`.
- VPS host memakai Nginx/Caddy sebagai reverse proxy HTTPS ke port tersebut.
- `.env` tetap pakai `APP_URL=https://domain-anda.com`.

Jika memakai Traefik di Swarm, nanti service `nginx` bisa diberi label Traefik. Itu lebih rapi untuk multi-service, tapi setup awalnya lebih panjang.

## 11. Update Aplikasi

Build image baru:

```bash
docker build -t username/catering-markesot:1.0.1 .
docker push username/catering-markesot:1.0.1
```

Ubah image di `docker-stack.yml` dari `1.0.0` ke `1.0.1`, lalu deploy ulang:

```bash
docker stack deploy -c docker-stack.yml catering
```

Cek proses update:

```bash
docker stack ps catering
docker service ls
```

Setelah update, jalankan migration jika ada:

```bash
docker exec -it CONTAINER_ID php artisan migrate --force
```

## 12. Rollback

Jika deploy gagal dan `failure_action: rollback` aktif, Swarm bisa rollback otomatis. Rollback manual:

```bash
docker service rollback catering_app
docker service rollback catering_queue
```

Jika masalah berasal dari versi image, ubah lagi tag image di `docker-stack.yml` ke versi sebelumnya lalu deploy ulang:

```bash
docker stack deploy -c docker-stack.yml catering
```

## 13. Monitoring Opsional

File `docker-compose.monitoring.yml` saat ini berisi Prometheus, Grafana, dan node-exporter. Untuk Swarm, prinsipnya sama, tetapi sebaiknya dibuat stack terpisah, misalnya `monitoring`.

Contoh deploy:

```bash
docker stack deploy -c docker-compose.monitoring.yml monitoring
```

Pastikan network eksternal yang dipakai benar-benar ada. Jika stack utama bernama `catering`, network biasanya bernama:

```txt
catering_catering
```

Jika file monitoring masih menunjuk ke nama lama, sesuaikan nama network saat membuat stack monitoring.

## 14. Hal yang Perlu Dihindari

Jangan commit file `.env`.

Jangan commit folder sementara seperti `temp-laravel/`.

Jangan commit perubahan permission massal `100644 => 100755`, kecuali memang file tersebut harus executable. Untuk source PHP, Blade, CSS, JS, config, dan README, biasanya tidak perlu executable.

Jangan expose MySQL ke publik. Di Swarm, MySQL cukup berada di overlay network internal.

Jangan menjalankan `db:seed --force` berulang di production tanpa memastikan seedernya aman terhadap data existing.

## 15. Checklist Deploy Pertama

1. Docker sudah terinstall di VPS.
2. `docker swarm init` sudah dijalankan.
3. Image aplikasi sudah dibuild dan dipush ke registry.
4. `.env` production sudah dibuat di VPS.
5. Docker config untuk `.env` sudah dibuat.
6. Docker config untuk Nginx sudah dibuat.
7. `docker-stack.yml` sudah memakai image dan config yang benar.
8. Stack sudah dideploy dengan `docker stack deploy`.
9. `php artisan migrate --force` sudah dijalankan.
10. `php artisan storage:link` sudah dijalankan.
11. Domain dan HTTPS sudah diarahkan ke service web.
12. Upload gambar dan bukti pembayaran sudah dites.
13. Login admin dan checkout pelanggan sudah dites.
14. Queue worker berjalan.
15. Backup database sudah disiapkan.

## 16. Perintah Cepat

Deploy:

```bash
docker stack deploy -c docker-stack.yml catering
```

Cek service:

```bash
docker stack services catering
```

Cek task/container:

```bash
docker stack ps catering
```

Log service:

```bash
docker service logs -f catering_app
docker service logs -f catering_nginx
docker service logs -f catering_queue
```

Masuk container:

```bash
docker exec -it CONTAINER_ID bash
```

Hapus stack:

```bash
docker stack rm catering
```

Hapus stack tidak otomatis menghapus volume. Volume database tetap ada sampai dihapus manual.



## 17. Secret dan Config Hygiene

Untuk deployment mini production, file konfigurasi sensitif tidak sebaiknya ditulis langsung di repository.

Prinsip yang dipakai:

- `.env` production disimpan di server atau Jenkins credential, bukan di Git.
- `docker-stack.yml` hanya membaca variable seperti `DB_PASSWORD` dan `MYSQL_ROOT_PASSWORD`.
- Backup SQL tidak disimpan di repo.
- `.env.example` hanya berisi placeholder agar tim tahu key apa saja yang diperlukan.

Contoh sebelum deploy manual:

```bash
set -a
. ./.env
set +a
docker stack deploy -c docker-stack.yml catering
```

Penjelasan sederhana:

`docker-stack.yml` adalah peta service, sedangkan `.env` adalah data rahasia perjalanan. Peta boleh dibagikan, data rahasia jangan ikut repo.

## 18. Penjelasan Mini Production Untuk Presentasi

Project ini belum dibuat seperti enterprise besar, tetapi sudah menunjukkan production mindset dasar:

- Service dipisah menjadi app, nginx, mysql, redis, dan monitoring.
- Data penting disimpan di volume agar tidak hilang saat container dibuat ulang.
- Healthcheck dipakai agar service bisa dipantau sehat atau tidak.
- Update Swarm dibuat bertahap dengan rollback jika gagal.
- Domain publik masuk lewat reverse proxy dan HTTPS.
- Monitoring disediakan melalui Prometheus dan Grafana.

Hal yang tidak wajib untuk scope ini:

- Kubernetes.
- Service mesh.
- Autoscaling kompleks.
- Alerting lengkap.
- Secret manager enterprise.

Kalimat presentasi:

"Saya sengaja memakai pendekatan mini production yang realistis. Fokusnya bukan menambah teknologi sebanyak mungkin, tetapi membuat deployment lebih rapi: service terpisah, ada healthcheck, ada rollback, ada volume, ada HTTPS, dan ada monitoring dasar."
