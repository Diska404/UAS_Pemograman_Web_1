# Keamanan aplikasi

## Kontrol yang diterapkan

| Ancaman | Kontrol | Lokasi |
|---|---|---|
| SQL injection | PDO prepared, emulate prepares false; metadata tabel dari allowlist | Core/Database, Models/Resource, Services |
| XSS | e() memakai htmlspecialchars ENT_QUOTES UTF-8; textContent pada JS | Helpers/functions, views, assets/js/app |
| CSRF | Token random 32 byte, hash_equals, semua POST/PUT/DELETE | Core/Csrf, controller |
| Password bocor | password_hash/password_verify; password tidak ada di response user | Services/Auth, ResourceService |
| Session fixation | Regenerasi ID setelah login/ganti password | AuthController, ProfileController |
| Session reuse | Idle timeout, cek akun aktif/role terkini setiap request | Services/Auth |
| Cookie interception | HttpOnly, SameSite=Lax, Secure jika HTTPS | bootstrap |
| Akses ilegal | Role server-side sebelum operasi; API write token Admin | Auth::requireUser |
| Token bocor database | SHA-256 token acak 256 bit, expiry 8 jam, revoke | ApiController, api_tokens |
| Brute force | Maks. 5 login gagal per 15 menit, identitas hash IP+email | login_attempts, Auth |
| Upload executable | Ekstensi, MIME, 2 MB, 16 MP, decode/re-encode PNG, private storage | UploadService |
| Stok race | Row locks berurutan, transaksi atomic, pemeriksaan saldo, CHECK DB | InventoryService |
| Spreadsheet formula injection | Semua data string XLSX explicit TYPE_STRING | ReportService |
| PDF remote loading | isRemoteEnabled=false dan isPhpEnabled=false | ReportService |
| Information disclosure | Pesan publik generik, detail log privat, display_errors=0 | public/index, bootstrap |
| Clickjacking/content injection | CSP same-origin, frame-ancestors none, X-Frame-Options DENY, nosniff | bootstrap |

## Kebijakan token dan sesi

Bearer token digunakan untuk API tanpa mengirim email/password berulang. Request mutasi juga membawa CSRF dari cookie session awal. Token tidak disimpan di localStorage UI. Perubahan password, role, atau penonaktifan user melalui Admin mencabut token yang ada; endpoint revoke mencabut token saat ini. Logout web menghancurkan sesi; token API independen tetap berlaku sampai expiry/revoke. Ini dibedakan pada dokumentasi API.

Perubahan password meregenerasi sesi saat ini. Sesi web lain milik akun yang masih aktif tidak otomatis dicabut hanya karena password diganti; penonaktifan akun menolak semua akses berikutnya. Bila membutuhkan kontrol semua perangkat, tambahkan session version di users dan verifikasi pada setiap sesi sebagai pengembangan berikutnya.

## Review lokal

Suite HTTP mencakup SQL injection sederhana, XSS, tanpa CSRF, role Operator pada endpoint Admin, token salah, akun nonaktif, FK rollback, upload MIME palsu, dan rekonsiliasi stok. Suite concurrency menguji dua koneksi dengan saldo yang sama. Composer audit dijalankan dan tidak menemukan advisory pada dependency terkunci saat pemeriksaan 15 September 2026.

## Operasional produksi

Gunakan HTTPS; PHP harus mengetahui status HTTPS jika di balik reverse proxy. Jangan mempercayai header X-Forwarded-* dari publik secara otomatis. Batasi akses database ke jaringan aplikasi; pakai akun khusus dengan SELECT/INSERT/UPDATE/DELETE setelah migrasi. Pastikan `.env`/storage/logs tidak dapat diunduh, backup di luar public, register dinonaktifkan bila hanya staf, dan kredensial demo diganti.

Log menyimpan email percobaan login, IP, user agent, dan deskripsi aktivitas; jangan membagikan log mentah. Tentukan retensi log dan audit sesuai kebutuhan organisasi. Login throttle adalah kontrol dasar per IP+email, bukan perlindungan DDoS terdistribusi. Reverse proxy dapat menambah pembatasan laju global. Update dependency secara berkala dan jalankan ulang tes.
