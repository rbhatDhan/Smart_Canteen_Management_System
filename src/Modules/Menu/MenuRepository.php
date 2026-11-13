<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Menu;

use SmartCanteen\Core\Database;

final class MenuRepository
{
    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    private Database $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    /** @return list<array<string,mixed>> */
    public function forDay(string $day, ?int $limit = null): array
    {
        $sql = 'SELECT id, name, price FROM menu WHERE menu_day = ?';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        return $this->db->all($sql, 's', [$day]);
    }

    /** Today's dishes keyed by dish id - used to validate orders server-side. */
    public function forDayById(string $day): array
    {
        $out = [];
        foreach ($this->forDay($day) as $row) {
            $out[(int) $row['id']] = $row;
        }
        return $out;
    }

    /** @return array<string,list<array<string,mixed>>> menu grouped by weekday */
    public function groupedByDay(): array
    {
        $rows = $this->db->all(
            "SELECT * FROM menu ORDER BY FIELD(menu_day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), name"
        );
        $menu = [];
        foreach ($rows as $row) {
            $menu[$row['menu_day']][] = $row;
        }
        return $menu;
    }

    public function distinctDishCount(): int
    {
        return (int) ($this->db->scalar('SELECT COUNT(DISTINCT name) FROM menu') ?? 0);
    }

    public function exists(string $day, string $name): bool
    {
        return $this->db->one(
            'SELECT id FROM menu WHERE LOWER(menu_day) = LOWER(?) AND LOWER(name) = LOWER(?) LIMIT 1',
            'ss',
            [$day, $name]
        ) !== null;
    }

    public function add(string $name, float $price, string $day): void
    {
        $this->db->run('INSERT INTO menu (name, price, menu_day) VALUES (?, ?, ?)', 'sds', [$name, $price, $day]);
    }

    public function update(int $id, string $name, float $price): void
    {
        $this->db->run('UPDATE menu SET name = ?, price = ? WHERE id = ?', 'sdi', [$name, $price, $id]);
    }

    /** @return bool false when the dish can't be removed because past orders reference it */
    public function delete(int $id): bool
    {
        try {
            $this->db->run('DELETE FROM menu WHERE id = ?', 'i', [$id]);
            return true;
        } catch (\mysqli_sql_exception $ex) {
            if ($ex->getCode() === 1451) { // foreign key constraint
                return false;
            }
            throw $ex;
        }
    }
}
