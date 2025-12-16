<?php
declare(strict_types=1);

final class Database {
    private static ?PDO $pdo = null;

    public static function conn(): PDO {
        if (self::$pdo) return self::$pdo;

        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = (int)($_ENV['DB_PORT'] ?? 3307);   // <<— here
        $db   = $_ENV['DB_NAME'] ?? 'shopdb';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? 'root';

        $dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return self::$pdo;
    }
}
