<?php

	Doctrine_Manager::getInstance()->bindComponent('VoluntaryworkersStatuses', 'SYSDAT');

	class VoluntaryworkersStatuses extends BaseVoluntaryworkersStatuses {

		public function get_voluntaryworker_statuses($vw_id, $clientid = false)
		{
			if(is_array($vw_id))
			{
				$vw_ids = $vw_id;
			}
			else
			{
				$vw_ids = array($vw_id);
			}

			//get new(multiple) statuses
			$drop = Doctrine_Query::create()
				->select('*')
				->from('VoluntaryworkersStatuses')
				->whereIn('vw_id', $vw_ids);
			if($clientid)
			{
				$drop->andWhere('clientid = "' . $clientid . '"');
			}

			$droparray = $drop->fetchArray();

			//var_dump($droparray); exit;
			$statuses = array();

			if($droparray)
			{
				$incr = '1';
				foreach($droparray as $k_voluntary => $v_voluntary)
				{
					//Dont read e and k statuses from VoluntaryworkersStatuses.
					//Last changes consists in removing e and k statuses from VoluntaryworkersStatuses
					//and read them from status field

					//ISPC-2054(voluntaryworkers statuses updated by clients)
					/*if($v_voluntary['id'] != 'e' && $v_voluntary['id'] != 'k')
					{
						$statuses[$v_voluntary['vw_id']][$incr] = $v_voluntary['status'];
						$incr++;
					}*/
					if($v_voluntary['status'] != '0')
					{
						$statuses[$v_voluntary['vw_id']][$incr] = $v_voluntary['status'];
						$incr++;
					}
					
				}
			}

			$old_status = Doctrine_Query::create()
				->select('*')
				->from('Voluntaryworkers')
				->where('isdelete = "0"')
				->andWhere('clientid = "' . $clientid . '"')
				->andWhereIn('id', $vw_ids);
			$old_status_res = $old_status->fetchArray();
			
			if($old_status_res)
			{
				foreach($old_status_res as $k_voluntary => $v_voluntary)
				{
					//get "keine angabe from old table" only if no data in statuses table
					if($v_voluntary['status'] == 'n' && count($statuses[$v_voluntary['id']]) == '0')
					{
						$statuses[$v_voluntary['id']][0] = $v_voluntary['status'];
					}
					else if($v_voluntary['status'] != 'n') //get all data but not "keine angabe"
					{
						$statuses[$v_voluntary['id']][0] = $v_voluntary['status'];
					}
					$statuses[$v_voluntary['id']] = array_values(array_unique($statuses[$v_voluntary['id']]));
				}
			}
			else
			{
				if(count($statuses[$vw_id]) == '0')
				{
					$statuses[$vw_id][0] = '-'; //keep this seat warm
				}
			}
			
			if(count($statuses) != '0')
			{
				return $statuses;
			}
			else
			{
				return false;
			}
		}

		public function clone_records($vw_id, $target_vw_id, $clientid)
		{
			//get curent volunteer statuses
			$vw_statuses = $this->get_voluntaryworker_statuses($vw_id);

			if($vw_statuses)
			{
				foreach($vw_statuses[$vw_id] as $k_status => $v_status_id)
				{
					$vw_statuses_data_array[] = array(
						'vw_id' => $target_vw_id,
						'clientid' => $clientid,
						'status' => $v_status_id,
					);
				}

				$collection = new Doctrine_Collection('VoluntaryworkersStatuses');
				$collection->fromArray($vw_statuses_data_array);
				$collection->save();
			}
		}
		
		public function get_voluntaryworkers_attached_statuses($clientid)
		{
			if(!$clientid)
			{
				return;
			}
		
			//get new(multiple) statuses
			$drop = Doctrine_Query::create()
			->select('DISTINCT(status) as status')
			->from('VoluntaryworkersStatuses')
			->where('clientid =?', $clientid);			
		
			$droparray = $drop->fetchArray();
			//var_dump($droparray); exit;
			
			return $droparray;
		}

	}

?>