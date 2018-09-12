# CryptoAuth
Server for wallet apps to check address is known


Setup:

1) run

      admin/setup.php
      
2) set cron job to run     /cron/daily.php

       crontab -e
       
       0 0 * * * php /mnt/data/www/hosted/auth/cron/daily.php
       
       above path should point to daily.php
       
3) delete admin folder and make cron folder non accessable from internet.



Wallet Integration:

make call to     api/?address=

returns:

{
	"error":false,
	"result":{
		"wallet":"Wallet Name for example TIP 1",
		"server":"domain.tld",
		"coin":"DigiByte",
		"code":"DGB",
		"address":"AddressAskedFor"
	}
}
