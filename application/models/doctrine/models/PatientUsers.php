<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientUsers', 'IDAT');

	class PatientUsers extends BasePatientUsers {

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

		/**
		 * @cla - if $userid does not belong to $logininfo->clientid ...
		 * ISPC-2482 Lore 22.11.2019    
		 * changed - so group visibility also checks for group
		 */
		public function getUserPatients($userid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$user_det = User::getUserDetails($userid);
			$group_id = Usergroup::getMasterGroup($user_det[0]['groupid']);
			set_time_limit(0);
			//$groupbypass = GroupDefaultVisibility::getClientVisibilityByGroup($group_id, $logininfo->clientid);// ISPC-2482 Lore 22.11.2019 
			$groupbypass = GroupDefaultVisibility::getClientVisibilityByGroup($group_id, $logininfo->clientid, $user_det[0]['groupid']);
			
			
			$showinfo = new Modules();
			$user2location = $showinfo->checkModulePrivileges("94", $logininfo->clientid);

			
			if($logininfo->usertype == 'SA' || $logininfo->usertype == 'CA' || $groupbypass)
			{
				//applies group default visibility admins bypass the permissions system all together
				$patuser = Doctrine_Query::create()
					->select('x.ipid')
					->from('EpidIpidMapping x')
					->where('x.clientid = ' . $logininfo->clientid);
				$return['bypass'] = true; //bypass check by group or admin
				$return['patients']['%'] = '%';
				$return['patients_str'] = $patuser->getDql();

				return $return;
			}
			else
			{
				if($user2location) // if  HEIMNETZ - Aufenthaltsort(94)  module is active 
				{
					/* ----- Get user associated locations ---------*/
					$user_locations = Users2Location::get_user_locations($logininfo->userid);

					$user_locations_str = '"X", ';
						if(!empty($user_locations)){
						foreach($user_locations as $user_id =>$locations){
							foreach($locations as $k=>$loc_id){
								$user_locations_array[] =$loc_id;
							}
						}
					}
	
					if(empty($user_locations_array)){
						$user_locations_array[] = "XXXXXX";
					}
					
					
					
					/* ----- Get active patients ---------*/
					$q = Doctrine_Query::create()
					->select('*')
					->from('EpidIpidMapping e INDEXBY e.ipid')
					->leftJoin('e.PatientMaster p')
					->where('e.clientid = '.$logininfo->clientid)
					->andWhere('p.isdelete = 0')
					->andWhere('p.isdischarged = 0')
					->andWhere('p.isstandby = 0')
					->andWhere('p.isstandbydelete = 0')
					->andWhere('p.isarchived = 0')
					->orderBy('e.ipid ASC');
					$patients = $q->fetchArray();
					
					foreach($patients as $ipidp => $p_data){
						$patient_ipids_array[] = $ipidp ;
					}
	
					if(empty($patient_ipids_array)){
						$patient_ipids_array[] = "XXXXXX";
					}
					
					/* ----- Get active patients with active location in user location---------*/
					$patloc = Doctrine_Query::create()
					->select('location_id,ipid')
					->from('PatientLocation')
					->where('isdelete="0"')
					->andWhereIn('ipid', $patient_ipids_array) // active patients
					->andWhereIn('location_id', $user_locations_array) // user locations
					->andWhere("valid_till='0000-00-00 00:00:00'")
					->orderBy('id DESC');
					$patients2user_location= $patloc->fetchArray();
					
					
					$patuser = Doctrine_Query::create()
					->select('*')
					->from('PatientUsers')
					->where('(userid ="' . $logininfo->userid . '" OR allowforall = 1)  and isdelete="0" and clientid=' . $logininfo->clientid)
					->orderBy('id ASC');
					$patuserarray = $patuser->fetchArray();
					
					
				}
				else
				{				
					$patuser = Doctrine_Query::create()
						->select('*')
						->from('PatientUsers')
						->where('(userid ="' . $logininfo->userid . '" OR allowforall = 1)  and isdelete="0" and clientid=' . $logininfo->clientid)
						->orderBy('id ASC');
					$patuserarray = $patuser->fetchArray();
	
					
					$grpuser = Doctrine_Query::create()
						->select('*')
						->from('PatientGroups')
						->where('groupid ="' . $group_id . '" and isdelete="0" and clientid=' . $logininfo->clientid)
						->orderBy('id ASC');
					$grpuserarray = $grpuser->fetchArray();
					
				}
			}

			if (empty($patients2user_location)) {
			    $patients2user_location[99999] = 'X'; //force to return nothing
			}
			if (empty($patuserarray)) {
			    $patuserarray[99999] = 'X'; //force to return nothing
			}

			
			if($patuserarray)
			{
				if($user2location) // if  HEIMNETZ - Aufenthaltsort(94)  module is active 
				{
					
					foreach($patuserarray as $pat)
					{
						$return['patients'][$pat['ipid']] = $pat['ipid'];
						$return['patients_str'] .= "'" . $pat['ipid'] . "',";
					}
										
					foreach($patients2user_location as $pat_data)
					{
						$return['patients'][$pat_data['ipid']] = $pat_data['ipid'];
						$return['patients_str'] .= "'" . $pat_data['ipid'] . "',";
					}
				} 
				else 
				{
					foreach($patuserarray as $pat)
					{
						$return['patients'][$pat['ipid']] = $pat['ipid'];
						$return['patients_str'] .= "'" . $pat['ipid'] . "',";
					}
	
					foreach($grpuserarray as $pat)
					{
						$return['patients'][$pat['ipid']] = $pat['ipid'];
						$return['patients_str'] .= "'" . $pat['ipid'] . "',";
					}
					
				}
				
				$return['patients_str'] = substr($return['patients_str'], 0, -1);
				$return['bypass'] = false;

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

		public function checkKeyUserPatient($userid, $ipid = '')
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($logininfo->usertype == 'SA')
			{
				return true;
			}
			else
			{

				return false; // there should be no key user bu super admin
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
		
		/**
		 * ISPC-2561 Carmen 13.03.2020
		 * get user patients from all the clients the user logged can connect
		 */
		public function getUserPatientsforsearch($userid, $concl, $patients, $mgroupid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			set_time_limit(0);		
			
			$showinfo = new Modules();
			$user2location = $showinfo->checkModulePrivileges("94", $concl);
			
			
			if($user2location) // if  HEIMNETZ - Aufenthaltsort(94)  module is active
			{
				/* ----- Get user associated locations ---------*/
				$user_locations = Users2Location::get_user_locations($userid);

				if(!empty($user_locations)){
					foreach($user_locations as $user_id =>$locations){
						foreach($locations as $k=>$loc_id){
							$user_locations_array[] =$loc_id;
						}
					}
				}
	
				
				foreach($patients as $p_data){
					$patient_ipids_array[] = $p_data['ipid'] ;
				}
	
				if(!empty($user_locations_array) && !empty($patient_ipids_array)){
				
				/* ----- Get active patients with active location in user location---------*/
				$patloc = Doctrine_Query::create()
				->select('location_id,ipid')
				->from('PatientLocation')
				->where('isdelete="0"')
				->andWhereIn('ipid', $patient_ipids_array) // active patients
				->andWhereIn('location_id', $user_locations_array) // user locations
				->andWhere("valid_till='0000-00-00 00:00:00'")
				->orderBy('id DESC');
				$patients2user_location= $patloc->fetchArray();
					
				}
				$patuser = Doctrine_Query::create()
				->select('*')
				->from('PatientUsers');
				//->where('(userid ="' . $logininfo->userid . '" OR allowforall = 1)  and isdelete="0" and clientid=' . $logininfo->clientid)
				$patuser->where('userid = ? OR allowforall = ?', array($userid, '1'));
				$patuser->andWhere('isdelete="0"');
				$patuser->andWhere(' clientid = ?', $concl);
				$patuser->orderBy('id ASC');
				$patuserarray = $patuser->fetchArray();
					
					
			}
			else
			{
				$patuser = Doctrine_Query::create()
				->select('*')
				->from('PatientUsers');
				//->where('(userid ="' . $logininfo->userid . '" OR allowforall = 1)  and isdelete="0" and clientid=' . $logininfo->clientid)
				$patuser->where('userid = ? OR allowforall = ?', array($userid, '1'));
				$patuser->andWhere('isdelete="0"');
				$patuser->andWhere('clientid = ?', $concl);
				$patuser->orderBy('id ASC');
				$patuserarray = $patuser->fetchArray();
				
				$grpuser = Doctrine_Query::create()
				->select('*')
				->from('PatientGroups')
				->where('groupid = ?', $mgroupid )
				->andWhere('isdelete="0"')
				->andWhere('clientid = ?', $concl)
				->orderBy('id ASC');
				$grpuserarray = $grpuser->fetchArray();
				
			}
		
			if($patuserarray || $grpuserarray)
			{
				if($user2location) // if  HEIMNETZ - Aufenthaltsort(94)  module is active
				{
						
					foreach($patuserarray as $pat)
					{
						$return['patients'][$pat['ipid']] = $pat['ipid'];
						$return['patients_str'] .= "'" . $pat['ipid'] . "',";
					}
		
					foreach($patients2user_location as $pat_data)
					{
						$return['patients'][$pat_data['ipid']] = $pat_data['ipid'];
						$return['patients_str'] .= "'" . $pat_data['ipid'] . "',";
					}
				}
				else
				{
					foreach($patuserarray as $pat)
					{
						$return['patients'][$pat['ipid']] = $pat['ipid'];
						$return['patients_str'] .= "'" . $pat['ipid'] . "',";
					}
		
					foreach($grpuserarray as $pat)
					{
						$return['patients'][$pat['ipid']] = $pat['ipid'];
						$return['patients_str'] .= "'" . $pat['ipid'] . "',";
					}
				
				}
		
				$return['patients_str'] = substr($return['patients_str'], 0, -1);
				$return['bypass'] = false;
			
				return $return;
			}
			else
			{
				return false;
			}
		}

		
		/**
		 * @author Ancuta
		 * ISPC-2561 11.05.2020
		 * @param unknown $userid
		 * @param unknown $clientid
		 * @return unknown|boolean
		 */
		public function getUserPatientsConnected($userid = 0,$clientid = 0)
		{
		    if(empty($userid) || empty($clientid) ){
		        return  false;
		    }
		    
		    $user_det = User::getUserDetails($userid);
		    $group_id = Usergroup::getMasterGroup($user_det[0]['groupid']);
		    set_time_limit(0);
		    $groupbypass = GroupDefaultVisibility::getClientVisibilityByGroup($group_id, $clientid, $user_det[0]['groupid']);
		    
		    $showinfo = new Modules();
		    $user2location = $showinfo->checkModulePrivileges("94", $clientid);
		    
	
		    if($user_det[0]['usertype'] == 'SA' || $user_det[0]['usertype'] == 'CA' || $groupbypass)
		    {
		        //applies group default visibility admins bypass the permissions system all together
		        $patuser = Doctrine_Query::create()
		        ->select('ipid')
		        ->from('EpidIpidMapping')
		        ->where('clientid = ' . $clientid);
		        $return['bypass'] = true; //bypass check by group or admin
		        $return['patients']['%'] = '%';
		        $return['patients_str'] = $patuser->getDql();
		        $return['clientid'] = $clientid;
		        
		        return $return;
		    }
		    else
		    {
		        
		   
		        if($user2location) // if  HEIMNETZ - Aufenthaltsort(94)  module is active
		        {
		            /* ----- Get user associated locations ---------*/
		            $user_locations = Users2Location::get_user_locations($userid);
		    
		            $user_locations_str = '"X", ';
		            if(!empty($user_locations)){
		                foreach($user_locations as $user_id =>$locations){
		                    foreach($locations as $k=>$loc_id){
		                        $user_locations_array[] =$loc_id;
		                    }
		                }
		            }
		            
		            if(empty($user_locations_array)){
		                $user_locations_array[] = "XXXXXX";
		            }
		            
		            
		            
		            /* ----- Get active patients ---------*/
		            $q = Doctrine_Query::create()
		            ->select('*')
		            ->from('EpidIpidMapping e INDEXBY e.ipid')
		            ->leftJoin('e.PatientMaster p')
		            ->where('e.clientid = '.$clientid)
		            ->andWhere('p.isdelete = 0')
		            ->andWhere('p.isdischarged = 0')
		            ->andWhere('p.isstandby = 0')
		            ->andWhere('p.isstandbydelete = 0')
		            ->andWhere('p.isarchived = 0')
		            ->orderBy('e.ipid ASC');
		            $patients = $q->fetchArray();
		            
		            foreach($patients as $ipidp => $p_data){
		                $patient_ipids_array[] = $ipidp ;
		            }
		            
		            if(empty($patient_ipids_array)){
		                $patient_ipids_array[] = "XXXXXX";
		            }
		            
		            /* ----- Get active patients with active location in user location---------*/
		            $patloc = Doctrine_Query::create()
		            ->select('location_id,ipid')
		            ->from('PatientLocation')
		            ->where('isdelete="0"')
		            ->andWhereIn('ipid', $patient_ipids_array) // active patients
		            ->andWhereIn('location_id', $user_locations_array) // user locations
		            ->andWhere("valid_till='0000-00-00 00:00:00'")
		            ->orderBy('id DESC');
		            $patients2user_location= $patloc->fetchArray();
		            
		            
		            $patuser = Doctrine_Query::create()
		            ->select('*')
		            ->from('PatientUsers')
		            ->where('(userid ="' . $userid . '" OR allowforall = 1)  and isdelete="0" and clientid=' . $clientid)
		            ->orderBy('id ASC');
		            $patuserarray = $patuser->fetchArray();
		            
		            
		        }
		        else
		        {
		            $patuser = Doctrine_Query::create()
		            ->select('*')
		            ->from('PatientUsers')
		            ->where('(userid ="' . $userid . '" OR allowforall = 1)  and isdelete="0" and clientid=' . $clientid)
		            ->orderBy('id ASC');
		            $patuserarray = $patuser->fetchArray();
		            
		            
		            $grpuser = Doctrine_Query::create()
		            ->select('*')
		            ->from('PatientGroups')
		            ->where('groupid ="' . $group_id . '" and isdelete="0" and clientid=' . $clientid)
		            ->orderBy('id ASC');
		            $grpuserarray = $grpuser->fetchArray();
		            
		        }
		    }
		    
		    if (empty($patients2user_location)) {
		        $patients2user_location[99999] = 'X'; //force to return nothing
		    }
		    if (empty($patuserarray)) {
		        $patuserarray[99999] = 'X'; //force to return nothing
		    }
 
		    
		    if($patuserarray)
		    {
		        if($user2location) // if  HEIMNETZ - Aufenthaltsort(94)  module is active
		        {
		            
		            foreach($patuserarray as $pat)
		            {
		                $return['patients'][$pat['ipid']] = $pat['ipid'];
		                $return['patients_str'] .= "'" . $pat['ipid'] . "',";
		            }
		            
		          
		            foreach($patients2user_location as $pat_data)
		            {
		                $return['patients'][$pat_data['ipid']] = $pat_data['ipid'];
		                $return['patients_str'] .= "'" . $pat_data['ipid'] . "',";
		            }
		        }
		        else
		        {
		            foreach($patuserarray as $pat)
		            {
		                $return['patients'][$pat['ipid']] = $pat['ipid'];
		                $return['patients_str'] .= "'" . $pat['ipid'] . "',";
		            }
		            
		            foreach($grpuserarray as $pat)
		            {
		                $return['patients'][$pat['ipid']] = $pat['ipid'];
		                $return['patients_str'] .= "'" . $pat['ipid'] . "',";
		            }
		            
		        }
		        
		        $return['patients_str'] = substr($return['patients_str'], 0, -1);
		        $return['bypass'] = false;
		        
		        return $return;
		    }
		    else
		    {
		        return false;
		    }
		}
		
	}

?>