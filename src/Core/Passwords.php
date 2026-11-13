<?php
declare(strict_types=1);

namespace SmartCanteen\Core;

final class Passwords
{
    /**
     * Verifies a password. Accounts that still hold a legacy plain-text password
     * (the original seed data / admin accounts) keep working and are transparently
     * upgraded to a bcrypt hash on their first successful login.
     */
    /** @param callable(int,string):void $upgrade called with (userId, newHash) when a stored hash should be replaced */
    public static function verifyAndUpgrade(array $user, string $input, callable $upgrade): bool
    {
        $stored = (string) $user['password'];
        $isHash = password_get_info($stored)['algoName'] !== 'unknown';

        if ($isHash) {
            if (!password_verify($input, $stored)) {
                return false;
            }
            if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                $upgrade((int) $user['id'], self::hash($input));
            }
            return true;
        }

        if (!hash_equals($stored, $input)) {
            return false;
        }
        $upgrade((int) $user['id'], self::hash($input));
        return true;
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
