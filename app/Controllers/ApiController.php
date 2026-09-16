<?php

namespace App\Controllers;

use App\Core\{Controller, Csrf, Database, HttpException};
use App\Helpers\Validator;
use App\Models\Resource;
use App\Services\{Auth, Audit, DashboardService, ResourceService};

final class ApiController extends Controller
{
    private function payload(): array
    {
        if (!str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            throw new HttpException(415, 'Gunakan Content-Type: application/json.');
        }
        $raw = file_get_contents('php://input');
        if (strlen($raw) > 65536) {
            throw new HttpException(413, 'Payload terlalu besar.');
        }
        try {
            $data = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new HttpException(400, 'JSON tidak valid.');
        }
        if (!is_array($data) || !str_starts_with(ltrim($raw), '{')) {
            throw new HttpException(400, 'Payload harus berupa objek JSON.');
        }
        return $data;
    }

    public function csrf(): void
    {
        self::json(['csrf_token' => Csrf::token()]);
    }

    public function token(): void
    {
        Csrf::verify();
        $input = $this->payload();
        $data = Validator::validate($input, ['email' => ['type' => 'email','max' => 150]]);
        $user = Auth::credentials($data['email'], is_string($input['password'] ?? null) ? $input['password'] : '');
        $token = bin2hex(random_bytes(32));
        Database::query('INSERT INTO api_tokens(user_id,token_hash,expires_at) VALUES (?,?,DATE_ADD(NOW(),INTERVAL 8 HOUR))', [$user['id'],hash('sha256', $token)]);
        Audit::record('api_token', 'Token API diterbitkan', (int)$user['id']);
        self::json(['token' => $token,'token_type' => 'Bearer','expires_in' => 28800], 'Token diterbitkan', 201);
    }

    public function revoke(): void
    {
        $user = Auth::requireUser(false, true, true);
        Csrf::verify();
        Database::query('DELETE FROM api_tokens WHERE token_hash=?', [hash('sha256', substr($_SERVER['HTTP_AUTHORIZATION'], 7))]);
        Audit::record('api_revoke', 'Token API dicabut', (int)$user['id']);
        self::json(null, 'Token dicabut');
    }

    public function items(?int $id = null): void
    {
        Auth::requireUser(false, true);
        $model = new Resource('barang');
        if ($id) {
            self::json($model->find($id));
            return;
        }
        $filters = \App\Models\BarangQuery::filters($_GET);
        self::json((new \App\Models\BarangQuery())->list($filters));
    }

    public function save(?int $id = null): void
    {
        $user = Auth::requireUser(true, true, true);
        Csrf::verify();
        $model = new Resource('barang');
        $saved = (new ResourceService())->save($model, $this->payload(), $id, (int)$user['id']);
        self::json($model->find($saved), 'Barang berhasil disimpan', $id ? 200 : 201);
    }

    public function delete(int $id): void
    {
        $user = Auth::requireUser(true, true, true);
        Csrf::verify();
        (new ResourceService())->delete(new Resource('barang'), $id, (int)$user['id']);
        self::json(null, 'Barang berhasil dihapus');
    }

    public function dashboard(): void
    {
        Auth::requireUser(false, true);
        self::json((new DashboardService())->data($_GET['range'] ?? '6'));
    }
}
