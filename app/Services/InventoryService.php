<?php

namespace App\Services;

use App\Core\{Database, HttpException};
use App\Models\Resource;

final class InventoryService
{
    /** Reverse the old movement and apply the new one atomically, including item changes. */
    public function save(Resource $resource, array $data, ?int $id, int $userId, bool $delete = false): int
    {
        return Database::transaction(function () use ($resource, $data, $id, $userId, $delete) {
            $old = $id ? $resource->find($id, true) : null;
            $sign = $resource->key === 'barang-masuk' ? 1 : -1;
            $deltas = [];
            if ($old) {
                $deltas[(int) $old['barang_id']] = -$sign * (int) $old['jumlah'];
            }
            if (!$delete) {
                $item = (int) $data['barang_id'];
                $deltas[$item] = ($deltas[$item] ?? 0) + $sign * (int) $data['jumlah'];
            }
            // Consistent ascending lock order reduces deadlocks when moving between items.
            ksort($deltas, SORT_NUMERIC);
            foreach ($deltas as $itemId => $delta) {
                $row = Database::query('SELECT id,stok,nama_barang FROM barang WHERE id=? FOR UPDATE', [$itemId])->fetch();
                if (!$row) {
                    throw new HttpException(422, 'Barang tidak ditemukan.', ['barang_id' => 'Pilih barang yang tersedia.']);
                }
                $stock = (int) $row['stok'] + $delta;
                if ($stock < 0 || $stock > 2000000000) {
                    throw new HttpException(422, 'Stok tidak mencukupi atau melebihi batas. Koreksi transaksi akan membuat saldo tidak valid.', ['jumlah' => 'Stok tersedia: ' . $row['stok'] . '. Periksa transaksi terkait.']);
                }
                Database::query('UPDATE barang SET stok=? WHERE id=?', [$stock, $itemId]);
            }
            if ($delete) {
                $resource->delete($id);
            } elseif ($id) {
                $resource->update($id, $data);
            } else {
                $data['nomor_transaksi'] = ($sign === 1 ? 'BM-' : 'BK-') . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(5)));
                $data['user_id'] = $userId;
                $id = $resource->insert($data);
            }
            Audit::record($resource->key . ($delete ? '_hapus' : ($old ? '_edit' : '_tambah')), 'Transaksi #' . $id . '; perubahan stok ' . json_encode($deltas), $userId);
            return $id;
        });
    }
}
