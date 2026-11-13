<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Menu;

use SmartCanteen\Core\Auth;
use SmartCanteen\Core\Csrf;
use SmartCanteen\Core\Http;
use SmartCanteen\Core\View;

final class MenuController
{
    private MenuRepository $menu;

    public function __construct()
    {
        $this->menu = new MenuRepository();
    }

    /** Public weekly menu (view_menu.php). */
    public function show(): void
    {
        View::render('menu/view_menu', [
            'menu'  => $this->menu->groupedByDay(),
            'days'  => MenuRepository::DAYS,
            'today' => date('l'),
        ]);
    }

    /** Admin menu editor (update_menu.php). */
    public function manage(): void
    {
        Auth::requireAdmin();

        if (Http::isPost()) {
            Csrf::require();
            if (isset($_POST['save_update'])) {
                $this->saveUpdate();
            } elseif (isset($_POST['add_menu'])) {
                $this->add();
            } elseif (isset($_POST['delete_menu'])) {
                $this->delete();
            }
            Http::redirect('update_menu.php');
        }

        View::render('admin/update_menu', [
            'menu'    => $this->menu->groupedByDay(),
            'days'    => MenuRepository::DAYS,
            'edit_id' => isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : null,
        ]);
    }

    private function saveUpdate(): void
    {
        $id    = (int) ($_POST['id'] ?? 0);
        $name  = trim((string) ($_POST['name'] ?? ''));
        $price = $_POST['price'] ?? '';
        if ($id > 0 && $name !== '' && mb_strlen($name) <= 100 && is_numeric($price) && (float) $price >= 0) {
            $this->menu->update($id, $name, (float) $price);
        }
    }

    private function add(): void
    {
        $name  = trim((string) ($_POST['name'] ?? ''));
        $price = $_POST['price'] ?? '';
        $day   = (string) ($_POST['menu_day'] ?? '');

        if ($name === '' || mb_strlen($name) > 100 || !is_numeric($price) || (float) $price < 0
            || !in_array($day, MenuRepository::DAYS, true)) {
            return;
        }
        if ($this->menu->exists($day, $name)) {
            $this->alertAndRedirect('warning', 'Duplicate Dish!', 'This dish already exists for ' . $day . '!');
        }
        $this->menu->add($name, (float) $price, $day);
        $this->alertAndRedirect('success', 'Success!', 'Dish added successfully!', 2000);
    }

    private function delete(): void
    {
        $id = (int) ($_POST['delete_id'] ?? 0);
        if ($id > 0 && !$this->menu->delete($id)) {
            $this->alertAndRedirect(
                'error',
                'Cannot delete',
                'This dish has past orders, so it cannot be deleted. Edit it instead.'
            );
        }
    }

    /** Same blank-page SweetAlert + redirect the original app showed. */
    private function alertAndRedirect(string $icon, string $title, string $text, ?int $timer = null): never
    {
        View::render('partials/swal_redirect', [
            'icon' => $icon, 'title' => $title, 'text' => $text, 'timer' => $timer, 'redirect' => 'update_menu.php',
        ]);
        exit;
    }
}
