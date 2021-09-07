<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientVoluntaryworkers', 'SYSDAT');

	class PatientVoluntaryworkers extends BasePatientVoluntaryworkers {

		public function getPatientVoluntaryworkers($ipid, $vwid = false)
		{
			$sql = "*, id as vw_id";
			$sql .=",vw_comment as vw_com";
			$sql .=",start_date as start_date";
			$sql .=",end_date as end_date";
			$sql .=",phone as vw_phone";
			$sql .=",mobile as vw_mobile";

			/*if($vwid)
			{
				$q = "PatientVoluntaryworkers.vwid= " . $vwid . " and";
			}
			else
			{
				$q = "";
			}*/
			
			
			$drop = Doctrine_Query::create()
				->select($sql)
				->from('Voluntaryworkers')
				->leftJoin('PatientVoluntaryworkers')
					//->where("" . $q . " PatientVoluntaryworkers.vwid = Voluntaryworkers.id and PatientVoluntaryworkers.ipid='" . $ipid . "' and PatientVoluntaryworkers.isdelete = 0 ");
				->where("PatientVoluntaryworkers.vwid = Voluntaryworkers.id")
				->andWhere("PatientVoluntaryworkers.ipid=?", $ipid)
				->andWhere("PatientVoluntaryworkers.isdelete = 0 ");
			
			if($vwid)
			{
				$drop->andWhere("PatientVoluntaryworkers.vwid=?", $vwid);
			}
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getPatientVworkers($ipid)
		{

			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientVoluntaryworkers')
				->where("ipid= ?" , $ipid)
				->andWhere("isdelete = 0")
				->fetchArray();

			return $drop;
		}

		public function getPatientLastVoluntaryworker($ipid)
		{

			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientVoluntaryworkers')
				->where("ipid=?", $ipid)
				->andWhere("isdelete = 0")
				->orderBy('id desc')
				->limit(1);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getPatientLastVoluntaryworkersNew($ipid, $ppd = false)
		{
			$drop = Doctrine_Query::create()
				->select('Voluntaryworkers.id as vw_id')
				->from('Voluntaryworkers')
				->leftJoin('PatientVoluntaryworkers')
				->where("" . $q . " PatientVoluntaryworkers.wlid = Voluntaryworkers.id")
				->andWhere("PatientVoluntaryworkers.ipid='" . $ipid . "' and PatientVoluntaryworkers.isdelete  = 0")
				->orderBy('PatientVoluntaryworkers.change_date DESC')
				->limit(1);

			$droparray = $drop->fetchArray();

			return $droparray;
		}

		function getVoluntaryworkersPatients($workers)
		{
			if(is_array($workers))
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('PatientVoluntaryworkers')
					->whereIn('vwid', $workers)
					->andWhere('isdelete = "0"')
					->orderBy('id desc');
				$droparray = $drop->fetchArray();
			}
			else
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('PatientVoluntaryworkers')
					->where('vwid = ?', $workers)
					->andWhere('isdelete = "0"')
					->orderBy('id desc');
				$droparray = $drop->fetchArray();
			}

			foreach($droparray as $k_worker => $v_worker)
			{
				$worker_patients[$v_worker['vwid']][] = $v_worker['ipid'];
			}

			return $worker_patients;
		}

		public function clone_records($ipid, $target_ipid, $target_client)
		{
			$patient_voluntary = $this->getPatientVworkers($ipid);
			$volunteer = new Voluntaryworkers();
			$volunteer_statuses = new VoluntaryworkersStatuses();

			foreach($patient_voluntary as $k_pvolunteer => $v_pvolunteer)
			{
				//clone master volunteer data
				$copied_volunteer = $volunteer->clone_record($v_pvolunteer['vwid'], $target_client);

				if($copied_volunteer)
				{
					//clone master volunteer statuses data
					$copied_volunteer_statuses = $volunteer_statuses->clone_records($v_pvolunteer['vwid'], $copied_volunteer, $target_client);

					$pvol = new PatientVoluntaryworkers();
					//ISPC-2614 Ancuta 20.07.2020 :: deactivate listner for clone
					$pc_listener = $pvol->getListener()->get('IntenseConnectionListener');
					$pc_listener->setOption('disabled', true);
					//--
					
					$pvol->ipid = $target_ipid;
					$pvol->vwid = $copied_volunteer;
					$pvol->vw_comment = $v_pvolunteer['vw_comment'];
					$pvol->start_date = $v_pvolunteer['start_date'];//ISPC-2614
					$pvol->isdelete = '0';
					//ISPC-2614 Ancuta 16.07.2020	:: activate lister after clone
					$pc_listener->setOption('disabled', false);
					//--
					$pvol->save();
					
					
					
				}
			}
		}

		function get_workers2patients($workers, $ipids = false, $details = false, $currently_connected = false)
		{
			if(is_array($workers))
			{
				$drop = Doctrine_Query::create();
				$drop->select('*');
				$drop->from('PatientVoluntaryworkers');
				$drop->whereIn('vwid', $workers);

				if($ipids && is_array($ipids))
				{
					$drop->andWhereIn('ipid', $ipids);
				}

				if($currently_connected)
				{
				    $drop->andWhere('date(start_date) <= CURDATE() ');
				    $drop->andWhere('end_date = "0000-00-00 00:00:00"  OR  date(end_date) >= CURDATE() ');
				}
				$drop->andWhere('isdelete = "0"');
				$drop->orderBy('start_date ASC');
				$droparray = $drop->fetchArray();
			}
			else
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('PatientVoluntaryworkers')
					->where('vwid = ?', $workers)
					->andWhere('isdelete = "0"')
					->orderBy('start_date ASC');
				$droparray = $drop->fetchArray();
			}

			foreach($droparray as $k_worker => $v_worker)
			{
				if($details)
				{
					$worker_patients[$v_worker['vwid']] = $v_worker;
				}
				else
				{
					$worker_patients[$v_worker['vwid']] = $v_worker['ipid']; // $v_worker['vwid'] is newly created every time a vw is added to patient
				}
			}

			return $worker_patients;
		}

		public function get_patient_voluntaryworkers($ipids, $fetch_master_data = false)
		{
			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}
			
			if(count($ipids_arr) == 0)
			{
				return false;
			}
			/*if(count($ipids_arr) == 0)
			{
				$ipids_arr[] = '9999999999';
			}*/
			
			$Q = Doctrine_Query::create()
				->select('*')
				->from('PatientVoluntaryworkers')
				->whereIn('ipid', $ipids_arr)
				->andWhere('isdelete="0"')
				->orderBy('id ASC');
			$q_res = $Q->fetchArray();
			
			if($q_res)
			{
				if($fetch_master_data)
				{
					//$voluntaryworkers_ids[] = '999999999999';
					$voluntaryworkers_ids = array();
					foreach($q_res as $k_res => $v_res)
					{
						$patient_voluntaryworker_details[$v_res['vwid']] = $v_res;
						$voluntaryworkers_ids[] = $v_res['vwid'];
					}
					
					$voluntaryworkers_details = array();
					if(!empty($voluntaryworkers_ids))
					{
						$voluntaryworkers = new Voluntaryworkers();
						$voluntaryworkers_details = $voluntaryworkers->get_voluntaryworkers($voluntaryworkers_ids);
					}
					
					foreach($voluntaryworkers_details as $k_voluntaryworker => $v_voluntaryworker)
					{
						$patient_voluntaryworker_details[$v_voluntaryworker['id']]['master'] = $v_voluntaryworker;
					}

					if($patient_voluntaryworker_details)
					{
						return $patient_voluntaryworker_details;
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

		//TODO-3782 Lore 26.01.2021
		public function get_patient_voluntaryworkers_parent_child($ipids, $fetch_master_data = false)
		{
		    if(is_array($ipids))
		    {
		        $ipids_arr = $ipids;
		    }
		    else
		    {
		        $ipids_arr = array($ipids);
		    }
		    
		    if(count($ipids_arr) == 0)
		    {
		        return false;
		    }
		    
		    $Q = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientVoluntaryworkers')
		    ->whereIn('ipid', $ipids_arr)
		    ->andWhere('isdelete="0"')
		    ->orderBy('id ASC');
		    $q_res = $Q->fetchArray();
		    
		    if($q_res)
		    {
		        if($fetch_master_data)
		        {
		            $voluntaryworkers_ids = array();
		            foreach($q_res as $k_res => $v_res)
		            {
		                $patient_voluntaryworker_details[$v_res['vwid']] = $v_res;
		                $voluntaryworkers_ids[] = $v_res['vwid'];
		            }
		            
		            $voluntaryworkers_details = array();
		            if(!empty($voluntaryworkers_ids))
		            {
		                $voluntaryworkers = new Voluntaryworkers();
		                $voluntaryworkers_details = $voluntaryworkers->get_voluntaryworkers($voluntaryworkers_ids);
		            }
		            
		            $parent_idss = array();
		            
		            foreach($voluntaryworkers_details as $k_voluntaryworker => $v_voluntaryworker)
		            {
		                if($v_voluntaryworker['parent_id'] != '0' && $v_voluntaryworker['status'] != 'e'){
		                    $voluntaryworkers_details_parent = $voluntaryworkers->get_voluntaryworkers($v_voluntaryworker['parent_id']);
		                    $patient_voluntaryworker_details[$v_voluntaryworker['id']]['master'] = $voluntaryworkers_details_parent[0];
		                } else {
		                    $patient_voluntaryworker_details[$v_voluntaryworker['id']]['master'] = $v_voluntaryworker;
		                }
		            }
		            
		            
		            if($patient_voluntaryworker_details)
		            {
		                return $patient_voluntaryworker_details;
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
		
		
		
		public function set_end_date_by_id( $id = 0, $end_date = 0 ) 
		{
			$result = 0;
			
			$q = Doctrine::getTable('PatientVoluntaryworkers')->find( $id );
			
			if ($q instanceof PatientVoluntaryworkers) {
				
				if (strtotime($q->start_date) > strtotime($end_date)) {
					$end_date = $q->start_date;//so we don't start after we end
				}
				
				$q->end_date = date("Y-m-d H:i:s", strtotime($end_date));
				$q->save();
				
				$result = $q->id;
			}
			
			return $result;
			
		}
	}

?>