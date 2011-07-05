-- phpMyAdmin SQL Dump
-- version 3.3.10
-- http://www.phpmyadmin.net
--
-- Host: mysql2.topsecret.playareacode.com
-- Generation Time: Jul 05, 2011 at 11:50 AM
-- Server version: 5.1.53
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `macon_ac`
--

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

DROP TABLE IF EXISTS `bills`;
CREATE TABLE IF NOT EXISTS `bills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sequence_id` varchar(45) NOT NULL,
  `serial_number` varchar(45) NOT NULL,
  `is_test_data` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bill_redemption_session`
--

DROP TABLE IF EXISTS `bill_redemption_session`;
CREATE TABLE IF NOT EXISTS `bill_redemption_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bill_tracking`
--

DROP TABLE IF EXISTS `bill_tracking`;
CREATE TABLE IF NOT EXISTS `bill_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(45) NOT NULL,
  `session_id` int(11) NOT NULL,
  `check_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bill_tracking_record`
--

DROP TABLE IF EXISTS `bill_tracking_record`;
CREATE TABLE IF NOT EXISTS `bill_tracking_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bill_tracking_id` int(11) NOT NULL,
  `note` text,
  `status` tinyint(3) unsigned NOT NULL,
  `at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bonds`
--

DROP TABLE IF EXISTS `bonds`;
CREATE TABLE IF NOT EXISTS `bonds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sequence_id` varchar(45) NOT NULL,
  `serial_number` varchar(45) NOT NULL,
  `symbol_1` varchar(45) NOT NULL,
  `symbol_2` varchar(45) NOT NULL,
  `symbol_3` varchar(45) NOT NULL,
  `is_test_data` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='pertains to individual bonds';

-- --------------------------------------------------------

--
-- Table structure for table `bond_tracking`
--

DROP TABLE IF EXISTS `bond_tracking`;
CREATE TABLE IF NOT EXISTS `bond_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(45) NOT NULL,
  `tos` tinyint(4) NOT NULL DEFAULT '0',
  `handout_event_id` int(11) DEFAULT NULL,
  `handout_event_other` varchar(255) DEFAULT NULL,
  `handout_situation` varchar(45) DEFAULT NULL,
  `date_distributed` datetime DEFAULT NULL,
  `is_redeemed` tinyint(4) NOT NULL DEFAULT '0',
  `redeemed_event_id` int(11) DEFAULT NULL,
  `redeemed_event_other` varchar(255) DEFAULT NULL,
  `redeemed_situation` varchar(45) DEFAULT NULL,
  `date_redeemed` datetime DEFAULT NULL,
  `redeemer_first_name` varchar(45) DEFAULT NULL,
  `redeemer_last_name` varchar(45) DEFAULT NULL,
  `redeemer_address` varchar(45) DEFAULT NULL,
  `redeemer_zip` varchar(45) DEFAULT NULL,
  `first_name` varchar(45) DEFAULT NULL,
  `last_name` varchar(45) DEFAULT NULL,
  `zip_code` varchar(45) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `staff_id_handout` int(11) DEFAULT NULL,
  `staff_other_handout` varchar(255) DEFAULT NULL,
  `staff_id_redeemed` int(11) DEFAULT NULL,
  `staff_other_redeemed` varchar(255) DEFAULT NULL,
  `note` text,
  `is_test_data` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `business`
--

DROP TABLE IF EXISTS `business`;
CREATE TABLE IF NOT EXISTS `business` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `zip` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `phone` varchar(45) NOT NULL,
  `tin` varchar(45) NOT NULL,
  `contract_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `checks`
--

DROP TABLE IF EXISTS `checks`;
CREATE TABLE IF NOT EXISTS `checks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(255) NOT NULL,
  `at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
CREATE TABLE IF NOT EXISTS `employee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_id` int(11) NOT NULL,
  `first_name` varchar(45) DEFAULT NULL,
  `last_name` varchar(45) NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `bus_id` (`business_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `handout_situation`
--

DROP TABLE IF EXISTS `handout_situation`;
CREATE TABLE IF NOT EXISTS `handout_situation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `situation` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `prizes`
--

DROP TABLE IF EXISTS `prizes`;
CREATE TABLE IF NOT EXISTS `prizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amount_per_person` int(11) NOT NULL,
  `serial_number` varchar(45) NOT NULL,
  `bond_1_serial_number` varchar(45) DEFAULT NULL,
  `bond_2_serial_number` varchar(45) DEFAULT NULL,
  `date_redeemed` datetime DEFAULT NULL,
  `is_test_data` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number_UNIQUE` (`serial_number`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `redeemed_situation`
--

DROP TABLE IF EXISTS `redeemed_situation`;
CREATE TABLE IF NOT EXISTS `redeemed_situation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `situation` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `dismissed_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Stand-in structure for view `track_view`
--
DROP VIEW IF EXISTS `track_view`;
CREATE TABLE IF NOT EXISTS `track_view` (
`sequence_id` varchar(45)
,`serial_number` varchar(45)
,`symbol_1` varchar(45)
,`symbol_2` varchar(45)
,`symbol_3` varchar(45)
,`bond_test` tinyint(4)
,`tracking_id` int(11)
,`is_redeemed` tinyint(4)
,`tos` tinyint(4)
,`handout_event_id` int(11)
,`handout_event_other` varchar(255)
,`redeemer_first_name` varchar(45)
,`redeemer_last_name` varchar(45)
,`redeemer_address` varchar(45)
,`redeemer_zip` varchar(45)
,`handout_situation` varchar(45)
,`date_distributed` datetime
,`redeemed_event_id` int(11)
,`redeemed_event_other` varchar(255)
,`redeemed_situation` varchar(45)
,`date_redeemed` datetime
,`first_name` varchar(45)
,`last_name` varchar(45)
,`zip_code` varchar(45)
,`address` varchar(255)
,`staff_id_handout` int(11)
,`staff_other_handout` varchar(255)
,`staff_id_redeemed` int(11)
,`staff_other_redeemed` varchar(255)
,`note` text
,`tracking_test` tinyint(4)
,`prize_serial_number` varchar(90)
,`prize_amount` varchar(90)
);
-- --------------------------------------------------------

--
-- Table structure for table `zipcode`
--

DROP TABLE IF EXISTS `zipcode`;
CREATE TABLE IF NOT EXISTS `zipcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zip` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for view `track_view`
--
DROP TABLE IF EXISTS `track_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`macon_db_admin`@`69.163.128.0/255.255.128.0` SQL SECURITY DEFINER VIEW `track_view` AS select `b`.`sequence_id` AS `sequence_id`,`b`.`serial_number` AS `serial_number`,`b`.`symbol_1` AS `symbol_1`,`b`.`symbol_2` AS `symbol_2`,`b`.`symbol_3` AS `symbol_3`,`b`.`is_test_data` AS `bond_test`,`bt`.`id` AS `tracking_id`,`bt`.`is_redeemed` AS `is_redeemed`,`bt`.`tos` AS `tos`,`bt`.`handout_event_id` AS `handout_event_id`,`bt`.`handout_event_other` AS `handout_event_other`,`bt`.`redeemer_first_name` AS `redeemer_first_name`,`bt`.`redeemer_last_name` AS `redeemer_last_name`,`bt`.`redeemer_address` AS `redeemer_address`,`bt`.`redeemer_zip` AS `redeemer_zip`,`bt`.`handout_situation` AS `handout_situation`,`bt`.`date_distributed` AS `date_distributed`,`bt`.`redeemed_event_id` AS `redeemed_event_id`,`bt`.`redeemed_event_other` AS `redeemed_event_other`,`bt`.`redeemed_situation` AS `redeemed_situation`,`bt`.`date_redeemed` AS `date_redeemed`,`bt`.`first_name` AS `first_name`,`bt`.`last_name` AS `last_name`,`bt`.`zip_code` AS `zip_code`,`bt`.`address` AS `address`,`bt`.`staff_id_handout` AS `staff_id_handout`,`bt`.`staff_other_handout` AS `staff_other_handout`,`bt`.`staff_id_redeemed` AS `staff_id_redeemed`,`bt`.`staff_other_redeemed` AS `staff_other_redeemed`,`bt`.`note` AS `note`,`bt`.`is_test_data` AS `tracking_test`,concat(ifnull(`p1`.`serial_number`,''),ifnull(`p2`.`serial_number`,'')) AS `prize_serial_number`,concat(ifnull(cast(`p1`.`amount_per_person` as char(45) charset utf8),''),ifnull(cast(`p2`.`amount_per_person` as char(45) charset utf8),'')) AS `prize_amount` from (((`bonds` `b` left join `bond_tracking` `bt` on((`bt`.`serial_number` = `b`.`serial_number`))) left join `prizes` `p1` on((`p1`.`bond_1_serial_number` = `b`.`serial_number`))) left join `prizes` `p2` on((`p2`.`bond_2_serial_number` = `b`.`serial_number`)));

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `bus_id` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
