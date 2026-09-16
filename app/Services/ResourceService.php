<?php

namespace App\Services;

use App\Core\{Database, HttpException};
use App\Helpers\Validator;
use App\Models\Resource;

final class ResourceService
{
    public function save(Resource $resource, array $input, ?int $id, int $actorId): int
    {
        $data = Validator::validate($input, $resource->fields);
        if ($resource->key === 'barang' && isset($input['stok']) && (string) $input['stok'] !== '0') {
            throw new HttpException(422, 'Stok hanya dapat diubah melalui transaksi.', ['stok' => 'Gunakan barang masuk atau keluar.']);
        }
        if ($resource->key === 'users') {
            if (!in_array($data['is_active'], [0,1], true)) {
                throw new HttpException(422, 'Status tidak valid.', ['is_active' => 'Pilih aktif/nonaktif.']);
            }
            if (!$id || !empty($input['password'])) {
                $data['password'] = password_hash(Validator::password($input['password'] ?? ''), PASSWORD_DEFAULT);
            }
        }
        if ($resource->transaction) {
            return (new InventoryService())->save($resource, $data, $id, $actorId);
        }
        $upload = new UploadService();
        $photo = $resource->key === 'barang' ? $upload->store($_FILES['foto'] ?? null) : null;
        $oldPhoto = null;
        try {
            $result = Database::transaction(function () use ($resource, $data, $id, $actorId, $photo, &$oldPhoto) {
                // Serialize user administration to preserve the last active administrator.
                if ($resource->key === 'users') {
                    Database::query('SELECT id FROM roles ORDER BY id FOR UPDATE')->fetchAll();
                }
                $old = $id ? $resource->find($id, true) : null;
                $oldPhoto = $old['foto'] ?? null;
                if ($resource->key === 'users' && $old) {
                    if ($id === $actorId && ($data['role_id'] !== (int) $old['role_id'] || $data['is_active'] !== 1)) {
                        throw new HttpException(422, 'Anda tidak dapat menonaktifkan atau mengubah role sendiri.');
                    }
                    $admins = Database::query("SELECT u.id FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='Admin' AND u.is_active=1")->fetchAll();
                    $role = Database::query('SELECT name FROM roles WHERE id=?', [$data['role_id']])->fetchColumn();
                    if (count($admins) <= 1 && in_array($id, array_column($admins, 'id')) && ($role !== 'Admin' || !$data['is_active'])) {
                        throw new HttpException(422, 'Minimal satu Admin aktif harus tersedia.');
                    }
                    if ($data['role_id'] !== (int) $old['role_id']) {
                        Audit::record('perubahan_role', 'User #' . $id . ' role ' . $old['role_id'] . ' menjadi ' . $data['role_id'], $actorId);
                    }
                    if (isset($data['password']) || !$data['is_active'] || $data['role_id'] !== (int)$old['role_id']) {
                        Database::query('DELETE FROM api_tokens WHERE user_id=?', [$id]);
                    }
                }
                if ($photo) {
                    $data['foto'] = $photo;
                }
                if ($id) {
                    $resource->update($id, $data);
                } else {
                    $id = $resource->insert($data);
                }
                Audit::record($resource->key . ($old ? '_edit' : '_tambah'), $resource->title . ' #' . $id, $actorId);
                return $id;
            });
        } catch (\Throwable $error) {
            $upload->remove($photo);
            throw $error;
        }
        if ($photo) {
            $upload->remove($oldPhoto);
        }
        return $result;
    }

    public function delete(Resource $resource, int $id, int $actorId): void
    {
        if ($resource->key === 'users') {
            throw new HttpException(403, 'Nonaktifkan akun melalui halaman edit agar riwayat tetap tersedia.');
        }
        if ($resource->transaction) {
            (new InventoryService())->save($resource, [], $id, $actorId, true);
            return;
        }
        $old = Database::transaction(function () use ($resource, $id, $actorId) {
            $old = $resource->find($id, true);
            $resource->delete($id);
            Audit::record($resource->key . '_hapus', $resource->title . ' #' . $id, $actorId);
            return $old;
        });
        (new UploadService())->remove($old['foto'] ?? null);
    }

    public static function databaseError(\PDOException $error): HttpException
    {
        $code = (int) ($error->errorInfo[1] ?? 0);
        return match ($code) {
            1062 => new HttpException(409, 'Kode atau email sudah digunakan. Gunakan nilai yang unik.'),
            1451 => new HttpException(409, 'Data masih digunakan oleh barang atau transaksi. Data tidak dapat dihapus.'),
            1452 => new HttpException(422, 'Referensi barang, gudang, supplier, atau role tidak tersedia.'),
            1213, 1205 => new HttpException(409, 'Data sedang diproses pengguna lain. Silakan ulangi.'),
            default => new HttpException(500, 'Data belum dapat diproses. Hubungi administrator.'),
        };
    }
}
