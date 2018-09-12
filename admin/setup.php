<?php

$configFile='../includes/config/database.php';
set_error_handler(function($errno,$errstr,$errfile,$errline,$errcontext) {
	if (0===error_reporting()) {
		return false;
	}
	throw new ErrorException($errstr,0,$errno,$errfile,$errline);
});


//load config files
require_once '../includes/autoload.php';
set_time_limit(0);


/*______            _ _                _    
 |  ____|          | | |              | |   
 | |__ ___  ___  __| | |__   __ _  ___| | __
 |  __/ _ \/ _ \/ _` | '_ \ / _` |/ __| |/ /
 | | |  __/  __/ (_| | |_) | (_| | (__|   < 
 |_|  \___|\___|\__,_|_.__/ \__,_|\___|_|\_\
*/
if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		//update config file
		case "config":
		file_put_contents($configFile,"<?php
define('DB_HOST', '{$_GET["host"]}');
define('DB_USER', '{$_GET["user"]}');
define('DB_PASS', '{$_GET["pass"]}');
define('DB_DATA', '{$_GET["data"]}');");		
		break;
		
	}
	header("location: setup.php");
}




/* _____      _                  _____             __ _         ______ _ _      
  / ____|    | |                / ____|           / _(_)       |  ____(_) |     
 | (___   ___| |_ _   _ _ __   | |     ___  _ __ | |_ _  __ _  | |__   _| | ___ 
  \___ \ / _ \ __| | | | '_ \  | |    / _ \| '_ \|  _| |/ _` | |  __| | | |/ _ \
  ____) |  __/ |_| |_| | |_) | | |___| (_) | | | | | | | (_| | | |    | | |  __/
 |_____/ \___|\__|\__,_| .__/   \_____\___/|_| |_|_| |_|\__, | |_|    |_|_|\___|
                       | |                               __/ |                  
                       |_|                              |___/  
*/
function needConfig() {	
	echo '<h1>Database Config:</h1><p>Please create a mysql database and provide the following info.</p><p>Database user needs the following permissions:<br>&nbsp;&nbsp;&nbsp;&nbsp;select,insert,update,delete,create,alter</p><form><input type="hidden" name="action" value="config"><table border="0"><tr><td>Host: </td><td><input type="text" name="host" value="localhost"></td></tr><tr><td>User: </td><td><input type="text" name="user" value="CryptoAuth"></td></tr><tr><td>Pass: </td><td><input type="password" name="pass" value=""></td></tr><tr><td>Data: </td><td><input type="text" name="data" value="CryptoAuth"></td></tr></table><input type="submit" value="Submit">';
	die();
}

//check if config file exists and working
if (!file_exists($configFile)) needConfig();
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATA);
if ($db->connect_error) needConfig();







/*_____       _ _   _       _ _           _____        _        _                    
 |_   _|     (_) | (_)     | (_)         |  __ \      | |      | |                   
   | |  _ __  _| |_ _  __ _| |_ _______  | |  | | __ _| |_ __ _| |__   __ _ ___  ___ 
   | | | '_ \| | __| |/ _` | | |_  / _ \ | |  | |/ _` | __/ _` | '_ \ / _` / __|/ _ \
  _| |_| | | | | |_| | (_| | | |/ /  __/ | |__| | (_| | || (_| | |_) | (_| \__ \  __/
 |_____|_| |_|_|\__|_|\__,_|_|_/___\___| |_____/ \__,_|\__\__,_|_.__/ \__,_|___/\___|
*/
$query="SELECT count(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '".DB_DATA."';";
$stmt=$db->prepare($query);
$stmt->bind_result($count);
$stmt->execute();
$stmt->store_result();
$stmt->fetch();
if ($count==0) {	
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
	
	echo '<h1>Database Tables Created</h1>'; 
	header("location: setup.php");
	die();
}

/*_   _               _          _   ______      _                 _             
 | \ | |             | |        | | |  ____|    | |               (_)            
 |  \| | ___  ___  __| | ___  __| | | |__  __  _| |_ ___ _ __  ___ _  ___  _ __  
 | . ` |/ _ \/ _ \/ _` |/ _ \/ _` | |  __| \ \/ / __/ _ \ '_ \/ __| |/ _ \| '_ \ 
 | |\  |  __/  __/ (_| |  __/ (_| | | |____ >  <| ||  __/ | | \__ \ | (_) | | | |
 |_| \_|\___|\___|\__,_|\___|\__,_| |______/_/\_\\__\___|_| |_|___/_|\___/|_| |_|
*/
if (!function_exists('idn_to_ascii')) {
	echo '<h3>Missing Extension Found</h3><p>Please edit php.ini to enable the "intl" plugin.  Just search php.ini for "intl" no quotes and remove the ; at the begining.  Then restart apache.';
	die;
}

/* _____                    _____        _        _                    
  / ____|                  |  __ \      | |      | |                   
 | (___  _   _ _ __   ___  | |  | | __ _| |_ __ _| |__   __ _ ___  ___ 
  \___ \| | | | '_ \ / __| | |  | |/ _` | __/ _` | '_ \ / _` / __|/ _ \
  ____) | |_| | | | | (__  | |__| | (_| | || (_| | |_) | (_| \__ \  __/
 |_____/ \__, |_| |_|\___| |_____/ \__,_|\__\__,_|_.__/ \__,_|___/\___|
          __/ |                                                        
         |___/  
*/
$done=true;
try {
	require_once "../cron/daily.php";
} catch (Exception $e) {
	$done=false;
}
if ($done) {
	echo '<h1>Setup Complete</h1><p>You should now setup a cron job to run /cron/daily.php</p>';
} else {
	echo '<h1>Setup Failed</h1><p>Please make sure server has access to the internet and auth.digibyte.rocks is working.';	
}