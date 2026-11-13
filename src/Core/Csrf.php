<?php
declare(strict_types=1);

namespace SmartCanteen\Core;

final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    /** Hidden input for classic forms (invisible, no UI impact). */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    /** Validates the token from a form field or the X-CSRF-Token header. */
    public static function valid(): bool
    {
        $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return is_string($sent) && $sent !== '' && hash_equals(self::token(), $sent);
    }

    public static function require(): void
    {
        if (!self::valid()) {
            http_response_code(419);
            exit('Your session expired. Please go back, refresh the page and try again.');
        }
    }
}
