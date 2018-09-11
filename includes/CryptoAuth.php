<?php
//new CryptoAuth(DB_HOST, DB_USER, DB_PASS, DB_DATA);
use Spatie\SslCertificate\SslCertificate;
	
class CryptoAuth {
	private	$db;
	
	public function __construct($host, $user, $pass, $data) {
		$this->db = new mysqli($host, $user, $pass, $data);
		return (!$this->db->connect_error);
	}
	
/*            _     _                             
     /\      | |   | |                            
    /  \   __| | __| |_ __ ___  ___ ___  ___  ___ 
   / /\ \ / _` |/ _` | '__/ _ \/ __/ __|/ _ \/ __|
  / ____ \ (_| | (_| | | |  __/\__ \__ \  __/\__ \
 /_/    \_\__,_|\__,_|_|  \___||___/___/\___||___/
*/
private function updateAddresses($walletData,$walletWID,$walletName,$cid,&$changes) {
	
	//get list of any addresses
	$query="SELECT `address`,`cid` FROM `addresses` WHERE `wid`=?";
	$stmtExisting=$this->db->prepare($query);
	$stmtExisting->bind_param('i',$walletWID);
	$stmtExisting->bind_result($walletAddress,$coinCID);
	$stmtExisting->execute();
	$stmtExisting->store_result();
	$query='DELETE FROM `addresses` WHERE `wid`=? AND `cid`=?';
	$stmtDelete=$this->db->prepare($query);
	$stmtDelete->bind_param('ii',$walletWID,$coinCID);
	$query=	'UPDATE `addresses` SET `address`=? WHERE `wid`=? AND `cid`=?';
	$stmtUpdate=$this->db->prepare($query);
	$stmtUpdate->bind_param('sii',$newAddress,$walletWID,$coinCID);
	while($stmtExisting->fetch()) {
		//check if address still valid
		$coinCode=array_search($coinCID,$cid);
		if (isset($walletData[$coinCode])) {
			if ($walletData[$coinCode]!=$walletAddress) {
				//changing
				$newAddress=$walletData[$coinCode];
				$stmtUpdate->execute();
				$changes[]="Changed $coinCode address $walletAddress to $newAddress in $walletName";
				
			}
			unset($walletData[$coinCode]);
			
		} else {
			//removing
			$stmtDelete->execute();
			$changes[]="Removed $coinCode address $walletAddress from $walletName";
		}
		
	}
	
	//add any addresses that didn't already exist
	$query='INSERT INTO `addresses`(`address`, `wid`, `cid`) VALUES (?,?,?)';
	$stmt=$this->db->prepare($query);
	$stmt->bind_param('sii',$address,$walletWID,$coinCID);
	foreach ($walletData as $coinCode=>$address) {
		$coinCode=strtoupper($coinCode);
		$coinCID=$cid[$coinCode];
		$stmt->execute();
		$changes[]="Added $coinCode address $address in $walletName";
	}
	
}


public function updateURL($url) {
/*_____       _ _   _       _ _         
 |_   _|     (_) | (_)     | (_)        
   | |  _ __  _| |_ _  __ _| |_ _______ 
   | | | '_ \| | __| |/ _` | | |_  / _ \
  _| |_| | | | | |_| | (_| | | |/ /  __/
 |_____|_| |_|_|\__|_|\__,_|_|_/___\___|
*/
	$changes=array();
	
	//validate ssl certificate
	require_once __DIR__."/vendor/autoload.php";
	$certificate = SslCertificate::createForHostName($url);
	if (!$certificate->isValid()) {
		return '{"error":"Invalid SSL Certificate"}';
	}
	
	//download json file if exists
	$data=@file_get_contents('https://'.$url.'/authenticate.crypto.json');
	if ($data=="") {
		return '{"error":"Could not open https://'.$url.'/authenticate.crypto.json"}';
	}
	
	$GLOBALS['errorValue']='{"error":"Invalid File Format"}';
	$data=json_decode($data,true);
	if ($data==NULL) {
		return "Invalid File Format";
	}
	if (!isset($data['wallets'])) {
		return "Invalid File Format";
	}
	$GLOBALS['errorValue']='{"error":"Unexpected Error"}';

/* _____ _ _            
  / ____(_) |           
 | (___  _| |_ ___  ___ 
  \___ \| | __/ _ \/ __|
  ____) | | ||  __/\__ \
 |_____/|_|\__\___||___/
*/
	//check if url in database and get 	$sid
	$query="SELECT `sid` FROM `sites` WHERE `url`=? LIMIT 1";
	$stmt=$this->db->prepare($query);
	$stmt->bind_param('s',$url);
	$stmt->bind_result($sid);
	$stmt->execute();
	$stmt->store_result();
	if (!$stmt->fetch()) {
		//Not present create site entry
		$owner=array(
			"name"=>	"",
			"handle"=>	"",
			"email"=>	"",
			"phone"=>	"",
			"address"=>	""
		);
		if (isset($data["owner"])) {
			foreach ($data["owner"] as $key=>$value) {
				$owner[$key]=$value;
			}
		}
		$query="INSERT INTO `sites`(`url`, `ownerName`, `ownerHandle`, `ownerEmail`, `ownerPhone`, `ownerAddress`) VALUES (?,?,?,?,?,?)";
		$stmt=$this->db->prepare($query);
		$stmt->bind_param('ssssss',$url,$owner["name"],$owner["handle"],$owner["email"],$owner["phone"],$owner["address"]);
		$stmt->execute();
		$sid=$this->db->insert_id;
	} 
	
/* _____      _           
  / ____|    (_)          
 | |     ___  _ _ __  ___ 
 | |    / _ \| | '_ \/ __|
 | |___| (_) | | | | \__ \
  \_____\___/|_|_| |_|___/
*/
	//get list of known coins
	$cid=array();
	$query="SELECT `cid`,`code` FROM `coins` WHERE 1";
	$stmt=$this->db->prepare($query);
	$stmt->bind_result($coinCID,$code);
	$stmt->execute();
	$stmt->store_result();
	while($stmt->fetch()) {
		$cid[$code]=$coinCID;
	}
	
	//add any missing coins
	$query="INSERT INTO `coins`(`name`,`code`) VALUES ('',?)";
	$stmt=$this->db->prepare($query);
	$stmt->bind_param('s',$code);
	foreach ($data["wallets"] as $name=>$coins) {
		foreach ($coins as $code=>$address) {
			$code=strtoupper($code);									//make sure upper case
			if (!isset($cid[$code])) {
				$stmt->execute();
				$cid[$code]=$this->db->insert_id;
			}
		}
	}
	
/*_          __   _ _      _       
 \ \        / /  | | |    | |      
  \ \  /\  / /_ _| | | ___| |_ ___ 
   \ \/  \/ / _` | | |/ _ \ __/ __|
    \  /\  / (_| | | |  __/ |_\__ \
     \/  \/ \__,_|_|_|\___|\__|___/
*/
	//get list of wallets we know
	$wid=array();
	$query="SELECT `wid`,`name` FROM `wallets` WHERE `sid`=?";
	$stmtExisting=$this->db->prepare($query);
	$stmtExisting->bind_param('i',$sid);
	$stmtExisting->bind_result($walletWID,$walletName);
	$stmtExisting->execute();
	$stmtExisting->store_result();
	$query='DELETE FROM `wallets` WHERE `wid`=?';
	$stmtDelete1=$this->db->prepare($query);
	$stmtDelete1->bind_param('i',$walletWID);	
	$query='DELETE FROM `addresses` WHERE `wid`=?';
	$stmtDelete2=$this->db->prepare($query);
	$stmtDelete2->bind_param('i',$walletWID);
	while($stmtExisting->fetch()) {
		//check if wallet still valid
		if (isset($data["wallets"][$walletName])) {
			//mark wallet as keep
			$wid[$walletName]=$walletWID;	
			$this->updateAddresses($data["wallets"][$walletName],$walletWID,$walletName,$cid,$changes);
			
		} else {
			//remove wallet
			$stmtDelete1->execute();
			$stmtDelete2->execute();
			$changes[]="Removed wallet $walletName";
		}
	}
	
	//add any wallets we don't already know about
	$query="INSERT INTO `wallets`(`name`, `sid`) VALUES (?,?)";
	$stmt=$this->db->prepare($query);
	$stmt->bind_param('si',$name,$sid);	
	foreach ($data["wallets"] as $name=>$coins) {
		if (!isset($wid[$name])) {
			//add wallet
			$stmt->execute();
			$wid[$name]=$walletWID=$this->db->insert_id;
			$changes[]="Added wallet $name";
			$this->updateAddresses($data["wallets"][$name],$walletWID,$name,$cid,$changes);
		}
	}
	return $changes;
}



public function getAddress($address) {
	$query='SELECT wallets.name,sites.url,coins.name,coins.code FROM `addresses`,`coins`,`sites`,`wallets` WHERE `addresses`.`address`=? AND `addresses`.`wid`=`wallets`.`wid` AND `addresses`.`cid`=`coins`.`cid` AND `wallets`.`sid`=`sites`.`sid` limit 1';
	$stmt=$this->db->prepare($query);
	$stmt->bind_param('s',$address);
	$stmt->bind_result($walletName,$server,$coinName,$coinCode);
	$stmt->execute();
	$stmt->store_result();
	if ($stmt->fetch()) {
		return array(
			"wallet"=>	substr($walletName,0,strpos($walletName,'@')),
			"server"=>	$server,
			"coin"=>	$coinName,
			"code"=>	$coinCode,
			"address"=>	$address
		);
	}
	return false;
}

public function getSites() {
	$query='SELECT `url` FROM `sites` WHERE 1';
	$stmt=$this->db->prepare($query);
	$stmt->bind_result($url);
	$stmt->execute();
	$stmt->store_result();
	$data=array();
	while ($stmt->fetch()) {
		$data[]=$url;
	}
	return $data;
}

public function setSites($data) {
	
}

public function getCoins() {
	$query='SELECT `code`,`name` FROM `coins` WHERE 1';
	$stmt=$this->db->prepare($query);
	$stmt->bind_result($code,$name);
	$stmt->execute();
	$stmt->store_result();
	$data=array();
	while ($stmt->fetch()) {
		$data[$code]=$name;
	}
	return $data;
}
	
public function exportCoins() {
	$query='SELECT `cid`,`code`,`name` FROM `coins` WHERE 1';
	$stmt=$this->db->prepare($query);
	$stmt->bind_result($cid,$code,$name);
	$stmt->execute();
	$stmt->store_result();
	$data=array();
	while ($stmt->fetch()) {
		$data[]=array($cid,$code,$name);
	}
	return $data;
}
	
public function setCoins($data) {
	$query='INSERT INTO `coins`(`cid`, `code`, `name`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `code`=?,`name`=?;';
	$stmt=$this->db->prepare($query);
	$stmt->bind_param('issss',$cid,$code,$name,$code,$name);
	foreach ($data as $line) {
		list($cid,$code,$name)=$line;
		$stmt->execute();
	}
}
	
}
	
	