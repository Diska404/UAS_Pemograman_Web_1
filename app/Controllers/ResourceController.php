<?php

namespace App\Controllers;

use App\Core\{Controller, Csrf, Database, HttpException};
use App\Models\Resource;
use App\Services\{Auth, ResourceService};

final class ResourceController extends Controller
{
    private Resource $resource;

    public function __construct(string $key)
    {
        $this->resource = new Resource($key);
    }

    private function access(bool $write = false): array
    {
        return Auth::requireUser($this->resource->key === 'users' || ($write && !$this->resource->transaction));
    }

    public static function options(): array
    {
        $options = ['status' => [1 => 'Aktif',0 => 'Nonaktif']];
        foreach (['barang' => ['kode_barang','nama_barang'],'gudang' => ['kode_gudang','nama_gudang'],'supplier' => ['kode_supplier','nama_supplier'],'roles' => ['name','name']] as $table => $columns) {
            $rows = Database::query("SELECT id,{$columns[0]} code,{$columns[1]} name FROM $table ORDER BY {$columns[1]}")->fetchAll();
            $options[$table] = [];
            foreach ($rows as $row) {
                $options[$table][$row['id']] = $table === 'roles' ? $row['name'] : $row['code'] . ' · ' . $row['name'];
            }
        }
        return $options;
    }

    public function index(): void
    {
        $user = $this->access();
        if ($this->resource->key === 'barang') {
            $filters = \App\Models\BarangQuery::filters($_GET);
            $this->view('barang/index', [
                'title' => 'Data Barang', 'filters' => $filters,
                'rows' => (new \App\Models\BarangQuery())->list($filters),
                'options' => self::options(),
                'categories' => Database::query('SELECT DISTINCT kategori FROM barang ORDER BY kategori')->fetchAll(\PDO::FETCH_COLUMN),
                'total' => (int) Database::query('SELECT COUNT(*) FROM barang')->fetchColumn(),
                'canWrite' => $user['role'] === 'Admin',
            ]);
            return;
        }
        $this->view('resources/index', ['title' => $this->resource->title, 'resource' => $this->resource,'rows' => $this->resource->all(),'options' => self::options(),'canWrite' => $user['role'] === 'Admin' || $this->resource->transaction]);
    }

    public function form(?int $id = null, array $old = [], array $errors = [], string $message = '', int $status = 200): void
    {
        $this->access(true);
        $row = $id ? $this->resource->find($id) : [];
        $this->view('resources/form', ['title' => ($id ? 'Edit ' : 'Tambah ') . $this->resource->title,'resource' => $this->resource,'row' => array_replace($row, $old),'id' => $id,'errors' => $errors,'message' => $message,'options' => self::options()], $status);
    }

    public function show(int $id): void
    {
        $user = $this->access();
        $this->view('resources/show', ['title' => 'Detail ' . $this->resource->title,'resource' => $this->resource,'row' => $this->resource->find($id),'options' => self::options(),'canWrite' => $user['role'] === 'Admin' || $this->resource->transaction]);
    }

    public function save(?int $id = null): void
    {
        $user = $this->access(true);
        Csrf::verify();
        try {
            (new ResourceService())->save($this->resource, $_POST, $id, (int)$user['id']);
            flash('Data berhasil disimpan.' . ($this->resource->transaction ? ' Stok telah diperbarui.' : ''));
            redirect('/' . $this->resource->key);
        } catch (\PDOException $error) {
            $error = ResourceService::databaseError($error);
            $this->form($id, $_POST, $error->errors, $error->getMessage(), $error->status);
        } catch (HttpException $error) {
            $this->form($id, $_POST, $error->errors, $error->getMessage(), $error->status);
        }
    }

    public function delete(int $id): void
    {
        $user = $this->access(true);
        Csrf::verify();
        try {
            (new ResourceService())->delete($this->resource, $id, (int)$user['id']);
            flash('Data berhasil dihapus. Saldo stok telah disesuaikan bila relevan.');
        } catch (\PDOException $error) {
            flash(ResourceService::databaseError($error)->getMessage(), 'error');
        } catch (HttpException $error) {
            flash($error->getMessage(), 'error');
        }
        redirect('/' . $this->resource->key);
    }
}
