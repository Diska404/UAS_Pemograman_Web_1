# Pengujian Data dan Filter

Eksekusi akhir: 2026-09-15T16:39:00+07:00. **30/30 PASS, 0 FAIL.**

Jalankan `php tests/inventory_read_model.php` pada APP_ENV=local dengan fixture demo asli dan server aktif. Test membaca database serta melakukan login/logout HTTP; tidak mengubah inventori.

| No | Skenario | Hasil aktual | Status |
|---|---|---|---|
| 1 | 60 produk persis sesuai fixture | 60 produk | PASS |
| 2 | 6 kategori masing-masing 10 barang | [{"kategori":"IoT & Embedded","total":10},{"kategori":"Komponen Komputer","total":10},{"kategori":"Laptop & Komputer","total":10},{"kategori":"Monitor & Display","total":10},{"kategori":"Networking","total":10},{"kategori":"Peripheral","total":10}] | PASS |
| 3 | 3 gudang dan 10 supplier fiktif | Jumlah master | PASS |
| 4 | Filter safe | 40 / 40 | PASS |
| 5 | Filter low | 15 / 15 | PASS |
| 6 | Filter out | 5 / 5 | PASS |
| 7 | Filter attention | 20 / 20 | PASS |
| 8 | Irisan kategori/gudang/status/pencarian | 1 hasil | PASS |
| 9 | Pencarian parameter tidak mengubah query SQL | Tidak ada hasil | PASS |
| 10 | Gudang yang tidak ada menghasilkan kosong | Tidak ada hasil | PASS |
| 11 | Filter tidak valid ditolak: {"status":"unknown"} | 422 | PASS |
| 12 | Filter tidak valid ditolak: {"q":["x"]} | 422 | PASS |
| 13 | Filter tidak valid ditolak: {"gudang_id":"1 OR 1=1"} | 422 | PASS |
| 14 | Filter tidak valid ditolak: {"kategori":"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"} | 422 | PASS |
| 15 | Rentang 6 dan seri lengkap | 6 bulan | PASS |
| 16 | Rentang 12 dan seri lengkap | 12 bulan | PASS |
| 17 | Rentang ytd dan seri lengkap | 9 bulan | PASS |
| 18 | Kategori dan gudang menjumlah stok yang sama | 681 unit | PASS |
| 19 | Kesehatan stok dan KPI cocok | 40/15/5 | PASS |
| 20 | Prioritas menampilkan habis dahulu | 5 teratas stok 0 | PASS |
| 21 | Riwayat 720 masuk dan 720 keluar | {"incoming":720,"outgoing":720} | PASS |
| 22 | Saldo seluruh barang cocok transaksi | 0 selisih; 681 unit | PASS |
| 23 | Saldo historis tidak pernah negatif | 1440 peristiwa diperiksa | PASS |
| 24 | HTTP dashboard range 12 | HTTP 200 | PASS |
| 25 | HTTP API memakai irisan filter bersama | HTTP 200 | PASS |
| 26 | Halaman Admin tabel dan galeri hasil filter | HTTP 200; 5 kartu | PASS |
| 27 | HTTP range array ditolak | HTTP 422 | PASS |
| 28 | HTTP filter array ditolak | HTTP 422 | PASS |
| 29 | Halaman hasil kosong tetap valid | HTTP 200 | PASS |
| 30 | Operator hanya melihat aksi detail barang | HTTP 200 | PASS |
