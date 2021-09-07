<?php
require_once("Pms/Form.php");
class Application_Form_Usergroup extends Pms_Form
{
	public function validate ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();
		$error = 0;
		$val = new Pms_Validation();
		if (!$val->isstring($post['groupname']))
		{
			$this->error_message['groupname'] = $Tr->translate('entergroupname');
			$error = 1;
		}

		if (strlen($_GET['id']) < 1)
		{
			$user = Doctrine_Query::create()
			->select('*')
			->from('Usergroup')
			->where("groupname ='" . $post['groupname'] . "' and clientid= '" . $logininfo->clientid . "'  and isdelete = 0");
			$userexec = $user->execute();
			if (count($userexec->toArray()) > 0)
			{
				$this->error_message['groupname'] = $Tr->translate("groupnamealreadyexists");
				$error = 7;
			}
		}
		if ($error == 0)
		{
			return true;
		}
		return false;
	}

	public function InsertData ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$user = new Usergroup();
		$user->groupname = $post['groupname'];
		$user->groupmaster = $post['groupmaster'];
		$user->clientid = $logininfo->clientid;
		$user->isactive = $post['isactive'];
		$user->indashboard = $post['indashboard'];
		$user->startpage_duty = $post['startpage_duty'];
		$user->social_groups = $post['social_groups'];
		$user->save();
	}

	public function UpdateData ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$user = Doctrine::getTable('Usergroup')->find($_GET['id']);
		$user->groupname = $post['groupname'];
		$user->groupmaster = $post['groupmaster'];
		$user->isactive = $post['isactive'];
		$user->indashboard = $post['indashboard'];
		$user->startpage_duty = $post['startpage_duty'];
		$user->social_groups = $post['social_groups'];
		$user->save();
	}
}
?>