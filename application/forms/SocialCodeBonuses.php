<?php

require_once("Pms/Form.php");

class Application_Form_SocialCodeBonuses extends Pms_Form
{
	public function validate($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();
		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['bonusname'])){
			$this->error_message['bonusname']=$Tr->translate('enterbonusname'); $error=1;
		}

		if(strlen($_GET['id'])<1)
		{
			$user = Doctrine_Query::create()
			->select('*')
			->from('SocialCodeBonuses')
			->where("bonusname ='".$post['bonusname']."' and clientid= '".$logininfo->clientid."'");
			$userexec = $user->execute();
			if(count($userexec->toArray())>0){
				$this->error_message['bonusname'] = $Tr->translate("bonusnamealreadyexists");$error=7;
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
		$user = new SocialCodeBonuses();
		$user->bonusname = $post['bonusname'];
		$user->bonusshortcut = $post['bonusshortcut'];
		$user->clientid = $logininfo->clientid;
		$user->save();
	}

	public function UpdateData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$user = Doctrine::getTable('SocialCodeBonuses')->find($_GET['id']);
		$user->bonusname = $post['bonusname'];
		$user->bonusshortcut = $post['bonusshortcut'];
		$user->save();
	}
}

?>