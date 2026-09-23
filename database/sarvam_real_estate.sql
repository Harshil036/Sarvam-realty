-- phpMyAdmin SQL Dump
-- Host: localhost
-- Generation Time: Aug 06, 2026 at 10:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sarvam_real_estate`
--
CREATE DATABASE IF NOT EXISTS `sarvam_real_estate` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sarvam_real_estate`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
-- password: Admin@123 (hashed)
--

INSERT INTO `admin` (`id`, `name`, `email`, `password`, `phone`, `profile_image`, `created_at`) VALUES
(1, 'Super Admin', 'admin@sarvam.com', '$2y$10$fV.M5X.3cW9B98oM1wP7zOCP8T2lK/X.i/HjZk/xGq5X.M.0kYn3.', '9876543210', 'admin.jpg', '2026-08-01 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','banned') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
-- password: User@123 (hashed)
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `address`, `profile_image`, `security_question`, `security_answer`, `status`, `created_at`, `updated_at`) VALUES
(1, 'John', 'Doe', 'john@example.com', '$2y$10$w09u7uR3n9W0V32e7t9.QOL8fQ0uE.Z8EwZ0R8wE8.O/h8n0qO/T6', '9876543211', '123 Main St, Mumbai', 'user1.jpg', 'What is your pet name?', 'Fluffy', 'active', '2026-08-01 10:00:00', '2026-08-01 10:00:00'),
(2, 'Jane', 'Smith', 'jane@example.com', '$2y$10$w09u7uR3n9W0V32e7t9.QOL8fQ0uE.Z8EwZ0R8wE8.O/h8n0qO/T6', '9876543212', '456 Park Ave, Delhi', 'user2.jpg', 'What is your pet name?', 'Buddy', 'active', '2026-08-02 11:00:00', '2026-08-02 11:00:00'),
(3, 'Rahul', 'Sharma', 'rahul@example.com', '$2y$10$w09u7uR3n9W0V32e7t9.QOL8fQ0uE.Z8EwZ0R8wE8.O/h8n0qO/T6', '9876543213', '789 MG Road, Bangalore', 'user3.jpg', 'What is your pet name?', 'Tommy', 'active', '2026-08-03 12:00:00', '2026-08-03 12:00:00'),
(4, 'Priya', 'Patel', 'priya@example.com', '$2y$10$w09u7uR3n9W0V32e7t9.QOL8fQ0uE.Z8EwZ0R8wE8.O/h8n0qO/T6', '9876543214', '101 Ring Road, Pune', 'user4.jpg', 'What is your pet name?', 'Lucy', 'active', '2026-08-04 13:00:00', '2026-08-04 13:00:00'),
(5, 'Amit', 'Kumar', 'amit@example.com', '$2y$10$w09u7uR3n9W0V32e7t9.QOL8fQ0uE.Z8EwZ0R8wE8.O/h8n0qO/T6', '9876543215', '202 Linking Road, Chennai', 'user5.jpg', 'What is your pet name?', 'Rocky', 'active', '2026-08-05 14:00:00', '2026-08-05 14:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `properties_sold` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `name`, `email`, `phone`, `bio`, `photo`, `experience_years`, `properties_sold`, `rating`, `status`, `created_at`) VALUES
(1, 'Vikram Singh', 'vikram@sarvam.com', '9876500001', 'Expert in luxury apartments in South Mumbai.', 'agent1.jpg', 10, 150, 4.80, 'active', '2026-08-01 10:00:00'),
(2, 'Neha Gupta', 'neha@sarvam.com', '9876500002', 'Specializes in commercial spaces in Delhi NCR.', 'agent2.jpg', 8, 120, 4.90, 'active', '2026-08-01 10:00:00'),
(3, 'Arjun Reddy', 'arjun@sarvam.com', '9876500003', 'Top agent for IT corridors in Bangalore.', 'agent3.jpg', 5, 80, 4.70, 'active', '2026-08-01 10:00:00'),
(4, 'Sneha Desai', 'sneha.d@sarvam.com', '9876500004', 'Experienced in residential plots and villas in Pune.', 'agent4.jpg', 12, 200, 4.95, 'active', '2026-08-01 10:00:00'),
(5, 'Rajesh Iyer', 'rajesh@sarvam.com', '9876500005', 'Focused on premium properties in Chennai.', 'agent5.jpg', 7, 95, 4.60, 'active', '2026-08-01 10:00:00'),
(6, 'Pooja Joshi', 'pooja@sarvam.com', '9876500006', 'Specialist in affordable housing projects across India.', 'agent6.jpg', 4, 60, 4.50, 'active', '2026-08-01 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `property_types`
--

CREATE TABLE `property_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_name` (`type_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_types`
--

INSERT INTO `property_types` (`id`, `type_name`, `icon`, `description`, `status`) VALUES
(1, 'Apartment', 'bi-building', 'Multi-story residential buildings', 'active'),
(2, 'Villa', 'bi-house-door', 'Independent houses and villas', 'active'),
(3, 'Commercial', 'bi-shop', 'Shops, offices and retail spaces', 'active'),
(4, 'Plot', 'bi-map', 'Empty land and residential plots', 'active'),
(5, 'Penthouse', 'bi-stars', 'Luxury top-floor apartments', 'active'),
(6, 'Studio', 'bi-house', 'Compact single-room apartments', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'India',
  `pincode` varchar(10) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `city`, `state`, `country`, `pincode`, `status`) VALUES
(1, 'Mumbai', 'Maharashtra', 'India', '400001', 'active'),
(2, 'Delhi', 'Delhi', 'India', '110001', 'active'),
(3, 'Bangalore', 'Karnataka', 'India', '560001', 'active'),
(4, 'Pune', 'Maharashtra', 'India', '411001', 'active'),
(5, 'Chennai', 'Tamil Nadu', 'India', '600001', 'active'),
(6, 'Hyderabad', 'Telangana', 'India', '500001', 'active'),
(7, 'Kolkata', 'West Bengal', 'India', '700001', 'active'),
(8, 'Ahmedabad', 'Gujarat', 'India', '380001', 'active'),
(9, 'Jaipur', 'Rajasthan', 'India', '302001', 'active'),
(10, 'Chandigarh', 'Chandigarh', 'India', '160001', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `property_type_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `area_sqft` int(11) NOT NULL,
  `bedrooms` int(11) DEFAULT 0,
  `bathrooms` int(11) DEFAULT 0,
  `parking` int(11) DEFAULT 0,
  `furnished_status` enum('furnished','semi-furnished','unfurnished') NOT NULL DEFAULT 'unfurnished',
  `listing_type` enum('buy','rent') NOT NULL DEFAULT 'buy',
  `status` enum('available','sold','rented','inactive') NOT NULL DEFAULT 'available',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_premium` tinyint(1) NOT NULL DEFAULT 0,
  `amenities` text DEFAULT NULL,
  `address` text NOT NULL,
  `main_image` varchar(255) DEFAULT 'default_property.jpg',
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `property_type_id` (`property_type_id`),
  KEY `location_id` (`location_id`),
  KEY `agent_id` (`agent_id`),
  CONSTRAINT `fk_property_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_property_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_property_type` FOREIGN KEY (`property_type_id`) REFERENCES `property_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `title`, `description`, `property_type_id`, `location_id`, `agent_id`, `price`, `area_sqft`, `bedrooms`, `bathrooms`, `parking`, `furnished_status`, `listing_type`, `status`, `is_featured`, `is_premium`, `amenities`, `address`, `main_image`, `views`, `created_at`, `updated_at`) VALUES
(1, 'Luxury Sea View Apartment in Worli', 'Experience ultimate luxury in this 4BHK apartment with uninterrupted sea views.', 1, 1, 1, 50000000.00, 2500, 4, 4, 2, 'furnished', 'buy', 'available', 1, 1, '[\"Swimming Pool\", \"Gym\", \"Security\", \"Club House\"]', 'Worli Sea Face, Mumbai', 'prop1.jpg', 1200, '2026-08-01 10:00:00', '2026-08-01 10:00:00'),
(2, 'Spacious Villa in Vasant Vihar', 'Beautiful independent villa with private garden in prime Delhi location.', 2, 2, 2, 45000000.00, 3500, 5, 5, 3, 'semi-furnished', 'buy', 'available', 1, 1, '[\"Garden\", \"Power Backup\", \"Security\"]', 'Vasant Vihar, Delhi', 'prop2.jpg', 950, '2026-08-02 10:00:00', '2026-08-02 10:00:00'),
(3, 'Modern Office Space in Koramangala', 'Fully furnished IT office space ready to move in.', 3, 3, 3, 150000.00, 1500, 0, 2, 2, 'furnished', 'rent', 'available', 0, 0, '[\"AC\", \"Cafeteria\", \"Conference Room\", \"WiFi\"]', 'Koramangala, Bangalore', 'prop3.jpg', 450, '2026-08-03 10:00:00', '2026-08-03 10:00:00'),
(4, 'Residential Plot in Wakad', 'Clear title NA plot perfect for building your dream home.', 4, 4, 4, 8500000.00, 2000, 0, 0, 0, 'unfurnished', 'buy', 'available', 0, 0, '[\"Gated Community\", \"Water Supply\"]', 'Wakad, Pune', 'prop4.jpg', 320, '2026-08-04 10:00:00', '2026-08-04 10:00:00'),
(5, 'Premium Penthouse in ECR', 'Lavish penthouse with private terrace and infinity pool.', 5, 5, 5, 35000000.00, 3000, 4, 5, 2, 'furnished', 'buy', 'available', 1, 1, '[\"Private Pool\", \"Terrace\", \"Gym\"]', 'East Coast Road, Chennai', 'prop5.jpg', 890, '2026-08-05 10:00:00', '2026-08-05 10:00:00'),
(6, 'Cozy Studio near HITEC City', 'Ideal for bachelors and IT professionals.', 6, 6, 6, 25000.00, 500, 1, 1, 1, 'furnished', 'rent', 'available', 0, 0, '[\"WiFi\", \"Security\", \"Maintenance Staff\"]', 'HITEC City, Hyderabad', 'prop6.jpg', 560, '2026-08-06 10:00:00', '2026-08-06 10:00:00'),
(7, '3BHK Flat in New Town', 'Spacious apartment in a major residential complex.', 1, 7, 1, 12000000.00, 1600, 3, 3, 1, 'semi-furnished', 'buy', 'available', 0, 0, '[\"Club House\", \"Play Area\", \"Security\"]', 'New Town, Kolkata', 'prop7.jpg', 410, '2026-08-07 10:00:00', '2026-08-07 10:00:00'),
(8, 'Commercial Shop in SG Highway', 'Prime location road facing retail shop.', 3, 8, 2, 22000000.00, 800, 0, 1, 1, 'unfurnished', 'buy', 'available', 1, 0, '[\"Visitor Parking\", \"Main Road Facing\"]', 'SG Highway, Ahmedabad', 'prop8.jpg', 380, '2026-08-08 10:00:00', '2026-08-08 10:00:00'),
(9, 'Heritage Villa in C Scheme', 'Restored traditional villa with modern amenities.', 2, 9, 4, 28000000.00, 2800, 4, 4, 2, 'furnished', 'buy', 'available', 1, 1, '[\"Garden\", \"Courtyard\", \"Power Backup\"]', 'C Scheme, Jaipur', 'prop9.jpg', 620, '2026-08-09 10:00:00', '2026-08-09 10:00:00'),
(10, '2BHK Apartment in Sector 17', 'Centrally located flat in the heart of the city.', 1, 10, 6, 9500000.00, 1100, 2, 2, 1, 'semi-furnished', 'buy', 'available', 0, 0, '[\"Park\", \"Security\"]', 'Sector 17, Chandigarh', 'prop10.jpg', 290, '2026-08-10 10:00:00', '2026-08-10 10:00:00'),
(11, 'Luxury Villa in Bandra West', 'Exquisite 5BHK villa in one of Mumbai\'s most sought-after neighborhoods.', 2, 1, 1, 65000000.00, 4000, 5, 6, 3, 'furnished', 'buy', 'available', 1, 1, '[\"Swimming Pool\", \"Gym\", \"Garden\", \"Security\"]', 'Bandra West, Mumbai', 'prop11.jpg', 1500, '2026-08-11 10:00:00', '2026-08-11 10:00:00'),
(12, 'Spacious 4BHK Apartment in South Ex', 'Luxurious apartment with premium fittings and marble flooring.', 1, 2, 2, 32000000.00, 2200, 4, 4, 2, 'semi-furnished', 'buy', 'available', 0, 1, '[\"Power Backup\", \"Security\", \"Club House\"]', 'South Extension, Delhi', 'prop12.jpg', 850, '2026-08-12 10:00:00', '2026-08-12 10:00:00'),
(13, 'IT Park Commercial Space in Whitefield', 'Large floor plate office space suitable for MNCs.', 3, 3, 3, 500000.00, 5000, 0, 4, 10, 'furnished', 'rent', 'available', 1, 0, '[\"Central AC\", \"Cafeteria\", \"Conference Room\", \"High-speed Elevators\"]', 'Whitefield, Bangalore', 'prop13.jpg', 600, '2026-08-13 10:00:00', '2026-08-13 10:00:00'),
(14, 'Scenic Plot near Pawna Lake', 'Lake-facing plot ideal for a weekend holiday home.', 4, 4, 4, 12000000.00, 3000, 0, 0, 0, 'unfurnished', 'buy', 'available', 1, 1, '[\"Lake View\", \"Gated Community\"]', 'Pawna Lake, Pune', 'prop14.jpg', 1100, '2026-08-14 10:00:00', '2026-08-14 10:00:00'),
(15, 'Elegant 3BHK Flat in Adyar', 'Well-ventilated apartment close to schools and hospitals.', 1, 5, 5, 18000000.00, 1800, 3, 3, 1, 'semi-furnished', 'buy', 'available', 0, 0, '[\"Security\", \"Park\"]', 'Adyar, Chennai', 'prop15.jpg', 420, '2026-08-15 10:00:00', '2026-08-15 10:00:00'),
(16, 'Modern Villa in Gachibowli', 'Contemporary design villa in a peaceful gated community.', 2, 6, 6, 40000000.00, 3200, 4, 4, 2, 'furnished', 'buy', 'available', 1, 1, '[\"Club House\", \"Swimming Pool\", \"Gym\"]', 'Gachibowli, Hyderabad', 'prop16.jpg', 980, '2026-08-16 10:00:00', '2026-08-16 10:00:00'),
(17, 'Spacious Penthouse in Salt Lake', 'Premium penthouse with panoramic city views.', 5, 7, 1, 25000000.00, 2600, 4, 4, 2, 'semi-furnished', 'buy', 'available', 0, 1, '[\"Terrace\", \"Security\", \"Power Backup\"]', 'Salt Lake City, Kolkata', 'prop17.jpg', 550, '2026-08-17 10:00:00', '2026-08-17 10:00:00'),
(18, 'Retail Space in Vastrapur', 'High footfall retail space in a busy commercial hub.', 3, 8, 2, 85000.00, 1000, 0, 1, 1, 'unfurnished', 'rent', 'available', 0, 0, '[\"Main Road Facing\", \"Visitor Parking\"]', 'Vastrapur, Ahmedabad', 'prop18.jpg', 310, '2026-08-18 10:00:00', '2026-08-18 10:00:00'),
(19, 'Luxury 4BHK Apartment in Malviya Nagar', 'Brand new luxury apartment with state-of-the-art amenities.', 1, 9, 4, 21000000.00, 2400, 4, 4, 2, 'furnished', 'buy', 'available', 1, 1, '[\"Gym\", \"Swimming Pool\", \"Security\"]', 'Malviya Nagar, Jaipur', 'prop19.jpg', 720, '2026-08-19 10:00:00', '2026-08-19 10:00:00'),
(20, 'Studio Apartment in Zirakpur', 'Affordable and compact studio for young professionals.', 6, 10, 6, 3500000.00, 450, 1, 1, 1, 'semi-furnished', 'buy', 'available', 0, 0, '[\"Security\", \"Maintenance Staff\"]', 'Zirakpur, Chandigarh', 'prop20.jpg', 240, '2026-08-20 10:00:00', '2026-08-20 10:00:00'),
(21, 'Sea Facing 2BHK in Juhu', 'Beautiful 2BHK flat with a spectacular view of the Arabian Sea.', 1, 1, 1, 38000000.00, 1200, 2, 2, 1, 'furnished', 'buy', 'available', 1, 1, '[\"Sea View\", \"Security\", \"Power Backup\"]', 'Juhu, Mumbai', 'prop21.jpg', 1350, '2026-08-21 10:00:00', '2026-08-21 10:00:00'),
(22, 'Independent House in Dwarka', 'Spacious house in a family-friendly neighborhood.', 2, 2, 2, 18000000.00, 1800, 3, 3, 2, 'semi-furnished', 'buy', 'available', 0, 0, '[\"Garden\", \"Security\"]', 'Dwarka Sector 12, Delhi', 'prop22.jpg', 480, '2026-08-22 10:00:00', '2026-08-22 10:00:00'),
(23, 'Co-working Space in Indiranagar', 'Vibrant co-working space with dedicated desks and private cabins.', 3, 3, 3, 20000.00, 300, 0, 1, 0, 'furnished', 'rent', 'available', 0, 0, '[\"WiFi\", \"Cafeteria\", \"AC\", \"Meeting Rooms\"]', 'Indiranagar, Bangalore', 'prop23.jpg', 670, '2026-08-23 10:00:00', '2026-08-23 10:00:00'),
(24, 'Premium Plot in Koregaon Park', 'Rare plot available in the prestigious Koregaon Park area.', 4, 4, 4, 45000000.00, 4000, 0, 0, 0, 'unfurnished', 'buy', 'available', 1, 1, '[\"Prime Location\", \"Gated Community\"]', 'Koregaon Park, Pune', 'prop24.jpg', 1050, '2026-08-24 10:00:00', '2026-08-24 10:00:00'),
(25, 'Luxury Villa in OMR', 'Expansive villa with top-notch amenities on the IT corridor.', 2, 5, 5, 55000000.00, 4500, 5, 5, 3, 'furnished', 'buy', 'available', 1, 1, '[\"Private Pool\", \"Home Theater\", \"Gym\", \"Security\"]', 'Old Mahabalipuram Road, Chennai', 'prop25.jpg', 890, '2026-08-25 10:00:00', '2026-08-25 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `fk_images_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_images`
--

INSERT INTO `property_images` (`property_id`, `image_path`, `is_primary`) VALUES
(1, 'prop1_1.jpg', 1), (1, 'prop1_2.jpg', 0),
(2, 'prop2_1.jpg', 1), (2, 'prop2_2.jpg', 0),
(3, 'prop3_1.jpg', 1), (3, 'prop3_2.jpg', 0),
(4, 'prop4_1.jpg', 1), (4, 'prop4_2.jpg', 0),
(5, 'prop5_1.jpg', 1), (5, 'prop5_2.jpg', 0),
(6, 'prop6_1.jpg', 1), (6, 'prop6_2.jpg', 0),
(7, 'prop7_1.jpg', 1), (7, 'prop7_2.jpg', 0),
(8, 'prop8_1.jpg', 1), (8, 'prop8_2.jpg', 0),
(9, 'prop9_1.jpg', 1), (9, 'prop9_2.jpg', 0),
(10, 'prop10_1.jpg', 1), (10, 'prop10_2.jpg', 0),
(11, 'prop11_1.jpg', 1), (11, 'prop11_2.jpg', 0),
(12, 'prop12_1.jpg', 1), (12, 'prop12_2.jpg', 0),
(13, 'prop13_1.jpg', 1), (13, 'prop13_2.jpg', 0),
(14, 'prop14_1.jpg', 1), (14, 'prop14_2.jpg', 0),
(15, 'prop15_1.jpg', 1), (15, 'prop15_2.jpg', 0),
(16, 'prop16_1.jpg', 1), (16, 'prop16_2.jpg', 0),
(17, 'prop17_1.jpg', 1), (17, 'prop17_2.jpg', 0),
(18, 'prop18_1.jpg', 1), (18, 'prop18_2.jpg', 0),
(19, 'prop19_1.jpg', 1), (19, 'prop19_2.jpg', 0),
(20, 'prop20_1.jpg', 1), (20, 'prop20_2.jpg', 0),
(21, 'prop21_1.jpg', 1), (21, 'prop21_2.jpg', 0),
(22, 'prop22_1.jpg', 1), (22, 'prop22_2.jpg', 0),
(23, 'prop23_1.jpg', 1), (23, 'prop23_2.jpg', 0),
(24, 'prop24_1.jpg', 1), (24, 'prop24_2.jpg', 0),
(25, 'prop25_1.jpg', 1), (25, 'prop25_2.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','responded','closed') NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_inquiry_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_inquiry_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`property_id`, `user_id`, `name`, `email`, `phone`, `message`, `status`) VALUES
(1, 1, 'John Doe', 'john@example.com', '9876543211', 'I am interested in this luxury apartment. When can I schedule a visit?', 'new'),
(2, 2, 'Jane Smith', 'jane@example.com', '9876543212', 'Can you provide more details about the garden area?', 'read'),
(3, 3, 'Rahul Sharma', 'rahul@example.com', '9876543213', 'Is the lease term negotiable?', 'responded'),
(5, 4, 'Priya Patel', 'priya@example.com', '9876543214', 'I want to see the penthouse this weekend.', 'new'),
(11, 5, 'Amit Kumar', 'amit@example.com', '9876543215', 'Does the price include all furnishings?', 'closed'),
(7, 1, 'John Doe', 'john@example.com', '9876543211', 'What is the maintenance cost?', 'new'),
(14, 2, 'Jane Smith', 'jane@example.com', '9876543212', 'Can we build a 2-story house here?', 'read'),
(16, 3, 'Rahul Sharma', 'rahul@example.com', '9876543213', 'What amenities are in the club house?', 'responded'),
(21, 4, 'Priya Patel', 'priya@example.com', '9876543214', 'Is it a sea facing apartment?', 'closed'),
(24, 5, 'Amit Kumar', 'amit@example.com', '9876543215', 'Is it in a gated community?', 'new'),
(4, NULL, 'Suresh', 'suresh@test.com', '9988776655', 'I want to know the exact location of the plot.', 'new'),
(8, NULL, 'Ramesh', 'ramesh@test.com', '9988776644', 'What is the expected rental yield?', 'read'),
(12, NULL, 'Kavita', 'kavita@test.com', '9988776633', 'Is parking available for 2 cars?', 'responded'),
(19, NULL, 'Sunil', 'sunil@test.com', '9988776622', 'Can I get a loan for this property?', 'closed'),
(25, NULL, 'Anjali', 'anjali@test.com', '9988776611', 'I want to visit the site tomorrow.', 'new');


-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_property` (`user_id`,`property_id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `fk_wishlist_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`user_id`, `property_id`) VALUES
(1, 1), (1, 11), (2, 2), (2, 14), (3, 3), (3, 16), (4, 5), (4, 21), (5, 7), (5, 24);

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(100) NOT NULL,
  `user_photo` varchar(255) DEFAULT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `review` text NOT NULL,
  `property_type` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`user_name`, `user_photo`, `rating`, `review`, `property_type`, `status`) VALUES
('John Doe', 'user1.jpg', 5, 'Found my dream home through Sarvam. Highly recommended!', 'Apartment', 'active'),
('Jane Smith', 'user2.jpg', 4, 'Great service and very professional agents.', 'Villa', 'active'),
('Rahul Sharma', 'user3.jpg', 5, 'Best platform to rent office space. Process was seamless.', 'Commercial', 'active'),
('Priya Patel', 'user4.jpg', 5, 'Amazing collection of premium properties.', 'Penthouse', 'active'),
('Amit Kumar', 'user5.jpg', 4, 'Very helpful staff. Guided me well during my property hunt.', 'Plot', 'active'),
('Suresh', NULL, 5, 'Quick response to my inquiries.', 'Apartment', 'active'),
('Kavita', NULL, 4, 'Good deals on flats.', 'Studio', 'active'),
('Ramesh', NULL, 5, 'Highly satisfied with the commercial property I bought.', 'Commercial', 'active');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
