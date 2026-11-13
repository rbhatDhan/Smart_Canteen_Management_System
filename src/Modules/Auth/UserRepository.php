<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Auth;

use SmartCanteen\Core\Database;

final class UserRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function findStudentByLogin(string $usernameOrUsn): ?array
    {
        return $this->db->one(
            "SELECT id, username, usn, password FROM users WHERE (username = ? OR usn = ?) AND role = 'student' LIMIT 1",
            'ss',
            [$usernameOrUsn, $usernameOrUsn]
        );
    }

    public function findAdminByUsername(string $username): ?array
    {
        return $this->db->one("SELECT * FROM users WHERE username = ? AND role = 'admin' LIMIT 1", 's', [$username]);
    }

    public function findById(int $id): ?array
    {
        return $this->db->one('SELECT * FROM users WHERE id = ?', 'i', [$id]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->one('SELECT * FROM users WHERE LOWER(TRIM(email)) = LOWER(?) LIMIT 1', 's', [trim($email)]);
    }

    public function usnOrEmailExists(string $usn, string $email): bool
    {
        return $this->db->one('SELECT id FROM users WHERE usn = ? OR email = ? LIMIT 1', 'ss', [$usn, $email]) !== null;
    }

    /** True if another user (not $exceptId) already uses this username, USN or email. */
    public function conflictsWithOther(int $exceptId, string $username, string $usn, string $email): bool
    {
        return $this->db->one(
            'SELECT id FROM users WHERE id <> ? AND (username = ? OR (usn <> "" AND usn = ?) OR (email <> "" AND email = ?)) LIMIT 1',
            'isss',
            [$exceptId, $username, $usn, $email]
        ) !== null;
    }

    public function createStudent(string $username, string $email, string $passwordHash, string $usn, string $dept): int
    {
        $this->db->run(
            "INSERT INTO users (username, email, password, role, usn, dept) VALUES (?, ?, ?, 'student', ?, ?)",
            'sssss',
            [$username, $email, $passwordHash, $usn, $dept]
        );
        return $this->db->insertId();
    }

    public function updatePassword(int $id, string $hash): void
    {
        $this->db->run('UPDATE users SET password = ? WHERE id = ?', 'si', [$hash, $id]);
    }

    public function updateProfile(int $id, string $username, string $email, string $dept, string $usn, ?string $passwordHash): void
    {
        if ($passwordHash !== null) {
            $this->db->run(
                'UPDATE users SET username=?, email=?, password=?, dept=?, usn=? WHERE id=?',
                'sssssi',
                [$username, $email, $passwordHash, $dept, $usn, $id]
            );
        } else {
            $this->db->run(
                'UPDATE users SET username=?, email=?, dept=?, usn=? WHERE id=?',
                'ssssi',
                [$username, $email, $dept, $usn, $id]
            );
        }
    }

    /** Stores only a SHA-256 of the reset token, so a DB leak can't be used to reset passwords. */
    public function saveResetToken(int $id, string $token, string $expires): void
    {
        $this->db->run(
            'UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?',
            'ssi',
            [hash('sha256', $token), $expires, $id]
        );
    }

    public function findByValidResetToken(string $token): ?array
    {
        return $this->db->one(
            'SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1',
            's',
            [hash('sha256', $token)]
        );
    }

    public function resetPassword(int $id, string $hash): void
    {
        $this->db->run(
            'UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?',
            'si',
            [$hash, $id]
        );
    }
}
