# Indeks Dokumentasi SIM Inventory

## Mulai dan penggunaan

- [README](../README.md): instalasi Windows, demo, menjalankan, testing, troubleshooting.
- [Arsitektur](ARCHITECTURE.md): alur request, OOP, aturan stok, kebijakan.
- [API](API.md): endpoint, payload, auth, status, contoh PowerShell.
- [Postman Collection](postman/Inventory_API.postman_collection.json) dan [Environment](postman/Inventory_Local.postman_environment.json).

## Analisis dan perancangan

- [Analisis sistem](01_ANALISIS_SISTEM.md).
- [Database / kamus data](DATABASE.md).
- [ERD](diagrams/ERD.md).
- [Use Case](diagrams/USE_CASE.md).
- [Activity Login](diagrams/ACTIVITY_LOGIN.md).
- [Activity Barang Masuk](diagrams/ACTIVITY_BARANG_MASUK.md).
- [Activity Barang Keluar](diagrams/ACTIVITY_BARANG_KELUAR.md).
- [Flowchart](diagrams/FLOWCHART.md).
- [Class Diagram](diagrams/CLASS_DIAGRAM.md).

## Pengujian dan kesiapan

- [Black Box Testing](BLACK_BOX_TESTING.md): hasil HTTP otomatis.
- [Concurrency Testing](CONCURRENCY_TESTING.md): dua koneksi dan pergantian barang.
- [UI Testing](UI_TESTING.md): browser desktop/tablet/mobile.
- [Final Audit](FINAL_AUDIT.md): checklist dan batas verifikasi.
- [Keamanan](SECURITY.md).
- [Deployment](DEPLOYMENT.md).

## Pengumpulan tugas

- [Laporan proyek](PROJECT_REPORT.md): BAB I–VI dan placeholder identitas/screenshot.
- [Outline presentasi](PRESENTATION_OUTLINE.md): materi 10–15 menit, demo, tanya jawab.

Dokumen ditulis berdasarkan implementasi aktual. Lengkapi identitas kampus dan screenshot final tanpa mengubah hasil pengujian menjadi klaim yang belum dijalankan.

- [Final Handoff](FINAL_HANDOFF.md) — status akhir, akun demo, run dan pekerjaan manual.
- [Pengujian data/filter](READ_MODEL_TESTING.md) — 30 hasil pemeriksaan fixture dan lapisan baca.
