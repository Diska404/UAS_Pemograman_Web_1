# REST API SIM Inventory v1

Base URL lokal: `http://localhost:8000/api/v1`. Semua response JSON memakai UTF-8. API membaca data sebenarnya dari MySQL melalui PDO.

## Envelope dan status

```json
{"success":true,"message":"Data berhasil diambil","data":[],"errors":{}}
```

Error validasi:

```json
{"success":false,"message":"Periksa kembali isian formulir.","data":null,"errors":{"kode_barang":"Kolom ini wajib diisi."}}
```

Status: 200 berhasil, 201 dibuat, 400 JSON tidak valid, 401 tidak terautentikasi/token invalid, 403 role tidak berhak, 404 data/route tidak ada, 405 metode salah, 409 kode/email duplikat atau FK delete/konflik lock, 413 body terlalu besar, 415 Content-Type salah, 419 CSRF salah, 422 validasi/referensi tidak valid, 429 login terlalu sering, 500 error internal generik. Error tidak menampilkan stack trace atau credential.

## Autentikasi dan CSRF

1. **GET `/csrf`**, tanpa autentikasi. Response 200:

   ```json
   {"success":true,"message":"Data berhasil diambil","data":{"csrf_token":"<64 karakter hex>"},"errors":{}}
   ```

   Simpan cookie `sim_inventory` dari response dan token. Cookie harus dikirim kembali pada request mutasi. Endpoint ini tidak memberikan hak Admin ataupun login.

2. **POST `/token`**, header `Content-Type: application/json`, `X-CSRF-Token: <csrf>`, cookie dari langkah 1. Body:

   ```json
   {"email":"admin@example.com","password":"InventoryDemo2026!"}
   ```

   Response **201**:

   ```json
   {"success":true,"message":"Token diterbitkan","data":{"token":"<64 karakter hex>","token_type":"Bearer","expires_in":28800},"errors":{}}
   ```

   Token hanya ditampilkan saat pembuatan, database menyimpan hash SHA-256. Token berlaku 8 jam. Login salah/inactive: 422; setelah lima kegagalan dalam 15 menit per IP+email: 429; tanpa CSRF: 419. Akun harus aktif.

3. GET API dapat menggunakan `Authorization: Bearer <token>` atau cookie sesi login web. Jika header Bearer ada tetapi invalid, sesi tidak digunakan sebagai fallback.
4. POST/PUT/DELETE barang **wajib token Admin**, `X-CSRF-Token` valid, dan cookie sesi CSRF. Cookie login tanpa token tidak cukup untuk mutasi API.

CSRF ditambahkan pada API token untuk memenuhi kebijakan semua mutasi, walaupun Bearer yang tidak dikirim otomatis browser biasanya sudah tahan CSRF. Postman cookie jar harus aktif. Login web meregenerasi sesi dan token CSRF; ambil `/csrf` lagi jika login melalui web pada cookie jar yang sama.

## Endpoint resource

| Method | URL | Auth | Request | Sukses | Error khusus |
|---|---|---|---|---|---|
| GET | `/barang` | Sesi / Bearer aktif | Query opsional `q`, `kategori`, `gudang_id`, `status` | 200 array barang | 401,422 |
| GET | `/barang/{id}` | Sesi / Bearer aktif | ID positif | 200 objek barang | 401,404 |
| POST | `/barang` | Bearer Admin + CSRF | JSON barang lengkap | 201 objek baru | 401,403,409,419,422 |
| PUT | `/barang/{id}` | Bearer Admin + CSRF | JSON seluruh field wajib | 200 objek terubah | 401,403,404,409,419,422 |
| DELETE | `/barang/{id}` | Bearer Admin + CSRF | Tanpa body | 200 data null | 401,403,404,409,419 |
| GET | `/stok` | Sesi / Bearer aktif | Query seperti `/barang` | 200 array dengan stok | 401 |
| GET | `/dashboard` | Sesi / Bearer aktif | `range=6`, `12`, atau `ytd` | 200 ringkasan | 401,422 |
| DELETE | `/token` | Bearer aktif + CSRF | Tanpa body | 200 data null | 401,419 |

### GET barang

Contoh request: `/barang?q=LPT-001&kategori=Laptop%20%26%20Komputer&gudang_id=1&status=safe`. Pencarian `q` memeriksa nama/kode; kategori dan gudang exact-match. Semua filter beririsan (AND), sama dengan halaman `/barang`. Response list tidak dipaginasi di server; DataTables dan galeri memakai hasil yang sama.

`status`: `safe` (`stok > stok_minimum`), `low` (`0 < stok <= stok_minimum`), `out` (`stok = 0`), `attention` (`stok <= stok_minimum`). Kosong berarti semua. Array, status asing, gudang nonnumerik, q lebih dari 150 karakter, atau kategori lebih dari 80 menghasilkan 422. Filter valid tanpa kecocokan menghasilkan array kosong.

Contoh dari fixture lokal; transaksi berikutnya dapat mengubah saldo/timestamp:

```json
{
    "success": true,
    "message": "Data berhasil diambil",
    "data": [
        {
            "id": 1,
            "kode_barang": "LPT-001",
            "nama_barang": "Acer Predator Helios Neo 16 PHN16-71",
            "kategori": "Laptop & Komputer",
            "satuan": "unit",
            "stok": 7,
            "stok_minimum": 2,
            "gudang_id": 1,
            "foto": null,
            "deskripsi": "Produk untuk simulasi inventori teknologi. Foto kategori merupakan ilustrasi, bukan foto produk.",
            "created_at": "2025-10-01 08:00:00",
            "updated_at": "2026-09-15 08:00:00",
            "nama_gudang": "Gudang Perangkat IT"
        }
    ],
    "errors": {}
}
```

GET detail memakai objek tunggal pada `data`, bukan array; join `nama_gudang` tambahan tersedia pada list.

### POST / PUT barang

```json
{
  "kode_barang": "API-DEMO-001",
  "nama_barang": "Kabel USB Demo",
  "kategori": "Elektronik",
  "satuan": "buah",
  "stok_minimum": 5,
  "gudang_id": 1,
  "deskripsi": "Barang untuk demonstrasi REST"
}
```

Field wajib: kode_barang (maks 30), nama_barang (100), kategori (80), satuan (30), stok_minimum (integer 0–1 miliar), gudang_id valid. Deskripsi opsional maks 3.000. PUT merupakan penggantian seluruh field master wajib, bukan PATCH. `foto` diunggah melalui form web multipart, bukan endpoint JSON. `stok` tidak boleh diubah langsung; barang baru stok 0. Input `stok` nonzero ditolak 422. Field tidak di-allowlist tidak dimasukkan SQL.

POST sukses 201, PUT sukses 200; keduanya mengembalikan objek barang aktual, message `Barang berhasil disimpan`. Kode unik duplikat 409. Tidak ada gudang terkait 422. Tidak ada ID target 404. Contoh error duplikat:

```json
{"success":false,"message":"Kode atau email sudah digunakan. Gunakan nilai yang unik.","data":null,"errors":{}}
```

### DELETE barang

Data hanya dapat dihapus bila tidak direferensikan transaksi. Response 200:

```json
{"success":true,"message":"Barang berhasil dihapus","data":null,"errors":{}}
```

Jika masih dipakai: 409 dengan message `Data masih digunakan oleh barang atau transaksi. Data tidak dapat dihapus.` Tidak ada efek stok tersembunyi pada DELETE master.

### GET stok dan dashboard

`/stok` merupakan alias pembacaan barang dengan filter yang sama. `/dashboard?range=6` mengembalikan agregat langsung dari database. Rentang `6` (default), `12`, dan `ytd` dihitung dari bulan berjalan zona waktu aplikasi. Bulan tanpa transaksi berisi 0. Rentang hanya mengubah `months` dan `series`; komposisi/kesehatan selalu saldo saat ini.

Contoh fixture pada 15 September 2026 (field `recent` dan `low` tidak ditampilkan di contoh agar ringkas):

```json
{
    "success": true,
    "message": "Data berhasil diambil",
    "data": {
        "cards": {
            "Total barang": 60,
            "Supplier": 10,
            "Gudang": 3,
            "Total stok": 681,
            "Masuk bulan ini": 360,
            "Keluar bulan ini": 836,
            "Stok rendah": 15,
            "Stok habis": 5,
            "Kategori": 6
        },
        "months": [
            "2026-04",
            "2026-05",
            "2026-06",
            "2026-07",
            "2026-08",
            "2026-09"
        ],
        "series": {
            "barang_masuk": [
                459,
                555,
                513,
                459,
                552,
                360
            ],
            "barang_keluar": [
                383,
                627,
                492,
                383,
                641,
                836
            ]
        },
        "category": [
            {
                "kategori": "IoT & Embedded",
                "total": "195",
                "items": 10
            },
            {
                "kategori": "Peripheral",
                "total": "124",
                "items": 10
            },
            {
                "kategori": "Networking",
                "total": "101",
                "items": 10
            },
            {
                "kategori": "Komponen Komputer",
                "total": "99",
                "items": 10
            },
            {
                "kategori": "Monitor & Display",
                "total": "94",
                "items": 10
            },
            {
                "kategori": "Laptop & Komputer",
                "total": "68",
                "items": 10
            }
        ],
        "health": [
            {
                "key": "safe",
                "label": "Aman",
                "total": 40
            },
            {
                "key": "low",
                "label": "Stok Rendah",
                "total": 15
            },
            {
                "key": "out",
                "label": "Habis",
                "total": 5
            }
        ],
        "warehouses": [
            {
                "id": 3,
                "nama_gudang": "Gudang IoT & Networking",
                "total": "296",
                "items": 20
            },
            {
                "id": 1,
                "nama_gudang": "Gudang Perangkat IT",
                "total": "286",
                "items": 30
            },
            {
                "id": 2,
                "nama_gudang": "Gudang Komponen",
                "total": "99",
                "items": 10
            }
        ],
        "range": "6"
    },
    "errors": {}
}
```

`category.total` dan `warehouses.total` adalah unit stok; `items` jumlah jenis barang. `health.total` menghitung jenis barang, dengan key safe/low/out. Total kategori = total gudang = card Total stok; total health = Total barang. `Stok rendah` kini hanya saldo positif sampai minimum; stok 0 berada pada `Stok habis`.

`recent` maksimal 8 transaksi Masuk/Keluar dengan kode barang, nomor, tanggal, jumlah dan created_at. `low` maksimal 8 barang yang perlu perhatian, diurutkan stok habis dahulu, kemudian stok terendah dan ID. Jumlah KPI tidak dibatasi panjang panel. Key lama tetap tersedia; field range/health/warehouses/items merupakan tambahan. Tidak ada perubahan autentikasi atau mutasi API.

### DELETE token

Mencabut hanya Bearer yang dikirim. Response 200 message `Token dicabut`, data null. Pemakaian ulang token menghasilkan 401. Logout web tidak mencabut token independen; gunakan endpoint revoke. Perubahan password atau perubahan role/status Admin mencabut semua token user tersebut.

## Contoh PowerShell

```powershell
$base = 'http://localhost:8000/api/v1'
$csrf = Invoke-RestMethod "$base/csrf" -SessionVariable apiSession
$headers = @{ 'X-CSRF-Token' = $csrf.data.csrf_token }
$login = @{ email='admin@example.com'; password='InventoryDemo2026!' } | ConvertTo-Json
$token = Invoke-RestMethod "$base/token" -Method Post -WebSession $apiSession -Headers $headers -ContentType 'application/json' -Body $login
$headers.Authorization = 'Bearer ' + $token.data.token
Invoke-RestMethod "$base/barang" -WebSession $apiSession -Headers $headers
```

## Postman

Import collection/environment dalam `docs/postman/`. Jalankan urutan: CSRF → Token → GET → POST → Detail → PUT → DELETE → Revoke. Script collection menyimpan `csrf_token`, `token`, dan `barang_id`; cookie jar otomatis menjaga sesi. Kode barang demo dibuat unik. Collection membuat lalu menghapus barang demo **yang dibuat collection sendiri**, tanpa transaksi. Jangan memasukkan ID barang asli ke variable `barang_id` untuk skenario DELETE demo.
