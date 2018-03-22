-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 22, 2018 at 08:21 PM
-- Server version: 5.7.20
-- PHP Version: 7.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `pj_langs`
--

CREATE TABLE `pj_langs` (
  `id` int(11) NOT NULL,
  `code` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pj_langs`
--

INSERT INTO `pj_langs` (`id`, `code`) VALUES
(1, 'en'),
(2, 'ru'),
(3, 'uk');

-- --------------------------------------------------------

--
-- Table structure for table `pj_test`
--

CREATE TABLE `pj_test` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pj_test`
--

INSERT INTO `pj_test` (`id`, `name`, `text`) VALUES
(1, 'Test 1', 'Lorem 1 ipsum dolor sit amet'),
(2, 'Test 2', 'Lorem 2 ipsum dolor sit amet'),
(3, 'Test 3', 'Lorem 3 ipsum dolor sit amet');

-- --------------------------------------------------------

--
-- Table structure for table `pj_tpls`
--

CREATE TABLE `pj_tpls` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `side` tinyint(4) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `state` tinyint(4) NOT NULL,
  `params` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pj_tpls`
--

INSERT INTO `pj_tpls` (`id`, `name`, `side`, `active`, `state`, `params`) VALUES
(1, 'default', 0, 1, 1, ''),
(2, 'default', 1, 1, 1, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pj_langs`
--
ALTER TABLE `pj_langs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pj_test`
--
ALTER TABLE `pj_test`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pj_tpls`
--
ALTER TABLE `pj_tpls`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pj_langs`
--
ALTER TABLE `pj_langs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pj_test`
--
ALTER TABLE `pj_test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pj_tpls`
--
ALTER TABLE `pj_tpls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
