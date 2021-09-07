<?php

	Doctrine_Manager::getInstance()->bindComponent('VisitKoordination', 'MDAT');

	class VisitKoordination extends BaseVisitKoordination {

		public function getAllPatientKoordinationVisits($ipid, $date = false)
		{
			$visitkoordination = Doctrine_Query::create()
				->select("*")
				->from('VisitKoordination')
				->where("ipid='" . $ipid . "'");
			if($date)
			{
				$visitkoordination->andWhere('DATE( `visit_date` ) = "' . date('Y-m-d', strtotime($date)) . '"');
			}

			$visitkoordinationarray = $visitkoordination->fetchArray();

			return $visitkoordinationarray;
		}

		public function getAllKoordinationVisitsInPeriod($userid, $start_date, $end_date, $recordids = false, $client_ipids)
		{
			$visitkoordination = Doctrine_Query::create()
				->select("*")
				->from('VisitKoordination')
				->where("create_user='" . $userid . "'")
				->andWhere('visit_date BETWEEN "' . date("Y-m-d H:i:s", $start_date) . '" AND "' . date("Y-m-d H:i:s", $end_date) . '"')
				->andWhereIn('ipid', $client_ipids);

			if($recordids)
			{
				$visitkoordination->andWhereNotIn('id', $recordids);
			}

			$visitkoordinationarray = $visitkoordination->fetchArray();

			return $visitkoordinationarray;
		}

		public function checkKoordinationVisitsByUser($user_id, $start_date, $end_date, $edit_id)
		{
			$koordication = Doctrine_Query::create()
				->select("ipid,start_date,end_date")
				->from('VisitKoordination')
				->where(" create_user = '" . $user_id . "'");
			$koordication->andWhere('( start_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" ) OR ( end_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" )');

			if($edit_id)
			{
				$koordication->andWhere(' id <> "' . $edit_id . '"');
			}
			$koordicationarray = $koordication->fetchArray();

			return $koordicationarray;
		}

		public function get_koordination_visits($recordids = false)
		{
			if($recordids)
			{
				$Q = Doctrine_Query::create()
					->select('*, visit_date as date')
					->from('VisitKoordination')
					->whereIn('id', $recordids);
				$koord_visits = $Q->fetchArray();

				return $koord_visits;
			}
			else
			{
				return false;
			}
		}
		
		public function get_patient_koordination_visits($ipid, $deleted_form_ids = false, $search = false)
		{
			$Tr = new Zend_View_Helper_Translate();

			if(empty($deleted_form_ids))
			{
				$deleted_form_ids[] = '9999999999999';
			}

			$koord_visit = Doctrine_Query::create()
				->select("*")
				->from('VisitKoordination')
				->where("ipid LIKE '" . $ipid . "'");
			if($deleted_form_ids)
			{
				$koord_visit->andWhereNotIn('id', $deleted_form_ids);
			}

			if($search)
			{
				$search_fields = array_keys($search);
				$search_arr = false;
				$search_sql = '';
				foreach($search_fields as $k_search => $v_search)
				{
					$search_arr[] = 'DATE(`' . $v_search . '`) = DATE("' . date('Y-m-d H:i:s', strtotime($search[$v_search])) . '")';
				}

				if($search_arr)
				{
					$search_sql = implode(" OR ", $search_arr);
				}
				$koord_visit->andWhere($search_sql);
			}

			$koord_visit_res = $koord_visit->fetchArray();

			foreach($koord_visit_res as $k_doc => $v_doc)
			{
				$v_doc['form_type'] = $Tr->translate('visitkoordinationform');
				//KVNO KOORDINATION VISIT
				$v_doc['visit_form_type'] = 'vkf';

				$koord_visit_array[] = $v_doc;
			}

			return $koord_visit_array;
		}

		
		/**
		 * @author Ancuta
		 * 20.03.2019
		 * @param string $ipids
		 * @param string $period
		 * @param string $deleted_visit_forms
		 * @param string $duration
		 * @return boolean|number
		 */
		public function get_visits_multiple_by_periods($ipids = false, $period = false, $deleted_visit_forms = false, $duration=false )
		{
		
		    if( empty($ipids)){
		        return false;
		    }
		    if(is_array($ipids))
		    {
		        $ipids_arr = $ipids;
		    }
		    else
		    {
		        $ipids_arr = array($ipids);
		    }
		
		    $select = Doctrine_Query::create()
		    ->select('*')
		    ->from('VisitKoordination')
		    ->whereIn('ipid', $ipids_arr);
		    if(!empty($deleted_visit_forms)){
		        $select->andWhereNotIn('id', $deleted_visit_forms);
		    }
		
		    if($period)
		    {
		        foreach($period['start'] as $k_period => $v_period)
		        {
		            $sql_period[] = ' DATE(start_date) BETWEEN DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '") AND DATE("' . date('Y-m-d H:i:s', strtotime($period['end'][$k_period])) . '") ';
		        }
		        $select->andWhere(implode(' OR ', $sql_period));
		    }
		
		    $select->orderBy('start_date ASC');
		    $select_res = $select->fetchArray();
		
		    if($select_res)
		    {
		        foreach($select_res as $k_cf => $v_cf)
		        {
		            $result[$v_cf['ipid']][$v_cf['id']] = $v_cf;
		
		            if($duration)
		            {
		                $result[$v_cf['ipid']][$v_cf['id']]['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_cf['start_date'],$v_cf['end_date']);
		            }
		        }
		
		        return $result;
		    }
		    else
		    {
		        return false;
		    }
		}
		
		
	}

?>