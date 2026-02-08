<?php

require_once __DIR__ . '/Env.php';

final class DB
{
    private static ?PDO $pdo = null;

    private static function config(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }

    private static function pdoServer(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;charset=%s',
            self::config('DB_HOST', '127.0.0.1'),
            self::config('DB_CHARSET', 'utf8mb4')
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        return new PDO(
            $dsn,
            self::config('DB_USER', 'root'),
            self::config('DB_PASS', ''),
            $options
        );
    }

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        // Load env once
        Env::load(dirname(__DIR__, 2) . '/.env');

        $dbName = self::config('DB_NAME');
        if (!$dbName) {
            throw new RuntimeException('DB_NAME is not set in .env');
        }

        // Ensure database exists
        $server = self::pdoServer();
        $charset = self::config('DB_CHARSET', 'utf8mb4');

        $server->exec(
            "CREATE DATABASE IF NOT EXISTS `{$dbName}`
             CHARACTER SET {$charset}
             COLLATE {$charset}_general_ci"
        );

        // Connect to database
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            self::config('DB_HOST'),
            $dbName,
            $charset
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        self::$pdo = new PDO(
            $dsn,
            self::config('DB_USER'),
            self::config('DB_PASS'),
            $options
        );

        return self::$pdo;
    }
}
