<?php

namespace App\Services;

use App\Core\{Database, HttpException};

final class DashboardService
{
    public function data(mixed $range = '6'): array
    {
        if (!is_string($range) || !in_array($range, ['6', '12', 'ytd'], true)) {
            throw new HttpException(422, 'Rentang grafik tidak valid.');
        }
        $cards = [];
        foreach (['barang' => 'Total barang','supplier' => 'Supplier','gudang' => 'Gudang'] as $table => $label) {
            $cards[$label] = (int) Database::query("SELECT COUNT(*) FROM $table")->fetchColumn();
        }
        $cards['Total stok'] = (int) Database::query('SELECT COALESCE(SUM(stok),0) FROM barang')->fetchColumn();
        $start = date('Y-m-01');
        $end = date('Y-m-01', strtotime('+1 month', strtotime($start)));
        foreach (['barang_masuk' => 'Masuk bulan ini','barang_keluar' => 'Keluar bulan ini'] as $table => $label) {
            $cards[$label] = (int) Database::query("SELECT COALESCE(SUM(jumlah),0) FROM $table WHERE tanggal>=? AND tanggal<?", [$start,$end])->fetchColumn();
        }
        $cards['Stok rendah'] = (int) Database::query('SELECT COUNT(*) FROM barang WHERE stok>0 AND stok<=stok_minimum')->fetchColumn();
        $cards['Stok habis'] = (int) Database::query('SELECT COUNT(*) FROM barang WHERE stok=0')->fetchColumn();
        $cards['Kategori'] = (int) Database::query('SELECT COUNT(DISTINCT kategori) FROM barang')->fetchColumn();
        $health = [
            ['key' => 'safe', 'label' => 'Aman', 'total' => $cards['Total barang'] - $cards['Stok rendah'] - $cards['Stok habis']],
            ['key' => 'low', 'label' => 'Stok Rendah', 'total' => $cards['Stok rendah']],
            ['key' => 'out', 'label' => 'Habis', 'total' => $cards['Stok habis']],
        ];
        $length = $range === 'ytd' ? (int) date('n') : (int) $range;
        $months = [];
        for ($i = $length - 1; $i >= 0; $i--) {
            $months[] = date('Y-m', strtotime("-$i months", strtotime($start)));
        }
        $series = [];
        foreach (['barang_masuk','barang_keluar'] as $table) {
            $rows = Database::query("SELECT DATE_FORMAT(tanggal,'%Y-%m') month,SUM(jumlah) total FROM $table WHERE tanggal>=? AND tanggal<? GROUP BY month", [$months[0].'-01',$end])->fetchAll();
            $map = array_column($rows, 'total', 'month');
            $series[$table] = array_map(static fn ($m) => (int)($map[$m] ?? 0), $months);
        }
        $category = Database::query('SELECT kategori,SUM(stok) total,COUNT(*) items FROM barang GROUP BY kategori ORDER BY total DESC,kategori')->fetchAll();
        $warehouses = Database::query('SELECT g.id,g.nama_gudang,COALESCE(SUM(b.stok),0) total,COUNT(b.id) items FROM gudang g LEFT JOIN barang b ON b.gudang_id=g.id GROUP BY g.id,g.nama_gudang ORDER BY total DESC,g.id')->fetchAll();
        $recent = Database::query("SELECT * FROM (SELECT m.id,m.nomor_transaksi,m.tanggal,b.nama_barang,m.jumlah,'Masuk' jenis,m.created_at,b.kode_barang FROM barang_masuk m JOIN barang b ON b.id=m.barang_id UNION ALL SELECT k.id,k.nomor_transaksi,k.tanggal,b.nama_barang,k.jumlah,'Keluar' jenis,k.created_at,b.kode_barang FROM barang_keluar k JOIN barang b ON b.id=k.barang_id) t ORDER BY created_at DESC,id DESC,jenis DESC LIMIT 8")->fetchAll();
        $low = Database::query('SELECT b.*,g.nama_gudang FROM barang b JOIN gudang g ON g.id=b.gudang_id WHERE stok<=stok_minimum ORDER BY (stok=0) DESC,stok,b.id LIMIT 8')->fetchAll();
        return compact('cards', 'months', 'series', 'category', 'recent', 'low', 'health', 'warehouses', 'range');
    }
}
