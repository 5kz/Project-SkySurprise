-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Värd: 127.0.0.1
-- Tid vid skapande: 22 apr 2025 kl 22:47
-- Serverversion: 10.4.32-MariaDB
-- PHP-version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databas: `skysurprise`
--

-- --------------------------------------------------------

--
-- Tabellstruktur `bookinginfo`
--

CREATE TABLE `bookinginfo` (
  `id` int(11) NOT NULL,
  `departure` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `destinationtype` varchar(50) NOT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumpning av Data i tabell `bookinginfo`
--

INSERT INTO `bookinginfo` (`id`, `departure`, `date`, `destinationtype`, `userid`) VALUES
(7, 'Göteborg Landvetter (GOT)', '2025-05-02', 'Surprise me', 10),
(8, 'Stockholm Arlanda (ARN)', '2025-05-03', 'Nature & adventure', 12),
(9, 'Stockholm Arlanda (ARN)', '2025-04-18', 'Nature & adventure', 10),
(10, 'Copenhagen Kastrup (CPH)', '2025-05-03', 'City escape', 10);

-- --------------------------------------------------------

--
-- Tabellstruktur `tbluser`
--

CREATE TABLE `tbluser` (
  `id` int(11) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `userlevel` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumpning av Data i tabell `tbluser`
--

INSERT INTO `tbluser` (`id`, `surname`, `lastname`, `email`, `password`, `userlevel`) VALUES
(10, 'Steffe', 'Storm', 'ss@gmail.com', '202cb962ac59075b964b07152d234b70', 1),
(11, 'Skysurprise', 'Support team', 'admin@skysurprise.com', '202cb962ac59075b964b07152d234b70', 9),
(12, 'Lasse', 'Flerre', 'lf@gmail.com', '202cb962ac59075b964b07152d234b70', 1);

-- --------------------------------------------------------

--
-- Tabellstruktur `ticketcomments`
--

CREATE TABLE `ticketcomments` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumpning av Data i tabell `ticketcomments`
--

INSERT INTO `ticketcomments` (`id`, `ticket_id`, `user_id`, `message`, `created_at`) VALUES
(1, 2, 11, 'Yes very much sir', '2025-04-19 18:58:16'),
(2, 2, 11, 'Yes very much sir', '2025-04-19 18:59:41'),
(3, 2, 11, 'Yes very much sir', '2025-04-19 19:01:25'),
(4, 2, 11, 'Yes very much sir', '2025-04-19 19:03:34'),
(5, 2, 11, 'Cool cool no more?', '2025-04-19 19:03:50'),
(6, 2, 11, 'Now you should work yes?', '2025-04-19 19:06:20'),
(7, 2, 11, 'Now you should work yes?', '2025-04-19 19:06:38'),
(8, 2, 11, 'Work?', '2025-04-19 19:07:09'),
(9, 4, 11, 'Hej vad synd vi löser', '2025-04-19 19:08:22'),
(10, 4, 11, 'Lös här är penga', '2025-04-19 19:11:38'),
(11, 4, 10, 'Bra bra lös åt mig?', '2025-04-19 19:16:45'),
(12, 4, 10, 'Jag nöjd när fixad', '2025-04-19 19:16:58'),
(13, 4, 11, 'grah', '2025-04-19 19:34:12'),
(14, 4, 10, 'keep it a stack?', '2025-04-19 19:37:00'),
(15, 4, 10, 'keep it a stack?', '2025-04-19 19:38:15'),
(16, 4, 10, 'rahhhh', '2025-04-19 19:38:22'),
(17, 4, 10, 'rahhhh', '2025-04-19 19:38:26'),
(18, 4, 10, 'rahhhh', '2025-04-19 19:41:05'),
(19, 4, 10, 'bello', '2025-04-19 19:41:14'),
(20, 4, 10, 'ts not tuff gang', '2025-04-19 19:41:24'),
(21, 4, 11, 'enough yapping', '2025-04-19 19:44:54'),
(22, 2, 11, 'bello', '2025-04-19 20:05:21'),
(23, 5, 11, 'Vi fixar', '2025-04-19 20:14:45'),
(24, 5, 12, 'Tack', '2025-04-19 20:15:29'),
(25, 6, 11, 'Hej', '2025-04-22 14:32:57'),
(26, 7, 11, 'Det ska nog vara lungt fixat nu?', '2025-04-22 14:57:27'),
(27, 7, 12, 'Ja perfect', '2025-04-22 14:57:48');

-- --------------------------------------------------------

--
-- Tabellstruktur `ticketinfo`
--

CREATE TABLE `ticketinfo` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(60) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(200) DEFAULT NULL,
  `status` enum('open','ongoing','closed') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumpning av Data i tabell `ticketinfo`
--

INSERT INTO `ticketinfo` (`id`, `user_id`, `title`, `description`, `image`, `status`, `created_at`) VALUES
(1, 11, 'sdfasf', 'asfafaf', NULL, 'closed', '2025-04-19 20:10:06'),
(2, 11, 'sdfasf', 'asfafaf', NULL, 'closed', '2025-04-19 20:14:30'),
(3, 10, 'asda', '1233441241231245', NULL, 'closed', '2025-04-19 20:10:02'),
(4, 10, 'hej Flyg ej funka :(', 'Flyg flyg flyg', NULL, 'closed', '2025-04-19 19:44:59'),
(5, 12, 'Trasig', '12312412414', NULL, 'closed', '2025-04-19 20:15:44'),
(6, 10, 'Hej hej', '12312312313', NULL, 'closed', '2025-04-22 15:00:05'),
(7, 12, 'Hej hjälp tack', 'Testare testman', NULL, 'closed', '2025-04-22 14:58:10'),
(8, 12, 'Img test', 'Test', 'uploads/1745335284_Screenshot 2023-11-14 163851.png', 'open', '2025-04-22 15:21:24'),
(9, 12, 'img2', 'igm', 'uploads/1745336900_Screenshot 2023-11-19 224635.png', 'open', '2025-04-22 15:48:20');

--
-- Index för dumpade tabeller
--

--
-- Index för tabell `bookinginfo`
--
ALTER TABLE `bookinginfo`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `tbluser`
--
ALTER TABLE `tbluser`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `ticketcomments`
--
ALTER TABLE `ticketcomments`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `ticketinfo`
--
ALTER TABLE `ticketinfo`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT för dumpade tabeller
--

--
-- AUTO_INCREMENT för tabell `bookinginfo`
--
ALTER TABLE `bookinginfo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT för tabell `tbluser`
--
ALTER TABLE `tbluser`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT för tabell `ticketcomments`
--
ALTER TABLE `ticketcomments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT för tabell `ticketinfo`
--
ALTER TABLE `ticketinfo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
