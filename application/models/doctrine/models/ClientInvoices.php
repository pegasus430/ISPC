<?php

	Doctrine_Manager::getInstance()->bindComponent('ClientInvoices', 'SYSDAT');

	class ClientInvoices extends BaseClientInvoices {

		public function getInvoices($ipidsArray, $clientid, $variousData, $offset = false, $pagelimit = false, $orderBy = false, $tab = false, $direction = false)
		{
			$inv = new ClientInvoices();
			$all_invoices = $inv->getClientInvoices($clientid);
			
			//exclude both storno and storned invoice
			$excluded_storned_items[] = '99999999999999';
			foreach($all_invoices as $k => $invoice_item)
			{
			    if($invoice_item['storno'] == "1" && $invoice_item['status'] != "3") //TODO-4024 Ancuta 09.04.2021
				{
					$excluded_storned_items[] = $invoice_item['id'];
					$excluded_storned_items[] = $invoice_item['record_id'];
				}
			}

			$invoices = Doctrine_Query::create()
				->select("*, IF(completedDate = '0000-00-00 00:00:00', create_date, IF(completedDate = '1970-01-01 01:00:00', create_date, completedDate)) as completed_date_sort")
				->from('ClientInvoices ci')
				->Where("clientid='" . $clientid . "'")
// 				->andWhere('ci.id NOT IN (SELECT cis.record_id from ClientInvoices cis where cis.clientid=?)', $clientid) // this is not needed here - they are removed at display in controller
				->andWhere('isDelete = 0');

			if(count($ipidsArray) > 1)
			{
				$invoices->andWhereIn('ipid', $ipidsArray);
			}

			if(count($variousData) > 0)
			{
				foreach($variousData as $field => $value)
				{
					if($field == "create_date")
					{
						$invoices->andWhere($field . ' between "' . date("Y-m-d H:i:s", strtotime($value)) . '" AND "' . date("Y-m-d H:i:s", strtotime("+1 day", strtotime($value))) . '"');
					}
					else if($field == "rnummer")
					{
						$invoices->andWhere($field . " LIKE '%" . $value . "%'     OR  CONCAT(prefix,rnummer)  LIKE  '%" . $value . "%'  "); // include prefix in search - if the search contains prefix and number
					}
					else if($field == "create_date_journal")
					{
						$invoices->andWhere('create_date' . ' between "' . date("Y-m-d H:i:s", strtotime($value['start_date'])) . '" AND "' . date("Y-m-d H:i:s", strtotime($value['end_date'])) . '"');
					}
					else
					{
						$invoices->andWhere($field . " ='" . $value . "'");
					}
				}
			}

			if($tab !== false)
			{
				switch($tab)
				{
					case "paid":
						$invoices->andWhere("paidDate != '0000-00-00 00:00:00'"); //not paid
						$invoices->andWhere("status =2");
						$invoices->andWhere("storno  = 0");
						$invoices->andWhereNotIn('id', $excluded_storned_items);
						break;

					case "canceled":
						$invoices->andWhere("status =3");
						$invoices->andWhereNotIn('id', $excluded_storned_items);
						break;

					case "draft":
						$invoices->andWhere("status =4");
						$invoices->andWhereNotIn('id', $excluded_storned_items);
						break;

					case "open":
						$invoices->andWhereIn("status", array("0", "1"));
						$invoices->andWhere("storno  = 0");
						$invoices->andWhereNotIn('id', $excluded_storned_items);
						break;

					case "overdue":
//						$invoices->andWhere("(paidDate = '0000-00-00 00:00:00' and dueDate <  NOW()) OR paidDate > dueDate");
						$invoices->andWhereIn("status", array("0", "1"));
						$invoices->andWhere("(paidDate = '0000-00-00 00:00:00' and dueDate <  NOW())");
						$invoices->andWhere("storno  = 0");
						$invoices->andWhereNotIn('id', $excluded_storned_items);
						break;

					default :

						break;
				}
			}


			if($orderBy !== false)
			{
				if($orderBy == 'rnummer')
				{
					$invoices->orderBy('rnummer ' . $direction);
//					$invoices->orderBy('CAST(LEFT(rnummer,LOCATE(" ",trim(rnummer))+1) AS DECIMAL(10,2)) ' . $direction);
				}
				else
				{
					$invoices->orderBy($orderBy . " " . $direction);
				}
			}

			if($offset !== false && $pagelimit !== false)
			{
				$invoices->limit($pagelimit);
				$invoices->offset($offset);
			}
			
			if($_REQUEST['show_debugq'] == 1){
				echo $invoices->getSqlQuery();
			}
			
			$invoicesResults = $invoices->fetchArray();

			return $invoicesResults;
		}

		public function getClientInvoices($clientid, $ipid = false)
		{
			$invoices = Doctrine_Query::create()
				->select("*, IF(completedDate = '0000-00-00 00:00:00', create_date, IF(completedDate = '1970-01-01 01:00:00', create_date, completedDate)) as completed_date_sort")
				->from('ClientInvoices ci')
				->Where("ci.clientid='" . $clientid . "'");
			if($ipid !== false)
			{
				$invoices->andWhere("ipid='" . $ipid . "'");
			}

			$invoices->andWhere("isDelete='0'");
			$invarray = $invoices->fetchArray();

//			print_r($invoices->getSqlQuery());
			return $invarray;
		}

		public function getClientDeletedInvoices($clientid, $ipid = false)
		{
			$invoices = Doctrine_Query::create()
				->select("*, IF(completedDate = '0000-00-00 00:00:00', create_date, IF(completedDate = '1970-01-01 01:00:00', create_date, completedDate)) as completed_date_sort")
				->from('ClientInvoices')
				->Where("clientid='" . $clientid . "'");
			if($ipid !== false)
			{
				$invoices->andWhere("ipid='" . $ipid . "'");
			}

			$invoices->andWhere("isDelete='1'");

			$invarray = $invoices->fetchArray();

			return $invarray;
		}

		public function getInvoice($invoiceid, $clientid)
		{
			$invoices = Doctrine_Query::create()
				->select("*, IF(completedDate = '0000-00-00 00:00:00', create_date, IF(completedDate = '1970-01-01 01:00:00', create_date, completedDate)) as completed_date_sort")
				->from('ClientInvoices')
				->Where("clientid='" . $clientid . "'")
				->andWhere("id='" . $invoiceid . "'")
				->andWhere("isDelete='0'");
			$invarray = $invoices->fetchArray();

			return $invarray;
		}

		public function get_highest_invoice_number($clientid, $prefix = false, $all = false)
		{
			$invoice_number = Doctrine_Query::create()
				->select("*, cast(rnummer as decimal) as dec_invoice_number")
				->from('ClientInvoices')
				->where("clientid='" . $clientid . "'")
				->andWhere('rnummer REGEXP "^[0-9]+$"')
				->andWhere('isdelete = 0')
				//->andWhere('storno = "0"') ISPC-2532 create new number for storno invoices Carmen 13.02.2020
// 				->orderBy('id DESC')
				->orderBy('dec_invoice_number DESC')
				->limit('1');
			if($prefix || strlen($prefix)) // TODO-2633 Ancuta - issue when prefix is 0 
			{
				$invoice_number->andWhere('prefix = "' . $prefix . '"');
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

		public function get_all_client_invoices($ipids, $clientid, $filter_data, $allowed_invoice_types, $offset = false, $pagelimit = false, $order_by = false, $direction = 'ASC', $remove_drafts = false,$users)
		{
			$Tr = new Zend_View_Helper_Translate();
			//maps
			$dbs = array(
				'by_invoice' => 'client_invoices',
				'bayern_invoice' => 'bayern_invoices',
				'nie_patient_invoice' => 'hi_invoice',
				'nie_user_invoice' => 'u_invoice',
				'bre_sapv_invoice' => 'bre_invoices',
				'bre_hospiz_sapv_invoice' => 'bre_hospiz_invoices',
				'he_invoice' => 'he_invoice',
				'bw_sapv_invoice' => 'bw_invoice',
				'bw_sgbv_invoice' => 'sgbv_invoice',
				'bw_mp_invoice' => 'medipumps_invoice',
				'bw_sgbxi_invoice' => 'sgbxi_invoice',
				'sh_invoice' => 'sh_invoice',
				'new_bayern_invoice' => 'bayern_invoices_new',
				'bra_invoice' => 'bra_invoice',
				'sh_internal_invoice' => 'sh_internal_invoice',
					
				//TODO-1425 :: 08.03.2018 	
				'bw_sapv_invoice_new' => 'bw_invoice_new',
				'bw_medipumps_invoice' => 'medipumps_invoice_new',
				'hospiz_invoice' => 'hospiz_invoice', 
				'rlp_invoice' => 'rlp_invoice',
			     
			    // ISPC-2214
				'bre_kinder_invoice' => 'invoice_system', 
			    // ISPC-2257+ ISPC-2272
				'sh_shifts_internal_invoice' => 'sh_shifts_internal_invoice',
			    // ISPC-2286
				'nr_invoice' => 'invoice_system', 
                //ISPC-2461
				'demstepcare_invoice' => 'invoice_system',

			    //ISPC-2263 Ancuta 14.05.2021
			    'rp_invoice'=>'rp_invoice',
			    //--
			);
			
			if(!$allowed_invoice_types)
			{
				return false;
			}

			$remove_drafts_sql = "";
			if($remove_drafts)
			{
				$remove_drafts_sql .=' AND status != "1" ';
			}

			foreach($dbs as $k_arr => $u_arr)
			{
				if(in_array($k_arr, $allowed_invoice_types))
				{
					$used_tables[] = $u_arr;
				}
			}

			//items filter mapping
			$items_filter_fields = array(
			    /*'client_invoices' => 'create_date',
				'bayern_invoices' => array('start' => 'start_active', 'end' => 'end_active'),
				'bre_invoices' => array('start' => 'start_active', 'end' => 'end_active'),
				'bre_hospiz_invoices' => array('start' => 'start_active', 'end' => 'end_active'),
				'he_invoice' => array('start' => 'start_active', 'end' => 'end_active'),
				'bw_invoice' => array('start' => 'start_active', 'end' => 'end_active'),
				'sgbv_invoice' => array('start' => 'start_active', 'end' => 'end_active'),
				'medipumps_invoice' => array('start' => 'start_active', 'end' => 'end_active'),
				'sgbxi_invoice' => array('start' => 'start_active', 'end' => 'end_active'),
				'hi_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
				'u_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
				'sh_invoice' => array('start' => 'start_active', 'end' => 'end_active'),
				'bayern_invoices_new' => array('start' => 'start_active', 'end' => 'end_active'),
				'bra_invoice' => array('start' => 'start_active', 'end' => 'end_active'),
				'sh_internal_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
				'rlp_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
			    'bw_invoice_new' => array('start' => 'start_active', 'end' => 'end_active'),
			    
			    'invoice_system' => array('start' => 'start_active', 'end' => 'end_active'), */
			    
				'client_invoices' => 'create_date',
				'bayern_invoices' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'bre_invoices' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'bre_hospiz_invoices' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'he_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'bw_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'sgbv_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'medipumps_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'sgbxi_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'hi_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
				'u_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
				'sh_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'bayern_invoices_new' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'bra_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'sh_internal_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
				'rlp_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
			    'bw_invoice_new' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
			    'invoice_system' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
				'sh_shifts_internal_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
			    
                //TODO-3519 Ancuta 19.10.2020
			    'hospiz_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
			    //--
			    //ISPC-2263 Ancuta 14.05.2021
				'rp_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
			    //--
			    
			);

			if($used_tables)
			{
				$first_table = true;
				$sql = '';

				foreach($used_tables as $k_table => $v_table)
				{
					if($first_table)
					{
						$sql .= 'SELECT';
					}
					else
					{
						$sql .= 'UNION SELECT';
					}

					if($v_table == 'client_invoices')
					{
						$sql .= ' id,"0" as sapv,"0" as user, clientid as client,  ipid, prefix, CONCAT(prefix, rnummer) AS invoice_number, rnummer as invoice_nr, invoiceTotal as invoice_total, isdelete, record_id, storno, create_date, create_date as invoice_date, create_date as invoice_end, "' . $Tr->translate($v_table) . '" as invoice_type_translated , "' . $v_table . '" as inv_type, id as t_type, IF(completedDate = "0000-00-00 00:00:00", create_date, IF(completedDate = "1970-01-01 01:00:00", create_date, completedDate)) as completed_date
						    ,create_date as inv_start_date
						    ,create_date as inv_end_date
						    ';
					}
					else if($v_table == 'medipumps_invoice')
					{
						$sql .= ' id, "0" as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, type as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
						    ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						     ';
					}
					else if($v_table == 'sh_invoice')
					{
						$sql .= ' id, sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
						    ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						    ';
					}
					else if($v_table == 'bra_invoice')
					{
						$sql .= ' id, sapvid as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						    ';
					}
					else if($v_table == 'bayern_invoices_new')
					{
						$sql .= ' id, sapvid as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date						    
						    ';
					}
					else if($v_table == 'sh_internal_invoice')
					{
						$sql .= ' id, "0" as sapv, user, client, "0" as ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date						    
						    ';
					}
					else if($v_table == 'sh_shifts_internal_invoice')
					{
						$sql .= ' id, "0" as sapv, user, client,  ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date						    
						    
						    ';
					}
					else if($v_table == 'rlp_invoice')
					{
						$sql .= ' id, sapvid as sapv, "0" as user, client,  ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date						    
						    ';
					}
					else if($v_table == 'invoice_system')
					{
						$sql .= ' id, sapvid as sapv, "0" as user, client,  ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date						    
						    ';
					}
					//ISPC-2263 Ancuta 14.05.2021
					else if($v_table == 'rp_invoice')
					{
					    $sql .= ' id, sapv_id as sapv, "0" as user, client,  ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						    ';
					}
					//--
					else
					{
//						if($v_table == 'bw_invoice' || $v_table == 'sgbv_invoice' || $v_table == 'sgbxi_invoice')
						if($v_table != 'u_invoice')
						{
							$sql .= ' id,"0" as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                                    ,invoice_start as inv_start_date
                                    ,invoice_end as inv_end_date
							    ';
						}
						else
						{
							$sql .= ' id,"0" as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, create_date as completed_date 
                                    ,invoice_start as inv_start_date
                                    ,invoice_end as inv_end_date
							    ';
						}
					}

   					$sql .= ' FROM `' . $v_table . '` ';

					if($ipids)
					{
						$sql_ipids = 'AND ipid IN("' . implode('", "', $ipids) . '") ';
					}
					else
					{
						$sql_ipids = '';
					}

					if($users)
					{
						$sql_users = 'AND user IN("' . implode('", "', $users) . '") ';
					}
					else
					{
						$sql_users = '';
					}

					if($v_table == 'client_invoices')
					{

						$remove_drafts_ci_sql = "";
						if($remove_drafts)
						{
							$remove_drafts_ci_sql .=' AND status != "4" '; 
						}
						
						$sql .= ' WHERE isdelete = "0" ' . $remove_drafts_ci_sql . ' AND clientid ="' . $clientid . '" ' . $sql_ipids;
						$sql .= ' AND status != "3" ';
					}
					else
					{
					    if($v_table != 'sh_internal_invoice'){
    						$sql .= ' WHERE isdelete = "0" ' . $remove_drafts_sql . ' AND client ="' . $clientid . '" ' . $sql_ipids;
					    } else{
					        $sql .= ' WHERE isdelete = "0" ' . $remove_drafts_sql . ' AND client ="' . $clientid . '" ' . $sql_users;
					    }
					    
						$sql .= ' AND status != "4" ';
					}

					if(count($filter_data))
					{

						foreach($filter_data as $filter_for => $filter_values)
						{
							//TODO-1059
							$filter_for_alias = false;
							if($v_table == 'client_invoices' && $filter_for == 'completed_date')
							{
								$filter_for = 'IF(completedDate = "0000-00-00 00:00:00", create_date,	IF(completedDate = "1970-01-01 01:00:00", create_date, completedDate))';
								$filter_for_alias = true;
							}							
							// TODO-3155 Ancuta 18.05.2020 - allow sh to search after completed date 
							//if(($v_table == 'client_invoices' || $v_table == 'hi_invoice' || $v_table == 'u_invoice' || $v_table == 'bre_invoices' || $v_table == 'he_invoice' || $v_table == "sh_invoice"  || $v_table == "sh_internal_invoice" || $v_table == "bayern_invoices_new") && $filter_for == 'completed_date')
							if(($v_table == 'client_invoices' || $v_table == 'hi_invoice' || $v_table == 'u_invoice' || $v_table == 'bre_invoices' || $v_table == 'he_invoice'  || $v_table == "sh_internal_invoice" || $v_table == "bayern_invoices_new") && $filter_for == 'completed_date')
							{
								$filter_for = 'create_date';
							}

							if($v_table == 'client_invoices' && $filter_for == 'invoice_number')
							{
//								$filter_for = 'rnummer';
								$filter_for = 'LOWER(CONCAT(`prefix`, `rnummer`))';
							}
							
							if($v_table == 'client_invoices' && $filter_for == 'invoice_total')
							{
								$filter_for = 'invoiceTotal';
							}
							
							if($v_table != 'client_invoices' && $filter_for == 'invoice_number')
							{
								$filter_for = 'LOWER(CONCAT(`prefix`, `invoice_number`))';
							}

//							$sql .= ' AND ' . $filter_for . ' ';
							if($filter_for == 'completed_date' || $filter_for == 'create_date' || $filter_for == 'item_date' || $filter_for_alias)
							{
								if($filter_for == 'completed_date' || $filter_for == 'create_date' || $filter_for_alias)
								{
									$sql .= ' AND DATE(' . $filter_for . ') ';
									$sql .= ' BETWEEN DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['start_date'])) . '") AND DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['end_date'])) . '") ';
								}

								if($filter_for == 'item_date' && strlen($filter_values['item_start_date']) > '0' && strlen($filter_values['item_end_date']) > '0')
								{// ISPC-633
									//change filter for acording to the existing fields in db
									if($v_table == 'client_invoices')
									{
										$sql .= ' AND DATE(' . $items_filter_fields[$v_table] . ') ';
										$sql .= ' BETWEEN DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['item_start_date'])) . '") AND DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['item_end_date'])) . '") ';
									}
									else if(count($items_filter_fields[$v_table]) == '2')
									{
										$sql .= ' AND DATE(' . $items_filter_fields[$v_table]['end'] . ') >= DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['item_start_date'])) . '") AND DATE(' . $items_filter_fields[$v_table]['start'] . ') <= DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['item_end_date'])) . '") ';
									}
									else if(count($items_filter_fields[$v_table]) > 2) 
									{
									    $sql .= ' AND ( ( DATE(' . $items_filter_fields[$v_table]['end'] . ') >= DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['item_start_date'])) . '") AND DATE(' . $items_filter_fields[$v_table]['start'] . ') <= DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['item_end_date'])) . '") ) ';
									    $sql .= ' OR (DATE(' . $items_filter_fields[$v_table]['end2'] . ') >= DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['item_start_date'])) . '") AND DATE(' . $items_filter_fields[$v_table]['start2'] . ') <= DATE("' . date('Y-m-d H:i:s', strtotime($filter_values['item_end_date'])) . '") ) )';
									}
								}
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
									//do like (switch to case sensitive
									if(strpos('LOWER(CONCAT(', $filter_for) !== false)
									{
										$sql .= ' LIKE "%' . strtolower($filter_values[0]) . '%" ';
									}
									else
									{
										$sql .= ' LIKE "%' . $filter_values[0] . '%" ';
									}
									
								}
							}
						}
					}

					$first_table = false;
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
			}

// 		print_r($filter_data);
// 		$logininfo = new Zend_Session_Namespace('Login_Info');
// 		if($logininfo->userid == "338"){
//      		print_r($sql);
// 		}
// 		exit;
			$resultset = Doctrine_Manager::getInstance()
				->getConnection('SYSDAT')
				->getDbh()
				->query($sql)
				->fetchAll(PDO::FETCH_ASSOC);
			return $resultset;
		}

		public function create_storno_invoice($invoiceid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$find_invoice = Doctrine::getTable('ClientInvoices')->findOneByIdAndClientid($invoiceid, $clientid);
			$found_invoice = $find_invoice->toArray();

			$has_storno = self::has_storno($invoiceid);

			if($found_invoice && !$has_storno)
			{
				//ISPC-2532 create new number for storno invoice Carmen 13.02.2020
				$invoice_number = $this->get_next_invoice_number($found_invoice['clientid']);
				
				$inv = new ClientInvoices();
				$inv->userid = $found_invoice['userid'];
				$inv->clientid = $found_invoice['clientid'];
				$inv->ipid = $found_invoice['ipid'];
				$inv->epid = $found_invoice['epid'];
				
				if($found_invoice['clientid'] != 0)
				{
					$inv->prefix = $invoice_number['prefix'];
					$inv->rnummer = $invoice_number['invoicenumber'];
				}
				else
				{
					$inv->prefix = $found_invoice['prefix'];
					$inv->rnummer = $found_invoice['rnummer'];
				}
				
				$inv->status = $found_invoice['status'];
				$inv->invoiceTotal = $found_invoice['invoiceTotal'];
				$inv->invoiceVat = $found_invoice['invoiceVat'];
				$inv->isDelete = '0';
				//$inv->completedDate = $found_invoice['completedDate'];
				$inv->completedDate = date('Y-m-d H:i:s', time());        //ISPC-2532 Lore 11.11.2020
				$inv->dueDate = $found_invoice['dueDate'];
				$inv->paidDate = $found_invoice['paidDate'];
//				$inv->completed_date = $found_invoice['completed_date'];
				$inv->record_id = $invoiceid;
				$inv->storno = '1';
				$inv->save();
			}
		}

		public function del_storno_invoice($invoiceid)
		{
			$del_storno_invoice = Doctrine::getTable('ClientInvoices')->findOneById($invoiceid);
			$del_storno_invoice->isDelete = '1';
			$del_storno_invoice->save();
		}

		public function get_next_invoice_number($clientid, $temp = false)
		{
			$client = new Client();
			$invoices = new ClientInvoices();
			$invoice_settings = new InvoiceSettings();

			$client_data = $client->getClientDataByid($clientid);

			$collective = '0';
			if($client_data[0]['invoice_number_type'] == '1')
			{
				$collective = '1';
			}
			$required_shortcuts = array('by_invoice');

			$invoice_settings_arr = $invoice_settings->getClientInvoiceSettings($clientid, $required_shortcuts, $collective);


			if($client_data[0]['invoice_number_type'] == '0') //individual type
			{

				$client_invoice_rnummer = $invoices->get_highest_invoice_number($clientid, $invoice_settings_arr['by_invoice']['invoice_prefix']);

				if($client_invoice_rnummer)
				{
					if($client_invoice_rnummer['rnummer'] >= $invoice_settings_arr['by_invoice']['invoice_start'] && $client_invoice_rnummer['prefix'] == $invoice_settings_arr['by_invoice']['invoice_prefix'])
					{
						$prefix = $invoice_settings_arr['by_invoice']['invoice_prefix'];
						$i_number = $client_invoice_rnummer['rnummer'];
						$i_number++;
					}
					else
					{
						$prefix = $invoice_settings_arr['by_invoice']['invoice_prefix'];
						$i_number = $invoice_settings_arr['by_invoice']['invoice_start'];
						if($invoice_settings_arr['by_invoice']['invoice_start'] == '0')
						{
							$i_number++;
						}
					}
				}
				else
				{
					if(strlen($invoice_settings_arr['by_invoice']['invoice_start']) > 0)
					{
						$prefix = $invoice_settings_arr['by_invoice']['invoice_prefix'];
						$i_number = $invoice_settings_arr['by_invoice']['invoice_start'];
						if($invoice_settings_arr['by_invoice']['invoice_start'] == '0')
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
//			print_r($invoice_nr_arr);
			return $invoice_nr_arr;
		}

		private function generate_temp_invoice_number($clientid)
		{
			$invoices = new ClientInvoices();
			$temp_prefix = 'TEMP_';
			$high_inv_nr = $invoices->get_highest_invoice_number($clientid, $temp_prefix);


			if($high_inv_nr)
			{
				$high_inv_nr['rnummer'] ++;
				$inv_nr = $high_inv_nr['rnummer'];
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
				->from('ClientInvoices')
				->where("clientid='" . $clientid . "'")
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
				->from('ClientInvoices')
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
		
		public function get_invoices($invoices_ids, $clientid)
		{
			if(empty($invoices_ids)){
				return false;
			}
		
			$cl_invoice_items = new InvoiceItems();
		
			$invoices = Doctrine_Query::create()
			->select("*, IF(completedDate = '0000-00-00 00:00:00', create_date, IF(completedDate = '1970-01-01 01:00:00', create_date, completedDate)) as completed_date_sort")
			->from('ClientInvoices')
			->whereIn("id", $invoices_ids)
			->andWhere("clientid=?", $clientid)
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
				$invoice_items = $cl_invoice_items->getMultipleInvoiceItems($invoices_ids, $clientid);
		
				foreach($invoices_res as $k_invoice_res => $v_invoice_res)
				{
					$master_data['invoices_users'][] = $v_invoice_res['userid'];
		
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


		public function getall_generated_invoices($ipids = array(), $clientid=false, $allowed_invoice_types=false, $inv_period = array(), $remove_drafts = false, $only_drafts = false, $users= false)
		{
		    
		    $Tr = new Zend_View_Helper_Translate();
		    //maps
		    $dbs = array(
		        'by_invoice' => 'client_invoices',
		        'bayern_invoice' => 'bayern_invoices',
		        'nie_patient_invoice' => 'hi_invoice',
		        'nie_user_invoice' => 'u_invoice',
		        'bre_sapv_invoice' => 'bre_invoices',
		        'bre_hospiz_sapv_invoice' => 'bre_hospiz_invoices',
		        'he_invoice' => 'he_invoice',
		        'bw_sapv_invoice' => 'bw_invoice',
		        'bw_sgbv_invoice' => 'sgbv_invoice',
		        'bw_mp_invoice' => 'medipumps_invoice',
		        'bw_sgbxi_invoice' => 'sgbxi_invoice',
		        'sh_invoice' => 'sh_invoice',
// 		        'new_bayern_invoice' => 'bayern_invoices_new',
		        'bayern_sapv_invoice' => 'bayern_invoices_new',
		        'bra_invoice' => 'bra_invoice',
		        'sh_internal_invoice' => 'sh_internal_invoice',

		        	
		        //TODO-1425 :: 08.03.2018
		        'bw_sapv_invoice_new' => 'bw_invoice_new',
		        'bw_medipumps_invoice' => 'medipumps_invoice_new',
		        'hospiz_invoice' => 'hospiz_invoice',
		        'rlp_invoice' => 'rlp_invoice',
		
		        // ISPC-2214
		        'bre_kinder_invoice' => 'invoice_system',
		        // ISPC-2286
		        'nr_invoice' => 'invoice_system',
		        // ISPC-2257+ ISPC-2272
		        'sh_shifts_internal_invoice' => 'sh_shifts_internal_invoice',
		        //ISPC-2461
		        'demstepcare_invoice' => 'invoice_system',
		        
		        //ISPC-2263 Ancuta 14.05.2021
		        'rp_invoice' => 'rp_invoice',//ISPC-2312 Ancuta 09.12.2020
		    );
		    	
		    if(!$allowed_invoice_types)
		    {
		        return false;
		    }
		
		    $remove_drafts_sql = "";
		    if($remove_drafts)
		    {
		        $remove_drafts_sql .=' AND status != "1" ';
		    }
		
		    $only_drafts_sql = "";
		    if($only_drafts)
		    {
		        $only_drafts_sql .=' AND status = "1" ';
		    }
		
		    $dates_sql = '';
		    if(!empty($inv_period)){
		        
		        $dates_sql .=' AND date(invoice_start) = "'.$inv_period['start'].'" AND date(invoice_end) = "'.$inv_period['end'].'" ';
		    }
		    
		    
		    foreach($dbs as $k_arr => $u_arr)
		    {
		        if(in_array($k_arr, $allowed_invoice_types))
		        {
		            $used_tables[] = $u_arr;
		        }
		    }
		    if($used_tables)
		    {
		        $first_table = true;
		        $sql = '';
		
		        foreach($used_tables as $k_table => $v_table)
		        {
		            if($first_table)
		            {
		                $sql .= 'SELECT';
		            }
		            else
		            {
		                $sql .= 'UNION SELECT';
		            }
		
		            if($v_table == 'client_invoices')
		            {
		                $sql .= ' id,"0" as sapv,"0" as user, clientid as client,  ipid, prefix, CONCAT(prefix, rnummer) AS invoice_number, rnummer as invoice_nr, invoiceTotal as invoice_total, isdelete, record_id, storno, create_date, create_date as invoice_date, create_date as invoice_end, "' . $Tr->translate($v_table) . '" as invoice_type_translated , "' . $v_table . '" as inv_type, id as t_type, IF(completedDate = "0000-00-00 00:00:00", create_date, IF(completedDate = "1970-01-01 01:00:00", create_date, completedDate)) as completed_date,status';
		            }
		            else if($v_table == 'medipumps_invoice')
		            {
		                $sql .= ' id, "0" as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start, invoice_start, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, type as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date ,status';
		            }
		            else if($v_table == 'sh_invoice')
		            {
		                $sql .= ' id, sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date,status';
		            }
		            else if($v_table == 'bra_invoice')
		            {
		                $sql .= ' id, sapvid as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date,status';
		            }
		            else if($v_table == 'bayern_invoices_new')
		            {
		                // TOD0-2315  16.07.2019
// 		                $sql .= ' id, sapvid as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date,status';
		                $sql .= ' id, sapvid as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "bayern_sapv_invoice" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date,status';
		            }
		            else if($v_table == 'sh_internal_invoice')
		            {
		                $sql .= ' id, "0" as sapv, user, client, "0" as ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date,status';
		            }
		            else if($v_table == 'sh_shifts_internal_invoice')
		            {
		                $sql .= ' id, "0" as sapv, user, client,        ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date,status';
		            }
		            
		            else if($v_table == 'rlp_invoice')
		            {
		                $sql .= ' id, sapvid as sapv, "0" as user, client,  ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date,status';
		            }
		            else if($v_table == 'invoice_system')
		            {
		                $sql .= ' id, sapvid as sapv, "0" as user, client,  ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, invoice_type as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date,status';
		            }
		            else
		            {
		                if($v_table != 'u_invoice')
		                {
		                    $sql .= ' id,"0" as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date,status';
		                }
		                else
		                {
		                    $sql .= ' id,"0" as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, create_date as completed_date ,status';
		                }
		            }
		
		            $sql .= ' FROM `' . $v_table . '` ';
		
		            if($ipids)
		            {
		                $sql_ipids = 'AND ipid IN("' . implode('", "', $ipids) . '") ';
		            }
		            else
		            {
		                $sql_ipids = '';
		            }
		
		            if($users)
		            {
		                $sql_users = 'AND user IN("' . implode('", "', $users) . '") ';
		            }
		            else
		            {
		                $sql_users = '';
		            }
		
		            if($v_table == 'client_invoices')
		            {
		
		                $remove_drafts_ci_sql = "";
		                if($remove_drafts)
		                {
		                    $remove_drafts_ci_sql .=' AND status != "4" ';
		                }
		                
		                $only_drafts_ci_sql = "";
		                if($only_drafts)
		                {
		                    $only_drafts_ci_sql .=' AND status = "4" ';
		                }
		                
		
		                $sql .= ' WHERE isdelete = "0" ' . $remove_drafts_ci_sql . ' '.$only_drafts_ci_sql.' AND clientid ="' . $clientid . '" ' . $sql_ipids;
		                $sql .= ' AND status != "3" ';
		            }
		            else
		            {
		                if($v_table != 'sh_internal_invoice' && $v_table!= "sh_shifts_internal_invoice"){
		                    $sql .= ' WHERE isdelete = "0" ' . $remove_drafts_sql . '  '.$dates_sql.' '.$only_drafts_sql.' AND client ="' . $clientid . '" ' . $sql_ipids;
		                } else{
		                    $sql .= ' WHERE isdelete = "0" ' . $remove_drafts_sql . '  '.$dates_sql.' '.$only_drafts_sql.' AND client ="' . $clientid . '" ' . $sql_users;
		                }
		                	
		                $sql .= ' AND status != "4" ';
		            }
	 
		
		            $first_table = false;
		        }
		
 
		        $order_by = 'completed_date DESC';
		        $sql .= ' ORDER BY ' . $order_by . ' ';
		    }
		    
// 		    echo $sql;
		
		    $resultset = Doctrine_Manager::getInstance()
		    ->getConnection('SYSDAT')
		    ->getDbh()
		    ->query($sql)
		    ->fetchAll(PDO::FETCH_ASSOC);
		    return $resultset;
		}		
		
		
		
		
		public function delete_drafts($ipids = array(), $clientid, $invoices = array(), $invoice_type, $userids = array())
		{
		    $invtype2model = array(
		        'by_invoice' => 'ClientInvoices',
		        'bayern_invoice' => 'BayernInvoices',
		        'nie_patient_invoice' => 'HiInvoices',
		        'nie_user_invoice' => 'UserInvoices',
		        'bre_sapv_invoice' => 'BreInvoices',
		        'bre_hospiz_sapv_invoice' => 'BreHospizInvoices',
		        'he_invoice' => 'HeInvoices',
		        'bw_sapv_invoice' => 'BwInvoices',
		        'bw_sgbv_invoice' => 'SgbvInvoices',
		        'bw_mp_invoice' => 'MedipumpsInvoices',
		        'bw_sgbxi_invoice' => 'SgbxiInvoices',
		        'sh_invoice' => 'ShInvoices',
		        'bayern_sapv_invoice' => 'BayernInvoicesNew',
		        'bra_invoice' => 'BraInvoices',
		        'sh_internal_invoice' => 'ShInternalInvoices',
		        'bw_sapv_invoice_new' => 'BwInvoicesNew',
		        'bw_medipumps_invoice' => 'MedipumpsInvoicesNew',
		        'hospiz_invoice' => 'HospizInvoices',
		        'rlp_invoice' => 'RlpInvoices',
		        'bre_kinder_invoice' => 'InvoiceSystem',
		        'nr_invoice' => 'InvoiceSystem',// ISPC-2286
		        'demstepcare_invoice' => 'InvoiceSystem',// ISPC-2461

		    );
		    
		    if(empty($invoices))
		    {
		        return false;
		    }
 
		    if(!$invoice_type)
		    {
		        return false;
		    }
		    
		    if(!empty($invtype2model[$invoice_type])){
		        
		        if($v_table == 'ClientInvoices')
		        {
		            $delInvoices = Doctrine_Query::create()
		            ->update("ClientInvoices")
		            ->set('status', "3")
		            ->whereIn('id', $iids)
		            ->andWhere('isDelete =0');
		            $d = $delInvoices->execute();
		        }
		        else if($invtype2model[$invoice_type] == 'InvoiceSystem')
		        {
		            $update_invoice = Doctrine_Query::create()
		            ->update($invtype2model[$invoice_type])
		            ->set('status', '4')
		            ->set('isdelete', '1')
		            ->whereIn('id ',$invoices)
		            ->andWhere('invoice_type =?',$invoice_type);
		            $update_invoice->execute();
		            
		        }
		        else
		        {
                    $update_invoice = Doctrine_Query::create()
    		        ->update($invtype2model[$invoice_type])
    		        ->set('status', '4')
    		        ->set('isdelete', '1')
    		        ->whereIn('id ',$invoices);
                    
                    if($ipids){
                        $update_invoice->whereIn('ipid ',$ipids);
                    }
                    if($userids && $invtype2model[$invoice_type] == 'ShInternalInvoices'){
                        $update_invoice->whereIn('user ',$userids);
                    }
    		        $update_invoice->execute();
		        }
		        
		      return true;
		    }
 
		}
		
		/**
		 * ISPC-2171 Lore 10.06.2020
		 * @param  $allowed_invoice_types
		 * @param  $stornoids
		 * @return boolean|array
		 */
		public function get_all_storno_nr_client_invoices($allowed_invoice_types, $stornoids )
		{
		    $Tr = new Zend_View_Helper_Translate();
		    //maps
		    $dbs = array(
		        'by_invoice' => 'client_invoices',
		        'bayern_invoice' => 'bayern_invoices',
		        'nie_patient_invoice' => 'hi_invoice',
		        'nie_user_invoice' => 'u_invoice',
		        'bre_sapv_invoice' => 'bre_invoices',
		        'bre_hospiz_sapv_invoice' => 'bre_hospiz_invoices',
		        'he_invoice' => 'he_invoice',
		        'bw_sapv_invoice' => 'bw_invoice',
		        'bw_sgbv_invoice' => 'sgbv_invoice',
		        'bw_mp_invoice' => 'medipumps_invoice',
		        'bw_sgbxi_invoice' => 'sgbxi_invoice',
		        'sh_invoice' => 'sh_invoice',
		        'new_bayern_invoice' => 'bayern_invoices_new',
		        'bra_invoice' => 'bra_invoice',
		        'sh_internal_invoice' => 'sh_internal_invoice',
		        
		        //TODO-1425 :: 08.03.2018
		        'bw_sapv_invoice_new' => 'bw_invoice_new',
		        'bw_medipumps_invoice' => 'medipumps_invoice_new',
		        'hospiz_invoice' => 'hospiz_invoice',
		        'rlp_invoice' => 'rlp_invoice',
		        
		        // ISPC-2214
		        'bre_kinder_invoice' => 'invoice_system',
		        // ISPC-2257+ ISPC-2272
		        'sh_shifts_internal_invoice' => 'sh_shifts_internal_invoice',
		        // ISPC-2286
		        'nr_invoice' => 'invoice_system',
		        //ISPC-2461
		        'demstepcare_invoice' => 'invoice_system',
		        
		        //ISPC-2263 Ancuta 14.05.2021
		        'rp_invoice' => 'rp_invoice',
		        //--
		    );
		    
		    if(!$allowed_invoice_types && !$stornoids)
		    {
		        return false;
		    }
		    
		    
		    foreach($dbs as $k_arr => $u_arr)
		    {
		        if(in_array($k_arr, $allowed_invoice_types))
		        {
		            $used_tables[] = $u_arr;
		        }
		    }
		    
		    //items filter mapping
		    $items_filter_fields = array(		        
		        'client_invoices' => 'create_date',
		        'bayern_invoices' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'bre_invoices' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'bre_hospiz_invoices' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'he_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'bw_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'sgbv_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'medipumps_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'sgbxi_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'hi_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
		        'u_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
		        'sh_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'bayern_invoices_new' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'bra_invoice' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'sh_internal_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
		        'rlp_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
		        'bw_invoice_new' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'invoice_system' => array('start' => 'start_active', 'end' => 'end_active','start2' => 'invoice_start', 'end2' => 'invoice_end'),
		        'sh_shifts_internal_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
		        
		        'sh_shifts_internal_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
		        
		        //ISPC-2263 Ancuta 14.05.2021
		        'rp_invoice' => array('start' => 'invoice_start', 'end' => 'invoice_end'),
		        //--
		    );
		    
		    if($used_tables)
		    {
		        $first_table = true;
		        $sql = '';
		        
		        foreach($used_tables as $k_table => $v_table)
		        {
		            if($first_table)
		            {
		                $sql .= 'SELECT';
		            }
		            else
		            {
		                $sql .= 'UNION SELECT';
		            }
		            
		            if($v_table == 'client_invoices')
		            {
		                $sql .= ' id,"0" as sapv,"0" as user, clientid as client,  ipid, prefix, CONCAT(prefix, rnummer) AS invoice_number, rnummer as invoice_nr, invoiceTotal as invoice_total, isdelete, record_id, storno, create_date, create_date as invoice_date, create_date as invoice_end, "' . $Tr->translate($v_table) . '" as invoice_type_translated , "' . $v_table . '" as inv_type, id as t_type, IF(completedDate = "0000-00-00 00:00:00", create_date, IF(completedDate = "1970-01-01 01:00:00", create_date, completedDate)) as completed_date
						    ,create_date as inv_start_date
						    ,create_date as inv_end_date
						    ';
		            }
		            else if($v_table == 'medipumps_invoice')
		            {
		                $sql .= ' id, "0" as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, type as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
						    ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						     ';
		            }
		            else if($v_table == 'sh_invoice')
		            {
		                $sql .= ' id, sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
						    ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						    ';
		            }
		            else if($v_table == 'bra_invoice')
		            {
		                $sql .= ' id, sapvid as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						    ';
		            }
		            else if($v_table == 'bayern_invoices_new')
		            {
		                $sql .= ' id, sapvid as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						    ';
		            }
		            else if($v_table == 'sh_internal_invoice')
		            {
		                $sql .= ' id, "0" as sapv, user, client, "0" as ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						    ';
		            }
		            else if($v_table == 'sh_shifts_internal_invoice')
		            {
		                $sql .= ' id, "0" as sapv, user, client,  ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
		                    
						    ';
		            }
		            else if($v_table == 'rlp_invoice')
		            {
		                $sql .= ' id, sapvid as sapv, "0" as user, client,  ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						    ';
		            }
		            else if($v_table == 'invoice_system')
		            {
		                $sql .= ' id, sapvid as sapv, "0" as user, client,  ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						    ';
		            }
		            //ISPC-2263 Ancuta 14.05.2021
		            else if($v_table == 'rp_invoice')
		            {
		                $sql .= ' id, sapv_id as sapv, "0" as user, client,  ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                            ,invoice_start as inv_start_date
						    ,invoice_end as inv_end_date
						    ';
		            }
		            //--
		            else
		            {
		                //						if($v_table == 'bw_invoice' || $v_table == 'sgbv_invoice' || $v_table == 'sgbxi_invoice')
		                if($v_table != 'u_invoice')
		                {
		                    $sql .= ' id,"0" as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, IF(completed_date = "0000-00-00 00:00:00", create_date, IF(completed_date = "1970-01-01 01:00:00", create_date, completed_date)) as completed_date
                                    ,invoice_start as inv_start_date
                                    ,invoice_end as inv_end_date
							    ';
		                }
		                else
		                {
		                    $sql .= ' id,"0" as sapv,"0" as user, client, ipid, prefix, CONCAT(prefix, invoice_number) as invoice_number, invoice_number as invoice_nr, invoice_total, isdelete, record_id, storno, invoice_start as invoice_date, invoice_end, create_date, "' . $Tr->translate($v_table) . '" as invoice_type_translated, "' . $v_table . '" as inv_type, id as t_type, create_date as completed_date
                                    ,invoice_start as inv_start_date
                                    ,invoice_end as inv_end_date
							    ';
		                }
		            }
		            
		            $sql .= ' FROM `' . $v_table . '`';
		            $sql .= ' WHERE id IN("' . implode('", "', $stornoids) . '") ';

		        }
		        

		    }
		    
		    
		    $resultset = Doctrine_Manager::getInstance()
		    ->getConnection('SYSDAT')
		    ->getDbh()
		    ->query($sql)
		    ->fetchAll(PDO::FETCH_ASSOC);
		    return $resultset;
		}
		
		
		
	}

?>