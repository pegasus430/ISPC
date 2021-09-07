<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientGroups', 'IDAT');

	class PatientGroups extends BasePatientGroups {

	    /**
	     * Function changed By ancuta - ISPC-ISPC-2482 Visibility permissions
	     * 14.12.2019 
	     * @param unknown $ipid
	     * @return unknown|array|unknown
	     */
		public function getPatientGroups($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$patgroup = Doctrine_Query::create()
				->select('*')
				->from('PatientGroups')
				->where('ipid ="' . $ipid . '"  and isdelete="0"')
				->orderBy('id ASC');
			$patgrouparray = $patgroup->fetchArray();

			$groups = false;
			if($patgrouparray)
			{
				foreach($patgrouparray as $group)
				{
// 					$pat_groups[$group['groupid']] = $group;
					$pat_groups[$group['master_groupid']][]= $group['groupid'];
				}
				return $pat_groups;
			}
			else
			{
				$def_groups = GroupDefaultVisibility::getClientVisibilityAll($clientid);

				if(is_array($pat_groups) && is_array($def_groups))
				{
					$groups = array_merge($pat_groups, $def_groups);
					$groups = array_unique($groups);
				}
				elseif(is_array($pat_groups))
				{
					$groups = $pat_groups;
				}
				elseif(is_array($def_groups))
				{
					$groups = $def_groups;
				}

				return $groups;
			}
		}

		/* ------------------------------------------------------- */

		public function getPatientUsers($ipid)
		{
			$patuser = Doctrine_Query::create()
				->select('*')
				->from('PatientUsers')
				->where('ipid ="' . $ipid . '"  and isdelete="0"')
				->orderBy('id ASC');
			$patuserarray = $patuser->fetchArray();

			if($patuserarray)
			{
				foreach($patuserarray as $user)
				{
					$users[$user['userid']] = $user;
				}
				return $users;
			}
		}

		public function getPatientKeyUser($ipid)
		{
			$patuser = Doctrine_Query::create()
				->select('*')
				->from('PatientUsers')
				->where('ipid ="' . $ipid . '"  and isdelete="0" and iskeyuser="1"')
				->orderBy('id ASC');
			$patuserarray = $patuser->fetchArray();

			if($patuserarray)
			{
				foreach($patuserarray as $user)
				{
					$users[$user['userid']] = $user;
				}
				return $users;
			}
			else
			{
				return false;
			}
		}

		public function getUserPatients($userid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			//admins bypass the permissions system all together
			if($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA')
			{
				$patuser = Doctrine_Query::create()
					->select('*')
					->from('PatientMaster p');
				$patuser->leftJoin("p.EpidIpidMapping e");
				$patuser->andWhere('e.clientid = ' . $logininfo->clientid);
			}
			else
			{
				$patuser = Doctrine_Query::create()
					->select('*')
					->from('PatientUsers')
					->where('(userid ="' . $logininfo->userid . '" OR allowforall = 1)  and isdelete="0" and clientid=' . $logininfo->clientid)
					->orderBy('id ASC');
			}
			$patuserarray = $patuser->fetchArray();
			$patuserarray[99999] = 'X'; //force to return nothing 
			if($patuserarray)
			{
				foreach($patuserarray as $pat)
				{
					$return['patients'][$pat['ipid']] = $pat['ipid'];
					$return['patients_str'] .= "'" . $pat['ipid'] . "',";
				}
				$return['patients_str'] = substr($return['patients_str'], 0, -1);
				return $return;
			}
			else
			{
				return false;
			}
		}

		public function checkUserPatient($userid, $ipid)
		{
			$patuser = Doctrine_Query::create()
				->select('*')
				->from('PatientUsers')
				->where('(userid ="' . $userid . '" OR allowforall = 1)  and isdelete="0" and ipid="' . $ipid . '"')
				->orderBy('id ASC');
			$patuserarray = $patuser->toArray();

			if($patuserarray)
			{
				return $patuserarray;
			}
			else
			{
				return $this->checkCreateUserPatient($userid, $ipid);
			}
		}

		public function assignUserPatient($userid, $ipid, $iskeyuser = 0)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$patuser = new PatientUsers();
			$patuser->clientid = $logininfo->clientid;
			$patuser->ipid = $ipid;
			$patuser->userid = $userid;
			$patuser->iskeyuser = $iskeyuser;
			$patuser->create_date = date('Y-m-d H:i:s');
			$patuser->create_user = $logininfo->userid;
			$patuser->save();
		}

		public function checkKeyUserPatient($userid, $ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($logininfo->usertype == 'CA' || $logininfo->usertype == 'SA')
			{
				return true;
			}
			else
			{
				$patuser = Doctrine_Query::create()
					->select('*')
					->from('PatientUsers')
					->where('userid ="' . $userid . '" and iskeyuser="1" and isdelete="0" and ipid="' . $ipid . '"')
					->orderBy('id ASC');
				$patuserarray = $patuser->fetchArray();

				if($patuserarray)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}

		public function checkAllowforallPatient($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$patuser = Doctrine_Query::create()
				->select('*')
				->from('PatientUsers')
				->where('allowforall="1" and isdelete="0" and ipid="' . $ipid . '"')
				->orderBy('id ASC');
			$patuserarray = $patuser->fetchArray();

			if($patuserarray)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function checkCreateUserPatient($userid, $ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$patuser = Doctrine_Query::create()
				->select('*')
				->from('PatientMaster')
				->where('create_user ="' . $userid . '" and ipid="' . $ipid . '"')
				->orderBy('id ASC');
			$patuserarray = $patuser->fetchArray();

			if($patuserarray)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

	}

?>