-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 21, 2025 at 01:07 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `enrollassess`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_codes`
--

CREATE TABLE `access_codes` (
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applicant_id` bigint UNSIGNED NOT NULL,
  `exam_id` bigint UNSIGNED DEFAULT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `access_codes`
--

INSERT INTO `access_codes` (`code`, `applicant_id`, `exam_id`, `is_used`, `used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
('BSIT-2J4E4ES3', 20, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-2K4VZHM7', 24, 1, 1, '2025-10-14 05:42:51', '2025-10-17 05:38:31', '2025-10-14 05:38:31', '2025-10-14 05:42:51'),
('BSIT-3NNEQ7FT', 2, 1, 1, '2025-10-14 06:08:58', '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-14 06:08:58'),
('BSIT-48MEEOFL', 4, 1, 1, '2025-10-20 03:52:55', '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-20 03:52:55'),
('BSIT-4CKXZXN0', 21, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-4GS60GQG', 6, 1, 1, '2025-10-20 04:28:10', '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-20 04:28:10'),
('BSIT-53QXTFSM', 8, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-9X8SOXC1', 16, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-CFSEL6LD', 9, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-DF9DQI0A', 19, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-F6EEBTPQ', 23, 1, 1, '2025-10-12 04:50:20', '2025-10-13 10:11:12', '2025-10-10 10:11:12', '2025-10-12 04:50:20'),
('BSIT-HAZ2YBP8', 3, 1, 1, '2025-10-15 23:46:10', '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-15 23:46:10'),
('BSIT-HPMTFWPV', 18, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-JCHANWTT', 17, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-KHKGXF0L', 14, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-L0KXCKOT', 22, 1, 1, '2025-10-20 04:15:28', '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-20 04:15:28'),
('BSIT-MLEXVGCK', 7, 1, 1, '2025-10-20 20:52:18', '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-20 20:52:18'),
('BSIT-MMMM9IZP', 15, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-MS6S717N', 5, 1, 1, '2025-10-20 03:55:08', '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-20 03:55:08'),
('BSIT-NJ5VDGTF', 11, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-PPZGHYEU', 13, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-QCK7Y65H', 10, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-UEXOS7ZN', 12, NULL, 0, NULL, '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-08 07:36:18'),
('BSIT-YAZLTBUA', 1, 1, 1, '2025-10-14 05:50:55', '2025-11-07 07:36:18', '2025-10-08 07:36:18', '2025-10-14 05:50:55');

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

CREATE TABLE `applicants` (
  `applicant_id` bigint UNSIGNED NOT NULL,
  `application_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_instructor_id` bigint UNSIGNED DEFAULT NULL,
  `preferred_course` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `enrollassess_score` decimal(5,2) DEFAULT NULL COMMENT 'EnrollAssess internal exam scores',
  `interview_score` decimal(5,2) DEFAULT NULL COMMENT 'Interview evaluation scores',
  `verbal_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','exam-completed','interview-available','interview-claimed','interview-scheduled','interview-completed','admitted','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `exam_completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicants`
--

INSERT INTO `applicants` (`applicant_id`, `application_no`, `first_name`, `middle_name`, `last_name`, `email_address`, `phone_number`, `assigned_instructor_id`, `preferred_course`, `score`, `enrollassess_score`, `interview_score`, `verbal_description`, `status`, `exam_completed_at`, `created_at`, `updated_at`) VALUES
(1, '0-25-1-08946-1806', 'GABRIEL', 'LOMACO', 'ABRIL', 'gabriel.1000abril@gmail.com', '9513693169', 2, 'BSIT', 27.00, 22.00, NULL, 'Needs Improvement', 'exam-completed', '2025-10-14 05:50:55', '2025-10-08 07:36:18', '2025-10-14 05:50:55'),
(2, '0-25-1-06909-1383', 'DANIELLE ANGELO', 'ARREZA', 'ALBARICO', 'danielleangelo.albarico@gmail.com', '9090855732', 2, 'BSIT', 24.75, 4.00, NULL, 'Needs Improvement', 'exam-completed', '2025-10-14 06:08:57', '2025-10-08 07:36:18', '2025-10-14 06:08:57'),
(3, '0-25-1-14691-2631', 'AERO JADE', 'GORDON', 'ALCALA', 'aerojade.alcala0304@gmail.com', '9129751059', 2, 'BSIT', 24.00, 4.00, NULL, 'Needs Improvement', 'exam-completed', '2025-10-15 23:46:12', '2025-10-08 07:36:18', '2025-10-15 23:46:12'),
(4, '0-25-1-23632-3057', 'CHELMAR', '', 'ALCANTARA', 'chelmaraldaye@gmail.com', '9633749297', 2, 'BSIT', 23.50, 5.00, NULL, 'Fair', 'exam-completed', '2025-10-20 03:52:55', '2025-10-08 07:36:18', '2025-10-20 03:52:55'),
(5, '0-25-1-07609-1347', 'LESAM', 'DABUIA', 'AMARO', 'lesamamobao@gmail.com', '9634701151', 2, 'BSIT', 23.25, 5.00, NULL, 'Fair', 'exam-completed', '2025-10-20 03:55:08', '2025-10-08 07:36:18', '2025-10-20 03:55:08'),
(6, '0-25-1-26099-4327', 'DIVINE', 'GANZON', 'AMARO', 'divineamaro26@gmail.com', '9771015748', 2, 'BSIT', 22.00, 9.00, NULL, 'Very Good', 'exam-completed', '2025-10-20 04:28:10', '2025-10-08 07:36:18', '2025-10-20 04:28:10'),
(7, '0-25-1-14267-2965', 'RICARDO', 'ILO', 'ANDO', 'rickmsuave@yahoo.com', '97710328749', 2, 'BSIT', 23.25, 8.00, NULL, 'Good', 'exam-completed', '2025-10-20 20:52:16', '2025-10-08 07:36:18', '2025-10-20 20:52:16'),
(8, '0-25-1-08677-1557', 'AMOS', 'RIS', 'ANG', 'rhynndan104@gmail.com', '933805174685', 2, 'BSIT', 17.75, NULL, NULL, 'Very Low', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(9, '0-25-1-25722-3925', 'FRANCIS RJ', 'NUÑEZ', 'ANG', 'angfrancisrj@gmail.com', '639385003791', 2, 'BSIT', 27.75, NULL, NULL, 'Low Average', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(10, '0-25-1-14682-2957', 'LORENCE', 'LOCHA', 'APURA', 'lorencelua30@gmail.com', '95114350679', 2, 'BSIT', 27.75, NULL, NULL, 'Low Average', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(11, '0-25-1-15213-3378', 'ALEXANDER', 'LUCAS', 'ARCENAL', 'aularcenal24@gmail.com', '9759004115', 2, 'BSIT', 23.25, NULL, NULL, 'Below Average', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(12, '0-25-1-15213-3379', 'JIA LIE', 'RUCHA', 'ARQUILLANO', 'jiale.arquillano@gmail.com', '9332093412', 2, 'BSIT', 27.00, NULL, NULL, 'Low Average', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(13, '0-25-1-05676-1043', 'JOHN PAUL', 'LOCHA', 'ASEO', 'aseojp046@gmail.com', '9759004115', 2, 'BSIT', 20.25, NULL, NULL, 'Below Average', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(14, '0-25-1-12907-3435', 'YVONNE MARIE', 'CASTAÑARES', 'AYSO', 'yvonnemarie.ayso@gmail.com', '9129748932', 2, 'BSIT', 28.50, NULL, NULL, 'Low Average', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(15, '0-25-1-11424-2538', 'SYNDRICK LARRY', 'LOCHA', 'BACUNAL', 'syndrickbacunal@gmail.com', '9124788555', 2, 'BSIT', 30.25, NULL, NULL, 'Low Average', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(16, '0-25-1-15312-2702', 'CHRISTEL ELLA MARIE', 'NADUA', 'BAGAY', 'christellabagay04@gmail.com', '9216309132', 2, 'BSIT', 22.25, NULL, NULL, 'Very Low', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(17, '0-25-1-03320-0624', 'JOSHUA', 'LOCHA', 'BAGUION', 'joshuabaguion14@gmail.com', '9479071041', 2, 'BSIT', 22.00, NULL, NULL, 'Very Low', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(18, '0-25-1-25292-0631', 'ALBERT', 'JIN', 'BALINGIT', 'balingitjen62@gmail.com', '9673897761', 2, 'BSIT', 21.25, NULL, NULL, 'Very Low', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(19, '0-25-1-09857-1331', 'JESSIE', 'SANICO', 'BALLERA', 'Nellysanico09x@gmail.com', '9517302483', 2, 'BSIT', 21.00, NULL, NULL, 'Very Low', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(20, '0-25-1-10795-1831', 'ANGEL AROE RAE', 'JACARAN', 'BARNICURATO', 'angel10barnicurato@gmail.com', '9260875434', 2, 'BSIT', 18.25, NULL, NULL, 'Very Low', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-08 23:48:50'),
(21, '0-25-1-10795-2577', 'EJIEN', 'PILAPIL', 'BARRAL', 'ejienpilapil@gmail.com', '9750385951', 2, 'BSIT', 14.25, NULL, NULL, 'Very Low', 'pending', NULL, '2025-10-08 07:36:18', '2025-10-20 04:18:39'),
(22, '0-25-1-18889-3851', 'MONICA', 'ANCA', 'BASALLAJE', 'lacabasalalje12@gmail.com', '9267170098', 2, 'BSIT', 29.25, 5.00, NULL, 'Fair', 'exam-completed', '2025-10-20 04:15:28', '2025-10-08 07:36:18', '2025-10-20 04:18:42'),
(23, '2025-0023', 'Christian Rey', 'Yap', 'Alegre', 'christian.alegre@evsu.edu.ph', '09918431792', 2, 'BSIT', NULL, 8.00, NULL, 'Needs Improvement', 'exam-completed', '2025-10-12 04:50:20', '2025-10-10 10:11:12', '2025-10-12 04:50:20'),
(24, '2025-0024', 'hazel', NULL, 'yray', 'christianhamlmao@gmail.com', '09918431792', 2, 'BSIT', 21.00, 24.00, NULL, 'Fair', 'exam-completed', '2025-10-14 05:43:29', '2025-10-14 05:38:31', '2025-10-20 04:19:11');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `exam_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_minutes` int NOT NULL,
  `total_items` int DEFAULT NULL,
  `mcq_quota` int DEFAULT NULL,
  `tf_quota` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`exam_id`, `title`, `duration_minutes`, `total_items`, `mcq_quota`, `tf_quota`, `description`, `is_active`, `starts_at`, `ends_at`, `created_at`, `updated_at`) VALUES
(1, 'Information Technology Assessment', 60, 10, 5, 5, 'IT fundamentals assessment covering programming, networking, and computer science basics', 1, '2025-10-19 09:42:00', '2025-10-21 10:26:00', '2025-10-08 07:43:00', '2025-10-20 20:49:56');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `interview_id` bigint UNSIGNED NOT NULL,
  `applicant_id` bigint UNSIGNED NOT NULL,
  `interviewer_id` bigint UNSIGNED NOT NULL,
  `schedule_date` datetime DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled','rescheduled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `communication_skills` int DEFAULT NULL COMMENT 'Score 0-10: Communication Skills',
  `motivation_interest` int DEFAULT NULL COMMENT 'Score 0-10: Motivation and Interest',
  `problem_solving_attitude` int DEFAULT NULL COMMENT 'Score 0-10: Problem-solving and Critical Thinking',
  `program_understanding` int DEFAULT NULL COMMENT 'Score 0-10: Understanding of the Program',
  `personality_attitude` int DEFAULT NULL COMMENT 'Score 0-10: Personality and Attitude',
  `it_background` int DEFAULT NULL COMMENT 'Score 0-10: IT Exposure / Background',
  `willingness_to_learn` int DEFAULT NULL COMMENT 'Score 0-10: Willingness to Learn',
  `overall_impression` int DEFAULT NULL COMMENT 'Score 0-10: Overall Impression',
  `interview_notes` text COLLATE utf8mb4_unicode_ci,
  `evaluator_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Evaluator notes and observations',
  `final_comments` text COLLATE utf8mb4_unicode_ci,
  `strengths` text COLLATE utf8mb4_unicode_ci COMMENT 'Applicant strengths',
  `areas_improvement` text COLLATE utf8mb4_unicode_ci COMMENT 'Areas for improvement',
  `overall_rating` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Overall rating: excellent, very_good, good, satisfactory, needs_improvement',
  `recommendation` enum('highly_recommended','recommended','conditional','not_recommended') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Final recommendation',
  `overall_score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `claimed_by` bigint UNSIGNED DEFAULT NULL,
  `claimed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `interviews`
--

INSERT INTO `interviews` (`interview_id`, `applicant_id`, `interviewer_id`, `schedule_date`, `status`, `communication_skills`, `motivation_interest`, `problem_solving_attitude`, `program_understanding`, `personality_attitude`, `it_background`, `willingness_to_learn`, `overall_impression`, `interview_notes`, `evaluator_notes`, `final_comments`, `strengths`, `areas_improvement`, `overall_rating`, `recommendation`, `overall_score`, `created_at`, `updated_at`, `claimed_by`, `claimed_at`) VALUES
(1, 1, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(2, 2, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(3, 3, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(4, 4, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(5, 5, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(6, 6, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(7, 7, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(8, 8, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(9, 9, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(10, 10, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(11, 11, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(12, 12, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(13, 13, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(14, 14, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(15, 15, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(16, 16, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(17, 17, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(18, 18, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(19, 19, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(20, 20, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-08 23:48:50', '2025-10-08 23:48:50', NULL, NULL),
(21, 23, 2, '2025-10-12 14:01:00', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'please come 15 minutes before', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-10 10:11:35', '2025-10-21 04:28:45', 1, '2025-10-21 04:28:45'),
(22, 21, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-20 04:18:39', '2025-10-20 04:18:39', NULL, NULL),
(23, 22, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-20 04:18:42', '2025-10-20 04:18:42', NULL, NULL),
(24, 24, 2, NULL, 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-20 04:19:11', '2025-10-20 04:19:11', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_08_05_133658_create_applicants_table', 1),
(5, '2025_08_05_133713_create_access_codes_table', 1),
(6, '2025_08_05_133725_create_interviews_table', 1),
(7, '2025_08_05_133729_create_questions_table', 1),
(8, '2025_08_05_133735_create_question_options_table', 1),
(9, '2025_08_05_133741_create_results_table', 1),
(10, '2025_08_05_133822_create_enrollassess_exams_table', 1),
(11, '2025_08_05_133842_create_enrollassess_exam_sets_table', 1),
(12, '2025_10_08_153105_add_assigned_instructor_id_to_applicants', 2),
(13, '2025_08_05_134553_modify_users_table_for_erd', 2),
(14, '2025_08_07_085152_update_questions_table_add_short_answer_type', 3),
(15, '2025_08_08_000101_update_interviews_table_nullable_and_status', 3),
(16, '2025_09_02_084227_add_password_column_to_users_table', 3),
(17, '2025_09_25_170307_update_applicants_table_for_stakeholder_columns', 3),
(18, '2025_09_25_170703_add_performance_indexes_to_tables', 3),
(19, '2025_09_25_190046_clean_applicants_table_for_stakeholder_columns', 4),
(20, '2025_09_27_085306_add_interview_pool_support_to_interviews_table', 4),
(21, '2025_09_27_124851_update_applicants_status_enum_add_interview_available', 4),
(22, '2025_09_27_124942_update_applicants_status_enum_final', 4),
(23, '2025_09_28_120347_add_enrollassess_and_interview_scores_to_applicants_table', 4),
(24, '2025_10_08_100000_add_question_bank_features', 5),
(25, '2025_10_08_200000_refactor_to_question_bank_and_direct_assignment', 5),
(26, '2025_10_08_210000_add_availability_window_to_exams_table', 5),
(27, '2025_10_12_112649_add_exam_id_to_access_codes_table', 6),
(28, '2025_10_20_120518_add_attempt_token_to_results_table', 7),
(29, '2025_10_21_121159_update_interviews_table_for_bsit_rubric', 8),
(30, '2025_10_21_122717_add_claimed_columns_to_interviews_table', 9),
(31, '2025_10_21_124910_add_final_comments_to_interviews_table', 10);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` bigint UNSIGNED NOT NULL,
  `exam_id` bigint UNSIGNED DEFAULT NULL,
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_type` enum('multiple_choice','true_false','short_answer','essay') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'multiple_choice',
  `correct_answer` tinyint(1) DEFAULT NULL,
  `points` int NOT NULL DEFAULT '1',
  `order_number` int DEFAULT NULL,
  `explanation` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `exam_id`, `question_text`, `question_type`, `correct_answer`, `points`, `order_number`, `explanation`, `is_active`, `created_at`, `updated_at`) VALUES
(54, 1, 'What does HTML stand for?', 'multiple_choice', NULL, 1, 1, 'HTML stands for Hyper Text Markup Language, the standard markup language for creating web pages.', 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(55, 1, 'Which programming language is known as the \"language of the web\"?', 'multiple_choice', NULL, 1, 2, 'JavaScript is widely known as the language of the web, running in every modern browser.', 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(56, 1, 'What is the main function of an operating system?', 'multiple_choice', NULL, 1, 3, 'An operating system manages computer hardware and software resources and provides common services for computer programs.', 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(57, 1, 'In networking, what does IP stand for?', 'multiple_choice', NULL, 1, 4, 'IP stands for Internet Protocol, which is responsible for addressing and routing data across networks.', 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(58, 1, 'Which data structure uses LIFO (Last In, First Out) principle?', 'multiple_choice', NULL, 1, 5, 'A Stack follows the LIFO principle where the last element added is the first one to be removed.', 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(59, 1, 'SQL stands for Structured Query Language.', 'true_false', 1, 1, 6, 'SQL stands for Structured Query Language, used for managing relational databases.', 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(60, 1, 'Python is a compiled programming language.', 'true_false', 0, 1, 7, 'Python is an interpreted language, not compiled. It executes code line by line.', 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(61, 1, 'HTTP stands for HyperText Transfer Protocol.', 'true_false', 1, 1, 8, 'HTTP is the foundation protocol for data communication on the World Wide Web.', 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(62, 1, 'RAM is a type of permanent storage.', 'true_false', 0, 1, 9, 'RAM (Random Access Memory) is volatile memory that loses data when power is off.', 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(63, 1, 'A byte consists of 8 bits.', 'true_false', 1, 1, 10, 'A byte is the fundamental unit of computer storage consisting of 8 bits.', 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25');

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `option_id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `option_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0',
  `order_number` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question_options`
--

INSERT INTO `question_options` (`option_id`, `question_id`, `option_text`, `is_correct`, `order_number`, `created_at`, `updated_at`) VALUES
(107, 54, 'Hyper Text Markup Language', 1, 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(108, 54, 'High Tech Modern Language', 0, 2, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(109, 54, 'Home Tool Markup Language', 0, 3, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(110, 54, 'Hyperlinks and Text Markup Language', 0, 4, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(111, 55, 'Python', 0, 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(112, 55, 'Java', 0, 2, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(113, 55, 'JavaScript', 1, 3, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(114, 55, 'C++', 0, 4, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(115, 56, 'Virus protection', 0, 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(116, 56, 'Manage computer hardware and software', 1, 2, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(117, 56, 'Word processing', 0, 3, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(118, 56, 'Internet browsing', 0, 4, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(119, 57, 'Internet Protocol', 1, 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(120, 57, 'Internal Process', 0, 2, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(121, 57, 'Integrated Platform', 0, 3, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(122, 57, 'Information Provider', 0, 4, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(123, 58, 'Queue', 0, 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(124, 58, 'Stack', 1, 2, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(125, 58, 'Array', 0, 3, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(126, 58, 'Linked List', 0, 4, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(127, 59, 'True', 1, 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(128, 59, 'False', 0, 2, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(129, 60, 'True', 0, 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(130, 60, 'False', 1, 2, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(131, 61, 'True', 1, 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(132, 61, 'False', 0, 2, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(133, 62, 'True', 0, 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(134, 62, 'False', 1, 2, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(135, 63, 'True', 1, 1, '2025-10-20 04:10:25', '2025-10-20 04:10:25'),
(136, 63, 'False', 0, 2, '2025-10-20 04:10:25', '2025-10-20 04:10:25');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `result_id` bigint UNSIGNED NOT NULL,
  `applicant_id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `answer_text` text COLLATE utf8mb4_unicode_ci,
  `selected_option_id` bigint UNSIGNED DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_earned` decimal(5,2) NOT NULL DEFAULT '0.00',
  `answered_at` timestamp NOT NULL,
  `attempt_token` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`result_id`, `applicant_id`, `question_id`, `answer_text`, `selected_option_id`, `is_correct`, `points_earned`, `answered_at`, `attempt_token`, `created_at`, `updated_at`) VALUES
(237, 22, 54, 'Hyper Text Markup Language', 107, 1, 1.00, '2025-10-20 04:15:28', '60e79ce5-0c85-47df-99c8-3c52550fd35b', '2025-10-20 04:15:28', '2025-10-20 04:15:28'),
(238, 22, 55, 'JavaScript', 113, 1, 1.00, '2025-10-20 04:15:28', '60e79ce5-0c85-47df-99c8-3c52550fd35b', '2025-10-20 04:15:28', '2025-10-20 04:15:28'),
(239, 22, 56, 'Manage computer hardware and software', 116, 1, 1.00, '2025-10-20 04:15:28', '60e79ce5-0c85-47df-99c8-3c52550fd35b', '2025-10-20 04:15:28', '2025-10-20 04:15:28'),
(240, 22, 57, 'Internet Protocol', 119, 1, 1.00, '2025-10-20 04:15:28', '60e79ce5-0c85-47df-99c8-3c52550fd35b', '2025-10-20 04:15:28', '2025-10-20 04:15:28'),
(241, 22, 58, 'Stack', 124, 1, 1.00, '2025-10-20 04:15:28', '60e79ce5-0c85-47df-99c8-3c52550fd35b', '2025-10-20 04:15:28', '2025-10-20 04:15:28'),
(242, 22, 59, 'True', NULL, 0, 0.00, '2025-10-20 04:15:28', '60e79ce5-0c85-47df-99c8-3c52550fd35b', '2025-10-20 04:15:28', '2025-10-20 04:15:28'),
(243, 22, 60, 'True', NULL, 0, 0.00, '2025-10-20 04:15:28', '60e79ce5-0c85-47df-99c8-3c52550fd35b', '2025-10-20 04:15:28', '2025-10-20 04:15:28'),
(244, 22, 61, 'True', NULL, 0, 0.00, '2025-10-20 04:15:28', '60e79ce5-0c85-47df-99c8-3c52550fd35b', '2025-10-20 04:15:28', '2025-10-20 04:15:28'),
(245, 22, 62, 'False', NULL, 0, 0.00, '2025-10-20 04:15:28', '60e79ce5-0c85-47df-99c8-3c52550fd35b', '2025-10-20 04:15:28', '2025-10-20 04:15:28'),
(246, 22, 63, 'False', NULL, 0, 0.00, '2025-10-20 04:15:28', '60e79ce5-0c85-47df-99c8-3c52550fd35b', '2025-10-20 04:15:28', '2025-10-20 04:15:28'),
(247, 6, 54, 'Hyper Text Markup Language', 107, 1, 1.00, '2025-10-20 04:28:10', '1935c8d5-02a8-43aa-9aa8-31f58cdd0301', '2025-10-20 04:28:10', '2025-10-20 04:28:10'),
(248, 6, 55, 'JavaScript', 113, 1, 1.00, '2025-10-20 04:28:10', '1935c8d5-02a8-43aa-9aa8-31f58cdd0301', '2025-10-20 04:28:10', '2025-10-20 04:28:10'),
(249, 6, 56, 'Manage computer hardware and software', 116, 1, 1.00, '2025-10-20 04:28:10', '1935c8d5-02a8-43aa-9aa8-31f58cdd0301', '2025-10-20 04:28:10', '2025-10-20 04:28:10'),
(250, 6, 57, 'Internet Protocol', 119, 1, 1.00, '2025-10-20 04:28:10', '1935c8d5-02a8-43aa-9aa8-31f58cdd0301', '2025-10-20 04:28:10', '2025-10-20 04:28:10'),
(251, 6, 58, 'Stack', 124, 1, 1.00, '2025-10-20 04:28:10', '1935c8d5-02a8-43aa-9aa8-31f58cdd0301', '2025-10-20 04:28:10', '2025-10-20 04:28:10'),
(252, 6, 59, 'True', NULL, 1, 1.00, '2025-10-20 04:28:10', '1935c8d5-02a8-43aa-9aa8-31f58cdd0301', '2025-10-20 04:28:10', '2025-10-20 04:28:10'),
(253, 6, 60, 'True', NULL, 0, 0.00, '2025-10-20 04:28:10', '1935c8d5-02a8-43aa-9aa8-31f58cdd0301', '2025-10-20 04:28:10', '2025-10-20 04:28:10'),
(254, 6, 61, 'True', NULL, 1, 1.00, '2025-10-20 04:28:10', '1935c8d5-02a8-43aa-9aa8-31f58cdd0301', '2025-10-20 04:28:10', '2025-10-20 04:28:10'),
(255, 6, 62, 'False', NULL, 1, 1.00, '2025-10-20 04:28:10', '1935c8d5-02a8-43aa-9aa8-31f58cdd0301', '2025-10-20 04:28:10', '2025-10-20 04:28:10'),
(256, 6, 63, 'True', NULL, 1, 1.00, '2025-10-20 04:28:10', '1935c8d5-02a8-43aa-9aa8-31f58cdd0301', '2025-10-20 04:28:10', '2025-10-20 04:28:10'),
(257, 7, 54, 'Hyper Text Markup Language', 107, 1, 1.00, '2025-10-20 20:52:16', '5139ebdb-4a50-48a9-b0d2-22f5e639b310', '2025-10-20 20:52:16', '2025-10-20 20:52:16'),
(258, 7, 55, 'JavaScript', 113, 1, 1.00, '2025-10-20 20:52:16', '5139ebdb-4a50-48a9-b0d2-22f5e639b310', '2025-10-20 20:52:16', '2025-10-20 20:52:16'),
(259, 7, 56, 'Manage computer hardware and software', 116, 1, 1.00, '2025-10-20 20:52:16', '5139ebdb-4a50-48a9-b0d2-22f5e639b310', '2025-10-20 20:52:16', '2025-10-20 20:52:16'),
(260, 7, 57, 'Internet Protocol', 119, 1, 1.00, '2025-10-20 20:52:16', '5139ebdb-4a50-48a9-b0d2-22f5e639b310', '2025-10-20 20:52:16', '2025-10-20 20:52:16'),
(261, 7, 58, 'Stack', 124, 1, 1.00, '2025-10-20 20:52:16', '5139ebdb-4a50-48a9-b0d2-22f5e639b310', '2025-10-20 20:52:16', '2025-10-20 20:52:16'),
(262, 7, 59, 'True', NULL, 1, 1.00, '2025-10-20 20:52:16', '5139ebdb-4a50-48a9-b0d2-22f5e639b310', '2025-10-20 20:52:16', '2025-10-20 20:52:16'),
(263, 7, 60, 'True', NULL, 0, 0.00, '2025-10-20 20:52:16', '5139ebdb-4a50-48a9-b0d2-22f5e639b310', '2025-10-20 20:52:16', '2025-10-20 20:52:16'),
(264, 7, 61, 'True', NULL, 1, 1.00, '2025-10-20 20:52:16', '5139ebdb-4a50-48a9-b0d2-22f5e639b310', '2025-10-20 20:52:16', '2025-10-20 20:52:16'),
(265, 7, 62, 'True', NULL, 0, 0.00, '2025-10-20 20:52:16', '5139ebdb-4a50-48a9-b0d2-22f5e639b310', '2025-10-20 20:52:16', '2025-10-20 20:52:16'),
(266, 7, 63, 'True', NULL, 1, 1.00, '2025-10-20 20:52:16', '5139ebdb-4a50-48a9-b0d2-22f5e639b310', '2025-10-20 20:52:16', '2025-10-20 20:52:16'),
(267, 7, 54, 'Hyper Text Markup Language', 107, 1, 1.00, '2025-10-20 20:52:17', '45e9d64b-cf3b-4afb-a37a-eb4d64792ca7', '2025-10-20 20:52:17', '2025-10-20 20:52:17'),
(268, 7, 55, 'JavaScript', 113, 1, 1.00, '2025-10-20 20:52:17', '45e9d64b-cf3b-4afb-a37a-eb4d64792ca7', '2025-10-20 20:52:17', '2025-10-20 20:52:17'),
(269, 7, 56, 'Manage computer hardware and software', 116, 1, 1.00, '2025-10-20 20:52:17', '45e9d64b-cf3b-4afb-a37a-eb4d64792ca7', '2025-10-20 20:52:17', '2025-10-20 20:52:17'),
(270, 7, 57, 'Internet Protocol', 119, 1, 1.00, '2025-10-20 20:52:18', '45e9d64b-cf3b-4afb-a37a-eb4d64792ca7', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(271, 7, 58, 'Stack', 124, 1, 1.00, '2025-10-20 20:52:18', '45e9d64b-cf3b-4afb-a37a-eb4d64792ca7', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(272, 7, 59, 'True', NULL, 1, 1.00, '2025-10-20 20:52:18', '45e9d64b-cf3b-4afb-a37a-eb4d64792ca7', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(273, 7, 60, 'True', NULL, 0, 0.00, '2025-10-20 20:52:18', '45e9d64b-cf3b-4afb-a37a-eb4d64792ca7', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(274, 7, 61, 'True', NULL, 1, 1.00, '2025-10-20 20:52:18', '45e9d64b-cf3b-4afb-a37a-eb4d64792ca7', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(275, 7, 62, 'True', NULL, 0, 0.00, '2025-10-20 20:52:18', '45e9d64b-cf3b-4afb-a37a-eb4d64792ca7', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(276, 7, 63, 'True', NULL, 1, 1.00, '2025-10-20 20:52:18', '45e9d64b-cf3b-4afb-a37a-eb4d64792ca7', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(277, 7, 54, 'Hyper Text Markup Language', 107, 1, 1.00, '2025-10-20 20:52:18', 'b8a54503-0023-4a4c-b55f-262c0d4ed8c0', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(278, 7, 55, 'JavaScript', 113, 1, 1.00, '2025-10-20 20:52:18', 'b8a54503-0023-4a4c-b55f-262c0d4ed8c0', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(279, 7, 56, 'Manage computer hardware and software', 116, 1, 1.00, '2025-10-20 20:52:18', 'b8a54503-0023-4a4c-b55f-262c0d4ed8c0', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(280, 7, 57, 'Internet Protocol', 119, 1, 1.00, '2025-10-20 20:52:18', 'b8a54503-0023-4a4c-b55f-262c0d4ed8c0', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(281, 7, 58, 'Stack', 124, 1, 1.00, '2025-10-20 20:52:18', 'b8a54503-0023-4a4c-b55f-262c0d4ed8c0', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(282, 7, 59, 'True', NULL, 1, 1.00, '2025-10-20 20:52:18', 'b8a54503-0023-4a4c-b55f-262c0d4ed8c0', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(283, 7, 60, 'True', NULL, 0, 0.00, '2025-10-20 20:52:18', 'b8a54503-0023-4a4c-b55f-262c0d4ed8c0', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(284, 7, 61, 'True', NULL, 1, 1.00, '2025-10-20 20:52:18', 'b8a54503-0023-4a4c-b55f-262c0d4ed8c0', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(285, 7, 62, 'True', NULL, 0, 0.00, '2025-10-20 20:52:18', 'b8a54503-0023-4a4c-b55f-262c0d4ed8c0', '2025-10-20 20:52:18', '2025-10-20 20:52:18'),
(286, 7, 63, 'True', NULL, 1, 1.00, '2025-10-20 20:52:18', 'b8a54503-0023-4a4c-b55f-262c0d4ed8c0', '2025-10-20 20:52:18', '2025-10-20 20:52:18');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('8Hz3WonSCRbx5NTrP4sejX7oOQPeCd1fU6PgvOpz', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWjZlOGNNOTVBNUx5VlJVNno3RDJxa05iYTBRWXZia01KMGdIZWN0RSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjE6Imh0dHA6Ly9lbnJvbGxhc3Nlc3MudGVzdC9pbnN0cnVjdG9yL2FwcGxpY2FudHM/ZmlsdGVyPXBlbmRpbmciO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1761022753),
('PNmcw55Q8gWCbZ2RvgHGolQ7Vc73nB89qPjh8Jwj', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoid3VyTXhQN20yN1lDQWdzdDlERmRjSXVtenRpYlZDOVB5MDNKMm9jSiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTk6Imh0dHA6Ly9lbnJvbGxhc3Nlc3MudGVzdC9pbnN0cnVjdG9yL2ludGVydmlldy9hcHBsaWNhbnRzLzIzIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mjt9', 1761051611),
('qc6LcFNv3em3YAN6dw1wnvMedbyM3cBtyAu1ofaG', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNUI1cVdGaUlURWdIS2t4Z05MZklXMzFwRFFiV1pXSTRxNWo0UkhQWiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly9lbnJvbGxhc3Nlc3MudGVzdC9hZG1pbi9kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1761051314),
('wcrY8iYnzUdGCmHMmEXCwSLwGTIO5ePv7P8Xitla', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoicUtnTThScENJMTJ4bm12dlpldjRlRlREYjlSd1FvTlNjVVl3YXhndCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly9lbnJvbGxhc3Nlc3MudGVzdC9leGFtL3Jlc3VsdHMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjExOiJhY2Nlc3NfY29kZSI7czoxMzoiQlNJVC1NTEVYVkdDSyI7czoxMjoiYXBwbGljYW50X2lkIjtpOjc7czoxODoiZXhhbV9hdHRlbXB0X3Rva2VuIjtzOjM2OiJiOGE1NDUwMy0wMDIzLTRhNGMtYjU1Zi0yNjJjMGQ0ZWQ4YzAiO30=', 1761022344);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint UNSIGNED NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('department-head','administrator','instructor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'instructor',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `password`, `full_name`, `role`, `email`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'dept_head', '$2y$12$uqvI2bBJEJZJV2fvE7YhbOtSj5coUXcSeAb/p0zVluY44nqdjGKsC', NULL, 'Dr. Maria Elena Santos', 'department-head', 'maria.santos@evsu.edu.ph', NULL, NULL, '2025-10-08 07:20:58', '2025-10-08 07:20:58'),
(2, 'instructor1', '$2y$12$nOSEJg8vnj3gjnfalGULo.tiJyoHSUiAjDCh6thyIUO0qcJQugL3a', NULL, 'Prof. Michael Angelo Garcia', 'instructor', 'michael.garcia@evsu.edu.ph', NULL, NULL, '2025-10-08 07:20:58', '2025-10-08 07:20:58'),
(3, 'instructor2', '$2y$12$NNgWrPwwN8z1a3XzyJyhKelfPh3lh8nCleuZ5NQakwYcbp4wBXwoa', NULL, 'Prof. Lisa Marie Torres', 'instructor', 'lisa.torres@evsu.edu.ph', NULL, NULL, '2025-10-08 07:20:58', '2025-10-08 07:20:58'),
(4, 'instructor3', '$2y$12$mAOKMR.gCQgFQoUqxj2zD.KXFfLk2uuQ3ChjGNKJcc9cbmop2jzIC', NULL, 'Prof. Robert James Villanueva', 'instructor', 'robert.villanueva@evsu.edu.ph', NULL, NULL, '2025-10-08 07:20:58', '2025-10-08 07:20:58'),
(5, 'instructor4', '$2y$12$8bXc9NvXO/SXyIAakDlsx.V4kujpPag8BeUdesiexpLXL3..iZLz2', NULL, 'Prof. Sarah Jane Mendoza', 'instructor', 'sarah.mendoza@evsu.edu.ph', NULL, NULL, '2025-10-08 07:20:58', '2025-10-08 07:20:58'),
(6, 'instructor5', '$2y$12$Ct41aB4Km9t6HWz74oMBAeIT9TQ8lXOI6JB5PuRgVoIiuAqelHA7S', NULL, 'Prof. Christopher Paul Ramos', 'instructor', 'christopher.ramos@evsu.edu.ph', NULL, NULL, '2025-10-08 07:20:58', '2025-10-08 07:20:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_codes`
--
ALTER TABLE `access_codes`
  ADD PRIMARY KEY (`code`),
  ADD KEY `access_codes_applicant_id_foreign` (`applicant_id`),
  ADD KEY `access_codes_exam_id_index` (`exam_id`);

--
-- Indexes for table `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`applicant_id`),
  ADD UNIQUE KEY `applicants_application_no_unique` (`application_no`),
  ADD UNIQUE KEY `applicants_email_address_unique` (`email_address`),
  ADD KEY `applicants_assigned_instructor_id_foreign` (`assigned_instructor_id`),
  ADD KEY `applicants_last_name_first_name_index` (`last_name`,`first_name`),
  ADD KEY `applicants_preferred_course_index` (`preferred_course`),
  ADD KEY `idx_applicants_status_created` (`status`,`created_at`);

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
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`exam_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`interview_id`),
  ADD KEY `interviews_applicant_id_foreign` (`applicant_id`),
  ADD KEY `interviews_interviewer_id_foreign` (`interviewer_id`),
  ADD KEY `interviews_claimed_by_foreign` (`claimed_by`);

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
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `questions_exam_id_foreign` (`exam_id`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `question_options_question_id_foreign` (`question_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `results_question_id_foreign` (`question_id`),
  ADD KEY `results_selected_option_id_foreign` (`selected_option_id`),
  ADD KEY `results_applicant_id_question_id_index` (`applicant_id`,`question_id`),
  ADD KEY `results_attempt_token_index` (`attempt_token`);

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
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applicants`
--
ALTER TABLE `applicants`
  MODIFY `applicant_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `exam_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `interview_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `option_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=137;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `result_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_codes`
--
ALTER TABLE `access_codes`
  ADD CONSTRAINT `access_codes_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `access_codes_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE;

--
-- Constraints for table `applicants`
--
ALTER TABLE `applicants`
  ADD CONSTRAINT `applicants_assigned_instructor_id_foreign` FOREIGN KEY (`assigned_instructor_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `interviews_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interviews_claimed_by_foreign` FOREIGN KEY (`claimed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `interviews_interviewer_id_foreign` FOREIGN KEY (`interviewer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`exam_id`) ON DELETE CASCADE;

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `results_applicant_id_foreign` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`applicant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `results_selected_option_id_foreign` FOREIGN KEY (`selected_option_id`) REFERENCES `question_options` (`option_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
