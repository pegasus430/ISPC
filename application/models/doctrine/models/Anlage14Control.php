<?php

	Doctrine_Manager::getInstance()->bindComponent('Anlage14Control', 'MDAT');

	class Anlage14Control extends BaseAnlage14Control {

	    public function get_anlage14_controlsheet($ipid, $start_date, $pdf_mode = false, $patient_days = array())
		{
		    //TODO-2957 Ancuta 02.03.2020 Added new param $patient_days
			
			$data = date('Y-m-d', strtotime($start_date));

			$anlage_master = Doctrine_Query::create()
				->select('*')
				->from('Anlage14')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('DATE(`date`) = "' . $data . '"')
				->limit(1);
			$anlage_master_res = $anlage_master->fetchArray();

			if($anlage_master_res && $anlage_master_res[0]['id'] > '0')
			{
				$anlage_hospitals = Doctrine_Query::create()
					->select('*')
					->from('Anlage14Hospitals')
					->where('isdelete = "0"')
					->andWhere('ipid LIKE "' . $ipid . '"')
					->andWhere('formid = "' . $anlage_master_res[0]['id'] . '"');
				$anlage_hospitals_res = $anlage_hospitals->fetchArray();

				if($anlage_hospitals_res)
				{
					foreach($anlage_hospitals_res as $k_hosp => $v_hosp)
					{
						$hosp['start'] = date('d.m.Y', strtotime($v_hosp['hospital_start']));
						$hosp['end'] = date('d.m.Y', strtotime($v_hosp['hospital_end']));

						$anlage14_control_data['patient_hospitals'][] = $hosp;
						$hosp = array();
					}
				}
			}

			$query = Doctrine_Query::create()
				->select('*')
				->from('Anlage14Control')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('MONTH(date) = MONTH("' . $start_date . '")')
				->andWhere('YEAR(date) = YEAR("' . $start_date . '")')
				->andWhere('isdelete = "0"');
			$q_res = $query->fetchArray();

			if($q_res)
			{
				$anlage14_control_data['custom_totals']['overall_beko'] = '0';
				$anlage14_control_data['custom_totals']['overall_folgeko'] = '0';
				$anlage14_control_data['custom_totals']['overall_phones'] = '0';
				$anlage14_control_data['custom_totals']['overall_doc_nur_non_hospiz'] = '0';
				$anlage14_control_data['custom_totals']['overall_doc_nur_hospiz'] = '0';

				foreach($q_res as $k_res => $v_res)
				{
					$curent_val['qty'] = $v_res['qty'];
					$curent_val['checked'] = $v_res['value'];
					if($pdf_mode)
					{
						$anlage14_control_data[$v_res['shortcut']][date('Y-m-d', strtotime($v_res['date']))] = $curent_val;
					}
					else
					{
						$anlage14_control_data[date('Y-m-d', strtotime($v_res['date']))][$v_res['shortcut']] = $curent_val;
					}


					//construct new overall totals based on saved data
					//remember db.value == checked (1|0)
					if($v_res['shortcut'] == 'sh_beko' && $curent_val['checked'] == '1')
					{
						$anlage14_control_data['custom_totals']['overall_beko'] += $curent_val['checked'];
					}

					if($v_res['shortcut'] == 'sh_folgeko' && $curent_val['checked'] == '1')
					{
						$anlage14_control_data['custom_totals']['overall_folgeko'] += $curent_val['checked'];
					}
//
					if($v_res['shortcut'] == 'sh_telefonat')
					{
					    // TODO-2957 Ancuta 02.03.2020 #2
					    $sh_telefonat_max_ammount = 2;
					    if( in_array(date('d.m.Y',strtotime($v_res['date'])), $patient_days[$v_res['ipid']]['hospiz']['real_days_cs'])){
					        $sh_telefonat_max_ammount = 1;
					    }
					    
					    if($v_res['qty'] >= $sh_telefonat_max_ammount)
						{
						    $qty_limit = $sh_telefonat_max_ammount;
						}
						else
						{
							$qty_limit = $v_res['qty'];
						}

						$anlage14_control_data['custom_totals']['overall_phones'] += $qty_limit;
					}


					if(($v_res['shortcut'] == 'sh_doc_non_hospiz_visits' || $v_res['shortcut'] == 'sh_nur_non_hospiz_visits' || $v_res['shortcut'] == 'sh_flatrate' || $v_res['shortcut'] == 'sh_other_visits') && $curent_val['checked'] == '1')
					{
						$master_overall_data['overall_doc_nur_non_hospiz'][] = date('Y-m-d', strtotime($v_res['date']));
						$master_overall_data['overall_doc_nur_non_hospiz'] = array_unique(array_values($master_overall_data['overall_doc_nur_non_hospiz']));
					}

					$anlage14_control_data['custom_totals']['overall_doc_nur_non_hospiz'] = count($master_overall_data['overall_doc_nur_non_hospiz']);

					//TODO-3562 Carmen 05.11.2020
					//if(($v_res['shortcut'] == 'sh_doc_hospiz_visits' && $curent_val['checked'] == '1') || $v_res['shortcut'] == 'sh_nur_hospiz_visits')
					if(($v_res['shortcut'] == 'sh_doc_hospiz_visits' && $curent_val['checked'] == '1'))
					{
						$anlage14_control_data['custom_totals']['overall_doc_nur_hospiz'] += $curent_val['checked'];
					}					
					
					if($v_res['shortcut'] == 'sh_nur_hospiz_visits' && $curent_val['qty'] > 0) {
						$curent_val['checked'] = '1';
						$anlage14_control_data['custom_totals']['overall_doc_nur_hospiz'] += $curent_val['checked'];
					}
					//--
				}

				if($anlage_master_res)
				{
					foreach($anlage_master_res as $k_res => $v_res)
					{
						if($v_res['raapv_sapv_date'] != '0000-00-00 00:00:00')
						{
							$anlage_master_res[$k_res]['raapv_sapv_date'] = date('d.m.Y', strtotime($v_res['raapv_sapv_date']));
						}
						else
						{
							$anlage_master_res[$k_res]['raapv_sapv_date'] = '';
						}

						if($v_res['khws_sapv_date'] != '0000-00-00 00:00:00')
						{
							$anlage_master_res[$k_res]['khws_sapv_date'] = date('d.m.Y', strtotime($v_res['khws_sapv_date']));
						}
						else
						{
							$anlage_master_res[$k_res]['khws_sapv_date'] = '';
						}

						if($v_res['stathospiz_sapv_date'] != '0000-00-00 00:00:00')
						{
							$anlage_master_res[$k_res]['stathospiz_sapv_date'] = date('d.m.Y', strtotime($v_res['stathospiz_sapv_date']));
						}
						else
						{
							$anlage_master_res[$k_res]['stathospiz_sapv_date'] = '';
						}

						if($v_res['pwunsch_sapv_date'] != '0000-00-00 00:00:00')
						{
							$anlage_master_res[$k_res]['pwunsch_sapv_date'] = date('d.m.Y', strtotime($v_res['pwunsch_sapv_date']));
						}
						else
						{
							$anlage_master_res[$k_res]['pwunsch_sapv_date'] = '';
						}

						if($v_res['dead_sapv_date'] != '0000-00-00 00:00:00')
						{
							$anlage_master_res[$k_res]['dead_sapv_date'] = date('d.m.Y', strtotime($v_res['dead_sapv_date']));
						}
						else
						{
							$anlage_master_res[$k_res]['dead_sapv_date'] = '';
						}

						if($v_res['aapv_start'] != '0000-00-00 00:00:00')
						{
							$anlage_master_res[$k_res]['aapv_start'] = date('d.m.Y', strtotime($v_res['aapv_start']));
						}
						else
						{
							$anlage_master_res[$k_res]['aapv_start'] = '';
						}

						if($v_res['aapv_end'] != '0000-00-00 00:00:00')
						{
							$anlage_master_res[$k_res]['aapv_end'] = date('d.m.Y', strtotime($v_res['aapv_end']));
						}
						else
						{
							$anlage_master_res[$k_res]['aapv_end'] = '';
						}

						if($v_res['hospiz_start'] != '0000-00-00 00:00:00')
						{
							$anlage_master_res[$k_res]['hospiz_start'] = date('d.m.Y', strtotime($v_res['hospiz_start']));
						}
						else
						{
							$anlage_master_res[$k_res]['hospiz_start'] = '';
						}
						if($v_res['hospiz_end'] != '0000-00-00 00:00:00')
						{
							$anlage_master_res[$k_res]['hospiz_end'] = date('d.m.Y', strtotime($v_res['hospiz_end']));
						}
						else
						{
							$anlage_master_res[$k_res]['hospiz_end'] = '';
						}

						if($v_res['patient_wish_start'] != '0000-00-00 00:00:00')
						{
							$anlage_master_res[$k_res]['patient_wish_start'] = date('d.m.Y', strtotime($v_res['patient_wish_start']));
						}
						else
						{
							$anlage_master_res[$k_res]['patient_wish_start'] = '';
						}
						if($v_res['patient_wish_end'] != '0000-00-00 00:00:00')
						{
							$anlage_master_res[$k_res]['patient_wish_end'] = date('d.m.Y', strtotime($v_res['patient_wish_end']));
						}
						else
						{
							$anlage_master_res[$k_res]['patient_wish_end'] = '';
						}
					}

					$anlage14_control_data = array_merge($anlage14_control_data, $anlage_master_res[0]);
				}
//				print_r($anlage14_control_data);
//				exit;
				return $anlage14_control_data;
			}
			else
			{
				return false;
			}
		}

		public function get_period_anlage14_controlsheet($ipids, $period = false, $invoice_results = false, $excluded_shortcuts=array())
		{
			//TODO-3562 Carmen 06.11.2020 Added new param $excluded_shortcuts
			
			if(is_array($ipids))
			{
				if(count($ipids) > 0)
				{
					$ipid = $ipids;
				}
				else
				{
					$ipid[] = '9999999999999';
				}
			}
			else
			{
				$ipid = array($ipids);
			}
			
			if(count($ipid) >'0' && !empty($ipid) && $period)
			{
				$query = Doctrine_Query::create()
					->select('*')
					->from('Anlage14Control')
					->where('isdelete = "0"');
				foreach($ipids as $k_ipid => $v_ipid)
				{
					$or_sql[] = '(ipid = "'.$v_ipid.'" AND date BETWEEN "' . date('Y-m-d', strtotime($period[$v_ipid]['start'])) . '" AND "' . date('Y-m-d', strtotime($period[$v_ipid]['end'])) . '")';
				}
				$query->andWhere(implode(' OR ', $or_sql));
//				if($_REQUEST['sql_dbg'])
//				{
//					print_r($query->getSqlQuery());
//
//				}
				$q_res = $query->fetchArray();

				if($q_res)
				{
					foreach($q_res as $k_res => $v_res)
					{
						$value = '0';
						if($invoice_results)
						{
							if($v_res['shortcut'] != 'sh_telefonat')
							{
								$value = $v_res['value'];
								//TODO-3562 Carmen 06.11.2020
								if(in_array($v_res['shortcut'], $excluded_shortcuts) && $v_res['qty'] > 0)
								{
									$value = "1";
								}
								//--
							}
							else
							{
								$value = $v_res['qty'];
							}
							$anlage14_control_data[$v_res['ipid']][date('d.m.Y', strtotime($v_res['date']))][$v_res['shortcut']] = $value;
						}
						else
						{
							if($v_res['shortcut'] != 'sh_telefonat')
							{
								$value = $v_res['value'];
							}
							else
							{
								$value = $v_res['qty'];
							}
							$anlage14_control_data[$v_res['ipid']][$v_res['shortcut']][date('d.m.Y', strtotime($v_res['date']))] = $v_res['value'];
						}
					}

					return $anlage14_control_data;
				}
				else
				{
					return false;
				}
			}
		}
		
		public function get_period_shstatistik_anlage14_controlsheet($client,$ipids, $period = false, $invoice_results = false, $excluded_shortcuts = array())
		{
			//TODO-3562 Carmen 06.11.2020 Added new param $excluded_shortcuts
			
			if(is_array($ipids))
			{
				if(count($ipids) > 0)
				{
					$ipid = $ipids;
				}
				else
				{
					$ipid[] = '9999999999999';
				}
			}
			else
			{
				$ipid = array($ipids);
			}
			
			if(count($ipid) >'0' && !empty($ipid) && $period)
			{
				$date_sql = " ";
				foreach($period as $k => $date)
				{
					$start_date_time = date('Y-m-d H:i:s', strtotime($date['start']));
					$end_date_time = date('Y-m-d H:i:s', strtotime($date['end']));
					$date_sql .= ' ( DATE(date) >= DATE("' . $start_date_time . '") AND DATE(date) <= DATE("' . $end_date_time . '") )  OR ';
				}
	 
				$query = Doctrine_Query::create()
					->select('*')
					->from('Anlage14Control')
					->where('isdelete = "0"')
					->where('isdelete = "0"')
					->andWhere('client = ?',$client)
// 					->andWhereIn('ipid',$ipid)
				;
				$query->andWhere('' . substr($date_sql, 0, -4) . '');
				$q_res = $query->fetchArray();

				if($q_res)
				{
					foreach($q_res as $k_res => $v_res)
					{
						$value = '0';
						if($invoice_results)
						{
							if($v_res['shortcut'] != 'sh_telefonat')
							{
								$value = $v_res['value'];
								//TODO-3562 Carmen 06.11.2020
								if(in_array($v_res['shortcut'], $excluded_shortcuts) && $v_res['qty'] > 0)
								{
									$value = "1";
								}
								//--
							}
							else
							{
								$value = $v_res['qty'];
							}
							$anlage14_control_data[$v_res['ipid']][date('d.m.Y', strtotime($v_res['date']))][$v_res['shortcut']] = $value;
						}
						else
						{
							if($v_res['shortcut'] != 'sh_telefonat')
							{
								$value = $v_res['value'];
							}
							else
							{
								$value = $v_res['qty'];
							}
							$anlage14_control_data[$v_res['ipid']][$v_res['shortcut']][date('d.m.Y', strtotime($v_res['date']))] = $v_res['value'];
						}
					}

					return $anlage14_control_data;
				}
				else
				{
					return false;
				}
			}
		}

		
		
		
		public function get_period_anlage14_report_controlsheet($ipids, $period = false)
		{
			if(is_array($ipids))
			{
				if(count($ipids) > 0)
				{
					$ipid = $ipids;
				}
				else
				{
					$ipid[] = '9999999999999';
				}
			}
			else
			{
				$ipid = array($ipids);
			}
			
			if(count($ipid) >'0' && !empty($ipid) && $period)
			{
				
				$date_sql = " ";
				foreach($period as $k => $date)
				{
					$start_date_time = date('Y-m-d H:i:s', strtotime($date['start']));
					$end_date_time = date('Y-m-d H:i:s', strtotime($date['end']));
					$date_sql .= ' ( DATE(date) >= DATE("' . $start_date_time . '") AND DATE(date) <= DATE("' . $end_date_time . '") )  OR ';
				}
	 
				$query = Doctrine_Query::create()
					->select('*')
					->from('Anlage14Control')
					->where('isdelete = "0"')
					->andWhereIn('ipid',$ipid);
				$query->andWhere('' . substr($date_sql, 0, -4) . '');
				$q_res = $query->fetchArray();
 
				if($q_res)
				{
					foreach($q_res as $k_res => $v_res)
					{
						$value = '0';
							if($v_res['shortcut'] != 'sh_telefonat')
							{
								$value = $v_res['value'];
							}
							else
							{
								$value = $v_res['qty'];
							}
							$anlage14_control_data[$v_res['ipid']][$v_res['shortcut']][date('d.m.Y', strtotime($v_res['date']))] = $value;
					}

					return $anlage14_control_data;
				}
				else
				{
					return false;
				}
			}
		}

		/**
		 * Ancuta
		 * ISPC-2257
		 * 12.10.2018
		 * @param unknown $clientid
		 * @param unknown $patient_days
		 * @param unknown $current_period
		 * @return boolean
		 */
		public function anlage_14_invoicesactions($clientid, $patient_days = array(), $current_period = array()){

		    
		    if( empty($clientid) || empty($patient_days) || empty($current_period)){
		        return false;
		    }
		   $current_period_days = PatientMaster::getDaysInBetween($current_period['start'], $current_period['end'],false,"d.m.Y");
		   $export_data =  array();
		    
		    $ipids = array();
		    $ipids = array_keys($patient_days);

		    $shortcuts_arr = array(
		        'sh_beko',
		        'sh_folgeko',
		        'sh_doc_non_hospiz_visits',
		        'sh_nur_non_hospiz_visits',
		        'sh_other_visits',
		        'sh_doc_hospiz_visits',
		        'sh_telefonat',
		        'sh_flatrate',
		        //used only in custom totals
		        'sh_nur_visits',
		        'sh_nur_hospiz_visits',
		    );
		    
		    $visits_shortcuts = array(
		        'sh_doc_non_hospiz_visits',
		        'sh_nur_non_hospiz_visits',
		        'sh_other_visits',
		        'sh_doc_hospiz_visits',
		        //used only in custom totals
		        'sh_nur_hospiz_visits',
		        'sh_nur_non_hospiz_visits',
		    );

		    //TODO-3562 Carmen 06.11.2020
			//shortcuts calculated internaly but not shown in view
			$excluded_shortcuts = array(
					'sh_nur_visits',
					'sh_nur_hospiz_visits',
			);
			//--

		    // sapv in period
		    $patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($ipids, true);
		    //ISPC-2478 Ancuta 27.10.2020
		    $fisrt_Sapv_trigger_flatrate = false;
		    $modules = new Modules();
		    if($modules->checkModulePrivileges("246", $clientid))
		    {
		        $fisrt_Sapv_trigger_flatrate = true;
		    }
		    //--
	
		    
		    //TODO-3724 Ancuta 21-25.01.2021 Start
		    //patient days
		    $conditions_ov['periods'][0]['start'] = '2009-01-01';
		    $conditions_ov['periods'][0]['end'] = date('Y-m-d');
		    $conditions_ov['client'] = $clientid;
		    $conditions_ov['ipids'] = $ipids;
		    $conditions_ov['include_standby'] = true;// TODO-2873 Ancuta 03.02.2020 [add standby condition, for patients thata are NOW standby but had active periods]
		    
		    //beware of date d.m.Y format here
		    $patient_days_ov = Pms_CommonData::patients_days($conditions_ov);
		    // --
		    
		    
		    $overall_period_days_sapv = array();
		    $curent_period_days_sapv  = array();
		    $sapv_day2sapv_type = array();
		    $patient_Erstsapv_days = array();
		    foreach($patients_sapv_periods as $ipid=>$s_data_arr){
		        foreach($s_data_arr as $sid=>$s_details){
		            foreach($s_details['days'] as $k=>$sapv_Day){
		                
		                if( in_array($sapv_Day,$patient_days[$ipid]['real_active_days'])
		                  && in_array($sapv_Day,$current_period_days) 
		                    ){
		                    
		                    $curent_period_days_sapv[$ipid][] = $sapv_Day;
		                    
		                    $sapv_day2sapv_type[$ipid][$sapv_Day] = $s_details['highest'];
		                    
		                    
		                    $export_data[$ipid]['curent_period_days_sapv'][] = $sapv_Day;
		                    $export_data[$ipid]['sapv_day2sapv_type'][$sapv_Day] = $s_details['highest'];
		                    
		                 /*    if($s_details['sapv_order'] == '1'){
		                        $patient_Erstsapv_days[$s_details['ipid']][$s_details['id']][] =  date('Y-m-d',strtotime($sapv_Day));
		                    } */
		                    
		                }
		                
		                //TODO-3596 - Ancuta 12.10.2020  -  $patient_Erstsapv_days - move outside - so you do not trigger if start is not in current 
		                if( in_array($sapv_Day,$patient_days[$ipid]['active_days']) &&  $s_details['sapv_order'] == '1'  ){
	                        $patient_Erstsapv_days[$s_details['ipid']][$s_details['id']][] =  date('Y-m-d',strtotime($sapv_Day));
		                }
		                
	                    $overall_period_days_sapv[$ipid][] = $sapv_Day;
		            }
		        }
		    }

		    $pat_dis = new PatientDischarge();
		    $patients_discharge = $pat_dis->get_patients_discharge($ipids);
		    
		    $patients_discharge_date = array();
		    foreach($patients_discharge as $k_dis => $v_dis)
		    {
		        $patients_discharge_date[$v_dis['ipid']] = $v_dis['discharge_date'];
		    }
		    
		     
		     
		    //get patient TELEFONAT (XT) START
		    
		    $tel_array = PatientCourse::get_sh_patient_shortcuts_course($ipids, array('XT'));
		    $patient_phones = array();
		    foreach($tel_array as $k_tel => $v_tel)
		    {
		        $v_tel_date = date('d.m.Y', strtotime($v_tel['done_date']));
		         
		        if(in_array($v_tel_date, $curent_period_days_sapv[$v_tel['ipid']]))
		        {
		            // REMOVE CONTACTS AFTER DISCHARGE TIME
		            if($v_tel_date == date('d.m.Y', strtotime($patients_discharge_date[$v_tel['ipid']])) && strtotime(date('Y-m-d H:i:s', strtotime($v_tel['done_date']))) > strtotime($patients_discharge_date[$v_tel['ipid']])){
		                // do not add
		            } else{
		    
		                $patient_phones[$v_tel['ipid']][$v_tel_date]['sh_telefonat'][] = $v_tel;
		                
		                $export_data[$v_tel['ipid']]['PHONE_ALL'][] = $v_tel_date;
		                $export_data[$v_tel['ipid']]['phone2day'][$v_tel_date][] = $v_tel['id'];

		                if(!in_array($v_tel_date,$export_data[$v_tel['ipid']]['all_valid_days'])){
    		                $export_data[$v_tel['ipid']]['all_valid_days'][] = $v_tel_date;
		                }
		                
		            }
		        }
		        $v_tel_date = '';
		    }
		    
		     
		    //get patient TELEFONAT (XT) END
		    //get contact forms (ALL) START
		    $contact_forms_all = array();
		    $contact_forms_all = ContactForms::get_sh_period_contact_forms($ipids, false, false, $curent_period_days_sapv);
		     
		    // REMOVE CONTACTS AFTER DISCHARGE TIME
		    foreach($contact_forms_all as $kcf => $day_cfs)
		    {
		        foreach($day_cfs as $k_dcf => $v_dcf)
		        {
		            if(is_numeric($k_dcf))
		            {
		                if(strtotime(date('Y-m-d H:i:s', strtotime($v_dcf['start_date']))) > strtotime($patients_discharge_date[$v_dcf['ipid']]) && $patient_days[$v_dcf['ipid']]['details']['isdischarged'] == '1')
		                {
		                    unset($contact_forms_all[$kcf][$k_dcf]);
		                }
		            }
		        }
		    }
		     
		    $current_form = array('shanlage14');
		    $form_items = FormsItems::get_all_form_items($clientid, $current_form, 'v');
		     
		    foreach($form_items[$current_form[0]] as $k_item => $v_item)
		    {
		        $items_arr[] = $v_item['id'];
		    }
		     
		    $items_contact_forms = Forms2Items::get_items_forms($clientid, $items_arr);
		    foreach($contact_forms_all as $kcf => $day_cfs)
		    {
		        foreach($day_cfs as $k_dcf => $v_dcf)
		        {
		            //format contact form date to fit the format used in patients_days()
		            $contact_form_date = date('d.m.Y', strtotime($kcf));
		             
		            if( in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['real_active_days'])){
		                 
		                //all contact forms mapped with id as key
		                $contact_forms_details[$v_dcf['id']] = $v_dcf;
		                 


   		                if(!in_array($contact_form_date,$export_data[$v_dcf['ipid']]['all_valid_days'])){
       		                $export_data[$v_dcf['ipid']]['all_valid_days'][] = $contact_form_date;
   		                }
		                
		                if( in_array($v_dcf['form_type'], $items_contact_forms['sh_other_visits'])
		                    && ! in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs'])
		                    && ! in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospital']['real_days_cs'])
		                )
		                {
		                    if(!in_array($contact_form_date,$export_data[$v_dcf['ipid']]['VISITS_ALL'])){
                                $export_data[$v_dcf['ipid']]['VISITS_ALL'][] = $contact_form_date;
		                    }
		                    
		                    //catch the contact forms added by users which belong to the client setting selected groups
		                    $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_other_visits'][] = $v_dcf['id'];
		                    
// 		                    $master_data['shift_data'][$v_dcf['ipid']]['visits_other'][] = $contact_form_date;
		                }
		                 
		                
		                
		                if(in_array($v_dcf['form_type'], $items_contact_forms['sh_doc_hospiz_visits']) || in_array($v_dcf['form_type'], $items_contact_forms['sh_doc_non_hospiz_visits']))
		                {
		                    
		                	if(!in_array($contact_form_date,$export_data[$v_dcf['ipid']]['VISITS_ALL'])){
                                $export_data[$v_dcf['ipid']]['VISITS_ALL'][] = $contact_form_date;
		                    }
// 		                    $master_data['shift_data'][$v_dcf['ipid']]['visits_h'][] = $contact_form_date;
		                    
		                    //all doctor contactforms
		                    $contact_forms[$v_dcf['ipid']][$contact_form_date]['doctor_all'][] = $v_dcf['id'];
		                     
		                    //split doctors contact forms into 2 entities (hospiz and non-hospiz)
		                    //TODO-3562 Carmen 06.11.2020
		                    /* if((in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs']) || in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospital']['real_days_cs'])) && in_array($v_dcf['form_type'], $items_contact_forms['sh_doc_hospiz_visits']))
		                    { */
		                    if((in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs'])) && in_array($v_dcf['form_type'], $items_contact_forms['sh_doc_hospiz_visits']))
		                    {
		                    //--
		                        $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_doc_hospiz_visits'][] = $v_dcf['id'];
		                    }
		                    else if((!in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs']) && !in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospital']['real_days_cs'])) && in_array($v_dcf['form_type'], $items_contact_forms['sh_doc_non_hospiz_visits']))
		                    {
		                        $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_doc_non_hospiz_visits'][] = $v_dcf['id'];
		                    }
		                }
		                 
		                if(in_array($v_dcf['form_type'], $items_contact_forms['sh_nur_hospiz_visits']) || in_array($v_dcf['form_type'], $items_contact_forms['sh_nur_non_hospiz_visits']))
		                {
		                	if(!in_array($contact_form_date,$export_data[$v_dcf['ipid']]['VISITS_ALL'])){
                                $export_data[$v_dcf['ipid']]['VISITS_ALL'][] = $contact_form_date;
		                    }
		                    
// 		                    $master_data['shift_data'][$v_dcf['ipid']]['visits_N'][] = $contact_form_date;
		                    //all nurse contactforms
		                    $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_nur_visits'][] = $v_dcf['id'];
		                     
		                    //nurse contact forms in hospiz and non hospiz(non hospiz is used in "Anzahl Tagespauschale")
		                    //TODO-3562 Carmen 06.11.2020
		                    /* if((in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs']) || in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospital']['real_days_cs'])) && in_array($v_dcf['form_type'], $items_contact_forms['sh_nur_hospiz_visits']))
		                    { */
		                    if((in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs'])) && in_array($v_dcf['form_type'], $items_contact_forms['sh_nur_hospiz_visits']))
		                    {
		                    //--
		                        $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_nur_hospiz_visits'][] = $v_dcf['id'];
		                    }
		                    //nurse contact forms in hospiz and non hospiz(non hospiz is used in "Anzahl Tagespauschale")
		                    else if((!in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs']) && !in_array($contact_form_date, $patient_days[$v_dcf['ipid']]['hospital']['real_days_cs'])) && in_array($v_dcf['form_type'], $items_contact_forms['sh_nur_non_hospiz_visits']))
		                    {
		                        $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_nur_non_hospiz_visits'][] = $v_dcf['id'];
		                    }
		                }
		            }
		        }
		    }
		     
		    
		    
		    //get contact forms (ALL) END
		    //get saved data if any START
		    
		    // Add period to each ipid if no period
		    foreach($ipids as $ipid){
		        if(empty($current_period[$ipid])){
		            $current_period[$ipid]['start'] = $current_period['start']; 
		            $current_period[$ipid]['end'] = $current_period['end']; 
		        }
		    }
		    $anlage14_res = $this->get_period_anlage14_controlsheet($ipids, $current_period, true, $excluded_shortcuts); //TODO-3562 Carmen 06.11.2020
		     
		    if(!empty($anlage14_res))
		    {
		        foreach($anlage14_res as $k_res => $v_res)
		        {
		            if(!empty($v_res))
		            {
		                $has_data[$k_res] = '1';
		            }
		        }
		    }
		     
		    //get saved data if any END
		    //load saved data and create master data array START
		    //226e3758a6002ecba1cad4537f8ffe32d19a6152
// 		    dd($patient_days['226e3758a6002ecba1cad4537f8ffe32d19a6152']['real_active_days']);

		    // overall treatment days 
		    $conditions['client'] = $clientid;
		    $conditions['ipids'] = $ipids;
		    $conditions['periods'][0]['start'] = '2009-01-01';
		    $conditions['periods'][0]['end'] = date('Y-m-d');
		    
		    $sql = 'e.epid, p.ipid, e.ipid,';
		    $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
		    $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
		    $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
		    $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
		    $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
		    $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
		    
		    //be aware of date d.m.Y format here
		    $patient_days_overall = array();
		    $patient_days_overall = Pms_CommonData::patients_days($conditions, $sql);
		    
		    
		    $master_data = array();
		    $patient_29th_days = array();
		    foreach($ipids as $kk_ipid => $vv_ipid)
		    {
		        $treated_days_all[$vv_ipid] = array_values($patient_days_overall[$vv_ipid]['treatment_days']);
		         
// 		        $pat_sapv_days_dmy[$vv_ipid] = $params['sapv_overall'][$vv_ipid];
		        $pat_sapv_days_dmy[$vv_ipid] = $overall_period_days_sapv[$vv_ipid];

		        array_walk($pat_sapv_days_dmy[$vv_ipid], function(&$value) {
		            $value = date('d.m.Y', strtotime($value));
		        });
		             
		            $treated_days_all[$vv_ipid] = array_intersect($treated_days_all[$vv_ipid], $pat_sapv_days_dmy[$vv_ipid]);
		             
		            $treated_days_all_ts[$vv_ipid] = $treated_days_all[$vv_ipid];
		            array_walk($treated_days_all_ts[$vv_ipid], function(&$value) {
		                $value = strtotime($value);
		            });
		                 
		                asort($treated_days_all_ts[$vv_ipid], SORT_NUMERIC);
		                 
		                $treated_days_all_ts[$vv_ipid] = array_values(array_unique($treated_days_all_ts[$vv_ipid]));
		                 
		                
		                
		                //TODO-3669 Ancuta 11.10.2020
		                $treated_days_all[$vv_ipid] = $treated_days_all_ts[$vv_ipid];
		                array_walk($treated_days_all[$vv_ipid], function(&$value) {
		                    $value = date('d.m.Y', $value);
		                });
		                // -- 
		                    
		                
		                $pat_treatment_days[$vv_ipid] = $treated_days_all_ts[$vv_ipid];
		                array_walk($pat_treatment_days[$vv_ipid], function(&$value) {
		                    $value = date('Y-m-d', $value);
		                });
		                     
		                    $flatrate_treatment_days[$vv_ipid] = $treated_days_all_ts[$vv_ipid];
		                     
		                    if(count($flatrate_treatment_days[$vv_ipid]) > 0)
		                    {
		                        $flatrate_start[$vv_ipid] = $flatrate_treatment_days[$vv_ipid][0];
		                        $fl_days[$vv_ipid] = array();
		                        while(count($fl_days[$vv_ipid]) < '7')
		                        {
		                            if(in_array($flatrate_start[$vv_ipid], $flatrate_treatment_days[$vv_ipid]))
		                            {
		                                $fl_days[$vv_ipid][] = $flatrate_start[$vv_ipid];
		                            }
		                            else
		                            {
		                                $fl_days[$vv_ipid][] = $flatrate_treatment_days[$vv_ipid][0];
		                            }
		                             
		                            $flatrate_start[$vv_ipid] = strtotime('+1 day', $flatrate_start[$vv_ipid]);
		                        }
		                    }
		                    
		                    
		                    // ISPC-2478 Ancuta 27.10.2020 Start
		                    $days29ths[$vv_ipid] = array();
		                    if($fisrt_Sapv_trigger_flatrate){
		                    foreach ($patient_Erstsapv_days[$vv_ipid] as $sid => $s_days) {
		                        array_walk($s_days, function (&$value) {
		                            $value = date('d.m.Y', strtotime($value));
		                        });
		                            
		                            $patient_Erstsapv_days[$vv_ipid][$sid] = array_values(array_intersect($treated_days_all[$vv_ipid], $s_days));
		                            array_walk($patient_Erstsapv_days[$vv_ipid][$sid], function (&$value) {
		                                $value = date('Y-m-d', strtotime($value));
		                            });
		                    }
		                    
		                    foreach ($patient_Erstsapv_days[$vv_ipid] as $sid => $s_days) {
		                        
		                        $s_days_ts = $s_days;
		                        array_walk($s_days_ts, function (&$value) {
		                            $value = strtotime($value);
		                        });
		                            //TODO-3725 Ancuta Added [$vv_ipid] key ::START
		                            // if existing flatrates - ar in the curent $s sapv days then skip
		                            if (array_intersect($fl_days[$vv_ipid], $s_days_ts)) {} else {
		                                
		                                $flatrate_treatment_days_sapv[$vv_ipid][$sid] = $s_days_ts; 
		                                
		                                if (count($flatrate_treatment_days_sapv[$vv_ipid][$sid]) > 0) {
		                                    $flatrate_start_sapv[$vv_ipid][$sid] = $flatrate_treatment_days_sapv[$vv_ipid][$sid][0];
		                                    $flatrate_start_days_sapv[$vv_ipid][$sid] = $flatrate_treatment_days_sapv[$vv_ipid][$sid][0];
		                                    $fl_days_Sapv[$vv_ipid][$sid] = array();
		                                    while (count($fl_days_Sapv[$vv_ipid][$sid]) < '7') {
		                                        if (in_array($flatrate_start_sapv[$vv_ipid][$sid], $flatrate_treatment_days_sapv[$vv_ipid][$sid])) {
		                                            $fl_days_Sapv[$vv_ipid][$sid][] = $flatrate_start_sapv[$vv_ipid][$sid];
		                                        } else {
		                                            $fl_days_Sapv[$vv_ipid][$sid][] = $flatrate_treatment_days_sapv[$vv_ipid][$sid][0];
		                                        }
		                                        
		                                        $flatrate_start_sapv[$vv_ipid][$sid] = strtotime('+1 day', $flatrate_start_sapv[$vv_ipid][$sid]);
		                                    }
		                                    
		                                    $fl_days[$vv_ipid] = array_merge($fl_days[$vv_ipid], $fl_days_Sapv[$vv_ipid][$sid]);
		                                }
		                            }
		                            //TODO-3725 Ancuta Added [$vv_ipid] key ::END
		                    }
		                    
		                    $fl_rts[$vv_ipid]  =array();
		                    foreach($flatrate_start_days_sapv[$vv_ipid] as $sapv_id=>$start_flartare_Date){//TODO-3725 Ancuta Added ipid key
		                        $fl_rts[$vv_ipid][$sapv_id][] = $start_flartare_Date;
		                        
		                        foreach($treated_days_all_ts[$vv_ipid] as $kdt => $day_treatment){
		                            if($day_treatment > $start_flartare_Date && count($fl_rts[$vv_ipid][$sapv_id]) <  30){
		                                $fl_rts[$vv_ipid][$sapv_id][] = $day_treatment;
		                            }
		                        }
		                    }
		                    
		                    foreach($fl_rts[$vv_ipid] as $sids=>$trsdays){
		                        if(count($trsdays) >=29){
		                            $days29ths[$vv_ipid][] = end($trsdays);
		                        }
		                    }
		                    
		                    
		                    
		                    
		                    
		                    
		                    
		                    
		                    
		                    
		                    //TODO-3724 Ancuta 21-25.01.2021 Start
		                    $fall_sapv_treatment_days = array();
		                    foreach($patient_days_ov as $pipid=>$pdata){
		                        $flnr= 0;
		                        foreach($pdata['patient_active'] as $pid=>$pa){
		                            if($pa['end'] == "0000-00-00" ){
		                                $pa['end'] = date("Y-m-d");
		                            }
		                            foreach($pdata['treatment_days'] as $tk=>$tr_day){
		                                if(Pms_CommonData::isintersected(strtotime($tr_day), strtotime($tr_day), strtotime($pa['start']), strtotime($pa['end']))
		                                    && in_array($tr_day,$pat_sapv_days_dmy[$vv_ipid])
		                                    ){
		                                        $fall_sapv_treatment_days[$pipid][$flnr][] = $tr_day;
		                                }
		                            }
		                            
		                            $flnr++;
		                        }
		                    }
		                    
		                    foreach($patient_Erstsapv_days[$vv_ipid] as $sid =>$s_days){
		                        foreach($s_days as $k=>$se){
		                            if(in_array(date('d.m.Y',strtotime($se)),$treated_days_all[$vv_ipid])){
		                                $Valid_patient_Erstsapv_days[$vv_ipid][$sid][]=$se;
		                            }
		                        }
		                    }
		                    foreach($Valid_patient_Erstsapv_days[$vv_ipid] as $ksi=>$es_dates_v){
		                        $valid_erst_sapv_starts[$vv_ipid][] = date('d.m.Y',strtotime($es_dates_v[0]));
		                        $all_valid_erst_sapv_starts[$vv_ipid][] = date('d.m.Y',strtotime($es_dates_v[0]));
		                    }
		                    
		                    foreach($valid_erst_sapv_starts[$vv_ipid] as $k=>$ss_start_date){
		                        
		                        if($valid_erst_sapv_starts[$vv_ipid][$k+1]){
		                            $per_sapv_start_dates[$ss_start_date] = PatientMaster::getDaysInBetween(date('Y-m-d',strtotime($ss_start_date)), date('Y-m-d',strtotime($valid_erst_sapv_starts[$vv_ipid][$k+1])),null,"d.m.Y");
		                            
		                            foreach($per_sapv_start_dates[$ss_start_date] as $l=>$sdate){
		                                $erst_29_days[$vv_ipid][$ss_start_date][] = $sdate;
		                            }
		                            
		                            if(count($erst_29_days[$vv_ipid][$ss_start_date]) < 29){
		                                unset($valid_erst_sapv_starts[$vv_ipid][$k+1]);
		                            }
		                        }
		                    }
		                    
		                    foreach ($fall_sapv_treatment_days[$vv_ipid] as $fall_nr=>$tr_sapv_fall_days){ //date fromat d.m.Y
		                        if( count($tr_sapv_fall_days) <  29 ){
		                            // SKIP
		                            
		                        } else {
		                            // If it is the first period ever - we add the FIrst 29days  no matter the  sapv type (ERST or not)
		                            if($fall_nr == 0){
		                                // first - we add the FIRST EVER 29th date
		                                $patient_29th_days[$vv_ipid][]  = $tr_sapv_fall_days[28]; // starts with key 0
		                                
		                                // we check if here we have additional ERST falls
		                                foreach($tr_sapv_fall_days as $k=>$d){
		                                    if(strtotime($d) > strtotime($tr_sapv_fall_days[28])){
		                                        $fall_remaining_days[$vv_ipid][$fall_nr][] = $d;
		                                    }
		                                }
		                                
		                                if(count($fall_remaining_days[$vv_ipid][$fall_nr]) < 29){
		                                    //skip
		                                    
		                                } else {
		                                    
		                                    $esdays = array();
		                                    foreach($valid_erst_sapv_starts[$vv_ipid] as $sk=>$se_date){
		                                        if( in_array($se_date, $fall_remaining_days[$vv_ipid][$fall_nr]))
		                                        {
		                                            $esdays[$vv_ipid][$se_date][] = $se_date;
		                                            
		                                            foreach($fall_remaining_days[$vv_ipid][$fall_nr] as $kl=>$ftrd){
		                                                if( strtotime($ftrd) > strtotime($se_date) && !in_array($ftrd,$esdays[$vv_ipid][$se_date]) && count($esdays[$vv_ipid][$se_date] )< 29){
		                                                    $esdays[$vv_ipid][$se_date][] = $ftrd;
		                                                }
		                                            }
		                                            $patient_29th_days[$vv_ipid][] = $esdays[$vv_ipid][$se_date][28];// starts with key 0
		                                        }
		                                    }
		                                }
		                            }
		                            //If it is a following fall - we check if the we have ERST and we start counting from it
		                            elseif($fall_nr > 0){
		                                
		                                $esdays = array();
		                                
		                                foreach($valid_erst_sapv_starts[$vv_ipid] as $sk=>$se_date){
		                                    
		                                    if( in_array($se_date, $fall_sapv_treatment_days[$vv_ipid][$fall_nr]))
		                                    {
		                                        $esdays[$vv_ipid][$se_date][] = $se_date;
		                                        
		                                        foreach($fall_sapv_treatment_days[$vv_ipid][$fall_nr] as $kl=>$ftrd){
		                                            if( strtotime($ftrd) > strtotime($se_date) && !in_array($ftrd,$esdays[$vv_ipid][$se_date]) && count($esdays[$vv_ipid][$se_date] )< 29){
		                                                $esdays[$vv_ipid][$se_date][] = $ftrd;
		                                            }
		                                        }
		                                        $patient_29th_days[$vv_ipid][] = $esdays[$vv_ipid][$se_date][28];// starts with key 0
		                                    }
		                                }
		                            }
		                        }
		                    }
		                    //TODO-3724 Ancuta 21-25.01.2021 END
		                    
		                  }
		                    //ISPC-2478 Ancuta 27.10.2020 END
		                   
		                  //TODO-3743 Ancuta 25.01.2021 $beck_array
		                  $beck_array[$vv_ipid][] = $pat_treatment_days[$vv_ipid][0];
		                  foreach($all_valid_erst_sapv_starts[$vv_ipid] as $k=>$pm_day){
		                      
		                      $last_beck = end($beck_array[$vv_ipid]);
		                      $per_sapv_start_dates[$vv_ipid][$pm_day] = PatientMaster::getDaysInBetween($last_beck, date('Y-m-d',strtotime($pm_day)) ,null,"Y-m-d");
		                      
		                      if(count($per_sapv_start_dates[$vv_ipid][$pm_day]) > 28){
		                          $beck_array[$vv_ipid][] = date('Y-m-d',strtotime($pm_day));
		                      }
		                      
		                  }
		                  //--
		                  
		                  
		                    //get FLATRATE DAYS - END
		                    
		                    /* $fl_days_Ss[$vv_ipid] = $fl_days[$vv_ipid];
		                    array_walk($fl_days_Ss[$vv_ipid], function(&$value) {
		                        $value = date('Y-m-d', $value);
		                    });
		                     dd($fl_days_Ss); */
		                    foreach($current_period_days as $k_day => $v_day)
		                    {
		                        if(in_array($v_day,$patient_days[$vv_ipid]['real_active_days'])){
		    
		                            $day_is_sapv = false;
		                            if(in_array($v_day, $curent_period_days_sapv[$vv_ipid]))
		                            {
		                                $day_is_sapv = true;
		                            }
		                             
		                            foreach($shortcuts_arr as $k_short => $v_short)
		                            {
		                                if(in_array($v_short, $visits_shortcuts))
		                                {
		                                    //handle visitable shortcuts here
		                                    //stop if day is in flatrate days
		                                    //reverted ISPC-1131 - show visits in flatrate days
		                                    if($anlage14_res[$vv_ipid][$v_day][$v_short] > 0)
		                                    {
		                                        
		                                        $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
		                                        $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = $anlage14_res[$vv_ipid][$v_day][$v_short];
		                                         
		                                        if(strlen($first_active_day[$vv_ipid]) == '0')
		                                        {
		                                            $first_active_day[$vv_ipid] = $v_day;
		                                        }
		                                        $last_active_day[$vv_ipid] = $v_day;
		                                    }
		                                    else if(count($contact_forms[$vv_ipid][$v_day][$v_short]) > '0' && !array_key_exists($v_day, $anlage14_res[$vv_ipid]) && $day_is_sapv)
		                                    {
		                                        
		                                        $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
		                                        $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = count($contact_forms[$vv_ipid][$v_day][$v_short]);
		                                         
		                                        if(strlen($first_active_day[$vv_ipid]) == '0')
		                                        {
		                                            $first_active_day[$vv_ipid] = $v_day;
		                                        }
		                                        $last_active_day[$vv_ipid] = $v_day;
		                                    }
		                                    else
		                                    {
		                                        $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '0';
		                                        $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '0';
		                                    }
		                                     
		                                    if($master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] == "1"){
		                                        
		                                        $master_data['shift_data'][$vv_ipid]['visit_days'][] = $v_day;
		                                        
		                                        if($sapv_day2sapv_type[$vv_ipid][$v_day] == "4"){
		                                           $master_data['shift_data'][$vv_ipid]['visit_days_vv'][] = $v_day;
		                                        }

		                                        if($sapv_day2sapv_type[$vv_ipid][$v_day] == "3"){
		                                           $master_data['shift_data'][$vv_ipid]['visit_days_tv'][] = $v_day;
		                                        }
		                                    }
		                                    
		                                    //add to totals
		                                    $master_data['invoices'][$vv_ipid]['totals'][$v_short] += $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'];
		                                     
		                                    //add to custom overall totals
		                                    if(
		                                        $v_short == 'sh_nur_non_hospiz_visits'
		                                        || $v_short == 'sh_doc_non_hospiz_visits'
		                                        || $v_short == 'sh_other_visits'
		                                    )
		                                    {
		                                        //Anzahl Tagespauschale - total days doc/nurse non hospiz
		                                        if($master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] == '1')
		                                        {
		                                            $master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz'][] = $v_day;
		                                            $master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz'] = array_unique(array_values($master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz']));
		                                            
		                                            
		                                                $master_data['shift_data'][$vv_ipid]['visists_NOT_IN_hospiz'][] = $v_day;
		                                                if($sapv_day2sapv_type[$vv_ipid][$v_day] == "4"){
		                                                    $master_data['shift_data'][$vv_ipid]['visists_NOT_IN_hospiz_VV'][] = $v_day;
		                                                }
		                                                if($sapv_day2sapv_type[$vv_ipid][$v_day] == "3"){
		                                                    $master_data['shift_data'][$vv_ipid]['visists_NOT_IN_hospiz_TV'][] = $v_day;
		                                                }
		                                            
		                                            
		                                        }
		                                    }
		                                    else if($v_short == 'sh_nur_hospiz_visits' || $v_short == 'sh_doc_hospiz_visits')
		                                    {
		                                        //Tagespauschalen Hospiz - total visits doc/nurse in hospiz
		                                        if($master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] == '1')
		                                        {
		                                            $master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_hospiz'][] = $v_day;
		                                            $master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_hospiz'] = array_unique(array_values($master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_hospiz']));
		                                            
		                                            
		                                            $master_data['shift_data'][$vv_ipid]['visists_IN_hospiz'][] = $v_day;
		                                            
		                                            if($sapv_day2sapv_type[$vv_ipid][$v_day] == "4"){
		                                                $master_data['shift_data'][$vv_ipid]['visists_IN_hospiz_VV'][] = $v_day;
		                                            }
		                                            if($sapv_day2sapv_type[$vv_ipid][$v_day] == "3"){
		                                                $master_data['shift_data'][$vv_ipid]['visists_IN_hospiz_TV'][] = $v_day;
		                                            }
		                                        }
		                                    }
		                                     
		                                    $master_data['invoices'][$vv_ipid]['custom_totals']['sh_overall_doc_nur_non_hospiz'] = count($master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz']);
		                                    $master_data['invoices'][$vv_ipid]['custom_totals']['sh_overall_doc_nur_hospiz'] = count($master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_hospiz']);
		                                }
		                                else
		                                {
		                                    //handle the rest of shortcuts here
		                                    if($v_short == 'sh_beko')
		                                    {
		                                        if($anlage14_res[$vv_ipid][$v_day][$v_short] > '0')
		                                        {
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = $anlage14_res[$vv_ipid][$v_day][$v_short];
		                                             
		                                            if(strlen($first_active_day[$vv_ipid]) == '0')
		                                            {
		                                                $first_active_day[$vv_ipid] = $v_day;
		                                            }
		                                            $last_active_day[$vv_ipid] = $v_day;
		                                        }
// 		                                        else if(strtotime($v_day) == strtotime($pat_treatment_days[$vv_ipid][0]) && !array_key_exists($v_day, $anlage14_res[$vv_ipid]) && $day_is_sapv)
	                                            else if(in_array( date('Y-m-d',strtotime($v_day)),$beck_array[$vv_ipid])   && !array_key_exists($v_day, $anlage14_res[$vv_ipid]) && $day_is_sapv)
		                                        {//TODO-3743 Ancuta 25.01.2021 $beck_array
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '1';
		                                             
		                                            if(strlen($first_active_day[$vv_ipid]) == '0')
		                                            {
		                                                $first_active_day[$vv_ipid] = $v_day;
		                                            }
		                                            $last_active_day[$vv_ipid] = $v_day;
		                                        }
		                                        else
		                                        {
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '0';
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '0';
		                                        }
		                                         
		                                        $master_data['invoices'][$vv_ipid]['custom_totals']['sh_overall_beko'] += $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'];
		                                    }
		                                     
		                                    if($v_short == 'sh_folgeko')
		                                    {
		                                        if($fisrt_Sapv_trigger_flatrate){//TODO-3724 Ancuta 21-25.01.2021 Start
    		                                        if($anlage14_res[$vv_ipid][$v_day][$v_short] > '0')
    		                                        {
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = $anlage14_res[$vv_ipid][$v_day][$v_short];
    		                                            if(strlen($first_active_day[$vv_ipid]) == '0')
    		                                            {
    		                                                $first_active_day[$vv_ipid] = $v_day;
    		                                            }
    		                                            $last_active_day[$vv_ipid] = $v_day;
    		                                        }
    		                                        else if( in_array($v_day, $patient_29th_days[$vv_ipid]))
    		                                        {
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '1';
    		                                             
    		                                            if(strlen($first_active_day[$vv_ipid]) == '0')
    		                                            {
    		                                                $first_active_day[$vv_ipid] = $v_day;
    		                                            }
    		                                            $last_active_day[$vv_ipid] = $v_day;
    		                                        }
    		                                        else
    		                                        {
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '0';
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '0';
    		                                        }
    		                                         
    		                                        $master_data['invoices'][$vv_ipid]['custom_totals']['sh_overall_folgeko'] += $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'];
		                                        }
		                                        else
		                                        {
		                                        
    		                                        if($anlage14_res[$vv_ipid][$v_day][$v_short] > '0')
    		                                        {
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = $anlage14_res[$vv_ipid][$v_day][$v_short];
    		                                            if(strlen($first_active_day[$vv_ipid]) == '0')
    		                                            {
    		                                                $first_active_day[$vv_ipid] = $v_day;
    		                                            }
    		                                            $last_active_day[$vv_ipid] = $v_day;
    		                                        }
    		                                        else if(count($treated_days_all_ts[$vv_ipid]) >= '26' && strtotime($v_day) == $treated_days_all_ts[$vv_ipid][28] && !array_key_exists($v_day, $anlage14_res[$vv_ipid]) && $day_is_sapv)
    		                                        {
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '1';
    		                                             
    		                                            if(strlen($first_active_day[$vv_ipid]) == '0')
    		                                            {
    		                                                $first_active_day[$vv_ipid] = $v_day;
    		                                            }
    		                                            $last_active_day[$vv_ipid] = $v_day;
    		                                        }
    		                                        else
    		                                        {
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '0';
    		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '0';
    		                                        }
    		                                         
    		                                        $master_data['invoices'][$vv_ipid]['custom_totals']['sh_overall_folgeko'] += $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'];
		                                            
		                                        }
		                                        
		                                        
		                                        
		                                        
		                                        
		                                    }
		                                     
		                                    if($v_short == 'sh_flatrate')
		                                    {
		                                        if($anlage14_res[$vv_ipid][$v_day][$v_short] > '0')
		                                        {
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = $anlage14_res[$vv_ipid][$v_day][$v_short];
		                                             
		                                            if(strlen($first_active_day[$vv_ipid]) == '0')
		                                            {
		                                                $first_active_day[$vv_ipid] = $v_day;
		                                            }
		                                            $last_active_day[$vv_ipid] = $v_day;
		                                             
		                                            //append flatrate into the Anzahl Tagespauschale
		                                            $master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz'][] = $v_day;
		                                            $master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz'] = array_unique(array_values($master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz']));
		                                        }
		                                        else if(!empty($fl_days) && !array_key_exists($v_day, $anlage14_res[$vv_ipid]) && $day_is_sapv)
		                                        {
		                                            if(in_array(strtotime($v_day), $fl_days[$vv_ipid]))
		                                            {
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '1';
		                                                 
		                                                if(strlen($first_active_day[$vv_ipid]) == '0')
		                                                {
		                                                    $first_active_day[$vv_ipid] = $v_day;
		                                                }
		                                                $last_active_day[$vv_ipid] = $v_day;
		                                                 
		                                                //append flatrate into the Anzahl Tagespauschale
		                                                $master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz'][] = $v_day;
		                                                $master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz'] = array_unique(array_values($master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz']));
		                                            }
		                                            else
		                                            {
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '0';
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '0';
		                                            }
		                                        }
		                                        else
		                                        {
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '0';
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '0';
		                                        }
		                                        
		                                        
		                                        
		                                        
		                                          if($master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] =="1"){
		                                              
		                                              $export_data[$vv_ipid]['FLATRATE_DAYS'][] = $v_day;
		                                              
		                                              
		                                              if(!in_array($v_day,$export_data[$vv_ipid]['all_valid_days'])){
		                                                  $export_data[$vv_ipid]['all_valid_days'][] = $v_day;
		                                                  
                                                          if($sapv_day2sapv_type[$vv_ipid][$v_day] == "4"){
                                                                $export_data[$vv_ipid]['flatrate_days_VV'][] = $v_day;
                                                          }
                                                          
                                                          if($sapv_day2sapv_type[$vv_ipid][$v_day] == "3"){
                                                                $export_data[$vv_ipid]['flatrate_days_TV'][] = $v_day;
                                                          }
                                                        
                                                        if($sapv_day2sapv_type[$vv_ipid][$v_day] == "3"){
                                                            $master_data['shift_data'][$vv_ipid]['flatrate_days_TV'][] = $v_day;
                                                        }
		                                              }
		                                              
                                                        $master_data['shift_data'][$vv_ipid]['flatrate_days'][] = $v_day;
                                                        
                                                        if($sapv_day2sapv_type[$vv_ipid][$v_day] == "4"){
                                                            $master_data['shift_data'][$vv_ipid]['flatrate_days_VV'][] = $v_day;
                                                        }
                                                        
                                                        if($sapv_day2sapv_type[$vv_ipid][$v_day] == "3"){
                                                            $master_data['shift_data'][$vv_ipid]['flatrate_days_TV'][] = $v_day;
                                                        }
		                                        }
		                                    }
		                                     
		                                    //added limit to shown/calculate phones only in days with no Anzahl Tagepauschale triggered(has visit and/or flatrate)
		                                    if($v_short == 'sh_telefonat')
		                                    {
		                                        $qty_limit[$vv_ipid] = '0';
		                                        if($anlage14_res[$vv_ipid][$v_day][$v_short] > 0)
		                                        {
		                                            // TODO-2957 Ancuta 02.03.2020 #2
		                                            $sh_telefonat_max_ammount = 2;
		                                            if( in_array($v_day, $patient_days[$vv_ipid]['hospiz']['real_days_cs'])){
    		                                            $sh_telefonat_max_ammount = 1;
		                                            }
		                                            
		                                            //changed to show maximum 2 phones (same way as it was calculated)
		                                            //
		                                            if($anlage14_res[$vv_ipid][$v_day][$v_short] >= $sh_telefonat_max_ammount)
		                                            {
		                                                $qty_limit[$vv_ipid] = $sh_telefonat_max_ammount;
		                                            }
		                                            else
		                                            {
		                                                $qty_limit[$vv_ipid] = $anlage14_res[$vv_ipid][$v_day][$v_short];
		                                            }
		                                             
		                                            if($anlage14_res[$vv_ipid][$v_day][$v_short] > '0')
		                                            {
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = $qty_limit[$vv_ipid];
		                                                 
		                                                if(strlen($first_active_day[$vv_ipid]) == '0')
		                                                {
		                                                    $first_active_day[$vv_ipid] = $v_day;
		                                                }
		                                                $last_active_day[$vv_ipid] = $v_day;
		                                                
		                                                $master_data['shift_data'][$vv_ipid]['sh_phone_days'][] = $v_day;
		                                                
		                                                if($sapv_day2sapv_type[$vv_ipid][$v_day] == "4"){
		                                                    $master_data['shift_data'][$vv_ipid]['sh_phone_days_VV'][] = $v_day;
		                                                }
		                                                
		                                                if($sapv_day2sapv_type[$vv_ipid][$v_day] == "3"){
		                                                    $master_data['shift_data'][$vv_ipid]['sh_phone_days_TV'][] = $v_day;
		                                                }
		                                                
		                                            }
		                                            else
		                                            {
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '0';
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '0';
		                                            }
		                                        }
		                                        else if(
		                                            !in_array($v_day, $master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz'])
		                                            && !in_array(strtotime($v_day), $fl_days[$vv_ipid])
		                                            && !array_key_exists($v_day, $anlage14_res[$vv_ipid])
		                                            && $day_is_sapv
		                                            && !in_array(date("d.m.Y",strtotime($v_day)), $master_data['patients'][$v_ipid]['invoice_data']['hospital_real_days_cs'])
		                                            && !in_array($v_day, $master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_hospiz'])
		                                        )
		                                        {
		                                            // TODO-2957 Ancuta 02.03.2020 #1+
		                                            // TODO-2957 Ancuta 02.03.2020 #2
		                                            $sh_telefonat_max_ammount = 2;
		                                            if( in_array($vv_ipid, $patient_days[$vv_ipid]['hospiz']['real_days_cs'])){
		                                                $sh_telefonat_max_ammount = 1;
		                                            }
		                                            //changed to show maximum 2 phones (same way as it was calculated)
		                                            if(count($patient_phones[$vv_ipid][$v_day][$v_short]) >= $sh_telefonat_max_ammount)
		                                            {
		                                                $qty_limit[$vv_ipid] = $sh_telefonat_max_ammount;
		                                            }
		                                            else
		                                            {
		                                                $qty_limit[$vv_ipid] = count($patient_phones[$vv_ipid][$v_day][$v_short]);
		                                            }
		                                             
		                                            if(count($patient_phones[$vv_ipid][$v_day][$v_short]) > '0')
		                                            {
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = $qty_limit[$vv_ipid];
		                                                 
		                                                if(strlen($first_active_day[$vv_ipid]) == '0')
		                                                {
		                                                    $first_active_day[$vv_ipid] = $v_day;
		                                                }
		                                                $last_active_day[$vv_ipid] = $v_day;
		                                                
		                                                $master_data['shift_data'][$vv_ipid]['sh_phone_days_NOT_IN_hospiz'][] = $v_day;
		                                                
		                                                if($sapv_day2sapv_type[$vv_ipid][$v_day] == "4"){
		                                                    $master_data['shift_data'][$vv_ipid]['sh_phone_days_NOT_IN_hospiz_VV'][] = $v_day;
		                                                }
		                                                
		                                                if($sapv_day2sapv_type[$vv_ipid][$v_day] == "3"){
		                                                    $master_data['shift_data'][$vv_ipid]['sh_phone_days_NOT_IN_hospiz_TV'][] = $v_day;
		                                                }
		                                                
		                                            }
		                                            else
		                                            {
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '0';
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '0';
		                                            }
		                                        }
		                                        else if(
		                                            !in_array($v_day, $master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz'])
		                                            && !in_array(strtotime($v_day), $fl_days[$vv_ipid])
		                                            && !array_key_exists($v_day, $anlage14_res[$vv_ipid])
		                                            && $day_is_sapv
		                                            && !in_array(date("d.m.Y",strtotime($v_day)), $master_data['patients'][$v_ipid]['invoice_data']['hospital_real_days_cs'])
		                                        )
		                                        {
		                                            //changed to show maximum 2 phones (same way as it was calculated)
		                                            if(count($patient_phones[$vv_ipid][$v_day][$v_short]) >= '2')
		                                            {
		                                                $qty_limit[$vv_ipid] = "2";
		                                            }
		                                            else
		                                            {
		                                                $qty_limit[$vv_ipid] = count($patient_phones[$vv_ipid][$v_day][$v_short]);
		                                            }
		                                             
		                                            if(count($patient_phones[$vv_ipid][$v_day][$v_short]) > '0')
		                                            {
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '1';
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = $qty_limit[$vv_ipid];
		                                                 
		                                                if(strlen($first_active_day[$vv_ipid]) == '0')
		                                                {
		                                                    $first_active_day[$vv_ipid] = $v_day;
		                                                }
		                                                $last_active_day[$vv_ipid] = $v_day;
		                                            }
		                                            else
		                                            {
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '0';
		                                                $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '0';
		                                            }
		                                        }
		                                        else
		                                        {
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked'] = '0';
		                                            $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'] = '0';
		                                        }
		                                        
		                                        if($master_data['invoices'][$vv_ipid][$v_day][$v_short]['checked']  =="1"){
		                                            $master_data['shift_data'][$vv_ipid]['phone_days'][] = $v_day;
		                                        
		                                        
		                                            if($sapv_day2sapv_type[$vv_ipid][$v_day] == "4"){
		                                                $master_data['shift_data'][$vv_ipid]['phone_days_VV'][] = $v_day;
		                                            }
		                                        
		                                            if($sapv_day2sapv_type[$vv_ipid][$v_day] == "3"){
		                                                $master_data['shift_data'][$vv_ipid]['phone_days_TV'][] = $v_day;
		                                            }
		                                        }
		                                        
		                                         
		                                        //Anzahl Telefonpauschale - total phones with limit per day of 2 qty
		                                        $master_data['invoices'][$vv_ipid]['custom_totals']['sh_overall_phones'] += $qty_limit[$vv_ipid];
		                                    }
		                                     
		                                    //add to totals
		                                    $master_data['invoices'][$vv_ipid]['totals'][$v_short] += $master_data['invoices'][$vv_ipid][$v_day][$v_short]['qty'];
		                                }
		                                 
		                                $master_data['patients'][$vv_ipid]['invoice_data']['first_active_day'] = $first_active_day[$vv_ipid];
		                                $master_data['patients'][$vv_ipid]['invoice_data']['last_active_day'] = $last_active_day[$vv_ipid];
		                                 
		                                $master_data['invoices'][$vv_ipid]['custom_totals']['sh_overall_doc_nur_non_hospiz'] = count($master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_non_hospiz']);
		                                $master_data['invoices'][$vv_ipid]['custom_totals']['sh_overall_doc_nur_hospiz'] = count($master_overall_data['invoices'][$vv_ipid]['overall_doc_nur_hospiz']);
		                            }
		                            //						}
		                             
		                        }
		                    }
		    }

		    
		    //load saved data and create master data array END
		    $result['master_data_invoices'] = $master_data['invoices'] ;	    
		    $result['master_data'] = $master_data['shift_data'] ;	    
		    $result['master_data_overall'] = $master_overall_data;	    
		    $result['export_data'] = $export_data;	    
		    
		    return $result;
		    
		}
		
		
	}

?>