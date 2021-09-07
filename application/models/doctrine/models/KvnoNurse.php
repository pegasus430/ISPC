<?php

	Doctrine_Manager::getInstance()->bindComponent('KvnoNurse', 'MDAT');

	class KvnoNurse extends BaseKvnoNurse {

		public function getAllPatientNurseVisits($ipid, $date = false)
		{
			$kvnonurse = Doctrine_Query::create()
				->select("*")
				->from('KvnoNurse')
				->where("ipid='" . $ipid . "'");
			if($date)
			{
				$kvnonurse->andWhere('DATE( `vizit_date` ) = "' . date('Y-m-d', strtotime($date)) . '"');
			}
			$kvnonursearray = $kvnonurse->fetchArray();

			return $kvnonursearray;
		}

		public function getAllNurseVisitsInPeriod($userid, $start_date, $end_date, $recordids = false, $client_ipids)
		{
			$kvnonurse = Doctrine_Query::create()
				->select("*")
				->from('KvnoNurse')
				->where("create_user='" . $userid . "'")
				->andWhere('vizit_date BETWEEN "' . date("Y-m-d H:i:s", $start_date) . '" AND "' . date("Y-m-d H:i:s", $end_date) . '"')
				->andWhereIn('ipid', $client_ipids);
			if($recordids)
			{
				$kvnonurse->andWhereNotIn('id', $recordids);
			}
			$kvnonursearray = $kvnonurse->fetchArray();

			return $kvnonursearray;
		}

		public function getPatientAllNurseVisitsInPeriod($ipid, $start_date, $end_date, $recordids = false)
		{
			$kvnonurse = Doctrine_Query::create()
				->select("*")
				->from('KvnoNurse')
				->where("ipid='" . $ipid . "'")
				->andWhere('vizit_date BETWEEN "' . date("Y-m-d 00:00:00", $start_date) . '" AND "' . date("Y-m-d 23:59:59", $end_date) . '"');
			if($recordids)
			{
				$kvnonurse->andWhereNotIn('id', $recordids);
			}
//			echo $kvnonurse->getSqlQuery(); exit;
			$kvnonursearray = $kvnonurse->fetchArray();

			return $kvnonursearray;
		}

		public function getNurseVisits($recordids, $visit_credentials = false)
		{
			if(is_array($recordids))
			{
				$form_ids = $recordids;
			}
			else
			{
				$form_ids = array($recordids);
			}

			$kvno_nurse = Doctrine_Query::create()
				->select('*, vizit_date as date')
				->from('KvnoNurse')
				->WhereIn('id', $form_ids);
			$kvno_nurse_res = $kvno_nurse->fetchArray();

			if($kvno_nurse_res)
			{
				if($visit_credentials['patient'] && count($form_ids) == '1')
				{
					$sympval = new SymptomatologyValues();
					$set_details = $sympval->getSymptpomatologyValues(1); //HOPE set

					$form_symp = new KvnoNurseSymp();
					$form_symps = $form_symp->getKvnoNurseSymp($form_ids[0], $visit_credentials['patient']);

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

				foreach($kvno_nurse_res as $k_form => $v_form)
				{
					if(count($newsymptomarr) > '0')
					{
						$v_form['symptomatik'] = implode('; ', $newsymptomarr['symptomatik']);
					}
					else
					{
						$v_form['symptomatik'] = '';
					}

					$kvno_nurse_form[$k_form] = $v_form;
				}

				return $kvno_nurse_form;
			}
			else
			{
				return false;
			}
		}

		public function deleteNurseVisit($recordid)
		{
			$del_nursevisit = Doctrine::getTable('KvnoNurse')->findOneById($recordid);
			$del_nursevisit->isdelete = '1';
			$del_nursevisit->save();

			if($del_nursevisit)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function checkNurseVisitsByUser($user_id, $start_date, $end_date, $edit_id)
		{
			$nurse = Doctrine_Query::create()
				->select("ipid,start_date,end_date")
				->from('KvnoNurse')
				->where(" create_user = '" . $user_id . "'")
				->andWhere('( start_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" ) OR ( end_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" )');
			if($edit_id)
			{
				$nurse->andWhere(' id <> "' . $edit_id . '"');
			}
			$nursearray = $nurse->fetchArray();

			return $nursearray;
		}

		public function checkNurseMultipleVisitsByUser($user_id, $visits_intervals)
		{
			foreach($visits_intervals as $k => $int)
			{
				$start_date = date('Y-m-d H:i:s', strtotime($int['start']));
				$end_date = date('Y-m-d H:i:s', strtotime($int['end']));

				$inter_sql .= '(( start_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" ) OR ( end_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" )) OR ';
			}

			$nurse = Doctrine_Query::create()
				->select("ipid,start_date,end_date")
				->from('KvnoNurse')
				->where(" create_user = '" . $user_id . "'");
			if(strlen($inter_sql) > 0)
			{
				$nurse->andWhere(substr($inter_sql, 0, -4));
			}
			$nursearray = $nurse->fetchArray();

			return $nursearray;
		}

		public function check_nurse_overlapping_visits_by_user($user_id, $visits_intervals)
		{
			$id_str = '0,';
			foreach($visits_intervals as $k => $int)
			{
				$start_date = date('Y-m-d H:i:s', strtotime($int['start']));
				$end_date = date('Y-m-d H:i:s', strtotime($int['end']));

				$inter_sql .= '(( start_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" ) OR ( end_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" )) OR ';

				if(!empty($int['visit_id']))
				{
// 				$id_str .= $int['visit_id'].',';
				}
			}

			$id_sql = ' id NOT IN (' . substr($id_str, 0, -1) . ') ';

			$nurse = Doctrine_Query::create()
				->select("ipid,start_date,end_date")
				->from('KvnoNurse')
				->where(" create_user = '" . $user_id . "'");

			if(strlen($inter_sql) > 0)
			{
				$nurse->andWhere(substr($inter_sql, 0, -4));
			}
			if(strlen($id_sql) > 0)
			{
				$nurse->andWhere($id_sql);
			}

			$nursearray = $nurse->fetchArray();

			return $nursearray;
		}

		public function get_all_overlaping_user_visits($user_id, $visits_intervals)
		{
			foreach($visits_intervals as $k => $int)
			{
				$start_date = $int['start'];
				$end_date = $int['end'];
				$inter_sql .= '(( start_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" ) OR ( end_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" )) OR ';
			}

			$nurse = Doctrine_Query::create()
				->select("ipid,start_date,end_date, create_user")
				->from('KvnoNurse')
				->where(" create_user = '" . $user_id . "'");
			if(strlen($inter_sql) > 0)
			{
				$nurse->andWhere(substr($inter_sql, 0, -4));
			}
			$nursearray = $nurse->fetchArray();

			return $nursearray;
		}

		public function get_patient_nurse_visits($ipid, $deleted_form_ids = false, $search = false)
		{
			$Tr = new Zend_View_Helper_Translate();

			if(empty($deleted_form_ids))
			{
				$deleted_form_ids[] = '9999999999999';
			}

			$kvnonurse = Doctrine_Query::create()
				->select("*")
				->from('KvnoNurse')
				->where("ipid='" . $ipid . "'");
			if($deleted_form_ids)
			{
				$kvnonurse->andWhereNotIn('id', $deleted_form_ids);
			}

			if($search)
			{
				$search_fields = array_keys($search);
				$search_arr = false;
				$search_sql = '';
				foreach($search_fields as $k_search => $v_search)
				{
//					$search_arr[] = $v_search . ' = "' . $search[$v_search] . '"';
					$search_arr[] = 'DATE(`' . $v_search . '`) = DATE("' . date('Y-m-d H:i:s', strtotime($search[$v_search])) . '")';
				}

				if($search_arr)
				{
					$search_sql = implode(" OR ", $search_arr);
				}
				$kvnonurse->andWhere($search_sql);
			}
			$kvnonurse_res = $kvnonurse->fetchArray();

			foreach($kvnonurse_res as $k_nurse => $v_nurse)
			{

				$v_nurse['form_type'] = $Tr->translate('kvnonurseform');
				//KVNO NURSE
				$v_nurse['visit_form_type'] = 'knur';

				$kvnonursearray[] = $v_nurse;
			}

			return $kvnonursearray;
		}

		

		public function get_visits_multiple($ipids = false, $period = false )
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
		
		    $course_arr = Doctrine_Query::create()
		    ->select("id,ipid,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
		    ->from('PatientCourse')
		    ->whereIn('ipid', $ipids_arr)
		    ->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
		    ->andWhere("wrong = 1")
		    ->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_nurse_form'")
		    ->orderBy('course_date ASC')
		    ->fetchArray();
		
		
		    foreach($course_arr as $k_contact_v => $v_contact_v)
		    {
		        $deleted_visit_forms[] = $v_contact_v['recordid'];
		    }
		
		    $select = Doctrine_Query::create()
		    ->select('*')
		    ->from('KvnoNurse')
		    ->whereIn('ipid', $ipids_arr)
		    ->andWhere('isdelete="0"');
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
		            $result[$v_cf['id']] = $v_cf;
		        }
		
		        return $result;
		    }
		    else
		    {
		        return false;
		    }
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
		    ->from('KvnoNurse')
		    ->whereIn('ipid', $ipids_arr)
		    ->andWhere('isdelete="0"');
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