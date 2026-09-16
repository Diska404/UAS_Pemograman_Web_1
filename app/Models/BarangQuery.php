<?php

namespace App\Models;

use App\Core\{Database, HttpException};

final class BarangQuery
{
    public static function filters(array $input): array
    {
        $filters = [];
        foreach (['q', 'kategori', 'gudang_id', 'status'] as $key) {
            $value = $input[$key] ?? '';
            if (!is_scalar($value)) {
                throw new HttpException(422, 'Filter barang tidak valid.');
            }
            $filters[$key] = trim((string) $value);
        }
        if (mb_strlen($filters['q']) > 150 || mb_strlen($filters['kategori']) > 80) {
            throw new HttpException(422, 'Teks filter terlalu panjang.');
        }
        if ($filters['gudang_id'] !== '' && (!ctype_digit($filters['gudang_id']) || (float) $filters['gudang_id'] < 1 || (float) $filters['gudang_id'] > 1000000000)) {
            throw new HttpException(422, 'Gudang tidak valid.');
        }
        if (!in_array($filters['status'], ['', 'safe', 'low', 'out', 'attention'], true)) {
            throw new HttpException(422, 'Status stok tidak valid.');
        }
        return $filters;
    }

    public function list(array $filters): array
    {
        $where = [];
        $params = [];
        foreach (['kategori', 'gudang_id'] as $key) {
            if ($filters[$key] !== '') {
                $where[] = "b.$key=?";
                $params[] = $filters[$key];
            }
        }
        if ($filters['q'] !== '') {
            $where[] = '(b.nama_barang LIKE ? OR b.kode_barang LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }
        $status = match ($filters['status']) {
            'safe' => 'b.stok>b.stok_minimum',
            'low' => 'b.stok>0 AND b.stok<=b.stok_minimum',
            'out' => 'b.stok=0',
            'attention' => 'b.stok<=b.stok_minimum',
            default => '',
        };
        if ($status !== '') {
            $where[] = $status;
        }
        return Database::query('SELECT b.*,g.nama_gudang FROM barang b JOIN gudang g ON g.id=b.gudang_id'
            . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY b.kode_barang', $params)->fetchAll();
    }
}
