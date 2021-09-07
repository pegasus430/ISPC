<?php

require_once("Pms/Form.php");

class Application_Form_ClientSymptomsGroups extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['groupname'])){
			$this->error_message['groupname']=$Tr->translate("entergroupname"); $error=4;
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
		$res = new ClientSymptomsGroups();
		$res->groupname = $post['groupname'];
		$res->clientid = $clientid;
		$res->save();
		return $res;
	}

	public function UpdateData($post,$clientid)
	{
		$res = Doctrine::getTable('ClientSymptomsGroups')->find($post['group_id']);
		$res->groupname = $post['groupname'];
		$res->save();
		return $res;
	}
}
?>