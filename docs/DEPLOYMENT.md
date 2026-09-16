# Panduan Deployment

## Requirement server

PHP 8.2+ 64-bit dan Composer 2; MySQL 8.0.16+ atau MariaDB 10.4+; InnoDB dan utf8mb4; Apache 2.4 dengan mod_rewrite atau Nginx + PHP-FPM. Extension aplikasi dan ekspor: pdo_mysql, mbstring, fileinfo, gd, zip, dom, xml, xmlreader, xmlwriter, simplexml, ctype, iconv, filter. OpenSSL untuk koneksi aman; curl untuk installer/test. Periksa `composer check-platform-reqs` pada host aktual.

## Persiapan

1. Review proyek lokal dan jalankan tes.
2. Buat backup sumber, database, dan `storage/uploads`.
3. Pilih host yang dapat mengatur document root menuju `public/`.
4. Siapkan database dan user khusus; jangan memakai root database untuk aplikasi.
5. Pastikan runtime PHP host kompatibel dengan `composer.lock`. Server demo `php -S` tidak dipakai di produksi.

## Shared hosting

Letakkan app, config, database, vendor, views, dan storage di direktori privat di luar `public_html`. Arahkan domain/subdomain document root ke folder `public` proyek bila panel mendukungnya. **Jangan upload root proyek langsung sebagai direktori publik.** Apabila provider memaksa public_html dan tidak mengizinkan public document root yang aman, gunakan subdomain dengan root khusus atau provider lain; jangan mengekspos `.env` untuk mengakali konfigurasi.

Upload source termasuk `composer.lock` dan `public/assets/vendor`. Jangan upload `.runtime`, `.git`, log lokal, hasil QA, atau `.env` development. Jika host tidak menyediakan Composer, install dependency pada lingkungan dengan versi PHP/extension yang sama, kemudian upload folder vendor yang dihasilkan.

```sh
composer install --no-dev --prefer-dist --optimize-autoloader
composer check-platform-reqs
```

## Database dan .env

Buat database kosong berkarakter utf8mb4. Buat `.env` di root privat proyek berdasarkan `.env.example`, dengan credential hosting:

```dotenv
APP_NAME="SIM Inventory"
APP_ENV=production
APP_URL=https://inventory.example.com
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database_hosting
DB_USERNAME=user_aplikasi
DB_PASSWORD=PASSWORD_KUAT_UNIK
SESSION_TIMEOUT=1800
REGISTER_ENABLED=false
```

Import `schema.sql` dan `seed.sql` melalui panel database atau jalankan installer CLI pada database kosong. Seed diperuntukkan demo; untuk penggunaan nyata hapus data demo melalui prosedur migrasi terencana sebelum mencatat operasional. Ganti password dua user demo melalui profil masing-masing atau reset Admin, ubah email sesuai pengguna, dan nonaktifkan akun yang tidak dipakai. Jangan jalankan suite HTTP demo setelah credential produksi berubah.

Untuk migrasi awal perlu CREATE/ALTER/INDEX/REFERENCES sesuai server. Setelah schema terpasang, batasi user aplikasi menjadi SELECT/INSERT/UPDATE/DELETE pada schema aplikasi. Simpan akun migrasi secara terpisah.

## VPS Apache

Contoh virtual host; ganti domain dan path:

```apache
<VirtualHost *:80>
    ServerName inventory.example.com
    DocumentRoot /var/www/sim-inventory/public
    <Directory /var/www/sim-inventory/public>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>
</VirtualHost>
```

Aktifkan mod_rewrite. File `.htaccess` di public mengarahkan route ke index.php. Domain harus berada di root URL, bukan subfolder `/inventory`.

## VPS Nginx + PHP-FPM

```nginx
server {
    listen 80;
    server_name inventory.example.com;
    root /var/www/sim-inventory/public;
    index index.php;
    client_max_body_size 4m;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        try_files $uri =404;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }
    location ~ /\. { deny all; }
}
```

Sesuaikan socket PHP dengan host. Setelah SSL terpasang, redirect HTTP ke HTTPS dan set parameter HTTPS dari terminasi TLS yang terpercaya. Konfigurasi cookie Secure bergantung pada `$_SERVER['HTTPS']`; pada reverse proxy atur melalui konfigurasi server terpercaya, bukan header pengguna sembarang.

## Permission dan PHP

Source read-only bagi web user; `.env` readable hanya oleh owner/web group. Folder `storage/logs`, `storage/uploads`, dan temp ekspor harus writable oleh proses PHP. Contoh Linux: source 644, folder 755, folder storage 750/770 sesuai ownership; jangan memakai 777. Pastikan session save path PHP writable. Atur `display_errors=Off`, `log_errors=On`, `upload_max_filesize=2M`, `post_max_size=4M`, `memory_limit=512M` untuk laporan demo lengkap, atau lebih sesuai volume export. PDF 720 transaksi diuji dengan puncak memori sekitar 282 MB; batas 256 MB tidak cukup untuk ekspor ini.

## HTTPS / SSL

Gunakan SSL dari panel hosting atau ACME/Let's Encrypt. Uji redirect HTTPS, cookie Secure/HttpOnly/SameSite, dan validitas sertifikat. Jangan membagikan URL produksi sebelum akses dan credential sudah ditinjau. Dokumen ini hanya panduan; tidak ada domain/hosting yang dibeli dan tidak ada publish publik dilakukan.

## Smoke test setelah deployment

- Login/logout, sesi Operator ditolak pada users/audit/export.
- Buat barang uji dan masuk 10, keluar 3, pastikan saldo 7; rollback edit/hapus diuji.
- CSS/JS lokal, DataTables, Chart.js, dialog SweetAlert, fetch stok.
- Foto valid bisa dilihat pengguna; file invalid ditolak.
- Laporan filter, PDF terbuka, XLSX terbuka.
- API GET/POST/PUT/DELETE dengan cookie/CSRF/token; token salah ditolak.
- URL `/.env`, `/database/schema.sql`, `/storage/logs/php.log`, `/vendor/autoload.php` tidak memberikan file privat.
- Backup dapat direstore pada database uji terpisah.

## Backup dan pemulihan

Backup harian database konsisten dengan `mysqldump --single-transaction`, folder `storage/uploads`, dan salinan `.env` yang dienkripsi/disimpan privat. Contoh perintah meminta password secara interaktif:

```sh
mysqldump -u backup_user -p --single-transaction --default-character-set=utf8mb4 sim_inventory > /private-backups/sim_inventory.sql
```

Jangan simpan backup di public. Simpan retensi sesuai kebutuhan, misalnya 7 harian + 4 mingguan. Uji restore berkala ke database lain, cocokkan saldo agregat dengan transaksi, lalu uji foto dan login. Upgrade dependency melalui staging, tes dahulu, backup sebelum release, dan pertahankan versi sumber/lock sebelumnya untuk rollback. Jangan rollback sumber yang tidak kompatibel dengan perubahan schema tanpa rencana migrasi.
