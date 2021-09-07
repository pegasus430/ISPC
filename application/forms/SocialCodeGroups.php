<?php

require_once("Pms/Form.php");

class Application_Form_SocialCodeGroups extends Pms_Form
{
	public function validate($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();
		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['groupname'])){
			$this->error_message['groupname']=$Tr->translate('entergroupname'); $error=1;
		}

		if(strlen($_GET['id'])<1)
		{
			$user = Doctrine_Query::create()
			->select('*')
			->from('SocialCodeGroups')
			->where("groupname ='".$post['groupname']."' and clientid= '".$logininfo->clientid."'");
			$userexec = $user->execute();
			if(count($userexec->toArray())>0){
				$this->error_message['groupname'] = $Tr->translate("groupnamealreadyexists");$error=7;
			}
		}
		if($error==0)
		{
		 return true;
		}
		return false;
	}


	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$user = new SocialCodeGroups();
		$user->groupname = $post['groupname'];
		$user->groupshortcut = $post['groupshortcut'];
		$user->clientid = $logininfo->clientid;
		$user->save();

	}

	public function UpdateData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$user = Doctrine::getTable('SocialCodeGroups')->find($_GET['id']);
		$user->groupname = $post['groupname'];
		$user->groupshortcut = $post['groupshortcut'];
		$user->save();

	}


}

?>