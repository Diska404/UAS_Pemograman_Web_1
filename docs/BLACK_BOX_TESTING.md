# Black Box Testing

Pengujian nyata melalui HTTP pada 15 September 2026 22:15 WIB. PHP 8.4.25; database 10.4.32-MariaDB.

Jalankan `php tests/run.php` setelah server lokal aktif. Suite membuat data QA dan membersihkan hanya ID yang dibuatnya. Akun demo harus masih memakai password development.

**Hasil: 78/78 PASS; 0 FAIL.**

| No | Fitur | Skenario | Input | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|---|
| 1 | Security | API tanpa autentikasi | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 401 | PASS |
| 2 | Auth | Halaman privat mengarah login | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 303 | PASS |
| 3 | CSRF | Login tanpa token | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 419 | PASS |
| 4 | Auth | Password salah ditolak | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 422 | PASS |
| 5 | Security | SQL injection login ditolak | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 422 | PASS |
| 6 | Auth | Login Admin | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 303 | PASS |
| 7 | Auth | Login Operator | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 303 | PASS |
| 8 | Pages | /dashboard | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 9 | Pages | /barang | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 10 | Pages | /gudang | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 11 | Pages | /supplier | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 12 | Pages | /barang-masuk | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 13 | Pages | /barang-keluar | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 14 | Pages | /users | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 15 | Pages | /profile | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 16 | Pages | /audit-logs | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 17 | Pages | /reports | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 18 | Role | Operator dibatasi /users | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 403 | PASS |
| 19 | Role | Operator dibatasi /audit-logs | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 403 | PASS |
| 20 | Role | Operator dibatasi /barang/create | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 403 | PASS |
| 21 | Role | Operator dibatasi /reports?format=pdf | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 403 | PASS |
| 22 | Role | POST master Operator ditolak | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 403 | PASS |
| 23 | Auth | Register publik mengabaikan role Admin | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Role Operator | PASS |
| 24 | Auth | Akun nonaktif ditolak | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 422 | PASS |
| 25 | Profile | Edit profil | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 303 | PASS |
| 26 | Profile | Password lama wajib cocok | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 422 | PASS |
| 27 | Profile | Ganti password | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 303 | PASS |
| 28 | Auth | Logout | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Session tidak lagi mengakses profil | PASS |
| 29 | Profile | Login dengan password baru | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 303 | PASS |
| 30 | Auth | Akun dinonaktifkan memutus akses sesi aktif | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Akses sesi ditolak | PASS |
| 31 | Role | Admin tidak dapat menurunkan role sendiri | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 422 | PASS |
| 32 | Users | Admin mengubah role user | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Role berubah menjadi Admin | PASS |
| 33 | CRUD | Tambah gudang | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | ID tersimpan | PASS |
| 34 | CRUD | Edit gudang | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 303 | PASS |
| 35 | CRUD | Tambah supplier | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | ID tersimpan | PASS |
| 36 | CRUD | Edit supplier | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 303 | PASS |
| 37 | API | Token Admin diterbitkan | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 201 | PASS |
| 38 | Security | Token database hanya hash | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | SHA-256 64 karakter | PASS |
| 39 | API | POST barang | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 201 | PASS |
| 40 | Security | XSS di-escape | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTML escaped | PASS |
| 41 | Validation | Kode barang unik | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 409 | PASS |
| 42 | API | PUT barang | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 43 | API | Payload invalid | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 400 | PASS |
| 44 | Inventory | Stok langsung ditolak | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Stok tetap 0 | PASS |
| 45 | CSRF | API mutasi tanpa CSRF | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 419 | PASS |
| 46 | Role | Token Operator tidak dapat POST barang | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 403 | PASS |
| 47 | API | Token tidak sah | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 401 | PASS |
| 48 | API | Data tidak ditemukan | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 404 | PASS |
| 49 | API | Pencarian API | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Satu barang ditemukan | PASS |
| 50 | Inventory | Masuk menambah stok | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Stok 10 | PASS |
| 51 | Inventory | FK invalid membatalkan perubahan stok | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Supplier invalid; stok tetap 10 | PASS |
| 52 | Validation | Jumlah nol ditolak | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 422 | PASS |
| 53 | Validation | Jumlah pecahan ditolak | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 422 | PASS |
| 54 | Integrity | Barang dengan transaksi tidak dapat dihapus | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 409 | PASS |
| 55 | Inventory | Keluar mengurangi stok | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Stok 6 | PASS |
| 56 | Inventory | Stok negatif dicegah | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 422; stok 6 | PASS |
| 57 | Inventory | Edit masuk tidak boleh membuat stok negatif | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Saldo tetap 6 | PASS |
| 58 | Inventory | Hapus masuk terpakai dibatalkan | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Transaksi dan stok tetap | PASS |
| 59 | Inventory | Edit masuk mengoreksi stok | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Stok 8 | PASS |
| 60 | Inventory | Edit keluar mengoreksi stok | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Stok 7 | PASS |
| 61 | Dashboard | JSON grafik 6 bulan | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | 6 bulan dan kategori | PASS |
| 62 | Report | Filter transaksi | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Satu transaksi | PASS |
| 63 | Report | Export pdf | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200; 19319 bytes | PASS |
| 64 | Report | Export excel | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200; 6679 bytes | PASS |
| 65 | Report | Excel dapat dibaca ulang | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Judul workbook cocok | PASS |
| 66 | Report | Filter tanggal invalid | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 422 | PASS |
| 67 | Upload | Foto PNG valid | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Nama acak; foto tersimpan | PASS |
| 68 | Upload | Foto dapat diakses pengguna | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 69 | Upload | MIME palsu ditolak | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 422 | PASS |
| 70 | Inventory | Hapus keluar mengembalikan stok | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Stok 12 | PASS |
| 71 | Inventory | Hapus masuk mengurangi stok | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Stok 0 | PASS |
| 72 | API | DELETE barang | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 200 | PASS |
| 73 | CRUD | Hapus supplier | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Data terhapus | PASS |
| 74 | CRUD | Hapus gudang | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Data terhapus | PASS |
| 75 | API | Revoke token | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | HTTP 401 | PASS |
| 76 | Inventory | Rekonsiliasi semua stok | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | 0 selisih | PASS |
| 77 | Security | Password tersimpan hash | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | Hash diverifikasi | PASS |
| 78 | Audit | Aktivitas penting tercatat | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | 6 kategori audit ditemukan | PASS |

## Pemeriksaan browser

Lihat [UI_TESTING.md](UI_TESTING.md) untuk pemeriksaan Chart.js, DataTables, SweetAlert2, desktop, tablet, dan mobile.

## Batas pengujian

Pengujian lokal bukan uji beban atau pentest eksternal. Ulangi smoke test setelah deployment; tanggal dan hasil suite akan berubah saat dijalankan ulang.
