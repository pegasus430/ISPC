<?php

 require_once("Pms/Form.php");
 
 class Application_Form_CareservicesGroups extends Pms_Form {

    public function validate($post)
 	{
 	    $Tr = new Zend_View_Helper_Translate();
 	    	
 	    $val = new Pms_Validation();
 
 	    if(!$val->isstring($post['groupname']))
 	    {
 	        $this->error_message['groupname'] = $Tr->translate('groupname_error');
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
 	
 		$ind = new CareservicesGroups();
 		$ind->client = $logininfo->clientid;
 		$ind->groupname = $post['groupname'];
 		$ind->save();
 		
 		$gr_id = $ind->id;
 		 
 		return $gr_id ;
 		
 	}
 	
 	public function UpdateData($post)
 	{
 		$ind = Doctrine::getTable('CareservicesGroups')->find($post['id']);
 		$ind->groupname = $post['groupname'];
 		if($post['client'] > 0)
 		{
 			$ind->client = $post['client'];
 		}
 		$ind->save();
 		
 	}
 	
 }