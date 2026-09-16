<?php

namespace App\Controllers;

use App\Core\{Controller, Csrf, Database, HttpException};
use App\Helpers\Validator;
use App\Services\{Auth, Audit, ResourceService};

final class AuthController extends Controller
{
    public function form(string $mode, array $errors = [], array $old = [], string $message = '', int $status = 200): void
    {
        $this->view('auth/form', compact('mode', 'errors', 'old', 'message') + ['title' => $mode === 'login' ? 'Selamat datang kembali' : 'Buat akun Operator'], $status);
    }

    public function submit(string $mode): void
    {
        Csrf::verify();
        try {
            $data = Validator::validate($_POST, ['email' => ['type' => 'email','max' => 150]] + ($mode === 'register' ? ['name' => ['max' => 100]] : []));
            if ($mode === 'register') {
                if (env('REGISTER_ENABLED', 'true') !== 'true') {
                    throw new HttpException(403, 'Pendaftaran publik dinonaktifkan.');
                }
                $password = Validator::password($_POST['password'] ?? '', $_POST['password_confirmation'] ?? '');
                Database::transaction(function () use ($data, $password) {
                    $role = Database::query("SELECT id FROM roles WHERE name='Operator'")->fetchColumn();
                    Database::query('INSERT INTO users(name,email,password,role_id) VALUES (?,?,?,?)', [$data['name'],$data['email'],password_hash($password, PASSWORD_DEFAULT),$role]);
                    Audit::record('register', 'Registrasi Operator', (int) Database::connection()->lastInsertId());
                });
                flash('Akun Operator berhasil dibuat. Silakan login.');
                redirect('/login');
            }
            $user = Auth::credentials($data['email'], is_string($_POST['password'] ?? null) ? $_POST['password'] : '');
            session_regenerate_id(true);
            $_SESSION = ['user_id' => (int)$user['id'], 'last_activity' => time(), '_csrf' => bin2hex(random_bytes(32))];
            redirect('/dashboard');
        } catch (\PDOException $error) {
            $error = ResourceService::databaseError($error);
            $this->form($mode, $error->errors, $_POST, $error->getMessage(), $error->status);
        } catch (HttpException $error) {
            $this->form($mode, $error->errors, $_POST, $error->getMessage(), $error->status);
        }
    }

    public function logout(): void
    {
        Csrf::verify();
        if ($user = Auth::user()) {
            Audit::record('logout', 'Keluar dari aplikasi', (int)$user['id']);
        }
        $_SESSION = [];
        setcookie(session_name(), '', ['expires' => time() - 3600,'path' => '/','httponly' => true,'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','samesite' => 'Lax']);
        session_destroy();
        redirect('/login');
    }
}
