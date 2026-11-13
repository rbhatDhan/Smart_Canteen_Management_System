-- Smart Canteen - fresh install schema (MariaDB 10.4+ / MySQL 5.7+).
-- Existing database? Use database/migrations/001_upgrade_existing_db.sql instead.

CREATE DATABASE IF NOT EXISTS `smartcanteen` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `smartcanteen`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL,
  `usn` varchar(20) DEFAULT NULL,
  `dept` varchar(50) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `menu_day` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_day` (`menu_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- One row per Razorpay order. The cart is stored server-side so the browser can never change what is charged/ordered.
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `razorpay_order_id` varchar(64) NOT NULL,
  `razorpay_payment_id` varchar(64) DEFAULT NULL,
  `razorpay_signature` varchar(128) DEFAULT NULL,
  `amount_paise` int(11) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'INR',
  `receipt` varchar(40) NOT NULL,
  `status` enum('created','paid','failed') NOT NULL DEFAULT 'created',
  `cart_json` text NOT NULL,
  `failure_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payments_rzp_order` (`razorpay_order_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `order_time` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `payment_status` varchar(20) NOT NULL DEFAULT 'Successful',
  `usn` varchar(20) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_payment_dish` (`payment_id`,`dish_id`),
  KEY `student_id` (`student_id`),
  KEY `dish_id` (`dish_id`),
  KEY `status` (`status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `menu` (`id`),
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- One rating (1-5 stars) per student per order. order_key = payment id of the order
-- (or the negative order-line id for orders placed before payments existed).
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

-- Failed/successful login attempts, used to lock brute-force guessing.
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

INSERT INTO `menu` (`name`, `price`, `menu_day`) VALUES
('Jeera Rice', 50.00, 'Monday'),    ('Anna Sambar', 30.00, 'Monday'),    ('Fried Rice', 60.00, 'Monday'),
('Peas Pulao', 55.00, 'Tuesday'),   ('Anna Sambar', 30.00, 'Tuesday'),   ('Fried Rice', 60.00, 'Tuesday'),
('Lemon Rice', 45.00, 'Wednesday'), ('Anna Sambar', 30.00, 'Wednesday'), ('Fried Rice', 60.00, 'Wednesday'),
('Bisi BeleBath', 55.00, 'Thursday'),('Anna Sambar', 30.00, 'Thursday'), ('Fried Rice', 60.00, 'Thursday'),
('Veg Biriyani', 65.00, 'Friday'),  ('Anna Sambar', 30.00, 'Friday'),    ('Fried Rice', 60.00, 'Friday'),
('Ghee Rice', 60.00, 'Saturday'),   ('Anna Sambar', 30.00, 'Saturday'),  ('Fried Rice', 60.00, 'Saturday');

-- No default users are created. See README "Create your first admin".
