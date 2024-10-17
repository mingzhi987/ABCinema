-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2024 at 05:52 PM
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
-- Database: `abcinema`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `BookingID` int(11) NOT NULL,
  `PaymentDate` date NOT NULL,
  `PaymentType` varchar(45) NOT NULL,
  `UserID` int(11) NOT NULL,
  `ShoppingCartID` int(11) NOT NULL,
  `MovieName` varchar(45) NOT NULL COMMENT 'to store movie name, obtained from Movies table, handled at frontend',
  `Showtime` date NOT NULL COMMENT 'to store screening date, retrieved from ScreeningTime, handled at frontend',
  `CinemaSeat` varchar(45) NOT NULL COMMENT 'Cinema hall and seat number, obtained from Cinema and Seating tables, concatenated and handled at frontend'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cinema`
--

CREATE TABLE `cinema` (
  `CinemaID` int(11) NOT NULL,
  `MovieAllocated` int(11) NOT NULL,
  `CinemaHall` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='To store cinema hall data, linked to MovieID';

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
  `MovieDesc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='To store movie data';

-- --------------------------------------------------------

--
-- Table structure for table `screeningtime2`
--

CREATE TABLE `screeningtime2` (
  `ScreenTimeID` int(11) NOT NULL,
  `ScreenTimeDate` date NOT NULL,
  `ScreenTimeCost` decimal(10,2) NOT NULL,
  `SeatingLocation` int(11) NOT NULL,
  `ScreeningMovie` int(11) NOT NULL,
  `ShoppingCartID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='A huge association table';

-- --------------------------------------------------------

--
-- Table structure for table `seating`
--

CREATE TABLE `seating` (
  `SeatID` int(11) NOT NULL,
  `CinemaNumber` int(11) NOT NULL,
  `BookingState` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shoppingcart`
--

CREATE TABLE `shoppingcart` (
  `ShoppingCartID` int(11) NOT NULL,
  `TotalPrice` decimal(10,2) NOT NULL,
  `UserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `useraccount`
--

CREATE TABLE `useraccount` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(45) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Password` varchar(45) NOT NULL,
  `DateOfBirth` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='For handling user data';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`BookingID`),
  ADD KEY `UserID` (`UserID`),
  ADD KEY `ShoppingCartID` (`ShoppingCartID`);

--
-- Indexes for table `cinema`
--
ALTER TABLE `cinema`
  ADD PRIMARY KEY (`CinemaID`),
  ADD KEY `MOVIE_FOREIGN` (`MovieAllocated`);

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`MovieID`);

--
-- Indexes for table `screeningtime2`
--
ALTER TABLE `screeningtime2`
  ADD PRIMARY KEY (`ScreenTimeID`),
  ADD KEY `SeatingLocation` (`SeatingLocation`),
  ADD KEY `ScreeningMovieIDs` (`ScreeningMovie`),
  ADD KEY `ShoppingCartID` (`ShoppingCartID`);

--
-- Indexes for table `seating`
--
ALTER TABLE `seating`
  ADD PRIMARY KEY (`SeatID`),
  ADD KEY `Cinema_Seating_FKEY` (`CinemaNumber`);

--
-- Indexes for table `shoppingcart`
--
ALTER TABLE `shoppingcart`
  ADD PRIMARY KEY (`ShoppingCartID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `useraccount`
--
ALTER TABLE `useraccount`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cinema`
--
ALTER TABLE `cinema`
  MODIFY `CinemaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `MovieID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `screeningtime2`
--
ALTER TABLE `screeningtime2`
  MODIFY `ScreenTimeID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seating`
--
ALTER TABLE `seating`
  MODIFY `SeatID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shoppingcart`
--
ALTER TABLE `shoppingcart`
  MODIFY `ShoppingCartID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `useraccount`
--
ALTER TABLE `useraccount`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `Booking_User_FKEY` FOREIGN KEY (`UserID`) REFERENCES `useraccount` (`UserID`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `ShoppingCart_Booking_FKEY` FOREIGN KEY (`ShoppingCartID`) REFERENCES `shoppingcart` (`ShoppingCartID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `cinema`
--
ALTER TABLE `cinema`
  ADD CONSTRAINT `Movie_Cinema_FKEY` FOREIGN KEY (`MovieAllocated`) REFERENCES `movies` (`MovieID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `screeningtime2`
--
ALTER TABLE `screeningtime2`
  ADD CONSTRAINT `Movie_ScreeningTime_FKEY` FOREIGN KEY (`ScreeningMovie`) REFERENCES `movies` (`MovieID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Seating_ScreeningTime_FKEY` FOREIGN KEY (`SeatingLocation`) REFERENCES `seating` (`SeatID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ShoppingCart_ScreeningTime_FKEY` FOREIGN KEY (`ShoppingCartID`) REFERENCES `shoppingcart` (`ShoppingCartID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `seating`
--
ALTER TABLE `seating`
  ADD CONSTRAINT `Cinema_Seating_FKEY` FOREIGN KEY (`CinemaNumber`) REFERENCES `cinema` (`CinemaID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shoppingcart`
--
ALTER TABLE `shoppingcart`
  ADD CONSTRAINT `User_ShoppingCart_FKEY` FOREIGN KEY (`UserID`) REFERENCES `useraccount` (`UserID`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
