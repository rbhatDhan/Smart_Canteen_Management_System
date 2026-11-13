<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Payments;

use SmartCanteen\Core\Database;

final class PaymentRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function create(int $studentId, string $rzpOrderId, int $amountPaise, string $receipt, string $cartJson): int
    {
        $this->db->run(
            "INSERT INTO payments (student_id, razorpay_order_id, amount_paise, currency, receipt, status, cart_json)
             VALUES (?, ?, ?, 'INR', ?, 'created', ?)",
            'isiss',
            [$studentId, $rzpOrderId, $amountPaise, $receipt, $cartJson]
        );
        return $this->db->insertId();
    }

    public function findByRazorpayOrderId(string $rzpOrderId): ?array
    {
        return $this->db->one('SELECT * FROM payments WHERE razorpay_order_id = ? LIMIT 1', 's', [$rzpOrderId]);
    }

    /** Atomic: only one caller (checkout callback OR webhook) can flip a payment to paid. */
    public function markPaid(int $id, string $rzpPaymentId, ?string $signature): int
    {
        return $this->db->run(
            "UPDATE payments SET status = 'paid', razorpay_payment_id = ?, razorpay_signature = ?, paid_at = NOW(), failure_reason = NULL
              WHERE id = ? AND status <> 'paid'",
            'ssi',
            [$rzpPaymentId, $signature, $id]
        );
    }

    /** Never downgrades a paid payment. */
    public function markFailed(string $rzpOrderId, ?string $rzpPaymentId, string $reason): void
    {
        $this->db->run(
            "UPDATE payments SET status = 'failed', razorpay_payment_id = COALESCE(?, razorpay_payment_id), failure_reason = ?
              WHERE razorpay_order_id = ? AND status <> 'paid'",
            'sss',
            [$rzpPaymentId, mb_substr($reason, 0, 250), $rzpOrderId]
        );
    }
}
