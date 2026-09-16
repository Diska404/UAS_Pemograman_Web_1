# Laporan Project Akhir Pemrograman Web 1

## COVER

**SISTEM INFORMASI MANAJEMEN INVENTORI BARANG BERBASIS WEB**  
SIM Inventory

Disusun oleh: **[NAMA MAHASISWA]**  
NIM: **[NIM]**  
Kelas: **[KELAS]**  
Dosen Pengampu: **[NAMA DOSEN]**  
Program Studi Teknik Informatika  
**[UNIVERSITAS]**  
**[TAHUN AKADEMIK]**

---

## BAB I — PENDAHULUAN

### 1.1 Latar Belakang

Organisasi memerlukan informasi persediaan yang tepat agar kebutuhan operasional dapat dipenuhi. Pengelolaan barang yang dilakukan melalui dokumen terpisah dapat menyebabkan keterlambatan pembaruan saldo dan kesulitan dalam menelusuri transaksi. Aplikasi web dengan database terpusat dapat menghubungkan data barang, supplier, gudang, dan transaksi sehingga pencatatan lebih teratur.

SIM Inventory dibangun menggunakan PHP native berorientasi objek dan PDO untuk memenuhi pembelajaran Pemrograman Web 1. Implementasi menekankan fungsi yang dapat diuji, keamanan dasar, konsistensi stok, dan dokumentasi yang sesuai kode. Data pada project merupakan data fiktif untuk demonstrasi, bukan hasil observasi organisasi tertentu.

### 1.2 Identifikasi Masalah

Pencarian data master belum efisien apabila catatan tersebar. Perubahan saldo manual berisiko tidak sesuai saat transaksi dikoreksi. Pengeluaran tanpa pemeriksaan saldo dapat menghasilkan stok negatif. Pengguna memerlukan hak akses yang berbeda dan laporan yang dapat diekspor tanpa pengolahan ulang.

### 1.3 Rumusan Masalah

Bagaimana merancang serta mengimplementasikan sistem inventori berbasis web yang mengintegrasikan master dan transaksi, menjaga konsistensi stok, membatasi akses pengguna, serta menyajikan dashboard dan laporan?

### 1.4 Tujuan

Membangun aplikasi inventori lengkap dengan CRUD, autentikasi/otorisasi, REST API, laporan, dan pengujian. Menerapkan OOP, prepared statement, transaksi database, validasi, serta pemisahan tanggung jawab kode agar mudah dipahami dan dikembangkan.

### 1.5 Manfaat

Pengguna memperoleh informasi saldo dan riwayat yang terpusat. Admin dapat mengelola akses dan mengevaluasi stok rendah. Mahasiswa memperoleh pengalaman implementasi sistem informasi dari analisis hingga persiapan deployment.

## BAB II — ANALISIS DAN PERANCANGAN

### 2.1 Analisis Kebutuhan

Kebutuhan fungsional mencakup master barang/gudang/supplier, transaksi masuk/keluar, dashboard, user, profil, audit, API, serta laporan. Kebutuhan nonfungsional meliputi keamanan request, integritas referensial, responsivitas, dan keterbacaan kode. Daftar terstruktur F01–F12 tersedia dalam [analisis sistem](01_ANALISIS_SISTEM.md).

Sistem dibatasi pada satu gudang per barang dan saldo saat ini. Penilaian harga persediaan, batch, expiry, transfer khusus, dan integrasi marketplace tidak termasuk lingkup.

### 2.2 Aktor dan Use Case

Admin mengakses seluruh modul, sementara Operator membaca master dan mengelola transaksi operasional. Tamu dapat mendaftar sebagai Operator. Pengaturan akses diperiksa pada server; menyembunyikan tombol hanya membantu antarmuka. Diagram: [USE_CASE.md](diagrams/USE_CASE.md).

### 2.3 Activity Diagram

Login memvalidasi CSRF, format, throttle, hash password, dan status aktif sebelum sesi diregenerasi. Transaksi barang masuk menambah saldo; barang keluar menguranginya. Edit/hapus membalik efek lama dalam satu transaksi database. Lihat [activity login](diagrams/ACTIVITY_LOGIN.md), [barang masuk](diagrams/ACTIVITY_BARANG_MASUK.md), dan [barang keluar](diagrams/ACTIVITY_BARANG_KELUAR.md).

### 2.4 Flowchart dan Arsitektur

Request diterima public/index.php, diarahkan Router, diperiksa controller, lalu diproses service/model. View menghasilkan HTML atau controller API menghasilkan JSON. Data privat dan credential berada di luar public. [Flowchart sistem](diagrams/FLOWCHART.md) menjelaskan percabangan read/write dan error.

### 2.5 ERD dan Perancangan Database

Database memiliki sepuluh tabel: roles, users, gudang, supplier, barang, barang_masuk, barang_keluar, audit_logs, api_tokens, login_attempts. FK menghubungkan master dan transaksi, kode unik mencegah duplikasi, serta CHECK membatasi jumlah/stok. Stok merupakan saldo tersimpan untuk efisiensi yang dipelihara hanya melalui service transaksi. [ERD](diagrams/ERD.md) dan [kamus database](DATABASE.md) memuat detail.

### 2.6 Class Diagram

Controller abstrak menjadi induk controller fitur. Database membungkus koneksi PDO. Resource menyediakan akses data generik berdasarkan metadata terpercaya. InventoryService mengisolasi aturan saldo, sedangkan UploadService dan ReportService memisahkan foto serta export. [Class diagram](diagrams/CLASS_DIAGRAM.md) sesuai nama class aktual.

## BAB III — IMPLEMENTASI

### 3.1 Lingkungan Pengembangan

Pengembangan dilakukan pada Windows dengan PHP 8.4.25 portabel, MariaDB 10.4.32 dari XAMPP, dan Composer 2. Database demo dijalankan pada port 3307 dengan direktori privat terpisah. Aplikasi disajikan melalui PHP development server port 8000. Bootstrap, DataTables, Chart.js, dan SweetAlert2 disertakan lokal agar tidak bergantung pada CDN saat presentasi.

### 3.2 Struktur Project dan OOP

Folder app dibagi menjadi Core, Controllers, Models, Services, Helpers. Config memuat environment dan metadata resource; views menampung presentasi. Inheritance dipakai pada controller, encapsulation pada state internal, dan composition pada service. Satu template CRUD dipakai ulang untuk menghindari kode duplikat. Rincian ada pada [ARCHITECTURE.md](ARCHITECTURE.md).

### 3.3 Database

Schema dan seed disediakan terpisah. Installer CLI menolak database yang tidak kosong. Data demo terdiri dari dua role, dua user, tiga gudang, sepuluh supplier, 60 barang dalam enam kategori, 720 transaksi masuk, dan 720 transaksi keluar bertanggal tetap Oktober 2025–September 2026. Password seed disimpan sebagai hash dan saldo total seed 681 sama dengan selisih pergerakan.

### 3.4 Authentication dan Authorization

Login memanggil password_verify terhadap hash database, memeriksa status aktif, dan membatasi percobaan gagal. Sesi menggunakan ID baru dan last_activity. Register selalu memasukkan role Operator dari database, bukan input role pengguna. Logout POST memverifikasi CSRF lalu menghapus sesi/cookie. Admin-only operation diperiksa server-side dan API mutasi memerlukan Bearer Admin.

[SCREENSHOT LOGIN DAN REGISTER]

### 3.5 CRUD dan Pengelolaan Stok

Barang, gudang, supplier memiliki create/read/update/delete lengkap dengan unique constraint. Data yang direferensikan tidak dapat dihapus. Foto disimpan sebagai PNG hasil re-encode di storage privat.

InventoryService menghitung delta: pembalikan lama ditambah efek baru. Misalnya masuk 10 lalu keluar 4 menghasilkan 6; edit masuk menjadi 12 menghasilkan 8. Kunci FOR UPDATE mencegah dua koneksi memakai saldo lama secara bersamaan. Exception menyebabkan rollback stok, transaksi, dan audit.

[SCREENSHOT DATA BARANG, FILTER, FORM MASUK, FORM KELUAR DAN VALIDASI STOK]

### 3.6 Dashboard dan Interaksi Frontend

Dashboard menampilkan enam KPI utama dan ringkasan master, empat grafik (pergerakan 6/12 bulan atau YTD, kategori berdasarkan unit stok, distribusi gudang, dan kesehatan stok), delapan transaksi terbaru, serta maksimal delapan barang prioritas dengan stok habis dahulu. Chart.js memperoleh JSON melalui Fetch API. Form transaksi mengambil saldo barang terpilih tanpa memuat ulang halaman. DataTables menangani pencarian, sorting, dan pagination client-side; SweetAlert2 menyediakan konfirmasi serta notifikasi.

[SCREENSHOT DASHBOARD DESKTOP DAN MOBILE]

### 3.7 REST API

API JSON menyediakan GET/POST/PUT/DELETE barang serta GET stok/dashboard. Login API menghasilkan token acak yang disimpan sebagai hash SHA-256 dengan masa berlaku delapan jam. Mutasi memerlukan token Admin dan CSRF. Status 4xx menjelaskan validasi/akses, sementara 500 tidak menampilkan detail database. Collection Postman mempermudah demonstrasi berurutan dari token sampai delete barang uji.

[SCREENSHOT POSTMAN GET, POST, PUT, DELETE DAN UNAUTHORIZED]

### 3.8 Security

Keamanan mencakup PDO prepared statement, htmlspecialchars, token CSRF, hash password, upload terverifikasi, row lock, cookie HttpOnly/SameSite/Secure, CSP, dan audit. Spreadsheet menggunakan string eksplisit untuk mencegah formula injection. Error detail disimpan privat. Kontrol dan batasnya dijelaskan pada [SECURITY.md](SECURITY.md).

### 3.9 Laporan PDF dan Excel

Laporan HTML mendukung stok, masuk, dan keluar. Periode, barang, gudang, serta supplier pada barang masuk dapat difilter. Admin mengekspor filter yang sama ke PDF melalui Dompdf dan XLSX melalui PhpSpreadsheet. Dokumen mencantumkan judul, periode, dan waktu cetak. Laporan stok adalah saldo saat ini, bukan saldo historis per tanggal.

[SCREENSHOT LAPORAN HTML, PDF, DAN EXCEL]

## BAB IV — PENGUJIAN

### 4.1 Metode Black Box

Suite melakukan request HTTP nyata pada server lokal. Respons status dan keadaan database diperiksa, bukan hanya keberadaan file. Skenario meliputi autentikasi, CRUD, token, CSRF, XSS, FK, upload, stok, laporan, dan pembacaan ulang XLSX. Hasil lengkap yang dihasilkan suite terdapat pada [BLACK_BOX_TESTING.md](BLACK_BOX_TESTING.md).

### 4.2 Pengujian Concurrency

Dua proses PHP dengan koneksi PDO terpisah mencoba mengeluarkan masing-masing 4 dari stok 5. Hasil pemeriksaan menunjukkan satu transaksi berhasil, satu ditolak, dan saldo akhir 1. Skenario edit transaksi berpindah barang juga diuji. Rincian: [CONCURRENCY_TESTING.md](CONCURRENCY_TESTING.md).

### 4.3 Pengujian Antarmuka

Login Admin, filter IoT & Embedded/gudang/status, pencarian galeri, urutan stok, pagination, navigasi keyboard, dashboard 12 bulan, serta viewport mobile 390×844 dan tablet 768×1024 diperiksa langsung. Grafik dirender dari data aktual dan tidak ditemukan error/warning konsol pada sesi pemeriksaan. Rincian: [UI_TESTING.md](UI_TESTING.md).

### 4.4 Hasil Regresi Final

Eksekusi akhir menghasilkan 78/78 assertion HTTP, 7/7 concurrency, dan 30/30 data/filter PASS; Newman menjalankan 11 request dengan 9 assertion tanpa kegagalan. Lint mencakup 48 file tanpa error. Impor kosong menghasilkan 60 barang dengan saldo 681. PDF stok memuat 60 data; PDF masuk/keluar masing-masing 720 data. Excel dibaca ulang dan input mirip formula tersimpan sebagai teks. PDF history penuh membutuhkan batas memori 512 MB (puncak sekitar 282 MB); konfigurasi lokal telah disesuaikan. Bukti lengkap ada pada [FINAL_AUDIT](FINAL_AUDIT.md).

### 4.5 Evaluasi

Hasil uji lokal mendukung bahwa alur utama bekerja pada lingkungan yang diuji. Pengujian ini belum mencakup beban produksi, seluruh browser, atau audit keamanan eksternal. Dataset kecil memakai DataTables client-side; peningkatan volume membutuhkan pagination server. Pemeriksaan setelah deployment tetap diperlukan karena versi PHP, permission, TLS, dan konfigurasi web server dapat berbeda.

## BAB V — DEPLOYMENT

### 5.1 Konfigurasi dan Proses

Aplikasi dipersiapkan untuk shared hosting atau VPS dengan document root public. Credential berada di .env privat, dependency dipasang sesuai lock, dan database diimport ke schema kosong. Storage harus dapat ditulis proses PHP dengan permission terbatas. Detail Apache/Nginx tersedia dalam [DEPLOYMENT.md](DEPLOYMENT.md).

### 5.2 SSL dan Pengujian

Produksi memerlukan HTTPS agar cookie Secure aktif. Ganti password demo, batasi register, dan pastikan URL privat tidak dapat diunduh. Smoke test mencakup login, role, saldo, export, upload, API, serta pemulihan backup. Tidak ada deployment publik atau pembelian hosting yang dilakukan pada proyek ini.

## BAB VI — PENUTUP

### 6.1 Kesimpulan

SIM Inventory mengintegrasikan pengelolaan master, transaksi, dan saldo dalam aplikasi web PHP native. Pemisahan controller/service/model memudahkan penjelasan OOP, sementara transaksi dan row lock menjaga konsistensi persediaan. Dashboard, API, laporan, serta dokumentasi mendukung demonstrasi project akhir dan pengembangan lebih lanjut.

### 6.2 Saran

Pengembangan berikutnya dapat menambahkan mutasi antar-gudang, histori snapshot, barcode, pencabutan seluruh sesi perangkat, pembatasan laju terdistribusi, serta pagination server untuk data besar. Identitas mahasiswa, format institusi, screenshot final, dan hasil smoke test hosting perlu dilengkapi sebelum pengumpulan.

## Referensi teknis

- [PHP Manual](https://www.php.net/manual/en/) — PDO, session, password.
- [Dompdf](https://github.com/dompdf/dompdf) — rendering PDF.
- [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/) — XLSX.
- [Bootstrap](https://getbootstrap.com/), [DataTables](https://datatables.net/), [Chart.js](https://www.chartjs.org/), [SweetAlert2](https://sweetalert2.github.io/) — antarmuka.
