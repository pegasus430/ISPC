<?php

require_once("Pms/Form.php");

class Application_Form_ShortcutPrevileges extends Pms_Form
{

	public function setShortcutPrevilege($mainshrtcutarr)
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid= $logininfo->clientid;

		$shrt = Doctrine::getTable('ShortcutPrevileges')->findBy('shortcutid',$mainshrtcutarr['shrtid']);
		$shrt = Doctrine_Query::create()
		->select('*')
		->from('ShortcutPrevileges')
		->where('shortcutid = "'.$mainshrtcutarr['shrtid'].'"')
		->andWhere('clientid = "'.$clientid.'"')
		->andWhere('groupid ="'.$mainshrtcutarr['grpid'].'"');

		$track = $shrt->execute();
		 
		if($track)
		{
			$shrtarr = $track->toArray();
		}
		 
		if(count($shrtarr)>0)
		{
			$q = Doctrine_Query::create()
			->update('ShortcutPrevileges')
			->set($mainshrtcutarr['act'],$mainshrtcutarr['val'])
			->where('id = "'.$shrtarr[0]['id'].'"');
			$qr =$q->execute();
		}
		else
		{
			$prev = new shortcutPrevileges();
			$prev->groupid = $mainshrtcutarr['grpid'];
			$prev->clientid = $clientid;
			$prev->shortcutid = $mainshrtcutarr['shrtid'];
			$prev->$mainshrtcutarr['act'] = $mainshrtcutarr['val'];
			$prev->save();
		}
	}

	public function CopypermissionData($post)
	{
		$q = Doctrine_Query::create()
		->delete('ShortcutPrevileges')
		->where('groupid= ?', $_GET['id'])
		->andWhere('clientid= ?', $post['hdnclientid']);
		$q->execute();

		$copyq = Doctrine_Query::create()
		->select('*')
		->from('ShortcutPrevileges')
		->where('groupid= ?', $_POST['copygroupid']);
		$userpre = $copyq->execute();

		foreach($userpre->toArray() as $key=>$val)
		{
			$user = new ShortcutPrevileges();
			$user->groupid = $_GET['id'];
			$user->clientid = $post['hdnclientid'];
			$user->shortcutid = $val['shortcutid'];
			$user->canadd = $val['canadd'];
			$user->canedit = $val['canedit'];
			$user->canview = $val['canview'];
			$user->candelete = $val['candelete'];
			$user->save();
		}
			

	}

	 
}

?>