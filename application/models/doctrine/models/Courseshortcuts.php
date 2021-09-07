<?php

	Doctrine_Manager::getInstance()->bindComponent('Courseshortcuts', 'SYSDAT');

	class Courseshortcuts extends BaseCourseshortcuts {

		public function getCourseData()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			//get patient ipid for shorcuts rights
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$setclientid = 'clientid="' . $clientid . '"';
			$privileges = UserPatientShortcuts::getUserShortcuts($userid, $ipid, 'canview');
			
			$comma = ",";
			$ipidval = "'0'";
			foreach($privileges as $key => $val)
			{
				$ipidval .= $comma . "'" . $val['shortcutid'] . "'";
				$comma = ",";
			}
			$ipcond = 'shortcut_id in (' . $ipidval . ')';

			$iskeyuser = PatientUsers::checkKeyUserPatient($userid, $ipid);

			if($logininfo->usertype == "SA" || $logininfo->usertype == "CA" || $iskeyuser)
			{
				$ipcond = 1;
			}

			$course = Doctrine_Query::create()
				->select('*')
				->from('Courseshortcuts')
				->where('isdelete=0')
				->andWhere($ipcond)
				->andWhere($setclientid)
				->orderBy("shortcut");
			$courseq = $course->execute();
			$coursearray = $courseq->toarray();

			return $coursearray;
		}

		public function getFilterCourseDataOLD($purpose = 'canview', $filter = false,$client = false)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			//get patient ipid for shorcuts rights
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$setclientid = 'clientid="' . $clientid . '"';
			
			if($client) {
    			$setclientid = 'clientid="' . $client . '"';
			} else {
                $setclientid = 'clientid="' . $clientid . '"';
			}
			
			
			$privileges = UserPatientShortcuts::getUserShortcuts($userid, $ipid, $purpose);


			$comma = ",";
			$ipidval = "'0'";


			foreach($privileges as $key => $val)
			{
				$ipidval .= $comma . "'" . $val['shortcutid'] . "'";
				$comma = ",";
			}
			$ipcond = 'shortcut_id in (' . $ipidval . ')';

			$iskeyuser = PatientUsers::checkKeyUserPatient($userid, $ipid);

			if($logininfo->usertype == "SA" || $logininfo->usertype == "CA" || $iskeyuser)
			{
				$ipcond = 1;
			}

			if($filter !== false)
			{
				$setclientid .= ' AND isfilter = 1';
			}

			$course = Doctrine_Query::create()
				->select('*')
				->from('Courseshortcuts')
				->where('isdelete=0')
				->andWhere($ipcond)
				->andWhere($setclientid);
			$coursearray = $course->fetchArray();

			return $coursearray;
		}
		
		
		public function getFilterCourseData($purpose = 'canview', $filter = false,$client = false)
		{
		
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
		
			//get patient ipid for shorcuts rights
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
				
			$setclientid = $clientid;
				
			$ipid_sh_val_arr = array();
				
			if($client)
			{
				$setclientid = $client;
			}
			else
			{
				$setclientid = $clientid;
		
				$iskeyuser = PatientUsers::checkKeyUserPatient($userid, $ipid);
				if($logininfo->usertype == "SA" || $logininfo->usertype == "CA" || $iskeyuser)
				{
					// do nothing - get shortcuts per client
				}
				else
				{
					$privileges = UserPatientShortcuts::getUserShortcuts($userid, $ipid, $purpose);
					foreach($privileges as $key => $val)
					{
						$ipid_sh_val_arr[] = $val['shortcutid'];
					}
				}
			}
		
			$course = Doctrine_Query::create()
			->select('*')
			->from('Courseshortcuts')
			->where('isdelete=0');
			if(!empty($ipid_sh_val_arr)){
				$course->andWhereIn('shortcut_id',$ipid_sh_val_arr);
			}
			$course ->andWhere('clientid = ?',$setclientid);
		
			if($filter !== false)
			{
				$course ->andWhere('isfilter = 1');
			}
			$coursearray = $course->fetchArray();
		
			return $coursearray;
		}		

		//@claudiu re-write
		public function getCourseDataByShortcut($short)
		{
		    if (empty($short)) {
		        return;
		    }
		
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		
		    $course = $this->getTable()->createQuery()
		    ->select('*')
		    ->where('isdelete=0')
		    ->andWhere("clientid = ?", $clientid);
		    
		    if (is_array($short)) {    
		        $course->andWhereIn("shortcut", $short);
		    } else {
		        $course->andWhere("shortcut= ?", $short);
		    }
		
		    return ($course->fetchArray());
		} 
		
		/*
		public function getCourseDataByShortcut($short)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			if($logininfo->usertype == "SA" || $logininfo->usertype == "CA")
			{
				$clientid = $logininfo->clientid;
				$ipcond = 1;
			}
			else
			{
				$clientid = $logininfo->clientid;
				$sp = new ShortcutPrevileges();
				$previleges = $sp->getShortcutPrevileges($clientid, 'canedit');
				$comma = ",";
				$ipidval = "'0'";

				foreach($previleges as $key => $val)
				{
					$ipidval .= $comma . "'" . $val['shortcutid'] . "'";
					$comma = ",";
				}
				$ipcond = 'shortcut_id in (' . $ipidval . ')';
			}

			$course = Doctrine_Query::create()
				->select('*')
				->from('Courseshortcuts')
				->where('isdelete=0')
				->andWhere("shortcut='" . $short . "'")
				->andWhere("clientid='" . $clientid . "'");
			$coursearr = $course->fetchArray();

			return($coursearr);
		}
		*/

		public function getCourseMultipleDataByShortcut($shorts = array())
		{

		    if (empty($shorts) || ! is_array($shorts)) {
		        return;
		    }
		    
		    $shorts = array_values(array_unique($shorts));
		    
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$course = Doctrine_Query::create()
				->select('*')
				->from('Courseshortcuts')
				->where('isdelete=0')
				->andWhereIn('shortcut', $shorts)
				->andWhere("clientid= ?", $clientid);
			$coursearr = $course->fetchArray();

			$coursearray = array();
			foreach($coursearr as $course)
			{
				$coursearray[$course['shortcut']] = $course;
			}

			return($coursearray);
		}

		public function getCourseDataById($id)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$course = Doctrine_Query::create()
				->select('*')
				->from('Courseshortcuts')
				->where('isdelete=0')
				->andWhere("shortcut_id='" . $id . "'")
				->andWhere("clientid='" . $clientid . "'");
			$crs = $course->execute();
			$coursearr = $crs->toArray();
			return($coursearr);
		}

		public function getShortcuts($shrct)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$course = Doctrine_Query::create()
				->select('*')
				->from('Courseshortcuts')
				->where('isdelete=0')
				->andWhere('shortcut_id in (' . $shrct . ')');
			$crs = $course->execute();
			$coursearr = $crs->toArray();
			return($coursearr);
		}

		public function getClientShortcuts($clientid)
		{
			$course = Doctrine_Query::create()
				->select('*, shortcut_id as shortcutid')
				->from('Courseshortcuts')
				->where('isdelete=0')
				->andWhere('clientid = "' . $clientid . '"');
			//print_r($course->getSqlQuery());
			$crs = $course->execute();
			$coursearr = $crs->toArray();
			return($coursearr);
		}

		public function getShortcutsMultiple($shortcut_ids)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$course = Doctrine_Query::create()
				->select('*')
				->from('Courseshortcuts')
				->where('isdelete=0')
				->andWhereIn('shortcut_id', $shortcut_ids);
			$coursearr = $course->fetchArray();

			foreach($coursearr as $course)
			{
				$coursearray[] = $course['shortcut'];
			}

			if(count($coursearray) == 0)
			{
				$coursearray[] = '999999999';
			}

			return $coursearray;
		}

		public function getShortcutIdByLetter($shortcut, $client)
		{
			$course = Doctrine_Query::create()
				->select('*')
				->from('Courseshortcuts')
				->where('isdelete=0')
				->andWhere('shortcut = "' . $shortcut . '"')
				->andWhere('clientid="' . $client . '"')
				->fetchArray();

			if($course)
			{
				return $course[0]['shortcut_id'];
			}
			else
			{
				return false;
			}
		}
	}

?>