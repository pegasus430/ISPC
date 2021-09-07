<?php
Doctrine_Manager::getInstance()->bindComponent('VitabookPatient', 'MDAT');

class VitabookPatient extends BaseVitabookPatient {

	
	//get
	
	
	
	//set
	public function set_new_vitabook_patient(){
		//http://beta.order-med.de/WebServices/
		$wsdl = "http://dev.order-med.de/webservices?wsdl";
	
		//$client = new SoapClient();
		//$client->setWsdl($wsdl);
		

		//$soap_client = new Vitabook();
		$soap_login = array("_UserName"=> "TestUser",
				"_Password"=> "testpassword",
				"_TypValidierung" => "TypExternWithToken", 
				/*
				TypNormal or 
				TypExtern or 
				TypExternWithToken or 
				TypEmailAuthentication or 
				TypExternWithCooperationValidation or 
				TypArztLogin or 
				TypMCard
				*/
				//"_SessionID" => "111111"
				//"_ClientID" => "1"
		);
		

		
		$client = new Zend_Soap_Client();
		$client->setWsdl($wsdl);
		/*
		$result = $client->CreateWebSession($soap_login);
		
		print_r($client->getLastResponse());
		print_r ($result->CreateWebSessionResult);
		print_r ($result->_SessionID);
		
		print_r($client->getLastRequest());
		*/
		//return;
		//Security
		$soap_login = array("_UserName"=> "TestUser",
				"_Password"=> "testpassword",
				//"_ClientID" => "",
				);
		$result = $client->RequestAuthToken($soap_login);
		print_r($client->getLastRequest());
		//print_r($client->getLastResponse());

		
		//Zend_Debug::dump($result);
		
		//print_r($result1);
		
		return true;
	}

}
function RequestAuthTokenResponse(){
	
	
}
?>