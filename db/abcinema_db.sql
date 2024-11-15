-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2024 at 06:57 AM
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
(48, '2024-11-05', 1, 14, 'John Wick: Chapter 4', 1, 1, 3, 20.00),
(49, '2024-11-05', 2, 14, 'John Wick: Chapter 4', 1, 1, 5, 20.00),
(52, '2024-11-07', 2, 2, 'John Wick: Chapter 4', 3, 1, 2, 40.00),
(54, '2024-11-07', 2, 6, 'Tarot', 8, 2, 6, 40.00),
(55, '2024-11-07', 2, 6, 'Tarot', 8, 2, 8, 40.00),
(57, '2024-11-07', 2, 8, 'John Wick: Chapter 4', 4, 1, 1, 20.00),
(58, '2024-11-07', 2, 9, 'John Wick: Chapter 4', 6, 1, 1, 40.00);

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
(5, 'Beetlejuice Beetlejuice', 'Fantasy', 105, 4, 'Michael Keaton returns to the titular role in the long-awaited sequel to Tim Burton’s award-winning Beetlejuice.', 'https://media.gv.com.sg/imagesresize/img1163.jpg'),
(6, 'Kung Fu Panda 4', 'Comedy', 100, 9, 'Po must train a new warrior when he\'s chosen to become the spiritual leader of the Valley of Peace. However, when a powerful shape-shifting sorceress sets her eyes on his Staff of Wisdom, he suddenly realizes he\'s going to need some help. Teaming up with a quick-witted corsac fox, Po soon discovers that heroes can be found in the most unexpected places.', 'https://upload.wikimedia.org/wikipedia/en/7/7f/Kung_Fu_Panda_4_poster.jpg'),
(7, 'Smile', 'Thriller', 80, 7, 'After witnessing a bizarre, traumatic incident involving a patient, Dr. Rose Cotter starts experiencing frightening occurrences that she can\'t explain. As an overwhelming terror begins taking over her life, Rose must confront her troubling past in order to survive and escape her horrifying new reality.', 'https://upload.wikimedia.org/wikipedia/en/7/7f/Smile_%282022_film%29.jpg'),
(8, 'The Avengers', 'Sci-Fi', 95, 8, 'When Thor\'s evil brother, Loki (Tom Hiddleston), gains access to the unlimited power of the energy cube called the Tesseract, Nick Fury (Samuel L. Jackson), director of S.H.I.E.L.D., initiates a superhero recruitment effort to defeat the unprecedented threat to Earth. Joining Fury\'s \"dream team\" are Iron Man (Robert Downey Jr.), Captain America (Chris Evans), the Hulk (Mark Ruffalo), Thor (Chris Hemsworth), the Black Widow (Scarlett Johansson) and Hawkeye (Jeremy Renner).', 'https://upload.wikimedia.org/wikipedia/en/8/8a/The_Avengers_%282012_film%29_poster.jpg'),
(9, 'Rush Hour ', 'Action', 104, 8, 'When a Chinese diplomat\'s daughter is kidnapped in Los Angeles, he calls in Hong Kong Detective Inspector Lee (Jackie Chan) to assist the FBI with the case. But the FBI doesn\'t want anything to do with Lee, and they dump him off on the LAPD, who assign wisecracking Detective James Carter (Chris Tucker) to watch over him. Although Lee and Carter can\'t stand each other, they choose to work together to solve the case on their own when they figure out they\'ve been ditched by both the FBI and police.', 'https://upload.wikimedia.org/wikipedia/en/4/49/Rush_Hour_poster.png'),
(10, 'Apollo 11', 'Documentary', 110, 7, 'Never-before-seen footage and audio recordings reveal the inner workings of NASA\'s most celebrated mission as astronauts Neil Armstrong, Buzz Aldrin, and Michael Collins embark on their historic 1969 trip to the moon.', 'https://upload.wikimedia.org/wikipedia/en/2/29/Apollo_11_%282019_film%29.png'),
(11, 'The Conjuring', 'Horror', 83, 8, 'Rod and Carolyn find their pet dog dead under mysterious circumstances and experience a spirit that harms their daughter Andrea. They finally call investigators who can help them get out of the mess.', 'https://upload.wikimedia.org/wikipedia/en/8/8c/The_Conjuring_poster.jpg'),
(12, 'Avatar', 'Sci-Fi', 121, 9, 'Jake, a paraplegic marine, replaces his brother on the Na\'vi-inhabited Pandora for a corporate mission. He is accepted by the natives as one of their own, but he must decide where his loyalties lie.', 'https://upload.wikimedia.org/wikipedia/en/d/d6/Avatar_%282009_film%29_poster.jpg');

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
(4, '2025-11-14 12:00:00', 20.00, 1, 1),
(6, '2025-11-14 18:00:00', 40.00, 1, 1),
(8, '2024-12-09 15:00:00', 40.00, 2, 2),
(9, '2024-12-08 15:00:00', 30.00, 2, 2);

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
  `login_token` varchar(255) DEFAULT NULL,
  `admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='For handling user data';

--
-- Dumping data for table `useraccount`
--

INSERT INTO `useraccount` (`UserID`, `Username`, `Email`, `FullName`, `Password`, `DateOfBirth`, `login_token`, `admin`) VALUES
(1, 'henry', 'henry@gmail.com.sg', 'Henry Stickmin', 'henrystickmin', '1999-04-01', NULL, NULL),
(2, 'john', 'aquila@gmail.com', 'Aquila', 'password', '1999-05-01', '9ac819c22be8dbe18481e26e163f3537', 1),
(3, 'tom', 'tomson@yahoo.com', 'Tom Thompson', 'password', '2005-06-01', NULL, NULL),
(4, 'chua', 'chua1020@e.ntu.edu.sg', 'chua1020', 'cntusg', '1990-03-20', '', NULL);

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
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `cinema`
--
ALTER TABLE `cinema`
  MODIFY `CinemaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `MovieID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `screeningtime2`
--
ALTER TABLE `screeningtime2`
  MODIFY `ScreenTimeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `seating`
--
ALTER TABLE `seating`
  MODIFY `SeatID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `shoppingcart`
--
ALTER TABLE `shoppingcart`
  MODIFY `ShoppingCartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `shoppingscreening`
--
ALTER TABLE `shoppingscreening`
  MODIFY `ShoppingScreeningID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
