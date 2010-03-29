-- phpMyAdmin SQL Dump
-- version 2.7.0-pl1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 10, 2006 at 08:08 AM
-- Server version: 5.0.18
-- PHP Version: 4.4.1-pl3-gentoo
--
-- Database: `datastoreTest`
--

-- --------------------------------------------------------

--
-- Table structure for table `auditTrail`
--

DROP TABLE IF EXISTS `auditTrail`;
CREATE TABLE `auditTrail` (
  `auditId` bigint(20) unsigned NOT NULL auto_increment,
  `eventId` bigint(20) unsigned NOT NULL default '0',
  `pageGroupId` tinyint(4) unsigned NOT NULL default '0',
  `userId` bigint(20) unsigned NOT NULL default '0',
  `pageId` bigint(20) unsigned NOT NULL default '0',
  `eventDesc` text NOT NULL,
  `eventTimestamp` bigint(20) unsigned NOT NULL default '0',
  `eventStatus` tinyint(4) unsigned NOT NULL default '0',
  `recordTableId` tinyint(4) unsigned NOT NULL default '0',
  `recordId` bigint(20) unsigned NOT NULL default '0',
  `tracerId` varchar(255) NOT NULL default '',
  `ipAddress` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`auditId`),
  KEY `recordTableId` (`recordTableId`,`recordId`),
  KEY `tracerId` (`tracerId`),
  KEY `userId` (`userId`),
  KEY `ipAddress` (`ipAddress`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `auditTrail`
--


-- --------------------------------------------------------

--
-- Table structure for table `auditTrailIntoHistory`
--

DROP TABLE IF EXISTS `auditTrailIntoHistory`;
CREATE TABLE `auditTrailIntoHistory` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `tableid` tinyint(4) unsigned NOT NULL default '0',
  `tableuid` bigint(20) unsigned NOT NULL default '0',
  `auditId` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `auditId` (`auditId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `auditTrailIntoHistory`
--


-- --------------------------------------------------------

--
-- Table structure for table `auditTrailRelations`
--

DROP TABLE IF EXISTS `auditTrailRelations`;
CREATE TABLE `auditTrailRelations` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `fromTableId` tinyint(4) unsigned NOT NULL default '0',
  `fromRecordId` bigint(20) unsigned NOT NULL default '0',
  `toTableId` tinyint(3) unsigned NOT NULL default '0',
  `toRecordId` bigint(20) unsigned NOT NULL default '0',
  `auditId` bigint(20) unsigned NOT NULL default '0',
  `relationship` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`uid`),
  KEY `auditId` (`auditId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `auditTrailRelations`
--


-- --------------------------------------------------------

--
-- Table structure for table `customerHistory`
--

DROP TABLE IF EXISTS `customerHistory`;
CREATE TABLE `customerHistory` (
  `historyUid` bigint(20) unsigned NOT NULL auto_increment,
  `customerId` bigint(20) unsigned NOT NULL default '0',
  `customerFirstName` varchar(255) NOT NULL default '',
  `customerSurname` varchar(255) NOT NULL default '',
  `customerAddress1` varchar(255) NOT NULL default '',
  `customerAddress2` varchar(255) default NULL,
  `customerCity` varchar(255) NOT NULL default '',
  `customerCounty` varchar(255) default NULL,
  `customerCountry` varchar(255) NOT NULL default 'UK',
  `customerPostcode` varchar(255) NOT NULL default '',
  `customerEmailAddress` varchar(255) NOT NULL default '',
  `customerPassword` varchar(255) default NULL,
  `dateCreated` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`historyUid`),
  KEY `emailIndex` (`customerId`,`customerEmailAddress`),
  KEY `customerId` (`customerId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `customerHistory`
--


-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customerId` bigint(20) unsigned NOT NULL auto_increment,
  `customerFirstName` varchar(255) NOT NULL default '',
  `customerSurname` varchar(255) NOT NULL default '',
  `customerAddress1` varchar(255) NOT NULL default '',
  `customerAddress2` varchar(255) default NULL,
  `customerCity` varchar(255) NOT NULL default '',
  `customerCounty` varchar(255) default NULL,
  `customerCountry` varchar(255) NOT NULL default 'UK',
  `customerPostcode` varchar(255) NOT NULL default '',
  `customerEmailAddress` varchar(255) NOT NULL default '',
  `customerPassword` varchar(255) default NULL,
  `dateCreated` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`customerId`),
  KEY `emailIndex` (`customerId`,`customerEmailAddress`),
  KEY `dateCreated` (`dateCreated`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customerId`, `customerFirstName`, `customerSurname`, `customerAddress1`, `customerAddress2`, `customerCity`, `customerCounty`, `customerCountry`, `customerPostcode`, `customerEmailAddress`, `customerPassword`, `dateCreated`) VALUES (1, 'Stuart', 'Herbert', '123 Example Road', NULL, 'Example City', 'Example County', 'UK', 'CF10 2GE', 'stuart@example.com', NULL, 0);
INSERT INTO `customers` (`customerId`, `customerFirstName`, `customerSurname`, `customerAddress1`, `customerAddress2`, `customerCity`, `customerCounty`, `customerCountry`, `customerPostcode`, `customerEmailAddress`, `customerPassword`, `dateCreated`) VALUES (2, 'ExampleFirstName2', 'ExampleSurname2', '234 Example Road', 'Example Address 2', 'Example City 2', 'Example County 2', 'UK', 'Example Postcode 2', 'example2@example.com', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
CREATE TABLE `notes` (
  `noteuid` bigint(20) unsigned NOT NULL auto_increment,
  `recordTableId` tinyint(4) unsigned NOT NULL default '0',
  `recordId` bigint(20) unsigned NOT NULL default '0',
  `notetext` text NOT NULL,
  `notetimestamp` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`noteuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `notes`
--


-- --------------------------------------------------------

--
-- Table structure for table `orderHistory`
--

DROP TABLE IF EXISTS `orderHistory`;
CREATE TABLE `orderHistory` (
  `historyUid` bigint(20) unsigned NOT NULL auto_increment,
  `customerId` bigint(20) unsigned NOT NULL default '0',
  `orderId` bigint(20) unsigned NOT NULL default '0',
  `orderStatus` tinyint(4) unsigned NOT NULL default '1',
  `orderTotal` float NOT NULL default '0',
  `orderPostage` float NOT NULL default '0',
  `orderStatusChange` bigint(20) unsigned NOT NULL default '0',
  `dateCreated` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`historyUid`),
  KEY `orderId` (`orderId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `orderHistory`
--


-- --------------------------------------------------------

--
-- Table structure for table `orderContents`
--

DROP TABLE IF EXISTS `orderContents`;
CREATE TABLE `orderContents` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `masterOrderId` bigint(20) unsigned NOT NULL default '0',
  `pid` bigint(20) unsigned NOT NULL default '0',
  `quantity` tinyint(4) unsigned NOT NULL default '0',
  `cost` float NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `masterOrderId` (`masterOrderId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `ordercontents`
--

INSERT INTO `orderContents` (`uid`, `masterOrderId`, `pid`, `quantity`, `cost`) VALUES (1, 1, 1, 5, 8.99);
INSERT INTO `orderContents` (`uid`, `masterOrderId`, `pid`, `quantity`, `cost`) VALUES (2, 1, 4, 20, 50.99);

-- --------------------------------------------------------

--
-- Table structure for table `ordercontentsHistory`
--

DROP TABLE IF EXISTS `ordercontentsHistory`;
CREATE TABLE `ordercontentsHistory` (
  `historyUid` bigint(20) unsigned NOT NULL auto_increment,
  `uid` bigint(20) unsigned NOT NULL default '0',
  `masterOrderId` bigint(20) unsigned NOT NULL default '0',
  `pid` bigint(20) unsigned NOT NULL default '0',
  `quantity` tinyint(4) unsigned NOT NULL default '0',
  `cost` float NOT NULL default '0',
  PRIMARY KEY  (`historyUid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `ordercontentsHistory`
--


-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `masterCustomerId` bigint(20) unsigned NOT NULL default '0',
  `giftCustomerId` bigint(20) unsigned NOT NULL default '0',
  `orderId` bigint(20) unsigned NOT NULL auto_increment,
  `orderStatus` tinyint(4) unsigned NOT NULL default '1',
  `orderTotal` float NOT NULL default '0',
  `orderPostage` float NOT NULL default '0',
  `orderStatusChange` bigint(20) unsigned NOT NULL default '0',
  `dateCreated` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`orderId`),
  KEY `orderStatus` (`orderStatus`),
  KEY `dateCreated` (`dateCreated`),
  KEY `masterCustomerId` (`masterCustomerId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`masterCustomerId`, `giftCustomerId`, `orderId`, `orderStatus`, `orderTotal`, `orderPostage`, `orderStatusChange`, `dateCreated`) VALUES (1, 2, 1, 1, 8.59, 0, 2006, 2006);
INSERT INTO `orders` (`masterCustomerId`, `giftCustomerId`, `orderId`, `orderStatus`, `orderTotal`, `orderPostage`, `orderStatusChange`, `dateCreated`) VALUES (1, 2, 2, 2, 99.99, 5.99, 1970, 2006);

-- --------------------------------------------------------

--
-- Table structure for table `pluginConstants`
--

DROP TABLE IF EXISTS `pluginConstants`;
CREATE TABLE `pluginConstants` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `package` varchar(255) NOT NULL default '',
  `constType` varchar(255) NOT NULL default '',
  `constName` varchar(255) NOT NULL default '',
  `constDesc` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `pluginConstants`
--


-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `pid` bigint(20) unsigned NOT NULL auto_increment,
  `productName` varchar(255) NOT NULL default '',
  `productSummary` varchar(255) NOT NULL default '',
  `productUrl` varchar(255) NOT NULL default '',
  `productCode` varchar(255) NOT NULL default '',
  `productCost` float NOT NULL default '0',
  `isActive` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`pid`),
  UNIQUE KEY `productCode` (`productCode`),
  KEY `isActive` (`isActive`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`pid`, `productName`, `productSummary`, `productUrl`, `productCode`, `productCost`, `isActive`) VALUES (1, 'Gentoo LAMP Server', 'A Linux/Apache/MySQL/PHP Stack for server environments', 'http://lamp.gentoo.org/server/', 'AA001', 15.99, 1);
INSERT INTO `products` (`pid`, `productName`, `productSummary`, `productUrl`, `productCode`, `productCost`, `isActive`) VALUES (2, 'Gentoo LAMP Developer Desktop', 'A developer''s workstation w/ the LAMP stack', 'http://lamp.gentoo.org/client/', 'AA002', 9.99, 1);
INSERT INTO `products` (`pid`, `productName`, `productSummary`, `productUrl`, `productCode`, `productCost`, `isActive`) VALUES (3, 'Gentoo Overlays', 'Per-team package trees for Gentoo', 'http://overlays.gentoo.org/', 'AA003', 5.99, 1);
INSERT INTO `products` (`pid`, `productName`, `productSummary`, `productUrl`, `productCode`, `productCost`, `isActive`) VALUES (4, 'Gentoo/ALT', 'Gentoo package management on non-Linux kernels', 'http://alt.gentoo.org/', 'AA004', 3.99, 1);

-- --------------------------------------------------------

--
-- Table structure for table `productsHistory`
--

DROP TABLE IF EXISTS `productsHistory`;
CREATE TABLE `productsHistory` (
  `historyId` bigint(20) unsigned NOT NULL auto_increment,
  `pid` bigint(20) unsigned NOT NULL default '0',
  `productName` varchar(255) NOT NULL default '',
  `productSummary` varchar(255) NOT NULL default '',
  `productUrl` varchar(255) NOT NULL default '',
  `productCode` varchar(255) NOT NULL default '',
  `productCost` float NOT NULL default '0',
  `isActive` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`historyId`),
  UNIQUE KEY `productCode` (`productCode`),
  KEY `isActive` (`isActive`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `productsHistory`
--

-- --------------------------------------------------------

--
-- Table structure for table `productTags`
--

DROP TABLE IF EXISTS `productTags`;
CREATE TABLE `productTags` (
  `productId` bigint(2) unsigned NOT NULL,
 `tagName` varchar(255) NOT NULL,
UNIQUE KEY `unique_key` (`productId`, `tagName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `productTags`
--

INSERT INTO `productTags` (productId, tagName) VALUES (1, "apache");
INSERT INTO `productTags` (productId, tagName) VALUES (1, "linux");
INSERT INTO `productTags` (productId, tagName) VALUES (1, "php");

-- --------------------------------------------------------

--
-- Table structure for table `relatedProducts`
--

DROP TABLE IF EXISTS `relatedProducts`;
CREATE TABLE `relatedProducts` (
  `uid` bigint(20) unsigned NOT NULL default '0',
  `productId1` bigint(20) unsigned NOT NULL default '0',
  `productId2` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `productId1` (`productId1`),
  KEY `productId2` (`productId2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `relatedProducts`
--

INSERT INTO `relatedProducts` (`uid`, `productId1`, `productId2` ) VALUES (1, 1, 2);
INSERT INTO `relatedProducts` (`uid`, `productId1`, `productId2` ) VALUES (2, 1, 3);
INSERT INTO `relatedProducts` (`uid`, `productId1`, `productId2` ) VALUES (3, 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `relations`
--

DROP TABLE IF EXISTS `relations`;
CREATE TABLE `relations` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `fromTableId` tinyint(3) unsigned NOT NULL default '0',
  `fromRecordId` bigint(20) unsigned NOT NULL default '0',
  `toTableId` tinyint(20) unsigned NOT NULL default '0',
  `toRecordId` bigint(20) unsigned NOT NULL default '0',
  `relationship` tinyint(4) NOT NULL default '0',
  `fromTimestamp` bigint(20) unsigned NOT NULL default '0',
  `toTimestamp` bigint(20) unsigned default NULL,
  PRIMARY KEY  (`uid`),
  KEY `relationship` (`relationship`),
  KEY `fromTableId` (`fromTableId`,`fromRecordId`,`toTableId`,`toRecordId`,`fromTimestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `relations`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `userId` bigint(20) unsigned NOT NULL auto_increment,
  `username` varchar(255) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `isActive` tinyint(4) NOT NULL default '1',
  `emailAddress` varchar(255) NOT NULL default '',
  `adminUser` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `users`
--

