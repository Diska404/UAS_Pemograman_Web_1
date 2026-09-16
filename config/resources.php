<?php

$text = static fn ($label, $max = 100, $required = true) => compact('label', 'max', 'required') + ['type' => 'text'];
$select = static fn ($label, $source) => compact('label', 'source') + ['type' => 'select'];
$note = ['label' => 'Keterangan', 'type' => 'textarea', 'max' => 3000, 'required' => false];
$date = ['label' => 'Tanggal', 'type' => 'date'];
$quantity = ['label' => 'Jumlah', 'type' => 'number', 'min' => 1];
return [
    'barang' => ['table' => 'barang', 'title' => 'Data Barang', 'fields' => [
        'kode_barang' => $text('Kode barang', 30), 'nama_barang' => $text('Nama barang'),
        'kategori' => $text('Kategori', 80), 'satuan' => $text('Satuan', 30),
        'stok_minimum' => ['label' => 'Stok minimum', 'type' => 'number', 'min' => 0],
        'gudang_id' => $select('Gudang', 'gudang'), 'deskripsi' => ['label' => 'Deskripsi'] + $note]],
    'gudang' => ['table' => 'gudang', 'title' => 'Data Gudang', 'fields' => [
        'kode_gudang' => $text('Kode gudang', 30), 'nama_gudang' => $text('Nama gudang'), 'lokasi' => $text('Lokasi', 255), 'keterangan' => $note]],
    'supplier' => ['table' => 'supplier', 'title' => 'Data Supplier', 'fields' => [
        'kode_supplier' => $text('Kode supplier', 30), 'nama_supplier' => $text('Nama supplier'), 'alamat' => $text('Alamat', 255),
        'telepon' => $text('Telepon', 30), 'email' => ['label' => 'Email','type' => 'email','max' => 150,'required' => false], 'keterangan' => $note]],
    'barang-masuk' => ['table' => 'barang_masuk', 'title' => 'Barang Masuk', 'fields' => [
        'tanggal' => $date, 'barang_id' => $select('Barang', 'barang'), 'supplier_id' => $select('Supplier', 'supplier'), 'jumlah' => $quantity, 'keterangan' => $note]],
    'barang-keluar' => ['table' => 'barang_keluar', 'title' => 'Barang Keluar', 'fields' => [
        'tanggal' => $date, 'barang_id' => $select('Barang', 'barang'), 'jumlah' => $quantity, 'tujuan' => $text('Tujuan', 255), 'keterangan' => $note]],
    'users' => ['table' => 'users','title' => 'Manajemen User','fields' => [
        'name' => $text('Nama lengkap'), 'email' => ['label' => 'Email','type' => 'email','max' => 150],
        'role_id' => $select('Role', 'roles'), 'is_active' => ['label' => 'Status akun','type' => 'select','source' => 'status','min' => 0]]],
];
