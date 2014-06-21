-- Use this script to extend the sglgrey database!
-- This script adds 2 new tables, required for Greyface.
--
-- Database: sqlgrey
-- ------------------------------------------------------


--
-- Table structure for table `tq_user`
--
DROP TABLE IF EXISTS `tq_user`;
CREATE TABLE IF NOT EXISTS `tq_user` (
  `user_id` int(11) NOT NULL auto_increment,
  `username` varchar(50) NOT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(50) NOT NULL,
  `is_admin` int(11) NOT NULL,
  `session` varchar(32) NOT NULL,
  `cookie` varchar(32) NOT NULL,
  `ip` varchar(32) NOT NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=`InnoDB`;



--
-- Table structure for table tq_alias`
--
DROP TABLE IF EXISTS `tq_alias`;
CREATE TABLE IF NOT EXISTS `tq_alias` (
  `alias_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `alias_name` varchar(50) NOT NULL,
  PRIMARY KEY  (`alias_id`),
  UNIQUE KEY `alias_name_unique` (`alias_name`),
  KEY `user_id` (`user_id`),
  KEY `alias_name` (`alias_name`),
  CONSTRAINT `constraint_tq_alias___tq_user`
  FOREIGN KEY (`user_id`)
  REFERENCES `tq_user` (`user_id`)
  ON DELETE CASCADE
) ENGINE=`InnoDB`;






