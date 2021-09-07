<?php

	Doctrine_Manager::getInstance()->bindComponent('BayernInvoicesNew', 'SYSDAT');

	class BayernInvoicesNew extends BaseBayernInvoicesNew {

		public function getBayernInvoice($invoice, $status = false)
		{
			$bayerninvoice_items = new BayernInvoiceItemsNew();

			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('BayernInvoicesNew')
				->where("id='" . $invoice . "'")
				->andWhere('isdelete = 0');

			if($status)
			{
				$invoices->andWhere('status = "' . $status . '"');
			}
			$invoices->limit('1');

			$invoices_res = $invoices->fetchArray();
			if($invoices_res)
			{
				//get all invoice items
				$invoice_items = $bayerninvoice_items->getInvoicesItems($invoices_res[0]['id']);

				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
					$invoice_details = $v_invoice_res;

					if($invoice_items)
					{
						$invoice_details['items'] = $invoice_items[$v_invoice_res['id']];
					}
				}

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
				->from('BayernInvoicesNew')
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

		public function get_highest_invoice_number($clientid, $prefix = false, $all = false)
		{
			$invoice_number = Doctrine_Query::create()
				->select("*")
				->from('BayernInvoicesNew')
				->where("client='" . $clientid . "'")
				->andWhere('isdelete = 0')
				->orderBy('invoice_number DESC')
				->limit('1');
			if($prefix)
			{
				$invoice_number->andWhere('prefix = "' . $prefix . '"');
			}
			else if($all === false)
			{
				$invoice_number->andWhere('prefix = ""');
			}

			$invoice_number_data = $invoice_number->fetchArray();
//			if($_REQUEST['dbgx'])
//			{
//				var_dump($prefix);
//				print_r($invoice_number->getSqlQuery());
////			exit;
//			}

			if($invoice_number_data)
			{
				return $invoice_number_data[0];
			}
			else
			{
				return false;
			}
		}

		public function get_period_bayern_invoices($ipids, $clientid, $period)
		{

			$ipids[] = '999999999999';

			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('BayernInvoicesNew')
				->where("client='" . $clientid . "'")
				->andWhereIn('ipid', $ipids)
				->andWhere('isdelete = 0');

			if($period)
			{
				$invoices->andWhere('invoice_start BETWEEN "' . date('Y-m-d H:i:s', strtotime($period['start'])) . '" AND "' . date('Y-m-d H:i:s', strtotime($period['end'])) . '"');
			}

			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				return $invoices_res;
			}
			else
			{
				return false;
			}
		}

		public function create_storno_invoice($invoiceid)
		{
			$find_invoice = Doctrine::getTable('BayernInvoicesNew')->findOneById($invoiceid);
			$found_invoice = $find_invoice->toArray();

			$has_storno = self::has_storno($invoiceid);

			if($found_invoice && !$has_storno)
			{
				$inv = new BayernInvoicesNew();
				$inv->invoice_start = $found_invoice['invoice_start'];
				$inv->invoice_end = $found_invoice['invoice_end'];
				$inv->invoiced_month = $found_invoice['invoiced_month'];
				$inv->start_active = $found_invoice['start_active'];
				$inv->end_active = $found_invoice['end_active'];
				$inv->start_sapv = $found_invoice['start_sapv'];
				$inv->end_sapv = $found_invoice['end_sapv'];
				$inv->sapv_approve_date = $found_invoice['sapv_approve_date'];
				$inv->sapv_approve_nr = $found_invoice['sapv_approve_nr'];
				$inv->ipid = $found_invoice['ipid'];
				$inv->client = $found_invoice['client'];
				
				//ISPC-2532 Carmen 17.02.2020
				$invoice_number = $this->get_next_invoice_number($found_invoice['client']);
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
				//--
				
				$inv->invoice_total = $found_invoice['invoice_total'];
				$inv->status = $found_invoice['status'];
				$inv->address = $found_invoice['address'];
				$inv->footer = $found_invoice['footer'];
				$inv->sapvid = $found_invoice['sapvid'];
				$inv->debtor_number = $found_invoice['debtor_number'];
				$inv->ppun = $found_invoice['ppun'];
				$inv->paycenter = $found_invoice['paycenter'];
				$inv->isdelete = '0';
				$inv->isarchived = $found_invoice['isarchived'];
				$inv->record_id = $invoiceid;
				$inv->storno = '1';
				//$inv->completed_date = $found_invoice['completed_date'];
				$inv->completed_date = date('Y-m-d H:i:s', time());        //ISPC-2532 Lore 11.11.2020
				$inv->save();
			}
		}

		public function del_storno_invoice($invoiceid)
		{
			$del_storno_invoice = Doctrine::getTable('BayernInvoicesNew')->findOneById($invoiceid);
			$del_storno_invoice->isdelete = '1';
			$del_storno_invoice->save();
		}

		public function get_previous_patient_invoices($ipid, $clientid)
		{
			if(!is_array($ipid))
			{
				$ipids = array($ipid);
			}
			else
			{
				$ipids = $ipid;
			}

			$invoices = Doctrine_Query::create()
				->select("*")
				->from('BayernInvoicesNew')
				->whereIn('ipid', $ipids)
				->andWhere("client='" . $clientid . "'")
				->andWhere('isdelete = 0')
				->andWhere(' DATE(invoice_start) < DATE("' . date('Y-m-d', time()) . '")');
			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				return $invoices_res;
			}
			else
			{
				return false;
			}
		}

		public function get_previous_patients_invoices($ipid)
		{
			if(!is_array($ipid))
			{
				$ipids = array($ipid);
			}
			else
			{
				$ipids = $ipid;
			}

			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('BayernInvoicesNew')
				->whereIn('ipid', $ipids)
				->andWhere('isdelete = 0');

			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				return $invoices_res;
			}
			else
			{
				return false;
			}
		}

		public function get_next_invoice_number($clientid, $temp = false)
		{
			$client = new Client();
			$invoice_settings = new InvoiceSettings();
			$bayern_invoices = new BayernInvoicesNew();
			$client_data = $client->getClientDataByid($clientid);

			$collective = '0';
			if($client_data[0]['invoice_number_type'] == '1')
			{
				$collective = '1';
			}
			$required_shortcuts = array('new_bayern_invoice');
			$invoice_settings_arr = $invoice_settings->getClientInvoiceSettings($clientid, $required_shortcuts, $collective);


			if($client_data[0]['invoice_number_type'] == '0') //individual type
			{
				//sapv invoice
				$invoice_rnummer = $bayern_invoices->get_highest_invoice_number($clientid, $invoice_settings_arr['new_bayern_invoice']['invoice_prefix']);

				if($invoice_rnummer)
				{

					if($invoice_rnummer['invoice_number'] >= $invoice_settings_arr['new_bayern_invoice']['invoice_start'] && $invoice_rnummer['prefix'] == $invoice_settings_arr['new_bayern_invoice']['invoice_prefix'])
					{
						$prefix = $invoice_settings_arr['new_bayern_invoice']['invoice_prefix'];
						$i_number = $invoice_rnummer['invoice_number'];
						$i_number++;
					}
					else
					{
						$prefix = $invoice_settings_arr['new_bayern_invoice']['invoice_prefix'];
						$i_number = $invoice_settings_arr['new_bayern_invoice']['invoice_start'];
						if($invoice_settings_arr['new_bayern_invoice']['invoice_start'] == '0')
						{
							$i_number++;
						}
					}
				}
				else
				{
					if(strlen($invoice_settings_arr['new_bayern_invoice']['invoice_start']))
					{
						$prefix = $invoice_settings_arr['new_bayern_invoice']['invoice_prefix'];
						$i_number = $invoice_settings_arr['new_bayern_invoice']['invoice_start'];
						if($invoice_settings_arr['new_bayern_invoice']['invoice_start'] == '0')
						{
							$i_number++;
						}
					}
					else
					{
						$prefix = '';
						$i_number = '1000';
					}
				}

				$prefix = $prefix;
				$invoicenumber = $i_number;
			}
			else
			{
				//get all client invoices highest number if collective settings are applied
				$collective_highest_number = $invoice_settings->get_all_invoices_high_number($clientid);

				foreach($invoice_settings_arr as $k_inv_settigns => $v_inv_settings)
				{
					if(in_array($v_inv_settings['invoice_prefix'], $collective_highest_number['prefix']))
					{
						foreach($collective_highest_number['number'] as $k_coll_high => $v_coll_high)
						{
							if($collective_highest_number['prefix'][$k_coll_high] == $v_inv_settings['invoice_prefix'])
							{
								$coll_numbers[] = $v_coll_high;
							}
						}

						if(count($coll_numbers) > 0)
						{
							$max_collection_number = max($coll_numbers);

							if($max_collection_number > $v_inv_settings['invoice_start'])
							{
								$i_number[$k_inv_settigns] = $max_collection_number;
							}
							else
							{
								$i_number[$k_inv_settigns] = $v_inv_settings['invoice_start'];
							}

							$i_number[$k_inv_settigns] ++;
						}
					}
					else
					{
						$i_number[$k_inv_settigns] = $v_inv_settings['invoice_start'];
						if($v_inv_settings['invoice_start'] == '0')
						{
							$i_number[$k_inv_settigns] ++;
						}
					}
				}

				$final_invoice_number = max($i_number);

				$prefix = $client_data[0]['invoice_number_prefix'];
				$invoicenumber = $final_invoice_number;
			}

			if($temp === false)
			{
				$invoice_nr_arr['prefix'] = $prefix;
				$invoice_nr_arr['invoicenumber'] = $invoicenumber;
			}
			else
			{
				$invoice_nr_arr = $this->generate_temp_invoice_number($clientid);
			}

			return $invoice_nr_arr;
		}

		private function generate_temp_invoice_number($clientid)
		{
			$invoices = new BayernInvoicesNew();
			$temp_prefix = 'TEMP_';
			$high_inv_nr = $invoices->get_highest_invoice_number($clientid, $temp_prefix);

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

		public function get_patient_storno_invoices($ipid)
		{

			$invoices = Doctrine_Query::create()
				->select("*")
				->from('BayernInvoicesNew')
				->where("ipid='" . $ipid . "'")
				->andWhere('isdelete = 0')
				->andWhere('storno = 1');
			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				foreach($invoices_res as $ki => $invoice_values)
				{
					$storno_invoices_ids[] = $invoice_values['record_id'];
				}
				return $storno_invoices_ids;
			}
			else
			{
				return false;
			}
		}

		public function get_patient_bayern_invoice($ipid, $status = false)
		{
			$bayerninvoice_items = new BayernInvoiceItemsNew();

			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('BayernInvoicesNew')
				->where("ipid='" . $ipid . "'")
				->andWhere('isdelete = 0')
				->andWhere('storno = 0');

			if($status)
			{
				$invoices->andWhereIn('status', $status);
			}
			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				$storno_invices = $this->get_patient_storno_invoices($ipid);
				foreach($invoices_res as $ki => $invoice_values)
				{
					if(!in_array($invoice_values['id'], $storno_invices))
					{
						$invoices_ids[] = $invoice_values['id'];
					}
				}
			}
			if($invoices_ids)
			{
				//get all invoice items
				$invoice_items = $bayerninvoice_items->getInvoicesItems($invoices_ids);
				//get ovearll amount of paid
				$paid_invoice_items = $bayerninvoice_items->get_overall_completed_items($invoices_ids);

				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
					$invoice_details = $v_invoice_res;

					if($invoice_items)
					{
						$invoice_details['items'][] = $invoice_items;
					}
				}

				if($paid_invoice_items)
				{
					$invoice_details['paid_items'] = $paid_invoice_items;
				}

				return $invoice_details;
			}
			else
			{
				return false;
			}
		}

//		takes all client storned invoices!
		public function get_storned_invoices($clientid = false)
		{
			if($clientid === true)
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
			}

			$storno_invoices = Doctrine_Query::create()
				->select("*")
				->from('BayernInvoicesNew')
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
				->from('BayernInvoicesNew')
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

		public function get_multi_patient_bayern_invoice($ipids, $status = false, $clientid = false)
		{
			$bayerninvoice_items = new BayernInvoiceItemsNew();
			$ipids[] = '999999999999999';

			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('BayernInvoicesNew')
				->whereIn("ipid", $ipids)
				->andWhere('isdelete = 0')
				->andWhere('storno = 0')
				->andWhere('tabname = "invoice"');
			if($status)
			{
				$invoices->andWhereIn('status', $status);
			}
			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				$storno_invices = $this->get_storned_invoices($clientid);

				foreach($invoices_res as $ki => $invoice_values)
				{
					if(!in_array($invoice_values['id'], $storno_invices))
					{
						$invoices_ids[] = $invoice_values['id'];
						$invoices_data[$invoice_values['ipid']] = $invoice_values;
					}
				}
			}

			if($invoices_ids)
			{
				//get all invoice items
				$invoice_items = $bayerninvoice_items->getInvoicesItems($invoices_ids);
				//get ovearll amount of paid
//				$paid_invoice_items = $bayerninvoice_items->get_overall_completed_items($invoices_ids);
				$paid_invoice_items = $bayerninvoice_items->get_multi_pat_overall_completed_items($invoices_ids);

				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
					if($invoice_items[$v_invoice_res['id']])
					{
						$v_invoice_res['items'] = $invoice_items[$v_invoice_res['id']];
					}

					if($paid_invoice_items[$v_invoice_res['id']])
					{
						$v_invoice_res['paid_items'] = $paid_invoice_items[$v_invoice_res['id']];
					}

					$invoice_details[] = $v_invoice_res;
				}

				return $invoice_details;
			}
			else
			{
				return false;
			}
		}

		public function get_invoices($invoices_ids)
		{
			$bayinvoice_items = new BayernInvoiceItemsNew();
			$invoices_ids[] = '9999999999999999';
			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('BayernInvoicesNew')
				->whereIn("id", $invoices_ids)
				->andWhere('isdelete = "0"')
				->andWhere('isarchived = "0"');
			$invoices_res = $invoices->fetchArray();

			foreach($invoices_res as $k_inv => $v_inv)
			{
				if($v_inv['storno'] == "1")
				{
					$storned_ids[$v_inv['id']] = $v_inv['record_id'];
					$invoices_ids[] = $v_inv['record_id'];
				}
			}

			if($invoices_res)
			{
				//get all invoice items
				$invoice_items = $bayinvoice_items->getInvoicesItems($invoices_ids);

				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
					$master_data['invoices_ipdis'][] = $v_invoice_res['ipid'];

					$master_data['invoices_data'][$v_invoice_res['id']] = $v_invoice_res;
					if($invoice_items)
					{
						if(array_key_exists($v_invoice_res['id'], $storned_ids))
						{
							//make sure that storno invoices have inherited the items of storned invoice
							$master_data['invoices_data'][$v_invoice_res['id']]['items'] = $invoice_items[$storned_ids[$v_invoice_res['id']]];
						}
						else
						{
							$master_data['invoices_data'][$v_invoice_res['id']]['items'] = $invoice_items[$v_invoice_res['id']];
						}
					}
				}

				return $master_data;
			}
			else
			{
				return false;
			}
		}

		public function get_bay_invoiced_sapvs($ipids)
		{
			$ipids[] = '9999999999999';

			$invoiced_sapvs = Doctrine_Query::create()
				->select("*")
				->from('BayernInvoicesNew')
				->whereIn("ipid", $ipids)
				->andwhere('storno = "0"')
				->andwhere('isdelete = "0"');
			$invoiced_sapvs_res = $invoiced_sapvs->fetchArray();

			if($invoiced_sapvs_res)
			{
				foreach($invoiced_sapvs_res as $k_res => $v_res)
				{
					$invoices_sapvs_ids['sapv'][] = $v_res['sapvid'];
					$invoices_sapvs_ids['sapv'] = array_values(array_unique($invoices_sapvs_ids['sapv']));

					$invoices_sapvs_ids['fall'][] = $v_res['ipid'] . '_' . date("Y_m_d", strtotime($v_res['invoice_start'])). '_' . date("Y_m_d", strtotime($v_res['invoice_end']));
					$invoices_sapvs_ids['fall'] = array_values(array_unique($invoices_sapvs_ids['fall']));

					$invoices_sapvs_ids['admission'][] = $v_res['admissionid'];
					$invoices_sapvs_ids['admission'] = array_values(array_unique($invoices_sapvs_ids['admission']));
				}
				return $invoices_sapvs_ids;
			}
			else
			{
				return false;
			}
		}

		public function generatecsv($post , $clientid = false)
		{
			if($post['document'])
			{
				$document_ids = $post['document'];
				$document_ids[] = '9999999999999999';
				//get invoices data
				$documents_data = BayernInvoicesNew::get_invoices($document_ids);
				$final_csv_data[0] = array(
					'Belegdatum', //date of invoice
					'Buchungskreis', //hardcode "4190"
					'', //blank
					'Debitor', //debutornumber or ppun
					'', //blank
					'Kostenstelle', //Paycenter
					'Ertragskonto', //hardcode "400910"
					'Belegkopftext', //patient surname, firstname
					'Positionstext', //patient surname, firstname
					'Betrag', //ammount with COMMA
					'Steuerrechnen', //hardcode "X"
					'Steuerschlüssel', //hardcode "40"
					'Innenauftrag', //leave blank
					'Zuordnung', //leave blank
					'Buchungsschlüssel Debitor', //"01" for invoice "11" for back invoice
					'Buchungsschlüssel Sachkonto', //hardcode "sa"
					'Referenz Belegkopf', //ispc invoice number
					'Belegart', //hardcode "v6"
				);

				//extract invoices ipids
				$invoice_ipids[] = '9999999999999999';
				foreach($documents_data['invoices_data'] as $k_doc_inv => $v_doc_inv)
				{
					$invoice_ipids[] = $v_doc_inv['ipid'];
				}

				if($invoice_ipids)
				{
					$patients_details = PatientMaster::get_multiple_patients_details($invoice_ipids);
				}
				
				/*
				 ISPC-1806
				 the export which the BAY_ZAHPV_SAPV team uses to export invoices needs another coloumn
				 patient health insuramce number
				 */
				$insurance_no_col = false;
				$patients_healthinsurance_number = array();
				if ( $clientid !== false ){
					$modules = new Modules();
					if($modules->checkModulePrivileges("140", $clientid))
					{
						$insurance_no_col = true;
						$final_csv_data[0][] = "Versichertennummer"; //insurance number
						$patients_healthinsurance_number = PatientHealthInsurance :: get_patients_healthinsurance_number($invoice_ipids);
						
					}
				}
								
				foreach($documents_data['invoices_data'] as $k_doc => $v_doc)
				{
					//col 1 - Belegdatum
					$invoice_date = date('d.m.Y', strtotime($v_doc['completed_date_sort']));

					//col 4 - Debitor
					$debtor_number_ppun = '';
					if(strlen(trim($v_doc['debtor_number'])) > '0' && strlen(trim($v_doc['ppun'])) == "0")
					{
						$debtor_number_ppun = $v_doc['debtor_number'];
					}

					if(strlen(trim($v_doc['ppun'])) > '0' && strlen(trim($v_doc['debtor_number'])) == "0")
					{
						$debtor_number_ppun = $v_doc['ppun'];
					}

					//col 8 und 9 - Belegkopftext und Positionstext
					$patient_name = '';
					if(strlen(trim($v_doc['last_name'])) != '0')
					{
						$patient['last_name'] = $v_doc['last_name'];
					}
					else if(strlen($patients_details[$v_doc['ipid']]['last_name']) > '0')
					{
						$patient['last_name'] = $patients_details[$v_doc['ipid']]['last_name'];
					}

					if(strlen(trim($v_doc['first_name'])) != '0')
					{
						$patient['first_name'] = $v_doc['first_name'];
					}
					else if(strlen($patients_details[$v_doc['ipid']]['first_name']) > '0')
					{
						$patient['first_name'] = $patients_details[$v_doc['ipid']]['first_name'];
					}

					$patient_name = implode(', ', $patient);

					//col 10 - Betrag
					$ammount = number_format($v_doc['invoice_total'], '2', ',', '.');

					//col 15 - Buchungsschlüssel Debitor
					if($v_doc['tabname'] == "invoice")
					{
						$inv_type_value = "01";
					}
					else if($v_doc['tabname'] == "paidback")
					{
						$inv_type_value = "11";
					}

					//col 16 - Buchungsschlüssel Sachkonto
					
					if($v_doc['tabname'] == "invoice")
					{
						$posting_key_value = "50"; // fill with "50" if "Buchungsschlüssel Debitor " is 01 
					}
					else if($v_doc['tabname'] == "paidback")
					{
						$posting_key_value = "40"; // fill with "40" if "Buchungsschlüssel Debitor " is 11
					}

					//col 17 - Referenz Belegkopf
					$invoice_number = $v_doc['prefix'] . $v_doc['invoice_number'];

					$final_csv_data[] = array(
						$invoice_date, //date of invoice
						'4290', //hardcode ""     TODO-186 CSV export - ISPC  can you change the hardcoded "4190" to "4290"
						'', //blank
						$debtor_number_ppun, //debutornumber or ppun
						'', //blank
						$v_doc['paycenter'], //Paycenter
						'400910', //hardcode "400910"
						$patient_name, //patient surname, firstname
						$patient_name, //patient surname, firstname
						$ammount,
						'X',
						'40',
						'',
						'',
						$inv_type_value,
						$posting_key_value,
						$invoice_number,
						'DR'
						
					);
					if ($insurance_no_col){
						//col 18 - healt insurance number // ISPC-1806
						end($final_csv_data);
						$key = key($final_csv_data);
						$final_csv_data[$key][]= $patients_healthinsurance_number[$v_doc['ipid']]['insurance_no'];
					}
				}
 
				if($final_csv_data)
				{
					Pms_CommonData::generate_csv($final_csv_data, 'export.csv', ';', null);
				}
				else
				{
					$redirector =Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
					$redirector->gotoSimple('bayerninvoices','invoicenew');
// 					$this->redirect(APP_BASE . 'invoicenew/bayerninvoices');
					exit;
				}
			}
			else
			{
			
				$redirector =Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				$redirector->gotoSimple('bayerninvoices','invoicenew');
// 				$this->redirect(APP_BASE . 'invoicenew/bayerninvoices');
				exit;
			}
		}

	}

?>