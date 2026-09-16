| **Nama** | Diska Kurnia Azzahra Putra |
| --- | --- |
| **NIM** | 312210369 |
| **Kelas** | [TIF26](https://edlink.id/panel/classes/2027477/info) |
| **Mata Kuliah** | Pemrograman Web 1 |
| **Dosen Pengajar** | Wahyu Hadikristanto, S.Kom., M.Kom. |

# SIM Inventory - Sistem Informasi Manajemen Inventori Barang

**Inventory Barang By Diska Kurnia Azzahra Putra**

## Tentang Project

Repository ini berisi project akhir mata kuliah Pemrograman Web 1 berupa Sistem Informasi Manajemen Inventori Barang berbasis web. Aplikasi dikembangkan menggunakan PHP Native dengan pendekatan OOP, PDO, MySQL/MariaDB, Bootstrap 5, dan JavaScript.

SIM Inventory digunakan untuk mengelola data barang, gudang, supplier, transaksi barang masuk dan keluar, laporan, pengguna, audit log, serta REST API. Perubahan stok dilakukan melalui transaksi supaya setiap penambahan dan pengurangan persediaan memiliki riwayat yang jelas.

Dataset demo berisi 60 barang, 6 kategori, 3 gudang, 10 supplier fiktif, 720 transaksi masuk, dan 720 transaksi keluar. Saldo akhir dataset adalah 681 unit dengan 40 barang aman, 15 stok rendah, dan 5 stok habis.

## Fitur Utama

- Login, register akun Operator, logout, session timeout, dan pembatasan percobaan login.
- Dashboard KPI, grafik pergerakan, kategori, gudang, kesehatan stok, dan aktivitas terbaru.
- CRUD data barang, gudang, dan supplier.
- Table View dan Gallery View pada Data Barang.
- Pencarian, filter kategori, gudang, status stok, sorting, dan pagination.
- Transaksi barang masuk yang menambah stok.
- Transaksi barang keluar dengan pemeriksaan ketersediaan stok.
- Koreksi dan penghapusan transaksi dengan perhitungan ulang saldo secara atomic.
- Laporan stok, barang masuk, dan barang keluar.
- Export laporan PDF dan Excel.
- Manajemen pengguna, role Admin/Operator, status akun, dan reset password.
- Audit log aktivitas penting.
- Profil dan ganti password.
- REST API v1 menggunakan Bearer token.
- Tampilan responsif untuk desktop dan mobile.

## Teknologi yang Digunakan

| Teknologi | Penggunaan |
| --- | --- |
| PHP 8 | Backend, routing, controller, service, validasi, dan proses form |
| MySQL / MariaDB | Penyimpanan data relasional |
| PDO | Prepared statement dan transaksi database |
| Bootstrap 5 | Layout dan komponen antarmuka |
| JavaScript ES6 | Interaksi halaman |
| Fetch API | Pengambilan data dinamis |
| REST API | Akses data dalam format JSON |
| Chart.js | Grafik dashboard dan laporan |
| DataTables | Search, sorting, dan pagination tabel |
| SweetAlert2 | Dialog konfirmasi dan status aksi |
| Dompdf | Export laporan PDF |
| PhpSpreadsheet | Export laporan Excel |
| Git | Pencatatan perubahan source code |

## Alur Sistem

Pengguna membuka aplikasi, melakukan login, lalu server memeriksa akun dan role. Setelah berhasil, pengguna masuk ke dashboard dan dapat membuka modul sesuai hak aksesnya.

```text
Pengguna
   ↓
Login → Validasi akun → Dashboard
   ↓
Master Data: Barang, Gudang, Supplier
Transaksi: Barang Masuk, Barang Keluar
Laporan: Stok, Barang Masuk, Barang Keluar, PDF, Excel
Sistem: Pengguna, Audit Log
   ↓
Logout
```

### Flowchart Sistem

Flowchart berikut menunjukkan pemrosesan request dari browser atau API sampai menghasilkan HTML, JSON, PDF, atau Excel.

![Flowchart Sistem](docs/diagrams/FLOWCHART.png)

### Activity Diagram Transaksi Inventori

Pada transaksi barang masuk, stok akan bertambah. Pada transaksi barang keluar, sistem memeriksa stok terlebih dahulu. Jika jumlah tidak mencukupi, transaksi ditolak.

![Activity Diagram Transaksi Inventori](docs/diagrams/ACTIVITY_INVENTORI.png)

## Use Case

Use Case Diagram memperlihatkan fitur yang dapat digunakan oleh Admin dan Operator. Admin memiliki akses tambahan untuk mengelola data tertentu, pengguna, audit log, serta operasi API yang membutuhkan hak administrator.

![Use Case SIM Inventory](docs/diagrams/USE_CASE.png)

## Struktur Database

Database menggunakan tabel `roles`, `users`, `gudang`, `supplier`, `barang`, `barang_masuk`, `barang_keluar`, `audit_logs`, `api_tokens`, dan `login_attempts`. Foreign key digunakan untuk menjaga hubungan antar data.

![Entity Relationship Diagram](docs/diagrams/ERD.png)

Class Diagram dan diagram aktivitas tambahan dapat dilihat pada folder [`docs/diagrams/`](docs/diagrams/).

## Implementasi

### 1. Login

Halaman login menjadi akses awal pengguna. Email dan password diverifikasi oleh server, kemudian pengguna diarahkan ke dashboard sesuai role. Terdapat pilihan **Ingat Email Saya** dan tombol untuk menampilkan atau menyembunyikan password.

![Halaman Login](docs/screenshots/01-login.png)

### 2. Register Operator

Pendaftaran publik hanya membuat akun dengan role Operator. Pengguna mengisi nama, email, password minimal 10 karakter, dan konfirmasi password.

![Register Operator](docs/screenshots/15-register-operator.png)

### 3. Dashboard

Dashboard menampilkan jumlah barang, saldo unit, transaksi bulan berjalan, stok rendah, dan stok habis. Grafik digunakan untuk melihat pergerakan inventori, komposisi kategori, distribusi gudang, serta kesehatan stok.

![Dashboard SIM Inventory](docs/screenshots/02-dashboard.png)

### 4. Data Barang

Data Barang memiliki pencarian, filter kategori, gudang, status stok, sorting, dan pagination. Pengguna dapat memilih Table View untuk membandingkan data atau Gallery View untuk melihat kartu dan foto barang.

| Table View | Gallery View |
| --- | --- |
| ![Table View Data Barang](docs/screenshots/03-barang-table.png) | ![Gallery View Data Barang](docs/screenshots/04-barang-gallery.png) |

Barang baru memiliki stok awal 0. Perubahan stok dilakukan melalui Barang Masuk dan Barang Keluar supaya riwayat tetap tercatat.

### 5. Tambah dan Edit Barang

Form barang digunakan untuk mengisi kode, nama, kategori, satuan, stok minimum, gudang, deskripsi, dan foto. Screenshot berikut menunjukkan proses perubahan informasi barang. Nilai stok tidak diubah dari form ini.

![Edit Data Barang](docs/screenshots/17-edit-barang.png)

### 6. Gudang

Modul Gudang mencatat kode gudang, nama, lokasi, dan keterangan. Data ini dipakai untuk menunjukkan lokasi barang dan menjadi filter laporan.

![Data Gudang](docs/screenshots/18-gudang.png)

### 7. Supplier

Modul Supplier menyimpan kode, nama, alamat, telepon, email, dan keterangan pemasok. Supplier dipilih ketika mencatat transaksi barang masuk.

![Data Supplier](docs/screenshots/19-supplier.png)

### 8. Barang Masuk

Barang Masuk digunakan untuk mencatat penerimaan barang. Setelah tanggal, barang, supplier, jumlah, dan keterangan valid, transaksi disimpan dan stok otomatis bertambah.

| Daftar Barang Masuk | Form Barang Masuk |
| --- | --- |
| ![Daftar Barang Masuk](docs/screenshots/06-barang-masuk.png) | ![Form Barang Masuk](docs/screenshots/20-form-barang-masuk.png) |

### 9. Barang Keluar

Barang Keluar digunakan untuk mencatat pemakaian atau pengeluaran. Sistem memeriksa stok pada server. Transaksi berhasil mengurangi stok jika jumlah tersedia; transaksi ditolak jika stok tidak cukup.

| Daftar Barang Keluar | Form Barang Keluar |
| --- | --- |
| ![Daftar Barang Keluar](docs/screenshots/07-barang-keluar.png) | ![Form Barang Keluar](docs/screenshots/21-form-barang-keluar.png) |

### 10. Laporan Inventori

Laporan stok menampilkan jumlah barang, saldo unit, stok rendah, stok habis, grafik kategori, dan distribusi gudang. Filter dapat menggunakan barang, gudang, serta status stok yang sesuai.

![Laporan Stok](docs/screenshots/08-laporan-stok.png)

### 11. Laporan Barang Masuk

Laporan Barang Masuk menyediakan filter tanggal, barang, gudang, dan supplier. Ringkasan menampilkan jumlah transaksi, jumlah unit, tren, barang teratas, serta kontribusi supplier.

![Laporan Barang Masuk](docs/screenshots/09-laporan-masuk.png)

### 12. Laporan Barang Keluar

Laporan Barang Keluar menampilkan jumlah transaksi, unit keluar, tren, barang yang paling banyak digunakan, dan distribusi tujuan atau gudang.

![Laporan Barang Keluar](docs/screenshots/10-laporan-keluar.png)

### 13. PDF dan Excel

Data hasil filter dapat diekspor ke PDF untuk dokumentasi atau pencetakan dan ke Excel untuk pengolahan lebih lanjut.

| Hasil PDF | Hasil Excel |
| --- | --- |
| ![Hasil Export PDF](docs/screenshots/23-export-pdf.png) | ![Hasil Export Excel](docs/screenshots/24-export-excel.png) |

### 14. Manajemen Pengguna

Admin dapat melihat daftar pengguna, role, dan status akun. Aksi yang tersedia meliputi perubahan role, aktivasi atau nonaktivasi akun, serta reset password.

![Manajemen Pengguna](docs/screenshots/11-pengguna.png)

### 15. Audit Log

Audit Log mencatat waktu, pengguna, aktivitas, deskripsi, dan alamat IP. Log membantu menelusuri login, perubahan data, transaksi, dan tindakan administrator.

![Audit Log](docs/screenshots/22-audit-log.png)

### 16. Profil dan Keamanan

Setiap pengguna dapat mengubah nama dan email sendiri. Password dapat diganti setelah memasukkan password saat ini, password baru, dan konfirmasi.

![Profil dan Keamanan](docs/screenshots/12-profil.png)

### 17. REST API

REST API v1 menyediakan operasi `GET`, `POST`, `PUT`, dan `DELETE`. Koleksi Postman berada pada [`docs/postman/`](docs/postman/). Contoh berikut menampilkan request `GET /api/v1/barang` yang berhasil menerima response **200 OK** dalam format JSON. Bearer token tidak ditampilkan.

![Response REST API 200 OK](docs/screenshots/25-rest-api-response.png)

### 18. Responsive Mobile

Pada layar seluler, sidebar berubah menjadi menu yang dapat dibuka dari bagian atas. Kartu dashboard disusun vertikal agar nilai dan tombol tetap mudah dibaca.

<p align="center">
  <img src="docs/screenshots/14-responsive-mobile.png" alt="Dashboard Responsive Mobile" width="340">
</p>

## Pengujian

Hasil verifikasi final repository:

| Kelompok Pengujian | Hasil |
| --- | --- |
| HTTP aplikasi, security, CRUD, dan API | 78/78 PASS |
| Concurrency transaksi stok | 7/7 PASS |
| Data, dashboard, dan filter | 30/30 PASS |
| Analitik laporan dan login UX | 21/21 PASS |
| Postman/Newman | 11 request, 9 assertion, 0 failure |
| PHP lint | 50 file aplikasi dan test, 0 error |

Skenario pengujian dan catatan hasil tersedia pada:

- [Black-box testing](docs/BLACK_BOX_TESTING.md)
- [Concurrency testing](docs/CONCURRENCY_TESTING.md)
- [Read-model testing](docs/READ_MODEL_TESTING.md)
- [UI testing](docs/UI_TESTING.md)
- [Final audit](docs/FINAL_AUDIT.md)

## Instalasi dan Menjalankan Project

### Persyaratan

- PHP 8.2 atau lebih baru.
- MySQL atau MariaDB.
- Composer 2.
- Extension PHP `pdo_mysql`, `mbstring`, `fileinfo`, dan `gd`.

### Menjalankan Demo pada Workspace Ini

PHP portabel, dependency Composer, `.env`, dan database demo lokal sudah tersedia. Buka PowerShell pada root project lalu jalankan:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/start-demo-db.ps1
powershell -ExecutionPolicy Bypass -File scripts/start-local.ps1
```

Aplikasi dapat dibuka melalui:

```text
http://localhost:8000
```

Database demo berjalan pada `127.0.0.1:3307` dengan schema `sim_inventory`.

### Instalasi Baru

1. Pasang dependency dan buat file konfigurasi:

   ```powershell
   composer install
   Copy-Item .env.example .env
   ```

2. Buat database `sim_inventory`, kemudian impor:

   ```text
   database/schema.sql
   database/seed.sql
   ```

3. Isi koneksi database pada `.env`.
4. Jalankan server development:

   ```powershell
   php -S localhost:8000 router.php
   ```

### Akun Demo

| Role | Email | Password Development |
| --- | --- | --- |
| Admin | `admin@example.com` | `InventoryDemo2026!` |
| Operator | `operator@example.com` | `InventoryDemo2026!` |

Akun tersebut hanya untuk pengujian lokal. Password harus diganti sebelum deployment production.

## Struktur Folder

```text
SIM-Inventory/
├── api/                 # Berkas pendukung API
├── app/
│   ├── Controllers/     # Controller web dan API
│   ├── Core/            # Router, database, dan controller dasar
│   ├── Helpers/         # Icon dan helper tampilan
│   ├── Models/          # Query dan resource data
│   └── Services/        # Aturan bisnis inventori, laporan, dan audit
├── bootstrap/           # Bootstrap aplikasi
├── config/              # Konfigurasi dan definisi resource
├── database/            # Schema, seed, dan katalog fixture
├── docs/                # Dokumentasi, diagram, screenshot, dan Postman
├── public/              # Document root dan asset antarmuka
├── scripts/             # Launcher lokal dan utilitas
├── storage/             # Log, export, dan upload runtime
├── tests/               # Pengujian aplikasi
├── views/               # Template halaman server-side
├── composer.json
├── README.md
└── router.php
```

## REST API

Base URL lokal:

```text
http://localhost:8000/api/v1
```

| Method | Endpoint | Fungsi |
| --- | --- | --- |
| `GET` | `/csrf` | Mengambil CSRF token dan cookie session |
| `POST` | `/token` | Membuat Bearer token |
| `DELETE` | `/token` | Mencabut token saat ini |
| `GET` | `/dashboard` | Mengambil ringkasan dashboard |
| `GET` | `/barang` | Mengambil daftar barang |
| `GET` | `/barang/{id}` | Mengambil detail barang |
| `POST` | `/barang` | Menambah barang sebagai Admin |
| `PUT` | `/barang/{id}` | Memperbarui barang sebagai Admin |
| `DELETE` | `/barang/{id}` | Menghapus barang sebagai Admin |
| `GET` | `/stok` | Mengambil data saldo stok |

Dokumentasi lengkap tersedia di [docs/API.md](docs/API.md). Token asli tidak ditulis pada README atau screenshot.

## Keamanan

- Password disimpan menggunakan `password_hash()` dan diverifikasi menggunakan `password_verify()`.
- Query database menggunakan PDO prepared statement tanpa emulasi.
- Form mutasi web dilindungi CSRF.
- Session ID diregenerasi setelah login dan memiliki idle timeout.
- Hak akses diperiksa pada server berdasarkan role.
- Percobaan login dibatasi per kombinasi IP dan email.
- Upload foto divalidasi berdasarkan MIME, extension, ukuran, dan dimensi.
- Token API disimpan sebagai hash SHA-256, memiliki masa berlaku, dan dapat dicabut.
- Perubahan penting dicatat pada audit log.

Detail keamanan tersedia pada [docs/SECURITY.md](docs/SECURITY.md).

## Dokumentasi

- [Indeks Dokumentasi](docs/INDEX.md)
- [Analisis Sistem](docs/01_ANALISIS_SISTEM.md)
- [Arsitektur](docs/ARCHITECTURE.md)
- [Database](docs/DATABASE.md)
- [REST API](docs/API.md)
- [Deployment](docs/DEPLOYMENT.md)
- [Laporan Project](docs/PROJECT_REPORT.md)
- [Final Audit](docs/FINAL_AUDIT.md)
- [Laporan Word Final](docs/Laporan_Project_Akhir_SIM_Inventory_Diska_Kurnia_Azzahra_Putra.docx)

## Author

**Diska Kurnia Azzahra Putra**  
NIM 312210369 · Kelas TIF26  
Program Studi Teknik Informatika · Universitas Pelita Bangsa  
Project Akhir Pemrograman Web 1
