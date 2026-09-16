<?php

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\HttpException;
use App\Models\BarangQuery;
use App\Services\DashboardService;

if (PHP_SAPI !== 'cli' || env('APP_ENV') !== 'local') {
    exit("Hanya untuk database demo lokal.\n");
}
$results = [];
function verify(string $scenario, bool $passed, string $actual): void
{
    global $results;
    $results[] = compact('scenario', 'passed', 'actual');
    echo ($passed ? 'PASS' : 'FAIL') . " | $scenario | $actual\n";
}
$query = new BarangQuery();
$rows = $query->list(BarangQuery::filters([]));
$demo = json_decode(file_get_contents(BASE_PATH . '/database/demo.json'), true, 512, JSON_THROW_ON_ERROR);
verify('60 produk persis sesuai fixture', count($rows) === 60 && array_diff(array_column($demo['products'], 'name'), array_column($rows, 'nama_barang')) === [], count($rows) . ' produk');
$categories = Database::query('SELECT kategori,COUNT(*) total FROM barang GROUP BY kategori')->fetchAll();
verify('6 kategori masing-masing 10 barang', count($categories) === 6 && array_unique(array_column($categories, 'total')) === [10], json_encode($categories));
verify('3 gudang dan 10 supplier fiktif', Database::query('SELECT COUNT(*) FROM gudang')->fetchColumn() === 3 && Database::query('SELECT COUNT(*) FROM supplier')->fetchColumn() === 10, 'Jumlah master');
foreach (['safe' => 40, 'low' => 15, 'out' => 5, 'attention' => 20] as $status => $expected) {
    $filtered = $query->list(BarangQuery::filters(['status' => $status]));
    verify("Filter $status", count($filtered) === $expected, count($filtered) . " / $expected");
}
$intersection = $query->list(BarangQuery::filters(['kategori' => 'IoT & Embedded','gudang_id' => '3','status' => 'out','q' => 'IOT-010']));
verify('Irisan kategori/gudang/status/pencarian', count($intersection) === 1 && $intersection[0]['kode_barang'] === 'IOT-010', count($intersection) . ' hasil');
verify('Pencarian parameter tidak mengubah query SQL', $query->list(BarangQuery::filters(['q' => "' OR 1=1 --"])) === [], 'Tidak ada hasil');
verify('Gudang yang tidak ada menghasilkan kosong', $query->list(BarangQuery::filters(['gudang_id' => '999999'])) === [], 'Tidak ada hasil');
foreach ([['status' => 'unknown'],['q' => ['x']],['gudang_id' => '1 OR 1=1'],['kategori' => str_repeat('x', 81)]] as $invalid) {
    $rejected = false;
    try {
        BarangQuery::filters($invalid);
    } catch (HttpException $error) {
        $rejected = $error->status === 422;
    }
    verify('Filter tidak valid ditolak: ' . json_encode($invalid), $rejected, '422');
}
$dashboard = new DashboardService();
foreach (['6' => 6,'12' => 12,'ytd' => (int)date('n')] as $range => $length) {
    $data = $dashboard->data((string)$range);
    verify("Rentang $range dan seri lengkap", count($data['months']) === $length && count($data['series']['barang_masuk']) === $length && count($data['series']['barang_keluar']) === $length, "$length bulan");
}
$data = $dashboard->data('12');
verify('Kategori dan gudang menjumlah stok yang sama', array_sum(array_column($data['category'], 'total')) === 681 && array_sum(array_column($data['warehouses'], 'total')) === 681, '681 unit');
verify('Kesehatan stok dan KPI cocok', array_column($data['health'], 'total') === [40,15,5] && $data['cards']['Stok rendah'] === 15 && $data['cards']['Stok habis'] === 5, '40/15/5');
verify('Prioritas menampilkan habis dahulu', array_column(array_slice($data['low'], 0, 5), 'stok') === [0,0,0,0,0], '5 teratas stok 0');
$counts = Database::query('SELECT (SELECT COUNT(*) FROM barang_masuk) incoming,(SELECT COUNT(*) FROM barang_keluar) outgoing')->fetch();
verify('Riwayat 720 masuk dan 720 keluar', (int)$counts['incoming'] === 720 && (int)$counts['outgoing'] === 720, json_encode($counts));
$mismatch = Database::query('SELECT COUNT(*) FROM barang b WHERE stok <> COALESCE((SELECT SUM(jumlah) FROM barang_masuk m WHERE m.barang_id=b.id),0)-COALESCE((SELECT SUM(jumlah) FROM barang_keluar k WHERE k.barang_id=b.id),0)')->fetchColumn();
verify('Saldo seluruh barang cocok transaksi', $mismatch === 0 && array_sum(array_column($rows, 'stok')) === 681, "$mismatch selisih; 681 unit");
$events = Database::query("SELECT barang_id,tanggal,jumlah delta,0 type,id FROM barang_masuk UNION ALL SELECT barang_id,tanggal,-jumlah delta,1 type,id FROM barang_keluar ORDER BY tanggal,type,id")->fetchAll();
$balances = [];
$nonnegative = true;
foreach ($events as $event) {
    $id = $event['barang_id'];
    $balances[$id] = ($balances[$id] ?? 0) + (int)$event['delta'];
    if ($balances[$id] < 0) {
        $nonnegative = false;
    }
}
verify('Saldo historis tidak pernah negatif', $nonnegative, count($events).' peristiwa diperiksa');
$base = env('TEST_URL', 'http://localhost:8000');
if (!in_array(parse_url($base, PHP_URL_HOST), ['localhost','127.0.0.1'], true)) {
    throw new RuntimeException('URL test harus lokal');
}
$cookie = tempnam(sys_get_temp_dir(), 'sim_read_');
$http = static function (string $path, ?array $post = null) use ($base, $cookie): array {
    $curl = curl_init($base.$path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true,CURLOPT_COOKIEJAR => $cookie,CURLOPT_COOKIEFILE => $cookie,CURLOPT_TIMEOUT => 20]);
    if ($post !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    if ($body === false) {
        throw new RuntimeException(curl_error($curl));
    }
    return [$status,$body,json_decode($body, true)];
};
try {
    $token = $http('/api/v1/csrf')[2]['data']['csrf_token'];
    $http('/login', ['email' => 'admin@example.com','password' => 'InventoryDemo2026!','_csrf' => $token]);
    [$status,$body,$json] = $http('/api/v1/dashboard?range=12');
    verify('HTTP dashboard range 12', $status === 200 && count($json['data']['months'] ?? []) === 12, "HTTP $status");
    [$status,$body,$json] = $http('/api/v1/barang?status=out&kategori=IoT%20%26%20Embedded&gudang_id=3');
    verify('HTTP API memakai irisan filter bersama', $status === 200 && count($json['data'] ?? []) === 1, "HTTP $status");
    [$status,$body] = $http('/barang?status=out');
    verify('Halaman Admin tabel dan galeri hasil filter', $status === 200 && substr_count($body, 'class="product-card"') === 5 && str_contains($body, 'Tambah barang'), "HTTP $status; 5 kartu");
    [$status] = $http('/api/v1/dashboard?range[]=12');
    verify('HTTP range array ditolak', $status === 422, "HTTP $status");
    [$status] = $http('/api/v1/barang?status[]=out');
    verify('HTTP filter array ditolak', $status === 422, "HTTP $status");
    [$status,$body] = $http('/barang?q=NO_MATCH_DEMO_000');
    verify('Halaman hasil kosong tetap valid', $status === 200 && str_contains($body, 'Tidak ada barang yang sesuai') && substr_count($body, 'class="product-card"') === 0, "HTTP $status");
    $token = $http('/api/v1/csrf')[2]['data']['csrf_token'];
    $http('/logout', ['_csrf' => $token]);
    $token = $http('/api/v1/csrf')[2]['data']['csrf_token'];
    $http('/login', ['email' => 'operator@example.com','password' => 'InventoryDemo2026!','_csrf' => $token]);
    [$status,$body] = $http('/barang?status=out');
    verify('Operator hanya melihat aksi detail barang', $status === 200 && !str_contains($body, 'Tambah barang') && !str_contains($body, 'class="delete-form"') && str_contains($body, '/barang/12'), "HTTP $status");
    $token = $http('/api/v1/csrf')[2]['data']['csrf_token'];
    $http('/logout', ['_csrf' => $token]);
} finally {
    unlink($cookie);
}
$failed = count(array_filter($results, static fn ($r) => !$r['passed']));
file_put_contents(BASE_PATH.'/tests/read-model-results.json', json_encode(['date' => date(DATE_ATOM),'total' => count($results),'failed' => $failed,'results' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo count($results)." assertions; $failed failures\n";
exit($failed ? 1 : 0);
