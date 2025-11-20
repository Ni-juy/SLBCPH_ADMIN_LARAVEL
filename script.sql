-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 20, 2025 at 10:26 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34


CREATE DATABASE churchdatabases;
USE churchdatabases;


-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2025 at 11:32 AM
-- Server version: 8.0.41
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `churchdatabases`
--

-- --------------------------------------------------------

--
-- Table structure for table `allocated_funds`
--

CREATE TABLE `allocated_funds` (
  `id` int NOT NULL,
  `branch_id` int NOT NULL,
  `expense_category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `allocated_amount` decimal(10,2) NOT NULL,
  `allocation_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `branch_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `extension_of` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `branch_type`, `is_archived`, `created_at`, `updated_at`, `extension_of`) VALUES
(1, 'San Pedro', 'B21 L4 Galatians St. Adelina1, San Antonio, San Pedro Laguna.', 'Main', 0, '2025-07-11 21:38:50', '2025-07-12 05:39:07', NULL),
(2, 'Calauan', 'FCBA Silangan Subd, Brgy. Dayap, Calauan, Laguna.', 'Organized', 0, '2025-06-22 06:38:13', '2025-07-26 04:12:33', NULL),
(3, 'Calamba (Mauro)', 'Block 1 Lot 4, Brgy. Bañadero, Calamba City, Laguna.', 'Organized', 0, '2025-06-22 02:16:35', '2025-07-11 07:42:37', NULL),
(9, 'Calamba (Banadero)', 'Blk 13 Lot 30 BRIA Homes, Brgy Bañadero, Calamba City, Laguna.', 'Organized', 0, '2025-06-30 03:37:46', '2025-07-11 07:42:13', NULL),
(81, 'testings', 'B21 L4 Galatians St. Adelina1, San Antonio, San Pedro Laguna.s', 'Mission', 1, '2025-09-11 00:32:16', '2025-09-11 01:03:23', NULL),
(83, 'adasd', 'adsa', 'Organized', 0, '2025-09-11 01:17:28', '2025-09-11 01:17:48', NULL),
(85, 'ter', 'ter', 'Extension', 0, '2025-10-14 01:04:03', '2025-10-14 01:04:03', 1);

-- --------------------------------------------------------

--
-- Table structure for table `branch_transfer_requests`
--

CREATE TABLE `branch_transfer_requests` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `current_branch_id` int NOT NULL,
  `requested_branch_id` int NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` enum('pending','forwarded','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_transfer_requests`
--

INSERT INTO `branch_transfer_requests` (`id`, `user_id`, `current_branch_id`, `requested_branch_id`, `reason`, `status`, `created_at`, `updated_at`) VALUES
(24, 54, 1, 2, NULL, 'approved', '2025-07-22 06:28:59', '2025-07-22 06:29:30'),
(25, 54, 2, 1, NULL, 'approved', '2025-07-22 20:42:01', '2025-07-22 20:45:35'),
(26, 54, 1, 2, NULL, 'approved', '2025-07-22 20:47:02', '2025-07-22 20:47:23'),
(28, 54, 2, 1, NULL, 'approved', '2025-07-23 23:36:20', '2025-07-23 23:38:29'),
(29, 54, 1, 2, NULL, 'approved', '2025-07-23 23:40:52', '2025-07-23 23:41:19'),
(30, 54, 2, 1, NULL, 'approved', '2025-07-23 23:43:59', '2025-07-23 23:44:54'),
(31, 54, 1, 2, NULL, 'approved', '2025-07-24 01:38:32', '2025-07-24 01:38:54'),
(32, 2, 1, 2, NULL, 'rejected', '2025-07-25 00:09:58', '2025-07-25 00:10:56'),
(33, 54, 1, 2, NULL, 'approved', '2025-07-27 21:49:26', '2025-07-27 22:18:50'),
(34, 2, 1, 2, 'Test', 'rejected', '2025-08-22 00:19:15', '2025-08-22 00:28:09'),
(35, 2, 1, 2, 'Test', 'approved', '2025-08-22 00:27:11', '2025-08-22 01:21:46'),
(36, 2, 2, 1, 'opo', 'approved', '2025-08-26 03:18:13', '2025-08-26 03:19:06'),
(37, 2, 1, 3, 'test', 'approved', '2025-08-29 01:27:37', '2025-08-29 01:28:21'),
(39, 2, 1, 2, 'test', 'rejected', '2025-08-31 04:13:23', '2025-08-31 04:14:07'),
(40, 2, 1, 2, 'TEST', 'approved', '2025-08-31 04:16:28', '2025-08-31 04:37:53'),
(41, 2, 1, 3, 'asdsad', 'approved', '2025-09-02 23:27:17', '2025-09-02 23:28:27'),
(46, 2, 2, 1, 'adad', 'rejected', '2025-09-03 01:59:22', '2025-09-03 22:26:09'),
(47, 2, 1, 2, 'dwad', 'approved', '2025-09-03 22:26:27', '2025-09-03 22:27:48'),
(48, 2, 1, 2, 'fef', 'approved', '2025-09-03 22:28:53', '2025-09-03 22:34:05'),
(49, 2, 1, 2, NULL, 'approved', '2025-09-08 23:45:28', '2025-09-08 23:45:56'),
(50, 2, 2, 1, NULL, 'approved', '2025-09-08 23:59:47', '2025-09-09 00:00:26');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `id` int UNSIGNED NOT NULL,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expiration` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`id`, `key`, `value`, `expiration`) VALUES
(146, 'laravel_cache_adminlogin:superadmin|::1:tries', 'i:2;', 1763533902);

-- --------------------------------------------------------

--
-- Table structure for table `church_services`
--

CREATE TABLE `church_services` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch_id` int NOT NULL,
  `day_of_week` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Sunday',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `church_services`
--

INSERT INTO `church_services` (`id`, `title`, `branch_id`, `day_of_week`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(1, 'Sundayservice', 1, 'Tuesday', '12:01:00', '13:00:00', '2025-07-15 23:25:39', '2025-11-11 05:09:48'),
(2, 'test', 2, 'Sunday', '10:00:00', '12:00:00', '2025-07-26 03:48:15', '2025-09-03 22:42:00');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `offering_id` int DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT '0.00',
  `date` date NOT NULL,
  `branch_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `parent_donation_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `user_id`, `offering_id`, `amount`, `date`, `branch_id`, `created_at`, `updated_at`, `parent_donation_id`) VALUES
(753, 31, NULL, 300.00, '2025-11-20', 1, '2025-11-19 23:43:44', '2025-11-19 23:43:44', NULL),
(754, 31, 101, 100.00, '2025-11-20', 1, '2025-11-19 23:43:44', '2025-11-19 23:43:44', 753),
(755, 31, 102, 100.00, '2025-11-20', 1, '2025-11-19 23:43:44', '2025-11-19 23:43:44', 753),
(756, 31, 103, 100.00, '2025-11-20', 1, '2025-11-19 23:43:44', '2025-11-19 23:43:44', 753);

-- --------------------------------------------------------

--
-- Table structure for table `donation_allocations`
--

CREATE TABLE `donation_allocations` (
  `id` int NOT NULL,
  `donation_id` int NOT NULL,
  `partition_id` int NOT NULL,
  `allocated_amount` decimal(10,2) DEFAULT '0.00',
  `allocation_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation_allocations`
--

INSERT INTO `donation_allocations` (`id`, `donation_id`, `partition_id`, `allocated_amount`, `allocation_date`, `created_at`, `updated_at`) VALUES
(1116, 753, 77, 84.00, '2025-11-20', '2025-11-19 23:43:44', '2025-11-19 23:43:44'),
(1117, 753, 92, 63.00, '2025-11-20', '2025-11-19 23:43:44', '2025-11-19 23:43:44'),
(1118, 753, 93, 63.00, '2025-11-20', '2025-11-19 23:43:44', '2025-11-19 23:43:44'),
(1119, 753, 96, 90.00, '2025-11-20', '2025-11-19 23:43:44', '2025-11-19 23:43:44'),
(1120, 753, 97, 10.00, '2025-11-20', '2025-11-19 23:43:44', '2025-11-19 23:43:44');

-- --------------------------------------------------------

--
-- Table structure for table `donation_confirmations`
--

CREATE TABLE `donation_confirmations` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reference_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `branch_id` int UNSIGNED NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation_confirmations`
--

INSERT INTO `donation_confirmations` (`id`, `name`, `reference_number`, `amount`, `message`, `branch_id`, `image_path`, `is_verified`, `created_at`, `updated_at`) VALUES
(17, 'Juan DelaCruz', '1234546789012', 100.00, 'adas', 21, NULL, 0, '2025-07-22 23:05:27', '2025-07-22 23:05:27');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plain_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `user_id`, `token`, `plain_password`, `verified_at`, `created_at`, `updated_at`) VALUES
(6, 101, 'UV7g13WbaMzaVx1j9zs8vR5PdSDzNtS0dcpt0OWqGQBByBbGOSlkTBcsQZbo', 'x22k08FQhC', '2025-09-16 00:40:22', '2025-09-16 00:38:06', '2025-09-16 08:40:22');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int NOT NULL,
  `branch_id` int DEFAULT NULL,
  `is_global` tinyint(1) DEFAULT '0',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `event_date` date NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('upcoming','ongoing','finished') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'upcoming'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `branch_id`, `is_global`, `title`, `description`, `location`, `event_date`, `start_date`, `end_date`, `start_time`, `end_time`, `created_by`, `created_at`, `updated_at`, `status`) VALUES
(361, 1, 0, 'LAST TEST', NULL, 'TEST', '2025-11-12', NULL, NULL, '18:40:00', '18:42:00', 3, '2025-11-12 02:39:04', '2025-11-20 02:14:04', 'finished'),
(362, 1, 0, 'ULO<', NULL, 'sds', '2025-11-12', NULL, NULL, '19:00:00', '19:01:00', 3, '2025-11-12 02:59:31', '2025-11-20 02:14:04', 'finished'),
(363, 1, 0, 'teret', 'sfsdfsdfsfsdfsdfsdff', 'dfsdfdsfsfsfds', '2025-11-15', NULL, NULL, '16:54:00', '16:59:00', 3, '2025-11-15 00:55:01', '2025-11-20 02:14:04', 'finished'),
(364, 1, 0, 'tea', 're', 're', '2025-11-16', NULL, NULL, '13:23:00', '13:24:00', 3, '2025-11-15 21:23:34', '2025-11-20 02:14:04', 'finished'),
(366, 1, 0, 'ww', NULL, 'dw', '2025-11-16', NULL, NULL, '13:26:00', '13:27:00', 3, '2025-11-15 21:23:59', '2025-11-20 02:14:04', 'finished'),
(368, 1, 0, 'test', 'ewrwer', 'rwerewr', '2025-11-28', NULL, NULL, '16:06:00', '16:08:00', 3, '2025-11-20 00:05:51', '2025-11-20 02:14:04', 'upcoming'),
(372, 1, 0, 'sd', 'asdasd', 'saddsa', '2025-11-21', NULL, NULL, '18:07:00', '19:07:00', 3, '2025-11-20 02:07:27', '2025-11-20 02:14:04', 'upcoming'),
(373, NULL, 1, 'dasd', 'asdads', 'asda', '2025-11-22', NULL, NULL, '18:12:00', '20:12:00', 3, '2025-11-20 02:12:10', '2025-11-20 02:14:04', 'upcoming'),
(374, 2, 0, 'adsa', 'asdsa', 'asdsa', '2025-11-21', NULL, NULL, '18:13:00', '19:13:00', 47, '2025-11-20 02:13:15', '2025-11-20 02:14:04', 'upcoming');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(10,2) DEFAULT '0.00',
  `branch_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `description`, `amount`, `branch_id`, `created_at`, `updated_at`) VALUES
(38, 'GENERAL', 0.00, 1, '2025-09-06 07:09:03', '2025-09-10 05:16:13'),
(39, 'UTILITY', 0.00, 1, '2025-09-06 07:09:03', '2025-09-06 07:09:03'),
(40, 'STANDBY', 0.00, 1, '2025-09-06 07:10:28', '2025-09-06 07:10:28'),
(41, 'PASTOR\'S SUPPORT', 0.00, 1, '2025-09-06 07:11:04', '2025-09-06 07:11:04'),
(43, 'GENERAL', 0.00, 2, '2025-09-08 23:50:49', '2025-09-08 23:50:49'),
(44, 'UTILITY', 0.00, 2, '2025-09-08 23:52:05', '2025-09-08 23:52:05'),
(45, 'STANDBY', 0.00, 2, '2025-09-08 23:52:05', '2025-09-08 23:52:05'),
(46, 'PASTOR\'S SUPPORT', 0.00, 2, '2025-09-08 23:52:05', '2025-09-08 23:52:05'),
(47, 'TIthes of Tithes', 0.00, 2, '2025-09-08 23:52:05', '2025-09-08 23:52:05');

-- --------------------------------------------------------

--
-- Table structure for table `faith_tracks`
--

CREATE TABLE `faith_tracks` (
  `id` int UNSIGNED NOT NULL,
  `branch_id` int UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_shared` date NOT NULL,
  `tracks_given` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` enum('faith','track') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'faith'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faith_tracks`
--

INSERT INTO `faith_tracks` (`id`, `branch_id`, `name`, `address`, `contact_number`, `date_shared`, `tracks_given`, `created_at`, `updated_at`, `type`) VALUES
(9, NULL, 'Juan DelaCruz', 'Calamba Laguna', '09218916436', '2025-07-01', NULL, '2025-07-11 20:45:15', '2025-07-11 20:45:15', 'faith'),
(10, NULL, NULL, NULL, NULL, '2025-07-01', 4, '2025-07-11 20:45:28', '2025-07-11 20:45:28', 'track'),
(11, 1, 'Juan DelaCruz', 'Calamba Laguna', '09218916436', '2025-07-01', NULL, '2025-07-11 20:49:15', '2025-07-11 20:49:15', 'faith'),
(12, 1, NULL, NULL, NULL, '2025-07-01', 10, '2025-07-11 20:49:28', '2025-07-11 20:49:28', 'track'),
(13, 1, NULL, NULL, NULL, '2025-07-01', 12, '2025-07-11 21:01:10', '2025-07-11 21:01:10', 'track'),
(14, 1, 'Eugene Villanueva San Joaquin', 'Calamba Laguna', '09218916436', '2025-07-12', NULL, '2025-07-11 21:01:36', '2025-07-11 21:01:36', 'faith'),
(16, 1, NULL, NULL, NULL, '2025-07-24', 25, '2025-07-24 06:43:47', '2025-07-24 06:43:47', 'track'),
(17, 1, NULL, NULL, NULL, '2025-07-23', 14, '2025-07-24 06:43:59', '2025-07-24 06:43:59', 'track'),
(20, 1, 'Eugene V. San Joaquin', 'were', '0942545943', '2025-07-31', NULL, '2025-07-31 02:02:23', '2025-07-31 02:02:23', 'faith'),
(21, 1, 'Eugene V. San Joaquin', 'were', '094254', '2025-07-31', NULL, '2025-07-31 02:02:30', '2025-07-31 02:02:30', 'faith'),
(23, 1, 'EUgene', 'here', '09876543211', '2025-08-13', NULL, '2025-08-13 04:37:15', '2025-09-05 23:56:09', 'faith'),
(24, 1, 'eugene', 'herew', '09876543212', '2025-08-13', NULL, '2025-08-13 04:37:33', '2025-08-13 04:37:33', 'faith'),
(25, 1, 'eugeneww', 'herewww', '09876543219', '2025-08-13', NULL, '2025-08-13 04:38:17', '2025-09-05 23:56:47', 'faith'),
(26, 1, 'testing', 'test', '09218916436', '2025-08-22', NULL, '2025-08-22 00:35:39', '2025-09-06 05:11:24', 'faith'),
(27, 1, NULL, NULL, NULL, '2025-08-22', 10, '2025-08-22 00:35:51', '2025-08-22 00:35:51', 'track'),
(28, 1, NULL, NULL, NULL, '2025-08-26', 10, '2025-08-26 10:21:32', '2025-08-26 10:21:32', 'track'),
(31, 2, 'Eugene Villanueva San Joaquin', 'Calamba Laguna', '09218916436', '2025-09-03', NULL, '2025-09-03 01:44:26', '2025-09-03 01:44:26', 'faith'),
(32, 2, NULL, NULL, NULL, '2025-09-03', 20, '2025-09-03 01:44:43', '2025-09-03 01:44:43', 'track'),
(33, 1, 'dwada', 'wdawdaw', '09876543211', '2025-09-03', NULL, '2025-09-03 21:46:26', '2025-09-05 23:58:00', 'faith'),
(34, 1, NULL, NULL, NULL, '2025-09-04', 130, '2025-09-03 21:46:46', '2025-09-06 00:07:27', 'track'),
(36, 1, 'ayoko', 'wdawdaw', '09876543211', '2025-09-05', NULL, '2025-09-05 23:59:53', '2025-09-06 05:11:35', 'faith'),
(37, 1, 'Eugene Villanueva San Joaquin', 'Calamba Laguna', '09218916436', '2025-10-01', NULL, '2025-10-01 02:38:08', '2025-10-01 02:38:08', 'faith'),
(38, 1, NULL, NULL, NULL, '2025-10-01', 12, '2025-10-01 02:38:22', '2025-10-01 02:38:22', 'track'),
(39, 1, 'sample', 'sample', '09212321211', '2025-10-01', NULL, '2025-10-01 03:02:37', '2025-10-01 03:02:37', 'faith'),
(40, 1, NULL, NULL, NULL, '2025-10-01', 10, '2025-10-01 03:03:09', '2025-10-01 03:03:09', 'track');

-- --------------------------------------------------------

--
-- Table structure for table `financial_allocation_rules`
--

CREATE TABLE `financial_allocation_rules` (
  `id` int NOT NULL,
  `branch_id` int NOT NULL,
  `expense_category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fund_expenses`
--

CREATE TABLE `fund_expenses` (
  `id` int NOT NULL,
  `allocation_id` int NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `image` text COLLATE utf8mb4_general_ci,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_details`
--

CREATE TABLE `member_details` (
  `id` int NOT NULL,
  `member_id` int NOT NULL,
  `marital_status` enum('Single','Married','Widowed','Divorced') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `occupation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergency_contact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergency_contact_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_03_24_120505_create_sessions_table', 1),
(3, '2025_03_24_134240_create_personal_access_tokens_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `offerings`
--

CREATE TABLE `offerings` (
  `id` int NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `parent_id` int DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT '0.00',
  `branch_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offerings`
--

INSERT INTO `offerings` (`id`, `category`, `parent_id`, `amount`, `branch_id`, `user_id`, `created_at`, `updated_at`) VALUES
(70, 'TITHES', NULL, 0.00, 2, 47, '2025-09-08 23:54:39', '2025-09-08 23:54:39'),
(71, 'LOVE', NULL, 0.00, 2, 47, '2025-09-08 23:54:39', '2025-09-08 23:54:39'),
(72, 'LOOSE', NULL, 0.00, 2, 47, '2025-09-08 23:54:39', '2025-09-08 23:54:39'),
(73, 'Tithes of Tithes', 70, 0.00, 2, 47, '2025-09-08 23:54:39', '2025-09-08 23:54:46'),
(101, 'TITHES', NULL, 0.00, 1, 3, '2025-11-19 23:23:01', '2025-11-19 23:23:01'),
(102, 'LOVE', NULL, 0.00, 1, 3, '2025-11-19 23:23:01', '2025-11-19 23:23:01'),
(103, 'LOOSE', NULL, 0.00, 1, 3, '2025-11-19 23:23:01', '2025-11-19 23:23:01'),
(104, 'Tithes of Tithes', 101, 0.00, 1, 3, '2025-11-19 23:23:15', '2025-11-19 23:23:15');

-- --------------------------------------------------------

--
-- Table structure for table `otp_verifications`
--

CREATE TABLE `otp_verifications` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `otp_code` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_verifications`
--

INSERT INTO `otp_verifications` (`id`, `email`, `otp_code`, `expires_at`, `created_at`, `updated_at`) VALUES
(10, 'ramelsales22@gmail.com', '637422', '2025-06-30 22:17:24', '2025-07-01 06:07:24', '2025-07-01 06:07:24');

-- --------------------------------------------------------

--
-- Table structure for table `partitions`
--

CREATE TABLE `partitions` (
  `id` int NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `partition` int NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `branch_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `amount` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partitions`
--

INSERT INTO `partitions` (`id`, `category`, `partition`, `description`, `branch_id`, `created_at`, `updated_at`, `amount`) VALUES
(56, 'GENERAL', 40, '40% of total (LOVE, LOOSE, Tithes of Tithes)', 2, '2025-07-26 03:35:33', '2025-07-26 03:35:33', 0.00),
(57, 'UTILITY', 30, '30% of total (LOVE, LOOSE, Tithes of Tithes)', 2, '2025-07-26 03:35:33', '2025-07-26 03:35:33', 0.00),
(58, 'STANDBY', 30, '30% of total (LOVE, LOOSE, Tithes of Tithes)', 2, '2025-07-26 03:35:33', '2025-07-26 03:35:33', 0.00),
(59, 'PASTOR\'S SUPPORT', 90, '90% of total (TITHES)', 2, '2025-07-26 03:35:33', '2025-07-26 03:35:33', 0.00),
(60, 'Tithes of Tithes', 10, '10% of total (TITHES)', 2, '2025-07-26 03:35:33', '2025-07-26 03:35:33', 0.00),
(77, 'GENERAL', 40, '40% of total (LOVE, LOOSE, Tithes of Tithes)', 1, '2025-09-10 05:13:49', '2025-11-19 23:42:03', 0.00),
(92, 'UTILITY', 30, '30% of total (LOVE, LOOSE, Tithes of Tithes)', 1, '2025-11-19 23:25:03', '2025-11-19 23:42:03', 0.00),
(93, 'STANDBY', 30, '30% of total (LOVE, LOOSE, Tithes of Tithes)', 1, '2025-11-19 23:25:03', '2025-11-19 23:42:03', 0.00),
(96, 'PASTOR\'S SUPPORT', 90, '90% of total (TITHES)', 1, '2025-11-19 23:42:03', '2025-11-19 23:42:03', 0.00),
(97, 'Tithes of Tithes', 10, '10% of total (TITHES)', 1, '2025-11-19 23:42:03', '2025-11-19 23:42:03', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `partition_offering`
--

CREATE TABLE `partition_offering` (
  `id` int NOT NULL,
  `partition_id` int NOT NULL,
  `offering_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partition_offering`
--

INSERT INTO `partition_offering` (`id`, `partition_id`, `offering_id`, `created_at`, `updated_at`) VALUES
(264, 77, 104, '2025-11-20 07:23:39', '2025-11-20 07:23:39'),
(275, 93, 102, '2025-11-20 07:26:09', '2025-11-20 07:26:09'),
(276, 93, 103, '2025-11-20 07:26:09', '2025-11-20 07:26:09'),
(277, 93, 104, '2025-11-20 07:26:09', '2025-11-20 07:26:09'),
(280, 77, 102, '2025-11-20 07:42:03', '2025-11-20 07:42:03'),
(281, 77, 103, '2025-11-20 07:42:03', '2025-11-20 07:42:03'),
(282, 92, 102, '2025-11-20 07:42:03', '2025-11-20 07:42:03'),
(283, 92, 103, '2025-11-20 07:42:03', '2025-11-20 07:42:03'),
(284, 92, 104, '2025-11-20 07:42:03', '2025-11-20 07:42:03'),
(285, 96, 101, '2025-11-20 07:42:03', '2025-11-20 07:42:03'),
(286, 97, 101, '2025-11-20 07:42:03', '2025-11-20 07:42:03');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('thunderhiper22@gmail.com', '5PKmzn7vNRJn1uGIxm3nz0Ikd2BNHQwGlqsrAjE2NedAMefTLilt7yJloIRi', '2025-06-08 23:03:59'),
('thunderhiper22@gmail.com', 'aHBVi8qxjDvo7D49iUKc6Jce2ZutLVB6Z5FpiKso5AGqWXXsI2HqZYLc3DOW', '2025-06-08 23:17:33'),
('thunderhiper22@gmail.com', 'Blhy8Vx3Hy7nICn3Ng3Z8hXcfRPCOC8eu5BgE1qtg01Q30VUhyDrONJ4uxBa', '2025-06-08 22:55:49'),
('thunderhiper22@gmail.com', 'dpUVvQHbYyL8Js8VDd5T8pJfeJ40og9kAEczBBk7hyxaEZIFEFnbym1wtMhv', '2025-06-08 22:59:55'),
('thunderhiper22@gmail.com', 'gL1seU2hXG75bPuS88plPaqTbdq83osA6utLhIVj7epqPWMi8uJK9rRyhJxQ', '2025-06-08 23:17:56'),
('thunderhiper22@gmail.com', 'J01SIYuL5cQNgHLoauGTcgroOTWhQs8GlzmtHpafXNNJoWkXPeOwsJcLr0TZ', '2025-06-08 23:04:36'),
('thunderhiper22@gmail.com', 'wplQgravDKLOP51eRLSZYNlpHu7I6RJ9unwWJPuaMG8FYuO2zPTg79uURafx', '2025-06-08 22:59:19');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('member01@example.com', '$2y$12$OI1QEXdZk3WwUsOLy4KBrOd0kMZYMx2CM0dOsODL/8TYbt5217VKK', '2025-06-17 06:59:35'),
('thunderhiper22@gmail.com', '$2y$12$p3/Ko0C9QmDIZpTEhHgCmeASgwkBu5AnfwCdTPEM5JK6F0ijZ2hdC', '2025-06-21 22:31:26'),
('ramelsales22@gmail.com', '$2y$12$XAymYQyqeukpCsCmspxx9uunsgp9stFG0dEH3F2n/a/9SzWeywdMW', '2025-09-04 04:33:45'),
('enegue4na4@gmail.com', '$2y$12$EJcaUDM8sp6sV0VXfhsUkuGBySNGrYAdX1gS3vZlZs7yyCcRAcK/C', '2025-09-04 04:35:19'),
('admin1@example.com', '$2y$12$cd8vWw1DAL.tulFRG9Cz2ukQV.X737RlFE0dmMTFWGY9J6IERusue', '2025-09-04 04:37:13'),
('leoneroyuri@gmail.com', '$2y$12$r5dZ2Ki9SxHXrFg160w2reLy1I4IFLIyF8G2Wafdf0FzRIpW6BCbO', '2025-09-04 04:39:05'),
('enegue4444@gmail.com', '$2y$12$kgqPjt9KXvnzlpsdB5y9XOj0YHtV8N89.jG1JMIg5lo3CcND2IRUS', '2025-09-04 04:39:35'),
('sanjoaquin.eugene04@gmail.com', '$2y$12$WL.o7pCtIYHT5Qj2AV85guE9k/7Q2Bpu6I0UeKVAtyM/JuI97rREO', '2025-09-04 04:42:03');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(24, 'App\\Models\\User', 5, 'authToken', 'fb360ef93de0c56b5bbbaefed9608055d9976ee2a7bca4ecb969adb96488a2cd', '[\"*\"]', '2025-06-08 21:45:24', NULL, '2025-06-08 21:40:25', '2025-06-08 21:45:24'),
(55, 'App\\Models\\User', 33, 'authToken', '0ec5535a8d20a6469655b8bd1104e0c219632fd83bdfb8a4d82e387249481b6e', '[\"*\"]', '2025-06-24 11:26:22', '2025-07-24 03:31:40', '2025-06-24 03:31:40', '2025-06-24 11:26:22'),
(110, 'App\\Models\\User', 48, 'authToken', '68122c379bb057ccbc82da8352037cd97aa678d62cd019e21c1ae82a41514cfe', '[\"*\"]', '2025-07-23 04:11:15', '2025-08-22 03:53:27', '2025-07-23 03:53:27', '2025-07-23 04:11:15'),
(213, 'App\\Models\\User', 2, 'authToken', '52af6de12f4f2c80e52dcea9c3b486df76f2561d35daedf47a07832cc91ef20d', '[\"*\"]', '2025-11-19 01:38:56', '2025-12-19 00:45:54', '2025-11-19 00:45:54', '2025-11-19 01:38:56');

-- --------------------------------------------------------

--
-- Table structure for table `pledges`
--

CREATE TABLE `pledges` (
  `id` int NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(10,2) DEFAULT '0.00',
  `branch_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prayer_requests`
--

CREATE TABLE `prayer_requests` (
  `id` int NOT NULL,
  `member_id` int NOT NULL,
  `branch_id` int NOT NULL,
  `type` enum('Prayer Request','Blessing','Reflection') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `request` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Pending','Reviewed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('tXizbyE0xnSASGjyyIz4MDNVEmmHB6dtwhS7Kaya', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicjlNTjYzY29VWE5vNjRrcFVaeXFuaUxoZnc2aG5IUWdGRzkyS21uSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fX0=', 1763634742);

-- --------------------------------------------------------

--
-- Table structure for table `sunday_service_attendance`
--

CREATE TABLE `sunday_service_attendance` (
  `id` int NOT NULL,
  `member_id` int NOT NULL,
  `branch_id` int NOT NULL,
  `event_id` int NOT NULL,
  `service_date` date NOT NULL,
  `status` enum('Attended','Missed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Missed',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sunday_service_attendance`
--

INSERT INTO `sunday_service_attendance` (`id`, `member_id`, `branch_id`, `event_id`, `service_date`, `status`, `created_at`, `updated_at`) VALUES
(1263, 2, 1, 361, '2025-11-12', 'Missed', '2025-11-12 02:42:02', '2025-11-12 02:42:02'),
(1264, 77, 1, 361, '2025-11-12', 'Missed', '2025-11-12 02:42:02', '2025-11-12 02:42:02'),
(1265, 101, 1, 361, '2025-11-12', 'Missed', '2025-11-12 02:42:02', '2025-11-12 02:42:02'),
(1266, 2, 1, 362, '2025-11-12', 'Missed', '2025-11-12 03:01:04', '2025-11-12 03:01:04'),
(1267, 77, 1, 362, '2025-11-12', 'Missed', '2025-11-12 03:01:04', '2025-11-12 03:01:04'),
(1268, 101, 1, 362, '2025-11-12', 'Missed', '2025-11-12 03:01:04', '2025-11-12 03:01:04'),
(1269, 2, 1, 363, '2025-11-15', 'Missed', '2025-11-15 21:03:14', '2025-11-15 21:03:14'),
(1270, 77, 1, 363, '2025-11-15', 'Missed', '2025-11-15 21:03:15', '2025-11-15 21:03:15'),
(1271, 101, 1, 363, '2025-11-15', 'Missed', '2025-11-15 21:03:15', '2025-11-15 21:03:15'),
(1272, 2, 1, 364, '2025-11-16', 'Missed', '2025-11-15 21:24:04', '2025-11-15 21:24:04'),
(1273, 77, 1, 364, '2025-11-16', 'Missed', '2025-11-15 21:24:04', '2025-11-15 21:24:04'),
(1274, 101, 1, 364, '2025-11-16', 'Missed', '2025-11-15 21:24:04', '2025-11-15 21:24:04'),
(1275, 2, 1, 366, '2025-11-16', 'Missed', '2025-11-15 21:27:02', '2025-11-15 21:27:02'),
(1276, 77, 1, 366, '2025-11-16', 'Missed', '2025-11-15 21:27:02', '2025-11-15 21:27:02'),
(1277, 101, 1, 366, '2025-11-16', 'Missed', '2025-11-15 21:27:02', '2025-11-15 21:27:02');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transparency`
--

CREATE TABLE `transparency` (
  `id` int NOT NULL,
  `pdf_link` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transparency`
--

INSERT INTO `transparency` (`id`, `pdf_link`, `branch_id`) VALUES
(1, '/storage/uploaded_pdfs/AcHcOP8jCtf4XTL7nk6qzAhXI8eCsaU4z5L0wmOc.pdf', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `branch_id` int DEFAULT NULL,
  `role` enum('Super Admin','Admin','Member','Visitor') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Member',
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contact_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `birthdate` date DEFAULT NULL,
  `profile_image` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `gender` enum('Male','Female') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Pending','Active','Inactive','Archived') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `skip_attendance_check` tinyint(1) DEFAULT '0',
  `middle_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `baptism_date` date DEFAULT NULL,
  `salvation_date` date DEFAULT NULL,
  `accepted_terms` tinyint(1) DEFAULT '0',
  `terms_accepted_at` timestamp NULL DEFAULT NULL,
  `unarchived_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `branch_id`, `role`, `username`, `email`, `password`, `remember_token`, `first_name`, `last_name`, `contact_number`, `address`, `birthdate`, `profile_image`, `created_at`, `updated_at`, `gender`, `status`, `skip_attendance_check`, `middle_name`, `baptism_date`, `salvation_date`, `accepted_terms`, `terms_accepted_at`, `unarchived_at`) VALUES
(1, NULL, 'Super Admin', 'admin123', 'admin@example.com', '$2y$12$sTbTo2V0nNZl/ppBF9iuFOX/PuwLSs2wk1lPNA7HKWfPj4Vtpwcam', 'uk1bDCeRFNhbKBaQUc97BI2iQgzWkeBohzbGi6j3JhSRKmUG9FT1WVWMbFyT', 'John', 'Doe', '09425459452', 'adsadsadsadas', NULL, 'profile_images/oD2z0WRgXeCDPoK8UqpjYA4kQsMxp1LafCTyEu8t.jpg', '2025-03-24 06:33:46', '2025-11-20 09:30:40', 'Male', 'Active', 0, 'protasio', NULL, NULL, 0, NULL, NULL),
(2, 1, 'Member', 'member01', 'ramelsales22@gmail.com', '$2y$12$v/bIYmqFtOBeKxGwU/Y8fOlmiF8UQVq1VFMCfpQYv8dp8bldwyHtC', NULL, 'Juan', 'Dela Cruz', '09425459438', 'Calamba Laguna', '2003-04-02', 'profile_images/SZa20DrGXbvQ8hzLHDQ7dzwtYsUBVgqlYhlj9SKD.jpg', '2025-03-24 06:37:02', '2025-11-16 06:39:40', 'Male', 'Inactive', 0, 'Balyentes', '2025-09-08', '2025-09-08', 0, '2025-09-06 05:10:09', NULL),
(3, 1, 'Admin', 'carlpogi', 'admin1@example.com', '$2y$12$yJQCOQK3m8xCgLSmJoaobegQyaR18cxZz2lRum1p1MKXZAM0TkO/2', '6JD5srRbm1bsVeAUALI55D91BQo3epTce2EKeNx08L0hI90Fr4tYIZJdP0OB', 'Carl Stephen', 'Vergara', '09425459458', 'adsadsads', NULL, 'profile_images/Hh8tRIIg5nnOX216X1uh4oESbUIA4VzYc75ynCFK.jpg', '2025-03-30 03:55:28', '2025-11-20 10:32:22', 'Male', 'Active', 0, 'Bayonetas', NULL, NULL, 0, NULL, NULL),
(31, NULL, 'Visitor', 'visitor_user', 'visitor@system.com', '$2y$12$K4Gk7Wq8qUVKZIX08pAJmOXUnGRzbnLeF8J0u2DmrjGEU7hxflOfe', NULL, 'Visitor', 'Visitor', NULL, NULL, NULL, NULL, '2025-07-26 07:09:33', '2025-07-26 07:09:33', 'Male', 'Active', 0, NULL, NULL, NULL, 0, NULL, NULL),
(45, 9, 'Admin', 'yuri', 'leoneroyuri@gmail.com', '$2y$12$dr8ohtytjV7/5Bigzy3Udu9TL0rS3IFQs5AUnGinMUbCmNHAcz8Kq', 'mAAaukZKIG4U5CrT4zmfgRqRMKJzgwGNCctBvT9CwZe9grYdkboqs4O6yvwH', 'Yuri', 'Leonero', '09218916436', 'Canlubang, Calamba', NULL, 'profile_images/sVP3J3SNByVJ8IJcEBFtIBtV8mvfPeHBIBWWskxv.jpg', '2025-06-30 18:27:56', '2025-07-25 00:32:33', 'Male', 'Active', 0, 'Bañes', NULL, NULL, 0, NULL, NULL),
(47, 2, 'Admin', 'gene', 'enegue4444@gmail.com', '$2y$12$f48H2dKzCdLle5MZS2silOfOyYeUABV3UaQ5AMGSPn2YLY1gxhVJ.', 'JY9P4gkHNSgaOBEmzhtEG5OyZvGgmp9ZUtLjPEHGMwuQrlp6kr52g642rxp5', 'Eugene', 'San Joaquin', '09218916436', 'San Cristobal, Calamba', NULL, 'profile_images/USKFOdEQoVRFDxfDaQqi4uYNsg4bSoVynnydFquo.jpg', '2025-06-30 20:27:12', '2025-11-20 10:13:21', 'Male', 'Active', 0, 'Villanueva', NULL, NULL, 0, NULL, NULL),
(54, 2, 'Member', 'test1', 'sanjoaquin.eugene04@gmail.com', '$2y$12$csTdc3hKx.DN0DLu/7Bc3exsiKM.TTnbMyEVM/2aSiVmfimfaqJL.', NULL, 'test1', 'test1', '09218916436', 'Bubuyan, Calamba', '2009-02-22', 'profile_images/UjonwAZkATukHveqJIXE5Kn3jQGfgusKhzBVjrNl.jpg', '2025-07-22 06:27:29', '2025-09-08 01:31:44', 'Male', 'Inactive', 0, 'test1', '2025-07-02', '2025-07-01', 0, '2025-09-08 01:31:44', NULL),
(60, 1, 'Admin', 'test', 'carlstephenvergara22@gmail.com', '$2y$12$3BLzoZY8nR/eOc7DtZ98xOhgyAiH6.FPMDPrp7/zHTHF.Ur8RFs7e', NULL, 'test', 'test', '09876543211', 'Barangay VI, Calamba', '2025-07-27', NULL, '2025-07-27 07:47:55', '2025-09-11 01:19:40', 'Male', 'Archived', 0, NULL, '2025-07-27', '2025-07-27', 0, NULL, NULL),
(77, 1, 'Member', 'evsanjoaquin1', 'evsan_joaquin@ccc.edu.ph', '$2y$12$muQf4Fv1iG1hZh4GXeSNl.mBahrJb.60oBEO3fp3WQxnVuSU6I2aq', NULL, 'Eugene', 'San Joaquin', '09876543244', 'Calamba, Bunggo', '2025-09-09', NULL, '2025-09-09 00:55:08', '2025-09-24 03:38:17', 'Male', 'Active', 1, 'Villanueva', NULL, NULL, 0, NULL, NULL),
(101, 1, 'Member', 'jbdelacruz', 'thunderhiper22@gmail.com', '$2y$12$bDPW9cmFVXrNX5dsv/pGLeJDh4DkcgmwqrFus4ypzzOU7CscpYu7S', NULL, 'Juan', 'Dela Cruz', '09218916436', 'Paete, Pinagsanjan', '2025-09-10', NULL, '2025-09-16 00:38:06', '2025-09-16 02:25:56', 'Male', 'Archived', 0, 'Balyentes', '2025-09-16', '2025-09-16', 0, NULL, NULL),
(112, NULL, 'Admin', 'jbdelacruz1', 'ybleonero@ccc.edu.ph', '$2y$12$FKGh2YiwrGkHfth8gO1Ne.JIw3lwzDNiev/VCxIFFKTyksKb02MtS', NULL, 'Juan', 'Dela Cruz', '09218916436', 'Magdapio, Paete', '2025-09-10', NULL, '2025-09-16 01:48:49', '2025-09-16 01:50:17', 'Male', 'Archived', 0, 'Balyentes', '2025-09-16', '2025-09-16', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_profile_updates`
--

CREATE TABLE `user_profile_updates` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `updated_field` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `old_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `new_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int NOT NULL,
  `branch_id` bigint NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `visit_date` date NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `inviter` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `branch_id`, `first_name`, `middle_name`, `last_name`, `visit_date`, `address`, `inviter`, `created_at`, `updated_at`) VALUES
(16, 2, 'test', NULL, 'test', '2025-07-28', 'Biliran', NULL, '2025-07-27 20:23:59', '2025-07-27 20:23:59'),
(21, 1, 'test', 'test', 'test', '2025-07-05', 'Bukidnon', 'test', '2025-07-27 20:25:43', '2025-07-27 20:25:43'),
(22, 1, 'test', 'test', 'test', '2025-07-03', 'Benguet', 'test', '2025-07-27 20:25:51', '2025-07-27 20:25:51'),
(23, 1, 'test', 'test', 'test', '2025-07-10', 'Batangas', 'test', '2025-07-27 20:25:59', '2025-07-27 20:25:59'),
(28, 1, 'test1', 'test', 'test', '2025-07-12', 'Bohol', 'test', '2025-07-27 20:26:49', '2025-07-27 20:32:25'),
(30, 1, 'test', 'test', 'test', '2025-07-31', 'Abra', 'test', '2025-07-31 01:52:43', '2025-07-31 01:52:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allocated_funds`
--
ALTER TABLE `allocated_funds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `branch_transfer_requests`
--
ALTER TABLE `branch_transfer_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_btr_user` (`user_id`),
  ADD KEY `fk_btr_current_branch` (`current_branch_id`),
  ADD KEY `fk_btr_requested_branch` (`requested_branch_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `church_services`
--
ALTER TABLE `church_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_id` (`branch_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `offering_id` (`offering_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `fk_parent_donation` (`parent_donation_id`);

--
-- Indexes for table `donation_allocations`
--
ALTER TABLE `donation_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `donation_id` (`donation_id`),
  ADD KEY `partition_id` (`partition_id`);

--
-- Indexes for table `donation_confirmations`
--
ALTER TABLE `donation_confirmations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_email_verifications_user` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `faith_tracks`
--
ALTER TABLE `faith_tracks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `financial_allocation_rules`
--
ALTER TABLE `financial_allocation_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `fund_expenses`
--
ALTER TABLE `fund_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `allocation_id` (`allocation_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `member_details`
--
ALTER TABLE `member_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offerings`
--
ALTER TABLE `offerings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_offerings_parent` (`parent_id`);

--
-- Indexes for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `partitions`
--
ALTER TABLE `partitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `partition_offering`
--
ALTER TABLE `partition_offering`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partition_id` (`partition_id`),
  ADD KEY `offering_id` (`offering_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`,`token`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD KEY `password_reset_tokens_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `pledges`
--
ALTER TABLE `pledges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `sunday_service_attendance`
--
ALTER TABLE `sunday_service_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transparency`
--
ALTER TABLE `transparency`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `user_profile_updates`
--
ALTER TABLE `user_profile_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allocated_funds`
--
ALTER TABLE `allocated_funds`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `branch_transfer_requests`
--
ALTER TABLE `branch_transfer_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `cache`
--
ALTER TABLE `cache`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `church_services`
--
ALTER TABLE `church_services`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=757;

--
-- AUTO_INCREMENT for table `donation_allocations`
--
ALTER TABLE `donation_allocations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1121;

--
-- AUTO_INCREMENT for table `donation_confirmations`
--
ALTER TABLE `donation_confirmations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=375;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `faith_tracks`
--
ALTER TABLE `faith_tracks`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `financial_allocation_rules`
--
ALTER TABLE `financial_allocation_rules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fund_expenses`
--
ALTER TABLE `fund_expenses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `member_details`
--
ALTER TABLE `member_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `offerings`
--
ALTER TABLE `offerings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `otp_verifications`
--
ALTER TABLE `otp_verifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `partitions`
--
ALTER TABLE `partitions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `partition_offering`
--
ALTER TABLE `partition_offering`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT for table `pledges`
--
ALTER TABLE `pledges`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `sunday_service_attendance`
--
ALTER TABLE `sunday_service_attendance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1290;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transparency`
--
ALTER TABLE `transparency`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `user_profile_updates`
--
ALTER TABLE `user_profile_updates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allocated_funds`
--
ALTER TABLE `allocated_funds`
  ADD CONSTRAINT `allocated_funds_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_transfer_requests`
--
ALTER TABLE `branch_transfer_requests`
  ADD CONSTRAINT `fk_btr_current_branch` FOREIGN KEY (`current_branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_btr_requested_branch` FOREIGN KEY (`requested_branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_btr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `church_services`
--
ALTER TABLE `church_services`
  ADD CONSTRAINT `fk_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`offering_id`) REFERENCES `offerings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donations_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_parent_donation` FOREIGN KEY (`parent_donation_id`) REFERENCES `donations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donation_allocations`
--
ALTER TABLE `donation_allocations`
  ADD CONSTRAINT `donation_allocations_ibfk_1` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donation_allocations_ibfk_2` FOREIGN KEY (`partition_id`) REFERENCES `partitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `fk_email_verifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `financial_allocation_rules`
--
ALTER TABLE `financial_allocation_rules`
  ADD CONSTRAINT `financial_allocation_rules_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fund_expenses`
--
ALTER TABLE `fund_expenses`
  ADD CONSTRAINT `fund_expenses_ibfk_1` FOREIGN KEY (`allocation_id`) REFERENCES `donation_allocations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `member_details`
--
ALTER TABLE `member_details`
  ADD CONSTRAINT `member_details_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offerings`
--
ALTER TABLE `offerings`
  ADD CONSTRAINT `fk_offerings_parent` FOREIGN KEY (`parent_id`) REFERENCES `offerings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `offerings_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `offerings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `partitions`
--
ALTER TABLE `partitions`
  ADD CONSTRAINT `partitions_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `partition_offering`
--
ALTER TABLE `partition_offering`
  ADD CONSTRAINT `partition_offering_ibfk_1` FOREIGN KEY (`partition_id`) REFERENCES `partitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partition_offering_ibfk_2` FOREIGN KEY (`offering_id`) REFERENCES `offerings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pledges`
--
ALTER TABLE `pledges`
  ADD CONSTRAINT `pledges_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prayer_requests`
--
ALTER TABLE `prayer_requests`
  ADD CONSTRAINT `prayer_requests_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prayer_requests_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sunday_service_attendance`
--
ALTER TABLE `sunday_service_attendance`
  ADD CONSTRAINT `sunday_service_attendance_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sunday_service_attendance_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sunday_service_attendance_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_profile_updates`
--
ALTER TABLE `user_profile_updates`
  ADD CONSTRAINT `user_profile_updates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
