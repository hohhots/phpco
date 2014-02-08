-- phpMyAdmin SQL Dump
-- version 2.9.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jan 03, 2007 at 12:41 PM
-- Server version: 5.0.27
-- PHP Version: 5.2.0
-- 
-- Database: `phpco`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `admin`
-- 

CREATE TABLE `admin` (
  `userid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `cookies`
-- 

CREATE TABLE `cookies` (
  `userid` int(10) unsigned NOT NULL default '0',
  `cookie` char(32) collate utf8_unicode_ci NOT NULL,
  `usertype` enum('G','U','A') collate utf8_unicode_ci NOT NULL default 'G',
  `start` int(15) unsigned NOT NULL default '0',
  `browser` char(150) collate utf8_unicode_ci NOT NULL default '',
  `ip` char(8) collate utf8_unicode_ci NOT NULL default '',
  `logcount` smallint(2) unsigned NOT NULL,
  `locktime` int(15) unsigned NOT NULL,
  `law` enum('y','n') collate utf8_unicode_ci NOT NULL default 'n',
  PRIMARY KEY  (`userid`,`cookie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `editou`
-- 

CREATE TABLE `editou` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL,
  `time` int(15) unsigned NOT NULL,
  `oldname` varchar(100) collate utf8_unicode_ci NOT NULL,
  `newname` varchar(100) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `loginfo`
-- 

CREATE TABLE `loginfo` (
  `order` int(10) NOT NULL auto_increment,
  `userid` int(10) unsigned default '0',
  `ip` varchar(15) collate utf8_unicode_ci NOT NULL default '',
  `time` int(15) unsigned NOT NULL,
  `sysinfo` varchar(150) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=375 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `ou_regist`
-- 

CREATE TABLE `ou_regist` (
  `orderid` int(5) unsigned NOT NULL,
  `cookie` char(32) collate utf8_unicode_ci NOT NULL,
  `ou` varchar(100) collate utf8_unicode_ci NOT NULL,
  `pou` varchar(100) collate utf8_unicode_ci NOT NULL,
  `telephonenumber1` varchar(20) collate utf8_unicode_ci NOT NULL,
  `telephonenumber2` varchar(20) collate utf8_unicode_ci default NULL,
  `telephonenumber3` varchar(20) collate utf8_unicode_ci default NULL,
  `telephonenumber4` varchar(20) collate utf8_unicode_ci default NULL,
  `facsimiletelephonenumber1` varchar(20) collate utf8_unicode_ci NOT NULL,
  `facsimiletelephonenumber2` varchar(20) collate utf8_unicode_ci default NULL,
  `postaladdress` varchar(100) collate utf8_unicode_ci NOT NULL,
  `postalcode` varchar(6) collate utf8_unicode_ci NOT NULL,
  `description` varchar(1000) collate utf8_unicode_ci default NULL,
  `regtime` int(15) unsigned NOT NULL,
  PRIMARY KEY  (`orderid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Table structure for table `user_regist`
-- 

CREATE TABLE `user_regist` (
  `orderid` int(5) unsigned NOT NULL,
  `cookie` char(32) collate utf8_unicode_ci NOT NULL,
  `cn` varchar(20) collate utf8_unicode_ci NOT NULL,
  `pou` varchar(100) collate utf8_unicode_ci NOT NULL,
  `userpassword` varchar(40) collate utf8_unicode_ci NOT NULL,
  `telephonenumber1` varchar(20) collate utf8_unicode_ci NOT NULL,
  `telephonenumber2` varchar(20) collate utf8_unicode_ci default NULL,
  `telephonenumber3` varchar(20) collate utf8_unicode_ci default NULL,
  `telephonenumber4` varchar(20) collate utf8_unicode_ci default NULL,
  `facsimiletelephonenumber1` varchar(20) collate utf8_unicode_ci default NULL,
  `facsimiletelephonenumber2` varchar(20) collate utf8_unicode_ci default NULL,
  `homephone` varchar(20) collate utf8_unicode_ci default NULL,
  `mail` varchar(40) collate utf8_unicode_ci default NULL,
  `mobile` varchar(11) collate utf8_unicode_ci default NULL,
  `roomnumber` varchar(10) collate utf8_unicode_ci default NULL,
  `postaladdress` varchar(100) collate utf8_unicode_ci default NULL,
  `postalcode` varchar(6) collate utf8_unicode_ci default NULL,
  `homepostaladdress` varchar(100) collate utf8_unicode_ci default NULL,
  `employeetype` varchar(20) collate utf8_unicode_ci default NULL,
  `description` varchar(1000) collate utf8_unicode_ci default NULL,
  `regtime` int(15) unsigned NOT NULL default '0',
  PRIMARY KEY  (`orderid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
