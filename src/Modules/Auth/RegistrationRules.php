<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Auth;

use SmartCanteen\Core\Env;

/** Validation rules shared by registration and profile editing. Each method returns an error message or null. */
final class RegistrationRules
{
    public static function usnPrefix(): string
    {
        return strtoupper((string) Env::get('USN_PREFIX', '4GW'));
    }

    /** Uppercases, trims and adds the college prefix when the student typed only e.g. "23IS056". */
    public static function normalizeUsn(string $usn): string
    {
        $usn = strtoupper(preg_replace('/\s+/', '', $usn) ?? '');
        $prefix = self::usnPrefix();
        return str_starts_with($usn, $prefix) ? $usn : $prefix . $usn;
    }

    public static function usnError(string $normalized): ?string
    {
        $pattern = '/^' . preg_quote(self::usnPrefix(), '/') . '\d{2}[A-Z]{2,4}\d{3}$/';
        return preg_match($pattern, $normalized) ? null : 'Enter a valid USN (example: ' . self::usnPrefix() . '23IS056).';
    }

    public static function usernameError(string $username): ?string
    {
        $len = mb_strlen($username);
        if ($len < 3 || $len > 50) {
            return 'Name must be between 3 and 50 characters.';
        }
        if (!preg_match('/^[\p{L}0-9 ._-]+$/u', $username)) {
            return 'Name can only contain letters, numbers, spaces and . _ -';
        }
        return null;
    }

    public static function emailError(string $email): ?string
    {
        if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Enter a valid email address.';
        }
        return null;
    }

    public static function departmentError(string $dept): ?string
    {
        $len = mb_strlen($dept);
        return ($len < 2 || $len > 50) ? 'Department must be between 2 and 50 characters.' : null;
    }

    public static function passwordError(string $password): ?string
    {
        if (strlen($password) < 8 || strlen($password) > 72
            || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            return 'Password must be 8-72 characters and include a letter and a number.';
        }
        return null;
    }
}
