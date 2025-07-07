CREATE DATABASE IF NOT EXISTS `SafeSpacePH`;
USE `SafeSpacePH`;
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2025 at 11:02 AM
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
-- Database: `safespaceph`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `aemail` varchar(255) NOT NULL,
  `apassword` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`aemail`, `apassword`) VALUES
('admin@safespaceph.com', '123');

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appoid` int(11) NOT NULL,
  `cid` int(10) DEFAULT NULL,
  `apponum` int(3) DEFAULT NULL,
  `scheduleid` int(10) DEFAULT NULL,
  `appodate` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appoid`, `cid`, `apponum`, `scheduleid`, `appodate`) VALUES
(1, 1, 1, 1, '2022-06-03');

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `cemail` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cpassword` varchar(255) DEFAULT NULL,
  `caddress` varchar(255) DEFAULT NULL,
  `cnic` varchar(15) DEFAULT NULL,
  `cdob` date DEFAULT NULL,
  `ctel` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`cid`, `cemail`, `cname`, `cpassword`, `caddress`, `cnic`, `cdob`, `ctel`) VALUES
(1, 'client@safespaceph.com', 'Client User', '123', '', '', NULL, ''),
(2, 'emhashenudara@gmail.com', 'Hashen Udara', '123', 'Sri Lanka', '0110000000', '2022-06-03', '0700000000'),
(3, 'gersradas@gmail.com', 'Gerard Doroja', '123456', '123456', '123456', '2025-07-24', '0712345678'),
(4, 'gerard@gmail.com', 'gerard  doroja', ':E:?Kr96Z72]M3t', 'dwdnwkjn', '1234561', '2025-08-28', '0712345678');

-- --------------------------------------------------------

--
-- Table structure for table `lawyer`
--

CREATE TABLE `lawyer` (
  `lawyerid` int(11) NOT NULL,
  `lawyeremail` varchar(255) DEFAULT NULL,
  `lawyername` varchar(255) DEFAULT NULL,
  `lawyerpassword` varchar(255) DEFAULT NULL,
  `lawyernic` varchar(15) DEFAULT NULL,
  `lawyertel` varchar(15) DEFAULT NULL,
  `specialties` int(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `lawyer`
--

INSERT INTO `lawyer` (`lawyerid`, `lawyeremail`, `lawyername`, `lawyerpassword`, `lawyernic`, `lawyertel`, `specialties`) VALUES
(1, 'lawyer@safespaceph.com', 'Test Lawyer', '123', '000000000', '0110000000', 1);

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `scheduleid` int(11) NOT NULL,
  `lawyerid` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `scheduledate` date DEFAULT NULL,
  `scheduletime` time DEFAULT NULL,
  `nop` int(4) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`scheduleid`, `lawyerid`, `title`, `scheduledate`, `scheduletime`, `nop`) VALUES
(1, '1', 'Test Session', '2050-01-01', '18:00:00', 50),
(2, '1', '1', '2022-06-10', '20:36:00', 1),
(3, '1', '12', '2022-06-10', '20:33:00', 1),
(4, '1', '1', '2022-06-10', '12:32:00', 1),
(5, '1', '1', '2022-06-10', '20:35:00', 1),
(6, '1', '12', '2022-06-10', '20:35:00', 1),
(7, '1', '1', '2022-06-24', '20:36:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `specialties`
--

CREATE TABLE `specialties` (
  `id` int(2) NOT NULL,
  `sname` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `specialties`
--

INSERT INTO specialties (id, sname) VALUES
(1, 'Family Law'),
(2, 'Workplace Harassment'),
(3, 'Sexual Harassment (RA 11313)'),
(4, 'Cybercrime / Online Harassment'),
(5, 'Human Trafficking / Exploitation'),
(6, 'Mental Health & Legal Safeguards'),
(7, 'Gender-Based Violence'),
(8, 'LGBTQ+ Rights'),
(9, 'Domestic Violence (RA 9262)'),
(10, 'Child Protection'),
(11, 'Legal Aid for Marginalized Groups'),
(12, 'Community Legal Education');

-- --------------------------------------------------------

--
-- Table structure for table `volunteer_lawyer`
--

CREATE TABLE `volunteer_lawyer` (
  `id` int(11) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `home_address` text NOT NULL,
  `years_experience` int(11) NOT NULL,
  `roll_number` varchar(50) NOT NULL,
  `license_file` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `motivation` text DEFAULT NULL,
  `consent_background_check` tinyint(1) DEFAULT 0,
  `agree_terms` tinyint(1) DEFAULT 0,
  `info_certified` tinyint(1) DEFAULT 0,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `availability_hours` int(11) DEFAULT NULL,
  `urgent_consult` varchar(20) DEFAULT NULL,
  `commitment_months` int(11) DEFAULT NULL,
  `preferred_areas` text DEFAULT NULL,
  `bar_region` varchar(255) DEFAULT NULL,
  `resume_file` varchar(255) DEFAULT NULL,
  `affiliation` varchar(255) DEFAULT NULL,
  `reference_contact` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `volunteer_lawyer`
--

INSERT INTO `volunteer_lawyer` (`id`, `last_name`, `first_name`, `email`, `contact_number`, `home_address`, `years_experience`, `roll_number`, `license_file`, `profile_photo`, `motivation`, `consent_background_check`, `agree_terms`, `info_certified`, `submitted_at`, `availability_hours`, `urgent_consult`, `commitment_months`, `preferred_areas`, `bar_region`, `resume_file`, `affiliation`, `reference_contact`) VALUES
(1, 'Doroja', 'Gerard Eric', 'gerardericdoroja@gmail.com', '09169558346', '123 ', 5, '1234567', 'uploads/license/license_686782d224bbe.png', 'uploads/profile_photo/photo_686782d225233.png', '12313123', 1, 1, 1, '2025-07-04 07:29:22', 3, 'Yes', 3, 'Family Law', '1234567', 'uploads/resume/resume_686782d226416.pdf', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `webuser`
--

CREATE TABLE `webuser` (
  `email` varchar(255) NOT NULL,
  `usertype` char(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `webuser`
--

INSERT INTO `webuser` (`email`, `usertype`) VALUES
('admin@safespaceph.com', 'a'),
('lawyer@safespaceph.com', 'l'),
('client@safespaceph.com', 'c'),
('emhashenudara@gmail.com', 'c'),
('gersradas@gmail.com', 'c'),
('gerard@gmail.com', 'c');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`aemail`);

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appoid`),
  ADD KEY `cid` (`cid`),
  ADD KEY `scheduleid` (`scheduleid`);

--

--
-- Indexes for table `lawyer`
--
ALTER TABLE `lawyer`
  ADD PRIMARY KEY (`lawyerid`),
  ADD KEY `specialties` (`specialties`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`scheduleid`),
  ADD KEY `lawyerid` (`lawyerid`);

--
-- Indexes for table `specialties`
--
ALTER TABLE `specialties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `volunteer_lawyer`
--
ALTER TABLE `volunteer_lawyer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `webuser`
--
ALTER TABLE `webuser`
  ADD PRIMARY KEY (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appoid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `scheduleid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `volunteer_lawyer`
--
ALTER TABLE `volunteer_lawyer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

ALTER TABLE `client` MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT;