<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Integration;

use SmartCanteen\Modules\Orders\OrderRepository;
use SmartCanteen\Modules\Payments\PaymentService;
use SmartCanteen\Tests\Support\DbTestCase;
use SmartCanteen\Tests\Support\FakeRazorpay;

final class PaymentFlowTest extends DbTestCase
{
    private FakeRazorpay $rzp;
    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rzp = new FakeRazorpay();
        $this->service = new PaymentService($this->rzp);
    }

    /** Creates a checkout for the student and returns [checkoutArray, paymentIdString]. */
    private function checkout(int $student, array $items): array
    {
        $co = $this->service->createCheckout($student, $items);
        return [$co, 'pay_TEST' . random_int(1000, 9999)];
    }

    public function testPriceComesFromTheDatabaseNotTheBrowser(): void
    {
        $s = $this->makeStudent();
        $dish = $this->makeDish('Veg Biriyani', '55.50');

        $co = $this->service->createCheckout($s, [$dish => 2]);

        $this->assertSame(11100, $co['amount']);           // 2 x 55.50 in paise
        $this->assertSame(11100, $this->rzp->created[0]['amount']);
        $row = $this->db->one('SELECT status, amount_paise FROM payments WHERE razorpay_order_id = ?', 's', [$co['order_id']]);
        $this->assertSame('created', $row['status']);
        $this->assertSame(0, $this->countOrders(), 'No order may exist before payment');
    }

    public function testDishNotOnTodaysMenuIsRejected(): void
    {
        $s = $this->makeStudent();
        $tomorrow = $this->makeDish('Tomorrow Special', '40', date('l', strtotime('+1 day')));

        $this->expectException(\InvalidArgumentException::class);
        $this->service->createCheckout($s, [$tomorrow => 1]);
    }

    public function testMoreThanTenOfOneDishIsRejected(): void
    {
        $s = $this->makeStudent();
        $dish = $this->makeDish('Rice', '30');

        $this->expectException(\InvalidArgumentException::class);
        $this->service->createCheckout($s, [$dish => 11]);
    }

    public function testEmptyCartIsRejected(): void
    {
        $s = $this->makeStudent();
        $dish = $this->makeDish('Rice', '30');

        $this->expectException(\InvalidArgumentException::class);
        $this->service->createCheckout($s, [$dish => 0]);
    }

    public function testVerifiedPaymentCreatesOrdersExactlyOnce(): void
    {
        $s = $this->makeStudent();
        $a = $this->makeDish('Rice', '30');
        $b = $this->makeDish('Sambar', '20');
        [$co, $pay] = $this->checkout($s, [$a => 2, $b => 1]);
        $sig = FakeRazorpay::sign($co['order_id'], $pay);

        $first = $this->service->confirm($s, $co['order_id'], $pay, $sig);
        $this->assertTrue($first['success']);
        $this->assertSame(2, $this->countOrders());

        // browser retries / double click / webhook arriving later
        $this->assertTrue($this->service->confirm($s, $co['order_id'], $pay, $sig)['success']);
        $this->service->confirmFromWebhook($co['order_id'], $pay);
        $this->assertSame(2, $this->countOrders(), 'Orders must not be duplicated');

        $row = $this->db->one('SELECT status, razorpay_payment_id FROM payments WHERE razorpay_order_id = ?', 's', [$co['order_id']]);
        $this->assertSame('paid', $row['status']);
        $this->assertSame($pay, $row['razorpay_payment_id']);
    }

    public function testOrderRowsCarryStatusPriceAndUsn(): void
    {
        $s = $this->makeStudent('bob', '4GW23IS042');
        $dish = $this->makeDish('Rice', '30');
        [$co, $pay] = $this->checkout($s, [$dish => 3]);
        $this->service->confirm($s, $co['order_id'], $pay, FakeRazorpay::sign($co['order_id'], $pay));

        $o = $this->db->one('SELECT * FROM orders');
        $this->assertSame('Pending', $o['status']);
        $this->assertSame('Successful', $o['payment_status']);
        $this->assertSame('4GW23IS042', $o['usn']);
        $this->assertSame('30.00', $o['unit_price']);
        $this->assertSame(3, (int) $o['quantity']);
    }

    public function testBadSignatureCreatesNoOrders(): void
    {
        $s = $this->makeStudent();
        $dish = $this->makeDish('Rice', '30');
        [$co, $pay] = $this->checkout($s, [$dish => 1]);

        $r = $this->service->confirm($s, $co['order_id'], $pay, 'forged');

        $this->assertFalse($r['success']);
        $this->assertSame(0, $this->countOrders());
    }

    public function testAnotherStudentCannotConfirmSomeoneElsesPayment(): void
    {
        $alice = $this->makeStudent('alice', '4GW23IS001');
        $mallory = $this->makeStudent('mallory', '4GW23IS999');
        $dish = $this->makeDish('Rice', '30');
        [$co, $pay] = $this->checkout($alice, [$dish => 1]);

        $r = $this->service->confirm($mallory, $co['order_id'], $pay, FakeRazorpay::sign($co['order_id'], $pay));

        $this->assertFalse($r['success']);
        $this->assertSame(0, $this->countOrders());
    }

    public function testMalformedReferencesAreRejected(): void
    {
        $s = $this->makeStudent();
        $r = $this->service->confirm($s, "order_1'; DROP TABLE orders;--", 'pay_1', 'x');
        $this->assertFalse($r['success']);
    }

    public function testFailureReportNeverDowngradesAPaidPayment(): void
    {
        $s = $this->makeStudent();
        $dish = $this->makeDish('Rice', '30');
        [$co, $pay] = $this->checkout($s, [$dish => 1]);
        $this->service->confirm($s, $co['order_id'], $pay, FakeRazorpay::sign($co['order_id'], $pay));

        $this->service->recordFailure($co['order_id'], $pay, 'late failure event');

        $status = $this->db->scalar('SELECT status FROM payments WHERE razorpay_order_id = ?', 's', [$co['order_id']]);
        $this->assertSame('paid', $status);
    }

    public function testStudentCanRetryAfterAFailedAttempt(): void
    {
        $s = $this->makeStudent();
        $dish = $this->makeDish('Rice', '30');
        [$co, $pay] = $this->checkout($s, [$dish => 1]);

        $this->service->recordFailure($co['order_id'], null, 'card declined');
        $this->assertSame('failed', $this->db->scalar('SELECT status FROM payments WHERE razorpay_order_id = ?', 's', [$co['order_id']]));

        $this->assertTrue($this->service->confirm($s, $co['order_id'], $pay, FakeRazorpay::sign($co['order_id'], $pay))['success']);
        $this->assertSame(1, $this->countOrders());
    }

    public function testRevenueUsesThePricePaidNotTheCurrentMenuPrice(): void
    {
        $s = $this->makeStudent();
        $dish = $this->makeDish('Rice', '30');
        [$co, $pay] = $this->checkout($s, [$dish => 2]);
        $this->service->confirm($s, $co['order_id'], $pay, FakeRazorpay::sign($co['order_id'], $pay));

        $this->db->run('UPDATE menu SET price = 999 WHERE id = ?', 'i', [$dish]);

        $this->assertEqualsWithDelta(60.0, (new OrderRepository())->revenue(), 0.001);
    }
}
