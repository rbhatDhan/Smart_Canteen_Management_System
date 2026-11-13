<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SmartCanteen\Modules\Auth\RegistrationRules;

final class RegistrationRulesTest extends TestCase
{
    public function testUsnIsNormalisedWithCollegePrefix(): void
    {
        $this->assertSame('4GW23IS056', RegistrationRules::normalizeUsn('23is056'));
        $this->assertSame('4GW23IS056', RegistrationRules::normalizeUsn(' 4gw23is056 '));
        $this->assertSame('4GW23IS056', RegistrationRules::normalizeUsn('4GW 23 IS 056'));
    }

    public function testValidUsnFormatsPass(): void
    {
        foreach (['4GW23IS056', '4GW21CS001', '4GW22AIML012'] as $usn) {
            $this->assertNull(RegistrationRules::usnError($usn), $usn);
        }
    }

    public function testInvalidUsnFormatsFail(): void
    {
        foreach (['4GW', '4GWABC', '4GW2IS056', '4GW23IS05', '4GW23I056', '4GW23IS0567', 'XYZ23IS056'] as $usn) {
            $this->assertNotNull(RegistrationRules::usnError($usn), $usn);
        }
    }

    public function testPasswordNeedsLengthLetterAndDigit(): void
    {
        $this->assertNull(RegistrationRules::passwordError('abcd1234'));
        $this->assertNotNull(RegistrationRules::passwordError('abc123'), 'too short');
        $this->assertNotNull(RegistrationRules::passwordError('abcdefgh'), 'no digit');
        $this->assertNotNull(RegistrationRules::passwordError('12345678'), 'no letter');
        $this->assertNotNull(RegistrationRules::passwordError(str_repeat('a1', 40)), 'longer than bcrypt limit');
    }

    public function testEmailAcceptsAnyValidDomain(): void
    {
        foreach (['a@b.com', 'student@college.edu.in', 'x.y+z@mail.co'] as $email) {
            $this->assertNull(RegistrationRules::emailError($email), $email);
        }
        foreach (['', 'plain', 'a@', '@b.com', 'a b@c.com'] as $email) {
            $this->assertNotNull(RegistrationRules::emailError($email), $email);
        }
    }

    public function testUsernameRules(): void
    {
        $this->assertNull(RegistrationRules::usernameError('Dhanya R Bhat'));
        $this->assertNull(RegistrationRules::usernameError('anna_k-2'));
        $this->assertNotNull(RegistrationRules::usernameError('ab'));
        $this->assertNotNull(RegistrationRules::usernameError('<script>alert(1)</script>'));
        $this->assertNotNull(RegistrationRules::usernameError(str_repeat('a', 51)));
    }

    public function testDepartmentLength(): void
    {
        $this->assertNull(RegistrationRules::departmentError('Computer Science'));
        $this->assertNotNull(RegistrationRules::departmentError('X'));
    }
}
