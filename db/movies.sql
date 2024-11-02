-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 01, 2024 at 12:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `abcinema`
--

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `MovieID` int(11) NOT NULL,
  `MovieName` varchar(45) NOT NULL,
  `MovieGenre` varchar(45) NOT NULL,
  `MovieLength` int(11) NOT NULL,
  `MovieRating` int(11) NOT NULL,
  `MovieDesc` text DEFAULT NULL,
  `MoviePoster` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='To store movie data';

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`MovieID`, `MovieName`, `MovieGenre`, `MovieLength`, `MovieRating`, `MovieDesc`, `MoviePoster`) VALUES
(1, 'kung fu panda 4', 'comedy', 100, 9, 'pandas fighting', 'kfp4.jpg'),
(2, 'smile', 'thriller', 80, 7, 'Smile', 'smile.jpg'),
(3, 'the avengers', 'sci-fi', 95, 8, 'Avengers Unite', 'avengers.jpg'),
(4, 'rush hour 4 ', 'action', 104, 8, 'Rush Hour is back again', 'rh4.jpg'),
(5, 'john wick 5', 'action', 135, 9, 'John is back in the fight again', 'jw5.jpg'),
(6, 'apollo 11', 'thriller', 110, 7, 'Based on true events', 'apollo11.jpg'),
(7, 'conjuring', 'horror', 83, 8, 'Conjuring will keep you on the edge of your seats', 'conjuring.jpg'),
(8, 'moo deng', 'documentary', 63, 7, 'How did Moo Deng gained popularity', 'moodeng.jpg'),
(9, 'avatar', 'sci-fi', 121, 9, 'Dive into a new world', 'avatar.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`MovieID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `MovieID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
