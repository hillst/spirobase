-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 12, 2013 at 04:09 PM
-- Server version: 5.5.32
-- PHP Version: 5.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `spirodela`
--
CREATE DATABASE IF NOT EXISTS `spirodela` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `spirodela`;

-- --------------------------------------------------------

--
-- Table structure for table `cms_page`
--

CREATE TABLE IF NOT EXISTS `cms_page` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `role_allowed` varchar(20) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `cms_page`
--

INSERT INTO `cms_page` (`pid`, `name`, `role_allowed`) VALUES
(3, 'Home', 'ROLE_ADMIN');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=88 ;

--
-- Dumping data for table `cms_page_things`
--

INSERT INTO `cms_page_things` (`thingid`, `pageid`, `content`, `thingname`, `groupnum`) VALUES
(79, 3, 'Spirodela Polyrhiza  ', 'Main-Header', 70),
(80, 3, 'In July 2008 the U.S. Department of Energy (DOE) Joint Genome Institute announced that the Community Sequencing Program would fund sequencing of the genome of the giant duckweed, Spirodela polyrhiza. This was a priority project for DOE in 2009. The research is intended to facilitate new biomass and bio-energy programs.\nDuckweed is being studied by researchers around the world as a possible source of clean energy. In the United States, in addition to being the subject of study by the DOE, both Rutgers University and North Carolina State University have ongoing projects to determine if duckweed might be a source of cost-effective, clean, renewable energy. Duckweed is a good candidate as a biofuel because as a biomass it grows rapidly, has 5 to 6 times as much starch as corn, and does not contribute to global warming. Duckweed is considered a carbon neutral energy source, because unlike most fuels, it removes carbon dioxide from the atmosphere.\nDuckweed also functions as a bioremediator by effectively filtering contaminants such as bacteria, nitrogen, phosphates, and other nutrients from naturally occurring bodies of water, constructed wetlands and waste water.', 'Main-Block', 70),
(85, 3, '<a href="#">Turion formations in Spirodela Polyrhiza</a></br><a href="#">probably should be real links</a>', 'Resources-Content', 3),
(87, 3, '314-587-1417<br/>\ndbryant@danforthcenter.org', 'Contact-Content', 4);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `role` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ROLE_USER',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9F85E0677` (`username`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=32 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `salt`, `password`, `email`, `is_active`, `role`) VALUES
(30, 'admin', 'bff5680c6fe1fde18704fd4168e5cfd525c7de7e', 'GC3aU+0KZFyn9ZTKNNefvDisoYo=', '', 1, 'ROLE_ADMIN'),
(31, 'user', '3c58be4edc7777caa6582394ca866d9e5878f46c', 'rK6IxNghj/U9nMbRR9gTQq0s7CU=', '', 1, 'ROLE_USER');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cms_page_things`
--
ALTER TABLE `cms_page_things`
  ADD CONSTRAINT `cms_page_things_ibfk_1` FOREIGN KEY (`pageid`) REFERENCES `cms_page` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
