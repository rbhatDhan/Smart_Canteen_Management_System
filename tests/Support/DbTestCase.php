<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Support;

use PHPUnit\Framework\TestCase;
use SmartCanteen\Core\Database;
use SmartCanteen\Modules\Auth\UserRepository;
use SmartCanteen\Modules\Menu\MenuRepository;

/** Base class for tests that need the MySQL test database. Skips itself when it isn't reachable. */
abstract class DbTestCase extends TestCase
{
    protected Database $db;

    protected function setUp(): void
    {
        if (!TestDatabase::boot()) {
            $this->markTestSkipped('MySQL test database is not reachable (start MySQL and check DB_* in .env).');
        }
        TestDatabase::reset();
        $this->db = Database::get();
    }

    protected function makeStudent(string $username = 'alice', string $usn = '4GW23IS001'): int
    {
        return (new UserRepository())->createStudent($username, $username . '@example.com', password_hash('secret1', PASSWORD_DEFAULT), $usn, 'ISE');
    }

    /** Adds a dish to the menu of the given weekday (default: today) and returns its id. */
    protected function makeDish(string $name, string $price, ?string $day = null): int
    {
        (new MenuRepository())->add($name, (float) $price, $day ?? date('l'));
        return $this->db->insertId();
    }

    protected function countOrders(): int
    {
        return (int) $this->db->scalar('SELECT COUNT(*) FROM orders');
    }
}
