-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 14, 2025 at 03:59 PM
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
-- Database: `modon`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drones`
--

CREATE TABLE `drones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `model` varchar(255) NOT NULL,
  `sr_no` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `drones`
--

INSERT INTO `drones` (`id`, `model`, `sr_no`, `user_id`, `created_at`, `updated_at`) VALUES
(10, 'Dji Mavic 4', 'SDF23423', 48, '2025-04-13 03:33:21', '2025-04-13 03:33:21'),
(11, 'Dji Mavic 4', 's', 48, '2025-04-13 03:33:27', '2025-04-13 03:33:47');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspection_types`
--

CREATE TABLE `inspection_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inspection_types`
--

INSERT INTO `inspection_types` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, '3D Mappings', 'Detecting violations in the external yards of factories', NULL, NULL),
(2, 'Gas Emission Analysis', 'Security inspection of hard-to-reach areas.', NULL, NULL),
(3, 'Yard Violations Detections', 'Update photos of the industrial city and cover events and activities.\n', NULL, NULL),
(4, 'Road Damage Monitoring', 'Monitoring road safety and detecting damages in industrial cities.', NULL, NULL),
(5, 'Remote Security Inspections', 'Imaging and analyzing harmful gases and emissions and their levels in industrial cities.\n', NULL, NULL),
(6, 'Emergency Response Support', 'Preparing a 3D map of the industrial city.\n', NULL, NULL),
(7, 'City Updates & Event Coverages', 'Responding to emergency cases reported to the specialized emergency call center.\n', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `latitude` varchar(1000) NOT NULL,
  `longitude` varchar(1000) NOT NULL,
  `map_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `latitude`, `longitude`, `map_url`, `description`, `created_at`, `updated_at`) VALUES
(2, 'Jeddah First Industrial City', '765', '566', 'https://iplocation.io/my-location', 'this is industry area.....', '2025-03-10 08:59:25', '2025-03-20 09:22:12'),
(3, ' Jeddah Second Industrial City', '5678', '6456', 'https://www.figma.com/design/SxNkfZdUofSsXzyaPckK6Z/Mudon---Aerial-Imaging-Services?node-id=0-1&p=f&t=YXQjxqN2LhFYVenu-0', 'WareHouse to Store', '2025-03-10 09:17:58', '2025-03-20 09:13:16'),
(4, 'Waha Jeddah', '9123', '8124123', 'https://www.figma.com/design/SxNkfZdUofSsXzyaPckK6Z/Mudon---Aerial-Imaging-Services?node-id=0-1&p=f&t=YXQjxqN2LhFYVenu-0', 'dasdas', '2025-03-10 10:02:15', '2025-03-13 08:59:45'),
(8, 'Makkah', '23', '78', 'https://iplocation.io/my-location', 'dasdas', '2025-03-13 09:02:55', '2025-03-13 09:02:55'),
(17, 'Jeddah Third Industrial City', '123', '2312', 'http://192.168.100.120:8000/locations', 'dasdasdasd', '2025-03-26 08:37:48', '2025-03-26 08:37:48'),
(20, 'Riyadh First Industrial City', '34', '54', 'http://localhost:8080/phpmyadmin/index.php?route=/table/change&db=modon&table=locations', 'dasd', '2025-03-26 08:37:48', '2025-03-26 08:37:48'),
(21, 'Riyadh Second Industrial City', '324', '45', 'http://localhost:8080/phpmyadmin/index.php?route=/table/change&db=modon&table=locations', 'dasdasd', '2025-03-26 08:37:48', '2025-03-26 08:37:48'),
(22, 'Al-Kharj', '34', '345', 'http://localhost:8080/phpmyadmin/index.php?route=/table/change&db=modon&table=locations', 'ada', '2025-04-10 13:05:05', '2025-04-17 13:05:05'),
(23, 'Sudair', '34', '54', 'http://localhost:8080/phpmyadmin/index.php?route=/table/change&db=modon&table=locations', 'asd', '2025-04-17 13:05:48', '2025-04-23 13:05:48'),
(24, 'Dammam First Industrial City', '34', '53', 'http://localhost:8080/phpmyadmin/index.php?route=/table/change&db=modon&table=locations', 'weqw', '2025-04-17 13:07:04', '2025-04-17 13:07:04'),
(25, 'Dammam Second Industrial City', '34', '423', 'http://localhost:8080/phpmyadmin/index.php?route=/table/change&db=modon&table=locations', 'qweqw', '2025-04-17 13:07:04', '2025-04-17 13:07:04'),
(26, 'Dammam Third Industrial City', '43', '42', 'http://localhost:8080/phpmyadmin/index.php?route=/table/change&db=modon&table=locations', 'dfsf', '2025-04-17 13:07:04', '2025-04-17 13:07:04'),
(27, 'Al-Ahsa First', '423', '34', 'http://localhost:8080/phpmyadmin/index.php?route=/table/change&db=modon&table=locations', 'wqe', '2025-04-17 13:07:04', '2025-04-17 13:07:04'),
(28, 'Al-Ahsa Second', '34', '423', 'http://localhost:8080/phpmyadmin/index.php?route=/table/change&db=modon&table=locations', 'eda', '2025-04-17 13:07:04', '2025-04-17 13:07:04');

-- --------------------------------------------------------

--
-- Table structure for table `location_assignments`
--

CREATE TABLE `location_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `region_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `location_assignments`
--

INSERT INTO `location_assignments` (`id`, `user_id`, `location_id`, `region_id`, `created_at`, `updated_at`) VALUES
(1, 15, 24, 4, '2025-04-12 15:33:48', '2025-04-12 15:33:48'),
(2, 15, 25, 4, '2025-04-12 15:33:48', '2025-04-12 15:33:48'),
(5, 15, 26, 4, '2025-04-12 15:33:48', '2025-04-12 15:33:48'),
(6, 15, 27, 4, '2025-04-12 15:33:48', '2025-04-12 15:33:48'),
(7, 15, 28, 4, '2025-04-12 15:34:15', '2025-04-12 15:34:15'),
(9, 15, 2, 3, '2025-04-12 15:36:04', '2025-04-12 15:36:04'),
(10, 15, 3, 3, '2025-04-12 15:36:04', '2025-04-12 15:36:04'),
(11, 15, 17, 3, '2025-04-12 15:36:55', '2025-04-12 15:36:55'),
(12, 15, 4, 3, '2025-04-12 15:36:55', '2025-04-12 15:36:55'),
(13, 15, 8, 3, '2025-04-12 15:37:13', '2025-04-12 15:37:13'),
(14, 15, 20, 2, '2025-04-12 15:37:35', '2025-04-12 15:37:35'),
(15, 15, 21, 2, '2025-04-12 15:37:35', '2025-04-12 15:37:35'),
(16, 15, 22, 2, '2025-04-12 15:38:03', '2025-04-12 15:38:03'),
(17, 15, 23, 2, '2025-04-12 15:38:03', '2025-04-12 15:38:03');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_03_10_091822_create_regions_table', 1),
(5, '2025_03_10_091822_create_user_types_table', 1),
(6, '2025_03_10_091823_add_user_type_and_region_to_users_table', 1),
(9, '2025_03_10_105252_create_locations_table', 2),
(10, '2025_03_10_114438_create_missions_table', 2),
(11, '2025_03_10_115809_add_region_id_to_locations_table', 3),
(13, '2025_03_10_120600_create_inspection_types_table', 4),
(14, '2025_03_10_120903_add_inspection_type_id_to_missions_table', 5),
(15, '2025_03_10_122710_create_mission_inspection_type_table', 6),
(16, '2025_03_10_123720_remove_inspection_type_id_from_missions_table', 7),
(19, '2025_03_11_115940_add_status_and_report_submitted_to_missions_table', 9),
(20, '2025_03_11_073406_create_pilot_reports_table', 10),
(21, '2025_03_27_073148_create_navigation_links_table', 11),
(22, '2025_03_27_073352_create_navigation_link_user_type_table', 11),
(23, '2025_04_07_070604_create_drones_table', 12),
(24, '2025_04_08_091503_add_image_to_users_table', 13),
(25, '2025_04_10_082319_create_pilots_table', 14),
(29, '2025_04_12_133640_create_user_region_table', 15),
(30, '2025_04_12_134155_remove_region_id_from_users_table', 16),
(31, '2025_04_12_152733_create_location_assignments_table', 17),
(32, '2025_04_12_152756_remove_region_id_from_locations_table', 17),
(33, '2025_04_13_073949_add_description_to_inspection_types_table', 18),
(35, '2025_04_13_093632_update_missions_table_for_mission_date', 19),
(36, '2025_04_13_103950_create_mission_approvals_table', 20),
(37, '2025_04_13_105024_add_approval_columns_to_mission_approvals_table', 21),
(38, '2025_04_13_105427_rollback_extra_fields_from_mission_approvals', 22),
(39, '2025_04_13_105546_create_mission_approvals_table', 23),
(40, '2025_04_14_070225_create_user_location_table', 24);

-- --------------------------------------------------------

--
-- Table structure for table `missions`
--

CREATE TABLE `missions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `mission_date` date DEFAULT current_timestamp(),
  `region_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `report_submitted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `missions`
--

INSERT INTO `missions` (`id`, `user_id`, `note`, `mission_date`, `region_id`, `created_at`, `updated_at`, `status`, `report_submitted`) VALUES
(1, 46, 'Ali', '2025-05-28', 2, '2025-04-13 07:58:50', '2025-04-14 08:47:39', 'Pending', 0),
(5, 46, 'A character can be any letter, number, punctuation, special character, or space. Each of these characters takes up one byte of space in a computer\'s memory. Some Unicode characters, like emojis and some letters in non-Latin alphabets, take up two bytes of space and therefore count as two characters. Use our character counter tool below for an accurate count of your characters.', '2025-04-30', 2, '2025-04-14 10:48:55', '2025-04-14 10:48:55', 'Pending', 0);

-- --------------------------------------------------------

--
-- Table structure for table `mission_approvals`
--

CREATE TABLE `mission_approvals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mission_id` bigint(20) UNSIGNED NOT NULL,
  `city_manager_approved` tinyint(1) NOT NULL DEFAULT 0,
  `region_manager_approved` tinyint(1) NOT NULL DEFAULT 0,
  `modon_admin_approved` tinyint(1) NOT NULL DEFAULT 0,
  `is_fully_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mission_approvals`
--

INSERT INTO `mission_approvals` (`id`, `mission_id`, `city_manager_approved`, `region_manager_approved`, `modon_admin_approved`, `is_fully_approved`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 0, 0, 0, '2025-04-13 07:58:50', '2025-04-13 07:58:50'),
(5, 5, 0, 0, 0, 0, '2025-04-14 10:48:55', '2025-04-14 10:48:55');

-- --------------------------------------------------------

--
-- Table structure for table `mission_inspection_type`
--

CREATE TABLE `mission_inspection_type` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mission_id` bigint(20) UNSIGNED NOT NULL,
  `inspection_type_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mission_inspection_type`
--

INSERT INTO `mission_inspection_type` (`id`, `mission_id`, `inspection_type_id`) VALUES
(5, 1, 2),
(8, 5, 4);

-- --------------------------------------------------------

--
-- Table structure for table `mission_location`
--

CREATE TABLE `mission_location` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mission_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mission_location`
--

INSERT INTO `mission_location` (`id`, `mission_id`, `location_id`) VALUES
(1, 1, 23),
(6, 5, 23);

-- --------------------------------------------------------

--
-- Table structure for table `navigation_links`
--

CREATE TABLE `navigation_links` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `navigation_links`
--

INSERT INTO `navigation_links` (`id`, `name`, `url`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Dashboard', '/dashboard', 1, '2025-03-27 04:38:34', '2025-03-27 04:38:34'),
(2, 'Missions', '/missions', 2, '2025-03-27 04:38:34', '2025-03-27 04:38:34'),
(3, 'Locations', '/locations', 3, '2025-03-27 04:38:34', '2025-03-27 04:38:34'),
(4, 'Pilot', '/pilot', 4, '2025-03-27 04:38:34', '2025-03-27 04:38:34'),
(5, 'Users', '/admin/users', 5, '2025-03-27 04:38:34', '2025-03-27 04:38:34'),
(6, 'Drones', '/drones', 6, '2025-04-08 08:01:55', '2025-04-08 10:01:55');

-- --------------------------------------------------------

--
-- Table structure for table `navigation_link_user_type`
--

CREATE TABLE `navigation_link_user_type` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `navigation_link_id` bigint(20) UNSIGNED NOT NULL,
  `user_type_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `navigation_link_user_type`
--

INSERT INTO `navigation_link_user_type` (`id`, `navigation_link_id`, `user_type_id`) VALUES
(1, 1, 1),
(2, 2, 3),
(3, 3, 1),
(4, 4, 4),
(5, 5, 1),
(6, 6, 1),
(7, 6, 2),
(8, 2, 7),
(9, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pilots`
--

CREATE TABLE `pilots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `license_no` varchar(255) NOT NULL,
  `license_expiry` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pilots`
--

INSERT INTO `pilots` (`id`, `user_id`, `license_no`, `license_expiry`, `created_at`, `updated_at`) VALUES
(13, 48, 'AEW3423', '2025-05-09', '2025-04-12 12:04:21', '2025-04-12 12:04:21');

-- --------------------------------------------------------

--
-- Table structure for table `pilot_reports`
--

CREATE TABLE `pilot_reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_reference` varchar(255) NOT NULL,
  `mission_id` bigint(20) UNSIGNED NOT NULL,
  `start_datetime` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_datetime` timestamp NOT NULL DEFAULT current_timestamp(),
  `video_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pilot_reports`
--

INSERT INTO `pilot_reports` (`id`, `report_reference`, `mission_id`, `start_datetime`, `end_datetime`, `video_url`, `description`, `created_at`, `updated_at`) VALUES
(39, 'REP-X2CFd5hH', 20, '2025-03-25 14:53:00', '2025-03-25 17:53:00', 'https://www.youtube.com/watch?v=CyORBodMwzI', 'dasd', '2025-03-25 09:53:45', '2025-03-25 09:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `pilot_report_images`
--

CREATE TABLE `pilot_report_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pilot_report_id` bigint(20) UNSIGNED NOT NULL,
  `inspection_type_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'all', '2025-04-07 09:44:12', '2025-04-07 14:44:12'),
(2, 'central', '2025-04-07 09:44:12', '2025-04-07 14:44:12'),
(3, 'western', '2025-04-07 09:44:12', '2025-04-07 14:44:12'),
(4, 'eastern', '2025-04-07 09:44:12', '2025-04-07 14:44:12');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('5NPNEiHr7rMctRL3pVp8lVCSdhln30EYWib2e2oL', 46, '192.168.100.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoic0FHZ1JwWWNyS29hQXcyU1UzajN2dkJTWG5xUGtBakVzZUlMVFE1ViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xOTIuMTY4LjEwMC4xMzQ6ODAwMC9taXNzaW9ucyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjQ2O30=', 1744639010);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_type_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `image`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `user_type_id`) VALUES
(15, 'Mohammad bilal', 'bilal@qltyss.com', '1744111605_1680002120213.jpeg', NULL, '$2y$12$1hQ41L6hYCcbHHjX69UfaOMCSFUzHoYefTkDrbKT9OwHoOG3JSNju', NULL, '2025-04-07 05:46:08', '2025-04-12 08:04:52', 1),
(33, 'Mohammad Adil', 'adil@gmail.com', '1744455631_ai-generated-8416791_1920.png', NULL, '$2y$12$yEn6JkqBscBKtqNZLm2P2ePeJ59gfZX4.su/fQrrSMHCWI1YkmoZ2', NULL, '2025-04-12 08:00:31', '2025-04-12 08:00:31', 2),
(44, 'uzair', 'uzair@gmail.com', '1744467212_blueberries-9450130_1920.jpg', NULL, '$2y$12$HCZ3uJoxiH27vzTNH35FOutZ0re6qx7skBNjVxf.BAxcX9WMzKdde', NULL, '2025-04-12 11:13:32', '2025-04-12 11:13:32', 3),
(45, 'momin', 'momin@gmail.com', '1744467240_ai-generated-9382803_1920.jpg', NULL, '$2y$12$k/Dp87Mj5faJUMMWcoU8xORlxgJRgZwBCk1MgI9/xyOQTTaeEMm6m', NULL, '2025-04-12 11:14:00', '2025-04-12 11:14:00', 6),
(46, 'nabeel', 'nabeel@qltyss.com', '1744467264_ai-generated-9453465_1920.png', NULL, '$2y$12$XlHKTA8/3QffgU5gRHgIHea6zYdoA1lf.GISqZeDsLVHWMvCU3oP6', NULL, '2025-04-12 11:14:24', '2025-04-13 03:56:32', 7),
(48, 'Ibrahim', 'ibrahim@gmail.com', '1744470261_ai-generated-8665850_1920.jpg', NULL, '$2y$12$e2VpDlDUL1cjGfNjdRt8KOjdjEErK3bl7154LJ.5Jnm0qw4BT3BkK', NULL, '2025-04-12 12:04:21', '2025-04-12 12:04:21', 4);

-- --------------------------------------------------------

--
-- Table structure for table `user_location`
--

CREATE TABLE `user_location` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_location`
--

INSERT INTO `user_location` (`id`, `user_id`, `location_id`, `created_at`, `updated_at`) VALUES
(9, 45, 22, NULL, NULL),
(10, 46, 23, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_region`
--

CREATE TABLE `user_region` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `region_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_region`
--

INSERT INTO `user_region` (`id`, `user_id`, `region_id`, `created_at`, `updated_at`) VALUES
(1, 15, 1, '2025-04-12 13:47:56', '2025-04-12 13:47:56'),
(3, 44, 2, NULL, NULL),
(4, 45, 2, NULL, NULL),
(14, 46, 2, '2025-04-12 14:59:45', '2025-04-12 14:59:45'),
(16, 33, 1, '2025-04-12 14:59:54', '2025-04-12 14:59:54'),
(18, 48, 3, '2025-04-12 15:04:21', '2025-04-12 15:04:21'),
(19, 48, 4, '2025-04-12 15:04:21', '2025-04-12 15:04:21');

-- --------------------------------------------------------

--
-- Table structure for table `user_types`
--

CREATE TABLE `user_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_types`
--

INSERT INTO `user_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'qss_admin', '2025-03-10 00:00:00', '2025-03-10 00:00:00'),
(2, 'modon_admin', '2025-03-10 00:00:00', '2025-03-10 00:00:00'),
(3, 'region_manager', '2025-03-10 00:00:00', '2025-03-10 00:00:00'),
(4, 'pilot', '2025-03-10 00:00:00', '2025-03-10 00:00:00'),
(6, 'city_manager', '2025-04-12 12:54:54', '2025-04-12 12:54:54'),
(7, 'city_supervisor', '2025-04-12 12:54:54', '2025-04-12 12:54:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `drones`
--
ALTER TABLE `drones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `drones_sr_no_unique` (`sr_no`),
  ADD KEY `drones_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inspection_types`
--
ALTER TABLE `inspection_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inspection_types_name_unique` (`name`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `location_assignments`
--
ALTER TABLE `location_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_assignments_user_id_foreign` (`user_id`),
  ADD KEY `location_assignments_location_id_foreign` (`location_id`),
  ADD KEY `location_assignments_region_id_foreign` (`region_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `missions`
--
ALTER TABLE `missions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `missions_region_id_foreign` (`region_id`);

--
-- Indexes for table `mission_approvals`
--
ALTER TABLE `mission_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mission_approvals_mission_id_foreign` (`mission_id`);

--
-- Indexes for table `mission_inspection_type`
--
ALTER TABLE `mission_inspection_type`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mission_inspection_type_mission_id_foreign` (`mission_id`),
  ADD KEY `mission_inspection_type_inspection_type_id_foreign` (`inspection_type_id`);

--
-- Indexes for table `mission_location`
--
ALTER TABLE `mission_location`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mission_location_mission_id_foreign` (`mission_id`),
  ADD KEY `mission_location_location_id_foreign` (`location_id`);

--
-- Indexes for table `navigation_links`
--
ALTER TABLE `navigation_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `navigation_link_user_type`
--
ALTER TABLE `navigation_link_user_type`
  ADD PRIMARY KEY (`id`),
  ADD KEY `navigation_link_user_type_navigation_link_id_foreign` (`navigation_link_id`),
  ADD KEY `navigation_link_user_type_user_type_id_foreign` (`user_type_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pilots`
--
ALTER TABLE `pilots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pilots_user_id_foreign` (`user_id`);

--
-- Indexes for table `pilot_reports`
--
ALTER TABLE `pilot_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pilot_reports_report_reference_unique` (`report_reference`),
  ADD KEY `pilot_reports_mission_id_foreign` (`mission_id`);

--
-- Indexes for table `pilot_report_images`
--
ALTER TABLE `pilot_report_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pilot_report_images_pilot_report_id_foreign` (`pilot_report_id`),
  ADD KEY `pilot_report_images_inspection_type_id_foreign` (`inspection_type_id`),
  ADD KEY `pilot_report_images_location_id_foreign` (`location_id`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `regions_name_unique` (`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_user_type_id_foreign` (`user_type_id`);

--
-- Indexes for table `user_location`
--
ALTER TABLE `user_location`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_location_user_id_foreign` (`user_id`),
  ADD KEY `user_location_location_id_foreign` (`location_id`);

--
-- Indexes for table `user_region`
--
ALTER TABLE `user_region`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_region_user_id_foreign` (`user_id`),
  ADD KEY `user_region_region_id_foreign` (`region_id`);

--
-- Indexes for table `user_types`
--
ALTER TABLE `user_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_types_name_unique` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `drones`
--
ALTER TABLE `drones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspection_types`
--
ALTER TABLE `inspection_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `location_assignments`
--
ALTER TABLE `location_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `missions`
--
ALTER TABLE `missions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mission_approvals`
--
ALTER TABLE `mission_approvals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mission_inspection_type`
--
ALTER TABLE `mission_inspection_type`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mission_location`
--
ALTER TABLE `mission_location`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `navigation_links`
--
ALTER TABLE `navigation_links`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `navigation_link_user_type`
--
ALTER TABLE `navigation_link_user_type`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pilots`
--
ALTER TABLE `pilots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pilot_reports`
--
ALTER TABLE `pilot_reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `pilot_report_images`
--
ALTER TABLE `pilot_report_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `user_location`
--
ALTER TABLE `user_location`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_region`
--
ALTER TABLE `user_region`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `user_types`
--
ALTER TABLE `user_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `drones`
--
ALTER TABLE `drones`
  ADD CONSTRAINT `drones_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `location_assignments`
--
ALTER TABLE `location_assignments`
  ADD CONSTRAINT `location_assignments_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `location_assignments_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `location_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `missions`
--
ALTER TABLE `missions`
  ADD CONSTRAINT `missions_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mission_approvals`
--
ALTER TABLE `mission_approvals`
  ADD CONSTRAINT `mission_approvals_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mission_inspection_type`
--
ALTER TABLE `mission_inspection_type`
  ADD CONSTRAINT `mission_inspection_type_inspection_type_id_foreign` FOREIGN KEY (`inspection_type_id`) REFERENCES `inspection_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mission_inspection_type_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mission_location`
--
ALTER TABLE `mission_location`
  ADD CONSTRAINT `mission_location_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mission_location_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `navigation_link_user_type`
--
ALTER TABLE `navigation_link_user_type`
  ADD CONSTRAINT `navigation_link_user_type_navigation_link_id_foreign` FOREIGN KEY (`navigation_link_id`) REFERENCES `navigation_links` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `navigation_link_user_type_user_type_id_foreign` FOREIGN KEY (`user_type_id`) REFERENCES `user_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pilots`
--
ALTER TABLE `pilots`
  ADD CONSTRAINT `pilots_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pilot_reports`
--
ALTER TABLE `pilot_reports`
  ADD CONSTRAINT `pilot_reports_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pilot_report_images`
--
ALTER TABLE `pilot_report_images`
  ADD CONSTRAINT `pilot_report_images_inspection_type_id_foreign` FOREIGN KEY (`inspection_type_id`) REFERENCES `inspection_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pilot_report_images_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pilot_report_images_pilot_report_id_foreign` FOREIGN KEY (`pilot_report_id`) REFERENCES `pilot_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_user_type_id_foreign` FOREIGN KEY (`user_type_id`) REFERENCES `user_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_location`
--
ALTER TABLE `user_location`
  ADD CONSTRAINT `user_location_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_location_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_region`
--
ALTER TABLE `user_region`
  ADD CONSTRAINT `user_region_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_region_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
