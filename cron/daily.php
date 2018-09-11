<?php
	
	require_once '../includes/autoload.php';
	require_once '../includes/CryptoAuth.php';
	
	
	
	//get master data
	$data=json_decode(file_get_contents(MASTER_SERVER),true)["result"];
	
	//load CryptoAuth
	$auth=new CryptoAuth(DB_HOST, DB_USER, DB_PASS, DB_DATA);
	
	//set coins
	$auth->setCoins($data["coins"]);

	//update url
	foreach ($data["sites"] as $url) {
		$auth->updateURL($url);
	}
	