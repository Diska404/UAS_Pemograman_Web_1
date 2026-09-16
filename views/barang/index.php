<?php use App\Helpers\InventoryPresentation as ItemUI; ?>
<div class="page-heading">
    <div><div class="eyebrow">MASTER DATA</div><h1>Data Barang<span class="heading-count"><?= $total ?></span></h1><p>Temukan perangkat, pantau persediaan, dan kelola inventori Anda.</p></div>
    <div class="page-actions"><a class="btn btn-light" href="/reports"><?= icon('report') ?> Laporan stok</a><?php if ($canWrite): ?><a class="btn btn-primary" href="/barang/create"><?= icon('plus') ?> Tambah barang</a><?php endif; ?></div>
</div>
<section class="panel inventory-panel" id="inventory-panel">
    <div class="inventory-toolbar">
        <div><h2>Direktori inventori</h2><p><?= count($rows) ?> dari <?= $total ?> barang sesuai filter</p></div>
        <div class="view-switch" role="group" aria-label="Tampilan barang"><button class="view-button active" data-view="table" aria-pressed="true"><?= icon('table') ?> Tabel</button><button class="view-button" data-view="gallery" aria-pressed="false"><?= icon('grid') ?> Galeri</button></div>
    </div>
    <form class="inventory-filters" action="/barang" method="get" id="inventory-filters">
        <div class="filter-search"><label for="inventory-query">Cari barang</label><div class="search-input"><?= icon('search') ?><input class="form-control" type="search" name="q" id="inventory-query" placeholder="Nama atau kode barang…" value="<?= e($filters['q']) ?>" maxlength="150"></div></div>
        <div><label for="inventory-category">Kategori</label><select class="form-select" name="kategori" id="inventory-category"><option value="">Semua kategori</option><?php foreach ($categories as $category): ?><option <?= $filters['kategori'] === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select></div>
        <div><label for="inventory-warehouse">Gudang</label><select class="form-select" name="gudang_id" id="inventory-warehouse"><option value="">Semua gudang</option><?php foreach ($options['gudang'] as $id => $label): ?><option value="<?= (int) $id ?>" <?= $filters['gudang_id'] === (string) $id ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div><label for="inventory-status">Status stok</label><select class="form-select" name="status" id="inventory-status"><?php foreach (['' => 'Semua status', 'safe' => 'Aman', 'low' => 'Stok Rendah', 'out' => 'Habis', 'attention' => 'Perlu perhatian'] as $key => $label): ?><option value="<?= e($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="filter-actions"><button class="btn btn-primary" type="submit" aria-label="Terapkan filter"><?= icon('filter') ?><span>Terapkan</span></button><a class="icon-button filter-reset" href="/barang" aria-label="Hapus semua filter" title="Hapus semua filter"><?= icon('close') ?></a></div>
    </form>
    <?php if (array_filter($filters)): ?><div class="active-filters" aria-label="Filter aktif"><span>Filter aktif</span><?php foreach ($filters as $key => $value): if ($value === '') continue; ?><span class="filter-chip"><?= e(match ($key) { 'status' => ['safe'=>'Aman','low'=>'Stok Rendah','out'=>'Habis','attention'=>'Perlu perhatian'][$value], 'gudang_id' => $options['gudang'][$value] ?? 'Gudang tidak ditemukan', default => $value }) ?></span><?php endforeach; ?></div><?php endif; ?>
    <div class="table-container inventory-table-container">
        <table class="table inventory-table" id="inventory-table"><thead><tr><th data-orderable="false">Foto</th><th>Kode</th><th>Nama barang</th><th>Kategori</th><th>Gudang</th><th>Stok</th><th>Minimum</th><th>Status</th><th data-orderable="false">Aksi</th></tr></thead><tbody>
        <?php foreach ($rows as $row): $health = ItemUI::health($row); ?>
        <tr data-item-id="<?= (int) $row['id'] ?>"><td><img class="table-product-image" loading="lazy" src="<?= e(ItemUI::photo($row)) ?>" data-fallback="<?= e(ItemUI::placeholder($row['kategori'])) ?>" alt="<?= e(!empty($row['foto']) ? 'Foto ' . $row['nama_barang'] : 'Ilustrasi kategori ' . $row['kategori']) ?>"></td><td class="code-cell"><?= e($row['kode_barang']) ?></td><td class="product-name-cell"><a href="/barang/<?= (int) $row['id'] ?>"><?= e($row['nama_barang']) ?></a></td><td><?= e($row['kategori']) ?></td><td><?= e($row['nama_gudang']) ?></td><td data-order="<?= (int) $row['stok'] ?>"><strong><?= (int) $row['stok'] ?></strong><small class="cell-secondary"><?= e($row['satuan']) ?></small></td><td><?= (int) $row['stok_minimum'] ?></td><td><span class="stock-status <?= e($health['key']) ?>"><span aria-hidden="true"><?= $health['icon'] ?></span> <?= e($health['label']) ?></span></td><td><?php require BASE_PATH . '/views/barang/actions.php'; ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>
        <div class="product-gallery" id="product-gallery" hidden>
        <?php foreach ($rows as $row): $health = ItemUI::health($row); ?>
            <article class="product-card" data-item-id="<?= (int) $row['id'] ?>">
                <a class="product-art" href="/barang/<?= (int) $row['id'] ?>" tabindex="-1" aria-hidden="true"><img loading="lazy" src="<?= e(ItemUI::photo($row)) ?>" data-fallback="<?= e(ItemUI::placeholder($row['kategori'])) ?>" alt=""></a>
                <div class="product-card-body"><div class="product-meta"><span class="code-cell"><?= e($row['kode_barang']) ?></span><span class="stock-status <?= e($health['key']) ?>"><?= $health['icon'] ?> <?= e($health['label']) ?></span></div><h3><a href="/barang/<?= (int) $row['id'] ?>"><?= e($row['nama_barang']) ?></a></h3><div class="product-category"><?= e($row['kategori']) ?></div><p class="product-location"><?= icon('warehouse') ?> <?= e($row['nama_gudang']) ?></p><div class="stock-summary"><span><strong><?= (int) $row['stok'] ?></strong> <?= e($row['satuan']) ?> tersedia</span><small>Min. <?= (int) $row['stok_minimum'] ?></small></div>
                <?php if ((int) $row['stok_minimum'] > 0): ?><progress class="stock-progress <?= e($health['key']) ?>" value="<?= min((int) $row['stok'], (int) $row['stok_minimum']) ?>" max="<?= (int) $row['stok_minimum'] ?>" aria-label="Pemenuhan stok minimum"></progress><div class="minimum-caption"><?= min(100, (int) floor(100 * $row['stok'] / $row['stok_minimum'])) ?>% batas minimum terpenuhi<?= $row['stok'] > $row['stok_minimum'] ? ' · di atas minimum' : '' ?></div><?php else: ?><div class="minimum-caption">Batas minimum 0 · tidak memakai indikator persentase</div><?php endif; ?>
                <div class="product-card-actions"><?php require BASE_PATH . '/views/barang/actions.php'; ?></div></div>
            </article>
        <?php endforeach; ?>
        <div class="gallery-empty" id="gallery-empty" hidden><?= icon('search') ?><h3>Tidak ada barang yang sesuai</h3><p>Coba ubah pencarian atau hapus filter.</p><a class="btn btn-light" href="/barang">Reset filter</a></div>
        </div>
    </div>
</section>
