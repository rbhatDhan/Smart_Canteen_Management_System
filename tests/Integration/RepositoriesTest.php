<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Integration;

use SmartCanteen\Modules\Auth\UserRepository;
use SmartCanteen\Modules\Menu\MenuRepository;
use SmartCanteen\Modules\Orders\OrderRepository;
use SmartCanteen\Tests\Support\DbTestCase;

final class RepositoriesTest extends DbTestCase
{
    public function testDishWithPastOrdersCannotBeDeletedButUnusedOneCan(): void
    {
        $s = $this->makeStudent();
        $used = $this->makeDish('Used Dish', '30');
        $unused = $this->makeDish('Unused Dish', '30');
        (new OrderRepository())->insertPaidLines($s, '4GW23IS001', 0 + $this->makePaymentRow($s), [
            ['dish_id' => $used, 'qty' => 1, 'unit_price' => '30.00'],
        ]);

        $menu = new MenuRepository();
        $this->assertFalse($menu->delete($used), 'FK violation must be reported, not thrown');
        $this->assertTrue($menu->delete($unused));
    }

    public function testDuplicateDishCheckIsCaseInsensitivePerDay(): void
    {
        $menu = new MenuRepository();
        $this->makeDish('Fried Rice', '60', 'Monday');
        $this->assertTrue($menu->exists('Monday', 'fried rice'));
        $this->assertFalse($menu->exists('Tuesday', 'Fried Rice'));
    }

    public function testStudentCanLogInByUsernameOrUsn(): void
    {
        $this->makeStudent('carol', '4GW23IS007');
        $users = new UserRepository();
        $this->assertSame('carol', $users->findStudentByLogin('carol')['username']);
        $this->assertSame('carol', $users->findStudentByLogin('4GW23IS007')['username']);
        $this->assertNull($users->findStudentByLogin('nobody'));
    }

    public function testAdminsAreNotFoundAsStudents(): void
    {
        $this->db->run("INSERT INTO users (username, password, role) VALUES ('boss', 'x', 'admin')");
        $users = new UserRepository();
        $this->assertNull($users->findStudentByLogin('boss'));
        $this->assertNotNull($users->findAdminByUsername('boss'));
    }

    public function testResetTokenIsStoredHashedAndExpires(): void
    {
        $id = $this->makeStudent('dave', '4GW23IS010');
        $users = new UserRepository();

        $users->saveResetToken($id, 'plain-token', date('Y-m-d H:i:s', strtotime('+1 hour')));
        $stored = $this->db->scalar('SELECT reset_token FROM users WHERE id = ?', 'i', [$id]);
        $this->assertNotSame('plain-token', $stored, 'Only a hash may be stored');
        $this->assertNotNull($users->findByValidResetToken('plain-token'));
        $this->assertNull($users->findByValidResetToken('other-token'));

        $users->saveResetToken($id, 'old-token', date('Y-m-d H:i:s', strtotime('-1 minute')));
        $this->assertNull($users->findByValidResetToken('old-token'), 'Expired tokens must not work');
    }

    public function testResetTokenIsSingleUse(): void
    {
        $id = $this->makeStudent('erin', '4GW23IS011');
        $users = new UserRepository();
        $users->saveResetToken($id, 'one-shot', date('Y-m-d H:i:s', strtotime('+1 hour')));

        $users->resetPassword($id, password_hash('newpass1', PASSWORD_DEFAULT));

        $this->assertNull($users->findByValidResetToken('one-shot'));
    }

    public function testProfileConflictDetectionIgnoresOwnRow(): void
    {
        $a = $this->makeStudent('frank', '4GW23IS020');
        $this->makeStudent('grace', '4GW23IS021');
        $users = new UserRepository();

        $this->assertFalse($users->conflictsWithOther($a, 'frank', '4GW23IS020', 'frank@example.com'));
        $this->assertTrue($users->conflictsWithOther($a, 'grace', '4GW23IS020', 'frank@example.com'));
    }

    /** Minimal payments row so orders can reference it. */
    private function makePaymentRow(int $student): int
    {
        $this->db->run(
            "INSERT INTO payments (student_id, razorpay_order_id, amount_paise, receipt, status, cart_json) VALUES (?, ?, 3000, 'r', 'paid', '[]')",
            'is',
            [$student, 'order_T' . random_int(1000, 999999)]
        );
        return $this->db->insertId();
    }
}
