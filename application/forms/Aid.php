<?php

 require_once("Pms/Form.php");
 
 class Application_Form_Aid extends Pms_Form {
 
 	public function validate($post)
 	{
 		$Tr = new Zend_View_Helper_Translate();
 
 		$error = 0;
 		$val = new Pms_Validation();
 		if($error == 0)
 		{
 			return true;
 		}
 
 		return false;
 	}
 
 	public function InsertData($post)
 	{
 		$logininfo = new Zend_Session_Namespace('Login_Info');
 	
 		$ind = new Aid();
 		$ind->clientid = $logininfo->clientid;
 		$ind->name = $post['name'];
 		$ind->favourite = $post['favourite']; //ISPC-2381 Carmen 25.01.2021
 		$ind->save();
 		
 		return $ind;
 		
 	}
 	
 	public function UpdateData($post)
 	{
 		$ind = Doctrine::getTable('Aid')->find($post['id']);
 		$ind->name = $post['name'];
 		$ind->favourite = $post['favourite']; //ISPC-2381 Carmen 25.01.2021
 		if($post['clientid'] > 0)
 		{
 			$ind->clientid = $post['clientid'];
 		}
 		$ind->save();
 		
 	}
 	
 }