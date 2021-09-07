<?php

	Doctrine_Manager::getInstance()->bindComponent('BayernDoctorVisit', 'MDAT');

	class BayernDoctorVisit extends BaseBayernDoctorVisit {

		public function getAllPatientBayernDoctorVisits($ipid, $date = false)
		{
			$bayerndoc = Doctrine_Query::create()
				->select("*")
				->from('BayernDoctorVisit')
				->where("ipid='" . $ipid . "'");

			if($date)
			{
				$bayerndoc->andWhere('DATE(  `visit_date` ) = "' . date('Y-m-d', strtotime($date)) . '"');
			}
			$bayerndocarray = $bayerndoc->fetchArray();

			return $bayerndocarray;
		}

		public function getAllBayernDoctorVisitsInPeriod($userid, $start_date, $end_date)
		{
			$bayerndoc = Doctrine_Query::create()
				->select("*")
				->from('BayernDoctorVisit')
				->where("create_user='" . $userid . "'")
				->andWhere('visit_date BETWEEN "' . date("Y-m-d H:i:s", $start_date) . '" AND "' . date("Y-m-d H:i:s", $end_date) . '"');

			$bayerndocarray = $bayerndoc->fetchArray();

			return $kvnodocarray;
		}

		public function checkBayernVisitsByUser($user_id, $start_date, $end_date, $edit_id)
		{
			$bayern_doc = Doctrine_Query::create()
				->select("ipid,start_date,end_date")
				->from('BayernDoctorVisit')
				->where(" create_user = '" . $user_id . "'");
			$bayern_doc->andWhere('( start_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" ) OR ( end_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" )');

			if($edit_id)
			{

				$bayern_doc->andWhere(' id <> "' . $edit_id . '"');
			}
			$bayern_docarray = $bayern_doc->fetchArray();

			return $bayern_docarray;
		}
		
		public function get_doctor_visits($recordids)
		{
			$bayern_doc = Doctrine_Query::create()
				->select('*, visit_date as date')
				->from('BayernDoctorVisit')
				->WhereIn('id', $recordids);
			$bayern_doc_res = $bayern_doc->fetchArray();

			return $bayern_doc_res;
		}

		public function get_doctor_visits_brief($recordid, $visit_credentials = false)
		{
			$bayern_doc = Doctrine_Query::create()
				->select('*, visit_date as date')
				->from('BayernDoctorVisit')
				->where('id = "' . $recordid . '"');
			$bayern_doc_res = $bayern_doc->fetchArray();

			if($bayern_doc_res)
			{
				if($visit_credentials['patient'])
				{
					$sympval = new SymptomatologyValues();
					$set_details = $sympval->getSymptpomatologyValues(1); //HOPE set

					$form_symp = new BayernDoctorSymp();
					$form_symps = $form_symp->getBayernDoctorSymp($recordid, $visit_credentials['patient']);

					if($visit_credentials['symptomatology_scale'] == 'a')
					{
						$sym_attrib_scale = Pms_CommonData::symptoms_attribute_values();
					}

					foreach($set_details as $key => $sym)
					{
						$sym_entry = '';
						if(strlen($form_symps[$sym['id']]['comment']) > '0')
						{
							$sym_comment = ' (' . $form_symps[$sym['id']]['comment'] . ')';
						}
						else
						{
							$sym_comment = '';
						}

						if($visit_credentials['symptomatology_scale'] == 'a')
						{

							$sym_value = $sym_attrib_scale[$form_symps[$sym['id']]['current_value']];
						}
						else
						{
							$sym_value = $form_symps[$sym['id']]['current_value'];
						}

						$sym_entry = $sym['value'] . ' ' . $sym_value . $sym_comment;

						$newsymptomarr['symptomatik'][] = $sym_entry;
					}
				}

				foreach($bayern_doc_res as $k_form => $v_form)
				{
					if(count($newsymptomarr) > '0')
					{
						$v_form['symptomatik'] = implode('; ', $newsymptomarr['symptomatik']);
					}
					else
					{
						$v_form['symptomatik'] = '';
					}

					$bayern_doc_form[$k_form] = $v_form;
				}

				return $bayern_doc_form;
			}
			else
			{
				return false;
			}
		}

		//changed: added grouped parameter (used in bayern_tv_ko_sapv_patients report)
		public function get_bayern_doctor_visits_period($ipid, $curent_period = false, $remove_deleted_visits = false, $grouped = true)
		{
			if($remove_deleted_visits)
			{
				$deleted_visits = Doctrine_Query::create()
					->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
					->from('PatientCourse')
					->where('wrong=1')
					->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
					->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('bayern_doctorvisit')) . "'")
					->andWhereIn('ipid', $ipid);

				if($curent_period)
				{
					$deleted_visits->andWhere('DATE(  `done_date` ) BETWEEN "' . date('Y-m-d', strtotime($curent_period['start'])) . '" AND "' . date('Y-m-d', strtotime($curent_period['end'])) . '"');
				}
				$deleted_visits_array = $deleted_visits->fetchArray();


				$deleted_visits_ids[] = '999999999';
				foreach($deleted_visits_array as $k_visit => $v_visit)
				{
					$deleted_visits_ids[] = $v_visit['recordid'];
				}
			}

			$bayerndoc = Doctrine_Query::create()
				->select("*")
				->from('BayernDoctorVisit')
				->whereIn("ipid", $ipid);

			if($curent_period)
			{
				$bayerndoc->andWhere('DATE(  `visit_date` ) BETWEEN "' . date('Y-m-d', strtotime($curent_period['start'])) . '" AND "' . date('Y-m-d', strtotime($curent_period['end'])) . '"');
			}

			if($remove_deleted_visits)
			{
				$bayerndoc->andWhereNotIn('id', $deleted_visits_ids);
			}
			$bayerndocarray = $bayerndoc->fetchArray();

			foreach($bayerndocarray as $k_visit_b => $v_visit_b)
			{
				//grouped by date default
				if($grouped)
				{
					$bayern_visits_arr[date('Y-m-d', strtotime($v_visit_b['visit_date']))][] = $v_visit_b;
				}
				else
				{
					$bayern_visits_arr[] = $v_visit_b;
				}
			}

			return $bayern_visits_arr;
		}

		public function get_visit_by_id($id)
		{
			$bayerndoc = Doctrine_Query::create()
				->select("*")
				->from('BayernDoctorVisit INDEXBY id')
				->where("id='" . $id . "'");
			$bayerndocarray = $bayerndoc->fetchArray();

			return $bayerndocarray;
		}

		public function get_patient_bayern_visits($ipid, $deleted_form_ids = false, $search = false)
		{
			$Tr = new Zend_View_Helper_Translate();

			if(empty($deleted_form_ids))
			{
				$deleted_form_ids[] = '9999999999999';
			}

			$bayern_visit = Doctrine_Query::create()
				->select("*")
				->from('BayernDoctorVisit')
				->where("ipid LIKE '" . $ipid . "'");
			if($deleted_form_ids)
			{
				$bayern_visit->andWhereNotIn('id', $deleted_form_ids);
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
				$bayern_visit->andWhere($search_sql);
			}

			$bayern_visit_res = $bayern_visit->fetchArray();

			foreach($bayern_visit_res as $k_doc => $v_doc)
			{
				$v_doc['form_type'] = $Tr->translate('bayerndoctorvisit');
				//BAYERN DOCTOR VISIT
				$v_doc['visit_form_type'] = 'bayern_doctorvisit';

				$bayern_visit_array[] = $v_doc;
			}

			return $bayern_visit_array;
		}
		
		public function get_multi_bay_doctor_visits_period($ipid, $curent_period = false, $remove_deleted_visits = false, $grouped = true)
		{
			if($remove_deleted_visits)
			{
				$deleted_visits = Doctrine_Query::create()
					->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
					->from('PatientCourse')
					->where('wrong=1')
					->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
					->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('bayern_doctorvisit')) . "'")
					->andWhereIn('ipid', $ipid);

				if($curent_period)
				{
					$deleted_visits->andWhere('DATE(  `done_date` ) BETWEEN "' . date('Y-m-d', strtotime($curent_period['start'])) . '" AND "' . date('Y-m-d', strtotime($curent_period['end'])) . '"');
				}
				$deleted_visits_array = $deleted_visits->fetchArray();


				$deleted_visits_ids[] = '999999999';
				foreach($deleted_visits_array as $k_visit => $v_visit)
				{
					$deleted_visits_ids[] = $v_visit['recordid'];
				}
			}

			$bayerndoc = Doctrine_Query::create()
				->select("*")
				->from('BayernDoctorVisit')
				->whereIn("ipid", $ipid);

			if($curent_period)
			{
				$bayerndoc->andWhere('DATE(  `visit_date` ) BETWEEN "' . date('Y-m-d', strtotime($curent_period['start'])) . '" AND "' . date('Y-m-d', strtotime($curent_period['end'])) . '"');
			}

			if($remove_deleted_visits)
			{
				$bayerndoc->andWhereNotIn('id', $deleted_visits_ids);
			}
			$bayerndocarray = $bayerndoc->fetchArray();

			foreach($bayerndocarray as $k_visit_b => $v_visit_b)
			{
				//grouped by date default
				if($grouped)
				{
					$bayern_visits_arr[$v_visit_b['ipid']][date('Y-m-d', strtotime($v_visit_b['visit_date']))][] = $v_visit_b;
				}
				else
				{
					$bayern_visits_arr[] = $v_visit_b;
				}
			}

			return $bayern_visits_arr;
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
		    ->from('BayernDoctorVisit')
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