<?php

 require_once("Pms/Form.php");
 
 class Application_Form_Remedies extends Pms_Form {
 
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
 	
 		$ind = new Remedies();
 		$ind->clientid = $logininfo->clientid;
 		$ind->indikation_key = $post['indikation_key'];
 		$ind->indikation_name = $post['indikation_name'];
 		$ind->save();
 		
 		return $ind;
 		
 	}
 	
 	public function UpdateData($post)
 	{
 		$ind = Doctrine::getTable('Remedies')->find($post['id']);
 		$ind->indikation_key = $post['indikation_key'];
 		$ind->indikation_name = $post['indikation_name'];
 		if($post['clientid'] > 0)
 		{
 			$ind->clientid = $post['clientid'];
 		}
 		$ind->save();
 		
 	}
 	
 }