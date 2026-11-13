-- Run AFTER 001. Adds: real "Member Since", real ratings, login attempt limiting.
-- MariaDB 10.4+ (XAMPP). Safe to run more than once. Back up first.

USE `smartcanteen`;

ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `created_at` timestamp NOT NULL DEFAULT current_timestamp();

-- Best-effort "member since" for existing accounts: the date of their first order, if they have one.
UPDATE `users` u
JOIN (SELECT student_id, MIN(order_time) AS first_order FROM `orders` GROUP BY student_id) f ON f.student_id = u.id
SET u.`created_at` = f.first_order
WHERE f.first_order < u.`created_at`;

CREATE TABLE IF NOT EXISTS `ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `order_key` int(11) NOT NULL,
  `stars` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rating_student_order` (`student_id`,`order_key`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(120) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_attempt_identifier` (`identifier`,`created_at`),
  KEY `idx_attempt_ip` (`ip`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
