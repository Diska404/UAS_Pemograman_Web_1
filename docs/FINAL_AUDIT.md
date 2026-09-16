# Audit Akhir / Definition of Done

**Status: siap untuk final review pengguna. Regresi akhir PASS.**

Tanggal: 15 September 2026. Lingkungan: Windows, PHP 8.4.25, MariaDB 10.4.32 pada 127.0.0.1:3307, aplikasi localhost:8000. Hasil berikut berasal dari eksekusi akhir, termasuk verifikasi terarah setelah penyesuaian batas memori PDF.

## Hasil aktual

| Area | Bukti akhir | Hasil |
|---|---|---|
| PHP | Lint 50 file aplikasi/test (di luar vendor, runtime, dan artefak ekspor) | 0 error |
| Aplikasi/HTTP | `tests/run.php`: auth, role, CRUD, stok, security, upload, API, laporan, audit | 78/78 PASS |
| Concurrency | `tests/concurrency.php`: pergantian barang, reversal, dua koneksi mengeluarkan 4 dari stok 5 | 7/7 PASS |
| Dashboard/filter/data | `tests/inventory_read_model.php`: fixture, rentang, agregasi, irisan filter, invalid input, Operator | 30/30 PASS |
| Analitik laporan/login UX | `tests/report_interaction.php`: kecocokan agregat dengan tabel, semantic autocomplete, penyimpanan email, aset dan motion | 21/21 PASS |
| Postman/Newman | Collection yang tersedia: 11 request dan 9 assertion | 0 failure |
| Seed bersih | Schema + seed diimpor ke database uji kosong yang kemudian dibersihkan | 10 tabel, 60 barang, 681 unit; PASS |
| Determinisme | Hash seed sebelum/sesudah pembentukan ulang identik | PASS |
| Rekonsiliasi | 720 masuk + 720 keluar; saldo semua barang sama dengan history; tidak pernah negatif | 0 selisih; PASS |
| Dataset akhir | 6 kategori × 10 barang, 3 gudang, 10 supplier | 60 barang; PASS |
| Kesehatan stok | Aman 40, rendah 15, habis 5 | PASS |
| PDF stok | Seluruh teks PDF diperiksa, kode unik lengkap; preview dirender | 4 halaman, 60 data; PASS |
| PDF masuk/keluar | Seluruh nomor transaksi PDF diperiksa; preview halaman awal dan halaman akhir masuk ditinjau | Masing-masing 35 halaman, 720 data unik; PASS |
| Excel | Stok, masuk dan keluar diekspor dan dibaca kembali | 60/720/720 baris data; PASS |
| Formula injection | Teks `=HYPERLINK(...)` diuji setelah ekspor/read-back | Cell bertipe string, bukan formula; PASS |
| Startup setelah penyesuaian runtime | Login, dashboard, saldo, dan PDF periode penuh melalui HTTP | HTTP 200, 60 barang/681 unit; PASS |
| Browser/UI | Desktop 1920/2560, tablet, ponsel, login, galeri, filter, pagination, navigasi, fokus dan konfirmasi | PASS; lihat UI_TESTING |
| Dependency | Composer audit | 0 advisory saat diperiksa |
| Kebersihan source | Pencarian debug/TODO/FIXME pada source aplikasi dan test | Tidak ditemukan |

## Masalah yang ditemukan dan diselesaikan

Ekspor PDF 720 transaksi melampaui batas memori PHP 256 MB. Pengujian terarah menunjukkan kebutuhan puncak sekitar **282 MB**. Runtime lokal dan `scripts/start-local.ps1` kini memakai **512 MB**; requirement deployment diperbarui. PDF masuk dan keluar lengkap berhasil, termasuk satu unduhan PDF masuk 720 transaksi melalui aplikasi setelah restart. Tidak ada perubahan pada logika laporan, stok, atau database untuk penyesuaian ini.

Setelah polishing UI, suite aplikasi lengkap dijalankan sekali dan menghasilkan 78/78 PASS. Uji concurrency menghasilkan 7/7 PASS. Pemeriksaan fixture sempat terbaca bersamaan saat suite CRUD masih memiliki dua data QA sementara; setelah suite membersihkannya, pemeriksaan fixture diulang secara berurutan dan menghasilkan 30/30 PASS pada data akhir 60 barang/681 unit. Tidak ada kegagalan aplikasi atau perubahan backend.

## Audit polishing UI

- Login: form desktop 520 px, heading responsif 28–36 px, kontrol 52 px, toggle password berlabel dinamis, double-submit prevention, dan loading button.
- Motion: token 160/240/420 ms, page-enter pada main content, stagger login sekali, ambient transform/opacity, count-up KPI 650 ms, feedback hover/active/focus, serta chart click feedback 90 ms.
- Accessibility: `prefers-reduced-motion` mematikan motion dekoratif/count-up/transform, focus-visible tetap jelas, login error memakai live semantics, dan toggle password mengembalikan fokus.
- Responsive: 1920×1080 dan 2560×1440 tanpa overflow; konten besar dibatasi 1800 px. Tablet login memakai satu kolom/form 520 px; ponsel form 325 px dan dashboard dua kolom.
- Course identity: seluruh identitas project yang ditemukan sekarang menggunakan **Pemrograman Web 1**.
- Protected backend: schema/seed, InventoryService, autentikasi/otorisasi, REST, security, transaksi, dan implementasi ekspor tidak diubah pada pass ini. Analitik baru bersifat baca-saja dan memakai hasil filter ReportService yang sama.

## UX dan analitik laporan

- Navigasi lintas dokumen memakai View Transitions sebagai progressive enhancement. Sidebar/topbar tetap stabil, konten utama memakai fade/translate singkat, dan fallback page-enter tetap berlaku.
- Laporan stok memiliki KPI kesehatan serta grafik status, kategori, dan gudang. Laporan transaksi memiliki KPI, tren harian/bulanan, produk teratas, serta kontribusi supplier atau distribusi gudang.
- Agregasi dibuat server-side dari baris hasil `ReportService`; tidak ada endpoint baru atau payload 720 baris ke Chart.js. Filter tanggal, barang, gudang, dan supplier otomatis sama dengan tabel dan ekspor.
- Login hanya menyimpan email pada localStorage dengan key `simInventory.rememberedEmail`. Password tetap dikelola semantic browser dan logout tetap mengakhiri session.

## Integritas implementasi

- InventoryService, Auth, ReportService, serta schema.sql identik dengan snapshot sebelum penyegaran UI/data.
- PDO, CSRF, escaping, password hashing, validasi upload, role server-side, reversal dan row lock tetap berlaku.
- `.env`, akun asli, session, dan audit dipertahankan. Backup demo lama berada di storage privat dan dikecualikan dari Git.
- Tidak ada perubahan schema/relasi; diagram domain tidak diganti.
- Aset kategori SVG lokal; tidak ada scraping, hotlink, atau unduhan foto produk.
- Test membuat/membersihkan data QA miliknya sendiri; saldo akhir tetap 681 pada 60 barang.

## Bukti dan dokumen

- [BLACK_BOX_TESTING](BLACK_BOX_TESTING.md): 78 hasil HTTP terperinci.
- [CONCURRENCY_TESTING](CONCURRENCY_TESTING.md): hasil race dan reversal.
- [UI_TESTING](UI_TESTING.md): pemeriksaan browser yang telah selesai.
- [READ_MODEL_TESTING](READ_MODEL_TESTING.md): 30 hasil data/filter.
- [FINAL_HANDOFF](FINAL_HANDOFF.md): penyerahan dan cara menjalankan.

File ekspor validasi lokal: `storage/exports/final-stok.pdf`, `final-masuk.pdf`, `final-keluar.pdf`, serta `.xlsx` masing-masing. Output mentah test/runtime tidak dilacak; output Newman dapat memuat cookie/token sehingga tetap privat.

## Finalisasi pengguna

- Review akhir aplikasi dan dokumen.
- Isi identitas mahasiswa/NIM/kelas/dosen/institusi yang masih berupa placeholder.
- Ambil screenshot final dan sesuaikan/konversi laporan Markdown ke format pengumpulan jika diperlukan.
- Buat commit dengan identitas Git pengguna bila ingin menyimpan versi; source saat ini belum di-commit.
- Bila akan dipublikasikan: siapkan hosting/domain, credential sendiri, HTTPS, ganti password demo, dan lakukan smoke test pada host.

## Batas nyata

DataTables masih client-side untuk inventori kecil. Satu barang mempunyai satu gudang; tidak ada transfer khusus, valuasi, atau snapshot historis. Laporan stok adalah saldo saat ini dan metadata transaksi mengikuti master terkini. Tanggal fixture tetap Oktober 2025–September 2026, sementara rentang dashboard mengikuti bulan server. Penggantian password mencabut token API tetapi tidak otomatis semua sesi web perangkat lain; penonaktifan user menolak akses berikutnya. Belum ada deployment publik, uji beban produksi, atau pentest eksternal.
