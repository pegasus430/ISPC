<?php

 require_once("Pms/Form.php");
 
 class Application_Form_SapvEvaluation extends Pms_Form {
 
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
 
 	public function insert($post)
 	{
 		$ind = new SapvEvaluation();
 		$ind->ipid = $post['ipid'];
 		$ind->status = $post['status'];
 		$ind->admissionid = $post['admissionid'];
 		$ind->save();
 		
 		return $ind->id;
 		
 	}

 	public function update($post)
 	{
 	    
 		$ind = Doctrine::getTable('SapvEvaluation')->findById($post['form_id']);
 		if(strlen($post['status']) > 0){
     		$ind->status = $post['status'];
 		}
 		$ind->save();
 		
 	}
 	
 	public function reset($post)
 	{
 	    $logininfo = new Zend_Session_Namespace('Login_Info');
 	    $userid = $logininfo->userid;
 	    $change_date = date('Y-m-d H:i:s', time());
 	    
 	    $sph = Doctrine_Query::create()
 	    ->update('SapvEvaluation')
 	    ->set('isdelete', '1')
 	    ->set('change_user', $userid)
 	    ->set('change_date', '"' . $change_date . '"')
 	    ->where("id='" . $post['form_id'] . "'")
 	    ->andWhere("admissionid='" . $post['admissionid'] . "'");
 	    $sph->execute();
 	}
 	
 }