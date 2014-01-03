-- MySQL dump 10.11
--
-- Host: localhost    Database: sqlgrey
-- ------------------------------------------------------
-- Server version	5.0.51a
--
-- Table structure for table `config`


ALTER TABLE `tq_user`
ADD `session` varchar(32) NOT NULL,
ADD `ip` varchar(32) NOT NULL,
ADD `cookie` varchar(32) NOT NULL