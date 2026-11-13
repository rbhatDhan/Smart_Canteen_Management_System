<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SmartCanteen\Core\Csrf;

final class CsrfAndHelpersTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
    }

    public function testTokenIsStablePerSession(): void
    {
        $this->assertSame(Csrf::token(), Csrf::token());
        $this->assertSame(64, strlen(Csrf::token()));
    }

    public function testValidFormTokenPasses(): void
    {
        $_POST['_csrf'] = Csrf::token();
        $this->assertTrue(Csrf::valid());
    }

    public function testValidHeaderTokenPasses(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = Csrf::token();
        $this->assertTrue(Csrf::valid());
    }

    public function testMissingOrWrongTokenFails(): void
    {
        Csrf::token();
        $this->assertFalse(Csrf::valid());
        $_POST['_csrf'] = 'nope';
        $this->assertFalse(Csrf::valid());
    }

    public function testFieldContainsEscapedToken(): void
    {
        $this->assertStringContainsString('value="' . Csrf::token() . '"', Csrf::field());
    }

    public function testEscapeHelperNeutralisesHtml(): void
    {
        $this->assertSame('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', e('<script>alert("x")</script>'));
        $this->assertSame('', e(null));
    }

    public function testJsHelperIsSafeInsideScriptTags(): void
    {
        $out = js('</script><b>"\'&');
        $this->assertStringNotContainsString('</script>', $out);
        $this->assertStringNotContainsString('<', $out);
    }
}
