<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SmartCanteen\Core\Passwords;

final class PasswordsTest extends TestCase
{
    public function testCorrectHashedPasswordLogsIn(): void
    {
        $user = ['id' => 7, 'password' => Passwords::hash('hunter22')];
        $upgraded = false;
        $this->assertTrue(Passwords::verifyAndUpgrade($user, 'hunter22', function () use (&$upgraded) { $upgraded = true; }));
        $this->assertFalse($upgraded, 'A current hash must not be rewritten');
    }

    public function testWrongPasswordIsRejected(): void
    {
        $user = ['id' => 7, 'password' => Passwords::hash('hunter22')];
        $this->assertFalse(Passwords::verifyAndUpgrade($user, 'wrong', fn () => $this->fail('must not upgrade')));
    }

    public function testLegacyPlainTextPasswordStillWorksAndIsUpgraded(): void
    {
        $user = ['id' => 3, 'password' => 'admin123'];
        $saved = [];
        $ok = Passwords::verifyAndUpgrade($user, 'admin123', function (int $id, string $hash) use (&$saved) {
            $saved = [$id, $hash];
        });
        $this->assertTrue($ok);
        $this->assertSame(3, $saved[0]);
        $this->assertTrue(password_verify('admin123', $saved[1]), 'The stored value must now be a real hash');
    }

    public function testLegacyPlainTextWrongPasswordIsRejectedWithoutUpgrade(): void
    {
        $user = ['id' => 3, 'password' => 'admin123'];
        $this->assertFalse(Passwords::verifyAndUpgrade($user, 'admin124', fn () => $this->fail('must not upgrade')));
    }

    public function testHashIsNotThePlainPassword(): void
    {
        $this->assertNotSame('secret', Passwords::hash('secret'));
        $this->assertNotSame(Passwords::hash('secret'), Passwords::hash('secret'), 'bcrypt salts each hash');
    }
}
