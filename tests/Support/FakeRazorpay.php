<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Support;

use SmartCanteen\Modules\Payments\RazorpayClient;

/** Razorpay stand-in: no network, but real signature maths (uses RAZORPAY_KEY_SECRET from tests/bootstrap.php). */
final class FakeRazorpay extends RazorpayClient
{
    /** @var list<array{amount:int,receipt:string}> */
    public array $created = [];

    public function configured(): bool
    {
        return true;
    }

    public function keyId(): string
    {
        return 'rzp_test_fake';
    }

    public function createOrder(int $amountPaise, string $receipt, array $notes = []): array
    {
        $this->created[] = ['amount' => $amountPaise, 'receipt' => $receipt];
        return ['id' => 'order_FAKE' . count($this->created), 'amount' => $amountPaise, 'currency' => 'INR'];
    }

    public function fetchPayment(string $paymentId): array
    {
        return ['id' => $paymentId, 'status' => 'captured'];
    }

    public function capturePayment(string $paymentId, int $amountPaise): array
    {
        return ['id' => $paymentId, 'status' => 'captured'];
    }

    public static function sign(string $orderId, string $paymentId): string
    {
        return hash_hmac('sha256', $orderId . '|' . $paymentId, 'unit_test_secret');
    }
}
