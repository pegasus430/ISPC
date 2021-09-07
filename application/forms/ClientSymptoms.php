<?php

require_once("Pms/Form.php");

class Application_Form_ClientSymptoms extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['description'])){
			$this->error_message['description']=$Tr->translate("enterdescription"); $error=4;
		}

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if($clientid<1)
		{
			$this->error_message['message']="Select client"; $error=4;
		}
			
		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post,$clientid)
	{
		$res = new ClientSymptoms();
		$res->description = $post['description'];
		$res->clientid = $clientid;
		$res->group_id = $post['group_id'];
		$res->save();
		return $res;
	}

	public function UpdateData($post,$clientid)
	{
	    if($post['symid']){
    		$res = Doctrine::getTable('ClientSymptoms')->find($post['symid']);
    		$res->description = $post['description'];
    		$res->group_id = $post['group_id'];
    		$res->save();
    		return $res;
	    } else {
    		return false;
	    }
	}
}
?>