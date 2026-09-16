<?php

namespace App\Services;

use App\Core\Database;

final class Audit
{
    public static function record(string $activity, string $description, ?int $userId = null): void
    {
        Database::query(
            'INSERT INTO audit_logs (user_id, activity, description, ip_address, user_agent) VALUES (?,?,?,?,?)',
            [$userId ?? ($_SESSION['user_id'] ?? null), $activity, mb_substr($description, 0, 1000),
             $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI', 0, 255)]
        );
    }
}
