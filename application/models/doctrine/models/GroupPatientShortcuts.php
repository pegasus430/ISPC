<?php

	Doctrine_Manager::getInstance()->bindComponent('GroupPatientShortcuts', 'IDAT');

	class GroupPatientShortcuts extends BaseGroupPatientShortcuts {

		public function getGroupShortcuts($groupid, $ipid, $permission = 'canview')
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;

			$course = Doctrine_Query::create()
				->select('*')
				->from('GroupPatientShortcuts')
				->where('groupid="' . $groupid . '"')
				->andWhere('ipid = "' . $ipid . '"')
				->andwhere('' . $permission . ' = 1');
			$csarr = $course->fetchArray();

			if($csarr)
			{
				return $csarr;
			}
		}

		public function getDocShortcutPrevileges()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;

			$userdata = Pms_CommonData::getUserData($userid);
			$groupid = $userdata[0]['groupid'];

			if($logininfo->usertype == "SA")
			{
				$course = Doctrine_Query::create()
					->select('*')
					->from('ShortcutPrevileges')
					->where('canedit=1 or canview=1')
					->andWhere('clientid=' . $logininfo->clientid);
			}
			else
			{
				$course = Doctrine_Query::create()
					->select('*')
					->from('ShortcutPrevileges')
					->where('canedit=1 or canview=1')
					->andWhere('clientid=' . $logininfo->clientid)
					->andWhere('groupid=' . $groupid);
			}
			$cs = $course->execute();

			if($cs)
			{
				$csarr = $cs->toArray();
				return $csarr;
			}
		}

	}

?>