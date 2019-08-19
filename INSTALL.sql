-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 08, 2017 at 02:41 PM
-- Server version: 5.7.20-0ubuntu0.16.04.1
-- PHP Version: 7.0.22-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


--
-- Database: `starlight`
--

-- --------------------------------------------------------

--
-- Table structure for table `accidents`
--

CREATE TABLE `abc` (
  `abc_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `abc_assoc` enum('school','room','child') NOT NULL,
  `abc_comment` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `abc_plan_assoc` (
  `abc_plan_assoc_id` int(11) NOT NULL,
  `abc_plan_fk` int(11) NOT NULL,
  `assoc_type` enum('school','room','child','') NOT NULL,
  `assoc_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `abc_plan_block` (
  `block_id` int(11) NOT NULL,
  `abc_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `antecedents` text NOT NULL,
  `behaviour` text NOT NULL,
  `consequence` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `accidents` (
  `accident_id` int(11) UNSIGNED NOT NULL,
  `child_id` int(11) UNSIGNED NOT NULL,
  `school_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `accident_date_time` datetime DEFAULT NULL,
  `accident_location` varchar(255) DEFAULT NULL,
  `accident_body_parts` varchar(255) DEFAULT NULL,
  `accident_description` text,
  `accident_cause` text,
  `accident_witnesses` varchar(255) DEFAULT NULL,
  `signed_by_user` int(11) UNSIGNED DEFAULT NULL,
  `signature` text,
  `signed_by` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `activities` (
  `activity_id` int(11) NOT NULL,
  `goal_id` int(11) NOT NULL,
  `activity_url` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD UNIQUE KEY `goal_id` (`goal_id`);

ALTER TABLE `activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Table structure for table `accident_body_parts`
--

CREATE TABLE `accident_body_parts` (
  `part_id` int(11) UNSIGNED NOT NULL,
  `part_name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `accident_body_parts`
--

INSERT INTO `accident_body_parts` (`part_id`, `part_name`) VALUES
(1, 'Head'),
(2, 'Front Torso'),
(3, 'Back'),
(4, 'Left Leg'),
(5, 'Right Leg'),
(6, 'Left Foot'),
(7, 'Right Foot'),
(8, 'Left Arm'),
(9, 'Right Arm'),
(10, 'Left Hand'),
(11, 'Right Hand'),
(12, 'Other');

-- --------------------------------------------------------

--
-- Table structure for table `accident_logs`
--

CREATE TABLE `accident_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `accident_id` int(11) UNSIGNED NOT NULL,
  `recipient` enum('T','P') DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `activation_tokens`
--

CREATE TABLE `activation_tokens` (
  `id` varchar(16) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(11) UNSIGNED NOT NULL,
  `selector` varchar(32) NOT NULL,
  `validator` varchar(64) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `broadcasts`
--

CREATE TABLE `broadcasts` (
  `broadcast_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `broadcast_subject` varchar(64) NOT NULL,
  `broadcast_message` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `broadcast_user`
--

CREATE TABLE `broadcast_user` (
  `broadcast_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `checklists`
--

CREATE TABLE `checklists` (
  `checklist_id` int(11) UNSIGNED NOT NULL,
  `school_id` int(11) UNSIGNED DEFAULT NULL,
  `country_id` varchar(2) DEFAULT NULL,
  `checklist_name` varchar(64) NOT NULL,
  `checklist_month_min` tinyint(3) UNSIGNED NOT NULL,
  `checklist_month_max` tinyint(3) UNSIGNED NOT NULL,
  `checklist_status` enum('A','D') NOT NULL DEFAULT 'A',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `checklists`
--

INSERT INTO `checklists` (`checklist_id`, `school_id`, `country_id`, `checklist_name`, `checklist_month_min`, `checklist_month_max`, `checklist_status`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'TeachKloud developmental checklist 0 to 3 months', 0, 3, 'A', '2017-09-05 15:53:07', '2017-09-05 15:53:07'),
(2, NULL, NULL, 'TeachKloud developmental checklist 4 to 7 months', 4, 7, 'A', '2017-09-07 13:39:12', '2017-09-07 13:39:12'),
(3, NULL, NULL, 'TeachKloud developmental checklist 8 to 12 months', 8, 12, 'A', '2017-09-07 13:39:12', '2017-09-07 13:39:12'),
(4, NULL, NULL, 'TeachKloud developmental checklist 13 to 24 months', 13, 24, 'A', '2017-09-07 13:39:12', '2017-09-07 13:39:12'),
(5, NULL, NULL, 'TeachKloud developmental checklist 25 to 36 months', 25, 36, 'A', '2017-09-07 13:39:12', '2017-09-07 13:39:12'),
(6, NULL, NULL, 'TeachKloud developmental checklist 37 to 48 months', 37, 48, 'A', '2017-09-07 13:39:12', '2017-09-07 13:39:12'),
(7, NULL, NULL, 'TeachKloud developmental checklist 49 to 60 months', 49, 60, 'A', '2017-09-07 13:39:12', '2017-09-07 13:39:12');

-- --------------------------------------------------------

--
-- Table structure for table `checklist_categories`
--

CREATE TABLE `checklist_categories` (
  `category_id` int(11) UNSIGNED NOT NULL,
  `category_name` varchar(32) NOT NULL,
  `category_sort` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `checklist_categories`
--

INSERT INTO `checklist_categories` (`category_id`, `category_name`, `category_sort`) VALUES
(1, 'Motor', 1),
(2, 'Cognitive', 4),
(3, 'Language', 5),
(4, 'Social', 6),
(5, 'Personal/Self-Help', 7),
(6, 'Gross Motor', 2),
(7, 'Fine Motor', 3),
(8, 'Emotional', 8);

-- --------------------------------------------------------

--
-- Table structure for table `checklist_child`
--

CREATE TABLE `checklist_child` (
  `id` int(11) UNSIGNED NOT NULL,
  `token_id` varchar(16) NOT NULL,
  `milestone_id` int(11) UNSIGNED NOT NULL,
  `child_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `observation` tinyint(1) NOT NULL DEFAULT '0',
  `red_flag` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `checklist_milestones`
--

CREATE TABLE `checklist_milestones` (
  `milestone_id` int(11) UNSIGNED NOT NULL,
  `milestone_description` varchar(255) NOT NULL,
  `checklist_id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `milestone_sort` int(11) UNSIGNED NOT NULL,
  `milestone_status` enum('A','D') NOT NULL DEFAULT 'A',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `checklist_milestones`
--

INSERT INTO `checklist_milestones` (`milestone_id`, `milestone_description`, `checklist_id`, `category_id`, `milestone_sort`, `milestone_status`, `created_at`, `updated_at`) VALUES
(1, 'Retains hold of object/rattle (1-2 months)', 1, 1, 1, 'A', NULL, NULL),
(2, 'Brings hands towards centre of body when lying on back (1-2 months)', 1, 1, 2, 'A', NULL, NULL),
(3, 'Raises head and cheek when lying on stomach (3 months)', 1, 1, 3, 'A', NULL, NULL),
(4, 'Supports upper body with arms when lying on stomach (3 months)', 1, 1, 4, 'A', NULL, NULL),
(5, 'Stretches legs out when lying on stomach or back (2-3 months)', 1, 1, 5, 'A', NULL, NULL),
(6, 'Opens and shuts hands (2-3 months)', 1, 1, 6, 'A', NULL, NULL),
(7, 'Pushes down on his legs when his feet are placed on firm surface (3 months)', 1, 1, 7, 'A', NULL, NULL),
(8, 'Occasionally rolls from stomach to back (3 months)', 1, 1, 8, 'A', NULL, NULL),
(9, 'Responds to voice i.e. turn to, wiggle, reacts (0-1 months)', 1, 2, 1, 'A', NULL, NULL),
(10, 'Watches face intently (2-3 months)', 1, 2, 2, 'A', NULL, NULL),
(11, 'Follows moving objects (2 months)', 1, 2, 3, 'A', NULL, NULL),
(12, 'Recognizes familiar objects and people at a distance (3 months)', 1, 2, 4, 'A', NULL, NULL),
(13, 'Starts using hands and eyes in coordination (3 months)', 1, 2, 5, 'A', NULL, NULL),
(14, 'Makes sucking sounds (1-2 months)', 1, 3, 1, 'A', NULL, NULL),
(15, 'Smiles at the sound of voice (2-3 months)', 1, 3, 2, 'A', NULL, NULL),
(16, 'Cooing noises; vocal play (begins at 3 months)', 1, 3, 3, 'A', NULL, NULL),
(17, 'Attends to sound (1-3 months)', 1, 3, 4, 'A', NULL, NULL),
(18, 'Startles to loud noise (1-3 months)', 1, 3, 5, 'A', NULL, NULL),
(19, 'Makes eye contact (0-1 mos.)', 1, 4, 1, 'A', NULL, NULL),
(20, 'Begins to develop a social smile (1-3 mos.)', 1, 4, 2, 'A', NULL, NULL),
(21, 'Enjoys playing with other people and may cry when playing stops (2-3 mos.)', 1, 4, 3, 'A', NULL, NULL),
(22, 'Becomes more communicative and expressive with face and body (2-3 mos.)', 1, 4, 4, 'A', NULL, NULL),
(23, 'Pushes up on extended arms (5 mos.)', 2, 1, 1, 'A', NULL, NULL),
(24, 'Pulls to sitting with no head lag (5 mos.)', 2, 1, 2, 'A', NULL, NULL),
(25, 'Sits with support of his hands (5-6 mos.)', 2, 1, 3, 'A', NULL, NULL),
(26, 'Sits unsupported for short periods (6-8 mos.)', 2, 1, 4, 'A', NULL, NULL),
(27, 'Supports whole weight on legs (6-7 mos.)', 2, 1, 5, 'A', NULL, NULL),
(28, 'Grasps feet (6 mos.)', 2, 1, 6, 'A', NULL, NULL),
(29, 'Transfers objects from hand to hand (6-7 mos.)', 2, 1, 7, 'A', NULL, NULL),
(30, 'Uses raking grasp (not pincer) (6 mos.)', 2, 1, 8, 'A', NULL, NULL),
(31, 'Routinely rolls from stomach to back and back to stomach (6 mos.)', 2, 1, 9, 'A', NULL, NULL),
(32, 'Plays peek-a-boo (4-7 mos.)', 2, 2, 1, 'A', NULL, NULL),
(33, 'Looks for a family member or pet when named (4-7 mos.)', 2, 2, 2, 'A', NULL, NULL),
(34, 'Explores with hands and mouth (4-7 mos.)', 2, 2, 3, 'A', NULL, NULL),
(35, 'Tracks moving objects with ease (4-7 mos.)', 2, 2, 4, 'A', NULL, NULL),
(36, 'Finds partially hidden objects (6-7 mos.)', 2, 2, 5, 'A', NULL, NULL),
(37, 'Grasps objects dangling in front of him (5-6 mos.)', 2, 2, 6, 'A', NULL, NULL),
(38, 'Looks for fallen toys (5-7 mos.)', 2, 2, 7, 'A', NULL, NULL),
(39, 'Laughs and squeals out loud (4-7 mos.)', 2, 3, 1, 'A', NULL, NULL),
(40, 'Distinguishes emotions by tone of voice (4-7 mos.)', 2, 3, 2, 'A', NULL, NULL),
(41, 'Responds to sound by making sounds (4-6 mos.)', 2, 3, 3, 'A', NULL, NULL),
(42, 'Responds to spoken “bye-bye” by waving (4-7 mos.)', 2, 3, 4, 'A', NULL, NULL),
(43, 'Uses voice to express joy and displeasure (4-6 mos.)', 2, 3, 5, 'A', NULL, NULL),
(44, 'Localizes or turns toward sounds (5-6 mos.)', 2, 3, 6, 'A', NULL, NULL),
(45, 'Syllable repetition begins (5-7 mos.)', 2, 3, 7, 'A', NULL, NULL),
(46, 'Enjoys social play (4-7 mos.)', 2, 4, 1, 'A', NULL, NULL),
(47, 'Interested in mirror images (5-7 mos.)', 2, 4, 2, 'A', NULL, NULL),
(48, 'Can calm down with ½ hour when upset (6 mos.)', 2, 4, 3, 'A', NULL, NULL),
(49, 'Responds to other people’s expression of emotion (4-7 mos.)', 2, 4, 4, 'A', NULL, NULL),
(50, 'Crawls forward on belly (8-9 mos.)', 3, 6, 1, 'A', NULL, NULL),
(51, 'Assumes hand and knee position (8-9 mos.)', 3, 6, 2, 'A', NULL, NULL),
(52, 'Gets to sitting position without assistance (8-10 mos.)', 3, 6, 3, 'A', NULL, NULL),
(53, 'Pulls self up to standing position at furniture (8-10 mos.)', 3, 6, 4, 'A', NULL, NULL),
(54, 'Creeps on hands and knees (9 mos.)', 3, 6, 5, 'A', NULL, NULL),
(55, 'Gets from sitting to crawling or prone (lying on stomach) position (9-10 mos.)', 3, 6, 6, 'A', NULL, NULL),
(56, 'Walks holding on to furniture (10-13 mos.)', 3, 6, 7, 'A', NULL, NULL),
(57, 'Stands momentarily without support (11-13 mos.)', 3, 6, 8, 'A', NULL, NULL),
(58, 'May walk two or three steps without support (11-13 mos.)', 3, 6, 9, 'A', NULL, NULL),
(59, 'Uses pincer grasp (grasp using thumb and index finger) (7-10 mos.)', 3, 7, 1, 'A', NULL, NULL),
(60, 'Bangs two one-inch cubes together (8-12 mos.)', 3, 7, 2, 'A', NULL, NULL),
(61, 'Pokes with index finger (9-12 mos.)', 3, 7, 3, 'A', NULL, NULL),
(62, 'Puts objects into container (10-12 mos.)', 3, 7, 4, 'A', NULL, NULL),
(63, 'Takes objects out of container (10-12 mos.)', 3, 7, 5, 'A', NULL, NULL),
(64, 'Tries to imitate scribbling (10-12 mos.)', 3, 7, 6, 'A', NULL, NULL),
(65, 'Looks at correct picture when image is named (8-9 mos.)', 3, 2, 1, 'A', NULL, NULL),
(66, 'Explores objects in many different ways (shaking, banging, throwing, dropping) (8-10 mos.)', 3, 2, 2, 'A', NULL, NULL),
(67, 'Enjoys looking at pictures in books (9-12 mos.)', 3, 2, 3, 'A', NULL, NULL),
(68, 'Imitates gestures (9-12 mos.)', 3, 2, 4, 'A', NULL, NULL),
(69, 'Engages in simple games of Peek-a-Boo, Pat-a-Cake, or rolling ball to another (9-12 mos.)', 3, 2, 5, 'A', NULL, NULL),
(70, 'Finds hidden objects easily (10-12 mos.)', 3, 2, 6, 'A', NULL, NULL),
(71, 'Babbles “dada” and “mama” (7-8 mos.)', 3, 3, 1, 'A', NULL, NULL),
(72, 'Babbles with inflection (7-9 mos.)', 3, 3, 2, 'A', NULL, NULL),
(73, 'Says “dada” and “mama” for specific person (8-10mos.)', 3, 3, 3, 'A', NULL, NULL),
(74, 'Responds to “no” by briefly stopping activity and noticing adult (9-12 mos.)', 3, 3, 4, 'A', NULL, NULL),
(75, 'Responds to simple verbal requests, such as “Give me” (9-14 mos.)', 3, 3, 5, 'A', NULL, NULL),
(76, 'Makes simple gestures such as shaking head for “no” (12 mos.)', 3, 3, 6, 'A', NULL, NULL),
(77, 'Uses exclamations such as “oh-oh” (12 mos.)', 3, 3, 7, 'A', NULL, NULL),
(78, 'Finger-feeds himself (8-12 mos.)', 3, 5, 1, 'A', NULL, NULL),
(79, 'Extends arm or leg to help when being dressed (9-12 mos.)', 3, 5, 2, 'A', NULL, NULL),
(80, 'May hold spoon when feeding (9-12 mos.)', 3, 5, 3, 'A', NULL, NULL),
(81, 'Shy or anxious with strangers (8-12 mos.)', 3, 4, 1, 'A', NULL, NULL),
(82, 'Cries when mother or father leaves (8-12 mos.)', 3, 4, 1, 'A', NULL, NULL),
(83, 'Enjoys imitating people in his play (10-12 mos.)', 3, 4, 2, 'A', NULL, NULL),
(84, 'Shows specific preferences for certain people and toys (8-12 mos.)', 3, 4, 3, 'A', NULL, NULL),
(85, 'Prefers mother and/or regular care provider over all others (8-12 mos.)', 3, 4, 4, 'A', NULL, NULL),
(86, 'Repeats sounds or gestures for attention (10-12 mos.)', 3, 4, 5, 'A', NULL, NULL),
(87, 'May test parents at bed time (9-12 mos.)', 3, 4, 6, 'A', NULL, NULL),
(88, 'Walks alone (12-16 mos.)', 4, 6, 1, 'A', NULL, NULL),
(89, 'Pulls toys behind him while walking (13-16 mos.)', 4, 6, 2, 'A', NULL, NULL),
(90, 'Carries large toy or several toys while walking (12-15 mos.)', 4, 6, 3, 'A', NULL, NULL),
(91, 'Begins to run stiffly (16-18 mos.)', 4, 6, 4, 'A', NULL, NULL),
(92, 'Walks into ball (18-24 mos.)', 4, 6, 5, 'A', NULL, NULL),
(93, 'Climbs onto and down from furniture unsupported (16-24 mos.)', 4, 6, 6, 'A', NULL, NULL),
(94, 'Walks up and down stairs holding on to support (18-24 mos.)', 4, 6, 7, 'A', NULL, NULL),
(95, 'Scribbles spontaneously (14-16 mos.)', 4, 7, 1, 'A', NULL, NULL),
(96, 'Turns over container to pour out contents (12-18 mos.)', 4, 7, 2, 'A', NULL, NULL),
(97, 'Builds tower of four blocks or more (20-24 mos.)', 4, 7, 3, 'A', NULL, NULL),
(98, 'Completes simple knobbed wooden puzzles of 3 to 4 pieces (21-24 mos.)', 4, 7, 4, 'A', NULL, NULL),
(99, 'Says “no” with meaning (13-15 mos.)', 4, 3, 1, 'A', NULL, NULL),
(100, 'Follows simple, one-step instructions (14-18 mos.)', 4, 3, 2, 'A', NULL, NULL),
(101, 'Says several single words (15-18 mos.)', 4, 3, 3, 'A', NULL, NULL),
(102, 'Recognizes names of familiar people, objects, and body parts (18-24 mos.)', 4, 3, 4, 'A', NULL, NULL),
(103, 'Points to object or picture when it’s named for them (18-24 mos.)', 4, 3, 5, 'A', NULL, NULL),
(104, 'Repeats words overheard in conversations (16-18 mos.)', 4, 3, 6, 'A', NULL, NULL),
(105, 'Uses two-word sentences (18-24 mos.)', 4, 3, 7, 'A', NULL, NULL),
(106, 'Finds objects even when hidden under 2 or 3 covers (13-15 mos.)', 4, 2, 1, 'A', NULL, NULL),
(107, 'Will listen to short story book with pictures (15-20 mos.)', 4, 2, 2, 'A', NULL, NULL),
(108, 'Identifies one body part (15-24 mos.)', 4, 2, 3, 'A', NULL, NULL),
(109, 'Begins to sort shapes and colours (20-24 mos.)', 4, 2, 4, 'A', NULL, NULL),
(110, 'Begins make-believe play (20-24 mos.)', 4, 2, 5, 'A', NULL, NULL),
(111, 'Starts to feed self with spoon, with some spilling (13-18 mos.)', 4, 5, 1, 'A', NULL, NULL),
(112, 'Likes to play with food when eating (18-24 mos.)', 4, 5, 2, 'A', NULL, NULL),
(113, 'Can put shoes on with help (20-24 mos.)', 4, 5, 3, 'A', NULL, NULL),
(114, 'Can open doors by turning knobs (18-24 mos.)', 4, 5, 4, 'A', NULL, NULL),
(115, 'Can drink from open cup, with some spilling (18-24 mos.)', 4, 5, 5, 'A', NULL, NULL),
(116, 'Imitates behaviour of others, especially adults and older children (18-24 mos.)', 4, 4, 1, 'A', NULL, NULL),
(117, 'Increasingly enthusiastic about company or other children (20-24 mos.)', 4, 4, 2, 'A', NULL, NULL),
(118, 'Demonstrates increasing independence (18-24 mos.)', 4, 4, 3, 'A', NULL, NULL),
(119, 'Begins to show defiant behaviour (18-24 mos.)', 4, 4, 4, 'A', NULL, NULL),
(120, 'Episodes of separation anxiety increase toward midyear, then fade', 4, 4, 5, 'A', NULL, NULL),
(121, 'Climbs well (24-30 mos.)', 5, 6, 1, 'A', NULL, NULL),
(122, 'Walks down stairs alone, placing both feet on each step (26-28 mos.)', 5, 6, 2, 'A', NULL, NULL),
(123, 'Walks upstairs alternating feet with support (24-30 mos.)', 5, 6, 3, 'A', NULL, NULL),
(124, 'Swings leg to kick ball (24-30 mos.)', 5, 6, 4, 'A', NULL, NULL),
(125, 'Runs easily (24-26 mos.)', 5, 6, 5, 'A', NULL, NULL),
(126, 'Pedals tricycle (30-36 mos.)', 5, 6, 6, 'A', NULL, NULL),
(127, 'Bends over easily without falling (24-30 mos.)', 5, 6, 7, 'A', NULL, NULL),
(128, 'Makes vertical, horizontal, circular strokes with pencil or crayon (30-36 mos.)', 5, 7, 1, 'A', NULL, NULL),
(129, 'Turns book pages one at a time (24-30 mos.)', 5, 7, 2, 'A', NULL, NULL),
(130, 'Builds a tower of more than 6 blocks (24-30 mos.)', 5, 7, 3, 'A', NULL, NULL),
(131, 'Holds a pencil in writing position (30-36 mos.)', 5, 7, 4, 'A', NULL, NULL),
(132, 'Screws and unscrews jar lids, nuts, and bolts (24-30 mos.)', 5, 7, 5, 'A', NULL, NULL),
(133, 'Turns rotating handles (door knob) (24-30 mos.)', 5, 7, 6, 'A', NULL, NULL),
(134, 'Uses pronouns (I, you, me, we, they) (24-30 mos.)', 5, 3, 1, 'A', NULL, NULL),
(135, 'Understands most sentences (24-40 mos.)', 5, 3, 2, 'A', NULL, NULL),
(136, 'Recognizes and identifies almost all common objects and pictures (26-32 mos.)', 5, 3, 3, 'A', NULL, NULL),
(137, 'Shows frustration when not understood by others (28-36 mos.)', 5, 3, 4, 'A', NULL, NULL),
(138, 'Understands physical relationships (on, in, under) (30-36 mos.)', 5, 3, 5, 'A', NULL, NULL),
(139, 'Can say name, age, and sex (30-36 mos.)', 5, 3, 6, 'A', NULL, NULL),
(140, 'Uses words to communicate wants and needs (30-36 mos.)', 5, 3, 7, 'A', NULL, NULL),
(141, 'Knows simple rhymes and songs (30-36 mos.)', 5, 3, 8, 'A', NULL, NULL),
(142, 'Strangers can understand most of words (30-36 mos.)', 5, 3, 9, 'A', NULL, NULL),
(143, 'Makes mechanical toys work (30-36 mos.)', 5, 2, 1, 'A', NULL, NULL),
(144, 'Matches an object in hand or room to a picture in a book (24-30 mos.)', 5, 2, 2, 'A', NULL, NULL),
(145, 'Plays make-believe with dolls, animals, and people (24-36 mos.)', 5, 2, 3, 'A', NULL, NULL),
(146, 'Sorts objects by colour (30-36 mos.)', 5, 2, 4, 'A', NULL, NULL),
(147, 'Completes puzzles with 3 or 4 pieces (24-36 mos.)', 5, 2, 5, 'A', NULL, NULL),
(148, 'Understands concept of “two” (26-32 mos.)', 5, 2, 6, 'A', NULL, NULL),
(149, 'Listens to stories (24-36 mos.)', 5, 2, 7, 'A', NULL, NULL),
(150, 'Knows several body parts (24-36 mos.)', 5, 2, 8, 'A', NULL, NULL),
(151, 'Can pull pants down with help (24-36 mos.)', 5, 5, 1, 'A', NULL, NULL),
(152, 'Helps put things away (24-36 mos.)', 5, 5, 2, 'A', NULL, NULL),
(153, 'Serves self at table with some spilling (30-36 mos.)', 5, 5, 3, 'A', NULL, NULL),
(154, 'Uses the word “mine” often (24-36 mos.)', 5, 4, 1, 'A', NULL, NULL),
(155, 'Says “no” but will still do what is asked (24-36 mos.)', 5, 4, 2, 'A', NULL, NULL),
(156, 'Expresses a wide range of emotions (24-36 mos.)', 5, 4, 3, 'A', NULL, NULL),
(157, 'Objects to major changes in routine, but is becoming more compliant (24-36 mos.)', 5, 4, 4, 'A', NULL, NULL),
(158, 'Begins to follow simple rules (30-36 mos.)', 5, 4, 5, 'A', NULL, NULL),
(159, 'Begins to separates more easily from parents (by 36 mo.)', 5, 4, 6, 'A', NULL, NULL),
(160, 'Hops and stands on one foot up to 5 seconds', 6, 6, 1, 'A', NULL, NULL),
(161, 'Goes upstairs and downstairs without support', 6, 6, 2, 'A', NULL, NULL),
(162, 'Kicks ball forward', 6, 6, 3, 'A', NULL, NULL),
(163, 'Throws ball overhand', 6, 6, 4, 'A', NULL, NULL),
(164, 'Catches bounced ball most of the time', 6, 6, 5, 'A', NULL, NULL),
(165, 'Moves forward and backward', 6, 6, 6, 'A', NULL, NULL),
(166, 'Uses riding toys', 6, 6, 7, 'A', NULL, NULL),
(167, 'Copies square shapes', 6, 7, 1, 'A', NULL, NULL),
(168, 'Draws a person with 2-4 body parts', 6, 7, 2, 'A', NULL, NULL),
(169, 'Uses scissors', 6, 7, 3, 'A', NULL, NULL),
(170, 'Draws circles and squares', 6, 7, 4, 'A', NULL, NULL),
(171, 'Begins to copy some capital letters', 6, 7, 5, 'A', NULL, NULL),
(172, 'Understands the concepts of "same" and "different"', 6, 3, 1, 'A', NULL, NULL),
(173, 'Has mastered some basic rules of grammar', 6, 3, 2, 'A', NULL, NULL),
(174, 'Speaks in sentences of 5-6 words', 6, 3, 3, 'A', NULL, NULL),
(175, 'Asks questions', 6, 3, 4, 'A', NULL, NULL),
(176, 'Speaks clearly enough for strangers to understand', 6, 3, 5, 'A', NULL, NULL),
(177, 'Tells stories', 6, 3, 6, 'A', NULL, NULL),
(178, 'Correctly names some colours', 6, 2, 1, 'A', NULL, NULL),
(179, 'Understands the concept of counting and may know a few numbers', 6, 2, 2, 'A', NULL, NULL),
(180, 'Begins to have a clearer sense of time', 6, 2, 3, 'A', NULL, NULL),
(181, 'Follows three-part commands', 6, 2, 4, 'A', NULL, NULL),
(182, 'Recalls parts of a story', 6, 2, 5, 'A', NULL, NULL),
(183, 'Understands the concept of same/different', 6, 2, 6, 'A', NULL, NULL),
(184, 'Engages in fantasy play', 6, 2, 7, 'A', NULL, NULL),
(185, 'Understands causality ("I can make things happen")', 6, 2, 8, 'A', NULL, NULL),
(186, 'Can feed self with spoon without spilling', 6, 5, 1, 'A', NULL, NULL),
(187, 'Washes and dries hands and face', 6, 5, 2, 'A', NULL, NULL),
(188, 'Can do simple household tasks (help set the table)', 6, 5, 3, 'A', NULL, NULL),
(189, 'Can put on simple clothing items, with help for button, zipper, shoelace (jacket, pants, shoes)', 6, 5, 4, 'A', NULL, NULL),
(190, 'Can run a brush or comb through own hair', 6, 5, 5, 'A', NULL, NULL),
(191, 'Interested in new experiences', 6, 4, 1, 'A', NULL, NULL),
(192, 'Cooperates/plays with other children', 6, 4, 2, 'A', NULL, NULL),
(193, 'Plays "mom "or "dad"', 6, 4, 3, 'A', NULL, NULL),
(194, 'More inventive in fantasy play', 6, 4, 4, 'A', NULL, NULL),
(195, 'Can stay on topic during conversations', 6, 4, 5, 'A', NULL, NULL),
(196, 'More independent', 6, 4, 6, 'A', NULL, NULL),
(197, 'Plays simple games with simple rules', 6, 4, 7, 'A', NULL, NULL),
(198, 'Begins to share toys with other children', 6, 4, 8, 'A', NULL, NULL),
(199, 'Often cannot distinguish between fantasy and reality', 6, 4, 9, 'A', NULL, NULL),
(200, 'May have imaginary friends or see monsters', 6, 4, 10, 'A', NULL, NULL),
(201, 'Stands on one foot for 10 seconds or longer', 7, 6, 1, 'A', NULL, NULL),
(202, 'Hops, somersaults', 7, 6, 2, 'A', NULL, NULL),
(203, 'Swings, climbs', 7, 6, 3, 'A', NULL, NULL),
(204, 'May be able to skip', 7, 6, 4, 'A', NULL, NULL),
(205, 'Copies triangle and other geometric patterns', 7, 7, 1, 'A', NULL, NULL),
(206, 'Draws person with body', 7, 7, 2, 'A', NULL, NULL),
(207, 'Prints some letters', 7, 7, 3, 'A', NULL, NULL),
(208, 'Dresses and undresses without assistance', 7, 7, 4, 'A', NULL, NULL),
(209, 'Recalls parts of a story', 7, 3, 1, 'A', NULL, NULL),
(210, 'Speaks sentences of more than 5 words', 7, 3, 2, 'A', NULL, NULL),
(211, 'Uses future tense', 7, 3, 3, 'A', NULL, NULL),
(212, 'Tells longer stories', 7, 3, 4, 'A', NULL, NULL),
(213, 'Says name and address', 7, 3, 5, 'A', NULL, NULL),
(214, 'Can count 10 or more objects', 7, 2, 1, 'A', NULL, NULL),
(215, 'Correctly names at least 4 colours', 7, 2, 2, 'A', NULL, NULL),
(216, 'Works in small groups for 5-10 minutes', 7, 2, 3, 'A', NULL, NULL),
(217, 'Better understands the concept of time', 7, 2, 4, 'A', NULL, NULL),
(218, 'Knows about things used every day in the home (money, food, etc.)', 7, 2, 5, 'A', NULL, NULL),
(219, 'Uses fork, spoon independently', 7, 5, 1, 'A', NULL, NULL),
(220, 'Can chew with lips closed', 7, 5, 2, 'A', NULL, NULL),
(221, 'Goes to the bathroom independently, with reminders', 7, 5, 3, 'A', NULL, NULL),
(222, 'Undresses independently, may be able to unbutton and unzip', 7, 5, 4, 'A', NULL, NULL),
(223, 'Wants to please', 7, 4, 1, 'A', NULL, NULL),
(224, 'Prefers to be with friends', 7, 4, 2, 'A', NULL, NULL),
(225, 'More likely to agree to rules', 7, 4, 3, 'A', NULL, NULL),
(226, 'Likes to sing, dance, and act', 7, 4, 4, 'A', NULL, NULL),
(227, 'Shows more independence', 7, 4, 5, 'A', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `checklist_red_flags`
--

CREATE TABLE `checklist_red_flags` (
  `red_flag_id` int(11) UNSIGNED NOT NULL,
  `red_flag_description` varchar(255) NOT NULL,
  `red_flag_month_min` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `red_flag_month_max` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `red_flag_sort` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `checklist_red_flags`
--

INSERT INTO `checklist_red_flags` (`red_flag_id`, `red_flag_description`, `red_flag_month_min`, `red_flag_month_max`, `red_flag_sort`) VALUES
(1, 'Doesn’t seem to respond to loud noises', 0, 3, 1),
(2, 'Doesn’t follow moving objects with eyes by 2 to 3 months', 0, 3, 2),
(3, 'Doesn’t smile at the sound of your voice by 2 months', 0, 3, 3),
(4, 'Doesn’t grasp and hold objects by 3 months', 0, 3, 4),
(5, 'Doesn’t smile at people by 3 months', 0, 3, 5),
(6, 'Cannot support head well at 3 months', 0, 3, 6),
(7, 'Doesn’t reach for and grasp toys by 3 to 4 months', 0, 3, 7),
(8, 'Doesn’t bring objects to mouth by 4 months', 0, 3, 8),
(9, 'Doesn’t push down with legs when feet are placed on a firm surface by 4 months', 0, 3, 9),
(10, 'Has trouble moving one or both eyes in all directions', 0, 3, 10),
(11, 'Crosses eyes most of the time (occasional crossing of the eyes is normal in these first months)\r\n', 0, 3, 11),
(12, 'Seems very stiff, tight muscles', 4, 7, 1),
(13, 'Seems very floppy, like a rag doll', 4, 7, 2),
(14, 'Head still flops back when body is pulled to sitting position (by 5months still exhibits head lag)', 4, 7, 3),
(15, 'Shows no affection for the person who cares for them', 4, 7, 4),
(16, 'Doesn’t seem to enjoy being around people', 4, 7, 5),
(17, 'One or both eyes consistently turn in or out', 4, 7, 6),
(18, 'Persistent tearing, eye drainage, or sensitivity to light', 4, 7, 7),
(19, 'Does not respond to sounds around them', 4, 7, 8),
(20, 'Has difficulty getting objects to mouth', 4, 7, 9),
(21, 'Does not turn head to locate sounds by 4 months', 4, 7, 10),
(22, 'Doesn’t roll over (stomach to back) by 6 months', 4, 7, 11),
(23, 'Cannot sit with help by 6 months (not by themselves)', 4, 7, 12),
(24, 'Does not laugh or make squealing sounds by 5 months', 4, 7, 13),
(25, 'Does not actively reach for objects by 6 months', 4, 7, 14),
(26, 'Does not follow objects with both eyes', 4, 7, 15),
(27, 'Does not bear some weight on legs by 5 months', 4, 7, 16),
(28, 'Has difficulty calming self, cries for long periods of time', 4, 7, 17),
(29, 'Does not crawl', 8, 12, 1),
(30, 'Drags one side of body while crawling (for over one month)', 8, 12, 2),
(31, 'Cannot stand when supported', 8, 12, 3),
(32, 'Does not search for objects that are hidden (10-12 mos.)', 8, 12, 4),
(33, 'Says no single words (“mama” or “dada”)', 8, 12, 5),
(34, 'Does not learn to use gestures such as waving or shaking head', 8, 12, 6),
(35, 'Does not sit steadily by 10 months', 8, 12, 7),
(36, 'Does not react to new environments and people', 8, 12, 8),
(37, 'Does not seek out caregiver when stressed', 8, 12, 9),
(38, 'Does not show interest in “peek-a-boo" or "patty cake” by 8 months', 8, 12, 10),
(39, 'Does not babble by 8 mos. (“dada,” “baba,” “mama”)', 8, 12, 11),
(40, 'Cannot walk by 18 months', 13, 24, 1),
(41, 'Fails to develop a mature heel-toe walking pattern after several months of walking, or walks exclusively on toes', 13, 24, 2),
(42, 'Does not speak at least 15 words by 18 months', 13, 24, 3),
(43, 'Does not use unique two-word phrases by age 2 (more milk, big dog, mommy help)', 13, 24, 4),
(44, 'By 15 months does not seem to know the function of common household objects (brush, telephone, cup, fork,\r\nspoon)', 13, 24, 5),
(45, 'Does not imitate actions or words by 24 months', 13, 24, 6),
(46, 'Does not follow simple one-step instructions by 24 months', 13, 24, 7),
(47, 'Cannot identify self', 13, 24, 8),
(48, 'Cannot form a two-word phrase', 13, 24, 9),
(49, 'Cannot hold and use a spoon or cup for eating and drinking', 13, 24, 10),
(50, 'Does not display a wide array of emotions (anger, fear, happy, excited, frustrated)', 13, 24, 11),
(51, 'Frequent falling and difficulty with stairs', 25, 36, 1),
(52, 'Persistent drooling or very unclear speech', 25, 36, 2),
(53, 'Inability to build a tower of more than 4 blocks', 25, 36, 3),
(54, 'Difficulty manipulating small objects', 25, 36, 4),
(55, 'Inability to copy a circle by 3 years old', 25, 36, 5),
(56, 'Inability to communicate in short phrases', 25, 36, 6),
(57, 'No involvement in pretend play', 25, 36, 7),
(58, 'Cannot feed self with spoon or drink from cup independently', 25, 36, 8),
(59, 'Failure to understand simple instructions', 25, 36, 9),
(60, 'Little interest in other children', 25, 36, 10),
(61, 'Extreme difficulty separating from primary caregiver', 25, 36, 11),
(62, 'Cannot jump in place', 37, 48, 1),
(63, 'Cannot ride a trike', 37, 48, 2),
(64, 'Cannot grasp a crayon between thumb and fingers', 37, 48, 3),
(65, 'Has difficulty scribbling', 37, 48, 4),
(66, 'Cannot copy a circle', 37, 48, 5),
(67, 'Cannot stack 4 blocks', 37, 48, 6),
(68, 'Still clings or cries when parents leave him', 37, 48, 7),
(69, 'Shows no interest in interactive games', 37, 48, 8),
(70, 'Ignores other children', 37, 48, 9),
(71, 'Doesn\'t respond to people outside the family', 37, 48, 10),
(72, 'Doesn\'t engage in fantasy play', 37, 48, 11),
(73, 'Resists dressing, sleeping, using the toilet', 37, 48, 12),
(74, 'Lashes out without any self-control when angry or upset', 37, 48, 13),
(75, 'Doesn\'t use sentences of more than three words', 37, 48, 14),
(76, 'Doesn\'t use "me" or "you" appropriately', 37, 48, 15),
(77, 'Exhibits extremely aggressive, fearful or timid behaviour', 49, 60, 1),
(78, 'Is unable to separate from parents', 49, 60, 2),
(79, 'Is easily distracted and unable to concentrate on any single activity for more than 5 minutes', 49, 60, 3),
(80, 'Shows little interest in playing with other children', 49, 60, 4),
(81, 'Refuses to respond to people in general', 49, 60, 5),
(82, 'Rarely uses fantasy or imitation in play', 49, 60, 6),
(83, 'Seems unhappy or sad much of the time', 49, 60, 7),
(84, 'Avoids or seems aloof with other children and adults', 49, 60, 8),
(85, 'Does not express a wide range of emotions', 49, 60, 9),
(86, 'Has trouble eating, sleeping or using the toilet', 49, 60, 10),
(87, 'Cannot differentiate between fantasy and reality', 49, 60, 11),
(88, 'Seems unusually passive', 49, 60, 12),
(89, 'Cannot understand prepositions ("put the cup on the table"; "get the ball under the couch")', 49, 60, 13),
(90, 'Cannot follow 2-part commands (“pick up the toy and put it on the shelf”)', 49, 60, 14),
(91, 'Cannot give his first and last name', 49, 60, 15),
(92, 'Does not use plurals or past tense', 49, 60, 16),
(93, 'Cannot build a tower of 6 to 8 blocks', 49, 60, 17),
(94, 'Holds crayon with fisted grasp', 49, 60, 18),
(95, 'Has trouble taking off clothing', 49, 60, 19),
(96, 'Unable to brush teeth or wash and dry hands', 49, 60, 20);

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `child_id` int(11) UNSIGNED NOT NULL,
  `school_id` int(11) UNSIGNED NOT NULL,
  `room_id` int(11) UNSIGNED NOT NULL,
  `enrollment_id` int(11) UNSIGNED DEFAULT NULL,
  `child_name` varchar(64) NOT NULL,
  `child_avatar_url` varchar(255) DEFAULT NULL,
  `child_gender` enum('M','F') DEFAULT NULL,
  `child_birthday` date DEFAULT NULL,
  `child_street` varchar(64) DEFAULT NULL,
  `child_city` varchar(32) DEFAULT NULL,
  `child_postal_code` varchar(32) DEFAULT NULL,
  `child_phone` varchar(32) DEFAULT NULL,
  `child_notes` varchar(255) DEFAULT NULL,
  `child_status` enum('A','D') NOT NULL DEFAULT 'A',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `child_record`
--

CREATE TABLE `child_record` (
  `child_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `child_user`
--

CREATE TABLE `child_user` (
  `child_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `token_id` varchar(16) NOT NULL,
  `status` enum('P','A','D') NOT NULL DEFAULT 'P'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `country_id` varchar(2) NOT NULL,
  `country_name` varchar(255) NOT NULL,
  `country_available` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`country_id`, `country_name`, `country_available`) VALUES
('AD', ' Andorra', 0),
('AE', ' United Arab Emirates', 0),
('AF', ' Afghanistan', 0),
('AG', ' Antigua and Barbuda', 0),
('AI', ' Anguilla', 0),
('AL', ' Albania', 0),
('AM', ' Armenia', 0),
('AO', ' Angola', 0),
('AQ', ' Antarctica', 0),
('AR', ' Argentina', 0),
('AS', ' American Samoa', 0),
('AT', ' Austria', 0),
('AU', ' Australia', 1),
('AW', ' Aruba', 0),
('AZ', ' Azerbaijan', 0),
('BA', ' Bosnia and Herzegovina', 0),
('BB', ' Barbados', 0),
('BD', ' Bangladesh', 0),
('BE', ' Belgium', 0),
('BF', ' Burkina Faso', 0),
('BG', ' Bulgaria', 0),
('BH', ' Bahrain', 0),
('BI', ' Burundi', 0),
('BJ', ' Benin', 0),
('BL', ' Saint Barthelemy', 0),
('BM', ' Bermuda', 0),
('BN', ' Brunei', 0),
('BO', ' Bolivia', 0),
('BR', ' Brazil', 0),
('BS', ' Bahamas', 0),
('BT', ' Bhutan', 0),
('BV', ' Bouvet Island', 0),
('BW', ' Botswana', 0),
('BY', ' Belarus', 0),
('BZ', ' Belize', 0),
('CA', ' Canada', 0),
('CC', ' Cocos (Keeling) Islands', 0),
('CD', ' Democratic Republic of Congo', 0),
('CF', ' Central African Republic', 0),
('CG', ' Republic of Congo', 0),
('CH', ' Switzerland', 0),
('CI', ' Cote d\'Ivoire', 0),
('CK', ' Cook Islands', 0),
('CL', ' Chile', 0),
('CM', ' Cameroon', 0),
('CN', ' China', 0),
('CO', ' Colombia', 0),
('CR', ' Costa Rica', 0),
('CU', ' Cuba', 0),
('CV', ' Cape Verde', 0),
('CW', ' Curacao', 0),
('CX', ' Christmas Island', 0),
('CY', ' Cyprus', 0),
('CZ', ' Czech Republic', 0),
('DE', ' Germany', 0),
('DJ', ' Djibouti', 0),
('DK', ' Denmark', 0),
('DM', ' Dominica', 0),
('DO', ' Dominican Republic', 0),
('DZ', ' Algeria', 0),
('EC', ' Ecuador', 0),
('EE', ' Estonia', 0),
('EG', ' Egypt', 0),
('EH', ' Western Sahara', 0),
('ER', ' Eritrea', 0),
('ES', ' Spain', 0),
('ET', ' Ethiopia', 0),
('FI', ' Finland', 0),
('FJ', ' Fiji', 0),
('FK', ' Falkland Islands (Islas Malvinas)', 0),
('FM', ' Micronesia', 0),
('FO', ' Faroe Islands', 0),
('FR', ' France', 0),
('FX', ' France (Metropolitan)', 0),
('GA', ' Gabon', 0),
('GB', ' United Kingdom', 1),
('GD', ' Grenada', 0),
('GE', ' Georgia', 0),
('GF', ' French Guiana', 0),
('GG', ' Guernsey', 0),
('GH', ' Ghana', 0),
('GI', ' Gibraltar', 0),
('GL', ' Greenland', 0),
('GM', ' Gambia', 0),
('GN', ' Guinea', 0),
('GP', ' Guadeloupe', 0),
('GQ', ' Equatorial Guinea', 0),
('GR', ' Greece', 0),
('GS', ' South Georgia and the Islands', 0),
('GT', ' Guatemala', 0),
('GU', ' Guam', 0),
('GW', ' Guinea-Bissau', 0),
('GY', ' Guyana', 0),
('HK', ' Hong Kong', 0),
('HM', ' Heard Island and McDonald Islands', 0),
('HN', ' Honduras', 0),
('HR', ' Croatia', 0),
('HT', ' Haiti', 0),
('HU', ' Hungary', 0),
('ID', ' Indonesia', 0),
('IE', ' Ireland', 1),
('IL', ' Israel', 0),
('IM', ' Isle of Man', 0),
('IN', ' India', 0),
('IO', ' British Indian Ocean Territory', 0),
('IQ', ' Iraq', 0),
('IR', ' Iran', 0),
('IS', ' Iceland', 0),
('IT', ' Italy', 0),
('JE', ' Jersey', 0),
('JM', ' Jamaica', 0),
('JO', ' Jordan', 0),
('JP', ' Japan', 0),
('KE', ' Kenya', 0),
('KG', ' Kyrgyzstan', 0),
('KH', ' Cambodia', 0),
('KI', ' Kiribati', 0),
('KM', ' Comoros', 0),
('KN', ' Saint Kitts and Nevis', 0),
('KP', ' North Korea', 0),
('KR', ' South South', 0),
('KW', ' Kuwait', 0),
('KY', ' Cayman Islands', 0),
('KZ', ' Kazakhstan', 0),
('LA', ' Laos', 0),
('LB', ' Lebanon', 0),
('LC', ' Saint Lucia', 0),
('LI', ' Liechtenstein', 0),
('LK', ' Sri Lanka', 0),
('LR', ' Liberia', 0),
('LS', ' Lesotho', 0),
('LT', ' Lithuania', 0),
('LU', ' Luxembourg', 0),
('LV', ' Latvia', 0),
('LY', ' Libya', 0),
('MA', ' Morocco', 0),
('MC', ' Monaco', 0),
('MD', ' Moldova', 0),
('ME', ' Montenegro', 0),
('MF', ' Saint Martin', 0),
('MG', ' Madagascar', 0),
('MH', ' Marshall Islands', 0),
('MK', ' Macedonia', 0),
('ML', ' Mali', 0),
('MM', ' Burma', 0),
('MN', ' Mongolia', 0),
('MO', ' Macau', 0),
('MP', ' Northern Mariana Islands', 0),
('MQ', ' Martinique', 0),
('MR', ' Mauritania', 0),
('MS', ' Montserrat', 0),
('MT', ' Malta', 0),
('MU', ' Mauritius', 0),
('MV', ' Maldives', 0),
('MW', ' Malawi', 0),
('MX', ' Mexico', 0),
('MY', ' Malaysia', 0),
('MZ', ' Mozambique', 0),
('NA', ' Namibia', 0),
('NC', ' New Caledonia', 0),
('NE', ' Niger', 0),
('NF', ' Norfolk Island', 0),
('NG', ' Nigeria', 0),
('NI', ' Nicaragua', 0),
('NL', ' Netherlands', 0),
('NO', ' Norway', 0),
('NP', ' Nepal', 0),
('NR', ' Nauru', 0),
('NU', ' Niue', 0),
('NZ', ' New Zealand', 0),
('OM', ' Oman', 0),
('PA', ' Panama', 0),
('PE', ' Peru', 0),
('PF', ' French Polynesia', 0),
('PG', ' Papua New Guinea', 0),
('PH', ' Philippines', 0),
('PK', ' Pakistan', 0),
('PL', ' Poland', 0),
('PM', ' Saint Pierre and Miquelon', 0),
('PN', ' Pitcairn Islands', 0),
('PR', ' Puerto Rico', 0),
('PS', ' Palestine', 0),
('PT', ' Portugal', 0),
('PW', ' Palau', 0),
('PY', ' Paraguay', 0),
('QA', ' Qatar', 0),
('RE', ' Reunion', 0),
('RO', ' Romania', 0),
('RS', ' Serbia', 0),
('RU', ' Russia', 0),
('RW', ' Rwanda', 0),
('SA', ' Saudi Arabia', 0),
('SB', ' Solomon Islands', 0),
('SC', ' Seychelles', 0),
('SD', ' Sudan', 0),
('SE', ' Sweden', 0),
('SG', ' Singapore', 0),
('SH', ' Saint Helena', 0),
('SI', ' Slovenia', 0),
('SJ', ' Svalbard', 0),
('SK', ' Slovakia', 0),
('SL', ' Sierra Leone', 0),
('SM', ' San Marino', 0),
('SN', ' Senegal', 0),
('SO', ' Somalia', 0),
('SR', ' Suriname', 0),
('SS', ' South Sudan', 0),
('ST', ' Sao Tome and Principe', 0),
('SV', ' El Salvador', 0),
('SX', ' Sint Maarten', 0),
('SY', ' Syria', 0),
('SZ', ' Swaziland', 0),
('TC', ' Turks and Caicos Islands', 0),
('TD', ' Chad', 0),
('TF', ' French Southern and Antarctic Lands', 0),
('TG', ' Togo', 0),
('TH', ' Thailand', 0),
('TJ', ' Tajikistan', 0),
('TK', ' Tokelau', 0),
('TL', ' Timor-Leste', 0),
('TM', ' Turkmenistan', 0),
('TN', ' Tunisia', 0),
('TO', ' Tonga', 0),
('TR', ' Turkey', 0),
('TT', ' Trinidad and Tobago', 0),
('TV', ' Tuvalu', 0),
('TW', ' Taiwan', 0),
('TZ', ' Tanzania', 0),
('UA', ' Ukraine', 0),
('UG', ' Uganda', 0),
('UM', ' United States Minor Outlying Islands', 0),
('US', ' United States', 0),
('UY', ' Uruguay', 0),
('UZ', ' Uzbekistan', 0),
('VA', ' Holy See (Vatican City)', 0),
('VC', ' Saint Vincent and the Grenadines', 0),
('VE', ' Venezuela', 0),
('VG', ' British Virgin Islands', 0),
('VI', ' Virgin Islands', 0),
('VN', ' Vietnam', 0),
('VU', ' Vanuatu', 0),
('WF', ' Wallis and Futuna', 0),
('WS', ' Samoa', 0),
('XK', ' Kosovo', 0),
('YE', ' Yemen', 0),
('YT', ' Mayotte', 0),
('ZA', ' South Africa', 0),
('ZM', ' Zambia', 0),
('ZW', 'Zimbabwe', 0);

-- --------------------------------------------------------

--
-- Table structure for table `daily_plans`
--

CREATE TABLE `daily_plans` (
  `daily_plan_id` int(11) NOT NULL,
  `school_fk` int(11) NOT NULL,
  `plan_public` tinyint(1) DEFAULT NULL,
  `date` date NOT NULL,
  `name` varchar(150) NOT NULL,
  `assoc` enum('school','room','child') NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `plan_img_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `daily_plan_assoc`
--

CREATE TABLE `daily_plan_assoc` (
  `daily_plan_assoc_id` int(11) NOT NULL,
  `daily_plan_fk` int(11) NOT NULL,
  `assoc_type` enum('school','room','child','') NOT NULL,
  `assoc_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `daily_plan_blocks`
--

CREATE TABLE `daily_plan_blocks` (
  `daily_plan_block_id` int(11) NOT NULL,
  `daily_plan_fk` int(11) NOT NULL,
  `time_block` varchar(15) NOT NULL,
  `description` varchar(100) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `mother_info` text COLLATE utf8mb4_unicode_ci,
  `father_info` text COLLATE utf8mb4_unicode_ci,
  `guardian_info` text COLLATE utf8mb4_unicode_ci,
  `other_person_name` text COLLATE utf8mb4_unicode_ci,
  `other_person_address` text COLLATE utf8mb4_unicode_ci,
  `other_person_relationship` text COLLATE utf8mb4_unicode_ci,
  `other_person_telephone` text COLLATE utf8mb4_unicode_ci,
  `collector_name` text COLLATE utf8mb4_unicode_ci,
  `collector_address` text COLLATE utf8mb4_unicode_ci,
  `collector_relationship` text COLLATE utf8mb4_unicode_ci,
  `collector_telephone` text COLLATE utf8mb4_unicode_ci,
  `med_and_health` text COLLATE utf8mb4_unicode_ci,
  `con_to_en` text COLLATE utf8mb4_unicode_ci,
  `con_to_en_doc` text COLLATE utf8mb4_unicode_ci,
  `getting_to_know1` text COLLATE utf8mb4_unicode_ci,
  `getting_to_know2` text COLLATE utf8mb4_unicode_ci,
  `getting_to_know3` text COLLATE utf8mb4_unicode_ci,
  `getting_to_know4` text COLLATE utf8mb4_unicode_ci,
  `required_files` text COLLATE utf8mb4_unicode_ci,
  `custom_fields` text COLLATE utf8mb4_unicode_ci,
  `confirmed` int(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form`
--

CREATE TABLE `form` (
  `form_id` int(11) NOT NULL,
  `form_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `form_blocks`
--

CREATE TABLE `form_blocks` (
  `form_blocks_id` int(11) NOT NULL,
  `form_fk` int(11) NOT NULL,
  `form_country_fk` int(11) NOT NULL,
  `label` varchar(300) NOT NULL,
  `description` varchar(300) DEFAULT NULL,
  `associated_table` varchar(100) NOT NULL,
  `associated_table_column` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `form_country`
--

CREATE TABLE `form_country` (
  `form_country_id` int(11) NOT NULL,
  `form_fk` int(11) NOT NULL,
  `country_fk` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `frameworks`
--

CREATE TABLE `frameworks` (
  `framework_id` int(11) UNSIGNED NOT NULL,
  `framework_name` varchar(64) NOT NULL,
  `country_id` varchar(2) NOT NULL,
  `framework_month_min` tinyint(3) UNSIGNED DEFAULT NULL,
  `framework_month_max` tinyint(3) UNSIGNED DEFAULT NULL,
  `school_id` int(10) DEFAULT NULL,
  `framework_status` enum('A','D') NOT NULL,
  `country_subdivision_id` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `frameworks`
--

INSERT INTO `frameworks` (`framework_id`, `framework_name`, `country_id`, `framework_month_min`, `framework_month_max`, `school_id`, `framework_status`, `country_subdivision_id`) VALUES
(1, 'Aistear', 'IE', NULL, NULL, NULL, 'A', NULL),
(2, 'Síolta', 'IE', NULL, NULL, NULL, 'A', NULL),
(3, 'EYFS', 'GB', 0, 11, NULL, 'A', NULL),
(4, 'EYFS', 'GB', 8, 20, NULL, 'A', NULL),
(5, 'EYFS', 'GB', 16, 26, NULL, 'A', NULL),
(6, 'EYFS', 'GB', 22, 36, NULL, 'A', NULL),
(7, 'EYFS', 'GB', 30, 50, NULL, 'A', NULL),
(8, 'EYFS', 'GB', 40, 60, NULL, 'A', NULL),
(9, 'EYFL', 'AU', 0, 60, NULL, 'A', NULL),
(10, 'Montessori', 'IE', NULL, NULL, NULL, 'A', NULL),
(11, 'Montessori', 'AU', NULL, NULL, NULL, 'A', NULL),
(12, 'Montessori', 'GB', NULL, NULL, NULL, 'A', NULL),
(13, 'EYFS', 'AE', 0, 11, NULL, 'A', NULL),
(14, 'EYFS', 'AE', 8, 20, NULL, 'A', NULL),
(15, 'EYFS', 'AE', 16, 26, NULL, 'A', NULL),
(16, 'EYFS', 'AE', 22, 36, NULL, 'A', NULL),
(17, 'EYFS', 'AE', 30, 50, NULL, 'A', NULL),
(18, 'EYFS', 'AE', 40, 60, NULL, 'A', NULL),
(19, 'Montessori', 'AE', NULL, NULL, NULL, 'A', NULL),
(20, 'Creative Curriculum', 'US', NULL, NULL, NULL, 'A', NULL),
(21, 'Montessori', 'US', NULL, NULL, NULL, 'A', NULL),
(22, 'Te Whāriki', 'NZ', NULL, NULL, NULL, 'A', NULL),
(23, 'Virginia’s Foundation Blocks for Early Learning', 'US', NULL, NULL, 0, 'A', 'US-VA'),
(24, 'Alabama Developmental Standards for Preschool Children', 'US', NULL, NULL, 0, 'A', 'US-AL'),
(25, 'EYFS', 'CZ', 0, 11, 0, 'A', NULL),
(26, 'EYFS', 'CZ', 8, 20, 0, 'A', NULL),
(27, 'EYFS', 'CZ', 16, 26, 0, 'A', NULL),
(28, 'EYFS', 'CZ', 22, 36, 0, 'A', NULL),
(29, 'EYFS', 'CZ', 30, 50, 0, 'A', NULL),
(30, 'EYFS', 'CZ', 40, 60, 0, 'A', NULL),
(31, 'Early Learning & Development Standards', 'US', NULL, NULL, 0, 'A', 'US-CT');

-- --------------------------------------------------------

--
-- Table structure for table `framework_categories`
--

CREATE TABLE `framework_categories` (
  `category_id` int(11) UNSIGNED NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_description` varchar(512) DEFAULT NULL,
  `category_group` varchar(255) NOT NULL,
  `framework_id` int(11) UNSIGNED NOT NULL,
  `category_sort` int(11) UNSIGNED DEFAULT '0',
  `category_status` enum('A','D') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `framework_categories`
--

INSERT INTO `framework_categories` (`category_id`, `category_name`, `category_description`, `category_group`, `framework_id`, `category_sort`, `category_status`) VALUES
(1, 'Aim 1', 'Children will be strong psychologically and socially.', 'Well-being', 1, 1, 'A'),
(2, 'Aim 2', 'Children will be as healthy and fit as they can be.', 'Well-being', 1, 2, 'A'),
(3, 'Aim 3', 'Children will be creative and spiritual.', 'Well-being', 1, 3, 'A'),
(4, 'Aim 4', 'Children will have positive outlooks on learning and on life.', 'Well-being', 1, 4, 'A'),
(5, 'Aim 1', 'Children will have strong self-identities and will feel respected and affirmed as unique individuals with their own life stories.', 'Identity and Belonging', 1, 1, 'A'),
(6, 'Aim 2', 'Children will have a sense of group identity where links with their family and community are acknowledged and extended.', 'Identity and Belonging', 1, 2, 'A'),
(7, 'Aim 3', 'Children will be able to express their rights and show an understanding and regard for the identity, rights and views of others.', 'Identity and Belonging', 1, 3, 'A'),
(8, 'Aim 4', 'Children will see themselves as capable learners.', 'Identity and Belonging', 1, 4, 'A'),
(9, 'Aim 1', 'Children will use non-verbal communication skills.', 'Communicating', 1, 1, 'A'),
(10, 'Aim 2', 'Children will use language.', 'Communicating', 1, 2, 'A'),
(11, 'Aim 3', 'Children will broaden their understanding of the world by making sense of experiences through language.', 'Communicating', 1, 3, 'A'),
(12, 'Aim 4', 'Children will express themselves creatively and imaginatively.', 'Communicating', 1, 4, 'A'),
(13, 'Aim 1', 'Children will learn about and make sense of the world around them.', 'Exploring and Thinking', 1, 1, 'A'),
(14, 'Aim 2', 'Children will develop and use skills and strategies for observing, questioning, investigating, understanding, negotiating, and problem-solving, and come to see themselves as explorers and thinkers.', 'Exploring and Thinking', 1, 2, 'A'),
(15, 'Aim 3', 'Children will explore ways to represent ideas, feelings, thoughts, objects, and actions through symbols.', 'Exploring and Thinking', 1, 3, 'A'),
(16, 'Aim 4', 'Children will have positive attitudes towards learning and develop dispositions like curiosity, playfulness, perseverance, confidence, resourcefulness, and risk-taking.', 'Exploring and Thinking', 1, 4, 'A'),
(17, 'Standard 1', 'Rights of the child', 'The National Quality Framework', 2, 1, 'A'),
(18, 'Standard 2', 'Environments', 'The National Quality Framework', 2, 2, 'A'),
(19, 'Standard 3', 'Parents and Families', 'The National Quality Framework', 2, 3, 'A'),
(20, 'Standard 4', 'Consultation', 'The National Quality Framework', 2, 4, 'A'),
(21, 'Standard 5', 'Interactions', 'The National Quality Framework', 2, 5, 'A'),
(22, 'Standard 6', 'Play', 'The National Quality Framework', 2, 6, 'A'),
(23, 'Standard 7', 'Curriculum', 'The National Quality Framework', 2, 7, 'A'),
(24, 'Standard 8', 'Planning and Evaluation', 'The National Quality Framework', 2, 8, 'A'),
(25, 'Standard 9', 'Health and Welfare', 'The National Quality Framework', 2, 9, 'A'),
(26, 'Standard 10', 'Organisation', 'The National Quality Framework', 2, 10, 'A'),
(27, 'Standard 11', 'Professional Practice', 'The National Quality Framework', 2, 11, 'A'),
(28, 'Standard 12', 'Communication', 'The National Quality Framework', 2, 12, 'A'),
(29, 'Standard 13', 'Transitions', 'The National Quality Framework', 2, 13, 'A'),
(30, 'Standard 14', 'Identity and Belonging', 'The National Quality Framework', 2, 14, 'A'),
(31, 'Standard 15', 'Legislation and Regulation', 'The National Quality Framework', 2, 15, 'A'),
(32, 'Standard 16', 'Community Involvement', 'The National Quality Framework', 2, 16, 'A'),
(33, 'Making relationships', '', 'Personal, Social and Emotional Development', 3, 1, 'A'),
(34, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 3, 2, 'A'),
(35, 'Listening and attention', '', 'Personal, Social and Emotional Development', 3, 3, 'A'),
(36, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 3, 4, 'A'),
(37, 'Understanding', '', 'Communication and Language', 3, 1, 'A'),
(38, 'Speaking', '', 'Communication and Language', 3, 2, 'A'),
(39, 'Listening and attention', '', 'Communication and Language', 3, 3, 'A'),
(40, 'Moving and Handling', '', 'Physical Development', 3, 1, 'A'),
(41, 'Health and self-care', '', 'Physical Development', 3, 2, 'A'),
(42, 'Reading', '', 'Literacy', 3, 1, 'A'),
(43, 'Numbers', '', 'Mathematics', 3, 1, 'A'),
(44, 'Shape, space and measure', '', 'Mathematics', 3, 2, 'A'),
(45, 'People and communities', '', 'Understanding the World', 3, 1, 'A'),
(46, 'The world', '', 'Understanding the World', 3, 2, 'A'),
(47, 'Technology', '', 'Understanding the World', 3, 3, 'A'),
(48, 'Exploring and using media and materials', 'Link to Playing and Exploring, Physical Development, Understanding the World to link to the curriculum framework and childrenâ€™s learning opportunities to demonstrate this sub theme instead.', 'Expressive Arts and Design', 3, 1, 'A'),
(49, 'Being imaginative', 'Babies and toddlers need to explore the world and develop a range of ways to communicate before they can express their own ideas through arts and design. Link to Communication and Language; Physical Development; Personal, Social and Emotional Development themes to demonstrate this.', 'Expressive Arts and Design', 3, 2, 'A'),
(50, 'Making relationships', '', 'Personal, Social and Emotional Development', 4, 1, 'A'),
(51, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 4, 2, 'A'),
(52, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 4, 3, 'A'),
(53, 'Listening and attention', '', 'Communication and Language', 4, 1, 'A'),
(54, 'Understanding', '', 'Communication and Language', 4, 2, 'A'),
(55, 'Speaking', '', 'Communication and Language', 4, 3, 'A'),
(56, 'Moving and Handling', '', 'Communication and Language', 4, 4, 'A'),
(57, 'Health and self-care', '', 'Communication and Language', 4, 5, 'A'),
(58, 'Reading', '', 'Communication and Language', 4, 6, 'A'),
(59, 'Numbers', '', 'Communication and Language', 4, 7, 'A'),
(60, 'Shape, space and measure', '', 'Communication and Language', 4, 8, 'A'),
(61, 'The world', '', 'Communication and Language', 4, 9, 'A'),
(62, 'Exploring and using media and materials', '', 'Expressive Arts and Design', 4, 1, 'A'),
(63, 'Making relationships', '', 'Personal, Social and Emotional Development', 5, 1, 'A'),
(64, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 5, 2, 'A'),
(65, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 5, 3, 'A'),
(66, 'Listening and attention', '', 'Communication and Language', 5, 1, 'A'),
(67, 'Understanding', '', 'Communication and Language', 5, 2, 'A'),
(68, 'Speaking', '', 'Communication and Language', 5, 3, 'A'),
(69, 'Moving and handling', '', 'Physical Development', 5, 1, 'A'),
(70, 'Health and self-care', '', 'Physical Development', 5, 2, 'A'),
(71, 'Reading', '', 'Literacy', 5, 1, 'A'),
(72, 'Numbers', '', 'Mathematics', 5, 1, 'A'),
(73, 'Shape, space and measure', '', 'Mathematics', 5, 2, 'A'),
(74, 'People and communities', '', 'Understanding the World', 5, 1, 'A'),
(75, 'The world', '', 'Understanding the World', 5, 2, 'A'),
(76, 'Technology', '', 'Understanding the World', 5, 3, 'A'),
(77, 'Exploring and using media and materials', '', 'Expressive Arts and Design', 5, 1, 'A'),
(78, 'Being imaginative', '', 'Expressive Arts and Design', 5, 2, 'A'),
(79, 'Making relationships', '', 'Personal, Social and Emotional Development', 6, 1, 'A'),
(80, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 6, 2, 'A'),
(81, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 6, 3, 'A'),
(82, 'Listening and attention', '', 'Communication and Language', 6, 1, 'A'),
(83, 'Understanding', '', 'Communication and Language', 6, 2, 'A'),
(84, 'Speaking', '', 'Communication and Language', 6, 3, 'A'),
(85, 'Moving and Handling', '', 'Physical Development', 6, 1, 'A'),
(86, 'Health and self-care', '', 'Physical Development', 6, 2, 'A'),
(87, 'Reading', '', 'Literacy', 6, 1, 'A'),
(88, 'Writing', '', 'Literacy', 6, 2, 'A'),
(89, 'Numbers', '', 'Literacy', 6, 3, 'A'),
(90, 'Shape, space and measure', '', 'Mathematics', 6, 1, 'A'),
(91, 'People and communities', '', 'Understanding the World', 6, 1, 'A'),
(92, 'The world', '', 'Understanding the World', 6, 2, 'A'),
(93, 'Technology', '', 'Understanding the World', 6, 3, 'A'),
(94, 'Exploring and using media and materials', '', 'Expressive Arts and Design', 6, 1, 'A'),
(95, 'Being imaginative', '', 'Expressive Arts and Design', 6, 2, 'A'),
(96, 'Making relationships', '', 'Personal, Social and Emotional Development', 7, 1, 'A'),
(97, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 7, 2, 'A'),
(98, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 7, 3, 'A'),
(99, 'Listening and attention', '', 'Communication and Language', 7, 1, 'A'),
(100, 'Understanding', '', 'Communication and Language', 7, 2, 'A'),
(101, 'Speaking', '', 'Communication and Language', 7, 3, 'A'),
(102, 'Moving and handling', '', 'Physical Development', 7, 1, 'A'),
(103, 'Health and self-care', '', 'Physical Development', 7, 2, 'A'),
(104, 'Reading', '', 'Literacy', 7, 1, 'A'),
(105, 'Writing', '', 'Literacy', 7, 1, 'A'),
(106, 'Numbers', '', 'Mathematics', 7, 1, 'A'),
(107, 'Shape, space and measure', '', 'Mathematics', 7, 2, 'A'),
(108, 'People and communities', '', 'Understanding the World', 7, 1, 'A'),
(109, 'The world', '', 'Understanding the World', 7, 2, 'A'),
(110, 'Technology', '', 'Understanding the World', 7, 3, 'A'),
(111, 'Exploring and using media and materials', '', 'Expressive Arts and Design', 7, 1, 'A'),
(112, 'Being imaginative', '', 'Expressive Arts and Design', 7, 2, 'A'),
(113, 'Making relationships', '', 'Personal, Social and Emotional Development', 8, 1, 'A'),
(114, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 8, 2, 'A'),
(115, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 8, 3, 'A'),
(116, 'Listening and attention', '', 'Communication and Language', 8, 1, 'A'),
(117, 'Understanding', '', 'Communication and Language', 8, 2, 'A'),
(118, 'Speaking', '', 'Communication and Language', 8, 3, 'A'),
(119, 'Moving and Handling', '', 'Physical Development', 8, 1, 'A'),
(120, 'Health and self-care', '', 'Physical Development', 8, 2, 'A'),
(121, 'Reading', '', 'Literacy', 8, 1, 'A'),
(122, 'Writing', '', 'Literacy', 8, 2, 'A'),
(123, 'Numbers', '', 'Mathematics', 8, 1, 'A'),
(124, 'Shape, space and measure', '', 'Mathematics', 8, 2, 'A'),
(125, 'People and Communities', '', 'Understanding the World', 8, 1, 'A'),
(126, 'The World', '', 'Understanding the World', 8, 2, 'A'),
(127, 'Technology', '', 'Understanding the World', 8, 3, 'A'),
(128, 'Exploring and using media and materials', '', 'Expressive Arts and Design', 8, 1, 'A'),
(129, 'Being imaginative', '', 'Expressive Arts and Design', 8, 2, 'A'),
(130, 'Children have a strong sense of identity', NULL, 'Outcome 1', 9, 1, 'A'),
(131, 'Children are connected with and contribute to their world', NULL, 'Outcome 2', 9, 2, 'A'),
(132, 'Children have a strong sense of wellbeing', NULL, 'Outcome 3', 9, 3, 'A'),
(133, 'Children are confident and involved learners', NULL, 'Outcome 4', 9, 4, 'A'),
(134, 'Children are effective communicators', NULL, 'Outcome 5', 9, 5, 'A'),
(135, '', NULL, 'Goals', 10, 1, 'A'),
(136, '', NULL, 'Goals', 11, 1, 'A'),
(137, '', NULL, 'Goals', 12, 1, 'A'),
(138, 'Making relationships', '', 'Personal, Social and Emotional Development', 13, 1, 'A'),
(139, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 13, 2, 'A'),
(140, 'Listening and attention', '', 'Personal, Social and Emotional Development', 13, 3, 'A'),
(141, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 13, 4, 'A'),
(142, 'Understanding', '', 'Communication and Language', 13, 1, 'A'),
(143, 'Speaking', '', 'Communication and Language', 13, 2, 'A'),
(144, 'Listening and attention', '', 'Communication and Language', 13, 3, 'A'),
(145, 'Moving and Handling', '', 'Physical Development', 13, 1, 'A'),
(146, 'Health and self-care', '', 'Physical Development', 13, 2, 'A'),
(147, 'Reading', '', 'Literacy', 13, 1, 'A'),
(148, 'Numbers', '', 'Mathematics', 13, 1, 'A'),
(149, 'Shape, space and measure', '', 'Mathematics', 13, 2, 'A'),
(150, 'People and communities', '', 'Understanding the World', 13, 1, 'A'),
(151, 'The world', '', 'Understanding the World', 13, 2, 'A'),
(152, 'Technology', '', 'Understanding the World', 13, 3, 'A'),
(153, 'Exploring and using media and materials', 'Link to Playing and Exploring, Physical Development, Understanding the World to link to the curriculum framework and childrenâ€™s learning opportunities to demonstrate this sub theme instead.', 'Expressive Arts and Design', 13, 1, 'A'),
(154, 'Being imaginative', 'Babies and toddlers need to explore the world and develop a range of ways to communicate before they can express their own ideas through arts and design. Link to Communication and Language; Physical Development; Personal, Social and Emotional Development themes to demonstrate this.', 'Expressive Arts and Design', 13, 2, 'A'),
(155, 'Making relationships', '', 'Personal, Social and Emotional Development', 14, 1, 'A'),
(156, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 14, 2, 'A'),
(157, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 14, 13, 'A'),
(158, 'Listening and attention', '', 'Communication and Language', 14, 1, 'A'),
(159, 'Understanding', '', 'Communication and Language', 14, 2, 'A'),
(160, 'Speaking', '', 'Communication and Language', 14, 3, 'A'),
(161, 'Moving and Handling', '', 'Communication and Language', 14, 4, 'A'),
(162, 'Health and self-care', '', 'Communication and Language', 14, 5, 'A'),
(163, 'Reading', '', 'Communication and Language', 14, 6, 'A'),
(164, 'Numbers', '', 'Communication and Language', 14, 7, 'A'),
(165, 'Shape, space and measure', '', 'Communication and Language', 14, 8, 'A'),
(166, 'The world', '', 'Communication and Language', 14, 9, 'A'),
(167, 'Exploring and using media and materials', '', 'Expressive Arts and Design', 14, 1, 'A'),
(168, 'Making relationships', '', 'Personal, Social and Emotional Development', 15, 1, 'A'),
(169, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 15, 2, 'A'),
(170, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 15, 3, 'A'),
(171, 'Listening and attention', '', 'Communication and Language', 15, 1, 'A'),
(172, 'Understanding', '', 'Communication and Language', 15, 2, 'A'),
(173, 'Speaking', '', 'Communication and Language', 15, 3, 'A'),
(174, 'Moving and handling', '', 'Physical Development', 15, 1, 'A'),
(175, 'Health and self-care', '', 'Physical Development', 15, 2, 'A'),
(176, 'Reading', '', 'Literacy', 15, 1, 'A'),
(177, 'Numbers', '', 'Mathematics', 15, 1, 'A'),
(178, 'Shape, space and measure', '', 'Mathematics', 15, 2, 'A'),
(179, 'People and communities', '', 'Understanding the World', 15, 1, 'A'),
(180, 'The world', '', 'Understanding the World', 15, 2, 'A'),
(181, 'Technology', '', 'Understanding the World', 15, 3, 'A'),
(182, 'Exploring and using media and materials', '', 'Expressive Arts and Design', 15, 1, 'A'),
(183, 'Being imaginative', '', 'Expressive Arts and Design', 15, 2, 'A'),
(184, 'Making relationships', '', 'Personal, Social and Emotional Development', 16, 1, 'A'),
(185, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 16, 2, 'A'),
(186, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 16, 3, 'A'),
(187, 'Listening and attention', '', 'Communication and Language', 16, 1, 'A'),
(188, 'Understanding', '', 'Communication and Language', 16, 2, 'A'),
(189, 'Speaking', '', 'Communication and Language', 16, 3, 'A'),
(190, 'Moving and Handling', '', 'Physical Development', 16, 1, 'A'),
(191, 'Health and self-care', '', 'Physical Development', 16, 2, 'A'),
(192, 'Reading', '', 'Literacy', 16, 1, 'A'),
(193, 'Writing', '', 'Literacy', 16, 2, 'A'),
(194, 'Numbers', '', 'Literacy', 16, 3, 'A'),
(195, 'Shape, space and measure', '', 'Mathematics', 16, 1, 'A'),
(196, 'People and communities', '', 'Understanding the World', 16, 1, 'A'),
(197, 'The world', '', 'Understanding the World', 16, 2, 'A'),
(198, 'Technology', '', 'Understanding the World', 16, 3, 'A'),
(199, 'Exploring and using media and materials', '', 'Expressive Arts and Design', 16, 1, 'A'),
(200, 'Being imaginative', '', 'Expressive Arts and Design', 16, 2, 'A'),
(201, 'Making relationships', '', 'Personal, Social and Emotional Development', 17, 1, 'A'),
(202, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 17, 2, 'A'),
(203, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 17, 3, 'A'),
(204, 'Listening and attention', '', 'Communication and Language', 17, 1, 'A'),
(205, 'Understanding', '', 'Communication and Language', 17, 2, 'A'),
(206, 'Speaking', '', 'Communication and Language', 17, 3, 'A'),
(207, 'Moving and handling', '', 'Physical Development', 17, 1, 'A'),
(208, 'Health and self-care', '', 'Physical Development', 17, 2, 'A'),
(209, 'Reading', '', 'Literacy', 17, 1, 'A'),
(210, 'Writing', '', 'Literacy', 17, 1, 'A'),
(211, 'Numbers', '', 'Mathematics', 17, 1, 'A'),
(212, 'Shape, space and measure', '', 'Mathematics', 17, 2, 'A'),
(213, 'People and communities', '', 'Understanding the World', 17, 1, 'A'),
(214, 'The world', '', 'Understanding the World', 17, 2, 'A'),
(215, 'Technology', '', 'Understanding the World', 17, 3, 'A'),
(216, 'Exploring and using media and materials', '', 'Expressive Arts and Design', 17, 1, 'A'),
(217, 'Being imaginative', '', 'Expressive Arts and Design', 17, 2, 'A'),
(218, 'Making relationships', '', 'Personal, Social and Emotional Development', 18, 1, 'A'),
(219, 'Self-confidence and self-awareness', '', 'Personal, Social and Emotional Development', 18, 2, 'A'),
(220, 'Managing feelings and behaviour', '', 'Personal, Social and Emotional Development', 18, 3, 'A'),
(221, 'Listening and attention', '', 'Communication and Language', 18, 1, 'A'),
(222, 'Understanding', '', 'Communication and Language', 18, 2, 'A'),
(223, 'Speaking', '', 'Communication and Language', 18, 3, 'A'),
(224, 'Moving and Handling', '', 'Physical Development', 18, 1, 'A'),
(225, 'Health and self-care', '', 'Physical Development', 18, 2, 'A'),
(226, 'Reading', '', 'Literacy', 18, 1, 'A'),
(227, 'Writing', '', 'Literacy', 18, 2, 'A'),
(228, 'Numbers', '', 'Mathematics', 18, 1, 'A'),
(229, 'Shape, space and measure', '', 'Mathematics', 18, 2, 'A'),
(230, 'People and Communities', '', 'Understanding the World', 18, 1, 'A'),
(231, 'The World', '', 'Understanding the World', 18, 2, 'A'),
(232, 'Technology', '', 'Understanding the World', 18, 3, 'A'),
(233, 'Exploring and using media and materials', '', 'Expressive Arts and Design', 18, 1, 'A'),
(234, 'Being imaginative', '', 'Expressive Arts and Design', 18, 2, 'A'),
(235, '', NULL, 'Goals', 19, 1, 'A'),
(254, 'Aim 1', 'Sense of Self', 'social and emotional', 20, 1, 'A'),
(255, 'Aim 2', 'Responsibility for Self and Others', 'social and emotional', 20, 2, 'A'),
(256, 'Aim 3', 'Prosocial Behavior', 'social and emotional', 20, 3, 'A'),
(257, 'Aim 1', 'Listening and Speaking', 'language development', 20, 1, 'A'),
(258, 'Aim 2', 'Reading and Writing', 'language development', 20, 2, 'A'),
(259, 'Aim 1', 'Gross Motor', 'physical development', 20, 1, 'A'),
(260, 'Aim 3', 'Fine Motor', 'physical development', 20, 2, 'A'),
(261, 'Aim 1', 'Learning and Problem Solving', 'cognitive development', 20, 1, 'A'),
(262, 'Aim 2', 'Logical Thinking', 'cognitive development', 20, 2, 'A'),
(263, 'Aim 4', 'Representation and Symbolic Thinking', 'cognitive development', 20, 3, 'A'),
(264, '', NULL, 'Goals', 21, 1, 'A');
(271, 'Wellbeing | Mana atua', NULL, 'Strands', 22, 1, 'A'),
(272, 'Belonging | Mana whenua', NULL, 'Strands', 22, 2, 'A'),
(273, 'Contribution | Mana tangata', NULL, 'Strands', 22, 3, 'A'),
(274, 'Communication | Mana reo', NULL, 'Strands', 22, 4, 'A'),
(275, 'Exploration | Mana aotūroa', NULL, 'Strands', 22, 5, 'A'),
(276, 'Principles | Kaupapa whakahaere', NULL, 'Principles', 22, 1, 'A'),
(277, 'Literacy', NULL, 'Literacy', 23, 1, 'A'),
(278, 'Vocabulary', NULL, 'Literacy', 23, 2, 'A'),
(279, 'Phonological Awareness', NULL, 'Literacy', 23, 3, 'A'),
(280, 'Letter Knowledge and Early Word Recognition', NULL, 'Literacy', 23, 4, 'A'),
(281, 'Print and Book Awareness', NULL, 'Literacy', 23, 5, 'A'),
(282, 'Writing', NULL, 'Literacy', 23, 6, 'A'),
(283, 'Number and Number Sense', NULL, 'Mathematics', 23, 1, 'A'),
(284, 'Computation', NULL, 'Mathematics', 23, 2, 'A'),
(285, 'Measurement', NULL, 'Mathematics', 23, 3, 'A'),
(286, 'Geometry', NULL, 'Mathematics', 23, 4, 'A'),
(287, 'Data Collection and Statistics', NULL, 'Mathematics', 23, 5, 'A'),
(288, 'Patterns and Relationships', NULL, 'Mathematics', 23, 6, 'A'),
(289, 'Scientific Investigation, Reasoning, and Logic', NULL, 'Science', 23, 1, 'A'),
(290, 'Force, Motion, and Energy', NULL, 'Science', 23, 2, 'A'),
(291, 'Matter/Physical Properties', NULL, 'Science', 23, 3, 'A'),
(292, 'Matter/Simple Physical and Chemical Reactions', NULL, 'Science', 23, 4, 'A'),
(293, 'Life Processes', NULL, 'Science', 23, 5, 'A'),
(294, 'Interrelationships in Earth/Space Systems', NULL, 'Science', 23, 6, 'A'),
(295, 'Earth Patterns, Cycles and Change', NULL, 'Science', 23, 7, 'A'),
(296, 'Resources', NULL, 'Science', 23, 8, 'A'),
(297, 'History/Similarities and Differences', NULL, 'History and Social Science', 23, 1, 'A'),
(298, 'History/Change Over Time', NULL, 'History and Social Science', 23, 2, 'A'),
(299, 'Geography/Location', NULL, 'History and Social Science', 23, 3, 'A'),
(300, 'Geography/Descriptive Words', NULL, 'History and Social Science', 23, 4, 'A'),
(301, 'Economics/World of Work', NULL, 'History and Social Science', 23, 5, 'A'),
(302, 'Economics/Making Choices and Earning Money', NULL, 'History and Social Science', 23, 6, 'A'),
(303, 'Civics/Citizenship', NULL, 'History and Social Science', 23, 7, 'A'),
(304, 'Skilled Movement/Locomotor Skills', NULL, 'Health and Physical Development', 23, 1, 'A'),
(305, 'Skilled Movement/Non-locomotor Skills', NULL, 'Health and Physical Development', 23, 2, 'A'),
(306, 'Skilled Movement/Manipulative Skills', NULL, 'Health and Physical Development', 23, 3, 'A'),
(307, 'Movement Principles and Concepts', NULL, 'Health and Physical Development', 23, 4, 'A'),
(308, 'Personal Fitness', NULL, 'Health and Physical Development', 23, 5, 'A'),
(309, 'Responsible Behaviors', NULL, 'Health and Physical Development', 23, 6, 'A'),
(310, 'Physically Active Lifestyle', NULL, 'Health and Physical Development', 23, 7, 'A'),
(311, 'Health Knowledge and Skills/Nutrition', NULL, 'Health and Physical Development', 23, 8, 'A'),
(312, 'Health Knowledge and Skills/Habits that Promote Health and Prevent Illness', NULL, 'Health and Physical Development', 23, 8, 'A'),
(313, 'Information Access and Use', NULL, 'Health and Physical Development', 23, 9, 'A'),
(314, 'Community Health and Safety', NULL, 'Health and Physical Development', 23, 10, 'A'),
(315, 'Self-Concept', NULL, 'Personal and Social Development', 23, 1, 'A'),
(316, 'Self-Regulation', NULL, 'Personal and Social Development', 23, 2, 'A'),
(317, 'Approaches to Learning', NULL, 'Personal and Social Development', 23, 3, 'A'),
(318, 'Interaction with Others', NULL, 'Personal and Social Development', 23, 4, 'A'),
(319, 'Social Problem Solving', NULL, 'Personal and Social Development', 23, 5, 'A'),
(320, 'Music Theory/Literacy', NULL, 'Music', 23, 1, 'A'),
(321, 'Performance', NULL, 'Music', 23, 2, 'A'),
(322, 'Music History and Cultural Context', NULL, 'Music', 23, 3, 'A'),
(323, 'Analysis, Evaluation, and Critique', NULL, 'Music', 23, 4, 'A'),
(324, 'Aesthetics', NULL, 'Music', 23, 5, 'A'),
(325, 'Visual Communication and Production', NULL, 'Visual Arts', 23, 1, 'A'),
(326, 'Art History and Cultural Context', NULL, 'Visual Arts', 23, 2, 'A'),
(327, 'Analysis, Evaluation, and Critique', NULL, 'Visual Arts', 23, 3, 'A'),
(328, 'Aesthetics', NULL, 'Visual Arts', 23, 4, 'A'),
(329, 'Goal 1', 'Children will develop curiosity, initiative, selfdirection, and persistence.', 'Approaches to Learning', 24, 1, 'A'),
(330, 'Goal 2', 'Children will develop positive attitudes, habits, and learning styles.', 'Approaches to Learning', 24, 2, 'A'),
(331, 'Goal 1', 'Children will develop listening comprehension skills (receptive language).', 'Language and Literacy', 24, 1, 'A'),
(332, 'Goal 2', 'Children will develop phonological awareness skills to discriminate the sounds of language.', 'Language and Literacy', 24, 2, 'A'),
(333, 'Goal 3', 'Children will develop an understanding of new vocabulary.', 'Language and Literacy', 24, 3, 'A'),
(334, 'Goal 4', 'Children will develop speaking skills for the purpose of communication (expressive language).', 'Language and Literacy', 24, 4, 'A'),
(335, 'Goal 5', 'Children will develop age-appropriate writing skills.', 'Language and Literacy', 24, 5, 'A'),
(336, 'Goal 6', 'Children will develop knowledge about the various uses of print and characteristics of written language (concepts about print).', 'Language and Literacy', 24, 6, 'A'),
(337, 'Goal 7', 'Children will develop alphabet knowledge.', 'Language and Literacy', 24, 7, 'A'),
(338, 'Goal 1', 'Children will begin to develop an awareness and understanding of numbers.', 'Mathematics', 24, 1, 'A'),
(339, 'Goal 2', 'Children will develop an understanding of basic geometric shapes and develop a sense of space.', 'Mathematics', 24, 2, 'A'),
(340, 'Goal 3', 'Children will show awareness of, recognize, and create patterns.', 'Mathematics', 24, 3, 'A'),
(341, 'Goal 4', 'Children will explore concepts of basic measurements.', 'Mathematics', 24, 4, 'A'),
(342, 'Goal 5', 'Children will analyze data within small and large group settings.', 'Mathematics', 24, 5, 'A'),
(343, 'Goal 1', 'Children will develop the ability to use scientific processes and inquiry.', 'Science and Environmental Education', 24, 1, 'A'),
(344, 'Goal 2', 'Children will acquire knowledge related to physical science.', 'Science and Environmental Education', 24, 2, 'A'),
(345, 'Goal 3', 'Children will acquire knowledge related to earth sciences and our environment.', 'Science and Environmental Education', 24, 3, 'A'),
(346, 'Goal 4', 'Children will acquire knowledge related to earth and space science.', 'Science and Environmental Education', 24, 4, 'A'),
(347, 'Goal 1', 'Children will gain knowledge of technology.', 'Technology', 24, 1, 'A'),
(348, 'Goal 1', 'Children will develop confidence and positive selfawareness.', 'Social and Emotional Development', 24, 1, 'A'),
(349, 'Goal 2', 'Children will increase the capacity for self control.', 'Social and Emotional Development', 24, 2, 'A'),
(350, 'Goal 3', 'Children will develop interpersonal and social skills for relating with other people.', 'Social and Emotional Development', 24, 3, 'A'),
(351, 'Goal 4', 'Children will develop a respect for differences in people and an appreciation of their role as being a member of the family, classroom, and the community.', 'Social and Emotional Development', 24, 4, 'A'),
(352, 'Goal 1', 'Children will develop gross motor skills.', 'Physical Development', 24, 1, 'A'),
(353, 'Goal 2', 'Children will develop fine motor skills.', 'Physical Development', 24, 2, 'A'),
(354, 'Goal 1', 'Children will acquire knowledge of healthy personal care routines.', 'Health and Daily Living', 24, 1, 'A'),
(355, 'Goal 2', 'Children will acquire knowledge of healthy nutritional practices.', 'Health and Daily Living', 24, 2, 'A'),
(356, 'Goal 3', 'Children will acquire knowledge of safety practices.', 'Health and Daily Living', 24, 3, 'A'),
(357, 'Goal 1', 'Children will use art for creative expression and representation.', 'Creative Arts', 24, 1, 'A'),
(358, 'Goal 2', 'Children will show self-expression through music and movement.', 'Creative Arts', 24, 2, 'A'),
(359, 'Goal 3', 'Children will participate in a variety of dramatic play activities.', 'Creative Arts', 24, 3, 'A'),
(360, 'Making relationships', NULL, 'Personal, Social and Emotional Development', 25, 1, 'A'),
(361, 'Self-confidence and self-awareness', NULL, 'Personal, Social and Emotional Development', 25, 2, 'A'),
(362, 'Listening and attention', NULL, 'Personal, Social and Emotional Development', 25, 3, 'A'),
(363, 'Managing feelings and behaviour', NULL, 'Personal, Social and Emotional Development', 25, 4, 'A'),
(364, 'Understanding', NULL, 'Communication and Language', 25, 1, 'A'),
(365, 'Speaking', NULL, 'Communication and Language', 25, 2, 'A'),
(366, 'Listening and attention', NULL, 'Communication and Language', 25, 3, 'A'),
(367, 'Moving and Handling', NULL, 'Physical Development', 25, 1, 'A'),
(368, 'Health and self-care', NULL, 'Physical Development', 25, 2, 'A'),
(369, 'Reading', NULL, 'Literacy', 25, 1, 'A'),
(370, 'Numbers', NULL, 'Mathematics', 25, 1, 'A'),
(371, 'Shape, space and measure', NULL, 'Mathematics', 25, 2, 'A'),
(372, 'People and communities', NULL, 'Understanding the World', 25, 1, 'A'),
(373, 'The world', NULL, 'Understanding the World', 25, 2, 'A'),
(374, 'Technology', NULL, 'Understanding the World', 25, 3, 'A'),
(375, 'Exploring and using media and materials', 'Link to Playing and Exploring, Physical Development, Understanding the World to link to the curriculum framework and childrenâ€™s learning opportunities to demonstrate this sub theme instead.', 'Expressive Arts and Design', 25, 1, 'A'),
(376, 'Being imaginative', 'Babies and toddlers need to explore the world and develop a range of ways to communicate before they can express their own ideas through arts and design. Link to Communication and Language; Physical Development; Personal, Social and Emotional Development themes to demonstrate this.', 'Expressive Arts and Design', 25, 2, 'A'),
(377, 'Making relationships', NULL, 'Personal, Social and Emotional Development', 26, 1, 'A'),
(378, 'Self-confidence and self-awareness', NULL, 'Personal, Social and Emotional Development', 26, 2, 'A'),
(379, 'Managing feelings and behaviour', NULL, 'Personal, Social and Emotional Development', 26, 3, 'A'),
(380, 'Listening and attention', NULL, 'Communication and Language', 26, 1, 'A'),
(381, 'Understanding', NULL, 'Communication and Language', 26, 2, 'A'),
(382, 'Speaking', NULL, 'Communication and Language', 26, 3, 'A'),
(383, 'Moving and Handling', NULL, 'Communication and Language', 26, 4, 'A'),
(384, 'Health and self-care', NULL, 'Communication and Language', 26, 5, 'A'),
(385, 'Reading', NULL, 'Communication and Language', 26, 6, 'A'),
(386, 'Numbers', NULL, 'Communication and Language', 26, 7, 'A'),
(387, 'Shape, space and measure', NULL, 'Communication and Language', 26, 8, 'A'),
(388, 'The world', NULL, 'Communication and Language', 26, 9, 'A'),
(389, 'Exploring and using media and materials', NULL, 'Expressive Arts and Design', 26, 1, 'A'),
(390, 'Making relationships', NULL, 'Personal, Social and Emotional Development', 27, 1, 'A'),
(391, 'Self-confidence and self-awareness', NULL, 'Personal, Social and Emotional Development', 27, 2, 'A'),
(392, 'Managing feelings and behaviour', NULL, 'Personal, Social and Emotional Development', 27, 3, 'A'),
(393, 'Listening and attention', NULL, 'Communication and Language', 27, 1, 'A'),
(394, 'Understanding', NULL, 'Communication and Language', 27, 2, 'A'),
(395, 'Speaking', NULL, 'Communication and Language', 27, 3, 'A'),
(396, 'Moving and Handling', NULL, 'Physical Development', 27, 1, 'A'),
(397, 'Health and self-care', NULL, 'Physical Development', 27, 2, 'A'),
(398, 'Reading', NULL, 'Literacy', 27, 1, 'A'),
(399, 'Numbers', NULL, 'Mathematics', 27, 1, 'A'),
(400, 'Shape, space and measure', NULL, 'Mathematics', 27, 2, 'A'),
(401, 'People and communities', NULL, 'Understanding the World', 27, 1, 'A'),
(402, 'The world', NULL, 'Understanding the World', 27, 2, 'A'),
(403, 'Technology', NULL, 'Understanding the World', 27, 3, 'A'),
(404, 'Exploring and using media and materials', NULL, 'Expressive Arts and Design', 27, 1, 'A'),
(405, 'Being imaginative', NULL, 'Expressive Arts and Design', 27, 2, 'A'),
(406, 'Making relationships', NULL, 'Personal, Social and Emotional Development', 28, 1, 'A'),
(407, 'Self-confidence and self-awareness', NULL, 'Personal, Social and Emotional Development', 28, 2, 'A'),
(408, 'Managing feelings and behaviour', NULL, 'Personal, Social and Emotional Development', 28, 3, 'A'),
(409, 'Listening and attention', NULL, 'Communication and Language', 28, 1, 'A'),
(410, 'Understanding', NULL, 'Communication and Language', 28, 2, 'A'),
(411, 'Speaking', NULL, 'Communication and Language', 28, 3, 'A'),
(412, 'Moving and Handling', NULL, 'Physical Development', 28, 1, 'A'),
(413, 'Health and self-care', NULL, 'Physical Development', 28, 2, 'A'),
(414, 'Reading', NULL, 'Literacy', 28, 1, 'A'),
(415, 'Writing', NULL, 'Literacy', 28, 2, 'A'),
(416, 'Numbers', NULL, 'Literacy', 28, 3, 'A'),
(417, 'Shape, space and measure', NULL, 'Mathematics', 28, 1, 'A'),
(418, 'People and communities', NULL, 'Understanding the World', 28, 1, 'A'),
(419, 'The world', NULL, 'Understanding the World', 28, 2, 'A'),
(420, 'Technology', NULL, 'Understanding the World', 28, 3, 'A'),
(421, 'Exploring and using media and materials', NULL, 'Expressive Arts and Design', 28, 1, 'A'),
(422, 'Being imaginative', NULL, 'Expressive Arts and Design', 28, 2, 'A'),
(423, 'Making relationships', NULL, 'Personal, Social and Emotional Development', 29, 1, 'A'),
(424, 'Self-confidence and self-awareness', NULL, 'Personal, Social and Emotional Development', 29, 2, 'A'),
(425, 'Managing feelings and behaviour', NULL, 'Personal, Social and Emotional Development', 29, 3, 'A'),
(426, 'Listening and attention', NULL, 'Communication and Language', 29, 1, 'A'),
(427, 'Understanding', NULL, 'Communication and Language', 29, 2, 'A'),
(428, 'Speaking', NULL, 'Communication and Language', 29, 3, 'A'),
(429, 'Moving and Handling', NULL, 'Physical Development', 29, 1, 'A'),
(430, 'Health and self-care', NULL, 'Physical Development', 29, 2, 'A'),
(431, 'Reading', NULL, 'Literacy', 29, 1, 'A'),
(432, 'Writing', NULL, 'Literacy', 29, 2, 'A'),
(433, 'Numbers', NULL, 'Mathematics', 29, 1, 'A'),
(434, 'Shape, space and measure', NULL, 'Mathematics', 29, 2, 'A'),
(435, 'People and communities', NULL, 'Understanding the World', 29, 1, 'A'),
(436, 'The world', NULL, 'Understanding the World', 29, 2, 'A'),
(437, 'Technology', NULL, 'Understanding the World', 29, 3, 'A'),
(438, 'Exploring and using media and materials', NULL, 'Expressive Arts and Design', 29, 1, 'A'),
(439, 'Being imaginative', NULL, 'Expressive Arts and Design', 29, 2, 'A'),
(440, 'Making relationships', NULL, 'Personal, Social and Emotional Development', 30, 1, 'A'),
(441, 'Self-confidence and self-awareness', NULL, 'Personal, Social and Emotional Development', 30, 2, 'A'),
(442, 'Managing feelings and behaviour', NULL, 'Personal, Social and Emotional Development', 30, 3, 'A'),
(443, 'Listening and attention', NULL, 'Communication and Language', 30, 1, 'A'),
(444, 'Understanding', NULL, 'Communication and Language', 30, 2, 'A'),
(445, 'Speaking', NULL, 'Communication and Language', 30, 3, 'A'),
(446, 'Moving and Handling', NULL, 'Physical Development', 30, 1, 'A'),
(447, 'Health and self-care', NULL, 'Physical Development', 30, 2, 'A'),
(448, 'Reading', NULL, 'Literacy', 30, 1, 'A'),
(449, 'Writing', NULL, 'Literacy', 30, 2, 'A'),
(450, 'Numbers', NULL, 'Mathematics', 30, 1, 'A'),
(451, 'Shape, space and measure', NULL, 'Mathematics', 30, 2, 'A'),
(452, 'People and communities', NULL, 'Understanding the World', 30, 1, 'A'),
(453, 'The world', NULL, 'Understanding the World', 30, 2, 'A'),
(454, 'Technology', NULL, 'Understanding the World', 30, 3, 'A'),
(455, 'Exploring and using media and materials', NULL, 'Expressive Arts and Design', 30, 1, 'A'),
(456, 'Being imaginative', NULL, 'Expressive Arts and Design', 30, 2, 'A'),
(457, 'Strand A', 'Early learning experiences will support children to develop effective approaches to learning.', 'Cognition', 31, 1, 'A'),
(458, 'Strand B', 'Early learning experiences will support children to use logic and reasoning.', 'Cognition', 31, 2, 'A'),
(459, 'Strand C', 'Early learning experiences will support children to strengthen executive function.', 'Cognition', 31, 3, 'A'),
(460, 'Strand A', 'Early learning experiences will support children to develop trusting healthy attachments and relationships with primary caregivers.', 'Social and Emotional Development', 31, 1, 'A'),
(461, 'Strand B', 'Early learning experiences will support children to develop self-regulation.', 'Social and Emotional Development', 31, 2, 'A'),
(462, 'Strand C', 'Early learning experiences will support children to develop, express, recognize and respond to emotions.', 'Social and Emotional Development', 31, 3, 'A'),
(463, 'Strand D', 'Early learning experiences will support children to develop self-awareness, self-concept and competence.', 'Social and Emotional Development', 31, 4, 'A'),
(464, 'Strand H', 'Early learning experiences will support children to develop social relationships.', 'Social and Emotional Development', 31, 5, 'A'),
(465, 'Strand A', 'Early learning experiences will support children to develop gross motor skills.', 'Physical Development and Health', 31, 1, 'A'),
(466, 'Strand B', 'Early learning experiences will support children to develop fine motor skills.', 'Physical Development and Health', 31, 2, 'A'),
(467, 'Strand C', 'Early learning experiences will support children to acquire adaptive skills.', 'Physical Development and Health', 31, 3, 'A'),
(468, 'Strand D', 'Early learning experiences will support children to maintain physical health status and well-being.', 'Physical Development and Health', 31, 4, 'A'),
(469, 'Strand A', 'Early learning experiences will support children to understand language (receptive language).', 'Early Language, Communication, and Literacy/Language and Literacy', 31, 1, 'A'),
(470, 'Strand B', 'Early learning experiences will support children to use language (expressive language).', 'Early Language, Communication, and Literacy/Language and Literacy', 31, 2, 'A'),
(471, 'Strand C', 'Early learning experiences will support children to use language for social interaction.', 'Early Language, Communication, and Literacy/Language and Literacy', 31, 3, 'A'),
(472, 'Strand D', 'Early learning experiences will support children to gain book appreciation and knowledge.', 'Early Language, Communication, and Literacy/Language and Literacy', 31, 4, 'A'),
(473, 'Strand E', 'Early learning experiences will support children to gain knowledge of print and its uses.', 'Early Language, Communication, and Literacy/Language and Literacy', 31, 5, 'A'),
(474, 'Strand F', 'Early learning experiences will support children to develop phonological awareness.', 'Early Language, Communication, and Literacy/Language and Literacy', 31, 6, 'A'),
(475, 'Strand G', 'Early learning experiences will support children to convey meaning through drawing, letters and words.', 'Early Language, Communication, and Literacy/Language and Literacy', 31, 7, 'A'),
(476, 'Strand A', 'Early learning experiences will support children to engage in and enjoy the arts.', 'Creative Arts', 31, 1, 'A'),
(477, 'Strand B', 'Early learning experiences will support children to explore and respond to creative works.', 'Creative Arts', 31, 2, 'A'),
(478, 'Strand A', 'Early learning experiences will support children to understand counting and cardinality.', 'Early Mathematical Discovery/Mathematics', 31, 1, 'A'),
(479, 'Strand B', 'Early learning experiences will support children to understand and describe relationships to solve problems (operations and algebraic thinking).', 'Early Mathematical Discovery/Mathematics', 31, 2, 'A'),
(480, 'Strand C', 'Early learning experiences will support children to understand the attributes and relative properties of objects (measurement and data).', 'Early Mathematical Discovery/Mathematics', 31, 3, 'A'),
(481, 'Strand D', 'Early learning experiences will support children to understand shapes and spatial relationships (geometry and spatial sense).', 'Early Mathematical Discovery/Mathematics', 31, 4, 'A'),
(482, 'Strand A', 'Early learning experiences will support children to apply scientific practices.', 'Early Scientific Inquiry/Science', 31, 1, 'A'),
(483, 'Strand B', 'Early learning experiences will support children to engage in the process of engineering.', 'Early Scientific Inquiry/Science', 31, 2, 'A'),
(484, 'Strand C', 'Early learning experiences will support children to understand patterns, process and relationships of living things.', 'Early Scientific Inquiry/Science', 31, 3, 'A'),
(485, 'Strand D', 'Early learning experiences will support children to understand physical sciences.', 'Early Scientific Inquiry/Science', 31, 4, 'A'),
(486, 'Strand E', 'Early learning experiences will support children to understand features of earth.', 'Early Scientific Inquiry/Science', 31, 5, 'A'),
(487, 'Strand A', 'Early Learning experiences will support children to understand self, family and a diverse community.', 'Social Studies', 31, 1, 'A'),
(488, 'Strand B', 'Early Learning experiences will support children to learn about people and the environment.', 'Social Studies', 31, 2, 'A'),
(489, 'Strand C', 'Early Learning experiences will support children to develop an understanding of economic systems and resources.', 'Social Studies', 31, 3, 'A'),
(490, 'Strand D', 'Early Learning experiences will support children to understand change over time.', 'Social Studies', 31, 4, 'A');

-- --------------------------------------------------------

--
-- Structure de la table `framework_goals`
--

CREATE TABLE `framework_goals` (
  `goal_id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `goal_description` varchar(512) DEFAULT NULL,
  `goal_help` text,
  `goal_sort` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `goal_status` enum('A','D') NOT NULL,
  `goal_keywords` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `framework_goals`
--

INSERT INTO `framework_goals` (`goal_id`, `category_id`, `goal_description`, `goal_help`, `goal_sort`, `goal_status`, `goal_keywords`) VALUES
(1, 1, 'make strong attachments and develop warm and supportive relationships with family, peers and adults in out-of-home settings and in their community', '', 1, 'A', 'eye-contact, voice, touch, feeding, toileting, sleeping, playing, play with your friends, played with, talks to, talked to'),
(2, 1, 'be aware of and name their own feelings, and understand that others may have different feelings', '', 2, 'A', 'sad, happy, smiling, feeling, we talked about'),
(3, 1, 'handle transitions and changes well', '', 3, 'A', 'starting pre-school, new school, primary school, staying overnight with, new house, new baby, new sibling, moving house, moving class'),
(4, 1, 'be confident and self-reliant', '', 4, 'A', 'independent, confident, yourself, on your own, mix the paint, mix different colours, take a picture, tractor, tricycle, bike, pictures'),
(5, 1, 'respect themselves, others and the environment', '', 5, 'A', 'open farm, animal, animals, rabbit, bird, cat, dog, garden, different smells, colours, textures, raincoat, wellies, hat, gloves, natural environment, stamping in puddles, stamped, playing in the puddle, played in the puddle, played with the mud, mud kitchen, digging, recycling, sweeping, made art with natural materials, making art with natural materials, natural materials, leaves, twigs, shells, stones, went for a walk, planting, planted, worms, ladybirds, spiders, caterpillars, played outside'),
(6, 1, 'make decisions and choices about their own learning and development', '', 6, 'A', 'chose, choose, decide, decided, wanted to, did not want to'),
(7, 2, 'gain increasing control and co-ordination of body movements', '', 1, 'A', 'lying on your tummy, tummy, lift your head, lifted his head, lifted her head, look around, sat up on your own, sat up, lay on your back, lying on your back, strengthen your muscles'),
(8, 2, 'be aware of their bodies, their bodily functions, and their changing abilities', '', 2, 'A', ''),
(9, 2, 'discover, explore and refine gross and fine motor skills', '', 3, 'A', 'jumped, jumping, running, ran, play, gross, fine motor skills, hand-eye, co-ordination, pincer, hands, legs, kick the ball, threw the ball, throw, discover, explore, clap hands, clapped, clapping, turn the page, turn the pages of the book, turn the pages, stand, walk, put shapes, shape sorter'),
(10, 2, 'use self-help skills in caring for their own bodies', '', 4, 'A', ''),
(11, 2, 'show good judgement when taking risks', '', 5, 'A', ''),
(12, 2, 'make healthy choices and demonstrate positive attitudes to nutrition, hygiene, exercise, and routine', '', 6, 'A', 'fruit, vegetables, apple, banana, peas, ham, strawberry, strawberries, running, playing, jumping, swimming, swinging, played, jumped, ran, moved, moving, exercise, routine, healthy eating, healthy, healthy food'),
(13, 3, 'express themselves creatively and experience the arts', '', 1, 'A', 'paint, paper, art, collage, mixing, mark-making, marks, crayons, pencils, colouring, coloured'),
(15, 3, 'develop and nurture their sense of wonder and awe', '', 3, 'A', 'wonder, awe, surprised, touching flowers, leaves, looking at spider webs, watching, listening to, touching, running water, playing, snow, played, snow, listened, touched, ran, wondered, touched the flowers, touched the leaves, nature walk, stones, wooden, wool, felt, basket, felt, splashed water'),
(16, 3, 'become reflective and think flexibly', '', 4, 'A', ''),
(17, 3, 'care for the environment', '', 5, 'A', ''),
(18, 3, 'understand that others may have beliefs and values different to their own', '', 6, 'A', ''),
(19, 4, 'show increasing independence, and be able to make choices and decisions', '', 1, 'A', 'showed you, told you, helped you, cupboard, lifted, chair, cared, caring for, blanket, swept the floor, tidied up'),
(20, 4, 'demonstrate a sense of mastery and belief in their own abilities and display learning dispositions, such as determination and perseverance', '', 2, 'A', ''),
(21, 4, 'think positively, take learning risks, and become resilient and resourceful when things go wrong', '', 3, 'A', ''),
(22, 4, 'motivate themselves, and welcome and seek challenge', '', 4, 'A', ''),
(23, 4, 'respect life, their own and others, and know that life has a meaning and purpose', '', 5, 'A', ''),
(24, 4, 'be active citizens', '', 6, 'A', ''),
(25, 5, 'build respectful relationships with others', '', 1, 'A', ''),
(26, 5, 'appreciate the features that make a person special and unique (name, size, hair, hand and footprint, gender, birthday)', '', 2, 'A', 'your hair, hand, footprint, boy, girl, birthday'),
(27, 5, 'understand that as individuals they are separate from others with their own needs, interests and abilities', '', 3, 'A', ''),
(28, 5, 'have a sense of ‘who they are’ and be able to describe their backgrounds, strengths and abilities', '', 4, 'A', 'family, home, pet, favourite toy, interested, about me, sport, animals, cars, dancing, singing, instrument, computers, literacy, numeracy'),
(29, 5, 'feel valued and see themselves and their interests reflected in the environment', '', 5, 'A', 'interested, mum, dad, family, your sister, your brother'),
(30, 5, 'express their own ideas, preferences and needs, and have these responded to with respect and consistency', '', 6, 'A', ''),
(31, 6, 'feel that they have a place and a right to belong to the group', '', 1, 'A', ''),
(32, 6, 'know that members of their family and community are positively acknowledged and welcomed', '', 2, 'A', 'family, community, language, polish, Nigerian, Irish, culture, world map, countries, garda, a nurse, a social welfare officer, a librarian, a teacher, a lollipop person, fireman, judge, teacher, family wall, community wall, pictures of'),
(33, 6, 'be able to share personal experiences about their own families and cultures, and come to know that there is a diversity of family structures, cultures and backgrounds', '', 3, 'A', 'disability, special needs, wheelchair, African, polish'),
(34, 6, 'understand and take part in routines, customs, festivals, and celebrations', '', 4, 'A', 'Chinese new year, celebration, family wall, community wall'),
(35, 6, 'see themselves as part of a wider community and know about their local area, including some of its places, features and people', '', 5, 'A', ''),
(36, 6, 'understand the different roles of people in the community', '', 6, 'A', 'garda, a nurse, a social welfare officer, a librarian, a teacher, a lollipop person, doctor, lollipop lady, fireman, teacher'),
(37, 7, 'express their views and help make decisions in matters that affect them', '', 1, 'A', ''),
(38, 7, 'understand the rules and the boundaries of acceptable behaviour', '', 2, 'A', 'rules'),
(39, 7, 'interact, work co-operatively, and help others', '', 3, 'A', 'team work, friend, talk, help others, work together, group'),
(40, 7, 'be aware of and respect others’ needs, rights, feelings, culture, language, background, and religious beliefs', '', 4, 'A', 'religion, feelings'),
(41, 7, 'have a sense of social justice and recognise and deal with unfair behaviour', '', 5, 'A', ''),
(42, 7, 'demonstrate the skills of co-operation, responsibility, negotiation, and conflict resolution', '', 6, 'A', ''),
(43, 8, 'develop a broad range of abilities and interests', '', 1, 'A', ''),
(44, 8, 'show an awareness of their own unique strengths, abilities and learning styles, and be willing to share their skills and knowledge with others', '', 2, 'A', ''),
(45, 8, 'show increasing confidence and self-assurance in directing their own learning', '', 3, 'A', ''),
(46, 8, 'demonstrate dispositions like curiosity, persistence and responsibility', '', 4, 'A', ''),
(47, 8, 'experience learning opportunities that are based on personal interests, and linked to their home, community and culture', '', 5, 'A', ''),
(48, 8, 'be motivated, and begin to think about and recognise their own progress and achievements', '', 6, 'A', ''),
(49, 9, 'use a range of body movements, facial expressions, and early vocalisations to show feelings and share information', '', 1, 'A', 'body movement, facial expressions, communicate, speak, feelings'),
(50, 9, 'understand and use non-verbal communication rules, such as turn-taking and making eye contact', '', 2, 'A', 'turn-taking, making eye contact, smiling, laughing, hear, feel, taste, singing'),
(51, 9, 'interpret and respond to non-verbal communication by others', '', 3, 'A', ''),
(52, 9, 'understand and respect that some people will rely on non-verbal communication as their main way of interacting with others', '', 4, 'A', ''),
(53, 9, 'combine non-verbal and verbal communication to get their point across', '', 5, 'A', ''),
(54, 9, 'express themselves creatively and imaginatively using non-verbal communication', '', 6, 'A', ''),
(55, 10, 'interact with other children and adults by listening, discussing and taking turns in conversation', '', 1, 'A', 'listening, discussing and taking turns in conversation, speaking, fast, slow, high, low, loud, soft'),
(56, 10, 'explore sound, pattern, rhythm, and repetition in language', '', 2, 'A', 'sound, pattern, rhythm, repetition, language'),
(57, 10, 'use an expanding vocabulary of words and phrases, and show a growing understanding of syntax and meaning', '', 3, 'A', ''),
(58, 10, 'use language with confidence and competence for giving and receiving information, asking questions, requesting, refusing, negotiating, problem-solving, imagining and recreating roles and situations, and clarifying thinking, ideas and feelings', '', 4, 'A', 'receiving information, asking questions, requesting, refusing, negotiating, problem-solving, imagining recreating roles and situations, clarifying thinking, ideas, feelings'),
(59, 10, 'become proficient users of at least one language and have an awareness and appreciation of other languages', '', 5, 'A', ''),
(60, 10, 'be positive about their home language, and know that they can use different languages to communicate with different people and in different situations.', '', 6, 'A', ''),
(61, 11, 'use language to interpret experiences, to solve problems, and to clarify thinking, ideas and feelings', '', 1, 'A', ''),
(62, 11, 'use books and ICT for fun, to gain information and broaden their understanding of the world', '', 2, 'A', 'read, stories, story, discuss, think, predict, suggest, recount, speculate, next, happen, story, stories, poems, song, rhyme, library'),
(63, 11, 'build awareness of the variety of symbols (pictures, print, numbers) used to communicate, and understand that these can be read by others', '', 3, 'A', 'read, stories, story, discuss, think, predict, suggest, recount, speculate, next, happen, story, stories, poems, song, rhyme, library'),
(64, 11, 'become familiar with and use a variety of print in an enjoyable and meaningful way', '', 4, 'A', 'mark-making, write, crayons, painting, printing, one, two, three, four, five, six, seven, eight, nine, ten, playdough to, sharing, finger games, rhymes, up, down, in, out, behind, in front, over, under, canners, digital camera, interactive, whiteboard, camera'),
(65, 11, 'have opportunities to use a variety of mark-making materials and implements in an enjoyable and meaningful way', '', 5, 'A', ''),
(66, 11, 'develop counting skills, and a growing understanding of the meaning and use of numbers and mathematical language in an enjoyable and meaningful way', '', 6, 'A', 'count, math, shop windows, house numbers, car registration plates, labels, calendars, phone numbers, weighing scales, clocks, stop-watches, phones, thermometers, price lists, money, pizza delivery, shopping list'),
(67, 12, 'share their feelings, thoughts and ideas by story-telling, making art, moving to music, role-playing, problem-solving, and responding to these experiences', '', 1, 'A', 'share feelings, feeling, thoughts, ideas, story-telling, art, music, role-playing, problem-solving, responding, dance, jump, group work, circle time, story time'),
(68, 12, 'express themselves through the visual arts using skills such as cutting, drawing, gluing, sticking, painting, building, printing, sculpting, and sewing', '', 2, 'A', 'cutting, drawing, gluing, sticking, painting, building, printing, sculpting, and sewing, art, painting, drawing'),
(69, 12, 'listen to and respond to a variety of types of music, sing songs and make music using instruments', '', 3, 'A', 'music, sing, songs, instruments'),
(70, 12, 'use language to imagine and recreate roles and experiences', '', 4, 'A', 'pretending, pretend play, role play'),
(71, 12, 'respond to and create literacy experiences through story, poetry, song, and drama', '', 5, 'A', 'scribing, reading, farmers market, the newsagent’s shop, menus in the cafe, writing, written'),
(72, 12, 'show confidence in trying out new things, taking risks, and thinking creatively', '', 6, 'A', 'trying, new, things taking risks, thinking creatively, manipulate materials, cookery, drama, cooking, baking,'),
(73, 13, 'engage, explore and experiment in their environment and use new physical skills including skills to manipulate objects and materials', '', 1, 'A', 'running, jumping, playing, moving, gross motor skills, sand, water, materials, colour, texture, size, walking in the woods, feeding the ducks in the river, farm, splashing, puddles, wellies, raingear, cycling, hopping, skipping, bouncing, obstacle course, basketball, tag-rugby, parachute game, beanbags, balls, bats, hula hoops, racquets, skipping ropes, slides, climbing frames, jigsaws, jigsaw, beads, sewing, building, construction, rain, animal sounds, traffic sounds, open farm, caring for animals, risky play, taking risks, risk, books, book, read, sharing, classifies things in the garden that are rough/smooth, thick, thin, high, low, and in dark, light shades, colours, flavours, flavour texture, vegetables, sweet, sour, crunchy, soft'),
(74, 13, 'demonstrate a growing understanding of themselves and others in their community', '', 2, 'A', 'community, volunteer, chocolate, melting, hardening, milk souring, dough rising, pastry crusting, weather, baking,'),
(75, 13, 'develop an understanding of change as part of their lives', '', 3, 'A', ''),
(76, 13, 'learn about the natural environment and its features, materials, animals, and plants, and their own responsibility as carers', '', 4, 'A', 'nature, leaves, shells, stones, wooden items, wool, felt splashing water, dog, cat, spider, animal, fish, rabbit, gerbil, pine cones, seashells, scraps'),
(77, 13, 'develop a sense of time, shape, space, and place', '', 5, 'A', 'shape, running, jumping, hopping, pedalling a tricycle, blocks, puzzles, thread reels, jar lids, tins, corks, strong cardboard tubes, large buttons, pine cones, seashells, scraps of material'),
(78, 13, 'come to understand concepts such as matching, comparing, ordering, sorting, size, weight, height, length, capacity, and money in an enjoyable and meaningful way', '', 6, 'A', 'play money, laundrette, supermarket, pretend area, pretend play, wooden floor, watches, clock, blowing, whistle, cups, cup, jug, kettle, bottle, pot, saucepan, sieve, spoon, spoons pouring, emptying, filling, transferring, comparing, pouring, sort, match, shape-sorters, songs, rhymes, finger-play, story, stories, weight, height, length, money, capacity comparing, ordering, float, sink'),
(79, 14, 'recognise patterns and make connections and associations between new learning and what they already know', '', 1, 'A', 'pattern, patterns, recognize patterns'),
(80, 14, 'gather and use information from different sources using their increasing cognitive, physical and social skills', '', 2, 'A', ''),
(81, 14, 'use their experience and information to explore and develop working theories about how the world works, and think about how and why they learn things', '', 3, 'A', 'interacting with others, playing, investigating, questioning, forming, testing and refining, ideas, idea'),
(82, 14, 'demonstrate their ability to reason, negotiate and think logically', '', 4, 'A', ''),
(83, 14, 'collaborate with others to share interests and to solve problems confidently', '', 5, 'A', ''),
(84, 14, 'use their creativity and imagination to think of new ways to solve problems', '', 6, 'A', ''),
(85, 15, 'make marks and use drawing, painting and model-making to record objects, events and ideas', '', 1, 'A', 'mini-beasts, colour, racing cars, princesses, fairies, flying creatures, buses, musical instruments'),
(86, 15, 'become familiar with and associate symbols (pictures, numbers, letters, and words) with the things they represent', '', 2, 'A', ''),
(87, 15, 'build awareness of the variety of symbols (pictures, print, numbers) used to communicate, and use these in an enjoyable and meaningful way leading to early reading and writing', '', 3, 'A', 'reading, read, write, writing, picture, print, symbols, numbers'),
(88, 15, 'express feelings, thoughts and ideas through improvising, moving, playing, talking, writing, story-telling, music and art', '', 4, 'A', 'improvising, moving, playing, talking, writing, story-telling, music, art'),
(89, 15, 'use letters, words, sentences, numbers, signs, pictures, colour, and shapes to give and record information, to describe and to make sense of their own and others experiences', '', 5, 'A', ''),
(90, 15, 'use books and ICT (software and the internet) for enjoyment and as a source of information', '', 6, 'A', 'books, computer, internet'),
(91, 16, 'demonstrate growing confidence in being able to do things for themselves', '', 1, 'A', 'independent, curious, confident, own'),
(92, 16, 'address challenges and cope with frustrations', '', 2, 'A', 'challenge, challenges'),
(93, 16, 'make decisions and take increasing responsibility for their own learning', '', 3, 'A', ''),
(94, 16, 'feel confident that their ideas, thoughts and questions will be listened to and taken seriously', '', 4, 'A', ''),
(95, 16, 'develop higher-order thinking skills such as problem-solving, predicting, analysing, questioning, and justifying', '', 5, 'A', 'problem-solving, predicting, analysing, questioning, justifying'),
(96, 16, 'act on their curiosity, take risks and be open to new ideas and uncertainty', '', 6, 'A', 'risk, curious, competent, confident'),
(97, 17, 'Ensuring that each child’s rights are met requires that she/he is enabled to exercise choice and to use initiative as an active participant and partner in her/his own development and learning.', '', 1, 'A', NULL),
(98, 18, 'Enriching environments, both indoor and outdoor (including materials and equipment) are well-maintained, safe, available, accessible, adaptable, developmentally appropriate, and offer a variety of challenging and stimulating experiences.', '', 1, 'A', NULL),
(99, 19, 'Valuing and involving parents and families requires a proactive partnership approach evidenced by a range of clearly stated, accessible and implemented processes, policies and procedures.', '', 1, 'A', NULL),
(100, 20, 'Ensuring inclusive decision-making requires consultation that promotes participation and seeks out, listens to and acts upon the views and opinions of children, parents and staff, and other stakeholders, as appropriate.', '', 1, 'A', NULL),
(101, 21, 'Fostering constructive interactions (child/child, child/adult and adult/adult) requires explicit policies, procedures and practice that emphasise the value of process and are based on mutual respect, equal partnership and sensitivity.', '', 1, 'A', NULL),
(102, 22, 'Promoting play requires that each child has ample time to engage in freely available and accessible, developmentally appropriate and well-resourced opportunities for exploration, creativity and â€œmeaning makingâ€ in the company of other children, with participating and supportive adults and alone, where appropriate.', '', 1, 'A', NULL),
(103, 23, 'Encouraging each child’s holistic development and learning requires the implementation of a verifiable, broad-based, documented and flexible curriculum or programme.', '', 1, 'A', NULL),
(104, 24, 'Enriching and informing all aspects of practice within the setting requires cycles of observation, planning, action and evaluation, undertaken on a regular basis.', '', 1, 'A', NULL),
(105, 25, 'Promoting the health and welfare of the child requires protection from harm, provision of nutritious food, appropriate opportunities for rest, and secure relationships characterised by trust and respect.', '', 1, 'A', NULL),
(106, 26, 'Organising and managing resources effectively requires an agreed written philosophy, supported by clearly communicated policies and procedures to guide and determine practice.', '', 1, 'A', NULL),
(107, 27, 'Practising in a professional manner requires that individuals have skills, knowledge, values and attitudes appropriate to their role and responsibility within the setting. In addition, it requires regular reflection upon practice and engagement in supported, ongoing professional development.', '', 1, 'A', NULL),
(108, 28, 'Communicating effectively in the best interests of the child requires policies, procedures and actions that promote the proactive sharing of knowledge and information among appropriate stakeholders, with respect and confidentiality.', '', 1, 'A', NULL),
(109, 29, 'Ensuring continuity of experiences for children requires policies, procedures and practice that promote sensitive management of transitions, consistency in key relationships, liaison within and between settings, the keeping and transfer of relevant information (with parental consent), and the close involvement of parents and, where appropriate, relevant professionals.', '', 1, 'A', NULL),
(110, 30, 'Promoting positive identities and a strong sense of belonging requires clearly defined policies, procedures and practice that empower every child and adult to develop a confident self- and group identity, and to have a positive understanding and regard for the identity and rights of others.', '', 1, 'A', NULL),
(111, 31, 'Being compliant requires that all relevant regulations and legislative requirements are met or exceeded.', '', 1, 'A', NULL),
(112, 32, 'Promoting community involvement requires the establishment of networks and connections evidenced by policies, procedures and actions which extend and support all adult’s and children’s engagement with the wider community.', '', 1, 'A', NULL),
(113, 33, 'Enjoys the company of others and seeks contact with others from birth.', '', 1, 'A', NULL),
(114, 33, 'Gazes at faces and copies facial movements. e.g. sticking out tongue, opening mouth and widening eyes.', '', 2, 'A', NULL),
(115, 33, 'Responds when talked to, for example, moves arms and legs, changes facial expression, moves body and makes mouth movements.', '', 3, 'A', NULL),
(116, 33, 'Recognises and is most responsive to main carer’s voice: face brightens, activity increases when familiar carer appears.', '', 4, 'A', NULL),
(117, 33, 'Responds to what carer is paying attention to, e.g. following their gaze.', '', 5, 'A', NULL),
(118, 33, 'Likes cuddles and being held: calms, snuggles in, smiles, gazes at carer’s face or strokes carer’s skin.', '', 6, 'A', NULL),
(119, 34, 'Laughs and gurgles, e.g. shows pleasure at being tickled and other physical interactions.', '', 1, 'A', NULL),
(120, 34, 'Uses voice, gesture, eye contact and facial expression to make contact with people and keep their attention.', '', 2, 'A', NULL),
(121, 35, 'Turns toward a familiar sound then locates range of sounds with accuracy.', '', 1, 'A', NULL),
(122, 35, 'Listens to, distinguishes and responds to intonations and sounds of voices.', '', 2, 'A', NULL),
(123, 35, 'Reacts in interaction with others by smiling, looking and moving.', '', 3, 'A', NULL),
(124, 35, 'Quietens or alerts to the sound of speech.', '', 4, 'A', NULL),
(125, 35, 'Looks intently at a person talking, but stops responding if speaker turns away.', '', 5, 'A', NULL),
(126, 35, 'Listens to familiar sounds, words, or finger plays.', '', 6, 'A', NULL),
(127, 35, 'Fleeting Attention – not under child’s control, new stimuli takes whole attention.', '', 7, 'A', NULL),
(128, 36, 'Is comforted by touch and people’s faces and voices.', '', 1, 'A', NULL),
(129, 36, 'Seeks physical and emotional comfort by snuggling in to trusted adults.', '', 2, 'A', NULL),
(130, 36, 'Calms from being upset when held, rocked, spoken or sung to with soothing voice.', '', 3, 'A', NULL),
(131, 36, 'Shows a range of emotions such as pleasure, fear and excitement.', '', 4, 'A', NULL),
(132, 36, 'Reacts emotionally to other people’s emotions, e.g. smiles when smiled at and becomes distressed if hears another child crying.', '', 5, 'A', NULL),
(133, 37, 'Stops and looks when hears own name.', '', 1, 'A', NULL),
(134, 37, 'Starts to understand contextual clues, e.g. familiar gestures, words and sounds.', '', 2, 'A', NULL),
(135, 38, 'Communicates needs and feelings in a variety of ways including crying, gurgling, babbling and squealing.', '', 1, 'A', NULL),
(136, 38, 'Makes own sounds in response when talked to by familiar adults.', '', 2, 'A', NULL),
(137, 38, 'Lifts arms in anticipation of being picked up.', '', 3, 'A', NULL),
(138, 38, 'Practises and gradually develops speech sounds (babbling) to communicate with adults; says sounds like ‘baba, nono, gogo’.', '', 4, 'A', NULL),
(139, 39, 'Turns toward a familiar sound then locates range of sounds with accuracy.', '', 1, 'A', NULL),
(140, 39, 'Listens to, distinguishes and responds to intonations and sounds of voices.', '', 2, 'A', NULL),
(141, 39, 'Reacts in interaction with others by smiling, looking and moving.', '', 3, 'A', NULL),
(142, 39, 'Quietens or alerts to the sound of speech.', '', 4, 'A', NULL),
(143, 39, 'Looks intently at a person talking, but stops responding if speaker turns away.', '', 5, 'A', NULL),
(144, 39, 'Listens to familiar sounds, words, or finger plays.', '', 6, 'A', NULL),
(145, 39, 'Fleeting Attention – not under child’s control, new stimuli takes whole attention.', '', 7, 'A', NULL),
(146, 40, 'Turns head in response to sounds and sights.', '', 1, 'A', NULL),
(147, 40, 'Gradually develops ability to hold up own head.', '', 2, 'A', NULL),
(148, 40, 'Makes movements with arms and legs which gradually become more controlled.', '', 3, 'A', NULL),
(149, 40, 'Rolls over from front to back, from back to front.', '', 4, 'A', NULL),
(150, 40, 'When lying on tummy becomes able to lift first head and then chest, supporting self with forearms and then straight arms.', '', 5, 'A', NULL),
(151, 40, 'Watches and explores hands and feet, e.g. when lying on back lifts legs into vertical position and grasps feet.', '', 6, 'A', NULL),
(152, 40, 'Reaches out for, touches and begins to hold objects.', '', 7, 'A', NULL),
(153, 40, 'Explores objects with mouth, often picking up an object and holding it to the mouth.', '', 8, 'A', NULL),
(154, 41, 'Responds to and thrives on warm, sensitive physical contact and care.', '', 1, 'A', NULL),
(155, 41, 'Expresses discomfort, hunger or thirst.', '', 2, 'A', NULL),
(156, 41, 'Anticipates food routines with interest.', '', 3, 'A', NULL),
(157, 42, 'Enjoys looking at books and other printed material with familiar people.', '', 1, 'A', NULL),
(158, 43, 'Notices changes in number of objects/images or sounds in group of up to 3.', '', 1, 'A', NULL),
(159, 44, 'Has opportunities to develop their sensory awareness, opportunities to observe objects and their movements, and to play and explore.', '', 1, 'A', NULL),
(160, 45, 'Has opportunities to build strong attachments and relationships within the setting.', '', 1, 'A', NULL),
(161, 46, 'Moves eyes, then head, to follow moving objects.', '', 1, 'A', NULL),
(162, 46, 'Reacts with abrupt change when a face or object suddenly disappears from view.', '', 2, 'A', NULL),
(163, 46, 'Looks around a room with interest; visually scans environment for novel, interesting objects and events.', '', 3, 'A', NULL),
(164, 46, 'Smiles with pleasure at recognisable playthings.', '', 4, 'A', NULL),
(165, 46, 'Repeats actions that have an effect, e.g. kicking or hitting a mobile or shaking a rattle.', '', 5, 'A', NULL),
(166, 47, 'The beginnings of understanding technology lie in babies exploring and making sense of objects and how they behave. The child has opportunities to explore, makes sense of objects and how they behave in their environment.', '', 1, 'A', NULL),
(167, 50, 'Seeks to gain attention in a variety of ways, drawing others into social interaction.', '', 1, 'A', NULL),
(168, 50, 'Builds relationships with special people.', '', 2, 'A', NULL),
(169, 50, 'Is wary of unfamiliar people.', '', 3, 'A', NULL),
(170, 50, 'Interacts with others and explores new situations when supported by familiar person.', '', 4, 'A', NULL),
(171, 50, 'Shows interest in the activities of others and responds differently to children and adults, e.g. may be more interested in watching children than adults or may pay more attention when children talk to them.', '', 5, 'A', NULL),
(172, 51, 'Enjoys finding own nose, eyes or tummy as part of naming games.', '', 1, 'A', NULL),
(173, 51, 'Learns that own voice and actions have effects on others.', '', 2, 'A', NULL),
(174, 51, 'Uses pointing with eye gaze to make requests, and to share an interest.', '', 3, 'A', NULL),
(175, 51, 'Engages other person to help achieve a goal, e.g. to get an object out of reach.', '', 4, 'A', NULL),
(176, 52, 'Uses familiar adult to share feelings such as excitement or pleasure, and for ‘emotional refuelling’ when feeling tired, stressed or frustrated.', '', 1, 'A', NULL),
(177, 52, 'Growing ability to soothe themselves, and may like to use a comfort object.', '', 2, 'A', NULL),
(178, 52, 'Cooperates with caregiving experiences, e.g. dressing.', '', 3, 'A', NULL),
(179, 52, 'Beginning to understand ‘yes’, ‘no’ and some boundaries.', '', 4, 'A', NULL),
(180, 53, 'Moves whole bodies to sounds they enjoy, such as music or a regular beat.', '', 1, 'A', NULL),
(181, 53, 'Has a strong exploratory impulse.', '', 2, 'A', NULL),
(182, 53, 'Concentrates intently on an object or activity of own choosing for short periods.', '', 3, 'A', NULL),
(183, 53, 'Pays attention to dominant stimulus â€“ easily distracted by noises or other people talking.', '', 4, 'A', NULL),
(184, 54, 'Developing the ability to follow others’ body language, including pointing and gesture.', '', 1, 'A', NULL),
(185, 54, 'Responds to the different things said when in a familiar context with a special person (e.g. ‘Where’s Mummy?’, ‘Where’s your nose?’).', '', 2, 'A', NULL),
(186, 54, 'Understanding of single words in context is developing, e.g. ‘cup’, ‘milk’, ‘daddy’.', '', 3, 'A', NULL),
(187, 55, 'Uses sounds in play, e.g. ‘brrrm’ for toy car.', '', 1, 'A', NULL),
(188, 55, 'Uses single words.', '', 2, 'A', NULL),
(189, 55, 'Frequently imitates words and sounds.', '', 3, 'A', NULL),
(190, 55, 'Enjoys babbling and increasingly experiments with using sounds and words to communicate for a range of purposes (e.g. teddy, more, no, bye-bye.)', '', 4, 'A', NULL),
(191, 55, 'Uses pointing with eye gaze to make requests, and to share an interest.', '', 5, 'A', NULL),
(192, 55, 'Creates personal words as they begin to develop language.', '', 6, 'A', NULL),
(193, 56, 'Sits unsupported on the floor.', '', 1, 'A', NULL),
(194, 56, 'When sitting, can lean forward to pick up small toys.', '', 2, 'A', NULL),
(195, 56, 'Pulls to standing, holding on to furniture or person for support.', '', 3, 'A', NULL),
(196, 56, 'Crawls, bottom shuffles or rolls continuously to move around.', '', 4, 'A', NULL),
(197, 56, 'Walks around furniture lifting one foot and stepping sideways (cruising), and walks with one or both hands held by adult.', '', 5, 'A', NULL),
(198, 56, 'Takes first few steps independently.', '', 6, 'A', NULL),
(199, 56, 'Passes toys from one hand to the other.', '', 7, 'A', NULL),
(200, 56, 'Holds an object in each hand and brings them together in the middle, e.g. holds two blocks and bangs them together.', '', 8, 'A', NULL),
(201, 56, 'Picks up small objects between thumb and fingers.', '', 9, 'A', NULL),
(202, 56, 'Enjoys the sensory experience of making marks in damp sand, paste or paint.', '', 10, 'A', NULL),
(203, 56, 'Holds pen or crayon using a whole hand (palmar) grasp and makes random marks with different strokes.', '', 11, 'A', NULL),
(204, 57, 'Opens mouth for spoon.', '', 1, 'A', NULL),
(205, 57, 'Holds own bottle or cup.', '', 2, 'A', NULL),
(206, 57, 'Grasps finger foods and brings them to mouth.', '', 3, 'A', NULL),
(207, 57, 'Attempts to use spoon: can guide towards mouth but food often falls off.', '', 4, 'A', NULL),
(208, 57, 'Can actively cooperate with nappy changing (lies still, helps hold legs up).', '', 5, 'A', NULL),
(209, 57, 'Starts to communicate urination, bowel movement.', '', 6, 'A', NULL),
(210, 58, 'Handles books and printed material with interest.', '', 1, 'A', NULL),
(211, 59, 'Develops an awareness of number names through their enjoyment of action rhymes and songs that relate to their experience of numbers.', '', 1, 'A', NULL),
(212, 59, 'Has some understanding that things exist, even when out of sight.', '', 2, 'A', NULL),
(213, 60, 'Recognises big things and small things in meaningful contexts.', '', 1, 'A', NULL),
(214, 60, 'Gets to know and enjoy daily routines, such as getting-up time, mealtimes, nappy time, and bedtime.', '', 2, 'A', NULL),
(215, 61, 'Closely observes what animals, people and vehicles do. ', '', 1, 'A', NULL),
(216, 61, 'Watches toy being hidden and tries to find it.', '', 2, 'A', NULL),
(217, 61, 'Looks for dropped objects.', '', 3, 'A', NULL),
(218, 61, 'Becomes absorbed in combining objects, e.g. banging two objects or placing objects into containers.', '', 4, 'A', NULL),
(219, 61, 'Knows things are used in different ways, e.g. a ball for rolling or throwing, a toy car for pushing.', '', 5, 'A', NULL),
(220, 62, 'Explores and experiments with a range of media through sensory exploration, and using whole body.', '', 1, 'A', NULL),
(221, 62, 'Move their whole bodies to sounds they enjoy, such as music or a regular beat.', '', 2, 'A', NULL),
(222, 62, 'Imitates and improvises actions they have observed, e.g. clapping or waving.', '', 3, 'A', NULL),
(223, 62, 'Begins to move to music, listen to or join in rhymes or songs.', '', 4, 'A', NULL),
(224, 62, 'Notices and is interested in the effects of making movements which leave marks.', '', 5, 'A', NULL),
(225, 63, 'Plays alongside others.', '', 1, 'A', NULL),
(226, 63, 'Uses a familiar adult as a secure base from which to explore independently in new environments, e.g. ventures away to play and interact with others, but returns for a cuddle or reassurance if becomes anxious.', '', 2, 'A', NULL),
(227, 63, 'Plays cooperatively with a familiar adult, e.g. rolling a ball back and forth.', '', 3, 'A', NULL),
(228, 64, 'Explores new toys and environments, but ‘checks in’ regularly with familiar adult as and when needed.', '', 1, 'A', NULL),
(229, 64, 'Gradually able to engage in pretend play with toys (supports child to understand their own thinking may be different from others).', '', 2, 'A', NULL),
(230, 64, 'Demonstrates sense of self as an individual, e.g. wants to do things independently, says â€œNoâ€ to adult.', '', 3, 'A', NULL),
(231, 65, 'Is aware of others’ feelings, for example, looks concerned if hears crying or looks excited if hears a familiar happy voice.', '', 1, 'A', NULL),
(232, 65, 'Growing sense of will and determination may result in feelings of anger and frustration which are difficult to handle, e.g. may have tantrums.', '', 2, 'A', NULL),
(233, 65, 'Responds to a few appropriate boundaries, with encouragement and support.', '', 3, 'A', NULL),
(234, 65, 'Begins to learn that some things are theirs, some things are shared, and some things belong to other people.', '', 4, 'A', NULL),
(235, 66, 'Listens to and enjoys rhythmic patterns in rhymes and stories.', '', 1, 'A', NULL),
(236, 66, 'Enjoys rhymes and demonstrates listening by trying to join in with actions or vocalisations.', '', 2, 'A', NULL),
(237, 66, 'Rigid attention â€“ may appear not to hear.', '', 3, 'A', NULL),
(238, 67, 'Selects familiar objects by name and will go and find objects when asked, or identify objects from a group.', '', 1, 'A', NULL),
(239, 67, 'Understands simple sentences (e.g. ‘Throw the ball.’)', '', 2, 'A', NULL),
(240, 68, 'Copies familiar expressions, e.g. ‘Oh dear’, ‘All gone’.', '', 1, 'A', NULL),
(241, 68, 'Beginning to put two words together (e.g. ‘want ball’, ‘more juice’).', '', 2, 'A', NULL),
(242, 68, 'Uses different types of everyday words (nouns, verbs and adjectives, e.g. banana, go, sleep, hot).', '', 3, 'A', NULL),
(243, 68, 'Beginning to ask simple questions.', '', 4, 'A', NULL),
(244, 68, 'Beginning to talk about people and things that are not present.', '', 5, 'A', NULL),
(245, 69, 'Walks upstairs holding hand of adult.', '', 1, 'A', NULL),
(246, 69, 'Comes downstairs backwards on knees (crawling).', '', 2, 'A', NULL),
(247, 69, 'Beginning to balance blocks to build a small tower.', '', 3, 'A', NULL),
(248, 69, 'Makes connections between their movement and the marks they make.', '', 4, 'A', NULL),
(249, 70, 'Develops own likes and dislikes in food and drink.', '', 1, 'A', NULL),
(250, 70, 'Willing to try new food textures and tastes.', '', 2, 'A', NULL),
(251, 70, 'Holds cup with both hands and drinks without much spilling.', '', 3, 'A', NULL),
(252, 70, 'Clearly communicates wet or soiled nappy or pants.', '', 4, 'A', NULL),
(253, 70, 'Shows some awareness of bladder and bowel urges.', '', 5, 'A', NULL),
(254, 70, 'Shows awareness of what a potty or toilet is used for.', '', 6, 'A', NULL),
(255, 70, 'Shows a desire to help with dressing/undressing and hygiene routines.', '', 7, 'A', NULL),
(256, 71, 'Interested in books and rhymes and may have favourites', '', 1, 'A', NULL),
(257, 72, 'Knows that things exist, even when out of sight.', '', 1, 'A', NULL),
(258, 72, 'Beginning to organise and categorise objects, e.g. putting all the teddy bears together or teddies and cars in separate piles.', '', 2, 'A', NULL),
(259, 72, 'Says some counting words randomly.', '', 3, 'A', NULL),
(260, 73, 'Attempts, sometimes successfully, to fit shapes into spaces on inset boards or jigsaw puzzles.', '', 1, 'A', NULL),
(261, 73, 'Uses blocks to create their own simple structures and arrangements.', '', 2, 'A', NULL),
(262, 73, 'Enjoys filling and emptying containers.', '', 3, 'A', NULL),
(263, 73, 'Associates a sequence of actions with daily routines.', '', 4, 'A', NULL),
(264, 73, 'Beginning to understand that things might happen ‘now’.', '', 5, 'A', NULL),
(265, 74, 'Is curious about people and shows interest in stories about themselves and their family.', '', 1, 'A', NULL),
(266, 74, 'Enjoys pictures and stories about themselves, their families and other people.', '', 2, 'A', NULL),
(267, 75, 'Explores objects by linking together different approaches: shaking, hitting, looking, feeling, tasting, mouthing, pulling, turning and poking.', '', 1, 'A', NULL),
(268, 75, 'Remembers where objects belong.', '', 2, 'A', NULL),
(269, 75, 'Matches parts of objects that fit together, e.g. puts lid on teapot.', '', 3, 'A', NULL),
(270, 76, 'Anticipates repeated sounds, sights and actions, e.g. when an adult demonstrates an action toy several times.', '', 1, 'A', NULL),
(271, 76, 'Shows interest in toys with buttons, flaps and simple mechanisms and beginning to learn to operate them.', '', 2, 'A', NULL),
(272, 77, 'Explores and experiments with a range of media through sensory exploration, and using whole body.', '', 1, 'A', NULL),
(273, 77, 'Move their whole bodies to sounds they enjoy, such as music or a regular beat.', '', 2, 'A', NULL),
(274, 77, 'Imitates and improvises actions they have observed, e.g. clapping or waving.', '', 3, 'A', NULL),
(275, 77, 'Begins to move to music, listen to or join in rhymes or songs.', '', 4, 'A', NULL),
(276, 77, 'Notices and is interested in the effects of making movements which leave marks.', '', 5, 'A', NULL),
(277, 78, 'Expresses self through physical action and sound.', '', 1, 'A', NULL),
(278, 78, 'Pretends that one object represents another, especially when objects have characteristics in common.', '', 2, 'A', NULL),
(279, 79, 'Interested in others’ play and starting to join in.', '', 1, 'A', NULL),
(280, 79, 'Seeks out others to share experiences.', '', 2, 'A', NULL),
(281, 79, 'Shows affection and concern for people who are special to them.', '', 3, 'A', NULL),
(282, 79, 'May form a special friendship with another child.', '', 4, 'A', NULL),
(283, 80, 'Separates from main carer with support and encouragement from a familiar adult.', '', 1, 'A', NULL),
(284, 80, 'Expresses own preferences and interests.', '', 2, 'A', NULL),
(285, 81, 'Seeks comfort from familiar adults when needed.', '', 1, 'A', NULL),
(286, 81, 'Can express their own feelings such as sad, happy, cross, scared, worried.', '', 2, 'A', NULL),
(287, 81, 'Responds to the feelings and wishes of others.', '', 3, 'A', NULL),
(288, 81, 'Aware that some actions can hurt or harm others.', '', 4, 'A', NULL),
(289, 81, 'Tries to help or give comfort when others are distressed.', '', 5, 'A', NULL),
(290, 81, 'Shows understanding and cooperates with some boundaries and routines.', '', 6, 'A', NULL),
(291, 81, 'Can inhibit own actions/behaviours, e.g. stop themselves from doing something they shouldn’t do.', '', 7, 'A', NULL),
(292, 81, 'Growing ability to distract self when upset, e.g. by engaging in a new play activity.', '', 8, 'A', NULL),
(293, 82, 'Listens with interest to the noises adults make when they read stories.', '', 1, 'A', NULL),
(294, 82, 'Recognises and responds to many familiar sounds, e.g. turning to a knock on the door, looking at or going to the door.', '', 2, 'A', NULL),
(295, 82, 'Shows interest in play with sounds, songs and rhymes.', '', 3, 'A', NULL),
(296, 82, 'Single channelled attention. Can shift to a different task if attention fully obtained – using child’s name helps focus.', '', 4, 'A', NULL),
(297, 83, 'Identifies action words by pointing to the right picture, e.g., “Who’s jumping?”', '', 1, 'A', NULL),
(298, 83, 'Understands more complex sentences, e.g. ‘Put your toys away and then we’ll read a book.’', '', 2, 'A', NULL),
(299, 83, 'Understands ‘who’, ‘what’, ‘where’ in simple questions (e.g. Who’s that/can? What’s that? Where is.?).', '', 3, 'A', NULL),
(300, 83, 'Developing understanding of simple concepts (e.g. big/little)', '', 4, 'A', NULL),
(301, 84, 'Uses language as a powerful means of widening contacts, sharing feelings, experiences and thoughts.', '', 1, 'A', NULL),
(302, 84, 'Holds a conversation, jumping from topic to topic.', '', 2, 'A', NULL),
(303, 84, 'Learns new words very rapidly and is able to use them in communicating.', '', 3, 'A', NULL),
(304, 84, 'Uses gestures, sometimes with limited talk, e.g. reaches toward toy, saying ‘I have it’.', '', 4, 'A', NULL),
(305, 84, 'Uses a variety of questions (e.g. what, where, who).', '', 5, 'A', NULL),
(306, 84, 'Uses simple sentences (e.g.’ Mummy gonna work.’).', '', 6, 'A', NULL),
(307, 84, 'Beginning to use word endings (e.g. going, cats).', '', 7, 'A', NULL),
(308, 85, 'Runs safely on whole foot.', '', 1, 'A', NULL),
(309, 85, 'Squats with steadiness to rest or play with object on the ground, and rises to feet without using hands.', '', 2, 'A', NULL),
(310, 85, 'Climbs confidently and is beginning to pull themselves up on nursery play climbing equipment.', '', 3, 'A', NULL),
(311, 85, 'Can kick a large ball.', '', 4, 'A', NULL),
(312, 85, 'Turns pages in a book, sometimes several at once.', '', 5, 'A', NULL),
(313, 85, 'Shows control in holding and using jugs to pour, hammers, books and mark-making tools.', '', 6, 'A', NULL),
(314, 85, 'Beginning to use three fingers (tripod grip) to hold writing tools.', '', 7, 'A', NULL),
(315, 85, 'Imitates drawing simple shapes such as circles and lines.', '', 8, 'A', NULL),
(316, 85, 'Walks upstairs or downstairs holding onto a rail two feet to a step.', '', 9, 'A', NULL),
(317, 85, 'May be beginning to show preference for dominant hand.', '', 10, 'A', NULL),
(318, 86, 'Feeds self competently with spoon.', '', 1, 'A', NULL),
(319, 86, 'Drinks well without spilling.', '', 2, 'A', NULL),
(320, 86, 'Clearly communicates their need for potty or toilet.', '', 3, 'A', NULL),
(321, 86, 'Beginning to recognise danger and seeks support of significant adults for help.', '', 4, 'A', NULL),
(322, 86, 'Helps with clothing, e.g. puts on hat, unzips zipper on jacket, takes off unbuttoned shirt.', '', 5, 'A', NULL),
(323, 86, 'Beginning to be independent in self-care, but still often needs adult support.', '', 6, 'A', NULL),
(324, 87, 'Has some favourite stories, rhymes, songs, poems or jingles.', '', 1, 'A', NULL),
(325, 87, 'Repeats words or phrases from familiar stories.', '', 2, 'A', NULL),
(326, 87, 'Fills in the missing word or phrase in a known rhyme, story or game, e.g. ‘Humpty Dumpty sat on a …’.', '', 3, 'A', NULL),
(327, 88, 'Distinguishes between the different marks they make.', '', 1, 'A', NULL),
(328, 89, 'Selects a small number of objects from a group when asked, for example, ‘please give me one’, ‘please give me two’.', '', 1, 'A', NULL),
(329, 89, 'Recites some number names in sequence.', '', 2, 'A', NULL),
(330, 89, 'Creates and experiments with symbols and marks representing ideas of number. ', '', 3, 'A', NULL),
(331, 89, 'Begins to make comparisons between quantities.', '', 4, 'A', NULL),
(332, 89, 'Uses some language of quantities, such as ‘more’ and ‘a lot’.', '', 5, 'A', NULL),
(333, 89, 'Knows that a group of things changes in quantity when something is added or taken away.', '', 6, 'A', NULL),
(334, 90, 'Notices simple shapes and patterns in pictures.', '', 1, 'A', NULL),
(335, 90, 'Beginning to categorise objects according to properties such as shape or size.', '', 2, 'A', NULL),
(336, 90, 'Begins to use the language of size.', '', 3, 'A', NULL),
(337, 90, 'Understands some talk about immediate past and future, e.g. ‘before’, ‘later’ or ‘soon’.', '', 4, 'A', NULL),
(338, 90, 'Anticipates specific time-based events such as mealtimes or home time.', '', 5, 'A', NULL),
(339, 91, 'Has a sense of own immediate family and relations.', '', 1, 'A', NULL),
(340, 91, 'In pretend play, imitates everyday actions and events from own family and cultural background, e.g. making and drinking tea.', '', 2, 'A', NULL),
(341, 91, 'Beginning to have their own friends.', '', 3, 'A', NULL),
(342, 91, 'Learns that they have similarities and differences that connect them to, and distinguish them from, others.', '', 4, 'A', NULL),
(343, 92, 'Enjoys playing with small-world models such as a farm, a garage, or a train track.', '', 1, 'A', NULL),
(344, 92, 'Notices detailed features of objects in their environment.', '', 2, 'A', NULL),
(345, 93, 'Seeks to acquire basic skills in turning on and operating some ICT equipment.', '', 1, 'A', NULL),
(346, 93, 'Operates mechanical toys, e.g. turns the knob on a wind-up toy or pulls back on a friction car.', '', 2, 'A', NULL),
(347, 94, 'Joins in singing favourite songs.', '', 1, 'A', NULL),
(348, 94, 'Creates sounds by banging, shaking, tapping or blowing.', '', 2, 'A', NULL),
(349, 94, 'Shows an interest in the way musical instruments sound.', '', 3, 'A', NULL),
(350, 94, 'Experiments with blocks, colours and marks.', '', 4, 'A', NULL),
(351, 95, 'Beginning to use representation to communicate, e.g. drawing a line and saying ‘That’s me.’', '', 1, 'A', NULL),
(352, 95, 'Beginning to make-believe by pretending.', '', 2, 'A', NULL),
(353, 96, 'Can play in a group, extending and elaborating play ideas, e.g. building up a role-play activity with other children.', '', 1, 'A', NULL),
(354, 96, 'Initiates play, offering cues to peers to join them.', '', 2, 'A', NULL),
(355, 96, 'Keeps play going by responding to what others are saying or doing.', '', 3, 'A', NULL),
(356, 96, 'Demonstrates friendly behaviour, initiating conversations and forming good relationships with peers and familiar adults.', '', 4, 'A', NULL),
(357, 97, 'Can select and use activities and resources with help.', '', 1, 'A', NULL),
(358, 97, 'Welcomes and values praise for what they have done.', '', 2, 'A', NULL),
(359, 97, 'Enjoys responsibility of carrying out small tasks.', '', 3, 'A', NULL),
(360, 97, 'Is more outgoing towards unfamiliar people and more confident in new social situations.', '', 4, 'A', NULL),
(361, 97, 'Confident to talk to other children when playing, and will communicate freely about own home and community.', '', 5, 'A', NULL),
(362, 97, 'Shows confidence in asking adults for help.', '', 6, 'A', NULL),
(363, 98, 'Aware of own feelings, and knows that some actions and words can hurt others’ feelings.', '', 1, 'A', NULL),
(364, 98, 'Begins to accept the needs of others and can take turns and share resources, sometimes with support from others.', '', 2, 'A', NULL),
(365, 98, 'Can usually tolerate delay when needs are not immediately met, and understands wishes may not always be met.', '', 3, 'A', NULL),
(366, 98, 'Can usually adapt behaviour to different events, social situations and changes in routine.', '', 4, 'A', NULL),
(367, 99, 'Listens to others one to one or in small groups, when conversation interests them.', '', 1, 'A', NULL),
(368, 99, 'Listens to stories with increasing attention and recall.', '', 2, 'A', NULL),
(369, 99, 'Joins in with repeated refrains and anticipates key events and phrases in rhymes and stories.', '', 3, 'A', NULL),
(370, 99, 'Focusing attention â€“ still listen or do, but can shift own attention.', '', 4, 'A', NULL),
(371, 99, 'Is able to follow directions (if not intently focused on own choice of activity).', '', 5, 'A', NULL),
(372, 100, 'Understands use of objects (e.g. “What do we use to cut things?’)', '', 1, 'A', NULL),
(373, 100, 'Shows understanding of prepositions such as ‘under’, ‘on top’, ‘behind’ by carrying out an action or selecting correct picture.', '', 2, 'A', NULL),
(374, 100, 'Responds to simple instructions, e.g. to get or put away an object.', '', 3, 'A', NULL),
(375, 100, 'Beginning to understand ‘why’ and ‘how’ questions.', '', 4, 'A', NULL),
(376, 101, 'Beginning to use more complex sentences to link thoughts (e.g. using and, because).', '', 1, 'A', NULL),
(377, 101, 'Can retell a simple past event in correct order (e.g. went down slide, hurt finger).', '', 2, 'A', NULL);
INSERT INTO `framework_goals` (`goal_id`, `category_id`, `goal_description`, `goal_help`, `goal_sort`, `goal_status`, `goal_keywords`) VALUES
(378, 101, 'Uses talk to connect ideas, explain what is happening and anticipate what might happen next, recall and relive past experiences.', '', 3, 'A', NULL),
(379, 101, 'Questions why things happen and gives explanations. Asks e.g. who, what, when, how.', '', 4, 'A', NULL),
(380, 101, 'Uses a range of tenses (e.g. play, playing, will play, played).', '', 5, 'A', NULL),
(381, 101, 'Uses intonation, rhythm and phrasing to make the meaning clear to others.', '', 6, 'A', NULL),
(382, 101, 'Uses vocabulary focused on objects and people that are of particular importance to them.', '', 7, 'A', NULL),
(383, 101, 'Builds up vocabulary that reflects the breadth of their experiences.', '', 8, 'A', NULL),
(384, 101, 'Uses talk in pretending that objects stand for something else in play, e,g, ‘This box is my castle.’', '', 9, 'A', NULL),
(385, 102, 'Moves freely and with pleasure and confidence in a range of ways, such as slithering, shuffling, rolling, crawling, walking, running, jumping, skipping, sliding and hopping.', '', 1, 'A', NULL),
(386, 102, 'Mounts stairs, steps or climbing equipment using alternate feet.', '', 2, 'A', NULL),
(387, 102, 'Walks downstairs, two feet to each step while carrying a small object.', '', 3, 'A', NULL),
(388, 102, 'Runs skilfully and negotiates space successfully, adjusting speed or direction to avoid obstacles.', '', 4, 'A', NULL),
(389, 102, 'Can stand momentarily on one foot when shown.', '', 5, 'A', NULL),
(390, 102, 'Can catch a large ball.', '', 6, 'A', NULL),
(391, 102, 'Draws lines and circles using gross motor movements.', '', 7, 'A', NULL),
(392, 102, 'Uses one-handed tools and equipment, e.g. makes snips in paper with child scissors.', '', 8, 'A', NULL),
(393, 102, 'Holds pencil between thumb and two fingers, no longer using whole-hand grasp.', '', 9, 'A', NULL),
(394, 102, 'Holds pencil near point between first two fingers and thumb and uses it with good control.', '', 10, 'A', NULL),
(395, 102, 'Can copy some letters, e.g. letters from their name.', '', 11, 'A', NULL),
(396, 103, 'Can tell adults when hungry or tired or when they want to rest or play.', '', 1, 'A', NULL),
(397, 103, 'Observes the effects of activity on their bodies.', '', 2, 'A', NULL),
(398, 103, 'Understands that equipment and tools have to be used safely.', '', 3, 'A', NULL),
(399, 103, 'Gains more bowel and bladder control and can attend to toileting needs most of the time themselves.', '', 4, 'A', NULL),
(400, 103, 'Can usually manage washing and drying hands.', '', 5, 'A', NULL),
(401, 103, 'Dresses with help, e.g. puts arms into open-fronted coat or shirt when held up, pulls up own trousers, and pulls up zipper once it is fastened at the bottom.', '', 6, 'A', NULL),
(402, 104, 'Enjoys rhyming and rhythmic activities.', '', 1, 'A', NULL),
(403, 104, 'Shows awareness of rhyme and alliteration.', '', 2, 'A', NULL),
(404, 104, 'Recognises rhythm in spoken words.', '', 3, 'A', NULL),
(405, 104, 'Listens to and joins in with stories and poems, one-to-one and also in small groups.', '', 4, 'A', NULL),
(406, 104, 'Joins in with repeated refrains and anticipates key events and phrases in rhymes and stories.', '', 5, 'A', NULL),
(407, 104, 'Beginning to be aware of the way stories are structured.', '', 6, 'A', NULL),
(408, 104, 'Suggests how the story might end.', '', 7, 'A', NULL),
(409, 104, 'Listens to stories with increasing attention and recall.', '', 8, 'A', NULL),
(410, 104, 'Describes main story settings, events and principal characters.', '', 9, 'A', NULL),
(411, 104, 'Shows interest in illustrations and print in books and print in the environment.', '', 10, 'A', NULL),
(412, 104, 'Recognises familiar words and signs such as own name and advertising logos.', '', 11, 'A', NULL),
(413, 104, 'Looks at books independently.', '', 12, 'A', NULL),
(414, 104, 'Handles books carefully.', '', 13, 'A', NULL),
(415, 104, 'Knows information can be relayed in the form of print.', '', 14, 'A', NULL),
(416, 104, 'Holds books the correct way up and turns pages.', '', 15, 'A', NULL),
(417, 104, 'Knows that print carries meaning and, in English, is read from left to right and top to bottom.', '', 16, 'A', NULL),
(418, 105, 'Sometimes gives meaning to marks as they draw and paint.', '', 1, 'A', NULL),
(419, 105, 'Ascribes meanings to marks that they see in different places.', '', 2, 'A', NULL),
(420, 106, 'Uses some number names and number language spontaneously.', '', 1, 'A', NULL),
(421, 106, 'Uses some number names accurately in play.', '', 2, 'A', NULL),
(422, 106, 'Recites numbers in order to 10.', '', 3, 'A', NULL),
(423, 106, 'Knows that numbers identify how many objects are in a set.', '', 4, 'A', NULL),
(424, 106, 'Beginning to represent numbers using fingers, marks on paper or pictures.', '', 5, 'A', NULL),
(425, 106, 'Sometimes matches numeral and quantity correctly.', '', 6, 'A', NULL),
(426, 106, 'Shows curiosity about numbers by offering comments or asking questions.', '', 7, 'A', NULL),
(427, 106, 'Compares two groups of objects, saying when they have the same number.', '', 8, 'A', NULL),
(428, 106, 'Shows an interest in number problems.', '', 9, 'A', NULL),
(429, 106, 'Separates a group of three or four objects in different ways, beginning to recognise that the total is still the same.', '', 10, 'A', NULL),
(430, 106, 'Shows an interest in numerals in the environment.', '', 11, 'A', NULL),
(431, 106, 'Shows an interest in representing numbers.', '', 12, 'A', NULL),
(432, 106, 'Realises not only objects, but anything can be counted, including steps, claps or jumps.', '', 13, 'A', NULL),
(433, 107, 'Shows an interest in shape and space by playing with shapes or making arrangements with objects.', '', 1, 'A', NULL),
(434, 107, 'Shows awareness of similarities of shapes in the environment.', '', 2, 'A', NULL),
(435, 107, 'Uses positional language.', '', 3, 'A', NULL),
(436, 107, 'Shows interest in shape by sustained construction activity or by talking about shapes or arrangements.', '', 4, 'A', NULL),
(437, 107, 'Shows interest in shapes in the environment.', '', 5, 'A', NULL),
(438, 107, 'Uses shapes appropriately for tasks.', '', 6, 'A', NULL),
(439, 107, 'Beginning to talk about the shapes of everyday objects, e.g. ‘round’ and ‘tall’.', '', 7, 'A', NULL),
(440, 108, 'Shows interest in the lives of people who are familiar to them.', '', 1, 'A', NULL),
(441, 108, 'Remembers and talks about significant events in their own experience.', '', 2, 'A', NULL),
(442, 108, 'Recognises and describes special times or events for family or friends.', '', 3, 'A', NULL),
(443, 108, 'Shows interest in different occupations and ways of life.', '', 4, 'A', NULL),
(444, 108, 'Knows some of the things that make them unique, and can talk about some of the similarities and differences in relation to friends or family.', '', 5, 'A', NULL),
(445, 109, 'Comments and asks questions about aspects of their familiar world such as the place where they live or the natural world.', '', 1, 'A', NULL),
(446, 109, 'Can talk about some of the things they have observed such as plants, animals, natural and found objects.', '', 2, 'A', NULL),
(447, 109, 'Talks about why things happen and how things work.', '', 3, 'A', NULL),
(448, 109, 'Developing an understanding of growth, decay and changes over time.', '', 4, 'A', NULL),
(449, 109, 'Shows care and concern for living things and the environment.', '', 5, 'A', NULL),
(450, 110, 'Knows how to operate simple equipment, e.g. turns on CD player and uses remote control.', '', 1, 'A', NULL),
(451, 110, 'Shows an interest in technological toys with knobs or pulleys, or real objects such as cameras or mobile phones.', '', 2, 'A', NULL),
(452, 110, 'Shows skill in making toys work by pressing parts or lifting flaps to achieve effects such as sound, movements or new images.', '', 3, 'A', NULL),
(453, 110, 'Knows that information can be retrieved from computers.', '', 4, 'A', NULL),
(454, 111, 'Enjoys joining in with dancing and ring games.', '', 1, 'A', NULL),
(455, 111, 'Sings a few familiar songs.', '', 2, 'A', NULL),
(456, 111, 'Beginning to move rhythmically.', '', 3, 'A', NULL),
(457, 111, 'Imitates movement in response to music.', '', 4, 'A', NULL),
(458, 111, 'Taps out simple repeated rhythms.', '', 5, 'A', NULL),
(459, 111, 'Explores and learns how sounds can be changed.', '', 6, 'A', NULL),
(460, 111, 'Explores colour and how colours can be changed.', '', 7, 'A', NULL),
(461, 111, 'Understands that they can use lines to enclose a space, and then begin to use these shapes to represent objects.', '', 8, 'A', NULL),
(462, 111, 'Beginning to be interested in and describe the texture of things.', '', 9, 'A', NULL),
(463, 111, 'Uses various construction materials.', '', 10, 'A', NULL),
(464, 111, 'Beginning to construct, stacking blocks vertically and horizontally, making enclosures and creating spaces.', '', 11, 'A', NULL),
(465, 111, 'Joins construction pieces together to build and balance.', '', 12, 'A', NULL),
(466, 111, 'Realises tools can be used for a purpose.', '', 13, 'A', NULL),
(467, 112, 'Developing preferences for forms of expression.', '', 1, 'A', NULL),
(468, 112, 'Uses movement to express feelings.', '', 2, 'A', NULL),
(469, 112, 'Creates movement in response to music.', '', 3, 'A', NULL),
(470, 112, 'Sings to self and makes up simple songs.', '', 4, 'A', NULL),
(471, 112, 'Makes up rhythms.', '', 5, 'A', NULL),
(472, 112, 'Notices what adults do, imitating what is observed and then doing it spontaneously when the adult is not there.', '', 6, 'A', NULL),
(473, 112, 'Engages in imaginative role-play based on own first-hand experiences', '', 7, 'A', NULL),
(474, 112, 'Builds stories around toys, e.g. farm animals needing rescue from an armchair ‘cliff’.', '', 8, 'A', NULL),
(475, 112, 'Uses available resources to create props to support role-play.', '', 9, 'A', NULL),
(476, 112, 'Captures experiences and responses with a range of media, such as music, dance and paint and other materials or words.', '', 10, 'A', NULL),
(477, 113, 'Initiates conversations, attends to and takes account of what others say.', '', 1, 'A', NULL),
(478, 113, 'Explains own knowledge and understanding, and asks appropriate questions of others.', '', 2, 'A', NULL),
(479, 113, 'Takes steps to resolve conflicts with other children, e.g. finding a compromise.', '', 3, 'A', NULL),
(480, 114, 'Confident to speak to others about own needs, wants, interests and opinions.', '', 1, 'A', NULL),
(481, 114, 'Can describe self in positive terms and talk about abilities.', '', 2, 'A', NULL),
(482, 115, 'Understands that own actions affect other people, for example, becomes upset or tries to comfort another child when they realise they have upset them.', '', 1, 'A', NULL),
(483, 115, 'Aware of the boundaries set, and of behavioural expectations in the setting.', '', 2, 'A', NULL),
(484, 115, 'Beginning to be able to negotiate and solve problems without aggression, e.g. when someone has taken their toy.', '', 3, 'A', NULL),
(485, 116, 'Maintains attention, concentrates and sits quietly during appropriate activity.', '', 1, 'A', NULL),
(486, 116, 'Two-channelled attention â€“ can listen and do for short span.', '', 2, 'A', NULL),
(487, 117, 'Responds to instructions involving a two-part sequence. Understands humour, e.g. nonsense rhymes, jokes.', '', 1, 'A', NULL),
(488, 117, 'Able to follow a story without pictures or props.', '', 2, 'A', NULL),
(489, 117, 'Listens and responds to ideas expressed by others in conversation or discussion.', '', 3, 'A', NULL),
(490, 118, 'Extends vocabulary, especially by grouping and naming, exploring the meaning and sounds of new words.', '', 1, 'A', NULL),
(491, 118, 'Uses language to imagine and recreate roles and experiences in play situations.', '', 2, 'A', NULL),
(492, 118, 'Links statements and sticks to a main theme or intention.', '', 3, 'A', NULL),
(493, 118, 'Uses talk to organise, sequence and clarify thinking, ideas, feelings and events.', '', 4, 'A', NULL),
(494, 118, 'Introduces a storyline or narrative into their play.', '', 5, 'A', NULL),
(495, 119, 'Experiments with different ways of moving.', '', 1, 'A', NULL),
(496, 119, 'Jumps off an object and lands appropriately.', '', 2, 'A', NULL),
(497, 119, 'Negotiates space successfully when playing racing and chasing games with other children, adjusting speed or changing direction to avoid obstacles.', '', 3, 'A', NULL),
(498, 119, 'Travels with confidence and skill around, under, over and through balancing and climbing equipment.', '', 4, 'A', NULL),
(499, 119, 'Shows increasing control over an object in pushing, patting, throwing, catching or kicking it.', '', 5, 'A', NULL),
(500, 119, 'Uses simple tools to effect changes to materials.', '', 6, 'A', NULL),
(501, 119, 'Handles tools, objects, construction and malleable materials safely and with increasing control.', '', 7, 'A', NULL),
(502, 119, 'Shows a preference for a dominant hand.', '', 8, 'A', NULL),
(503, 119, 'Begins to use anticlockwise movement and retrace vertical lines.', '', 9, 'A', NULL),
(504, 119, 'Begins to form recognisable letters.', '', 10, 'A', NULL),
(505, 119, 'Uses a pencil and holds it effectively to form recognisable letters, most of which are correctly formed.', '', 11, 'A', NULL),
(506, 120, 'Eats a healthy range of foodstuffs and understands need for variety in food.', '', 1, 'A', NULL),
(507, 120, 'Usually dry and clean during the day.', '', 2, 'A', NULL),
(508, 120, 'Shows some understanding that good practices with regard to exercise, eating, sleeping and hygiene can contribute to good health.', '', 3, 'A', NULL),
(509, 120, 'Shows understanding of the need for safety when tackling new challenges, and considers and manages some risks.', '', 4, 'A', NULL),
(510, 120, 'Shows understanding of how to transport and store equipment safely.', '', 5, 'A', NULL),
(511, 120, 'Practices some appropriate safety measures without direct supervision.', '', 6, 'A', NULL),
(512, 121, 'Continues a rhyming string.', '', 1, 'A', NULL),
(513, 121, 'Hears and says the initial sound in words.', '', 2, 'A', NULL),
(514, 121, 'Can segment the sounds in simple words and blend them together and knows which letters represent some of them.', '', 3, 'A', NULL),
(515, 121, 'Links sounds to letters, naming and sounding the letters of the alphabet.', '', 4, 'A', NULL),
(516, 121, 'Begins to read words and simple sentences.', '', 5, 'A', NULL),
(517, 121, 'Uses vocabulary and forms of speech that are increasingly influenced by their experiences of books.', '', 6, 'A', NULL),
(518, 121, 'Enjoys an increasing range of books.', '', 7, 'A', NULL),
(519, 121, 'Knows that information can be retrieved from books and computers.', '', 8, 'A', NULL),
(520, 122, 'Gives meaning to marks they make as they draw, write and paint.', '', 1, 'A', NULL),
(521, 122, 'Begins to break the flow of speech into words.', '', 2, 'A', NULL),
(522, 122, 'Continues a rhyming string.', '', 3, 'A', NULL),
(523, 122, 'Hears and says the initial sound in words.', '', 4, 'A', NULL),
(524, 122, 'Can segment the sounds in simple words and blend them together. ', '', 5, 'A', NULL),
(525, 122, 'Links sounds to letters, naming and sounding the letters of the alphabet.', '', 6, 'A', NULL),
(526, 122, 'Uses some clearly identifiable letters to communicate meaning, representing some sounds correctly and in sequence.', '', 7, 'A', NULL),
(527, 122, 'Writes own name and other things such as labels, captions.', '', 8, 'A', NULL),
(528, 122, 'Attempts to write short sentences in meaningful contexts.', '', 9, 'A', NULL),
(529, 123, 'Recognise some numerals of personal significance.', '', 1, 'A', NULL),
(530, 123, 'Recognises numerals 1 to 5.', '', 1, 'A', NULL),
(531, 123, 'Counts up to three or four objects by saying one number name for each item.', '', 2, 'A', NULL),
(532, 123, 'Counts actions or objects which cannot be moved.', '', 3, 'A', NULL),
(533, 123, 'Counts objects to 10, and beginning to count beyond 10. ', '', 4, 'A', NULL),
(534, 123, 'Counts out up to six objects from a larger group. Selects the correct numeral to represent 1 to 5, then 1 to 10 objects. ', '', 5, 'A', NULL),
(535, 123, 'Counts an irregular arrangement of up to ten objects. ', '', 6, 'A', NULL),
(536, 123, 'Estimates how many objects they can see and checks by counting them. ', '', 7, 'A', NULL),
(537, 123, 'Uses the language of ‘more’ and ‘fewer’ to compare two sets of objects. ', '', 8, 'A', NULL),
(538, 123, 'Finds the total number of items in two groups by counting all of them.', '', 9, 'A', NULL),
(539, 123, 'Says the number that is one more than a given number. ', '', 10, 'A', NULL),
(540, 123, 'Finds one more or one less from a group of up to five objects, then ten objects. ', '', 11, 'A', NULL),
(541, 123, 'In practical activities and discussion, beginning to use the vocabulary involved in adding and subtracting.', '', 12, 'A', NULL),
(542, 123, 'Records, using marks that they can interpret and explain. ', '', 13, 'A', NULL),
(543, 123, 'Begins to identify own mathematical problems based on own interests and fascinations.', '', 14, 'A', NULL),
(544, 124, 'Beginning to use mathematical names for ‘solid’ 3D shapes and ‘flat’ 2D shapes, and mathematical terms to describe shapes. ', '', 1, 'A', NULL),
(545, 124, 'Selects a particular named shape. ', '', 2, 'A', NULL),
(546, 124, 'Can describe their relative position such as ‘behind’ or ‘next to’. ', '', 3, 'A', NULL),
(547, 124, 'Orders two or three items by length or height. ', '', 4, 'A', NULL),
(548, 124, 'Orders two items by weight or capacity. ', '', 5, 'A', NULL),
(549, 124, 'Uses familiar objects and common shapes to create and recreate patterns and build models. ', '', 6, 'A', NULL),
(550, 124, 'Uses everyday language related to time. ', '', 7, 'A', NULL),
(551, 124, 'Beginning to use everyday language related to money.', '', 8, 'A', NULL),
(552, 124, 'Orders and sequences familiar events. ', '', 9, 'A', NULL),
(553, 124, 'Measures short periods of time in simple ways.', '', 10, 'A', NULL),
(554, 125, 'Enjoys joining in with family customs and routines. ', '', 1, 'A', NULL),
(555, 126, 'Looks closely at similarities, differences, patterns and change. ', '', 1, 'A', NULL),
(556, 127, 'Completes a simple program on a computer. ', '', 1, 'A', NULL),
(557, 127, 'Uses ICT hardware to interact with age-appropriate computer software. ', '', 2, 'A', NULL),
(558, 128, 'Begins to build a repertoire of songs and dances. ', '', 1, 'A', NULL),
(559, 128, 'Explores the different sounds of instruments. ', '', 2, 'A', NULL),
(560, 128, 'Explores what happens when they mix colours. ', '', 3, 'A', NULL),
(561, 128, 'Experiments to create different textures. ', '', 4, 'A', NULL),
(562, 128, 'Understands that different media can be combined to create new effects. ', '', 5, 'A', NULL),
(563, 128, 'Manipulates materials to achieve a planned effect. ', '', 6, 'A', NULL),
(564, 128, 'Constructs with a purpose in mind, using a variety of resources. ', '', 7, 'A', NULL),
(565, 128, 'Uses simple tools and techniques competently and appropriately. ', '', 8, 'A', NULL),
(566, 128, 'Selects appropriate resources and adapts work where necessary. ', '', 9, 'A', NULL),
(567, 128, 'Selects tools and techniques needed to shape, assemble and join materials they are using. ', '', 10, 'A', NULL),
(568, 129, 'Create simple representations of events, people and objects. ', '', 1, 'A', NULL),
(569, 129, 'Initiates new combinations of movement and gesture in order to express and respond to feelings, ideas and experiences. ', '', 2, 'A', NULL),
(570, 129, 'Chooses particular colours to use for a purpose. ', '', 3, 'A', NULL),
(571, 129, 'Introduces a storyline or narrative into their play. ', '', 4, 'A', NULL),
(572, 129, 'Plays alongside other children who are engaged in the same theme. ', '', 5, 'A', NULL),
(573, 129, 'Plays cooperatively as part of a group to develop and act out a narrative. ', '', 6, 'A', NULL),
(574, 130, 'Children feel safe, secure, and supported', 'This goal is evident in practice when your example shows how children can: <ul> <li>communicate their needs for comfort and assistance</li> <li>establish and maintain respectful, trusting relationships with other children and educators</li> <li>openly express their feelings and ideas in their interactions with others</li> <li>respond to ideas and suggestions from others</li> <li>initiate interactions and conversations with trusted educators</li> <li>confidently explore and engage with social and physical environments through relationships and play</li> <li>initiate and join in play</li> <li>explore aspects of identity through role play</li> </ul>', 1, 'A', NULL),
(575, 130, 'Children develop their emerging autonomy, inter-dependence, resilience and sense of agency Children develop knowledgeable and confident self identities', 'This goal is evident in practice when your example shows how children can: <ul> <li>demonstrate increasing awareness of the needs and rights of others</li> <li>be open to new challenges and discoveries</li> <li>increasingly co-operate and work collaboratively with others</li> <li>take considered risk in their decision-making and cope with the unexpected</li> <li>recognise their individual achievements and the achievements of others</li> <li>demonstrate an increasing capacity for self-regulation</li> <li>approach new safe situations with confidence</li> <li>begin to initiate negotiating and sharing behaviours</li> <li>persist when faced with challenges and when first attempts are not successful</li> </ul>', 2, 'A', NULL),
(576, 130, 'Children learn to interact in relation to others with care, empathy and respect', 'This goal is evident in practice when your example shows how children can:\r\n<ul>\r\n<li>Children show interest in other children and being part of a group</li>\r\n<li>engage in and contribute to shared play experiences</li>\r\n<li>express a wide range of emotions, thoughts and views constructively</li>\r\n<li>empathise with and express concern for others</li>\r\n<li>display awareness of and respect for othersâ€™ perspectives</li>\r\n<li>reflect on their actions and consider consequences for others</li>\r\n</ul>', 4, 'A', NULL),
(577, 131, 'Children develop a sense of belonging to groups and communities', 'This goal is evident in practice when your example shows how children: <ul> <li>begin to recognise that they have a right to belong to many communities</li> <li>cooperate with others and negotiate roles and relationships in play episodes and group experiences</li> <li>take action to assist other children to participate in social groups</li> <li>broaden their understanding of the world in which they live</li> <li>express an opinion in matters that affect them</li> <li>build on their own social experiences to explore other ways of being</li> <li>participate in reciprocal relationships</li> <li>gradually learn to â€˜readâ€™ the behaviours of others and respond appropriately</li> <li>understand different ways of contributing through play and projects</li> <li>demonstrate a sense of belonging and comfort in their environments</li> <li>are playful and respond positively to others, reaching out for company and friendship</li> <li>contribute to fair decision-making about matters that affect them</li> </ul>', 1, 'A', NULL),
(578, 131, 'Children have an understanding of the reciprocal rights and responsibilities necessary for active community participation', '', 2, 'A', NULL),
(579, 131, 'Children respond to diversity with respect', '', 3, 'A', NULL),
(580, 131, 'Children become aware of fairness Children become socially responsible and show respect for the environment', '', 4, 'A', NULL),
(581, 132, 'Children become strong in their social and emotional wellbeing', 'This goal is evident in practice when your example shows how children can: <ul> <li>Children can recognise and communicate their bodily needs (for example, thirst, hunger, rest, comfort, physical activity)</li> <li>are happy, healthy, safe and connected to others</li> <li>engage in increasingly complex sensory motor skills and movement patterns</li> <li>combine gross and fine motor movement and balance to achieve increasingly complex patterns of activity including dance, creative movement and drama</li> <li>use their sensory capabilities and dispositions with increasing integration, skill and purpose to explore and respond to their world</li> <li>demonstrate spatial awareness and orient themselves, moving around and through their environments confidently and safely</li> <li>manipulate equipment and manage tools with increasing competence and skill</li> <li>respond through movement to traditional and contemporary music, dance and storytelling</li> <li>show an increasing awareness of healthy lifestyles and good nutrition</li> <li>show increasing independence and competence in personal hygiene, care and safety for themselves and others</li> <li>show enthusiasm for participating in physical play and negotiate play spaces to ensure the safety and wellbeing of themselves and others</li> </ul>', 1, 'A', NULL),
(582, 132, 'Children take increasing responsibility for their own health and physical wellbeing', '', 2, 'A', NULL),
(583, 133, 'Children develop dispositions for learning such as curiosity, cooperation, confidence, creativity, commitment, enthusiasm, persistence, imagination and reflexivity', '', 1, 'A', NULL),
(584, 133, 'Children develop a range of skills and processes such as problem solving inquiry, experimentation, hypothesising, researching and investigating Children transfer and adapt what they have learned from one context to another', '', 2, 'A', NULL),
(585, 133, 'Children resource their own learning through connecting with people, place technologies and natural and processed materials', '', 3, 'A', NULL),
(586, 134, 'Children interact verbally and non-verbally with others for a range of purposes', '', 1, 'A', NULL),
(587, 134, 'Children engage with a range of texts and gain meaning from these texts', '', 2, 'A', NULL),
(588, 134, 'Children express ideas and make meaning using a range of media', '', 3, 'A', NULL),
(589, 134, 'Children begin to understand how symbols and pattern systems work', '', 4, 'A', NULL),
(590, 134, 'Children use information and communication technologies to access information, investigate ideas and represent their thinking', '', 5, 'A', NULL),
(591, 130, 'Children develop knowledgeable and confident self identities', 'This goal is evident in practice when your example shows how children can:\r\n<ul>\r\n<li>Feel recognised and respected for who they are</li>\r\n<li>explore different identities and points of view in dramatic play</li>\r\n<li>share aspects of their culture with the other children and educators</li>\r\n<li>use their home language to construct meaning</li>\r\n<li>develop strong foundations in both the culture and language/s of their family and of the broader community without compromising their cultural identities</li>\r\n<li>develop their social and cultural heritage through engagement with Elders and community members</li>\r\n<li>reach out and communicate for comfort, assistance and companionship</li>\r\n<li>celebrate and share their contributions and achievements with others</li>\r\n</ul>', 3, 'A', NULL),
(592, 135, 'Practical life skills', '', 1, 'A', NULL),
(593, 135, 'Sensorial activities', '', 2, 'A', NULL),
(594, 135, 'Mathematics', '', 3, 'A', NULL),
(595, 135, 'Language', '', 4, 'A', NULL),
(596, 135, 'Cultural Studies', '', 5, 'A', NULL),
(597, 136, 'Practical life skills', '', 1, 'A', NULL),
(598, 136, 'Sensorial activities', '', 2, 'A', NULL),
(599, 136, 'Mathematics', '', 3, 'A', NULL),
(600, 136, 'Language', '', 4, 'A', NULL),
(601, 136, 'Cultural Studies', '', 5, 'A', NULL),
(602, 137, 'Practical life skills', '', 1, 'A', NULL),
(603, 137, 'Sensorial activities', '', 2, 'A', NULL),
(604, 137, 'Mathematics', '', 3, 'A', NULL),
(605, 137, 'Language', '', 4, 'A', NULL),
(606, 137, 'Cultural Studies', '', 5, 'A', NULL),
(607, 254, 'Shows ability to adjust to new situations', '', 1, 'A', NULL),
(608, 254, 'Demonstrates appropriate trust in adults', '', 2, 'A', NULL),
(609, 254, 'Recognizes own feelings and manages them appropriately', '', 3, 'A', NULL),
(610, 254, 'Stands up for rights', '', 4, 'A', NULL),
(611, 255, 'Demonstrates self-direction and independence', '', 1, 'A', NULL),
(612, 255, 'Takes responsibility for own well-being', '', 2, 'A', NULL),
(613, 255, 'Respects and cares for classroom environment and materials', '', 3, 'A', NULL),
(614, 255, 'Follows classroom routines', '', 4, 'A', NULL),
(615, 255, 'Follows classroom rules', '', 5, 'A', NULL),
(616, 256, 'Plays well with other children', '', 1, 'A', NULL),
(617, 256, 'Recognizes the feelings of others and responds appropriately', '', 2, 'A', NULL),
(618, 256, 'Shares and respects the rights of others', '', 3, 'A', NULL),
(619, 256, 'Uses thinking skills to resolve conflicts', '', 4, 'A', NULL),
(620, 257, 'Hears and discriminates the sounds of language', '', 1, 'A', NULL),
(621, 257, 'Expresses self using words and expanded sentences', '', 2, 'A', NULL),
(622, 257, 'Understands and follows oral directions', '', 3, 'A', NULL),
(623, 257, 'Answers questions', '', 4, 'A', NULL),
(624, 257, 'Asks questions', '', 5, 'A', NULL),
(625, 257, 'Actively participates in conversations', '', 6, 'A', NULL),
(626, 258, 'Enjoys and values reading', '', 1, 'A', NULL),
(627, 258, 'Demonstrates understanding of print concepts', '', 2, 'A', NULL),
(628, 258, 'Demonstrates knowledge of the alphabet', '', 3, 'A', NULL),
(629, 258, 'Uses emerging reading skills to make meaning from print', '', 4, 'A', NULL),
(630, 258, 'Comprehends and interprets meaning from books and other texts', '', 5, 'A', NULL),
(631, 258, 'Understands the purpose of writing', '', 6, 'A', NULL),
(632, 258, 'Writes letters and words', '', 7, 'A', NULL),
(633, 259, 'Demonstrates basic locomotor skills (running, jumping, hopping, galloping)', '', 1, 'A', NULL),
(634, 259, 'Shows balance while moving', '', 2, 'A', NULL),
(635, 259, 'Climbs up and down', '', 3, 'A', NULL),
(636, 259, 'Pedals and steers a tricycle (or other wheeled vehicle)', '', 4, 'A', NULL),
(637, 259, 'Demonstrates throwing, kicking, and catching skills', '', 5, 'A', NULL),
(638, 260, 'Controls small muscles in hands', '', 1, 'A', NULL),
(639, 260, 'Coordinates eye-hand movement', '', 2, 'A', NULL),
(640, 260, 'Uses tools for writing and drawing', '', 3, 'A', NULL),
(641, 261, 'Observes objects and events with curiosity', '', 1, 'A', NULL),
(642, 261, 'Approaches problems flexibly', '', 2, 'A', NULL),
(643, 261, 'Shows persistence in approaching tasks', '', 3, 'A', NULL),
(644, 261, 'Explores cause and effect', '', 4, 'A', NULL),
(645, 261, 'Applies knowledge or experience to a new context', '', 5, 'A', NULL),
(646, 262, 'Classifies objects', '', 1, 'A', NULL),
(647, 262, 'Compares/measures', '', 2, 'A', NULL),
(648, 262, 'Arranges objects in a series', '', 3, 'A', NULL),
(649, 262, 'Recognizes patterns and can repeat them', '', 4, 'A', NULL),
(650, 262, 'Shows awareness of time concepts and sequence', '', 5, 'A', NULL),
(651, 262, 'Shows awareness of position in space', '', 6, 'A', NULL),
(652, 262, 'Uses one-to-one correspondence', '', 7, 'A', NULL),
(653, 262, 'Uses numbers and counting', '', 8, 'A', NULL),
(654, 263, 'Takes on pretend roles and situations', '', 1, 'A', NULL),
(655, 263, 'Makes believe with objects', '', 2, 'A', NULL),
(656, 263, 'Makes and interprets representations', '', 3, 'A', NULL),
(657, 264, 'Practical life skills', '', 1, 'A', NULL),
(658, 264, 'Sensorial activities', '', 2, 'A', NULL),
(659, 264, 'Mathematics', '', 3, 'A', NULL),
(660, 264, 'Language', '', 4, 'A', NULL),
(661, 264, 'Cultural Studies', '', 5, 'A', NULL);
(662, 271, 'Their health is promoted', NULL, 1, 'A', NULL),
(663, 271, 'Their emotional wellbeing is nurtured', NULL, 2, 'A', NULL),
(664, 271, 'They are kept safe from harm', NULL, 3, 'A', NULL),
(665, 272, 'Connecting links with the family and the wider world are affirmed and extended', NULL, 1, 'A', NULL),
(666, 272, 'They know that they have a place', NULL, 2, 'A', NULL),
(667, 272, 'They feel comfortable with the routines, customs and regular events', NULL, 3, 'A', NULL),
(668, 272, 'They know the limits and boundaries of acceptable behaviour', NULL, 4, 'A', NULL),
(669, 273, 'There are equitable opportunities for learning, irrespective of gender, ability, age, ethnicity, or background', NULL, 1, 'A', NULL),
(670, 273, 'They are affirmed as individuals', NULL, 2, 'A', NULL),
(671, 273, 'They are encouraged to learn with and alongside others', NULL, 3, 'A', NULL),
(672, 274, 'They develop non-verbal communication skills for a range of purposes', NULL, 1, 'A', NULL),
(673, 274, 'They develop verbal communication skills for a range of purposes', NULL, 2, 'A', NULL),
(674, 274, 'They experience the stories and symbols of their own and other cultures', NULL, 3, 'A', NULL),
(675, 274, 'They discover different ways to be creative and expressive', NULL, 4, 'A', NULL),
(676, 275, 'Their play is valued as meaningful learning and the importance of spontaneous play is recognised', NULL, 1, 'A', NULL),
(677, 275, 'They gain confidence in and control of their bodies', NULL, 2, 'A', NULL),
(678, 275, 'They learn strategies for active exploration, thinking and reasoning', NULL, 3, 'A', NULL),
(679, 275, 'They develop working theories for making sense of the natural, social, physical and material worlds', NULL, 4, 'A', NULL),
(680, 276, 'Empowerment | Whakamana', NULL, 1, 'A', NULL),
(681, 276, 'Holistic development | Kotahitanga', NULL, 2, 'A', NULL),
(682, 276, 'Family and community | Whānau tangata', NULL, 3, 'A', NULL),
(683, 276, 'Relationships | Ngā hononga ', NULL, 4, 'A', NULL),
(684, 277, 'Listen with increasing attention to spoken language, conversations, and texts read aloud.', NULL, 1, 'A', NULL),
(685, 277, 'Correctly identify characters, objects, and actions in a text with or without pictures and begin to comment about each.', NULL, 2, 'A', NULL),
(686, 277, 'Make predictions about what might happen in a story.', NULL, 3, 'A', NULL),
(687, 277, 'Use complete sentences to ask and answer questions about experiences or about what has been read.', NULL, 4, 'A', NULL),
(688, 277, 'Use appropriate and expanding language for a variety of purposes, e.g., ask questions, express needs, get information.', NULL, 5, 'A', NULL),
(689, 277, 'Engage in turn taking exchanges and rules of polite conversation with adults and peers, understanding that conversation is interactive.', NULL, 6, 'A', NULL),
(690, 277, 'Listen attentively to stories in a whole class setting.', NULL, 7, 'A', NULL),
(691, 277, 'Follow simple one and two step oral directions.', NULL, 8, 'A', NULL),
(692, 278, 'Use size, shape, color, and spatial words to describe people, places, and things.', NULL, 1, 'A', NULL),
(693, 278, 'Listen with increasing understanding to conversations and directions.', NULL, 2, 'A', NULL),
(694, 278, 'Use expanding vocabulary with increasing frequency and sophistication to express and describe feelings, needs, and ideas.', NULL, 3, 'A', NULL),
(695, 278, 'Participate in a wide variety of active sensory experiences to build vocabulary.', NULL, 4, 'A', NULL),
(696, 279, 'Identify words that rhyme and generate simple rhymes.', NULL, 1, 'A', NULL),
(697, 279, 'Identify words within spoken sentences.', NULL, 2, 'A', NULL),
(698, 279, 'Begin to produce consonant letter sounds in isolation.', NULL, 3, 'A', NULL),
(699, 279, 'Successfully detect beginning sounds in words.', NULL, 4, 'A', NULL),
(700, 279, 'Begin to isolate or produce syllables within multisyllable words.', NULL, 5, 'A', NULL),
(701, 280, 'Identify and name uppercase and lowercase letters in random order.', NULL, 1, 'A', NULL),
(702, 280, 'Identify the letter that represents a spoken sound.', NULL, 2, 'A', NULL),
(703, 280, 'Provide the most common sound for the majority of letters.', NULL, 3, 'A', NULL),
(704, 280, 'Begin to match uppercase and lowercase letters.', NULL, 4, 'A', NULL),
(705, 280, 'Read simple/familiar high-frequency words, including child’s name.', NULL, 5, 'A', NULL),
(706, 280, 'Notice letters in familiar everyday context and ask an adult how to spell words, names, or titles.', NULL, 6, 'A', NULL),
(707, 281, 'Identify the front and back cover of a book.', NULL, 1, 'A', NULL),
(708, 281, 'Identify the location of the title and title page of a book.', NULL, 2, 'A', NULL),
(709, 281, 'Identify where reading begins on a page (first word).', NULL, 3, 'A', NULL),
(710, 281, 'Follow text with a finger, pointing to each word as it is read from left to right and top to bottom with assistance.', NULL, 4, 'A', NULL),
(711, 281, 'Distinguish print from pictures.', NULL, 5, 'A', NULL),
(712, 281, 'Turn pages one at a time from the front to the back of a book.', NULL, 6, 'A', NULL),
(713, 282, 'Distinguish print from images or illustrations.', NULL, 1, 'A', NULL),
(714, 282, 'Demonstrate use of print to convey meaning.', NULL, 2, 'A', NULL),
(715, 282, 'Copy or write letters and numbers using various materials.', NULL, 3, 'A', NULL),
(716, 282, 'Print first name independently.', NULL, 4, 'A', NULL),
(717, 282, 'Begin to use correct manuscript letter and number formation.', NULL, 5, 'A', NULL),
(718, 282, 'Copy various words associated with people or objects within the child’s environment.', NULL, 6, 'A', NULL),
(719, 282, 'Use phonetically spelled words to convey messages or tell a story.', NULL, 7, 'A', NULL),
(720, 282, 'Understands that writing proceeds left to right and top to bottom.', NULL, 8, 'A', NULL),
(721, 283, 'Count forward to 20 or more. Count backward from 5.', NULL, 1, 'A', NULL),
(722, 283, 'Count a group (set/collection) of five to ten objects by touching each object as it is counted and saying the correct number (one-to-one correspondence).', NULL, 2, 'A', NULL),
(723, 283, 'Count the items in a collection of one to ten items and know the last counting word tells “how many.”', NULL, 3, 'A', NULL),
(724, 283, 'Compare two groups (sets/collections) of matched objects (zero through ten in each set) and describe the groups using the terms more, fewer, or same.', NULL, 4, 'A', NULL),
(725, 283, 'Use ordinal numbers (first through fifth) when describing the position of objects or groups of children in a sequence.', NULL, 5, 'A', NULL),
(726, 284, 'Describe changes in groups (sets/ collections) by using more when groups of objects (sets) are combined (added together).', NULL, 1, 'A', NULL),
(727, 284, 'Describe changes in groups (sets/ collections) by using fewer when groups of objects (sets) are separated (taken away).', NULL, 2, 'A', NULL),
(728, 285, 'Recognize attributes of length by using the terms longer or shorter when comparing two objects.', NULL, 1, 'A', NULL),
(729, 285, 'Know the correct names for the standard tools used for telling time and temperature, and for measuring length, capacity, and weight (clocks, calendars, thermometers, rulers, measuring cups, and scales).', NULL, 2, 'A', NULL),
(730, 285, 'Use the appropriate vocabulary when comparing temperatures, e.g., hot, cold.', NULL, 3, 'A', NULL),
(731, 285, 'Use appropriate vocabulary when describing duration of time, e.g., hour, day, week, month, morning, afternoon, and night.', NULL, 4, 'A', NULL),
(732, 286, 'Match and sort shapes (circle, triangle, rectangle, and square).', NULL, 1, 'A', NULL),
(733, 286, 'Describe how shapes are similar and different.', NULL, 2, 'A', NULL),
(734, 286, 'Recognize and name shapes (circle, triangle, rectangle, and square).', NULL, 3, 'A', NULL),
(735, 286, 'Describe the position of objects in relation to other objects and themselves using the terms next to, beside, above, below, under, over, top, and bottom.', NULL, 4, 'A', NULL),
(736, 287, 'Collect information to answer questions of interest to children.', NULL, 1, 'A', NULL),
(737, 287, 'Use descriptive language to compare data by identifying which is more, fewer, or the same in object and picture graphs.', NULL, 2, 'A', NULL),
(738, 288, 'Sort and classify objects according to one or two attributes (color, size, shape, and texture).', NULL, 1, 'A', NULL),
(739, 288, 'Identify and explore simple patterns, i.e., AB, AB; red, blue, red, blue.', NULL, 2, 'A', NULL),
(740, 288, 'Use patterns to predict relationships between objects, i.e., the blue shape follows the yellow shape, the triangle follows the square.', NULL, 3, 'A', NULL),
(741, 289, 'Use the five senses to explore and investigate the natural world.', NULL, 1, 'A', NULL),
(742, 289, 'Use simple tools and technology safely to observe and explore different objects and environments.', NULL, 2, 'A', NULL),
(743, 289, 'Ask questions about the natural world related to observations.', NULL, 3, 'A', NULL),
(744, 289, 'Make predictions about what will happen next based on previous experiences.', NULL, 4, 'A', NULL),
(745, 289, 'Conduct simple scientific investigations.', NULL, 5, 'A', NULL),
(746, 290, 'Describe, demonstrate, and compare the motion of common objects in terms of speed and direction, e.g., fast, slow, up, down.', NULL, 1, 'A', NULL),
(747, 290, 'Describe and demonstrate the effects of common forces (pushes and pulls) on objects.', NULL, 2, 'A', NULL),
(748, 290, 'Describe the effects magnets have on other objects.', NULL, 3, 'A', NULL),
(749, 290, 'Investigate and describe the way simple tools work, e.g., a hammer, a wheel, a screwdriver.', NULL, 4, 'A', NULL),
(750, 291, 'Describe and sort objects by their physical properties, e.g., color, shape, texture, feel, size and weight, position, speed, and phase of matter (solid or liquid).', NULL, 1, 'A', NULL),
(751, 291, 'Recognize water in its solid and liquid forms.', NULL, 2, 'A', NULL),
(752, 291, 'Describe the differences between solid and liquid objects.', NULL, 3, 'A', NULL),
(753, 291, 'Sort objects based on whether they sink or float in water.', NULL, 4, 'A', NULL),
(754, 292, 'Predict changes to matter when various substances are to be combined.', NULL, 1, 'A', NULL),
(755, 292, 'Observe and conduct simple experiments that explore what will happen when substances are combined.', NULL, 2, 'A', NULL),
(756, 292, 'Observe and record the experiment results and describe what is seen.', NULL, 3, 'A', NULL),
(757, 293, 'Describe what living things need to live and grow (food, water, and air).', NULL, 1, 'A', NULL),
(758, 293, 'Identify basic structures for plants and animals (plants-roots, stems, leaves; animals-eyes, mouth, ears, etc.).', NULL, 2, 'A', NULL),
(759, 293, 'Recognize that many young plants and animals are similar but not identical to their parents and to one another.', NULL, 3, 'A', NULL),
(760, 294, 'Use vocabulary to describe major features of Earth and the sky.', NULL, 1, 'A', NULL),
(761, 294, 'Identify objects in the sky – moon, stars, sun, and clouds.', NULL, 2, 'A', NULL),
(762, 294, 'Classify things seen in the night sky and those seen in the day sky.', NULL, 3, 'A', NULL),
(763, 294, 'Explore and sort objects in the natural environment (sand, pebbles, rocks, leaves, moss, and other artifacts).', NULL, 4, 'A', NULL),
(764, 295, 'Make daily weather observations and use common weather related vocabulary to describe the observations, e.g., sunny, rainy, cloudy, cold, hot, etc.', NULL, 1, 'A', NULL),
(765, 295, 'Identify how weather affects daily life.', NULL, 2, 'A', NULL),
(766, 295, 'Describe basic weather safety rules.', NULL, 3, 'A', NULL),
(767, 295, 'Observe and recognize the characteristics of the four seasons and the changes observed from season to season.', NULL, 4, 'A', NULL),
(768, 295, 'Observe and classify the shapes and forms of many common natural objects, e.g., rocks, leaves, twigs, clouds, the moon, etc.', NULL, 5, 'A', NULL),
(769, 295, 'Compare a variety of living things to determine how they change over time (life cycles).', NULL, 6, 'A', NULL),
(770, 295, 'Describe home and school routines.', NULL, 7, 'A', NULL),
(771, 296, 'Identify ways that some things can be conserved.', NULL, 1, 'A', NULL),
(772, 296, 'Recognize that some things can be reused.', NULL, 2, 'A', NULL),
(773, 296, 'Recognize that some things can be recycled.', NULL, 3, 'A', NULL),
(774, 296, 'Understand and use vocabulary such as conserve, recycle, and reuse.', NULL, 4, 'A', NULL),
(775, 297, 'Recognize ways in which people are alike and different.', NULL, 1, 'A', NULL),
(776, 297, 'Describe his/her own unique characteristics and those of others.', NULL, 2, 'A', NULL),
(777, 297, 'Make the connection that he/she is both a member of a family and a member of a classroom community.', NULL, 3, 'A', NULL),
(778, 297, 'Engage in pretend play to understand self and others.', NULL, 4, 'A', NULL),
(779, 297, 'Participate in activities and traditions associated with different cultural heritages.', NULL, 5, 'A', NULL),
(780, 298, 'Describe ways children have changed since they were babies.', NULL, 1, 'A', NULL),
(781, 298, 'Express the difference between past and present using words such as before, after, now, and then.', NULL, 2, 'A', NULL),
(782, 298, 'Order/sequence events and objects.', NULL, 3, 'A', NULL),
(783, 298, 'Ask questions about artifacts from everyday life in the past.', NULL, 4, 'A', NULL),
(784, 298, 'Recount episodes from stories about the past.', NULL, 5, 'A', NULL),
(785, 298, 'Take on a role from a specific time, use symbols and props, and act out a story/narrative.', NULL, 6, 'A', NULL),
(786, 298, 'Describe past times based on stories, pictures, visits, songs, and music.', NULL, 7, 'A', NULL),
(787, 299, 'Identify and describe prominent features of the classroom, school, neighborhood, and community.', NULL, 1, 'A', NULL),
(788, 299, 'Engage in play where one item represents another (miniature vehicles, people, and blocks).', NULL, 2, 'A', NULL),
(789, 299, 'Make and walk on paths between objects, e.g., from the door to the window.', NULL, 3, 'A', NULL),
(790, 299, 'Represent objects in the order in which they occur in the environment.', NULL, 4, 'A', NULL),
(791, 299, 'Experience seeing things from different elevations.', NULL, 5, 'A', NULL),
(792, 300, 'Use words to describe features of locations in the environment and man-made structures found in stories and seen in everyday experiences.', NULL, 1, 'A', NULL),
(793, 300, 'Use direction words (on, under, over, behind, near, far, above, below, toward, and away) one direction at a time.', NULL, 2, 'A', NULL),
(794, 300, 'Use comparison words (closer, farther away, taller, shorter, higher, lower, alike, different, inside, and outside).', NULL, 3, 'A', NULL),
(795, 300, 'Use attribute words (hard, soft, rough, and smooth).', NULL, 4, 'A', NULL),
(796, 300, 'Use labels and symbols for what the child has seen.', NULL, 5, 'A', NULL),
(797, 301, 'Identify pictures of work and name the jobs people do.', NULL, 1, 'A', NULL),
(798, 301, 'Describe what people do in their community job.', NULL, 2, 'A', NULL),
(799, 301, 'Match tools to jobs.', NULL, 3, 'A', NULL),
(800, 301, 'Match job sites to work done.', NULL, 4, 'A', NULL),
(801, 301, 'Role play the jobs of workers.', NULL, 5, 'A', NULL),
(802, 302, 'Identify choices.', NULL, 1, 'A', NULL),
(803, 302, 'Recognize that everyone has wants and needs.', NULL, 2, 'A', NULL),
(804, 302, 'Recognize that our basic needs include food, clothing, and shelter.', NULL, 3, 'A', NULL),
(805, 302, 'Choose daily tasks.', NULL, 4, 'A', NULL),
(806, 302, 'Role play purchasing situations where choices are made.', NULL, 5, 'A', NULL),
(807, 303, 'Cooperate with others in a joint activity.', NULL, 1, 'A', NULL),
(808, 303, 'Recognize the need for rules to help get along with others.', NULL, 2, 'A', NULL),
(809, 303, 'Participate in creating rules for the classroom.', NULL, 3, 'A', NULL),
(810, 303, 'State personal plans for learning center activities.', NULL, 4, 'A', NULL),
(811, 303, 'Participate in discussing and generating solutions to a class problem.', NULL, 5, 'A', NULL),
(812, 303, 'Share thoughts and opinions in group settings.', NULL, 6, 'A', NULL),
(813, 303, 'Demonstrate responsible behaviors in caring for classroom materials.', NULL, 7, 'A', NULL),
(814, 303, 'Identify the needs of other people by helping them.', NULL, 8, 'A', NULL),
(815, 304, 'Demonstrate beginning forms of the Skilled Movement/Locomotor Skills of jumping, hopping, and galloping.', NULL, 1, 'A', NULL),
(816, 304, 'Perform these locomotor skills in response to teacher-led creative dance.', NULL, 2, 'A', NULL),
(817, 305, 'Maintain a stable static position while practicing specific balances on different bases of support, e.g., standing on toes or standing on one foot.', NULL, 1, 'A', NULL),
(818, 305, 'Maintain balance while performing a controlled spin.', NULL, 2, 'A', NULL),
(819, 305, 'Maintain balance while walking on a painted line or a low balance beam that is no more than three inches above the floor.', NULL, 3, 'A', NULL),
(820, 305, 'Maintain balance while climbing up steps and walking on a horizontal ladder placed on the floor.', NULL, 4, 'A', NULL),
(821, 305, 'Perform criss-cross pattern activities.', NULL, 5, 'A', NULL),
(822, 306, 'Manipulate a variety of objects during structured and unstructured physical activity settings.', NULL, 1, 'A', NULL),
(823, 306, 'Manipulate small objects using one hand independently, the other hand independently, and both hands working on the same task.', NULL, 2, 'A', NULL),
(824, 306, 'Demonstrate increasing ability to coordinate throwing, catching, kicking, bouncing, and juggling movements.', NULL, 3, 'A', NULL),
(825, 306, 'Coordinate eye-hand and eye-foot movements to perform a task.', NULL, 4, 'A', NULL),
(826, 307, 'Apply knowledge of movement concepts by performing various locomotor movements while changing directions (right, left, up, down, forward, and backward), levels (high, medium, and low), pathways (straight, curved, and zigzag), and effort (fast, slow, hard, and soft).', NULL, 1, 'A', NULL),
(827, 307, 'Identify fundamental movement patterns such as running and jumping.', NULL, 2, 'A', NULL),
(828, 307, 'Begin and expand movement vocabulary.', NULL, 3, 'A', NULL),
(829, 307, 'Perform various locomotor movements demonstrating changes in directions, levels, pathways, effort, and relationships in space while listening to music, or responding to a drum beat, the beat of a tambourine, verbal instruction, or other signals.', NULL, 4, 'A', NULL),
(830, 308, 'Participate in activities that allow the child to experience and recognize a rise in the heart rate and breathing rate.', NULL, 1, 'A', NULL),
(831, 308, 'Participate in activities designed to strengthen major muscle groups.', NULL, 2, 'A', NULL),
(832, 308, 'Participate in activities that enhance flexibility.', NULL, 3, 'A', NULL),
(833, 309, 'Demonstrate safe behaviors by participating appropriately during physical activity, accepting feedback, and taking responsibility for behavior when prompted.', NULL, 1, 'A', NULL),
(834, 309, 'Share equipment and space, and take turns with help from the teacher.', NULL, 2, 'A', NULL),
(835, 309, 'Work well with others.', NULL, 3, 'A', NULL),
(836, 309, 'Listen to and follow simple directions.', NULL, 4, 'A', NULL),
(837, 310, 'Identify the activities that they like and dislike.', NULL, 1, 'A', NULL),
(838, 310, 'Describe what it means to be physically active and then have the opportunity to actively pursue the activities they have described.', NULL, 2, 'A', NULL),
(839, 310, 'Participate in activities geared toward different levels of proficiency.', NULL, 3, 'A', NULL),
(840, 310, 'Identify places at home, in the neighborhood, and in the community where children can play safely and be physically active.', NULL, 4, 'A', NULL),
(841, 311, 'Indicate awareness of hunger and fullness.', NULL, 1, 'A', NULL),
(842, 311, 'Identify foods and the food groups to which they belong, e.g., vegetables, fruits, dairy, meats, and grains.', NULL, 2, 'A', NULL),
(843, 311, 'Distinguish food and beverages on a continuum from more healthy to less healthy.', NULL, 3, 'A', NULL),
(844, 311, 'Demonstrate an understanding that eating a variety of fresh fruits and vegetables with lots of different colors helps the body grow and be healthy.', NULL, 4, 'A', NULL),
(845, 312, 'Demonstrate how to correctly wash hands.', NULL, 1, 'A', NULL),
(846, 312, 'Demonstrate covering the mouth or nose when coughing or sneezing.', NULL, 2, 'A', NULL),
(847, 312, 'Identify habits that keep us healthy.', NULL, 3, 'A', NULL),
(848, 312, 'Explain the importance of rest.', NULL, 4, 'A', NULL),
(849, 312, 'Be able to communicate when one is not feeling well.', NULL, 5, 'A', NULL),
(850, 313, 'Understand that health care providers can help them when they are not feeling well.', NULL, 1, 'A', NULL),
(851, 313, 'Identify people they can trust, e.g., police, firefighters, family members, and teachers, and understand they will keep them safe.', NULL, 2, 'A', NULL),
(852, 313, 'Be able to differentiate between safe and unsafe situations.', NULL, 3, 'A', NULL),
(853, 313, 'Begin to share feelings and express how they feel.', NULL, 4, 'A', NULL),
(854, 314, 'Follow safety rules on the playground with adult assistance and reminders.', NULL, 1, 'A', NULL),
(855, 314, 'Follow emergency protocols after practicing safety drills, e.g., fire, earthquake, and lockdown drills.', NULL, 2, 'A', NULL),
(856, 314, 'Demonstrate pedestrian safety and vehicle awareness.', NULL, 3, 'A', NULL),
(857, 314, 'Understand bicycle/tricycle safety and the importance of wearing a helmet.', NULL, 4, 'A', NULL),
(858, 314, 'Know how to make an emergency phone call.', NULL, 5, 'A', NULL),
(859, 314, 'Act safely around pools, ponds, and other water, e.g., oceans, rivers, creeks, ditches, and swamps.', NULL, 6, 'A', NULL),
(860, 315, 'Demonstrate knowledge of personal information including first and last name, gender, age, birthday, parents’ names, teacher’s name, school name, town or city where they live, and street name.', NULL, 1, 'A', NULL),
(861, 315, 'Begin to recognize and express own emotions using words rather than actions.', NULL, 2, 'A', NULL),
(862, 315, 'Recognize self as a unique individual and respect differences of others.', NULL, 3, 'A', NULL),
(863, 315, 'Develop personal preferences regarding activities and materials.', NULL, 4, 'A', NULL),
(864, 315, 'Demonstrate self-direction in use of materials.', NULL, 5, 'A', NULL),
(865, 315, 'Develop increasing independence in school activities throughout the day.', NULL, 6, 'A', NULL),
(866, 316, 'Contribute ideas for classroom rules and routines.', NULL, 1, 'A', NULL),
(867, 316, 'Follow rules and routines within the learning environment.', NULL, 2, 'A', NULL),
(868, 316, 'Use classroom materials purposefully and respectfully.', NULL, 3, 'A', NULL),
(869, 316, 'Manage transitions and adapt to changes in routine.', NULL, 4, 'A', NULL),
(870, 316, 'Develop positive responses to challenges.', NULL, 5, 'A', NULL),
(871, 317, 'Show interest and curiosity in learning new concepts and trying new activities and experiences.', NULL, 1, 'A', NULL),
(872, 317, 'Demonstrate ability to learn from experiences by applying prior knowledge to new situations.', NULL, 2, 'A', NULL),
(873, 317, 'Increase attention to a task or activity over time.', NULL, 3, 'A', NULL),
(874, 317, 'Seek and accept help when needed.', NULL, 4, 'A', NULL),
(875, 317, 'Attempt to complete a task in more than one way before asking for help.', NULL, 5, 'A', NULL),
(876, 318, 'Initiate and sustain interactions with other children.', NULL, 1, 'A', NULL),
(877, 318, 'Demonstrate verbal strategies for making a new friend.', NULL, 2, 'A', NULL),
(878, 318, 'Interact appropriately with other children and familiar adults by cooperating, helping, sharing, and expressing interest.', NULL, 3, 'A', NULL),
(879, 318, 'Participate successfully in group settings.', NULL, 4, 'A', NULL),
(880, 318, 'Demonstrate respectful and polite vocabulary.', NULL, 5, 'A', NULL),
(881, 318, 'Begin to recognize and respond to the needs, rights, and emotions of others.', NULL, 6, 'A', NULL),
(882, 319, 'Express feelings through appropriate gestures, actions, and words.', NULL, 1, 'A', NULL),
(883, 319, 'Recognize conflicts and seek possible solutions.', NULL, 2, 'A', NULL),
(884, 319, 'Allow others to take turns.', NULL, 3, 'A', NULL),
(885, 319, 'Increase the ability to share materials and toys with others over time.', NULL, 4, 'A', NULL),
(886, 319, 'Include others in play activities.', NULL, 5, 'A', NULL),
(887, 320, 'Understand the vocabulary of music.', NULL, 1, 'A', NULL),
(888, 320, 'Understand that written music represents sounds by using notes.', NULL, 2, 'A', NULL),
(889, 320, 'Understand that composers write music, musicians sing or play instruments, and dancers utilize music elements in expressing dance.', NULL, 3, 'A', NULL),
(890, 320, 'Identify common musical instruments.', NULL, 4, 'A', NULL),
(891, 321, 'Demonstrate the difference between singing and speaking.', NULL, 1, 'A', NULL),
(892, 321, 'Develop the understanding that the child’s body and voice are musical instruments.', NULL, 2, 'A', NULL),
(893, 321, 'Participate in opportunities to use singing voice and musical instruments.', NULL, 3, 'A', NULL),
(894, 321, 'Practice good manners when participating in musical performance.', NULL, 4, 'A', NULL),
(895, 321, 'Repeat simple musical patterns using voice, body, and instruments.', NULL, 5, 'A', NULL),
(896, 322, 'Understand that music comes from many different places in the world.', NULL, 1, 'A', NULL),
(897, 322, 'Understand that music sounds differently depending on who created it and when it was written.', NULL, 2, 'A', NULL),
(898, 322, 'Develop an appreciation for different types of music.', NULL, 3, 'A', NULL),
(899, 323, 'The child will talk about and compare musical patterns and sounds.', NULL, 1, 'A', NULL),
(900, 323, 'The child will recognize differences and similarities among music styles.', NULL, 2, 'A', NULL),
(901, 323, 'The child will explore the creation and purpose of music in personal and social life.', NULL, 3, 'A', NULL),
(902, 323, 'The child will participate in music activities that involve sharing, taking turns, and cooperation.', NULL, 4, 'A', NULL),
(903, 323, 'The child will identify types of music he/she prefers.', NULL, 5, 'A', NULL),
(904, 324, 'Use the body and motion to express a response to a musical selection.', NULL, 1, 'A', NULL),
(905, 324, 'Express a response to a musical selection by using available visual arts supplies.', NULL, 2, 'A', NULL),
(906, 324, 'Use words to describe how a musical selection makes the child feel.', NULL, 3, 'A', NULL),
(907, 325, 'Understand that artists create visual arts using many different tools.', NULL, 1, 'A', NULL),
(908, 325, 'Understand that the visual arts take many forms.', NULL, 2, 'A', NULL),
(909, 325, 'Use a variety of materials, textures, and tools for producing visual art.', NULL, 3, 'A', NULL),
(910, 325, 'Develop and use fine motor skills necessary to produce two- and three-dimensional works of art.', NULL, 4, 'A', NULL),
(911, 326, 'Understand that all cultures have art that reflects their experiences and identity.', NULL, 1, 'A', NULL),
(912, 326, 'Understand that works of art can be a historical record of a certain time period in history.', NULL, 2, 'A', NULL),
(913, 326, 'Develop an appreciation for the various forms of visual arts.', NULL, 3, 'A', NULL),
(914, 327, 'Use the body to express a response to a work of art.', NULL, 1, 'A', NULL),
(915, 327, 'Understand that each person responds to and creates works of art in unique ways.', NULL, 2, 'A', NULL),
(916, 327, 'Use available art supplies to express an individual response to an art form.', NULL, 3, 'A', NULL),
(917, 327, 'Use words to describe a response or reaction to a visual arts selection.', NULL, 4, 'A', NULL),
(918, 327, 'The child will identify types of works of art that he/she prefers.', NULL, 5, 'A', NULL),
(919, 328, 'Understand that the visual arts express feelings, experiences, and cultures.', NULL, 1, 'A', NULL),
(920, 328, 'Talk about different kinds of art and recognize the idea, theme, or purpose.', NULL, 2, 'A', NULL),
(921, 328, 'Create specific works of art based on a common theme, concept, or emotion.', NULL, 3, 'A', NULL),
(922, 328, 'Collect, compare, and use natural objects and objects made by people.', NULL, 4, 'A', NULL),
(923, 328, 'Understand the purpose of an art museum.', NULL, 5, 'A', NULL),
(924, 329, 'Make and express choices, plans and decisions.', NULL, 1, 'A', NULL),
(925, 329, 'Choose and complete challenging tasks.', NULL, 2, 'A', NULL),
(926, 329, 'Understand and follow rules and routines.', NULL, 3, 'A', NULL),
(927, 329, 'Accept changes in plans and schedules.', NULL, 4, 'A', NULL),
(928, 329, 'Demonstrate increasing ability to complete task and maintain concentration over time.', NULL, 5, 'A', NULL),
(929, 330, 'Demonstrate an eagerness and interest in learning.', NULL, 1, 'A', NULL),
(930, 330, 'Develop increasing ability to find more than one solution to a question or problem.', NULL, 2, 'A', NULL),
(931, 331, 'Understand and followspoken directions.', NULL, 1, 'A', NULL),
(932, 331, 'Listen attentively to stories or class discussions.', NULL, 2, 'A', NULL),
(933, 331, 'Demonstrate increased language comprehension skills by retelling or dictating stories from books and classroom experiences.', NULL, 3, 'A', NULL),
(934, 331, 'Begin to use pre-reading skills and strategies (ex.: prior knowledge to text, making predictions about text and using picture clues.', NULL, 4, 'A', NULL),
(935, 332, 'Discriminate and identify sounds in spoken language.', NULL, 1, 'A', NULL),
(936, 332, 'Recognize common sounds at the beginning of a series of words.', NULL, 2, 'A', NULL),
(937, 332, 'Identity syllables in words.', NULL, 3, 'A', NULL),
(938, 332, 'Identify words that rhyme.', NULL, 4, 'A', NULL),
(939, 333, 'Name a variety of pictures/objects and/or actions in the natural environment.', NULL, 1, 'A', NULL),
(940, 333, 'Use new and challenging vocabulary words correctly within the context of play or other classroom experiences.', NULL, 2, 'A', NULL),
(941, 333, 'Connect new vocabulary with prior educational experiences.', NULL, 3, 'A', NULL),
(942, 334, 'Express wants and needs.', NULL, 1, 'A', NULL),
(943, 334, 'Respond to questions.', NULL, 2, 'A', NULL),
(944, 334, 'Engage in conversations with peers and adults.', NULL, 3, 'A', NULL),
(945, 334, 'Increase length and grammatical complexity of sentences.', NULL, 4, 'A', NULL),
(946, 334, 'Participate in classroom activities that are repetitive in nature such as songs, rhymes, and finger plays.', NULL, 5, 'A', NULL),
(947, 334, 'Engage in storytelling and pretend play, using oral language.', NULL, 6, 'A', NULL),
(948, 334, 'Show progress in speaking English (for non-English speaking children).', NULL, 7, 'A', NULL),
(949, 335, 'Experiment with a variety of writing tools and materials.', NULL, 1, 'A', NULL),
(950, 335, 'Progress from using scribbles, shapes, or pictures to represent ideas, to using letters or letter-like symbols, or writing familiar words such as their own names.', NULL, 2, 'A', NULL),
(951, 336, 'Demonstrate an interest in books and exhibit appropriate book handling skills.', NULL, 1, 'A', NULL),
(952, 336, 'Show increasing awareness of environmental print in the classroom, home, and community.', NULL, 2, 'A', NULL),
(953, 336, 'Understand that writing is used as a form of communication for a variety of purposes.', NULL, 3, 'A', NULL),
(954, 336, 'Demonstrate increasing awareness that a word is a unit of print; that letters are grouped to form a word; and that words are separated by spaces.', NULL, 4, 'A', NULL),
(955, 336, 'Show progress in recognizing the association between spoken and written words by following print as it is read aloud.', NULL, 5, 'A', NULL),
(956, 337, 'Identify letters of the alphabet, especially letters in own name.', NULL, 1, 'A', NULL),
(957, 337, 'Show progress in identifying the names of letters and the sounds they represent. ', NULL, 2, 'A', NULL),
(958, 337, 'Demonstrate increased ability to recognize letters at the beginning of words.', NULL, 3, 'A', NULL),
(959, 338, 'Demonstrate use of one-to-one correspondence in counting objects and matching numeral name with sets of objects.', NULL, 1, 'A', NULL),
(960, 338, 'Show increasing ability to count in sequence to 10 and beyond.', NULL, 2, 'A', NULL),
(961, 338, 'Begin to understand the concept of estimation.', NULL, 3, 'A', NULL),
(962, 338, 'Use language to compare numbers of objects with terms such as more, less, equal to, greater than, or fewer than. ', NULL, 4, 'A', NULL),
(963, 338, 'Use ordinal number words to describe the position of objects (ex.: "first," "second," "third," etc.).', NULL, 5, 'A', NULL),
(964, 338, 'Begin to use numbers and counting as a means for solving problems and measuring quantity.', NULL, 6, 'A', NULL),
(965, 339, 'Recognize, describe, compare, and name common shapes, their parts, and attributes.', NULL, 1, 'A', NULL),
(966, 339, 'Use math language to indicate understanding of positional concepts.', NULL, 2, 'A', NULL),
(967, 339, 'Use classroom materials to combine shapes to create other shapes.', NULL, 3, 'A', NULL),
(968, 339, 'Begin to understand concept of "part" and "whole" using real objects.', NULL, 4, 'A', NULL),
(969, 340, 'Match, sort, place in a series, and regroup objects according to attributes (color, shape, size, etc.).', NULL, 1, 'A', NULL),
(970, 340, 'Describe, duplicate, and extend simple patterns using a variety of materials or objects.', NULL, 2, 'A', NULL),
(971, 340, 'Recognize and identify patterns in the environment. ', NULL, 3, 'A', NULL),
(972, 341, 'Use comparative/superlative terms to describe and contrast objects (ex.: long, longer, longest; short, shorter, shortest; small, medium, large).', NULL, 1, 'A', NULL),
(973, 341, 'Use standard and nonstandard measurement tools to determine length, volume, and weight of objects.', NULL, 2, 'A', NULL),
(974, 341, 'Demonstrate an understanding of measurable concepts of time and sequence.', NULL, 3, 'A', NULL),
(975, 342, 'Use math vocabulary to compare sets of objects with terms such as more, less, equal to, greater than, fewer.', NULL, 1, 'A', NULL),
(976, 342, 'Classify objects using more than one attribute.', NULL, 2, 'A', NULL),
(977, 342, 'Sort and classify objects using self-selected criteria.', NULL, 3, 'A', NULL),
(978, 342, 'Develop ability to collect, describe, and record information through drawings, maps, charts and graphs.', NULL, 4, 'A', NULL),
(979, 343, 'Use senses to gather information, classify objects, observe processes, and describe materials.', NULL, 1, 'A', NULL),
(980, 343, 'Make predictions and test ideas based on trial and error, observation, prior experience, demonstrations, and discussions.', NULL, 2, 'A', NULL),
(981, 343, 'Record observations using simple visual tools such as drawings, graphs, charts, logos.', NULL, 3, 'A', NULL),
(982, 343, 'Describe simple cause and effect relationships.', NULL, 4, 'A', NULL),
(983, 344, 'Investigate, explore, and compare objects in the classroom and on the playground.', NULL, 1, 'A', NULL),
(984, 344, 'Examine and describe the properties of solids and liquids.', NULL, 2, 'A', NULL),
(985, 344, 'Name and use simple machines in the context of daily play and problem-solving.', NULL, 3, 'A', NULL),
(986, 344, 'Explore and describe different types of speed, motion, and sounds.', NULL, 4, 'A', NULL),
(987, 344, 'Design and create items with simple tools.', NULL, 5, 'A', NULL),
(988, 345, 'Identify, describe and compare natural items from their immediate environment.', NULL, 1, 'A', NULL),
(989, 345, 'Demonstrate respect for preserving the environment.', NULL, 2, 'A', NULL),
(990, 345, 'Describe basic needs of how to care for living things.', NULL, 3, 'A', NULL),
(991, 345, 'Demonstrate knowledge of changes that plants and animals pass through during life cycles.', NULL, 4, 'A', NULL),
(992, 345, 'Identify and describe common animals and insects, and their natural habitats.', NULL, 5, 'A', NULL),
(993, 346, 'Identify four seasons and seasonal changes.', NULL, 1, 'A', NULL),
(994, 346, 'Identify types of weather and impact on environment. ', NULL, 2, 'A', NULL),
(995, 346, 'Identify and classify objects observed in the day sky and in the night sky.', NULL, 3, 'A', NULL),
(996, 346, 'Identify common earth materials and landforms.', NULL, 4, 'A', NULL),
(997, 346, 'Observe and describe light and shadows.', NULL, 5, 'A', NULL),
(998, 347, 'Demonstrate basic knowledge of computer skills.', NULL, 1, 'A', NULL),
(999, 347, 'Demonstrate knowledge of a variety of media and technology tools.', NULL, 2, 'A', NULL),
(1000, 347, 'Demonstrate knowledge of the use of technology as a communication system of the world.', NULL, 3, 'A', NULL),
(1001, 348, 'Display a healthy self image.', NULL, 1, 'A', NULL),
(1002, 348, 'Demonstrate awareness of attributes of self (abilities, characteristics and preferences).', NULL, 2, 'A', NULL),
(1003, 348, 'Demonstrate knowledge of self through recognition of body parts.', NULL, 3, 'A', NULL),
(1004, 348, 'Demonstrate growth in capacity for independence.', NULL, 4, 'A', NULL),
(1005, 349, 'Initiate play with other children.', NULL, 1, 'A', NULL);
INSERT INTO `framework_goals` (`goal_id`, `category_id`, `goal_description`, `goal_help`, `goal_sort`, `goal_status`, `goal_keywords`) VALUES
(1006, 349, 'Recognize and manage feelings and impulses in developmentally appropriate ways.', NULL, 2, 'A', NULL),
(1007, 349, 'Demonstrate the ability to control behavior when changing activities with class or group.', NULL, 3, 'A', NULL),
(1008, 349, 'Separate easily from family.', NULL, 4, 'A', NULL),
(1009, 350, 'Sustain interaction with peers by cooperating, playing and interacting.', NULL, 1, 'A', NULL),
(1010, 350, 'Understand how actions affect others and begin to accept consequences.', NULL, 2, 'A', NULL),
(1011, 350, 'Show increasing ability to use compromise and discussion to resolve conflict with peers.', NULL, 3, 'A', NULL),
(1012, 351, 'Show progress in understanding similarities and respecting differences in people.', NULL, 1, 'A', NULL),
(1013, 351, 'Show understanding and respect for the property of others.', NULL, 2, 'A', NULL),
(1014, 351, 'Develop an awareness of how actions positively affect the classroom environment.', NULL, 3, 'A', NULL),
(1015, 352, 'Develop and demonstrate strength and coordination of large muscles.', NULL, 1, 'A', NULL),
(1016, 352, 'Develop and demonstrate skills for walking.', NULL, 2, 'A', NULL),
(1017, 352, 'Develop and demonstrate skills for sitting.', NULL, 3, 'A', NULL),
(1018, 352, 'Develop and demonstrate skills for rolling.', NULL, 4, 'A', NULL),
(1019, 353, 'Develop and demonstrate strength and coordination of small muscles.', NULL, 1, 'A', NULL),
(1020, 353, 'Develop eye-hand coordination in a purposeful way.', NULL, 2, 'A', NULL),
(1021, 354, 'Wash and dry hands without assistance.', NULL, 1, 'A', NULL),
(1022, 354, 'Toilet independently.', NULL, 2, 'A', NULL),
(1023, 354, 'Brush teeth independently.', NULL, 3, 'A', NULL),
(1024, 354, 'Cover mouth and nose when sneezing and coughing.', NULL, 4, 'A', NULL),
(1025, 354, 'Manipulate clothing/fasteners.', NULL, 5, 'A', NULL),
(1026, 354, 'Put on/take off coat, socks, and shoes. ', NULL, 6, 'A', NULL),
(1027, 355, 'Follow mealtime routines and procedures.', NULL, 1, 'A', NULL),
(1028, 355, 'Open a food/drink container.', NULL, 2, 'A', NULL),
(1029, 355, 'Eat with a spoon or fork.', NULL, 3, 'A', NULL),
(1030, 355, 'Drink from an open cup.', NULL, 4, 'A', NULL),
(1031, 355, 'Identify healthy foods from basic food groups (meat, dairy, grains, fruits, vegetables).', NULL, 5, 'A', NULL),
(1032, 356, 'Demonstrate knowledge of personal safety.', NULL, 1, 'A', NULL),
(1033, 356, 'Recognize and know to avoid potentially harmful situations.', NULL, 2, 'A', NULL),
(1034, 356, 'Recognize and know to avoid potentially harmful substances.', NULL, 3, 'A', NULL),
(1035, 357, 'Children will use art for creative expression and representation.', NULL, 1, 'A', NULL),
(1036, 358, 'Use a variety of musical instruments, rhythms, and songs to develop creative expression.', NULL, 1, 'A', NULL),
(1037, 358, 'Participate in creative music and movement activities.', NULL, 2, 'A', NULL),
(1038, 358, 'Identify and appreciate different types of music from various cultures.', NULL, 3, 'A', NULL),
(1039, 359, 'Participate in dramatic play to express feelings, dramatize stories, reenact real-life roles and experiences.', NULL, 1, 'A', NULL),
(1040, 359, 'Engage in cooperative pretend play with another child using symbolic materials and gestures to represent real objects and situations.', NULL, 2, 'A', NULL),
(1041, 360, 'Enjoys the company of others and seeks contact with others from birth.', NULL, 1, 'A', 'enjoys company, social, seeks contact, enjoys touch, feeling, love, birth, infant'),
(1042, 360, 'Gazes at faces and copies facial movements. e.g. sticking out tongue, opening mouth and widening eyes.', NULL, 2, 'A', 'facial, movements, mimics, mimics adult, opens mouth, wide eyes, moves tongue, raises eyebrows, sticks out tongue'),
(1043, 360, 'Responds when talked to, for example, moves arms and legs, changes facial expression, moves body and makes mouth movements.', NULL, 3, 'A', 'responsive, voice, audio, expression, expressive, movement, arms, legs'),
(1044, 360, 'Recognises and is most responsive to main carer’s voice: face brightens, activity increases when familiar carer appears.', NULL, 4, 'A', 'responsive, voice, familiar, turns head, widens eyes, face brightens, joyful facial expression'),
(1045, 360, 'Responds to what carer is paying attention to, e.g. following their gaze.', NULL, 5, 'A', 'follows gaze, watches, watched, turned, followed, turns, head, eyes, move'),
(1046, 360, 'Likes cuddles and being held: calms, snuggles in, smiles, gazes at carer’s face or strokes carer’s skin.', NULL, 6, 'A', 'enjoys physical touch, likes to be held, smiles, cuddle, smiled, gazes happily'),
(1047, 361, 'Laughs and gurgles, e.g. shows pleasure at being tickled and other physical interactions.', NULL, 1, 'A', 'enjoys interaction, likes to be tickled, enjoys physical touch, laughs, laughed, laughing, gurgles, gurgled, gurgling, babbles, babbled, babbling, tickled'),
(1048, 361, 'Uses voice, gesture, eye contact and facial expression to make contact with people and keep their attention.', NULL, 2, 'A', 'attention,  eye-contact, voice, gesture, coos'),
(1049, 362, 'Turns toward a familiar sound then locates range of sounds with accuracy.', NULL, 1, 'A', 'recognises familiar sounds, locates sound, knows where sound is coming from, looked to see where the noise was coming from'),
(1050, 362, 'Listens to, distinguishes and responds to intonations and sounds of voices.', NULL, 2, 'A', 'responds, pitch, voice, sounds, sound'),
(1051, 362, 'Reacts in interaction with others by smiling, looking and moving.', NULL, 3, 'A', 'smiles, smiled , smiling, reacts, looks, interacts with, movement'),
(1052, 362, 'Quietens or alerts to the sound of speech.', NULL, 4, 'A', 'alert, listens , my voice'),
(1053, 362, 'Looks intently at a person talking, but stops responding if speaker turns away.', NULL, 5, 'A', NULL),
(1054, 362, 'Listens to familiar sounds, words, or finger plays.', NULL, 6, 'A', 'listens to music, listens to finger plays, listens to sound, finger play, familiar voice'),
(1055, 362, 'Fleeting Attention – not under child’s control, new stimuli takes whole attention.', NULL, 7, 'A', 'distracted, new, sound'),
(1056, 363, 'Is comforted by touch and people’s faces and voices.', NULL, 1, 'A', 'enjoys being around others, comforted, comfort, hugs, hugged, kissed, kisses, gentle, squeeze, squeezed, cuddled, cuddle'),
(1057, 363, 'Seeks physical and emotional comfort by snuggling in to trusted adults.', NULL, 2, 'A', 'seeks touch, cuddle, hugs, gentle'),
(1058, 363, 'Calms from being upset when held, rocked, spoken or sung to with soothing voice.', NULL, 3, 'A', 'rocked, calm, sung'),
(1059, 363, 'Shows a range of emotions such as pleasure, fear and excitement.', NULL, 4, 'A', 'excitement, fear, happy, smiled, pleasure, enjoy, enjoyed, excited, feared, afraid, frown, giggle, cries, cried, frowned, giggled'),
(1060, 363, 'Reacts emotionally to other people’s emotions, e.g. smiles when smiled at and becomes distressed if hears another child crying.', NULL, 5, 'A', 'reacts to emotion, smiles, frowns, giggles, cries'),
(1061, 364, 'Stops and looks when hears own name.', NULL, 1, 'A', 'knows name, name, responds to name'),
(1062, 364, 'Starts to understand contextual clues, e.g. familiar gestures, words and sounds.', NULL, 2, 'A', 'gestures, words, speak, spoken, sounds'),
(1063, 365, 'Communicates needs and feelings in a variety of ways including crying, gurgling, babbling and squealing.', NULL, 1, 'A', 'cries to communicate, gurgles to communicate, babbles, babbled, squealed, communicate, squeals'),
(1064, 365, 'Makes own sounds in response when talked to by familiar adults.', NULL, 2, 'A', 'makes sounds, creates sound in response to, made a sound in response to'),
(1065, 365, 'Lifts arms in anticipation of being picked up.', NULL, 3, 'A', 'lifts arms, picked up'),
(1066, 365, 'Practises and gradually develops speech sounds (babbling) to communicate with adults; says sounds like ‘baba, nono, gogo’.', NULL, 4, 'A', 'babbles, creates speech sounds, makes a sound, made sounds'),
(1067, 366, 'Turns toward a familiar sound then locates range of sounds with accuracy.', NULL, 1, 'A', 'recognises familiar sounds, locates sound, knows where sound is coming from, looked to see where the noise was coming from'),
(1068, 366, 'Listens to, distinguishes and responds to intonations and sounds of voices.', NULL, 2, 'A', 'responds, pitch, voice, sounds, sound'),
(1069, 366, 'Reacts in interaction with others by smiling, looking and moving.', NULL, 3, 'A', 'smiles, smiled , smiling, reacts, looks, interacts with, movement'),
(1070, 366, 'Quietens or alerts to the sound of speech.', NULL, 4, 'A', 'alert, listens , my voice'),
(1071, 366, 'Looks intently at a person talking, but stops responding if speaker turns away.', NULL, 5, 'A', NULL),
(1072, 366, 'Listens to familiar sounds, words, or finger plays.', NULL, 6, 'A', 'listens to music, listens to finger plays, listens to sound, finger play, familiar voice'),
(1073, 366, 'Fleeting Attention – not under child’s control, new stimuli takes whole attention.', NULL, 7, 'A', 'distracted, new, sound'),
(1074, 367, 'Turns head in response to sounds and sights.', NULL, 1, 'A', 'turns head, turned head, responds to sounds, responded to a sound, responded to'),
(1075, 367, 'Gradually develops ability to hold up own head.', NULL, 2, 'A', 'holds head up, held head up, held head'),
(1076, 367, 'Makes movements with arms and legs which gradually become more controlled.', NULL, 3, 'A', 'begins to control movement of arms and legs, controlled arms, controlled movement, controlled legs'),
(1077, 367, 'Rolls over from front to back, from back to front.', NULL, 4, 'A', 'can roll over, rolled over, roll over'),
(1078, 367, 'When lying on tummy becomes able to lift first head and then chest, supporting self with forearms and then straight arms.', NULL, 5, 'A', 'can sit up from laying, sat up, sits up, sit up'),
(1079, 367, 'Watches and explores hands and feet, e.g. when lying on back lifts legs into vertical position and grasps feet.', NULL, 6, 'A', 'explores feet while laying on back, vertical position, grasps feet, held feet'),
(1080, 367, 'Reaches out for, touches and begins to hold objects.', NULL, 7, 'A', 'reaches for objects, holds objects, reached, held'),
(1081, 367, 'Explores objects with mouth, often picking up an object and holding it to the mouth.', NULL, 8, 'A', 'mouth, mouth, licks, opens mouth'),
(1082, 368, 'Responds to and thrives on warm, sensitive physical contact and care.', NULL, 1, 'A', 'enjoys physical contact, physical contact, hug, cuddle, hugged, responds to warm, loving touch'),
(1083, 368, 'Expresses discomfort, hunger or thirst.', NULL, 2, 'A', 'expresses hunger, hunger, thirst, expresses thirst, thirsty, hungry'),
(1084, 368, 'Anticipates food routines with interest.', NULL, 3, 'A', 'breakfast, lunch, dinner, snack'),
(1085, 369, 'Enjoys looking at books and other printed material with familiar people.', NULL, 1, 'A', 'enjoys books, read to, read, likes looking at books with, loves books, loves to read, reading'),
(1086, 370, 'Notices changes in number of objects/images or sounds in group of up to 3.', NULL, 1, 'A', 'understands groups of 3, understands changes to groups of 3 or less, changes in number, changes in images, changes in a picture'),
(1087, 371, 'Has opportunities to develop their sensory awareness, opportunities to observe objects and their movements, and to play and explore.', NULL, 1, 'A', 'develops sensory awareness, sensory, senses, touch, smell, sight, observes, play, explore, engage, played, explored, observed'),
(1088, 372, 'Has opportunities to build strong attachments and relationships within the setting.', NULL, 1, 'A', 'interact, social, talk, spoke, talked, friends, group, attachments, attached'),
(1089, 373, 'Moves eyes, then head, to follow moving objects.', NULL, 1, 'A', 'follows objects with eyes, moves eyes, moved eyes'),
(1090, 373, 'Reacts with abrupt change when a face or object suddenly disappears from view.', NULL, 2, 'A', 'reacts to face, reacted to, reacts to'),
(1091, 373, 'Looks around a room with interest; visually scans environment for novel, interesting objects and events.', NULL, 3, 'A', 'shows interest in looking for object, shows interest in environment, showed interest in, looks around room, looked around, observed'),
(1092, 373, 'Smiles with pleasure at recognisable playthings.', NULL, 4, 'A', 'enjoys playing with familiar items, enjoys playing with'),
(1093, 373, 'Repeats actions that have an effect, e.g. kicking or hitting a mobile or shaking a rattle.', NULL, 5, 'A', 'shakes rattle for reaction, shakes rattle, shook rattle'),
(1094, 374, 'The beginnings of understanding technology lie in babies exploring and making sense of objects and how they behave. The child has opportunities to explore, makes sense of objects and how they behave in their environment.', NULL, 1, 'A', 'begin to understand of how objects behave, cause and effect, making sense of objects, explore, crawled'),
(1095, 377, 'Seeks to gain attention in a variety of ways, drawing others into social interaction.', NULL, 1, 'A', 'seeks attention, seeks attention, social, friends, attention, social interactions, social, interaction, attentive, smiling, babbling, eye-contact, touch'),
(1096, 377, 'Builds relationships with special people.', NULL, 2, 'A', 'builds relationships, best friend, new friend'),
(1097, 377, 'Is wary of unfamiliar people.', NULL, 3, 'A', NULL),
(1098, 377, 'Interacts with others and explores new situations when supported by familiar person.', NULL, 4, 'A', 'interacts with new people with support'),
(1099, 377, 'Shows interest in the activities of others and responds differently to children and adults, e.g. may be more interested in watching children than adults or may pay more attention when children talk to them.', NULL, 5, 'A', 'watching, listening, played on her own, played on his own, watched while we, watched others, watches'),
(1100, 378, 'Enjoys finding own nose, eyes or tummy as part of naming games.', NULL, 1, 'A', 'can find nose, eyes, naming game, own reflection, reflection, mirror, looked in the mirror, pointed to nose, pointed to mouth, pointed to eyes, pointed to face'),
(1101, 378, 'Learns that own voice and actions have effects on others.', NULL, 2, 'A', 'knows that voices, own voice, own actions, respond, responded'),
(1102, 378, 'Uses pointing with eye gaze to make requests, and to share an interest.', NULL, 3, 'A', 'uses eyes, gaze'),
(1103, 378, 'Engages other person to help achieve a goal, e.g. to get an object out of reach.', NULL, 4, 'A', 'engages with others for support'),
(1104, 379, 'Uses familiar adult to share feelings such as excitement or pleasure, and for ‘emotional refuelling’ when feeling tired, stressed or frustrated.', NULL, 1, 'A', 'shares emotion with familiar adult'),
(1105, 379, 'Growing ability to soothe themselves, and may like to use a comfort object.', NULL, 2, 'A', 'able to self soothe, soothe, teddy bear, blanket'),
(1106, 379, 'Cooperates with caregiving experiences, e.g. dressing.', NULL, 3, 'A', 'cooperates during daily tasks, daily routine, dressing, dressed'),
(1107, 379, 'Beginning to understand ‘yes’, ‘no’ and some boundaries.', NULL, 4, 'A', 'becoming familiar with meaning of ‘yes’ and ‘no’'),
(1108, 380, 'Moves whole bodies to sounds they enjoy, such as music or a regular beat.', NULL, 1, 'A', 'moves body to music, dance, music, sound, danced, dances, regular beat'),
(1109, 380, 'Has a strong exploratory impulse.', NULL, 2, 'A', NULL),
(1110, 380, 'Concentrates intently on an object or activity of own choosing for short periods.', NULL, 3, 'A', 'can concentrate, concentrates, concentrating, concentrated'),
(1111, 380, 'Pays attention to dominant stimulus â€“ easily distracted by noises or other people talking.', NULL, 4, 'A', NULL),
(1112, 381, 'Developing the ability to follow others’ body language, including pointing and gesture.', NULL, 1, 'A', 'follows gesture of others, pointing, names, point, peep-po, pat-a-cake, round and round the garden, row your boat'),
(1113, 381, 'Responds to the different things said when in a familiar context with a special person (e.g. ‘Where’s Mummy?’, ‘Where’s your nose?’).', NULL, 2, 'A', 'where’s mummy, where’s your nose, where’s daddy, where’s your hand, where are your eyes, where are you fingers, time for milk, responded'),
(1114, 381, 'Understanding of single words in context is developing, e.g. ‘cup’, ‘milk’, ‘daddy’.', NULL, 3, 'A', 'understands some words, cup, milk, mom, mum, dad, daddy, sister, teacher, you, him, her, them, we, she'),
(1115, 382, 'Uses sounds in play, e.g. ‘brrrm’ for toy car.', NULL, 1, 'A', 'makes sounds for objects, makes sounds, made a sound'),
(1116, 382, 'Uses single words.', NULL, 2, 'A', NULL),
(1117, 382, 'Frequently imitates words and sounds.', NULL, 3, 'A', 'imitates familiar sounds and words'),
(1118, 382, 'Enjoys babbling and increasingly experiments with using sounds and words to communicate for a range of purposes (e.g. teddy, more, no, bye-bye.)', NULL, 4, 'A', 'babbling, babbled, more, no, yes, mama, dada, teddy, bye'),
(1119, 382, 'Uses pointing with eye gaze to make requests, and to share an interest.', NULL, 5, 'A', 'uses eyes, gaze'),
(1120, 382, 'Creates personal words as they begin to develop language.', NULL, 6, 'A', 'makes up own words, language, words'),
(1121, 383, 'Sits unsupported on the floor.', NULL, 1, 'A', 'can sit unsupported, sits without support, sits without help, sits unaided, sat without support, sat unsupported'),
(1122, 383, 'When sitting, can lean forward to pick up small toys.', NULL, 2, 'A', 'can lean forward, leaned forward'),
(1123, 383, 'Pulls to standing, holding on to furniture or person for support.', NULL, 3, 'A', 'pulls himself up to stand, pulls herself up to stand, pulls himself, pulls herself'),
(1124, 383, 'Crawls, bottom shuffles or rolls continuously to move around.', NULL, 4, 'A', 'crawls or shuffles to move around'),
(1125, 383, 'Walks around furniture lifting one foot and stepping sideways (cruising), and walks with one or both hands held by adult.', NULL, 5, 'A', 'holds onto furniture while walking around it, cruises'),
(1126, 383, 'Takes first few steps independently.', NULL, 6, 'A', 'steps independently, took a few steps, takes a few steps, walked for the first time, walks for a few steps'),
(1127, 383, 'Passes toys from one hand to the other.', NULL, 7, 'A', 'passes toy between hands, passed toy from one hand to the other, passes toys from one hand to the other'),
(1128, 383, 'Holds an object in each hand and brings them together in the middle, e.g. holds two blocks and bangs them together.', NULL, 8, 'A', 'holds blocks, banged them together, holds cars in hand, held blocks, held lego'),
(1129, 383, 'Picks up small objects between thumb and fingers.', NULL, 9, 'A', 'uses thumb and fingers, can pick up a small, picks up, pincer grasp'),
(1130, 383, 'Enjoys the sensory experience of making marks in damp sand, paste or paint.', NULL, 10, 'A', 'holds pen to make random strokes, strokes, pencil, held pencil, held colouring'),
(1131, 383, 'Holds pen or crayon using a whole hand (palmar) grasp and makes random marks with different strokes.', NULL, 11, 'A', 'holds pen to make random strokes, strokes, pencil, held pencil, held colouring'),
(1132, 384, 'Opens mouth for spoon.', NULL, 1, 'A', 'opens mouth, opened mouth to, opens mouth for'),
(1133, 384, 'Holds own bottle or cup.', NULL, 2, 'A', 'holds bottle, held bottle, fed herself, fed himself, held a bottle, held his cup, held her cup, held her bottle'),
(1134, 384, 'Grasps finger foods and brings them to mouth.', NULL, 3, 'A', 'uses fingers to grasp food, uses fingers, grasped food, ate food by'),
(1135, 384, 'Attempts to use spoon: can guide towards mouth but food often falls off.', NULL, 4, 'A', 'attempts to use spoon, used spoon'),
(1136, 384, 'Can actively cooperate with nappy changing (lies still, helps hold legs up).', NULL, 5, 'A', 'cooperates during nappy changes, lies still, lies still, holds legs up'),
(1137, 384, 'Starts to communicate urination, bowel movement.', NULL, 6, 'A', 'i want to pee, i want to use the toilet, bathroom'),
(1138, 385, 'Handles books and printed material with interest.', NULL, 1, 'A', 'books, read, read, loves to read, loves books, likes to read, likes books, library'),
(1139, 386, 'Develops an awareness of number names through their enjoyment of action rhymes and songs that relate to their experience of numbers.', NULL, 1, 'A', 'songs, fingerplays, rhymes, songs, number names'),
(1140, 386, 'Has some understanding that things exist, even when out of sight.', NULL, 2, 'A', 'object permanence, peekaboo'),
(1141, 387, 'Recognises big things and small things in meaningful contexts.', NULL, 1, 'A', 'songs, fingerplays, rhymes, songs, number names'),
(1142, 387, 'Has some understanding that things exist, even when out of sight.', NULL, 2, 'A', 'object permanence, peekaboo'),
(1143, 388, 'Closely observes what animals, people and vehicles do. ', NULL, 1, 'A', 'watched, watches, observed'),
(1144, 388, 'Watches toy being hidden and tries to find it.', NULL, 2, 'A', 'looks for hidden toy, watches toy being hidden and tries to find it, object permanence'),
(1145, 388, 'Looks for dropped objects.', NULL, 3, 'A', 'actively looks for dropped object, looks for dropped'),
(1146, 388, 'Becomes absorbed in combining objects, e.g. banging two objects or placing objects into containers.', NULL, 4, 'A', 'combines objects, combining, placing, banging a toy, bangs toys, puts objects into different containers, bangs two objects together'),
(1147, 388, 'Knows things are used in different ways, e.g. a ball for rolling or throwing, a toy car for pushing.', NULL, 5, 'A', 'ball for throwing, pushed a toy car, threw a ball, rolled a ball'),
(1148, 389, 'Explores and experiments with a range of media through sensory exploration, and using whole body. ', NULL, 1, 'A', 'explores, sensory exploration'),
(1149, 389, 'Move their whole bodies to sounds they enjoy, such as music or a regular beat.', NULL, 2, 'A', 'moves body to, music, beat, danced, dances, moved, movement'),
(1150, 389, 'Imitates and improvises actions they have observed, e.g. clapping or waving.', NULL, 3, 'A', 'clapping, clapped, waved, waving'),
(1151, 389, 'Begins to move to music, listen to or join in rhymes or songs.', NULL, 4, 'A', 'listened to music, listened to songs, sang, sung, moved to music, danced, dances'),
(1152, 389, 'Notices and is interested in the effects of making movements which leave marks.', NULL, 5, 'A', NULL),
(1153, 390, 'Plays alongside others.', NULL, 1, 'A', 'plays alongside others, social, parallel play'),
(1154, 390, 'Uses a familiar adult as a secure base from which to explore independently in new environments, e.g. ventures away to play and interact with others, but returns for a cuddle or reassurance if becomes anxious.', NULL, 2, 'A', 'comes back to familiar adult when playing in new environment, explore, independently, explores independently, interacts with, explored, secure base'),
(1155, 390, 'Plays cooperatively with a familiar adult, e.g. rolling a ball back and forth.', NULL, 3, 'A', 'enjoys playing with, co-operate, rolls ball, rolled ball, back and forth, rolled ball to'),
(1156, 391, 'Explores new toys and environments, but ‘checks in’ regularly with familiar adult as and when needed.', NULL, 1, 'A', 'enjoys independent exploration but checks in with adult'),
(1157, 391, 'Gradually able to engage in pretend play with toys (supports child to understand their own thinking may be different from others).', NULL, 2, 'A', 'begins to pretend play, pretend play, role play, pretends'),
(1158, 391, 'Demonstrates sense of self as an individual, e.g. wants to do things independently, says â€œNoâ€ to adult.', NULL, 3, 'A', NULL),
(1159, 392, 'Is aware of others’ feelings, for example, looks concerned if hears crying or looks excited if hears a familiar happy voice.', NULL, 1, 'A', 'aware of others feelings'),
(1160, 392, 'Growing sense of will and determination may result in feelings of anger and frustration which are difficult to handle, e.g. may have tantrums.', NULL, 2, 'A', 'determination, determined'),
(1161, 392, 'Responds to a few appropriate boundaries, with encouragement and support.', NULL, 3, 'A', 'responds appropriately to boundaries, boundaries, rules'),
(1162, 392, 'Plays alongside others.', NULL, 4, 'A', 'plays alongside others, social, parallel play'),
(1163, 393, 'Listens to and enjoys rhythmic patterns in rhymes and stories.', NULL, 1, 'A', 'enjoys rhythmic patterns, rhyme, story, beat, sound'),
(1164, 393, 'Enjoys rhymes and demonstrates listening by trying to join in with actions or vocalisations.', NULL, 2, 'A', 'enjoys rhythmic patterns, rhyme, story, beat, sound, action game, joining in'),
(1165, 393, 'Rigid attention â€“ may appear not to hear.', NULL, 3, 'A', NULL),
(1166, 394, 'Selects familiar objects by name and will go and find objects when asked, or identify objects from a group.', NULL, 1, 'A', 'can select familiar object by name'),
(1167, 394, 'Understands simple sentences (e.g. ‘Throw the ball.’)', NULL, 2, 'A', 'understands simple sentences, simple sentence, throw the ball, understands'),
(1168, 395, 'Copies familiar expressions, e.g. ‘Oh dear’, ‘All gone’.', NULL, 1, 'A', 'oh dear, all gone, uh-oh, oh no, all fall down'),
(1169, 395, 'Beginning to put two words together (e.g. ‘want ball’, ‘more juice’).', NULL, 2, 'A', 'puts two words together, want more, more please, more juice, i want'),
(1170, 395, 'Uses different types of everyday words (nouns, verbs and adjectives, e.g. banana, go, sleep, hot).', NULL, 3, 'A', 'banana, go, sleep, hot, no, cold, book, more, please, thank you, hi, hello, bye, uh-oh, all gone, choo-choo, tree, apple, birdie, dog, cat, teddy bear, hat, coat, scarf, shoes, wellies, rain'),
(1171, 395, 'Beginning to ask simple questions.', NULL, 4, 'A', 'asked, asks, question, questions'),
(1172, 395, 'Beginning to talk about people and things that are not present.', NULL, 5, 'A', 'talks about people and things that are not present'),
(1173, 396, 'Walks upstairs holding hand of adult.', NULL, 1, 'A', 'walks upstairs holding'),
(1174, 396, 'Comes downstairs backwards on knees (crawling).', NULL, 2, 'A', 'comes downstairs backwards'),
(1175, 396, 'Beginning to balance blocks to build a small tower.', NULL, 3, 'A', 'begins to balance blocks, built a tower, played with blocks'),
(1176, 396, 'Makes connections between their movement and the marks they make.', NULL, 4, 'A', NULL),
(1177, 397, 'Develops own likes and dislikes in food and drink.', NULL, 1, 'A', 'i like this, i don’t like this, yuck, yum'),
(1178, 397, 'Willing to try new food textures and tastes.', NULL, 2, 'A', 'he tried, new, new food'),
(1179, 397, 'Holds cup with both hands and drinks without much ...', NULL, 3, 'A', 'holds cup with two hands, held cup, holds cup'),
(1180, 397, 'Clearly communicates wet or soiled nappy or pants.', NULL, 4, 'A', 'able to communicate soiled nappy or pants'),
(1181, 397, 'Shows some awareness of bladder and bowel urges.', NULL, 5, 'A', 'asserts awareness of toilet needs, i want to use the toilet, wanted to pee, toilet, potty'),
(1182, 397, 'Shows awareness of what a potty or toilet is used for.', NULL, 6, 'A', 'shows understanding of the toilet is used for'),
(1183, 397, 'Shows a desire to help with dressing/undressing and hygiene routines.', NULL, 7, 'A', 'dress, undress, dressed himself, put on his jacket, put on his coat, put on his shoes, put on his hat, put on his gloves, put on his, put on her coat, dressed herself, put on her jacket, put on her clothes, put on his clothes, washed her hands, washes her hands, washed his hands, washes his hands'),
(1184, 398, 'Interested in books and rhymes and may have favourites', NULL, 1, 'A', 'shows interest in books, favourite book'),
(1185, 399, 'Knows that things exist, even when out of sight.', NULL, 1, 'A', 'object permanence, knows things exist'),
(1186, 399, 'Beginning to organise and categorise objects, e.g. putting all the teddy bears together or teddies and cars in separate piles.', NULL, 2, 'A', 'can organise similar objects, categorise, similar'),
(1187, 399, 'Says some counting words randomly.', NULL, 3, 'A', 'says some counting words randomly, counting'),
(1188, 400, 'Attempts, sometimes successfully, to fit shapes into spaces on inset boards or jigsaw puzzles.', NULL, 1, 'A', 'jigsaw, puzzle, shapes'),
(1189, 400, 'Uses blocks to create their own simple structures and arrangements.', NULL, 2, 'A', 'uses blocks, used blocks'),
(1190, 400, 'Enjoys filling and emptying containers.', NULL, 3, 'A', 'emptying, filling'),
(1191, 400, 'Associates a sequence of actions with daily routines.', NULL, 4, 'A', 'asked, asks, question, questions'),
(1192, 400, 'Beginning to understand that things might happen ‘now’.', NULL, 5, 'A', 'talks about people and things that are not present'),
(1193, 401, 'Is curious about people and shows interest in stories about themselves and their family.', NULL, 1, 'A', 'shows interest in learning more about her family, family, shows interest in herself'),
(1194, 401, 'Enjoys pictures and stories about themselves, their families and other people.', NULL, 2, 'A', 'enjoys looking at books and pictures about their family and other people'),
(1195, 402, 'Explores objects by linking together different approaches: shaking, hitting, looking, feeling, tasting, mouthing, pulling, turning and poking.', NULL, 1, 'A', 'uses all senses to discover objects, senses, touch, sight, taste, smell, shakes, hits, looks, feels, tastes, pulls, pokes, poked, tasted, looked, shook, pulled, mouthing'),
(1196, 402, 'Remembers where objects belong.', NULL, 2, 'A', 'tidied up, put it back'),
(1197, 402, 'Matches parts of objects that fit together, e.g. puts lid on teapot.', NULL, 3, 'A', 'understands what objects go together, matches, matched, puts lid on teapot, puts lid, key into the door'),
(1198, 403, 'Anticipates repeated sounds, sights and actions, e.g. when an adult demonstrates an action toy several times.', NULL, 1, 'A', 'sight, action, repeated sounds'),
(1199, 403, 'Shows interest in toys with buttons, flaps and simple mechanisms and beginning to learn to operate them.', NULL, 2, 'A', NULL),
(1200, 404, 'Explores and experiments with a range of media through sensory exploration, and using whole body.', NULL, 1, 'A', 'explores, sensory exploration'),
(1201, 404, 'Move their whole bodies to sounds they enjoy, such as music or a regular beat.', NULL, 2, 'A', 'moves body to, music, beat, danced, dances, moved, movement'),
(1202, 404, 'Imitates and improvises actions they have observed, e.g. clapping or waving.', NULL, 3, 'A', 'clapping, clapped, waved, waving'),
(1203, 404, 'Begins to move to music, listen to or join in rhymes or songs.', NULL, 4, 'A', 'listened to music, listened to songs, sang, sung, moved to music, danced, dances'),
(1204, 404, 'Notices and is interested in the effects of making movements which leave marks.', NULL, 5, 'A', NULL),
(1205, 405, 'Expresses self through physical action and sound.', NULL, 1, 'A', 'expressed, physical, sound'),
(1206, 405, 'Pretends that one object represents another, especially when objects have characteristics in common.', NULL, 2, 'A', 'pretend play, wand, role play, pretended, unicorn, dragon, fairy, leprechaun'),
(1207, 406, 'Interested in others’ play and starting to join in.', NULL, 1, 'A', 'played with, group play, circle time'),
(1208, 406, 'Seeks out others to share experiences.', NULL, 2, 'A', 'seeks out others'),
(1209, 406, 'Shows affection and concern for people who are special to them.', NULL, 3, 'A', 'shows affection, shows concern , showed concern, affectionate, hugs, cuddled'),
(1210, 406, 'May form a special friendship with another child.', NULL, 4, 'A', 'makes friendships with others, friends, best friend'),
(1211, 407, 'Separates from main carer with support and encouragement from a familiar adult.', NULL, 1, 'A', NULL),
(1212, 407, 'Expresses own preferences and interests.', NULL, 2, 'A', 'interests, expressed'),
(1213, 408, 'Seeks comfort from familiar adults when needed.', NULL, 1, 'A', NULL),
(1214, 408, 'Can express their own feelings such as sad, happy, cross, scared, worried.', NULL, 2, 'A', NULL),
(1215, 408, 'Responds to the feelings and wishes of others.', NULL, 3, 'A', NULL),
(1216, 408, 'Aware that some actions can hurt or harm others.', NULL, 4, 'A', NULL),
(1217, 408, 'Tries to help or give comfort when others are distressed.', NULL, 5, 'A', NULL),
(1218, 408, 'Shows understanding and cooperates with some boundaries and routines.', NULL, 6, 'A', NULL),
(1219, 408, 'Can inhibit own actions/behaviours, e.g. stop themselves from doing something they shouldn’t do.', NULL, 7, 'A', NULL),
(1220, 408, 'Growing ability to distract self when upset, e.g. by engaging in a new play activity.', NULL, 8, 'A', NULL),
(1221, 409, 'Listens with interest to the noises adults make when they read stories.', NULL, 1, 'A', 'listens with interest, read a story, reads, reading'),
(1222, 409, 'Recognises and responds to many familiar sounds, e.g. turning to a knock on the door, looking at or going to the door.', NULL, 2, 'A', 'responds to familiar noises, knock on the door, knock on the window, familiar sound'),
(1223, 409, 'Shows interest in play with sounds, songs and rhymes.', NULL, 3, 'A', 'sounds, songs, rhymes'),
(1224, 409, 'Single channelled attention. Can shift to a different task if attention fully obtained – using child’s name helps focus.', NULL, 4, 'A', NULL),
(1225, 410, 'Identifies action words by pointing to the right picture, e.g., “Who’s jumping?”', NULL, 1, 'A', 'identifies action words in text, action words, ran, jumped, climbed'),
(1226, 410, 'Understands more complex sentences, e.g. ‘Put your toys away and then we’ll read a book.’', NULL, 2, 'A', 'understands'),
(1227, 410, 'Understands ‘who’, ‘what’, ‘where’ in simple questions (e.g. Who’s that/can? What’s that? Where is.?).', NULL, 3, 'A', 'who, what, where who’s that, what’s that, understands simple sentences'),
(1228, 410, 'Developing understanding of simple concepts (e.g. big/little)', NULL, 4, 'A', 'big, little, small, large, tiny'),
(1229, 411, 'Uses language as a powerful means of widening contacts, sharing feelings, experiences and thoughts.', NULL, 1, 'A', 'uses language to communicate, shares feelings, shares thoughts, shares experiences, feelings, thought, think, communicate'),
(1230, 411, 'Holds a conversation, jumping from topic to topic.', NULL, 2, 'A', 'conversation, talks, talked, discussed, discussion'),
(1231, 411, 'Learns new words very rapidly and is able to use them in communicating.', NULL, 3, 'A', 'learns new words quickly'),
(1232, 411, 'Uses gestures, sometimes with limited talk, e.g. reaches toward toy, saying ‘I have it’.', NULL, 4, 'A', 'gestures, i have it, i want it'),
(1233, 411, 'Uses a variety of questions (e.g. what, where, who).', NULL, 5, 'A', 'questions'),
(1234, 411, 'Uses simple sentences (e.g.’ Mummy gonna work.’).', NULL, 6, 'A', 'uses simple sentences'),
(1235, 411, 'Beginning to use word endings (e.g. going, cats).', NULL, 7, 'A', NULL),
(1236, 412, 'Runs safely on whole foot.', NULL, 1, 'A', 'runs safely on whole foot, whole foot'),
(1237, 412, 'Squats with steadiness to rest or play with object on the ground, and rises to feet without using hands.', NULL, 2, 'A', 'squats without using hands, squats'),
(1238, 412, 'Climbs confidently and is beginning to pull themselves up on nursery play climbing equipment.', NULL, 3, 'A', 'climbs confidently, climbed, climbed'),
(1239, 412, 'Can kick a large ball.', NULL, 4, 'A', 'can kick a large ball, kicked a ball'),
(1240, 412, 'Turns pages in a book, sometimes several at once.', NULL, 5, 'A', 'turns pages in a book, book'),
(1241, 412, 'Shows control in holding and using jugs to pour, hammers, books and mark-making tools.', NULL, 6, 'A', 'pouring, filling, hammer, books, jug, holding'),
(1242, 412, 'Beginning to use three fingers (tripod grip) to hold writing tools.', NULL, 7, 'A', 'tripod grasp, tripod grip, writing, pencil, crayon, paint brush'),
(1243, 412, 'Imitates drawing simple shapes such as circles and lines.', NULL, 8, 'A', 'attempts to draw circles and lines, drew a circle, drew a line'),
(1244, 412, 'Walks upstairs or downstairs holding onto a rail two feet to a step.', NULL, 9, 'A', 'walks up the stairs, downstairs'),
(1245, 412, 'May be beginning to show preference for dominant hand.', NULL, 10, 'A', 'dominant hand, right hand, left hand'),
(1246, 413, 'Feeds self competently with spoon.', NULL, 1, 'A', 'uses spoon appropriately'),
(1247, 413, 'Drinks well without spilling.', NULL, 2, 'A', 'drinks without spilling'),
(1248, 413, 'Clearly communicates their need for potty or toilet.', NULL, 3, 'A', 'communicates bathroom needs, potty, toilet, bathroom'),
(1249, 413, 'Beginning to recognise danger and seeks support of significant adults for help.', NULL, 4, 'A', 'seeks help when danger arises'),
(1250, 413, 'Helps with clothing, e.g. puts on hat, unzips zipper on jacket, takes off unbuttoned shirt.', NULL, 5, 'A', 'assists with dressing self'),
(1251, 413, 'Beginning to be independent in self-care, but still often needs adult support.', NULL, 6, 'A', 'shows self-care independence but needs help'),
(1252, 414, 'Has some favourite stories, rhymes, songs, poems or jingles.', NULL, 1, 'A', 'favourite story'),
(1253, 414, 'Repeats words or phrases from familiar stories.', NULL, 2, 'A', 'repeats familiar stories'),
(1254, 414, 'Fills in the missing word or phrase in a known rhyme, story or game, e.g. ‘Humpty Dumpty sat on a …’.', NULL, 3, 'A', 'fills in missing nursery rhyme lyric or poem'),
(1255, 415, 'Distinguishes between the different marks they make.', NULL, 1, 'A', 'distinguishes between the different marks they make.'),
(1256, 416, 'Selects a small number of objects from a group when asked, for example, ‘please give me one’, ‘please give me two’.', NULL, 1, 'A', 'selects objects when asked, give me'),
(1257, 416, 'Recites some number names in sequence.', NULL, 2, 'A', 'number names, counting, counted, counts'),
(1258, 416, 'Creates and experiments with symbols and marks representing ideas of number. ', NULL, 3, 'A', 'makes marks to represent idea of numbers, mark making, symbol, experimenting'),
(1259, 416, 'Begins to make comparisons between quantities.', NULL, 4, 'A', 'compares quantities'),
(1260, 416, 'Uses some language of quantities, such as ‘more’ and ‘a lot’.', NULL, 5, 'A', 'uses words “more” “less” a lot”'),
(1261, 416, 'Knows that a group of things changes in quantity when something is added or taken away.', NULL, 6, 'A', 'quantity, conservation'),
(1262, 417, 'Notices simple shapes and patterns in pictures.', NULL, 1, 'A', 'shapes and patterns in pictures'),
(1263, 417, 'Beginning to categorise objects according to properties such as shape or size.', NULL, 2, 'A', 'shape, size'),
(1264, 417, 'Begins to use the language of size.', NULL, 3, 'A', 'sizes, size'),
(1265, 417, 'Understands some talk about immediate past and future, e.g. ‘before’, ‘later’ or ‘soon’.', NULL, 4, 'A', 'understands past and future, see you later, see you soon, before, later, past, future'),
(1266, 417, 'Anticipates specific time-based events such as mealtimes or home time.', NULL, 5, 'A', 'anticipates events, mealtimes, home time, circle time'),
(1267, 418, 'Has a sense of own immediate family and relations.', NULL, 1, 'A', 'siblings, sister, brother, mum, mummy, daddy, dad, grand-dad, grandpa, grandma, grandmother, grandfather, cousin, auntie, aunt, uncle'),
(1268, 418, 'In pretend play, imitates everyday actions and events from own family and cultural background, e.g. making and drinking tea.', NULL, 2, 'A', 'imitates everyday actions, drinking tea, brushing teeth, cooking, role play, home corner, recycling at home'),
(1269, 418, 'Beginning to have their own friends.', NULL, 3, 'A', 'peers, friend'),
(1270, 418, 'Learns that they have similarities and differences that connect them to, and distinguish them from, others.', NULL, 4, 'A', NULL),
(1271, 419, 'Enjoys playing with small-world models such as a farm, a garage, or a train track.', NULL, 1, 'A', 'garage, train track, role play, pretend, dolls house, farm animals, dinosaurs'),
(1272, 419, 'Notices detailed features of objects in their environment.', NULL, 2, 'A', 'exploring, seasons, planting, tadpoles, frogs, trees, nature, grass, sky, birds'),
(1273, 420, 'Seeks to acquire basic skills in turning on and operating some ICT equipment.', NULL, 1, 'A', 'turns on toys, turns off toys, explores electronic toys'),
(1274, 420, 'Operates mechanical toys, e.g. turns the knob on a wind-up toy or pulls back on a friction car.', NULL, 2, 'A', 'turns knob, winds up toy, mechanical toys, operates toy'),
(1275, 421, 'Joins in singing favourite songs.', NULL, 1, 'A', 'sings songs, joins in singing, favourite songs'),
(1276, 421, 'Creates sounds by banging, shaking, tapping or blowing.', NULL, 2, 'A', 'creates sound, bangs, shakes, taps, blows, hits, claps'),
(1277, 421, 'Shows an interest in the way musical instruments sound.', NULL, 3, 'A', 'musical instruments, sounds, interest in sounds'),
(1278, 421, 'Experiments with blocks, colours and marks.', NULL, 4, 'A', 'makes towers, uses blocks, uses colours, makes marks'),
(1279, 422, 'Beginning to use representation to communicate, e.g. drawing a line and saying ‘That’s me.’', NULL, 1, 'A', 'representation through imagination'),
(1280, 422, 'Beginning to make-believe by pretending.', NULL, 2, 'A', 'make believe, pretend play'),
(1281, 423, 'Can play in a group, extending and elaborating play ideas, e.g. building up a role-play activity with other children.', NULL, 1, 'A', 'role plays with others, make believe, takes direction from others, imagines play'),
(1282, 423, 'Initiates play, offering cues to peers to join them.', NULL, 2, 'A', 'initiates play, invites others, gives cues, joins others to play'),
(1283, 423, 'Keeps play going by responding to what others are saying or doing.', NULL, 3, 'A', 'engages in play, keeps playing, stays playing'),
(1284, 423, 'Demonstrates friendly behaviour, initiating conversations and forming good relationships with peers and familiar adults.', NULL, 4, 'A', 'forms relationships, is friendly, initiates conversation, initiates play with peers, initiates play with adults'),
(1285, 424, 'Can select and use activities and resources with help.', NULL, 1, 'A', 'selects activities, selects resources, selects toys with help, uses activities, uses resources, choice, chose, choose, choices, selected'),
(1286, 424, 'Welcomes and values praise for what they have done.', NULL, 2, 'A', 'values praise, enjoys praise, likes being told “good job”, feels good when praised'),
(1287, 424, 'Enjoys responsibility of carrying out small tasks.', NULL, 3, 'A', NULL),
(1288, 424, 'Is more outgoing towards unfamiliar people and more confident in new social situations.', NULL, 4, 'A', 'outgoing, outgoing with unfamiliar people, confident, confident in social situations, asserts independence in new situations'),
(1289, 424, 'Confident to talk to other children when playing, and will communicate freely about own home and community.', NULL, 5, 'A', 'communicates easily, communication, played with, good language skills, communicates with peers, communicates about home or school, talks when playing, confidently talks about home, talking'),
(1290, 424, 'Shows confidence in asking adults for help.', NULL, 6, 'A', 'asks for help, can ask for help, confident, asked for help'),
(1291, 425, 'Aware of own feelings, and knows that some actions and words can hurt others’ feelings.', NULL, 1, 'A', 'raware of self, aware of feelings, feelings, expression, expressed, actions, words, actions can hurt, words can hurt'),
(1292, 425, 'Begins to accept the needs of others and can take turns and share resources, sometimes with support from others.', NULL, 2, 'A', 'share, needs of others, take turns, support others, sharing, shared'),
(1293, 425, 'Can usually tolerate delay when needs are not immediately met, and understands wishes may not always be met.', NULL, 3, 'A', 'patience, tolerance'),
(1294, 425, 'Can usually adapt behaviour to different events, social situations and changes in routine.', NULL, 4, 'A', 'appropriate behaviour, adaptable behaviour, understands social situations, behave, routine, social'),
(1295, 426, 'Listens to others one to one or in small groups, when conversation interests them.', NULL, 1, 'A', 'listens, listens to peers, understands conversation, shows interest in conversation'),
(1296, 426, 'Listens to stories with increasing attention and recall.', NULL, 2, 'A', 'listens to stories, pays attention, recall,  attentive, attention'),
(1297, 426, 'Joins in with repeated refrains and anticipates key events and phrases in rhymes and stories.', NULL, 3, 'A', 'phrases, stories, rhymes, rhyming'),
(1298, 426, 'Focusing attention â€“ still listen or do, but can shift own attention.', NULL, 4, 'A', NULL),
(1299, 426, 'Is able to follow directions (if not intently focused on own choice of activity).', NULL, 5, 'A', 'follows direction, choice, chose'),
(1300, 427, 'Understands use of objects (e.g. “What do we use to cut things?’)', NULL, 1, 'A', 'objects, use of objects, understands objects, understands tools, understands use of everyday tools, scissors, paint brush, pencil, crayon'),
(1301, 427, 'Shows understanding of prepositions such as ‘under’, ‘on top’, ‘behind’ by carrying out an action or selecting correct picture.', NULL, 2, 'A', 'understands prepositions, under, top, behind, below, beside, in front, next to'),
(1302, 427, 'Responds to simple instructions, e.g. to get or put away an object.', NULL, 3, 'A', 'instructions, simple instructions, directions, understands directions, 1 step directions'),
(1303, 427, 'Beginning to understand ‘why’ and ‘how’ questions.', NULL, 4, 'A', 'Beginning to understand ‘why’ and ‘how’ questions.'),
(1304, 428, 'Beginning to use more complex sentences to link thoughts (e.g. using and, because).', NULL, 1, 'A', 'complex sentences, long sentences, linking thoughts'),
(1305, 428, 'Can retell a simple past event in correct order (e.g. went down slide, hurt finger).', NULL, 2, 'A', 'retell story, order of events, recall past event, recall, retell, retold, told us, explained that'),
(1306, 428, 'Uses talk to connect ideas, explain what is happening and anticipate what might happen next, recall and relive past experiences.', NULL, 3, 'A', 'connects ideas, tell stories, relives past, anticipate future, uses speech, retell experiences'),
(1307, 428, 'Questions why things happen and gives explanations. Asks e.g. who, what, when, how.', NULL, 4, 'A', 'understands questions, asks questions, who, what, when, why, how, explains things'),
(1308, 428, 'Uses a range of tenses (e.g. play, playing, will play, played).', NULL, 5, 'A', 'uses tenses appropriately, past tense, future tense, tenses'),
(1309, 428, 'Uses intonation, rhythm and phrasing to make the meaning clear to others.', NULL, 6, 'A', 'intonation, rhythm, phrases, communicates clearly, expresses self through speech'),
(1310, 428, 'Uses vocabulary focused on objects and people that are of particular importance to them.', NULL, 7, 'A', 'vocabulary, familiar vocabulary, important vocabulary'),
(1311, 428, 'Builds up vocabulary that reflects the breadth of their experiences.', NULL, 8, 'A', 'variety of vocabulary to describe, describes experiences'),
(1312, 428, 'Uses talk in pretending that objects stand for something else in play, e,g, ‘This box is my castle.’', NULL, 9, 'A', 'talk to pretend, imagination, pretend play, pretending, role play, wand, castle'),
(1313, 429, 'Moves freely and with pleasure and confidence in a range of ways, such as slithering, shuffling, rolling, crawling, walking, running, jumping, skipping, sliding and hopping.', NULL, 1, 'A', 'gross motor, moves freely, moves confidently, runs, running, ran, shuffles, shuffled, rolls, rolling, rolled, jumps, jumping, jumped, crawls, crawling, crawled, walks, walking, walked, skips, skipping, skipped, hops, hopping, hopped'),
(1314, 429, 'Mounts stairs, steps or climbing equipment using alternate feet.', NULL, 2, 'A', 'alternate feet, climbs stairs, climbs ladder, climbs equipment'),
(1315, 429, 'Walks downstairs, two feet to each step while carrying a small object.', NULL, 3, 'A', 'walks downstairs, holding object, stairs, two feet to each step'),
(1316, 429, 'Runs skilfully and negotiates space successfully, adjusting speed or direction to avoid obstacles.', NULL, 4, 'A', 'runs, runs skilfully, ran fast, running, avoids obstacles, coordination, coordinated'),
(1317, 429, 'Can stand momentarily on one foot when shown.', NULL, 5, 'A', 'stand on one foot, balance, hop, hopped, hopping'),
(1318, 429, 'Can catch a large ball.', NULL, 6, 'A', 'catch, can catch a large ball, catching a large ball, hand-eye coordination'),
(1319, 429, 'Draws lines and circles using gross motor movements.', NULL, 7, 'A', 'gross motor movement, gross motor'),
(1320, 429, 'Uses one-handed tools and equipment, e.g. makes snips in paper with child scissors.', NULL, 8, 'A', 'one handed movements, one handed tools, scissors, hammer, pen, paintbrush, dotter, makes snips in paper, cut paper, cutting, spoon, fork, knife, tracing, copying, building with blocks, jigsaw, puzzles, pegboards, wooden beads, chalk, colouring, pouring water into containers, pouring water, dressing, undressing dolls, large zippers, snaps, laces, clay, pasting'),
(1321, 429, 'Holds pencil between thumb and two fingers, no longer using whole-hand grasp.', NULL, 9, 'A', 'pincer grip, holds pencil, uses thumb and fingers, fine motor'),
(1322, 429, 'Holds pencil near point between first two fingers and thumb and uses it with good control.', NULL, 10, 'A', 'control of fine motor, holds pencil near point'),
(1323, 429, 'Can copy some letters, e.g. letters from their name.', NULL, 11, 'A', 'copy some letters, knows some letters in name, letter names, copy letters'),
(1324, 430, 'Can tell adults when hungry or tired or when they want to rest or play.', NULL, 1, 'A', 'hungry, tired, rest, play, bathroom, hurt');
INSERT INTO `framework_goals` (`goal_id`, `category_id`, `goal_description`, `goal_help`, `goal_sort`, `goal_status`, `goal_keywords`) VALUES
(1325, 430, 'Observes the effects of activity on their bodies.', NULL, 2, 'A', 'activity'),
(1326, 430, 'Understands that equipment and tools have to be used safely.', NULL, 3, 'A', 'tools used safely, equipment safety, safety, understands safety, understands danger with tools, understands danger with equipment'),
(1327, 430, 'Gains more bowel and bladder control and can attend to toileting needs most of the time themselves.', NULL, 4, 'A', 'bladder control, bathroom independence, bathroom on his own, bathroom on her own, toilet on her own, toilet on his own, toilet by himself, toilet by herself'),
(1328, 430, 'Can usually manage washing and drying hands.', NULL, 5, 'A', 'wash hands, dry hands'),
(1329, 430, 'Dresses with help, e.g. puts arms into open-fronted coat or shirt when held up, pulls up own trousers, and pulls up zipper once it is fastened at the bottom.', NULL, 6, 'A', 'getting dressed, pulls zipper, pulls up/down trousers, velcro shoes, pulls on socks, zipper, dressed, removed coat, put jacket on'),
(1330, 431, 'Enjoys rhyming and rhythmic activities.', NULL, 1, 'A', 'rhyming, rhythmic, rhythmic activities, rhymed'),
(1331, 431, 'Shows awareness of rhyme and alliteration.', NULL, 2, 'A', 'aware of rhyme, aware of alliteration'),
(1332, 431, 'Recognises rhythm in spoken words.', NULL, 3, 'A', 'rhythm, spoken words'),
(1333, 431, 'Listens to and joins in with stories and poems, one-to-one and also in small groups.', NULL, 4, 'A', 'joins in stories, small groups, one-to-one, poems, listens'),
(1334, 431, 'Joins in with repeated refrains and anticipates key events and phrases in rhymes and stories.', NULL, 5, 'A', 'phrases, stories, rhymes, rhyming'),
(1335, 431, 'Beginning to be aware of the way stories are structured.', NULL, 6, 'A', 'story structure, story, stories, book, storybook'),
(1336, 431, 'Suggests how the story might end.', NULL, 7, 'A', 'story might end, end of the story, suggested ending to story'),
(1337, 431, 'Listens to stories with increasing attention and recall.', NULL, 8, 'A', 'describes story, setting, events, characters, conflict, resolution'),
(1338, 431, 'Describes main story settings, events and principal characters.', NULL, 9, 'A', 'describes story, setting, events, characters, conflict, resolution'),
(1339, 431, 'Shows interest in illustrations and print in books and print in the environment.', NULL, 10, 'A', 'illustrations, pictures, enjoys pictures in print'),
(1340, 431, 'Recognises familiar words and signs such as own name and advertising logos.', NULL, 11, 'A', 'own name, logo, signs, symbols, words'),
(1341, 431, 'Looks at books independently.', NULL, 12, 'A', 'independent, independent reader, books'),
(1342, 431, 'Handles books carefully.', NULL, 13, 'A', 'care of book, handles book carefully, handled book, handles book appropriately'),
(1343, 431, 'Knows information can be relayed in the form of print.', NULL, 14, 'A', 'information comes from words, spoken, information, books contain information'),
(1344, 431, 'Holds books the correct way up and turns pages.', NULL, 15, 'A', 'holds books, turns pages, turned page, held a book, turned the page'),
(1345, 431, 'Knows that print carries meaning and, in English, is read from left to right and top to bottom.', NULL, 16, 'A', 'reading'),
(1346, 432, 'Sometimes gives meaning to marks as they draw and paint.', NULL, 1, 'A', 'drawing, drew, painted, painting, scribbling, scribbled, mark making'),
(1347, 432, 'Ascribes meanings to marks that they see in different places.', NULL, 2, 'A', 'marks, ascribes meaning, aware of marks'),
(1348, 433, 'Uses some number names and number language spontan...', NULL, 1, 'A', 'number names, number language'),
(1349, 433, 'Uses some number names accurately in play.', NULL, 2, 'A', 'number names'),
(1350, 433, 'Recites numbers in order to 10.', NULL, 3, 'A', 'numbers 1-10, 1-10 in order, counted, recited'),
(1351, 433, 'Knows that numbers identify how many objects are in a set.', NULL, 4, 'A', 'understands numbers, identifies quantity, numbers, quantity, objects in a set'),
(1352, 433, 'Beginning to represent numbers using fingers, marks on paper or pictures.', NULL, 5, 'A', 'fingers represent numbers, used fingers to count, marks represent numbers, marks on paper'),
(1353, 433, 'Sometimes matches numeral and quantity correctly.', NULL, 6, 'A', 'quantity, numerals, number'),
(1354, 433, 'Shows curiosity about numbers by offering comments or asking questions.', NULL, 7, 'A', 'asks about numbers, curious about numbers, asks questions, curiosity, curious'),
(1355, 433, 'Compares two groups of objects, saying when they have the same number.', NULL, 8, 'A', 'compares, contrasts, compares numbers'),
(1356, 433, 'Shows an interest in number problems.', NULL, 9, 'A', 'number problems'),
(1357, 433, 'Separates a group of three or four objects in different ways, beginning to recognise that the total is still the same.', NULL, 10, 'A', 'understands quantity, separates objects, quantity'),
(1358, 433, 'Shows an interest in numerals in the environment.', NULL, 11, 'A', 'numbers in environment, numbers in public'),
(1359, 433, 'Shows an interest in representing numbers.', NULL, 12, 'A', NULL),
(1360, 433, 'Realises not only objects, but anything can be counted, including steps, claps or jumps.', NULL, 13, 'A', 'counting, understanding counting, counted'),
(1361, 434, 'Shows an interest in shape and space by playing with shapes or making arrangements with objects.', NULL, 1, 'A', 'shapes, space, playing with shapes, played with shapes, played with blocks'),
(1362, 434, 'Shows awareness of similarities of shapes in the environment.', NULL, 2, 'A', 'similarities of shapes, similarities in environment, shapes in environment, shapes'),
(1363, 434, 'Uses positional language.', NULL, 3, 'A', 'above, down, inside, outside, right left'),
(1364, 434, 'Shows interest in shape by sustained construction activity or by talking about shapes or arrangements.', NULL, 4, 'A', 'talks about shapes, construction of shapes, arrangement of shapes, scissors, glue, string, shapes, arrangements'),
(1365, 434, 'Shows interest in shapes in the environment.', NULL, 5, 'A', 'notices shapes in environment, shapes all around'),
(1366, 434, 'Uses shapes appropriately for tasks.', NULL, 6, 'A', 'uses shapes appropriately'),
(1367, 434, 'Beginning to talk about the shapes of everyday objects, e.g. ‘round’ and ‘tall’.', NULL, 7, 'A', 'everyday shapes, everyday objects, round, tall, big small, curved, wide, thin, pointy'),
(1368, 435, 'Shows interest in the lives of people who are familiar to them.', NULL, 1, 'A', 'family, kin, interest in family, sibling, friend, sister, brother, cousin, grandmother, nan, grandfather, grand-dad'),
(1369, 435, 'Remembers and talks about significant events in their own experience.', NULL, 2, 'A', 'wedding, going to a football match, garden centre, aquarium, museum, birthdays, celebrations and festivals, visiting a pet shop, visit a pet shop, visit a farm, went to a farm, went to a pet shop'),
(1370, 435, 'Recognises and describes special times or events for family or friends.', NULL, 3, 'A', 'birthday, anniversary, thanksgiving, holiday, easter, christmas, valentines day, mothers day, fathers day, new years'),
(1371, 435, 'Shows interest in different occupations and ways of life.', NULL, 4, 'A', 'nurse, doctor, fireman, firewoman, engineer, chef, cook, teacher, accountant, electrician, plumber, vet, veterinarian, ways of life, mechanic, youtuber, radiographer, educator'),
(1372, 435, 'Knows some of the things that make them unique, and can talk about some of the similarities and differences in relation to friends or family.', NULL, 5, 'A', 'uniqueness, different, differences, skin colour, colour of your skin, curly hair, thick hair, brown skin, black skin, white skin, blue eyes, brown eyes, blonde hair, green eyes, hazel eyes, black eyes, diversity, understands differences in relation to others'),
(1373, 436, 'Comments and asks questions about aspects of their familiar world such as the place where they live or the natural world.', NULL, 1, 'A', 'the world, natural world, natural, live, neighbourhood'),
(1374, 436, 'Can talk about some of the things they have observed such as plants, animals, natural and found objects.', NULL, 2, 'A', 'talk about observations, things they see, plants, animals, natural world, found objects, natural materials, top of form, bottom of form, alligator, ant, bear, bee, bird, camel, cat, cheetah, chicken, chimpanzee, cow, crocodile, deer, dog, dolphin, duck, eagle, elephant, fish, fly, fox, frog, giraffe, goat, goldfish, hamster, hippopotamus, horse, kangaroo, kitten, lion, lobster, monkey, octopus, owl, panda, pig, puppy, rabbit, rat, scorpion, seal, shark, sheep, snail, snake, spider, squirrel, tiger, turtle, wolf, zebra'),
(1375, 436, 'Talks about why things happen and how things work.', NULL, 3, 'A', 'why things happen, how things happen, how things work'),
(1376, 436, 'Developing an understanding of growth, decay and changes over time.', NULL, 4, 'A', 'growth, decay, death, changes, time'),
(1377, 436, 'Shows care and concern for living things and the environment.', NULL, 5, 'A', 'concern for environment, living things, natural world, taking care of earth, animals, rabbit, dog, cat, gerbil'),
(1378, 437, 'Knows how to operate simple equipment, e.g. turns on CD player and uses remote control.', NULL, 1, 'A', 'cd player, turns on, turns off, remote control'),
(1379, 437, 'Shows an interest in technological toys with knobs or pulleys, or real objects such as cameras or mobile phones.', NULL, 2, 'A', 'batteries, knobs, pulleys, turns on, turns off, interactive, tablet, computer, digital camera, camera, phone'),
(1380, 437, 'Shows skill in making toys work by pressing parts or lifting flaps to achieve effects such as sound, movements or new images.', NULL, 3, 'A', 'can turn on, can turn off, press buttons, lift flaps, sound, movement, picture'),
(1381, 437, 'Knows that information can be retrieved from computers.', NULL, 4, 'A', 'computers, computers contain information, information on computer, computer, laptop'),
(1382, 438, 'Enjoys joining in with dancing and ring games.', NULL, 1, 'A', 'dancing, ring around the rosie, danced, dance, ring games, nursery rhymes, apple bobbing, ampe, ghana, asteroids, ball & cup, ball games, ball tag, beanbag toss, blind man\'s buff, british bulldogs, button, button, who\'s got the button?, bloody knuckles, buck buck, high cockalorum, baseball, basketball, bosadiwala, cat\'s cradle, catch, chain tag, chinese whispers, chopsticks, clapping games, conkers, continuous cricket, cops and robbers, cowboys and indians, counting out, crack the whip, chod, chinese whispers, telephone game, dandy shandy, dice, doctor, dodgeball, double dutch, duck and the rock, duck, duck, goose, down down baby, double dutch, jump rope, down by the banks, egg toss, feather game, floor is lava, follow the leader, football, four corners, four square, french cricket, ghost in the graveyard, grandmother\'s footsteps, grounders, hand ball, hand games, here comes an old soldier, hide and go seek, hoop rolling, hopscotch, horseshoes, hot buttered beans, hot potato, hunt the thimble, huckle buckle beanstalk, hurray, i spy, jacks, jackstones, jinx, jumping rope, jumpsies, chinese jump rope, jump roster jump, kabaddi, keep away, kickball, kick-to-kick, kick the can, kingey, kiss chase, knife game, knock, knock, ginger, knucklebonen, kerplunk, lagori, leapfrog, limbo, marbles, marco polo, monkey in the middle, mother may i?, mumblety-peg, musical chairs, mary mack, old soldier, oshikura manju, paddle ball, paper football, paper soccer, pat-a-cake, peekaboo, pencil fighting, pick-up sticks, pie, pitching pennies, poison, poohsticks, piggy in the middle, queenie, red hands, red rover, ring a ring o\' roses, ringolevio, rock-paper-scissors, sardines, seven up, sharks and minnows, silent ball, simon says, singing games, skipping rope, skully, sleeping lions, snakes & ladders, soccer hockey, spinning top, spud, statues, red light, green light, grandmother\'s footsteps, stickball, stone skipping, stoop ball, string games, cats cradle, stuck in the mud, tag, fox and geese, freeze tag, tennis, tether ball, thumb war, tic-tac-toe, tiddlywinks, tip-cat, tower puzzle, truth or dare?, tug of war, tumbang preso, what\'s the time, mr wolf?'),
(1383, 438, 'Sings a few familiar songs.', NULL, 2, 'A', 'sings songs, familiar songs, knows familiar songs'),
(1384, 438, 'Beginning to move rhythmically.', NULL, 3, 'A', 'moves to rhythm, rhythmic movements, dance, dancing, danced, beat'),
(1385, 438, 'Imitates movement in response to music.', NULL, 4, 'A', 'moves body to music, music, danced, dancing, dance'),
(1386, 438, 'Taps out simple repeated rhythms.', NULL, 5, 'A', 'taps rhythm, repeated rhythm, move to the beat'),
(1387, 438, 'Explores and learns how sounds can be changed.', NULL, 6, 'A', 'sounds change, explores sounds, explores pitch of sounds'),
(1388, 438, 'Explores colour and how colours can be changed.', NULL, 7, 'A', NULL),
(1389, 438, 'Understands that they can use lines to enclose a space, and then begin to use these shapes to represent objects.', NULL, 8, 'A', 'shapes represent objects, lines in space'),
(1390, 438, 'Beginning to be interested in and describe the texture of things.', NULL, 9, 'A', 'texture, soft, wet, scratchy, fuzzy, slimy, prickly'),
(1391, 438, 'Uses various construction materials.', NULL, 10, 'A', 'construction, materials, construction materials, glue, paper, tape, cardboard, paper,  sculpture'),
(1392, 438, 'Beginning to construct, stacking blocks vertically and horizontally, making enclosures and creating spaces.', NULL, 11, 'A', 'uses blocks, construct, constructed, used blocks, blocks, makes enclosures, creates spaces, stacks things, joins construction pieces together'),
(1393, 438, 'Joins construction pieces together to build and balance.', NULL, 12, 'A', 'builds, balances, construction, builds blocks, stacked blocks, bricks, fixing, connecting, combining, balance, build'),
(1394, 438, 'Realises tools can be used for a purpose.', NULL, 13, 'A', 'uses tools, used a scissors, pens, pencils, scissors, stapler, elastic band, glue, masking tape, hole punch, spreader, rolling pin, cutter, grater'),
(1395, 439, 'Developing preferences for forms of expression.', NULL, 1, 'A', 'expresses themselves, expressed, emotion, smiles, individuality'),
(1396, 439, 'Uses movement to express feelings.', NULL, 2, 'A', 'movement, feelings through movement, danced, jumped'),
(1397, 439, 'Creates movement in response to music.', NULL, 3, 'A', 'movement through music, music, movement, dance'),
(1398, 439, 'Sings to self and makes up simple songs.', NULL, 4, 'A', 'sings, sings songs, makes up simple song'),
(1399, 439, 'Makes up rhythms.', NULL, 5, 'A', 'rhythm, makes up rhythm, creates rhythm'),
(1400, 439, 'Notices what adults do, imitating what is observed and then doing it spontaneously when the adult is not there.', NULL, 6, 'A', 'observes adults, imitates adults, copied'),
(1401, 439, 'Engages in imaginative role-play based on own first-hand experiences', NULL, 7, 'A', 'role plays, role play, pretend play, dressed up, superman, spiderman, superhero, imaginative role play, role play through experiences'),
(1402, 439, 'Builds stories around toys, e.g. farm animals needing rescue from an armchair ‘cliff’.', NULL, 8, 'A', 'creates stories, created a story, pretend plays, pretend play, pretended'),
(1403, 439, 'Uses available resources to create props to support role-play.', NULL, 9, 'A', 'props, role play, pretend play'),
(1404, 439, 'Captures experiences and responses with a range of media, such as music, dance and paint and other materials or words.', NULL, 10, 'A', 'dances to music, dance, paint, painted, danced to music'),
(1405, 440, 'Initiates conversations, attends to and takes account of what others say.', NULL, 1, 'A', 'discuss, discussed, discussing, initiates discussion, conversations, understands others, initiates conversation'),
(1406, 440, 'Explains own knowledge and understanding, and asks appropriate questions of others.', NULL, 2, 'A', 'ask questions, explains understanding, own knowledge, questioning, asked'),
(1407, 440, 'Takes steps to resolve conflicts with other children, e.g. finding a compromise.', NULL, 3, 'A', NULL),
(1408, 441, 'Confident to speak to others about own needs, wants, interests and opinions.', NULL, 1, 'A', 'confident, convey needs, opinions, interests'),
(1409, 441, 'Can describe self in positive terms and talk about abilities.', NULL, 2, 'A', 'self-awareness, positive self-talk'),
(1410, 442, 'Understands that own actions affect other people, for example, becomes upset or tries to comfort another child when they realise they have upset them.', NULL, 1, 'A', 'aware of actions, consequences, actions affect others'),
(1411, 442, 'Aware of the boundaries set, and of behavioural expectations in the setting.', NULL, 2, 'A', 'boundaries, behavioural expectation, self-awareness, social awareness, rules'),
(1412, 442, 'Beginning to be able to negotiate and solve problems without aggression, e.g. when someone has taken their toy.', NULL, 3, 'A', 'negotiate, problem-solve, talk it out, resolution, resolve'),
(1413, 443, 'Maintains attention, concentrates and sits quietly during appropriate activity.', NULL, 1, 'A', 'pays attention, attention, quietly, quiet time, concentrates, sits quietly, appropriate behaviour'),
(1414, 443, 'Two-channelled attention â€“ can listen and do for short span.', NULL, 2, 'A', NULL),
(1415, 444, 'Responds to instructions involving a two-part sequence. Understands humour, e.g. nonsense rhymes, jokes.', NULL, 1, 'A', 'understands sarcasm, jokes, humour, nonsense rhymes'),
(1416, 444, 'Able to follow a story without pictures or props.', NULL, 2, 'A', 'listen to a story, story-telling, follows along, story, book'),
(1417, 444, 'Listens and responds to ideas expressed by others in conversation or discussion.', NULL, 3, 'A', 'listens, responds, understands others, speech, ideas, expression, conversation, discusses, discussed'),
(1418, 445, 'Extends vocabulary, especially by grouping and naming, exploring the meaning and sounds of new words.', NULL, 1, 'A', 'extends vocabulary, groups sounds, groups meaning, new words, new sounds'),
(1419, 445, 'Uses language to imagine and recreate roles and experiences in play situations.', NULL, 2, 'A', 'creates new stories, imagines new play scenarios, talks about experiences through play, play based, imagination, role play, pretend play'),
(1420, 445, 'Links statements and sticks to a main theme or intention.', NULL, 3, 'A', NULL),
(1421, 445, 'Uses talk to organise, sequence and clarify thinking, ideas, feelings and events.', NULL, 4, 'A', 'tells stories, clarify thinking, asks questions, asked a question, questioned, beginning, middle, end, ideas, feelings, events'),
(1422, 445, 'Introduces a storyline or narrative into their play.', NULL, 5, 'A', 'adds story to play, storytelling, narrative, storyline, playing cops and robbers, pretending'),
(1423, 446, 'Experiments with different ways of moving.', NULL, 1, 'A', 'moves body, moves side to side, moves up and down, shuffles, runs, slides, jumps, sprints, walks, leaps'),
(1424, 446, 'Jumps off an object and lands appropriately.', NULL, 2, 'A', 'jumps, jumps off, jumps appropriately'),
(1425, 446, 'Negotiates space successfully when playing racing and chasing games with other children, adjusting speed or changing direction to avoid obstacles.', NULL, 3, 'A', 'runs safely, chases safely, adjusts speed, speeds up, slows down, avoids obstacles, obstacle course'),
(1426, 446, 'Travels with confidence and skill around, under, over and through balancing and climbing equipment.', NULL, 4, 'A', 'moves, moves with confidence, balances, climbs, climbs equipment, slides, jumps, goes over, goes under, over, under'),
(1427, 446, 'Shows increasing control over an object in pushing, patting, throwing, catching or kicking it.', NULL, 5, 'A', 'controls objects, kicks, throws, pats, pushes, throwing, catching, patting, pushing, kicking a ball, kicked a ball, pedals, catches, bicycle, football, basketball'),
(1428, 446, 'Uses simple tools to effect changes to materials.', NULL, 6, 'A', 'uses tools, tools to change material, paint brush, scissors, colour, glue, materials, simple tools'),
(1429, 446, 'Handles tools, objects, construction and malleable materials safely and with increasing control.', NULL, 7, 'A', 'controls tools, handles tools, handles objects, handles construction materials, controls materials'),
(1430, 446, 'Shows a preference for a dominant hand.', NULL, 8, 'A', 'shows dominant hand, left hand dominant, right hand dominant'),
(1431, 446, 'Begins to use anticlockwise movement and retrace vertical lines.', NULL, 9, 'A', 'anticlockwise movements, vertical lines, retrace, retracing, retrace lines'),
(1432, 446, 'Begins to form recognisable letters.', NULL, 10, 'A', 'forms letters, recognisable writing'),
(1433, 446, 'Uses a pencil and holds it effectively to form recognisable letters, most of which are correctly formed.', NULL, 11, 'A', 'hold pencil correctly, holds pencil, writes letters correctly'),
(1434, 447, 'Eats a healthy range of foodstuffs and understands need for variety in food.', NULL, 1, 'A', 'healthy food, fruit, apple, pear, banana, orange, strawberry, yoghurt, vegetables, peas, carrots, understands healthy foods, food choices, variety of foods, healthy eating habits, healthy meals, healthy lunch'),
(1435, 447, 'Usually dry and clean during the day.', NULL, 2, 'A', 'dry clothes, clean clothes, stays clean, stays dry, tidy'),
(1436, 447, 'Shows some understanding that good practices with regard to exercise, eating, sleeping and hygiene can contribute to good health.', NULL, 3, 'A', 'healthy, exercise, eat well, sleep well, hygiene, contributions to good health, self-care, brush teeth, wash hands'),
(1437, 447, 'Shows understanding of the need for safety when tackling new challenges, and considers and manages some risks.', NULL, 4, 'A', 'safety, safety needs, understands danger, considers risk, challenging play, risky play'),
(1438, 447, 'Shows understanding of how to transport and store equipment safely.', NULL, 5, 'A', 'transports equipment, stores equipment appropriately'),
(1439, 447, 'Practices some appropriate safety measures without direct supervision.', NULL, 6, 'A', 'independent safety, unsupervised safety, traffic light'),
(1440, 448, 'Continues a rhyming string.', NULL, 1, 'A', 'rhymes, continues rhyme, knows rhyming words, rhyming string'),
(1441, 448, 'Hears and says the initial sound in words.', NULL, 2, 'A', 'beginner reader, reads words, sounds out words, reads sentences, phonemic awareness'),
(1442, 448, 'Can segment the sounds in simple words and blend them together and knows which letters represent some of them.', NULL, 3, 'A', 'blends sounds, segment sounds, letter sounds, beginning reader, phonics'),
(1443, 448, 'Links sounds to letters, naming and sounding the letters of the alphabet.', NULL, 4, 'A', 'phonemic awareness, letter sounds, letter names, sounding out'),
(1444, 448, 'Begins to read words and simple sentences.', NULL, 5, 'A', 'beginner reader, reads words, sounds out words, reads sentences, reads simple sentences'),
(1445, 448, 'Uses vocabulary and forms of speech that are increasingly influenced by their experiences of books.', NULL, 6, 'A', 'uses text from books, uses stories in own speech, own speech'),
(1446, 448, 'Enjoys an increasing range of books.', NULL, 7, 'A', 'enjoys variety of books, wide range of books, explores new books'),
(1447, 448, 'Knows that information can be retrieved from books and computers.', NULL, 8, 'A', 'information comes from computers, computers contain information, information from technology'),
(1448, 449, 'Gives meaning to marks they make as they draw, write and paint.', NULL, 1, 'A', 'makes marks, draws marks, mark-making, paint, draw, write, writes, paints, gives meaning to marks'),
(1449, 449, 'Begins to break the flow of speech into words.', NULL, 2, 'A', 'separates sentences, breaks down speech into words, simple words, singular words, parts of sentence'),
(1450, 449, 'Continues a rhyming string.', NULL, 3, 'A', 'rhymes, continues rhyme, knows rhyming words, rhyming string'),
(1451, 449, 'Hears and says the initial sound in words.', NULL, 4, 'A', 'beginner reader, reads words, sounds out words, reads sentences, phonemic awareness'),
(1452, 449, 'Can segment the sounds in simple words and blend them together. ', NULL, 5, 'A', 'sounds out, sounds out words, phonemic awareness'),
(1453, 449, 'Links sounds to letters, naming and sounding the letters of the alphabet.', NULL, 6, 'A', 'phonemic awareness, letter sounds, letter names, sounding out'),
(1454, 449, 'Uses some clearly identifiable letters to communicate meaning, representing some sounds correctly and in sequence.', NULL, 7, 'A', 'phonemic awareness, creates letters, identifiable letters, words from sounds, letters'),
(1455, 449, 'Writes own name and other things such as labels, captions.', NULL, 8, 'A', 'knows name, writes name, writes caption, write his name, writes her name'),
(1456, 449, 'Attempts to write short sentences in meaningful contexts.', NULL, 9, 'A', 'sentences, writes sentences, contextual sentences'),
(1457, 450, 'Recognise some numerals of personal significance.', NULL, 1, 'A', 'recognise numerals, favourite numerals, significant numerals'),
(1458, 450, 'Recognises numerals 1 to 5.', NULL, 1, 'A', 'recognise 1,2,3,4,5, can count to five, counts to five'),
(1459, 450, 'Counts up to three or four objects by saying one number name for each item.', NULL, 2, 'A', 'counts numerically, counting, counts 1,2, 3, 4, 1-1 correspondence, counts to four, counts to three'),
(1460, 450, 'Counts actions or objects which cannot be moved.', NULL, 3, 'A', 'counts objects, inanimate objects, counts actions'),
(1461, 450, 'Counts objects to 10, and beginning to count beyond 10.', NULL, 4, 'A', 'counts up to 10, counting to 10, counts beyond 10, can count up to 10, counted to ten, number 10, 1,2,3,4,5,6,7,8,9,10'),
(1462, 450, 'Counts out up to six objects from a larger group. Selects the correct numeral to represent 1 to 5, then 1 to 10 objects. ', NULL, 5, 'A', 'counts to six, understands numerals, can count to 6, counted to 6, counted up to 6, count to 10'),
(1463, 450, 'Counts an irregular arrangement of up to ten objects. ', NULL, 6, 'A', 'irregular objects, irregular arrangement, 1-10, counted up to 10, counting to 10, counts to 10, counts up to 10'),
(1464, 450, 'Estimates how many objects they can see and checks by counting them. ', NULL, 7, 'A', 'estimates, estimates objects, sees objects and estimates, counting'),
(1465, 450, 'Uses the language of ‘more’ and ‘fewer’ to compare two sets of objects. ', NULL, 8, 'A', 'more, fewer, less than, greater than, more than, compare, contrast'),
(1466, 450, 'Finds the total number of items in two groups by counting all of them.', NULL, 9, 'A', 'total number, two groups, counts, counting all objects'),
(1467, 450, 'Says the number that is one more than a given number. ', NULL, 10, 'A', 'counts, counting, counted'),
(1468, 450, 'Finds one more or one less from a group of up to five objects, then ten objects. ', NULL, 11, 'A', 'take one, take one away, more than, less than, one more, one less'),
(1469, 450, 'In practical activities and discussion, beginning to use the vocabulary involved in adding and subtracting.', NULL, 12, 'A', 'adding, subtracted, subtracting, understanding mathematical vocabulary, mathematical vocabulary, doubling, halving'),
(1470, 450, 'Records, using marks that they can interpret and explain.', NULL, 13, 'A', 'records numbers, interprets marks they’ve made'),
(1471, 450, 'Begins to identify own mathematical problems based on own interests and fascinations.', NULL, 14, 'A', 'personal mathematical problems, identify mathematical interests, identify mathematical fascinations, math'),
(1472, 451, 'Beginning to use mathematical names for ‘solid’ 3D shapes and ‘flat’ 2D shapes, and mathematical terms to describe shapes.', NULL, 1, 'A', '2d shapes, 3d shapes, flat, round, solid, hollow, mathematical terms, solid shapes, describe shapes, sort shapes, shape sorting, matching triangles, shadow play, square, cubes, circle'),
(1473, 451, 'Selects a particular named shape.', NULL, 2, 'A', 'shape names, selects shape'),
(1474, 451, 'Can describe their relative position such as ‘behind’ or ‘next to’.', NULL, 3, 'A', 'behind, next to, relative positions, beside, on top, under, beneath'),
(1475, 451, 'Orders two or three items by length or height.', NULL, 4, 'A', 'orders by height, orders by length, length, height, dimensions, ruler, lever balance, metre stick, measuring jug'),
(1476, 451, 'Orders two items by weight or capacity.', NULL, 5, 'A', 'weight, capacity, density, days, weeks, time, month'),
(1477, 451, 'Uses familiar objects and common shapes to create and recreate patterns and build models.', NULL, 6, 'A', 'patterns, create patterns, build models, common shapes, create models, familiar objects'),
(1478, 451, 'Uses everyday language related to time.', NULL, 7, 'A', 'time, time related language, clock, morning, afternoon, evening, lunch time, nap time, sleep time'),
(1479, 451, 'Beginning to use everyday language related to money.', NULL, 8, 'A', 'money, buy, sell, exchange, money language, credit, debit, expense, pounds, quid, notes'),
(1480, 451, 'Orders and sequences familiar events. ', NULL, 9, 'A', 'events, birthdays, sequences, holidays'),
(1481, 451, 'Measures short periods of time in simple ways.', NULL, 10, 'A', 'moment, second, short amount of time, quickly'),
(1482, 452, 'Enjoys joining in with family customs and routines.', NULL, 1, 'A', 'family time, customs, holidays, holiday, christmas, cousins, family tradition, new years day, new years eve, mothers day, fathers day'),
(1483, 453, 'Looks closely at similarities, differences, patterns and change.', NULL, 1, 'A', 'the world, patterns, sequences, differences, similarities, change, status, economic status, social, social statuses, environments, surroundings, landscape, past events, present events, lifecycle, life-cycle'),
(1484, 454, 'Completes a simple program on a computer.', NULL, 1, 'A', 'computer, tablet'),
(1485, 454, 'Uses ICT hardware to interact with age-appropriate computer software.', NULL, 2, 'A', 'computer game, computer software, interaction, computer literacy, technology'),
(1486, 455, 'Begins to build a repertoire of songs and dances.', NULL, 1, 'A', 'song, dance, creation, own repertoire, imagination, made up songs, movement'),
(1487, 455, 'Explores the different sounds of instruments.', NULL, 2, 'A', 'instruments, sing, singing, sang, sounds of instruments, pitches, listening, hearing sounds, drum, guitar, recorder, flute, shakers, maracas, animal shakers, box shakers, egg shakers, shakere, cabasa, tambourines, jingle bells, cowbell, triangles, finger cymbals, hand cymbals, wood blocks, tone blocks, rhythm sticks, castanets, xylophone, guiro, handbells, boomwhackers, whistle'),
(1488, 455, 'Explores what happens when they mix colours.', NULL, 3, 'A', 'colour mixing, colour exploration, colour, red, blue, green, purple, pink, orange, yellow, black, brown, white, primary colours'),
(1489, 455, 'Experiments to create different textures.', NULL, 4, 'A', 'textures, explores textures, soft, hard, fluffy, rough, smooth, scratchy, slimy, experiment, science, wet, dry, flaky, fixed, holding bay, 3d, 2d'),
(1490, 455, 'Understands that different media can be combined to create new effects.', NULL, 5, 'A', 'media, two or more media, new effects, new media, create new media, combine media'),
(1491, 455, 'Manipulates materials to achieve a planned effect.', NULL, 6, 'A', 'manipulates materials, planned effect, creates new materials, mould, sculpting, creating'),
(1492, 455, 'Constructs with a purpose in mind, using a variety of resources.', NULL, 7, 'A', 'constructs, purposeful, thought out, uses resources, creation'),
(1493, 455, 'Uses simple tools and techniques competently and appropriately.', NULL, 8, 'A', 'tools, technology, simple tools, appropriate techniques, understands function, magnifying glass, eye droppers, test tubes, magnets, goggles'),
(1494, 455, 'Selects appropriate resources and adapts work where necessary.', NULL, 9, 'A', 'selects resources, appropriate resources, adapts, adaptation'),
(1495, 455, 'Selects tools and techniques needed to shape, assemble and join materials they are using.', NULL, 10, 'A', 'uses appropriate tools, understands tools, uses certain technique, assemble materials, join materials together, assemble, shape, joins materials, joins blocks, scissors, stapler, elastic bands, glue, masking tap'),
(1496, 456, 'Create simple representations of events, people and objects.', NULL, 1, 'A', 'creates representation, role play, imagination, draw a person, draw arms, draw a face, drew a face'),
(1497, 456, 'Initiates new combinations of movement and gesture in order to express and respond to feelings, ideas and experiences.', NULL, 2, 'A', 'combinations of movement, movement, dance, smiles, smiled, smiling, pointing, shake head, reach, raise arms, wave, open hand, tap, clap, blow a kiss, peace sign, high five, hug, combinations of gesture, express feelings, respond to feelings, respond to ideas, create experiences'),
(1498, 456, 'Chooses particular colours to use for a purpose.', NULL, 3, 'A', 'chooses colour, green, blue, red, white, brown, purposeful colour choice'),
(1499, 456, 'Introduces a storyline or narrative into their play.', NULL, 4, 'A', 'adds story to play, storytelling, narrative, storyline, playing cops and robbers, pretending'),
(1500, 456, 'Plays alongside other children who are engaged in the same theme.', NULL, 5, 'A', NULL),
(1501, 456, 'Plays cooperatively as part of a group to develop and act out a narrative.', NULL, 6, 'A', 'plays cooperatively, cooperation, group, with other children, story, works among group, act out story, act out narrative'),
(1502, 457, 'Curiosity and Initiative', NULL, 1, 'A', NULL),
(1503, 457, 'Engagement with Environment, People and Objects', NULL, 2, 'A', NULL),
(1504, 457, 'Eagerness to Learn', NULL, 3, 'A', NULL),
(1505, 457, 'Cooperation with Peers in Learning Experiences', NULL, 4, 'A', NULL),
(1506, 458, 'Cause and Effect', NULL, 1, 'A', NULL),
(1507, 458, 'Attributes, Sorting and Patterns', NULL, 2, 'A', NULL),
(1508, 458, 'Problem Solving', NULL, 3, 'A', NULL),
(1509, 458, 'Symbolic Representation', NULL, 4, 'A', NULL),
(1510, 459, 'Choosing and Planning', NULL, 1, 'A', NULL),
(1511, 459, 'Task Persistence', NULL, 2, 'A', NULL),
(1512, 459, 'Cognitive Flexibility', NULL, 3, 'A', NULL),
(1513, 459, 'Working Memory', NULL, 4, 'A', NULL),
(1514, 459, 'Regulation of Attention and Impulses', NULL, 5, 'A', NULL),
(1515, 460, 'Trusting Relationships', NULL, 1, 'A', NULL),
(1516, 460, 'Managing Separation', NULL, 2, 'A', NULL),
(1517, 461, 'Regulation of Emotions and Behavior', NULL, 1, 'A', NULL),
(1518, 461, 'Regulation of Impulses and Behavior', NULL, 2, 'A', NULL),
(1519, 462, 'Emotional Expression', NULL, 1, 'A', NULL),
(1520, 462, 'Recognition and Response to Emotions in Others', NULL, 2, 'A', NULL),
(1521, 463, 'Sense of self', NULL, 1, 'A', NULL),
(1522, 463, 'Personal Preferences', NULL, 2, 'A', NULL),
(1523, 463, 'Self-Concept and Competency', NULL, 3, 'A', NULL),
(1524, 464, 'Adult Relationships', NULL, 1, 'A', NULL),
(1525, 464, 'Play/Friendship', NULL, 2, 'A', NULL),
(1526, 464, 'Conflict Resolution', NULL, 3, 'A', NULL),
(1527, 465, 'Mobility', NULL, 1, 'A', NULL),
(1528, 465, 'Large Muscle Movement and Coordination', NULL, 2, 'A', NULL),
(1529, 466, 'Visual Motor Integration', NULL, 1, 'A', NULL),
(1530, 466, 'Small Muscle Movement and Coordination', NULL, 2, 'A', NULL),
(1531, 467, 'Feeding Routines/Nutrition', NULL, 1, 'A', NULL),
(1532, 467, 'Safety and Responsibility', NULL, 2, 'A', NULL),
(1533, 467, 'Dressing and Hygiene', NULL, 3, 'A', NULL),
(1534, 468, 'Physical Health Status', NULL, 1, 'A', NULL),
(1535, 468, 'Physical Activity', NULL, 2, 'A', NULL),
(1536, 468, 'Healthy Behaviors', NULL, 3, 'A', NULL),
(1537, 469, 'Word Comprehension', NULL, 1, 'A', NULL),
(1538, 469, 'Language Comprehension', NULL, 2, 'A', NULL),
(1539, 470, 'Vocabulary', NULL, 1, 'A', NULL),
(1540, 470, 'Expression of Ideas, Feelings and Needs', NULL, 2, 'A', NULL),
(1541, 470, 'Language Structure', NULL, 3, 'A', NULL),
(1542, 471, 'Conventions of Conversation', NULL, 1, 'A', NULL),
(1543, 471, 'Language for Interaction', NULL, 2, 'A', NULL),
(1544, 472, 'Interest and Engagement with Books', NULL, 1, 'A', NULL),
(1545, 472, 'Understanding of Stories or Information', NULL, 2, 'A', NULL),
(1546, 473, 'Book Concepts', NULL, 1, 'A', NULL),
(1547, 473, 'Print Concepts', NULL, 2, 'A', NULL),
(1548, 473, 'Letter Recognition', NULL, 3, 'A', NULL),
(1549, 474, 'Phonological Awareness', NULL, 1, 'A', NULL),
(1550, 475, 'Drawing and Writing', NULL, 1, 'A', NULL),
(1551, 476, 'Music', NULL, 1, 'A', NULL),
(1552, 476, 'Visual Arts', NULL, 2, 'A', NULL),
(1553, 476, 'Drama', NULL, 3, 'A', NULL),
(1554, 476, 'Dance', NULL, 4, 'A', NULL),
(1555, 477, 'Appreciation of the Arts', NULL, 1, 'A', NULL),
(1556, 478, 'Number Names', NULL, 1, 'A', NULL),
(1557, 478, 'Cardinality', NULL, 2, 'A', NULL),
(1558, 478, 'Written Numerals', NULL, 3, 'A', NULL),
(1559, 478, 'Recognition of Quantity', NULL, 4, 'A', NULL),
(1560, 478, 'Comparison', NULL, 5, 'A', NULL),
(1561, 479, 'Number Operations', NULL, 1, 'A', NULL),
(1562, 480, 'Measurement', NULL, 1, 'A', NULL),
(1563, 480, 'Data', NULL, 2, 'A', NULL),
(1564, 480, 'Sorting and Classifying', NULL, 3, 'A', NULL),
(1565, 481, 'Spatial Relationships', NULL, 1, 'A', NULL),
(1566, 481, 'Identification of Shapes', NULL, 2, 'A', NULL),
(1567, 481, 'Composition of Shapes', NULL, 3, 'A', NULL),
(1568, 482, 'Questioning and Defining Problems', NULL, 1, 'A', NULL),
(1569, 482, 'Investigating', NULL, 2, 'A', NULL),
(1570, 482, 'Using Evidence', NULL, 3, 'A', NULL),
(1571, 483, 'Design Cycle', NULL, 1, 'A', NULL),
(1572, 484, 'Unity and Diversity of Life', NULL, 1, 'A', NULL),
(1573, 484, 'Living Things and Their Interactions with the Environment and Each Other', NULL, 2, 'A', NULL),
(1574, 485, 'Energy, Force and Motion', NULL, 1, 'A', NULL),
(1575, 485, 'Matter and its Properties', NULL, 2, 'A', NULL),
(1576, 486, 'Earth’s Features and the Effects of Weather and Water', NULL, 1, 'A', NULL),
(1577, 486, 'Earth and Human Activity', NULL, 2, 'A', NULL),
(1578, 487, 'Individual Development and Identity', NULL, 1, 'A', NULL),
(1579, 487, 'Culture', NULL, 2, 'A', NULL),
(1580, 488, 'Power, Authority and Governance', NULL, 1, 'A', NULL),
(1581, 488, 'People, Places and Environments', NULL, 2, 'A', NULL),
(1582, 488, 'Civic Ideals and Practices', NULL, 3, 'A', NULL),
(1583, 489, 'Individuals, Groups and Institutions', NULL, 1, 'A', NULL),
(1584, 489, 'Production, Distribution and Consumption', NULL, 2, 'A', NULL),
(1585, 489, 'Science, Technology and Society', NULL, 3, 'A', NULL),
(1586, 490, 'Time, Continuity and Change', NULL, 1, 'A', NULL);


-- --------------------------------------------------------

--
-- Table structure for table `goal_monthly_plan`
--

CREATE TABLE `goal_monthly_plan` (
  `goal_fk` int(11) NOT NULL,
  `monthly_plan_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `goal_story`
--

CREATE TABLE `goal_story` (
  `goal_id` int(11) UNSIGNED NOT NULL,
  `story_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `medias`
--

CREATE TABLE `medias` (
  `media_id` int(11) UNSIGNED NOT NULL,
  `media_full_url` varchar(512) NOT NULL,
  `media_thumbnail_url` varchar(512) NOT NULL,
  `media_mime_type` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `media_monthly_plan`
--

CREATE TABLE `media_monthly_plan` (
  `monthly_plan_fk` int(11) NOT NULL,
  `media_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `media_record`
--

CREATE TABLE `media_record` (
  `media_id` int(11) UNSIGNED NOT NULL,
  `record_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `media_story`
--

CREATE TABLE `media_story` (
  `media_id` int(11) UNSIGNED NOT NULL,
  `story_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `monthly_plans`
--

CREATE TABLE `monthly_plans` (
  `monthly_plan_id` int(11) NOT NULL,
  `school_fk` int(11) NOT NULL,
  `plan_public` tinyint(1) DEFAULT NULL,
  `month` tinyint(4) NOT NULL,
  `year` char(4) NOT NULL,
  `assoc` enum('school','room','child') NOT NULL,
  `theme` text,
  `well_being` text,
  `identity_belonging` text,
  `communication` text,
  `exploring_thinking` text,
  `description` text,
  `expressive_arts_design` text,
  `literacy` text,
  `mathematics` text,
  `personal_social_emotional_development` text,
  `physical_development` text,
  `understanding_the_world` text,
  `connected_contribute` text,
  `confident_learners` text,
  `comment` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `monthly_plan_assoc`
--

CREATE TABLE `monthly_plan_assoc` (
  `monthly_plan_assoc_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `monthly_plan_fk` int(11) NOT NULL,
  `assoc_type` enum('school','room','child','') NOT NULL,
  `assoc_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `policy_id` int(11) UNSIGNED NOT NULL,
  `policy_name` varchar(255) DEFAULT NULL,
  `policy_description` text,
  `policy_required` tinyint(1) NOT NULL DEFAULT '0',
  `policy_sort` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `policy_default` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `policy_school`
--

CREATE TABLE `policy_school` (
  `policy_id` int(11) UNSIGNED NOT NULL,
  `school_id` int(11) UNSIGNED NOT NULL,
  `body` text,
  `file_url` VARCHAR(512) NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `policy_public` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `question_body` text,
  `question_description` text,
  `school_categories` varchar(32) DEFAULT NULL,
  `question_upload` tinyint(1) DEFAULT '0',
  `question_multiple_choice` tinyint(1) DEFAULT '0',
  `answer_id` int(11) UNSIGNED DEFAULT NULL,
  `question_recommendation` text,
  `question_sort` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `category_id`, `question_body`, `question_description`, `school_categories`, `question_upload`, `question_multiple_choice`, `answer_id`, `question_recommendation`, `question_sort`) VALUES
(1, 1, 'Have you registered your pre-school with TUSLA?', '', '', 0, 0, 2, 'What you must do to register your pre-school\n\nApplication fees to register\n\n<ol>\n<li>Full day care service €80</li>\n<li>Part-time day care service €80</li>\n<li>Sessional pre-school service €40</li>\n<li>Pre-school service in a drop-in centre €80</li>\n<li>Temporary pre-school service €80</li>\n<li>Childminding service €40</li>\n<li>Overnight pre-school service €80</li>\n</ol>\n\nDocuments you need\n\n<strong>Preschool service except for a temporary service:</strong> A person who proposes to provide a pre-school service other than a temporary pre-school service shall make an application (p. 27-34) in respect of the pre-school service at least 3 months before the person proposes to commence the service. You may send the application to TUSLA directly or register through the <a href="http://tuslaportal.azurewebsites.net/" target="_blank">TUSLA TEYIS Service Portal</a> online portal.\n\nDocuments that must accompany your <a href="http://www.dcya.gov.ie/documents/publications/20160510ChildCareActEarlyYrsRegs2016SI221of2016.pdf" target="_blank">application form (p. 27-34)</a>:\n\n<ul>\n<li><a href="https://vetting.garda.ie/" target="_blank">Garda vetting/Police vetting</a> for proposed registered provider and person in charge if different</li>\n<li>Two references in respect of the proposed registered provider, and in respect of the person in charge if different</li>\n<li>Floor plan of the interior design of the centre giving details of the dimensions of all rooms intended for children’s use, also indicating owner’s/staff rooms</li>\n<li>Plan of any outdoor area available for children’s use</li>\n<li>Evidence of registration from Companies Registration Office, where applicable</li>\n<li>Proof of identity of the proposed registered provider (copy of passport or driving licence are the only acceptable documents)</li>\n<li>Copy of the Certificate of Insurance or written confirmation of insurance cover</li>\n<li>Copy of Statement of Purpose and Function</li>\n<li>Copy 20 of Safety Statement</li>\n<li>Copy of Policy on Managing Behaviour</li>\n<li>Copy of Complaints Policy</li>\n<li>Copy of Policy on Administration of Medication</li>\n<li>Copy of Policy on Infection Control</li>\n<li>Copy of Policy on Safe Sleep</li>\n<li>Application Fee Due</li>\n</ul>\n\n<strong>Temporary preschool service:</strong> A person who proposes to provide a temporary pre-school service shall make an application in respect of the pre-school service at least 21 days before the person proposes to commence the service.\n\nDocuments that must accompany your <a href="http://www.dcya.gov.ie/documents/publications/20160510ChildCareActEarlyYrsRegs2016SI221of2016.pdf" target="_blank">application form (p. 36-42)</a>:\n\n<ul>\n<li><a href="https://vetting.garda.ie/" target="_blank">Garda vetting/Police vetting</a> for proposed registered provider and person in charge if different</li>\n<li>Two references in respect of the proposed registered provider, and in respect of the person in charge if different</li>\n<li>Floor plan of the interior design of the centre giving details of the dimensions of all rooms intended for children’s use, also indicating owner’s/staff rooms</li>\n<li>Evidence of registration from Companies Registration Office, where applicable</li>\n<li>Copy of the Certificate of Insurance or written confirmation of insurance cover</li>\n<li>Copy of Statement of Purpose and Function</li>\n<li>Proof of identity of the proposed registered provider (copy of passport or driving licence are the only acceptable documents)</li>\n<li>Copy of Safety Statement</li>\n<li>Copy of Policy on Managing Behaviour</li>\n<li>Copy of Complaints Policy</li>\n<li>Copy of Policy on Administration of Medication</li>\n<li>Copy of Policy on Infection Control</li>\n<li>Copy of Policy on Safe Sleep</li>\n<li>Application Fee Due</li>\n</ul>\n\nPlease note: after registering your preschool you may be subject to a visit from a representative of the agency of the premises where the pre-school service is being, or is proposed to be, provided, as the case may be.', 1),
(4, 1, 'Do you have a register available for inspection to the public?', '', '', 0, 0, 12, 'Please ensure that you have a register which is available for inspection by members of the public by means of the internet. (2) The following details, in addition to information supplied in your application form to register your preschool with TUSLA, should be in the register: (a) the name, if any, of the pre-school service; (b) the name of the person in charge of the pre-school service (if different to the registered provider); (c) the date from which the registration of the pre-school service takes effect (if different from the date of registration); (d) in the case of an application in respect of a temporary pre-school service, the dates on which the service is to be provided; (e) whether the pre-school service offers one or more of the following classes of service: (i) childminding service; (ii) full day care service; (iii) overnight pre-school service; (iv) part-time day care service; (v) pre-school service in a drop-in centre;  (vi) temporary pre-school service; (vii) sessional pre-school service; (f) the age profile of children for which the service is registered to provide services; (g) any condition attached to registration.', 2),
(5, 1, 'Before I change anything on my register, I will notify TUSLA 60 days in advance.', '', '', 0, 0, 14, 'Please ensure you notify TUSLA 60 days in advance before changing anything on your register. TUSLA contact details can be found on the <a href="http://www.TUSLA.ie" target="_blank">TUSLA website</a>.', 3),
(6, 2, 'I have a designated person in charge and a named person who is able to deputise at all times during normal hours.', '', '', 0, 0, 16, 'You must have a designated or named person who must be on the premises at all times during the period when the preschool service is being carried on.', 1),
(7, 2, 'There is a clear management structure in writing which identifies the lines of authority and accountability in the service and the specific roles and responsibilities of each employee and unpaid worker.', '', '', 0, 0, 18, 'There must be a clear management structure in place.', 2),
(8, 2, 'Which of the following documentations do you have for each contractor and unpaid worker on your premises?', '', '', 0, 1, NULL, 'Please ensure you have the mentioned documents for each contractor and/or unpaid worker on your premises.', 3),
(9, 2, 'Do all your employees hold at least a QQI Level 5 (i.e. FETAC Level 5, major award) in Early Childhood Care and Education at Level 5 on the National Qualifications Framework or a qualification deemed by the Minister to be equivalent. Except where an employee has signed a declaration (grandfathering) on/or before June 30th to retire from employment before September 2021, or if they have received a letter from the minister confirming it will not apply to them.', '', '', 0, 0, 20, 'All employees must hold at least a QQI LEVEL 5 (AKA FETAC Level 5) or as equivalent as deemed by the Minister. <a href="http://www.dcya.gov.ie/documents/ecce-scheme/20150925EYPPInfoQualificationsRequirements.pdf" target="_blank">Find more information here</a>.\n\nThis does not apply to you before 1 September 2021 if\n\n(a) has signed a declaration on or before 30 June 2016 to the effect that he or she intends to retire from employment in a pre-school service before 1 September 2021\n\n(b) is in possession of a letter from the Minister confirming that paragraph (4) shall not apply to him or her before that date.', 4),
(10, 2, 'Have your employees been provided with information and training on the 2016 early years regulations and your policies and procedures. They should have been given a copy or access to these policies and procedures.', '', '', 0, 0, 22, 'A registered provider must ensure that all employees, unpaid workers and contractors are appropriately supervised and provided with appropriate information, and where necessary training, including in relation to the following:\n\n(b) Part VIIA (inserted by section 92 of the Child and Family Agency Act 2013 (No. 40 of 2013) of the Act\n\n(c) <a href="http://www.dcya.gov.ie/documents/publications/20160510ChildCareActEarlyYrsRegs2016SI221of2016.pdf" target="_blank">the Regulations</a>.', 5),
(11, 2, 'Do you have mandatory policies and procedures in place?', 'You can <a href="/policies">manage your policies</a> on TeachKloud.', '', 0, 0, 146, 'Please <a href="/policies">click here</a> to view the policies which are mandatory for your pre-school. You can easily create new policies or edit existing ones.', 6),
(12, 2, 'Have staff signed off on these policies and are these kept in their individual and personal files?', '', '', 0, 0, 24, 'Please ensure staff have signed off on individual policies and these signed policies are kept in their individual files.', 7),
(13, 2, 'Does your service abide by the following adult-to-child ratios? Please note that these ratios do not include contractors or unpaid persons.', '<table class="table">\n<thead>\n<tr><th class="w-50">Age Range</th><th class="w-50">Adult:Child Ratio</th></tr>\n</thead>\n<tbody>\n<tr><td>0 — 1 year</td><td>1:3</td></tr>\n<tr><td>1 — 2 years</td><td>1:5</td></tr>\n<tr><td>2 — 3 years</td><td>1:6</td></tr>\n<tr><td>3 — 6 years</td><td>1:8</td></tr>\n</tbody>\n</table>', '5', 0, 0, 159, 'Please ensure you abide by the required ratios.', 8),
(14, 2, 'Are there 2 adults on the premises at all times? This does not include contractors or unpaid persons.', '', '1,3,4,5,6', 0, 0, 26, 'There must be two adults on your premises at all times.', 12),
(15, 2, 'Do you have a second person familiar with the operation of the service who is in a position to provide assistance in the event of an emergency?', '', '2', 0, 0, 28, 'Please ensure that a second person familiar with your service and in a position to provide assistance to you in operating the service is, at all times, within close distance of the service and available to attend the service to assist you in the event of an emergency.', 13),
(16, 2, 'If I operate my service on my own, I have appointed a second adult who is familiar with my service to provide assistance and this person is close to my service and available to attend my service in an emergency. ', '', '6', 0, 0, 30, 'If you operate your service on your own please ensure you have another designated adult who is in close proximity to you and available in the event of an emergency.', 14),
(17, 2, 'I ensure that (a) there are no more than 5 pre-school children in my care at any given time, including my own pre-school children, (b)  there are no more than 2 children under the age of 15 months in my care at any given time including my own pre-school children (except all children are siblings), and there is a working 30 telephone on the premises.', '', '2', 0, 0, 32, 'Please ensure that (a) there are no more than 5 pre-school children in your care at any given time, including your own pre-school children, (b) there are no more than 2 children under the age of 15 months (except they are all siblings) in his or her care at any given time, including his or her own pre-school children, and there is a working 30 telephone on the premises.', 15),
(18, 2, 'I ensure that there are no more than 24 children attending my service at any given time and none of these children attend my service for longer than 8 hours consecutively. I also understand that if I have a a pre-school service in a drop-in centre no child should attend my service for longer than 2 hours consecutively.', '', '1,3', 0, 0, 34, 'Please ensure that there are no more than 24 children attending your service at any given time and that non of these children attend for 8 hours consecutively. If you have a a pre-school service in a drop-in centre, please ensure that no child should attend your service for longer than 2 hours consecutively.', 16),
(19, 2, 'At regular intervals, being intervals of not more than one year, a review is carried out in respect of the quality and safety of care provided by the pre-school service to pre-school children attending the service including a review of the policies, procedures and statements of the service.', '', '1,2,4,5,6', 0, 0, 36, 'Please ensure that you regularly (more than once a year) review the health, safety and quality of care and education provided to children in your setting. This can be done through things such as an audit of your preschool, review of policies and procedures, review of teaching practices and curricula implementation.', 17),
(20, 3, 'I keep the following records on each child in my setting.', '', '2,4,5,6', 0, 1, NULL, 'Please ensure that you keep records of all the points mentioned.', 1),
(21, 3, 'I have implemented the following.', '', '1,3', 0, 1, NULL, 'Please complete the points you have not checked.', 2),
(22, 3, 'The following records are in writing and kept on my preschool\'s premises.', '', '2,4,5,6', 0, 1, NULL, 'Please ensure that you keep records of all the points mentioned.', 3),
(23, 3, 'If you are a registered provider, do you retain all documents and records relating to references and Garda and police vetting for a period of 5 years from the date the person (to whom the document or record relates) starts working in the service?', '', '1,2,4,5,6', 0, 0, 64, 'Please ensure that you retain all documents and records relating to references and Garda and police vetting for a period of 5 years from the date the person (to whom the document or record relates) starts working in the service.', 4),
(24, 3, 'Are records on children kept for a period of 2 years from the date from which the child attends the service?', '', '1,3', 0, 0, 66, 'Please ensure that records on children kept for a period of 2 years from the date from which the child attends the service.', 5),
(25, 3, 'I have taken these steps with regards to giving parents key information.', '', '', 0, 1, NULL, 'Please complete the points you have not checked.', 6),
(26, 3, 'Each child’s learning, development and well-being is facilitated through the provision of developmentally appropriate learning opportunities, interactions, materials and equipment.', '', '', 0, 0, 71, 'Please follow Aistear (2009) and Siolta (2006) guidelines to ensure that each child’s learning, development and well-being is facilitated within the daily life of the pre-school service through the provision of the appropriate activities, interaction, materials and equipment, having regard to the age and stage of development of the child, and (b) appropriate and suitable care practices are in place in the pre-school service, having regard to the number of children attending the service and the nature of their needs. Evidence for this can be records of children\'s learning stories/journals and observations which you can start doing today on TeachKloud. You can also upload pictures or videos to show examples of how you facilitate learning and development for each child to show you are compliant with this question.', 7),
(28, 3, 'If you have more than 15 children attending your service, do you have an assigned person (employee or unpaid worker) to check children in and out?', '', '', 0, 0, 75, 'Please ensure that you have an assigned person (employee or unpaid worker) to check children in and out if you have more than fifteen attending your service.', 9),
(29, 3, 'Do all entries (excluding employees, unpaid workers, person dropping or collecting, a preschool child and unpaid worker) into your preschool setting have to be approved?', '', '', 0, 0, 77, 'Please ensure that all entries (excluding employees, unpaid workers, person dropping or collecting, a preschool child and unpaid worker) into your preschool setting are approved.', 10),
(30, 3, 'A daily record is kept on all entries on all such persons.', '', '', 0, 0, 79, 'Please ensure you keep a daily entry of each person (excluding unpaid workers, persons dropping or collecting, a preschool child  and/or unpaid worker) into your setting.', 11),
(31, 3, 'How long do you retain these records for?', '', '', 0, 0, 80, 'Please ensure that a record in writing is retained for a period of one year from the date to which it relates for all entries into your prescool excluding employees, unpaid workers, persons dropping and collecting a preschool child and unpaid workers.', 12),
(32, 3, 'I keep records of all fire safety drills that take place on my premises.', '', '', 0, 0, 86, 'Please ensure that you keep records of all fire safety drills that take place on your early years setting premises.', 13),
(33, 3, 'I keep records on the number, type and maintenance of firefighting equipment and smoke alarms in the premises.', '', '', 0, 0, 88, 'Please ensure that you keep records of all fire safety drills that take place on your early years setting premises.', 14),
(34, 3, 'Records pertaining to fire safety measures are kept for a period of 5 years after its creation.', '', '', 0, 0, 90, 'Please ensure that records pertaining to fire safety measures are kept for a period of 5 years after its creation.', 15),
(35, 3, 'In the event of a fire, I have a notice of procedures to be followed which is displayed in visible location.', '', '', 0, 0, 92, 'Please ensure that you have a notice of procedures to be followed in the event of a fire and this is displayed in a conspicuous position on the premises. Guidance on this can be <a href="/policies">found here</a>.', 16),
(36, 4, 'I agree that I do not use corporal punishment.', '', '', 0, 0, 94, 'Corporal punishment is not permitted in any early years setting.', 1),
(37, 4, 'Unless in accordance with the terms of the consent of a parent or guardian given in the form specified in your policy on the use of the internet and photographic and recording device, please ensure that any child attending your preschool is not (a) permitted access to the internet, (b) photographed, or (c) recorded, while attending the pre-school service.', '', '', 0, 0, 96, 'If you have not obtained consent from parents or guardians please ensure that children do not have access to the internet, are not photographed or recorded.', 2),
(39, 4, 'There are suitable facilities for children to rest during the day and/or at night.', '', '4', 0, 0, 100, 'Please ensure that there are suitable facilities for children to rest during the day and/or at night if you are an overnight facility.', 4),
(41, 4, 'Depending on whether you moved premises before or after 30 June 2016, please tick which applies to you.', '', '', 0, 0, 167, 'If you moved on or after 30 June 2016, please ensure that you have a suitable, safe and secure outdoor play area which children have access to on a daily basis. Otherwise ensure that children in your setting have access to an outdoor play area.', 6),
(43, 4, 'Do you have varied food and drink which is nutritious available to each child in the setting? You may upload pictures or PDFs of a sample menu for your preschool here.', '', '', 1, 0, 108, 'Please ensure that you have varied food and drink which is nutritious available to each child in the setting.', 8),
(44, 4, 'All children are checked in and out by an employee or unpaid worker.', '', '', 0, 0, 110, 'Please ensure that all children are checked in and out by an employee or unpaid worker.', 9),
(45, 4, 'There is a person trained in first aid on my premises at all times.', '', '', 0, 0, 112, 'Please ensure that there is a person trained in first aid on the premises of your setting at all times.', 10),
(46, 4, 'Does your First Aid Box contain the following?', '<table class="table">\r\n<thead>\r\n<tr><th class="w-50">Materials</th><th class="w-50" colspan="3">First Aid Box Contents</th></tr>\r\n<tr><th></th><th>1 - 5 children</th><th>6 - 25 children</th><th>26 - 50 children</th></tr>\r\n</thead>\r\n<tbody>\r\n<tr><td>Hypoallergenic plasters</td><td>12</td><td>20</td><td>20</td></tr>\r\n<tr><td>Sterile eye pads (bandage attached)</td><td>2</td><td>6</td><td>6</td></tr>\r\n<tr><td>Individually wrapped triangular bandages</td><td>2</td><td>6</td><td>6</td></tr>\r\n<tr><td>Small individually wrapped, sterile, unmedicated wound dressings</td><td>1</td><td>2</td><td>4</td></tr>\r\n<tr><td>Medium individually wrapped, non-stick, sterile, unmedicated wound dressings</td><td>1</td><td>2</td><td>4</td></tr>\r\n<tr><td>Individually wrapped antiseptic wipes</td><td>8</td><td>8</td><td>10</td></tr>\r\n<tr><td>Paramedic shears</td><td>1</td><td>1</td><td>1</td></tr>\r\n<tr><td>Latex gloves: non-powdered latex or Nitril gloves (latex-free)</td><td>1 box</td><td>1 box</td><td>1 box</td></tr>\r\n<tr><td>Additionally, where there is no running water, sterile eye-wash</td><td>1</td><td>2</td><td>2</td></tr>\r\n</tr>\r\n</tbody>\r\n</table>', '', 0, 0, 114, 'The contents required under the Preschool Regulations is outlined in the Explanatory Guide to Requirements pg. 82 Child Care (Pre-School Services) (No 2) Regulations 2006. These are the requirements:', 11),
(47, 4, 'Children are supervised at all times.', '', '', 0, 0, 116, 'Children must be supervised at all times.', 12),
(48, 4, 'I have adequate insurance for all aspects of my premises.', 'Insurances include:\r\n<ul>\r\n<li>Categories of Insurance Cover for Pre-School Service</li>\r\n<li>Public Liability</li>\r\n<li>Fire and Theft</li>\r\n<li>Motor Insurance (if used to transport pre-school children)</li>\r\n<li>Building Insurance</li>\r\n<li>Outings (if applicable)</li>\r\n</ul>', '', 0, 0, 118, 'Adequate insurance is required in all of the stated areas.', 13),
(49, 5, 'Please tick the ones which apply to you. My premises are of:', '', '', 0, 1, NULL, 'Please ensure that your premises are of the expected quality.', 1),
(50, 5, 'I have adequate floor space available in my premises for the work, play and movement of children attending the pre-school service.', '<table class="table">\r\n<thead>\r\n<tr><th class="w-50">Age Range</th><th class="w-50">Clear Floor Space Per Child</th></tr>\r\n</thead>\r\n<tbody>\r\n<tr><td>0 — 1 year</td><td>3.5 square metres</td></tr>\r\n<tr><td>1 — 2 years</td><td>2.8 square metres</td></tr>\r\n<tr><td>2 — 3 years</td><td>2.35 square metres</td></tr>\r\n<tr><td>3 — 6 years</td><td>2.3 square metres</td></tr>\r\n<tr><td>ECCE Scheme Providers</td><td>1.8 square metres</td></tr>\r\n</tbody>\r\n</table>', '6', 0, 0, 126, '', 2),
(51, 5, 'I have no more than 22 children in a room at all times.', '', '6', 0, 0, 128, 'Please ensure you have less than 22 children in a room at all times.', 3),
(52, 5, 'Within three days of the following occurring I will notify TUSLA.', '', '', 0, 1, NULL, 'This is not a choice at all.', 4),
(53, 5, 'My Complaints policy includes the following:', '', '', 0, 1, NULL, 'Please amend your complaint policy to include all of the components mentioned here.', 5),
(54, 5, 'A record in writing of the complaint is kept for 2 years from the date that the complaint has been dealt with.', '', '', 0, 0, 138, 'Please ensure that a record in writing of any complaint is kept for 2 years from the date that the complaint has been dealt with.', 6),
(55, 4, 'I have a Health and Safety Statement as outlined by the Safety, Health & Welfare at Work Act, 2005.', '', '', 1, 0, 148, 'Please ensure that you have a Health and Safety Statement as outlined by the Safety, Health & Welfare at Work Act, 2005 on your premises.', 14),
(56, 3, 'I store the following records for a period of 2 years.', '', '', 1, 1, NULL, 'Please ensure you keep the following records for a period of 2 years: details of attendance by each pre-school child on a daily basis; details of staff rosters on a daily basis; details of any medication administered to a pre-school child attending the service with signed parental consent; details of any accident, injury or incident involving a pre-school child attending the service.', 16),
(57, 2, 'Does your service abide by the following adult-to-child ratios? Please note that these ratios do not include contractors or unpaid persons.', '<table class="table">\n<thead>\n<tr><th class="w-50">Age Range</th><th class="w-50">Adult:Child Ratio</th></tr>\n</thead>\n<tbody>\n<tr><td>0 — 1 year</td><td>1:3</td></tr>\n<tr><td>1 — 2.5 years</td><td>1:5</td></tr>\n<tr><td>2.5 — 6 years</td><td>1:11</td></tr>\n</tbody>\n</table>', '6', 0, 0, 161, 'Please ensure you abide by the required ratios.', 9),
(58, 2, 'Does your service abide by the following adult-to-child ratios? Please note that these ratios do not include contractors or unpaid persons.', '<table class="table">\n<thead>\n<tr><th class="w-50">Age Range</th><th class="w-50">Adult:Child Ratio</th></tr>\n</thead>\n<tbody>\n<tr><td>0 — 6 years</td><td>1:4</td></tr>\n</tbody>\n</table>', '1,3', 0, 0, 163, 'Please ensure you abide by the required ratios.', 10),
(59, 2, 'Does your service abide by the following adult-to-child ratios? Please note that these ratios do not include contractors or unpaid persons.', '<table class="table">\n<thead>\n<tr><th class="w-50">Age Range</th><th class="w-50">Adult:Child Ratio</th></tr>\n</thead>\n<tbody>\n<tr><td>0 — 1 year</td><td>1:3</td></tr>\n<tr><td>1 — 6 years</td><td>1:5</td></tr>\n</tbody>\n</table>', '4', 0, 0, 165, 'Please ensure you abide by the required ratios.', 11),
(60, 5, 'Do you have a safety cert for all smoke alarms on the premises?', NULL, NULL, 0, 0, 168, 'Please ensure you have a safety certificate for all fire alarms on your premises. Third party operators such as electricians can guide you in this process.', 7),
(61, 5, 'Have appropriate safety precautions been taken at patio doors, glass panels and low-level windows e.g. laminated/toughened glass/visibility strips.', NULL, NULL, 0, 0, 170, 'Please ensure that safety precautions have been taken with doors, patios, glass or windows in your setting.', 8),
(62, 5, 'Are visibility strips fitted to patio doors, glazed panels or low-level windows where required?', NULL, NULL, 0, 0, 172, 'Please ensure that visibility strips have been fitted to patio doors, glazed panels or low-level windows were required.', 9),
(63, 5, 'Have windows at first floor level been fitted with restrictive opening devices?', NULL, NULL, 0, 0, 174, 'Please have windows at first floor level been fitted with restrictive opening devices.', 10),
(64, 5, 'Have suitable handrails been provided where necessary?', NULL, NULL, 0, 0, 176, 'Please have suitable handrails .', 11),
(65, 5, 'Have safety gates been provided at top and bottom of the stairs where required?', NULL, NULL, 0, 0, 178, 'Please have safety gates at top and bottom of the stairs.', 12),
(66, 5, 'Is the staircase adequately lit?', NULL, NULL, 0, 0, 180, 'Please ensure that the staircase adequately lit.', 13),
(67, 5, 'Is play equipment safe for the age group using them, in good condition, free from pinch and crush points, exposed bolts or sharp edges?', NULL, NULL, 0, 0, 182, 'Please ensure that play equipment is safe for the age group using them, in good condition, free from pinch and crush points, exposed bolts or sharp edges', 14),
(68, 5, 'Are baby walkers prohibited?', NULL, NULL, 0, 0, 184, 'Baby walkers should not be used in your setting.', 15),
(69, 5, 'Has heavy equipment or furniture that may tip over been fully anchored?', NULL, NULL, 0, 0, 186, 'Heavy equipment or furniture that may tip over should be be fully anchored.', 16),
(70, 5, 'Are all flexes or cables checked and in good condition?', NULL, NULL, 0, 0, 188, 'Please have all flexes or cables checked by suitable personel to verify that they are in good condition.', 17),
(71, 5, 'Are kettles stored safely?', NULL, NULL, 0, 0, 190, 'Store kettles away from the reach of children and in a safe/high area.', 18),
(72, 5, 'Is the TV securely mounted?', NULL, NULL, 0, 0, 192, 'Please ensure that the TV is securely mounted.', 19),
(73, 6, 'Have you registered as an early years provider on the early years register, or received an exemption?  ', '', NULL, 1, 0, 195, 'Unless you have received an exemption please ensure that you register your service with Ofsted', 1),
(74, 6, 'All supervisors have at least a level 3 qualification appropriate to Early Years Provision', 'You may upload your action plan here if all staff do not have a level 3.', NULL, 1, 0, 197, 'Please ensure all supervisors have a least a level 3 qualification appropriate to early years provision. If they do not please develop an action plan on how it is proposed that staff reach this level. Be advised that Ofsted may review this plan.', 2),
(75, 6, 'At least 50% of all staff have at least a level 2 qualification appropriate to Early Years Provision', '', NULL, 0, 0, 199, 'Please ensure at least 50% of staff have at least a level 2 qualification appropriate to early years provision.', 3),
(76, 6, 'The manager of my service has at least a Level 3 qualification appropriate to his/her post and 2 years experience', '', NULL, 0, 0, 201, '', 4),
(77, 6, 'All staff have received induction training which is not limited to training on policies/prodcedures/health and safety best practice', '', NULL, 1, 0, 203, 'Please ensure that all staff have received induction training which is not limited to training on policies/prodcedures/health and safety best practice', 5),
(78, 6, 'Trainees under 17 years of age are supervised at all times and are not counted as part of the mandatory ratios except management are satisfied that these trainees are competent', '', NULL, 0, 0, 205, 'Trainees under 17 must be supervised at all times unless deemed competent by staff after receiving supervision and training.', 6),
(79, 6, 'A record of staff, volunteers and committee members is accessible and kept on my premises. It includes their curriculum vitae.', '', NULL, 0, 0, 207, 'Accessible individual records should be kept on the premises containing the name and address of the staff members/volunteers or committee members. This should have information relating to their qualification, training, name and address.', 7),
(80, 6, 'There is a named person who is able to take charge in the absence of the manager', '', NULL, 1, 0, 209, 'Please ensure that there is a named person who is capable of taking charge in the absence of the manager. ', 8),
(81, 6, 'The size of a group never exeeds 26 children', '', NULL, 0, 0, 211, 'Please ensure that group size is never more than 26 children', 9),
(82, 6, 'Every child has a key person (member of staff) that has been allocated to them', '', NULL, 0, 0, 213, 'Please ensure that each child has a key person', 10),
(83, 6, 'This key person is responsible for the child’s well-being and communication with caregivers', '', NULL, 0, 0, 215, 'A key person (i.e. member of staff) should be allocated to each child. This key person should be responsible for communication with parents and the childs overall well-being and development on a daily basis', 11),
(84, 6, 'We have an operational plan in place', '', NULL, 1, 1, 217, 'Please ensure you have an operational plan in place. This plan should include how staff will be deployed within the provision, how and what activities will be provided and how the continuing training needs of staff will be met.', 12),
(85, 6, 'We abide by these ratios', '', NULL, 0, 1, NULL, 'Please ensure that you abide by the appropriate ratios for your service type<ul><li>1:3 children under 2 years</li><li>1:4 children aged 2 years</li><li>1:8 children aged 3+ years</li></ul>', 13),
(86, 6, 'We have a minimum of two adults on duty at all times', '', NULL, 0, 0, 223, 'Please ensure you have a minimum of two adults on duty at all times.', 14),
(87, 6, 'There is a system for registering children and staff attendance on a daily basis, showing hours of attendance. The name, address and date of birth of each child who is looked after on the premises should be recorded', '', NULL, 0, 1, 225, 'Please ensure that there is a system for registering children and staff attendance on a daily basis, showing hours of attendance. The name, address and date of birth of each child who is looked after on the premises should be recorded', 15),
(88, 6, 'There are procedures and policies in place to cover emergencies and unexpected staff absences, and sufficient suitable staff and volunteers to cover staff breaks, holidays, sickness and time spent with parents.', '', NULL, 0, 1, 227, 'This policy is required.', 16),
(89, 7, 'The registered person and their staff encourage children to be confident, independent and develop their self-esteem, this is reflected in our curriculum.', '', NULL, 0, 0, 229, 'Children should be encouraged to be confident, independent and develop their self-esteem through staff practices and the curriculum goals.', 1),
(90, 7, 'We observe and record what children do we use these observations to understand what learning opportunities to provide next for children', '', NULL, 0, 0, 231, 'Please ensure you have a record of observations and next steps for childrens learning based on these observations. You can do this on TeachKlouds Learing Stories section.', 2),
(91, 7, 'Resources are organised in such a way that all children have easy access to them', '', NULL, 1, 0, 233, 'Resources are accessible to children and staff are available to support children in their learning. Accessible to children and deploy staff to support children’s play and learning.', 3),
(92, 7, 'Children have opportunities to be active in and outdoors', 'Upload pictures of your indoor and outdoor play areas.', NULL, 1, 0, 235, 'Please ensure that children have opportunities to play indoors and outdoors unless you have an exemption in place', 4),
(93, 7, 'For babies under 2 there is a daily system of exchange of information between the parent and key person. ', '', NULL, 0, 0, 237, 'Please ensure that for babies under 2 a daily system of exchange of information between the parent and key person is required.', 5),
(94, 7, 'No child is received into the provision without emergency contact numbers being provided. If the parents are unavailable to be contacted, another named person is available to collect the child if necessary.', '', NULL, 0, 0, 239, 'Please ensure that emergency contact numbers are provided by parents. Parents should also provide details of a named designated person if parents are unavailable. ', 6),
(95, 7, 'Children are encouraged to work towards the Early Learning Goals and this is evidenced in our observations/documentation', '', NULL, 0, 0, 241, 'Children should be encouraged to work towards the Early Learning Goals and this should be evidenced in our observations/documentation', 7),
(96, 8, 'There is a separate base room for children under two and nappy changing facilities are provided which meet environmental health standards', '', NULL, 1, 0, 243, 'A separate base room for children under two is required. Nappy changing facilities are required.', 1),
(97, 8, 'The premises are clean with adequate lighting and ventilation', '', NULL, 1, 0, 245, 'Please ensure that your premises is clean, well lit,with adequate natural lighting where appropriate, adequately ventilated and maintained in a suitable state of repair and decoration.', 2),
(98, 8, 'The registered premises are for the sole use of the facility during the hours of operation.', '', NULL, 0, 0, 247, 'The registered premises should have the sole use of the facility during the hours', 3),
(99, 8, 'We have access to a telephone on the premises', '', NULL, 0, 0, 249, 'Please ensure that there is at least one telephone on your premises', 4),
(100, 8, 'The following space standards are maintained in my service', '', NULL, 0, 1, NULL, 'Please ensure that the following space requirements are adhered to: <ul><li>under 2 years 3.5 square metres</li><li>2 years 2.5 square metres</li><li>3–7 years 2.3 square metres</li></ul>', 5),
(101, 8, 'The following space standards are maintained in my service', '', NULL, 0, 1, 255, '', 6),
(102, 8, 'There is a resting area for children who wish to relax, sleep or play quietly which is equipped with adequate furniture and materials', '', NULL, 1, 0, 257, 'Please ensure that there is a resting area for children who wish to relax, sleep or play quietly which is equipped with adequate furniture and materials', 7),
(103, 8, 'We have storage area for equipment', '', NULL, 1, 0, 259, 'Please ensure that you have a stroage area for equipment', 8),
(104, 8, 'There is a minimum of one toilet and one wash hand basin with hot and cold water available for every 10 children over the age of two years.', '', NULL, 1, 0, 261, 'Please ensure that there is a minimum of one toilet and one wash hand basin with hot and cold water available for every 10 children over the age of two years.', 9),
(105, 8, 'We have an area to store records and sensitive information', '', NULL, 1, 0, 263, 'Please ensure that there is a dedicated area to store records and sensitive information', 10),
(106, 8, 'There are separate toilet facilities for adults', '', NULL, 1, 0, 265, 'Please ensure that there are separate toilet facilities for adults', 11),
(107, 8, 'There is a break room availabe for staff away from any areas being used by children', '', NULL, 1, 0, 267, 'Please ensure that staff have a break room', 12),
(108, 8, 'There is a kitchen where food can be prepared. Exceptionally, if this is not available, the registered person will show how adequate arrangements will be made to provide food and drinks for children and staff', '', NULL, 1, 0, 269, 'There should be a kitchen where food can be prepared unless in some circumstances where the provider how food is prepared with other adequate arrangements.', 13),
(109, 8, 'Children do not have access to the kitchen unless it is being used solely for a supervised children’s activity', '', NULL, 0, 0, 271, 'Children should not have access to the kitchen unless it is for a supervised learning opportunity', 14),
(110, 8, 'A separate laundry is provided or adequate arrangements made to launder nursery linen. Where laundry facilities are provided on site, children are not allowed access.', '', NULL, 0, 0, 273, 'A separate laundry area should be provided or adequate arrangements made. Where laundry facilities are provided on site, children are not allowed access.', 15),
(111, 8, 'Arrangements are made to ensure that an adequate supply of clean bedding, towels, spare clothes etc. are always available', '', NULL, 0, 0, 275, 'Please ensure that arrangements are made to ensure that an adequate supply of clean bedding, towels, spare and so on are always available', 16),
(112, 9, 'Furniture, toys and equipment on the premises are in good repair and conform to BS EN safety standards or the Toys (Safety) Regulations (1995) where applicable. Where public playgrounds are used, the registered person ensures that the children do not use faulty equipment.', '', NULL, 0, 0, 277, 'Please ensure that furniture, toys and equipment on the premises are in good repair and conform to BS EN safety standards or the Toys (Safety) Regulations (1995) where applicable. Where public playgrounds are used, the registered person ensures that the children do not use faulty equipment.', 17),
(113, 9, 'Developmentally appropriate toys and materials are available for children', '', NULL, 0, 0, 279, 'Developmentally appropriate toys and materials are available for children', 18),
(114, 9, 'There are sufficient numbers of child sized chairs and tables to allow flexible arrangements for groups of children to play and eat together.', '', NULL, 0, 0, 281, 'There should be sufficient numbers of child sized chairs and tables to allow flexible arrangements for groups of children to play and eat together in your service.', 19),
(115, 10, 'A risk assessment is conducted and reviewed if there is a significant change. An action plan is developed to reduce any risks or hazards identified', '', NULL, 1, 0, 283, 'A risk assessment should be conducted and reviewed if there is a significant change. An action plan is developed to reduce any risks or hazards identified', 1),
(116, 10, 'Staff are trained to have an understanding of health and safety requirements', '', NULL, 0, 0, 285, 'Staff should be trained to have an understanding of health and safety requirements', 2),
(117, 10, 'Appliances are in compliance with health and safety regulations. They undergo safety checks and are maintained.', '', NULL, 0, 0, 287, 'Gas, electrical and other appliances and fittings should conform to safety requirements and should not pose a hazard to children.', 3),
(118, 10, 'Indoor and outdoor play areas are secured. Children cannot leave these areas without supervision', '', NULL, 1, 0, 289, 'Children should not be able to leave the premises or outdoor play areas without supervision', 4),
(119, 10, 'There is a procedure to be folowed in the event that a child is missing or not collected', '', NULL, 1, 0, 291, 'Please ensure that there is a policy/procedure in place for children who are not collected or who are lost.', 5),
(120, 10, 'Natural water areas such as ponds are made safe or inaccessible to children. Outdoor water activities are closely supervised at all times', '', NULL, 1, 0, 293, 'Please ensure that any areas such as natural water areas or ponds are made safe or inaccessible to children. Outdoor water activities must be closely supervised at all times', 6),
(121, 10, 'There are clearly defined procedures for emergency evacuation of your premises', '', NULL, 1, 0, 295, 'Please ensure that there are clearly defined procedures for emergency evacuation of your premises', 7),
(122, 10, 'Fire drills are carried out periodically and a record is kept of such drills, any fire certificates or any other records required by the Fire Safety Officer', '', NULL, 1, 0, 297, 'Please ensure that fire drills are carried out periodically and a record is kept of such drills, any fire certificates or any other records required by the Fire Safety Officer', 8),
(123, 10, 'Fire doors are kept clear and free at all times', '', NULL, 1, 0, 299, '', 9),
(124, 10, 'Fire blankets, extinguishers, alarms and smoke detectors which conform to BS EN safety standards are provided as necessary, checked to the frequency specified by the manufacturer and kept in working order', '', NULL, 1, 0, 301, 'Fire blankets, extinguishers, alarms and smoke detectors which conform to BS EN safety standards should be available on your premises, provided as necessary, checked to the frequency specified by the manufacturer and kept in working order', 10),
(125, 10, 'We have a policy and procedure to follow for outings. ', '', NULL, 1, 0, 303, '', 11),
(126, 10, 'Records are kept about vehicles in which children are transported, including insurance details and a list of named drivers. Drivers using their own transport have adequate insurance cover.', '', NULL, 1, 0, 305, '', 12),
(127, 11, 'Staff are informed of and kept up to date with hygiene procedures.', '', NULL, 1, 0, 307, 'Staff should be informed of and kept up to date with hygiene procedures.', 1),
(128, 11, 'Children are encouraged to learn about personal hygiene through the daily routine.', '', NULL, 0, 0, 309, 'Children should be encouraged to learn about personal hygiene through the daily routine.', 2),
(129, 11, 'Any animals on the premises do not pose a health risk to children ', '', NULL, 0, 0, 311, 'Please ensure that any animals on the premises are safe to be in the proximity of children and do not pose a health risk.', 3),
(130, 11, 'Sand pits/play areas are kept clean to avoid contamination', '', NULL, 1, 0, 313, 'Sand pits/areas should be cleaned regularly to avoid contamination', 4),
(131, 11, 'Those responsible for the preparation and handling of food are fully aware of, and comply with, regulations relating to food safety and hygiene', '', NULL, 1, 0, 315, 'Please ensure that those responsible for the preparation and handling of food are fully aware of, and comply with, regulations relating to food safety and hygiene. This can be achieved through training on regulations relating to food safety.', 5),
(132, 11, 'We have a policy on the administration of medication and this has been given to parents and staff', '', NULL, 1, 0, 317, 'Please ensure you have a policy on the administration of medication.', 6),
(133, 11, 'If medication is to be administered, the following procedures are followed:', '', NULL, 1, 1, NULL, 'Please ensure that if medication is to be administered that: medicines are not usually administered unless they have been prescribed for that child by a doctor;<ul><li>Consent is obtained from parents before administering any medication</li><li>written records are kept of all medicines administered to children and parents sign the record book to acknowledge the entry</li><li>if the administration of prescription medicines requires technical/medical knowledge then individual training is provided for staff from a qualified health professional. Training is specific to the individual child concerned.</li></ul>', 7),
(134, 11, 'A first aid box is acessible to adults only, with the necessary materials as deemed by the First Aid Training Course. The materials are kept up to date and stocked.', '', NULL, 1, 0, 321, 'Please ensure your first aid box is stocked approporiately and is kept out of the reach of children.', 8),
(135, 11, 'At least one member of staff has a current and valid First Aid certificate. The first aid qualification includes training in first aid for infants and young children.', 'You may include your First Aid Certificate here.', NULL, 1, 0, 323, 'Please ensure at least one member of staff has training in First Aid', 9),
(136, 11, 'Parents sign any and all records of accidents on our premises including during events such as outings', '', NULL, 1, 0, 325, 'Parents sign any and all records of accidents on our premises including during events such as outings', 10),
(137, 12, 'There is a policy about the exclusion of children who are ill or infectious. This is discussed with parents and includes a procedure for contacting parents or another adult designated by the parent if a child becomes ill whilst in your care.', '', NULL, 1, 0, 327, 'Please ensure that here is a policy about the exclusion of children who are ill or infectious. This should be discussed with parents and includes a procedure for contacting parents or another adult designated by the parent if a child becomes ill whilst in your care.', 1),
(138, 12, 'We request information from parents about any special dietary requirements, preferences or food allergies their child may have and a record is made of these special requirements, if any.', '', NULL, 1, 0, 329, 'Please ensure that if any child has special dietary requirements, allergies, intolerances or similar that this is recorded and adhered to.', 2),
(139, 12, 'We actively promote equality of opportunity and anti-discriminatory practice for all children. We have policies and procedures to support this. These are accessible to parents and staff.', '', NULL, 1, 0, 331, 'Please ensure that you have inclusion policies and procedures which are accessible to parents and staff.  These should be reviewed regularly.', 3),
(140, 12, 'We communicate with parents to ensure that all children’s records contain information which enables appropriate care to be given', '', NULL, 1, 0, 333, 'Please ensure that child records are up to date by communicating with parents and reviewing records where necessary ', 4),
(141, 12, 'We have a written statement about special needs which is consistent with current legislation and guidance and includes both special educational needs and disabilities. It is available to parents.', '', NULL, 1, 0, 335, 'A policy on special needs is required.', 5),
(142, 12, 'The physical environment is suitable for children with physical disabilities', '', NULL, 1, 0, 337, 'Children with physical disabilities should have an environment which is suited to them and their needs.', 6),
(143, 12, 'We consult with parents/caregivers about the need for any special services and equipment for the children in their care.', '', NULL, 1, 0, 339, 'Please do your best to discuss with parents/caregivers about the need for any special services and equipment for their children', 7),
(144, 12, 'We have a policy on behaviour management, including bullying, which states the methods used to manage children’s behaviour. This is fully understood and followed by all staff and discussed with parents and children.', '', NULL, 1, 0, 341, 'A policy on behaviour management is required.', 8),
(145, 12, 'Adults do not use any form of physical intervention, e.g. holding, unless it is necessary to prevent personal injury to the child, other children, an adult or serious damage to property. Any incident is recorded and the parent informed of the incident on the day.', '', NULL, 0, 0, 343, 'Corporal punishment is not permitted. ', 9),
(146, 12, 'There is a named staff member within the setting who has the responsibility for behaviour management issues and has the skills to support staff and be able to access expert advice if ordinary methods are not effective with a particular child.', 'Have you or any member of your staff received training on behaviour management in early years services? Upload your supporting document here.', NULL, 1, 0, 345, 'There should be a designated person who has the skills to support staff in behaviour management be able to access expert advice if ordinary methods are not effective with a particular child.', 10),
(147, 12, 'Information is given to parents which include:', '', NULL, 0, 1, NULL, 'All of the information must be given to parents.', 11);
INSERT INTO `questions` (`question_id`, `category_id`, `question_body`, `question_description`, `school_categories`, `question_upload`, `question_multiple_choice`, `answer_id`, `question_recommendation`, `question_sort`) VALUES
(148, 12, 'There is a system in place for the regular exchange of information between parents and staff members. Parents are able to share information and their views and concerns are respected and acknowledged. Appropriate and prompt action is taken on any concerns raised and a record of all complaints is maintained', '', NULL, 0, 0, 353, 'Please ensure that thre is a system in place to aid in family involvement and communication with children’s families and teachers.', 12),
(149, 12, 'We understand the need to maintain privacy and confidentiality for children and their families', '', NULL, 1, 0, 355, 'Protection of children’s privacy is important for early years services. Please take all reasonable steps to ensure the privacy and confidentiality of childrens records and data is upheld.', 13),
(150, 12, 'Parents have access to children’s records. Regular information is provided for parents about activities provided for the children, for example, through wall displays, photographs and examples of children’s work', '', NULL, 1, 0, 357, 'Parents should be able to access their childs records.', 14),
(151, 12, 'Children are only released from the care of the provision to individuals named by the parent.', '', NULL, 0, 1, NULL, 'Children should only released from the care of the provision to individuals named by the parent.', 15),
(152, 12, 'We have a designated staff member has training in child protection and other staff can implement child protection poilicies and procedures in the abseence of the designated member of staff.', '', NULL, 1, 0, 361, 'Please ensure that a designated member of staff has attended a child protection training course and is responsible for liaison with local child protection agencies and with Ofsted in any child protection situation', 16),
(153, 12, 'Records relating to individual children are retained for a reasonable period of time after the children have left the provision. The records are always available for inspection by the early years child care inspector.', '', NULL, 0, 0, 363, '', 17),
(154, 12, 'If you are an overnight setting, please ensure that a contract, signed by the parent, stating all relevant details regarding the child and their care, including the name of the emergency contact and confirmation of their agreement to collect the child during the night if necessary, is obtained.', '', NULL, 1, 0, 365, 'Please ensure that a contract, signed by the parent, stating all relevant details regarding the child and their care, including the name of the emergency contact and confirmation of their agreement to collect the child during the night if necessary, is obtained.', 18);

-- --------------------------------------------------------

--
-- Table structure for table `question_answers`
--

CREATE TABLE `question_answers` (
  `answer_id` int(11) UNSIGNED NOT NULL,
  `question_id` int(11) UNSIGNED NOT NULL,
  `answer_body` text,
  `answer_sort` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `question_answers`
--

INSERT INTO `question_answers` (`answer_id`, `question_id`, `answer_body`, `answer_sort`) VALUES
(1, 1, 'Yes', 1),
(2, 1, 'No', 2),
(11, 4, 'Yes', 1),
(12, 4, 'No', 2),
(13, 5, 'Yes', 1),
(14, 5, 'No', 2),
(15, 6, 'Yes', 1),
(16, 6, 'No', 2),
(17, 7, 'Yes', 1),
(18, 7, 'No', 2),
(19, 9, 'Yes', 1),
(20, 9, 'No', 2),
(21, 10, 'Yes', 1),
(22, 10, 'No', 2),
(23, 12, 'Yes', 1),
(24, 12, 'No', 2),
(25, 14, 'Yes', 1),
(26, 14, 'No', 2),
(27, 15, 'Yes, I have', 1),
(28, 15, 'No, I have not', 2),
(29, 16, 'Yes, I have', 1),
(30, 16, 'No, I have not', 2),
(31, 17, 'Yes, I have', 1),
(32, 17, 'No, I have not', 2),
(33, 18, 'Yes, I have taken these steps', 1),
(34, 18, 'No I have not taken these steps yet', 2),
(35, 19, 'Yes', 1),
(36, 19, 'No', 2),
(38, 20, 'The name and date of birth of the child', 1),
(39, 20, 'The date on which the child first attended the service', 2),
(40, 20, 'The date on which the child ceased to attend the service', 3),
(41, 20, 'The name and address of a parent or guardian of the child and a telephone number where that parent or guardian or a relative or friend of the child can be contacted during the hours of operation of the service', 4),
(42, 20, 'Authorisation for the collection of the child', 5),
(43, 20, 'Details of any illness, disability, allergy or special need of the child, together with all the information relevant to the provision of special care or attention', 6),
(44, 20, 'The name and telephone number of the child’s registered medical practitioner', 7),
(45, 20, 'Record of immunisations, if any, received by the child; (i) written parental consent for appropriate medical treatment of the child in the event of an emergency', 8),
(47, 21, 'A record in writing shall be open to inspection on the premises by—\n\n(a) a parent or guardian of a pre-school child but only in respect of the record relating to that child,\n(b) an employee who is authorised in that behalf by the registered provider, and (c) an authorised person.', 1),
(48, 21, 'A registered provider shall ensure that a record in writing referred to in paragraph (1) is retained for a period of 2 years from the date on which the child to whom it relates ceases to attend the service.', 2),
(49, 21, 'A registered provider shall ensure that a record in writing referred to in paragraph (2) is retained for a period of 2 years from the date on which the child attends the service.', 3),
(51, 22, 'The name, position, qualifications and experience of the person in charge and of every other employee, unpaid worker and contractor', 1),
(52, 22, 'Details of the class of service and the age profile of children for which the service is registered to provide services', 2),
(53, 22, 'Details of the adult:child ratios in the service', 3),
(54, 22, 'The type of care or programme provided in the service', 4),
(55, 22, 'The facilities available', 5),
(56, 22, 'The opening hours and fees', 6),
(57, 22, 'The policies, procedures and statements the service is required to maintain in accordance with Regulation 10', 7),
(58, 22, 'Details of attendance by each pre-school child on a daily basis', 8),
(59, 22, 'Details of staff rosters on a daily basis', 9),
(60, 22, 'Details of any medication administered to a pre-school child attending the service with signed parental consent', 10),
(61, 22, 'Details of any accident, injury or incident involving a pre-school child attending the service', 11),
(63, 23, 'Yes', 1),
(64, 23, 'No', 2),
(65, 24, 'Yes', 1),
(66, 24, 'No', 2),
(67, 25, 'I have given them information on our policies and procedures.', 1),
(68, 25, 'I have provided parents with a copy of the Early Years Regulations, 2016 or they have access to this on my preschool’s premises.', 2),
(70, 26, 'Yes', 1),
(71, 26, 'No', 2),
(74, 28, 'Yes', 1),
(75, 28, 'No', 2),
(76, 29, 'Yes', 1),
(77, 29, 'No', 2),
(78, 30, 'Yes', 1),
(79, 30, 'No', 2),
(80, 31, '1 year', 1),
(81, 31, '2 years', 2),
(82, 31, '3 years', 3),
(83, 31, '4 years', 4),
(84, 31, '5 years', 5),
(85, 32, 'Yes', 1),
(86, 32, 'No', 2),
(87, 33, 'Yes', 1),
(88, 33, 'No', 2),
(89, 34, 'Yes', 1),
(90, 34, 'No', 2),
(91, 35, 'Yes', 1),
(92, 35, 'No', 2),
(93, 36, 'Yes', 1),
(94, 36, 'No', 2),
(95, 37, 'Yes', 1),
(96, 37, 'No', 2),
(99, 39, 'Yes', 1),
(100, 39, 'No', 2),
(103, 41, 'I moved premises on or after 30 June 2016 and have a suitable, safe and secure outdoor play area which children have access to on a daily basis', 1),
(104, 41, 'I moved premises before 30 June 2016 and children in my setting have access to an outdoor play area', 2),
(107, 43, 'Yes', 1),
(108, 43, 'No', 2),
(109, 44, 'Yes', 1),
(110, 44, 'No', 2),
(111, 45, 'Yes', 1),
(112, 45, 'No', 2),
(113, 46, 'Yes', 1),
(114, 46, 'No', 2),
(115, 47, 'Yes', 1),
(116, 47, 'No', 2),
(117, 48, 'Yes', 1),
(118, 48, 'No', 2),
(119, 49, 'Sound and stable structure', 1),
(120, 49, 'Safe and secure', 2),
(121, 49, 'Kept adequately lit, heated and ventilated', 3),
(122, 49, 'Cleaned, maintained and repaired, as required', 4),
(123, 49, 'Equipped with adequate and suitable sanitary facilities', 5),
(125, 50, 'Yes', 1),
(126, 50, 'No', 2),
(127, 51, 'Yes', 1),
(128, 51, 'No', 2),
(129, 52, 'The death of a pre-school child while attending the service, including the death of a child in hospital following his or her transfer to hospital from the service.', 1),
(131, 53, 'The procedure to be followed by a person for the purposes of making a complaint in relation to the service', 1),
(132, 53, 'The manner in which such a complaint shall be dealt with', 2),
(133, 53, 'The procedures for keeping a person who makes such a complaint informed of the manner in which it is being dealt with', 3),
(134, 53, 'Record in writing is kept of a complaint made to the provider in respect of the pre-school service', 4),
(135, 53, 'The complaint is duly dealt with in accordance with my complaints policy. The record in writing includes (a) include the nature of the complaint and the manner in which the complaint was dealt with, and (b) be open to inspection on the premises by an authorised person', 5),
(137, 54, 'Yes', 1),
(138, 54, 'No', 2),
(140, 8, 'References from the person’s most recent and past employers, if any.', 1),
(141, 8, 'References from reputable sources in the case of a person who has no past employers.', 2),
(142, 8, 'Vetting disclosure from the National Vetting Bureau of the Garda Síochána.', 3),
(143, 8, 'Where a person has lived in a state other than Ireland for a period of longer than 6 consecutive months, he or she provides police vetting from the police authorities in that state.', 4),
(145, 11, 'Yes', 1),
(146, 11, 'No', 2),
(147, 55, 'Yes', 1),
(148, 55, 'No', 2),
(149, 56, 'Details of attendance by each pre-school child on a daily basis.', 1),
(150, 56, 'Details of daily staff rosters.', 2),
(151, 56, 'Details of any medication administered to a pre-school child attending the service with signed parental consent.', 3),
(152, 56, 'Details of any accident, injury or incident involving a pre-school child attending the service.', 4),
(154, 52, 'The diagnosis of a pre-school child attending the service, an employee, unpaid worker, contractor or other person working in the service as suffering from an infectious disease within the meaning of the Infectious Diseases Regulations 1981 (S.I. No. 390 of 1981).', 2),
(155, 52, 'An incident that occurs in the service that results in the service being closed for any length of time.', 3),
(156, 52, 'Serious injury of a pre-school child while attending the service that requires immediate medical treatment by a registered medical practitioner whether in a hospital or otherwise.', 4),
(157, 52, 'An incident in respect of which a pre-school.', 5),
(158, 13, 'Yes', 1),
(159, 13, 'No', 2),
(160, 57, 'Yes', 1),
(161, 57, 'No', 2),
(162, 58, 'Yes', 1),
(163, 58, 'No', 2),
(164, 59, 'Yes', 1),
(165, 59, 'No', 2),
(166, 28, 'I have less than 15 children attending my service', 3),
(167, 41, 'None of the above', 3),
(168, 60, 'Yes', 1),
(169, 60, 'No', 2),
(170, 61, 'Yes', 1),
(171, 61, 'No', 2),
(172, 62, 'Yes', 1),
(173, 62, 'No', 2),
(174, 63, 'Yes (or not applicable)', 1),
(175, 63, 'No', 2),
(176, 64, 'Yes (or not applicable)', 1),
(177, 64, 'No', 2),
(178, 65, 'Yes (or not applicable)', 1),
(179, 65, 'No', 2),
(180, 66, 'Yes (or not applicable)', 1),
(181, 66, 'No', 2),
(182, 67, 'Yes', 1),
(183, 67, 'No', 2),
(184, 68, 'Yes', 1),
(185, 68, 'No', 2),
(186, 69, 'Yes', 1),
(187, 69, 'No', 2),
(188, 70, 'Yes', 1),
(189, 70, 'No', 2),
(190, 71, 'Yes', 1),
(191, 71, 'No', 2),
(192, 72, 'Yes', 1),
(193, 72, 'No', 2),
(194, 73, 'Yes', 1),
(195, 73, 'No', 2),
(196, 74, 'Yes', 1),
(197, 74, 'No', 2),
(198, 75, 'Yes', 1),
(199, 75, 'No', 2),
(200, 76, 'Yes', 1),
(201, 76, 'No', 2),
(202, 77, 'Yes', 1),
(203, 77, 'No', 2),
(204, 78, 'Yes', 1),
(205, 78, 'No', 2),
(206, 79, 'Yes', 1),
(207, 79, 'No', 2),
(208, 80, 'Yes', 1),
(209, 80, 'No', 2),
(210, 81, 'Yes', 1),
(211, 81, 'No', 2),
(212, 82, 'Yes', 1),
(213, 82, 'No', 2),
(214, 83, 'Yes', 1),
(215, 83, 'No', 2),
(216, 84, 'Yes', 1),
(217, 84, 'No', 2),
(218, 85, '1:3 children under 2 years', 1),
(219, 85, '1:4 children aged 2 years', 2),
(220, 85, '1:8 children aged 3–7 years', 3),
(222, 86, 'Yes', 1),
(223, 86, 'No', 2),
(224, 87, 'Yes', 1),
(225, 87, 'No', 2),
(226, 88, 'Yes', 1),
(227, 88, 'No', 2),
(228, 89, 'Yes', 1),
(229, 89, 'No', 2),
(230, 90, 'Yes', 1),
(231, 90, 'No', 2),
(232, 91, 'Yes', 1),
(233, 91, 'No', 2),
(234, 92, 'Yes', 1),
(235, 92, 'No', 2),
(236, 93, 'Yes', 1),
(237, 93, 'No', 2),
(238, 94, 'Yes', 1),
(239, 94, 'No', 2),
(240, 95, 'Yes', 1),
(241, 95, 'No', 2),
(242, 96, 'Yes', 1),
(243, 96, 'No', 2),
(244, 97, 'Yes', 1),
(245, 97, 'No', 2),
(246, 98, 'Yes', 1),
(247, 98, 'No', 2),
(248, 99, 'Yes', 1),
(249, 99, 'No', 2),
(250, 100, 'Under 2 years: 3.5 Square Metres', 1),
(251, 100, '2 years: 2.5 Square Metres', 2),
(252, 100, '3–7 years: 2.3 Square Metres', 3),
(254, 101, 'Yes', 1),
(255, 101, 'No', 2),
(256, 102, 'Yes', 1),
(257, 102, 'No', 2),
(258, 103, 'Yes', 1),
(259, 103, 'No', 2),
(260, 104, 'Yes', 1),
(261, 104, 'No', 2),
(262, 105, 'Yes', 1),
(263, 105, 'No', 2),
(264, 106, 'Yes', 1),
(265, 106, 'No', 2),
(266, 107, 'Yes', 1),
(267, 107, 'No', 2),
(268, 108, 'Yes', 1),
(269, 108, 'No', 2),
(270, 109, 'Yes', 1),
(271, 109, 'No', 2),
(272, 110, 'Yes', 1),
(273, 110, 'No', 2),
(274, 111, 'Yes', 1),
(275, 111, 'No', 2),
(276, 112, 'Yes', 1),
(277, 112, 'No', 2),
(278, 113, 'Yes', 1),
(279, 113, 'No', 2),
(280, 114, 'Yes', 1),
(281, 114, 'No', 2),
(282, 115, 'Yes', 1),
(283, 115, 'No', 2),
(284, 116, 'Yes', 1),
(285, 116, 'No', 2),
(286, 117, 'Yes', 1),
(287, 117, 'No', 2),
(288, 118, 'Yes', 1),
(289, 118, 'No', 2),
(290, 119, 'Yes', 1),
(291, 119, 'No', 2),
(292, 120, 'Yes', 1),
(293, 120, 'No', 2),
(294, 121, 'Yes', 1),
(295, 121, 'No', 2),
(296, 122, 'Yes', 1),
(297, 122, 'No', 2),
(298, 123, 'Yes', 1),
(299, 123, 'No', 2),
(300, 124, 'Yes', 1),
(301, 124, 'No', 2),
(302, 125, 'Yes', 1),
(303, 125, 'No', 2),
(304, 126, 'Yes', 1),
(305, 126, 'No', 2),
(306, 127, 'Yes', 1),
(307, 127, 'No', 2),
(308, 128, 'Yes', 1),
(309, 128, 'No', 2),
(310, 129, 'Yes', 1),
(311, 129, 'No', 2),
(312, 130, 'Yes', 1),
(313, 130, 'No', 2),
(314, 131, 'Yes', 1),
(315, 131, 'No', 2),
(316, 132, 'Yes', 1),
(317, 132, 'No', 2),
(318, 133, 'Yes', 1),
(319, 133, 'No', 2),
(320, 134, 'Yes', 1),
(321, 134, 'No', 2),
(322, 135, 'Yes', 1),
(323, 135, 'No', 2),
(324, 136, 'Yes', 1),
(325, 136, 'No', 2),
(326, 137, 'Yes', 1),
(327, 137, 'No', 2),
(328, 138, 'Yes', 1),
(329, 138, 'No', 2),
(330, 139, 'Yes', 1),
(331, 139, 'No', 2),
(332, 140, 'Yes', 1),
(333, 140, 'No', 2),
(334, 141, 'Yes', 1),
(335, 141, 'No', 2),
(336, 142, 'Yes', 1),
(337, 142, 'No', 2),
(338, 143, 'Yes', 1),
(339, 143, 'No', 2),
(340, 144, 'Yes', 1),
(341, 144, 'No', 2),
(342, 145, 'Yes', 1),
(343, 145, 'No', 2),
(344, 146, 'Yes', 1),
(345, 146, 'No', 2),
(346, 147, 'Basic written information about the setting, e.g. the admissions policy, hours, contact information, staffing, routines and so on.', 1),
(347, 147, 'The role of parents, including any expectations that parents participate on the management committee or as volunteers', 2),
(348, 147, 'Details of policies and procedures which are available to parents', 3),
(349, 147, 'A written complaints procedure which includes the address and telephone number of the regulator', 4),
(350, 147, 'Information about activities provided for children', 5),
(352, 148, 'Yes', 1),
(353, 148, 'No', 2),
(354, 149, 'Yes', 1),
(355, 149, 'No', 2),
(356, 150, 'Yes', 1),
(357, 150, 'No', 2),
(358, 151, 'Yes', 1),
(359, 151, 'No', 2),
(360, 152, 'Yes', 1),
(361, 152, 'No', 2),
(362, 153, 'Yes', 1),
(363, 153, 'No', 2),
(364, 154, 'Yes', 1),
(365, 154, 'No', 2);

-- --------------------------------------------------------

--
-- Table structure for table `question_categories`
--

CREATE TABLE `question_categories` (
  `category_id` int(11) UNSIGNED NOT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `country_id` varchar(2) NOT NULL,
  `category_sort` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `question_categories`
--

INSERT INTO `question_categories` (`category_id`, `category_name`, `country_id`, `category_sort`) VALUES
(1, 'Preschool Registration', 'IE', 1),
(2, 'Management and Staff', 'IE', 2),
(3, 'Records', 'IE', 3),
(4, 'Health, Welfare and Development of Child', 'IE', 4),
(5, 'Premises and Space Requirements', 'IE', 5),
(6, 'Organisation', 'GB', 1),
(7, 'Care, Learning and Play', 'GB', 2),
(8, 'Physical Environment', 'GB', 3),
(9, 'Equipment', 'GB', 4),
(10, 'Safety', 'GB', 5),
(11, 'Health', 'GB', 6),
(12, 'Inclusion and Family Involvement', 'GB', 7);

-- --------------------------------------------------------

--
-- Table structure for table `question_school`
--

CREATE TABLE `question_school` (
  `question_id` int(11) UNSIGNED NOT NULL,
  `answer_id` int(11) UNSIGNED NOT NULL,
  `school_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `records`
--

CREATE TABLE `records` (
  `record_id` int(11) UNSIGNED NOT NULL,
  `school_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `record_public` tinyint(1) NOT NULL DEFAULT '0',
  `record_date` date NOT NULL,
  `record_time` time DEFAULT NULL,
  `record_type` enum('nap','medication','meal','diaper','toilet','mood','note') NOT NULL DEFAULT 'note',
  `record_comment` varchar(512) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `record_params`
--

CREATE TABLE `record_params` (
  `record_id` int(11) UNSIGNED NOT NULL,
  `param_id` varchar(32) NOT NULL,
  `param_value` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `referral_id` varchar(16) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `school_id` int(11) UNSIGNED NOT NULL,
  `referral_email` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resource_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `resource_name` varchar(64) DEFAULT NULL,
  `resource_description` varchar(140) DEFAULT NULL,
  `resource_min_age` tinyint(1) UNSIGNED DEFAULT '0',
  `resource_max_age` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `categories` varchar(255) DEFAULT NULL,
  `resource_url` varchar(255) NOT NULL,
  `resource_thumbnail_url` varchar(255) DEFAULT NULL,
  `resource_downloads` int(11) UNSIGNED NOT NULL,
  `resource_status` enum('P','A','D') NOT NULL DEFAULT 'P',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `resource_categories`
--

CREATE TABLE `resource_categories` (
  `category_id` int(11) UNSIGNED NOT NULL,
  `category_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) UNSIGNED NOT NULL,
  `school_id` int(11) UNSIGNED NOT NULL,
  `room_name` varchar(64) DEFAULT NULL,
  `room_description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `school_id` int(11) UNSIGNED NOT NULL,
  `stripe_id` varchar(64) DEFAULT NULL,
  `stripe_connect_id` varchar(64) DEFAULT NULL,
  `school_vat_id` varchar(32) DEFAULT NULL,
  `school_billing_date` date DEFAULT NULL,
  `school_name` varchar(64) NOT NULL,
  `school_type` enum('P','C') NOT NULL DEFAULT 'P',
  `category_id` int(11) UNSIGNED NOT NULL,
  `school_avatar_url` varchar(255) DEFAULT NULL,
  `school_street` varchar(64) DEFAULT NULL,
  `school_city` varchar(64) DEFAULT NULL,
  `school_postal_code` varchar(64) DEFAULT NULL,
  `country_id` varchar(2) NOT NULL,
  `school_phone` varchar(32) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `school_categories`
--

CREATE TABLE `school_categories` (
  `category_id` int(11) UNSIGNED NOT NULL,
  `category_name` varchar(64) DEFAULT NULL,
  `category_description` text,
  `country_id` varchar(2) NOT NULL,
  `category_sort` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `school_categories`
--

INSERT INTO `school_categories` (`category_id`, `category_name`, `category_description`, `country_id`, `category_sort`) VALUES
(1, 'Drop-In Centre', 'Children can be left for a short time while parents avail of a service, such as shopping or a gym.', 'IE', 3),
(2, 'Childminding Service', 'A pre-school service which may include an overnight service offered by a person who single-handedly takes care of pre-school children, including the childminder’s own children, in the childminder’s home for a total of more than 2 hours per day, except where exemptions apply.', 'IE', 4),
(3, 'Temporary Pre-School Service', 'A pre-school service offering day care exclusively on a temporary basis. This refers to a service where a pre-school child is cared for while the parent or guardian is attending a once-off event such as a conference or a sports event.', 'IE', 5),
(4, 'Overnight Pre-School Service', 'A service in which pre-school children are taken care of for a total of more than 2 hours between the hours of 7pm and 6am except where exemptions provided apply.', 'IE', 6),
(5, 'Full Day Care Service', 'Full day care services are provided to pre-school children for more than 3.5 hours. These include day nurseries and crèches. These are usually geared at all ages of pre-school children, including babies and can often also take in school children in the afternoon.', 'IE', 1),
(6, 'Sessional Service', 'Sessional Services offer a planned programme for usually no more than 3.5 hours per session and include pre-schools, playgroups, crèches, Naionrai and Montessori groups. These are usually available to children aged three and over.', 'IE', 2),
(7, 'Primary School', 'Junior and senior primary school teachers.', 'IE', 7),
(8, 'Day Nurseries', 'Day nurseries are usually privately run and provide care for children aged from six weeks to five years old. All must be registered and annually inspected by their Early Years Team in their local Health and Social Care Trust.', 'GB', 1),
(9, 'Private Nursery Schools (i.e. Private Independent Schools)', 'Private Nursery Schools provide full or sessional day care to children aged two to five. They may follow a specific educational approach. Private nurseries may register with Ofsted or the ISI.', 'GB', 2),
(10, 'Independent Schools', 'Independent schools are privately run and educate children from three to sixteen years old. They may be registered with Oftsed of ISI. They may follow the EYFS or follow their own curriculum and operating procedures.', 'GB', 3),
(11, 'Pre-schools', 'Pre-schools are operated by trained early years professionals who provide learning experiences for children between 2 and 5. Some preschools will run 5 mornings per week and have afternoon sessions. Preschools operate for at least 38 weeks in the year and usually operate according to term time. They may be registered with Ofsted.', 'GB', 4),
(12, 'Playgroups', 'Playgroups are operated by early years professionals. Playgroups offer short daily sessions of care and learning through play for children around aged two to four years old approximately. They operate for (at least) 38 weeks in the year, 5 days per week. They may be registered with Ofsted.', 'GB', 5),
(13, '(LEA) Maintained Nursery Schools', 'These schools offer full and part-time early years education places, typically between school hours. They are attached to primary schools. A child can attend for a full or half day. Nursery schools may also offer childcare after school.', 'GB', 6),
(14, 'OFSTED Registered Childminder', 'Registered childminders are self-employed carers who work from their own homes. They must be registered with their local Health and Social Care Trust if they look after children to whom they are not closely related for more than two hours in any day, for reward.', 'GB', 7),
(15, 'Out of School Clubs (i.e. Play Centres)', 'Out of School Clubs provide safe and stimulating play opportunities for school age children at times when schools are not open. They can operate before school in the mornings, from the end of the school day and at the end of the working day, throughout the school holidays, or a combination of all three. They typically cater for children aged four to fifteen.', 'GB', 8),
(16, 'Activity Clubs', 'Activity Clubs offer a range of activities for school-aged children. They are usually not registered as childcare but often run at the end of the school day or during school holidays.', 'GB', 9),
(17, 'Parent and Toddler Groups', 'Parents and Toddler groups are drop-in sessions for parents that have young children. These sessions will be run by other parents, by the voluntary sector or in Children’s Centres. They will not be registered with Ofsted, as they provide less than two hours’ worth of care in a day.', 'GB', 10),
(18, 'Preschools or Kindergartens', 'These services may be run by government, private or community providers. Children who will be starting school within the next year (4-5 year olds) can attend preschool. Some 3 year olds are also eligible to attend. Children usually attend up to four sessions per week for up to four terms. Preschools may be a stand-alone preschool centre, co-located with a school or co-located with another child care service.', 'AU', 1),
(19, 'Integrated early childhood services ', 'These services offer a combination of two or more services. The services vary from centre to centre and may include child care, playgroup, preschool, early education and learning, early development, health services and family support services.', 'AU', 2),
(20, 'Long Day Care', 'Long day care is generally available all day or on a part-time basis until children start school.', 'AU', 3),
(21, 'Occasional Care', 'Occasional care is sessional occasional child care for babies, toddlers and children under school age. In South Australia, occasional care is generally offered through government preschools and in some child care centres.', 'AU', 4),
(22, 'Out of School Hours Care (OSHC)', 'This service is available to school-aged children before and/or after school, usually on pupil free days during the school term and sometimes during school holidays.', 'AU', 5),
(23, 'In-home Care', 'Nanny and babysitting services within the home.', 'AU', 6),
(24, 'Approved carers ', 'These are individuals or organisations that have a licence to operate and have been approved by the Australian Government as having qualified and trained staff, meeting health, safety and other quality standards and being available for certain hours each week.', 'AU', 7),
(25, 'Parenting groups, playgroups and play centres', 'These options offer parents with babies and toddlers the opportunity to meet regularly with other parents, share experiences and build a support network. Generally, parents bring their children along to these groups and participate in play-based learning that supports the child\'s growth and development.', 'AU', 8);

-- --------------------------------------------------------

--
-- Table structure for table `school_user`
--

CREATE TABLE `school_user` (
  `school_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `role` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `token_id` varchar(16) NOT NULL,
  `status` enum('P','A','D') NOT NULL DEFAULT 'P'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `stories`
--

CREATE TABLE `stories` (
  `story_id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `child_id` int(11) NOT NULL,
  `story_public` tinyint(1) NOT NULL,
  `story_action_1` text,
  `story_action_2` text,
  `story_action_3` text,
  `story_reflection_1` text,
  `story_reflection_2` text,
  `story_reflection_3` text,
  `story_reflection_4` text,
  `story_reflection_5` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `story_action_4` text,
  `story_action_5` text,
  `story_action_6` text,
  `story_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB AVG_ROW_LENGTH=1064 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `timelines`
--

CREATE TABLE `timelines` (
  `timeline_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `child_id` int(11) UNSIGNED NOT NULL,
  `timeline_type` enum('create','checklist','story','accident','record','summary','monthlyPlan') NOT NULL,
  `timeline_linked_id` int(11) UNSIGNED NOT NULL,
  `timeline_public` tinyint(1) NOT NULL DEFAULT '0',
  `timeline_comment` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `timeline_comments`
--

CREATE TABLE `timeline_comments` (
  `comment_id` int(11) UNSIGNED NOT NULL,
  `timeline_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `body` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `timesheets`
--

CREATE TABLE `timesheets` (
  `timesheet_date` date NOT NULL,
  `child_id` int(11) UNSIGNED NOT NULL,
  `timesheet_in` time DEFAULT NULL,
  `timesheet_out` time DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `stripe_parent_id` varchar(64) DEFAULT NULL,
  `user_email` varchar(64) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_first_name` varchar(64) NOT NULL,
  `user_last_name` varchar(64) NOT NULL,
  `user_type` enum('T','P') NOT NULL DEFAULT 'T',
  `user_avatar_url` varchar(255) DEFAULT NULL,
  `user_notify_comment` tinyint(1) NOT NULL DEFAULT '1',
  `user_notify_story` tinyint(1) NOT NULL DEFAULT '1',
  `user_notify_checklist` tinyint(1) NOT NULL DEFAULT '1',
  `user_notify_record` tinyint(1) NOT NULL DEFAULT '1',
  `user_consent_terms` datetime NOT NULL,
  `user_consent_privacy` datetime NOT NULL,
  `user_admin` tinyint(1) NOT NULL DEFAULT '0',
  `user_status` enum('P','A','D') NOT NULL DEFAULT 'P',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `framework_texts` (
  `text_id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `text_title` varchar(512) DEFAULT NULL,
  `text_sort` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `framework_texts` (`text_id`, `category_id`, `text_title`, `text_sort`) VALUES
(1, 135, 'Next steps/Future learning opportunities', 1),
(2, 136, 'Next steps/Future learning opportunities', 1),
(3, 137, 'Next steps/Future learning opportunities', 1);

CREATE TABLE `text_story` (
  `text_id` int(11) UNSIGNED NOT NULL,
  `story_id` int(11) UNSIGNED NOT NULL,
  `contents` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ----------------------------------------------------------------
--  TABLE risk_assessments
-- ----------------------------------------------------------------

CREATE TABLE `risk_assessments`
(
   `risk_assessment_id`	int(11) UNSIGNED,
   `school_fk`       	int(11) UNSIGNED,
   `user_fk`       	 	int(11) UNSIGNED,
   `name`            	varchar(256),
   `date`            	date,
   `minimise`        	text,
   `review`          	text,
   `shareteachers`   	tinyint(1) UNSIGNED DEFAULT 0,
   `shareparents`    	tinyint(1) UNSIGNED DEFAULT 0,
   `deleted`             tinyint(1) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ----------------------------------------------------------------
--  TABLE risks
-- ----------------------------------------------------------------

CREATE TABLE `risks`
(
   `risk_id`              int(11) UNSIGNED,
   `risk_assessment_fk`   int(11) UNSIGNED,
   `description`          varchar(256),
   `people`               varchar(256),
   `rating`               varchar(20),
   `actions`              varchar(512),
   `further_actions`      varchar(512),
   `date`                 date,
   `rating_after`         varchar(20),
   `deleted`              tinyint(1) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ----------------------------------------------------------------
--  TABLE safety_fire_duties
-- ----------------------------------------------------------------

CREATE TABLE `safety_fire_duties`
(
   `type`       varchar(7) NOT NULL,
   `school_fk`  int(11) UNSIGNED,
   `name`       varchar(64),
   `phone`      varchar(32),
   `duties`     varchar(250),
   `comments`   text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
--  TABLE safety_general_register
-- ----------------------------------------------------------------

CREATE TABLE `safety_general_register`
(
   `id`                     int(11) UNSIGNED NOT NULL,
   `school_fk`  			int(11) UNSIGNED,
   `date`                   date NOT NULL,
   `time`                   time,
   `log_number`             int(11) UNSIGNED,
   `documented_by`          varchar(64),
   `drill`                  varchar(250),
   `inspection_of`          varchar(250),
   `fire`                   varchar(250),
   `fault`                  varchar(250),
   `other`                  varchar(250),
   `action`                 varchar(250),
   `date_to_be_completed`   date,
   `completed`              tinyint(1) UNSIGNED DEFAULT 0,
   `deleted`                tinyint(1) UNSIGNED DEFAULT 0,
   `file_url`     			varchar(512)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ----------------------------------------------------------------
--  TABLE safety_inventory
-- ----------------------------------------------------------------

CREATE TABLE `safety_inventory`
(
   `id`                      int(11) UNSIGNED,
   `school_fk`               int(11) UNSIGNED,
   `location_of_equipment`   varchar(250),
   `number`                  int(11) UNSIGNED,
   `type`                    varchar(512),
   `location`                text,
   `deleted`                 tinyint(1) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
--  TABLE safety_log_book
-- ----------------------------------------------------------------

CREATE TABLE `safety_log_book`
(
   `id`                 int(11) UNSIGNED,
   `school_fk`          int(11) UNSIGNED,
   `date`               date,
   `nature_of_drill`    varchar(250),
   `persons`            varchar(512),
   `evacuation_time`    varchar(64),
   `person_in_charge`   varchar(64),
   `comments`           text,
   `deleted`            tinyint(1) UNSIGNED DEFAULT 0
);


-- ----------------------------------------------------------------
--  TABLE safety_attachments
-- ----------------------------------------------------------------

CREATE TABLE `safety_attachments`
(
   `attachment_id`  int(11) UNSIGNED,
   `school_fk`	    int(11) UNSIGNED,
   `name`    		varchar(256),
   `file_url`     	varchar(512)
);


CREATE TABLE `weekly_daily_blocks` (
  `weekly_daily_block_id` int(11) NOT NULL,
  `weekly_plan_fk` int(11) NOT NULL,
  `day` enum('mon','tue','wed','thu','fri','sat','sun') NOT NULL,
  `learning_opportunity` text NOT NULL,
  `time_when` tinytext NOT NULL,
  `rationale_interests` text NOT NULL,
  `family_involvement` text NOT NULL,
  `materials` text NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `weekly_plans` (
  `weekly_plan_id` int(11) NOT NULL,
  `school_fk` int(11) NOT NULL,
  `plan_public` tinyint(1) DEFAULT NULL,
  `year` tinytext NOT NULL,
  `week` tinyint(4) NOT NULL,
  `assoc` enum('school','room','child') NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `in_session` tinyint(1) NOT NULL DEFAULT '1',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `weekly_plan_assoc` (
  `weekly_plan_assoc_id` int(11) NOT NULL,
  `weekly_plan_fk` int(11) NOT NULL,
  `assoc_type` set('school','room','child') NOT NULL,
  `assoc_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tec_sheme_info` (
  `id` int(11) UNSIGNED NOT NULL,
  `scheme_name` varchar(100) NOT NULL,
  `scheme_type` varchar(5) NOT NULL,
  `scheme_type_descr` varchar(255) DEFAULT NULL,
  `value_A` float(6,2) DEFAULT '0.00',
  `value_AJ` float(6,2) DEFAULT '0.00',
  `value_B` float(6,2) DEFAULT '0.00',
  `value_D` float(6,2) DEFAULT '0.00'
) ENGINE=InnoDB AVG_ROW_LENGTH=399 DEFAULT CHARSET=latin1;



CREATE TABLE `invoice` (
  `id` int(11) NOT NULL,
  `idT` int(11) NOT NULL,
  `nameT` varchar(255) CHARACTER SET utf8 NOT NULL,
  `cityT` varchar(255) CHARACTER SET utf8 NOT NULL,
  `postalCodeT` varchar(20) CHARACTER SET utf8 NOT NULL,
  `phoneT` varchar(20) NOT NULL,
  `emailT` varchar(255) CHARACTER SET utf8 NOT NULL,
  `idC` int(11) NOT NULL,
  `nameC` varchar(255) CHARACTER SET utf8 NOT NULL,
  `streetC` varchar(255) CHARACTER SET utf8 NOT NULL,
  `cityC` varchar(255) CHARACTER SET utf8 NOT NULL,
  `postalCodeC` varchar(20) CHARACTER SET utf8 NOT NULL,
  `invoiceNumber` int(11) NOT NULL,
  `date` date NOT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `open_days` int(11) DEFAULT NULL,
  `week` int(11) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `vatRegNo` varchar(255) DEFAULT NULL,
  `currency` enum('€','$','£','A$') CHARACTER SET utf8 NOT NULL,
  `fee` int(11) NOT NULL,
  `hours` int(11) NOT NULL,
  `discount` int(11) DEFAULT NULL,
  `discountType` enum('%','€') CHARACTER SET utf8 DEFAULT NULL,
  `discountVal` float(6,2) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','paid') NOT NULL,
  `validate` enum('no','yes') DEFAULT NULL COMMENT 'Send to parent',
  `approved` enum('no','yes') DEFAULT NULL COMMENT 'Invoice approved by the admin',
  `url_logo` varchar(255) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `invoice_extras` (
  `id` int(11) NOT NULL,
  `invoiceNumber` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  `extra_desc` varchar(255) DEFAULT NULL,
  `extra_val` float(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `invoice_scheme` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL DEFAULT '0',
  `ascc_type` varchar(4) DEFAULT NULL,
  `ascc_value` float(6,2) DEFAULT NULL,
  `ccs_type` varchar(4) DEFAULT NULL,
  `ccs_band` varchar(2) DEFAULT NULL,
  `ccs_value` float(6,2) DEFAULT NULL,
  `ccsp_type` varchar(4) DEFAULT NULL,
  `ccsp_band` varchar(2) DEFAULT NULL,
  `ccsp_value` float(6,2) DEFAULT NULL,
  `ccsr_type` varchar(4) DEFAULT NULL,
  `ccsr_value` float(6,2) DEFAULT NULL,
  `ccsrt_type` varchar(4) DEFAULT NULL,
  `ccsrt_value` float(6,2) DEFAULT NULL,
  `ccsu_type` varchar(4) DEFAULT NULL,
  `ccsu_value` float(6,2) DEFAULT NULL,
  `cecas_type` varchar(4) DEFAULT NULL,
  `cecas_value` float(6,2) DEFAULT NULL,
  `cecps_type` varchar(4) DEFAULT NULL,
  `cecps_value` float(6,2) DEFAULT NULL,
  `cets_type` varchar(4) DEFAULT NULL,
  `cets_value` float(6,2) DEFAULT NULL,
  `ecce_type` enum('yes','no') DEFAULT NULL,
  `ecce_value` float(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `tec_sheme_info`
  ADD PRIMARY KEY (`id`);
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id`);
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `invoice_extras`
  ADD PRIMARY KEY (`id`);
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `invoice_scheme`
  ADD PRIMARY KEY (`id`);
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `abc`
  ADD PRIMARY KEY (`abc_id`),
  MODIFY `abc_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `abc_plan_assoc`
  ADD PRIMARY KEY (`abc_plan_assoc_id`),
  MODIFY `abc_plan_assoc_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `abc_plan_block`
  ADD PRIMARY KEY (`block_id`),
  MODIFY `block_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `weekly_daily_blocks`
  ADD PRIMARY KEY (`weekly_daily_block_id`);

ALTER TABLE `weekly_plans`
  ADD PRIMARY KEY (`weekly_plan_id`);

ALTER TABLE `weekly_plan_assoc`
  ADD PRIMARY KEY (`weekly_plan_assoc_id`);


ALTER TABLE `weekly_daily_blocks`
  MODIFY `weekly_daily_block_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

ALTER TABLE `weekly_plans`
  MODIFY `weekly_plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `weekly_plan_assoc`
  MODIFY `weekly_plan_assoc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accidents`
--
ALTER TABLE `accidents`
  ADD PRIMARY KEY (`accident_id`),
  MODIFY `accident_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `accident_body_parts`
--
ALTER TABLE `accident_body_parts`
  ADD PRIMARY KEY (`part_id`),
  MODIFY `part_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `accident_logs`
--
ALTER TABLE `accident_logs`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `broadcasts`
--
ALTER TABLE `broadcasts`
  ADD PRIMARY KEY (`broadcast_id`),
  MODIFY `broadcast_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `broadcast_user`
--
ALTER TABLE `broadcast_user`
  ADD PRIMARY KEY (`broadcast_id`,`user_id`);

--
-- Indexes for table `checklists`
--
ALTER TABLE `checklists`
  ADD PRIMARY KEY (`checklist_id`),
  MODIFY `checklist_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `checklist_categories`
--
ALTER TABLE `checklist_categories`
  ADD PRIMARY KEY (`category_id`),
  MODIFY `category_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `checklist_child`
--
ALTER TABLE `checklist_child`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `checklist_milestones`
--
ALTER TABLE `checklist_milestones`
  ADD PRIMARY KEY (`milestone_id`),
  MODIFY `milestone_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `checklist_red_flags`
--
ALTER TABLE `checklist_red_flags`
  ADD PRIMARY KEY (`red_flag_id`),
  MODIFY `red_flag_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`child_id`),
  MODIFY `child_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `child_record`
--
ALTER TABLE `child_record`
  ADD PRIMARY KEY (`child_id`,`record_id`);

--
-- Indexes for table `child_user`
--
ALTER TABLE `child_user`
  ADD PRIMARY KEY (`child_id`,`user_id`),
  ADD UNIQUE KEY `token_id` (`token_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`country_id`);

--
-- Indexes for table `daily_plans`
--
ALTER TABLE `daily_plans`
  ADD PRIMARY KEY (`daily_plan_id`),
  MODIFY `daily_plan_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `daily_plan_assoc`
--
ALTER TABLE `daily_plan_assoc`
  ADD PRIMARY KEY (`daily_plan_assoc_id`),
  MODIFY `daily_plan_assoc_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `daily_plan_blocks`
--
ALTER TABLE `daily_plan_blocks`
  ADD PRIMARY KEY (`daily_plan_block_id`),
  MODIFY `daily_plan_block_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  MODIFY `enrollment_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `form`
--
ALTER TABLE `form`
  ADD PRIMARY KEY (`form_id`),
  MODIFY `form_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `form_blocks`
--
ALTER TABLE `form_blocks`
  ADD PRIMARY KEY (`form_blocks_id`),
  MODIFY `form_blocks_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `form_country`
--
ALTER TABLE `form_country`
  ADD PRIMARY KEY (`form_country_id`),
  MODIFY `form_country_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `frameworks`
--
ALTER TABLE `frameworks`
  ADD PRIMARY KEY (`framework_id`),
  MODIFY `framework_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `framework_categories`
--
ALTER TABLE `framework_categories`
  ADD PRIMARY KEY (`category_id`),
  MODIFY `category_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `framework_goals`
--
ALTER TABLE `framework_goals`
  ADD PRIMARY KEY (`goal_id`),
  MODIFY `goal_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `goal_story`
--
ALTER TABLE `goal_story`
  ADD PRIMARY KEY (`goal_id`,`story_id`);

--
-- Indexes for table `medias`
--
ALTER TABLE `medias`
  ADD PRIMARY KEY (`media_id`),
  MODIFY `media_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `media_record`
--
ALTER TABLE `media_record`
  ADD PRIMARY KEY (`media_id`,`record_id`);

--
-- Indexes for table `media_story`
--
ALTER TABLE `media_story`
  ADD PRIMARY KEY (`media_id`,`story_id`);

--
-- Indexes for table `monthly_plans`
--
ALTER TABLE `monthly_plans`
  ADD PRIMARY KEY (`monthly_plan_id`),
  MODIFY `monthly_plan_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`policy_id`),
  MODIFY `policy_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `policy_school`
--
ALTER TABLE `policy_school`
  ADD PRIMARY KEY (`policy_id`,`school_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  MODIFY `question_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `question_answers`
--
ALTER TABLE `question_answers`
  ADD PRIMARY KEY (`answer_id`),
  MODIFY `answer_id` int(11) AUTO_INCREMENT,
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `question_categories`
--
ALTER TABLE `question_categories`
  ADD PRIMARY KEY (`category_id`),
  MODIFY `category_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `question_school`
--
ALTER TABLE `question_school`
  ADD PRIMARY KEY (`question_id`,`answer_id`,`school_id`),
  ADD KEY `school_id` (`school_id`);

--
-- Indexes for table `records`
--
ALTER TABLE `records`
  ADD PRIMARY KEY (`record_id`),
  MODIFY `record_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `record_params`
--
ALTER TABLE `record_params`
  ADD PRIMARY KEY (`record_id`,`param_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`referral_id`),
  MODIFY `referral_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resource_id`),
  MODIFY `resource_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `resource_categories`
--
ALTER TABLE `resource_categories`
  ADD PRIMARY KEY (`category_id`),
  MODIFY `category_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  MODIFY `room_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`school_id`),
  MODIFY `school_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `school_categories`
--
ALTER TABLE `school_categories`
  ADD PRIMARY KEY (`category_id`),
  MODIFY `category_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `school_user`
--
ALTER TABLE `school_user`
  ADD PRIMARY KEY (`school_id`,`user_id`);

--
-- Indexes for table `stories`
--
ALTER TABLE `stories`
  ADD PRIMARY KEY (`story_id`),
  MODIFY `story_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `timelines`
--
ALTER TABLE `timelines`
  ADD PRIMARY KEY (`timeline_id`),
  MODIFY `timeline_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `timeline_comments`
--
ALTER TABLE `timeline_comments`
  ADD PRIMARY KEY (`comment_id`),
  MODIFY `comment_id` int(11) AUTO_INCREMENT;

--
-- Indexes for table `timesheets`
--
ALTER TABLE `timesheets`
  ADD PRIMARY KEY (`timesheet_date`,`child_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  MODIFY `user_id` int(11) AUTO_INCREMENT,
  ADD UNIQUE KEY `email` (`user_email`,`user_type`);

ALTER TABLE `text_story`
  ADD PRIMARY KEY (`text_id`,`story_id`);

ALTER TABLE `framework_texts`
  ADD PRIMARY KEY (`text_id`),
  MODIFY `text_id` int(11) AUTO_INCREMENT;

--
-- Indexes for risks tables
--
ALTER TABLE `risk_assessments`
  ADD PRIMARY KEY (`risk_assessment_id`),
  MODIFY `risk_assessment_id` int(11) AUTO_INCREMENT;

ALTER TABLE `risks`
  ADD PRIMARY KEY (`risk_id`),
  MODIFY `risk_id` int(11) AUTO_INCREMENT;

--
-- Indexes for safety tables
--

ALTER TABLE `safety_fire_duties`
  ADD PRIMARY KEY (`type`, `school_fk`);

ALTER TABLE `safety_general_register`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) AUTO_INCREMENT;

ALTER TABLE `safety_inventory`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) AUTO_INCREMENT;

ALTER TABLE `safety_log_book`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) AUTO_INCREMENT;

ALTER TABLE `safety_attachments`
  ADD PRIMARY KEY (`attachment_id`),
  MODIFY `attachment_id` int(11) AUTO_INCREMENT;

ALTER TABLE users ADD user_consent_consent datetime;

ALTER TABLE `timesheets`
  ADD `missing` ENUM('Absent','Sick','Holiday','') NULL DEFAULT NULL AFTER `timesheet_out`,
  ADD `missing_comment` TEXT NULL DEFAULT NULL AFTER `missing`,
  ADD `missing_evidence_url` VARCHAR(255) NULL DEFAULT NULL AFTER `missing_comment`;
  
  
  
CREATE TABLE `billing` (
  `id` int(11) NOT NULL,
  `idT` int(11) NOT NULL,
  `nameT` varchar(20) CHARACTER SET utf8 NOT NULL,
  `cityT` varchar(10) CHARACTER SET utf8 NOT NULL,
  `postalCodeT` varchar(20) CHARACTER SET utf8 NOT NULL,
  `phoneT` int(11) NOT NULL,
  `emailT` varchar(50) CHARACTER SET utf8 NOT NULL,
  `idC` int(11) NOT NULL,
  `nameC` varchar(20) CHARACTER SET utf8 NOT NULL,
  `streetC` varchar(20) CHARACTER SET utf8 NOT NULL,
  `cityC` varchar(10) CHARACTER SET utf8 NOT NULL,
  `postalCodeC` varchar(20) CHARACTER SET utf8 NOT NULL,
  `invoiceNumber` int(11) NOT NULL,
  `date` date NOT NULL,
  `vatRegNo` int(11) NOT NULL,
  `currency` enum('€','$','£','¥') CHARACTER SET utf8 NOT NULL,
  `fee` int(11) NOT NULL,
  `hours` int(11) NOT NULL,
  `ecce` enum('yes','no') NOT NULL,
  `tec` varchar(6) CHARACTER SET utf8 NOT NULL,
  `childCareScheme` varchar(10) CHARACTER SET utf8 NOT NULL,
  `extras` int(11) NOT NULL,
  `discount` int(11) NOT NULL,
  `discountType` enum('%','€') CHARACTER SET utf8 NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','paid') NOT NULL,
  `read` enum('no','yes') NOT NULL,
  `url_logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `billing`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `billing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

INSERT INTO `tec_sheme_info` (`id`, `scheme_name`, `scheme_type`, `scheme_type_descr`, `value_A`, `value_AJ`, `value_B`, `value_D`) VALUES
(1, 'CETS', 'FT', 'Full time', 29.00, 0.00, 0.00, 0.00),
(2, 'CETS', 'PT', 'Part time', 16.00, 0.00, 0.00, 0.00),
(3, 'CETS', 'AS', 'After School', 9.00, 0.00, 0.00, 0.00),
(4, 'CETS', 'ASWT', 'After School With Transport', 16.00, 0.00, 0.00, 0.00),
(6, 'ASCC', 'AS', 'After School', 9.00, 0.00, 0.00, 0.00),
(7, 'ASCC', 'ASWT', 'After School With Transport', 16.00, 0.00, 0.00, 0.00),
(9, 'CEC AS', 'AS', 'After School', 9.00, 0.00, 0.00, 0.00),
(10, 'CEC PS', 'PT', 'Part Time', 16.00, 0.00, 0.00, 0.00),
(11, 'ECCE', '1', '1 Day', 12.90, 0.00, 0.00, 0.00),
(12, 'ECCE', '2', '2 Days', 25.80, 0.00, 0.00, 0.00),
(13, 'ECCE', '3', '3 Days', 38.70, 0.00, 0.00, 0.00),
(14, 'ECCE', '4', '4 Days', 51.60, 0.00, 0.00, 0.00),
(15, 'ECCE', '5', '5 Days', 64.90, 0.00, 0.00, 0.00),
(16, 'CCSP', 'FD', 'Full Day', 29.00, 16.00, 15.00, 10.00),
(17, 'CCSP', 'PTA', 'Part Time a.m', 16.00, 16.00, 7.00, 5.00),
(18, 'CCSP', 'PTP', 'Part Time p.m', 16.00, 16.00, 7.00, 5.00),
(19, 'CCSP', 'SA', 'Sessional a.m', 9.00, 9.00, 5.00, 3.40),
(20, 'CCSP', 'SP', 'Sessional p.m', 9.00, 9.00, 5.00, 3.40),
(21, 'CCSP', 'HSA', 'Half Session a.m', 4.50, 4.50, 2.50, 1.70),
(22, 'CCSP', 'HSP', 'Half Session p.m', 4.50, 4.50, 2.50, 1.70),
(23, 'CCSP', 'UFD', 'Universal Full Day', 4.00, 0.00, 0.00, 0.00),
(24, 'CCSP', 'UPTA', 'Universal Part Time a.m', 2.00, 0.00, 0.00, 0.00),
(25, 'CCSP', 'UPTP', 'Universal Part Time p.m', 2.00, 0.00, 0.00, 0.00),
(26, 'CCSP', 'USA', 'Universal Sessional a.m', 1.40, 0.00, 0.00, 0.00),
(27, 'CCSP', 'USP', 'Universal Sessional p.m', 1.40, 0.00, 0.00, 0.00),
(28, 'CCSP', 'UHA', 'Universal Half Sessional a.m', 0.70, 0.00, 0.00, 0.00),
(29, 'CCSP', 'UHP', 'Universal Half Sessional p.m', 0.70, 0.00, 0.00, 0.00),
(30, 'CCS', 'FD', 'Full Day', 29.00, 16.00, 14.00, 10.00),
(31, 'CCS', 'PTA', 'Part Time a.m', 16.00, 16.00, 7.00, 5.00),
(32, 'CCS', 'PTP', 'Part Time p.m', 16.00, 16.00, 7.00, 5.00),
(33, 'CCS', 'SA', 'Sessional a.m', 9.00, 9.00, 5.00, 3.40),
(34, 'CCS', 'SP', 'Sessional p.m', 9.00, 9.00, 5.00, 3.40),
(35, 'CSS', 'HSA', 'Half Session a.m', 4.50, 4.50, 2.50, 1.70),
(36, 'CSS', 'HSP', 'Half Session p.m', 4.50, 4.50, 2.50, 1.70),
(37, 'CCSU', 'FD', 'Full Time', 4.00, 0.00, 0.00, 0.00),
(38, 'CCSU', 'PT', 'Part Time', 2.00, 0.00, 0.00, 0.00),
(39, 'CCSU', 'SE', 'Sessional', 1.40, 0.00, 0.00, 0.00),
(40, 'CCSU', 'HS', 'Half Sessional', 0.70, 0.00, 0.00, 0.00),
(41, 'CCSR', 'PT', 'Part Time', 29.00, 0.00, 0.00, 0.00),
(42, 'CCSR', 'SE', 'Sessional', 14.50, 0.00, 0.00, 0.00),
(43, 'CCSRT', 'PT', 'Part Tiime', 32.00, 0.00, 0.00, 0.00),
(44, 'CCSRT', 'SE', 'Sessional', 17.50, 0.00, 0.00, 0.00);


CREATE TABLE `learning_summary` (
  `learning_summary_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `learning_summary_public` tinyint(4) NOT NULL,
  `name_theme` text CHARACTER SET utf8,
  `picture_description` text CHARACTER SET utf8,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `week` tinyint(4) NOT NULL,
  `year` tinytext CHARACTER SET utf8 NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `in_session` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `goal_learning_summary` (
  `goal_id` int(11) UNSIGNED NOT NULL,
  `learning_summary_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `child_learning_summary` (
  `child_id` int(11) NOT NULL,
  `learning_summary_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `media_learning_summary` (
  `media_id` int(11) UNSIGNED NOT NULL,
  `learning_summary_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `text_learning_summary` (
  `text_id` int(11) UNSIGNED NOT NULL,
  `learning_summary_id` int(11) UNSIGNED NOT NULL,
  `contents` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `child_learning_summary`
  ADD PRIMARY KEY (`learning_summary_id`,`child_id`);

ALTER TABLE `goal_learning_summary`
  ADD PRIMARY KEY (`goal_id`,`learning_summary_id`);

ALTER TABLE `learning_summary`
  ADD PRIMARY KEY (`learning_summary_id`);

ALTER TABLE `media_learning_summary`
  ADD PRIMARY KEY (`media_id`,`learning_summary_id`);

ALTER TABLE `text_learning_summary`
  ADD PRIMARY KEY (`text_id`,`learning_summary_id`);

ALTER TABLE `learning_summary`
  MODIFY `learning_summary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;



CREATE TABLE IF NOT EXISTS `library_share` (
  `library_id` int(11) UNSIGNED NOT NULL,
  `room_id` int(11) UNSIGNED NOT NULL,
  primary key (library_id, room_id)
);

CREATE TABLE `libraries` (
  `library_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `library_name` varchar(64) DEFAULT NULL,
  `library_description` varchar(140) DEFAULT NULL,
  `library_downloads` int(11) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `libraries`
  ADD PRIMARY KEY (`library_id`),
  MODIFY `library_id` int(11) AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Table structure for table `gdpr_answers`
--

CREATE TABLE `gdpr_answers` (
  `answer_id` int(11) NOT NULL,
  `question_id` int(11) UNSIGNED NOT NULL,
  `answer_body` text,
  `answer_sort` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `gdpr_answers`
--

INSERT INTO `gdpr_answers` (`answer_id`, `question_id`, `answer_body`, `answer_sort`) VALUES
(1, 7, 'Yes', 1),
(2, 7, 'This has not been achieved yet', 2),
(3, 8, 'Yes', 1),
(4, 8, 'This has not been achieved yet', 2),
(5, 9, 'Yes', 1),
(6, 9, 'This has not been achieved yet', 2),
(7, 10, 'Yes', 1),
(8, 10, 'This has not been achieved yet', 2),
(9, 17, 'Yes', 1),
(10, 17, 'This has not been achieved yet', 2),
(11, 18, 'Yes', 1),
(12, 18, 'No', 2),
(13, 30, 'Yes', 1),
(14, 30, 'No', 2),
(15, 34, 'Yes', 1),
(16, 34, 'This has not been achieved yet', 2),
(17, 39, 'Yes', 1),
(18, 39, 'This has not been achieved yet', 2),
(19, 42, 'Yes', 1),
(20, 42, 'This has not been achieved yet', 2),
(21, 43, 'Yes', 1),
(22, 43, 'This has not been achieved yet', 2),
(23, 49, 'Yes', 1),
(24, 49, 'This has not been achieved yet', 2),
(25, 50, 'Yes', 1),
(26, 50, 'No', 2),
(27, 52, 'Yes', 1),
(28, 52, 'No', 2),
(29, 54, 'Yes', 1),
(30, 54, 'No', 2),
(31, 55, 'Yes', 1),
(32, 55, 'No', 2),
(33, 56, 'Yes', 1),
(34, 56, 'No', 2),
(35, 57, 'Yes', 1),
(36, 57, 'This has not been achieved yet', 2),
(37, 63, 'Yes', 1),
(38, 63, 'No', 2),
(39, 64, 'Yes', 1),
(40, 64, 'No', 2),
(41, 65, 'Yes', 1),
(42, 65, 'No', 2),
(43, 66, 'Yes', 1),
(44, 66, 'No', 2),
(45, 70, 'Yes, please explain', 1),
(46, 70, 'No', 2),
(47, 71, 'Yes', 1),
(48, 71, 'No', 2),
(59, 67, 'Yes', 1),
(60, 67, 'This has not been achieved yet', 2),
(61, 58, 'Yes', 1),
(62, 58, 'This has not been achieved yet', 2),
(63, 59, 'Yes', 1),
(64, 59, 'This has not been achieved yet', 2),
(65, 61, 'Yes', 1),
(66, 61, 'This has not been achieved yet', 2),
(67, 62, 'Yes', 1),
(68, 62, 'This has not been achieved yet', 2),
(69, 67, 'Yes', 1),
(70, 67, 'This has not been achieved yet', 2),
(71, 76, 'Yes', 1),
(72, 76, 'This has not been achieved yet', 2),
(73, 77, 'Yes, please explain how', 1),
(74, 77, 'This has not been achieved yet', 2);

-- --------------------------------------------------------

--
-- Table structure for table `gdpr_categories`
--

CREATE TABLE `gdpr_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `category_sort` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gdpr_categories`
--

INSERT INTO `gdpr_categories` (`category_id`, `category_name`, `category_sort`) VALUES
(1, 'Overview of personal data as held by the school', 1),
(2, 'Records of processing activities - students records', 2),
(3, 'Records of processing activities - employee & staff records', 3),
(4, 'Requests of access, correction, deletion of records', 4),
(5, 'Retention and disposal of records', 5),
(6, 'Transerfs to third parties', 6),
(7, 'School\'s computer system & security', 7),
(8, 'Internal awareness and governance', 8),
(9, 'Data security breach', 9);

-- --------------------------------------------------------

--
-- Table structure for table `gdpr_questions`
--

CREATE TABLE `gdpr_questions` (
  `question_id` int(11) NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `question_body` text,
  `question_description` text,
  `question_note` text,
  `question_multiple_choice` tinyint(1) DEFAULT '0',
  `answer_id` int(11) UNSIGNED DEFAULT NULL,
  `question_sort` int(11) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gdpr_questions`
--

INSERT INTO `gdpr_questions` (`question_id`, `category_id`, `question_body`, `question_description`, `question_note`, `question_multiple_choice`, `answer_id`, `question_sort`) VALUES
(1, 1, 'How do you obtain personal data? (List all ways) ', 'Example: Enrolment Form, CV/Application Form ', '', 0, 1, 1),
(2, 1, 'What types of personal data do you obtain?', 'Example: Date of Birth, Mother’s Maiden Name', '', 0, 2, 2),
(3, 1, 'Do you record the Personal Public Service Number – PPSN/Social Security Number?', 'Example: Payroll', '', 0, 3, 3),
(4, 1, 'Is verification documentation sought? If so, what happens to verification documentation? Is a copy made and retained for your records?', 'Example: Garda/Police Vetting, Teacher Registration', '', 0, 4, 4),
(5, 1, 'Who holds the responsibility for the control of the data?', 'Example:  Staff records held by ……. / Payroll details held by…………', '', 0, 5, 5),
(6, 1, 'Is there a system for classifying information (e.g. sensitive, confidential) with corresponding levels of access to such information? Please explain the system in place', '', '', 0, 6, 6),
(7, 1, 'Have all teachers and decision makers (e.g. owner of early learning centre etc) been made aware of the GDPR guidelines?', '', '', 1, 7, 7),
(8, 1, 'Has a lead person been appointed as the designated data controller?', '', '', 1, 8, 8),
(9, 1, 'Can all staff and teachers answer parent/guardians queries regarding how data is stored and managed?', '', '', 1, 9, 9),
(10, 1, 'Have all parents/guardians been communicated and updated regarding your legal obligations and their rights under GDPR?', '', '', 1, 10, 10),
(11, 2, 'How do you obtain Student’s personal data? ', '', '', 0, 11, 1),
(12, 2, 'What types of personal data do you obtain?', '', '', 0, 12, 2),
(13, 2, 'What specified purpose do you hold these student records for?', '', '', 0, 13, 3),
(14, 2, 'Do you process any sensitive personal data ?', '', '', 0, 14, 4),
(15, 2, 'Under what circumstances do you obtain sensitive data?', '', '', 0, 15, 5),
(16, 2, 'Who has access to sensitive data?', '', '', 0, 16, 6),
(17, 2, 'In the case of sensitive personal data have you sought explicit consent from the individual parent/guardian/student for the holding and/or disclosure of such data?', '', 'NOTE: If you are required under legislation for early childhood services/preschools in your country (which most are) for the health and welfare of children to gather emergency contact information and you were given these details from the parent/guardian of the child then the parent/guardian has given you consent. Just ensure that you only use it for the intended purpose which is to make contact with the emergency person(s) if there is an emergency.', 1, 17, 7),
(18, 2, 'Are all details received input onto a computer system?', '', '', 1, 18, 8),
(19, 2, 'What procedures are in place to ensure that a student’s data is being recorded accurately?', '', '', 0, 19, 9),
(20, 2, 'Is sensitive personal data transmitted internally/externally?', 'Example: to third parties', '', 0, 20, 10),
(21, 2, 'How long are personnel files (computer and manual) held, especially after the student has left the school?', '', '', 0, 21, 11),
(22, 2, 'Are records stored in a secure location?', '', '', 0, 22, 12),
(23, 3, 'How do you obtain Employee and Staffs’ personal data? ', '', '', 0, 23, 1),
(24, 3, 'What types of personal data do you obtain?', '', '', 0, 24, 2),
(25, 3, 'Do you explain to employees why you are collecting particular items of personal data and the purpose for which you are going to use it (when this is not obvious)?', '', '', 0, 25, 3),
(26, 3, 'Do you process any sensitive personal data ?', '', '', 0, 26, 4),
(27, 3, 'Under what circumstances do you obtain sensitive data?', '', '', 0, 27, 5),
(28, 3, 'Who has access to sensitive data?', '', '', 0, 28, 6),
(29, 3, 'In the case of sensitive personal data have you sought explicit consent from the relevant employee/staff for the holding and/or disclosure of such data?', '', '', 0, 29, 7),
(30, 3, 'Are all details received input onto a computer system?', '', '', 1, 30, 8),
(31, 3, 'What procedures are in place to ensure that a person’s data is being recorded accurately?', '', '', 0, 31, 9),
(32, 3, 'How long are personnel files (computer and manual) held, especially after the staff member has left employment?', '', '', 0, 32, 10),
(33, 3, 'What do you do with approved and rejected application forms?', '', '', 0, 33, 11),
(34, 3, 'Is any employee monitoring taking place re-usage of email and internet etc? If so, what information is provided to employees? Is there a policy in place setting out the terms of such monitoring and has it been brought to the attention of all staff?', '', '', 1, 34, 12),
(35, 3, 'Are records stored in a secure location?', '', '', 0, 35, 13),
(36, 4, 'How do you handle access to personal data requests received under GDPR? ', '', '', 0, 36, 1),
(37, 4, 'Are you clear as to what information you are obliged to release?', '', '', 0, 37, 2),
(38, 4, 'Are you clear as to what information you are, in certain circumstances, prohibited from releasing (e.g. health data, social work data)?', 'Example: health data, social work data', '', 0, 38, 3),
(39, 4, 'Do you ensure that all third party data is redacted before releasing the materials to a data subject? ', '', '', 1, 39, 4),
(40, 4, 'Where you withhold information from an access request (from appropriate guardians/parents), do you inform the data subjects of the reasons for this and advise them of their right to complain to the Office of the Data Protection Commissioner? ', '', '', 0, 40, 5),
(41, 4, 'Are employees/students/parents/guardians familiar with the process relating to making an access request? (for example, in your data protection policy you may state that to make an access request you may require a signed letter in writing and will provide access within 24 hours on business working days).', 'for example, in your data protection policy you may state that to make an access request you may require a signed letter in writing and will provide access within 24 hours on business working days', '', 0, 41, 6),
(42, 4, 'Are you in a position to respond to such a request within the statutory 21 or 40 day periods ? Does the response include reference to the right to complain to the Office of the Data Protection Commissioner?', '', 'NOTE:   21 days to provide a description of the personal data and the purposes for which they are kept (where the request is made under section 3 of the Data Protection Acts); 40 days to provide a copy of the data (i.e. a request made under section 4 of the Data Protection Acts)', 1, 42, 7),
(43, 4, 'Do you take steps to ensure your databases are kept up-to-date? ', '', '', 1, 43, 8),
(44, 4, 'Do you know how much of the personal data you hold is time-sensitive (i.e. likely to become inaccurate over time unless it is updated)? Please explain.', '', '', 0, 44, 9),
(45, 4, 'What procedures are in place to amend or delete inaccurate or unnecessary data within 40 days of being notified of same? ', '', '', 0, 45, 10),
(46, 5, 'Is there a clear policy on how long information on personal data is to be retained? You may attach the policy here.', '', '', 0, 46, 1),
(47, 5, 'Do you purge your databases of data which you no longer need in a regular, safe and secure manner?', 'Specify how often this is this done', '', 0, 47, 2),
(48, 5, 'Do you have a policy on the safe and secure disposal/deletion of personal data as soon as the purpose for which you obtained the data has been completed? ', '', '', 0, 48, 3),
(49, 5, 'Are the systems for the disposal of personal data secure?', '', '', 1, 49, 4),
(50, 6, 'Do you transfer/disclose/outsource personal data to third parties, either on your own initiative or on request? ', '', '', 1, 50, 1),
(51, 6, 'How are the students/parents/employees/staffs made aware that their personal data may be transferred/disclosed/outsourced to a third party?', '', '', 0, 51, 2),
(52, 6, 'Where the transfers are to Government Departments, are the transfers in compliance with the “Protection of the Confidentiality of Personal Data Guidance Notes” issued by the CMOD, Department of Finance, December 2008?', 'Click skip if not applicable', '', 1, 52, 3),
(53, 6, 'Are there procedural guidelines to deal with requests for personal data from third parties?', 'Attach contracts or guidelines here', '', 0, 53, 4),
(54, 6, 'Do you inform employees/students/parents/guardians of such disclosures?', '', '', 1, 54, 5),
(55, 6, 'Do you document all requests received and responses made?', 'Attach relevant documents here', '', 1, 55, 6),
(56, 6, 'Where you transfer personal data to third parties to be processed by them (e.g. CCTV system operators, HR/payroll companies, cloud computing, archiving services etc.), do you have a GDPR-compliant service-level agreement/data processing agreement in place?', 'Attach contracts here', '', 1, 56, 7),
(57, 6, 'Has the school’s Personal Data Security Breach Code of Practice been incorporated into any data processing agreement/service-level agreement reached with data processors? ', '', '', 1, 57, 8),
(58, 7, 'Does your school have computer usage guidelines (including internet and email usage) in place and are they up-to-date? Have the guidelines been brought to the attention of all staff members and are refresher training sessions given regularly? ', '', '', 1, 58, 1),
(59, 7, 'Are there access-level restrictions?', '', '', 1, 59, 2),
(60, 7, 'Who assigns access levels?', '', '', 0, 60, 3),
(61, 7, 'If documents containing personal data are sent externally by email, are they encrypted or password protected?', '', '', 1, 61, 4),
(62, 7, 'Is there a clear procedure governing the granting and removal of access for employees/students on enrolling and leaving the school? ', '', '', 1, 62, 5),
(63, 7, 'Does the school staff have remote access to IT systems containing personal data?', '', '', 1, 63, 6),
(64, 7, 'Is the school authority confident that such remote access is secure and governed by documented procedures? ', '', '', 1, 64, 7),
(65, 7, 'Are patterns of abnormal usage identifiable?', '', '', 1, 65, 8),
(66, 7, 'Is CCTV in operation?', '', '', 1, 66, 9),
(67, 7, 'Do you have a policy with regard to the operation of CCTV?', '', '', 1, 67, 10),
(68, 7, 'What is the retention period for CCTV footage?', '', '', 0, 68, 11),
(69, 7, 'Who has access to CCTV images?', '', '', 0, 69, 12),
(70, 7, 'Is CCTV used for reasons other than security?', '', '', 1, 70, 13),
(71, 7, 'If the CCTV is monitored/run by a third party (external security company), do you have a written data processing agreement in place with them?', 'Attach any relevant documents here', '', 1, 71, 14),
(72, 8, 'How is data protection compliance monitored throughout the school? ', '', '', 0, 72, 1),
(73, 8, 'Are internal audits of data protection compliance conducted regularly? ', '', '', 0, 73, 2),
(74, 8, 'How does your school create awareness and responsibility amongst your employee and staff regarding GDPR principles? ', '', '', 0, 74, 3),
(75, 8, 'Who is responsible for implementing the GDPR Data Protection Policy?', '', '', 0, 75, 4),
(76, 9, 'Is school management aware of its reporting obligations under the Data Protection Commissioner Code of Practice in relation to a data breach?', '', '', 1, 76, 1),
(77, 9, 'Would your school be in a position to respond rapidly and appropriately to such a breach?', '', '', 1, 77, 2);

-- --------------------------------------------------------

--
-- Table structure for table `gdpr_school`
--

CREATE TABLE `gdpr_school` (
  `question_id` int(11) UNSIGNED NOT NULL,
  `answer_id` int(11) UNSIGNED NOT NULL,
  `school_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `answer_body` text,
  `additional_information` text,
  `file_url` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS policy_user (
  policy_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  PRIMARY KEY (policy_id,user_id),
  KEY user_id (user_id)
);

CREATE TABLE `goal_weekly_plan` (
  `goal_fk` int(11) NOT NULL,
  `weekly_plan_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `goal_daily_plan` (
  `goal_fk` int(11) NOT NULL,
  `daily_plan_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `accidents` ADD `signed_by_user` INT(11) UNSIGNED NULL AFTER `accident_witnesses`, ADD `signature` TEXT NULL AFTER `signed_by_user`, ADD `signed_by` VARCHAR(255) NULL AFTER `signature`;

-- --------------------------------------------------------

--
-- Table structure for table `draft_daily_plans`
--

CREATE TABLE `draft_daily_plans` (
  `draft_daily_plan_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `school_fk` int(11) NOT NULL,
  `plan_public` tinyint(1) DEFAULT NULL,
  `date` date,
  `name` varchar(150),
  `assoc` enum('school','room','child') NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) NOT NULL,
  `plan_img_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `draft_daily_plan_assoc`
--

CREATE TABLE `draft_daily_plan_assoc` (
  `draft_daily_plan_assoc_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `draft_daily_plan_fk` int(11) NOT NULL,
  `assoc_type` enum('school','room','child','') NOT NULL,
  `assoc_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `draft_daily_plan_blocks`
--

CREATE TABLE `draft_daily_plan_blocks` (
  `daily_plan_block_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `draft_daily_plan_fk` int(11) NOT NULL,
  `time_block` varchar(15),
  `description` varchar(100),
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `goal_draft_daily_plan` (
  `goal_fk` int(11) NOT NULL,
  `draft_daily_plan_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `draft_weekly_plans`
--

CREATE TABLE `draft_weekly_plans` (
  `draft_weekly_plan_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `school_fk` int(11) NOT NULL,
  `plan_public` tinyint(1) DEFAULT NULL,
  `year` tinytext NOT NULL,
  `week` tinyint(4) NOT NULL,
  `assoc` enum('school','room','child') NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `draft_weekly_plan_assoc`
--

CREATE TABLE `draft_weekly_plan_assoc` (
  `draft_weekly_plan_assoc_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `draft_weekly_plan_fk` int(11) NOT NULL,
  `assoc_type` enum('school','room','child','') NOT NULL,
  `assoc_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `draft_weekly_daily_blocks`
--

CREATE TABLE `draft_weekly_daily_blocks` (
  `weekly_daily_block_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `draft_weekly_plan_fk` int(11) NOT NULL,
  `day` enum('mon','tue','wed','thu','fri','sat','sun') NOT NULL,
  `learning_opportunity` text,
  `time_when` tinytext,
  `rationale_interests` text,
  `family_involvement` text,
  `materials` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `goal_draft_weekly_plan` (
  `goal_fk` int(11) NOT NULL,
  `draft_weekly_plan_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `draft_monthly_plans`
--

CREATE TABLE `draft_monthly_plans` (
  `draft_monthly_plan_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `school_fk` int(11) NOT NULL,
  `plan_public` tinyint(1) DEFAULT NULL,
  `month` tinyint(4) NOT NULL,
  `year` char(4) NOT NULL,
  `assoc` enum('school','room','child') NOT NULL,
  `theme` text,
  `well_being` text,
  `identity_belonging` text,
  `communication` text,
  `exploring_thinking` text,
  `description` text,
  `expressive_arts_design` text,
  `literacy` text,
  `mathematics` text,
  `personal_social_emotional_development` text,
  `physical_development` text,
  `understanding_the_world` text,
  `connected_contribute` text,
  `confident_learners` text,
  `comment` text,
  `group_url` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `draft_weekly_plan_assoc`
--

CREATE TABLE `draft_weekly_plan_assoc` (
  `draft_weekly_plan_assoc_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `draft_weekly_plan_fk` int(11) NOT NULL,
  `assoc_type` enum('school','room','child','') NOT NULL,
  `assoc_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `draft_monthly_plan_assoc`
--

CREATE TABLE `draft_monthly_plan_assoc` (
  `draft_monthly_plan_assoc_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `draft_monthly_plan_fk` int(11) NOT NULL,
  `assoc_type` enum('school','room','child','') NOT NULL,
  `assoc_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `goal_draft_monthly_plan`
--

CREATE TABLE `goal_draft_monthly_plan` (
  `goal_fk` int(11) NOT NULL,
  `draft_monthly_plan_fk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `country_subdivisions`
--

CREATE TABLE `country_subdivisions` (
  `country_subdivision_id` varchar(10) NOT NULL,
  `country_subdivision_name` varchar(255) NOT NULL,
  `country_subdivision_available` tinyint(1) NOT NULL DEFAULT '0',
  `country_fk` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Contenu de la table `country_subdivisions`
--

INSERT INTO `country_subdivisions` (`country_subdivision_id`, `country_subdivision_name`, `country_subdivision_available`, `country_fk`) VALUES
('US-AK', 'Alaska', 0, 'US'),
('US-AL', 'Alabama', 0, 'US'),
('US-AR', 'Arkansas', 0, 'US'),
('US-AZ', 'Arizona', 0, 'US'),
('US-CA', 'California', 0, 'US'),
('US-CO', 'Colorado', 0, 'US'),
('US-CT', 'Connecticut', 0, 'US'),
('US-DC', 'District of Columbia', 0, 'US'),
('US-DE', 'Delaware', 0, 'US'),
('US-FL', 'Florida', 0, 'US'),
('US-GA', 'Georgia', 0, 'US'),
('US-HI', 'Hawaii', 0, 'US'),
('US-IA', 'Iowa', 0, 'US'),
('US-ID', 'Idaho', 0, 'US'),
('US-IL', 'Illinois', 0, 'US'),
('US-IN', 'Indiana', 0, 'US'),
('US-KS', 'Kansas', 0, 'US'),
('US-KY', 'Kentucky', 0, 'US'),
('US-LA', 'Louisiana', 0, 'US'),
('US-MA', 'Massachusetts', 0, 'US'),
('US-MD', 'Maryland', 0, 'US'),
('US-ME', 'Maine', 0, 'US'),
('US-MI', 'Michigan', 0, 'US'),
('US-MN', 'Minnesota', 0, 'US'),
('US-MO', 'Missouri', 0, 'US'),
('US-MS', 'Mississippi', 0, 'US'),
('US-MT', 'Montana', 0, 'US'),
('US-NC', 'North Carolina', 0, 'US'),
('US-ND', 'North Dakota', 0, 'US'),
('US-NE', 'Nebraska', 0, 'US'),
('US-NH', 'New Hampshire', 0, 'US'),
('US-NJ', 'New Jersey', 0, 'US'),
('US-NM', 'New Mexico', 0, 'US'),
('US-NV', 'Nevada', 0, 'US'),
('US-NY', 'New York', 0, 'US'),
('US-OH', 'Ohio', 0, 'US'),
('US-OK', 'Oklahoma', 0, 'US'),
('US-OR', 'Oregon', 0, 'US'),
('US-PA', 'Pennsylvania', 0, 'US'),
('US-RI', 'Rhode Island', 0, 'US'),
('US-SC', 'South Carolina', 0, 'US'),
('US-SD', 'South Dakota', 0, 'US'),
('US-TN', 'Tennessee', 0, 'US'),
('US-TX', 'Texas', 0, 'US'),
('US-UT', 'Utah', 0, 'US'),
('US-VA', 'Virginia', 0, 'US'),
('US-VT', 'Vermont', 0, 'US'),
('US-WA', 'Washington', 0, 'US'),
('US-WI', 'Wisconsin', 0, 'US'),
('US-WV', 'West Virginia', 0, 'US'),
('US-WY', 'Wyoming', 0, 'US');