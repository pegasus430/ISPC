<?php

require_once("Pms/Form.php");

class Application_Form_HeInvoices extends Pms_Form {

	public function validate($post)
	{

	}

	public function create_invoice($clientid, $post)
	{
//		print_r($post);
//		exit;
		$Tr = new Zend_View_Helper_Translate();

		$ins_inv = new HeInvoices();
		$ins_inv->invoice_start = date('Y-m-d H:i:s', strtotime($post['start_invoice']));
		$ins_inv->invoice_end = date('Y-m-d H:i:s', strtotime($post['end_invoice']));
		$ins_inv->ipid = $post['ipid'];
		$ins_inv->client = $clientid;
		$ins_inv->pricelist_type = $post['pricelist_type'];
		$ins_inv->prefix = $post['prefix'];
		$ins_inv->invoice_number = $post['invoice_number'];
		$ins_inv->invoice_total = Pms_CommonData::str2num($post['invoice_total']);
		$ins_inv->address = $post['address'];
		$ins_inv->footer = $post['footer'];
		
		$ins_inv->show_boxes = 'show_box_active,show_box_patient,show_box_sapv';               //ISPC-2747 pct.b Lore 27.11.2020
		
		//ISPC-2746 Carmen 07.12.2020 - new columns
		//$ins_inv->client_ik = $post['client_ik'];
		$ins_inv->sapv_approve_nr = $post['sapv_approve_nr'];
		$ins_inv->first_name = $post['patientdetails']['first_name'];
		$ins_inv->last_name = $post['patientdetails']['last_name'];
		$ins_inv->birthdate = date('Y-m-d', strtotime($post['patientdetails']['birthd']));
		$ins_inv->insurance_no = $post['insurance_no'];
		$ins_inv->street = $post['patientdetails']['street'];
		//--
		
		$ins_inv->status = '1'; // DRAFT - ENTWURF
		$ins_inv->save();


		$inserted_id = $ins_inv->id;
		$invoice_total = '0';
		if($inserted_id)
		{
			foreach($post['items'] as $k_item => $v_item)
			{
				//step 1: get and organize dates for items
				foreach($post['items'] as $k_item_x => $v_item_x)
				{
					foreach($v_item_x['from_date'] as $k_period => $v_period)
					{
						$paid = '';
						if(strlen($v_item_x['paid_periods'][$k_period])>'0')
						{
							$paid = $v_item_x['paid_periods'][$k_period];
						}
						else
						{
							$paid = '0';
						}

						$invoice_items_periods[$v_item_x['shortcut']][$k_period] = array(
								'invoice' => $inserted_id,
								'item' => '',
								'paid' => $paid,
								'from_date' => $v_period,
								'till_date' => $v_item_x['till_date'][$k_period]
						);
					}
					//step 2: get shortcuts of items with dates
					$shortcuts_with_period[] = $v_item['shortcut'];
				}

				$from_date = '0000-00-00 00:00:00';
				$till_date = '0000-00-00 00:00:00';

				if(!empty($v_item['description']))
				{
					$description = $v_item['description'];
				}
				else
				{
					$description = $Tr->translate('shortcut_description_' . $k_item);
				}

				$invoice_items[] = array(
						'invoice' => $inserted_id,
						'client' => $clientid,
						'shortcut' => $v_item['shortcut'],
						'description' => $description,
						'qty' => $v_item['qty'],
						'percent' => $v_item['percent'],
						'price' => $v_item['price_details']['price'],
						'total' => Pms_CommonData::str2num($v_item['total']),
						'custom' => '0',
						'from_date' => $from_date,
						'till_date' => $till_date,
						'isdelete' => '0',
				);
				$invoice_total += Pms_CommonData::str2num($v_item['total']);
			}


			foreach($post['custom']['shortcut'] as $k_cust_item => $v_cust_item)
			{
				if(!empty($v_cust_item))
				{
				    if($post['custom']['from_date'][$k_cust_item] != "0000-00-00 00:00:00"){
				        $c_from_data = $post['custom']['from_date'][$k_cust_item];
				    } else{
				        $c_from_data = '0000-00-00 00:00:00';
				    }
				    
				    if($post['custom']['till_date'][$k_cust_item] != "0000-00-00 00:00:00"){
				        $c_till_data = $post['custom']['till_date'][$k_cust_item];
				    } else{
				        $c_till_data = '0000-00-00 00:00:00';
				    }
				    
				    if($post['custom']['xbdt_action'][$k_cust_item]){
				        $custom = "3";
				        $xbdt_actions_array[] = $post['custom']['xbdt_action'][$k_cust_item];
				    } else{
				        $custom = "1";
				    }
				    
					$invoice_items[] = array(
							'invoice' => $inserted_id,
							'client' => $clientid,
							'shortcut' => $v_cust_item,
							'related_shortcut' => $post['custom']['related_shortcut'][$k_cust_item],
							'custom_dta_id' => $post['custom']['custom_dta_id'][$k_cust_item],
							'description' => $post['custom']['description'][$k_cust_item],
							'qty' => $post['custom']['qty'][$k_cust_item],
							'price' => $post['custom']['price'][$k_cust_item],
							'total' => Pms_CommonData::str2num($post['custom']['total'][$k_cust_item]),
							'custom' => $custom,
							'from_date' => $c_from_data,
							'till_date' => $c_till_data,
					        'xbdt_action' => $post['custom']['xbdt_action'][$k_cust_item],
							'isdelete' => '0',
					);

					$invoice_total += Pms_CommonData::str2num($post['custom']['total'][$k_cust_item]);
				}
				
				
			}

			$post['previous_invoices'] = $this->array_sort($post['previous_invoices'], 'from', SORT_ . strtoupper(ASC));
			foreach($post['previous_invoices'] as $k_prev_inv => $v_prev_inv)
			{

				if($v_prev_inv['from'])
				{
					$from_date = date('Y-m-d H:i:s', strtotime($v_prev_inv['from']));
				}
				else
				{
					$from_date = '0000-00-00 00:00:00';
				}

				if($v_prev_inv['till'])
				{
					$till_date = date('Y-m-d H:i:s', strtotime($v_prev_inv['till']));
				}
				else
				{
					$till_date = '0000-00-00 00:00:00';
				}

				$invoice_items[] = array(
						'invoice' => $inserted_id,
						'client' => $clientid,
						'shortcut' => $k_prev_inv,
						'description' => $v_prev_inv['description'],
						'qty' => $v_prev_inv['qty'],
						'price' => $v_prev_inv['price'],
						'total' => Pms_CommonData::str2num($v_prev_inv['total']),
						'custom' => '2',
						'from_date' => $from_date,
						'till_date' => $till_date,
						'isdelete' => '0',
				);
				$invoice_total += Pms_CommonData::str2num($v_prev_inv['total']);
			}

			//LE: 28.10.2014
			//adding items without code results in invoice total to be different from sum(item_total) of invoice
			//update invoice total
			$update_invoice = Doctrine_Query::create()
				->update("HeInvoices")
				->set('invoice_total', "'" . Pms_CommonData::str2num($invoice_total) . "'")
				->where('id ="' . $inserted_id . '"');
			$update_total = $update_invoice->execute();

			$collection = new Doctrine_Collection('HeInvoiceItems');
			$collection->fromArray($invoice_items);
			$collection->save();
			//step 3: get inserted shortcuts with period only
			if($shortcuts_with_period)
			{
				$q = Doctrine_Query::create()
				->select('*')
				->from('HeInvoiceItems')
				->whereIn('shortcut', $shortcuts_with_period)
				->andWhereIn('invoice', $inserted_id);
				$q_res = $q->fetchArray();

				foreach($q_res as $k_item_data => $v_item_data)
				{
					foreach($invoice_items_periods[$v_item_data['shortcut']] as $k_iip => $v_iip)
					{
						$v_iip['item'] = $v_item_data['id'];
						$invoice_period_items[] = $v_iip;
					}
				}


				if($invoice_period_items)
				{
					$items_period_col = new Doctrine_Collection('HeInvoiceItemsPeriod');
					$items_period_col->fromArray($invoice_period_items);
					$items_period_col->save();
				}
			}
			
			
			
			// UPDATE XBDT - set paid
			
			if(!empty($xbdt_actions_array)){
			    foreach($xbdt_actions_array as $k=>$xbdt_action_id){
			        
			        $existing = Doctrine::getTable('PatientXbdtActions')->find($xbdt_action_id);
			        if($existing){
			            $existing->file_id = "1";
			            $existing->edited_from = "heinvoice";
			            $existing->save();
			            
			        }
			    }
			}
			
			return $inserted_id; //TODO-3739 Carmen 26.01.2021
			
		}
	}

	public function edit_invoice($invoice, $clientid, $post, $status)
	{
		if($invoice)
		{
			$del_custom_items = $this->delete_custom_items($invoice);
			$del_xbdt_custom_items = $this->delete_xbdt_custom_items($invoice);
//
//			//update initial invoice
//			$update_invoice = Doctrine_Query::create()
//			->update("HeInvoices")
//			->set('invoice_number', "'" . $post['invoice_number'] . "'")
//			->set('prefix', "'" . $post['prefix'] . "'")
//			->set('address', "'" . addslashes($post['address']) . "'")
//			->set('footer', "'" . $post['footer'] . "'")
//			->set('invoice_total', "'" . Pms_CommonData::str2num($post['invoice_total']) . "'")
//			->where('id ="' . $invoice . '"');
//			if($status != '1' && $status != '4' && $status != '0' && strlen($post['pdf']) == 0)
//			{
//				$update_invoice->set('completed_date', "'" . date('Y-m-d H:i:s', strtotime($post['completed_date'])) . "'");
//			}
//			if($status == '4')
//			{
//				$update_invoice->set('isdelete', '1');
//			}
//			$update_invoice->execute();

			//update initial invoice
			if($status == '4')
			{
				$update_invoice = Doctrine_Query::create()
					->update("HeInvoices")
					->where('id ="' . $invoice . '"')
					->set('isdelete', '1')
					//dont delete paid and partialy paid
					->andWhere('status != "3"')
					->andWhere('status != "5"');
				$update_invoice->execute();
			}

			if($status != '1' && $status != '4' && strlen($post['pdf']) == 0)
			{
				$update_invoice = Doctrine_Query::create()
					->update("HeInvoices")
					->where('id ="' . $invoice . '"')
					->set('invoice_number', "'" . $post['invoice_number'] . "'")
					->set('prefix', "'" . $post['prefix'] . "'")
					->set('completed_date', "'" . date('Y-m-d H:i:s', strtotime($post['completed_date'])) . "'");
				$update_invoice->execute();
			}

			//update recipient && footer (html update issues)
			$update_invoice = Doctrine::getTable('HeInvoices')->findOneById($invoice);
			$update_invoice->invoice_number = $post['invoice_number'];
			$update_invoice->prefix = $post['prefix'];
			$update_invoice->address = $post['address'];
			$update_invoice->footer = $post['footer'];
			$update_invoice->invoice_total = Pms_CommonData::str2num($post['invoice_total']);
			$update_invoice->save();

			//remove deleted items
			if(count($post['delete_ids']['normal_ids']) > '0')
			{
				$del_normal_items = Doctrine_Query::create()
					->update("HeInvoiceItems")
					->set('isdelete', '1')
					->whereIn('id', $post['delete_ids']['normal_ids']);
				$deleted_items = $del_normal_items->execute();
			}


			if(count($post['delete_ids']['pv_ids']) > '0')
			{
				$del_pv_items = Doctrine_Query::create()
				->update("HeInvoiceItems")
				->set('isdelete', '1')
				->whereIn('id', $post['delete_ids']['pv_ids']);
				$deleted_items = $del_pv_items->execute();
			}

			//				insert invoice custom items
			foreach($post['custom']['shortcut'] as $k_cust_item => $v_cust_item)
			{
				if(!empty($v_cust_item) && $post['custom']['description'])
				{
				    
                    if ($post['custom']['from_date'][$k_cust_item] != "0000-00-00 00:00:00") {
                        $c_from_data = $post['custom']['from_date'][$k_cust_item];
                    } else {
                        $c_from_data = '0000-00-00 00:00:00';
                    }
                    if ($post['custom']['till_date'][$k_cust_item] != "0000-00-00 00:00:00") {
                        $c_till_data = $post['custom']['till_date'][$k_cust_item];
                    } else {
                        $c_till_data = '0000-00-00 00:00:00';
                    }
                    
                    if ($post['custom']['xbdt_action'][$k_cust_item]) {
                        $custom = "3";
                        $xbdt_actions_array[] = $post['custom']['xbdt_action'][$k_cust_item];
                    } else {
                        $custom = "1";
                    }
				    
					$invoice_items[] = array(
							'invoice' => $invoice,
							'client' => $clientid,
							'shortcut' => $v_cust_item,
							'related_shortcut' => $post['custom']['related_shortcut'][$k_cust_item],
							'custom_dta_id' => $post['custom']['custom_dta_id'][$k_cust_item],
							'description' => $post['custom']['description'][$k_cust_item],
							'qty' => $post['custom']['qty'][$k_cust_item],
							'price' => $post['custom']['price'][$k_cust_item],
							'total' => $post['custom']['total'][$k_cust_item],
							'custom' => $custom,
							'from_date' => $c_from_data,
							'till_date' => $c_till_data,
							'xbdt_action' => $post['custom']['xbdt_action'][$k_cust_item],
							'isdelete' => '0',
					);
				}
			}

			$collection = new Doctrine_Collection('HeInvoiceItems');
			$collection->fromArray($invoice_items);
			$collection->save();

			if($status != '0') //dont change status when is paid and edited
			{
				$update_invoice_status = Doctrine_Query::create()
				->update("HeInvoices")
				->set('status', $status)
				->where('id ="' . $invoice . '"')
				->andWhere('status != "3"');
				$update_invoice_status->execute();
			}

			if($update_invoice)
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

	public function delete_invoice($invoice)
	{
		$update_invoice = Doctrine_Query::create()
		->update("HeInvoices")
		->set('status', '4')
		->set('isdelete', '1')
		->where('id ="' . $invoice . '"');
		$update_invoice->execute();

		if($update_invoice)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function delete_custom_items($invoice)
	{
		$delete_cust_items = Doctrine_Query::create()
		->update("HeInvoiceItems")
		->set('isdelete', '1')
		->where('invoice ="' . $invoice . '"')
		->andWhere('custom ="1"')
		->andWhere('isdelete ="0"');
		$cust_items_res = $delete_cust_items->execute();

		if($cust_items_res)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
    public function delete_xbdt_custom_items($invoice)
	{
		$delete_cust_items = Doctrine_Query::create()
		->update("HeInvoiceItems")
		->set('isdelete', '1')
		->where('invoice ="' . $invoice . '"')
		->andWhere('custom ="3"')
		->andWhere('isdelete ="0"');
		$cust_items_res = $delete_cust_items->execute();

		if($cust_items_res)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function ToggleStatusInvoices($iids, $status, $clientid = false)
	{
		//	setStatus of multiple client invoices **
		if(count($iids) > 0)
		{
			/* ---------------------- Status Client Invoice START ------------------- */
			$statusInvoices = Doctrine_Query::create()
			->update("HeInvoices")
			->set('status', "'" . $status . "'");

			if($status == "3") //paid
			{
				$statusInvoices->set('paid_date', "NOW()");
			}
			//reset paid date if invoice is running out of payments (user deletes them)
			if($status == "1" || $status == "2") //draft or unpaid
			{
				$statusInvoices->set('paid_date', "'0000-00-00 00:00:00'");
				if($status == '2')
				{
					$statusInvoices->set('completed_date', "NOW()");
				}
			}
			$statusInvoices->whereIn('id', $iids)->andWhere('isdelete =0');
			$d = $statusInvoices->execute();

			//generate new rechnung number for completed invoices
			if($status == '2' && $clientid)
			{
				$he_invoice = new HeInvoices();

				foreach($iids as $k_inv_nr => $v_inv_nr)
				{
					$he_invoice_number = $he_invoice->get_next_invoice_number($clientid);

					$prefix = $he_invoice_number['prefix'];
					$invoicenumber = $he_invoice_number['invoicenumber'];

					$update_inv = Doctrine_Core::getTable('HeInvoices')->findOneByIdAndStatusAndIsdeleteAndPrefix($v_inv_nr, '2', '0', 'TEMP_');
					//avoid errors
					if($update_inv)
					{
						$update_inv->prefix = $prefix;
						$update_inv->invoice_number = $invoicenumber;
						$update_inv->save();
					}
				}
			}
		}
	}

	public function submit_payment($post)
	{
		/* -------------------------- Get Client Invoice Payments   START ------------------- */
		$invoices = Doctrine_Query::create()
		->select("*, SUM(amount) as PaidAmount")
		->from('HeInvoicePayments')
		->Where("invoice='" . $post['invoiceId'] . "'")
		->andWhere('isdelete = 0');
		$itemInvArray = $invoices->fetchArray();

		/*--------------------------- Update Client Invoice   START ------------------- */
		$lastPay = end($itemInvArray);
		$updateCI = Doctrine::getTable('HeInvoices')->findOneById($post['invoiceId']);
		$curentInvoiceArr = $updateCI->toArray();

		if(empty($itemInvArray[0]['id']))
		{
			if(bccomp($curentInvoiceArr['invoice_total'], Pms_CommonData::str2num($post['paymentAmount'])) == 0)
			{
				$status = "3"; //completed
			}
			else if(bccomp($curentInvoiceArr['invoice_total'], Pms_CommonData::str2num($post['paymentAmount'])) == 1)
			{
				$status = "5"; //partial
			}
		}
		else
		{
			if(bccomp(($lastPay['PaidAmount'] + Pms_CommonData::str2num($post['paymentAmount'])), $curentInvoiceArr['invoice_total']) == 0)
			{
				$status = "3"; //completed
			}
			else
			{
				$status = "5"; //partial
			}
		}

		$updateCI->status = $status;
		$updateCI->save();

		/* -------------------------- Add Invoice Payment START ------------------------------- */
		$invPayment = new HeInvoicePayments();
		$invPayment->invoice = $post['invoiceId'];
		$invPayment->amount = Pms_CommonData::str2num($post['paymentAmount']);
		$invPayment->comment = $post['paymentComment'];
		$invPayment->paid_date = date("Y-m-d H:i:s", strtotime($post['paymentDate']));
		$invPayment->isdelete = "0";
		$invPayment->save();
	}

	public function delete_multiple_invoices($iids)
	{
		$iids[] = "99999999999999999";

		if(count($iids) > 0)
		{
			$delInvoices = Doctrine_Query::create()
			->update("HeInvoices")
			->set('isdelete', "1")
			->set('status', "4")
			->whereIn('id', $iids)
			->andWhere('isdelete =0');

			$d = $delInvoices->execute();
		}
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
							if($on == 'date' || $on == 'discharge_date' || $on == 'from_date' || $on = 'from')
							{
								$sortable_array[$k] = strtotime($v2);
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
					if($on == 'date' || $on == 'from_date' || $on = 'from')
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
//					asort($sortable_array);
					$sortable_array = Pms_CommonData::a_sort($sortable_array);
					break;
				case 'SORT_DESC':
//					arsort($sortable_array);
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
	
	/*
	 * ISPC-2747 Lore 23.11.2020
	 */
	public function validate_custom_invoice($post)
	{
	    $error = 0;
	    $Tr = new Zend_View_Helper_Translate();
	    $validator = new Pms_Validation();
	    
	    if(!$validator->isstring($post['prefix']))
	    {
	        $this->error_message['prefix'] = $Tr->translate('bay_custom_invoice_prefix_required');
	        $error = 1;
	    }
	    
	    if(!$validator->isstring($post['invoice_number']) && $error != "1")
	    {
	        $this->error_message['invoice_number'] = $Tr->translate('bay_custom_invoice_invoice_number_required');
	        $error = 2;
	    }
	    
	    if(!$validator->isstring($post['start_active']) && !$validator->isdate($post['start_active']) && $error != "1" && $error != "2")
	    {
	        $this->error_message['start_active'] = $Tr->translate('bay_custom_invoice_start_active_required');
	        $error = 3;
	    }
	    
	    if(!$validator->isstring($post['end_active']) && !$validator->isdate($post['end_active']) && $error != "1" && $error != "2" && $error != '3')
	    {
	        $this->error_message['end_active'] = $Tr->translate('bay_custom_invoice_end_active_required');
	        $error = 4;
	    }
	    
	    if($error == 0)
	    {
	        return true;
	    }
	    else
	    {
	        return false;
	    }
	}
	
	/*
	 * ISPC-2747 Lore 23.11.2020
	 */
	public function insert_custom_invoice($post)
	{
	    
	    $format = 'Y-m-d H:i:s';
	    if(!empty($post['start_active']))
	    {
	        $invoice_start = date($format, strtotime($post['start_active']));
	    }
	    else
	    {
	        $invoice_start = "0000-00-00 00:00:00";
	    }
	    
	    if(!empty($post['end_active']))
	    {
	        $invoice_end = date($format, strtotime($post['end_active']));
	    }
	    else
	    {
	        $invoice_end = "0000-00-00 00:00:00";
	    }
	    
	    
	    if(!empty($post['start_active']))
	    {
	        $start_active = date($format, strtotime($post['start_active']));
	    }
	    else
	    {
	        $start_active = '0000-00-00 00:00:00';
	    }
	    
	    if(!empty($post['end_active']))
	    {
	        $end_active = date($format, strtotime($post['end_active']));
	    }
	    else
	    {
	        $end_active = '0000-00-00 00:00:00';
	    }
	    
	    if(!empty($post['start_sapv']))
	    {
	        $start_sapv = date($format, strtotime($post['start_sapv']));
	    }
	    else
	    {
	        $start_sapv = '0000-00-00 00:00:00';
	    }
	    
	    if(!empty($post['birthd']))
	    {
	        $birthdate = date('Y-m-d', strtotime($post['birthd']));
	    }
	    else
	    {
	        $birthdate = "0000-00-00";
	    }
	    
	    if(!empty($post['end_sapv']))
	    {
	        $end_sapv = date($format, strtotime($post['end_sapv']));
	    }
	    else
	    {
	        $end_sapv = '0000-00-00 00:00:00';
	    }
	    
	    if(!empty($post['sapv_approve_date']))
	    {
	        $sapv_approve_date = date($format, strtotime($post['sapv_approve_date']));
	    }
	    else
	    {
	        $sapv_approve_date = '0000-00-00 00:00:00';
	    }
	    
	    //ISPC-2747 pct.b Lore 27.11.2020
	    $show_boxes = '';
	    if(isset($post['show_box_active'])){
	        $show_boxes .= 'show_box_active,';
	    }
	    if(isset($post['show_box_patient'])){
	        $show_boxes .= 'show_box_patient,';
	    }
	    if(isset($post['show_box_sapv'])){
	        $show_boxes .= 'show_box_sapv,';
	    }
	    
	    $ins_inv = new HeInvoices();
	    $ins_inv->invoice_start = $invoice_start;
	    $ins_inv->invoice_end = $invoice_end;
	    $ins_inv->start_active = $start_active; //first product day in period
	    $ins_inv->end_active = $end_active; //last product day in period
	    $ins_inv->ipid = $post['ipid'];
	    
	    $ins_inv->client = $post['clientid'];
	    $ins_inv->client_name = $post['client_ik'];
	    $ins_inv->prefix = $post['prefix'];
	    $ins_inv->invoice_number = $post['invoice_number'];
	    $ins_inv->invoice_total = $post['invoice_total'];
	    $ins_inv->address = $post['address'];
	    $ins_inv->footer = $post['footer'];
	    
	    $ins_inv->start_sapv = $start_sapv; //allready formated as db format
	    $ins_inv->end_sapv = $end_sapv; //allready formated as db format
	    $ins_inv->sapv_approve_date = $sapv_approve_date;
	    $ins_inv->sapv_approve_nr = $post['sapv_approve_nr'];
	    $ins_inv->first_name = $post['first_name'];
	    $ins_inv->last_name = $post['last_name'];
	    $ins_inv->birthdate = $birthdate;
	    $ins_inv->street = $post['street'];
	    $ins_inv->patient_care = $post['patient_pflegestufe'];
	    $ins_inv->insurance_no = $post['insurance_no'];
	    $ins_inv->debtor_number = $post['debtor_number'];
	    $ins_inv->ppun = $post['ppun'];
	    $ins_inv->paycenter = $post['paycenter'];
	    
	    $ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020
	    $ins_inv->custom_invoice = 'custom_invoice';      //ISPC-2747 pct.b Lore 27.11.2020
	    
	    $ins_inv->status = '1'; // DRAFT - ENTWURF
	    $ins_inv->isdelete = '0';
	    $ins_inv->record_id = '0';
	    $ins_inv->storno = '0';
	    $ins_inv->save();
	    $ins_id = $ins_inv->id;
	    
	    if($ins_id)
	    {
	        foreach($post['row'] as $k_inv => $v_inv)
	        {
	            $invoice_items_arr[] = array(
	                'invoice' => $ins_id,
	                'client' => $post['clientid'],
	                'name' => $post['name'][$k_inv],
	                'shortcut' => $post['shortcut'][$k_inv],
	                'description' => $post['name'][$k_inv],
	                'qty' => $post['qty'][$k_inv],
	                'price' => $post['price'][$k_inv],
	                'total' => $post['total'][$k_inv],
	                'custom' => '1',
	                'isdelete' => '0'
	            );
	        }
	        
	        if(count($invoice_items_arr) > 0)
	        {
	            //insert many records with one query!!
	            $collection = new Doctrine_Collection('HeInvoiceItems');
	            $collection->fromArray($invoice_items_arr);
	            $collection->save();
	        }
	    }
	    
	    return $ins_id;
	}
	
	public function update_custom_invoice($invoice_id = false, $post, $status = false)
	{
	    if($invoice_id)
	    {
	        $inserted_id = '';
	        $invoice_total = '';
	        $invoice_items = array();
	        $format = 'Y-m-d H:i:s';
	        
	        
	        if(!empty($post['completed_date']))
	        {
	            $completed_date = date($format, strtotime($post['completed_date']));
	        }
	        else
	        {
	            $completed_date = "0000-00-00 00:00:00";
	        }
	        
	        if(!empty($post['start_active']))
	        {
	            $invoice_start = date($format, strtotime($post['start_active']));
	        }
	        else
	        {
	            $invoice_start = "0000-00-00 00:00:00";
	        }
	        
	        if(!empty($post['end_active']))
	        {
	            $invoice_end = date($format, strtotime($post['end_active']));
	        }
	        else
	        {
	            $invoice_end = "0000-00-00 00:00:00";
	        }
	        
	        
	        
	        if(!empty($post['start_active']))
	        {
	            $start_active = date($format, strtotime($post['start_active']));
	        }
	        else
	        {
	            $start_active = '0000-00-00 00:00:00';
	        }
	        
	        if(!empty($post['end_active']))
	        {
	            $end_active = date($format, strtotime($post['end_active']));
	        }
	        else
	        {
	            $end_active = '0000-00-00 00:00:00';
	        }
	        
	        if(!empty($post['start_sapv']))
	        {
	            $start_sapv = date($format, strtotime($post['start_sapv']));
	        }
	        else
	        {
	            $start_sapv = '0000-00-00 00:00:00';
	        }
	        
	        if(!empty($post['birthd']))
	        {
	            $birthdate = date('Y-m-d', strtotime($post['birthd']));
	        }
	        else
	        {
	            $birthdate = "0000-00-00";
	        }
	        
	        if(!empty($post['end_sapv']))
	        {
	            $end_sapv = date($format, strtotime($post['end_sapv']));
	        }
	        else
	        {
	            $end_sapv = '0000-00-00 00:00:00';
	        }
	        
	        if(!empty($post['sapv_approve_date']))
	        {
	            $sapv_approve_date = date($format, strtotime($post['sapv_approve_date']));
	        }
	        else
	        {
	            $sapv_approve_date = '0000-00-00 00:00:00';
	        }
	        
	        //ISPC-2747 pct.b Lore 27.11.2020
	        $show_boxes = '';
	        if(isset($post['show_box_active'])){
	            $show_boxes .= 'show_box_active,';
	        }
	        if(isset($post['show_box_patient'])){
	            $show_boxes .= 'show_box_patient,';
	        }
	        if(isset($post['show_box_sapv'])){
	            $show_boxes .= 'show_box_sapv,';
	        }
	        
	        $ins_inv = Doctrine::getTable('HeInvoices')->findOneById($invoice_id);
	        $ins_inv_data = $ins_inv->toArray();
	        
	        if($status)
	        {
	            //dont delete invoices paid and partialy paid
	            if($status == '4' && $ins_inv_data['status'] != '3' && $ins_inv_data['status'] != '5')
	            {
	                $ins_inv->isdelete = "1";
	            }
	            
	            if($status != '0' && $status != '1' && $status != '4')
	            {
	                $ins_inv->completed_date = $completed_date;
	            }
	            
	            if($status != '0' && $ins_inv_data['status'] != '3' && $ins_inv_data['status'] != '5') //dont change status when is paid and edited
	            {
	                $ins_inv->status = $status;
	            }
	        }
	        
	        $ins_inv->invoice_start = $invoice_start;
	        $ins_inv->invoice_end = $invoice_end;
	        $ins_inv->start_active = $start_active; //first product day in period
	        $ins_inv->end_active = $end_active; //last product day in period
	        $ins_inv->ipid = $post['ipid'];
	        
	        $ins_inv->client = $post['clientid'];
	        $ins_inv->client_name = $post['client_ik'];
	        $ins_inv->prefix = $post['prefix'];
	        $ins_inv->invoice_number = $post['invoice_number'];
	        $ins_inv->invoice_total = $post['invoice_total'];
	        $ins_inv->address = $post['address'];
	        $ins_inv->footer = $post['footer'];
	        
	        $ins_inv->start_sapv = $start_sapv; //allready formated as db format
	        $ins_inv->end_sapv = $end_sapv; //allready formated as db format
	        $ins_inv->sapv_approve_date = $sapv_approve_date;
	        $ins_inv->sapv_approve_nr = $post['sapv_approve_nr'];
	        $ins_inv->first_name = $post['first_name'];
	        $ins_inv->last_name = $post['last_name'];
	        $ins_inv->birthdate = $birthdate;
	        $ins_inv->street = $post['street'];
	        $ins_inv->patient_care = $post['patient_pflegestufe'];
	        $ins_inv->insurance_no = $post['insurance_no'];
	        $ins_inv->debtor_number = $post['debtor_number'];
	        $ins_inv->ppun = $post['ppun'];
	        $ins_inv->paycenter = $post['paycenter'];
	        
	        $ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020

	        $ins_inv->save();
	        $ins_id = $ins_inv->id;
	        
	        if($ins_id)
	        {
	            foreach($post['row'] as $k_inv => $v_inv)
	            {
	                $invoice_items_arr[] = array(
	                    'invoice' => $ins_id,
	                    'client' => $post['clientid'],
	                    'name' => $post['name'][$k_inv],
	                    'shortcut' => $post['shortcut'][$k_inv],
	                    'description' => $post['name'][$k_inv],
	                    'qty' => $post['qty'][$k_inv],
	                    'price' => $post['price'][$k_inv],
	                    'total' => $post['total'][$k_inv],
	                    'custom' => '1',
	                    'isdelete' => '0'
	                );
	            }
	            
	            if(count($invoice_items_arr) > 0)
	            {
	                self::delete_items($ins_id);
	                
	                //insert many records with one query!!
	                $collection = new Doctrine_Collection('HeInvoiceItems');
	                $collection->fromArray($invoice_items_arr);
	                $collection->save();
	            }
	        }
	        
	        return $ins_id;
	    }
	    else
	    {
	        return false;
	    }
	}
	
	private function delete_items($invoice)
	{
	    if($invoice)
	    {
	        $q = Doctrine_Query::create()
	        ->update('HeInvoiceItems')
	        ->set('isdelete', "1")
	        ->where('invoice = "' . $invoice . '"')
	        ->andWhere('isdelete = "0"');
	        $q_res = $q->execute();
	    }
	}

	/**
	 * ISPC-2312 Ancuta 07.12.2020
	 * @param unknown $iids
	 * @param unknown $clientid
	 * @return boolean
	 */
	public function archive_multiple_invoices($iids, $clientid)
	{
	    if (empty($iids)) {
	        return false;
	    }
	    $iids = array_unique($iids);
	    
	    $archive_invoices = Doctrine_Query::create()
	    ->update("HeInvoices")
	    ->set('isarchived', "1")
	    ->whereIn('id', $iids)
	    ->andWhere('client = "' . $clientid . '"')
	    ->andWhere('isdelete ="0"');
	    $archive = $archive_invoices->execute();
	}
	
}
