<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientSpecialists', 'IDAT');

	class PatientSpecialists extends BasePatientSpecialists {

		public function get_patient_specialists($ipids, $fetch_master_data = false)
		{
			if(empty($ipids)){
				return;
			}
			
			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}

			$Q = Doctrine_Query::create()
				->select('*')
				->from('PatientSpecialists')
				->whereIn('ipid', $ipids_arr)
				->andWhere('isdelete="0"')
				->orderBy('id ASC');
			$q_res = $Q->fetchArray();
			
			if($q_res)
			{
				if($fetch_master_data)
				{
					//$specialists_ids[] = '999999999999';
					$specialists_ids = array();
					foreach($q_res as $k_res => $v_res)
					{
						$patient_specialist_detalils[$v_res['sp_id']] = $v_res;
						$specialists_ids[] = $v_res['sp_id'];
					}
					if(!empty($specialists_ids))
					{
						$specialists = new Specialists();
						$specialists_details = $specialists->get_specialist($specialists_ids);
					}
					if($specialists_details)
					{
						foreach($specialists_details as $k_specialist => $v_specialist)
						{
							$patient_specialist_detalils[$v_specialist['id']]['master'] = $v_specialist;
						}

						if($patient_specialist_detalils)
						{
							return $patient_specialist_detalils;
						}
					}
				}
				else
				{
					return $q_res;
				}
			}
			else
			{
				return false;
			}
		}

		public function get_patient_specialist($ipid, $pat_sp_id)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientSpecialists')
				->where("ipid=?",  $ipid)
				->andWhere("sp_id=?",  $pat_sp_id);
				//->where('ipid = "' . $ipid . '"')
				//->andWhere('sp_id = "' . $pat_sp_id . '"');
				
			$q_res = $q->fetchArray();


			foreach($q_res as $k_q_res => $v_q_res)
			{
				$pat_sp_details[$v_q_res['sp_id']]['comment'] = $v_q_res['comment'];
			}

			if($q_res)
			{
				$specialists = new Specialists();
				$sp_master_array = $specialists->get_specialist($q_res[0]['sp_id']);

				if($sp_master_array)
				{
					foreach($sp_master_array as $k_sp_master => $v_sp_master)
					{
						$sp_master_details[$v_sp_master['id']] = $v_sp_master;
						$sp_master_details[$v_sp_master['id']]['comment'] = $pat_sp_details[$v_sp_master['id']]['comment'];
					}
				}

				return $sp_master_details[$q_res[0]['sp_id']];
			}
			else
			{
				return false;
			}
		}

		
		public static function beautifyName( &$usrarray )
		{
		    //mb_convert_case(nice_name, MB_CASE_TITLE, 'UTF-8'); ?
		    if ( empty($usrarray) || ! is_array($usrarray)) {
		        return;
		    }
		
		   
		    foreach ( $usrarray as &$k )
		    {
		        
		        if ( ! is_array($k) || isset($k['nice_name'])) {
		            continue; // varaible allready exists, use another name for the variable
		        }
		
		        if (isset($k['master'])){
    		        $k ['nice_name']  = trim($k['master']['title']) != "" ? trim($k['master']['title']) . " " : "";
    		        $k ['nice_name']  .= trim($k['master']['last_name']);
    		        $k ['nice_name']  .= trim($k['master']['first_name']) != "" ? (", " . trim($k['master']['first_name'])) : "";
    		        $k ['nice_name']  .= trim($k['master']['practice']) != "" ? " (" . trim($k['master']['practice']) . ')' : "";
		        } else {
		            $k ['nice_name']  = trim($k['title']) != "" ? trim($k['title']) . " " : "";
		            $k ['nice_name']  .= trim($k['last_name']);
		            $k ['nice_name']  .= trim($k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
		            $k ['nice_name']  .= trim($k['practice']) != "" ? " (" . trim($k['practice']) . ')' : "";
		        }
		    }
		}
		
		
		
		public function findOrCreateOneByIpidAndId($ipid = '', $id = 0, array $data = array())
		{
		    
		    if ( empty($id) || ! $entity = $this->getTable()->findOneByIpidAndId($ipid, $id)) {
		
		        $entity = $this->getTable()->create(array('ipid' => $ipid));
		        unset($data[$this->getTable()->getIdentifier()]);
		    }
		
		    $entity->fromArray($data); //update
		
		    $entity->save(); //at least one field must be dirty in order to persist
		
		    return $entity;
		}

		
		/**
		 * ISPC-2614
		 * @param unknown $ipid
		 * @param unknown $target_ipid
		 * @param unknown $target_client
		 * @return unknown
		 */
		public function clone_records($ipid, $target_ipid, $target_client)
		{
		    $pfleges = $this->get_patient_specialists($ipid);
		    
		    if($pfleges)
		    {
		        foreach($pfleges as $k_pfl => $v_pfl)
		        {
		            $pfl = new Specialists();
		            $master_pfl = $pfl->clone_record($v_pfl['sp_id'], $target_client);
		            
		            if($master_pfl)
		            {
		                $pfl_cl = new PatientSpecialists();
		                //ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
		                $pc_listener = $pfl_cl->getListener()->get('IntenseConnectionListener');
		                $pc_listener->setOption('disabled', true);
		                //--
		                $pfl_cl->ipid = $target_ipid;
		                $pfl_cl->sp_id = $master_pfl;
		                $pfl_cl->comment = $v_pfl['comment'];
		                $pfl_cl->save();
		                //ISPC-2614 Ancuta 16.07.2020	:: activate lister after clone
		                $pc_listener->setOption('disabled', false);
		                //--
		            }
		        }
		        
		        return $pfl->id;
		    }
		}
		
	}

?>