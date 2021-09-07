<?php

 require_once("Pms/Form.php");
 
 class Application_Form_Servicesfuneral extends Pms_Form {
 
 	public function validate($post)
 	{
 		$Tr = new Zend_View_Helper_Translate();
 
 		$error = 0;
 		$val = new Pms_Validation();
 		
 		if(empty($post['services_funeral_name']))
 		{
 			$this->error_message['sfn'] = $Tr->translate('servicesfuneral_error');
 			$error = 1;
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
 		$ind = new Servicesfuneral();
 		$ind->clientid = $logininfo->clientid;
 		$ind->services_funeral_name = $post['services_funeral_name'];
 		$ind->cp_fname = $post['cp_fname'];
 		$ind->cp_lname = $post['cp_lname'];
 		$ind->street = $post['street'];
 		$ind->zip = $post['zip'];
 		$ind->city = $post['city'];
 		$ind->phone = $post['phone'];
 		$ind->fax = $post['fax'];
 		$ind->email = $post['email'];
 		$ind->save();
 		
 		return $ind;
 		
 	}
 	
 	public function UpdateData($post)
 	{
 		$ind = Doctrine::getTable('Servicesfuneral')->find($post['id']);
 		$ind->services_funeral_name = $post['services_funeral_name'];
 		$ind->cp_fname = $post['cp_fname'];
 		$ind->cp_lname = $post['cp_lname'];
 		$ind->street = $post['street'];
 		$ind->zip = $post['zip'];
 		$ind->city = $post['city'];
 		$ind->phone = $post['phone'];
 		$ind->fax = $post['fax'];
 		$ind->email = $post['email'];
 		if($post['clientid'] > 0)
 		{
 			$ind->clientid = $post['clientid'];
 		}
 		$ind->save();
 		
 	}
 	
 }