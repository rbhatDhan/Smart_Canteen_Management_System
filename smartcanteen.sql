-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Oct 22, 2025 at 07:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smartcanteen`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `menu_day` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `name`, `price`, `menu_day`) VALUES
(49, 'Jeera Rice', 50.00, 'Monday'),
(50, 'Anna Sambar', 30.00, 'Monday'),
(51, 'Fried Rice', 60.00, 'Monday'),
(52, 'Peas Pulao', 55.00, 'Tuesday'),
(53, 'Anna Sambar', 30.00, 'Tuesday'),
(54, 'Fried Rice', 60.00, 'Tuesday'),
(55, 'Lemon Rice', 45.00, 'Wednesday'),
(56, 'Anna Sambar', 30.00, 'Wednesday'),
(57, 'Fried Rice', 60.00, 'Wednesday'),
(58, 'Bisi BeleBath', 55.00, 'Thursday'),
(59, 'Anna Sambar', 30.00, 'Thursday'),
(60, 'Fried Rice', 60.00, 'Thursday'),
(61, 'Veg Biriyani', 65.00, 'Friday'),
(62, 'Anna Sambar', 30.00, 'Friday'),
(63, 'Fried Rice', 60.00, 'Friday'),
(64, 'Ghee Rice', 60.00, 'Saturday'),
(65, 'Anna Sambar', 30.00, 'Saturday'),
(66, 'Fried Rice', 60.00, 'Saturday');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `order_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL,
  `usn` varchar(20) DEFAULT NULL,
  `dept` varchar(50) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `usn`, `dept`, `reset_token`, `reset_expires`) VALUES
(1, 'admin1', NULL, 'admin123', 'admin', NULL, NULL, NULL, NULL),
(2, 'admin2', NULL, 'secureadmin', 'admin', NULL, NULL, NULL, NULL),
(3, 'admin3', NULL, 'passAdmin', 'admin', NULL, NULL, NULL, NULL),
(4, 'student1', NULL, 'stud123', 'student', NULL, NULL, NULL, NULL),
(5, 'student2', NULL, 'pass456', 'student', NULL, NULL, NULL, NULL),
(6, 'student3', NULL, 'mypassword', 'student', NULL, NULL, NULL, NULL),
(7, 'student4', NULL, 'student789', 'student', NULL, NULL, NULL, NULL),
(8, 'student5', NULL, 'abc123', 'student', NULL, NULL, NULL, NULL),
(9, 'student6', NULL, 'xyz789', 'student', NULL, NULL, NULL, NULL),
(10, 'student7', NULL, 'hello123', 'student', NULL, NULL, NULL, NULL),
(11, 'student8', NULL, 'pass101', 'student', NULL, NULL, NULL, NULL),
(12, 'student9', NULL, 'qwerty', 'student', NULL, NULL, NULL, NULL),
(13, 'student10', NULL, 'student2025', 'student', NULL, NULL, NULL, NULL),
(14, 'thejashree', NULL, '12345', 'student', NULL, NULL, NULL, NULL),
(16, 'Kusuma', NULL, 'kusuma', 'student', '4GW23IS060', 'ISE', NULL, NULL),
(17, 'Varshini', NULL, 'varshini', 'student', '4GW23IS059', 'ISE', NULL, NULL),
(19, 'Dhanya', 'rbhatdhanya@gmail.com', '$2y$10$nmEtFcoihtns7ZcVq4Bbqu8Zlsi5bFRIRx1lV4QhfZK8J90zmbAoi', 'student', '4GW23IS009', 'ISE', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `dish_id` (`dish_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `menu` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
