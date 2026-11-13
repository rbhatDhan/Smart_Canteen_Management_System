<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SmartCanteen\Modules\Payments\RazorpayClient;

/** Signature checks are the security core of the payment flow, so they get dedicated tests. */
final class RazorpayClientTest extends TestCase
{
    private function sign(string $order, string $payment): string
    {
        return hash_hmac('sha256', $order . '|' . $payment, 'unit_test_secret');
    }

    public function testValidCheckoutSignatureIsAccepted(): void
    {
        $c = new RazorpayClient();
        $this->assertTrue($c->verifyCheckoutSignature('order_A1', 'pay_B2', $this->sign('order_A1', 'pay_B2')));
    }

    public function testTamperedPaymentIdIsRejected(): void
    {
        $c = new RazorpayClient();
        $this->assertFalse($c->verifyCheckoutSignature('order_A1', 'pay_OTHER', $this->sign('order_A1', 'pay_B2')));
    }

    public function testTamperedOrderIdIsRejected(): void
    {
        $c = new RazorpayClient();
        $this->assertFalse($c->verifyCheckoutSignature('order_OTHER', 'pay_B2', $this->sign('order_A1', 'pay_B2')));
    }

    public function testEmptyAndGarbageSignaturesAreRejected(): void
    {
        $c = new RazorpayClient();
        $this->assertFalse($c->verifyCheckoutSignature('order_A1', 'pay_B2', ''));
        $this->assertFalse($c->verifyCheckoutSignature('order_A1', 'pay_B2', 'not-a-signature'));
    }

    public function testWebhookSignatureUsesRawBodyAndWebhookSecret(): void
    {
        $c = new RazorpayClient();
        $body = '{"event":"order.paid"}';
        $good = hash_hmac('sha256', $body, 'unit_webhook_secret');
        $this->assertTrue($c->verifyWebhookSignature($body, $good));
        $this->assertFalse($c->verifyWebhookSignature($body . ' ', $good));
        $this->assertFalse($c->verifyWebhookSignature($body, ''));
    }

    public function testDetectsTestModeKeys(): void
    {
        $this->assertTrue((new RazorpayClient())->isTestMode());
        $this->assertTrue((new RazorpayClient())->configured());
    }
}
