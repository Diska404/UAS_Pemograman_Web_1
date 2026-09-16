# Entity Relationship Diagram

Sesuai `database/schema.sql`. Semua tabel InnoDB, PK BIGINT UNSIGNED; FK RESTRICT kecuali audit user SET NULL dan api_tokens CASCADE.

```mermaid
erDiagram
    roles ||--o{ users : memiliki
    users ||--o{ barang_masuk : mencatat
    users ||--o{ barang_keluar : mencatat
    users o|--o{ audit_logs : melakukan
    users ||--o{ api_tokens : memiliki
    gudang ||--o{ barang : menyimpan
    supplier ||--o{ barang_masuk : memasok
    barang ||--o{ barang_masuk : diterima
    barang ||--o{ barang_keluar : dikeluarkan
    roles {
        bigint id PK
        varchar name UK
    }
    users {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        bigint role_id FK
        tinyint is_active
        timestamp created_at
        timestamp updated_at
    }
    gudang {
        bigint id PK
        varchar kode_gudang UK
        varchar nama_gudang
        varchar lokasi
        text keterangan
        timestamp created_at
        timestamp updated_at
    }
    supplier {
        bigint id PK
        varchar kode_supplier UK
        varchar nama_supplier
        varchar alamat
        varchar telepon
        varchar email
        text keterangan
        timestamp created_at
        timestamp updated_at
    }
    barang {
        bigint id PK
        varchar kode_barang UK
        varchar nama_barang
        varchar kategori
        varchar satuan
        int stok
        int stok_minimum
        bigint gudang_id FK
        varchar foto
        text deskripsi
        timestamp created_at
        timestamp updated_at
    }
    barang_masuk {
        bigint id PK
        varchar nomor_transaksi UK
        date tanggal
        bigint barang_id FK
        bigint supplier_id FK
        int jumlah
        text keterangan
        bigint user_id FK
        timestamp created_at
        timestamp updated_at
    }
    barang_keluar {
        bigint id PK
        varchar nomor_transaksi UK
        date tanggal
        bigint barang_id FK
        int jumlah
        varchar tujuan
        text keterangan
        bigint user_id FK
        timestamp created_at
        timestamp updated_at
    }
    audit_logs {
        bigint id PK
        bigint user_id FK
        varchar activity
        varchar description
        varchar ip_address
        varchar user_agent
        timestamp created_at
    }
    api_tokens {
        bigint id PK
        bigint user_id FK
        char token_hash UK
        datetime expires_at
        timestamp created_at
    }
    login_attempts {
        bigint id PK
        char identity_hash
        timestamp created_at
    }
```

`login_attempts` tidak mempunyai FK user karena percobaan gagal dapat berasal dari email yang belum terdaftar. Identity hash mewakili kombinasi IP dan email.
