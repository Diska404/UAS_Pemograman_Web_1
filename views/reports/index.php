<div class="page-heading">
    <div>
        <div class="eyebrow">RINGKASAN & DOKUMENTASI</div>
        <h1>Laporan inventori</h1>
        <p>Telusuri persediaan dan transaksi sesuai periode yang Anda pilih.</p>
    </div>
</div>

<section class="panel form-panel mb-4 report-filter-panel">
    <form method="get" action="/reports" class="row g-3">
        <div class="col-md-4">
            <label for="type" class="form-label">Jenis laporan</label>
            <select id="type" name="type" class="form-select">
                <?php foreach (['stok' => 'Stok barang', 'masuk' => 'Barang masuk', 'keluar' => 'Barang keluar'] as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $report['type'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php foreach (['start' => 'Tanggal awal', 'end' => 'Tanggal akhir'] as $key => $label): ?>
            <div class="col-md-4">
                <label for="<?= $key ?>" class="form-label"><?= $label ?></label>
                <input class="form-control" type="date" name="<?= $key ?>" id="<?= $key ?>" value="<?= e($report[$key]) ?>" required>
            </div>
        <?php endforeach; ?>
        <?php foreach (['barang' => 'Barang', 'gudang' => 'Gudang', 'supplier' => 'Supplier (barang masuk)'] as $key => $label): ?>
            <div class="col-md-4">
                <label for="<?= $key ?>_id" class="form-label"><?= $label ?></label>
                <select class="form-select" name="<?= $key ?>_id" id="<?= $key ?>_id">
                    <option value="">Semua</option>
                    <?php foreach ($options[$key] as $id => $name): ?>
                        <option value="<?= (int)$id ?>" <?= (string)($_GET[$key.'_id'] ?? '') === (string)$id ? 'selected' : '' ?>><?= e($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
        <div class="col-12 d-flex flex-wrap align-items-center gap-3">
            <button class="btn btn-primary" data-loading-label="Menyiapkan laporan…">Tampilkan laporan</button>
            <small class="text-secondary">Laporan stok menunjukkan saldo saat ini; filter tanggal berlaku untuk transaksi.</small>
        </div>
    </form>
</section>

<section class="report-analytics" aria-labelledby="report-analytics-title">
    <div class="report-section-heading">
        <div>
            <div class="eyebrow">VISUAL ANALYTICS</div>
            <h2 id="report-analytics-title">Ringkasan hasil filter</h2>
        </div>
        <p><?= e($report['period']) ?> · <?= count($report['rows']) ?> data</p>
    </div>
    <div class="report-kpi-grid">
        <?php foreach ($analytics['kpis'] as $kpi): ?>
            <article class="report-kpi">
                <span><?= e($kpi['label']) ?></span>
                <strong><?= number_format((float)$kpi['value'], 0, ',', '.') ?></strong>
                <small><?= e($kpi['note']) ?></small>
            </article>
        <?php endforeach; ?>
    </div>
    <div class="report-chart-grid">
        <?php foreach ($analytics['charts'] as $chart): ?>
            <article class="panel report-chart-card">
                <header>
                    <h3><?= e($chart['title']) ?></h3>
                    <p><?= e($chart['subtitle']) ?></p>
                </header>
                <div class="report-chart-canvas">
                    <canvas id="<?= e($chart['id']) ?>" role="img" aria-label="<?= e($chart['title']) ?>"></canvas>
                    <?php if (!$chart['values']): ?><p class="report-chart-empty">Belum ada data untuk filter ini.</p><?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<script type="application/json" id="report-analytics-data"><?= json_encode($analytics, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>

<section class="panel report-table-panel">
    <div class="panel-heading">
        <div>
            <h2><?= e($report['title']) ?></h2>
            <p><?= e($report['period']) ?> · <?= count($report['rows']) ?> data</p>
        </div>
        <?php if ($admin): ?>
            <div class="action-group">
                <a class="btn btn-light" href="/reports?<?= e(http_build_query(array_merge($_GET, ['format' => 'pdf']))) ?>">↓ PDF</a>
                <a class="btn btn-light" href="/reports?<?= e(http_build_query(array_merge($_GET, ['format' => 'excel']))) ?>">↓ Excel</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="table-container">
        <table class="table data-table">
            <thead><tr><?php foreach ($report['headers'] as $header): ?><th><?= e($header) ?></th><?php endforeach; ?></tr></thead>
            <tbody><?php foreach ($report['rows'] as $row): ?><tr><?php foreach ($row as $cell): ?><td><?= e($cell) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody>
        </table>
    </div>
</section>
