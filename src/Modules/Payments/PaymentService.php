<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Payments;

use SmartCanteen\Core\Database;
use SmartCanteen\Core\Logger;
use SmartCanteen\Modules\Auth\UserRepository;
use SmartCanteen\Modules\Menu\MenuRepository;
use SmartCanteen\Modules\Notifications\Mailer;
use SmartCanteen\Modules\Orders\OrderRepository;

/**
 * Payment flow:
 *  1. createCheckout()  - validates the cart against today's menu, prices it from the DB
 *                         (never from the browser), creates a Razorpay order.
 *  2. confirm()         - verifies Razorpay's signature, then creates the real order rows.
 *  3. confirmFromWebhook() - same finalisation if the browser never came back.
 * Orders are only created after a verified payment, and only once (idempotent).
 */
final class PaymentService
{
    public const MAX_QTY_PER_DISH = 10;

    private RazorpayClient $razorpay;
    private PaymentRepository $payments;
    private OrderRepository $orders;
    private UserRepository $users;

    /** Dependencies are injectable so tests can pass a fake Razorpay client. */
    public function __construct(?RazorpayClient $razorpay = null)
    {
        $this->razorpay = $razorpay ?? new RazorpayClient();
        $this->payments = new PaymentRepository();
        $this->orders   = new OrderRepository();
        $this->users    = new UserRepository();
    }

    /**
     * @param array<int|string,mixed> $items dish_id => quantity
     * @return array<string,mixed> parameters for Razorpay Checkout
     * @throws \InvalidArgumentException for bad carts (message is safe to show)
     * @throws \RuntimeException when Razorpay can't be reached / isn't configured
     */
    public function createCheckout(int $studentId, array $items): array
    {
        if (!$this->razorpay->configured()) {
            throw new \RuntimeException('Razorpay keys are not configured.');
        }
        $this->assertCanteenOpen();

        $menu = (new MenuRepository())->forDayById(date('l'));
        $cart = [];
        $totalPaise = 0;

        foreach ($items as $dishId => $qty) {
            $dishId = (int) $dishId;
            $qty    = (int) $qty;
            if ($qty <= 0) {
                continue;
            }
            if (!isset($menu[$dishId])) {
                throw new \InvalidArgumentException('One of the selected dishes is not on today\'s menu. Please reload the page.');
            }
            if ($qty > self::MAX_QTY_PER_DISH) {
                throw new \InvalidArgumentException('You can order at most ' . self::MAX_QTY_PER_DISH . ' of each dish.');
            }
            $unitPaise = (int) round(((float) $menu[$dishId]['price']) * 100);
            $totalPaise += $unitPaise * $qty;
            $cart[] = [
                'dish_id'    => $dishId,
                'name'       => $menu[$dishId]['name'],
                'qty'        => $qty,
                'unit_price' => number_format($unitPaise / 100, 2, '.', ''),
            ];
        }

        if (!$cart) {
            throw new \InvalidArgumentException('Please select at least one dish.');
        }
        if ($totalPaise < 100) {
            throw new \InvalidArgumentException('Order total must be at least ₹1.');
        }

        $receipt = 'sc_' . $studentId . '_' . time();
        $order = $this->razorpay->createOrder($totalPaise, $receipt, ['student_id' => (string) $studentId]);

        $this->payments->create(
            $studentId,
            (string) $order['id'],
            $totalPaise,
            $receipt,
            json_encode($cart, JSON_UNESCAPED_UNICODE)
        );

        $student = $this->users->findById($studentId) ?? [];
        return [
            'key'         => $this->razorpay->keyId(),
            'order_id'    => $order['id'],
            'amount'      => $totalPaise,
            'currency'    => 'INR',
            'name'        => 'Smart Canteen',
            'description' => 'Canteen order for ' . date('l'),
            'prefill'     => [
                'name'  => $student['username'] ?? '',
                'email' => $student['email'] ?? '',
            ],
        ];
    }

    /** Optional opening hours: set CANTEEN_OPEN and CANTEEN_CLOSE (HH:MM) in .env to enforce them. */
    private function assertCanteenOpen(): void
    {
        $open  = (string) \SmartCanteen\Core\Env::get('CANTEEN_OPEN', '');
        $close = (string) \SmartCanteen\Core\Env::get('CANTEEN_CLOSE', '');
        if (!preg_match('/^\d{2}:\d{2}$/', $open) || !preg_match('/^\d{2}:\d{2}$/', $close)) {
            return; // no hours configured = always open
        }
        $now = date('H:i');
        if ($now < $open || $now > $close) {
            throw new \InvalidArgumentException("The canteen is closed right now. Orders are accepted between $open and $close.");
        }
    }

    /**
     * Called by the browser after Checkout succeeds.
     * @return array{success:bool,message:string}
     */
    public function confirm(int $studentId, string $orderId, string $paymentId, string $signature): array
    {
        if (!preg_match('/^order_\w+$/', $orderId) || !preg_match('/^pay_\w+$/', $paymentId)) {
            return ['success' => false, 'message' => 'Invalid payment reference.'];
        }
        if (!$this->razorpay->verifyCheckoutSignature($orderId, $paymentId, $signature)) {
            Logger::error("Bad checkout signature for $orderId / $paymentId");
            return ['success' => false, 'message' => 'Payment verification failed.'];
        }

        $payment = $this->payments->findByRazorpayOrderId($orderId);
        if (!$payment || (int) $payment['student_id'] !== $studentId) {
            return ['success' => false, 'message' => 'Payment record not found.'];
        }

        $this->finalize($payment, $paymentId, $signature); // no-op if the webhook already did it
        return ['success' => true, 'message' => 'Payment confirmed and order placed successfully!'];
    }

    /** Called by the signed webhook (order.paid / payment.captured). */
    public function confirmFromWebhook(string $orderId, string $paymentId): void
    {
        $payment = $this->payments->findByRazorpayOrderId($orderId);
        if (!$payment) {
            Logger::info("Webhook for unknown order $orderId ignored");
            return;
        }
        $this->finalize($payment, $paymentId, null);
    }

    public function recordFailure(string $orderId, ?string $paymentId, string $reason): void
    {
        $this->payments->markFailed($orderId, $paymentId, $reason);
    }

    /** @return bool true if this call created the orders, false if they already existed */
    private function finalize(array $payment, string $rzpPaymentId, ?string $signature): bool
    {
        $created = (bool) Database::get()->transaction(function () use ($payment, $rzpPaymentId, $signature): bool {
            if ($this->payments->markPaid((int) $payment['id'], $rzpPaymentId, $signature) === 0) {
                return false; // already paid & processed
            }
            $cart = json_decode((string) $payment['cart_json'], true) ?: [];
            $student = $this->users->findById((int) $payment['student_id']);
            $this->orders->insertPaidLines(
                (int) $payment['student_id'],
                $student['usn'] ?? null,
                (int) $payment['id'],
                $cart
            );
            return true;
        });

        if ($created) {
            $this->captureIfNeeded($rzpPaymentId, (int) $payment['amount_paise']);
            $this->sendConfirmationEmail($payment, $rzpPaymentId);
        }
        return $created;
    }

    /** If the Razorpay account uses manual capture, capture now (best effort). */
    private function captureIfNeeded(string $paymentId, int $amountPaise): void
    {
        try {
            $p = $this->razorpay->fetchPayment($paymentId);
            if (($p['status'] ?? '') === 'authorized') {
                $this->razorpay->capturePayment($paymentId, $amountPaise);
            }
        } catch (\Throwable $ex) {
            Logger::error('Capture check failed for ' . $paymentId . ': ' . $ex->getMessage());
        }
    }

    private function sendConfirmationEmail(array $payment, string $rzpPaymentId): void
    {
        try {
            $student = $this->users->findById((int) $payment['student_id']);
            if (!$student || empty($student['email'])) {
                return;
            }
            $rows = '';
            foreach (json_decode((string) $payment['cart_json'], true) ?: [] as $l) {
                $rows .= '<li>' . e($l['name']) . ' × ' . (int) $l['qty'] . ' — ₹' . e($l['unit_price']) . ' each</li>';
            }
            $total = number_format(((int) $payment['amount_paise']) / 100, 2);
            $body = '<h2>Order Confirmation</h2>'
                  . '<p>Hi ' . e($student['username']) . ',</p>'
                  . '<p>Your order has been placed successfully!</p>'
                  . "<ul>{$rows}</ul>"
                  . "<p><strong>Total paid:</strong> ₹{$total}</p>"
                  . '<p><strong>Payment Status:</strong> Successful (' . e($rzpPaymentId) . ')</p>'
                  . '<p><strong>Order Status:</strong> Pending</p>'
                  . '<p>You will be notified once the order is ready.</p>';
            (new Mailer())->send((string) $student['email'], (string) $student['username'], 'Order Confirmation', $body);
        } catch (\Throwable $ex) {
            Logger::error('Confirmation email failed: ' . $ex->getMessage());
        }
    }
}
