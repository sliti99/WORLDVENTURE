-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2025 at 10:00 AM 
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
-- Database: `worldventure`
--
CREATE DATABASE IF NOT EXISTS `worldventure` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `worldventure`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
-- User table first for foreign keys
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('visitor','user','admin','support') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
-- Note: Passwords should be properly hashed in a real application. '$2y$...' suggests bcrypt.
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, '2025-04-03 23:36:08', '2025-04-03 23:36:08'),
(2, 'Regular User', 'user@example.com', '$2y$10$examplePasswordHashUser', 'user', NULL, '2025-04-16 08:00:00', '2025-04-16 08:00:00');


-- --------------------------------------------------------

--
-- Table structure for table `admins` - Consider merging into `users` table with role='admin'
-- Keeping separate for now as per original structure, but adding missing columns from INSERT
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `name`, `email`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$exDW.0o9RHJQKyXHr5pDPuHsQzOC3FD8uV6Qg80IBhtPyOsP.9YFu', 'Administrateur', 'admin@worldventure.com', 'admin', '2025-03-09 23:16:47', '2025-03-09 23:16:47'),
(2, 'admin2', 'admin2', 'adminis', 'adminis@esp.t', 'support', '2025-03-09 23:42:27', '2025-03-09 23:42:27');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Travel Tips', 'travel-tips', '2025-04-03 23:36:08'),
(2, 'Destinations', 'destinations', '2025-04-03 23:36:08'),
(3, 'Adventure', 'adventure', '2025-04-03 23:36:08'),
(4, 'Budget Travel', 'budget-travel', '2025-04-03 23:36:08');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `status` enum('published','draft','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `reactions` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   UNIQUE KEY `slug` (`slug`),
   KEY `author_id` (`author_id`),
   CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE -- Link to users table
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `content`, `excerpt`, `image`, `author_id`, `status`, `reactions`, `created_at`, `updated_at`) VALUES
(1, 'Welcome to WorldVenture', 'welcome-to-worldventure', '<p>This is your first blog post on WorldVenture. Edit or delete it, then start writing!</p>', 'This is your first blog post on WorldVenture.', NULL, 1, 'published', 5, '2025-04-03 23:36:08', '2025-04-16 09:30:00'),
(2, 'Top 10 Travel Destinations in 2025', 'top-10-travel-destinations-2025', '<p>Explore the most amazing destinations of 2025!</p><p>From beaches to mountains, we have covered everything in this comprehensive guide.</p>', 'Discover the most popular travel spots for your next adventure.', NULL, 1, 'published', 12, '2025-04-03 23:36:08', '2025-04-16 09:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL, -- Added user_id column
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reactions` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `post_id` (`post_id`),
   KEY `user_id` (`user_id`),
   CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE, -- Link to posts table
   CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE -- Link to users table
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `reactions`, `created_at`) VALUES
(1, 1, 1, 'This is a great first post! Looking forward to more content.', 3, '2025-04-03 23:36:08'),
(2, 2, 2, 'I love these destination suggestions! I would also add Paris to the list.', 7, '2025-04-03 23:36:08');

-- --------------------------------------------------------

--
-- Table structure for table `destinations` (Assuming structure based on usage)
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offers` (Assuming structure based on usage)
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `destination_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `destination_id` (`destination_id`),
   CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL -- Link to destinations
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reclamations` (Assuming structure based on usage)
--

CREATE TABLE `reclamations` (
  `id_reclamation` int(11) NOT NULL AUTO_INCREMENT,
  `id_user_reclamation` int(11) NOT NULL, -- Assuming link to users
  `subject` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `etat_reclamation` varchar(500) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`id_reclamation`),
   KEY `id_user_reclamation` (`id_user_reclamation`),
   CONSTRAINT `reclamations_ibfk_1` FOREIGN KEY (`id_user_reclamation`) REFERENCES `users` (`id`) ON DELETE CASCADE -- Link to users
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `responses` (Assuming structure based on usage, likely responses to reclamations)
--

CREATE TABLE `responses` (
  `id_reponse` int(11) NOT NULL AUTO_INCREMENT,
  `id_reclamation` int(11) NOT NULL, -- Link to the reclamation
  `id_user_reponse` int(11) NOT NULL, -- User who responded (likely admin/support)
  `response_message` text COLLATE utf8mb4_general_ci NOT NULL,
  `date_reponse` timestamp NOT NULL DEFAULT current_timestamp(), -- Changed to timestamp
   PRIMARY KEY (`id_reponse`),
   KEY `id_reclamation` (`id_reclamation`),
   KEY `id_user_reponse` (`id_user_reponse`),
   CONSTRAINT `responses_ibfk_1` FOREIGN KEY (`id_reclamation`) REFERENCES `reclamations` (`id_reclamation`) ON DELETE CASCADE,
   CONSTRAINT `responses_ibfk_2` FOREIGN KEY (`id_user_reponse`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'site_title', 'WorldVenture - Travel Blog', '2025-04-03 23:36:08', '2025-04-03 23:36:08'),
(2, 'site_description', 'Explore the world with WorldVenture - Your ultimate travel companion', '2025-04-03 23:36:08', '2025-04-03 23:36:08'),
(3, 'blog_posts_per_page', '10', '2025-04-03 23:36:08', '2025-04-03 23:36:08'),
(4, 'enable_comments', 'true', '2025-04-03 23:36:08', '2025-04-03 23:36:08');

-- --------------------------------------------------------

--
-- Table structure for table `reactions_log`
--

CREATE TABLE `reactions_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `item_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reaction` (`user_id`, `type`, `item_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reactions_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
