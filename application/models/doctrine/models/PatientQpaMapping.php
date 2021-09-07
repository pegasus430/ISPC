<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientQpaMapping', 'IDAT');

	class PatientQpaMapping extends BasePatientQpaMapping {

		public $triggerformid = 13;
		public $triggerformname = "frmasignusertopatient";

		public function getPatientClientData($epid)
		{
			$fdoc = Doctrine_Query::create()
				->select("*")
				->from('PatientQpaMapping')
				->where("epid=?", $epid);
			$loc = $fdoc->execute();

			if($loc)
			{
				$epa = $loc->toArray();
				return $epa;
			}
		}

		public function getuserClientData($clientid)
		{
			$fdoc = Doctrine_Query::create()
				->select("distinct(userid)")
				->from('PatientQpaMapping')
				->where("clientid=?", $clientid);
			$loc = $fdoc->execute();

			if($loc)
			{
				$epa = $loc->toArray();
				return $epa;
			}
		}

		public function getPatientAssignedDoctorsData($epids, $clientid, $returnType = false)
		{
			$fdoc = Doctrine_Query::create()
				->select("*")
				->from('PatientQpaMapping')
				->whereIn("epid", $epids)
				->andWhere('clientid=?', $clientid)
				->andWhere('epid!=""');
			$docassigned = $fdoc->fetchArray();

			//$docidsdata[] = "99999999";
			$docidsdata = array();
			foreach($docassigned as $kdocassigned => $vdocassigned)
			{
				$patientDocIds[$vdocassigned['epid']][] = $vdocassigned['userid'];
				$docidsdata[$vdocassigned['userid']] = $vdocassigned['userid'];
			}
			$finalArrayPatientDocAssigned = array();
			if($returnType != false && $returnType == "ids")
			{
				//returns an array with doctor ids only
				foreach($patientDocIds as $kpat => $vpat)
				{
					$finalArrayPatientDocAssigned[$kpat][$vpat] = $vpat;
				}
			}
			else if($returnType != false && $returnType == "names")
			{
				//returns an array with doctor names only
				$usersArray = array();
				if(count($docidsdata) == 0)
				{
					$users = Doctrine_Query::create()
						->select("*")
						->from('User')
						->whereIn("id", $docidsdata)
						->andWhere('clientid=?', $clientid)
						->orderBy('last_name ASC');
					$usersArray = $users->fetchArray();
				}

				foreach($usersArray as $user)
				{
					$usersData[$user['id']] = $user['last_name'] . " " . $user['first_name'];
				}

				foreach($patientDocIds as $pepid => $patientData)
				{

					$cnt = count($patientData);
					for($i = 0; $i <= $cnt; $i++)
					{
						$finalArrayPatientDocAssigned[$pepid][] = $usersData[$patientData[$i]];
					}
				}
			}

			return $finalArrayPatientDocAssigned;
		}

		/**
		 * changed the fn to leftjoin and removed the 9999
		 * Jul 18, 2017 @claudiu 
		 * 
		 * @param array $ipids
		 * @param string $users
		 * @return multitype:|unknown
		 */
		public function getAssignedUsers($ipids = array(), $users = false)
		{
			$result = array();
			
			if( ! is_array($ipids) || count($ipids) == 0 ){
				return $result;
			}
			
			$q = $this->getTable()->createQuery("pqm")
			->select('pqm.* , eim.*')
			->leftJoin("pqm.EpidIpidMapping eim")
			->whereIn('eim.ipid', $ipids);
			
			if($users !== false && is_array($users) && ! empty($users))
			{
				$q->andWhereIn('pqm.userid', $users);
			}
			
			$qr = $q->fetchArray();

			foreach($qr as $row)
			{
				$result['assignments'][ $row['EpidIpidMapping']['ipid'] ][$row['userid']] = $row['userid'];
			}
			
			return $result;
			
		}
		
		public function getAssignedUsers_OLD($ipids, $users = false)
		{
			if(!is_array($ipids) || count($ipids) == 0 ){
				return array();
			}
			$epid_ipid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidMapping')
				->whereIn('ipid', $ipids)
				->andWhere('epid IS NOT NULL');
			$epid_ipid_res = $epid_ipid->fetchArray();

			//$epids[] = '999999999';
			$epids = array();
			foreach($epid_ipid_res as $k_epid => $v_epid)
			{
				$epid2ipid[$v_epid['epid']] = $v_epid['ipid'];
				$epids[] = $v_epid['epid'];
			}
			$qpa_map_res = array();
			if(!empty($epids))
			{
				$qpa_map = Doctrine_Query::create()
					->select('*')
					->from('PatientQpaMapping')
					->whereIn('epid', $epids);
				//used to get for specific users
				if($users)
				{
				$qpa_map->andWhereIn('userid', $users);
				}
				$qpa_map_res = $qpa_map->fetchArray();
			}
			
			$master_data = array();
			foreach($qpa_map_res as $k_qmap => $v_qmap)
			{
				$master_data['assignments'][$epid2ipid[$v_qmap['epid']]][$v_qmap['userid']] = $v_qmap['userid'];
			}

			return $master_data;
		}

		public function get_patient_assigned_doctors($epids, $clientid, $returnType = false)
		{
			if(empty($epids))
			{
				//$epids[] = 'XXX';
				return array();
			}
			$patient = Doctrine_Query::create()
				->select("*")
				->from('PatientQpaMapping')
				->whereIn("epid", $epids)
				->andWhere('clientid=?', $clientid)
				->andWhere('epid!=""');
			$assigned_doctors_array = $patient->fetchArray();

			foreach($assigned_doctors_array as $kass => $vass)
			{
				$epid2docs[$vass['epid']][] = $vass['userid'];
				$doc_ids[$vass['userid']] = $vass['userid'];
			}
			$epids2docs_final = array();
			if($returnType != false && $returnType == "ids")
			{
				//returns an array with doctor ids only
				foreach($epid2docs as $kpat => $vpat)
				{
					$epids2docs_final[$kpat][$vpat] = $vpat;
				}
			}
			else if($returnType != false && $returnType == "names")
			{
				//returns an array with doctor names only
				if(empty($doc_ids))
				{
					//$doc_ids[] = "99999999";
					return array();
				}

				$users = Doctrine_Query::create()
					->select("*")
					->from('User')
					->whereIn("id", $doc_ids)
					->andWhere('clientid="' . $clientid . '"')
					->orderBy('last_name ASC');
				$users_array = $users->fetchArray();

				foreach($users_array as $user)
				{
					if($user['user_title'])
					{
						$users_data[$user['id']] = $user['user_title'] ." ". $user['last_name'].", ".$user['first_name'];
					}
					else 
					{
						$users_data[$user['id']] = $user['last_name'].", ".$user['first_name'];
					}
				}

				foreach($epid2docs as $pepid => $patient_data)
				{
					foreach($patient_data as $k => $userid)
					{
						$epids2docs_final[$pepid][] = $users_data[$userid];
					}
				}
			}
			else if($returnType != false && $returnType == "details")
			{
				//returns an array with doctor names only
				if(empty($doc_ids))
				{
					//$doc_ids[] = "99999999";
					return array();
				}

				$users = Doctrine_Query::create()
					->select("*")
					->from('User')
					->whereIn("id", $doc_ids)
					->andWhere('clientid="' . $clientid . '"')
					->orderBy('last_name ASC');
				$users_array = $users->fetchArray();

				foreach($users_array as $user)
				{
					$users_data[$user['id']] = $user;
				}

				foreach($epid2docs as $pepid => $patient_data)
				{
					foreach($patient_data as $k => $userid)
					{
						$epids2docs_final[$pepid][] = $users_data[$userid];
					}
				}
			}

			return $epids2docs_final;
		}

		
		public function get_patient_assigned_doctors_ps($epids, $clientid,$display = "tooltip")
		{
			if(empty($epids))
			{
				//$epids[] = 'XXX';
				return array();
			}
			$patient = Doctrine_Query::create()
				->select("*")
				->from('PatientQpaMapping')
				->whereIn("epid", $epids)
				->andWhere('clientid=?', $clientid)
				->andWhere('epid!=""');
			$assigned_doctors_array = $patient->fetchArray();

			foreach($assigned_doctors_array as $kass => $vass)
			{
				$epid2docs[$vass['epid']][] = $vass['userid'];
				$doc_ids[$vass['userid']] = $vass['userid'];
			}

			//returns an array with doctor names only
			if(empty($doc_ids))
			{
				//$doc_ids[] = "99999999";
				return array();
			}
			
			$users = Doctrine_Query::create()
			->select("*")
			->from('User')
			->whereIn("id", $doc_ids)
			->andWhere('clientid="' . $clientid . '"')
			->andWhere('isdelete = 0')
			->orderBy('last_name ASC');
			$users_array = $users->fetchArray();
			
			$inactive_users = array();
			foreach($users_array as $user)
			{
			    if($user['isactive'] =="0"){
			        
    			    if($user['user_title'])
    			    {
    			        $users_data[$user['id']] = $user['user_title'] ." ". $user['last_name'].", ".$user['first_name'];
    			    }
    			    else
    			    {
    			        $users_data[$user['id']] = $user['last_name'].", ".$user['first_name'];
    			    }
			    }
			    else
			    {
    			    $inactive_users[] = $user['id'];
			    }
			}
			
			// pseudo groups details
			$us_array = PseudoGroupUsers::get_usersgroup();
			
			foreach($us_array as $key_us => $val_us)
			{
			    $users2groups[$val_us['pseudo_id']][] = $val_us['user_id'];
			    $users2groups_str[$val_us['pseudo_id']] .= $users_data[$val_us['user_id']].';<br/>';
			    $user2group[$val_us['user_id']] = $val_us['pseudo_id'];
			}
			
			
			$all_groups = UserPseudoGroup::get_userpseudo();
			
			foreach($all_groups as $k=>$gr_data)
			{
			    if($display == "plane")
			    {
    			    $group[$gr_data['id']]['name'] = $gr_data['servicesname'];
			    } 
			    else
			    {
                    $group[$gr_data['id']]['name'] = '<span class="assigned_groups" id="'.$gr_data['id'].'" title="'.$users2groups_str[$gr_data['id']].'"><b>'.$gr_data['servicesname']."</b></span>";
			    }
			}
			
			
			$used_ug_overall = array();
			
		    foreach($epid2docs as $epid => $assigned_users)
			{
			    foreach($assigned_users as $ak => $a_user)
			    {
			        foreach($users2groups as $gr_id => $group_users)
			        {
			            if(in_array($a_user,$users2groups[$gr_id]))
			            {
                            $used[$epid]['groups'][$gr_id][] = $a_user; 
                            $used_ug_overall[$epid][] = $a_user; 
			            }
			        }
			    }
			}
			
			
			
			foreach($used as $ppepid=>$as_data)
			{
			    foreach($as_data['groups'] as $gid => $g_uids)
			    {
			        if(count($g_uids) == count($users2groups[$gid]) && count(array_intersect($users2groups[$gid],$g_uids)) == count($users2groups[$gid]))
			        {
			            $epids2docs_final[$ppepid][] = $group[$gid]['name'];
			        }
			        else
			        {
			            foreach($g_uids as $k => $uid){
    			            $epids2docs_final[$ppepid][] = $users_data[$uid];
    			            $ass_users_final[$ppepid][] = $uid;
			            }
			        }
			    }
			}
			
			foreach($epid2docs as $epid => $assigned_users)
			{
			    if(!empty($used_ug_overall[$epid]))
			    {
			        $left_users[$epid]  = array_diff($epid2docs[$epid], $used_ug_overall[$epid]);
			    }
			    else
			    {
			        $left_users[$epid]  = $assigned_users;
			    }

			    foreach($left_users[$epid] as $kid => $u_id_s)
			    {
			        if(!in_array($u_id_s, $ass_users_final[$epid]) && !in_array($u_id_s, $inactive_users))
			        {
			            $epids2docs_final[$epid][] = $users_data[$u_id_s];
			            $ass_users_final[$epid][] = $u_id_s;
			        }
			    }
			}
 
			return $epids2docs_final;
		}

		//assert userid is assigned for this epid
		static public function assert_epid2userid( $epid = '', $userid = 0)
		{
			if($epid == '' || $userid == 0 ) {
				
				return false;
			}
			$qpa_map = Doctrine_Query::create()
			->select('id')
			->from('PatientQpaMapping')
			->where('epid = ?', $epid)
			->limit(1)
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
			
			if ( ! empty($qpa_map) && isset($qpa_map['id']) ) {
				
				return true;
				
			} else {
				
				return false;
			}
		}
		
		
		//assert userid is assigned for this ipid
		static public function assert_ipid2userid( $ipid = '', $userid = 0)
		{
			if($ipid == '' || $userid == 0 ) {
				
				return false;
			}
			
			
			$epid_ipid = Doctrine_Query::create()
			->select('id, epid')
			->from('EpidIpidMapping')
			->where('ipid = ?', $ipid)
			->limit(1)
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);

			if ( empty($epid_ipid)) {
			
				return false;
			} 
			
			$epid = $epid_ipid['epid'];
			
			$qpa_map = Doctrine_Query::create()
			->select('id')
			->from('PatientQpaMapping')
			->where('epid = ?', $epid)
			->limit(1)
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
				
			if ( ! empty($qpa_map) && isset($qpa_map['id']) ) {
		
				return true;
		
			} else {
		
				return false;
			}
		}

		
		/**
		 * get userid of the treattedby from an array of ipids and|or epids
		 * 
		 * do NOT modify the current keys returned,
		 * add other keys to the returned value if you want to change the function
		 * please do NOT select (*), create another fn if you want to select all the columns
		 * $result['extend_fn_warning_key'] = " ids or other result format";
		 *  
		 * @param array $ipid_epid = array("ipid"=>array(), "epid"=>array());
		 * @return void|multitype:string = returns the same array style as the input (not by reference)
		 */
		public function get_assigned_userid( $ipid_epid = array("ipids"=>array(), "epids"=>array()) )
		{	
			
			if( (empty($ipid_epid['ipids']) && empty($ipid_epid['epids']))
				|| (! is_array($ipid_epid['ipids']) && ! is_array($ipid_epid['epids']) )
			) {
				return; // bad args
			}
			
			$all_epids = array();
			$epis_from_ipids = array();
			$result = array();
			
			if ( !empty($ipid_epid['ipids']) && is_array($ipid_epid['ipids'])) {
				$all_epids = $epis_from_ipids = EpidIpidMapping::getIpidsEpids($ipid_epid['ipids']);
			}
			
			if ( ! empty($ipid_epid['epids']) && is_array($ipid_epid['epids']) && is_array($epis_from_ipids)) {
				
				$ipid_epid['epids'] = array_map('strtoupper', $ipid_epid['epids']); // upper epids because EpidIpidMapping return them like that.. so preserve 
				$all_epids = array_merge($ipid_epid['epids'], $epis_from_ipids);
			}
			
			if( empty($all_epids) || ! is_array($all_epids) ) {
				return; // no epid
			}
					
			$qpa_map = Doctrine_Query::create()
			->select('id, UPPER(epid) as epid, userid')
			->from('PatientQpaMapping')
			->whereIn('UPPER(epid)', array_unique(array_values($all_epids)))
			->fetchArray();
						
			$flipped_all_epids = array_flip($all_epids); // after the array_merge ipids should be last so we can flip

			foreach($qpa_map as $row)
			{
				$result['epids'][ $row['epid'] ] [] = $row['userid'];
				
				//if only epids given as params, $result['ipids'] will be numeric (NO ipids_from_epids are fetched in this function)
				if (isset($flipped_all_epids[$row['epid']])) {
					$result['ipids'][ $flipped_all_epids[$row['epid']] ] [] = $row['userid'];
				}
				
			}
			
			return $result;
		}
	
		
		/**
		 * 
		 * Jul 10, 2017 @claudiu 
		 * taken from PatientUsers
		 * 
		 * @param number $userid
		 * @param string $epid
		 * @param string $assign_date
		 */
		public static function assignUserPatient($userid = 0, $epid = "", $assign_date = null)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			
			if(is_null($assign_date)) {
				$assign_date = date("Y-m-d H:i:s", time());
			}
			
			$patuser = new PatientQpaMapping();
			$patuser->epid = $epid;
			$patuser->userid = $userid;
			$patuser->clientid = $logininfo->clientid;
			$patuser->assign_date = $assign_date;
			$patuser->create_date = date('Y-m-d H:i:s');
			$patuser->create_user = $logininfo->userid;
			$patuser->save();
			
			return $patuser->id;
		}


		//Maria:: Migration CISPC to ISPC 22.07.2020
        public function getAssignedUsernames($ipids, $clientid){
            $users=User::getUsersWithGroupnameFast($clientid);
            if(!is_array($ipids)){
                $ipids=array($ipids);
            }
            $assu=$this->getAssignedUsers_OLD($ipids);
            $map=Array();
            foreach ($assu['assignments'] as $assipid=>$assusers){
                foreach ($assusers as $user) {
                    $map[$assipid][] = $users[$user];
                }
            }
            return $map;
        }
	}

?>