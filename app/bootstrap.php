<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/Helpers/functions.php';
require BASE_PATH . '/app/Helpers/icons.php';
if (is_file(BASE_PATH . '/.env')) {
    foreach (file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        if (getenv(trim($key)) === false) {
            putenv(trim($key) . '=' . trim(trim($value), "\"'"));
        }
    }
}
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $path = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($path)) {
            require $path;
        }
    }
});
date_default_timezone_set('Asia/Jakarta');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php.log');
if (PHP_SAPI !== 'cli') {
    ini_set('session.use_strict_mode', '1');
    session_name('sim_inventory');
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax', 'path' => '/']);
    session_start();
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
    header('Cache-Control: no-store');
}
