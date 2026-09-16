<?php

namespace App\Controllers;

use App\Core\{Controller, Csrf, Database, HttpException};
use App\Helpers\Validator;
use App\Services\{Auth, Audit, ResourceService};

final class ProfileController extends Controller
{
    public function index(array $errors = [], string $message = '', int $status = 200): void
    {
        $this->view('profile/index', ['title' => 'Profil & Keamanan','profile' => Auth::requireUser(),'errors' => $errors,'message' => $message], $status);
    }

    public function save(bool $password = false): void
    {
        $user = Auth::requireUser();
        Csrf::verify();
        try {
            Database::transaction(function () use ($user, $password) {
                $current = Database::query('SELECT * FROM users WHERE id=? FOR UPDATE', [$user['id']])->fetch();
                if ($password) {
                    if (!is_string($_POST['current_password'] ?? null) || !password_verify($_POST['current_password'], $current['password'])) {
                        throw new HttpException(422, 'Password saat ini tidak cocok.', ['current_password' => 'Password salah.']);
                    }
                    $new = Validator::password($_POST['password'] ?? '', $_POST['password_confirmation'] ?? '');
                    Database::query('UPDATE users SET password=? WHERE id=?', [password_hash($new, PASSWORD_DEFAULT),$user['id']]);
                    Database::query('DELETE FROM api_tokens WHERE user_id=?', [$user['id']]);
                    Audit::record('perubahan_password', 'Password diperbarui; token dicabut', (int)$user['id']);
                } else {
                    $data = Validator::validate($_POST, ['name' => ['max' => 100],'email' => ['type' => 'email','max' => 150]]);
                    Database::query('UPDATE users SET name=?,email=? WHERE id=?', [$data['name'],$data['email'],$user['id']]);
                    Audit::record('profil_edit', 'Profil diperbarui', (int)$user['id']);
                }
            });
            if ($password) {
                session_regenerate_id(true);
                $_SESSION['_csrf'] = bin2hex(random_bytes(32));
            }
            flash($password ? 'Password berhasil diubah. Token API lama telah dicabut.' : 'Profil berhasil diperbarui.');
            redirect('/profile');
        } catch (\PDOException $error) {
            $error = ResourceService::databaseError($error);
            $this->index($error->errors, $error->getMessage(), $error->status);
        } catch (HttpException $error) {
            $this->index($error->errors, $error->getMessage(), $error->status);
        }
    }
}
