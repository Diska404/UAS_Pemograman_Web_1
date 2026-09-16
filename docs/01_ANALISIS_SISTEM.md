# Analisis Sistem SIM Inventory

## Latar belakang

Persediaan barang merupakan bagian penting dari operasional organisasi. Pencatatan yang tersebar pada buku atau spreadsheet terpisah menyulitkan petugas mengetahui saldo yang benar, menelusuri transaksi, dan menyusun laporan. Kesalahan pencatatan dapat menimbulkan ketidaksesuaian antara kebutuhan dan persediaan yang tersedia.

SIM Inventory dirancang sebagai aplikasi web terpusat yang menghubungkan master barang, gudang, supplier, transaksi, dan pengguna. Proyek ini juga menjadi sarana penerapan PHP native berorientasi objek, PDO, database relasional, REST API, serta keamanan aplikasi pada mata kuliah Pemrograman Web 1.

## Identifikasi masalah

1. Data barang dan lokasi penyimpanan sulit dicari ketika pencatatan belum terpusat.
2. Pembaruan saldo manual rentan terlambat atau salah ketika transaksi dikoreksi.
3. Pengeluaran barang berisiko melebihi stok apabila validasi tidak dilakukan.
4. Hak akses pengguna yang tidak dibedakan meningkatkan risiko perubahan data master tanpa otorisasi.
5. Laporan membutuhkan pengolahan ulang, sementara jejak perubahan sulit ditelusuri.

## Rumusan masalah

Bagaimana membangun sistem inventori web yang mengintegrasikan pengelolaan master dan transaksi, menjaga konsistensi saldo, menerapkan autentikasi/otorisasi, serta menghasilkan laporan yang dapat dipakai untuk evaluasi operasional?

## Tujuan

- Menghasilkan aplikasi inventori yang dapat dijalankan dan didemonstrasikan.
- Mengotomatisasi perhitungan stok dan mencegah saldo negatif.
- Memisahkan akses Admin dan Operator secara server-side.
- Menyediakan dashboard, REST JSON, laporan HTML/PDF/Excel, dan audit log.
- Menghasilkan database, dokumentasi, dan pengujian yang sesuai implementasi.

## Manfaat

Petugas memperoleh pencarian dan pencatatan yang lebih konsisten. Admin memperoleh pengendalian pengguna, stok rendah, dan laporan. Mahasiswa memperoleh contoh penerapan OOP, transaksi database, keamanan, pengujian, serta deployment yang dapat dijelaskan secara langsung.

## Ruang lingkup

Data barang dengan satu gudang per barang, supplier, gudang, transaksi masuk/keluar, saldo saat ini, user, profil, audit, dashboard, dan laporan. Tidak mencakup akuntansi, harga pokok, barcode fisik, batch/expiry, retur khusus, integrasi marketplace, atau pembelian hosting. Koreksi dilakukan melalui edit/hapus transaksi dengan aturan saldo.

## Aktor sistem

**Admin** mengelola semua fitur termasuk master, pengguna, audit, dan export. **Operator** membaca master, mengelola transaksi operasional, melihat dashboard/laporan HTML, dan mengelola profil sendiri. **Tamu** hanya dapat membuka login/register serta mengambil token CSRF; register selalu menghasilkan Operator.

## Kebutuhan fungsional

| ID | Kebutuhan | Kriteria penerimaan |
|---|---|---|
| F01 | Autentikasi | Password di-hash; login/regenerasi sesi; logout; register Operator. |
| F02 | Master barang | CRUD, kode unik, kategori, satuan, minimum, gudang, foto, deskripsi. |
| F03 | Gudang dan supplier | CRUD dengan unique constraint dan FK restriction. |
| F04 | Barang masuk | Create/update/delete menyesuaikan saldo secara atomik. |
| F05 | Barang keluar | Jumlah positif; saldo tidak boleh negatif. |
| F06 | Dashboard | Enam KPI, ringkasan master, empat grafik, transaksi terbaru, serta prioritas stok habis/rendah. |
| F07 | Pengguna | Admin mengubah data/role/status dan reset password. |
| F08 | Profil | Nama/email sendiri dan password dengan verifikasi lama. |
| F09 | Audit | Catat login, CRUD, transaksi, password, role, token. |
| F10 | API | Resource barang CRUD JSON dengan status HTTP dan token. |
| F11 | Laporan | Filter transaksi/stock, HTML, PDF, XLSX. |
| F12 | UX daftar | DataTables pencarian, sorting, pagination, filter barang. |

## Kebutuhan nonfungsional

Keamanan: prepared statement, CSRF, escape output, validasi upload, cookie aman, role server-side, penanganan error. Integritas: InnoDB, FK, CHECK, transaction dan row lock. Usability: bahasa Indonesia, responsif, pesan dekat field, dialog konfirmasi. Maintainability: MVC sederhana, metadata reusable, `.env`, dependency lock. Portability: PHP 8.2+, MySQL/MariaDB, document root public. Operasional: HTTPS dan backup pada deployment. Kinerja: dirancang untuk skala tugas kuliah/inventori kecil; belum mengklaim hasil uji beban produksi.

## Penyempurnaan pengalaman pengguna

Prioritas tindak lanjut adalah barang habis, lalu barang rendah. Enam kategori teknologi memudahkan demonstrasi persediaan perangkat. Pengguna dapat memilih tabel atau galeri, menggabungkan filter nama/kode, kategori, gudang, dan status, serta membuka hasil langsung dari grafik. Angka grafik berasal dari agregasi database yang sama dengan daftar. Preferensi tampilan lokal tidak memengaruhi hak akses atau data bisnis.
