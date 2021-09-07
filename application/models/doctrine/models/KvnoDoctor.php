<?php

	Doctrine_Manager::getInstance()->bindComponent('KvnoDoctor', 'MDAT');

	class KvnoDoctor extends BaseKvnoDoctor {

		public function getAllPatientDoctorVisits($ipid, $date = false)
		{
			$kvnodoc = Doctrine_Query::create()
				->select("*")
				->from('KvnoDoctor')
				->where("ipid='" . $ipid . "'")
				->andWhere('isdelete = "0"');
			if($date)
			{
				$kvnodoc->andWhere('DATE(  `vizit_date` ) = "' . date('Y-m-d', strtotime($date)) . '"');
			}
			$kvnodocarray = $kvnodoc->fetchArray();

			return $kvnodocarray;
		}

		public function getAllDoctorVisitsInPeriod($userid, $start_date, $end_date, $recordids = false, $client_ipids)
		{
			$kvnodoc = Doctrine_Query::create()
				->select("*")
				->from('KvnoDoctor')
				->where("create_user='" . $userid . "'")
				->andWhere('vizit_date BETWEEN "' . date("Y-m-d H:i:s", $start_date) . '" AND "' . date("Y-m-d H:i:s", $end_date) . '"')
				->andWhereIn('ipid', $client_ipids)
				->andWhere('isdelete = "0"');
			if($recordids)
			{
				$kvnodoc->andWhereNotIn('id', $recordids);
			}
			$kvnodocarray = $kvnodoc->fetchArray();

			return $kvnodocarray;
		}

		public function getDoctorVisits($recordids, $visit_credentials = false)
		{
			if(is_array($recordids))
			{
				$form_ids = $recordids;
			}
			else
			{
				$form_ids = array($recordids);
			}

			$kvno_doc = Doctrine_Query::create()
				->select('*, vizit_date as date')
				->from('KvnoDoctor')
				->WhereIn('id', $form_ids)
				->andWhere('isdelete = "0"');
			$kvno_doc_res = $kvno_doc->fetchArray();

			if($kvno_doc_res)
			{
				if($visit_credentials['patient'] && count($form_ids) == '1')
				{
					$sympval = new SymptomatologyValues();
					$set_details = $sympval->getSymptpomatologyValues(1); //HOPE set

					$form_symp = new KvnoDoctorSymp();
					$form_symps = $form_symp->getKvnoDoctorSymp($form_ids[0], $visit_credentials['patient']);

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

				foreach($kvno_doc_res as $k_form => $v_form)
				{
					if(count($newsymptomarr) > '0')
					{
						$v_form['symptomatik'] = implode('; ', $newsymptomarr['symptomatik']);
					}
					else
					{
						$v_form['symptomatik'] = '';
					}

					//maintain output format as the function has other calls than the brief one
					$kvno_doc_form[$k_form] = $v_form;
				}

				return $kvno_doc_form;
			}
			else
			{
				return false;
			}
		}

		public function checkDoctorVisitsByUser($user_id, $start_date, $end_date, $edit_id)
		{
			$kvnodoc = Doctrine_Query::create()
				->select("ipid,start_date,end_date")
				->from('KvnoDoctor')
				->where(" create_user = '" . $user_id . "'")
				->andWhere('isdelete = "0"');
			$kvnodoc->andWhere('( start_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" ) OR ( end_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" )');

			if($edit_id)
			{

				$kvnodoc->andWhere(' id <> "' . $edit_id . '"');
			}

			$kvnodocarray = $kvnodoc->fetchArray();

			return $kvnodocarray;
		}

		public function deleteDoctorVisit($recordid)
		{
			$del_docvisit = Doctrine::getTable('KvnoDoctor')->findOneById($recordid);
			$del_docvisit->isdelete = '1';
			$del_docvisit->save();

			if($del_docvisit)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function get_visit_by_id($id)
		{
			$kvno_doc = Doctrine_Query::create()
				->select('*')
				->from('KvnoDoctor INDEXBY id')
				->where("id='" . $id . "'")
				->andWhere('isdelete = "0"');
			$kvno_doc_res = $kvno_doc->fetchArray();

			return $kvno_doc_res;
		}

		public function get_patient_doctor_visits($ipid, $deleted_form_ids = false, $search = false)
		{
			$Tr = new Zend_View_Helper_Translate();

			if(empty($deleted_form_ids))
			{
				$deleted_form_ids[] = '9999999999999';
			}

			$kvnodoctor = Doctrine_Query::create()
				->select("*")
				->from('KvnoDoctor')
				->where("ipid='" . $ipid . "'");
			if($deleted_form_ids)
			{
				$kvnodoctor->andWhereNotIn('id', $deleted_form_ids);
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
				$kvnodoctor->andWhere($search_sql);
			}
			$kvnodoctor_res = $kvnodoctor->fetchArray();

			foreach($kvnodoctor_res as $k_doc => $v_doc)
			{

				$v_doc['form_type'] = $Tr->translate('kvnodoctorform');
				//KVNO DOCTOR
				$v_doc['visit_form_type'] = 'kdoc';

				$kvnodoctorarray[] = $v_doc;
			}

			return $kvnodoctorarray;
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
		    ->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_doctor_form'")
		    ->orderBy('course_date ASC')
		    ->fetchArray();
		
		    
		    foreach($course_arr as $k_contact_v => $v_contact_v)
		    {
		        $deleted_visit_forms[] = $v_contact_v['recordid'];
		    }
		
		    $select = Doctrine_Query::create()
		    ->select('*')
		    ->from('KvnoDoctor')
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
		    ->from('KvnoDoctor')
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