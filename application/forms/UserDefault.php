<?php

require_once("Pms/Form.php");

class Application_Form_UserDefault extends Pms_Form
{
	public function InsertData($post) {
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$q = Doctrine_Query::create()
		->delete('UserDefaultPermissions')
		->where('user_id= ?', $_REQUEST['id'])
		->andWhere('clientid= ?', $clientid)
		->andWhere('menu_id > 0');
		$q->execute();

		foreach ($post['hdnmoduleid'] as $tabid) {
			if ($post['canview'][$tabid] == '1' || $post['canedit'][$tabid] == '1' || $post['canadd'][$tabid] == '1' || $post['candelete'][$tabid] == '1' ) {

				$user = new UserDefaultPermissions();
				$user->user_id = $_REQUEST['id'];
				$user->clientid = $clientid;
				$user->menu_id = $tabid;
				$user->canadd = $post['canadd'][$tabid];
				$user->canedit = $post['canedit'][$tabid];
				$user->canview = $post['canview'][$tabid];
				$user->candelete = $post['candelete'][$tabid];
				$user->save();
			}
		}
	}
	
	public function resetData($post) {
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$q = Doctrine_Query::create()
		->delete('UserDefaultPermissions')
		->where('user_id= ?', $_REQUEST['id'])
		->andWhere('clientid= ?', $clientid )
		->andWhere('menu_id > 0');
		$q->execute();
	
	}
	
}
?>