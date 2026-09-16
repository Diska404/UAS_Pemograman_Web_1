<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath(__DIR__ . '/public' . rawurldecode($path));
$public = realpath(__DIR__ . '/public') . DIRECTORY_SEPARATOR;
if ($file && str_starts_with($file, $public) && is_file($file) && preg_match('/\.(css|js|png|jpg|jpeg|webp|svg|woff2?)$/i', $file)) {
    return false;
}
require __DIR__ . '/public/index.php';
