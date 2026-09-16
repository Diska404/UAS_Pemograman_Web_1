# Dokumentasi Database

Schema sumber: `database/schema.sql`. Engine InnoDB; karakter utf8mb4_unicode_ci. Kamus berikut dihasilkan dari schema yang benar-benar diimport pada MariaDB lokal. Semua id adalah primary key auto-increment BIGINT UNSIGNED.

## Daftar tabel

| Tabel | Fungsi |
|---|---|
| api_tokens | Hash token Bearer dan masa berlaku. |
| audit_logs | Jejak aktivitas penting tanpa password/token mentah. |
| barang | Master inventori dan saldo stok saat ini. |
| barang_keluar | Pengeluaran barang untuk tujuan operasional. |
| barang_masuk | Penerimaan barang dari supplier. |
| gudang | Master lokasi penyimpanan. |
| login_attempts | Pembatasan percobaan login gagal per IP+email. |
| roles | Daftar peran Admin dan Operator. |
| supplier | Master pemasok barang. |
| users | Identitas, hash password, role, dan status aktif pengguna. |

## api_tokens

Hash token Bearer dan masa berlaku.

| Field | Tipe | Nullable | Key | Default | Relasi / catatan |
|---|---|---|---|---|---|
| id | bigint(20) unsigned | NO | PRI | NULL / tidak ada | auto_increment |
| user_id | bigint(20) unsigned | NO | MUL | NULL / tidak ada | users.id (CASCADE) |
| token_hash | char(64) | NO | UNI | NULL / tidak ada | — |
| expires_at | datetime | NO | — | NULL / tidak ada | — |
| created_at | timestamp | NO | — | current_timestamp() | — |

## audit_logs

Jejak aktivitas penting tanpa password/token mentah.

| Field | Tipe | Nullable | Key | Default | Relasi / catatan |
|---|---|---|---|---|---|
| id | bigint(20) unsigned | NO | PRI | NULL / tidak ada | auto_increment |
| user_id | bigint(20) unsigned | YES | MUL | NULL / tidak ada | users.id (SET NULL) |
| activity | varchar(60) | NO | — | NULL / tidak ada | — |
| description | varchar(1000) | NO | — | NULL / tidak ada | — |
| ip_address | varchar(45) | NO | — | NULL / tidak ada | — |
| user_agent | varchar(255) | NO | — | NULL / tidak ada | — |
| created_at | timestamp | NO | MUL | current_timestamp() | — |

## barang

Master inventori dan saldo stok saat ini.

| Field | Tipe | Nullable | Key | Default | Relasi / catatan |
|---|---|---|---|---|---|
| id | bigint(20) unsigned | NO | PRI | NULL / tidak ada | auto_increment |
| kode_barang | varchar(30) | NO | UNI | NULL / tidak ada | — |
| nama_barang | varchar(100) | NO | — | NULL / tidak ada | — |
| kategori | varchar(80) | NO | MUL | NULL / tidak ada | — |
| satuan | varchar(30) | NO | — | NULL / tidak ada | — |
| stok | int(11) | NO | — | 0 | — |
| stok_minimum | int(11) | NO | — | 0 | — |
| gudang_id | bigint(20) unsigned | NO | MUL | NULL / tidak ada | gudang.id |
| foto | varchar(80) | YES | — | NULL / tidak ada | — |
| deskripsi | text | YES | — | NULL / tidak ada | — |
| created_at | timestamp | NO | — | current_timestamp() | — |
| updated_at | timestamp | NO | — | current_timestamp() | on update current_timestamp() |

## barang_keluar

Pengeluaran barang untuk tujuan operasional.

| Field | Tipe | Nullable | Key | Default | Relasi / catatan |
|---|---|---|---|---|---|
| id | bigint(20) unsigned | NO | PRI | NULL / tidak ada | auto_increment |
| nomor_transaksi | varchar(50) | NO | UNI | NULL / tidak ada | — |
| tanggal | date | NO | MUL | NULL / tidak ada | — |
| barang_id | bigint(20) unsigned | NO | MUL | NULL / tidak ada | barang.id |
| jumlah | int(11) | NO | — | NULL / tidak ada | — |
| tujuan | varchar(255) | NO | — | NULL / tidak ada | — |
| keterangan | text | YES | — | NULL / tidak ada | — |
| user_id | bigint(20) unsigned | NO | MUL | NULL / tidak ada | users.id |
| created_at | timestamp | NO | — | current_timestamp() | — |
| updated_at | timestamp | NO | — | current_timestamp() | on update current_timestamp() |

## barang_masuk

Penerimaan barang dari supplier.

| Field | Tipe | Nullable | Key | Default | Relasi / catatan |
|---|---|---|---|---|---|
| id | bigint(20) unsigned | NO | PRI | NULL / tidak ada | auto_increment |
| nomor_transaksi | varchar(50) | NO | UNI | NULL / tidak ada | — |
| tanggal | date | NO | MUL | NULL / tidak ada | — |
| barang_id | bigint(20) unsigned | NO | MUL | NULL / tidak ada | barang.id |
| supplier_id | bigint(20) unsigned | NO | MUL | NULL / tidak ada | supplier.id |
| jumlah | int(11) | NO | — | NULL / tidak ada | — |
| keterangan | text | YES | — | NULL / tidak ada | — |
| user_id | bigint(20) unsigned | NO | MUL | NULL / tidak ada | users.id |
| created_at | timestamp | NO | — | current_timestamp() | — |
| updated_at | timestamp | NO | — | current_timestamp() | on update current_timestamp() |

## gudang

Master lokasi penyimpanan.

| Field | Tipe | Nullable | Key | Default | Relasi / catatan |
|---|---|---|---|---|---|
| id | bigint(20) unsigned | NO | PRI | NULL / tidak ada | auto_increment |
| kode_gudang | varchar(30) | NO | UNI | NULL / tidak ada | — |
| nama_gudang | varchar(100) | NO | — | NULL / tidak ada | — |
| lokasi | varchar(255) | NO | — | NULL / tidak ada | — |
| keterangan | text | YES | — | NULL / tidak ada | — |
| created_at | timestamp | NO | — | current_timestamp() | — |
| updated_at | timestamp | NO | — | current_timestamp() | on update current_timestamp() |

## login_attempts

Pembatasan percobaan login gagal per IP+email.

| Field | Tipe | Nullable | Key | Default | Relasi / catatan |
|---|---|---|---|---|---|
| id | bigint(20) unsigned | NO | PRI | NULL / tidak ada | auto_increment |
| identity_hash | char(64) | NO | MUL | NULL / tidak ada | — |
| created_at | timestamp | NO | — | current_timestamp() | — |

## roles

Daftar peran Admin dan Operator.

| Field | Tipe | Nullable | Key | Default | Relasi / catatan |
|---|---|---|---|---|---|
| id | bigint(20) unsigned | NO | PRI | NULL / tidak ada | auto_increment |
| name | varchar(20) | NO | UNI | NULL / tidak ada | — |

## supplier

Master pemasok barang.

| Field | Tipe | Nullable | Key | Default | Relasi / catatan |
|---|---|---|---|---|---|
| id | bigint(20) unsigned | NO | PRI | NULL / tidak ada | auto_increment |
| kode_supplier | varchar(30) | NO | UNI | NULL / tidak ada | — |
| nama_supplier | varchar(100) | NO | — | NULL / tidak ada | — |
| alamat | varchar(255) | NO | — | NULL / tidak ada | — |
| telepon | varchar(30) | NO | — | NULL / tidak ada | — |
| email | varchar(150) | YES | — | NULL / tidak ada | — |
| keterangan | text | YES | — | NULL / tidak ada | — |
| created_at | timestamp | NO | — | current_timestamp() | — |
| updated_at | timestamp | NO | — | current_timestamp() | on update current_timestamp() |

## users

Identitas, hash password, role, dan status aktif pengguna.

| Field | Tipe | Nullable | Key | Default | Relasi / catatan |
|---|---|---|---|---|---|
| id | bigint(20) unsigned | NO | PRI | NULL / tidak ada | auto_increment |
| name | varchar(100) | NO | — | NULL / tidak ada | — |
| email | varchar(150) | NO | UNI | NULL / tidak ada | — |
| password | varchar(255) | NO | — | NULL / tidak ada | — |
| role_id | bigint(20) unsigned | NO | MUL | NULL / tidak ada | roles.id |
| is_active | tinyint(4) | NO | — | 1 | — |
| created_at | timestamp | NO | — | current_timestamp() | — |
| updated_at | timestamp | NO | — | current_timestamp() | on update current_timestamp() |

## Relasi dan constraint

- roles 1:N users. Role wajib tersedia; register memilih role Operator dari server.
- gudang 1:N barang. Gudang dengan barang tidak dapat dihapus.
- barang 1:N barang_masuk dan barang_keluar. Barang dengan riwayat tidak dapat dihapus.
- supplier 1:N barang_masuk. Supplier dengan riwayat masuk tidak dapat dihapus.
- users 1:N transaksi dan api_tokens; audit user nullable untuk login gagal atau user yang sudah dihapus secara administrasi database.
- Foreign key default RESTRICT; hanya api_tokens ON DELETE CASCADE dan audit_logs ON DELETE SET NULL. UI tidak menyediakan delete user.
- UNIQUE: roles.name; users.email; kode_gudang; kode_supplier; kode_barang; nomor_transaksi pada masing-masing tabel transaksi; token_hash.
- CHECK: stok dan stok_minimum >=0; jumlah >0; is_active 0/1.
- Index: kategori barang, tanggal transaksi, waktu audit, identity_hash+created_at login attempts; FK secara otomatis diindeks InnoDB.
- Login attempts tidak mempunyai FK karena email gagal mungkin belum menjadi user.

## Normalisasi

Atribut tiap field bernilai tunggal (1NF). Semua tabel mempunyai surrogate key satu kolom sehingga tidak ada dependensi parsial pada composite PK (2NF). Nama gudang, supplier, role, dan petugas disimpan pada tabel master; transaksi menyimpan FK sehingga tidak mengulang atribut master (3NF untuk data master/transaksi).

`barang.stok` adalah denormalisasi terkontrol untuk saldo operasional, bukan klaim bahwa semua field sepenuhnya bebas data turunan. Invariant `stok = SUM(masuk) - SUM(keluar)` dipelihara InventoryService dalam transaksi terkunci. Barang baru selalu stok 0. Seed menghitung saldo dari transaksi demo. Nama kategori/satuan disimpan sebagai string sederhana; dapat dijadikan master terpisah bila kategori/satuan membutuhkan atribut dan pengelolaan khusus.

## Makna data dan batas historis

Barang hanya berada di satu gudang. Perubahan gudang atau nama master akan tercermin pada laporan lama karena query memakai join master terkini. Tidak ada snapshot gudang, satuan, atau nama pada transaksi. Periode laporan transaksi memakai tanggal transaksi, sedangkan created_at adalah waktu pencatatan. Petugas pembuat dipertahankan saat edit; pengedit dicatat pada audit.

Semua waktu aplikasi menggunakan Asia/Jakarta; koneksi MySQL diset +07:00. Date transaksi tidak memakai zona waktu, dan harus antara 2000-01-01 sampai hari ini. Stok tidak boleh diedit melalui form/API master. Koreksi dilakukan dengan transaksi sehingga dapat ditelusuri.

## Rekonsiliasi

```sql
SELECT b.id, b.kode_barang, b.stok,
       COALESCE((SELECT SUM(m.jumlah) FROM barang_masuk m WHERE m.barang_id=b.id),0)
       - COALESCE((SELECT SUM(k.jumlah) FROM barang_keluar k WHERE k.barang_id=b.id),0) AS saldo_riwayat
FROM barang b;
```

Saldo seed total 681 unit dari 60 barang dalam 6 kategori. Terdapat 40 barang aman, 15 rendah (positif dan tidak melebihi minimum), serta 5 habis. Riwayat mencakup 720 masuk dan 720 keluar pada tanggal tetap Oktober 2025–September 2026. Nomor transaksi demo tetap BM-DEMO/BK-DEMO; nomor baru memakai prefix, waktu, dan random agar unik. Seed dirancang untuk database kosong dan tidak idempotent.

## ERD

[Source Mermaid lengkap](diagrams/ERD.md). Diagram menggunakan field dan relasi dari schema aktual. Import: schema.sql terlebih dahulu, lalu seed.sql, atau installer CLI pada database kosong.

## Fixture teknologi yang deterministik

Sumber katalog: `database/demo.json`; pembentuk SQL: `scripts/build_seed.py`, tanpa akses jaringan. Tanggal, ID demo, kode, jumlah, dan hash akun tetap. Seed tidak memakai CURDATE atau random. Saldo akhir dihitung dari agregat transaksi. Enam kategori masing-masing 10 produk; Gudang Perangkat IT menampung 30 produk (286 unit), Gudang Komponen 10 produk (99 unit), Gudang IoT & Networking 20 produk (296 unit). Gambar barang seed NULL, sehingga UI menggunakan ilustrasi kategori lokal.

Seed hanya untuk database kosong. Pada workspace pengembangan ini, data demo lama dicocokkan terhadap fixture awal dan dibackup ke `storage/exports/baseline-inventory-backup.json` sebelum diperbarui; akun dan konfigurasi lokal dipertahankan. Tidak tersedia migrasi otomatis untuk mengganti data bisnis pengguna.
