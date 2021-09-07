<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientApprovedVisitTypes', 'MDAT');

	class PatientApprovedVisitTypes extends BasePatientApprovedVisitTypes {

		public function get_last_patient_approved_visit_type($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientApprovedVisitTypes')
				->where("ipid='" . $ipid . "'")
				->andwhere('isdelete = 0')
				->orderBy('id desc')
				->limit(1);
			$livearr = $drop->fetchArray();

			if($livearr)
			{
				return $livearr[0];
			}
		}

		public function get_active_patient_approved_visit_type($ipid, $current_day = false)
		{

			$drop = Doctrine_Query::create();
			$drop->select("*");
			$drop->from('PatientApprovedVisitTypes');
			$drop->where("ipid='" . $ipid . "'");

			if($current_day)
			{
				$current_day = date('Y-m-d 00:00:00');
				$drop->andwhere(' DATE(start_date) <= DATE("' . $current_day . '") ');
			}
			$drop->andwhere('end_date = "0000-00-00 00:00:00"');
			$drop->andwhere('isdelete = 0');
			$drop->orderBy('id desc');
			$drop->limit(1);
			//echo $drop->getSqlQuery();
			//exit;
			$livearr = $drop->fetchArray();

			if($livearr)
			{
				return $livearr[0];
			}
		}

		public function get_all_patient_approved_visit_type($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientApprovedVisitTypes')
				->where("ipid='" . $ipid . "'")
				->andwhere('isdelete = 0')
				->orderBy('start_date ASC');
			$livearr = $drop->fetchArray();

			if($livearr)
			{
				foreach($livearr as $key => $value)
				{

					$approved_visits_periods[$key]['start_date'] = date("d.m.Y", strtotime($value['start_date']));
					if($value['end_date'] != "0000-00-00 00:00:00")
					{
						$approved_visits_periods[$key]['end_date'] = date("d.m.Y", strtotime($value['end_date']));
					}
					else
					{
						$approved_visits_periods[$key]['end_date'] = "";
					}

					$approved_visits_periods[$key]['visit_type'] = $value['visit_type'];
					$approved_visits_periods[$key]['id'] = $value['id'];
				}
				return $approved_visits_periods;
			}
		}

		public function get_overlapping_visits_types($ipid, $start, $end, $excluded_id = false)
		{
			$start_date = date('Y-m-d', $start);
			$end_date = date('Y-m-d', $end);

			$pat_med = Doctrine_Query::create()
				->select('*')
				->from('PatientApprovedVisitTypes')
				->where('ipid ="' . $ipid . '"')
				->andWhere('( start_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" ) OR ( end_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" )')
				->andWhere('isdelete="0"');
			if($excluded_id)
			{
				$pat_med->andWhere('id != "' . $excluded_id . '"');
			}
			//echo $pat_med->getSqlQuery();
			//exit;
			$pat_medipumps = $pat_med->fetchArray();

			return $pat_medipumps;
		}

		public function patients_approved_visits_in_period($ipids, $start, $end)
		{
			$start_date = date('Y-m-d', strtotime($start));
			$end_date = date('Y-m-d', strtotime($end));


			if(!is_array($ipids))
			{
				$ipid_array[] = $ipids;
			}
			else
			{
				$ipid_array = $ipids;
			}

			if(count($ipid_array) == 0)
			{
				$ipid_array[] = '999999999';
			}

			$pat_avt = Doctrine_Query::create()
				->select('*')
				->from('PatientApprovedVisitTypes')
				->whereIn('ipid', $ipid_array)
				->andWhere('( DATE(start_date) BETWEEN DATE("' . $start_date . '") AND DATE("' . $end_date . '") ) OR ( DATE(end_date) BETWEEN DATE("' . $start_date . '") AND DATE("' . $end_date . '") ) OR (DATE(start_date) < DATE("' . $start_date . '") AND end_date="0000-00-00 00:00:00") ')
				->andWhere('isdelete="0"');
			//echo $pat_avt->getSqlQuerVy();
			//exit;
			$pat_avt_array = $pat_avt->fetchArray();

			//print_r($pat_avt_array); exit;
			foreach($pat_avt_array as $k => $value)
			{
				$existing_values[$value['ipid']][] = $value;
			}

			return $existing_values;
		}

		public function patients_approved_visits_overall($ipids)
		{
			if(!is_array($ipids))
			{
				$ipid_array[] = $ipids;
			}
			else
			{
				$ipid_array = $ipids;
			}

			if(count($ipid_array) == 0)
			{
				$ipid_array[] = '999999999';
			}

			$pat_avt = Doctrine_Query::create()
				->select('*')
				->from('PatientApprovedVisitTypes')
				->whereIn('ipid', $ipid_array)
				->andWhere('isdelete="0"');
			$pat_avt_array = $pat_avt->fetchArray();

			foreach($pat_avt_array as $k => $value)
			{
				$existing_values[$value['ipid']][] = $value;
			}

			return $existing_values;
		}

	}

?>