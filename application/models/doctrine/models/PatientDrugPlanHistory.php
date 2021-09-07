<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanHistory', 'MDAT');

	class PatientDrugPlanHistory extends BasePatientDrugPlanHistory {

		public function get_patient_drugplan_history($ipid, $period)
		{
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanHistory')
				->where("ipid = '" . $ipid . "'")
				->andWhere('DATE(pd_change_date) BETWEEN DATE("' . $period['start'] . '") AND DATE("' . $period['end'] . '") OR DATE(pd_create_date) BETWEEN DATE("' . $period['start'] . '") AND DATE("' . $period['end'] . '")')
				->orderBy("id ASC");
			$drugs_array = $drugs->fetchArray();

			if($drugs_array)
			{
				return $drugs_array;
			}
			else
			{
				return false;
			}
		}

		public function drugplanid_history($ipid = '', $drugplan_id = 0)
		{
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanHistory')
				->where("ipid = ?", $ipid)
				->andWhere("pd_id = ?", $drugplan_id)
				->orderBy("id ASC");
			$drugs_array = $drugs->fetchArray();

			if($drugs_array)
			{
				return $drugs_array;
			}
			else
			{
				return false;
			}
		}

		function get_weeks_between($start, $end)
		{
			$start = strtotime($start, 0);
			$end = strtotime($end, 0);
			$difference = $end - $start;
			$datediff = floor($difference / 604800);

			return $datediff;
		}

		public function get_period_weeks($start, $end)
		{
			$start = strtotime($start);
			$end = strtotime($end);

			$weeks = array();
			$days_week_counter = '1';
			$week = '1';
			while($start <= $end)
			{
				if($days_week_counter == '8')
				{
					$days_week_counter = '1';
					$week++;
				}
				$start_formatted = date('Y-m-d', $start);

				$weeks[$week][] = $start_formatted;

				$start = strtotime('+1 day', $start);
				$days_week_counter++;
			}
			return $weeks;
		}

		public function get_period_calendaristic_weeks($start, $end)
		{
			$start = strtotime($start);
			$end = strtotime($end);

			$weeks = array();
			while($start < $end)
			{
				$year = date('Y', $start);
				$week_number = date('W', $start);
				$start_formatted = date('Y-m-d', $start);

				$weeks[$week_number . '' . $year][] = $start_formatted;

				$start = strtotime('+1 day', $start);
			}
			return $weeks;
		}

	}

?>