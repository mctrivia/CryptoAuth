<?php
	$GLOBALS['errorValue']='{"error":"Unexpected Error"}';
	function jsonError($e) {
		//file_put_contents('~error.txt',$e->getMessage()."\r\n\r\n\r\n",FILE_APPEND);
		echo $GLOBALS['errorValue'];die();
	}
	set_error_handler('jsonError');
	set_exception_handler('jsonError');

/*              _   _       _____                      _                 _           
     /\        | | | |     |  __ \                    | |               | |          
    /  \  _   _| |_| |__   | |  | | _____      ___ __ | | ___   __ _  __| | ___ _ __ 
   / /\ \| | | | __| '_ \  | |  | |/ _ \ \ /\ / / '_ \| |/ _ \ / _` |/ _` |/ _ \ '__|
  / ____ \ |_| | |_| | | | | |__| | (_) \ V  V /| | | | | (_) | (_| | (_| |  __/ |   
 /_/    \_\__,_|\__|_| |_| |_____/ \___/ \_/\_/ |_| |_|_|\___/ \__,_|\__,_|\___|_| 
*/

if (isset($_GET["server"])) {
	
	//verify address given and get only the domain
	$server=parse_url($_GET["server"],PHP_URL_HOST);
	if ($server==false) {
		echo '{"error":"Invalid Server Address"}';die();
	}
	
	//try to download file.  return default if fails
	$GLOBALS['errorValue']='{"error":false,"server":'.json_encode($server).',"result":{"owner":{"name":"","handle":"","email":"","phone":"","address":""},"wallets":{}}}';
	$data=json_decode(file_get_contents("https://{$server}/authenticate.crypto.json"));
	echo json_encode(array(
		"error"=>	false,
		"server"=>	$server,
		"result"=>	$data
	));
	die();	
}
	
/*              _   _       _    _           _       _       
     /\        | | | |     | |  | |         | |     | |      
    /  \  _   _| |_| |__   | |  | |_ __   __| | __ _| |_ ___ 
   / /\ \| | | | __| '_ \  | |  | | '_ \ / _` |/ _` | __/ _ \
  / ____ \ |_| | |_| | | | | |__| | |_) | (_| | (_| | ||  __/
 /_/    \_\__,_|\__|_| |_|  \____/| .__/ \__,_|\__,_|\__\___|
                                  | |                        
                                  |_| 
*/
if (isset($_GET["update"])) {
	//update database
	require_once '../includes/autoload.php';
	require_once '../includes/CryptoAuth.php';
	$auth=new CryptoAuth(DB_HOST, DB_USER, DB_PASS, DB_DATA);
	
	//validate input
	$GLOBALS['errorValue']='{"error":"Invalid Server Address"}';
	$url=parse_url($_GET["update"],PHP_URL_HOST);
	if ($url==false) {
		return 'Invalid Server Address';
	}
	$url=strtolower($url);	
	
	//get server data
	$data=$auth->updateURL($url);
	if (!is_array($data)) $data=array($data);
	echo json_encode($data);
	die();
}

/* _____ _               _                  _     _                   
  / ____| |             | |        /\      | |   | |                  
 | |    | |__   ___  ___| | __    /  \   __| | __| |_ __ ___  ___ ___ 
 | |    | '_ \ / _ \/ __| |/ /   / /\ \ / _` |/ _` | '__/ _ \/ __/ __|
 | |____| | | |  __/ (__|   <   / ____ \ (_| | (_| | | |  __/\__ \__ \
  \_____|_| |_|\___|\___|_|\_\ /_/    \_\__,_|\__,_|_|  \___||___/___/
*/
if (isset($_GET["address"])) {
	$address=$_GET["address"];
	
	require_once '../includes/autoload.php';
	require_once '../includes/CryptoAuth.php';
	$auth=new CryptoAuth(DB_HOST, DB_USER, DB_PASS, DB_DATA);
	$data=array(
		"error"=>	false,
		"result"=>	$auth->getAddress($address)
	);
	echo json_encode($data);
	die();
}







/* _____      _         _      _     _   
  / ____|    (_)       | |    (_)   | |  
 | |     ___  _ _ __   | |     _ ___| |_ 
 | |    / _ \| | '_ \  | |    | / __| __|
 | |___| (_) | | | | | | |____| \__ \ |_ 
  \_____\___/|_|_| |_| |______|_|___/\__|
*/
if (isset($_GET["coins"])) {
	require_once '../includes/autoload.php';
	require_once '../includes/CryptoAuth.php';
	$auth=new CryptoAuth(DB_HOST, DB_USER, DB_PASS, DB_DATA);
	$data=array(
		"error"=>	false,
		"result"=>	$auth->getCoins()
	);
	echo json_encode($data);
	die();
}






/* _____ _ _            
  / ____(_) |           
 | (___  _| |_ ___  ___ 
  \___ \| | __/ _ \/ __|
  ____) | | ||  __/\__ \
 |_____/|_|\__\___||___/
*/
	require_once '../includes/autoload.php';
	require_once '../includes/CryptoAuth.php';
	$auth=new CryptoAuth(DB_HOST, DB_USER, DB_PASS, DB_DATA);
	$data=array(
		"error"=>	false,
		"result"=>	array(
			"coins"=>	$auth->exportCoins(),
			"sites"=>	$auth->getSites()
		)
	);
	echo json_encode($data);