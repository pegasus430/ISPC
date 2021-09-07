<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientQpaLeading', 'IDAT');

	class PatientQpaLeading extends BasePatientQpaLeading {

		public function get_current_leading_users($ipid)
		{
			$fdoc = Doctrine_Query::create()
				->select("*")
				->from('PatientQpaLeading')
				->where("ipid=?", $ipid)
				->andWhere("isdelete = 0")
				->andWhere('end_date="0000-00-00 00:00:00"');
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
				->from('PatientQpaLeading')
				->where("clientid='" . $clientid . "'");
			$loc = $fdoc->execute();

			if($loc)
			{
				$epa = $loc->toArray();
				return $epa;
			}
		}

		public function getPatientAssignedDoctorsData($ipids, $clientid, $returnType = false)
		{
			$fdoc = Doctrine_Query::create()
				->select("*")
				->from('PatientQpaLeading')
				->whereIn("ipid", $ipids)
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('ipid!=""');
			$docassigned = $fdoc->fetchArray();

			$docidsdata[] = "99999999";
			foreach($docassigned as $kdocassigned => $vdocassigned)
			{
				$patientDocIds[$vdocassigned['ipid']][] = $vdocassigned['userid'];
				$docidsdata[$vdocassigned['userid']] = $vdocassigned['userid'];
			}

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
				$users = Doctrine_Query::create()
					->select("*")
					->from('User')
					->whereIn("id", $docidsdata)
					->andWhere('clientid="' . $clientid . '"')
					->orderBy('last_name ASC');
				$usersArray = $users->fetchArray();

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

		public function getAssignedUsers($ipids, $users = false)
		{
			$epid_ipid = Doctrine_Query::create()
				->select('*')
				->from('EpidIpidLeading')
				->whereIn('ipid', $ipids)
				->andWhere('ipid IS NOT NULL');
			$epid_ipid_res = $epid_ipid->fetchArray();

			$epids[] = '999999999';
			foreach($epid_ipid_res as $k_epid => $v_epid)
			{
				$epid2ipid[$v_epid['epid']] = $v_epid['ipid'];
				$epids[] = $v_epid['epid'];
			}

			$qpa_map = Doctrine_Query::create()
				->select('*')
				->from('PatientQpaLeading')
				->whereIn('epid', $epids);
			//used to get for specific users
			if($users)
			{
				$qpa_map->andWhereIn('userid', $users);
			}
			$qpa_map_res = $qpa_map->fetchArray();

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
				$epids[] = 'XXX';
			}
			$patient = Doctrine_Query::create()
				->select("*")
				->from('PatientQpaLeading')
				->whereIn("epid", $epids)
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('epid!=""');
			$assigned_doctors_array = $patient->fetchArray();

			foreach($assigned_doctors_array as $kass => $vass)
			{
				$epid2docs[$vass['epid']][] = $vass['userid'];
				$doc_ids[$vass['userid']] = $vass['userid'];
			}

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
					$doc_ids[] = "99999999";
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
					$doc_ids[] = "99999999";
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
				$epids[] = 'XXX';
			}
			$patient = Doctrine_Query::create()
				->select("*")
				->from('PatientQpaLeading')
				->whereIn("epid", $epids)
				->andWhere('clientid="' . $clientid . '"')
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
				$doc_ids[] = "99999999";
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
			        if(!in_array($u_id_s, $ass_users_final[$epid]))
			        {
			            $epids2docs_final[$epid][] = $users_data[$u_id_s];
			            $ass_users_final[$epid][] = $u_id_s;
			        }
			    }
			}
 
			return $epids2docs_final;
		}

		
		
		public function get_assigned_userid( $ipid_epid = array("ipids"=>array(), "epids"=>array()) )
		{
				
			if( (empty($ipid_epid['ipids']) && empty($ipid_epid['epids']))
					|| (! is_array($ipid_epid['ipids']) && ! is_array($ipid_epid['epids']) )
			) {
				return; // bad args
			}
				
			$all_ipids = array();
			$ipids_from_epids = array();
			$result = array();
				
			if ( !empty($ipid_epid['epids']) && is_array($ipid_epid['epids'])) {
				$ipid_epid['epids'] = array_map('strtoupper', $ipid_epid['epids']); // uppercase epids because EpidIpidMapping return them like that.. so preserve
				$all_ipids = $ipids_from_epids = EpidIpidMapping::getEpidsIpids($ipid_epid['epids']);
				
			}
				
			if ( ! empty($ipid_epid['ipids']) && is_array($ipid_epid['ipids']) && is_array($ipids_from_epids)) {
				
				$all_ipids = array_merge($ipid_epid['ipids'], $ipids_from_epids);
			}
			
			
			if( empty($all_ipids) || ! is_array($all_ipids) ) {
				return; // no epid
			}
			
			
			$qpa_map = Doctrine_Query::create()
			->select('id, ipid, userid')
			->from('PatientQpaLeading')
			->whereIn('ipid', array_unique(array_values($all_ipids)))
			->fetchArray();
		
			$flipped_all_ipids = array_flip($all_ipids); // after the array_merge epids should be last so we can flip

			foreach($qpa_map as $row)
			{
				$result['ipids'][ $row['ipid'] ] [] = $row['userid'];
		
				//if only epids given as params, $result['ipids'] will be numeric (NO ipids_from_epids are fetched in this function)
				if (isset($flipped_all_ipids[$row['ipids']])) {
					$result['epids'][ $flipped_all_ipids[$row['ipid']] ] [] = $row['userid'];
				}
		
			}
				
			return $result;
		}
	}

?>