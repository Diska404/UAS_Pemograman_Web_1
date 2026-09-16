<?php

namespace App\Services;

use App\Core\{Database, HttpException};

final class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        if (time() - ($_SESSION['last_activity'] ?? 0) > (int) env('SESSION_TIMEOUT', '1800')) {
            unset($_SESSION['user_id']);
            return null;
        }
        $user = Database::query('SELECT u.id,u.name,u.email,u.is_active,u.role_id,r.name role FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=?', [$_SESSION['user_id']])->fetch();
        if (!$user || !$user['is_active']) {
            unset($_SESSION['user_id']);
            return null;
        }
        $_SESSION['last_activity'] = time();
        return $user;
    }

    public static function credentials(string $email, string $password): array
    {
        $identity = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? 'cli') . '|' . strtolower($email));
        $count = Database::query('SELECT COUNT(*) FROM login_attempts WHERE identity_hash=? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)', [$identity])->fetchColumn();
        if ($count >= 5) {
            throw new HttpException(429, 'Terlalu banyak percobaan. Coba lagi setelah 15 menit.');
        }
        $user = Database::query('SELECT u.*, r.name role FROM users u JOIN roles r ON r.id=u.role_id WHERE email=?', [$email])->fetch();
        if (!$user || !password_verify($password, $user['password']) || !$user['is_active']) {
            Database::query('INSERT INTO login_attempts(identity_hash) VALUES (?)', [$identity]);
            Audit::record('login_gagal', 'Login ditolak: ' . mb_substr($email, 0, 150));
            throw new HttpException(422, 'Email atau password salah, atau akun tidak aktif.', ['email' => 'Periksa kembali akun Anda.']);
        }
        Database::query('DELETE FROM login_attempts WHERE identity_hash=?', [$identity]);
        Audit::record('login_berhasil', 'Autentikasi berhasil', (int) $user['id']);
        return $user;
    }

    public static function requireUser(bool $admin = false, bool $api = false, bool $tokenRequired = false): array
    {
        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $user = null;
        if ($api && str_starts_with($authorization, 'Bearer ')) {
            $token = substr($authorization, 7);
            $user = Database::query('SELECT u.id,u.name,u.email,u.role_id,u.is_active,r.name role FROM api_tokens t JOIN users u ON u.id=t.user_id JOIN roles r ON r.id=u.role_id WHERE t.token_hash=? AND t.expires_at>NOW() AND u.is_active=1', [hash('sha256', $token)])->fetch() ?: null;
        } elseif (!$tokenRequired) {
            $user = self::user();
        }
        if (!$user) {
            if (!$api) {
                redirect('/login');
            }
            throw new HttpException(401, 'Autentikasi diperlukan atau token tidak valid.');
        }
        if ($admin && $user['role'] !== 'Admin') {
            throw new HttpException(403, 'Akses hanya untuk Admin.');
        }
        return $user;
    }
}
