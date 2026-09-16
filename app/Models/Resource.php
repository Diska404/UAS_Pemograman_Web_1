<?php

namespace App\Models;

use App\Core\{Database, HttpException};

/** Schema metadata is a trusted allowlist, never a table name from request input. */
final class Resource
{
    public readonly string $table;
    public readonly string $title;
    public readonly array $fields;
    public readonly bool $transaction;

    public function __construct(public readonly string $key)
    {
        $catalog = require BASE_PATH . '/config/resources.php';
        if (!isset($catalog[$key])) {
            throw new HttpException(404, 'Modul tidak ditemukan.');
        }
        $meta = $catalog[$key];
        $this->table = $meta['table'];
        $this->title = $meta['title'];
        $this->fields = $meta['fields'];
        $this->transaction = in_array($key, ['barang-masuk', 'barang-keluar'], true);
    }

    public function all(): array
    {
        $columns = $this->key === 'users' ? 'id,name,email,role_id,is_active,created_at,updated_at' : '*';
        return Database::query("SELECT $columns FROM {$this->table} ORDER BY id DESC")->fetchAll();
    }

    public function find(int $id, bool $lock = false): array
    {
        $columns = $this->key === 'users' ? 'id,name,email,role_id,is_active,created_at,updated_at' : '*';
        $row = Database::query("SELECT $columns FROM {$this->table} WHERE id=?" . ($lock ? ' FOR UPDATE' : ''), [$id])->fetch();
        if (!$row) {
            throw new HttpException(404, 'Data tidak ditemukan.');
        }
        return $row;
    }

    public function insert(array $data): int
    {
        $columns = implode(',', array_keys($data));
        $holders = implode(',', array_fill(0, count($data), '?'));
        Database::query("INSERT INTO {$this->table} ($columns) VALUES ($holders)", array_values($data));
        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $set = implode(',', array_map(static fn ($key) => "$key=?", array_keys($data)));
        Database::query("UPDATE {$this->table} SET $set WHERE id=?", [...array_values($data), $id]);
    }

    public function delete(int $id): void
    {
        Database::query("DELETE FROM {$this->table} WHERE id=?", [$id]);
    }
}
