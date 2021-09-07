<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoices', 'SYSDAT');

	class InternalInvoices extends BaseInternalInvoices {

		public function getInternalInvoice($invoice, $status = false, $allow_deleted = false)
		{
			$internal_invoice_items = new InternalInvoiceItems();

			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('InternalInvoices')
				->where("id='" . $invoice . "'");
			if($allow_deleted === false)
			{
				$invoices->andWhere('isdelete = 0');
			}

			if($status)
			{
				$invoices->andWhere('status = "' . $status . '"');
			}
			$invoices->limit('1');

			$invoices_res = $invoices->fetchArray();
			if($invoices_res)
			{
				//get all invoice items
				$invoice_items = $internal_invoice_items->getInvoicesItems($invoices_res[0]['id']);

				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
					$invoice_details = $v_invoice_res;

					if($invoice_items)
					{
						$invoice_details['items'] = $invoice_items[$v_invoice_res['id']];
					}
				}
				//items sorted by first date of period
				$invoice_details['items'] = $this->array_sort($invoice_details['items'], 'periods', 'SORT_ASC');

				return $invoice_details;
			}
			else
			{
				return false;
			}
		}

		public function get_invoice($invoiceid)
		{
			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('InternalInvoices')
				->andWhere('id = "' . $invoiceid . '"')
				->andWhere('isdelete = 0');
			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				return $invoices_res;
			}
			else
			{
				return $invoices;
			}
		}

		public function create_storno_invoice($invoiceid)
		{
			$find_invoice = Doctrine::getTable('InternalInvoices')->findOneById($invoiceid);
			$found_invoice = $find_invoice->toArray();

			$has_storno = self::has_storno($invoiceid);

			if($found_invoice && !$has_storno)
			{
				//ISPC-2532 create new number for storno invoice Carmen 13.02.2020*/
				$invoice_number = $this->get_next_invoice_number($found_invoice['client'], $found_invoice['user']);
				
				$inv = new InternalInvoices();
				$inv->client = $found_invoice['client'];
				$inv->user = $found_invoice['user'];
				$inv->invoice_start = $found_invoice['invoice_start'];
				$inv->invoice_end = $found_invoice['invoice_end'];
				$inv->start_active = $found_invoice['start_active'];
				$inv->end_active = $found_invoice['end_active'];
				$inv->ipid = $found_invoice['ipid'];
				
				if($found_invoice['client'] != 0)
				{
					$inv->prefix = $invoice_number['prefix'];
					$inv->invoice_number = $invoice_number['invoicenumber'];
				}
				else
				{
					$inv->prefix = $found_invoice['prefix'];
					$inv->invoice_number = $found_invoice['invoice_number'];
				}
				
				$inv->invoice_total = $found_invoice['invoice_total'];
				$inv->status = $found_invoice['status'];
				$inv->address = $found_invoice['address'];
				$inv->footer = $found_invoice['footer'];
				$inv->isdelete = '0';
				$inv->record_id = $invoiceid;
				$inv->storno = '1';
				//$inv->completed_date = $found_invoice['completed_date'];
				$inv->completed_date = date('Y-m-d H:i:s', time());        //ISPC-2532 Lore 11.11.2020
				$inv->save();
				
				// TODO-3012 Ancuta 20-23.03.2020 (start)
				// update Le actions, remove billed  info - so they can be invoiced again
				$this->storno_patient_xbdt_actions($invoiceid);
			}
		}

		public function del_storno_invoice($invoiceid)
		{
			$del_storno_invoice = Doctrine::getTable('InternalInvoices')->findOneById($invoiceid);
			$del_storno_invoice->isdelete = '1';
			$del_storno_invoice->save();
			
			// TODO-3012 Ancuta 20-23.03.2020 (start)
			// update Le actions, remove billed  info - so they can be invoiced again
			$this->storno_patient_xbdt_actions($invoiceid);
		}

		public function get_user_highest_invoice_number($clientid, $user, $prefix = false, $all = false)
		{
			$invoice_number = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoices');
			$invoice_number->where("client='" . $clientid . "'");

			if($prefix != "TEMP_")
			{
				$invoice_number->andWhere("user='" . $user . "'");
			}
			$invoice_number->andWhere('isdelete = 0')
				->orderBy('invoice_number DESC')
				->limit('1');
			if($prefix)
			{
//				$invoice_number->andWhere('prefix = "' . $prefix . '"');
				$invoice_number->andWhere('prefix = ? ', $prefix);
			}
			else if($all === false)
			{
				$invoice_number->andWhere('prefix = ""');
			}

			$invoice_number_data = $invoice_number->fetchArray();

			if($invoice_number_data)
			{
				return $invoice_number_data[0];
			}
			else
			{
				return false;
			}
		}

		public function get_next_invoice_number($clientid, $user, $temp = false, $invoice_settings = false)
		{
			$internal_invoice_settings = new InternalInvoiceSettings();
			$user_detauls = new User();
			$user_data = $user_detauls->getUsersDetails($user);

			$user_initials[$user] = mb_substr($user_data[$user]['first_name'], 0, 1, "UTF-8") . "" . mb_substr($user_data[$user]['last_name'], 0, 1, "UTF-8");

			if($invoice_settings)
			{
				$internal_invoice_settings_arr = $invoice_settings;
			}
			else
			{
				$internal_invoice_settings_arr = $internal_invoice_settings->getUserInternalInvoiceSettings($user, $clientid);
			}
//			print_r("internal_invoice_settings_arr \n");
//			print_r($internal_invoice_settings_arr);exit;


			$internal_invoice_number = $this->get_user_highest_invoice_number($clientid, $user, $internal_invoice_settings_arr[$user]['invoice_prefix']);
//			print_r("internal_invoice_number \n");
//			print_r($internal_invoice_number);exit;

			if($internal_invoice_number)
			{
				if($internal_invoice_number['invoice_number'] >= $internal_invoice_settings_arr[$user]['invoice_start'] && $internal_invoice_number['prefix'] == $internal_invoice_settings_arr[$user]['invoice_prefix'])
				{
					$prefix = $internal_invoice_settings_arr[$user]['invoice_prefix'];
					$i_number = $internal_invoice_number['invoice_number'];
					$i_number++;
				}
				else
				{
					$prefix = $internal_invoice_settings_arr[$user]['invoice_prefix'];
					$i_number = $internal_invoice_settings_arr[$user]['invoice_start'];
					if($internal_invoice_settings_arr[$user]['invoice_start'] == '0')
					{
						$i_number++;
					}
				}
			}
			else
			{
				if(strlen($internal_invoice_settings_arr[$user]['invoice_start']) > 0)
				{
					$prefix = $internal_invoice_settings_arr[$user]['invoice_prefix'];
					$i_number = $internal_invoice_settings_arr[$user]['invoice_start'];
					if($internal_invoice_settings_arr[$user]['invoice_start'] == '0')
					{
						$i_number++;
					}
				}
				else
				{
					$prefix = $user_initials[$user];
					$i_number = '1000';
				}
			}
			$invoicenumber = $i_number;


			if($temp === false)
			{
				$invoice_nr_arr['prefix'] = $prefix;
				$invoice_nr_arr['invoicenumber'] = $invoicenumber;
			}
			else
			{
				$invoice_nr_arr = $this->generate_user_temp_invoice_number($clientid, $user);
			}

			return $invoice_nr_arr;
		}

		private function generate_user_temp_invoice_number($clientid, $user)
		{
			$temp_prefix = 'TEMP_';
			$high_inv_nr = $this->get_user_highest_invoice_number($clientid, $user, $temp_prefix);

			if($high_inv_nr)
			{
				$high_inv_nr['invoice_number'] ++;
				$inv_nr = $high_inv_nr['invoice_number'];
			}
			else
			{
				$inv_nr = '1';
			}

			$invoice_nr_arr['prefix'] = $temp_prefix;
			$invoice_nr_arr['invoicenumber'] = $inv_nr;

			return $invoice_nr_arr;
		}

		public function get_all_client_internal_invoices($ipids, $clientid, $filter_data, $offset = false, $pagelimit = false, $order_by = false, $direction = 'ASC', $remove_drafts = false)
		{
			$Tr = new Zend_View_Helper_Translate();

			$items_filter_fields = array(
				'internal_invoices' => array('start' => 'start_active', 'end' => 'end_active')
			);

			$remove_drafts_sql = "";
			if($remove_drafts)
			{
				$remove_drafts_sql .=' AND status != "1" ';
			}

			$storno_invoices_q = Doctrine_Query::create()
			->select("*")
			->from('InternalInvoices')
			->where('client = ?',$clientid)
			->andWhere('storno = 1')
			->andWhere('isdelete = 0');
			$storno_invoices_array = $storno_invoices_q->fetchArray();
		
			$storno_ids = array();
			$storno_ids_str="";
			foreach($storno_invoices_array as $k => $st)
			{
			    $storno_ids[] = $st['record_id'];
			    $storno_ids_str .= '"' . $st['record_id'] . '",';
			}
 
			if( strlen($storno_ids_str) > 0  && $filter_data['storno'][0] == "0")
			{
			    $storno_ids_str = substr($storno_ids_str, 0, -1);
			    $storno_ids_str_sql = " AND id NOT IN (" . $storno_ids_str . ")";
			} else{
			    $storno_ids_str_sql = "";
			}
			
			$sql .= 'SELECT	id,	client,	user,ipid,prefix,CONCAT(prefix, invoice_number) as invoice_number,invoice_number as invoice_nr,	invoice_total,	isdelete,	record_id,	storno,	invoice_start as invoice_date,	create_date,id as t_type,completed_date	FROM `internal_invoices`';
			if($ipids)
			{
				$sql_ipids = 'AND ipid IN("' . implode('", "', $ipids) . '") ';
			}
			else
			{
				$sql_ipids = '';
			}


			$sql .= ' WHERE isdelete = "0" ' . $remove_drafts_sql . '  '.$storno_ids_str_sql.'   AND client ="' . $clientid . '" ' . $sql_ipids;
			$sql .= ' AND status != "4" ';
//			print_r($filter_data);
//			exit;
			if(count($filter_data))
			{

				foreach($filter_data as $filter_for => $filter_values)
				{

					if($filter_for == 'completed_date' || $filter_for == 'create_date' || $filter_for == 'item_date')
					{
						if($filter_for == 'completed_date' || $filter_for == 'create_date')
						{
							$sql .= ' AND DATE(' . $filter_for . ') ';
							$sql .= ' BETWEEN DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['start_date'])) . '") AND DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['end_date'])) . '") ';
						}

						if($filter_for == 'item_date' && strlen($filter_values['item_start_date']) > '0' && strlen($filter_values['item_end_date']) > '0')
						{

							$sql .= ' AND DATE(end_active) >= DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['item_start_date'])) . '") AND DATE(start_active) <= DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['item_end_date'])) . '") ';
						}
					}
					elseif($filter_for == 'invoice_number')
					{
						$sql.= ' AND ( LOWER(CONCAT(`prefix`, CAST(  `invoice_number` AS CHAR ))) LIKE "%' . addslashes(strtolower($filter_values[0])) . '%" )';
					}
					else
					{
						$sql .= ' AND ' . $filter_for . ' ';
						if(is_array($filter_values) && count($filter_values) > '1')
						{
							//do in
							$sql .= ' IN("' . implode(', ', $filter_values) . '") ';
						}
						else
						{
							//do like
							$sql .= ' LIKE "%' . $filter_values[0] . '%" ';
						}
					}
				}
			}

			if($order_by && $direction)
			{
				switch($order_by)
				{
					case 'inv_nr':
						$order_by = 'invoice_number ' . $direction;
						break;

					case 'inv_date':
						$order_by = 'completed_date ' . $direction;
						break;

					case 'inv_stype':
						$order_by = 'storno ' . $direction;
						break;

					case 'inv_type':
						$order_by = 'invoice_type_translated ' . $direction;
						break;

					case 'inv_amount':
						$order_by = 'CAST(invoice_total AS DECIMAL(10,2)) ' . $direction;
						break;
					default:
						$order_by = 'completed_date DESC';
						break;
				}
			}
			else
			{
				$order_by = 'completed_date DESC';
			}
			$sql .= ' ORDER BY ' . $order_by . ' ';

//			print_r($filter_data);
// 			print_r($sql);
// 			exit;
			$resultset = Doctrine_Manager::getInstance()
				->getConnection('SYSDAT')
				->getDbh()
				->query($sql)
				->fetchAll(PDO::FETCH_ASSOC);
			return $resultset;
		}

		public function get_internal_invoices_users($clientid)
		{
			$invoice = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoices')
				->where("client='" . $clientid . "'")
				->andWhere('isdelete = 0');
			$invoice_data = $invoice->fetchArray();

			if($invoice_data)
			{
				foreach($invoice_data as $k => $details)
				{
					$invoiced_users[] = $details['user'];
				}

				return $invoiced_users;
			}
			else
			{
				return false;
			}
		}

		public function get_completed_previous_invoices($clientid, $ipid, $users, $period)
		{
		    
		    // get storno ids 
		    // ISPC-2233 p3
		    // Added by Ancuta 30.08.2018
		    $storno_invoices_q = Doctrine_Query::create()
		    ->select("id,record_id")
		    ->from('InternalInvoices')
		    ->where('client = ?', $clientid)
		    ->andWhere('storno = 1')
		    ->andWhere('isdelete = 0');
		    $storno_invoices_array = $storno_invoices_q->fetchArray();
		    	
		    foreach($storno_invoices_array as $k=>$st)
		    {
		        $storno_ids[] = $st['record_id'];
		    }
		    
		    
			//2 = completed 3, 5 = paid and partialy paid
			$excluded_statuses = array('1', '4');

			$invoices = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoices')
				->where("client= ?", $clientid)
				->andWhere('ipid =? ', $ipid)
				->andWhere('isdelete = 0')
				->andWhere('storno = 0');
    			//   ISPC-2233 p3 -Added by Ancuta 30.08.2018
    			if(!empty($storno_ids)){
    				$invoices->andWhereNotIn('id',$storno_ids);
    			}
    			//--
				$invoices->andWhere('invoice_start <= "' . date('Y-m-d H:i:s', strtotime($period['start'])) . '" OR (invoice_end <= "' . date('Y-m-d H:i:s', strtotime($period['end'])) . '" AND invoice_end >= "' . date('Y-m-d H:i:s', strtotime($period['start'])) . '" )')
				->andWhereIn('user', $users)
				->andWhereNotIn('status', $excluded_statuses);
			$invoices_res = $invoices->fetchArray();
//			print_r($invoices->getSqlQuery());

			if($invoices_res)
			{
				foreach($invoices_res as $k_res => $v_res)
				{
					$completed_items_data['invoices'][] = $v_res;
					$invoices_ids[] = $v_res['id'];
					$invoice2user[$v_res['id']] = $v_res['user'];
				}

				$items_invoices = InternalInvoiceItems::getInvoicesItems($invoices_ids);

				if($items_invoices)
				{
					foreach($items_invoices as $k_item => $v_items)
					{
						foreach($v_items as $k_v_item => $v_v_item)
						{
							$completed_items_data['items'][$v_v_item['id']] = $v_v_item;
							$completed_items_data['items'][$v_v_item['id']]['user_invoice'] = $invoice2user[$v_v_item['invoice']];
						}
					}

					//print_r($completed_items_data);
					//exit;

					return $completed_items_data;
				}
				else
				{
					//invoices without items
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function get_completed_previous_invoices_multiple($clientid, $ipids, $users, $period)
		{
			//2 = completed 3, 5 = paid and partialy paid
			$excluded_statuses = array('1', '4');

			$ipids_arr[] = '999999999';
			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else if($ipids !== false)
			{
				$ipids_arr = array($ipids);
			}

			foreach($period['start'] as $k_period => $v_period)
			{
//				$sql_period[] = ' DATE(invoice_start) BETWEEN DATE("'.date('Y-m-d H:i:s', strtotime($v_period)).'") AND DATE("'.date('Y-m-d H:i:s', strtotime($period['end'][$k_period])).'") OR DATE(invoice_end) BETWEEN DATE("'.date('Y-m-d H:i:s', strtotime($v_period)).'") AND DATE("'.date('Y-m-d H:i:s', strtotime($period['end'][$k_period])).'") ';
				$sql_period[] = ' (DATE(invoice_start) BETWEEN DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '") AND DATE("' . date('Y-m-d H:i:s', strtotime($period['end'][$k_period])) . '") OR DATE(invoice_end) BETWEEN DATE("' . date('Y-m-d H:i:s', strtotime($period['end'][$k_period])) . '") AND DATE("' . date('Y-m-d H:i:s', strtotime($period['end'][$k_period])) . '")) ';
				$sql_period[] = ' (DATE(invoice_start) <= DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '") AND DATE(invoice_end) >= DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '")) ';
				$sql_period[] = ' (DATE(invoice_start) <= DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '") AND DATE(invoice_end) <= DATE("' . date('Y-m-d H:i:s', strtotime($period['end'][$k_period])) . '") AND DATE(invoice_end) > DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '")) ';
//				$sql_period[] = ' DATE(invoice_start) <= DATE("'.date('Y-m-d H:i:s', strtotime($v_period)).'")
//								OR (DATE(invoice_start) >= DATE("'.date('Y-m-d H:i:s', strtotime($v_period)).'") AND DATE(invoice_start) <= DATE("'.date('Y-m-d H:i:s', strtotime($period['end'][$k_period])).'"))
//								OR (DATE(invoice_end) <= DATE("'.date('Y-m-d H:i:s', strtotime($period['end'][$k_period])).'") AND DATE(invoice_end) >= DATE("'.date('Y-m-d H:i:s', strtotime($v_period)).'"))
//								OR (DATE(invoice_end) <= DATE("'.date('Y-m-d H:i:s', strtotime($period['end'][$k_period])).'") AND DATE(invoice_end) >= DATE("'.date('Y-m-d H:i:s', strtotime($v_period)).'") ) ';
			}
//			print_r($ipids_arr);

			$invoices = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoices')
				->where("client='" . $clientid . "'")
				->andWhereIn('ipid', $ipids_arr)
				->andWhere('isdelete = 0')
				->andWhere(implode(' OR ', $sql_period))
				->andWhereIn('user', $users)
				->andWhereNotIn('status', $excluded_statuses)
				->orderBy('invoice_start ASC');
			$invoices_res = $invoices->fetchArray();
//			print_r($invoices->getSqlQuery());
//			print_r($invoices_res);

			if($invoices_res)
			{
				foreach($invoices_res as $k_res => $v_res)
				{
					$invoices_ids[] = $v_res['id'];
					$invoices_ids2ipid[$v_res['id']] = $v_res['ipid'];
					$invoices_ids2user[$v_res['id']] = $v_res['user'];
				}

				$items_invoices = InternalInvoiceItems::getInvoicesItems($invoices_ids);

				if($items_invoices)
				{
					foreach($items_invoices as $k_item => $v_items)
					{
						foreach($v_items as $k_v_item => $v_v_item)
						{

							$v_v_item['user'] = $invoices_ids2user[$v_v_item['invoice']];
							$v_v_item['ipid'] = $invoices_ids2ipid[$v_v_item['invoice']];
							$completed_items_data[] = $v_v_item;
						}
					}

					//print_r($completed_items_data);
					//exit;
					return $completed_items_data;
				}
				else
				{
					//invoices without items
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function sp_rules_multiple($specific_products, $period, $ipids, $clientid, $users_ids_associated, $national_holidays, $previous_items, $active_patient_details)
		{


			//get patient contact forms, deleted from verlauf are excluded
			$pat_course = new PatientCourse();
			$excluded_cf = $pat_course->get_deleted_contactforms($ipids, false);


			//get client user groups
			$grps = new UserGroup();
			$c_groups = $grps->getClientGroups($clientid);
			$c_groups_ids[] = '99999999999';
			foreach($c_groups as $k_group => $v_group)
			{
				$c_groups_ids[] = $v_group['id'];
			}

			//get groups users
			$groups_users = $grps->get_groups_users($c_groups_ids, $clientid);


			//get contact forms
			$cf = new ContactForms();
//			$patient_working_cf = $cf->get_internal_invoice_contactforms($ipid, $excluded_cf[$ipid], $period);
			$patient_working_cf = $cf->get_internal_invoice_contactforms_multiple($ipids, $excluded_cf, $period);

//			foreach($specific_products as $ksp =>$vsp)
//			{
//				$specific_shortcuts2prodid[$vsp['code']] = $vsp['id'];
//			}
//			print_r("previous_items\n");
//			print_r($previous_items);

			foreach($previous_items as $k_pitem => $v_pitem)
			{
				foreach($v_pitem['periods']['from_date'] as $k_date => $v_date)
				{
					//$arr[date][shortcutid] = qty;
//					$previous_items_arr[strtotime($v_date)][$v_pitem['product']] = $v_pitem['qty'];
//					
					//add user id based on invoiceid
					$previous_items_arr[$v_pitem['ipid']][$v_pitem['user']][strtotime($v_date)][$v_pitem['product']] = $v_pitem['qty'];
				}
			}

			$showtime_module = new Modules();
			$showtime = $showtime_module->checkModulePrivileges("78", $clientid);

			$condition_one = array();
			$condition_two = array();
			foreach($specific_products as $k_product => $v_product_details)
			{
				//get client showtime module (no module means to act as not selected)
				if(!$showtime)
				{
					//overwrite the saved value if any
					$v_product_details['showtime'] = $showtime_module_value;
				}

				foreach($patient_working_cf as $k_cf => $v_cf)
				{
					if($v_product_details['holiday'] == '1')
					{
						$check_holiday = true;
					}
					else
					{
						$check_holiday = false;
					}

					$time_diff = (strtotime($v_cf['end_date']) - strtotime($v_cf['start_date']));
					$v_cf['duration'] = ($time_diff / 60);

//					holiday debug
					if($v_cf['form_type'] == $v_product_details['contactform_type'] && (($check_holiday && (in_array(date('Y-m-d', strtotime($v_cf['start_date'])), $national_holidays) || date('w', strtotime($v_cf['start_date'])) == '0' || date('w', strtotime($v_cf['start_date'])) == '6')) || (!$check_holiday && !in_array(date('Y-m-d', strtotime($v_cf['start_date'])), $national_holidays) && date('w', strtotime($v_cf['start_date'])) != '0' && date('w', strtotime($v_cf['start_date'])) != '6')))
					{
						//check range duration
						if($v_product_details['range_type'] == 'min')
						{
							if($v_cf['duration'] >= $v_product_details['range_start'] && $v_cf['duration'] <= $v_product_details['range_end'])
							{
								$condition_one[$v_cf['ipid']][$v_cf['id']][$v_product_details['code']] = '1';
							}
						}

						//check range distance
						if($v_product_details['range_type'] == 'km')
						{
							$clean_km_string = str_replace(' km', '', trim(rtrim($v_cf['fahrtstreke_km'])));

							if($clean_km_string >= $v_product_details['km_range_start'] && $clean_km_string <= $v_product_details['km_range_end'])
							{
								$condition_one[$v_cf['ipid']][$v_cf['id']][$v_product_details['code']] = '1';
							}
						}

						//check which time we use for reference
						$v_product_details_ts['time_start'] = strtotime('1970-01-01 ' . $v_product_details['time_start'] . ':00');
						$v_product_details_ts['time_end'] = strtotime('1970-01-01 ' . $v_product_details['time_end'] . ':00');
						$constant_midnight = strtotime('1970-01-01 00:00:00');

						if($v_product_details['calculation_trigger'] == 'time_start')
						{
							//use contact form start_date
							$start_cf = strtotime(date('1970-01-01 H:i:s', strtotime($v_cf['start_date'])));
						}
						else if($v_product_details['calculation_trigger'] == 'time_end')
						{
							//use contact form end_date
							$start_cf = strtotime(date('1970-01-01 H:i:s', strtotime($v_cf['end_date'])));
						}

						//hours condition
						if($v_product_details_ts['time_start'] < $v_product_details_ts['time_end']) //08-20 normal interval
						{
							if(($start_cf >= $v_product_details_ts['time_start'] && $start_cf < $v_product_details_ts['time_end']) && $condition_one[$v_cf['ipid']][$v_cf['id']][$v_product_details['code']] == '1')
							{
								$condition_two[$v_cf['ipid']][$v_cf['id']][$v_product_details['code']] = '1';
							}
						}
						else if($v_product_details_ts['time_start'] > $v_product_details_ts['time_end'] || ($start_cf >= $constant_midnight && $start_cf < $v_product_details_ts['time_end'])
						) //20-08 interval (overnight)
						{
							if((($start_cf >= $v_product_details_ts['time_end'] && $start_cf >= $v_product_details_ts['time_start']) || ($start_cf < $v_product_details_ts['time_end']) ) && $condition_one[$v_cf['id']][$v_product_details['code']] == '1')
							{

								$condition_two[$v_cf['ipid']][$v_cf['id']][$v_product_details['code']] = '1';
							}
						}

						$reformated_date = date('d.m.Y', strtotime($v_cf['date']));

						//creator and aditional users
						if($condition_two[$v_cf['ipid']][$v_cf['id']][$v_product_details['code']] == '1')
						{
							if($v_product_details['asigned_users'] == '1')
							{

								//create array with additional users which belong to the product group
								asort($v_cf['aditional_users']);
								asort($groups_users[$v_product_details['usergroup']]);

								$allowed_users[$v_cf['id']] = array_intersect($groups_users[$v_product_details['usergroup']], $v_cf['aditional_users']);

								//add create user if contactform has no aditional users
								if(empty($allowed_users[$v_cf['id']]))
								{
									$allowed_users[$v_cf['id']][] = $v_cf['create_user'];
								}

								//create master item for all aditional users which belong to product group
								if(!empty($allowed_users[$v_cf['id']]))
								{
									foreach($allowed_users[$v_cf['id']] as $k_group_user => $v_group_user)
									{
										$item_data = array();
										$formated_date = date('Y-m-d', strtotime($v_cf['date']));

										if(in_array($reformated_date, $active_patient_details[$v_cf['ipid']]['active_days']))
										{
											if(!empty($users_ids_associated[$v_group_user]))
											{
												//create master item for assigned user of allowed user
												$master_items[$v_cf['ipid']][$users_ids_associated[$v_group_user]][strtotime($formated_date)][$v_product_details['id']]['normal'] += 1;
												$master_items[$v_cf['ipid']][$users_ids_associated[$v_group_user]][strtotime($formated_date)][$v_product_details['id']]['p_id'] = $v_product_details['id'];
												$master_items[$v_cf['ipid']][$users_ids_associated[$v_group_user]][strtotime($formated_date)][$v_product_details['id']]['normal_days'][] = $formated_date;
												//											$master_items[$v_cf['ipid']][$users_ids_associated[$v_group_user]][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$formated_date] = $v_cf['id'];
												if($v_product_details['showtime'] == '1')
												{
													$master_items[$v_cf['ipid']][$users_ids_associated[$v_group_user]][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$v_cf['id']] = $v_cf;
												}
											}
											else
											{
												//create master item for allowed user
												$master_items[$v_cf['ipid']][$v_group_user][strtotime($formated_date)][$v_product_details['id']]['normal'] += 1;
												$master_items[$v_cf['ipid']][$v_group_user][strtotime($formated_date)][$v_product_details['id']]['p_id'] = $v_product_details['id'];
												$master_items[$v_cf['ipid']][$v_group_user][strtotime($formated_date)][$v_product_details['id']]['normal_days'][] = $formated_date;
												//											$master_items[$v_cf['ipid']][$v_group_user][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$formated_date] = $v_cf['id'];
												if($v_product_details['showtime'] == '1')
												{
													$master_items[$v_cf['ipid']][$v_group_user][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$v_cf['id']] = $v_cf;
												}
											}
										}
									}
								}
							}
							else
							{
								//check if creator is in same group as product requirement
								if(in_array($v_cf['create_user'], $groups_users[$v_product_details['usergroup']]) && in_array($reformated_date, $active_patient_details[$v_cf['ipid']]['active_days']))
								{
									$item_data = array();
									$formated_date = date('Y-m-d', strtotime($v_cf['date']));

									if(!empty($users_ids_associated[$v_cf['create_user']]))
									{
										//create master item for assigned creator user
										$master_items[$v_cf['ipid']][$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['normal'] += 1;
										$master_items[$v_cf['ipid']][$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['p_id'] = $v_product_details['id'];
										$master_items[$v_cf['ipid']][$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['normal_days'][] = $formated_date;
//										$master_items[$v_cf['ipid']][$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$formated_date][] = $v_cf['id'];
										if($v_product_details['showtime'] == '1')
										{
											$master_items[$v_cf['ipid']][$users_ids_associated[$v_cf['create_user']]][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$v_cf['id']] = $v_cf;
										}
									}
									else
									{
										//create master item for creator user
										$master_items[$v_cf['ipid']][$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['normal'] += 1;
										$master_items[$v_cf['ipid']][$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['p_id'] = $v_product_details['id'];
										$master_items[$v_cf['ipid']][$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['normal_days'][] = $formated_date;
//										$master_items[$v_cf['ipid']][$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$formated_date][] = $v_cf['id'];
										if($v_product_details['showtime'] == '1')
										{
											$master_items[$v_cf['ipid']][$v_cf['create_user']][strtotime($formated_date)][$v_product_details['id']]['contact_forms'][$v_cf['id']] = $v_cf;
										}
									}
								}
							}
						}
					}
				}
			}

			if($_REQUEST['dbg']=='5')
			{
				print_r("master_items1\n");
				print_r($master_items);
				print_r("previous_items_arr\n");
				print_r($previous_items_arr);
				exit;
			}
			//remove previous items qty
			foreach($master_items as $k_ipid => $v_patient_data)
			{
				foreach($v_patient_data as $k_userid => $v_item_data)
				{
					foreach($v_item_data as $k_date => $v_item_details)
					{
						foreach($v_item_details as $k_shortcut_id => $v_values)
						{
							if(($v_values['normal'] - $previous_items_arr[$k_ipid][$k_userid][$k_date][$k_shortcut_id]) > '0')
							{
								//remove qty
								$master_items[$k_ipid][$k_userid][$k_date][$k_shortcut_id]['normal'] = ($v_values['normal'] - $previous_items_arr[$k_ipid][$k_userid][$k_date][$k_shortcut_id]);
							}
							else
							{
								//remove shortcut if qty is 0 or negative
								unset($master_items[$k_ipid][$k_userid][$k_date][$k_shortcut_id]);
							}
						}
					}
				}
			}

			foreach($master_items as $k_ipid => $v_patient_data)
			{
				foreach($v_patient_data as $k_userid => $v_item_data)
				{
					foreach($v_item_data as $k_date => $v_item_details)
					{
//						print_r($k_ipid.' - '.$k_date."\n");
//						print_r($v_item_details);
//
//						var_dump(empty($master_items[$k_ipid][$v_patient_data][$k_date]));
//						print_r("\n");

						if(empty($master_items[$k_ipid][$v_patient_data][$k_date]))
						{
							unset($master_items[$k_ipid][$v_patient_data][$k_date]);
						}
					}

					if(empty($master_items[$k_ipid][$v_patient_data]))
					{
						unset($master_items[$k_ipid][$v_patient_data]);
					}
				}
			}

//			print_r("master_items2\n");
//			print_r($master_items);
//			exit;
			return $master_items;
		}

		public function dp_rules_multiple($dayproducts, $period, $ipids, $clientid, $users_ids_associated, $national_holidays, $previous_items, $patient_active_details)
		{
			$grps = new UserGroup();
			$pm = new PatientMaster();
			
			$epids = Pms_CommonData::get_multiple_epids($ipids);
			if (!$epids)
			{	
				$epids[]= '9999999999';
			}	
			//get client user groups
			$c_groups = $grps->getClientGroups($clientid);
			$c_groups_ids[] = '99999999999';
			foreach($c_groups as $k_group => $v_group)
			{
				$c_groups_ids[] = $v_group['id'];
			}

			//get groups users
			$groups_users = $grps->get_groups_users($c_groups_ids, $clientid);
//			print_r($groups_users);
			//get assigned users (from groups_users)
			$assigned_usr = Doctrine_Query::create()
				->select('*')
				->from('PatientQpaMapping')
//				->where('epid="' . $epid . '"');
				->whereIn('epid', array_values($epids));
			$assigned_users = $assigned_usr->fetchArray();
//			print_r($assigned_users);
//			print_r($epids);

			foreach($assigned_users as $k_usr => $v_usr)
			{
				$assignedusers[] = $v_usr['userid'];
				$ipid2users[array_search($v_usr['epid'], $epids)][] = $v_usr['userid'];
				if(!empty($users_ids_associated[$v_usr['userid']]))
				{
					$assignedusers[] = $users_ids_associated[$v_usr['userid']];
				}

				$assignedusers = array_values(array_unique($assignedusers));
			}
//			print_r($ipid2users);
			//day products id mapped
			foreach($dayproducts as $k_d_pr => $v_d_pr)
			{
				$day_products[$v_d_pr['id']] = $v_d_pr;
				if($v_d_pr['grouped'] == '1')
				{
					$grouped_products[] = $v_d_pr['id'];
				}
			}

			//sapvs
			$patient_sapv_details = InternalInvoices::get_patient_all_sapv($ipids);

//			print_r("dayproducts\n");
//			print_r($dayproducts);
//			foreach($dayproducts as $k_prod => $v_prod)
//			{
//				$day_products_ids[$v_prod['normal_price_name']] = $v_prod['id'];
//				$day_products_ids[$v_prod['hosp_adm_price_name']] = $v_prod['id'];
//				$day_products_ids[$v_prod['hosp_price_name']] = $v_prod['id'];
//				$day_products_ids[$v_prod['hosp_dis_price_name']] = $v_prod['id'];
//				$day_products_ids[$v_prod['hospiz_adm_price_name']] = $v_prod['id'];
//				$day_products_ids[$v_prod['hospiz_price_name']] = $v_prod['id'];
//				$day_products_ids[$v_prod['hospiz_dis_price_name']] = $v_prod['id'];
//				$day_products_ids[$v_prod['standby_price_name']] = $v_prod['id'];
//				$day_products_ids[$v_prod['hosp_dis_hospiz_adm_price_name']] = $v_prod['id'];
//				$day_products_ids[$v_prod['hospiz_dis_hosp_adm_price_name']] = $v_prod['id'];
//			}
//			print_r("day_products_names2ids\n");
//			print_r($day_products_ids);
//			print_r("PRRRRR\n");
//			print_r($previous_items);
//			exit;
			foreach($previous_items as $k_item => $v_item_data)
			{

				$day_product_id = $v_item_data['product'];
				if(empty($previous_items_arr[$v_item_data['ipid']][$day_product_id][$v_item_data['sub_item'] . '_days']))
				{
					$previous_items_arr[$v_item_data['ipid']][$day_product_id][$v_item_data['sub_item'] . '_days'] = array();
				}

				if(empty($previous_items_arr[$v_item_data['ipid']]['grouped_days']))
				{
					$previous_items_arr[$v_item_data['ipid']]['grouped_days'] = array();
				}

				foreach($v_item_data['periods']['from_date'] as $k_period => $v_period_start)
				{
					if($v_item_data['type'] == 'dp')
					{
						if(date('Y-m-d', strtotime($v_item_data['periods']['till_date'][$k_period])) != '1970-01-01')
						{
							$previous_items_arr[$v_item_data['ipid']][$day_product_id][$v_item_data['sub_item'] . '_days'] = array_merge($previous_items_arr[$v_item_data['ipid']][$day_product_id][$v_item_data['sub_item'] . '_days'], $pm->getDaysInBetween($v_period_start, $v_item_data['periods']['till_date'][$k_period]));
						}
						else
						{
							$previous_items_arr[$v_item_data['ipid']][$day_product_id][$v_item_data['sub_item'] . '_days'][] = date('Y-m-d', strtotime($v_period_start));
						}

						$previous_items_arr[$v_item_data['ipid']][$day_product_id][$v_item_data['sub_item'] . '_days'] = array_values(array_unique($previous_items_arr[$v_item_data['ipid']][$day_product_id][$v_item_data['sub_item'] . '_days']));
					}
					else if($v_item_data['type'] == 'gr')
					{
						if(date('Y-m-d', strtotime($v_item_data['periods']['till_date'][$k_period])) != '1970-01-01')
						{
							$previous_items_arr[$v_item_data['ipid']]['grouped_days'] = array_merge($previous_items_arr[$v_item_data['ipid']]['grouped_days'], $pm->getDaysInBetween($v_period_start, $v_item_data['periods']['till_date'][$k_period]));
						}
						else
						{
							$previous_items_arr[$v_item_data['ipid']]['grouped_days'][] = date('Y-m-d', strtotime($v_period_start));
						}

						$previous_items_arr[$v_item_data['ipid']]['grouped_days'] = array_values(array_unique($previous_items_arr[$v_item_data['ipid']]['grouped_days']));
					}
				}
			}

//			print_r("previous_items\n");
//			print_r($previous_items);
//			print_r("previous_items_arr\n");
//			print_r($previous_items_arr);
//			exit;


			foreach($period['start'] as $k_per => $v_per)
			{
				if(empty($period_days))
				{
					$period_days = array();
				}
				$period_days = array_merge($period_days, $pm->getDaysInBetween($v_per, $period['end'][$k_per]));
			}

			//get patient treatment active days
			$patient_treatment = $pm->getTreatedDaysRealMultiple($ipids);

			foreach($patient_treatment as $k_ipid => $v_treatment_data)
			{
				if(count($v_treatment_data['admissionDates']) > 0)
				{
					foreach($v_treatment_data['admissionDates'] as $k_admission => $v_admission_data)
					{
						$start = date('Y-m-d', strtotime($v_admission_data['date']));
						$v_treatment_data['admission_days'][] = $v_admission_data['date'];

						if(!empty($v_treatment_data['dischargeDates'][$k_admission]['date']))
						{
							$end = date('Y-m-d', strtotime($v_treatment_data['dischargeDates'][$k_admission]['date']));
							$v_treatment_data['discharge_days'][] = $v_treatment_data['dischargeDates'][$k_admission]['date'];
						}
						else
						{
							$end = date('Y-m-d', strtotime($v_treatment_data['discharge_date']));
						}

						if(empty($v_treatment_data['patient_treatment_days']))
						{
							$v_treatment_data['patient_treatment_days'] = array();
						}

						$v_treatment_data['patient_treatment_days'] = array_merge($v_treatment_data['patient_treatment_days'], $pm->getDaysInBetween($start, $end));
					}
				}
				else
				{
					$start = date('Y-m-d', strtotime($v_treatment_data['admission_date']));
					$end = date('Y-m-d', strtotime($v_treatment_data['discharge_date']));

					$v_treatment_data['admission_days'][0] = $start;
					$v_treatment_data['discharge_days'][0] = $end;

					if(empty($v_treatment_data['patient_treatment_days']))
					{
						$v_treatment_data['patient_treatment_days'] = array();
					}

					$v_treatment_data['patient_treatment_days'] = array_merge($v_treatment_data['patient_treatment_days'], $pm->getDaysInBetween($start, $end));
				}

				//limit patient treatment period to the selected period
				$v_treatment_data['patient_treatment_days'] = array_values(array_intersect($v_treatment_data['patient_treatment_days'], $period_days));
				$patient_treatment[$k_ipid] = $v_treatment_data;
			}

//			print_r($patient_treatment);
			//get patient locations
			$patient_locations_days = InternalInvoices::get_patient_locations_days_multiple($ipids, $clientid);
//			print_r($patient_locations_days);

			foreach($patient_treatment as $k_pat_ipid => $v_pat_treat)
			{
				if(count($patient_locations_days[$k_pat_ipid]['all_locations_days']) == '0')
				{
					$patient_locations_days[$k_pat_ipid]['all_locations_days'] = array();
				}
				//construct no location patient days
				$patient_treatment[$k_pat_ipid]['no_location_treatment_days'] = array_diff($patient_treatment[$k_pat_ipid]['patient_treatment_days'], $patient_locations_days[$k_pat_ipid]['all_locations_days']);
				asort($patient_treatment[$k_pat_ipid]['no_location_treatment_days']);

				//put the remaining array days into the location normal days
				if(count($patient_locations_days[$k_pat_ipid]['locations_days']['normal']) == '0')
				{
					$patient_locations_days[$k_pat_ipid]['locations_days']['normal'] = array();
				}
				$patient_locations_days[$k_pat_ipid]['locations_days']['normal'] = array_merge($patient_locations_days[$k_pat_ipid]['locations_days']['normal'], $patient_treatment[$k_pat_ipid]['no_location_treatment_days']);
				asort($patient_locations_days[$k_pat_ipid]['locations_days']['normal']);
				$patient_locations_days[$k_pat_ipid]['locations_days']['normal'] = array_values(array_unique($patient_locations_days[$k_pat_ipid]['locations_days']['normal']));



				if(!empty($patient_locations_days[$k_pat_ipid]['locations_days']['normal']))
				{
					$patient_locations_days[$k_pat_ipid]['locations_days']['normal'] = array_values(array_unique($patient_locations_days[$k_pat_ipid]['locations_days']['normal']));
				}

				//remove hospital adm/dis and hospiz adm/dis from normal days
				if(!empty($patient_locations_days[$k_pat_ipid]['locations_days']['hosp']))
				{
					$patient_locations_days[$k_pat_ipid]['locations_days']['hosp'] = array_values(array_unique($patient_locations_days[$k_pat_ipid]['locations_days']['hosp']));

					$patient_locations_days[$k_pat_ipid]['locations_days']['normal'] = array_diff($patient_locations_days[$k_pat_ipid]['locations_days']['normal'], $patient_locations_days[$k_pat_ipid]['locations_days']['hosp']);
				}

				if(!empty($patient_locations_days[$k_pat_ipid]['locations_days']['hospiz']))
				{
					$patient_locations_days[$k_pat_ipid]['locations_days']['hospiz'] = array_values(array_unique($patient_locations_days[$k_pat_ipid]['locations_days']['hospiz']));

					$patient_locations_days[$k_pat_ipid]['locations_days']['normal'] = array_diff($patient_locations_days[$k_pat_ipid]['locations_days']['normal'], $patient_locations_days[$k_pat_ipid]['locations_days']['hospiz']);
				}
			}

			//get sapv patient details
			$vv_status2days = array('1' => 'be_days', '2' => 'ko_days', '3' => 'tv_days', '4' => 'vv_days');

			foreach($patient_treatment as $k_patient_ipid => $v_patient_treatment)
			{
				foreach($v_patient_treatment['patient_treatment_days'] as $key => $v_day)
				{
					foreach($day_products as $k_product => $v_product)
					{
						if($v_product['holiday'] == '1')
						{
							$check_holiday = true;
						}
						else
						{
							$check_holiday = false;
						}

						//LE: removed allready invoiced days
						if(($check_holiday && (in_array($v_day, $national_holidays) || date('w', strtotime($v_day)) == '0' || date('w', strtotime($v_day)) == '6')) || (!$check_holiday && !in_array($v_day, $national_holidays) && date('w', strtotime($v_day)) != '0' && date('w', strtotime($v_day)) != '6'))
						{
							if($v_product['sapv'] != '0' && in_array($v_day, $patient_sapv_details[$k_patient_ipid][$vv_status2days[$v_product['sapv']]])) //sapv product and patient treatment day is having product sapv
							{
								$day_items[$k_patient_ipid][$v_product['id']]['grouped'] = $v_product['grouped'];

								if($patient_active_details[$k_patient_ipid]['details']['isstandby'] == '1')
								{
									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['normal']) && $v_product['standby_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['standby_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0")) //standby location day product
									{
										$day_items[$k_patient_ipid][$v_product['id']]['standby'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['standby_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['standby_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}
								}
								else
								{
									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['normal']) && $v_product['normal_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['normal_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))//normal location day product
									{
										$day_items[$k_patient_ipid][$v_product['id']]['normal_1'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['normal'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['normal_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['normal_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}
								}

								if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['hosp'])) //hospital location day product
								{
									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['hosp_adm']) && $v_product['hosp_adm_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hosp_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_adm'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_adm_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}

									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['hosp_dis']) && $v_product['hosp_dis_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hosp_dis_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_dis'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}

									if(!in_array($v_day, $day_items[$v_product['id']]['hosp_adm_days']) && !in_array($v_day, $day_items[$v_product['id']]['hosp_dis_days']) && $v_product['hosp_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hosp_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hosp'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}
								}

								if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['hospiz'])) //hospiz location day product
								{
									//check if the day is admision or not
									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['hospiz_adm']) && $v_product['hospiz_adm_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hospiz_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_adm_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}

									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['hospiz_dis']) && $v_product['hospiz_dis_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hospiz_dis_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}

									if(!in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days']) && !in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days']) && $v_product['hospiz_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hospiz_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$v_product['id']]['hospiz'] += '1';
										$day_items[$v_product['id']]['hospiz_days'][] = $v_day;
										$day_items[$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_price'];
										$day_items[$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}
								}

								//					check if date is in both locations
								if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['hosp']) && in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['hospiz']))
								{
									//						hospital discharge - hospiz admision method
									if(in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days']) && in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days']) && $v_product['hosp_dis_hospiz_adm_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hosp_dis_hospiz_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_hospiz_adm'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_hospiz_adm_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_hospiz_adm_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;

										//remove from array if verified
										unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days'][array_search($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days'])]);

										$day_items[$k_patient_ipid][$v_product['id']]['hosp_dis'] -= 1;
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm'] -= 1;

										if(count($day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days']) == 0 || $day_items[$k_patient_ipid][$v_product['id']]['hosp_dis'] < '0')
										{
											unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days']);
											unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_dis']);
										}

										unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days'][array_search($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days'])]);

										if(count($day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days']) == 0 || $day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm'] < '0')
										{
											unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days']);
											unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm']);
										}
									}

									//						hospiz discharge - hospital admision method
									if(in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days']) && in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days']) && $v_product['hospiz_dis_hosp_adm_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hospiz_dis_hosp_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_hosp_adm'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_hosp_adm_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_hosp_adm_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;

										//remove from array if verified
										unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days'][array_search($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days'])]);
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis'] -= 1;
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_adm'] -= 1;

										if(count($day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days']) == 0 || $day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis'] < '0')
										{
											unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days']);
											unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis']);
										}

										unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days'][array_search($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days'])]);
										if(count($day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days']) == 0 || $day_items[$k_patient_ipid][$v_product['id']]['hosp_adm'] < '0')
										{
											unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days']);
											unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_adm']);
										}
									}
								}
								ksort($day_items[$k_patient_ipid][$v_product['id']]['product_all_days']);
							}
							else if($v_product['sapv'] == '0' && !in_array($v_day, $patient_sapv_details[$k_patient_ipid]['sapv_days'])) //product without sapv
							{

								$day_items[$k_patient_ipid][$v_product['id']]['grouped'] = $v_product['grouped'];

								if($patient_active_details[$k_patient_ipid]['details']['isstandby'] == '1')
								{
									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['normal']) && $v_product['standby_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['standby_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))//standby location day product
									{
										$day_items[$k_patient_ipid][$v_product['id']]['standby'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['standby_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['standby_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}
								}
								else
								{
//									print_r($k_patient_ipid."\n");
//									print_r('(2). '.$v_day."\n");
//									print_r($v_product);
//									var_dump(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['normal']));
//									var_dump(!in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['normal_days']));
//									var_dump($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days']));
//									var_dump(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['normal']) && $v_product['normal_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['normal_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"));
//									print_r("\n\n");

									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['normal']) && $v_product['normal_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['normal_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))//normal location day product
									{
										$day_items[$k_patient_ipid][$v_product['id']]['normal_2'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['normal'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['normal_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['normal_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}
								}

								if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['hosp'])) //hospital location day product
								{
									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['hosp_adm']) && $v_product['hosp_adm_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hosp_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_adm'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_adm_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}

									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['hosp_dis']) && $v_product['hosp_dis_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hosp_dis_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_dis'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}

									if(!in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days']) && !in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days']) && $v_product['hosp_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hosp_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hosp'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}
								}

								if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['hospiz'])) //hospiz location day product
								{
									//check if the day is admision or not
									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['hospiz_adm']) && $v_product['hospiz_adm_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hospiz_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_adm_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}

									if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['hospiz_dis']) && $v_product['hospiz_dis_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hospiz_dis_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}

									if(!in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days']) && !in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days']) && $v_product['hospiz_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hospiz_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;
									}
								}

								//					check if date is in both locations
								if(in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['hosp']) && in_array($v_day, $patient_locations_days[$k_patient_ipid]['locations_days']['hospiz']))
								{
									//						hospital discharge - hospiz admision method
									if(in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days']) && in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days']) && $v_product['hosp_dis_hospiz_adm_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$v_product['id']]['hosp_dis_hospiz_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_hospiz_adm'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_hospiz_adm_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hosp_dis_hospiz_adm_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;

										//remove from array if verified
										unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days'][array_search($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days'])]);
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_dis'] -= 1;
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm'] -= 1;

										if(count($day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days']) == 0 || $day_items[$k_patient_ipid][$v_product['id']]['hosp_dis'] < '0')
										{
											unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_dis_days']);
											unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_dis']);
										}

										unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days'][array_search($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days'])]);
										if(count($day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days']) == 0 || $day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm'] < '0')
										{
											unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm_days']);
											unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_adm']);
										}
									}

									//						hospiz discharge - hospital admision method
									if(in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days']) && in_array($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days']) && $v_product['hospiz_dis_hosp_adm_price'] != '0.00' && !in_array($v_day, $previous_items_arr[$k_patient_ipid][$k_patient_ipid][$v_product['id']]['hospiz_dis_hosp_adm_days']) && (($v_product['grouped'] == "1" && !in_array($v_day, $previous_items_arr[$k_patient_ipid]['grouped_days'])) || $v_product['grouped'] == "0"))
									{
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_hosp_adm'] += '1';
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_hosp_adm_days'][] = $v_day;
										$day_items[$k_patient_ipid][$v_product['id']]['days_products_prices'][$v_day] = $v_product['hospiz_dis_hosp_adm_price'];
										$day_items[$k_patient_ipid][$v_product['id']]['product_all_days'][strtotime($v_day)] = $v_day;

										//remove from array if verified
										unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days'][array_search($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days'])]);
										$day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis'] -= 1;
										$day_items[$k_patient_ipid][$v_product['id']]['hosp_adm'] -= 1;

										if(count($day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days']) == 0 || $day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis'] < '0')
										{
											unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis_days']);
											unset($day_items[$k_patient_ipid][$v_product['id']]['hospiz_dis']);
										}

										unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days'][array_search($v_day, $day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days'])]);
										if(count($day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days']) == 0 || $day_items[$k_patient_ipid][$v_product['id']]['hosp_adm'] < '0')
										{
											unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_adm_days']);
											unset($day_items[$k_patient_ipid][$v_product['id']]['hosp_adm']);
										}
									}
								}
								ksort($day_items[$k_patient_ipid][$v_product['id']]['product_all_days']);
							}
							if(empty($day_items[$k_patient_ipid][$v_product['id']]['product_all_days']))
							{
								unset($day_items[$k_patient_ipid][$v_product['id']]);
							}
						}
					}
				}
			}

//			print_r("day_items\n");
//			print_r($day_items);
//			exit;
			if($_REQUEST['daydbg'])
			{
				print_r($assignedusers);
				print_r($ipid2users);
				exit;
			}

			//assigned users get all products if they belongs to the product group
			foreach($assignedusers as $k_usr => $v_usr_id)
			{
				foreach($day_products as $k_prod => $v_prod)
				{
					foreach($patient_treatment as $k_ipid => $data)
					{
						if(in_array($v_usr_id, $groups_users[$v_prod['usergroup']]) && in_array($v_usr_id, $ipid2users[$k_ipid]) && array_key_exists($v_prod['id'], $day_items[$k_ipid]))
						{
							if(!empty($users_ids_associated[$v_usr_id]))
							{
								$master_items[$k_ipid][$users_ids_associated[$v_usr_id]][$v_prod['id']] = $day_items[$k_ipid][$v_prod['id']];
							}
							else
							{
								$master_items[$k_ipid][$v_usr_id][$v_prod['id']] = $day_items[$k_ipid][$v_prod['id']];
							}
						}
					}
				}
			}

			return $master_items;
		}

		public function get_patient_all_sapv($ipids)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$patientmaster = new PatientMaster();

			$ipids[] = '9999999999';
			$dropSapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->whereIn('ipid', $ipids)
				->andWhere('verordnungbis !="000-00-00 00:00:00" ')
				->andWhere('verordnungam !="000-00-00 00:00:00" ')
				->andWhere('isdelete=0')
				->andWhere('status != 1 ')
				->orderBy('verordnungam ASC');
			$sapv_array = $dropSapv->fetchArray();

			$s = 1;
			foreach($sapv_array as $sapvkey => $sapvvalue)
			{
//				$sapv[$sapvvalue['ipid']]['sapv_intervals'] = array();

				$sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['all_types'] = explode(',', $sapvvalue['verordnet']);

				$sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['type'] = $sapvvalue['verordnet'];
				$sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['from'] = $sapvvalue['verordnungam'];
				$sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['till'] = $sapvvalue['verordnungbis'];

				$sapv[$sapvvalue['ipid']]['sapv_start_days'][] = date('Y-m-d', strtotime($sapvvalue['verordnungam']));

				$sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['start'] = date('Y-m-d', strtotime($sapvvalue['verordnungam']));
				$sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['end'] = date('Y-m-d', strtotime($sapvvalue['verordnungbis']));

				$patient_active_sapv[$sapvvalue['ipid']][] = $patientmaster->getDaysInBetween($sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['start'], $sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['end']);


				if(in_array('1', $sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('2', $sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('3', $sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('4', $sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['all_types']))
				{
					$sapv_details[$sapvvalue['ipid']]['be_days'][] = $patientmaster->getDaysInBetween($sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['start'], $sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['end']);
				}

				if(in_array('2', $sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('3', $sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('4', $sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['all_types']))
				{
					$sapv_details[$sapvvalue['ipid']]['ko_days'][] = $patientmaster->getDaysInBetween($sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['start'], $sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['end']);
				}

				if(in_array('3', $sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['all_types']) && !in_array('4', $sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['all_types']))
				{
					$sapv_details[$sapvvalue['ipid']]['tv_days'][] = $patientmaster->getDaysInBetween($sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['start'], $sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['end']);
				}
				if(in_array('4', $sapv[$sapvvalue['ipid']]['patient_sapv'][$sapvvalue['id']]['all_types']))
				{
					$sapv_details[$sapvvalue['ipid']]['vv_days'][] = $patientmaster->getDaysInBetween($sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['start'], $sapv[$sapvvalue['ipid']]['sapv_intervals'][$s]['end']);
				}

				$s++;
			}

			foreach($sapv_details as $kipid => $sapv_det)
			{

				asort($sapv[$kipid]['sapv_start_days']);
				foreach($sapv_det['be_days'] as $kbes => $be_intervals)
				{
					foreach($be_intervals as $be_days)
					{
						$sapv[$kipid]['be_days'][] = $be_days;
					}
				}

				foreach($sapv_det['ko_days'] as $kkos => $ko_intervals)
				{
					foreach($ko_intervals as $ko_days)
					{
						$sapv[$kipid]['ko_days'][] = $ko_days;
					}
				}

				asort($sapv[$kipid]['tv_days']);

				foreach($sapv_det['tv_days'] as $ktvs => $tv_intervals)
				{
					foreach($tv_intervals as $tv_days)
					{
						$sapv[$kipid]['tv_days'][] = $tv_days;
					}
				}
				asort($sapv[$kipid]['tv_days']);
				$sapv[$kipid]['tv_days'] = array_unique($sapv[$kipid]['tv_days']);



				foreach($sapv_det['vv_days'] as $kvvs => $vv_intervals)
				{
					foreach($vv_intervals as $vv_days)
					{
						$sapv[$kipid]['vv_days'][] = $vv_days;
					}
				}
				asort($sapv[$kipid]['vv_days']);
				$sapv[$kipid]['vv_days'] = array_unique($sapv[$kipid]['vv_days']);



				foreach($patient_active_sapv[$kipid] as $sinter => $sinterval_days)
				{
					foreach($sinterval_days as $sdays)
					{
						$sapv[$kipid]['sapv_days_overall'][] = $sdays;
					}
				}
				asort($sapv[$kipid]['sapv_days_overall']);
				$sapv[$kipid]['sapv_days'] = array_unique($sapv[$kipid]['sapv_days_overall']);
			}

//			print_r("QQQsdays\n");
//			print_r($sapv);
//			exit;
			return $sapv;
		}

		public function get_patient_locations_days_multiple($ipids, $clientid)
		{
			$pl = new PatientLocation();
			$pm = new PatientMaster();


			//get client locations
			$c_locations = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->where('isdelete = 0')
				->andWhere('client_id ="' . $clientid . '"');
			$c_locations_array = $c_locations->fetchArray();

			foreach($c_locations_array as $k_loc => $v_loc)
			{
				$master_locations[$v_loc['id']]['type'] = $v_loc['location_type'];
				$master_locations[$v_loc['id']]['location_name'] = $v_loc['location'];
			}

			//get patient locations
//			$patient_locations = $pl->get_valid_patient_locations($ipid);
			$patient_locations_arr = $pl->get_valid_patients_locations($ipids);

			foreach($patient_locations_arr as $k_ipid => $patient_locations)
			{
				foreach($patient_locations as $k_patient_loc => $v_patient_loc)
				{
					$start = date('Y-m-d', strtotime($v_patient_loc['valid_from']));

					if($v_patient_loc['valid_till'] != '0000-00-00 00:00:00')
					{
						$end = date('Y-m-d', strtotime($v_patient_loc['valid_till']));
					}
					else
					{
						$end = date('Y-m-d', time());
					}

					if($master_locations[$v_patient_loc['location_id']]['type'] == '1') //hospital
					{
						if(empty($pat_days[$v_patient_loc['ipid']]['hosp']))
						{
							$pat_days[$v_patient_loc['ipid']]['hosp'] = array();
						}

						$pat_days[$v_patient_loc['ipid']]['hosp'] = array_merge($pat_days[$v_patient_loc['ipid']]['hosp'], $pm->getDaysInBetween($start, $end));

						$pat_locations[$v_patient_loc['ipid']]['hosp_adm'][] = $start;
						$pat_locations[$v_patient_loc['ipid']]['hosp_dis'][] = $end;
					}
					else if($master_locations[$v_patient_loc['location_id']]['type'] == '2') //hospiz
					{
						if(empty($pat_days[$v_patient_loc['ipid']]['hospiz']))
						{
							$pat_days[$v_patient_loc['ipid']]['hospiz'] = array();
						}

						$pat_days[$v_patient_loc['ipid']]['hospiz'] = array_merge($pat_days[$v_patient_loc['ipid']]['hospiz'], $pm->getDaysInBetween($start, $end));
						$pat_locations[$v_patient_loc['ipid']]['hospiz_adm'][] = $start;
						$pat_locations[$v_patient_loc['ipid']]['hospiz_dis'][] = $end;
					}
					else
					{
						if(empty($pat_days[$v_patient_loc['ipid']]['normal']))
						{
							$pat_days[$v_patient_loc['ipid']]['normal'] = array();
						}

						$pat_days[$v_patient_loc['ipid']]['normal'] = array_merge($pat_days[$v_patient_loc['ipid']]['normal'], $pm->getDaysInBetween($start, $end));
						$pat_locations[$v_patient_loc['ipid']]['normal_adm'][] = $start;
						$pat_locations[$v_patient_loc['ipid']]['normal_dis'][] = $end;
					}

					if(empty($pat_locations[$v_patient_loc['ipid']]['all_locations_days']))
					{
						$pat_locations[$v_patient_loc['ipid']]['all_locations_days'] = array();
					}

					$pat_locations[$v_patient_loc['ipid']]['all_locations_days'] = array_merge($pat_locations[$v_patient_loc['ipid']]['all_locations_days'], $pm->getDaysInBetween($start, $end));

					//uncomment to transfer original patient locations to main function
					//$pat_locations['locations'][] = $v_patient_loc;
					$pat_locations[$v_patient_loc['ipid']]['locations_days'] = $pat_days[$v_patient_loc['ipid']];
				}
			}

			return $pat_locations;
		}

		private function array_sort($array, $on = NULL, $order = SORT_ASC)
		{
			$new_array = array();
			$sortable_array = array();

			if(count($array) > 0)
			{
				foreach($array as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $k2 => $v2)
						{
							if($k2 == $on)
							{

								if($on == 'date' || $on == 'discharge_date' || $on == 'from_date' || $on == 'from' || $on == 'start_date_filter')
								{
									$sortable_array[$k] = strtotime($v2);
								}
								else if(is_array($v2) && $on == 'periods')
								{
									$sortable_array[$k] = strtotime($v2['from_date'][0]);
								}
								else
								{
									$sortable_array[$k] = ucfirst($v2);
								}
							}
						}
					}
					else
					{
						if($on == 'date' || $on == 'from_date' || $on == 'from' || $on == 'start_date_filter')
						{
							$sortable_array[$k] = strtotime($v);
						}
						else
						{
							$sortable_array[$k] = ucfirst($v);
						}
					}
				}

				switch($order)
				{
					case 'SORT_ASC':
//						asort($sortable_array);
						$sortable_array = Pms_CommonData::a_sort($sortable_array);
						break;
					case 'SORT_DESC':
//						arsort($sortable_array);
						$sortable_array = Pms_CommonData::ar_sort($sortable_array);
						break;
				}

				foreach($sortable_array as $k => $v)
				{
					$new_array[$k] = $array[$k];
				}
			}

			return $new_array;
		}

		public function get_storned_invoices($clientid)
		{
			$storno_invoices = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoices')
				->where("client='" . $clientid . "'")
				->andwhere('storno = "1"');
			$storno_invoices_res = $storno_invoices->fetchArray();

			if($storno_invoices_res)
			{
				$storned_invoices[] = '999999999999';
				foreach($storno_invoices_res as $k_storno => $v_storno)
				{
					$storned_invoices[] = $v_storno['record_id'];
				}

				return $storned_invoices;
			}
			else
			{
				return false;
			}
		}

		public function has_storno($invoiceid)
		{
			$invoice_storno = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoices')
				->where("record_id='" . $invoiceid . "'")
				->andwhere('storno = "1"')
				->limit('1');
			$invoice_storno_res = $invoice_storno->fetchArray();

			if($invoice_storno_res)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
				
		public function get_users_completed_invoices($clientid, $users, $period)
		{
			
			//2 = completed 3, 5 = paid and partialy paid
			$excluded_statuses = array('1', '4');
			
			foreach($period['start'] as $k_period => $v_period)
			{
				$sql_period[] = ' (DATE(invoice_start) BETWEEN DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '") AND DATE("' . date('Y-m-d H:i:s', strtotime($period['end'][$k_period])) . '") OR DATE(invoice_end) BETWEEN DATE("' . date('Y-m-d H:i:s', strtotime($period['end'][$k_period])) . '") AND DATE("' . date('Y-m-d H:i:s', strtotime($period['end'][$k_period])) . '")) ';
				$sql_period[] = ' (DATE(invoice_start) <= DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '") AND DATE(invoice_end) >= DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '")) ';
				$sql_period[] = ' (DATE(invoice_start) <= DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '") AND DATE(invoice_end) <= DATE("' . date('Y-m-d H:i:s', strtotime($period['end'][$k_period])) . '") AND DATE(invoice_end) > DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '")) ';
			}
			
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoices')
				->where("client='" . $clientid . "'")
				->andWhereIn('user', $users)
				->andWhere('isdelete = 0')
				->andWhere(implode(' OR ', $sql_period))
				->andWhereNotIn('status', $excluded_statuses)
				->orderBy('invoice_start ASC');
			$invoices_res = $invoices->fetchArray();
			
			if($invoices_res)
			{
				foreach($invoices_res as $k_res => $v_res)
				{
					$invoices_ids[] = $v_res['id'];
					$invoices_ids2ipid[$v_res['id']] = $v_res['ipid'];
					$invoices_ids2user[$v_res['id']] = $v_res['user'];
					$invoices_ids2period[$v_res['id']] = $v_res['invoice_start'].' > '.$v_res['invoice_end'];
					$invoices_ids2data[$v_res['id']] = $v_res;
				}
				$items_invoices = InternalInvoiceItems::getInvoicesItems($invoices_ids);

				if($items_invoices)
				{
					foreach($items_invoices as $k_item => $v_items)
					{
						foreach($v_items as $k_v_item => $v_v_item)
						{
							$v_v_item['user'] = $invoices_ids2user[$v_v_item['invoice']];
							$v_v_item['ipid'] = $invoices_ids2ipid[$v_v_item['invoice']];
							$v_v_item['invoice_data'] = $invoices_ids2data[$v_v_item['invoice']];
							$completed_items_data[] = $v_v_item;
						}
					}
					return $completed_items_data;
				}
				else
				{
					//invoices without items
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		
		
		 /**
		  * @author Ancuta 
		  * create date 26.04.2019
		  * @param unknown $invoices
		  * @param number $clientid
		  * @return void|unknown|boolean
		  */

		public function getMultipleInternalInvoice($invoices=array(),$clientid = 0 )
		{
		    if(empty($invoices) && empty($clientid)){
		        return ;
		    }
		    $internal_invoice_items = new InternalInvoiceItems();
		
		    $invoices = Doctrine_Query::create()
		    ->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
		    ->from('InternalInvoices')
		    ->whereIn("id",$invoices)
		    ->andWhere('client = ?',$clientid);
		    $invoices_res = $invoices->fetchArray();

		    if($invoices_res)
		    {
		        //get all invoice items
		        foreach($invoices_res as $k_invoice_res => $v_invoice_res)
		        {
		            $invoice_items = $internal_invoice_items->getInvoicesItems($v_invoice_res['id']); // STUPID!!!!  CHANGE
		            $invoice_details[$v_invoice_res['id']] = $v_invoice_res;
		
		            if($invoice_items)
		            {
		                $invoice_details[$v_invoice_res['id']]['items'] = $invoice_items[$v_invoice_res['id']];
		            }
		        }
		        //items sorted by first date of period
		        $invoice_details[$v_invoice_res['id']]['items'] = $this->array_sort($invoice_details[$v_invoice_res['id']]['items'], 'periods', 'SORT_ASC');
		
		        return $invoice_details;
		    }
		    else
		    {
		        return false;
		    }
		}
	
		/**
		 * @author Ancuta
		 * TODO-3012 Ancuta 20-23.03.2020
		 */
		private function storno_patient_xbdt_actions($invoice_id) {
        if (empty($invoice_id)) {
            return;
        }

        $internal_invoice_items = new InternalInvoiceItems();
        $invoice_items = $internal_invoice_items->getInvoicesItems($invoice_id);

        if (! empty($invoice_items[$invoice_id])) {
            foreach ($invoice_items[$invoice_id] as $k => $item) {
                if ($item['type'] == 'le') {
                    $patient_action_id = 0;
                    $patient_action_id = $item['product'];
                    // find in patient action id
                    $patient_le_Actions = Doctrine::getTable('PatientXbdtActions')->find($patient_action_id);
                    if ($patient_le_Actions) {
                        $patient_le_Actions->file_id = 0;
                        $patient_le_Actions->edited_from = "internalinvoice";
                        $patient_le_Actions->save();
                    }
                }
            }
        }
    }
		
	}

?>