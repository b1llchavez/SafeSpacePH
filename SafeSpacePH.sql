-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jul 26, 2025 at 06:17 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `SafeSpacePH`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `aemail` varchar(255) NOT NULL,
  `apassword` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
  `appoid` int NOT NULL,
  `cid` int DEFAULT NULL,
  `apponum` int DEFAULT NULL,
  `scheduleid` int DEFAULT NULL,
  `appodate` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `description` text,
  `cancellation_reason` varchar(255) DEFAULT NULL,
  `cancellation_explanation` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `cid` int NOT NULL,
  `cemail` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cpassword` varchar(255) DEFAULT NULL,
  `caddress` varchar(255) DEFAULT NULL,
  `cdob` date DEFAULT NULL,
  `ctel` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`cid`, `cemail`, `cname`, `cpassword`, `caddress`, `cdob`, `ctel`) VALUES
(1, 'client@safespaceph.com', 'Client User', '123', '123 Client Address, Quezon City', '2000-01-01', '09123456789');

-- --------------------------------------------------------

--
-- Table structure for table `identity_verifications`
--

CREATE TABLE `identity_verifications` (
  `id` int NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `dob` date NOT NULL,
  `sex` varchar(20) NOT NULL,
  `civil_status` varchar(50) NOT NULL,
  `citizenship` varchar(100) NOT NULL,
  `birth_place` varchar(255) NOT NULL,
  `present_address` text NOT NULL,
  `permanent_address` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `emergency_contact_name` varchar(255) NOT NULL,
  `emergency_contact_number` varchar(20) NOT NULL,
  `emergency_contact_relationship` varchar(100) NOT NULL,
  `id_type` varchar(100) NOT NULL,
  `id_number` varchar(255) NOT NULL,
  `id_photo_front_path` varchar(255) DEFAULT NULL,
  `id_photo_back_path` varchar(255) DEFAULT NULL,
  `profile_photo_path` varchar(255) DEFAULT NULL,
  `agree_terms` tinyint(1) NOT NULL,
  `submission_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lawyer`
--

CREATE TABLE `lawyer` (
  `lawyerid` int NOT NULL,
  `lawyeremail` varchar(255) DEFAULT NULL,
  `lawyername` varchar(255) DEFAULT NULL,
  `lawyerpassword` varchar(255) DEFAULT NULL,
  `lawyerrollid` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `lawyertel` varchar(15) DEFAULT NULL,
  `specialties` int DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  `meeting_platform` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lawyer`
--

INSERT INTO `lawyer` (`lawyerid`, `lawyeremail`, `lawyername`, `lawyerpassword`, `lawyerrollid`, `lawyertel`, `specialties`, `meeting_link`, `meeting_platform`) VALUES
(1, 'lawyer@safespaceph.com', 'Test Lawyer', '123', '12345', '09123456780', 1, 'SafeSpace PH Office, P. Paredes St., Sampaloc, Manila 1015', 'SafeSpace PH Office');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `reporter_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reporter_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reporter_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `legal_consultation_requested` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'No',
  `supplementary_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `report_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `admin_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `scheduleid` int NOT NULL,
  `lawyerid` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `scheduledate` date DEFAULT NULL,
  `scheduletime` time DEFAULT NULL,
  `nop` int DEFAULT NULL,
  `clientid` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `specialties`
--

CREATE TABLE `specialties` (
  `id` int NOT NULL,
  `sname` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `specialties`
--

INSERT INTO `specialties` (`id`, `sname`) VALUES
(1, 'Family Law'),
(2, 'Child Protection'),
(3, 'Gender-Based Violence'),
(4, 'Human Rights'),
(5, 'Women\'s Rights'),
(6, 'LGBTQ+ Advocacy'),
(7, 'Domestic Violence'),
(8, 'Sexual Harassment'),
(9, 'Safe Spaces Act'),
(10, 'Anti-Bullying'),
(11, 'Cybercrime and Online Harassment'),
(12, 'Mental Health Law'),
(13, 'Disability Rights'),
(14, 'Labor Law'),
(15, 'Anti-Discrimination'),
(16, 'Community Legal Education'),
(17, 'Victim Support'),
(18, 'Juvenile Justice'),
(19, 'Public Interest Law'),
(20, 'Legal Aid'),
(21, 'Privacy and Data Protection'),
(22, 'Trafficking in Persons'),
(23, 'Alternative Dispute Resolution'),
(24, 'Civil Rights'),
(25, 'Criminal Law'),
(26, 'Mediation and Counseling');

-- --------------------------------------------------------

--
-- Table structure for table `volunteer_lawyer`
--

CREATE TABLE `volunteer_lawyer` (
  `id` int NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `home_address` text NOT NULL,
  `years_experience` int NOT NULL,
  `roll_number` varchar(50) NOT NULL,
  `license_file` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `motivation` text,
  `consent_background_check` tinyint(1) DEFAULT '0',
  `agree_terms` tinyint(1) DEFAULT '0',
  `info_certified` tinyint(1) DEFAULT '0',
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `availability_hours` int DEFAULT NULL,
  `urgent_consult` varchar(20) DEFAULT NULL,
  `commitment_months` int DEFAULT NULL,
  `preferred_areas` text,
  `bar_region` varchar(255) DEFAULT NULL,
  `resume_file` varchar(255) DEFAULT NULL,
  `affiliation` varchar(255) DEFAULT NULL,
  `reference_contact` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `webuser`
--

CREATE TABLE `webuser` (
  `email` varchar(255) NOT NULL,
  `usertype` char(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `webuser`
--

INSERT INTO `webuser` (`email`, `usertype`) VALUES
('admin@safespaceph.com', 'a'),
('lawyer@safespaceph.com', 'l'),
('client@safespaceph.com', 'c');

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
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`cid`);

--
-- Indexes for table `identity_verifications`
--
ALTER TABLE `identity_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- Indexes for table `lawyer`
--
ALTER TABLE `lawyer`
  ADD PRIMARY KEY (`lawyerid`),
  ADD KEY `specialties` (`specialties`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `appoid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `cid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `identity_verifications`
--
ALTER TABLE `identity_verifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lawyer`
--
ALTER TABLE `lawyer`
  MODIFY `lawyerid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `scheduleid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `volunteer_lawyer`
--
ALTER TABLE `volunteer_lawyer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
