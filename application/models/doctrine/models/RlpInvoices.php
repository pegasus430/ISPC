<?php
 
	Doctrine_Manager::getInstance()->bindComponent('RlpInvoices', 'SYSDAT');

	class RlpInvoices extends BaseRlpInvoices {

		public function rlp_products(){

			$products = array(
				"be_aasse",//Beratung / Assessment gem. §7 Abs. 1;
				"sapv_flatrate",//Pauschale , wenn es nicht zur Versorgung mit SAPV kommt 
				"first_dot",//Am 1. Behandlungstag ;
				"second_dot",//ab dem 2. Behandlungstag ;
				"doctor_visit",//Koordinationspausch. Arzt ;
				"nurse_visit",//Koordinationspausch. Pflege ;
				"regional_flatrate" // Regionale Strukturpauschale ;
			); 
			
			return $products;
		}
		
		/**
		 * Default ids
		 * @return multitype:string
		 * TODO-2058
		 */
		public function rlp_products_ident(){

			$products = array(
				"be_aasse"=>"1001",//Beratung / Assessment gem. §7 Abs. 1;
				"sapv_flatrate"=>"2002",//Pauschale , wenn es nicht zur Versorgung mit SAPV kommt 
				"first_dot"=>"2001",//Am 1. Behandlungstag ;
				"second_dot"=>"4000",//ab dem 2. Behandlungstag ;
				"doctor_visit"=>"4102",//Koordinationspausch. Arzt ;
				"nurse_visit"=>"4103",//Koordinationspausch. Pflege ;
				"regional_flatrate"=>"7001" // Regionale Strukturpauschale ;
			); 
			
			return $products;
		}
		
		public function rlp_locations(){
			
			$locations = array(
				//privater Haushalt 
				"private_house" => array(
						"dta_digits_1_2" => "00",
						"location_type" => array(//  privater Haushalt 
							"5", // Zu Hause
							"6" // bei Kontaktperson
						)	
				),
				// vollst. Pflegeeinrichtung 
				"complete_care_facility" => array(
						"dta_digits_1_2" => "10",
						"location_type" => array(//vollst. Pflegeeinrichtung 
							"9", //Kurzzeitpflege
							"3" //Pflegeheim
						)
				),
				// teilst. Pflegeeinrichtung 
				"partial_care_facility" => array(
						"dta_digits_1_2" => "20",
						"location_type" => array(//teilst. Pflegeeinrichtung
							"4", //Altenheim
							"8" // betreutes Wohnen
						)
				),
				// stat. Hospiz 
				"hospiz_location" => array(
						"dta_digits_1_2" => "30",
						"location_type" => array(//stat. Hospiz 
							"2" //Hospiz
						)
				),
				// sonst. Ort 	
				"other_locations" =>array(
						"dta_digits_1_2" => "60",
						"location_type" => array(//sonst. Ort 
							"0" // Sonstige
						)
				)			
			);
			
			return $locations;
		}
		
		public function rlp_products_default_prices(){
 		
			$products_default_prices = array(
					// Beratung / Assessment gem. §7 Abs. 1
					"be_aasse" => array(
							"private_house"=> array(
								"price"=>"255.00",
								"dta_price"=>"255.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"1001",
							),
							"complete_care_facility"=> array(
								"price"=>"255.00",
								"dta_price"=>"255.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"1001",
							),
							
							"partial_care_facility"=> array(
								"price"=>"255.00",
								"dta_price"=>"255.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"1001",
							),
							"hospiz_location"=> array(
								"price"=>"100.00",
								"dta_price"=>"400.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"1001",
							),
							"other_locations"=> array(
								"price"=>"255.00",
								"dta_price"=>"255.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"1001",
							),
					),
					
					//Pauschale , wenn es nicht zur Versorgung mit SAPV kommt
					"sapv_flatrate" => array(
							"private_house"=> array(
								"price"=>"150.00",
								"dta_price"=>"150.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2002",
							),
							"complete_care_facility"=> array(
								"price"=>"150.00",
								"dta_price"=>"150.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2002",
							),
							
							"partial_care_facility"=> array(
								"price"=>"150.00",
								"dta_price"=>"150.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2002",
							),
							"hospiz_location"=> array(
								"price"=>"150.00",
								"dta_price"=>"150.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2002",
							),
							"other_locations"=> array(
								"price"=>"150.00",
								"dta_price"=>"150.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2002",
							),
					),
					//Am 1. Behandlungstag ;
					"first_dot" => array(
							"private_house"=> array(
								"price"=>"230.00",
								"dta_price"=>"230.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2001",
							),
							"complete_care_facility"=> array(
								"price"=>"218.00",
								"dta_price"=>"218.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2001",
							),
							
							"partial_care_facility"=> array(
								"price"=>"100.00",
								"dta_price"=>"100.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2001",
							),
							"hospiz_location"=> array(
								"price"=>"100.00",
								"dta_price"=>"100.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2001",
							),
							"other_locations"=> array(
								"price"=>"230.00",
								"dta_price"=>"230.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2001",
							),
					),
					//ab dem 2. Behandlungstag ;
					"second_dot" => array(
							"private_house"=> array(
								"price"=>"115.00",
								"dta_price"=>"115.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2001",
							),
							"complete_care_facility"=> array(
								"price"=>"109.00",
								"dta_price"=>"109.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2001",
							),
							
							"partial_care_facility"=> array(
								"price"=>"109.00",
								"dta_price"=>"109.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2001",
							),
							"hospiz_location"=> array(
								"price"=>"50.00",
								"dta_price"=>"50.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2001",
							),
							"other_locations"=> array(
								"price"=>"115.00",
								"dta_price"=>"115.00",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"2001",
							),
					),
					
					//Koordinationspausch. Arzt;
					"doctor_visit" => array(
							"private_house"=> array(
								"price"=>"4.00",
								"dta_price"=>"4.00",
								"dta_digits_3_4"=>"20",
								"dta_digits_7_10"=>"4102",
							),
							"complete_care_facility"=> array(
								"price"=>"4.00",
								"dta_price"=>"4.00",
								"dta_digits_3_4"=>"20",
								"dta_digits_7_10"=>"4102",
							),
							
							"partial_care_facility"=> array(
								"price"=>"4.00",
								"dta_price"=>"4.00",
								"dta_digits_3_4"=>"20",
								"dta_digits_7_10"=>"4102",
							),
							"hospiz_location"=> array(
								"price"=>"4.00",
								"dta_price"=>"4.00",
								"dta_digits_3_4"=>"20",
								"dta_digits_7_10"=>"4102",
							),
							"other_locations"=> array(
								"price"=>"4.00",
								"dta_price"=>"4.00",
								"dta_digits_3_4"=>"20",
								"dta_digits_7_10"=>"4102",
							),
					),
					
					// Koordinationspausch. Pflege;
					"nurse_visit" => array(
							"private_house"=> array(
								"price"=>"2.00",
								"dta_price"=>"2.00",
								"dta_digits_3_4"=>"30",
								"dta_digits_7_10"=>"4103",
							),
							"complete_care_facility"=> array(
								"price"=>"2.00",
								"dta_price"=>"2.00",
								"dta_digits_3_4"=>"30",
								"dta_digits_7_10"=>"4103",
							),
							
							"partial_care_facility"=> array(
								"price"=>"2.00",
								"dta_price"=>"2.00",
								"dta_digits_3_4"=>"30",
								"dta_digits_7_10"=>"4103",
							),
							"hospiz_location"=> array(
								"price"=>"2.00",
								"dta_price"=>"2.00",
								"dta_digits_3_4"=>"30",
								"dta_digits_7_10"=>"4103",
							),
							"other_locations"=> array(
								"price"=>"2.00",
								"dta_price"=>"2.00",
								"dta_digits_3_4"=>"30",
								"dta_digits_7_10"=>"4103",
							),
					),
					
					// Regionale Strukturpauschale 
					"regional_flatrate" => array(
							"private_house"=> array(
								"price"=>"106.51",
								"dta_price"=>"106.51",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"7001",
							),
							"complete_care_facility"=> array(
								"price"=>"106.51",
								"dta_price"=>"106.51",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"7001",
							),
							
							"partial_care_facility"=> array(
								"price"=>"106.51",
								"dta_price"=>"106.51",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"7001",
							),
							"hospiz_location"=> array(
								"price"=>"106.51",
								"dta_price"=>"106.51",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"7001",
							),
							"other_locations"=> array(
								"price"=>"106.51",
								"dta_price"=>"106.51",
								"dta_digits_3_4"=>"10",
								"dta_digits_7_10"=>"7001",
							),
					)
					
			);
			
			return $products_default_prices;	
		}
		
		
		public function rlp_sapvtypes(){

			$sapv_types = array(
					"be" => array(
						"dta_digits_5_6"=>"10"
					),
					"ko" => array(
						"dta_digits_5_6"=>"20"
					),
					"beko" => array(
						"dta_digits_5_6"=>"30"
					),
					"tv" => array(
						"dta_digits_5_6"=>"40"
					),
					"vv" => array(
						"dta_digits_5_6"=>"50"
					)
			);
			
			return $sapv_types;
		}
		
		//TODO-2997 Ancuta 11.03.2020 added extra param client
		public function get_rlp_invoiced_sapvs($ipids,$clientid = false)
		{
			if(empty($ipids)){
				return false;
			}
		
			// TODO-1961 - Ancuta 12.12.2018
			// get storno ids -
			$storno_arr = Doctrine_Query::create()
			->select("*")
			->from('RlpInvoices')
			->whereIn("ipid", $ipids);
			if($clientid){
			    $storno_arr->andwhere('client = ?',$clientid);
			}
			
			$storno_arr->andwhere('storno = "1"')
			->andwhere('isdelete = "0"');
			$storno_arr_res = $storno_arr->fetchArray();
			// TODO-3711 Ancuta 06.01.2021 - changed the way stornos are taken 
			$storno_ids = array();
			if(!empty($storno_arr_res)){
			    foreach($storno_arr_res as $inv => $sinv){
			        $storno_ids[] = $sinv['record_id'];
			    }
			}
		
		    //--
			
			$invoiced_sapvs = Doctrine_Query::create()
			->select("*")
			->from('RlpInvoices')
			->whereIn("ipid", $ipids);
			if($clientid){
			    $invoiced_sapvs->andwhere('client = ?',$clientid);
			}
			$invoiced_sapvs->andwhere('storno = "0"')
			->andwhere('isdelete = "0"');
			// TODO-1961 - Ancuta 12.12.2018
			if(!empty($storno_ids)){
                $invoiced_sapvs->andwhereNotIn('id',$storno_ids);
			}
			// --
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
		
		
		public function getRlpInvoices($invoice, $status = false)
		{
			$rp_invoices_items = new RlpInvoiceItems();

			$invoices = Doctrine_Query::create()
			
				->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
				->from('RlpInvoices')
// 				->where("id='" . $invoice . "'")
				->whereIn("id", $invoice)
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
				$invoice_items = $rp_invoices_items->getInvoicesItems($invoices_res[0]['id']);

				
				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
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
				->from('RlpInvoices')
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
				->from('RlpInvoices')
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
			$find_invoice = Doctrine::getTable('RlpInvoices')->findOneById($invoiceid);
			$found_invoice = $find_invoice->toArray();

			$has_storno = self::has_storno($invoiceid);

			if($found_invoice && !$has_storno)
			{
			    // get the new invoice number
			    // ISPC-2171 
			    // Comment added on 12.06.2018 
			    // c) a Storno is yet created with the same number as the invoice. Can you give the storno an own "invoice number"? so storno from invoice number 1234 is maybe 1235
			    $rp_invoice_number =  self::get_next_invoice_number($found_invoice['client']);
			    $prefix = $rp_invoice_number['prefix'];
			    $invoicenumber = $rp_invoice_number['invoicenumber'];
			    
				$inv = new RlpInvoices();
				$inv->invoice_start = $found_invoice['invoice_start'];
				$inv->invoice_end = $found_invoice['invoice_end'];
				$inv->start_active = $found_invoice['start_active'];
				$inv->end_active = $found_invoice['end_active'];
				$inv->start_sapv = $found_invoice['start_sapv'];
				$inv->end_sapv = $found_invoice['end_sapv'];
				$inv->sapv_approve_date = $found_invoice['sapv_approve_date'];
				$inv->sapv_approve_nr = $found_invoice['sapv_approve_nr'];
				$inv->ipid = $found_invoice['ipid'];
				$inv->client = $found_invoice['client'];
				
				//added for ISPC-2532 Carmen 14.02.2020 to be the same as all the others invoices
				if($found_invoice['client'] != 0)
				{
					$inv->prefix = $prefix;
					$inv->invoice_number = $invoicenumber;
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
			$del_storno_invoice = Doctrine::getTable('RlpInvoices')->findOneById($invoiceid);
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
				->from('RlpInvoices')
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
				->from('RlpInvoices')
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
			$invoices = new RlpInvoices();
			$client_data = $client->getClientDataByid($clientid);

			$collective = '0';
			if($client_data[0]['invoice_number_type'] == '1')
			{
				$collective = '1';
			}
			$required_shortcuts = array('rlp_invoice');
			$invoice_settings_arr = $invoice_settings->getClientInvoiceSettings($clientid, $required_shortcuts, $collective);


			if($client_data[0]['invoice_number_type'] == '0') //individual type
			{
				//Rp invoice
				$rlp_invoice_number = $invoices->get_highest_invoice_number($clientid, $invoice_settings_arr['rlp_invoice']['invoice_prefix']);
				if($rlp_invoice_number)
				{
					if($rlp_invoice_number['invoice_number'] >= $invoice_settings_arr['rlp_invoice']['invoice_start'] && $rlp_invoice_number['prefix'] == $invoice_settings_arr['rlp_invoice']['invoice_prefix'])
					{
						$prefix = $invoice_settings_arr['rlp_invoice']['invoice_prefix'];
						$i_number = $rlp_invoice_number['invoice_number'];
						$i_number++;
					}
					else
					{
						$prefix = $invoice_settings_arr['rlp_invoice']['invoice_prefix'];
						$i_number = $invoice_settings_arr['rlp_invoice']['invoice_start'];
						if($invoice_settings_arr['rlp_invoice']['invoice_start'] == '0')
						{
							$i_number++;
						}
					}
				}
				else
				{
					if(strlen($invoice_settings_arr['rlp_invoice']['invoice_start']) > 0)
					{
						$prefix = $invoice_settings_arr['rlp_invoice']['invoice_prefix'];
						$i_number = $invoice_settings_arr['rlp_invoice']['invoice_start'];
						if($invoice_settings_arr['rlp_invoice']['invoice_start'] == '0')
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
			$invoices = new RlpInvoices();
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
				->from('RlpInvoices')
				->where("client='" . $clientid . "'")
				->andwhere('storno = "1"');
			$storno_invoices_res = $storno_invoices->fetchArray();

			if($storno_invoices_res)
			{
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
				->from('RlpInvoices')
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

		
		
		
		public function get_multiple_rlp_invoices($invoice_ids = false)
		{
			$rp_invoiceitems = new RlpInvoiceItems();
		
			if ($invoice_ids === false || !is_array($invoice_ids) || empty($invoice_ids)){
				return false;
			}
			$invoices = Doctrine_Query::create()
			->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
			->from('RlpInvoices')
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
		
		
		
		
		
		
		
		
		public function get_invoices($invoices_ids,$allow_archiv = false)
		{
			if(empty($invoices_ids)){
				return false;
			}
			
			$rlp_invoice_items = new RlpInvoiceItems();
			
			$invoices = Doctrine_Query::create()
			->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
			->from('RlpInvoices')
			->whereIn("id", $invoices_ids)
			->andWhere('isdelete = "0"');
			if(!$allow_archiv){
                $invoices->andWhere('isarchived = "0"');
			}
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
				$invoice_items = $rlp_invoice_items->getInvoicesItems($invoices_ids);
		
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