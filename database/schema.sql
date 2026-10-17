-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2026 at 10:08 AM
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
-- Database: `streetreel`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `movie_id` int(10) UNSIGNED NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `slug` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `name`, `slug`) VALUES
(1, 'Street Fighting', 'street-fighting'),
(2, 'Martial Arts', 'martial-arts'),
(3, 'Gang Wars', 'gang-wars'),
(4, 'Revenge', 'revenge'),
(5, 'Underground Racing', 'underground-racing'),
(6, 'Tournaments', 'tournaments'),
(7, 'Dystopian', 'dystopian'),
(10, 'Rockband', 'rockband');

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(120) NOT NULL,
  `tagline` varchar(160) NOT NULL DEFAULT '',
  `synopsis` text NOT NULL,
  `release_year` smallint(5) UNSIGNED NOT NULL,
  `runtime_min` smallint(5) UNSIGNED NOT NULL,
  `rating` varchar(8) NOT NULL DEFAULT 'R',
  `director` varchar(80) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `genre_id` int(10) UNSIGNED DEFAULT NULL,
  `poster_url` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `title`, `tagline`, `synopsis`, `release_year`, `runtime_min`, `rating`, `director`, `price`, `genre_id`, `poster_url`, `featured`, `created_at`) VALUES
(1, 'Undisputed III: Redemption', 'Only one fighter can rise above them all.', 'Imprisoned fighter Yuri Boyka enters an international prison tournament, fighting to prove himself and earn another chance at freedom.', 2010, 96, 'R', 'Isaac Florentine', 12.99, 2, 'http://localhost/streetreel/images/posters/Undisputed.jpg', 1, '2026-10-11 13:40:51'),
(2, 'Redline', 'Speed, luxury, and high-stakes competition.', 'Natasha Martin, a talented mechanic and racing enthusiast, becomes involved with wealthy car collectors who organize illegal high-performance racing competitions. As rivalries intensify and personal conflicts emerge, the racing world draws her into a dangerous struggle involving ambition, money, and loyalty.', 2007, 95, 'PG-13', 'Andy Cheng', 9.99, 5, 'http://localhost/streetreel/images/posters/REDLINE.jpg', 0, '2026-10-11 13:40:51'),
(3, 'Need for Speed', 'For those who love speed, there\'s no limit.', 'Tobey Marshall, a skilled street racer and mechanic, is framed for a crime by a wealthy former business associate. After serving time in prison, he enters a high-profile cross-country race to clear his name and seek justice. Along the way, he faces formidable competitors and attempts to rebuild his reputation.', 2014, 132, 'PG-13', 'Scott Waugh', 11.99, 5, 'http://localhost/streetreel/images/posters/NFS.jpg', 1, '2026-10-11 13:40:51'),
(4, 'The Fast and the Furious: Tokyo Drift', 'On the streets of Tokyo, the rules are different.', 'After getting into trouble with illegal racing in the United States, teenager Sean Boswell moves to Tokyo to live with his father. There, he discovers the world of drift racing and becomes involved in rivalries within the city\'s underground car culture. As he develops his driving skills, he must navigate dangerous rivalries and prove himself in a new environment.', 2006, 104, 'PG-13', 'Justin Lin', 8.99, 5, 'http://localhost/streetreel/images/posters/TFATFT.jpg', 0, '2026-10-11 13:40:51'),
(5, 'The Fast and the Furious', 'Live your life a quarter mile at a time.', 'An undercover police officer infiltrates a Los Angeles street-racing crew suspected of carrying out a series of truck hijackings. As he becomes immersed in the underground racing scene, he develops friendships and begins questioning where his loyalties truly lie.', 2001, 106, 'PG-13', 'Rob Cohen', 9.99, 5, 'http://localhost/streetreel/images/posters/TFATF.jpg', 0, '2026-10-11 13:40:51'),
(6, 'Boy Kills World', 'Revenge has a new voice.', 'Set in a dystopian society ruled by a ruthless family, the film follows Boy, a deaf young man whose imagination is shaped by video games. After a personal tragedy, he trains under a mysterious shaman and prepares to challenge the oppressive regime responsible for his suffering. His journey becomes a surreal blend of revenge, dark comedy, and over-the-top action.', 2023, 111, 'R', 'Moritz Mohr', 11.99, 4, 'http://localhost/streetreel/images/posters/BoyKillsWorld.jpg', 0, '2026-10-11 13:40:51'),
(7, 'The Raid: Redemption', '30 floors of chaos. One way out.', 'An elite police squad launches a raid on a high-rise apartment building controlled by a ruthless crime lord. When their mission is compromised and they become trapped inside, the officers must fight their way through dangerous criminals to survive. The film is known for its intense martial arts choreography, close-quarters combat, and relentless action.', 2011, 101, 'R', 'Gareth Evans', 5.99, 2, 'http://localhost/streetreel/images/posters/TheRaidRedemption.jpg', 0, '2026-10-11 13:40:51'),
(8, 'Guns Akimbo', 'Locked, loaded, and forced to play.', 'Miles, an ordinary video game developer, becomes the target of an underground online death-match organization after provoking its followers. He wakes up with guns attached to his hands and is forced to participate in a brutal competition against a highly skilled opponent. As he struggles to survive, Miles must overcome his fear and confront the violent spectacle he once treated as entertainment. The film blends dark comedy, action, and criticism of internet culture and online violence.', 2019, 98, 'R', 'Jason Lei Howden', 12.99, 6, 'http://localhost/streetreel/images/posters/GunsAkimbo(2019).webp', 1, '2026-10-11 13:40:51'),
(9, 'Fearless', 'One man\'s journey to become a legend.', 'Martial artist Huo Yuanjia faces personal tragedy and learns that true strength involves honor, humility, and responsibility.', 2004, 104, 'PG-13', 'Ronny Yu', 9.99, 2, 'http://localhost/streetreel/images/posters/Fearless.jpg', 0, '2026-10-11 13:40:51'),
(10, 'Ip Man', 'A legend is born.', 'During the Japanese occupation of Foshan, Wing Chun master Ip Man defends his community and the honor of Chinese martial arts.', 2008, 106, 'R', 'Wilson Yip', 10.99, 2, 'http://localhost/streetreel/images/posters/IpMan.jpg', 0, '2026-10-11 13:40:51'),
(11, 'Green Room', 'One band. One venue. No way out.', 'Green Room follows a struggling punk rock band that accepts a performance at a remote venue controlled by neo-Nazis. After witnessing a violent crime backstage, the band members become trapped inside and must fight to survive against people determined to silence them. As tension escalates, they rely on teamwork, courage, and quick thinking to escape. The film is a tense thriller that explores survival, extremism, and the dangers of being caught in a situation beyond one\'s control.', 2015, 95, 'R', 'Jeremy Saulnier', 6.99, 1, 'http://localhost/streetreel/images/posters/GreenRoom.jpg', 1, '2026-10-11 13:40:51'),
(12, 'Pistol', 'The rise of punk rock and the band that changed music forever.', 'This biographical drama follows Steve Jones and the rise of the Sex Pistols, one of the most influential punk rock bands in British music history. Set in 1970s London, the series explores the band\'s rebellious spirit, chaotic lifestyle, and challenge to the traditional music industry, capturing the raw energy of a generation determined to make its voice heard.', 2022, 60, 'PG-13', 'Danny Boyle', 13.99, 10, 'http://localhost/streetreel/images/posters/pistol(2022).jpg', 0, '2026-10-11 13:40:51'),
(13, 'Wayne', 'A teenage delinquent on a mission to set things right.', 'Wayne follows a tough, rebellious teenager who sets out on a dangerous road trip with his new friend Del to recover a car stolen from his late father. With his impulsive behavior, fierce loyalty, and disregard for authority, Wayne faces violent confrontations and unexpected challenges along the way. Beneath its dark humor and action, the series explores friendship, family struggles, and the search for justice in a world that often feels unfair.', 2019, 35, 'R', 'Various directors, including Iain B. MacDonald', 8.99, 1, 'http://localhost/streetreel/images/posters/Wayne(2019).jpg', 0, '2026-10-11 13:40:51'),
(14, 'Daria', 'A sarcastic outsider\'s view of a world obsessed with fitting in.', 'Daria follows an intelligent, cynical, and socially detached teenage girl as she navigates high school, family life, and suburban society. Known for her sharp wit and deadpan humor, Daria questions social expectations, superficial popularity, and conformity while observing the absurdities of everyday life. The animated series uses satire to explore teenage identity, consumer culture, and the pressures of fitting in.', 1997, 21, 'R', 'Karen Disher', 5.99, 1, 'http://localhost/streetreel/images/posters/Daria.jpg', 0, '2026-10-11 13:40:51'),
(15, 'Fight Club', 'Mischief. Mayhem. Soap.', 'Fight Club follows an unnamed office worker who suffers from insomnia and feels trapped in a monotonous life built around consumerism and material possessions. Searching for meaning and excitement, he meets Tyler Durden, a charismatic and rebellious soap salesman who challenges society\'s obsession with money, status, and conformity.\r\nThe two men establish an underground fight club where frustrated men gather to release their anger through bare-knuckle fights. What begins as an outlet for their frustrations soon evolves into something far more dangerous as Tyler\'s radical philosophy grows into an increasingly extreme movement.\r\nAs the narrator becomes more deeply involved, his grip on reality begins to weaken, and he discovers that his relationship with Tyler is far more complicated than he ever imagined.\r\nThe film explores themes of masculinity, identity, consumerism, alienation, and the struggle to find meaning in modern life.', 1999, 139, 'R', 'David Fincher', 5.99, 1, 'https://imgs.search.brave.com/ahhYr9TzKhGx9Qy5P7SXFiV7tbtKu2CF9ga2cRZcwmE/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZS50bWRiLm9yZy90/L3AvdzUwMC9qU3pp/aW9Td1BWck95OVlv/dzNYaFdJQkRqcTEu/anBn', 1, '2026-10-11 13:40:51');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `status` enum('paid','pending','refunded') NOT NULL DEFAULT 'paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `payment_method`, `status`, `created_at`) VALUES
(1, 1, 6.99, 'E-wallet (demo)', 'paid', '2026-10-12 00:55:34'),
(2, 2, 8.99, 'E-wallet (demo)', 'paid', '2026-10-12 00:58:12'),
(3, 2, 6.99, 'Card (demo)', 'paid', '2026-10-12 00:58:29');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `movie_id` int(10) UNSIGNED DEFAULT NULL,
  `title_snapshot` varchar(120) NOT NULL,
  `price` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `movie_id`, `title_snapshot`, `price`) VALUES
(1, 1, 11, 'Green Room', 6.99),
(2, 2, 13, 'Wayne', 8.99),
(3, 3, 11, 'Green Room', 6.99);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'RUNAWAYS', 'admin@local.com', '$2y$10$RoaVcmaxj/mFGTANphj0feeHO4Wq3yv12lucq3SH9AvmE2m3/XCN6', 'admin', '2026-10-11 13:46:05'),
(2, 'JoyJoy', 'KiraGwapa123@local.com', '$2y$10$Z8ItqeRorpbOTUldvcKin.3AxcIUeMJEck7EAr/2wizr42nW///YO', 'user', '2026-10-12 00:57:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cart` (`user_id`,`movie_id`),
  ADD KEY `fk_cart_movie` (`movie_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_movies_genre` (`genre_id`),
  ADD KEY `idx_movies_featured` (`featured`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_oi_movie` (`movie_id`),
  ADD KEY `fk_oi_order` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `movies`
--
ALTER TABLE `movies`
  ADD CONSTRAINT `fk_movies_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oi_movie` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
