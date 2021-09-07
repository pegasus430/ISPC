<?php

	Doctrine_Manager::getInstance()->bindComponent('UserPatientShortcuts', 'IDAT');

	class UserPatientShortcuts extends BaseUserPatientShortcuts {

		public function getUserShortcuts($userid, $ipid, $permission = 'canview')
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;


			$course = Doctrine_Query::create()
				->select('*')
				->from('UserPatientShortcuts')
				->where('userid="' . $userid . '"')
				->andWhere('ipid = "' . $ipid . '"')
				->andwhere('' . $permission . ' = 1');
			$csarr = $course->fetchArray();

			if($csarr)
			{
				return $csarr;
			}
			else
			{
				$user_det = User::getUserDetails($userid);
				$group_id = Usergroup::getMasterGroup($user_det[0]['groupid']);

				$csarr = GroupPatientShortcuts::getGroupShortcuts($group_id, $ipid, $permission);

				if($csarr)
				{
					return $csarr;
				}
				else
				{
					$csarr = GroupCourseDefaultPermissions::getShortcutsByGroupAndClient($group_id, $clientid, $permission);
					return $csarr;
				}
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