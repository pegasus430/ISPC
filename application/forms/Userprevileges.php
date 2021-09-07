<?php

require_once("Pms/Form.php");

class Application_Form_Userprevileges extends Pms_Form
{
	public function InsertData($post)
	{
		foreach($post['hdnmoduleid'] as $moduleid)
		{
			$q = Doctrine_Query::create()
			->select('*')
			->from('UserPrevileges')
			->where('userid = ?'.$_GET['id'])
			->andWhere('moduleid= ?', $moduleid);
				
			$userpre = $q->execute();
				
			if(count($userpre->toArray())<1)
			{

				$user = new UserPrevileges();
				$user->userid = $_GET['id'];
				$user->clientid = $post['hdnclientid'];
				$user->moduleid = $moduleid;
				$user->canadd = $post['canadd'][$moduleid];
				$user->canedit = $post['canedit'][$moduleid];
				$user->canview = $post['canview'][$moduleid];
				$user->candelete = $post['candelete'][$moduleid];
				$user->save();
			}else{
					
				$q = Doctrine_Query::create()
				->delete('UserPrevileges')
				->where('userid = ?'.$_GET['id'])
				->andWhere('moduleid= ?', $moduleid);
				$q->execute();

				$user = new UserPrevileges();
				$user->userid = $_GET['id'];
				$user->clientid = $post['hdnclientid'];
				$user->moduleid = $moduleid;
				$user->canadd = $post['canadd'][$moduleid];
				$user->canedit = $post['canedit'][$moduleid];
				$user->canview = $post['canview'][$moduleid];
				$user->candelete = $post['candelete'][$moduleid];
				$user->save();
					
			}
		}

	}

	public function CopypermissionData($post)
	{
		$q = Doctrine_Query::create()
		->delete('UserPrevileges')
		->where('userid= ?', $_GET['id'])
		->andWhere('clientid= ?', $post['hdnclientid']);
		$q->execute();

		$copyq = Doctrine_Query::create()
		->select('*')
		->from('UserPrevileges')
		->where('userid= ?', $_POST['copyuserid']);
			
		$userpre = $copyq->execute();
		foreach($userpre->toArray() as $key=>$val)
		{
			$user = new UserPrevileges();
			$user->userid = $_GET['id'];
			$user->clientid = $post['hdnclientid'];
			$user->moduleid = $val['moduleid'];
			$user->canadd = $val['canadd'];
			$user->canedit = $val['canedit'];
			$user->canview = $val['canview'];
			$user->candelete = $val['candelete'];
			$user->save();
		}
			

	}

	public function UpdateData($post)
	{
		$user = Doctrine::getTable('User')->find($_GET['id']);
		$user->username = $post['username'];
		if(strlen($post['password'])>0)
		{
			$user->password = $post['password'];
		}
		$user->name = $post['name'];
		$user->isadmin = $post['isadmin'];
		$user->save();

		header("location: listuser");
	}


}

?>