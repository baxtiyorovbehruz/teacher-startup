<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $database,
            $charset
        );

        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new PDOException(
                'MySQL ulanishida xatolik. `app/config/config.php` faylini tekshiring va `database/schema.sql` ni import qiling.'
            );
        }
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetch();

        return $result === false ? null : $result;
    }

    public function execute(string $sql, array $params = []): bool
    {
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($params);
    }

    public function insert(string $sql, array $params = []): int
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return (int) $this->pdo->lastInsertId();
    }
}
