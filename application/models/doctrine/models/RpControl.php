<?php

	Doctrine_Manager::getInstance()->bindComponent('RpControl', 'MDAT');

	class RpControl extends BaseRpControl {

		public function get_rp_controlsheet($ipid, $date_start = false, $date_end = false)
		{
			$query = Doctrine_Query::create()
				->select('*')
				->from('RpControl')
				->where('ipid = ?', $ipid )
				->andWhere('isdelete="0"');

			if($date_start && !$date_end)
			{
				$query->andWhere('MONTH(date) = MONTH("' . $date_start . '")');
				$query->andWhere('YEAR(date) = YEAR("' . $date_start . '")');
			}
			else if($date_start && $date_end)
			{
				$query->andWhere('DATE(date) BETWEEN DATE("' . $date_start . '") AND DATE("' . $date_end . '")');
			}

			$q_res = $query->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$formated_date = date('Y-m-d', strtotime($v_res['date']));
					$master_data[$v_res['shortcut']][$formated_date]['p_home'] = $v_res['qty_home'];
					$master_data[$v_res['shortcut']][$formated_date]['p_nurse'] = $v_res['qty_nurse'];
					$master_data[$v_res['shortcut']][$formated_date]['p_hospiz'] = $v_res['qty_hospiz'];
				}

				return $master_data;
			}
			else
			{
				return false;
			}
		}
		
		
		public function get_rp_multiple_controlsheet($ipids,$specific_period_days= false)
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
				
			
			$query = Doctrine_Query::create()
				->select('*')
				->from('RpControl')
				->whereIn('ipid', $ipid)
				->andWhere('isdelete="0"');
			$q_res = $query->fetchArray();

			if($q_res)
			{
				
				foreach($q_res as $k_res => $v_res)
				{
					if($specific_period_days) {
						
						$formated_date = date('Y-m-d', strtotime($v_res['date']));
						
						if(in_array($formated_date,$specific_period_days[$v_res['ipid']])) {
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['p_home'] = $v_res['qty_home'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['p_nurse'] = $v_res['qty_nurse'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['p_hospiz'] = $v_res['qty_hospiz'];
						}
					}
					else 
					{
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['p_home'] = $v_res['qty_home'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['p_nurse'] = $v_res['qty_nurse'];
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date]['p_hospiz'] = $v_res['qty_hospiz'];
					}
				}

				return $master_data;
			}
			else
			{
				return false;
			}
		}
		
		
		public function rp_invoice_sapv_period($ipid,$period_id=false,$curent_period,$source = 'invoice'){

			set_time_limit(0);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$patientmaster = new PatientMaster();
			$tab_menus = new TabMenus();
			$client = new Client();
			$p_list = new PriceList();
			$user = new User();
			$usergroups = new Usergroup();
			$patientdischarge = new PatientDischarge();
			$discharge_method = new DischargeMethod();
			
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$patient_epid = strtoupper(Pms_CommonData::getEpid($ipid));
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$sapv = new SapvVerordnung();
			//get patinet info
			$patientmaster = new PatientMaster();
			$current_period_days = $patientmaster->getDaysInBetween($curent_period['start'], $curent_period['end']);
			
			//user Betriebsstätten-Nr.
			$user = Doctrine::getTable('User')->find($userid);
			if($user)
			{
				$uarray = $user->toArray();
			}
			
			//get client details
			$clientdata = Pms_CommonData::getClientData($clientid);
			
			//get all sapvs
			$patient_sapvs = $sapv->get_all_sapvs($ipid);
				
			foreach($patient_sapvs as $k=>$sv_data){
				$st_date = date('Y-m-d',strtotime($sv_data['verordnungam']));
				$sapv_period2type[$st_date] = $sv_data['verordnet'];
			}
			
			
			$patient_discharge = $patientdischarge->getPatientDischarge($ipid);
			$discharge_dead_date = '';
			if($patient_discharge)
			{
				//get discharge methods
				$discharge_methods = $discharge_method->getDischargeMethod($clientid, 0);
			
				foreach($discharge_methods as $k_dis_method => $v_dis_method)
				{
					if($v_dis_method['abbr'] == "TOD" || $v_dis_method['abbr'] == "TODNA")
					{
						$death_methods[] = $v_dis_method['id'];
					}
				}
				$death_methods = array_values(array_unique($death_methods));
			
				if(in_array($patient_discharge[0]['discharge_method'], $death_methods))
				{
					$discharge_dead_date = date('Y-m-d', strtotime($patient_discharge[0]['discharge_date']));
				}
			}
			
			//construct sapv_period_selector
			//if no sapvid requested then use the last sapv
			$has_no_sapv = true;
			foreach($patient_sapvs as $k_sapv => $v_sapv)
			{
				$sapv_dates['id'] = $v_sapv['id'];
				$sapv_dates['from'] = date('Y-m-d', strtotime($v_sapv['verordnungam']));
				$sapv_dates['till'] = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
				$sapv_dates['type'] = trim($v_sapv['verordnet']);
			
				$sapv_selector_source[$v_sapv['id']] = $sapv_dates;
				$has_no_sapv = false;
			}
			if($period_id > 0)
			{
				$selected_period = $period_id;
			}
			else if(empty($period_id) || $period_id == '0')
			{
				$last_sel_per = end($sapv_selector_source);
				$selected_period = $last_sel_per['id'];
			}
			
			$first_sapv_id = $patient_sapvs[0]['id'];
			
			if($has_no_sapv === false)
			{
				//get curent verordnung date from-till
				$curent_sapv_from = $sapv_selector_source[$selected_period]['from'];
				$curent_sapv_till = $sapv_selector_source[$selected_period]['till'];
				$curent_sapv_type = $sapv_selector_source[$selected_period]['type'];
				//overide the end of selected sapv period with discharge date
				if(strlen($discharge_dead_date) > 0)
				{
					if(strtotime($curent_sapv_till) > strtotime($discharge_dead_date))
					{
						$curent_sapv_till = date('Y-m-d', strtotime($discharge_dead_date));
					}
			
					if(strtotime($curent_sapv_from) > strtotime($discharge_dead_date))
					{
						$curent_sapv_from = date('Y-m-d', strtotime($discharge_dead_date));
					}
				}
			
			
				// check if there were sapv periods with only BE
				foreach($sapv_period2type as $per_start => $per_type )
				{
					if(strtotime($per_start)  < strtotime($curent_sapv_from)  && $per_type == "1")
					{
						$only_be_before[] =  $per_start ;
					} else {
						$execpt_be[] =  $per_start ;
					}
				}
			
			
				$curent_sapv_from_f = date('d.m.Y', strtotime($curent_sapv_from));
				$curent_sapv_till_f = date('d.m.Y', strtotime($curent_sapv_till));
			
				$curent_period['start'] = $curent_sapv_from;
				$curent_period['end'] = $curent_sapv_till;
				$pd_curent_period['start'] = $curent_sapv_from;
				$pd_curent_period['end'] = $curent_sapv_till;
			
			}
			else
			{
				$curent_period['start'] = '1970-01-01';
				$curent_period['end'] = '1970-01-01';
				$pd_curent_period['start'] = '2009-01-01';
				$pd_curent_period['end'] = date('Y-m-d');
			
			}
			
			// get active days of patient
			$conditions['periods'][0]['start'] = $pd_curent_period['start'];
			$conditions['periods'][0]['end'] = $pd_curent_period['end'];
			$conditions['client'] = $clientid;
			$conditions['ipids'] = array($ipid);
			$patient_days = Pms_CommonData::patients_days($conditions);
				
			if($_REQUEST['dbg_pd']=="1"){
				print_r($patient_days); exit;
			}
			// if patient had an only be before
			$bill_assessment = 1;
			$bill_secondary_assessment = 0;
			if(isset($only_be_before) && !empty($only_be_before)){
				$admission_days = $patient_days[$ipid]['admission_days'];
					
				$last_only_be = end($only_be_before);
				$last_admission_date  = end($admission_days);
			
				if(strtotime($last_only_be) < strtotime($last_admission_date)){
					$from_sapv_be2patient_admision = $patientmaster->getDaysInBetween($last_only_be, $last_admission_date);
					if(count($from_sapv_be2patient_admision) < 28 ){
						// if the next admission is within 28 days after the Beratung admission, NO assessment is again billed
						$bill_assessment = 0;
						$bill_secondary_assessment = 0;
			
					} else {
						//if the next admission is AFTER 28 days after the Beratung admission, a "reduziertes Assessment" is only billed
						$bill_assessment = 0;
						$bill_secondary_assessment = 1;
					}
				}
			}
			
			//get patient locations and construct day2location_type arr
			$pat_locations = PatientLocation::get_period_locations($ipid, $curent_period);
			
			
			foreach($pat_locations as $k_pat => $v_pat)
			{
				if($v_pat['discharge_location'] == "0")
				{
					foreach($v_pat['all_days'] as $k_day => $v_day)
					{
						if(in_array(date("d.m.Y",strtotime($v_day)),$patient_days[$ipid]['real_active_days']) )
						{ // allow only location days that are included in patient active days
							$pat_days2loctype[$v_day] = $v_pat['master_details']['location_type'];
						}
					}
				}
			}
			
			
			//get default products pricelist
			$shortcuts = Pms_CommonData::get_prices_shortcuts();
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();
			
			$invoice_date_start = $invoice_date_end = date('Y-m-d', time());
			
			//			$ppl = PriceList::get_period_price_list($curent_sapv_from, $curent_sapv_till);
			if(!$curent_sapv_from)
			{
				$pr_date_from = $pr_date_till = date('Y-m-d', time());
				$ppl = PriceList::get_period_price_list($pr_date_from, $pr_date_till);
			}
			else
			{
				$ppl = PriceList::get_period_price_list($curent_sapv_from, $curent_sapv_till);
			}
			
			// get product shortcuts
			// grid is based on this -> latter use this arr
			// as source to build products arr
			
			//location type to price_type mapping
			$location_type_match = Pms_CommonData::get_rp_price_mapping();
			
			foreach($shortcuts['rp'] as $k_short => $v_short)
			{
				if(!$curent_sapv_from)
				{
					$price_date = date('Y-m-d', time());
				}
				else
				{
					$price_date = $curent_sapv_from;
				}
// 				$products[$v_short]['shortcut'] = $v_short;
// 				$products[$v_short]['price'] = '';
// 				$products[$v_short]['qty_gr']['p_home'] = '0';
// 				$products[$v_short]['price_gr']['p_home'] = $ppl[$price_date][0][$v_short]['p_home'];
// 				$products[$v_short]['total']['p_home'] = '0.00';
			
// 				$products[$v_short]['qty_gr']['p_nurse'] = '0';
// 				$products[$v_short]['price_gr']['p_nurse'] = $ppl[$price_date][0][$v_short]['p_nurse'];
// 				$products[$v_short]['total']['p_nurse'] = '0.00';
			
// 				$products[$v_short]['qty_gr']['p_hospiz'] = '0';
// 				$products[$v_short]['price_gr']['p_hospiz'] = $ppl[$price_date][0][$v_short]['p_hospiz'];
// 				$products[$v_short]['total']['p_hospiz'] = '0.00';
			}
			
			/*------------------- Get all visits  details --------------------------*/
			//get used form types
			$form_types = new FormTypes();
			$all_forms = $form_types->get_form_types($clientid);
			$set_one = $form_types->get_form_types($clientid, '1');
			foreach($set_one as $k_set_one => $v_set_one)
			{
				$set_one_ids[] = $v_set_one['id'];
			}
				
			//get doctor and nurse users
			//get all related users details
			$master_groups_first = array('4', '5');
				
			$client_user_groups_first = $usergroups->getUserGroups($master_groups_first);
				
			foreach($client_user_groups_first as $k_group_f => $v_group_f)
			{
				$master2client[$v_group_f['groupmaster']][] = $v_group_f['id'];
			}
				
			$client_users = $user->getClientsUsers($clientid);
				
			$nurse_users = array();
			$doctor_users = array();
			foreach($client_users as $k_cuser_det => $v_cuser_det)
			{
				$master_user_details[$v_cuser_det['id']] = $v_cuser_det;
				if(in_array($v_cuser_det['groupid'], $master2client['5']))
				{
					$nurse_users[] = $v_cuser_det['id'];
				}
				else if(in_array($v_cuser_det['groupid'], $master2client['4']))
				{
					$doctor_users[] = $v_cuser_det['id'];
				}
			}
				
			//get curent contact forms
// 			$contact_forms = $this->get_period_contact_forms($ipid, $curent_period, false, true);
			$contact_forms = ContactForms::get_period_contact_forms_special($ipid, $curent_period, false, true);
			$doctor_contact_forms = array();
			$nurse_contact_forms = array();
				
			ksort($contact_forms);
			
			foreach($contact_forms as $kcf => $day_cfs)
			{
				foreach($day_cfs as $k_dcf => $v_dcf)
				{
					$all_contact_forms[] = $v_dcf;
				}
			}
				
			foreach($all_contact_forms as $k_cf => $v_cf)
			{
				//visit date formated
				$visit_date = date('Y-m-d', strtotime($v_cf['billable_date']));
					
				//switch shortcut_type based on patient location for *visit* date
				$vday_matched_loc_price_type = $location_type_match[$pat_days2loctype[$visit_date]];
					
				//switch shortcut doctor/nurse
				$shortcut_switch = false;
				if(in_array($v_cf['create_user'], $doctor_users))
				{
					$shortcut_switch = 'doc';
				}
				else if(in_array($v_cf['create_user'], $nurse_users))
				{
					$shortcut_switch = 'nur';
				}
					
				//create products (doc||nurse)
				if(strlen($vday_matched_loc_price_type) > 0 && $shortcut_switch && in_array($v_cf['form_type'], $set_one_ids))
				{
					if($ppl[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type] != '0.00')
					{
						$contact_forms2date[date('Y-m-d', strtotime($v_cf['date']))][] = $v_cf;
					}
				}
			}
			//check if patient has saved data in db
			$saved_data = RpControl::get_rp_controlsheet($ipid, $curent_period['start'], $curent_period['end']);
			//			print_r($curent_period);
			//			exit;
			
			if($saved_data)
			{
				//reconstruct array
				foreach($saved_data as $k_shortcut => $v_sv_data)
				{
					$saved_data_arr[$k_shortcut]['shortcut'] = $k_shortcut;
			
					foreach($v_sv_data as $k_date => $v_qty)
					{
						if($ppl[$k_date][0][$k_shortcut]['p_home'] != '0.00')
						{
							$saved_data_arr[$k_shortcut]['qty_gr']['p_home'] += $v_qty['p_home'];
							$saved_data_arr[$k_shortcut]['price_gr']['p_home'] = $ppl[$k_date][0][$k_shortcut]['p_home'];
							$saved_data_arr[$k_shortcut]['total']['p_home'] += ($v_qty['p_home'] * $ppl[$k_date][0][$k_shortcut]['p_home']);
							if($v_qty['p_home'] != 0  && !empty($contact_forms2date[$k_date])){
								$dates[$k_shortcut][$k_date] += $v_qty['p_home'];
							}
						}
			
						if($ppl[$k_date][0][$k_shortcut]['p_nurse'] != '0.00')
						{
							$saved_data_arr[$k_shortcut]['qty_gr']['p_nurse'] += $v_qty['p_nurse'];
							$saved_data_arr[$k_shortcut]['price_gr']['p_nurse'] = $ppl[$k_date][0][$k_shortcut]['p_nurse'];
							$saved_data_arr[$k_shortcut]['total']['p_nurse'] += ($v_qty['p_nurse'] * $ppl[$k_date][0][$k_shortcut]['p_nurse']);
							if($v_qty['p_nurse'] != 0   && !empty($contact_forms2date[$k_date]) ){
								$dates[$k_shortcut][$k_date] += $v_qty['p_nurse'];
							}
						}
			
						if($ppl[$k_date][0][$k_shortcut]['p_hospiz'] != '0.00')
						{
							$saved_data_arr[$k_shortcut]['qty_gr']['p_hospiz'] += $v_qty['p_hospiz'];
							$saved_data_arr[$k_shortcut]['price_gr']['p_hospiz'] = $ppl[$k_date][0][$k_shortcut]['p_hospiz'];
							$saved_data_arr[$k_shortcut]['total']['p_hospiz'] += ($v_qty['p_hospiz'] * $ppl[$k_date][0][$k_shortcut]['p_hospiz']);
							if($v_qty['p_hospiz'] != 0   && !empty($contact_forms2date[$k_date]) ){
								$dates[$k_shortcut][$k_date] += $v_qty['p_hospiz'];
							}
						}
					}
				}
			
				$products = $saved_data_arr;
			}
			
			foreach($dates as $dn_sh=>$visits_values)
			{
				if($dn_sh == "rp_doc_2" || $dn_sh == "rp_nur_2")
				{
					foreach($visits_values as $date=>$saved_qty)
					{
						if($saved_qty != 0 && (count($contact_forms2date[$date]) <= $saved_qty) )
						{
							foreach($contact_forms2date[$date] as $cfk=>$cf_data)
							{
								$extra_data['home_visit'][$cf_data['id']] = $cf_data;
							}
						}
						else if($saved_qty != 0 && (count($contact_forms2date[$date]) > $saved_qty) )
						{
							$cfs[$date] = 0;
								
							foreach($contact_forms2date[$date] as $cfk=>$cf_data)
							{
								if( $cfs[$date] <= $saved_qty )
								{
									$extra_data['home_visit'][$cf_data['id']] = $cf_data;
									$cfs[$date]++;
								}
							}
						}
					}
				}
			}
			
			if(!$saved_data)
			{
				if($has_no_sapv === false)
				{
					if($source == "form"){
						foreach($current_period_days as $k_sday => $v_sday)
						{
							foreach($shortcuts['rp'] as $k_short => $v_short)
							{
								$products[$v_short][$v_sday]['p_home'] = '0';
								$products[$v_short][$v_sday]['p_nurse'] = '0';
								$products[$v_short][$v_sday]['p_hospiz'] = '0';
							}
						}
						if($curent_sapv_type == "1"){
								
							
							//GATHER CONTROL SHEET DATA START
							//Ebene 1 (Assessment / Beratung) - RP-Assessment marked as completed
							if($bill_assessment == "1"){
								$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $current_period);
	
								if($curent_sapv_type == "1"){
									$rp_asses = $rp_asses[0]; // bill only first assessment
								}	
								foreach($rp_asses as $k_assessment => $v_assessment)
								{
									$location_matched_price = $location_type_match[$pat_days2loctype[$v_assessment['completed_date']]];
								
									if(strlen($location_matched_price) > 0)
									{
										$products['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price] += '1';
									}
								}
							}
	
							//Ebene 1 (reduziertes Assessment) - Not used yet
							if($bill_secondary_assessment == "1"){
								$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $current_period);
						 
								foreach($rp_asses as $k_assessment => $v_assessment)
								{
									$location_matched_price = $location_type_match[$pat_days2loctype[$v_assessment['completed_date']]];
										
									if(strlen($location_matched_price) > 0)
									{
										$products['rp_eb_2'][$v_assessment['completed_date']][$location_matched_price] += '1';
									}
								}
							}
							
							//DOCTOR and NURSE VISITS - all
							//get used form types
							$form_types = new FormTypes();
							$set_one = $form_types->get_form_types($clientid, '1');
							foreach($set_one as $k_set_one => $v_set_one)
							{
								$set_one_ids[] = $v_set_one['id'];
							}
							
							//get doctor and nurse users
							//get all related users details
							$master_groups_first = array('4', '5');
							
							$client_user_groups_first = $usergroups->getUserGroups($master_groups_first);
							
							foreach($client_user_groups_first as $k_group_f => $v_group_f)
							{
								$master2client[$v_group_f['groupmaster']][] = $v_group_f['id'];
							}
							
							$client_users = $user->getClientsUsers($clientid);
							
							$nurse_users = array();
							$doctor_users = array();
							foreach($client_users as $k_cuser_det => $v_cuser_det)
							{
								$master_user_details[$v_cuser_det['id']] = $v_cuser_det;
								if(in_array($v_cuser_det['groupid'], $master2client['5']))
								{
									$nurse_users[] = $v_cuser_det['id'];
								}
								else if(in_array($v_cuser_det['groupid'], $master2client['4']))
								{
									$doctor_users[] = $v_cuser_det['id'];
								}
							}
							
							//get curent contact forms
// 							$contact_forms = ContactForms::get_period_contact_forms_special($ipid, $current_period, false, true);
							
// 							$doctor_contact_forms = array();
// 							$nurse_contact_forms = array();
							
// 							foreach($contact_forms as $kcf => $day_cfs)
// 							{
// 								foreach($day_cfs as $k_dcf => $v_dcf)
// 								{
// 									$all_contact_forms[] = $v_dcf;
// 								}
// 							}
	
// 							$doctor_cfs = array();
// 							$nurse_cfs = array();
// 							foreach($all_contact_forms as $k_cf => $v_cf)
// 							{
// 								if(in_array($v_cf['create_user'], $doctor_users))
// 								{
// // 									if(empty($doctor_cfs)){
// 										$doctor_cfs[] =$v_cf['start_date'];
// // 									}
// 								}
// 								else if(in_array($v_cf['create_user'], $nurse_users))
// 								{
// 									if(empty($nurse_cfs)){
// 										$nurse_cfs[] =$v_cf;
// 									}
// 								}
// 							}
// 							print_r('ser');
// 							print_r($doctor_cfs);
				 
// 							// one per doctor, one per nurse
// 							$all_dn_contact_froms = array_merge($doctor_cfs,$nurse_cfs);


							$prod_vis_ident = array();

							foreach($all_contact_forms as $k_cf => $v_cf)
							{
								//visit date formated
								$visit_date = date('Y-m-d', strtotime($v_cf['date']));
							
								//switch shortcut_type based on patient location for *visit* date
								$vday_matched_loc_price_type = $location_type_match[$pat_days2loctype[$visit_date]];
							
								//switch shortcut doctor/nurse
								$shortcut_switch = false;
								if(in_array($v_cf['create_user'], $doctor_users))
								{
									$shortcut_switch = 'doc';
								}
								else if(in_array($v_cf['create_user'], $nurse_users))
								{
									$shortcut_switch = 'nur';
								}
							
								//create products (doc||nurse)
								if(strlen($vday_matched_loc_price_type) > 0 && $shortcut_switch && in_array($v_cf['form_type'], $set_one_ids))
								{
									if($ppl[$v_sapv_day][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type] != '0.00')
									{
										//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
										$shortcut = 'rp_' . $shortcut_switch . '_2';
										$qty[$vday_matched_loc_price_type] = '1';
										
										if(!isset($products_cnt[$shortcut][$vday_matched_loc_price_type])){
											$products_cnt[$shortcut][$vday_matched_loc_price_type] = 0;
										}
										
										if($products_cnt[$shortcut][$vday_matched_loc_price_type] == 0){
											
											$products['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type] += 1;
											if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
											{
												$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
												$products_cnt[$shortcut][$vday_matched_loc_price_type]++;
												
												$prod_vis_ident[$shortcut_switch][] = $v_cf['id'];
											}
											
										}
									}
							
									$shortcut = '';
									$qty[$vday_matched_loc_price_type] = '';
									
									
									
									
									//DOCTOR VISITS - Ärztlicher Hausbesuch - price for the doctor visit from minute 20 - 60 (rp_doc_1||rp_nur_1)
									if($v_cf['visit_duration'] >= '0')
									{
										$shortcut = 'rp_' . $shortcut_switch . '_1';
										if(!isset($products_cnt[$shortcut][$vday_matched_loc_price_type]) ){
											$products_cnt[$shortcut][$vday_matched_loc_price_type] = 0;
										}
										
										if( $products_cnt[$shortcut][$vday_matched_loc_price_type] == 0 && in_array($v_cf['id'],$prod_vis_ident[$shortcut_switch]) ){

											$qty[$vday_matched_loc_price_type] = '1';
											
											if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
											{
												$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
												$products_cnt[$shortcut][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
											}
										}
									}
									$shortcut ='';
									//DOCTOR VISITS - Zusatzentgeld Arzt - for EVERY 15 minutes more from minute 61 this product is added. (doctor) (rp_doc_3||rp_nur_3)
									$multiplier = '';
									$qty[$vday_matched_loc_price_type] = '';
									if($v_cf['visit_duration'] > '45')
									{
										// calculate multiplier of 15 minutes after 60 min (round up)
										// ISPC-2006 :: From 60 was changed to 45
										// calculate multiplier of 15 minutes after 45 min (round up)
										$shortcut = 'rp_' . $shortcut_switch . '_3';
										if(!isset($products_cnt[$shortcut][$vday_matched_loc_price_type]) ) {
											$products_cnt[$shortcut][$vday_matched_loc_price_type] = 0; 
										}
										
										if($products_cnt[$shortcut][$vday_matched_loc_price_type] == 0 && in_array($v_cf['id'],$prod_vis_ident[$shortcut_switch]) ){
											
											$multiplier = ceil(($v_cf['visit_duration'] - 45) / 15);
											
											$qty[$vday_matched_loc_price_type] = $multiplier; //multiplier value
								
											if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
											{
												$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
												$products_cnt[$shortcut][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
											}
										}
									}
							
									$shortcut = '';
									$qty[$vday_matched_loc_price_type] = '';
									//DOCTOR VISITS - Abschlag Arzt - reduction of the doctor visit price when visit is shorter than 20 minutes.(rp_doc_4||||rp_nur_4)
									if($v_cf['visit_duration'] < '20')
									{
										$shortcut = 'rp_' . $shortcut_switch . '_4';
										
										if(!isset($products_cnt[$shortcut][$vday_matched_loc_price_type]) ) {
											$products_cnt[$shortcut][$vday_matched_loc_price_type] = 0;
										}
										
										if( $products_cnt[$shortcut][$vday_matched_loc_price_type] == 0 && in_array($v_cf['id'],$prod_vis_ident[$shortcut_switch]) ){
											$qty[$vday_matched_loc_price_type] = '1';
								
											if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
											{
												$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
												$products_cnt[$shortcut][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
											}
										}
									}
							
									$shortcut = '';
									$qty[$vday_matched_loc_price_type] = '';
								}
							}
							
							//Fallabschluss - patient death coordination. added once (rp_pat_dead)
							if(strlen($discharge_dead_date) > 0)
							{
								//visit date formated
// 								$visit_date = date('Y-m-d', strtotime($discharge_dead_date));
							
// 								//switch shortcut_type based on patient location for *visit* date
// 								$dead_matched_loc_price_type = $location_type_match[$pat_days2loctype[$discharge_dead_date]];
// 								$qty[$vday_matched_loc_price_type] = '1';
// 								if($dead_matched_loc_price_type && $ppl[$v_sapv_day][0]['rp_pat_dead'][$dead_matched_loc_price_type] != '0.00')
// 								{
// 									$products['rp_pat_dead'][$v_sapv_day][$dead_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
// 								}
							}
							//GATHER CONTROL SHEET DATA END
							
						} 
						else // OTHER SAPV TYPES 
						{
									
								//GATHER CONTROL SHEET DATA START
								//Ebene 1 (Assessment / Beratung) - RP-Assessment marked as completed
								
								if($bill_assessment =="1"){
									$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $current_period);
		
									if($curent_sapv_type == "1"){
										$rp_asses = $rp_asses[0]; // bill only first assessment
									}	
									foreach($rp_asses as $k_assessment => $v_assessment)
									{
										$location_matched_price = $location_type_match[$pat_days2loctype[$v_assessment['completed_date']]];
									
										if(strlen($location_matched_price) > 0)
										{
											$products['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price] += '1';
										}
									}
								}
		
								//Ebene 1 (reduziertes Assessment) - Not used yet
								if($bill_secondary_assessment =="1"){
									$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $current_period);
							 
									foreach($rp_asses as $k_assessment => $v_assessment)
									{
										$location_matched_price = $location_type_match[$pat_days2loctype[$v_assessment['completed_date']]];
											
										if(strlen($location_matched_price) > 0)
										{
											$products['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price] += '1';
										}
									}
								}
								
								
								//Ebene 2 - the daily added price when patient is active and has Verordnung
								foreach($patient_sapvs as $k_pat_sapv => $v_pat_sapv)
								{
									$sapvdays = $patientmaster->getDaysInBetween(date('Y-m-d', strtotime($v_pat_sapv['verordnungam'])), date('Y-m-d', strtotime($v_pat_sapv['verordnungbis'])));
								
									if(empty($sapv_days))
									{
										$sapv_days = array();
									}
								
									$sapv_days = array_merge($sapv_days, $sapvdays);
									$sapv_days = array_values(array_unique($sapv_days));
								}
								
								foreach($sapv_days as $k_sapv_day => $v_sapv_day)
								{
									$sapvday_loc_matched_price = $location_type_match[$pat_days2loctype[$v_sapv_day]];
								
									if(strlen($sapvday_loc_matched_price) > 0 && $ppl[$v_sapv_day][0]['rp_eb_3'][$sapvday_loc_matched_price] != '0.00')
									{
										$products['rp_eb_3'][$v_sapv_day][$sapvday_loc_matched_price] += 1;
									}
								}
								
								//DOCTOR and NURSE VISITS - all
								//get used form types
								$form_types = new FormTypes();
								$set_one = $form_types->get_form_types($clientid, '1');
								foreach($set_one as $k_set_one => $v_set_one)
								{
									$set_one_ids[] = $v_set_one['id'];
								}
								
								//get doctor and nurse users
								//get all related users details
								$master_groups_first = array('4', '5');
								
								$client_user_groups_first = $usergroups->getUserGroups($master_groups_first);
								
								foreach($client_user_groups_first as $k_group_f => $v_group_f)
								{
									$master2client[$v_group_f['groupmaster']][] = $v_group_f['id'];
								}
								
								$client_users = $user->getClientsUsers($clientid);
								
								$nurse_users = array();
								$doctor_users = array();
								foreach($client_users as $k_cuser_det => $v_cuser_det)
								{
									$master_user_details[$v_cuser_det['id']] = $v_cuser_det;
									if(in_array($v_cuser_det['groupid'], $master2client['5']))
									{
										$nurse_users[] = $v_cuser_det['id'];
									}
									else if(in_array($v_cuser_det['groupid'], $master2client['4']))
									{
										$doctor_users[] = $v_cuser_det['id'];
									}
								}
								
								//get curent contact forms
								$contact_forms = ContactForms::get_period_contact_forms($ipid, $current_period, false, true);
								
								$doctor_contact_forms = array();
								$nurse_contact_forms = array();
								
								foreach($contact_forms as $kcf => $day_cfs)
								{
									foreach($day_cfs as $k_dcf => $v_dcf)
									{
										$all_contact_forms[] = $v_dcf;
									}
								}
								
								foreach($all_contact_forms as $k_cf => $v_cf)
								{
									//visit date formated
									$visit_date = date('Y-m-d', strtotime($v_cf['billable_date']));
								
									//switch shortcut_type based on patient location for *visit* date
									$vday_matched_loc_price_type = $location_type_match[$pat_days2loctype[$visit_date]];
								
									//switch shortcut doctor/nurse
									$shortcut_switch = false;
									if(in_array($v_cf['create_user'], $doctor_users))
									{
										$shortcut_switch = 'doc';
									}
									else if(in_array($v_cf['create_user'], $nurse_users))
									{
										$shortcut_switch = 'nur';
									}
								
									//create products (doc||nurse)
									if(strlen($vday_matched_loc_price_type) > 0 && $shortcut_switch && in_array($v_cf['form_type'], $set_one_ids))
									{
										if($ppl[$v_sapv_day][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type] != '0.00')
										{
											//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
											$products['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type] += 1;
											if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
											{
												$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
											}
										}
								
										$shortcut = '';
										$qty[$vday_matched_loc_price_type] = '';
										//DOCTOR VISITS - Ärztlicher Hausbesuch - price for the doctor visit from minute 20 - 60 (rp_doc_1||rp_nur_1)
										if($v_cf['visit_duration'] >= '0')
										{
											$shortcut = 'rp_' . $shortcut_switch . '_1';
											$qty[$vday_matched_loc_price_type] = '1';
											if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
											{
												$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
											}
										}
								
										//DOCTOR VISITS - Zusatzentgeld Arzt - for EVERY 15 minutes more from minute 61 this product is added. (doctor) (rp_doc_3||rp_nur_3)
										$multiplier = '';
										$qty[$vday_matched_loc_price_type] = '';
										if($v_cf['visit_duration'] > '45')
										{
								
											// calculate multiplier of 15 minutes after 60 min (round up)
											// 	ISPC-2006 :: From 60 was changed to 45
											// calculate multiplier of 15 minutes after 45 min (round up)
											$shortcut = 'rp_' . $shortcut_switch . '_3';
											$multiplier = ceil(($v_cf['visit_duration'] - 45) / 15);
											$qty[$vday_matched_loc_price_type] = $multiplier; //multiplier value
								
											if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
											{
												$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
											}
										}
								
										$shortcut = '';
										$qty[$vday_matched_loc_price_type] = '';
										//DOCTOR VISITS - Abschlag Arzt - reduction of the doctor visit price when visit is shorter than 20 minutes.(rp_doc_4||||rp_nur_4)
										if($v_cf['visit_duration'] < '20')
										{
											$shortcut = 'rp_' . $shortcut_switch . '_4';
											$qty[$vday_matched_loc_price_type] = '1';
								
											if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$v_sapv_day][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
											{
												$products[$shortcut][$visit_date][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
											}
										}
								
										$shortcut = '';
										$qty[$vday_matched_loc_price_type] = '';
									}
								}
								
								//Fallabschluss - patient death coordination. added once (rp_pat_dead)
								if(strlen($discharge_dead_date) > 0)
								{
									//visit date formated
									$visit_date = date('Y-m-d', strtotime($discharge_dead_date));
								
									//switch shortcut_type based on patient location for *visit* date
									$dead_matched_loc_price_type = $location_type_match[$pat_days2loctype[$discharge_dead_date]];
									$qty[$vday_matched_loc_price_type] = '1';
									if($dead_matched_loc_price_type && $ppl[$v_sapv_day][0]['rp_pat_dead'][$dead_matched_loc_price_type] != '0.00')
									{
										$products['rp_pat_dead'][$v_sapv_day][$dead_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
									}
								}
								//GATHER CONTROL SHEET DATA END
								
						}
								
					}
					else // for INVOICE
					{
					
					if($curent_sapv_type == "1") // only BE
					{
						$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $curent_period);
			
						if($bill_assessment == "1"){
							$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $current_period);
						
							if($curent_sapv_type == "1"){
								$rp_asses = $rp_asses[0]; // bill only first assessment
							}
							foreach($rp_asses as $k_assessment => $v_assessment)
							{
								$location_matched_price = $location_type_match[$pat_days2loctype[$v_assessment['completed_date']]];
						
								if(strlen($location_matched_price) > 0)
								{
									$products['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price] += '1';
								}
							}
						}
						
						//Ebene 1 (reduziertes Assessment) - Not used yet
						if($bill_secondary_assessment == "1"){
							$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $current_period);
								
							foreach($rp_asses as $k_assessment => $v_assessment)
							{
								$location_matched_price = $location_type_match[$pat_days2loctype[$v_assessment['completed_date']]];
						
								if(strlen($location_matched_price) > 0)
								{
									$products['rp_eb_2'][$v_assessment['completed_date']][$location_matched_price] += '1';
								}
							}
						}
						
						
						if(!empty($rp_asses)){
							$rp_assessment_final[0] = $rp_asses[0];
			
							#
							//--if the next admission is within 28 days after the Beratung admission, NO assessment is again billed
							//--if the next admission is AFTER 28 days after the Beratung admission, a "reduziertes Assessment" is only billed
								
								
							foreach($rp_assessment_final as $k_assessment => $v_assessment)
							{
								$location_matched_price = $location_type_match[$pat_days2loctype[$v_assessment['completed_date']]];
									
								if(strlen($location_matched_price) > 0)
								{
									//found saved data for day of assessment completion
									if(strlen($saved_data['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price]))
									{
										$products['rp_eb_1']['qty_gr'][$location_matched_price] += $saved_data['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price];
										$products['rp_eb_1']['price'] = $ppl[$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price];
										$products['rp_eb_1']['total'][$location_matched_price] = ($products['rp_eb_1']['total'][$location_matched_price] + ($saved_data_arr['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price] * $ppl[$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price]));
										$products['rp_eb_1']['source']['saved_data'][$sapvday_loc_matched_price][$v_assessment['completed_date']] += $saved_data['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price];
										$excluded_saved_data_days['rp_eb_1'][] = $v_assessment['completed_date'];
									}
									//no saved data, load system data instead
									else
									{
										$products['rp_eb_1']['qty_gr'][$location_matched_price] += '1';
										$products['rp_eb_1']['price'] = $ppl[$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price];
										$products['rp_eb_1']['total'][$location_matched_price] = ($products['rp_eb_1']['total'][$location_matched_price] + $ppl[$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price]);
										$products['rp_eb_1']['source']['system_data'][$location_matched_price][$v_assessment['completed_date']] += '1';
									}
								}
							}
						}
			
			
						$visit_cnt['rp_doc_1'] = 0;
						$visit_cnt['rp_doc_2'] = 0;
						$visit_cnt['rp_doc_3'] = 0;
						$visit_cnt['rp_doc_4'] = 0;
			
						$visit_cnt['rp_nur_1'] = 0;
						$visit_cnt['rp_nur_2'] = 0;
						$visit_cnt['rp_nur_3'] = 0;
						$visit_cnt['rp_nur_4'] = 0;
			
						//DOCTOR and NURSE VISITS - all
						foreach($all_contact_forms as $k_cf => $v_cf)
						{
							//visit date formated
							$visit_date = date('Y-m-d', strtotime($v_cf['date']));
			
							//switch shortcut_type based on patient location for *visit* date
							$vday_matched_loc_price_type = $location_type_match[$pat_days2loctype[$visit_date]];
			
							//switch shortcut doctor/nurse
							$shortcut_switch = false;
							if(in_array($v_cf['create_user'], $doctor_users))
							{
								$shortcut_switch = 'doc';
							}
							else if(in_array($v_cf['create_user'], $nurse_users))
							{
								$shortcut_switch = 'nur';
							}
			
							//create products (doc||nurse)
							if(strlen($vday_matched_loc_price_type) > 0 && $shortcut_switch && in_array($v_cf['form_type'], $set_one_ids)
							)
							{
								//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
								if($ppl[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type] != '0.00')
								{
									//overide with saved data
									if(strlen($saved_data['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type]) > '0')
									{
										$products['rp_' . $shortcut_switch . '_2']['qty_gr'][$vday_matched_loc_price_type] += $saved_data['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type];
										$products['rp_' . $shortcut_switch . '_2']['price'] = $ppl[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type];
										$products['rp_' . $shortcut_switch . '_2']['extra_data'] = $v_cf;
										$extra_data['home_visit'][$v_cf['id']] = $v_cf;
										$products['rp_' . $shortcut_switch . '_2']['total'][$vday_matched_loc_price_type] = ($products['rp_' . $shortcut_switch . '_2']['total'][$vday_matched_loc_price_type] + ($saved_data['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type] * $ppl[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type]));
										$excluded_saved_data_days['rp_' . $shortcut_switch . '_2'][] = $visit_date;
									}
									else
									{
			
										if($visit_cnt['rp_' . $shortcut_switch . '_2'] == 0 ){
											$products['rp_' . $shortcut_switch . '_2']['qty_gr'][$vday_matched_loc_price_type] += 1;
											$extra_data['home_visit'][$v_cf['id']] = $v_cf;
											$products['rp_' . $shortcut_switch . '_2']['price'] = $ppl[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type];
											$products['rp_' . $shortcut_switch . '_2']['total'][$vday_matched_loc_price_type] = ($products['rp_' . $shortcut_switch . '_2']['total'][$vday_matched_loc_price_type] + $ppl[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type]);
											$visit_cnt['rp_' . $shortcut_switch . '_2']++;
										}
									}
			
									$shortcut = '';
									$qty[$vday_matched_loc_price_type] = '';
								}
			
								//DOCTOR VISITS - Ärztlicher Hausbesuch - price for the doctor visit from minute 20 - 60 (rp_doc_1||rp_nur_1)
								if($v_cf['visit_duration'] >= '0')
								{
									$shortcut = 'rp_' . $shortcut_switch . '_1';
									$qty[$vday_matched_loc_price_type] = '1';
			
									if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
									{
										//overide with saved data
										if(strlen($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type]) > '0')
										{
											$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type];
											$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
											$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type] * $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type]));
											$excluded_saved_data_days[$shortcut][] = $visit_date;
										}
										else
										{
											if($visit_cnt[$shortcut] == 0 ){
												$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
												$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
												$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] * $qty[$vday_matched_loc_price_type]));
												$visit_cnt[$shortcut]++;
											}
										}
									}
									$shortcut = '';
								}
			
								//DOCTOR VISITS - Zusatzentgeld Arzt - for EVERY 15 minutes more from minute 61 this product is added. (doctor) (rp_doc_3||rp_nur_3)
								if($v_cf['visit_duration'] > '45')
								{
									// calculate multiplier of 15 minutes after 60 min (round up)
									// ISPC-2006 :: From 60 was changed to 45
									// calculate multiplier of 15 minutes after 45 min (round up)
									$shortcut = 'rp_' . $shortcut_switch . '_3';
									$multiplier = ceil(($v_cf['visit_duration'] - 45) / 15);
									$qty[$vday_matched_loc_price_type] = $multiplier; //multiplier value
			
									if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
									{
			
										//overide with saved data
										if(strlen($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type]) > '0')
										{
											$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type];
											$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
											$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type] * $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type]));
											$excluded_saved_data_days[$shortcut][] = $visit_date;
										}
										else
										{
											if($visit_cnt[$shortcut] == 0 ){
												$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
												$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
												$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] * $qty[$vday_matched_loc_price_type]));
												$visit_cnt[$shortcut]++;
											}
										}
									}
									$shortcut = '';
								}
			
								//DOCTOR VISITS - Abschlag Arzt - reduction of the doctor visit price when visit is shorter than 20 minutes.(rp_doc_4||||rp_nur_4)
								if($v_cf['visit_duration'] < '20')
								{
									$shortcut = 'rp_' . $shortcut_switch . '_4';
									$qty[$vday_matched_loc_price_type] = '1';
			
									if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
									{
										//overide with saved data
										if(strlen($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type]) > '0')
										{
											$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type];
											$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
											$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type] * $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type]));
											$excluded_saved_data_days[$shortcut][] = $visit_date;
										}
										else
										{
											if($visit_cnt[$shortcut] == 0 ){
												$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
												$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
												$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] * $qty[$vday_matched_loc_price_type]));
												$visit_cnt[$shortcut]++;
											}
										}
									}
									$shortcut = '';
								}
							}
						}
			
					}
					else
					{
			
			
						//GATHER INVOICE ITEMS START
						//Ebene 1 (Assessment / Beratung) - RP-Assessment marked as completed
						$rp_asses = Rpassessment::get_patient_completed_rpassessment($ipid, $curent_period);
			
						foreach($rp_asses as $k_assessment => $v_assessment)
						{
							$location_matched_price = $location_type_match[$pat_days2loctype[$v_assessment['completed_date']]];
			
							if(strlen($location_matched_price) > 0)
							{
								//found saved data for day of assessment completion
								if(strlen($saved_data['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price]))
								{
									$products['rp_eb_1']['qty_gr'][$location_matched_price] += $saved_data['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price];
									$products['rp_eb_1']['price'] = $ppl[$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price];
									$products['rp_eb_1']['total'][$location_matched_price] = ($products['rp_eb_1']['total'][$location_matched_price] + ($saved_data_arr['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price] * $ppl[$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price]));
									$products['rp_eb_1']['source']['saved_data'][$sapvday_loc_matched_price][$v_assessment['completed_date']] += $saved_data['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price];
									$excluded_saved_data_days['rp_eb_1'][] = $v_assessment['completed_date'];
								}
								//no saved data, load system data instead
								else
								{
									$products['rp_eb_1']['qty_gr'][$location_matched_price] += '1';
									$products['rp_eb_1']['price'] = $ppl[$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price];
									$products['rp_eb_1']['total'][$location_matched_price] = ($products['rp_eb_1']['total'][$location_matched_price] + $ppl[$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price]);
									$products['rp_eb_1']['source']['system_data'][$location_matched_price][$v_assessment['completed_date']] += '1';
								}
							}
						}
						//Ebene 1 (reduziertes Assessment) - Not used yet (saved data for this shortcut as is not calculated by system)
						//Ebene 2 - the daily added price when patient is active and has Verordnung
						$sapv_days = $patientmaster->getDaysInBetween($curent_sapv_from, $curent_sapv_till);
						$sapv_days = array_values(array_unique($sapv_days));
			
						foreach($sapv_days as $k_sapv_day => $v_sapv_day)
						{
							$sapvday_loc_matched_price = $location_type_match[$pat_days2loctype[$v_sapv_day]];
			
							if(strlen($sapvday_loc_matched_price) > 0 && $ppl[$v_sapv_day][0]['rp_eb_3'][$sapvday_loc_matched_price] != '0.00')
							{
								if(strlen($saved_data['rp_eb_3'][$v_sapv_day][$sapvday_loc_matched_price]) > '0')
								{
									$products['rp_eb_3']['qty_gr'][$sapvday_loc_matched_price] += $saved_data['rp_eb_3'][$v_sapv_day][$sapvday_loc_matched_price];
									$products['rp_eb_3']['price'] = $ppl[$v_sapv_day][0]['rp_eb_3'][$sapvday_loc_matched_price];
									$products['rp_eb_3']['source']['saved_data'][$sapvday_loc_matched_price][$v_sapv_day] += $saved_data['rp_eb_3'][$v_sapv_day][$sapvday_loc_matched_price];
									$excluded_saved_data_days['rp_eb_3'][] = $v_sapv_day;
								}
								else
								{
									$products['rp_eb_3']['qty_gr'][$sapvday_loc_matched_price] += 1;
									$products['rp_eb_3']['price'] = $ppl[$v_sapv_day][0]['rp_eb_3'][$sapvday_loc_matched_price];
									$products['rp_eb_3']['source']['system_data'][$sapvday_loc_matched_price][$v_sapv_day] += '1';
								}
			
								$products['rp_eb_3']['total'][$sapvday_loc_matched_price] = ($products['rp_eb_3']['qty_gr'][$sapvday_loc_matched_price] * $ppl[$v_sapv_day][0]['rp_eb_3'][$sapvday_loc_matched_price]);
							}
						}
			
						//DOCTOR and NURSE VISITS - all
						foreach($all_contact_forms as $k_cf => $v_cf)
						{
							//visit date formated
							$visit_date = date('Y-m-d', strtotime($v_cf['date']));
			
							//switch shortcut_type based on patient location for *visit* date
							$vday_matched_loc_price_type = $location_type_match[$pat_days2loctype[$visit_date]];
			
							//switch shortcut doctor/nurse
							$shortcut_switch = false;
							if(in_array($v_cf['create_user'], $doctor_users))
							{
								$shortcut_switch = 'doc';
							}
							else if(in_array($v_cf['create_user'], $nurse_users))
							{
								$shortcut_switch = 'nur';
							}
			
							//create products (doc||nurse)
							if(strlen($vday_matched_loc_price_type) > 0 && $shortcut_switch && in_array($v_cf['form_type'], $set_one_ids)
							)
							{
								//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
								if($ppl[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type] != '0.00')
								{
									//overide with saved data
									if(strlen($saved_data['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type]) > '0')
									{
										$products['rp_' . $shortcut_switch . '_2']['qty_gr'][$vday_matched_loc_price_type] += $saved_data['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type];
										$products['rp_' . $shortcut_switch . '_2']['price'] = $ppl[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type];
										$products['rp_' . $shortcut_switch . '_2']['extra_data'] = $v_cf;
										$extra_data['home_visit'][$v_cf['id']] = $v_cf;
										$products['rp_' . $shortcut_switch . '_2']['total'][$vday_matched_loc_price_type] = ($products['rp_' . $shortcut_switch . '_2']['total'][$vday_matched_loc_price_type] + ($saved_data['rp_' . $shortcut_switch . '_2'][$visit_date][$vday_matched_loc_price_type] * $ppl[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type]));
										$excluded_saved_data_days['rp_' . $shortcut_switch . '_2'][] = $visit_date;
									}
									else
									{
										$products['rp_' . $shortcut_switch . '_2']['qty_gr'][$vday_matched_loc_price_type] += 1;
										$extra_data['home_visit'][$v_cf['id']] = $v_cf;
										$products['rp_' . $shortcut_switch . '_2']['price'] = $ppl[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type];
										$products['rp_' . $shortcut_switch . '_2']['total'][$vday_matched_loc_price_type] = ($products['rp_' . $shortcut_switch . '_2']['total'][$vday_matched_loc_price_type] + $ppl[$visit_date][0]['rp_' . $shortcut_switch . '_2'][$vday_matched_loc_price_type]);
									}
			
			
									$shortcut = '';
									$qty[$vday_matched_loc_price_type] = '';
								}
			
								//DOCTOR VISITS - Ärztlicher Hausbesuch - price for the doctor visit from minute 20 - 60 (rp_doc_1||rp_nur_1)
								if($v_cf['visit_duration'] >= '0')
								{
									$shortcut = 'rp_' . $shortcut_switch . '_1';
									$qty[$vday_matched_loc_price_type] = '1';
			
									if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
									{
										//overide with saved data
										if(strlen($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type]) > '0')
										{
											$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type];
											$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
											$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type] * $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type]));
											$excluded_saved_data_days[$shortcut][] = $visit_date;
										}
										else
										{
											$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
											$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
											$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] * $qty[$vday_matched_loc_price_type]));
										}
									}
								}
			
								//DOCTOR VISITS - Zusatzentgeld Arzt - for EVERY 15 minutes more from minute 61 this product is added. (doctor) (rp_doc_3||rp_nur_3)
								if($v_cf['visit_duration'] > '45')
								{
									// calculate multiplier of 15 minutes after 60 min (round up)
									// ISPC-2006 :: From 60 was changed to 45
									// calculate multiplier of 15 minutes after 45 min (round up)
									$shortcut = 'rp_' . $shortcut_switch . '_3';
									$multiplier = ceil(($v_cf['visit_duration'] - 45) / 15);
									$qty[$vday_matched_loc_price_type] = $multiplier; //multiplier value
			
									if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
									{
			
										//overide with saved data
										if(strlen($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type]) > '0')
										{
											$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type];
											$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
											$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type] * $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type]));
											$excluded_saved_data_days[$shortcut][] = $visit_date;
										}
										else
										{
											$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
											$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
											$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] * $qty[$vday_matched_loc_price_type]));
										}
									}
								}
			
								//DOCTOR VISITS - Abschlag Arzt - reduction of the doctor visit price when visit is shorter than 20 minutes.(rp_doc_4||||rp_nur_4)
								if($v_cf['visit_duration'] < '20')
								{
									$shortcut = 'rp_' . $shortcut_switch . '_4';
									$qty[$vday_matched_loc_price_type] = '1';
			
									if($shortcut && $qty[$vday_matched_loc_price_type] && $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] != '0.00')
									{
										//overide with saved data
										if(strlen($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type]) > '0')
										{
											$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type];
											$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
											$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type] * $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type]));
											$excluded_saved_data_days[$shortcut][] = $visit_date;
										}
										else
										{
											$products[$shortcut]['qty_gr'][$vday_matched_loc_price_type] += $qty[$vday_matched_loc_price_type];
											$products[$shortcut]['price'] = $ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type];
											$products[$shortcut]['total'][$vday_matched_loc_price_type] = ($products[$shortcut]['total'][$vday_matched_loc_price_type] + ($ppl[$visit_date][0][$shortcut][$vday_matched_loc_price_type] * $qty[$vday_matched_loc_price_type]));
										}
									}
								}
							}
						}
			
						//Fallabschluss - patient death coordination. added once (rp_pat_dead)
						if(strlen($discharge_dead_date) > 0)
						{
							//visit date formated
							$visit_date = date('Y-m-d', strtotime($discharge_dead_date));
			
							//switch shortcut_type based on patient location for *visit* date
							$dead_matched_loc_price_type = $location_type_match[$pat_days2loctype[$discharge_dead_date]];
							$qty[$dead_matched_loc_price_type] = '1';
			
							if($dead_matched_loc_price_type && $ppl[$visit_date][0]['rp_pat_dead'][$dead_matched_loc_price_type] != '0.00')
							{
								//overide with saved data
								if(strlen($saved_data['rp_pat_dead'][$visit_date][$dead_matched_loc_price_type]) > '0')
								{
									$products['rp_pat_dead']['qty_gr'][$dead_matched_loc_price_type] += $saved_data['rp_pat_dead'][$visit_date][$dead_matched_loc_price_type];
									$products['rp_pat_dead']['price'] = $ppl[$visit_date][0]['rp_pat_dead'][$dead_matched_loc_price_type];
									$products['rp_pat_dead']['total'][$dead_matched_loc_price_type] = ($products['rp_pat_dead']['total'][$dead_matched_loc_price_type] + ($saved_data['rp_pat_dead'][$visit_date][$dead_matched_loc_price_type] * $ppl[$visit_date][0]['rp_pat_dead'][$dead_matched_loc_price_type]));
									$excluded_saved_data_days['rp_pat_dead'][] = $visit_date;
								}
								else
								{
									$products['rp_pat_dead']['qty_gr'][$dead_matched_loc_price_type] += $qty[$dead_matched_loc_price_type];
									$products['rp_pat_dead']['price'] = $ppl[$discharge_dead_date][0]['rp_pat_dead'][$dead_matched_loc_price_type];
									$products['rp_pat_dead']['total'][$dead_matched_loc_price_type] = ($products['rp_pat_dead']['total'][$dead_matched_loc_price_type] + ($ppl[$visit_date][0]['rp_pat_dead'][$dead_matched_loc_price_type] * $qty[$dead_matched_loc_price_type]));
								}
							}
							//GATHER INVOICE ITEMS END
						}
					}
					
					
					
					}
				}
				else
				{
					//reset date values
 
				}
			}
			
			//append the rest of saved data for existing invoiced sapv days
			//removed rp_eb_3 from shortcuts arr because is allready calculated
			$shortcuts_array = array('rp_eb_1', 'rp_eb_2', 'rp_doc_1', 'rp_doc_2', 'rp_doc_3', 'rp_doc_4', 'rp_nur_1', 'rp_nur_2', 'rp_nur_3', 'rp_nur_4', 'rp_pat_dead');
			foreach($shortcuts_array as $k_short => $v_short)
			{
				foreach($sapv_days as $k_sapv_day => $vsapv_day)
				{
					$sapv_day_loc_matched_price = $location_type_match[$pat_days2loctype[$vsapv_day]];
			
					if(!in_array($vsapv_day, $excluded_saved_data_days[$v_short]) && !in_array($vsapv_day, $second_exclude[$v_short][$sapv_day_loc_matched_price]) && $ppl[$vsapv_day][0][$v_short][$sapv_day_loc_matched_price] != '0.00' && strlen($saved_data[$v_short][$vsapv_day][$sapv_day_loc_matched_price]) > 0
					)
					{
						$products[$v_short]['qty_gr'][$sapv_day_loc_matched_price] += $saved_data[$v_short][$vsapv_day][$sapv_day_loc_matched_price];
						$products[$v_short]['price'] = $ppl[$vsapv_day][0][$v_short][$sapv_day_loc_matched_price];
						$products[$v_short]['total'][$sapv_day_loc_matched_price] = ($products[$v_short]['qty_gr'][$sapv_day_loc_matched_price] * $ppl[$vsapv_day][0][$v_short][$sapv_day_loc_matched_price]);
						$second_exclude[$v_short][$sapv_day_loc_matched_price][] = $vsapv_day;
					}
				}
			}
			
			
 			return $products;
			
// 			$client_form_type =  FormTypeActions::get_form_type_actions();
		
// 			foreach($all_forms as $k=>$ft){
// 				$form2action[$ft['id']] = $client_form_type[$ft['action']]['name'];
// 			}
			
			
		}
		
		

	}

?>