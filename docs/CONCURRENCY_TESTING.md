# Pengujian concurrency dan pergantian barang

Dijalankan 2026-09-15 22:15 WIB melalui dua proses PHP dengan koneksi PDO terpisah. Jalankan `php tests/concurrency.php` pada environment lokal.

| Skenario | Hasil |
|---|---|
| Edit masuk mengganti barang: saldo A=0 B=5 | PASS |
| Edit kembali: saldo A=5 B=0 | PASS |
| Edit keluar mengganti barang: saldo A=5 B=4 | PASS |
| Hapus transaksi pindahan: saldo B=6 | PASS |
| Dua koneksi keluar 4 dari stok 5: satu berhasil, satu ditolak | PASS |
| Saldo akhir concurrency = 1 | PASS |
| Hanya satu transaksi keluar tersimpan | PASS |

Bukan uji beban. Dua permintaan berebut saldo yang sama; SELECT FOR UPDATE menyerialisasi perubahan. Transaksi kedua membaca saldo hasil commit pertama.
