<?php

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Services\ReportService;

if (PHP_SAPI !== 'cli' || env('APP_ENV') !== 'local') {
    exit("Tests hanya boleh berjalan pada APP_ENV=local.\n");
}
$base = env('TEST_URL', 'http://localhost:8000');
if (!in_array(parse_url($base, PHP_URL_HOST), ['localhost','127.0.0.1'], true)) {
    exit("Tests harus menarget localhost.\n");
}
$results = [];
$prefix = 'QA'.bin2hex(random_bytes(4));
$created = ['barang_masuk' => [],'barang_keluar' => [],'barang' => [],'supplier' => [],'gudang' => [],'users' => []];

function check(string $feature, string $scenario, bool $condition, string $actual): void
{
    global $results;
    $results[] = [$feature,$scenario,$condition ? 'PASS' : 'FAIL',$actual];
    echo ($condition ? 'PASS' : 'FAIL')." | $feature | $scenario | $actual\n";
}

final class Client
{
    public string $csrf = '';
    public string $token = '';
    private string $cookies;
    public function __construct()
    {
        $this->cookies = tempnam(sys_get_temp_dir(), 'sim_');
    }
    public function __destruct()
    {
        if (is_file($this->cookies)) {
            unlink($this->cookies);
        }
    }
    public function request(string $method, string $path, ?array $data = null, bool $json = false, bool $csrf = true, bool $auth = true): array
    {
        global $base;
        $ch = curl_init($base.$path);
        $headers = [];
        if ($csrf && $this->csrf) {
            $headers[] = 'X-CSRF-Token: '.$this->csrf;
        }
        if ($auth && $this->token) {
            $headers[] = 'Authorization: Bearer '.$this->token;
        }
        if ($json) {
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true,CURLOPT_CUSTOMREQUEST => $method,CURLOPT_COOKIEJAR => $this->cookies,CURLOPT_COOKIEFILE => $this->cookies,CURLOPT_HTTPHEADER => $headers,CURLOPT_TIMEOUT => 30]);
        if ($data !== null) {
            $multipart = count(array_filter($data, fn ($v) => $v instanceof CURLFile)) > 0;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json ? json_encode($data) : ($multipart ? $data : http_build_query($data)));
        }
        $body = curl_exec($ch);
        if ($body === false) {
            throw new RuntimeException(curl_error($ch));
        }
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        unset($ch);
        return ['status' => $status,'body' => $body,'json' => json_decode($body, true),'type' => $type];
    }
    public function refresh(): void
    {
        $this->csrf = $this->request('GET', '/api/v1/csrf')['json']['data']['csrf_token'];
    }
    public function login(string $email, string $password): array
    {
        $this->refresh();
        $response = $this->request('POST', '/login', compact('email', 'password'));
        $this->refresh();
        return $response;
    }
    public function issue(string $email, string $password): array
    {
        $this->refresh();
        $response = $this->request('POST', '/api/v1/token', compact('email', 'password'), true);
        $this->token = $response['json']['data']['token'] ?? '';
        return $response;
    }
}

function idFor(string $table, string $column, string $value): int
{
    global $created;
    $id = (int)Database::query("SELECT id FROM $table WHERE $column=?", [$value])->fetchColumn();
    if ($id) {
        $created[$table][] = $id;
    }
    return $id;
}
function stock(int $id): int
{
    return (int)Database::query('SELECT stok FROM barang WHERE id=?', [$id])->fetchColumn();
}
function latestTx(string $table, int $item): int
{
    global $created;
    $id = (int)Database::query("SELECT id FROM $table WHERE barang_id=? ORDER BY id DESC LIMIT 1", [$item])->fetchColumn();
    if ($id) {
        $created[$table][] = $id;
    }
    return $id;
}

try {
    $guest = new Client();
    $admin = new Client();
    $operator = new Client();
    $r = $guest->request('GET', '/api/v1/barang');
    check('Security', 'API tanpa autentikasi', $r['status'] === 401, 'HTTP '.$r['status']);
    $r = $guest->request('GET', '/barang');
    check('Auth', 'Halaman privat mengarah login', $r['status'] === 303, 'HTTP '.$r['status']);
    $r = $guest->request('POST', '/login', ['email' => 'admin@example.com','password' => 'InventoryDemo2026!'], false, false);
    check('CSRF', 'Login tanpa token', $r['status'] === 419, 'HTTP '.$r['status']);
    $r = $guest->login('admin@example.com', 'wrong');
    check('Auth', 'Password salah ditolak', $r['status'] === 422, 'HTTP '.$r['status']);
    $r = $guest->login("' OR 1=1 --", 'wrong');
    check('Security', 'SQL injection login ditolak', $r['status'] === 422, 'HTTP '.$r['status']);
    $r = $admin->login('admin@example.com', 'InventoryDemo2026!');
    check('Auth', 'Login Admin', $r['status'] === 303, 'HTTP '.$r['status']);
    $r = $operator->login('operator@example.com', 'InventoryDemo2026!');
    check('Auth', 'Login Operator', $r['status'] === 303, 'HTTP '.$r['status']);
    foreach (['/dashboard','/barang','/gudang','/supplier','/barang-masuk','/barang-keluar','/users','/profile','/audit-logs','/reports'] as $path) {
        $r = $admin->request('GET', $path);
        check('Pages', $path, $r['status'] === 200, 'HTTP '.$r['status']);
    }
    foreach (['/users','/audit-logs','/barang/create','/reports?format=pdf'] as $path) {
        $r = $operator->request('GET', $path);
        check('Role', 'Operator dibatasi '.$path, $r['status'] === 403, 'HTTP '.$r['status']);
    }
    $r = $operator->request('POST', '/gudang', ['kode_gudang' => 'FORBIDDEN']);
    check('Role', 'POST master Operator ditolak', $r['status'] === 403, 'HTTP '.$r['status']);
    $email = strtolower($prefix).'@example.com';
    $guest->refresh();
    $r = $guest->request('POST', '/register', ['name' => $prefix,'email' => $email,'password' => 'QaPassword2026!','password_confirmation' => 'QaPassword2026!','role_id' => 1]);
    $uid = idFor('users', 'email', $email);
    check('Auth', 'Register publik mengabaikan role Admin', $r['status'] === 303 && (int)Database::query('SELECT role_id FROM users WHERE id=?', [$uid])->fetchColumn() === 2, 'Role Operator');
    $r = $admin->request('POST', '/users/'.$uid, ['name' => $prefix,'email' => $email,'role_id' => 2,'is_active' => 0]);
    $r = $guest->login($email, 'QaPassword2026!');
    check('Auth', 'Akun nonaktif ditolak', $r['status'] === 422, 'HTTP '.$r['status']);
    $admin->request('POST', '/users/'.$uid, ['name' => $prefix,'email' => $email,'role_id' => 2,'is_active' => 1]);
    $guest->login($email, 'QaPassword2026!');
    $r = $guest->request('POST', '/profile', ['name' => $prefix.' Updated','email' => $email]);
    check('Profile', 'Edit profil', $r['status'] === 303, 'HTTP '.$r['status']);
    $r = $guest->request('POST', '/profile/password', ['current_password' => 'wrong','password' => 'QaPasswordNew2026!','password_confirmation' => 'QaPasswordNew2026!']);
    check('Profile', 'Password lama wajib cocok', $r['status'] === 422, 'HTTP '.$r['status']);
    $r = $guest->request('POST', '/profile/password', ['current_password' => 'QaPassword2026!','password' => 'QaPasswordNew2026!','password_confirmation' => 'QaPasswordNew2026!']);
    check('Profile', 'Ganti password', $r['status'] === 303, 'HTTP '.$r['status']);
    $guest->refresh();
    $r = $guest->request('POST', '/logout');
    check('Auth', 'Logout', $r['status'] === 303 && $guest->request('GET', '/profile')['status'] === 303, 'Session tidak lagi mengakses profil');
    $r = $guest->login($email, 'QaPasswordNew2026!');
    check('Profile', 'Login dengan password baru', $r['status'] === 303, 'HTTP '.$r['status']);
    $admin->request('POST', '/users/'.$uid, ['name' => $prefix,'email' => $email,'role_id' => 2,'is_active' => 0]);
    $r = $guest->request('GET', '/profile');
    check('Auth', 'Akun dinonaktifkan memutus akses sesi aktif', $r['status'] === 303, 'Akses sesi ditolak');
    $admin->request('POST', '/users/'.$uid, ['name' => $prefix,'email' => $email,'role_id' => 2,'is_active' => 1]);
    $r = $admin->request('POST', '/users/1', ['name' => 'Admin Inventory','email' => 'admin@example.com','role_id' => 2,'is_active' => 1]);
    check('Role', 'Admin tidak dapat menurunkan role sendiri', $r['status'] === 422, 'HTTP '.$r['status']);
    $r = $admin->request('POST', '/users/'.$uid, ['name' => $prefix,'email' => $email,'role_id' => 1,'is_active' => 1]);
    check('Users', 'Admin mengubah role user', $r['status'] === 303 && (int)Database::query('SELECT role_id FROM users WHERE id=?', [$uid])->fetchColumn() === 1, 'Role berubah menjadi Admin');
    $admin->request('POST', '/users/'.$uid, ['name' => $prefix,'email' => $email,'role_id' => 2,'is_active' => 1]);
    $r = $admin->request('POST', '/gudang', ['kode_gudang' => $prefix,'nama_gudang' => 'Gudang QA','lokasi' => 'Jakarta','keterangan' => 'Testing']);
    $warehouse = idFor('gudang', 'kode_gudang', $prefix);
    check('CRUD', 'Tambah gudang', $r['status'] === 303 && $warehouse > 0, 'ID tersimpan');
    $r = $admin->request('POST', '/gudang/'.$warehouse, ['kode_gudang' => $prefix,'nama_gudang' => 'Gudang QA Edit','lokasi' => 'Bandung']);
    check('CRUD', 'Edit gudang', $r['status'] === 303, 'HTTP '.$r['status']);
    $supplierData = ['kode_supplier' => $prefix,'nama_supplier' => 'Supplier QA','alamat' => 'Jakarta','telepon' => '0215551234','email' => 'qa@example.com'];
    $r = $admin->request('POST', '/supplier', $supplierData);
    $supplier = idFor('supplier', 'kode_supplier', $prefix);
    check('CRUD', 'Tambah supplier', $r['status'] === 303 && $supplier > 0, 'ID tersimpan');
    $r = $admin->request('POST', '/supplier/'.$supplier, array_replace($supplierData, ['nama_supplier' => 'Supplier Edit']));
    check('CRUD', 'Edit supplier', $r['status'] === 303, 'HTTP '.$r['status']);
    $r = $admin->issue('admin@example.com', 'InventoryDemo2026!');
    check('API', 'Token Admin diterbitkan', $r['status'] === 201 && strlen($admin->token) === 64, 'HTTP '.$r['status']);
    check('Security', 'Token database hanya hash', Database::query('SELECT COUNT(*) FROM api_tokens WHERE token_hash=?', [hash('sha256', $admin->token)])->fetchColumn() == 1, 'SHA-256 64 karakter');
    $itemData = ['kode_barang' => $prefix,'nama_barang' => '<script>alert(1)</script>','kategori' => 'QA','satuan' => 'unit','stok_minimum' => 5,'gudang_id' => $warehouse,'deskripsi' => '=HYPERLINK("https://example.com")'];
    $r = $admin->request('POST', '/api/v1/barang', $itemData, true);
    $item = idFor('barang', 'kode_barang', $prefix);
    check('API', 'POST barang', $r['status'] === 201 && $item > 0, 'HTTP '.$r['status']);
    $r = $admin->request('GET', '/barang/'.$item);
    check('Security', 'XSS di-escape', str_contains($r['body'], '&lt;script&gt;') && !str_contains($r['body'], '<script>alert(1)</script>'), 'HTML escaped');
    $r = $admin->request('POST', '/api/v1/barang', $itemData, true);
    check('Validation', 'Kode barang unik', $r['status'] === 409, 'HTTP '.$r['status']);
    $r = $admin->request('PUT', '/api/v1/barang/'.$item, array_replace($itemData, ['nama_barang' => 'Barang QA']), true);
    check('API', 'PUT barang', $r['status'] === 200, 'HTTP '.$r['status']);
    $r = $admin->request('POST', '/api/v1/barang', [], true);
    check('API', 'Payload invalid', $r['status'] === 400 || $r['status'] === 422, 'HTTP '.$r['status']);
    $r = $admin->request('PUT', '/api/v1/barang/'.$item, array_replace($itemData, ['stok' => 50]), true);
    check('Inventory', 'Stok langsung ditolak', $r['status'] === 422 && stock($item) === 0, 'Stok tetap 0');
    $r = $admin->request('PUT', '/api/v1/barang/'.$item, $itemData, true, false);
    check('CSRF', 'API mutasi tanpa CSRF', $r['status'] === 419, 'HTTP '.$r['status']);
    $operator->issue('operator@example.com', 'InventoryDemo2026!');
    $r = $operator->request('POST', '/api/v1/barang', $itemData, true);
    check('Role', 'Token Operator tidak dapat POST barang', $r['status'] === 403, 'HTTP '.$r['status']);
    $bad = new Client();
    $bad->token = 'invalid';
    $r = $bad->request('GET', '/api/v1/barang');
    check('API', 'Token tidak sah', $r['status'] === 401, 'HTTP '.$r['status']);
    $r = $admin->request('GET', '/api/v1/barang/99999999');
    check('API', 'Data tidak ditemukan', $r['status'] === 404, 'HTTP '.$r['status']);
    $r = $admin->request('GET', '/api/v1/barang?q='.$prefix);
    check('API', 'Pencarian API', $r['status'] === 200 && count($r['json']['data']) === 1, 'Satu barang ditemukan');
    $incoming = ['tanggal' => date('Y-m-d'),'barang_id' => $item,'supplier_id' => $supplier,'jumlah' => 10];
    $r = $operator->request('POST', '/barang-masuk', $incoming);
    $inId = latestTx('barang_masuk', $item);
    check('Inventory', 'Masuk menambah stok', $r['status'] === 303 && stock($item) === 10, 'Stok '.stock($item));
    $r = $operator->request('POST', '/barang-masuk', array_replace($incoming, ['supplier_id' => 99999999]));
    check('Inventory', 'FK invalid membatalkan perubahan stok', $r['status'] === 422 && stock($item) === 10, 'Supplier invalid; stok tetap 10');
    $r = $operator->request('POST', '/barang-masuk', array_replace($incoming, ['jumlah' => 0]));
    check('Validation', 'Jumlah nol ditolak', $r['status'] === 422 && stock($item) === 10, 'HTTP '.$r['status']);
    $r = $operator->request('POST', '/barang-masuk', array_replace($incoming, ['jumlah' => 1.5]));
    check('Validation', 'Jumlah pecahan ditolak', $r['status'] === 422 && stock($item) === 10, 'HTTP '.$r['status']);
    $r = $admin->request('DELETE', '/api/v1/barang/'.$item, null, true);
    check('Integrity', 'Barang dengan transaksi tidak dapat dihapus', $r['status'] === 409, 'HTTP '.$r['status']);
    $outgoing = ['tanggal' => date('Y-m-d'),'barang_id' => $item,'jumlah' => 4,'tujuan' => 'QA'];
    $r = $operator->request('POST', '/barang-keluar', $outgoing);
    $outId = latestTx('barang_keluar', $item);
    check('Inventory', 'Keluar mengurangi stok', $r['status'] === 303 && stock($item) === 6, 'Stok '.stock($item));
    $r = $operator->request('POST', '/barang-keluar', array_replace($outgoing, ['jumlah' => 7]));
    check('Inventory', 'Stok negatif dicegah', $r['status'] === 422 && stock($item) === 6, 'HTTP '.$r['status'].'; stok '.stock($item));
    $r = $operator->request('POST', '/barang-masuk/'.$inId, array_replace($incoming, ['jumlah' => 3]));
    check('Inventory', 'Edit masuk tidak boleh membuat stok negatif', $r['status'] === 422 && stock($item) === 6, 'Saldo tetap 6');
    $operator->request('POST', '/barang-masuk/'.$inId.'/delete');
    check('Inventory', 'Hapus masuk terpakai dibatalkan', stock($item) === 6 && Database::query('SELECT COUNT(*) FROM barang_masuk WHERE id=?', [$inId])->fetchColumn() == 1, 'Transaksi dan stok tetap');
    $r = $operator->request('POST', '/barang-masuk/'.$inId, array_replace($incoming, ['jumlah' => 12]));
    check('Inventory', 'Edit masuk mengoreksi stok', $r['status'] === 303 && stock($item) === 8, 'Stok '.stock($item));
    $r = $operator->request('POST', '/barang-keluar/'.$outId, array_replace($outgoing, ['jumlah' => 5]));
    check('Inventory', 'Edit keluar mengoreksi stok', $r['status'] === 303 && stock($item) === 7, 'Stok '.stock($item));
    $r = $admin->request('GET', '/api/v1/dashboard');
    check('Dashboard', 'JSON grafik 6 bulan', $r['status'] === 200 && count($r['json']['data']['months']) === 6, '6 bulan dan kategori');
    $r = $admin->request('GET', '/reports?type=masuk&start='.date('Y-m-d').'&end='.date('Y-m-d').'&barang_id='.$item.'&supplier_id='.$supplier);
    check('Report', 'Filter transaksi', $r['status'] === 200 && str_contains($r['body'], '1 data'), 'Satu transaksi');
    foreach (['pdf' => '%PDF','excel' => 'PK'] as $format => $magic) {
        $r = $admin->request('GET', '/reports?type=stok&format='.$format.'&barang_id='.$item);
        check('Report', 'Export '.$format, $r['status'] === 200 && str_starts_with($r['body'], $magic), 'HTTP '.$r['status'].'; '.strlen($r['body']).' bytes');
        if ($r['status'] === 200) {
            file_put_contents(BASE_PATH.'/storage/exports/test-report.'.($format === 'excel' ? 'xlsx' : 'pdf'), $r['body']);
        }
    }
    $xlsx = \PhpOffice\PhpSpreadsheet\IOFactory::load(BASE_PATH.'/storage/exports/test-report.xlsx');
    check('Report', 'Excel dapat dibaca ulang', $xlsx->getActiveSheet()->getCell('A1')->getValue() === 'Laporan Stok Barang', 'Judul workbook cocok');
    $r = $admin->request('GET', '/reports?start=2026-02-30');
    check('Report', 'Filter tanggal invalid', $r['status'] === 422, 'HTTP '.$r['status']);
    $file = BASE_PATH.'/storage/exports/test-photo.png';
    $img = imagecreatetruecolor(30, 30);
    imagepng($img, $file);
    $r = $admin->request('POST', '/barang/'.$item, array_replace($itemData, ['foto' => new CURLFile($file, 'image/png', 'photo.png')]));
    $photo = Database::query('SELECT foto FROM barang WHERE id=?', [$item])->fetchColumn();
    check('Upload', 'Foto PNG valid', $r['status'] === 303 && preg_match('/^[a-f0-9]{40}\.png$/', $photo ?? '') === 1, 'Nama acak; foto tersimpan');
    $r = $admin->request('GET', '/media/barang/'.$item);
    check('Upload', 'Foto dapat diakses pengguna', $r['status'] === 200 && str_contains($r['type'], 'image/png'), 'HTTP '.$r['status']);
    $badfile = BASE_PATH.'/storage/exports/test-bad.php';
    file_put_contents($badfile, '<?php echo 1;');
    $r = $admin->request('POST', '/barang/'.$item, array_replace($itemData, ['foto' => new CURLFile($badfile, 'image/png', 'fake.png')]));
    check('Upload', 'MIME palsu ditolak', $r['status'] === 422, 'HTTP '.$r['status']);
    $r = $operator->request('POST', '/barang-keluar/'.$outId.'/delete');
    check('Inventory', 'Hapus keluar mengembalikan stok', $r['status'] === 303 && stock($item) === 12, 'Stok '.stock($item));
    $r = $operator->request('POST', '/barang-masuk/'.$inId.'/delete');
    check('Inventory', 'Hapus masuk mengurangi stok', $r['status'] === 303 && stock($item) === 0, 'Stok '.stock($item));
    $r = $admin->request('DELETE', '/api/v1/barang/'.$item, null, true);
    check('API', 'DELETE barang', $r['status'] === 200, 'HTTP '.$r['status']);
    $r = $admin->request('POST', '/supplier/'.$supplier.'/delete');
    check('CRUD', 'Hapus supplier', $r['status'] === 303 && !Database::query('SELECT id FROM supplier WHERE id=?', [$supplier])->fetch(), 'Data terhapus');
    $r = $admin->request('POST', '/gudang/'.$warehouse.'/delete');
    check('CRUD', 'Hapus gudang', $r['status'] === 303 && !Database::query('SELECT id FROM gudang WHERE id=?', [$warehouse])->fetch(), 'Data terhapus');
    $r = $admin->request('DELETE', '/api/v1/token', null, true);
    $r = $admin->request('GET', '/api/v1/barang');
    check('API', 'Revoke token', $r['status'] === 401, 'HTTP '.$r['status']);
    $mismatch = Database::query('SELECT COUNT(*) FROM barang b WHERE stok <> COALESCE((SELECT SUM(jumlah) FROM barang_masuk m WHERE m.barang_id=b.id),0)-COALESCE((SELECT SUM(jumlah) FROM barang_keluar k WHERE k.barang_id=b.id),0)')->fetchColumn();
    check('Inventory', 'Rekonsiliasi semua stok', (int)$mismatch === 0, $mismatch.' selisih');
    check('Security', 'Password tersimpan hash', password_get_info(Database::query('SELECT password FROM users WHERE id=?', [$uid])->fetchColumn())['algo'] !== null, 'Hash diverifikasi');
    check('Audit', 'Aktivitas penting tercatat', Database::query("SELECT COUNT(DISTINCT activity) FROM audit_logs WHERE activity IN ('login_berhasil','login_gagal','logout','perubahan_password','barang-masuk_tambah','barang-keluar_tambah')")->fetchColumn() == 6, '6 kategori audit ditemukan');
} catch (Throwable $error) {
    check('Runner','Eksekusi suite',false,$error->getMessage());
} finally {
    // Cleanup only IDs created by this invocation; original demo records are preserved.
    foreach ($created as $table => $ids) {
        foreach (array_unique($ids) as $id) {
            if ($table === 'barang') {
                $photo = Database::query('SELECT foto FROM barang WHERE id=?',[$id])->fetchColumn();
                (new \App\Services\UploadService())->remove($photo ?: null);
            }
            Database::query("DELETE FROM $table WHERE id=?",[$id]);
        }
    }
}
$failed = count(array_filter($results,fn ($r) => $r[2] === 'FAIL'));
$payload = ['date' => date(DATE_ATOM),'total' => count($results),'failed' => $failed,'results' => $results];
file_put_contents(BASE_PATH.'/tests/results.json',json_encode($payload,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$markdown = "# Black Box Testing\n\nPengujian nyata melalui HTTP pada ".date('d F Y H:i')." WIB. PHP ".PHP_VERSION."; database ".Database::query('SELECT VERSION()')->fetchColumn().".\n\nJalankan `php tests/run.php` setelah server lokal aktif. Suite membuat data QA dan membersihkan hanya ID yang dibuatnya. Akun demo harus masih memakai password development.\n\n**Hasil: ".(count($results) - $failed)."/".count($results)." PASS; $failed FAIL.**\n\n| No | Fitur | Skenario | Input | Expected Result | Actual Result | Status |\n|---|---|---|---|---|---|---|\n";
foreach ($results as $i => $r) {
    $markdown .= '| '.($i + 1).' | '.$r[0].' | '.str_replace('|','/',$r[1]).' | Request dan data QA sesuai skenario; lihat tests/run.php | Perilaku sesuai skenario dan assertion suite | '.str_replace(["\n",'|'],[' ','/'],$r[3]).' | '.$r[2]." |\n";
}
$markdown .= "\n## Pemeriksaan browser\n\nLihat [UI_TESTING.md](UI_TESTING.md) untuk pemeriksaan Chart.js, DataTables, SweetAlert2, desktop, tablet, dan mobile.\n\n## Batas pengujian\n\nPengujian lokal bukan uji beban atau pentest eksternal. Ulangi smoke test setelah deployment; tanggal dan hasil suite akan berubah saat dijalankan ulang.\n";
file_put_contents(BASE_PATH.'/docs/BLACK_BOX_TESTING.md',$markdown);
echo "\n".count($results)." assertions; $failed failures\n";
exit($failed ? 1 : 0);
