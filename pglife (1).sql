-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2026 at 08:29 AM
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
-- Database: `pglife`
--

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `icon` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `name`, `type`, `icon`) VALUES
(1, 'Wifi', 'Common Area', 'wifi'),
(2, 'Power Backup', 'Building', 'powerbackup'),
(3, 'Fire Extinguisher', 'Building', 'fireext'),
(4, 'TV', 'Common Area', 'tv'),
(5, 'Bed with Mattress', 'Bedroom', 'bed'),
(6, 'Parking', 'Building', 'parking'),
(7, 'Water Purifier', 'Common Area', 'rowater'),
(8, 'Dining', 'Common Area', 'dining'),
(9, 'Air Conditioner', 'Bedroom', 'ac'),
(10, 'Washing Machine', 'Common Area', 'washingmachine'),
(11, 'Lift', 'Building', 'lift'),
(12, 'CCTV', 'Building', 'cctv'),
(13, 'Geyser', 'Washroom', 'geyser');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`) VALUES
(1, 'Delhi'),
(2, 'Mumbai'),
(3, 'Bengaluru'),
(4, 'Hyderabad'),
(5, 'Shimla');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `payer_id` int(11) NOT NULL,
  `shared_with_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `split_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `property_id`, `payer_id`, `shared_with_id`, `title`, `amount`, `split_amount`, `created_at`) VALUES
(1, 2, 5, 1, 'wifi', 500.00, 250.00, '2026-06-28 01:38:18'),
(2, 3, 5, 1, 'wifi', 500.00, 250.00, '2026-06-28 02:12:53'),
(3, 1, 5, 1, 'wifi', 700.00, 350.00, '2026-06-28 03:57:47'),
(4, 1, 5, 1, 'wifi', 800.00, 400.00, '2026-06-28 23:55:18');

-- --------------------------------------------------------

--
-- Table structure for table `interested_users_properties`
--

CREATE TABLE `interested_users_properties` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interested_users_properties`
--

INSERT INTO `interested_users_properties` (`id`, `user_id`, `property_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 5),
(4, 2, 3),
(5, 2, 4),
(6, 3, 1),
(7, 3, 5),
(8, 4, 3),
(9, 4, 4),
(10, 4, 5),
(91, 5, 4),
(92, 5, 3),
(98, 5, 5),
(99, 5, 2),
(102, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `property_id`, `user_id`, `receiver_id`, `message`, `created_at`) VALUES
(1, 1, 5, 8, 'hlo', '2026-06-29 22:47:24'),
(2, 1, 5, 8, 'hi', '2026-06-29 22:55:21'),
(3, 1, 5, 8, 'hi', '2026-06-29 23:02:23'),
(4, 1, 7, 8, 'hey bro', '2026-06-29 23:02:52'),
(5, 1, 5, 8, 'hlo', '2026-06-29 23:11:42'),
(6, 1, 8, 8, 'hlo', '2026-06-30 01:35:20'),
(7, 1, 6, 8, 'hi', '2026-06-30 03:42:11'),
(8, 1, 1, 8, 'hlo', '2026-06-30 03:42:37'),
(9, 1, 6, 1, 'hi', '2026-06-30 04:13:55'),
(10, 1, 1, 6, 'hi', '2026-06-30 04:14:23'),
(11, 1, 6, 5, 'hi', '2026-06-30 04:15:38'),
(12, 1, 5, 6, 'i', '2026-06-30 04:16:31'),
(13, 1, 5, 5, 'hi', '2026-06-30 04:28:58'),
(14, 1, 8, 5, 'hi', '2026-06-30 04:29:17'),
(15, 1, 5, 8, 'hlo bro kese ho', '2026-06-30 05:16:41'),
(16, 1, 5, 5, 'hey brother', '2026-06-30 05:17:14'),
(17, 1, 5, 8, 'prnam', '2026-06-30 05:23:29'),
(18, 1, 5, 5, 'hr', '2026-06-30 05:32:54'),
(19, 1, 5, 8, 'hu', '2026-06-30 05:45:52'),
(20, 1, 5, 5, 'ht', '2026-06-30 05:52:15'),
(21, 1, 5, 8, 'hu', '2026-06-30 05:52:22'),
(22, 1, 5, 5, 'i', '2026-06-30 06:00:09'),
(23, 1, 5, 8, 'hlo', '2026-06-30 06:00:24'),
(24, 1, 5, 7, 'hip', '2026-06-30 06:00:48'),
(25, 1, 5, 8, 'hi', '2026-06-30 06:09:29'),
(26, 1, 5, 5, 'hlo', '2026-06-30 06:09:42'),
(27, 1, 8, 5, 'hlo kes ho', '2026-06-30 06:11:21'),
(28, 1, 6, 5, 'hety', '2026-07-01 22:06:43'),
(29, 1, 5, 8, 'he', '2026-07-01 22:09:26'),
(30, 1, 8, 5, 'oop', '2026-07-01 22:09:43'),
(31, 1, 6, 5, 'what?', '2026-07-01 23:22:54'),
(32, 1, 5, 6, 'nothing', '2026-07-01 23:23:13');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `gender` enum('male','female','unisex') NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `rent` int(11) NOT NULL,
  `rating_clean` float(2,1) NOT NULL,
  `rating_food` float(2,1) NOT NULL,
  `rating_safety` float(2,1) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `lat` float DEFAULT NULL,
  `lng` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `city_id`, `name`, `address`, `description`, `gender`, `image`, `rent`, `rating_clean`, `rating_food`, `rating_safety`, `owner_id`, `views`, `lat`, `lng`) VALUES
(1, 1, 'Elite Residency PG', 'Block-B, Near Main Market, Connaught Place, New Delhi 110001', 'Fully managed modern living space with premium facilities. Perfect for students and working professionals looking for a peaceful environment. Well-connected to the metro station and local markets. Includes high-speed internet and weekly deep cleaning service.', 'male', NULL, 7500, 4.5, 4.0, 4.7, 8, 8, 28.6328, 77.2195),
(2, 1, 'Stanza Nest Stay', 'H.No. 124, Near Metro Pillar 45, Karol Bagh, New Delhi 110005', 'Comfortable and spacious studio rooms available on sharing basis. Located in a safe residential area with 24/7 security. Common lounge area available for chilling with friends. Walking distance from major coaching institutes and food hubs.', 'unisex', NULL, 6500, 3.8, 3.5, 4.2, 8, 7, 28.6442, 77.1932),
(3, 2, 'Skyline Luxury Living', 'Sector 2, Near Link Road, Andheri West, Mumbai 400053', 'Premium girls PG with top-notch safety features. Located in a posh locality with quick access to corporate parks and cafes. Fully furnished kitchen, automated washing machines, and cozy bedding provided. Feels like a home away from home.', 'female', NULL, 12000, 4.8, 4.5, 4.9, 8, 5, 19.1312, 72.8365),
(4, 2, 'The Comfort Zone', 'Plot 42, Gorai Road, Borivali West, Mumbai 400092', 'Beautifully designed rooms tailored for students. Equipped with individual study tables, wardrobes, and attached washrooms. Great community of flatmates to network and grow together. Food menu curated by professional chefs.', 'female', NULL, 9000, 4.1, 3.9, 4.4, 8, 4, 19.2356, 72.8344),
(5, 2, 'Ganpati Paying Guest', 'Sainath Complex, Near SV Road, Borivali East, Mumbai 400066', 'Excellent budget-friendly property offering premium amenities. Spacious rooms with separate balconies, proper ventilation, and ambient lighting. Ideal for anyone wanting a clean, peaceful, and productive environment in Mumbai.', 'male', NULL, 8500, 4.4, 4.1, 4.6, 8, 7, 19.2281, 72.8572),
(6, 5, 'The Ridge View Premium Homestay', 'Near Mall Road, Below Kali Bari Temple, Shimla, Himachal Pradesh - 171001', 'Beautiful mountain view PG near Mall Road. Fully furnished rooms with room heaters for winters, 24/7 hot running water (geyser), high-speed Wi-Fi (50 Mbps), and home-cooked authentic North Indian meals. Perfect for students and remote working IT professionals. Extremely safe with CCTV backup.', 'unisex', '1782856081_images.jpg', 9500, 5.0, 5.0, 5.0, 8, 4, 31.1048, 77.1734),
(8, 3, 'Silicon Valley Premium Living', 'lat No. 204, 3rd Cross Road, Near Sony World Signal, Koramangala 4th Block, Bengaluru, Karnataka - 560034', 'Premium co-living space perfect for tech professionals and students. High-speed 5G Wi-Fi (300 Mbps), 3 times delicious North & South Indian food included, fully automatic washing machines, AC, 24/7 power backup, attached balcony, CCTV security, and daily housekeeping. No hidden charges, electricity bill is included in the rent!', 'unisex', '1782858224_images (1).jpg', 8500, 5.0, 5.0, 5.0, 8, 3, 12.9345, 77.6266),
(14, 4, 'Ismile Living Hyderabad', 'Plot 12, Near DLF Cyber City, Gachibowli, Hyderabad, Telangana 500032', 'A premium, fully managed tech-enabled student accommodation.', 'unisex', NULL, 9500, 4.5, 4.0, 4.8, 11, 5, 17.4402, 78.339),
(15, 4, 'Elite Stay PG', 'H.No 10-3/A, Beside Metro Station, Ameerpet, Hyderabad, Telangana 500016', 'Affordable and highly accessible budget accommodation.', 'male', NULL, 7500, 4.2, 3.8, 4.0, 11, 1, 17.4348, 78.448);

-- --------------------------------------------------------

--
-- Table structure for table `properties_amenities`
--

CREATE TABLE `properties_amenities` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties_amenities`
--

INSERT INTO `properties_amenities` (`id`, `property_id`, `amenity_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 4),
(4, 1, 5),
(5, 1, 7),
(6, 1, 9),
(7, 1, 10),
(8, 1, 11),
(9, 1, 13),
(10, 2, 1),
(11, 2, 2),
(12, 2, 3),
(13, 2, 5),
(14, 2, 7),
(15, 2, 10),
(16, 2, 13),
(17, 3, 1),
(18, 3, 2),
(19, 3, 3),
(20, 3, 5),
(21, 3, 7),
(22, 3, 9),
(23, 3, 10),
(24, 3, 11),
(25, 3, 12),
(26, 3, 13),
(27, 4, 1),
(28, 4, 3),
(29, 4, 5),
(30, 4, 7),
(31, 4, 8),
(32, 4, 10),
(33, 4, 12),
(34, 4, 13),
(35, 5, 1),
(36, 5, 2),
(37, 5, 5),
(38, 5, 7),
(39, 5, 8),
(40, 5, 9),
(41, 5, 10),
(42, 5, 11),
(43, 5, 12),
(44, 5, 13);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `property_id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 1, 1, 4.5, 'Amazing PG with premium facilities!', '2026-06-27 23:15:50'),
(2, 2, 2, 4.0, 'Very affordable and neat environment.', '2026-06-27 23:15:50'),
(3, 4, 1, 4.2, 'Good food and stable wifi connection.', '2026-06-27 23:15:50');

-- --------------------------------------------------------

--
-- Table structure for table `roommate_connections`
--

CREATE TABLE `roommate_connections` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `target_user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roommate_connections`
--

INSERT INTO `roommate_connections` (`id`, `user_id`, `target_user_id`, `property_id`, `status`, `created_at`) VALUES
(1, 1, 2, 1, 'pending', '2026-06-30 03:20:13'),
(2, 1, 6, 1, 'accepted', '2026-06-30 03:24:24'),
(3, 5, 6, 1, 'accepted', '2026-06-30 04:15:20'),
(4, 5, 6, 3, 'pending', '2026-07-01 22:05:35');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `content` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `property_id`, `user_name`, `content`) VALUES
(1, 1, 'Rohan Sharma', 'The management is super cooperative. The rooms are clean, and the location makes commuting so much easier every single day.'),
(2, 1, 'Kabir Mehta', 'Great food quality compared to other PGs in this area. Best decision to move in here for my final college year.'),
(3, 2, 'Aanya Verma', 'Super safe environment and the high-speed wifi makes working from home extremely smooth. Totally worth the rent.'),
(4, 2, 'Hrithik Roy', 'Nice common area to chill out after college lectures. Had an amazing time staying here with my roommates.'),
(5, 2, 'Amit Mishra', 'Clean washrooms and punctual power backup. Highly recommended for students coming to Delhi.'),
(6, 3, 'Riya Sen', 'Extremely secure and beautifully designed. The staff is polite, and the food feels genuinely home-cooked.'),
(7, 3, 'Sneha Kapoor', 'Love the community here! Met some amazing friends. The safety features give complete peace of mind.'),
(8, 4, 'Tanvi Joshi', 'Everything is well-managed from laundry to meals. The location is very convenient with public transport nearby.'),
(9, 5, 'Vikram Malhotra', 'Amazing value for money in Mumbai. Clean rooms, helpful warden, and all necessary facilities work perfectly.'),
(10, 5, 'Siddharth Jain', 'The property is well-maintained and very close to the station. Definitely the best PG option around Borivali.');

-- --------------------------------------------------------

--
-- Table structure for table `tour_bookings`
--

CREATE TABLE `tour_bookings` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_slot` varchar(50) NOT NULL,
  `tour_type` enum('Physical','Virtual') NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled') DEFAULT 'Confirmed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tour_bookings`
--

INSERT INTO `tour_bookings` (`id`, `property_id`, `user_id`, `booking_date`, `booking_slot`, `tour_type`, `status`, `created_at`) VALUES
(2, 1, 5, '2026-06-30', '03:55', 'Virtual', 'Confirmed', '2026-06-29 22:20:01'),
(3, 1, 7, '2026-06-30', '04:35', 'Virtual', 'Confirmed', '2026-06-29 23:01:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `college_name` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `phone`, `gender`, `college_name`, `role`) VALUES
(1, 'rohan.sharma99@gmail.com', '$2y$10$wv4WfPEeIKaK8C6OPzpWFedAVSf0uBdt5bWGIgR7jYgY4owh.Dj6i', 'Rohan Sharma', '9812345670', 'male', 'Delhi University', 'user'),
(2, 'sneha.k@yahoo.com', '$2y$10$i2nV.Z0LSWdVKHV/.UikXOsN1Q55GySlAAPhLjQfXLLKRHXguKDQe', 'Sneha Kapoor', '8765432109', 'female', 'IIT Bombay', 'user'),
(3, 'ayush.verma@outlook.com', '$2y$10$uY7FOi/j3HZbxL.R/8wNVeJZfs5Jw8/SsFBld2GMlPTAFaYDdJWAO', 'Ayush Verma', '7012345678', 'male', 'DTU', 'user'),
(4, 'divya.jain@gmail.com', '$2y$10$45sR9.PgSo/95v0wcYwQv.7DakRZsWFhUtbKIszy9ARze5K6ZRxFm', 'Divya Jain', '9988776655', 'female', 'NMIMS Mumbai', 'user'),
(5, 'test@gmail.com', '$2y$10$V8KZPO0LNto6F0TOU4O0TeyT9iH24WVwxDvrI5yYezejv31L3Tjpq', 'test', '2132456750', 'male', 'sdd', 'user'),
(6, 'rahul@gmail.com', '$2y$10$lvQ70eXcbkg/MpXGwK3xZu6m0zKgMAnHsp6nZ8i.Wj9yQp5NUTogW', 'Rahul Sharma', '2132456750', 'male', 'sdd', 'user'),
(7, 'test2@gmail.com', '$2y$10$REG/6BXgNIY/mYLNLFI5K.Cb0jLEIUWhEnXCqWVm9QnLdelEjw3xm', 'test2', '2341245675', 'male', 'IIT delhi', 'user'),
(8, 'TanishChandSood@gmail.com', '$2y$10$sxWnS2su5XnAyBaIeNqFtuQ2Ts9UvRmgWUFb1JDl7CNpVVvZMhfSS', 'Tanish Chand Sood', '7876133124', 'male', '', 'owner'),
(10, 'testuser@gmail.com', '$2y$10$o9K6eZC7dUiFyhnkJjb9IelNDDnXtWQUGbQtfq1lyEVbUaWqXgNuC', 'test3', '3427677389', 'male', 'ddt', 'user'),
(11, 'testuser1@gmail.com', '$2y$10$kvBLWG1CuvTFD1nU6Y7ywOSV.vJinS6aALJkAHtHi09oHKohYA5/2', 't', '3687435475', 'male', 'i', 'owner');

-- --------------------------------------------------------

--
-- Table structure for table `user_vibes`
--

CREATE TABLE `user_vibes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vibe_tag` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_vibes`
--

INSERT INTO `user_vibes` (`id`, `user_id`, `vibe_tag`) VALUES
(1, 1, 'Late Night Owl'),
(2, 1, 'Studious'),
(3, 2, 'Early Bird'),
(4, 2, 'Veg Only'),
(5, 3, 'Late Night Owl'),
(6, 3, 'Studious');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `interested_users_properties`
--
ALTER TABLE `interested_users_properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `city_id` (`city_id`);

--
-- Indexes for table `properties_amenities`
--
ALTER TABLE `properties_amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `amenity_id` (`amenity_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roommate_connections`
--
ALTER TABLE `roommate_connections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `tour_bookings`
--
ALTER TABLE `tour_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_vibes`
--
ALTER TABLE `user_vibes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `interested_users_properties`
--
ALTER TABLE `interested_users_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `properties_amenities`
--
ALTER TABLE `properties_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roommate_connections`
--
ALTER TABLE `roommate_connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tour_bookings`
--
ALTER TABLE `tour_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_vibes`
--
ALTER TABLE `user_vibes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `interested_users_properties`
--
ALTER TABLE `interested_users_properties`
  ADD CONSTRAINT `interested_users_properties_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `interested_users_properties_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`);

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`);

--
-- Constraints for table `properties_amenities`
--
ALTER TABLE `properties_amenities`
  ADD CONSTRAINT `properties_amenities_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `properties_amenities_ibfk_2` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`);

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`);

--
-- Constraints for table `tour_bookings`
--
ALTER TABLE `tour_bookings`
  ADD CONSTRAINT `tour_bookings_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tour_bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
