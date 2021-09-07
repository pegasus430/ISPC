<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientStandby', 'IDAT');

	class PatientStandby extends BasePatientStandby {
		
		public function get_patient_standby_fall($ipids) 
		{
			
			
			if(!is_array($ipids)) {
				
				$ipidsarr[] = $ipids;
				
			}
			$ipidsarr[] = '999999999';
			
			
			$patientfall = Doctrine_Query::create()
			->select('*')
			->from('PatientStandby p')
			->whereIn('p.ipid', $ipids);
			
			$patientfall_data = $patientfall->fetchArray();
			
			if(!empty($patientfall_data)) {
				return $patientfall_data;
			}
		}
		
		
		
		
		

		public function get_patient_standby_admissions($ipid)
		{
			$q = Doctrine_Query::create()
			->select('*')
			->from('PatientStandbyDetails')
			->where('ipid LIKE "' . $ipid . '"')
			->orderBy('date ASC');
			$q_res = $q->fetchArray();
		
			//"new" patient - data in readmission
			if($q_res)
			{
				$incr = '0';
				foreach($q_res as $k_patient_date => $v_patient_date)
				{
					//date_type switcher
					if($v_patient_date['date_type'] == '1')
					{
						$type = 'start';
					}
					else
					{
						$type = 'end';
					}
		
					$patient_admission[$incr][$type] = $v_patient_date['date'];
		
					//check next item (which is supposed to be discharge) exists
					if($v_patient_date['date_type'] == '1' && !array_key_exists(($k_patient_date + 1), $q_res))
					{
						$patient_admission[$incr]['end'] = '';
					}
		
					//increment when reaching end dates(date_type=2)
					if($v_patient_date['date_type'] == '2')
					{
						$incr++;
					}
				}
			}
			else
			{
				//"old patient" - check if patient has admission(PM)/discharge(PD)
			/* 	$q_pm = Doctrine_Query::create()
				->select('*')
				->from('PatientMaster')
				->andWhere('ipid LIKE "' . $ipid . '"');
				$q_pm_res = $q_pm->fetchArray();
		
				if($q_pm_res)
				{
					$patient_admission[0]['start'] = $q_pm_res[0]['admission_date'];
		
					if($q_pm_res[0]['isdischarged'] == '1')
					{
						$q_pd = Doctrine_Query::create()
						->select('*')
						->from('PatientDischarge')
						->where('ipid LIKE "' . $ipid . '"')
						->andWhere('isdelete = "0"');
						$q_pd_res = $q_pd->fetchArray();
		
						if($q_pd_res)
						{
							$patient_admission[0]['end'] = $q_pd_res[0]['discharge_date'];
						}
					}
					else
					{
						$patient_admission[0]['end'] = '';
					}
				} */
			}
		
		
			if(count($patient_admission) > '0')
			{
				$del_old_active_data = Doctrine_Query::create()
				->delete('PatientStandby s')
				->where('s.ipid LIKE "' . $ipid . '"');
				$del_old_active_data->execute();
		
				foreach($patient_admission as $k_adm_cycle => $v_cycle_data)
				{
					if(strlen($v_cycle_data['end']) == '0')
					{
						$end_date = '0000-00-00';
					}
					else
					{
						$end_date = date('Y-m-d', strtotime($v_cycle_data['end']));
					}
		
					$cycle_records[] = array(
							'ipid' => $ipid,
							'start' => date('Y-m-d', strtotime($v_cycle_data['start'])),
							'end' => $end_date
					);
				}
		
				$collection = new Doctrine_Collection('PatientStandby');
				$collection->fromArray($cycle_records);
				$collection->save();
			}
			unset($cycle_records);
			unset($patient_admission);
			unset($q_res);
			unset($q_pm_res);
			unset($q_pd_res);
			unset($del_old_active_data);
		
			//		return $patient_admission;
		}
		
		
		
		
		
		
		
	}

?>