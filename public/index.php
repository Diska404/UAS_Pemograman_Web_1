<?php

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\{Controller, Router, HttpException};
use App\Controllers\{AuthController, DashboardController, ResourceController, ProfileController, ApiController, ReportController};
use App\Services\{Auth, ResourceService};

try {
    $router = new Router();
    $auth = new AuthController();
    $dashboard = new DashboardController();
    $profile = new ProfileController();
    $api = new ApiController();
    $router->add('GET', '/', fn () => redirect(Auth::user() ? '/dashboard' : '/login'));
    foreach (['login','register'] as $mode) {
        $router->add('GET', '/'.$mode, fn () => $auth->form($mode));
        $router->add('POST', '/'.$mode, fn () => $auth->submit($mode));
    }
    $router->add('POST', '/logout', fn () => $auth->logout());
    $router->add('GET', '/dashboard', fn () => $dashboard->index());
    $router->add('GET', '/audit-logs', fn () => $dashboard->audit());
    foreach (array_keys(require BASE_PATH.'/config/resources.php') as $key) {
        $controller = new ResourceController($key);
        $router->add('GET', '/'.$key, fn () => $controller->index());
        $router->add('GET', '/'.$key.'/create', fn () => $controller->form());
        $router->add('POST', '/'.$key, fn () => $controller->save());
        $router->add('GET', '/'.$key.'/{id}', fn ($id) => $controller->show((int)$id));
        $router->add('GET', '/'.$key.'/{id}/edit', fn ($id) => $controller->form((int)$id));
        $router->add('POST', '/'.$key.'/{id}', fn ($id) => $controller->save((int)$id));
        $router->add('POST', '/'.$key.'/{id}/delete', fn ($id) => $controller->delete((int)$id));
    }
    $router->add('GET', '/profile', fn () => $profile->index());
    $router->add('POST', '/profile', fn () => $profile->save());
    $router->add('POST', '/profile/password', fn () => $profile->save(true));
    $router->add('GET', '/reports', fn () => (new ReportController())->index());
    $router->add('GET', '/media/barang/{id}', function ($id) {
        Auth::requireUser();
        $row = (new \App\Models\Resource('barang'))->find((int)$id);
        $name = $row['foto'] ?? '';
        if (!preg_match('/^[a-f0-9]{40}\.png$/D', $name) || !is_file(BASE_PATH.'/storage/uploads/'.$name)) {
            throw new HttpException(404, 'Foto tidak ditemukan.');
        }
        header('Content-Type: image/png');
        readfile(BASE_PATH.'/storage/uploads/'.$name);
    });
    require BASE_PATH.'/api/v1/routes.php';
    $router->dispatch();
} catch (Throwable $error) {
    if ($error instanceof PDOException) {
        error_log((string)$error);
        $error = ResourceService::databaseError($error);
    }
    $status = $error instanceof HttpException ? $error->status : 500;
    $message = $error instanceof HttpException ? $error->getMessage() : 'Terjadi kendala pada aplikasi. Hubungi administrator.';
    if ($status === 500) {
        error_log((string)$error);
    }
    if (str_starts_with(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/api/')) {
        Controller::json(null, $message, $status, $error instanceof HttpException ? $error->errors : []);
    } else {
        http_response_code($status);
        require BASE_PATH.'/views/error.php';
    }
}
