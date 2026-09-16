<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $config = require BASE_PATH . '/config/database.php';
            self::$connection = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                 PDO::ATTR_EMULATE_PREPARES => false]
            );
            self::$connection->exec("SET time_zone = '+07:00'");
        }
        return self::$connection;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    public static function transaction(callable $callback): mixed
    {
        $db = self::connection();
        $db->beginTransaction();
        try {
            $result = $callback();
            $db->commit();
            return $result;
        } catch (\Throwable $error) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $error;
        }
    }
}
