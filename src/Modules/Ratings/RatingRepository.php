<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Ratings;

use SmartCanteen\Core\Database;

final class RatingRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    /** One rating per student per order; rating again replaces the previous one. */
    public function save(int $studentId, int $orderKey, int $stars): void
    {
        $this->db->run(
            'INSERT INTO ratings (student_id, order_key, stars) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE stars = VALUES(stars)',
            'iii',
            [$studentId, $orderKey, $stars]
        );
    }

    /** @return array<int,int> order_key => stars for one student */
    public function forStudent(int $studentId): array
    {
        $map = [];
        foreach ($this->db->all('SELECT order_key, stars FROM ratings WHERE student_id = ?', 'i', [$studentId]) as $r) {
            $map[(int) $r['order_key']] = (int) $r['stars'];
        }
        return $map;
    }

    /** @return array{average:?float,count:int} overall canteen rating from all students */
    public function summary(): array
    {
        $row = $this->db->one('SELECT AVG(stars) AS avg_stars, COUNT(*) AS n FROM ratings');
        $count = (int) ($row['n'] ?? 0);
        return [
            'average' => $count > 0 ? round((float) $row['avg_stars'], 1) : null,
            'count'   => $count,
        ];
    }
}
