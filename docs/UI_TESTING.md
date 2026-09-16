# Pengujian Antarmuka Final

Pemeriksaan browser dilakukan pada 15 September 2026 setelah penyegaran UI dan fixture teknologi. Matriks ini tidak diulang pada fase resume karena tidak ada perubahan frontend berikutnya. Data berasal dari aplikasi/database lokal.

| No | Skenario | Hasil aktual | Status |
|---|---|---|---|
| 1 | Login Admin | Dashboard dan menu khusus Admin tersedia | PASS |
| 2 | Dashboard desktop | 60 SKU, 681 unit, 15 rendah, 5 habis dan empat grafik dirender | PASS |
| 3 | Rentang 12 bulan | Fetch mengubah periode menjadi Oktober 2025–September 2026; tabel angka berisi 12 baris | PASS |
| 4 | Filter kategori dari dashboard | IoT & Embedded membuka 10 dari 60 barang | PASS |
| 5 | Filter gudang dari dashboard | Gudang IoT & Networking membuka 20 barang | PASS |
| 6 | Filter stok habis | Halaman menampilkan tepat 5 barang | PASS |
| 7 | Tabel | Pagination awal 1–12 dari 60; tombol berikutnya 13–24 dari 60 | PASS |
| 8 | Galeri dan preferensi | Pergantian tabel/galeri berjalan; galeri tetap dipilih sesudah reload | PASS |
| 9 | Urutan stok galeri | Pilihan stok terendah menempatkan IOT-010 (stok 0) di depan pada kategori IoT | PASS |
| 10 | Pencarian galeri | IOT-010 menghasilkan satu kartu dan info pagination 1 dari 1 | PASS |
| 11 | Tanpa hasil | Pencarian tidak cocok menampilkan keadaan kosong dan reset filter | PASS |
| 12 | Placeholder | Ilustrasi lokal kategori tampil pada produk tanpa foto | PASS |
| 13 | Sidebar desktop | Ciutkan/perluas bekerja; nama aksesibel Data Barang tetap tersedia | PASS |
| 14 | Menu ponsel | Fokus masuk ke tombol tutup; Escape menutup dan mengembalikan fokus ke menu-toggle | PASS |
| 15 | Ponsel 390×844 | Galeri/filter terbaca; scrollWidth 375 tidak melebihi viewport 390 | PASS |
| 16 | Tablet 768×1024 | KPI tiga kolom, grafik dan menu ponsel; scrollWidth 753 tidak melebihi viewport 768 | PASS |
| 17 | Desktop | scrollWidth 1265 tidak melebihi viewport 1280; scroll tabel berada di area tabel | PASS |
| 18 | SweetAlert konfirmasi | Hapus menampilkan dialog; Batal mengembalikan halaman tanpa perubahan data | PASS |
| 19 | Form transaksi | Field tanggal/barang/supplier/jumlah dan kontrol simpan tampil pada layout baru | PASS |
| 20 | Konsol saat pemeriksaan grafik | Tidak ada error/warning pada pembacaan konsol tersebut | PASS |

## Verifikasi pendukung

30/30 pemeriksaan `tests/inventory_read_model.php` memverifikasi agregasi, filter bersama, respons API, pembatasan aksi Operator, dan fixture. Tooltip menggunakan nilai seri database: kategori unit/persen, pergerakan masuk/keluar/net, gudang unit/jenis, kesehatan jumlah/persen. Legend berupa tautan memberi alternatif navigasi keyboard. Label status menyertai warna dan progres menjelaskan batas minimum.

Pengujian ini bukan sertifikasi semua browser/perangkat. Screenshot telah digunakan selama review, tetapi belum dikemas sebagai berkas lampiran laporan akademik. Pengguna dapat mengambil screenshot final setelah identitas dan format pengumpulan ditetapkan.

## Polishing pass

Review akhir memakai browser zoom 100% dengan viewport 1920×1080, 2560×1440, 768×1024, dan 390×844. Login desktop mempertahankan split-screen dengan form selebar 520 px, heading 36 px, serta input dan tombol setinggi 52 px. Pada tablet dan ponsel, login berubah menjadi satu kolom agar form tidak menyempit; pengukuran tablet menghasilkan form 520 px dan ponsel 325 px tanpa overflow global.

Toggle password mengubah tipe input, label aksesibel, dan mengembalikan fokus ke input. Login salah menampilkan pesan halus yang tetap memakai `role="alert"`; login benar menuju dashboard. KPI berhenti pada nilai aktual 60/681/360/836/15/5, keempat grafik selesai dimuat tanpa error konsol, dan klik segmen kategori membuka `/barang?kategori=Laptop+%26+Komputer`. Dashboard monitor 2560 px membatasi konten pada 1800 px; ponsel memakai dua kolom KPI. Report dan profil tetap utuh dengan focus transition 160 ms.

CSS memuat 36 aturan di dalam `prefers-reduced-motion: reduce`; transform, stagger, count-up, ambient motion, dan durasi transisi dinonaktifkan secara efektif. Seluruh fungsi tetap tersedia tanpa motion.

## UX dan interaksi laporan

Pemeriksaan lanjutan pada 15 September 2026 memverifikasi transisi navigasi lintas dokumen, analitik laporan, dan penyimpanan email login.

| Skenario | Hasil aktual | Status |
|---|---|---|
| Login mengingat email | Setelah login dan logout, `admin@example.com` terisi kembali dan checkbox tetap aktif | PASS |
| Semantic password manager | Email memakai `autocomplete="username"`; password memakai `current-password` | PASS |
| Logout | Session berakhir dan browser kembali ke `/login`; tidak ada token login persisten buatan aplikasi | PASS |
| Laporan stok | KPI 60 barang/681 unit/15 menipis/5 habis; tiga canvas kesehatan, kategori, dan gudang ter-render | PASS |
| Laporan masuk | Filter 1–15 September menampilkan 60 transaksi/360 unit/10 supplier serta tiga grafik ter-render | PASS |
| Laporan keluar | Filter 1–15 September menampilkan 60 transaksi/836 unit/60 barang serta tiga grafik ter-render | PASS |
| Sinkronisasi filter | Suite terarah membandingkan agregat grafik dengan baris laporan untuk stok, masuk, keluar, barang, gudang, supplier, dan tanggal | 21/21 PASS |
| Ponsel 390×844 | KPI dua kolom, grafik satu kolom; scrollWidth 375 tidak melebihi viewport 390 | PASS |
| Konsol tab bersih | Tidak ada error atau warning setelah pemuatan laporan dan grafik | PASS |
| Reduced motion | Animasi View Transition, fallback, dan hierarchy stagger dipangkas menjadi 1 ms | PASS |

Browser mendukung `view-transition-name: app-main`. Sidebar dan topbar memakai nama transisi stabil; konten utama memakai fade/translate singkat. Fallback CSS tetap tersedia bagi browser tanpa dukungan View Transitions.
