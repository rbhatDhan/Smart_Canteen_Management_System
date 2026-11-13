<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Ratings;

use SmartCanteen\Core\Auth;
use SmartCanteen\Core\Csrf;
use SmartCanteen\Core\Http;
use SmartCanteen\Modules\Orders\OrderRepository;

final class RatingController
{
    /** POST api/ratings_submit.php  {order: <order key>, stars: 1-5} */
    public function submit(): void
    {
        Auth::requireRoleJson('student');
        if (!Http::isPost()) {
            Http::json(['success' => false, 'message' => 'Method not allowed.'], 405);
        }
        if (!Csrf::valid()) {
            Http::json(['success' => false, 'message' => 'Session expired. Please refresh the page.'], 419);
        }

        $body  = Http::jsonBody();
        $key   = (int) ($body['order'] ?? 0);
        $stars = (int) ($body['stars'] ?? 0);
        if ($key === 0 || $stars < 1 || $stars > 5) {
            Http::json(['success' => false, 'message' => 'Please choose 1 to 5 stars.'], 422);
        }

        $order = (new OrderRepository())->orderForStudent(Auth::id(), $key);
        if (!$order) {
            Http::json(['success' => false, 'message' => 'Order not found.'], 404);
        }
        if ($order['status'] !== 'Completed') {
            Http::json(['success' => false, 'message' => 'You can rate an order once it is completed.'], 409);
        }

        $ratings = new RatingRepository();
        $ratings->save(Auth::id(), $key, $stars);
        $summary = $ratings->summary();
        Http::json(['success' => true, 'stars' => $stars, 'average' => $summary['average'], 'count' => $summary['count']]);
    }
}
