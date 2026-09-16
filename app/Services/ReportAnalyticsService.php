<?php

namespace App\Services;

final class ReportAnalyticsService
{
    public function summarize(array $report): array
    {
        $rows = $report['rows'] ?? [];

        return match ($report['type'] ?? 'stok') {
            'masuk' => $this->movement($rows, $report, true),
            'keluar' => $this->movement($rows, $report, false),
            default => $this->stock($rows),
        };
    }

    private function stock(array $rows): array
    {
        $health = ['Aman' => 0, 'Menipis' => 0, 'Habis' => 0];
        $categories = [];
        $warehouses = [];
        $units = 0;

        foreach ($rows as $row) {
            $stock = (int)($row['Stok'] ?? 0);
            $minimum = (int)($row['Minimum'] ?? 0);
            $units += $stock;
            $status = $stock <= 0 ? 'Habis' : ($stock <= $minimum ? 'Menipis' : 'Aman');
            $health[$status]++;
            $categories[(string)$row['Kategori']] = ($categories[(string)$row['Kategori']] ?? 0) + $stock;
            $warehouses[(string)$row['Gudang']] = ($warehouses[(string)$row['Gudang']] ?? 0) + $stock;
        }

        return [
            'type' => 'stok',
            'kpis' => [
                ['label' => 'Barang terfilter', 'value' => count($rows), 'note' => 'jenis barang'],
                ['label' => 'Total stok', 'value' => $units, 'note' => 'unit tersedia'],
                ['label' => 'Stok menipis', 'value' => $health['Menipis'], 'note' => 'perlu dipantau'],
                ['label' => 'Stok habis', 'value' => $health['Habis'], 'note' => 'perlu ditindaklanjuti'],
            ],
            'charts' => [
                ['id' => 'report-health-chart', 'title' => 'Kesehatan stok', 'subtitle' => 'Jumlah jenis barang per status', 'kind' => 'doughnut', 'labels' => array_keys($health), 'values' => array_values($health)],
                ['id' => 'report-category-chart', 'title' => 'Stok per kategori', 'subtitle' => 'Total unit dari hasil filter', 'kind' => 'bar-horizontal', 'labels' => array_keys($categories), 'values' => array_values($categories)],
                ['id' => 'report-warehouse-chart', 'title' => 'Stok per gudang', 'subtitle' => 'Total unit dari hasil filter', 'kind' => 'bar', 'labels' => array_keys($warehouses), 'values' => array_values($warehouses)],
            ],
        ];
    }

    private function movement(array $rows, array $report, bool $incoming): array
    {
        $start = new \DateTimeImmutable($report['start']);
        $end = new \DateTimeImmutable($report['end']);
        $daily = $start->diff($end)->days <= 62;
        $trend = [];
        $products = [];
        $contributors = [];
        $quantity = 0;

        foreach ($rows as $row) {
            $amount = (int)($row['Jumlah'] ?? 0);
            $quantity += $amount;
            $date = (string)($row['Tanggal'] ?? '');
            $period = $daily ? substr($date, 0, 10) : substr($date, 0, 7);
            $trend[$period] = ($trend[$period] ?? 0) + $amount;
            $product = (string)($row['Barang'] ?? 'Tanpa nama');
            $products[$product] = ($products[$product] ?? 0) + $amount;
            $contributor = (string)($row[$incoming ? 'Supplier' : 'Gudang'] ?? 'Tidak diketahui');
            $contributors[$contributor] = ($contributors[$contributor] ?? 0) + $amount;
        }

        ksort($trend);
        $products = $this->rank($products, 5);
        $contributors = $this->rank($contributors, count($contributors));
        $topProduct = array_key_first($products);

        return [
            'type' => $incoming ? 'masuk' : 'keluar',
            'kpis' => [
                ['label' => 'Transaksi', 'value' => count($rows), 'note' => 'sesuai filter'],
                ['label' => 'Total kuantitas', 'value' => $quantity, 'note' => 'unit tercatat'],
                ['label' => $incoming ? 'Supplier aktif' : 'Barang bergerak', 'value' => count(array_unique(array_map(static fn (array $row): string => (string)($row[$incoming ? 'Supplier' : 'Barang'] ?? ''), $rows))), 'note' => 'dalam periode'],
                ['label' => 'Produk teratas', 'value' => $topProduct ? (int)$products[$topProduct] : 0, 'note' => $topProduct ?: 'Belum ada data'],
            ],
            'charts' => [
                ['id' => 'report-trend-chart', 'title' => 'Tren '.($incoming ? 'barang masuk' : 'barang keluar'), 'subtitle' => 'Total unit per '.($daily ? 'hari' : 'bulan'), 'kind' => 'line', 'labels' => array_keys($trend), 'values' => array_values($trend)],
                ['id' => 'report-product-chart', 'title' => 'Produk teratas', 'subtitle' => 'Maksimal 5 produk berdasarkan kuantitas', 'kind' => 'bar-horizontal', 'labels' => array_keys($products), 'values' => array_values($products)],
                ['id' => 'report-contributor-chart', 'title' => $incoming ? 'Kontribusi supplier' : 'Distribusi per gudang', 'subtitle' => 'Proporsi unit dari hasil filter', 'kind' => $incoming ? 'bar-horizontal' : 'doughnut', 'labels' => array_keys($contributors), 'values' => array_values($contributors)],
            ],
        ];
    }

    private function rank(array $values, int $limit): array
    {
        uksort($values, static function (string $left, string $right) use ($values): int {
            return $values[$right] <=> $values[$left] ?: strnatcasecmp($left, $right);
        });

        return array_slice($values, 0, $limit, true);
    }
}
