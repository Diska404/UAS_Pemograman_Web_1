<?php

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\HttpException;
use App\Models\Resource;
use App\Services\{InventoryService, ResourceService};

if (PHP_SAPI !== 'cli' || env('APP_ENV') !== 'local') {
    exit("Local CLI only\n");
}
if (($argv[1] ?? '') === 'worker') {
    try {
        (new InventoryService())->save(new Resource('barang-keluar'), ['barang_id' => (int)$argv[2],'jumlah' => 4,'tanggal' => date('Y-m-d'),'tujuan' => 'QA concurrent'], null, 1);
        echo 'OK';
    } catch (HttpException $error) {
        echo 'REJECTED:'.$error->status;
    }
    exit;
}
$service = new ResourceService();
$inventory = new InventoryService();
$itemIds = [];
$assertions = [];
$assert = function (string $label, bool $ok) use (&$assertions) {
    $assertions[] = [$label,$ok];
    echo ($ok ? 'PASS' : 'FAIL')." | $label\n";
};
try {
    $warehouse = (int)Database::query('SELECT id FROM gudang ORDER BY id LIMIT 1')->fetchColumn();
    $supplier = (int)Database::query('SELECT id FROM supplier ORDER BY id LIMIT 1')->fetchColumn();
    foreach (['A','B'] as $suffix) {
        $itemIds[] = $service->save(new Resource('barang'), ['kode_barang' => 'QA-CON-'.bin2hex(random_bytes(5)).$suffix,'nama_barang' => 'QA concurrency','kategori' => 'QA','satuan' => 'unit','stok_minimum' => 0,'gudang_id' => $warehouse], null, 1);
    }
    [$a,$b] = $itemIds;
    $in = ['barang_id' => $a,'supplier_id' => $supplier,'jumlah' => 5,'tanggal' => date('Y-m-d')];
    $inId = $inventory->save(new Resource('barang-masuk'), $in, null, 1);
    $inventory->save(new Resource('barang-masuk'), array_replace($in, ['barang_id' => $b]), $inId, 1);
    $get = fn ($id) => (int)Database::query('SELECT stok FROM barang WHERE id=?', [$id])->fetchColumn();
    $assert('Edit masuk mengganti barang: saldo A=0 B=5', $get($a) === 0 && $get($b) === 5);
    $inventory->save(new Resource('barang-masuk'), $in, $inId, 1);
    $assert('Edit kembali: saldo A=5 B=0', $get($a) === 5 && $get($b) === 0);
    $inB = $inventory->save(new Resource('barang-masuk'), array_replace($in, ['barang_id' => $b,'jumlah' => 6]), null, 1);
    $out = ['barang_id' => $a,'jumlah' => 2,'tanggal' => date('Y-m-d'),'tujuan' => 'QA switch'];
    $outId = $inventory->save(new Resource('barang-keluar'), $out, null, 1);
    $inventory->save(new Resource('barang-keluar'), array_replace($out, ['barang_id' => $b]), $outId, 1);
    $assert('Edit keluar mengganti barang: saldo A=5 B=4', $get($a) === 5 && $get($b) === 4);
    $inventory->save(new Resource('barang-keluar'), [], $outId, 1, true);
    $assert('Hapus transaksi pindahan: saldo B=6', $get($b) === 6);
    $processes = [];
    // Independent CLI processes use separate PDO connections to exercise row locking.
    for ($i = 0;$i < 2;$i++) {
        $pipes = [];
        $process = proc_open([PHP_BINARY,__FILE__,'worker',(string)$a], [0 => ['pipe','r'],1 => ['pipe','w'],2 => ['pipe','w']], $pipes, BASE_PATH);
        fclose($pipes[0]);
        $processes[] = [$process,$pipes];
    }
    $outcomes = [];
    foreach ($processes as [$process,$pipes]) {
        $outcomes[] = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        if ($stderr) {
            echo $stderr;
        }
    }
    sort($outcomes);
    $assert('Dua koneksi keluar 4 dari stok 5: satu berhasil, satu ditolak', $outcomes === ['OK','REJECTED:422']);
    $assert('Saldo akhir concurrency = 1', $get($a) === 1);
    $assert('Hanya satu transaksi keluar tersimpan', (int)Database::query('SELECT COUNT(*) FROM barang_keluar WHERE barang_id=?', [$a])->fetchColumn() === 1);
} finally {
    foreach ($itemIds as $id) {
        Database::query('DELETE FROM barang_keluar WHERE barang_id=?', [$id]);
        Database::query('DELETE FROM barang_masuk WHERE barang_id=?', [$id]);
        Database::query('DELETE FROM barang WHERE id=?', [$id]);
    }
}
$md = "# Pengujian concurrency dan pergantian barang\n\nDijalankan ".date('Y-m-d H:i')." WIB melalui dua proses PHP dengan koneksi PDO terpisah. Jalankan `php tests/concurrency.php` pada environment lokal.\n\n| Skenario | Hasil |\n|---|---|\n";
foreach ($assertions as [$label,$ok]) {
    $md .= "| $label | ".($ok ? 'PASS' : 'FAIL')." |\n";
}
$md .= "\nBukan uji beban. Dua permintaan berebut saldo yang sama; SELECT FOR UPDATE menyerialisasi perubahan. Transaksi kedua membaca saldo hasil commit pertama.\n";
file_put_contents(BASE_PATH.'/docs/CONCURRENCY_TESTING.md',$md);
exit(count(array_filter($assertions,fn ($r) => !$r[1])) ? 1 : 0);
