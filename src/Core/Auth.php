<?php
declare(strict_types=1);

namespace SmartCanteen\Core;

final class Auth
{
    public static function loggedIn(): bool
    {
        return isset($_SESSION['username'], $_SESSION['user_id']);
    }

    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public static function id(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public static function dashboardUrl(): string
    {
        return self::role() === 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php';
    }

    public static function requireStudent(string $redirect = 'student_login.php'): void
    {
        if (!self::loggedIn() || self::role() !== 'student') {
            Http::redirect($redirect);
        }
    }

    public static function requireAdmin(string $redirect = 'admin_login.php'): void
    {
        if (!self::loggedIn() || self::role() !== 'admin') {
            Http::redirect($redirect);
        }
    }

    /** For JSON endpoints: respond 401/403 instead of redirecting. */
    public static function requireRoleJson(string $role): void
    {
        if (!self::loggedIn()) {
            Http::json(['success' => false, 'message' => 'Please log in first.'], 401);
        }
        if (self::role() !== $role) {
            Http::json(['success' => false, 'message' => 'You are not allowed to do this.'], 403);
        }
    }
}
