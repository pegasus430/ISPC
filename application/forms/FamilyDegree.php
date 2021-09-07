<?php

 require_once("Pms/Form.php");
 
 class Application_Form_FamilyDegree extends Pms_Form {
 
 	public function validate($post)
 	{
 		$Tr = new Zend_View_Helper_Translate();
 
 		$error = 0;
 		$val = new Pms_Validation();
 		
 		if(empty($post['family_degree']))
 		{
 			//$this->error_message['servicesname'] = $Tr->translate('city_error');
 			$this->error_message['family_degree'] = $Tr->translate('field_relation_not_empty');
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
 		$ind = new FamilyDegree();
 		$ind->clientid = $logininfo->clientid;
 		$ind->family_degree = $post['family_degree'];
 		$ind->save();
 		
 		return $ind;
 		
 	}
 	
 	public function UpdateData($post)
 	{
 		$ind = Doctrine::getTable('FamilyDegree')->find($post['id']);
 		$ind->family_degree = $post['family_degree'];
 		if($post['clientid'] > 0)
 		{
 			$ind->clientid = $post['clientid'];
 		}
 		$ind->save();
 		
 	}
 	
 }