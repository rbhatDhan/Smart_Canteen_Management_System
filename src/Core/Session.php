<?php
declare(strict_types=1);

namespace SmartCanteen\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // Sign people out after a period of inactivity (default 2 hours, SESSION_IDLE_MINUTES in .env).
        $idle = max(1, (int) Env::get('SESSION_IDLE_MINUTES', '120')) * 60;
        if (isset($_SESSION['user_id'], $_SESSION['last_active']) && time() - (int) $_SESSION['last_active'] > $idle) {
            $_SESSION = [];
            session_regenerate_id(true);
        }
        $_SESSION['last_active'] = time();
    }

    public static function login(array $user, string $role): void
    {
        session_regenerate_id(true); // prevent session fixation
        $_SESSION['user_id']  = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['usn']      = $user['usn'] ?? null;
        $_SESSION['role']     = $role;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
