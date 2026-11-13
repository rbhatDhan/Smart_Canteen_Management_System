<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Orders;

use SmartCanteen\Core\Database;

/**
 * Orders are stored as one row per dish ("line"). One checkout (= one Razorpay payment) produces one
 * ORDER made of several lines. The order's key is the payment id; orders from before payments existed
 * (no payment_id) are single-line orders keyed by the negative line id.
 */
final class OrderRepository
{
    /** Revenue uses the price paid at purchase time (falls back to current price for legacy rows). */
    private const LINE_TOTAL = 'COALESCE(o.unit_price, m.price) * o.quantity';
    private const PAID = "o.payment_status <> 'Unsuccessful'";
    private const KEY = 'COALESCE(o.payment_id, -o.id)';
    private const LINE_SELECT = 'o.id, o.quantity, o.order_time, o.status, o.payment_status, o.usn, o.payment_id,
            m.name AS dish_name, COALESCE(o.payment_id, -o.id) AS order_key, p.razorpay_payment_id';
    private const LINE_JOIN = 'FROM orders o
            JOIN menu m ON o.dish_id = m.id
            LEFT JOIN payments p ON o.payment_id = p.id';

    private Database $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    // ---- creating orders ------------------------------------------------

    /**
     * Inserts one order row per cart line for a verified payment.
     * @param list<array{dish_id:int,qty:int,unit_price:string|float}> $lines
     */
    public function insertPaidLines(int $studentId, ?string $usn, int $paymentId, array $lines): void
    {
        $now = date('Y-m-d H:i:s');
        foreach ($lines as $l) {
            $this->db->run(
                "INSERT INTO orders (student_id, dish_id, quantity, order_time, status, payment_status, usn, unit_price, payment_id)
                 VALUES (?, ?, ?, ?, 'Pending', 'Successful', ?, ?, ?)",
                'iiissdi',
                [$studentId, (int) $l['dish_id'], (int) $l['qty'], $now, $usn, (float) $l['unit_price'], $paymentId]
            );
        }
    }

    // ---- grouping lines into orders ------------------------------------

    /**
     * @param list<array<string,mixed>> $rows order lines (as selected with LINE_SELECT)
     * @return list<array<string,mixed>> orders, in the order their first line appeared
     */
    public static function groupLines(array $rows): array
    {
        $orders = [];
        foreach ($rows as $r) {
            $key = (int) $r['order_key'];
            if (!isset($orders[$key])) {
                $orders[$key] = [
                    'key'            => $key,
                    'number'         => $key > 0 ? (string) $key : 'L' . (-$key),
                    'order_time'     => $r['order_time'],
                    'usn'            => $r['usn'] ?? null,
                    'username'       => $r['username'] ?? null,
                    'email'          => $r['email'] ?? null,
                    'payment_status' => 'Successful',
                    'payment_ref'    => $r['razorpay_payment_id'] ?? null,
                    'items'          => [],
                    'quantity'       => 0,
                    '_pending'       => 0,
                ];
            }
            $orders[$key]['items'][] = ['name' => (string) $r['dish_name'], 'qty' => (int) $r['quantity']];
            $orders[$key]['quantity'] += (int) $r['quantity'];
            if ($r['status'] !== 'Completed') {
                $orders[$key]['_pending']++;
            }
            if ($r['payment_status'] === 'Unsuccessful') {
                $orders[$key]['payment_status'] = 'Unsuccessful';
            }
        }
        foreach ($orders as &$o) {
            $o['status'] = $o['_pending'] === 0 ? 'Completed' : 'Pending';
            $o['label'] = implode(', ', array_map(static fn (array $i): string => $i['name'] . ' × ' . $i['qty'], $o['items']));
            unset($o['_pending']);
        }
        unset($o);
        return array_values($orders);
    }

    /** @return array{0:string,1:string,2:int} [sql condition, mysqli type, bound value] selecting one order's lines */
    private function orderCondition(int $key): array
    {
        return $key > 0
            ? ['o.payment_id = ?', 'i', $key]
            : ['(o.payment_id IS NULL AND o.id = ?)', 'i', -$key];
    }

    // ---- student side ---------------------------------------------------

    /** @return list<array<string,mixed>> all orders of a student, newest first */
    public function forStudent(int $studentId): array
    {
        $rows = $this->db->all(
            'SELECT ' . self::LINE_SELECT . ' ' . self::LINE_JOIN . '
              WHERE o.student_id = ?
              ORDER BY o.order_time DESC, o.id DESC',
            'i',
            [$studentId]
        );
        return self::groupLines($rows);
    }

    public function recentForStudent(int $studentId, int $limit = 5): array
    {
        return array_slice($this->forStudent($studentId), 0, $limit);
    }

    public function studentTotals(int $studentId): array
    {
        $key = 'COALESCE(payment_id, -id)';
        return [
            'total'   => (int) $this->db->scalar("SELECT COUNT(DISTINCT $key) FROM orders WHERE student_id = ?", 'i', [$studentId]),
            'pending' => (int) $this->db->scalar("SELECT COUNT(DISTINCT $key) FROM orders WHERE student_id = ? AND status <> 'Completed'", 'i', [$studentId]),
        ];
    }

    public function favoriteDish(int $studentId): string
    {
        $name = $this->db->scalar(
            'SELECT m.name FROM orders o JOIN menu m ON o.dish_id = m.id
              WHERE o.student_id = ? AND ' . self::PAID . '
              GROUP BY o.dish_id, m.name
              ORDER BY SUM(o.quantity) DESC, m.name
              LIMIT 1',
            'i',
            [$studentId]
        );
        return $name === null ? 'N/A' : (string) $name;
    }

    /** One of the student's own orders, or null. */
    public function orderForStudent(int $studentId, int $key): ?array
    {
        [$cond, $type, $val] = $this->orderCondition($key);
        $rows = $this->db->all(
            'SELECT ' . self::LINE_SELECT . ' ' . self::LINE_JOIN . " WHERE o.student_id = ? AND $cond",
            'i' . $type,
            [$studentId, $val]
        );
        return $rows ? self::groupLines($rows)[0] : null;
    }

    // ---- admin side -----------------------------------------------------

    private const ADMIN_SELECT = 'u.username, u.email, ';
    private const ADMIN_JOIN = ' JOIN users u ON o.student_id = u.id ';

    public function adminCounts(): array
    {
        $key = 'COALESCE(payment_id, -id)';
        return [
            'total'     => (int) $this->db->scalar("SELECT COUNT(DISTINCT $key) FROM orders"),
            'pending'   => (int) $this->db->scalar("SELECT COUNT(DISTINCT $key) FROM orders WHERE status <> 'Completed'"),
            'completed' => (int) $this->db->scalar("SELECT COUNT(DISTINCT $key) FROM orders WHERE status = 'Completed'"),
        ];
    }

    public function revenue(): float
    {
        return (float) ($this->db->scalar(
            'SELECT SUM(' . self::LINE_TOTAL . ') FROM orders o JOIN menu m ON o.dish_id = m.id WHERE ' . self::PAID
        ) ?? 0);
    }

    /** @return list<array<string,mixed>> */
    public function allForAdmin(): array
    {
        $rows = $this->db->all(
            'SELECT ' . self::ADMIN_SELECT . self::LINE_SELECT . ' ' . self::LINE_JOIN . self::ADMIN_JOIN . '
              ORDER BY o.order_time DESC, o.id DESC'
        );
        return self::groupLines($rows);
    }

    public function recentForAdmin(int $limit = 5): array
    {
        return array_slice($this->allForAdmin(), 0, $limit);
    }

    public function orderForAdmin(int $key): ?array
    {
        [$cond, $type, $val] = $this->orderCondition($key);
        $rows = $this->db->all(
            'SELECT ' . self::ADMIN_SELECT . self::LINE_SELECT . ' ' . self::LINE_JOIN . self::ADMIN_JOIN . " WHERE $cond",
            $type,
            [$val]
        );
        return $rows ? self::groupLines($rows)[0] : null;
    }

    /** Sets the status of every line of one order. */
    public function updateStatus(int $key, string $status): void
    {
        if ($key > 0) {
            $this->db->run('UPDATE orders SET status = ? WHERE payment_id = ?', 'si', [$status, $key]);
        } else {
            $this->db->run('UPDATE orders SET status = ? WHERE payment_id IS NULL AND id = ?', 'si', [$status, -$key]);
        }
    }

    public function dishSales(): array
    {
        return $this->db->all(
            'SELECT m.name AS dish_name, SUM(o.quantity) AS total_sold
               FROM orders o JOIN menu m ON o.dish_id = m.id
              GROUP BY o.dish_id, m.name
              ORDER BY total_sold DESC'
        );
    }

    public function averageQuantity(): float
    {
        return (float) ($this->db->scalar('SELECT AVG(quantity) FROM orders') ?? 0);
    }
}
