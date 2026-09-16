<?php

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [$method, '#^' . preg_replace('/\{id\}/', '([1-9][0-9]*)', $path) . '/?$#', $handler];
    }

    public function dispatch(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $matched = false;
        foreach ($this->routes as [$method, $pattern, $handler]) {
            if (preg_match($pattern, $path, $matches)) {
                $matched = true;
                if ($method === $_SERVER['REQUEST_METHOD']) {
                    array_shift($matches);
                    $handler(...$matches);
                    return;
                }
            }
        }
        throw new HttpException($matched ? 405 : 404, $matched ? 'Metode HTTP tidak diizinkan.' : 'Halaman atau data tidak ditemukan.');
    }
}
