# Presentasi SIM Inventory — 10–15 Menit

Target durasi 13 menit, 16 bagian. Gunakan screenshot aplikasi nyata atau demo live. Outline ini berupa bahan presentasi, bukan file PowerPoint.

| No | Bagian | Durasi | Poin yang dijelaskan |
|---|---|---|---|
| 1 | Judul | 20 dtk | Nama sistem, identitas, mata kuliah. |
| 2 | Latar belakang | 30 dtk | Pencatatan terpisah menyulitkan saldo dan laporan. |
| 3 | Permasalahan | 30 dtk | Saldo salah saat koreksi, pencarian lambat, akses belum dibedakan. |
| 4 | Solusi | 30 dtk | Sistem web terpusat master + transaksi + laporan. |
| 5 | Aktor | 35 dtk | Admin kelola seluruh fitur, Operator fokus operasional. |
| 6 | Arsitektur | 50 dtk | Router → controller → service → model/PDO → view/JSON. |
| 7 | Database / ERD | 55 dtk | Master, FK, unique, users/roles, saldo tersimpan. |
| 8 | Fitur | 30 dtk | Ringkas modul utama, profil, audit. |
| 9 | Dashboard | 45 dtk | 6 KPI, 4 grafik, periode 6/12/YTD, klik status stok. |
| 10 | CRUD & stok | 150 dtk | Demo tambah barang, masuk/keluar, edit, negatif ditolak. |
| 11 | REST API | 80 dtk | Postman CSRF, token, GET, POST, PUT, DELETE. |
| 12 | Keamanan | 60 dtk | Prepared, escape, CSRF, hash, role, upload, row lock. |
| 13 | Laporan | 45 dtk | Filter → HTML → PDF/Excel. |
| 14 | Testing | 45 dtk | Tunjukkan hasil suite nyata, concurrency, UI. |
| 15 | Deployment | 35 dtk | Public document root, .env privat, SSL, backup. |
| 16 | Kesimpulan | 25 dtk | Hasil, batas, rencana pengembangan. |

## Urutan demo yang aman

1. Pastikan database/server aktif; buka dashboard Admin dan Postman sebelum mulai.
2. Buat barang `PRESENTASI-001` dengan stok minimum 5; stok awal otomatis 0.
3. Catat masuk 10, kemudian keluar 4; tunjukkan saldo 6.
4. Coba keluar 7; sistem menolak dan saldo tetap 6.
5. Edit masuk menjadi 12; saldo menjadi 8. Jelaskan pembalikan jumlah lama.
6. Tunjukkan filter barang/kategori dan audit perubahan.
7. Buka sesi Operator pada browser terpisah; akses `/users` menghasilkan 403.
8. Filter laporan transaksi hari ini dan ekspor PDF/Excel sebagai Admin.
9. Jalankan collection Postman berurutan; koleksi memakai barang uji yang berbeda dan menghapus hanya barang itu.
10. Jika demo perlu dibersihkan, hapus keluar dahulu, lalu masuk, baru master barang melalui aplikasi.

## Pertanyaan dosen dan jawaban singkat

**Mengapa PHP native, bukan Laravel?** Untuk memperlihatkan implementasi OOP, PDO, routing, autentikasi, dan kontrol keamanan secara langsung dengan struktur sederhana.

**Di mana konsep OOP?** Controller mewarisi Controller abstrak. Database menyembunyikan koneksi private. Resource menginisialisasi metadata melalui constructor. Service memakai composition untuk memisahkan bisnis stok, upload, dan laporan.

**Mengapa stok disimpan jika dapat dihitung?** Agar pembacaan saldo cepat. Ini denormalisasi terkontrol; setiap perubahan dijalankan dalam transaksi dan diuji terhadap jumlah masuk dikurangi keluar.

**Apa yang terjadi jika dua pengguna mengeluarkan barang bersamaan?** FOR UPDATE membuat koneksi kedua menunggu kunci. Setelah koneksi pertama commit, koneksi kedua membaca saldo baru dan ditolak bila tidak mencukupi.

**Bagaimana edit transaksi masuk?** Sistem mengurangi efek jumlah lama lalu menambah jumlah baru secara atomic. Jika barang diganti, delta dibagi ke barang lama dan baru; keduanya dikunci berurutan.

**Mengapa hapus transaksi masuk dapat ditolak?** Barang mungkin sudah dikeluarkan. Membalik masuk akan menghasilkan saldo negatif, sehingga seluruh operasi dibatalkan.

**Apakah Operator hanya dibatasi tombol?** Tidak. Controller dan API memeriksa role pada server sebelum membaca/mengubah modul khusus Admin.

**Bagaimana SQL injection dicegah?** Input menjadi parameter PDO, bukan disisipkan pada SQL. Nama tabel/kolom berasal dari metadata allowlist yang ditulis pengembang.

**Apa beda CSRF dan XSS?** CSRF memalsukan request atas sesi pengguna; token memverifikasinya. XSS menjalankan konten tidak tepercaya di browser; output di-escape dan JS memakai textContent.

**Mengapa token di-hash?** Agar kebocoran database tidak langsung menyediakan Bearer yang dapat digunakan. Token mentah acak hanya dikirim sekali saat penerbitan, berlaku 8 jam.

**Mengapa API tetap memakai CSRF?** Kebijakan project mensyaratkan seluruh mutasi memiliki CSRF. Bearer diwajibkan untuk identitas/role, CSRF dan cookie dipertahankan untuk konsistensi kebijakan.

**Mengapa memakai satu model Resource?** CRUD master memiliki pola sama. Metadata terpercaya mengurangi duplikasi; aturan kompleks stok berada di InventoryService terpisah.

**Bagaimana upload aman?** Ekstensi/MIME/ukuran/dimensi diperiksa, gambar didekode dan di-encode ulang PNG, nama random, disimpan di luar public dan dibaca melalui route login.

**Apakah laporan stok dapat menunjukkan saldo masa lalu?** Belum. Laporan stok adalah saldo saat ini. Periode berlaku untuk transaksi; label UI menjelaskan batas tersebut.

**Apakah production-ready untuk ribuan pengguna?** Proyek telah diuji lokal dan disiapkan deployment, tetapi belum menjalani uji beban/pentest produksi. DataTables masih client-side untuk skala inventori kecil.

**Apa yang perlu difinalisasi?** Identitas mahasiswa, screenshot laporan, penyesuaian format kampus, akun produksi, konfigurasi SSL, dan smoke test hosting.

## Demonstrasi UI versi teknologi (±2 menit)

1. Tunjukkan 60 SKU, 681 unit, 15 rendah dan 5 habis; jelaskan perbedaan unit dan jenis barang.
2. Ganti rentang 6 menjadi 12 bulan, lalu tunjukkan tabel angka grafik.
3. Klik kategori IoT & Embedded: 10 barang; gudang IoT & Networking: 20 barang.
4. Beralih ke galeri, urutkan stok terendah, cari IOT-010, lalu reset.
5. Buka status Habis: 5 barang. Tunjukkan placeholder lokal dan akses Operator tanpa edit master.
6. Jelaskan 720 transaksi masuk dan keluar yang membentuk saldo; bukan angka grafik buatan.

Fixture bertanggal tetap Oktober 2025–September 2026; siapkan periode demo sesuai tanggal presentasi.

## Urutan demo final

Login → Dashboard → hover grafik untuk angka/persen → klik kategori/legend → Data Barang → Tabel/Galeri → filter stok → catat Barang Masuk → catat Barang Keluar → tunjukkan saldo berubah → REST API melalui collection → PDF/Excel → akses Operator → layout ponsel. Gunakan barang QA tersendiri saat demo transaksi agar fixture tetap mudah dibandingkan.

**Mengapa memory limit 512 MB?** PDF history 720 transaksi memerlukan puncak sekitar 282 MB pada pengujian, sehingga 256 MB tidak cukup. Batas runtime ditingkatkan; logika transaksi tetap sama.

**Apa hasil uji akhirnya?** HTTP 78/78, concurrency 7/7, data/filter 30/30, Newman 9 assertion tanpa failure, 48 file PHP tanpa error. PDF/Excel, impor bersih dan saldo 681 juga telah diverifikasi.
