<?php

declare(strict_types=1);

function env(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(\App\Core\Csrf::token()) . '">';
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = compact('message', 'type');
}
