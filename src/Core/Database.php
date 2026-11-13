<?php
declare(strict_types=1);

namespace SmartCanteen\Core;

use mysqli;
use mysqli_stmt;

/** Thin prepared-statement wrapper around mysqli. */
final class Database
{
    private static ?self $instance = null;
    private mysqli $conn;

    private function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->conn = new mysqli(
            Env::get('DB_HOST', 'localhost'),
            Env::get('DB_USER', 'root'),
            Env::get('DB_PASS', ''),
            Env::get('DB_NAME', 'smartcanteen'),
            (int) Env::get('DB_PORT', '3306')
        );
        $this->conn->set_charset('utf8mb4');
    }

    public static function get(): self
    {
        return self::$instance ??= new self();
    }

    /** Execute a prepared statement. $types uses mysqli notation (e.g. "sis"). */
    public function query(string $sql, string $types = '', array $params = []): mysqli_stmt
    {
        $stmt = $this->conn->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    /** @return list<array<string,mixed>> */
    public function all(string $sql, string $types = '', array $params = []): array
    {
        $stmt = $this->query($sql, $types, $params);
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /** @return array<string,mixed>|null */
    public function one(string $sql, string $types = '', array $params = []): ?array
    {
        return $this->all($sql, $types, $params)[0] ?? null;
    }

    public function scalar(string $sql, string $types = '', array $params = []): mixed
    {
        $row = $this->one($sql, $types, $params);
        return $row === null ? null : reset($row);
    }

    /** Run INSERT/UPDATE/DELETE and return the number of affected rows. */
    public function run(string $sql, string $types = '', array $params = []): int
    {
        $stmt = $this->query($sql, $types, $params);
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function insertId(): int
    {
        return (int) $this->conn->insert_id;
    }

    /** Run $fn inside a transaction; rolls back and rethrows on failure. */
    public function transaction(callable $fn): mixed
    {
        $this->conn->begin_transaction();
        try {
            $result = $fn($this);
            $this->conn->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /** MySQL error number of the last failure (e.g. 1451 = FK violation, 1062 = duplicate). */
    public function errno(): int
    {
        return (int) $this->conn->errno;
    }
}
