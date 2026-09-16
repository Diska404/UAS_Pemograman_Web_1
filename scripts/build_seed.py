"""Build the fixed September 2026 demo fixture without network or random values."""
import json
from pathlib import Path

root = Path(__file__).resolve().parents[1]
demo = json.loads((root / 'database/demo.json').read_text(encoding='utf-8'))


def quote(value):
    if value is None:
        return 'NULL'
    if isinstance(value, int):
        return str(value)
    return "'" + str(value).replace("'", "''") + "'"


lines = ['-- Deterministic demo: October 2025 through September 2026.',
         'SET NAMES utf8mb4;', 'START TRANSACTION;']


def insert(table, values):
    lines.append(f"INSERT INTO {table} ({','.join(values)}) VALUES ({','.join(map(quote, values.values()))});")


for role_id, role in [(1, 'Admin'), (2, 'Operator')]:
    insert('roles', {'id': role_id, 'name': role})
    insert('users', {'id': role_id, 'name': role + ' Inventory', 'email': role.lower() + '@example.com',
                    'password': demo['password_hash'], 'role_id': role_id,
                    'created_at': '2025-10-01 08:00:00', 'updated_at': '2025-10-01 08:00:00'})
for i, name in enumerate(demo['warehouses'], 1):
    insert('gudang', {'id': i, 'kode_gudang': f'GD-{i:03}', 'nama_gudang': name,
                     'lokasi': ['Jakarta - Ruang IT', 'Jakarta - Ruang Komponen', 'Bandung - Laboratorium'][i-1],
                     'keterangan': 'Gudang demonstrasi fiktif', 'created_at': '2025-10-01 08:00:00', 'updated_at': '2025-10-01 08:00:00'})
for i, name in enumerate(demo['suppliers'], 1):
    insert('supplier', {'id': i, 'kode_supplier': f'SUP-{i:03}', 'nama_supplier': name,
                        'alamat': f'Jl. Teknologi Contoh No. {i}, Jakarta', 'telepon': f'021555{i:04}',
                        'email': f'supplier{i}@example.com', 'keterangan': 'Supplier fiktif untuk demonstrasi; bukan klaim distributor resmi.',
                        'created_at': '2025-10-01 08:00:00', 'updated_at': '2025-10-01 08:00:00'})
for item in demo['products']:
    i = item['id']
    insert('barang', {'id': i, 'kode_barang': item['code'], 'nama_barang': item['name'], 'kategori': item['category'],
                      'satuan': 'unit', 'stok_minimum': item['minimum'], 'gudang_id': item['warehouse_id'],
                      'deskripsi': 'Produk untuk simulasi inventori teknologi. Foto kategori merupakan ilustrasi, bukan foto produk.',
                      'created_at': '2025-10-01 08:00:00', 'updated_at': '2026-09-15 08:00:00'})
    previous = 0
    for month in range(12):
        year = 2025 + (month + 9) // 12
        number = (month + 9) % 12 + 1
        balance = item['stock'] + ((i * 7 + month * 3) % (item['minimum'] * 3 + 5) if month < 11 else 0)
        flow = 2 + (i * 3 + month * 5) % 9
        incoming = flow + max(0, balance - previous)
        outgoing = flow + max(0, previous - balance)
        day = 5 + i % 8
        date_in = f'{year}-{number:02}-{day:02}'
        date_out = f'{year}-{number:02}-{day+1:02}'
        insert('barang_masuk', {'nomor_transaksi': f'BM-DEMO-{year}{number:02}-{i:03}', 'tanggal': date_in,
                               'barang_id': i, 'supplier_id': (i-1) % 10 + 1, 'jumlah': incoming,
                               'keterangan': 'Pengadaan demo teknologi', 'user_id': 1,
                               'created_at': date_in + ' 09:00:00', 'updated_at': date_in + ' 09:00:00'})
        insert('barang_keluar', {'nomor_transaksi': f'BK-DEMO-{year}{number:02}-{i:03}', 'tanggal': date_out,
                                'barang_id': i, 'jumlah': outgoing, 'tujuan': ['Divisi IT', 'Laboratorium', 'Tim Infrastruktur'][i % 3],
                                'keterangan': 'Pemakaian operasional demo', 'user_id': 2,
                                'created_at': date_out + ' 14:00:00', 'updated_at': date_out + ' 14:00:00'})
        previous += incoming - outgoing
        assert previous == balance and previous >= 0
lines.append('UPDATE barang b SET stok=COALESCE((SELECT SUM(jumlah) FROM barang_masuk m WHERE m.barang_id=b.id),0)-COALESCE((SELECT SUM(jumlah) FROM barang_keluar k WHERE k.barang_id=b.id),0), updated_at=\'2026-09-15 08:00:00\';')
insert('audit_logs', {'user_id': 1, 'activity': 'seed', 'description': 'Demo teknologi September 2026 diimport',
                      'ip_address': '127.0.0.1', 'user_agent': 'Installer', 'created_at': '2026-09-15 08:00:00'})
lines.append('COMMIT;')
(root / 'database/seed.sql').write_text('\n'.join(lines) + '\n', encoding='utf-8')
print(f"Seed: {len(demo['products'])} products, 720 incoming, 720 outgoing, {sum(p['stock'] for p in demo['products'])} units")
