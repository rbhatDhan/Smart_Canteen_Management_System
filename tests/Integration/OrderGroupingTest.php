<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Integration;

use SmartCanteen\Modules\Orders\OrderRepository;
use SmartCanteen\Tests\Support\DbTestCase;

/** One checkout (payment) must read as ONE order with several items. */
final class OrderGroupingTest extends DbTestCase
{
    private OrderRepository $orders;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orders = new OrderRepository();
    }

    private function paidOrder(int $student, array $lines): int
    {
        $this->db->run(
            "INSERT INTO payments (student_id, razorpay_order_id, amount_paise, receipt, status, cart_json) VALUES (?, ?, 100, 'r', 'paid', '[]')",
            'is',
            [$student, 'order_G' . random_int(1000, 99999999)]
        );
        $payment = $this->db->insertId();
        $this->orders->insertPaidLines($student, '4GW23IS001', $payment, $lines);
        return $payment;
    }

    public function testOneCheckoutIsOneOrderWithItsItemsListed(): void
    {
        $s = $this->makeStudent();
        $rice = $this->makeDish('Rice', '30');
        $sambar = $this->makeDish('Sambar', '20');
        $payment = $this->paidOrder($s, [
            ['dish_id' => $rice, 'qty' => 2, 'unit_price' => '30.00'],
            ['dish_id' => $sambar, 'qty' => 1, 'unit_price' => '20.00'],
        ]);

        $orders = $this->orders->forStudent($s);

        $this->assertCount(1, $orders);
        $this->assertSame((string) $payment, $orders[0]['number']);
        $this->assertSame(3, $orders[0]['quantity']);
        $this->assertStringContainsString('Rice × 2', $orders[0]['label']);
        $this->assertStringContainsString('Sambar × 1', $orders[0]['label']);
        $this->assertSame('Pending', $orders[0]['status']);
    }

    public function testHistoryIncludesPendingOrdersNotOnlyCompletedOnes(): void
    {
        $s = $this->makeStudent();
        $rice = $this->makeDish('Rice', '30');
        $this->paidOrder($s, [['dish_id' => $rice, 'qty' => 1, 'unit_price' => '30.00']]);

        $this->assertCount(1, $this->orders->forStudent($s));
        $this->assertSame(['total' => 1, 'pending' => 1], $this->orders->studentTotals($s));
    }

    public function testCompletingAnOrderCompletesAllItsLines(): void
    {
        $s = $this->makeStudent();
        $a = $this->makeDish('Rice', '30');
        $b = $this->makeDish('Sambar', '20');
        $payment = $this->paidOrder($s, [
            ['dish_id' => $a, 'qty' => 1, 'unit_price' => '30.00'],
            ['dish_id' => $b, 'qty' => 1, 'unit_price' => '20.00'],
        ]);

        $this->orders->updateStatus($payment, 'Completed');

        $this->assertSame(0, (int) $this->db->scalar("SELECT COUNT(*) FROM orders WHERE status <> 'Completed'"));
        $this->assertSame('Completed', $this->orders->forStudent($s)[0]['status']);
        $this->assertSame(['total' => 1, 'pending' => 0], $this->orders->studentTotals($s));
        $this->assertSame(['total' => 1, 'pending' => 0, 'completed' => 1], $this->orders->adminCounts());
    }

    public function testOrdersPlacedBeforePaymentsExistedStayVisibleAsSingleLineOrders(): void
    {
        $s = $this->makeStudent();
        $dish = $this->makeDish('Old Dish', '40');
        $this->db->run(
            "INSERT INTO orders (student_id, dish_id, quantity, order_time, status, payment_status, usn) VALUES (?, ?, 2, NOW(), 'Pending', 'Successful', '4GW23IS001')",
            'ii',
            [$s, $dish]
        );
        $lineId = $this->db->insertId();

        $orders = $this->orders->forStudent($s);
        $this->assertCount(1, $orders);
        $this->assertSame('L' . $lineId, $orders[0]['number']);

        $this->orders->updateStatus($orders[0]['key'], 'Completed');
        $this->assertSame('Completed', $this->orders->forStudent($s)[0]['status']);
    }

    public function testStudentsOnlySeeTheirOwnOrders(): void
    {
        $alice = $this->makeStudent('alice', '4GW23IS001');
        $bob = $this->makeStudent('bob', '4GW23IS002');
        $dish = $this->makeDish('Rice', '30');
        $payment = $this->paidOrder($alice, [['dish_id' => $dish, 'qty' => 1, 'unit_price' => '30.00']]);

        $this->assertCount(0, $this->orders->forStudent($bob));
        $this->assertNull($this->orders->orderForStudent($bob, $payment));
        $this->assertNotNull($this->orders->orderForStudent($alice, $payment));
    }

    public function testFavouriteDishIsTheMostOrderedOne(): void
    {
        $s = $this->makeStudent();
        $rice = $this->makeDish('Rice', '30');
        $sambar = $this->makeDish('Sambar', '20');
        $this->assertSame('N/A', $this->orders->favoriteDish($s));

        $this->paidOrder($s, [['dish_id' => $rice, 'qty' => 1, 'unit_price' => '30.00'], ['dish_id' => $sambar, 'qty' => 4, 'unit_price' => '20.00']]);

        $this->assertSame('Sambar', $this->orders->favoriteDish($s));
    }
}
