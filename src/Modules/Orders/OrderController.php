<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Orders;

use SmartCanteen\Core\Auth;
use SmartCanteen\Core\Csrf;
use SmartCanteen\Core\Env;
use SmartCanteen\Core\Http;
use SmartCanteen\Core\View;
use SmartCanteen\Modules\Menu\MenuRepository;
use SmartCanteen\Modules\Notifications\Mailer;

final class OrderController
{
    public const STATUSES = ['Pending', 'Completed'];

    private OrderRepository $orders;

    public function __construct()
    {
        $this->orders = new OrderRepository();
    }

    /** place_order.php - the student's order + Razorpay checkout page. */
    public function placeOrder(): void
    {
        Auth::requireStudent('student_login.php?error=' . rawurlencode('Please login first'));
        $today = date('l');
        View::render('student/place_order', [
            'selected_day' => $today,
            'paid'         => ($_GET['paid'] ?? '') === '1',
            'dishes'       => (new MenuRepository())->forDay($today),
            'payConfig'    => [
                'csrf'      => Csrf::token(),
                'createUrl' => 'api/payments_create.php',
                'verifyUrl' => 'api/payments_verify.php',
                'failedUrl' => 'api/payments_failed.php',
                'keyReady'  => (bool) Env::get('RAZORPAY_KEY_ID') && (bool) Env::get('RAZORPAY_KEY_SECRET'),
            ],
        ]);
    }

    /** order_history.php - every order of the logged-in student (pending and completed). */
    public function history(): void
    {
        Auth::requireStudent();
        $user = (new \SmartCanteen\Modules\Auth\UserRepository())->findById(Auth::id());
        if (!$user) {
            exit('<p>User not found.</p>');
        }

        View::render('student/order_history', [
            'username'     => $user['username'],
            'orders'       => $this->orders->forStudent(Auth::id()),
            'ratings'      => (new \SmartCanteen\Modules\Ratings\RatingRepository())->forStudent(Auth::id()),
            'totalOrders'  => $this->orders->studentTotals(Auth::id())['total'],
            'favoriteDish' => $this->orders->favoriteDish(Auth::id()),
            'memberSince'  => date('Y', strtotime((string) ($user['created_at'] ?? 'now'))),
            'csrf'         => Csrf::token(),
        ]);
    }

    /** view_orders.php - admin order management table (one row per order). */
    public function adminList(): void
    {
        Auth::requireAdmin();
        View::render('admin/view_orders', [
            'counts' => $this->orders->adminCounts(),
            'rows'   => $this->orders->allForAdmin(),
            'csrf'   => Csrf::token(),
        ]);
    }

    /** POST api/orders_update_status.php (admin only, JSON). */
    public function updateStatusApi(): void
    {
        Auth::requireRoleJson('admin');
        if (!Http::isPost()) {
            Http::json(['success' => false, 'message' => 'Method not allowed.'], 405);
        }
        if (!Csrf::valid()) {
            Http::json(['success' => false, 'message' => 'Session expired. Refresh the page.'], 419);
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? ''));
        if ($id === 0 || !in_array($status, self::STATUSES, true)) {
            Http::json(['success' => false, 'message' => 'Invalid data.'], 400);
        }

        $order = $this->orders->orderForAdmin($id);
        if (!$order) {
            Http::json(['success' => false, 'message' => 'Order not found.'], 404);
        }
        if ($order['payment_status'] === 'Unsuccessful') {
            Http::json(['success' => false, 'message' => 'Payment was not successful for this order.'], 409);
        }

        $changed = $order['status'] !== $status;
        $this->orders->updateStatus($id, $status);

        $mailed = false;
        if ($changed && $status === 'Completed' && !empty($order['email'])) {
            $name = e($order['username']);
            $body = "<p>Hi <b>{$name}</b>,</p>"
                  . '<p>Your order <b>#' . e($order['number']) . '</b> (' . e($order['label']) . ') is <b>Completed</b> - please collect it from the counter.</p>'
                  . '<p>Thank you for using Smart Canteen!</p><hr>'
                  . '<small>This is an automated notification from the Smart Canteen system.</small>';
            $mailed = (new Mailer())->send((string) $order['email'], (string) $order['username'], 'Order #' . $order['number'] . ' Completed ✅', $body);
        }
        Http::json(['success' => true, 'emailed' => $mailed]);
    }
}
