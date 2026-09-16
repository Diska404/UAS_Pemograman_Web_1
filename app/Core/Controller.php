<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        extract($data, EXTR_SKIP);
        ob_start();
        require BASE_PATH . '/views/' . $template . '.php';
        $content = ob_get_clean();
        require BASE_PATH . '/views/layouts/main.php';
    }

    public static function json(mixed $data = null, string $message = 'Data berhasil diambil', int $status = 200, array $errors = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $status < 400, 'message' => $message, 'data' => $data, 'errors' => (object) $errors], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
