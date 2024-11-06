-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2024 at 04:52 PM
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
-- Database: `abcinema_db`
--
CREATE database IF NOT EXISTS abcinema_db;

USE abcinema_db;
-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `BookingID` int(11) NOT NULL,
  `PaymentDate` date NOT NULL,
  `UserID` int(11) NOT NULL,
  `ShoppingCartID` int(11) NOT NULL,
  `MovieName` varchar(45) NOT NULL COMMENT 'to store movie name, obtained from Movies table, handled at frontend',
  `Showtime` int(11) NOT NULL COMMENT 'to store screening date, retrieved from ScreeningTime, handled at frontend',
  `CinemaID` int(11) NOT NULL COMMENT 'Cinema hall and seat number, obtained from Cinema and Seating tables, concatenated and handled at frontend',
  `SeatID` int(11) NOT NULL,
  `Price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`BookingID`, `PaymentDate`, `UserID`, `ShoppingCartID`, `MovieName`, `Showtime`, `CinemaID`, `SeatID`, `Price`) VALUES
(44, '2024-11-04', 1, 12, 'John Wick: Chapter 4', 1, 1, 4, 10.00),
(45, '2024-11-04', 2, 12, 'John Wick: Chapter 4', 1, 1, 2, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `cinema`
--

CREATE TABLE `cinema` (
  `CinemaID` int(11) NOT NULL,
  `MovieAllocated` int(11) NOT NULL,
  `CinemaHall` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='To store cinema hall data, linked to MovieID';

--
-- Dumping data for table `cinema`
--

INSERT INTO `cinema` (`CinemaID`, `MovieAllocated`, `CinemaHall`) VALUES
(1, 1, '2'),
(2, 2, '4'),
(3, 3, '1'),
(4, 4, '5'),
(5, 5, '3');

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
(1, 'John Wick: Chapter 4', 'Action', 90, 5, 'A 2023 American neo-noir action thriller film directed and co-produced by Chad Stahelski and written by Shay Hatten and Michael Finch. The sequel to John Wick: Chapter 3 – Parabellum (2019) and the fourth installment in the John Wick franchise, the film stars Keanu Reeves as the title character, alongside Donnie Yen, Bill Skarsgård, Laurence Fishburne, Hiroyuki Sanada, Shamier Anderson, Lance Reddick, Rina Sawayama, Scott Adkins, Clancy Brown, and Ian McShane. In the film, John Wick sets out for revenge on the High Table and those who left him for dead.', 'https://upload.wikimedia.org/wikipedia/en/d/d0/John_Wick_-_Chapter_4_promotional_poster.jpg'),
(2, 'Tarot', 'Horror', 92, 4, 'Tarot is a 2024 American supernatural horror film written and directed by Spenser Cohen and Anna Halberg, in their feature film directorial debuts, based on the 1992 novel Horrorscope by Nicholas Adams. The film stars Harriet Slater, Adain Bradley, Avantika, Wolfgang Novogratz, Humberly González, Larsen Thompson, Olwen Fouéré, and Jacob Batalon as a group of college students who, after using a strange Tarot deck, begin to gruesomely die one by one.', 'https://upload.wikimedia.org/wikipedia/en/1/10/Tarot_Teaser_Poster.jpg'),
(3, 'Inside Out 2', 'Animation', 96, 5, 'Inside Out 2 is a 2024 American animated coming-of-age film produced by Pixar Animation Studios for Walt Disney Pictures. The sequel to Inside Out (2015), it was directed by Kelsey Mann (in his feature directorial debut) and produced by Mark Nielsen, from a screenplay written by Meg LeFauve and Dave Holstein, and a story conceived by Mann and LeFauve. Amy Poehler, Phyllis Smith, Lewis Black, Diane Lane, and Kyle MacLachlan reprise their roles from the first film, with Maya Hawke, Kensington Tallman, Liza Lapira (replacing Mindy Kaling), Tony Hale (replacing Bill Hader), Ayo Edebiri, Lilimar, Grace Lu, Sumayyah Nuriddin-Green, Adèle Exarchopoulos, and Paul Walter Hauser joining the cast. The film tells the story of Riley\'s emotions as they find themselves joined by new emotions that want to take over Riley\'s head.', 'https://upload.wikimedia.org/wikipedia/en/f/f7/Inside_Out_2_poster.jpg'),
(4, 'Cám: The Sisters', 'Horror', 122, 4, 'A series of unexplained missing cases and disturbing deaths start to occur in a faraway village when the Chief\'s dead daughter comes back to life, unveiling shocking secrets of the village\'s Forest Demon and the ritual of human sacrifice related to the prosperity of the Chief\'s family.', 'https://media.gv.com.sg/imagesresize/img4162.jpg'),
(5, 'Beetlejuice Beetlejuice', 'Fantasy', 105, 4, 'Michael Keaton returns to the titular role in the long-awaited sequel to Tim Burton’s award-winning Beetlejuice.', 'https://media.gv.com.sg/imagesresize/img1163.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `screeningtime2`
--

CREATE TABLE `screeningtime2` (
  `ScreenTimeID` int(11) NOT NULL,
  `ScreenTimeDate` datetime NOT NULL,
  `ScreenTimeCost` decimal(10,2) NOT NULL,
  `SeatingLocation` int(11) NOT NULL,
  `ScreeningMovie` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='A huge association table';

--
-- Dumping data for table `screeningtime2`
--

INSERT INTO `screeningtime2` (`ScreenTimeID`, `ScreenTimeDate`, `ScreenTimeCost`, `SeatingLocation`, `ScreeningMovie`) VALUES
(1, '2024-11-12 12:00:00', 20.00, 1, 1),
(2, '2024-11-12 15:00:00', 10.00, 1, 1),
(3, '2024-11-12 18:00:00', 40.00, 1, 1),
(4, '2024-11-14 12:00:00', 20.00, 1, 1),
(5, '2024-11-14 15:00:00', 10.00, 1, 1),
(6, '2024-11-14 18:00:00', 40.00, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `seating`
--

CREATE TABLE `seating` (
  `SeatID` int(11) NOT NULL,
  `CinemaNumber` int(11) NOT NULL,
  `SeatNumber` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seating`
--

INSERT INTO `seating` (`SeatID`, `CinemaNumber`, `SeatNumber`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 2, 1),
(7, 2, 2),
(8, 2, 3),
(9, 2, 4),
(10, 2, 5),
(11, 3, 1),
(12, 3, 2),
(13, 3, 3),
(14, 3, 4),
(15, 3, 5),
(16, 4, 1),
(17, 4, 2),
(18, 4, 3),
(19, 4, 4),
(20, 4, 5),
(21, 5, 1),
(22, 5, 2),
(23, 5, 3),
(24, 5, 4),
(25, 5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `shoppingcart`
--

CREATE TABLE `shoppingcart` (
  `ShoppingCartID` int(11) NOT NULL,
  `TotalPrice` decimal(10,2) NOT NULL,
  `UserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shoppingcart`
--

INSERT INTO `shoppingcart` (`ShoppingCartID`, `TotalPrice`, `UserID`) VALUES
(2, 0.00, 4);

-- --------------------------------------------------------

--
-- Table structure for table `shoppingscreening`
--

CREATE TABLE `shoppingscreening` (
  `ShoppingScreeningID` int(11) NOT NULL,
  `ShoppingCartID` int(11) NOT NULL,
  `ScreenTimeID` int(11) NOT NULL,
  `SeatID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shoppingscreening`
--

INSERT INTO `shoppingscreening` (`ShoppingScreeningID`, `ShoppingCartID`, `ScreenTimeID`, `SeatID`) VALUES
(13, 2, 1, 1);

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
  `DateOfBirth` date NOT NULL,
  `login_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='For handling user data';

--
-- Dumping data for table `useraccount`
--

INSERT INTO `useraccount` (`UserID`, `Username`, `Email`, `FullName`, `Password`, `DateOfBirth`, `login_token`) VALUES
(1, 'henry', 'henry@gmail.com.sg', 'Henry Stickmin', 'urmomgay', '1999-04-01', NULL),
(2, 'john', 'chuacheehean@gmail.com', 'John Major', 'password', '1999-05-01', 'f571a1848b028361601b2947f00388f8'),
(3, 'tom', 'tomson@yahoo.com', 'Tom Thompson', 'password', '2005-06-01', NULL),
(4, 'lampa', 'chua1020@e.ntu.edu.sg', 'ni lampa tua', 'lanjiao', '1990-03-20', '068f8b3665417e76e03f56e54b31aa1e');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`BookingID`),
  ADD KEY `UserID` (`UserID`);

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
  ADD KEY `ScreeningMovieIDs` (`ScreeningMovie`);

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
-- Indexes for table `shoppingscreening`
--
ALTER TABLE `shoppingscreening`
  ADD PRIMARY KEY (`ShoppingScreeningID`),
  ADD KEY `ShoppingFK` (`ShoppingCartID`),
  ADD KEY `ScreenTimeFK` (`ScreenTimeID`),
  ADD KEY `SeatingFK` (`SeatID`);

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
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `cinema`
--
ALTER TABLE `cinema`
  MODIFY `CinemaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `MovieID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `screeningtime2`
--
ALTER TABLE `screeningtime2`
  MODIFY `ScreenTimeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `seating`
--
ALTER TABLE `seating`
  MODIFY `SeatID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `shoppingcart`
--
ALTER TABLE `shoppingcart`
  MODIFY `ShoppingCartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `shoppingscreening`
--
ALTER TABLE `shoppingscreening`
  MODIFY `ShoppingScreeningID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `useraccount`
--
ALTER TABLE `useraccount`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `Booking_User_FKEY` FOREIGN KEY (`UserID`) REFERENCES `useraccount` (`UserID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `cinema`
--
ALTER TABLE `cinema`
  ADD CONSTRAINT `Movie_Cinema_FKEY` FOREIGN KEY (`MovieAllocated`) REFERENCES `movies` (`MovieID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `screeningtime2`
--
ALTER TABLE `screeningtime2`
  ADD CONSTRAINT `Movie_ScreeningTime_FKEY` FOREIGN KEY (`ScreeningMovie`) REFERENCES `movies` (`MovieID`) ON DELETE CASCADE ON UPDATE CASCADE;

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

--
-- Constraints for table `shoppingscreening`
--
ALTER TABLE `shoppingscreening`
  ADD CONSTRAINT `ScreenTimeFK` FOREIGN KEY (`ScreenTimeID`) REFERENCES `screeningtime2` (`ScreenTimeID`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `SeatingFK` FOREIGN KEY (`SeatID`) REFERENCES `seating` (`SeatID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `ShoppingFK` FOREIGN KEY (`ShoppingCartID`) REFERENCES `shoppingcart` (`ShoppingCartID`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
