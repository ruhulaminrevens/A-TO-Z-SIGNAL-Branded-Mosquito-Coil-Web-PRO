<?php
require_once __DIR__ . '/functions.php';

function try_db(): ?PDO {
    static $pdo = false;
    if ($pdo !== false) return $pdo;
    $config = require __DIR__ . '/../config/database.php';
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $config['host'], $config['database'], $config['charset'] ?? 'utf8mb4');
    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (Throwable $e) {
        $pdo = null;
        return null;
    }
}

function db(): PDO {
    $pdo = try_db();
    if (!$pdo) {
        throw new RuntimeException('Database connection failed. Update config/database.php after importing database/schema.sql.');
    }
    return $pdo;
}
