<?php

	Doctrine_Manager::getInstance()->bindComponent('RpInvoices', 'SYSDAT');

	class RpInvoices extends BaseRpInvoices {

		public function getRpInvoice($invoice, $status = false)
		{
			$rpinvoice_items = new RpInvoiceItems();

			$invoices = Doctrine_Query::create()
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort
					krankenkasse as insurance_company_name,
					patient_name as patientname,
					geb as birthdate,
					kassen_nr as kvnumber,
					versicherten_nr as insurance_no,
					ins_status as insurance_status")
				->from('RpInvoices')
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
				$invoice_items = $rpinvoice_items->getInvoicesItems($invoices_res[0]['id']);

				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
					if($v_invoice_res['sapv_start'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_invoice_res['sapv_start'])) != '1970-01-01')
					{
						$v_invoice_res['curent_sapv_from'] = date('d.m.Y', strtotime($v_invoice_res['sapv_start']));
					}
					else
					{
						$v_invoice_res['curent_sapv_from'] = '';
					}

					if($v_invoice_res['sapv_end'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_invoice_res['sapv_end'])) != '1970-01-01')
					{
						$v_invoice_res['curent_sapv_till'] = date('d.m.Y', strtotime($v_invoice_res['sapv_end']));
					}
					else
					{
						$v_invoice_res['invoice_date_from'] = '';
					}

					if($v_invoice_res['invoice_start'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_invoice_res['invoice_start'])) != '1970-01-01')
					{
						$v_invoice_res['invoice_date_from'] = date('d.m.Y', strtotime($v_invoice_res['invoice_start']));
					}
					else
					{
						$v_invoice_res['invoice_date_from'] = '';
					}

					if($v_invoice_res['invoice_end'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_invoice_res['invoice_end'])) != '1970-01-01')
					{
						$v_invoice_res['invoice_date_till'] = date('d.m.Y', strtotime($v_invoice_res['invoice_end']));
					}
					else
					{
						$v_invoice_res['invoice_date_till'] = '';
					}

					if($v_invoice_res['date_delivery'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_invoice_res['date_delivery'])) != '1970-01-01')
					{
						$v_invoice_res['date_delivery'] = date('d.m.Y', strtotime($v_invoice_res['date_delivery']));
					}
					else
					{
						$v_invoice_res['date_delivery'] = '';
					}

					if($v_invoice_res['completed_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_invoice_res['completed_date'])) != '1970-01-01')
					{
						$v_invoice_res['completed_date'] = date('d.m.Y', strtotime($v_invoice_res['completed_date']));
					}
					else
					{
						$v_invoice_res['completed_date'] = date('d.m.Y');
					}

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
				->from('RpInvoices')
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
				->from('RpInvoices')
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
			$invoice_number = $invoice_number->fetchArray();

			if($invoice_number)
			{
				return $invoice_number[0];
			}
			else
			{
				return false;
			}
		}

		public function create_storno_invoice($invoiceid)
		{
			$find_invoice = Doctrine::getTable('RpInvoices')->findOneById($invoiceid);
			$found_invoice = $find_invoice->toArray();

			$has_storno = self::has_storno($invoiceid);

			if($found_invoice && !$has_storno)
			{
				//ISPC-2532 create new number for storno invoice Carmen 13.02.2020*/
				$invoice_number = $this->get_next_invoice_number($found_invoice['client']);
				
				$inv = new RpInvoices();
				$inv->ipid = $found_invoice['ipid'];
				$inv->client = $found_invoice['client'];

				$inv->krankenkasse = $found_invoice['krankenkasse'];
				$inv->patient_name = $found_invoice['patient_name'];
				$inv->geb = $found_invoice['geb'];
				$inv->kassen_nr = $found_invoice['kassen_nr'];
				$inv->versicherten_nr = $found_invoice['versicherten_nr'];
				$inv->ins_status = $found_invoice['ins_status'];
				$inv->betriebsstatten_nr = $found_invoice['betriebsstatten_nr'];
				$inv->arzt_nr = $found_invoice['arzt_nr'];
				$inv->topdatum = $found_invoice['topdatum'];
				$inv->client_ik = $found_invoice['client_ik'];

				$inv->invoice_start = $found_invoice['invoice_start'];
				$inv->invoice_end = $found_invoice['invoice_end'];
				$inv->main_diagnosis = $found_invoice['main_diagnosis'];

				$inv->sapv_id = $found_invoice['sapv_id'];
				$inv->sapv_start = $found_invoice['sapv_start'];
				$inv->sapv_end = $found_invoice['sapv_end'];

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
				$inv->paid_date = $found_invoice['paid_date'];
				$inv->status = $found_invoice['status'];
				$inv->stample = $found_invoice['stample'];
				$inv->sapv_erst = $found_invoice['sapv_erst'];
				$inv->sapv_folge = $found_invoice['sapv_folge'];

				$inv->date_delivery = $found_invoice['date_delivery'];
				$inv->sig_date = $found_invoice['sig_date'];
				$inv->bottom_signature = $found_invoice['bottom_signature'];

				$inv->isdelete = '0';
				$inv->record_id = $invoiceid;
				$inv->storno = '1';
				//$inv->completed_date = $found_invoice['completed_date'];
				$inv->completed_date = date('Y-m-d H:i:s', time());        //ISPC-2532 Lore 11.11.2020
				$inv->save();
			}
		}

		public function del_storno_invoice($invoiceid)
		{
			$del_storno_invoice = Doctrine::getTable('RpInvoices')->findOneById($invoiceid);
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
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('RpInvoices')
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
				->from('RpInvoices')
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
		    
		    //TODO-3680 Ancuta 11.12.2020  - Changed from rp_invoice to rpinvoice - as this was foolishly changed by Carmen 
			$client = new Client();
			$invoice_settings = new InvoiceSettings();
			$invoices = new RpInvoices();
			$client_data = $client->getClientDataByid($clientid);

			$collective = '0';
			if($client_data[0]['invoice_number_type'] == '1')
			{
				$collective = '1';
			}
			$required_shortcuts = array('rpinvoice');
			$invoice_settings_arr = $invoice_settings->getClientInvoiceSettings($clientid, $required_shortcuts, $collective);


			if($client_data[0]['invoice_number_type'] == '0') //individual type
			{
				//Rp invoice
				$rp_invoice_number = $invoices->get_highest_invoice_number($clientid, $invoice_settings_arr['rpinvoice']['invoice_prefix']);
				if($rp_invoice_number)
				{
					if($rp_invoice_number['invoice_number'] >= $invoice_settings_arr['rpinvoice']['invoice_start'] && $rp_invoice_number['prefix'] == $invoice_settings_arr['rpinvoice']['invoice_prefix'])
					{
						$prefix = $invoice_settings_arr['rpinvoice']['invoice_prefix'];
						$i_number = $rp_invoice_number['invoice_number'];
						$i_number++;
					}
					else
					{
						$prefix = $invoice_settings_arr['rpinvoice']['invoice_prefix'];
						$i_number = $invoice_settings_arr['rpinvoice']['invoice_start'];
						if($invoice_settings_arr['rpinvoice']['invoice_start'] == '0')
						{
							$i_number++;
						}
					}
				}
				else
				{
					if(strlen($invoice_settings_arr['rpinvoice']['invoice_start']) > 0)
					{
						$prefix = $invoice_settings_arr['rpinvoice']['invoice_prefix'];
						$i_number = $invoice_settings_arr['rpinvoice']['invoice_start'];
						if($invoice_settings_arr['rpinvoice']['invoice_start'] == '0')
						{
							$i_number++;
						}
					}
					else
					{
						$prefix = "";
						$i_number = '1000';
					}
				}
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
				$invoice_nr_arr = self::generate_temp_invoice_number($clientid);
			}

			return $invoice_nr_arr;
		}

		private function generate_temp_invoice_number($clientid)
		{
			$invoices = new RpInvoices();
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

		public function get_storned_invoices($clientid)
		{
			$storno_invoices = Doctrine_Query::create()
				->select("*")
				->from('RpInvoices')
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
				->from('RpInvoices')
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

		
		
		
		public function get_multiple_rp_invoices($invoice_ids = false)
		{
			$rp_invoiceitems = new RpInvoiceItems();
		
			if ($invoice_ids === false || !is_array($invoice_ids) || empty($invoice_ids)){
				return false;
			}
			$invoices = Doctrine_Query::create()
			->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
			->from('RpInvoices')
			->whereIn("id", $invoice_ids)
			->andWhere('isdelete = 0');
			$invoices_res = $invoices->fetchArray();
		
			if($invoices_res)
			{
				//get all invoice items
				$invoice_items = $rp_invoiceitems->getInvoicesItems($invoice_ids);
					
				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
		
					if($invoice_items[$v_invoice_res['id']])
					{
						$v_invoice_res['items'] = $invoice_items[$v_invoice_res['id']];
					}
					else
					{
						$v_invoice_res['items'] = '';
					}
		
					$invoice_details[$v_invoice_res['id']] = $v_invoice_res;
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
			if(empty($invoices_ids)){
				return false;
			}
		
			$rp_invoice_items = new RpInvoiceItems();
		
			$invoices = Doctrine_Query::create()
			->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
			->from('RpInvoices')
			->whereIn("id", $invoices_ids)
			->andWhere('isdelete = "0"');
			//->andWhere('isarchived = "0"');
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
				$invoice_items = $rp_invoice_items->getInvoicesItems($invoices_ids);
		
				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
					$master_data['invoices_users'][] = $v_invoice_res['user'];
		
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
		
		
	}

?>