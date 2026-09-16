<?php

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\{ReportAnalyticsService, ReportService};

if (PHP_SAPI !== 'cli' || env('APP_ENV') !== 'local') {
    exit("Hanya untuk lingkungan lokal.\n");
}

$checks = [];
$verify = static function (string $label, bool $passed, string $actual = '') use (&$checks): void {
    $checks[] = $passed;
    echo ($passed ? 'PASS' : 'FAIL') . " | $label" . ($actual !== '' ? " | $actual" : '') . "\n";
};
$reports = new ReportService();
$analytics = new ReportAnalyticsService();

$stock = $reports->build(['type' => 'stok', 'gudang_id' => '1']);
$stockSummary = $analytics->summarize($stock);
$stockUnits = array_sum(array_map(static fn (array $row): int => (int)$row['Stok'], $stock['rows']));
$verify('KPI stok memakai jumlah baris terfilter', $stockSummary['kpis'][0]['value'] === count($stock['rows']));
$verify('KPI unit stok sama dengan tabel', $stockSummary['kpis'][1]['value'] === $stockUnits, (string)$stockUnits);
$verify('Grafik kesehatan mencakup semua baris stok', array_sum($stockSummary['charts'][0]['values']) === count($stock['rows']));
$verify('Grafik kategori sama dengan total stok tabel', array_sum($stockSummary['charts'][1]['values']) === $stockUnits);
$verify('Grafik gudang sama dengan total stok tabel', array_sum($stockSummary['charts'][2]['values']) === $stockUnits);

$incoming = $reports->build(['type' => 'masuk', 'start' => '2026-07-01', 'end' => '2026-09-30', 'supplier_id' => '1']);
$incomingSummary = $analytics->summarize($incoming);
$incomingUnits = array_sum(array_map(static fn (array $row): int => (int)$row['Jumlah'], $incoming['rows']));
$verify('Filter supplier diterapkan pada tabel masuk', count(array_unique(array_column($incoming['rows'], 'Supplier'))) <= 1);
$verify('KPI masuk sama dengan total tabel', $incomingSummary['kpis'][1]['value'] === $incomingUnits);
$verify('Tren masuk sama dengan total tabel', array_sum($incomingSummary['charts'][0]['values']) === $incomingUnits);
$verify('Produk teratas masuk berasal dari hasil filter', array_sum($incomingSummary['charts'][1]['values']) <= $incomingUnits);
$verify('Kontribusi supplier sama dengan total tabel', array_sum($incomingSummary['charts'][2]['values']) === $incomingUnits);
$allIncoming = $reports->build(['type' => 'masuk', 'start' => '2026-09-01', 'end' => '2026-09-30']);
$allIncomingSummary = $analytics->summarize($allIncoming);
$verify('Kontribusi seluruh supplier tidak dipotong', array_sum($allIncomingSummary['charts'][2]['values']) === array_sum(array_column($allIncoming['rows'], 'Jumlah')));

$outgoing = $reports->build(['type' => 'keluar', 'start' => '2026-09-01', 'end' => '2026-09-30', 'gudang_id' => '2']);
$outgoingSummary = $analytics->summarize($outgoing);
$outgoingUnits = array_sum(array_map(static fn (array $row): int => (int)$row['Jumlah'], $outgoing['rows']));
$verify('KPI keluar sama dengan total tabel', $outgoingSummary['kpis'][1]['value'] === $outgoingUnits);
$verify('Tren keluar sama dengan total tabel', array_sum($outgoingSummary['charts'][0]['values']) === $outgoingUnits);
$verify('Distribusi gudang sama dengan total tabel', array_sum($outgoingSummary['charts'][2]['values']) === $outgoingUnits);

$authView = file_get_contents(BASE_PATH . '/views/auth/form.php');
$appJs = file_get_contents(BASE_PATH . '/public/assets/js/app.js');
$layout = file_get_contents(BASE_PATH . '/views/layouts/main.php');
$css = file_get_contents(BASE_PATH . '/public/assets/css/app.css');
$verify('Login memakai autocomplete username', str_contains($authView, "'username'"));
$verify('Password login memakai current-password', str_contains($authView, "'current-password'"));
$verify('Kontrol ingat email tersedia tanpa name server', str_contains($authView, 'id="remember-email"') && !str_contains($authView, 'name="remember-email"'));
$verify('Penyimpanan lokal memakai kunci khusus email', str_contains($appJs, 'simInventory.rememberedEmail'));
$verify('Kode ingat email tidak membaca atau menyimpan password', !preg_match('/localStorage\.(?:getItem|setItem)\([^\n]*(?:password|current-password)/i', $appJs));
$verify('Halaman laporan memuat skrip grafik lokal', str_contains($layout, '/assets/js/report.js'));
$verify('Transisi halaman native dan reduced motion tersedia', str_contains($css, '@view-transition') && str_contains($css, '::view-transition-old(app-main)') && str_contains($css, 'prefers-reduced-motion: reduce'));

$failed = count(array_filter($checks, static fn (bool $passed): bool => !$passed));
echo "\n" . (count($checks) - $failed) . '/' . count($checks) . " PASS; $failed FAIL\n";
exit($failed ? 1 : 0);
