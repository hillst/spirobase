-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 08, 2013 at 03:43 PM
-- Server version: 5.5.32
-- PHP Version: 5.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `hillcms`
--
CREATE DATABASE IF NOT EXISTS `hillcms` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `hillcms`;

-- --------------------------------------------------------

--
-- Table structure for table `cms_page`
--

CREATE TABLE IF NOT EXISTS `cms_page` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `role_allowed` varchar(20) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `cms_page`
--

INSERT INTO `cms_page` (`pid`, `name`, `role_allowed`) VALUES
(1, 'Home', 'ROLE_ADMIN'),
(2, 'People', 'ROLE_USER');

-- --------------------------------------------------------

--
-- Table structure for table `cms_page_things`
--

CREATE TABLE IF NOT EXISTS `cms_page_things` (
  `thingid` int(11) NOT NULL AUTO_INCREMENT,
  `pageid` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `thingname` varchar(25) NOT NULL,
  `groupnum` int(11) NOT NULL,
  PRIMARY KEY (`thingid`),
  KEY `pageid` (`pageid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=79 ;

--
-- Dumping data for table `cms_page_things`
--

INSERT INTO `cms_page_things` (`thingid`, `pageid`, `content`, `thingname`, `groupnum`) VALUES
(1, 1, 'this is a good place to describe your webapp', 'Main-Block', 0),
(55, 2, 'Steven Hill', 'Bio-Name', 17),
(56, 2, 'includes/images/Steve.jpg ', 'Bio-Picture', 17),
(57, 2, 'This is where I would typically describe myself :)', 'Bio-Text', 17),
(58, 2, 'Member', 'Bio-Title', 17),
(75, 1, 'includes/images/phenotyper1.jpg', 'Slide-Image', 1),
(76, 1, 'Content for image caption', 'Slide-Caption', 1),
(77, 1, 'Header for page', 'Main-Header', 0),
(78, 1, 'subheading', 'Main-Subheader', 0);

-- --------------------------------------------------------

--
-- Table structure for table `hillcms_roles`
--

CREATE TABLE IF NOT EXISTS `hillcms_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_12E586C57698A6A` (`role`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `hillcms_roles`
--

INSERT INTO `hillcms_roles` (`id`, `name`, `role`) VALUES
(1, 'admin', 'ROLE_ADMIN'),
(2, 'general', 'ROLE_USER');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9F85E0677` (`username`),
  UNIQUE KEY `UNIQ_1483A5E9E7927C74` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `salt`, `password`, `email`, `is_active`) VALUES
(1, 'admin', '', 'W6ph5Mm5Pz8GgiULbPgzG37mj9g=', 'changeme@gmail.com', 1),
(2, 'user', '', 'W6ph5Mm5Pz8GgiULbPgzG37mj9g=', 'changeme@gmail.cm', 1),
(5, 'test', 'bdfa95f8', 'bdfa95f8979a37f3807c398fb57dd64eedca5242', 'a@aol.com', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE IF NOT EXISTS `user_role` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_2DE8C6A3A76ED395` (`user_id`),
  KEY `IDX_2DE8C6A3D60322AC` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
(1, 1),
(2, 2);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cms_page_things`
--
ALTER TABLE `cms_page_things`
  ADD CONSTRAINT `FK_9DD310778BF4141` FOREIGN KEY (`pageid`) REFERENCES `cms_page` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `FK_2DE8C6A3A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_2DE8C6A3D60322AC` FOREIGN KEY (`role_id`) REFERENCES `hillcms_roles` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
