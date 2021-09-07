<?php

	Doctrine_Manager::getInstance()->bindComponent('ShortcutPrevileges', 'IDAT');

	class ShortcutPrevileges extends BaseShortcutPrevileges {

		public function getShortcutPrevileges($cid, $prev)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;

			$userdata = Pms_CommonData::getUserData($userid);
			$groupid = $userdata[0]['groupid'];

			$course = Doctrine_Query::create()
				->select('*')
				->from('ShortcutPrevileges')
				->where('groupid="' . $groupid . '"')
				->andwhere('' . $prev . ' = 1');
			$cs = $course->execute();


			if($cs)
			{
				$csarr = $cs->toArray();
				return $csarr;
			}
		}

		public function getBottomShortcutPrevileges()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;

			$userdata = Pms_CommonData::getUserData($userid);
			$groupid = $userdata[0]['groupid'];

			$course = Doctrine_Query::create()
				->select('*')
				->from('ShortcutPrevileges')
				->where('groupid="' . $groupid . '"')
				->andwhere("canedit=1 and canview=1");
			$cs = $course->execute();

			if($cs)
			{
				$csarr = $cs->toArray();
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