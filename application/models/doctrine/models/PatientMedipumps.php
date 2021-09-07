<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientMedipumps', 'MDAT');

	class PatientMedipumps extends BasePatientMedipumps {

		public function get_patient_medipumps($ipid)
		{
			$Q = Doctrine_Query::create()
				->select('*')
				->from('PatientMedipumps')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('isdelete="0"')
				->orderBy('start_date ASC');
			$q_res = $Q->fetchArray();

			if($q_res)
			{
				return $q_res;
			}
			else
			{
				return false;
			}
		}
		
		public function get_multi_patient_medipumps($ipids_arr = false)
		{
			if($ipids_arr && is_array($ipids_arr))
			{
				$Q = Doctrine_Query::create()
					->select('*')
					->from('PatientMedipumps')
					->whereIn('ipid', $ipids_arr)
					->andWhere('isdelete="0"')
					->orderBy('start_date ASC');
				$q_res = $Q->fetchArray();

				if($q_res)
				{
					foreach($q_res as $k_q_res => $v_q_res)
					{
						$mp_res[$v_q_res['ipid']][] = $v_q_res;
					}
					
					return $mp_res;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function get_period_medipumps($ipid, $current_period)
		{
			$patientmaster = new PatientMaster();

			$pat_med = Doctrine_Query::create()
				->select('*')
				->from('PatientMedipumps')
				->where('ipid ="' . $ipid . '"')
				->andWhere('isdelete="0"')
				->orderBy('start_date,id ASC');
			$pat_medipumps = $pat_med->fetchArray();

			$renting_days = array();
			foreach($pat_medipumps as $k_pat_med => $v_pat_med)
			{
				if($v_pat_med['end_date'] == '0000-00-00 00:00:00')
				{
					$till[$v_pat_med['medipump']] = date('Y-m-d');
				}
				else
				{
					$till[$v_pat_med['medipump']] = date('Y-m-d', strtotime($v_pat_med['end_date']));
				}

				$r1start[$v_pat_med['medipump']] = strtotime(date('Y-m-d', strtotime($v_pat_med['start_date'])));
				$r1end[$v_pat_med['medipump']] = strtotime(date('Y-m-d', strtotime($till[$v_pat_med['medipump']])));
				$r2start = strtotime($current_period['start']);
				$r2end = strtotime($current_period['end']);

				if(Pms_CommonData::isintersected($r1start[$v_pat_med['medipump']], $r1end[$v_pat_med['medipump']], $r2start, $r2end))
				{
					$rent_start[$v_pat_med['medipump']] = date('Y-m-d', strtotime($v_pat_med['start_date']));

					if(empty($rent_medipump[$v_pat_med['medipump']]))
					{
						$rent_medipump[$v_pat_med['medipump']] = array();
					}

					$rent_medipump[$v_pat_med['medipump']] = array_merge($rent_medipump[$v_pat_med['medipump']], $patientmaster->getDaysInBetween($rent_start[$v_pat_med['medipump']], $till[$v_pat_med['medipump']]));

					$rent[$v_pat_med['medipump']] = $rent_medipump[$v_pat_med['medipump']];
					$rent[$v_pat_med['medipump']] = array_values(array_unique($rent[$v_pat_med['medipump']]));
				}
			}

			$medipump_rent_days = $rent;

			return $medipump_rent_days;
		}
		
		public function get_multi_pat_period_medipumps($ipids_arr = false, $current_period)
		{
			if($ipids_arr && is_array($ipids_arr))
			{
				$patientmaster = new PatientMaster();

				$pat_med = Doctrine_Query::create()
					->select('*')
					->from('PatientMedipumps')
					->whereIn('ipid', $ipids_arr)
					->andWhere('isdelete="0"')
					->orderBy('start_date,id ASC');
				$pat_medipumps = $pat_med->fetchArray();
				
				$renting_days = array();
				foreach($pat_medipumps as $k_pat_med => $v_pat_med)
				{
					if($v_pat_med['end_date'] == '0000-00-00 00:00:00')
					{
						$till[$v_pat_med['medipump']] = date('Y-m-d');
					}
					else
					{
						$till[$v_pat_med['medipump']] = date('Y-m-d', strtotime($v_pat_med['end_date']));
					}

					$r1start[$v_pat_med['medipump']] = strtotime(date('Y-m-d', strtotime($v_pat_med['start_date'])));
					$r1end[$v_pat_med['medipump']] = strtotime(date('Y-m-d', strtotime($till[$v_pat_med['medipump']])));
					$r2start = strtotime($current_period[$v_pat_med['ipid']]['start']);
					$r2end = strtotime($current_period[$v_pat_med['ipid']]['end']);
					
					$current_period_tmp = $current_period;
					array_walk($current_period_tmp[$v_pat_med['ipid']]['days'], function(&$value) {
						$value = date("Y-m-d", strtotime($value));
					});
			
					if(Pms_CommonData::isintersected($r1start[$v_pat_med['medipump']], $r1end[$v_pat_med['medipump']], $r2start, $r2end))
					{
						$rent_start[$v_pat_med['medipump']] = date('Y-m-d', strtotime($v_pat_med['start_date']));

						if(empty($rent_medipump[$v_pat_med['medipump']]))
						{
							$rent_medipump[$v_pat_med['medipump']] = array();
						}

						$rent_medipump[$v_pat_med['medipump']] = array_merge($rent_medipump[$v_pat_med['medipump']], $patientmaster->getDaysInBetween($rent_start[$v_pat_med['medipump']], $till[$v_pat_med['medipump']]));

						$rent['mp_days'][$v_pat_med['ipid']][$v_pat_med['medipump']] = $rent_medipump[$v_pat_med['medipump']];
						$rent['mp_days'][$v_pat_med['ipid']][$v_pat_med['medipump']] = array_values(array_unique($rent['mp_days'][$v_pat_med['ipid']][$v_pat_med['medipump']]));
						
						$rent['mp_days'][$v_pat_med['ipid']][$v_pat_med['medipump']] = array_intersect($current_period_tmp[$v_pat_med['ipid']]['days'], $rent['mp_days'][$v_pat_med['ipid']][$v_pat_med['medipump']]);
						$rent['mp_ids'][$v_pat_med['ipid']][] = $v_pat_med['medipump'];
					}
				}

				$medipump_rent_days = $rent;
				
				return $medipump_rent_days;
			} else {
				return false;
			}
		}

		public function get_period_medipumps_invoice($ipid, $current_period)
		{
			$patientmaster = new PatientMaster();

			$pat_med = Doctrine_Query::create()
				->select('*')
				->from('PatientMedipumps')
				->where('ipid ="' . $ipid . '"')
				->andWhere('isdelete="0"')
				->orderBy('start_date,id ASC');
			$pat_medipumps = $pat_med->fetchArray();

			$renting_days = array();
			foreach($pat_medipumps as $k_pat_med => $v_pat_med)
			{
				if($v_pat_med['end_date'] == '0000-00-00 00:00:00')
				{
					$till[$v_pat_med['id']] = date('Y-m-d');
				}
				else
				{
					$till[$v_pat_med['id']] = date('Y-m-d', strtotime($v_pat_med['end_date']));
				}

				$r1start[$v_pat_med['id']] = strtotime(date('Y-m-d', strtotime($v_pat_med['start_date'])));
				$r1end[$v_pat_med['id']] = strtotime(date('Y-m-d', strtotime($till[$v_pat_med['id']])));
				$r2start = strtotime($current_period['start']);
				$r2end = strtotime($current_period['end']);

				if(Pms_CommonData::isintersected($r1start[$v_pat_med['id']], $r1end[$v_pat_med['id']], $r2start, $r2end))
				{
					$rent_start[$v_pat_med['id']] = date('Y-m-d', strtotime($v_pat_med['start_date']));

					if(empty($rent_medipump[$v_pat_med['id']]))
					{
						$rent_medipump[$v_pat_med['id']] = array();
					}

					$rent_pat_medipump[$v_pat_med['medipump']][$v_pat_med['id']] = $patientmaster->getDaysInBetween($rent_start[$v_pat_med['id']], $till[$v_pat_med['id']]);
				}
			}

			return $rent_pat_medipump;
		}

		public function get_overlapping_medipumps($ipid, $medipump, $start, $end, $excluded_id = false)
		{
			$start_date = date('Y-m-d', $start);
			$end_date = date('Y-m-d', $end);

			$pat_med = Doctrine_Query::create()
				->select('*')
				->from('PatientMedipumps')
				->where('ipid ="' . $ipid . '"')
				->andWhere('medipump = "' . $medipump . '"')
				->andWhere('DATE(start_date) <= "' . $end_date . '" AND DATE(end_date) >= "' . $start_date . '"')
				->andWhere('isdelete="0"');
			if($excluded_id)
			{
				$pat_med->andWhere('id != "' . $excluded_id . '"');
			}
			$pat_medipumps = $pat_med->fetchArray();

			return $pat_medipumps;
		}

	}

?>