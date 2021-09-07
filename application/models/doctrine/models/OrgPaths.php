<?php

	Doctrine_Manager::getInstance()->bindComponent('OrgPaths', 'SYSDAT');

	class OrgPaths extends BaseOrgPaths {

		public function get_paths($client, $function = false)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('OrgPaths')
				->where('client = "' . $client . '"')
				->andWhere('isdelete="0"');
			if($function)
			{
				$q->andWhere('function = "' . $function . '"');
			}
			$qres = $q->fetchArray();

			if($qres)
			{
				return $qres;
			}
			else
			{
				return false;
			}
		}

		public function get_clients_paths($client_array, $function = false)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('OrgPaths')
				->where('isdelete="0"')
				->andWhereIn("client", $client_array);
			if($function)
			{
				$q->andWhere('function = "' . $function . '"');
			}
			$qres = $q->fetchArray();

			if($qres)
			{
				return $qres;
			}
			else
			{
				return false;
			}
		}

		//master path todo
		public function admission_todo($ipids, $required_client = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			if($required_client)
			{
				$clientid = $required_client;
			}
			else
			{
				$clientid = $logininfo->clientid;
			}

			$steps = new OrgSteps();
			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}

			if (empty($ipids_arr)) {
				return;
			}
			
			$sql = "*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
			$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
			$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
			$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
			$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
			$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
			$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";


			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}

			$q = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->whereIn('p.ipid', $ipids_arr)
				->andWhere('p.isdelete = "0"')
				->andWhere('p.isstandbydelete = "0"')
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid);
			$q_res = $q->fetchArray();

// 			$ipids_arr_final[] = '999999999999999';
			$ipids_arr_final =  array();
			foreach($q_res as $k_p_res => $v_p_res)
			{
				$patient_details[$v_p_res['ipid']] = $v_p_res;
				$ipids_arr_final[] = $v_p_res['ipid'];
			}
			if (empty($ipids_arr_final)) {
				$ipids_arr_final[] = '999999999999999';
			}

			$patients_admision = Doctrine_Query::create()
			->select('*')
			->from('PatientReadmission')
			->whereIn('ipid', $ipids_arr_final)
			->andWhere('date_type = 1');
			$fadmarr = $patients_admision->fetchArray();
 			
 			foreach($fadmarr as $ki => $ipid_readmission){
 				if( strtotime(date("Y-m-d H:i", strtotime($patient_details[$ipid_readmission['ipid']]['admission_date'])))  == strtotime(date("Y-m-d H:i", strtotime($ipid_readmission['date']))))
 				{
 					$patient_identification[$ipid_readmission['ipid']]['identification'] = $ipid_readmission['id'];
 				}	
 			}
			
			$sapv_verordnung = new SapvVerordnung();
			$sapv_highest_active = $sapv_verordnung->get_today_active_highest_sapv($ipids_arr_final);

			$current_path = $this->get_paths($clientid, 'admission_todo');
			$current_path_shortcuts = $steps->get_paths_steps($current_path, true);

			$patient_steps = new PatientSteps();
			$saved_data = $patient_steps->get_patient_steps($ipids_arr_final);

			foreach($patient_details as $k_ipid_data => $v_patient_data)
			{
				$curent_patient_sapv = $sapv_highest_active['last'][$k_ipid_data];
				$admission_date = date('Y-m-d', strtotime($v_patient_data['admission_date']));

				//A1a, A1b, A2
				$first_admisson_date_check = date('Y-m-d', strtotime('+1 day', strtotime($admission_date)));
				//A1c
				$second_admission_date_check = date('Y-m-d', strtotime('+2 days', strtotime($admission_date)));
				//A2a
				$third_admission_date_check = date('Y-m-d', strtotime('+4 days', strtotime($admission_date)));

				$r1start = strtotime($curent_patient_sapv['verordnungam']);
				$r1end = strtotime($curent_patient_sapv['verordnungbis']);
				$r2start = $r2end = strtotime($first_admisson_date_check);
				$r2start_second = $r2end_second = strtotime($second_admission_date_check);
				$r2start_third = $r2end_third = strtotime($third_admission_date_check);

				//new
				$first_check_period = Pms_CommonData::get_days_number_between($curent_patient_sapv['verordnungam'], $first_admisson_date_check);
				if($first_check_period < 0)
				{
					$first_check_period = ($first_check_period * (-1));
				}

				//new
				$second_check_period = Pms_CommonData::get_days_number_between($curent_patient_sapv['verordnungam'], $second_admission_date_check);
				if($second_check_period < 0)
				{
					$second_check_period = ($second_check_period * (-1));
				}

				$third_check_period = Pms_CommonData::get_days_number_between($curent_patient_sapv['verordnungam'], $third_admission_date_check);
				if($third_check_period < 0)
				{
					$third_check_period = ($third_check_period * (-1));
				}

				$intersected = Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end);
				$intersected_second = Pms_CommonData::isintersected($r1start, $r1end, $r2start_third, $r2end_second);
				$intersected_third = Pms_CommonData::isintersected($r1start, $r1end, $r2start_third, $r2end_third);

				//primary sapv status
				//print_r($curent_patient_sapv);
				if(!empty($curent_patient_sapv) && $patient_details[$k_ipid_data]['isdischarged'] == '0')
				{
					$status_grey = false;
					$status_second_grey = false;
				}
				else
				{
					$status_grey = true;
					$status_second_grey = true;
				}
//				print_r("first_check_period:\n");
//	 			print_r($first_check_period);
//				print_r("first_check_period = \n");
//				print_r($first_admisson_date_check." - ".$curent_patient_sapv['verordnungam']);

				if((($intersected || $intersected_second || $intersected_third) && $first_check_period >= '0' && $curent_patient_sapv['primary_set'] <= '1' && $curent_patient_sapv['primary_set'] != '0') || $status_grey)
				{
					if(!$status_grey)
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}

					$master_data[$k_ipid_data]['admission_todo']['A1a']['status'] = $stat;
					$status_grey = true;
				}
				else if(($intersected || $intersected_second || $intersected_third) && $curent_patient_sapv['primary_set'] > '1')
				{
					$master_data[$k_ipid_data]['admission_todo']['A1a']['status'] = 'green';
				}
				$master_data[$k_ipid_data]['admission_todo']['A1a']['step_identification'] = $patient_identification[$k_ipid_data]['identification'] ;

				if((($intersected || $intersected_second || $intersected_third) && $first_check_period >= '0' && $curent_patient_sapv['primary_set'] <= '2' && $curent_patient_sapv['primary_set'] != '0') || $status_grey)
				{
					if(!$status_grey)
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}

					$master_data[$k_ipid_data]['admission_todo']['A1b']['status'] = $stat;
					$status_grey = true;
				}
				else if(($intersected || $intersected_second || $intersected_third) && $curent_patient_sapv['primary_set'] > '2')
				{
					$master_data[$k_ipid_data]['admission_todo']['A1b']['status'] = 'green';
				}
				$master_data[$k_ipid_data]['admission_todo']['A1b']['step_identification'] = $patient_identification[$k_ipid_data]['identification'] ;
				
				
				if((($intersected_second || $intersected_third) && $second_check_period >= '0' && $curent_patient_sapv['primary_set'] <= '3' && $curent_patient_sapv['primary_set'] != '0') || $status_grey)
				{
					if(!$status_grey)
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}
					$master_data[$k_ipid_data]['admission_todo']['A1c']['status'] = $stat;
					$status_grey = true;
				}
				else if(($intersected_second || $intersected_third) && $curent_patient_sapv['primary_set'] > '3')
				{
					$master_data[$k_ipid_data]['admission_todo']['A1c']['status'] = 'green';
				}
				$master_data[$k_ipid_data]['admission_todo']['A1c']['step_identification'] = $patient_identification[$k_ipid_data]['identification'] ;

				//secondary sapv status
				//A2

				if((($intersected_second || $intersected_third) && $second_check_period >= '0' && $curent_patient_sapv['secondary_set'] <= '1' && $curent_patient_sapv['secondary_set'] >= '0') || $status_second_grey)
				{
					if(!$status_second_grey)
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}
					$master_data[$k_ipid_data]['admission_todo']['A2']['status'] = $stat;
					$status_second_grey = true;
				}
				else if($curent_patient_sapv['secondary_set'] > '1')
				{
					$master_data[$k_ipid_data]['admission_todo']['A2']['status'] = 'green';
				}
				$master_data[$k_ipid_data]['admission_todo']['A2']['step_identification'] = $patient_identification[$k_ipid_data]['identification'] ;
				
				
				//A2a
				if(($intersected_third && $third_check_period >= '0' && $curent_patient_sapv['secondary_set'] <= '3' && $curent_patient_sapv['secondary_set'] != '0') || $status_second_grey)
				{
					if(!$status_second_grey)
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}
					$master_data[$k_ipid_data]['admission_todo']['A2a']['status'] = $stat;
					$status_second_grey = true;
				}
				else if($intersected_third && $curent_patient_sapv['secondary_set'] > '3')
				{
					$master_data[$k_ipid_data]['admission_todo']['A2a']['status'] = 'green';
				}
				$master_data[$k_ipid_data]['admission_todo']['A2a']['step_identification'] = $patient_identification[$k_ipid_data]['identification'] ;
				
				
				//A3
				$shortcut_id = array_search('A3', $current_path_shortcuts['shortcuts']);

				//print_r($saved_data);
				//print_r($shortcut_id);
				if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
				{
					$master_data[$k_ipid_data]['admission_todo']['A3']['status'] = "green";
					$master_data[$k_ipid_data]['admission_todo']['A3']['value'] = "1";
				}
				else if($patient_details[$k_ipid_data]['isdischarged'] == '1')
				{
					$master_data[$k_ipid_data]['admission_todo']['A3']['status'] = "gray";
				}
				else
				{
					$master_data[$k_ipid_data]['admission_todo']['A3']['status'] = "red";
				}
				$master_data[$k_ipid_data]['admission_todo']['A3']['step_identification'] = $patient_identification[$k_ipid_data]['identification'] ;
				
				
			}

			foreach($ipids_arr as $k_ipid => $v_ipid)
			{
				//recheck if all patients have status value
				if(!array_key_exists($v_ipid, $master_data))
				{
					$master_data[$v_ipid]['admission_todo']['A1a']['status'] = 'gray';
					$master_data[$v_ipid]['admission_todo']['A1b']['status'] = 'gray';
					$master_data[$v_ipid]['admission_todo']['A1c']['status'] = 'gray';
					$master_data[$v_ipid]['admission_todo']['A2']['status'] = 'gray';
					$master_data[$v_ipid]['admission_todo']['A2a']['status'] = 'gray';
					$master_data[$v_ipid]['admission_todo']['A3']['status'] = 'gray';
				}
			}

			return $master_data;
		}
		

		public function original_request_todo($ipids)
		{
			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}


			$sapv_verordnung = new SapvVerordnung();
			$sapv_highest_active = $sapv_verordnung->get_today_active_highest_sapv($ipids_arr);
 
			foreach($ipids_arr as $k_ipids => $v_ipid)
			{
				//O1, O2
				$curent_patient_sapv = $sapv_highest_active['last'][$v_ipid];
				$sapv_start_date = date('Y-m-d', strtotime($curent_patient_sapv['verordnungam']));
				$sapv_max_date = date('Y-m-d', strtotime('+10 day', strtotime($curent_patient_sapv['verordnungam'])));

				//O1
				if(!empty($curent_patient_sapv))
				{
					$status_grey = false;
				}
				else
				{
					$status_grey = true;
				}

				//print_r("sapv_max_date\n");
				//print_r($sapv_max_date);
				//print_r("sapv_start_date\n");
				//print_r($sapv_start_date);
				//print_r($curent_patient_sapv);
				if((strtotime($sapv_max_date) <= time() && $curent_patient_sapv['primary_set'] < '5') || $status_grey)
				{
					if(!$status_grey)
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}

					$master_data[$v_ipid]['original_request_todo']['O1']['status'] = $stat;
					$status_grey = true;
				}
				else if(strtotime($sapv_max_date) > time())
				{
					if($curent_patient_sapv['primary_set'] < '5')
					{
						$stat = 'gray';
						$status_grey = true;
					}
					else
					{
						$stat = 'green';
					}
					$master_data[$v_ipid]['original_request_todo']['O1']['status'] = $stat;
				}
				else
				{
					$master_data[$v_ipid]['original_request_todo']['O1']['status'] = 'green';
				}
				$master_data[$v_ipid]['original_request_todo']['O1']['step_identification'] = $curent_patient_sapv['id'];

				//O2
				if((strtotime($sapv_max_date) <= time() && $curent_patient_sapv['secondary_set'] < '5') || $status_grey)
				{

					if(!$status_grey)
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}

					$master_data[$v_ipid]['original_request_todo']['O2']['status'] = $stat;
					$status_grey = true;
				}
				else if(strtotime($sapv_max_date) > time())
				{
					if($curent_patient_sapv['secondary_set'] < '5')
					{
						$stat = 'gray';
						$status_grey = true;
					}
					else
					{
						$stat = 'green';
					}
					$master_data[$v_ipid]['original_request_todo']['O2']['status'] = $stat;
				}
				else
				{
					$master_data[$v_ipid]['original_request_todo']['O2']['status'] = 'green';
				}
				$master_data[$v_ipid]['original_request_todo']['O2']['step_identification'] = $curent_patient_sapv['id'];
			}

			foreach($ipids_arr as $k_ipid => $v_ipid)
			{
				//recheck if all patients have status value
				if(!array_key_exists($v_ipid, $master_data))
				{
					$master_data[$v_ipid]['original_request_todo']['O1']['status'] = 'gray';
					$master_data[$v_ipid]['original_request_todo']['O2']['status'] = 'gray';
				}
			}
 
			return $master_data;
		}

		public function folgeverordnung_todo($ipids, $required_client = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($required_client)
			{
				$clientid = $required_client;
			}
			else
			{
				$clientid = $logininfo->clientid;
			}

			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}
			
			if (empty($ipids_arr)) {
				return;
			}
			
			$sql = "*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
			$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
			$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
			$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
			$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
			$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
			$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";


			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}

			$q = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->whereIn('p.ipid', $ipids_arr)
				->andWhere('p.isdelete = "0"')
				->andWhere('p.isstandbydelete = "0"')
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid);
			$q_res = $q->fetchArray();
// 			$ipids_arr_final[] = '999999999999999';
			$ipids_arr_final =  array();
			foreach($q_res as $k_p_res => $v_p_res)
			{
				$patient_details[$v_p_res['ipid']] = $v_p_res;
				$ipids_arr_final[] = $v_p_res['ipid'];
			}
			if (empty($ipids_arr_final)){
				$ipids_arr_final[] = '999999999999999';
			}
			$steps = new OrgSteps();

			$current_path = $this->get_paths($clientid, 'folgeverordnung_todo');
			$current_path_shortcuts = $steps->get_paths_steps($current_path, true);

			$patient_steps = new PatientSteps();
			$saved_data = $patient_steps->get_patient_steps($ipids_arr_final);
			
// 			print_r($saved_data); exit;
//04341e8488598b0629d3dbded265d55342eefdce
// sapv id  14420
			$sapv_verordnung = new SapvVerordnung();
			$sapv_highest_active = $sapv_verordnung->get_today_active_highest_sapv($ipids_arr_final, true);
// print_R($sapv_highest_active); exit;
			$patient_steps = new PatientSteps();
			$saved_patient_steps = $patient_steps->get_patient_steps($ipids_arr_final);

			//print_r($saved_data);
			foreach($sapv_highest_active['last'] as $k_ipid => $v_sapv_data)
			{
				$curent_sapv_start = date('Y-m-d', strtotime($v_sapv_data['verordnungam']));
				$curent_sapv_end = date('Y-m-d', strtotime($v_sapv_data['verordnungbis']));

				$today = date('Y-m-d', time());
				$next_sapv_data = $sapv_highest_active['next'][$v_sapv_data['ipid']];
				$next_sapv_start = date('Y-m-d', strtotime($next_sapv_data['verordnungam']));

				if(strtotime($curent_sapv_end) >= strtotime($today))
				{
					$sapv_ending_days = Pms_CommonData::get_days_number_between($curent_sapv_end, $today);
				}
				else
				{
					$sapv_ending_days = Pms_CommonData::get_days_number_between($today, $curent_sapv_end);
				}


				if(strtotime($curent_sapv_start) <= strtotime($today))
				{
					$sapv_started_days = Pms_CommonData::get_days_number_between($today, $curent_sapv_start);
				}

				
				if(strtotime($curent_sapv_end) >= strtotime($today))
				{
					$sapv_ending_working_days = Pms_CommonData::get_working_days($today, $curent_sapv_end);
				}
				else
				{
					$sapv_ending_working_days = Pms_CommonData::get_working_days($curent_sapv_end, $today);
				}
				
// 				print_r("\n days:");
// 				print_r($sapv_ending_days);
// 				print_r("  <br/>--------- working_days: ");
// 				print_r($sapv_ending_working_days);

				if(strtotime($next_sapv_start) <= strtotime($today))
				{
					$next_sapv_started_days = Pms_CommonData::get_days_number_between($today, $next_sapv_start);
				}

				//F1 - gets red 5 days before SAPV VErordnung ends (MANUAL CHECK) //  ISPC-854 - should get red  5 WORKING DAYS before SAPV ends - Added on: 12-05-2014
				$status_grey = false;
				$shortcut_id = array_search('F1', $current_path_shortcuts['shortcuts']);

				
				if($saved_data[$v_sapv_data['ipid']][$shortcut_id]['value'] == '1' && $saved_data[$v_sapv_data['ipid']][$shortcut_id]['step_identification'] == $v_sapv_data['id'] && $patient_details[$k_ipid]['isdischarged'] == '0')
				{
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['status'] = "green";
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['value'] = "1";
				}
				else if( ($sapv_ending_working_days <= '5' && $sapv_ending_working_days >= 0 && $saved_data[$v_sapv_data['ipid']][$shortcut_id]['step_identification'] != $v_sapv_data['id'] )  || $patient_details[$k_ipid]['isdischarged'] == '')
				{
					if(!$status_grey && $patient_details[$k_ipid]['isdischarged'] == '0')
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}

					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['status'] = $stat;
					$status_grey = true;
				}
// 				else if($sapv_ending_days > '5')
				else if($sapv_ending_working_days > '5')
				{
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['status'] = "blue";
					$status_grey = true;
				}
				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['sapv_ending_days'] = $sapv_ending_days;
				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['sapv_ending_working_days'] = $sapv_ending_working_days;

				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['step_identification'] = $v_sapv_data['id'];


				//F2 - gets red when at end of SAPV VErodnung no NEW SAPV Verordnung was added.
				//print_r("sapv_ending_days\n");
				//print_r($sapv_ending_days);
				//print_r("\nnext_sapv_data\n");
				//print_r($next_sapv_data);
				//print_r("\nsapv_ending_days\n");
				//print_r($curent_sapv_end." - ".$today);
				if(($sapv_ending_days > '3' && $sapv_ending_days >= '0') || $status_grey)
				{
					if(count($next_sapv_data) == '0')
					{
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['status'] = "gray";
						$status_grey = true;
					}
					else
					{
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['status'] = "green";
					}
				}
				else if($sapv_ending_days <= '3' && $sapv_ending_days >= '0')
				{
					if(count($next_sapv_data) == '0')
					{
						$stat = 'red';
						$status_grey = true;
					}
					else
					{
						$stat = 'green';
					}
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['status'] = $stat;
				}

				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['sapv_ending_days'] = $sapv_ending_days;
				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['next_sapv'] = $next_sapv_data;
				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['step_identification'] = $v_sapv_data['id'];


				//F3 - gets red if 2 days before actual SAPV Verordnung ends the new Verordnung has still status "ist bestellt"
				if(($sapv_ending_days <= '2' && $next_sapv_data['primary_set'] <= '2' && $sapv_ending_days >= '0' ) || $status_grey)
				{
					if(!$status_grey)
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}

					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F3']['status'] = $stat;
					$status_grey = true;
				}
				else if($sapv_ending_days > '2' && $next_sapv_data['primary_set'] <= '2')
				{
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F3']['status'] = 'blue';
					$status_grey = true;
				}
				else
				{
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F3']['status'] = "green";
				}
				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F3']['step_identification'] = $v_sapv_data['id'];

				//F4 - gets red if on last day of actual SAPV Verordnung the new Verordnung is in status "noch nicht versendet")
				if(($sapv_ending_days <= '1' && $next_sapv_data['primary_set'] == '3' && $sapv_ending_days >= '0') || $status_grey)
				{
					if(!$status_grey)
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}

					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F4']['status'] = $stat;
					$status_grey = true;
				}
				else if($sapv_ending_days > '1' && $next_sapv_data['primary_set'] <= '3')
				{
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F4']['status'] = 'blue';
					$status_grey = true;
				}
				else
				{
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F4']['status'] = "green";
				}
				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F4']['step_identification'] = $v_sapv_data['id'];

				//F5 - if NEW 2nd Page is longer than 5 days in status "nicht versendet"
				if(($next_sapv_started_days >= 5 && $next_sapv_data['secondary_set'] <= '3') || $status_grey)
				{
					if(!$status_grey)
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}

					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F5']['status'] = $stat;
					$status_grey = true;
				}
				else if($next_sapv_data['secondary_set'] <= '3')
				{
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F5']['status'] = "red";
					$status_grey = true;
				}
				else
				{
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F5']['status'] = "green";
				}
				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F5']['step_identification'] = $v_sapv_data['id'];

				//F6 - if new SAPV Verordnung is NOT directly after 1st Verordnung (so a none SAPV Day would be there)
				if((strtotime($curent_sapv_end) <= strtotime($next_sapv_start)) || $status_grey)
				{
					$days_between_sapvs = Pms_CommonData::get_days_number_between($next_sapv_data['verordnungam'], $curent_sapv_end);
				}
				else if(strtotime($curent_sapv_end) > strtotime($next_sapv_start))
				{
					$days_between_sapvs = '0';
				}

				if(($days_between_sapvs != '0' && $days_between_sapvs != '1' && $days_between_sapvs > '0') || $status_grey)
				{
					if(!$status_grey)
					{
						$stat = 'red';
					}
					else
					{
						$stat = 'gray';
					}

					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F6']['status'] = $stat;
					$status_grey = true;
				}
				else
				{
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F6']['status'] = "green";
				}

				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F6']['days_between_sapvs'] = $days_between_sapvs;
				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F6']['sapv_ending_days'] = $sapv_ending_days;
				$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F6']['step_identification'] = $v_sapv_data['id'];
			}

			foreach($ipids_arr as $k_ipid => $v_ipid)
			{
				//recheck if all patients have status value
				if(!array_key_exists($v_ipid, $master_data))
				{
					$master_data[$v_ipid]['folgeverordnung_todo']['F1']['status'] = 'gray';
					$master_data[$v_ipid]['folgeverordnung_todo']['F2']['status'] = 'gray';
					$master_data[$v_ipid]['folgeverordnung_todo']['F3']['status'] = 'gray';
					$master_data[$v_ipid]['folgeverordnung_todo']['F4']['status'] = 'gray';
					$master_data[$v_ipid]['folgeverordnung_todo']['F5']['status'] = 'gray';
					$master_data[$v_ipid]['folgeverordnung_todo']['F6']['status'] = 'gray';
				}
			}

			return $master_data;
		}

		public function discharged_todo($ipids, $clientid = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($clientid)
			{
				$clientid = $clientid;
			}
			else
			{
				$clientid = $logininfo->clientid;
			}

			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}

			if (empty($ipids_arr)){
				return;
			}
			
			$sql = "*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
			$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
			$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
			$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
			$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
			$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
			$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";


			// if super admin check if patient is visible or not

			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}

			$q = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->whereIn('p.ipid', $ipids_arr)
				->andWhere('p.isdelete = "0"')
				->andWhere('p.isstandbydelete = "0"')
				->andWhere('p.isdischarged = "1"')
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid);
			$q_res = $q->fetchArray();
// 			$ipids_arr_final[] = '999999999999999';
			$ipids_arr_final =  array();
			foreach($q_res as $k_p_res => $v_p_res)
			{
				$patient_details[$v_p_res['ipid']] = $v_p_res;
				$ipids_arr_final[] = $v_p_res['ipid'];
			}
			if (empty($ipids_arr_final)) {
				$ipids_arr_final[] = '999999999999999';
			}

			$current_path = $this->get_paths($clientid, 'discharged_todo');
			$steps = new OrgSteps();
			$current_path_shortcuts = $steps->get_paths_steps($current_path, true);

			$patient_steps = new PatientSteps();
			$saved_data = $patient_steps->get_patient_steps($ipids_arr_final);



			$dis = Doctrine_Query::create()
				->select("*")
				->from('DischargeMethod')
				->where("isdelete = 0  and clientid=" . $clientid . " and (abbr = 'TOD' or abbr = 'tod' or abbr='Tod' or abbr='DTH' or abbr='Dth' or abbr='dth' or abbr='DIS' or abbr='dis' or abbr='Dis' or abbr='transkh' or abbr='Transkh' or abbr='TRANSKH' or abbr='Verstorben' or abbr='verstorben'  or abbr='VERSTORBEN' or abbr='TODENT' or abbr='Todent' or abbr='todent')");
			$dis_method_array = $dis->fetchArray();

			$discharge_methods_ids[] = '999999999999999';
			foreach($dis_method_array as $k_discharge_method => $v_discharge_method)
			{
				$discharge_methods_details[$v_discharge_method['id']] = $v_discharge_method;
				$discharge_methods_ids[] = $v_discharge_method['id'];
			}
			$death_methods_arr = array('tod', 'todna', 'dth', 'todent', 'verstorben');
			$stabil_methods_arr = array('dis');
			$hospitals_methods_arr = array('transkh');

			//get discharge by methods
			$dis = Doctrine_Query::create()
				->select("*,AES_DECRYPT(discharge_comment,'" . Zend_Registry::get('salt') . "') as discharge_comment")
				->from('PatientDischarge')
				->whereIn('ipid', $ipids_arr_final)
				->andWhereIn('discharge_method', $discharge_methods_ids)
				->andWhere('isdelete = "0"');
			$dis_arr = $dis->fetchArray();
			foreach($dis_arr as $k_dis => $v_dis)
			{
				$discharge_date = date('Y-m-d', strtotime($v_dis['discharge_date']));
				$today_date = date('Y-m-d', time());

				$ts_discharge_date = strtotime($discharge_date);
				$ts_today_date = strtotime($today_date);

				if($ts_today_date >= $ts_discharge_date)
				{
					$discharge_days = Pms_CommonData::get_days_number_between($today_date, $discharge_date);
				}

				$discharged_patients[$v_dis['ipid']] = $v_dis;
				$discharged_patients[$v_dis['ipid']]['ts_discharge_date'] = $ts_discharge_date;
				$discharged_patients[$v_dis['ipid']]['ts_today_date'] = $ts_today_date;
				$discharged_patients[$v_dis['ipid']]['discharged_days'] = $discharge_days;
			}


			foreach($patient_details as $k_patient => $v_patient)
			{
				$executed_shortcuts[$v_patient['ipid']] = array();
				//E1
				$shortcut_id = array_search('E1', $current_path_shortcuts['shortcuts']);
				$discharge_method[$v_patient['ipid']] = strtolower($discharge_methods_details[$discharged_patients[$v_patient['ipid']]['discharge_method']]['abbr']);

				if(in_array($discharge_method[$v_patient['ipid']], $death_methods_arr) && !in_array($shortcut_id, $executed_shortcuts[$v_patient['ipid']]))
				{
					$executed_shortcuts[$v_patient['ipid']][] = $shortcut_id;
					if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '2')
					{
						if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '1' && $saved_data[$v_patient['ipid']][$shortcut_id]['step_identification'] == $discharged_patients[$v_patient['ipid']]['id'] )
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E1']['status'] = 'green';
							$master_data[$v_patient['ipid']]['discharged_todo']['E1']['value'] = $saved_data[$v_patient['ipid']][$shortcut_id]['value'];
						}
						else if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '0' || !array_key_exists($shortcut_id, $saved_data))
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E1']['status'] = 'red';
						}
					}
					else if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient['ipid']]['discharged_days'] < '2')
					{
						if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '1')
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E1']['status'] = 'green';
							$master_data[$v_patient['ipid']]['discharged_todo']['E1']['value'] = $saved_data[$v_patient['ipid']][$shortcut_id]['value'];
						}
						else
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E1']['status'] = 'blue';
						}
					}
				}
				else
				{
					$master_data[$v_patient['ipid']]['discharged_todo']['E1']['status'] = 'gray';
				}
				$master_data[$v_patient['ipid']]['discharged_todo']['E1']['step_identification'] = $discharged_patients[$v_patient['ipid']]['id'];


				//E2
				$shortcut_id = array_search('E2', $current_path_shortcuts['shortcuts']);
				$discharge_method[$v_patient['ipid']] = strtolower($discharge_methods_details[$discharged_patients[$v_patient['ipid']]['discharge_method']]['abbr']);

				if(in_array($discharge_method[$v_patient['ipid']], $stabil_methods_arr) && !in_array($shortcut_id, $executed_shortcuts[$v_patient['ipid']]))
				{
					$executed_shortcuts[$v_patient['ipid']][] = $shortcut_id;
					if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '2')
					{
						if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '1'  && $saved_data[$v_patient['ipid']][$shortcut_id]['step_identification'] == $discharged_patients[$v_patient['ipid']]['id'] )
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E2']['status'] = 'green';
							$master_data[$v_patient['ipid']]['discharged_todo']['E2']['value'] = $saved_data[$v_patient['ipid']][$shortcut_id]['value'];
						}
						else if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '0' || !array_key_exists($shortcut_id, $saved_data))
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E2']['status'] = 'red';
						}
					}
					else if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient['ipid']]['discharged_days'] < '2')
					{
						if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '1')
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E2']['status'] = 'green';
							$master_data[$v_patient['ipid']]['discharged_todo']['E2']['value'] = $saved_data[$v_patient['ipid']][$shortcut_id]['value'];
						}
						else
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E2']['status'] = 'blue';
						}
					}
				}
				else
				{
					$master_data[$v_patient['ipid']]['discharged_todo']['E2']['status'] = 'gray';
				}
				$master_data[$v_patient['ipid']]['discharged_todo']['E2']['step_identification'] = $discharged_patients[$v_patient['ipid']]['id'];

				//E3
				$shortcut_id = array_search('E3', $current_path_shortcuts['shortcuts']);
				$discharge_method[$v_patient['ipid']] = strtolower($discharge_methods_details[$discharged_patients[$v_patient['ipid']]['discharge_method']]['abbr']);

				if(in_array($discharge_method[$v_patient['ipid']], $hospitals_methods_arr) && !in_array($shortcut_id, $executed_shortcuts[$v_patient['ipid']]))
				{
					$executed_shortcuts[$v_patient['ipid']][] = $shortcut_id;
					if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '2')
					{
						if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '1'  && $saved_data[$v_patient['ipid']][$shortcut_id]['step_identification'] == $discharged_patients[$v_patient['ipid']]['id'])
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E3']['status'] = 'green';
							$master_data[$v_patient['ipid']]['discharged_todo']['E3']['value'] = $saved_data[$v_patient['ipid']][$shortcut_id]['value'];
						}
						else if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '0' || !array_key_exists($shortcut_id, $saved_data))
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E3']['status'] = 'red';
						}
					}
					else if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient['ipid']]['discharged_days'] < '2')
					{
						if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '1')
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E3']['status'] = 'green';
							$master_data[$v_patient['ipid']]['discharged_todo']['E3']['value'] = $saved_data[$v_patient['ipid']][$shortcut_id]['value'];
						}
						else
						{
							$master_data[$v_patient['ipid']]['discharged_todo']['E3']['status'] = 'blue';
						}
					}
				}
				else
				{
					$master_data[$v_patient['ipid']]['discharged_todo']['E3']['status'] = 'gray';
				}
				$master_data[$v_patient['ipid']]['discharged_todo']['E3']['step_identification'] = $discharged_patients[$v_patient['ipid']]['id'];
			}

			foreach($ipids_arr as $k_ipid => $v_ipid)
			{
				//recheck if all patients have status value
				if(!array_key_exists($v_ipid, $master_data))
				{
					$master_data[$v_ipid]['discharged_todo']['E1']['status'] = 'gray';
					$master_data[$v_ipid]['discharged_todo']['E2']['status'] = 'gray';
					$master_data[$v_ipid]['discharged_todo']['E3']['status'] = 'gray';
				}
			}

			return $master_data;
		}

		public function discharged_billing_todo($ipids, $clientid = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($clientid)
			{
				$clientid = $clientid;
			}
			else
			{
				$clientid = $logininfo->clientid;
			}

			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}
			
			if (empty($ipids_arr)) {
				return;
			}

			$sql = "*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
			$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
			$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
			$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
			$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
			$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
			$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";

			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}

			$q = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->whereIn('p.ipid', $ipids_arr)
				->andWhere('p.isdelete = "0"')
				->andWhere('p.isstandbydelete = "0"')
				->andWhere('p.isdischarged = "1"')
				->leftJoin("p.EpidIpidMapping e")
				->andWhere('e.clientid = ' . $clientid);
			$q_res = $q->fetchArray();
// 			$ipids_arr_final[] = '999999999999999';
			$ipids_arr_final =  array();
			foreach($q_res as $k_p_res => $v_p_res)
			{
				$patient_details[$v_p_res['ipid']] = $v_p_res;
				$ipids_arr_final[] = $v_p_res['ipid'];
			}
			if (empty($ipids_arr_final)) {
				$ipids_arr_final[] = '999999999999999';
			}

			$current_path = $this->get_paths($clientid, 'discharged_billing_todo');
			$steps = new OrgSteps();
			$current_path_shortcuts = $steps->get_paths_steps($current_path, true);

			$patient_steps = new PatientSteps();
			$saved_data = $patient_steps->get_patient_steps($ipids_arr_final);


			$sapv_verordnung = new SapvVerordnung();
			$all_sapv_data = $sapv_verordnung->get_all_sapvs($ipids_arr_final);

			foreach($all_sapv_data as $k => $sapv_values)
			{
				$patient_all_sapv_data[$sapv_values['ipid']][] = $sapv_values;

				if($all_sapv_data[$k]['primary_set'] == "5" && $all_sapv_data[$k]['secondary_set'] == "5")
				{
					$patient_sapv_data[$sapv_values['ipid']]['original'][] = $sapv_values;
				}
				else
				{
					$patient_sapv_data[$sapv_values['ipid']]['inprogress'][] = $sapv_values;
					$patient_sapv_data[$sapv_values['ipid']]['extra_info'][] = date('d.m.Y', strtotime($sapv_values['verordnungam'])) . ' - ' . date('d.m.Y', strtotime($sapv_values['verordnungbis']));
				}
			}

			//get discharge details
			$dis = Doctrine_Query::create()
				->select("*,AES_DECRYPT(discharge_comment,'" . Zend_Registry::get('salt') . "') as discharge_comment")
				->from('PatientDischarge')
				->whereIn('ipid', $ipids_arr_final)
				->andWhere('isdelete = "0"');
			$dis_arr = $dis->fetchArray();

			foreach($dis_arr as $k_dis => $v_dis)
			{
				$discharge_date = date('Y-m-d', strtotime($v_dis['discharge_date']));
				$today_date = date('Y-m-d', time());

				$ts_discharge_date = strtotime($discharge_date);
				$ts_today_date = strtotime($today_date);

				if($ts_today_date >= $ts_discharge_date)
				{
					$discharge_days = Pms_CommonData::get_days_number_between($today_date, $discharge_date);
				}

				$discharged_patients[$v_dis['ipid']] = $v_dis;
				$discharged_patients[$v_dis['ipid']]['ts_discharge_date'] = $ts_discharge_date;
				$discharged_patients[$v_dis['ipid']]['ts_today_date'] = $ts_today_date;
				$discharged_patients[$v_dis['ipid']]['discharged_days'] = $discharge_days;
			}
			
			foreach($patient_details as $k_patient => $v_patient)
			{
				if($patient_all_sapv_data[$v_patient['ipid']])
				{

					$executed_shortcuts[$v_patient['ipid']] = array();
					//AB1
					$shortcut_id = array_search('AB1', $current_path_shortcuts['shortcuts']);
					if(!in_array($shortcut_id, $executed_shortcuts[$v_patient['ipid']]))
					{
						$executed_shortcuts[$v_patient['ipid']][] = $shortcut_id;
						if((!$patient_sapv_data[$v_patient['ipid']]['inprogress'] || empty($patient_sapv_data[$v_patient['ipid']]['inprogress'])))
						{
							if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '1')
							{
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB1']['status'] = 'green';
							}
							else if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient['ipid']]['discharged_days'] < '1')
							{
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB1']['status'] = 'blue';
							}
						}
						else if(!empty($patient_sapv_data[$v_patient['ipid']]['inprogress']))
						{
							$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB1']['status'] = 'red';
							$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB1']['extra_info'] = implode(', ', $patient_sapv_data[$v_patient['ipid']]['extra_info']);
						}
						else
						{
							$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB1']['status'] = 'gray';
						}
					}
					else
					{
						$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB1']['status'] = 'gray';
					}
					$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB1']['step_identification'] = $discharged_patients[$v_patient['ipid']]['id'];
					
					
					//AB2
					$shortcut_id = array_search('AB2', $current_path_shortcuts['shortcuts']);
					if(!in_array($shortcut_id, $executed_shortcuts[$v_patient['ipid']]))
					{
						$executed_shortcuts[$v_patient['ipid']][] = $shortcut_id;

						if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '7')
						{
							if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '1'  && $saved_data[$v_patient['ipid']][$shortcut_id]['step_identification'] == $discharged_patients[$v_patient['ipid']]['id']  )
							{
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB2']['status'] = 'green';
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB2']['value'] = $saved_data[$v_patient['ipid']][$shortcut_id]['value'];
							}
							else if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '0' || !array_key_exists($shortcut_id, $saved_data))
							{
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB2']['status'] = 'red';
							}
						}
						else if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient['ipid']]['discharged_days'] < '7')
						{
							if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '1')
							{
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB2']['status'] = 'green';
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB2']['value'] = $saved_data[$v_patient['ipid']][$shortcut_id]['value'];
							}
							else
							{
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB2']['status'] = 'blue';
							}
						}
					}
					else
					{
						$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB2']['status'] = 'gray';
					}
					$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB2']['step_identification'] = $discharged_patients[$v_patient['ipid']]['id'];
					
					
					
					//AB3
					$shortcut_id = array_search('AB3', $current_path_shortcuts['shortcuts']);
					if(!in_array($shortcut_id, $executed_shortcuts[$v_patient['ipid']]))
					{
						$executed_shortcuts[$v_patient['ipid']][] = $shortcut_id;

						if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '10')
						{
							if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '1'  && $saved_data[$v_patient['ipid']][$shortcut_id]['step_identification'] == $discharged_patients[$v_patient['ipid']]['id'] )
							{
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB3']['status'] = 'green';
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB3']['value'] = $saved_data[$v_patient['ipid']][$shortcut_id]['value'];
							}
							else if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '0' || !array_key_exists($shortcut_id, $saved_data))
							{
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB3']['status'] = 'red';
							}
						}
						else if($discharged_patients[$v_patient['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient['ipid']]['discharged_days'] < '10')
						{
							if($saved_data[$v_patient['ipid']][$shortcut_id]['value'] == '1')
							{
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB3']['status'] = 'green';
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB3']['value'] = $saved_data[$v_patient['ipid']][$shortcut_id]['value'];
							}
							else
							{
								$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB3']['status'] = 'blue';
							}
						}
					}
					else
					{
						$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB3']['status'] = 'gray';
					}
					
					$master_data[$v_patient['ipid']]['discharged_billing_todo']['AB3']['step_identification'] = $discharged_patients[$v_patient['ipid']]['id'];
				}
			}

			foreach($ipids_arr as $k_ipid => $v_ipid)
			{
				//recheck if all patients have status value
				if(!array_key_exists($v_ipid, $master_data))
				{
					$master_data[$v_ipid]['discharged_billing_todo']['AB1']['status'] = 'gray';
					$master_data[$v_ipid]['discharged_billing_todo']['AB2']['status'] = 'gray';
					$master_data[$v_ipid]['discharged_billing_todo']['AB3']['status'] = 'gray';
				}
			}
			return $master_data;
		}

		//multiclient rewriten functions
		public function get_paths_multiclient($client, $function = false)
		{
			if(is_array($client))
			{
				$clientid_arr = $client;
			}
			else
			{
				$clientid_arr = array($client);
			}

			$q = Doctrine_Query::create()
				->select('*')
				->from('OrgPaths')
				->whereIn('client', $clientid_arr)
				->andWhere('isdelete="0"');

			if($function)
			{
				$q->andWhere('function = "' . $function . '"');
			}

			$qres = $q->fetchArray();

			if($qres)
			{
				if(is_array($client))
				{
					foreach($qres as $k_res => $v_res)
					{
						$q_res_arr[$v_res['client']][] = $v_res;
					}
				}
				else
				{
					$q_res_arr = $qres;
				}


				return $q_res_arr;
			}
			else
			{
				return false;
			}
		}

		public function get_org_data_overview($ipids, $all_clients_path_arr = array())
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			$clientid[] = '99999999999999';
			foreach($all_clients_path_arr as $k_client => $v_client_paths)
			{
				$clientid[] = $k_client;
			}

			$steps = new OrgSteps();
			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}

			$sql = "*, e.ipid,e.epid,e.clientid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1) as first_name,";
			$sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middle_name,";
			$sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as last_name,";
			$sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
			$sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
			$sql .= "CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1)  as street1,";
			$sql .= "CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1)  as street2,";
			$sql .= "CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1)  as zip,";
			$sql .= "CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1)  as city,";
			$sql .= "CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,";
			$sql .= "CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1)  as mobile,";
			$sql .= "CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1)  as sex";


			// if super admin check if patient is visible or not
			if($logininfo->usertype == 'SA')
			{
				$sql = "*, e.ipid,e.epid,e.clientid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
				$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
			}

			$q = Doctrine_Query::create()
				->select($sql)
				->from('PatientMaster p')
				->whereIn('p.ipid', $ipids_arr)
				->andWhere('p.isdelete = "0"')
				->andWhere('p.isstandbydelete = "0"')
				->leftJoin("p.EpidIpidMapping e")
				->andWhereIn('e.clientid', $clientid);
			$q_res = $q->fetchArray();

			$ipids_arr_final[] = '999999999999999';
			$ipids_arr_final_discharged[] = '999999999999999';
			foreach($q_res as $k_p_res => $v_p_res)
			{
				$patient_details[$v_p_res['ipid']] = $v_p_res;
				$ipids_arr_final[] = $v_p_res['ipid'];

				if($v_p_res['isdischarged'] == '1')
				{
					$ipids_arr_final_discharged[] = $v_p_res['ipid'];
				}
			}
			
			
			$patients_admision = Doctrine_Query::create()
			->select('*')
			->from('PatientReadmission')
			->whereIn('ipid', $ipids_arr_final)
			->andWhere('date_type = 1');
			$fadmarr = $patients_admision->fetchArray();
			
			foreach($fadmarr as $ki => $ipid_readmission){
				if( strtotime(date("Y-m-d H:i", strtotime($patient_details[$ipid_readmission['ipid']]['admission_date'])))  ==  strtotime(date("Y-m-d H:i", strtotime($ipid_readmission['date']))))
				{
					$patient_identification[$ipid_readmission['ipid']]['identification'] = $ipid_readmission['id'];
				}
			}
				

			//get highest sapv verordnung
			$sapv_verordnung = new SapvVerordnung();
			$sapv_highest_active = $sapv_verordnung->get_today_active_highest_sapv($ipids_arr_final, true);

			//get all sapv data
			$all_sapv_data = $sapv_verordnung->get_all_sapvs($ipids_arr_final);

			foreach($all_sapv_data as $k => $sapv_values)
			{
				$patient_all_sapv_data[$sapv_values['ipid']][] = $sapv_values;
				//check if patient is in discharged array
				if(in_array($sapv_values['ipid'], $ipids_arr_final_discharged))
				{

					if($all_sapv_data[$k]['primary_set'] == "5" && $all_sapv_data[$k]['secondary_set'] == "5")
					{
						$patient_sapv_data[$sapv_values['ipid']]['original'][] = $sapv_values;
					}
					else
					{
						$patient_sapv_data[$sapv_values['ipid']]['inprogress'][] = $sapv_values;
						$patient_sapv_data[$sapv_values['ipid']]['extra_info'][] = date('d.m.Y', strtotime($sapv_values['verordnungam'])) . ' - ' . date('d.m.Y', strtotime($sapv_values['verordnungbis']));
					}
				}
			}

			//changed to get for multiple clients
			$current_path = $this->get_paths_multiclient($clientid);
			$current_path_shortcuts = $steps->get_paths_steps_multiclient($current_path, true);

			//get patients saved data
			$patient_steps = new PatientSteps();
			$saved_data = $patient_steps->get_patient_steps($ipids_arr_final);

			//get discharge todos required data
			$dis = Doctrine_Query::create()
				->select("*")
				->from('DischargeMethod')
				->where("isdelete = 0")
				->andWhereIn('clientid', $clientid);
			$dis_method_array = $dis->fetchArray();

			$discharge_methods_ids[] = '999999999999999';
			foreach($dis_method_array as $k_discharge_method => $v_discharge_method)
			{
				$discharge_methods_details[$v_discharge_method['id']] = $v_discharge_method;
				$discharge_methods_ids[] = $v_discharge_method['id'];
			}
			$death_methods_arr = array('tod', 'todna', 'dth', 'todent', 'verstorben');
			$stabil_methods_arr = array('dis');
			$hospitals_methods_arr = array('transkh');

			//get discharge by methods
			$dis = Doctrine_Query::create()
				->select("*,AES_DECRYPT(discharge_comment,'" . Zend_Registry::get('salt') . "') as discharge_comment")
				->from('PatientDischarge')
				->whereIn('ipid', $ipids_arr_final)
				->andWhereIn('discharge_method', $discharge_methods_ids)
				->andWhere('isdelete = "0"');
			$dis_arr = $dis->fetchArray();

			$today_date = date('Y-m-d', time());
			$ts_today_date = strtotime($today_date);

			foreach($dis_arr as $k_dis => $v_dis)
			{
				$discharge_date = date('Y-m-d', strtotime($v_dis['discharge_date']));
				$ts_discharge_date = strtotime($discharge_date);

				if($ts_today_date >= $ts_discharge_date)
				{
					$discharge_days = Pms_CommonData::get_days_number_between($today_date, $discharge_date);
				}

				$discharged_patients[$v_dis['ipid']] = $v_dis;
				$discharged_patients[$v_dis['ipid']]['ts_discharge_date'] = $ts_discharge_date;
				$discharged_patients[$v_dis['ipid']]['ts_today_date'] = $ts_today_date;
				$discharged_patients[$v_dis['ipid']]['discharged_days'] = $discharge_days;
			}
// print_r($discharged_patients); exit;


			//foreach from hell, check every minion for shortcuts 
			foreach($patient_details as $k_ipid_data => $v_patient_data)
			{
				$patient_client_id = $v_patient_data['EpidIpidMapping']['clientid'];
				//admission_todo checks START

				if(in_array('admission_todo', $all_clients_path_arr[$patient_client_id]))
				{
					$curent_patient_sapv = $sapv_highest_active['last'][$k_ipid_data];
					$admission_date = date('Y-m-d', strtotime($v_patient_data['admission_date']));

					//A1a, A1b, A2
					$first_admisson_date_check = date('Y-m-d', strtotime('+1 day', strtotime($admission_date)));
					//A1c
					$second_admission_date_check = date('Y-m-d', strtotime('+2 days', strtotime($admission_date)));
					//A2a
					$third_admission_date_check = date('Y-m-d', strtotime('+4 days', strtotime($admission_date)));


					$r1start = strtotime($curent_patient_sapv['verordnungam']);
					$r1end = strtotime($curent_patient_sapv['verordnungbis']);
					$r2start = $r2end = strtotime($first_admisson_date_check);
					$r2start_second = $r2end_second = strtotime($second_admission_date_check);
					$r2start_third = $r2end_third = strtotime($third_admission_date_check);


					//new
					$first_check_period = Pms_CommonData::get_days_number_between($curent_patient_sapv['verordnungam'], $first_admisson_date_check);
					if($first_check_period < 0)
					{
						$first_check_period = ($first_check_period * (-1));
					}

					//new
					$second_check_period = Pms_CommonData::get_days_number_between($curent_patient_sapv['verordnungam'], $second_admission_date_check);
					if($second_check_period < 0)
					{
						$second_check_period = ($second_check_period * (-1));
					}

					$third_check_period = Pms_CommonData::get_days_number_between($curent_patient_sapv['verordnungam'], $third_admission_date_check);
					if($third_check_period < 0)
					{
						$third_check_period = ($third_check_period * (-1));
					}

					$intersected = Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end);
					$intersected_second = Pms_CommonData::isintersected($r1start, $r1end, $r2start_third, $r2end_second);
					$intersected_third = Pms_CommonData::isintersected($r1start, $r1end, $r2start_third, $r2end_third);

					//primary sapv status
					if(!empty($curent_patient_sapv) && $patient_details[$k_ipid_data]['isdischarged'] == '0')
					{
						$status_grey = false;
						$status_second_grey = false;
					}
					else
					{
						$status_grey = true;
						$status_second_grey = true;
					}

					if((($intersected || $intersected_second || $intersected_third) && $first_check_period >= '0' && $curent_patient_sapv['primary_set'] <= '1' && $curent_patient_sapv['primary_set'] != '0') || $status_grey)
					{

						if(!$status_grey)
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}

						$master_data[$k_ipid_data]['admission_todo']['A1a']['status'] = $stat;
						$status_grey = true;
					}
					else if(($intersected || $intersected_second || $intersected_third) && $curent_patient_sapv['primary_set'] > '1')
					{
						$master_data[$k_ipid_data]['admission_todo']['A1a']['status'] = 'green';
					}
					
					$master_data[$k_ipid_data]['admission_todo']['A1a']['step_identification'] = $patient_identification[$k_ipid_data]['identification'];

					
					
					
					if((($intersected || $intersected_second || $intersected_third) && $first_check_period >= '0' && $curent_patient_sapv['primary_set'] <= '2' && $curent_patient_sapv['primary_set'] != '0') || $status_grey)
					{
						if(!$status_grey)
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}

						$master_data[$k_ipid_data]['admission_todo']['A1b']['status'] = $stat;
						$status_grey = true;
					}
					else if(($intersected || $intersected_second || $intersected_third) && $curent_patient_sapv['primary_set'] > '2')
					{
						$master_data[$k_ipid_data]['admission_todo']['A1b']['status'] = 'green';
					}
					$master_data[$k_ipid_data]['admission_todo']['A1b']['step_identification'] =  $patient_identification[$k_ipid_data]['identification'];
					
					if((($intersected_second || $intersected_third) && $second_check_period >= '0' && $curent_patient_sapv['primary_set'] <= '3' && $curent_patient_sapv['primary_set'] != '0') || $status_grey)
					{
						if(!$status_grey)
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}
						$master_data[$k_ipid_data]['admission_todo']['A1c']['status'] = $stat;
						$status_grey = true;
					}
					else if(($intersected_second || $intersected_third) && $curent_patient_sapv['primary_set'] > '3')
					{
						$master_data[$k_ipid_data]['admission_todo']['A1c']['status'] = 'green';
					}
					$master_data[$k_ipid_data]['admission_todo']['A1c']['step_identification'] =  $patient_identification[$k_ipid_data]['identification'];

					//secondary sapv status
					//A2
					if((($intersected_second || $intersected_third) && $second_check_period >= '0' && $curent_patient_sapv['secondary_set'] <= '1' && $curent_patient_sapv['secondary_set'] >= '0') || $status_second_grey)
					{
						if(!$status_second_grey)
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}
						$master_data[$k_ipid_data]['admission_todo']['A2']['status'] = $stat;
						$status_second_grey = true;
					}
					else if($curent_patient_sapv['secondary_set'] > '1')
					{
						$master_data[$k_ipid_data]['admission_todo']['A2']['status'] = 'green';
					}
					
					$master_data[$k_ipid_data]['admission_todo']['A2']['step_identification'] =  $patient_identification[$k_ipid_data]['identification'];
					
					//A2a
					if(($intersected_third && $third_check_period >= '0' && $curent_patient_sapv['secondary_set'] <= '3' && $curent_patient_sapv['secondary_set'] != '0') || $status_second_grey)
					{
						if(!$status_second_grey)
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}
						$master_data[$k_ipid_data]['admission_todo']['A2a']['status'] = $stat;
						$status_second_grey = true;
					}
					else if($intersected_third && $curent_patient_sapv['secondary_set'] > '3')
					{
						$master_data[$k_ipid_data]['admission_todo']['A2a']['status'] = 'green';
					}
					$master_data[$k_ipid_data]['admission_todo']['A2a']['step_identification'] =  $patient_identification[$k_ipid_data]['identification'];

					
					//A3
					$shortcut_id = array_search('A3', $current_path_shortcuts['shortcuts'][$patient_client_id]);
					if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
					{
						$master_data[$k_ipid_data]['admission_todo']['A3']['status'] = "green";
						$master_data[$k_ipid_data]['admission_todo']['A3']['value'] = "1";
					}
					else if($patient_details[$k_ipid_data]['isdischarged'] == '1')
					{
						$master_data[$k_ipid_data]['admission_todo']['A3']['status'] = "gray";
					}
					else
					{
						$master_data[$k_ipid_data]['admission_todo']['A3']['status'] = "red";
					}
					$master_data[$k_ipid_data]['admission_todo']['A3']['step_identification'] =  $patient_identification[$k_ipid_data]['identification'];				
					
				}
				//admission_todo checks  END
				//original_request_todo checks START
				if(in_array('original_request_todo', $all_clients_path_arr[$patient_client_id]))
				{
					//O1, O2
					$curent_patient_sapv = $sapv_highest_active['last'][$k_ipid_data];
					$sapv_start_date = date('Y-m-d', strtotime($curent_patient_sapv['verordnungam']));
					$sapv_max_date = date('Y-m-d', strtotime('+10 day', strtotime($curent_patient_sapv['verordnungam'])));

					//O1
					if(!empty($curent_patient_sapv))
					{
						$status_grey = false;
					}
					else
					{
						$status_grey = true;
					}
					//print_r("sapv_max_date\n");
					//print_r($sapv_max_date);
					//print_r("sapv_start_date\n");
					//print_r($sapv_start_date);
					//print_r($curent_patient_sapv);

					if((strtotime($sapv_max_date) <= time() && $curent_patient_sapv['primary_set'] < '5') || $status_grey)
					{

						if(!$status_grey)
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}

						$master_data[$k_ipid_data]['original_request_todo']['O1']['status'] = $stat;
						$status_grey = true;
					}
					else if(strtotime($sapv_max_date) > time())
					{
						if($curent_patient_sapv['primary_set'] < '5')
						{
							$stat = 'gray';
							$status_grey = true;
						}
						else
						{
							$stat = 'green';
						}
						$master_data[$k_ipid_data]['original_request_todo']['O1']['status'] = $stat;
					}
					else
					{
						$master_data[$k_ipid_data]['original_request_todo']['O1']['status'] = 'green';
					}
					$master_data[$k_ipid_data]['original_request_todo']['O1']['step_identification'] = $curent_patient_sapv['id'];
					
					//O2
					if((strtotime($sapv_max_date) <= time() && $curent_patient_sapv['secondary_set'] < '5') || $status_grey)
					{

						if(!$status_grey)
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}

						$master_data[$k_ipid_data]['original_request_todo']['O2']['status'] = $stat;
						$status_grey = true;
					}
					else if(strtotime($sapv_max_date) > time())
					{
						if($curent_patient_sapv['secondary_set'] < '5')
						{
							$stat = 'gray';
							$status_grey = true;
						}
						else
						{
							$stat = 'green';
						}
						$master_data[$k_ipid_data]['original_request_todo']['O2']['status'] = $stat;
					}
					else
					{
						$master_data[$k_ipid_data]['original_request_todo']['O2']['status'] = 'green';
					}
					$master_data[$k_ipid_data]['original_request_todo']['O2']['step_identification'] = $curent_patient_sapv['id'];
				}

				//original_request_todo checks END
				//discharged_todo checks START
				if(in_array('discharged_todo', $all_clients_path_arr[$patient_client_id]))
				{
					$executed_shortcuts[$v_patient_data['ipid']] = array();
					//E1
					$shortcut_id = array_search('E1', $current_path_shortcuts['shortcuts'][$patient_client_id]);
					$discharge_method[$v_patient_data['ipid']] = strtolower($discharge_methods_details[$discharged_patients[$v_patient_data['ipid']]['discharge_method']]['abbr']);

					if(in_array($discharge_method[$v_patient_data['ipid']], $death_methods_arr) && !in_array($shortcut_id, $executed_shortcuts[$v_patient_data['ipid']]))
					{
						$executed_shortcuts[$v_patient_data['ipid']][] = $shortcut_id;
						if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '2')
						{
							if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E1']['status'] = 'green';
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E1']['value'] = $saved_data[$v_patient_data['ipid']][$shortcut_id]['value'];
							}
							else if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '0' || !array_key_exists($shortcut_id, $saved_data))
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E1']['status'] = 'red';
							}
						}
						else if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient_data['ipid']]['discharged_days'] < '2')
						{
							if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E1']['status'] = 'green';
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E1']['value'] = $saved_data[$v_patient_data['ipid']][$shortcut_id]['value'];
							}
							else
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E1']['status'] = 'blue';
							}
						}
					}
					else
					{
						$master_data[$v_patient_data['ipid']]['discharged_todo']['E1']['status'] = 'gray';
					}
					$master_data[$v_patient_data['ipid']]['discharged_todo']['E1']['step_identification'] =  $discharged_patients[$v_patient_data['ipid']]['id'] ;


					//E2
					$shortcut_id = array_search('E2', $current_path_shortcuts['shortcuts'][$patient_client_id]);
					$discharge_method[$v_patient_data['ipid']] = strtolower($discharge_methods_details[$discharged_patients[$v_patient_data['ipid']]['discharge_method']]['abbr']);

					if(in_array($discharge_method[$v_patient_data['ipid']], $stabil_methods_arr) && !in_array($shortcut_id, $executed_shortcuts[$v_patient_data['ipid']]))
					{
						$executed_shortcuts[$v_patient_data['ipid']][] = $shortcut_id;
						if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '2')
						{
							if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E2']['status'] = 'green';
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E2']['value'] = $saved_data[$v_patient_data['ipid']][$shortcut_id]['value'];
							}
							else if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '0' || !array_key_exists($shortcut_id, $saved_data))
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E2']['status'] = 'red';
							}
						}
						else if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient_data['ipid']]['discharged_days'] < '2')
						{
							if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E2']['status'] = 'green';
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E2']['value'] = $saved_data[$v_patient_data['ipid']][$shortcut_id]['value'];
							}
							else
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E2']['status'] = 'blue';
							}
						}
					}
					else
					{
						$master_data[$v_patient_data['ipid']]['discharged_todo']['E2']['status'] = 'gray';
					}
					$master_data[$v_patient_data['ipid']]['discharged_todo']['E2']['step_identification'] =  $discharged_patients[$v_patient_data['ipid']]['id'] ;

					//E3
					$shortcut_id = array_search('E3', $current_path_shortcuts['shortcuts'][$patient_client_id]);
					$discharge_method[$v_patient_data['ipid']] = strtolower($discharge_methods_details[$discharged_patients[$v_patient_data['ipid']]['discharge_method']]['abbr']);

					if(in_array($discharge_method[$v_patient_data['ipid']], $hospitals_methods_arr) && !in_array($shortcut_id, $executed_shortcuts[$v_patient_data['ipid']]))
					{
						$executed_shortcuts[$v_patient_data['ipid']][] = $shortcut_id;
						if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '2')
						{
							if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E3']['status'] = 'green';
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E3']['value'] = $saved_data[$v_patient_data['ipid']][$shortcut_id]['value'];
							}
							else if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '0' || !array_key_exists($shortcut_id, $saved_data))
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E3']['status'] = 'red';
							}
						}
						else if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient_data['ipid']]['discharged_days'] < '2')
						{
							if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E3']['status'] = 'green';
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E3']['value'] = $saved_data[$v_patient_data['ipid']][$shortcut_id]['value'];
							}
							else
							{
								$master_data[$v_patient_data['ipid']]['discharged_todo']['E3']['status'] = 'blue';
							}
						}
					}
					else
					{
						$master_data[$v_patient_data['ipid']]['discharged_todo']['E3']['status'] = 'gray';
					}
					
					$master_data[$v_patient_data['ipid']]['discharged_todo']['E3']['step_identification'] =  $discharged_patients[$v_patient_data['ipid']]['id'] ;					
				}
				//discharged_todo checks END	
				//discharged_billing_todo checks START
				if(in_array('discharged_billing_todo', $all_clients_path_arr[$patient_client_id]))
				{
					if($patient_all_sapv_data[$v_patient_data['ipid']] && in_array($v_patient_data['ipid'], $ipids_arr_final_discharged))
					{

						$executed_shortcuts[$v_patient_data['ipid']] = array();
						//AB1
						$shortcut_id = array_search('AB1', $current_path_shortcuts['shortcuts'][$patient_client_id]);

						if(!in_array($shortcut_id, $executed_shortcuts[$v_patient_data['ipid']]))
						{
							$executed_shortcuts[$v_patient_data['ipid']][] = $shortcut_id;
							if((!$patient_sapv_data[$v_patient_data['ipid']]['inprogress'] || empty($patient_sapv_data[$v_patient_data['ipid']]['inprogress'])))
							{
								if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '1')
								{
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB1']['status'] = 'green';
								}
								else if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient_data['ipid']]['discharged_days'] < '1')
								{
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB1']['status'] = 'blue';
								}
							}
							else if(!empty($patient_sapv_data[$v_patient_data['ipid']]['inprogress']))
							{
								$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB1']['status'] = 'red';
								$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB1']['extra_info'] = implode(', ', $patient_sapv_data[$v_patient_data['ipid']]['extra_info']);
							}
							else
							{
								$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB1']['status'] = 'gray';
							}
						}
						else
						{
							$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB1']['status'] = 'gray';
						}
						
						$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB1']['step_identification'] =  $discharged_patients[$v_patient_data['ipid']]['id'] ;						
						
						//AB2
						$shortcut_id = array_search('AB2', $current_path_shortcuts['shortcuts'][$patient_client_id]);
						if(!in_array($shortcut_id, $executed_shortcuts[$v_patient_data['ipid']]))
						{
							$executed_shortcuts[$v_patient_data['ipid']][] = $shortcut_id;

							if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '7')
							{
								if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
								{
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB2']['status'] = 'green';
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB2']['value'] = $saved_data[$v_patient_data['ipid']][$shortcut_id]['value'];
								}
								else if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '0' || !array_key_exists($shortcut_id, $saved_data))
								{
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB2']['status'] = 'red';
								}
							}
							else if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient_data['ipid']]['discharged_days'] < '7')
							{
								if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
								{
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB2']['status'] = 'green';
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB2']['value'] = $saved_data[$v_patient_data['ipid']][$shortcut_id]['value'];
								}
								else
								{
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB2']['status'] = 'blue';
								}
							}
						}
						else
						{
							$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB2']['status'] = 'gray';
						}
						
						$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB2']['step_identification'] =  $discharged_patients[$v_patient_data['ipid']]['id'] ;						
						
						//AB3
						$shortcut_id = array_search('AB3', $current_path_shortcuts['shortcuts'][$patient_client_id]);
						if(!in_array($shortcut_id, $executed_shortcuts[$v_patient_data['ipid']]))
						{
							$executed_shortcuts[$v_patient_data['ipid']][] = $shortcut_id;

							if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '10')
							{
								if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
								{
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB3']['status'] = 'green';
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB3']['value'] = $saved_data[$v_patient_data['ipid']][$shortcut_id]['value'];
								}
								else if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '0' || !array_key_exists($shortcut_id, $saved_data))
								{
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB3']['status'] = 'red';
								}
							}
							else if($discharged_patients[$v_patient_data['ipid']]['discharged_days'] >= '0' && $discharged_patients[$v_patient_data['ipid']]['discharged_days'] < '10')
							{
								if($saved_data[$v_patient_data['ipid']][$shortcut_id]['value'] == '1')
								{
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB3']['status'] = 'green';
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB3']['value'] = $saved_data[$v_patient_data['ipid']][$shortcut_id]['value'];
								}
								else
								{
									$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB3']['status'] = 'blue';
								}
							}
						}
						else
						{
							$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB3']['status'] = 'gray';
						}
						
						$master_data[$v_patient_data['ipid']]['discharged_billing_todo']['AB3']['step_identification'] =  $discharged_patients[$v_patient_data['ipid']]['id'] ;
					}
				}

				//discharged_billing_todo checks END	
				//print_r($master_data);exit;
			}

			foreach($sapv_highest_active['last'] as $k_ipid => $v_sapv_data)
			{
				if(in_array('folgeverordnung_todo', $all_clients_path_arr[$patient_details[$k_ipid]['EpidIpidMapping']['clientid']]))
				{

					$curent_sapv_start = date('Y-m-d', strtotime($v_sapv_data['verordnungam']));
					$curent_sapv_end = date('Y-m-d', strtotime($v_sapv_data['verordnungbis']));

					$today = date('Y-m-d', time());
					$next_sapv_data = $sapv_highest_active['next'][$v_sapv_data['ipid']];
					$next_sapv_start = date('Y-m-d', strtotime($next_sapv_data['verordnungam']));

					if(strtotime($curent_sapv_end) >= strtotime($today))
					{
						$sapv_ending_days = Pms_CommonData::get_days_number_between($curent_sapv_end, $today);
					}
					else
					{
						$sapv_ending_days = Pms_CommonData::get_days_number_between($today, $curent_sapv_end);
					}

					if(strtotime($curent_sapv_end) >= strtotime($today))
					{
						$sapv_ending_working_days = Pms_CommonData::get_working_days($today, $curent_sapv_end);
					}
					else
					{
						$sapv_ending_working_days = Pms_CommonData::get_working_days($curent_sapv_end, $today);
					}
					
					
					

					if(strtotime($curent_sapv_start) <= strtotime($today))
					{
						$sapv_started_days = Pms_CommonData::get_days_number_between($today, $curent_sapv_start);
					}

					if(strtotime($next_sapv_start) <= strtotime($today))
					{
						$next_sapv_started_days = Pms_CommonData::get_days_number_between($today, $next_sapv_start);
					}

					//			F1 - gets red 5 days before SAPV VErordnung ends (MANUAL CHECK)//  ISPC-854 - should get red  5 WORKING DAYS before SAPV ends - Added on: 12-05-2014
					$status_grey = false;

					$shortcut_id = array_search('F1', $current_path_shortcuts['shortcuts'][$patient_details[$k_ipid]['EpidIpidMapping']['clientid']]);

					if($saved_data[$v_sapv_data['ipid']][$shortcut_id]['value'] == '1'  && $saved_data[$v_sapv_data['ipid']][$shortcut_id]['step_identification'] == $v_sapv_data['id']  && $patient_details[$k_ipid]['isdischarged'] == '0')
					{
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['status'] = "green";
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['value'] = "1";
					}
// 					else if(($sapv_ending_days <= '5' && $sapv_ending_days >= 0 && $saved_data[$v_sapv_data['ipid']][$shortcut_id]['value'] != '1') || $patient_details[$k_ipid]['isdischarged'] == '')
					else if(($sapv_ending_working_days <= '5' && $sapv_ending_working_days >= 0 && $saved_data[$v_sapv_data['ipid']][$shortcut_id]['step_identification'] != $v_sapv_data['id'] ) || $patient_details[$k_ipid]['isdischarged'] == '')
					{

						if(!$status_grey && $patient_details[$k_ipid]['isdischarged'] == '0')
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}

						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['status'] = $stat;
						$status_grey = true;
					}
// 					else if($sapv_ending_days > '5')
					else if($sapv_ending_working_days > '5')
					{
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['status'] = "blue";
						$status_grey = true;
					}
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['sapv_ending_days'] = $sapv_ending_days;
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['sapv_ending_working_days'] = $sapv_ending_working_days;
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F1']['step_identification'] =  $v_sapv_data['id'];


					//F2 - gets red when at end of SAPV VErodnung no NEW SAPV Verordnung was added.
					//print_r("sapv_ending_days\n");
					//print_r($sapv_ending_days);
					//print_r("\nnext_sapv_data\n");
					//print_r($next_sapv_data);
					//print_r("\nsapv_ending_days\n");
					//print_r($curent_sapv_end." - ".$today);
					if(($sapv_ending_days > '3' && $sapv_ending_days >= '0') || $status_grey)
					{
						if(count($next_sapv_data) == '0')
						{
							$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['status'] = "gray";
							$status_grey = true;
						}
						else
						{
							$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['status'] = "green";
						}
					}
					else if($sapv_ending_days <= '3' && $sapv_ending_days >= '0')
					{
						if(count($next_sapv_data) == '0')
						{
							$stat = 'red';
							$status_grey = true;
						}
						else
						{
							$stat = 'green';
						}
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['status'] = $stat;
					}


					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['sapv_ending_days'] = $sapv_ending_days;
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['next_sapv'] = $next_sapv_data;
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F2']['step_identification'] =  $v_sapv_data['id'];

					//F3 - gets red if 2 days before actual SAPV Verordnung ends the new Verordnung has still status "ist bestellt"
					if(($sapv_ending_days <= '2' && $next_sapv_data['primary_set'] <= '2' && $sapv_ending_days >= '0' ) || $status_grey)
					{
						if(!$status_grey)
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}

						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F3']['status'] = $stat;
						$status_grey = true;
					}
					else if($sapv_ending_days > '2' && $next_sapv_data['primary_set'] <= '2')
					{
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F3']['status'] = 'blue';
						$status_grey = true;
					}
					else
					{
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F3']['status'] = "green";
					}
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F3']['step_identification'] =  $v_sapv_data['id'];

					//			F4 - gets red if on last day of actual SAPV Verordnung the new Verordnung is in status "noch nicht versendet")
					if(($sapv_ending_days <= '1' && $next_sapv_data['primary_set'] == '3' && $sapv_ending_days >= '0') || $status_grey)
					{
						if(!$status_grey)
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}

						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F4']['status'] = $stat;
						$status_grey = true;
					}
					else if($sapv_ending_days > '1' && $next_sapv_data['primary_set'] <= '3')
					{
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F4']['status'] = 'blue';
						$status_grey = true;
					}
					else
					{
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F4']['status'] = "green";
					}
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F4']['step_identification'] =  $v_sapv_data['id'];

					//F5 - if NEW 2nd Page is longer than 5 days in status "nicht versendet"
					if(($next_sapv_started_days >= 5 && $next_sapv_data['secondary_set'] <= '3') || $status_grey)
					{
						if(!$status_grey)
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}

						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F5']['status'] = $stat;
						$status_grey = true;
					}
					else if($next_sapv_data['secondary_set'] <= '3')
					{
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F5']['status'] = "red";
						$status_grey = true;
					}
					else
					{
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F5']['status'] = "green";
					}
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F5']['step_identification'] =  $v_sapv_data['id'];

					//F6 - if new SAPV Verordnung is NOT directly after 1st Verordnung (so a none SAPV Day would be there)
					if((strtotime($curent_sapv_end) <= strtotime($next_sapv_start)) || $status_grey)
					{
						$days_between_sapvs = Pms_CommonData::get_days_number_between($next_sapv_data['verordnungam'], $curent_sapv_end);
					}
					else if(strtotime($curent_sapv_end) > strtotime($next_sapv_start))
					{
						$days_between_sapvs = '0';
					}

					if(($days_between_sapvs != '0' && $days_between_sapvs != '1' && $days_between_sapvs > '0') || $status_grey)
					{
						if(!$status_grey)
						{
							$stat = 'red';
						}
						else
						{
							$stat = 'gray';
						}

						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F6']['status'] = $stat;
						$status_grey = true;
					}
					else
					{
						$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F6']['status'] = "green";
					}

					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F6']['days_between_sapvs'] = $days_between_sapvs;
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F6']['sapv_ending_days'] = $sapv_ending_days;
					$master_data[$v_sapv_data['ipid']]['folgeverordnung_todo']['F6']['step_identification'] =  $v_sapv_data['id'];
				}
			}

			foreach($ipids_arr as $k_ipid => $v_ipid)
			{
				//recheck if all patients have status value
				if(array_key_exists($v_ipid, $master_data))
				{
					if(count($master_data[$v_ipid]['admission_todo']) == '0' && empty($master_data[$v_ipid]['admission_todo']))
					{
						$master_data[$v_ipid]['admission_todo']['A1a']['status'] = 'gray';
						$master_data[$v_ipid]['admission_todo']['A1b']['status'] = 'gray';
						$master_data[$v_ipid]['admission_todo']['A1c']['status'] = 'gray';
						$master_data[$v_ipid]['admission_todo']['A2']['status'] = 'gray';
						$master_data[$v_ipid]['admission_todo']['A2a']['status'] = 'gray';
						$master_data[$v_ipid]['admission_todo']['A3']['status'] = 'gray';
					}

					if(count($master_data[$v_ipid]['original_request_todo']) == '0' && empty($master_data[$v_ipid]['original_request_todo']))
					{
						$master_data[$v_ipid]['original_request_todo']['O1']['status'] = 'gray';
						$master_data[$v_ipid]['original_request_todo']['O2']['status'] = 'gray';
					}

					if(count($master_data[$v_ipid]['folgeverordnung_todo']) == '0' && empty($master_data[$v_ipid]['folgeverordnung_todo']))
					{
						$master_data[$v_ipid]['folgeverordnung_todo']['F1']['status'] = 'gray';
						$master_data[$v_ipid]['folgeverordnung_todo']['F2']['status'] = 'gray';
						$master_data[$v_ipid]['folgeverordnung_todo']['F3']['status'] = 'gray';
						$master_data[$v_ipid]['folgeverordnung_todo']['F4']['status'] = 'gray';
						$master_data[$v_ipid]['folgeverordnung_todo']['F5']['status'] = 'gray';
						$master_data[$v_ipid]['folgeverordnung_todo']['F6']['status'] = 'gray';
					}

					if(count($master_data[$v_ipid]['discharged_todo']) == '0' && empty($master_data[$v_ipid]['discharged_todo']))
					{
						$master_data[$v_ipid]['discharged_todo']['E1']['status'] = 'gray';
						$master_data[$v_ipid]['discharged_todo']['E2']['status'] = 'gray';
						$master_data[$v_ipid]['discharged_todo']['E3']['status'] = 'gray';
					}

					if(count($master_data[$v_ipid]['discharged_billing_todo']) == '0' && empty($master_data[$v_ipid]['discharged_billing_todo']))
					{
						$master_data[$v_ipid]['discharged_billing_todo']['AB1']['status'] = 'gray';
						$master_data[$v_ipid]['discharged_billing_todo']['AB2']['status'] = 'gray';
						$master_data[$v_ipid]['discharged_billing_todo']['AB3']['status'] = 'gray';
					}
				}
			}
// 			print_r($master_data['8cdd3d50e48d89322d84916f3d393b60608bff14']); exit;
			return $master_data;
		}
	}

?>