<?php
return array (
	
	//the clientid for this server
	'clientid'		=>	79,
	//the userid this server has
	'userid'		=>	1,

	//everything is logged to db is encrypted if true 
	'encryptlog'	=>  false,		
	//0=print all, 2 print only errors									
	'verbosity' 	=>	0,
									
	//where to send outgoing mdm-messages								
	'mdm_host'	=> "localhost",
	//where to send outgoing mdm-messages
	'mdm_port'	=> "7250",	
	
	//where to send outgoing bar-messages								
	'bar_host'	=> "kommsp03.be-mrz-komm.klin",
	//where to send outgoing bar-messages
	'bar_port'	=> "8010",
	
	
	//where to send outgoing ft1-messages								
	'ft1_host'	=> "localhost",
	//where to send outgoing bar-messages
	'ft1_port'	=> "7151"	
	);

?>
