CREATE DATABASE IF NOT EXISTS `SafeSpacePH`;
USE `SafeSpacePH`;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `admin` (
  `aemail` varchar(255) NOT NULL,
  `apassword` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


INSERT INTO `admin` (`aemail`, `apassword`) VALUES
('admin@safespaceph.com', '123');


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


INSERT INTO `appointment` (`appoid`, `cid`, `apponum`, `scheduleid`, `appodate`, `status`, `description`, `cancellation_reason`, `cancellation_explanation`) VALUES
(17, 13, 1753340073, 27, '2025-07-24', 'cancelled', 'HAHAUHDAUIGDAHISBDANSK D', 'Personal emergency', 'testing ulit');



CREATE TABLE `client` (
  `cid` int NOT NULL,
  `cemail` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `cpassword` varchar(255) DEFAULT NULL,
  `caddress` varchar(255) DEFAULT NULL,
  `cdob` date DEFAULT NULL,
  `ctel` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



INSERT INTO `client` (`cid`, `cemail`, `cname`, `cpassword`, `caddress`, `cdob`, `ctel`) VALUES
(1, 'client@safespaceph.com', 'Client User', '123', '', NULL, ''),
(14, 'sihareg730@simerm.com', 'SafeSpace PH S', 'Testing123!', '119 San Isidro St., Villa Espana II, Brgy. Tatalon', '2005-01-10', '9917912370'),
(13, 'mamornobillc@gmail.com', 'Bill Mamorno', 'Testing123!', '119 San Isidro St., Villa Espana II, Brgy. Tatalon', '2006-03-18', '9917912370');



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



INSERT INTO `identity_verifications` (`id`, `first_name`, `middle_name`, `last_name`, `suffix`, `dob`, `sex`, `civil_status`, `citizenship`, `birth_place`, `present_address`, `permanent_address`, `email`, `contact_number`, `emergency_contact_name`, `emergency_contact_number`, `emergency_contact_relationship`, `id_type`, `id_number`, `id_photo_front_path`, `id_photo_back_path`, `profile_photo_path`, `agree_terms`, `submission_date`, `is_verified`) VALUES
(3, 'Bill', 'Chavez', 'Mamorno', '', '2006-03-18', 'Male', 'Single', 'Filipino', 'Manila', '119 San Isidro St., Villa Espana II, Brgy. Tatalon', '119 San Isidro St., Villa Espana II, Brgy. Tatalon', 'mamornobillc@gmail.com', '09917912370', 'Edmuna Mamorno', '09396035648', 'Mother', 'Driver&#039;s License', '11001212154548', 'uploads/id_front/front_6873c9f225cc6_id 1.jpg', 'uploads/id_back/back_6873c9f225fd7_id 2.png', 'uploads/profile_photo_client/profile_6873c9f226122_profile photo sample.jpg', 1, '2025-07-13 15:00:02', 0);


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


INSERT INTO `lawyer` (`lawyerid`, `lawyeremail`, `lawyername`, `lawyerpassword`, `lawyerrollid`, `lawyertel`, `specialties`, `meeting_link`, `meeting_platform`) VALUES
(1, 'lawyer@safespaceph.com', 'Test Lawyer', '123', '202411951', '09917912370', 18, 'SafeSpace PH Office, P. Paredes St., Sampaloc, Manila 1015', 'SafeSpace PH Office');



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
  `admin_notes` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `reports` (`id`, `client_id`, `reporter_name`, `reporter_phone`, `reporter_email`, `title`, `description`, `legal_consultation_requested`, `supplementary_notes`, `file_name`, `file_path`, `uploaded_at`, `report_status`, `admin_notes`) VALUES
(6, 13, 'Bill Mamorno', '09917912370', 'mamornobillc@gmail.com', 'Violation Report: Public Harassment', 'Violation Type: Public Harassment\nDate of Incident: 2005-11-11\nTime of Incident: 11:11\nLocation of Incident: working\nPerpetrator Information: white haired\nVictim\'s Name: Bill ERIC Mamorno\nVictim\'s Contact: mamornobillc@gmail.com\n\n---Reporter\'s Detailed Description---\nworking 2', 'Yes', 'sdsdsds', 'report_68821a0c41eea.jpg', '../uploads/reports/report_68821a0c41eea.jpg', '2025-07-24 19:33:32', 'rejected', 'sasa');



CREATE TABLE `schedule` (
  `scheduleid` int NOT NULL,
  `lawyerid` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `scheduledate` date DEFAULT NULL,
  `scheduletime` time DEFAULT NULL,
  `nop` int DEFAULT NULL,
  `clientid` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



INSERT INTO `schedule` (`scheduleid`, `lawyerid`, `title`, `scheduledate`, `scheduletime`, `nop`, `clientid`) VALUES
(26, NULL, 'DDD', '2026-02-11', '15:33:00', 1, 1),
(27, '1', 'TETSING TESTING', '2027-11-11', '11:11:00', 1, 13);



CREATE TABLE `specialties` (
  `id` int NOT NULL,
  `sname` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


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
  `reference_contact` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


CREATE TABLE `webuser` (
  `email` varchar(255) NOT NULL,
  `usertype` char(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



INSERT INTO `webuser` (`email`, `usertype`) VALUES
('admin@safespaceph.com', 'a'),
('lawyer@safespaceph.com', 'l'),
('client@safespaceph.com', 'u'),
('mamornobillc@gmail.com', 'u'),
('sihareg730@simerm.com', 'u'),
('jarix90822@mvpmedix.com', 'l');


ALTER TABLE `admin`
  ADD PRIMARY KEY (`aemail`);


ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appoid`),
  ADD KEY `cid` (`cid`),
  ADD KEY `scheduleid` (`scheduleid`);


ALTER TABLE `client`
  ADD PRIMARY KEY (`cid`);


ALTER TABLE `identity_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_unique` (`email`);


ALTER TABLE `lawyer`
  ADD PRIMARY KEY (`lawyerid`),
  ADD KEY `specialties` (`specialties`);


ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `schedule`
  ADD PRIMARY KEY (`scheduleid`),
  ADD KEY `lawyerid` (`lawyerid`),
  ADD KEY `fk_client_schedule` (`clientid`);


ALTER TABLE `specialties`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `volunteer_lawyer`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `webuser`
  ADD PRIMARY KEY (`email`);




ALTER TABLE `appointment`
  MODIFY `appoid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;


ALTER TABLE `client`
  MODIFY `cid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;


ALTER TABLE `identity_verifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `lawyer`
  MODIFY `lawyerid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;


ALTER TABLE `reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;


ALTER TABLE `schedule`
  MODIFY `scheduleid` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;



ALTER TABLE `volunteer_lawyer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;