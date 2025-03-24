-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2025 at 02:20 AM
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
-- Database: `role_management_db3`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Permissions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`ID`, `Name`, `Password`, `Permissions`) VALUES
(1, 'Admin', '1234', 'all');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `AttendanceID` int(11) NOT NULL,
  `TeacherAssignmentID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `LesuurID` int(11) NOT NULL,
  `AttendanceDate` date NOT NULL,
  `CohortID` int(11) NOT NULL,
  `PeriodID` int(11) NOT NULL,
  `Status` enum('Aanwezig','Afwezig','Laat','Ziek','Vrijstelling') NOT NULL DEFAULT 'Afwezig'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`AttendanceID`, `TeacherAssignmentID`, `UserID`, `LesuurID`, `AttendanceDate`, `CohortID`, `PeriodID`, `Status`) VALUES
(1, 7, 6, 1, '2025-03-09', 1, 1, 'Aanwezig'),
(2, 7, 25, 1, '2025-03-09', 1, 1, 'Aanwezig'),
(3, 7, 26, 1, '2025-03-09', 1, 1, 'Afwezig'),
(4, 7, 29, 1, '2025-03-09', 1, 1, 'Afwezig'),
(5, 7, 30, 1, '2025-03-09', 1, 1, 'Laat'),
(6, 7, 6, 2, '2025-03-09', 1, 1, 'Aanwezig'),
(7, 7, 25, 2, '2025-03-09', 1, 1, 'Aanwezig'),
(8, 7, 26, 2, '2025-03-09', 1, 1, 'Aanwezig'),
(9, 7, 29, 2, '2025-03-09', 1, 1, 'Aanwezig'),
(10, 7, 30, 2, '2025-03-09', 1, 1, 'Aanwezig'),
(11, 10, 6, 3, '2025-03-09', 1, 1, 'Aanwezig'),
(12, 10, 25, 3, '2025-03-09', 1, 1, 'Aanwezig'),
(13, 10, 26, 3, '2025-03-09', 1, 1, 'Aanwezig'),
(14, 10, 29, 3, '2025-03-09', 1, 1, 'Aanwezig'),
(15, 10, 30, 3, '2025-03-09', 1, 1, 'Aanwezig'),
(16, 7, 6, 7, '2025-03-11', 1, 1, 'Afwezig'),
(17, 7, 25, 7, '2025-03-11', 1, 1, 'Afwezig'),
(18, 7, 26, 7, '2025-03-11', 1, 1, 'Afwezig'),
(19, 7, 29, 7, '2025-03-11', 1, 1, 'Afwezig'),
(20, 7, 30, 7, '2025-03-11', 1, 1, 'Afwezig'),
(21, 7, 6, 8, '2025-03-11', 1, 1, 'Aanwezig'),
(22, 7, 25, 8, '2025-03-11', 1, 1, 'Afwezig'),
(23, 7, 26, 8, '2025-03-11', 1, 1, 'Aanwezig'),
(24, 7, 29, 8, '2025-03-11', 1, 1, 'Aanwezig'),
(25, 7, 30, 8, '2025-03-11', 1, 1, 'Aanwezig'),
(26, 7, 6, 3, '2025-03-17', 2, 11, 'Aanwezig'),
(27, 7, 25, 3, '2025-03-17', 2, 11, 'Aanwezig'),
(28, 7, 26, 3, '2025-03-17', 2, 11, 'Afwezig'),
(29, 7, 29, 3, '2025-03-17', 2, 11, 'Laat'),
(30, 7, 30, 3, '2025-03-17', 2, 11, 'Laat'),
(31, 7, 25, 6, '2025-03-23', 1, 1, 'Aanwezig'),
(32, 7, 26, 6, '2025-03-23', 1, 1, 'Aanwezig'),
(33, 7, 29, 6, '2025-03-23', 1, 1, 'Aanwezig'),
(34, 7, 30, 6, '2025-03-23', 1, 1, 'Aanwezig'),
(35, 7, 26, 4, '2025-03-23', 1, 1, 'Aanwezig'),
(36, 7, 29, 4, '2025-03-23', 1, 1, 'Aanwezig'),
(37, 7, 30, 4, '2025-03-23', 1, 1, 'Aanwezig'),
(38, 6, 6, 1, '2025-03-23', 2, 12, 'Aanwezig'),
(39, 6, 25, 1, '2025-03-23', 2, 12, 'Aanwezig'),
(40, 6, 26, 1, '2025-03-23', 2, 12, 'Aanwezig'),
(41, 6, 29, 1, '2025-03-23', 2, 12, 'Aanwezig'),
(43, 6, 6, 2, '2025-03-23', 2, 12, 'Aanwezig'),
(45, 6, 25, 2, '2025-03-23', 2, 12, 'Aanwezig'),
(47, 6, 26, 2, '2025-03-23', 2, 12, 'Aanwezig'),
(49, 6, 29, 2, '2025-03-23', 2, 12, 'Aanwezig'),
(50, 6, 30, 1, '2025-03-23', 2, 12, 'Aanwezig'),
(51, 6, 30, 2, '2025-03-23', 2, 12, 'Aanwezig'),
(52, 8, 22, 3, '2025-03-23', 2, 6, 'Aanwezig'),
(53, 8, 22, 4, '2025-03-23', 2, 6, 'Aanwezig'),
(54, 11, 22, 5, '2025-03-23', 2, 6, 'Aanwezig'),
(55, 11, 22, 6, '2025-03-23', 2, 6, 'Aanwezig'),
(56, 16, 45, 4, '2025-03-24', 13, 3, 'Aanwezig'),
(57, 16, 45, 5, '2025-03-24', 13, 3, 'Aanwezig'),
(58, 16, 46, 4, '2025-03-24', 13, 3, 'Aanwezig'),
(59, 16, 46, 5, '2025-03-24', 13, 3, 'Aanwezig'),
(60, 16, 47, 4, '2025-03-24', 13, 3, 'Aanwezig'),
(61, 16, 47, 5, '2025-03-24', 13, 3, 'Aanwezig'),
(62, 16, 48, 4, '2025-03-24', 13, 3, 'Aanwezig'),
(63, 16, 48, 5, '2025-03-24', 13, 3, 'Aanwezig'),
(64, 16, 49, 4, '2025-03-24', 13, 3, 'Aanwezig'),
(65, 16, 49, 5, '2025-03-24', 13, 3, 'Aanwezig'),
(66, 16, 50, 4, '2025-03-24', 13, 3, 'Aanwezig'),
(67, 16, 50, 5, '2025-03-24', 13, 3, 'Aanwezig');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `ClassID` int(11) NOT NULL,
  `ClassName` varchar(50) NOT NULL,
  `Cohort` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`ClassID`, `ClassName`, `Cohort`) VALUES
(1, 'Class 1A', '2024-2025'),
(2, 'Class 2B', '2024-2025'),
(3, 'Class 3C', '2024-2025'),
(4, 'Class 4D', '2023-2024'),
(6, 'Class 3E', '2024-2025'),
(7, 'Class 7F', '2024-2025'),
(8, 'Class 10B', '2024-2025'),
(9, 'Class 12R', '2024-2025'),
(10, 'PT4.06.21', '2024-2025'),
(11, 'PT4.06.11', '2024-2025'),
(12, 'PT3.06.21', '2024-2025'),
(13, 'PT3.06.11', '2024-2025'),
(14, 'PT2.06.01', '2024-2025'),
(15, 'PT2.06.02', '2024-2025'),
(16, 'PT1.06.01', '2024-2025'),
(17, 'PT1.06.02', '2024-2025');

-- --------------------------------------------------------

--
-- Table structure for table `cohorts`
--

CREATE TABLE `cohorts` (
  `CohortID` int(11) NOT NULL,
  `SchoolYear` varchar(20) NOT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cohorts`
--

INSERT INTO `cohorts` (`CohortID`, `SchoolYear`, `StartDate`, `EndDate`) VALUES
(1, 'Standaard Schooljaar', NULL, NULL),
(2, '2025-2026', '2025-10-01', '2026-08-18'),
(3, '2026-2027', '2026-10-01', '2027-08-18'),
(4, '2027-2028', '2027-10-01', '2028-08-18'),
(5, '2028-2029', '2028-10-01', '2029-08-18'),
(6, '2029-2030', '2029-10-01', '2030-08-18'),
(7, '2030-2031', '2030-10-01', '2031-08-18'),
(8, '2031-2032', '2031-10-01', '2032-08-18'),
(9, '2032-2033', '2032-10-01', '2033-08-18'),
(10, '2033-2034', '2033-10-01', '2034-08-18'),
(11, '2034-2035', '2034-10-01', '2035-08-18'),
(12, '2035-2036', '2035-10-01', '2036-08-18'),
(13, '2024-2025', '2024-10-01', '2025-08-18');

-- --------------------------------------------------------

--
-- Table structure for table `lesuren`
--

CREATE TABLE `lesuren` (
  `LesuurID` int(11) NOT NULL,
  `LesuurNummer` int(11) NOT NULL,
  `StartTijd` time NOT NULL,
  `EindTijd` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lesuren`
--

INSERT INTO `lesuren` (`LesuurID`, `LesuurNummer`, `StartTijd`, `EindTijd`) VALUES
(1, 1, '07:00:00', '07:45:00'),
(2, 2, '07:45:00', '08:30:00'),
(3, 3, '08:45:00', '09:30:00'),
(4, 4, '09:30:00', '10:15:00'),
(5, 5, '10:30:00', '11:15:00'),
(6, 6, '11:15:00', '12:00:00'),
(7, 7, '12:15:00', '13:00:00'),
(8, 8, '13:00:00', '13:45:00');

-- --------------------------------------------------------

--
-- Table structure for table `perioden`
--

CREATE TABLE `perioden` (
  `PeriodID` int(11) NOT NULL,
  `PeriodName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perioden`
--

INSERT INTO `perioden` (`PeriodID`, `PeriodName`) VALUES
(1, 'Periode 1'),
(2, 'Periode 2'),
(3, 'Periode 3'),
(4, 'Periode 4'),
(5, 'Periode 5'),
(6, 'Periode 6'),
(7, 'Periode 7'),
(8, 'Periode 8'),
(9, 'Periode 9'),
(10, 'Periode 10'),
(11, 'Periode 11'),
(12, 'Periode 12');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `RoleID` int(11) NOT NULL,
  `RoleName` varchar(50) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`RoleID`, `RoleName`, `Description`) VALUES
(1, 'Admin', 'Has all permissions'),
(2, 'Teacher', 'Teacher role'),
(3, 'Student', 'Student role'),
(4, 'Director', 'Director role'),
(5, 'RC', 'RC role');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `StudID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `Richting` varchar(10) DEFAULT NULL,
  `Cohort` varchar(20) DEFAULT NULL,
  `ClassID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`StudID`, `UserID`, `Name`, `email`, `Richting`, `Cohort`, `ClassID`) VALUES
(1, 3, 'Alice Brown', 'student1@example.com', '', '', 1),
(2, 6, 'Darryl Jones 3rd', 'darryljones@eg.com', 'INFR', '2024-2025', 4),
(5, 22, 'Sandesh', 'sandesh@example.com', 'ICT', '2024-2025', 2),
(6, 24, 'Aryan Bansradj', 'aryanbansradj@gmail.com', 'ICT', '2024-2025', 6),
(7, 25, 'Main Aquillera', 'main@example.com', 'ICT', '2024-2025', 4),
(8, 26, 'Goeptar Aswien', 'aswien@example.com', 'ICT', '2024-2025', 4),
(9, 29, 'Ary2', 'ary2@example.com', 'AV', '2024-2025', 4),
(10, 30, 'Bert', 'bert@example.com', 'ICT', '2024-2025', 4),
(11, 31, 'Max Soerohardjo', 'ms@example.com', 'AV', '2024-2025', 7),
(13, 38, 'Fatima', 'fatima@eg.com', 'ICT', '2024-2025', 16),
(14, 39, 'Dimitri', 'dimitri@eg.com', 'ICT', '2024-2025', 16),
(15, 40, 'Sofia', 'sofia@eg.com', 'ICT', '2024-2025', 16),
(16, 41, 'Lucia', 'lucia@eg.com', 'ICT', '2024-2025', 16),
(17, 42, 'Anya', 'anya@eg.com', 'ICT', '2024-2025', 16),
(18, 43, 'Yasmine', 'yasmine@eg.com', 'ICT', '2024-2025', 16),
(19, 45, 'Zara', 'zara@eg.com', 'ICT', '2024-2025', 17),
(20, 46, 'Jeroen', 'jeroen@eg.com', 'ICT', '2024-2025', 17),
(21, 47, 'Freya', 'freya@eg.com', 'ICT', '2024-2025', 17),
(22, 48, 'Mei', 'mei@eg.com', 'ICT', '2024-2025', 17),
(23, 49, 'Isabella', 'isabella@eg.com', 'ICT', '2024-2025', 17),
(24, 50, 'Samuel', 'samuel@eg.com', 'ICT', '2024-2025', 17);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `SubjectID` int(11) NOT NULL,
  `SubjectName` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`SubjectID`, `SubjectName`, `Description`) VALUES
(1, 'Mathematics', 'The study of numbers, equations, and patterns.'),
(2, 'English', 'Study of literature, language, and composition.'),
(3, 'History', 'Exploration of historical events and cultures.'),
(4, 'Science', 'Investigation of natural phenomena and experiments.'),
(5, 'ICT', 'Information and Communication Technology.'),
(6, 'Physics', 'Study of matter, energy, and their interactions.'),
(7, 'Chemistry', 'Exploring substances, reactions, and properties of matter.'),
(8, 'Biology', 'Study of living organisms and ecosystems.'),
(9, 'Geography', 'Understanding the Earth, its features, and human impact.'),
(10, 'Philosophy', 'Exploration of knowledge, existence, and ethics.'),
(11, 'Economics', 'Study of production, consumption, and transfer of wealth.'),
(12, 'Art', 'Creative expression through various visual forms.'),
(13, 'Music', 'Understanding and creating musical compositions and theory.'),
(14, 'Psychology', 'Study of human behavior and mental processes.'),
(15, 'Sociology', 'Analysis of society, culture, and social relationships.');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `TeacherID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `Richting` varchar(10) DEFAULT NULL,
  `ClassID` int(11) DEFAULT NULL,
  `Cohort` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`TeacherID`, `UserID`, `Name`, `email`, `Richting`, `ClassID`, `Cohort`) VALUES
(1, 1, 'John Doe', 'teacher1@example.com', '', NULL, ''),
(3, 11, 'ab', 'a@example.com', '', NULL, ''),
(7, 21, 'Sewradj', 'bom@gmail.com', 'ICT', 2, '2024-2025'),
(8, 23, 'Hans', 'hans@example.com', 'AV', 4, '2024-2025'),
(9, 27, 'Yoost', 'yoost@example.com', 'ICT', 4, '2024-2025'),
(11, 36, 'Jennifer', 'jen@eg.com', 'ICT', 8, '2025-2026'),
(12, 44, 'Chaira', 'chaira@eg.com', 'ICT', 17, '2024-2025');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `AssignmentID` int(11) NOT NULL,
  `TeacherID` int(11) NOT NULL,
  `SubjectID` int(11) NOT NULL,
  `ClassID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_assignments`
--

INSERT INTO `teacher_assignments` (`AssignmentID`, `TeacherID`, `SubjectID`, `ClassID`) VALUES
(1, 1, 1, 1),
(12, 1, 2, 4),
(2, 9, 2, 2),
(3, 9, 4, 3),
(4, 11, 5, 4),
(9, 16, 2, 2),
(8, 21, 3, 2),
(11, 21, 4, 2),
(10, 23, 1, 4),
(6, 23, 2, 4),
(7, 27, 5, 4),
(13, 36, 1, 8),
(15, 36, 1, 16),
(14, 36, 5, 6),
(16, 44, 7, 17),
(17, 44, 8, 17);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `RoleID` int(11) NOT NULL,
  `Richting` varchar(10) DEFAULT NULL,
  `Cohort` varchar(20) DEFAULT NULL,
  `Klas` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `Name`, `Password`, `Email`, `RoleID`, `Richting`, `Cohort`, `Klas`) VALUES
(1, 'John Doe', 'ten', 'teacher1@example.com', 2, NULL, NULL, NULL),
(3, 'Alice Brown', 'password789', 'student1@example.com', 3, NULL, NULL, NULL),
(5, 'administrator', 'admin', 'administrator@example.com', 1, NULL, NULL, NULL),
(6, 'Darryl Jones', '$2y$10$hsrDgP39D7T1Ipc0S6c1v.4pW.jQR/owE08yIdTVHrxLqNFQ65H3G', 'darryljones@example.com', 3, NULL, NULL, NULL),
(7, 'Admin', '$2y$10$7vKGm8cr8yn7IkYhfcdOTurjE6iNsBzjvm0AZ9rn.XuroKye0.JVi', 'admin@eg.com', 1, 'NONE', '0000-00-00', '0'),
(9, 'TestBewerk2', '$2y$10$7kOAYVnN4gdHl9fF8aPSM.dtooZhOSQMn7s7VvvRgfGYVufk92gXy', 'testbewerk2@eg.com', 2, 'AV', '2024-2025', '4'),
(11, 'ab', '$2y$10$KvzZAg735O2SgzbzGGXBuur8XlP8QaKLEVasAK5TNCqc/15ZYb3aO', 'a@example.com', 2, 'ICT', '204-225', '4'),
(12, 'Diana Davids', '$2y$10$v//RK0giqiMtTPom6tab8e7XBT7L27z8obRGqrlmdLlrO30ls1sO2', 'dianadavids@example.com', 3, 'AV', '2024-2025', '3'),
(16, 'Test', '$2y$10$/uEfQC3NiWQKHopSNyE6tuAhSW1pKrbsF.A4HTvR0KvG5WYMZJoNu', 'test@example.com', 2, 'ICT', '2024-2025', '2'),
(21, 'Sewradj', '$2y$10$nZYXCwtrj9Z/.09GciDVQujPGM/mq8yg1FgTrKMWqebvS2Jv.OOe2', 'bom@gmail.com', 2, 'ICT', '2024-2025', '2'),
(22, 'Sandesh', '$2y$10$WpaEDD42AT7wqE1ltTZKOujSnA4J0BnISGMzLQca4A1jEYJzim46u', 'sandesh@example.com', 3, 'ICT', '2024-2025', '2'),
(23, 'Hans', '$2y$10$3PgEqnuxCzJXzg0l9bYdGOlrpXq0pVQVORPfkmLiismI3K6/9zkMK', 'hans@example.com', 2, 'AV', '2024-2025', '4'),
(24, 'Aryan Bansradj', 'aryan', 'aryanbansradj@gmail.com', 3, 'ICT', '2024-2025', '6'),
(25, 'Main Aquillera', '$2y$10$DuJr7hHfbIXAQwLIngj2fubt6EJ0h1iTAWj0EWG3sadyWAbzMzr4S', 'main@example.com', 3, 'ICT', '2024-2025', '4'),
(26, 'Goeptar Aswien', '$2y$10$I9ZQO4LsGF.0J.VTB99T2eDWYMd45lnl2RezeeKoF10UQyNISraZa', 'aswien@example.com', 3, 'ICT', '2024-2025', '4'),
(27, 'Yoost', '$2y$10$z5YbuYMCt7ZC.5nEVpxneeqOX01sYMV/w9iwSJRgRMg2a2L4XsFRq', 'yoost@example.com', 2, 'ICT', '2024-2025', '4'),
(28, 'Dir', '$2y$10$1lp.mvGBB3IWuTVVo7.bkeTWhbweG8q04558H4DCT5aBQPTJUtYyS', 'dir@example.com', 4, NULL, NULL, NULL),
(29, 'Ary2', '$2y$10$uwmOWIsMiY4qFdlcfqGVzuNC5Chbt310yZqcYjCFV5sj331ysVyEa', 'ary2@example.com', 3, 'AV', '2024-2025', '2'),
(30, 'Bert', '$2y$10$.i4DwWkENqqL6jekDcHqh.ZKcrj2fcLHjhjC1szos1s5b/WuPhMZu', 'bert@example.com', 3, 'ICT', '2024-2025', '4'),
(31, 'Max Soerohardjo', '$2y$10$CFi0lu7/tHlYim.n.Q8cN.pkukZnXQsWd9N8SPGMZlelv4jPuh5F2', 'ms@example.com', 3, 'ICT', '2024-2025', '4'),
(33, 'RichtingPersoon', '$2y$10$DCvgR7iqbPykJUBTVbPhp.kXTRpPKhhdJKoozk368g8XMct2CsrA.', 'richt@eg.com', 5, 'ICT', '2024-2025', ''),
(36, 'Jennifer', '$2y$10$nwADs3fMxgyIZvcIRQxO4ezNe3eHX/pweubJ9BgisRJrI9iVB7pRG', 'jen@eg.com', 2, 'NONE', '2025-2026', '8'),
(37, 'Juliana', '$2y$10$AfFnq0tewIy5WSg4iNtj6.WzfICh5LPhkVkbnAEvgmrmhL0vor/0G', 'jul@eg.com', 5, 'AV', '2025-2026', ''),
(38, 'Fatima', '$2y$10$wfaj0MFarLrNlk8XFTyR4eBpZXo0q9ELc9HLy2PTTr/XLiYZnDvV2', 'fatima@eg.com', 3, 'ICT', '2024-2025', '16'),
(39, 'Dimitri', '$2y$10$QpiigSoHQNZVLusRmx94sOdmYww/pGD/OFdRrhb4sFI5ARsXRgIoO', 'dimitri@eg.com', 3, 'ICT', '2024-2025', '16'),
(40, 'Sofia', '$2y$10$7pZbY5YOpBCuTaWX6u7dJuKkrFBVPokcFLXVWHdzfzQE5/75TKXpC', 'sofia@eg.com', 3, 'ICT', '2024-2025', '16'),
(41, 'Lucia', '$2y$10$IUsdkmIk9tRHcuLTWg2IS.62qu83ubKEiRE7tSfkCtq8Wo33JJ3eC', 'lucia@eg.com', 3, 'ICT', '2024-2025', '16'),
(42, 'Anya', '$2y$10$z2/nTOQxTWXr19Xri1lsb.sHRQxG4tvkblSHRBLKYEan03w9Ph/OO', 'anya@eg.com', 3, 'ICT', '2024-2025', '16'),
(43, 'Yasmine', '$2y$10$kZ0va8RRpStUqjct2nQRG.SEBW7j2YAnso9CuI3QYT3mvEIjUSwye', 'yasmine@eg.com', 3, 'ICT', '2024-2025', '16'),
(44, 'Chaira', '$2y$10$y2SB8x3z/XXFAQ4R1hkmKe7WqjIlq72DxPgLKExndSo.0A43by03y', 'chaira@eg.com', 2, 'ICT', '2024-2025', '17'),
(45, 'Zara', '$2y$10$7HjPjm81juOu/Q/4IS8CV.9m5T7K2WP3KARQd0a/Gi/ZTrpuNha/u', 'zara@eg.com', 3, 'ICT', '2024-2025', '17'),
(46, 'Jeroen', '$2y$10$CrSZxDtEBFxMLRW3O.hAk.K2fptaC6r3H9tIQaQKjcXaFJqtbn4ve', 'jeroen@eg.com', 3, 'ICT', '2024-2025', '17'),
(47, 'Freya', '$2y$10$3GItHOHk/Cf96ovPPBcpD.t.QPRvFmM3kSCnHuh3lFdpNxfSOBtEm', 'freya@eg.com', 3, 'ICT', '2024-2025', '17'),
(48, 'Mei', '$2y$10$yEy5YA69KFju91E/jDyEOu/aGhq42veiPPj8D9rphz30s7Z.h.oI.', 'mei@eg.com', 3, 'ICT', '2024-2025', '17'),
(49, 'Isabella', '$2y$10$1Go3dllzbsZBPQjXFjHQYOYN/SLYMTN.qFHKI2M3k4d8uKKh97FtW', 'isabella@eg.com', 3, 'ICT', '2024-2025', '17'),
(50, 'Samuel', '$2y$10$leXgfwFDbPv/YjRp5d66J.Hir/.GE/DElAPYZQA.WP9rMpeHNn8PK', 'samuel@eg.com', 3, 'ICT', '2024-2025', '17');

-- --------------------------------------------------------

--
-- Table structure for table `vrijstelling`
--

CREATE TABLE `vrijstelling` (
  `VrijstellingID` int(11) NOT NULL,
  `TeacherAssignmentID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `LesuurID` int(11) NOT NULL,
  `CohortID` int(11) NOT NULL,
  `PeriodID` int(11) NOT NULL,
  `VrijstellingsDatum` date NOT NULL,
  `Reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vrijstelling`
--

INSERT INTO `vrijstelling` (`VrijstellingID`, `TeacherAssignmentID`, `UserID`, `LesuurID`, `CohortID`, `PeriodID`, `VrijstellingsDatum`, `Reason`) VALUES
(1, 7, 6, 1, 0, 0, '2025-03-17', ''),
(2, 7, 25, 1, 0, 0, '2025-03-17', ''),
(3, 7, 26, 1, 0, 0, '2025-03-17', ''),
(4, 7, 29, 1, 0, 0, '2025-03-17', ''),
(5, 7, 30, 1, 0, 0, '2025-03-17', ''),
(6, 6, 6, 8, 0, 0, '2025-03-17', ''),
(7, 6, 25, 8, 0, 0, '2025-03-17', ''),
(8, 6, 26, 8, 0, 0, '2025-03-17', ''),
(9, 6, 29, 8, 0, 0, '2025-03-17', ''),
(10, 6, 30, 8, 0, 0, '2025-03-17', ''),
(11, 7, 6, 8, 0, 0, '2025-03-23', ''),
(12, 7, 25, 8, 0, 0, '2025-03-23', ''),
(13, 7, 26, 8, 0, 0, '2025-03-23', ''),
(14, 7, 29, 8, 0, 0, '2025-03-23', ''),
(15, 7, 30, 8, 0, 0, '2025-03-23', ''),
(16, 7, 6, 6, 0, 0, '2025-03-23', ''),
(17, 7, 6, 4, 1, 1, '2025-03-23', ''),
(18, 7, 25, 4, 1, 1, '2025-03-23', ''),
(19, 6, 30, 1, 2, 12, '2025-03-23', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`AttendanceID`),
  ADD UNIQUE KEY `unique_attendance` (`TeacherAssignmentID`,`UserID`,`LesuurID`,`AttendanceDate`),
  ADD KEY `fk_attendance_ta` (`TeacherAssignmentID`),
  ADD KEY `fk_attendance_user` (`UserID`),
  ADD KEY `fk_attendance_lesuur` (`LesuurID`),
  ADD KEY `fk_attendance_cohort` (`CohortID`),
  ADD KEY `fk_attendance_period` (`PeriodID`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`ClassID`),
  ADD UNIQUE KEY `ClassName` (`ClassName`);

--
-- Indexes for table `cohorts`
--
ALTER TABLE `cohorts`
  ADD PRIMARY KEY (`CohortID`);

--
-- Indexes for table `lesuren`
--
ALTER TABLE `lesuren`
  ADD PRIMARY KEY (`LesuurID`),
  ADD UNIQUE KEY `LesuurNummer` (`LesuurNummer`);

--
-- Indexes for table `perioden`
--
ALTER TABLE `perioden`
  ADD PRIMARY KEY (`PeriodID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`RoleID`),
  ADD UNIQUE KEY `RoleName` (`RoleName`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`StudID`),
  ADD KEY `fk_student_class` (`ClassID`),
  ADD KEY `fk_student_users` (`UserID`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`SubjectID`),
  ADD UNIQUE KEY `SubjectName` (`SubjectName`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`TeacherID`,`UserID`),
  ADD UNIQUE KEY `UniqueEmailTeacher` (`email`),
  ADD KEY `fk_teacher_users` (`UserID`),
  ADD KEY `fk_teacher_class` (`ClassID`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`AssignmentID`),
  ADD UNIQUE KEY `unique_assignment` (`TeacherID`,`SubjectID`,`ClassID`),
  ADD KEY `fk_assignment_subject` (`SubjectID`),
  ADD KEY `fk_assignment_class` (`ClassID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `fk_users_roles` (`RoleID`);

--
-- Indexes for table `vrijstelling`
--
ALTER TABLE `vrijstelling`
  ADD PRIMARY KEY (`VrijstellingID`),
  ADD UNIQUE KEY `unique_vrijstelling` (`TeacherAssignmentID`,`UserID`,`LesuurID`,`VrijstellingsDatum`),
  ADD KEY `fk_vrijstelling_ta` (`TeacherAssignmentID`),
  ADD KEY `fk_vrijstelling_user` (`UserID`),
  ADD KEY `fk_vrijstelling_lesuur` (`LesuurID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `AttendanceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `ClassID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `cohorts`
--
ALTER TABLE `cohorts`
  MODIFY `CohortID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `lesuren`
--
ALTER TABLE `lesuren`
  MODIFY `LesuurID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `perioden`
--
ALTER TABLE `perioden`
  MODIFY `PeriodID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `StudID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `SubjectID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `TeacherID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `AssignmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `vrijstelling`
--
ALTER TABLE `vrijstelling`
  MODIFY `VrijstellingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_cohort` FOREIGN KEY (`CohortID`) REFERENCES `cohorts` (`CohortID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_lesuur` FOREIGN KEY (`LesuurID`) REFERENCES `lesuren` (`LesuurID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_period` FOREIGN KEY (`PeriodID`) REFERENCES `perioden` (`PeriodID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_ta` FOREIGN KEY (`TeacherAssignmentID`) REFERENCES `teacher_assignments` (`AssignmentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attendance_user` FOREIGN KEY (`UserID`) REFERENCES `users` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `fk_student_class` FOREIGN KEY (`ClassID`) REFERENCES `classes` (`ClassID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_student_users` FOREIGN KEY (`UserID`) REFERENCES `users` (`ID`);

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `fk_teacher_class` FOREIGN KEY (`ClassID`) REFERENCES `classes` (`ClassID`),
  ADD CONSTRAINT `fk_teacher_users` FOREIGN KEY (`UserID`) REFERENCES `users` (`ID`);

--
-- Constraints for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD CONSTRAINT `fk_assignment_class` FOREIGN KEY (`ClassID`) REFERENCES `classes` (`ClassID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assignment_subject` FOREIGN KEY (`SubjectID`) REFERENCES `subjects` (`SubjectID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assignment_teacher` FOREIGN KEY (`TeacherID`) REFERENCES `users` (`ID`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_roles` FOREIGN KEY (`RoleID`) REFERENCES `roles` (`RoleID`);

--
-- Constraints for table `vrijstelling`
--
ALTER TABLE `vrijstelling`
  ADD CONSTRAINT `fk_vrijstelling_lesuur` FOREIGN KEY (`LesuurID`) REFERENCES `lesuren` (`LesuurID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vrijstelling_ta` FOREIGN KEY (`TeacherAssignmentID`) REFERENCES `teacher_assignments` (`AssignmentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vrijstelling_user` FOREIGN KEY (`UserID`) REFERENCES `users` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
