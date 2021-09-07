<?php

Doctrine_Manager::getInstance()->bindComponent('SgbvForms', 'MDAT');

class SgbvForms extends BaseSgbvForms {

	function getPatientSgbvForm($ipid, $sgbv_form)
	{
		$select = Doctrine_Query::create()
		->select('*')
		->from('SgbvForms')
		->where('id="' . $sgbv_form . '"')
		->andWhere('ipid = "' . $ipid . '"')
		->andWhere('isdelete="0"');
		$select_res = $select->fetchArray();

		if($select_res)
		{
			return $select_res[0];
		}
		else
		{
			return false;
		}
	}

	function getallPatientSgbvForm($ipid, $form_date = false, $status_only = false, $negation_status = false, $free_of_charge = false)
	{
		//ISPC-2746 Carmen 03.12.2020
		if(!is_array($ipid))
		{
			$ipid = array($ipid);
		}
		//--
		
		$select = Doctrine_Query::create()
		->select('*')
		->from('SgbvForms')
		//ISPC-2746 Carmen 03.12.2020
		//->where('ipid = "' . $ipid . '"')
		->whereIn('ipid', $ipid)
		//--
		->andWhere('isdelete="0"');
		if($form_date)
		{
			if(is_array($form_date))
			{
				$form_date['start'] = date('Y-m-d', strtotime($form_date['start']));
				$form_date['end'] = date('Y-m-d', strtotime($form_date['end']));

				$select->andWhere('DATE("' . $form_date['start'] . '") <= DATE(`valid_till`)');
				$select->andWhere('DATE("' . $form_date['end'] . '") >= DATE(`valid_from`)');
			}
			else
			{
				$form_date = date('Y-m-d', strtotime($form_date));
				$select->andWhere('DATE("' . $form_date . '") BETWEEN DATE(valid_from) AND DATE(valid_till)');
			}
		}

		if($status_only)
		{
			if(is_array($status_only))
			{
				$select->andWhereIn('status', $status_only);
			}
			else
			{
				$select->andWhere('status="' . $status_only . '"');
			}
		}
		if($negation_status)
		{
			if(is_array($negation_status))
			{
				$select->andWhereNotIn('status', $negation_status);
			}
			else
			{
				$select->andWhere('status != "' . $negation_status . '" ');
			}
		}

		if($free_of_charge)
		{
			$select->andWhere('free_of_charge = "1"');
		}

		$select->orderBy('ipid ASC, valid_from ASC'); //ISPC-2746 Carmen 03.12.2020

		$select_res = $select->fetchArray();

		if($select_res)
		{
			return $select_res;
		}
		else
		{
			return false;
		}
	}

	//sgbv validation functions
	function validate_sgbv($ipid, $form_date, $actions, $patient_details)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
		$groupid = $logininfo->groupid;
		$usertype = $logininfo->usertype;

		$sgbvi = new SgbvFormsItems();
		$sgbv_block = new FormBlockSgbv();
		$pm = new PatientMaster();

		$modules = new Modules();
		$send_todos = "1";
		if($modules->checkModulePrivileges("151", $clientid))//TODO-989 dectivate todos set to coordinators
		{
			$send_todos = "0";
		} 
		
		
		
		//get free of charge (foc) sgbvs in form period
		$foc_sgbv = $this->getallPatientSgbvForm($ipid, $form_date, false, false, true);

		$foc_sgbv_ids[] = '9999999999';
		foreach($foc_sgbv as $k_foc_sgbv => $v_foc_sgbv)
		{
			$foc_sgbv_ids[] = $v_foc_sgbv['id'];
		}

		$foc_sgbv_actions = $sgbvi->getPatientSgbvFormItems($ipid, $foc_sgbv_ids);
		foreach($foc_sgbv_actions as $k_foc_action => $v_foc_action)
		{
			$foc_actions[] = $v_foc_action['action_id'];
		}

		//reverse actions arr
		foreach($actions as $k_post_act => $v_post_act)
		{
			if(!in_array($k_post_act, $foc_actions))
			{
				$actions_arr[] = $k_post_act;
			}
		}

		if(count($actions_arr) == 0)
		{
			$actions_arr[] = '99999999';
		}

		//get curent day active verordnungs
		$current_sgbvs = $this->getallPatientSgbvForm($ipid, $form_date);

		//get verordnungs datas (ids, period days)
		foreach($current_sgbvs as $kc_sgbv => $vc_sgbv)
		{
			//construct each sgbv start - end period
			$sgbv_approved_date = date('Y-m-d', strtotime($vc_sgbv['approved_limit']));
			$contact_form_date = date('Y-m-d', strtotime($from_date));

			//check if form date is inside start - approved_limit period
			if($vc_sgbv['approved_limit'] != '0000-00-00 00:00:00' && $vc_sgbv['approved_limit'] != '1970-01-01 00:00:00' && strtotime($sgbv_approved_date) >= strtotime($contact_form_date))
			{
				$sgbvs_ids[] = $vc_sgbv['id'];
				$sgbv_till = $sgbv_approved_date;
			}
			else if($vc_sgbv['approved_limit'] == '0000-00-00 00:00:00' || $vc_sgbv['approved_limit'] == '1970-01-01 00:00:00')
			{
				$sgbvs_ids[] = $vc_sgbv['id'];
				$sgbv_till = date('Y-m-d', strtotime($vc_sgbv['valid_till']));
			}

			$start = date('Y-m-d', strtotime($vc_sgbv['valid_from']));
			$end = $sgbv_till;

			//period days array
			$sgbv_period_days[$vc_sgbv['id']] = array();
			$sgbv_period_days[$vc_sgbv['id']] = $pm->getDaysInBetween($start, $end);

			//period start - end array
			$sgbv_periods[$vc_sgbv['id']]['from'] = $start;
			$sgbv_periods[$vc_sgbv['id']]['till'] = $end;
		}

		if(count($current_sgbvs) == 0)
		{
			$sgbvs_ids[] = '9999999';
		}

		//get sgbv etalon actions
		$actions_sgbv = $sgbvi->getPatientSgbvFormItems($ipid, $sgbvs_ids);

		$sgbv_foc_actions = array();
		$all_foc_actions = array();
		//			print_r("sgbv_periods_Days\n");
		//			print_r($sgbv_period_days);
		//			print_r("sgbv_actions\n");
		//			print_r($actions_sgbv);
		foreach($actions_sgbv as $k_e_action => $v_e_action)
		{
			//SUM Overlapping sgbv amounts
			$sgbv_actions_reper[$v_e_action['action_id']]['per_day'] += $v_e_action['per_day'];
			$sgbv_actions_reper[$v_e_action['action_id']]['per_week'] = $v_e_action['per_week'];

			if(empty($sgbv_action_period_days[$v_e_action['action_id']]))
			{
				$sgbv_action_period_days[$v_e_action['action_id']] = array();
			}

			$sgbv_action_period_days[$v_e_action['action_id']] = array_merge($sgbv_action_period_days[$v_e_action['action_id']], $pm->getDaysInBetween(date('Y-m-d', strtotime($v_e_action['valid_from'])), date('Y-m-d', strtotime($v_e_action['valid_till']))));


			$actions_sgbv_ids[] = $v_e_action['action_id'];
			$actions_sgbv_details[] = $v_e_action;

			if($v_e_action['free_of_charge'] == '1')
			{
				$sgbv_foc_actions[$v_e_action['sgbv_form_id']][] = $v_e_action['action_id'];
				$all_foc_actions[] = $v_e_action['action_id'];
			}
		}

		foreach($sgbv_action_period_days as $k_arr_action => $v_arr_days)
		{
			foreach($v_arr_days as $k_arr_day => $v_arr_day)
			{
				$action_day_week = date('W', strtotime($v_arr_day));
				$sgbv_period_actions_reper[$k_arr_action][$action_day_week]['per_week'] = $sgbv_actions_reper[$k_arr_action]['per_week'];
				$sgbv_period_actions_reper[$k_arr_action][$v_arr_day]['per_day'] = $sgbv_actions_reper[$k_arr_action]['per_day'];
			}
		}
		//			print_r("sgbv_action_period_days\n");
		//			print_r($sgbv_action_period_days);
		//			print_r("sgbv_week_actions_reper\n");
		//			print_r($sgbv_week_actions_reper);

		krsort($actions_sgbv_ids);
		ksort($actions_sgbv_details);
		krsort($no_sgbv_slots);
		$sgbv_foc_actions = array_values(array_unique($sgbv_foc_actions));

		//			print_r("Actions arr\n\n");
		//			print_r($actions_arr);
		//			print_r("Actions SGBV\n\n");
		//			print_r($actions_sgbv);
		//			print_r("SGBV Actions Reper\n\n");
		//			print_r($sgbv_actions_reper);
		//			print_r("FOC Submitted Actions \n\n");
		//			print_r($sgbv_foc_actions);
		//			exit;
		$no_sgbv_slots = array_diff($actions_arr, $actions_sgbv_ids);
		$no_sgbv_slots = array_unique($no_sgbv_slots);

		//1.  get all contact forms filled in all active sgbv periods
		if(count($sgbv_periods) > 0)
		{
			$where = '';
			foreach($sgbv_periods as $k_sgbv => $v_sgbv_data)
			{
				$where .= 'DATE(`date`) BETWEEN DATE("' . $v_sgbv_data['from'] . '") AND DATE("' . $v_sgbv_data['till'] . '") OR ';
			}

			$select_q = Doctrine_Query::create()
			->select('*')
			->from('ContactForms')
			->where('ipid = "' . $ipid . '"')
			->andwhere('isdelete = "0"')
			->andWhere(substr($where, 0, -3));
			$contact_forms = $select_q->fetchArray();

			foreach($contact_forms as $k_c_form => $v_c_form)
			{
				$c_form_ids[] = $v_c_form['id'];
				$c_form_dates[$v_c_form['id']] = date('Y-m-d', strtotime($v_c_form['billable_date']));
			}


			$saved_actions = $sgbv_block->getFormsSavedActions($ipid, $c_form_ids);

			foreach($saved_actions as $k_saved_actions => $v_saved_actions)
			{
				$patient_saved_actions_verification[$c_form_dates[$v_saved_actions['contact_form_id']]][] = $v_saved_actions['action_id'];
				$patient_saved_actions[$c_form_dates[$v_saved_actions['contact_form_id']]][$v_saved_actions['action_id']] += '1';

				$week_number = date('W', strtotime($c_form_dates[$v_saved_actions['contact_form_id']]));
				$patient_saved_actions_week[$week_number][$v_saved_actions['action_id']] += $v_saved_actions['action_value'];
			}

			ksort($patient_saved_actions);


			//Tier 1 Existing action in sgbv array for current form day => if not.. need to be added to verordnung creation
			foreach($actions_arr as $k_submitted_action => $v_submitted_action)
			{
				if(!array_key_exists($v_submitted_action, $sgbv_actions_reper))
				{
					$new_verordnung_minimal_items[] = $v_submitted_action;
				}
			}

			//Tier 2 Day and Week Verifications
			//determine if submited action is covered by sgbv
			foreach($actions_sgbv_details as $k_date_saved => $v_saved_actions)
			{
				$week_number = date('W', strtotime($k_date_saved));

				foreach($v_saved_actions as $k_action_id => $k_action_qty)
				{
					$week_qty[$week_number][$k_action_id] +=$k_action_qty;
				}
			}
			//				print_r("new_verordnung_minimal_items 1\n");
			//				print_r($new_verordnung_minimal_items);
			//				print_r("submitted actions\n");
			//				print_r($actions);
			//				print_r("patient_saved_actions\n");
			//				print_r($patient_saved_actions);
			//				print_r("sgbv_actions_reper\n");
			//				print_r($sgbv_actions_reper);
			//
			//				print_r("patient_period_actions_reper\n");
			//				print_r($sgbv_period_actions_reper);
			$remaining_slots = array();
			foreach($patient_saved_actions as $k_saved_date => $v_saved_actions_arr)
			{
				$current_week_nr = date('W', strtotime($k_saved_date));
				$contact_form_date_week = date('W', strtotime($form_date));

				foreach($sgbv_period_actions_reper as $k_action_id => $v_action_count)
				{
					if(array_key_exists($k_action_id, $v_saved_actions_arr) && in_array($k_action_id, $actions_arr) && !in_array($k_action_id, $all_foc_actions))
					{
						if($patient_saved_actions_week[$contact_form_date_week][$k_action_id] >= $v_action_count[$contact_form_date_week]['per_week'])
						{
							$week_exceded_ids[$current_week_nr][] = $k_action_id;
							$new_verordnung_minimal_items[] = $k_action_id;
						}
					}
				}

				foreach($sgbv_period_actions_reper as $k_d_action_id => $v_d_action_count)
				{
					if(array_key_exists($k_d_action_id, $v_saved_actions_arr) && in_array($k_d_action_id, $actions_arr) && !in_array($k_d_action_id, $all_foc_actions))
					{
						$formated_form_date = date('Y-m-d', strtotime($form_date));
						print_r("Test \n" . $formated_form_date . " - " . $v_d_action_count[$formated_form_date]['per_day'] . "\n");
						print_r($v_saved_actions_arr[$k_d_action_id] . " >= " . $v_d_action_count[$formated_form_date]['per_day'] . " && ");
						var_dump(strtotime($k_saved_date) == strtotime($formated_form_date));
						print_r("\n");


						if($v_saved_actions_arr[$k_d_action_id] >= $v_d_action_count[$formated_form_date]['per_day'] && strtotime($k_saved_date) == strtotime($formated_form_date))
						{
							$day_exceded_ids[$formated_form_date][] = $k_d_action_id;
							$new_verordnung_minimal_items[] = $k_d_action_id;
						}
					}
				}
			}
			//				print_r("new_verordnung_minimal_items 2\n");
			//				print_r($new_verordnung_minimal_items);
			//				print_r("week_exceded_ids\n");
			//				print_r($week_exceded_ids);
			//				print_r("day_exceded_ids\n");
			//				print_r($day_exceded_ids);
			//
			//				exit;
			$new_verordnung_minimal_items = array_unique($new_verordnung_minimal_items);

			//				print_r('$week_exceded_ids'."\n");
			//				print_r($week_exceded_ids);
			//				print_r('$day_exceeded_ids'."\n");
			//				print_r($day_exceeded_ids);
			//				print_r('$new_verordnung_minimal_items'."\n");
			//				print_r($new_verordnung_minimal_items);
			//				print_r('Reper Actions $sgbv_actions_reper' . "\n");
			//				print_r($sgbv_actions_reper);
			//				print_r('Saved Actions ' . "\n");
			//				print_r($patient_saved_actions_verification);
			//				print_r('Saved Actions $patient_saved_actions' . "\n");
			//				print_r($patient_saved_actions);
			//				print_r('Saved Actions $patient_saved_actions_week' . "\n");
			//				print_r($patient_saved_actions_week);
			//				print_r('Submited Actions $actions_arr' . "\n");
			//				print_r($actions_arr);
			//				print_r('No slots, needs sgbv' . "\n");
			//				print_r($no_slots_actions);
			//				exit;
		}
		else //create new verordnung items with all submitted actions
		{

			//reverse actions arr
			foreach($actions as $k_post_act => $v_post_act)
			{
				$actions_arr[] = $k_post_act;
			}
			if(count($actions_arr) == 0)
			{
				$actions_arr[] = '99999999';
			}
			array_unique($actions_arr);
			//				prepare $new_verordnung_minimal_items array
			$new_verordnung_minimal_items = array_unique($actions_arr);
		}

		$new_verordnung_minimal_items = array_unique($new_verordnung_minimal_items);


		if(count($new_verordnung_minimal_items) == 1 && in_array('99999999', $new_verordnung_minimal_items))
		{
			$new_verordnung_minimal_items = array();
		}

		if(count($new_verordnung_minimal_items) > 0)
		{
			$save_sgbv_form = new Application_Form_SgbvForms();
			$save_items_form = new Application_Form_SgbvFormsItems();
			$save_history_form = new Application_Form_SgbvFormsHistory();

			//get coord users
			$user_group = new Usergroup();
			$master_groups = array("6"); //Koordination master group
			$users_groups = $user_group->getUserGroups($master_groups);


			//determine form type
			$all_sgbv = $this->getallPatientSgbvForm($ipid);

			if($all_sgbv === false)
			{
				$form_type = 'first';
			}
			else
			{
				$form_type = 'follow';
			}

			//check to see if we have unaproved verordnung - sgbv with  new status -  keine Angabe == changed to status 10 which is the new status for generated sgbv's
			$created_sgbv = $this->getallPatientSgbvForm($ipid, false, '10', '6');

			if(!$created_sgbv) //create new sgbv
			{
				$new_verordnung_minimal['ipid'] = $ipid;
				$new_verordnung_minimal['valid_from'] = date('Y-m-d H:i:s', strtotime($form_date));
				$new_verordnung_minimal['valid_till'] = date('Y-m-d H:i:s', strtotime($form_date));
				$new_verordnung_minimal['form_type'] = $form_type;
				$new_verordnung_minimal['status'] = '10'; // New status -  keine Angabe
				$new_verordnung_minimal['items'] = $new_verordnung_minimal_items;


				//save sgbv form
				$save_sgbv = $save_sgbv_form->insert_minimal_sgbv($new_verordnung_minimal);
				$save_sgbv_id = $save_sgbv;

				if($send_todos == "1")
				{
					//insert todo with sgb v id
					$text = '<a href="' . APP_BASE . 'patient/sgbvverordnung?sgbv_form_id=' . $save_sgbv_id . '&id=' . Pms_Uuid::encrypt($patient_details['id']) . '">';
					$text .= strtoupper($patient_details['epid']) . ' - ' . $patient_details['last_name'] . ', ' . $patient_details['first_name'] . ' - Neue Leistungen ohne SGB V Verordnung';
					$text .= '</a>';
					if(count($users_groups) > 0)
					{
						$grous_additional_info = array();
						foreach($users_groups as $group)
						{
							$grous_additional_info[] = "g".$group['id'];
						}
						
						foreach($users_groups as $group)
						{
							$records_todo[] = array(
									"client_id" => $clientid,
									"user_id" => $userid,
									"group_id" => $group['id'],
									"ipid" => $ipid,
									"todo" => $text,
									"triggered_by" => 'system',
									"create_date" => date('Y-m-d H:i:s', time()),
									"until_date" => date('Y-m-d', strtotime($form_date)) . " 00:00:00",
									"additional_info" => implode(";",$grous_additional_info)
							);
						}
					}
	
					//save sgbv notification in koordinator todo
					$collection = new Doctrine_Collection('ToDos');
					$collection->fromArray($records_todo);
					$collection->save();
				}

				//save sgbv form items
				if($save_sgbv_id > 0)
				{
					$save_items = $save_items_form->insert_items_minimal($new_verordnung_minimal, $save_sgbv_id);
					$history_data['status'] = '10'; // New status -  keine Angabe
					$save_history = $save_history_form->InsertHistorySgbvData($history_data, $save_sgbv_id);
				}
			}
			else
			{
				$last_verordnung = end($created_sgbv);

				//edit existing verordnung
				$mod = Doctrine::getTable('SgbvForms')->find($last_verordnung['id']);
				$verordnung_data = $mod->toArray();
				if(strtotime($mod->valid_till) <= strtotime($form_date))
				{
					$mod->valid_till = date('Y-m-d H:i:s', strtotime($form_date));
					$valid_till = date('Y-m-d H:i:s', strtotime($form_date));
				}
				else
				{
					$valid_till = $mod->valid_till;
				}
				$mod->save();

				$verordnung_minimal['ipid'] = $verordnung_data['ipid'];
				$verordnung_minimal['valid_from'] = date('Y-m-d H:i:s', strtotime($verordnung_data['valid_from']));
				$verordnung_minimal['valid_till'] = date('Y-m-d H:i:s', strtotime($valid_till));

				$form_items = new SgbvFormsItems();
				$existing_form_items = $form_items->getPatientSgbvFormItems($ipid, $last_verordnung['id']);

				//					print_r($existing_form_items);
				//					print_r($sgbv_foc_actions);
				//					print_r("<br />");

				$sgbv_foc_actions = array();
				foreach($existing_form_items as $k_ext_items => $v_ext_items)
				{
					$existing_items_ids[] = $v_ext_items['action_id'];

					//add foc actions here if none where present from start
					if($v_ext_items['free_of_charge'] == '1')
					{
						$sgbv_foc_actions[$v_ext_items['sgbv_form_id']][] = $v_ext_items['action_id'];
					}
				}

				//					print_r($last_verordnung['id']);
				//					print_r("<br />");
				//					print_r($sgbv_foc_actions);
				//					print_r("<br />");
				//					exit;

				foreach($new_verordnung_minimal_items as $k_item => $v_item)
				{
					if(!in_array($v_item, $existing_items_ids))
					{
						$verordnung_minimal['items'][] = $v_item;
					}
				}

				$update_old_items = $save_items_form->update_old_items($last_verordnung['id'], $valid_till, $actions_arr, $sgbv_foc_actions[$last_verordnung['id']]);
				$save_items = $save_items_form->insert_items_minimal($verordnung_minimal, $last_verordnung['id']);
			}
		}
	}

	public function quick_change_status($sgbv_id, $new_status, $approve_date)
	{
		if($sgbv_id && $new_status)
		{
			$change_status = Doctrine_Query::create()
			->update('SgbvForms')
			->set('status', '"' . $new_status . '"');
			if($new_status == '5' && strlen($approve_date) > 0)
			{
				$change_status->set('approved_limit', '"' . date('Y-m-d H:i:s', strtotime($approve_date)) . '"');
			}
			else
			{
				$change_status->set('approved_limit', '"0000-00-00 00:00:00"');
			}
			$change_status->where('id = "' . $sgbv_id . '"');
			$quick_change = $change_status->execute();

			if($quick_change)
			{
				return true;
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

	function get_all_patients_active_sgbv($ipid)
	{
		if(!is_array($ipid))
		{
			$ipids = array($ipid);
		}
		else
		{
			$ipids = $ipid;
		}

		$statuses = array('10', '6');
		$select = Doctrine_Query::create()
		->select('ipid')
		->from('SgbvForms')
		->whereIn('ipid', $ipids)
		->andWhereNotIn('status', $statuses)
		->andWhere('isdelete="0" and "' . date('Y-m-d', time()) . '" BETWEEN DATE(`valid_from`) AND DATE(`valid_till`)');
		$select_res = $select->fetchArray();

		if($select_res)
		{
			return $select_res;
		}
		else
		{
			return false;
		}
	}



	public function delete_sgbv($sgbv_id)
	{
		if($sgbv_id)
		{
			$delete_sgbv_query = Doctrine_Query::create()
			->update('SgbvForms')
			->set('isdelete', '1')
			->set('change_date', '"'.date('Y-m-d H:i:s', time()).'"');
			$delete_sgbv_query->where('id = "' . $sgbv_id . '"');
			$delete_sgbv_result  = $delete_sgbv_query  ->execute();

			if($delete_sgbv_result)
			{
				return true;
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