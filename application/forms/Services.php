<?php

 require_once("Pms/Form.php");
 
 class Application_Form_Services extends Pms_Form {
 
 	public function validate($post)
 	{
 		$Tr = new Zend_View_Helper_Translate();
 
 		$error = 0;
 		$val = new Pms_Validation();
 		
 		if(empty($post['services_name']))
 		{
 			//$this->error_message['servicesname'] = $Tr->translate('city_error');
 			$this->error_message['servicesname'] = $Tr->translate('field_services_not_empty');
 			$error = 7;
 		}
 		if($error == 0)
 		{
 			return true;
 		}
 
 		return false;
 	}
 
 	public function InsertData($post)
 	{
 		
 		$logininfo = new Zend_Session_Namespace('Login_Info');
 		$ind = new Services();
 		$ind->clientid = $logininfo->clientid;
 		$ind->services_name = $post['services_name'];
 		$ind->save();
 		
 		return $ind;
 		
 	}
 	
 	public function UpdateData($post)
 	{
 		$ind = Doctrine::getTable('Services')->find($post['id']);
 		$ind->services_name = $post['services_name'];
 		if($post['clientid'] > 0)
 		{
 			$ind->clientid = $post['clientid'];
 		}
 		$ind->save();
 		
 	}
 	
 }