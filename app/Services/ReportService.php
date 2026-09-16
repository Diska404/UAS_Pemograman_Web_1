<?php

namespace App\Services;

use App\Core\{Database, HttpException};

final class ReportService
{
    public function build(array $input): array
    {
        foreach ($input as $value) {
            if (!is_scalar($value)) {
                throw new HttpException(422, 'Filter laporan tidak valid.');
            }
        }
        $type = $input['type'] ?? 'stok';
        if (!in_array($type, ['stok','masuk','keluar'], true)) {
            throw new HttpException(422, 'Jenis laporan tidak valid.');
        }
        $start = $input['start'] ?? date('Y-m-01');
        $end = $input['end'] ?? date('Y-m-d');
        foreach ([$start,$end] as $date) {
            $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if (!$parsed || $parsed->format('Y-m-d') !== $date) {
                throw new HttpException(422, 'Tanggal laporan tidak valid.');
            }
        }
        if ($start > $end) {
            throw new HttpException(422, 'Tanggal awal harus sebelum tanggal akhir.');
        }
        $where = [];
        $params = [];
        foreach (['barang_id' => 'b.id','gudang_id' => 'b.gudang_id'] as $key => $column) {
            if (!empty($input[$key])) {
                if (!ctype_digit((string)$input[$key])) {
                    throw new HttpException(422, 'Filter ID tidak valid.');
                }
                $where[] = "$column=?";
                $params[] = $input[$key];
            }
        }
        if ($type === 'stok') {
            $sql = 'SELECT b.kode_barang AS Kode,b.nama_barang AS Barang,b.kategori AS Kategori,g.nama_gudang AS Gudang,b.satuan AS Satuan,b.stok AS Stok,b.stok_minimum AS Minimum FROM barang b JOIN gudang g ON g.id=b.gudang_id';
        } else {
            $table = $type === 'masuk' ? 'barang_masuk' : 'barang_keluar';
            $extra = $type === 'masuk' ? 's.nama_supplier AS Supplier' : 't.tujuan AS Tujuan';
            $sql = "SELECT t.nomor_transaksi AS Nomor,t.tanggal AS Tanggal,b.nama_barang AS Barang,g.nama_gudang AS Gudang,$extra,t.jumlah AS Jumlah,b.satuan AS Satuan,u.name AS Petugas FROM $table t JOIN barang b ON b.id=t.barang_id JOIN gudang g ON g.id=b.gudang_id JOIN users u ON u.id=t.user_id";
            if ($type === 'masuk') {
                $sql .= ' JOIN supplier s ON s.id=t.supplier_id';
                if (!empty($input['supplier_id'])) {
                    if (!ctype_digit((string)$input['supplier_id'])) {
                        throw new HttpException(422, 'Supplier tidak valid.');
                    }
                    $where[] = 't.supplier_id=?';
                    $params[] = $input['supplier_id'];
                }
            }
            $where[] = 't.tanggal BETWEEN ? AND ?';
            $params[] = $start;
            $params[] = $end;
        }
        $rows = Database::query($sql.($where ? ' WHERE '.implode(' AND ', $where) : '').' ORDER BY '.($type === 'stok' ? 'b.kode_barang' : 't.tanggal DESC,t.id DESC'), $params)->fetchAll();
        $headers = $rows ? array_keys($rows[0]) : ($type === 'stok' ? ['Kode','Barang','Kategori','Gudang','Satuan','Stok','Minimum'] : ['Nomor','Tanggal','Barang','Gudang',$type === 'masuk' ? 'Supplier' : 'Tujuan','Jumlah','Satuan','Petugas']);
        $title = 'Laporan '.($type === 'stok' ? 'Stok Barang' : ($type === 'masuk' ? 'Barang Masuk' : 'Barang Keluar'));
        $period = $type === 'stok' ? 'Saldo saat ini · '.date('d/m/Y H:i') : $start.' s.d. '.$end;
        return compact('type', 'title', 'period', 'rows', 'headers', 'start', 'end');
    }

    public function export(array $report, string $format): void
    {
        if ($format === 'pdf') {
            $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => false,'isPhpEnabled' => false]);
            ob_start();
            require BASE_PATH.'/views/reports/print.php';
            $html = ob_get_clean();
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream('laporan-'.$report['type'].'.pdf', ['Attachment' => true]);
        } elseif ($format === 'excel') {
            $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $book->getActiveSheet();
            $sheet->setTitle('Laporan');
            $last = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($report['headers']));
            foreach ([1 => $report['title'],2 => $report['period'],3 => 'Dicetak: '.date('d/m/Y H:i').' WIB'] as $row => $text) {
                $sheet->setCellValueExplicit('A'.$row, $text, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->mergeCells("A$row:$last$row");
            }
            foreach ([$report['headers'],...array_map('array_values', $report['rows'])] as $r => $values) {
                foreach ($values as $c => $value) {
                    // Explicit strings prevent user data from becoming spreadsheet formulas.
                    $sheet->setCellValueExplicit([$c + 1,$r + 5], (string)$value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
            $sheet->getStyle("A5:{$last}5")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle("A5:{$last}5")->getFill()->setFillType('solid')->getStartColor()->setARGB('FF243D36');
            for ($i = 1;$i <= count($report['headers']);$i++) {
                $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
            }
            $sheet->freezePane('A6');
            $sheet->setAutoFilter('A5:'.$last.(count($report['rows']) + 5));
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="laporan-'.$report['type'].'.xlsx"');
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save('php://output');
        } else {
            throw new HttpException(422,'Format export tidak valid.');
        }
    }
}
