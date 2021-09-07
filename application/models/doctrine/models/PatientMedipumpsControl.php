<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientMedipumpsControl', 'MDAT');

	class PatientMedipumpsControl extends BasePatientMedipumpsControl {

		public function get_medipumps_controlsheet($ipid, $date)
		{
			if(is_array($ipid))
			{
				if(count($ipid) > 0)
				{
					$ipid = $ipid;
				}
				else
				{
					$ipid[] = '9999999999999';
				}
			}
			else
			{
				$ipid = array($ipid);
			}

			$query = Doctrine_Query::create()
				->select('*')
				->from('PatientMedipumpsControl')
				->whereIn('ipid', $ipid)
				->andWhere('MONTH(date) = MONTH("' . $date . '")')
				->andWhere('YEAR(date) = YEAR("' . $date . '")');
			$q_res = $query->fetchArray();

			if($q_res)
			{
				return $q_res;
			}
			else
			{
				return false;
			}
		}
		
		public function get_multi_patients_controlsheet($ipids_arr = false, $current_period = false)
		{
			if($ipids_arr && $current_period)
			{
				foreach($ipids_arr as $k_ipid => $v_ipid)
				{
					if(!empty($current_period[$v_ipid]))
					{
						$sql_q[] = " (`ipid` LIKE '".$v_ipid."' AND `date` BETWEEN '".date('Y-m-d', strtotime($current_period[$v_ipid]['start']))."' AND '". date('Y-m-d', strtotime($current_period[$v_ipid]['end']))."') ";
					}
				}

				$query = Doctrine_Query::create()
					->select('*')
					->from('PatientMedipumpsControl')
					->where(implode(' OR ', $sql_q));
				$q_res = $query->fetchArray();

				if($q_res)
				{
					foreach($q_res as $k_res => $v_res)
					{
						$mp_saved_data[$v_res['ipid']][] = $v_res;
					}
					
					return $mp_saved_data;
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

	}

?>