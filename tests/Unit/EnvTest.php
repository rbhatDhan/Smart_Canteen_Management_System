<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SmartCanteen\Core\Env;

final class EnvTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($this->file, <<<ENV
# a comment
T_PLAIN=hello
T_SPACED =  padded value  
T_QUOTED="with # hash"
T_SINGLE='single quoted'
T_INLINE=value   # trailing comment
T_EMPTY=
T_TRUE=true
T_OFF=no
ENV);
        Env::load($this->file);
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
    }

    public function testReadsPlainAndTrimmedValues(): void
    {
        $this->assertSame('hello', Env::get('T_PLAIN'));
        $this->assertSame('padded value', Env::get('T_SPACED'));
    }

    public function testQuotedValuesKeepHashAndLoseQuotes(): void
    {
        $this->assertSame('with # hash', Env::get('T_QUOTED'));
        $this->assertSame('single quoted', Env::get('T_SINGLE'));
    }

    public function testInlineCommentsAreStripped(): void
    {
        $this->assertSame('value', Env::get('T_INLINE'));
    }

    public function testMissingAndEmptyFallBackToDefault(): void
    {
        $this->assertSame('fallback', Env::get('T_DOES_NOT_EXIST', 'fallback'));
        $this->assertSame('', Env::get('T_EMPTY', ''));
    }

    public function testBooleans(): void
    {
        $this->assertTrue(Env::bool('T_TRUE'));
        $this->assertFalse(Env::bool('T_OFF'));
        $this->assertTrue(Env::bool('T_NOPE', true));
    }
}
