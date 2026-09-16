# Final Handoff

## Project

**Sistem Informasi Manajemen Inventori Barang Berbasis Web**  
Brand: **SIM Inventory**

## Final Status

**Siap untuk final review pengguna. Regresi akhir PASS.** Aplikasi lokal sudah berjalan di [localhost:8000](http://localhost:8000). Tidak ada blocker untuk review lokal. Pembangunan UI/data dan verifikasi selesai; tidak ada deployment publik atau commit yang dilakukan.

## Final Dataset

- 60 barang; 6 kategori; tepat 10 barang per kategori.
- 3 gudang dan 10 supplier demo fiktif.
- 12 bulan history tetap: Oktober 2025–September 2026.
- 720 transaksi masuk dan 720 keluar.
- Saldo akhir **681 unit**; **40 Aman, 15 Stok Rendah, 5 Habis**.
- Seed deterministik; impor database kosong dan rekonsiliasi berhasil.

Kategori: Laptop & Komputer, Monitor & Display, Komponen Komputer, Peripheral, Networking, IoT & Embedded.

## Completed Features

- Autentikasi, registrasi Operator, profil/password, otorisasi Admin/Operator dan manajemen user.
- CRUD master barang/gudang/supplier serta transaksi masuk/keluar dengan audit.
- Dashboard interaktif, tabel/galeri dengan preferensi lokal, pencarian, sorting, pagination, serta filter kategori/gudang/status.
- REST API barang/stok/dashboard, token Bearer, revoke, CSRF, dan validasi.
- Laporan HTML, PDF dan Excel; upload foto tervalidasi dengan placeholder SVG kategori lokal.
- Sidebar desktop yang dapat diciutkan, navigasi ponsel, keyboard/fokus, dan layout responsif.
- Prepared statements, escaping, hashing, validasi upload dan akses server-side.
- Login split-screen yang lebih proporsional, toggle password, loading submit, count-up KPI, serta micro-interaction konsisten dengan dukungan reduced-motion.

## Dashboard

Enam KPI utama: jumlah jenis barang, total unit, masuk bulan ini, keluar bulan ini, stok rendah, dan habis. Ringkasan master menunjukkan kategori/gudang/supplier. Empat grafik menampilkan komposisi unit per kategori, pergerakan 6/12 bulan atau YTD, distribusi gudang, serta kesehatan berdasarkan jenis barang. Tooltip menggunakan nilai/persen aktual; pergerakan menyertakan net masuk dikurangi keluar. Klik kategori/gudang/status atau legend membuka filter Barang yang sama. Panel prioritas mendahulukan stok habis; panel aktivitas menampilkan delapan transaksi terbaru.

## Inventory Integrity

`stok = jumlah masuk − jumlah keluar`.

Edit/hapus membalik efek transaksi lama sebelum menerapkan perubahan. Operasi saldo/transaksi/audit berada dalam satu transaksi database. Stok negatif ditolak dan exception membatalkan keseluruhan perubahan. Penguncian baris dalam urutan stabil menjaga saldo ketika dua koneksi bertransaksi bersamaan. InventoryService tidak diubah pada penyegaran UI ini.

## Test Results

| Pemeriksaan | Hasil akhir |
|---|---|
| PHP syntax | 50 file aplikasi/test, 0 error |
| HTTP aplikasi/security/CRUD/API | 78/78 PASS |
| Concurrency | 7/7 PASS |
| Data/dashboard/filter | 30/30 PASS |
| Analitik laporan/login UX | 21/21 PASS |
| Newman | 11 request, 9 assertion, 0 failure |
| Schema + seed kosong | 10 tabel, 60 barang, 681 unit; PASS |
| Rekonsiliasi/history | 1.440 transaksi, 0 selisih, tidak pernah negatif |
| PDF | Stok 60 data/4 halaman; masuk dan keluar masing-masing 720 data/35 halaman; seluruh nomor unik lengkap |
| Excel | Read-back 60/720/720 data; formula-like input tetap string |
| UI/browser | Login dan aplikasi pada 1920/2560, tablet/mobile, filter/galeri/pagination/sidebar/fokus/reduced-motion PASS |
| Dependency/source hygiene | 0 advisory; tidak ada debug/TODO/FIXME |

**Perbaikan regresi:** PDF riwayat penuh membutuhkan sekitar 282 MB; runtime dan launcher lokal diatur `memory_limit=512M`. Ekspor melalui HTTP berhasil setelah penyesuaian. Tidak diperlukan perubahan logika bisnis. Detail bukti: [FINAL_AUDIT](FINAL_AUDIT.md).

Pass UX terakhir menambahkan View Transitions progresif, ringkasan grafik laporan baca-saja, dan opsi mengingat email. Database, schema/seed, saldo, InventoryService, kontrak API, serta implementasi PDF/Excel tetap sama pada pass ini. Identitas resmi project adalah **Pemrograman Web 1**.

## Demo Accounts

| Role | Email | Password demo lokal |
|---|---|---|
| Admin | admin@example.com | InventoryDemo2026! |
| Operator | operator@example.com | InventoryDemo2026! |

Credential database privat tetap berada di `.env` dan tidak disalin ke dokumen.

## How to Run

Di PowerShell pada folder proyek:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/start-demo-db.ps1
powershell -ExecutionPolicy Bypass -File scripts/start-local.ps1
```

Jika database dan server sudah aktif, langsung buka [http://localhost:8000](http://localhost:8000). Launcher memakai PHP portabel lokal dan batas memori PDF 512 MB. Database demo berada pada 127.0.0.1:3307; data XAMPP lain tidak diubah. Instalasi pada komputer lain: [README](../README.md).

## Important Files

- `public/index.php`, `router.php`: entry point aplikasi.
- `app/Services/InventoryService.php`: transaksi, reversal, locking saldo.
- `app/Models/BarangQuery.php`, `app/Services/DashboardService.php`: filter dan agregasi.
- `views/barang/index.php`, `views/dashboard/index.php`, `views/layouts/main.php`: tampilan utama.
- `public/assets/css/app.css`, `public/assets/js/`: desain dan interaksi.
- `database/schema.sql`, `database/seed.sql`, `database/demo.json`: schema dan fixture.
- `tests/run.php`, `tests/concurrency.php`, `tests/inventory_read_model.php`, `tests/report_interaction.php`: test yang dapat dijalankan ulang.
- [README](../README.md), [API](API.md), [PROJECT_REPORT](PROJECT_REPORT.md), [PRESENTATION_OUTLINE](PRESENTATION_OUTLINE.md), [FINAL_AUDIT](FINAL_AUDIT.md).

## Manual Work Remaining

1. Review akhir oleh pengguna dan isi identitas mahasiswa pada laporan.
2. Ambil screenshot final untuk lampiran dan sesuaikan/konversi laporan ke Word/PDF bila diwajibkan kampus.
3. Foto produk asli dapat diunggah sebagai pengganti placeholder bila diinginkan; ini opsional.
4. Commit source menggunakan identitas Git pengguna bila diperlukan.
5. Hosting/domain belum dilakukan. Jika akan dipublikasikan, ganti password demo, isi credential produksi, aktifkan HTTPS, dan lakukan smoke test host.

## Known Limitations

- DataTables melakukan pagination client-side; data sangat besar memerlukan penanganan server-side.
- Satu barang terhubung ke satu gudang; transfer khusus/multi-gudang, valuasi dan snapshot historis tidak tersedia.
- Laporan stok adalah saldo saat ini; nama/kategori/gudang laporan mengikuti master terkini.
- Fixture bertanggal tetap. Grafik mengikuti waktu server sehingga periode tanpa history akan tampil nol.
- Penggantian password tidak otomatis mencabut semua sesi web lain; token API dicabut, dan akun nonaktif ditolak pada request berikutnya.
- Laporan sangat besar memerlukan kapasitas memori sesuai volume; 512 MB sudah diuji untuk 720 transaksi per PDF.
- Belum diuji beban produksi/pentest eksternal dan belum dipublikasikan.
