<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientSteps', 'MDAT');

	class PatientSteps extends BasePatientSteps {

		public function get_patient_steps($ipid)
		{
			if(is_array($ipid))
			{
				$ipids_arr = $ipid;
			}
			else
			{
				$ipids_arr = array($ipid);
			}

			if (empty($ipids_arr)){
				return false;
			}
			
			$query = Doctrine_Query::create()
				->select('*')
				->from('PatientSteps')
				->whereIn('ipid', $ipids_arr)
				->andWhere("isdelete = 0");
			$query_res = $query->fetchArray();

			if($query_res)
			{
				foreach($query_res as $k_res => $v_res)
				{
					if(is_array($ipid))
					{
						$res_array[$v_res['ipid']][$v_res['step']] = $v_res;
					}
					else
					{
						$res_array[$v_res['step']] = $v_res;
					}
				}

				return $res_array;
			}
			else
			{
				return false;
			}
		}

	}

?>