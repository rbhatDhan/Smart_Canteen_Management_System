<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Auth;

use SmartCanteen\Core\Database;

/** Locks an account (and noisy IPs) after repeated failed logins. */
final class LoginThrottle
{
    public const MAX_FAILURES_PER_ACCOUNT = 5;
    public const MAX_FAILURES_PER_IP = 20;
    public const WINDOW_MINUTES = 15;

    private Database $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    private static function id(string $scope, string $identifier): string
    {
        return substr($scope . ':' . mb_strtolower(trim($identifier)), 0, 120);
    }

    public static function clientIp(): string
    {
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
    }

    public function isLocked(string $scope, string $identifier, string $ip): bool
    {
        $since = date('Y-m-d H:i:s', time() - self::WINDOW_MINUTES * 60);
        $byAccount = (int) $this->db->scalar(
            'SELECT COUNT(*) FROM login_attempts WHERE identifier = ? AND success = 0 AND created_at > ?',
            'ss',
            [self::id($scope, $identifier), $since]
        );
        $byIp = (int) $this->db->scalar(
            'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND success = 0 AND created_at > ?',
            'ss',
            [$ip, $since]
        );
        return $byAccount >= self::MAX_FAILURES_PER_ACCOUNT || $byIp >= self::MAX_FAILURES_PER_IP;
    }

    public function recordFailure(string $scope, string $identifier, string $ip): void
    {
        $this->db->run(
            'INSERT INTO login_attempts (identifier, ip, success, created_at) VALUES (?, ?, 0, ?)',
            'sss',
            [self::id($scope, $identifier), $ip, date('Y-m-d H:i:s')]
        );
        $this->db->run('DELETE FROM login_attempts WHERE created_at < ?', 's', [date('Y-m-d H:i:s', time() - 86400)]);
    }

    /** A good login forgets that account's earlier failures. */
    public function recordSuccess(string $scope, string $identifier): void
    {
        $this->db->run('DELETE FROM login_attempts WHERE identifier = ?', 's', [self::id($scope, $identifier)]);
    }
}
