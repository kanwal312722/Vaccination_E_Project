<?php
require_once __DIR__ . '/../config/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        if (strpos(DB_DSN, 'sqlite') === 0) { $pdo->exec('PRAGMA foreign_keys = ON'); }
    }
    return $pdo;
}

// Every query uses prepared statements. Never put user input into $sql directly.
function q(string $sql, array $params = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}
function rows(string $sql, array $p = []): array { return q($sql, $p)->fetchAll(); }
function row(string $sql, array $p = []): ?array { $r = q($sql, $p)->fetch(); return $r ?: null; }
function val(string $sql, array $p = []) { $r = q($sql, $p)->fetchColumn(); return $r === false ? null : $r; }

set_exception_handler(function (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo DEBUG ? '<pre>' . htmlspecialchars((string)$e) . '</pre>' : 'Something went wrong. Please try again later.';
});
