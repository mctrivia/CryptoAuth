<?php

set_time_limit(0);

/*_____       _ _   _       _ _         
 |_   _|     (_) | (_)     | (_)        
   | |  _ __  _| |_ _  __ _| |_ _______ 
   | | | '_ \| | __| |/ _` | | |_  / _ \
  _| |_| | | | | |_| | (_| | | |/ /  __/
 |_____|_| |_|_|\__|_|\__,_|_|_/___\___|
*/

//load config files
require_once '../includes/autoload.php';
	
//load database
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATA);
if ($db->connect_error) {
	echo 'Database Trouble.  Please Try Again';die();
}

//addresses
$db->query("CREATE TABLE `addresses` (
  `address` varchar(255) NOT NULL,
  `wid` bigint(20) UNSIGNED NOT NULL,
  `cid` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
$db->query("ALTER TABLE `addresses`
  ADD UNIQUE KEY `address` (`address`);");
  
//coins
$db->query("CREATE TABLE `coins` (
  `cid` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
$db->query("ALTER TABLE `coins`
  ADD PRIMARY KEY (`cid`);");

//sites
$db->query("CREATE TABLE `sites` (
  `sid` bigint(20) UNSIGNED NOT NULL,
  `url` varchar(255) NOT NULL,
  `ownerName` varchar(255) NOT NULL,
  `ownerHandle` varchar(255) NOT NULL,
  `ownerEmail` varchar(255) NOT NULL,
  `ownerPhone` varchar(255) NOT NULL,
  `ownerAddress` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
$db->query("ALTER TABLE `sites`
  ADD PRIMARY KEY (`sid`);");
$db->query("ALTER TABLE `sites`
  MODIFY `sid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;");
  
//wallets
$db->query("CREATE TABLE `wallets` (
  `wid` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sid` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
$db->query("ALTER TABLE `wallets`
  ADD PRIMARY KEY (`wid`);");
$db->query("ALTER TABLE `wallets`
  MODIFY `wid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;");
  
  
 require_once "../cron/daily.php";