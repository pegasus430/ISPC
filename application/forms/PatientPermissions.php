<?php

require_once("Pms/Form.php");

class Application_Form_PatientPermissions extends Pms_Form {

	public function InsertData($post) {
		if (!empty($_REQUEST['user_id'])) {
			$q = Doctrine_Query::create()
			->delete('PatientPermissions')
			->where('userid= ?', $_REQUEST['user_id'])
			->andWhere('ipid= ?', $post['ipid'])
			->andWhere('pat_nav_id !=0');
			$q->execute();
		} else if (!empty($_REQUEST['group_id'])) {
			$q = Doctrine_Query::create()
			->delete('PatientGroupPermissions')
			->where('groupid= ?', $_REQUEST['group_id'])
			->andWhere('ipid= ?', $post['ipid'])
			->andWhere('pat_nav_id !=0');
			$q->execute();
		}

		foreach ($post['hiddmodid'] as $tabid) {
			if ($post['canedit'][$tabid] == 1 || $post['canview'][$tabid] == 1) {
				if(!empty($_REQUEST['user_id'])){
					$user = new PatientPermissions();
					$user->userid = $_REQUEST['user_id'];
					$user->clientid = $post['hiddclientid'];
					$user->ipid = $post['ipid'];
					$user->pat_nav_id = $tabid;
					$user->canadd = $post['canadd'][$tabid];
					$user->canedit = $post['canedit'][$tabid];
					$user->canview = $post['canview'][$tabid];
					$user->candelete = $post['candelete'][$tabid];
					$user->save();
				} else if(!empty($_REQUEST['group_id'])){

					$user = new PatientGroupPermissions();
					$user->groupid = $_REQUEST['group_id'];
					$user->clientid = $post['hiddclientid'];
					$user->ipid = $post['ipid'];
					$user->pat_nav_id = $tabid;
					$user->canadd = $post['canadd'][$tabid];
					$user->canedit = $post['canedit'][$tabid];
					$user->canview = $post['canview'][$tabid];
					$user->candelete = $post['candelete'][$tabid];
					$user->save();
				}
			}
		}
	}

	public function InsertMiscModulesData ( $post )
	{
		if (!empty($_REQUEST['user_id']))
		{
			$q = Doctrine_Query::create()
			->delete('PatientPermissions')
			->where('userid= ?', $_REQUEST['user_id'])
			->andWhere('ipid= ?', $post['ipid'])
			->andWhere('misc_id !=0');
			$q->execute();
		}
		else if (!empty($_REQUEST['group_id']))
		{
			$q = Doctrine_Query::create()
			->delete('PatientGroupPermissions')
			->where('groupid= ?', $_REQUEST['group_id'])
			->andWhere('ipid= ?', $post['ipid'])
			->andWhere('misc_id !=0');
			$q->execute();
		}

		foreach ($post['m_hiddmodid'] as $tabid)
		{
			if ($post['m_canedit'][$tabid] == 1 || $post['m_canview'][$tabid] == 1)
			{
				if (!empty($_REQUEST['user_id']))
				{
					$user = new PatientPermissions();
					$user->userid = $_REQUEST['user_id'];
					$user->clientid = $post['hiddclientid'];
					$user->ipid = $post['ipid'];
					$user->misc_id = $tabid;
					$user->canadd = $post['m_canadd'][$tabid];
					$user->canedit = $post['m_canedit'][$tabid];
					$user->canview = $post['m_canview'][$tabid];
					$user->candelete = $post['m_candelete'][$tabid];
					$user->save();
				}
				else if (!empty($_REQUEST['group_id']))
				{

					$user = new PatientGroupPermissions();
					$user->groupid = $_REQUEST['group_id'];
					$user->clientid = $post['hiddclientid'];
					$user->ipid = $post['ipid'];
					$user->misc_id = $tabid;
					$user->canadd = $post['m_canadd'][$tabid];
					$user->canedit = $post['m_canedit'][$tabid];
					$user->canview = $post['m_canview'][$tabid];
					$user->candelete = $post['m_candelete'][$tabid];
					$user->save();
				}
			}
		}
	}

	public function InsertCourseData($post) {
		if (!empty($_REQUEST['user_id'])) {
			$q = Doctrine_Query::create()
			->delete('UserPatientShortcuts')
			->where('userid= ?', $_REQUEST['user_id'])
			->andWhere('ipid= ?', $post['ipid']);
			$q->execute();
		} else if (!empty($_REQUEST['group_id'])) {
			$q = Doctrine_Query::create()
			->delete('GroupPatientShortcuts')
			->where('groupid= ?', $_REQUEST['group_id'])
			->andWhere('ipid= ?', $post['ipid']);
			$q->execute();
		}
		foreach ($post['hiddmodid'] as $tabid) {
			if ($post['canedit'][$tabid] == 1 || $post['canview'][$tabid] == 1) {

				if(!empty($_REQUEST['user_id'])){
					$user = new UserPatientShortcuts();
					$user->userid = $_REQUEST['user_id'];
					$user->ipid = $post['ipid'];
					$user->clientid = $post['hiddclientid'];
					$user->shortcutid = $tabid;
					$user->canadd = $post['canadd'][$tabid];
					$user->canedit = $post['canedit'][$tabid];
					$user->canview = $post['canview'][$tabid];
					$user->candelete = $post['candelete'][$tabid];
					$user->save();
				} else if(!empty($_REQUEST['group_id'])){

					$user = new GroupPatientShortcuts();
					$user->groupid = $_REQUEST['group_id'];
					$user->ipid = $post['ipid'];
					$user->clientid = $post['hiddclientid'];
					$user->shortcutid = $tabid;
					$user->canadd = $post['canadd'][$tabid];
					$user->canedit = $post['canedit'][$tabid];
					$user->canview = $post['canview'][$tabid];
					$user->candelete = $post['candelete'][$tabid];
					$user->save();
				}
			}
		}
	}
}

?>