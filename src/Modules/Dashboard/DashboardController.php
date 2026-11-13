<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Dashboard;

use SmartCanteen\Core\Auth;
use SmartCanteen\Core\View;
use SmartCanteen\Modules\Menu\MenuRepository;
use SmartCanteen\Modules\Orders\OrderRepository;

final class DashboardController
{
    private OrderRepository $orders;
    private MenuRepository $menu;

    public function __construct()
    {
        $this->orders = new OrderRepository();
        $this->menu   = new MenuRepository();
    }

    /** index.php - public landing page. */
    public function landing(): void
    {
        View::render('home/index', ['menuItems' => $this->menu->forDay(date('l'), 4)]);
    }

    public function student(): void
    {
        Auth::requireStudent();
        $totals = $this->orders->studentTotals(Auth::id());
        View::render('student/dashboard', [
            'username'      => $_SESSION['username'],
            'totalOrders'   => $totals['total'],
            'pendingOrders' => $totals['pending'],
            'menuCount'     => $this->menu->distinctDishCount(),
            'rating'        => (new \SmartCanteen\Modules\Ratings\RatingRepository())->summary(),
            'recentOrders'  => $this->orders->recentForStudent(Auth::id(), 5),
        ]);
    }

    public function admin(): void
    {
        Auth::requireAdmin();
        $counts = $this->orders->adminCounts();
        View::render('admin/dashboard', [
            'dishCount'    => $this->menu->distinctDishCount(),
            'orderCount'   => $counts['total'],
            'pendingCount' => $counts['pending'],
            'revenue'      => $this->orders->revenue(),
            'recentOrders' => $this->orders->recentForAdmin(5),
            'menuItems'    => $this->menu->forDay(date('l'), 5),
        ]);
    }

    public function statistics(): void
    {
        Auth::requireAdmin();
        $sales = $this->orders->dishSales();
        View::render('admin/statistics', [
            'dishNames'    => array_column($sales, 'dish_name'),
            'quantities'   => array_map('intval', array_column($sales, 'total_sold')),
            'totalOrders'  => $this->orders->adminCounts()['total'],
            'totalRevenue' => $this->orders->revenue(),
            'avgItems'     => $this->orders->averageQuantity(),
        ]);
    }
}
