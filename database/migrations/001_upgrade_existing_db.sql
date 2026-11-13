-- Upgrade an EXISTING smartcanteen database (the one your current app uses) for the new version.
-- Safe to run more than once on MariaDB 10.4+ (XAMPP). On MySQL, remove the "IF NOT EXISTS" parts
-- for columns/indexes that already exist, or run the statements one by one in phpMyAdmin.
-- Take a backup first (phpMyAdmin > Export).

USE `smartcanteen`;

-- 1. Columns the old code already relied on (missing from the old smartcanteen.sql) + new ones.
ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `status` varchar(20) NOT NULL DEFAULT 'Pending',
  ADD COLUMN IF NOT EXISTS `payment_status` varchar(20) NOT NULL DEFAULT 'Successful',
  ADD COLUMN IF NOT EXISTS `usn` varchar(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `unit_price` decimal(10,2) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `payment_id` int(11) DEFAULT NULL;

-- 2. Payments table (Razorpay).
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

-- 3. Prevent the same payment from creating the same order line twice.
ALTER TABLE `orders` ADD UNIQUE KEY IF NOT EXISTS `uq_orders_payment_dish` (`payment_id`,`dish_id`);
ALTER TABLE `orders` ADD KEY IF NOT EXISTS `status` (`status`);

-- 4. Backfill data for old orders.
UPDATE `orders` o JOIN `users` u ON o.`student_id` = u.`id` SET o.`usn` = u.`usn` WHERE o.`usn` IS NULL;
UPDATE `orders` SET `payment_status` = 'Successful' WHERE `payment_status` IS NULL OR `payment_status` = '';

-- Passwords: nothing to do. Old plain-text passwords (admin accounts, seeded students) keep working
-- and are automatically converted to secure hashes the first time each person logs in.
