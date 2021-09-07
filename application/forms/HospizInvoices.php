<?php

	require_once("Pms/Form.php");

	class Application_Form_HospizInvoices extends Pms_Form {

		public function validate($post)
		{

		}

		public function insert_invoice($post)
		{
		    
				$hospiz_invoices = new HospizInvoices();

				$invoice_data = $post['invoice'];
				$clientid = $post['client']['id'];

// 				$sapv_id = $invoice_data['sapv']['id'];
// 				$admission_id = $invoice_data['admissionid']; // WHAT ??? 
// 				$admission_id = $post['admission_period']; // WHAT ??? 
				$admission_id = "0"; //Changed to month selection 

				//invoice period dates
				if(date("Y", strtotime($invoice_data['period']['start'])) != "1970" && strlen($invoice_data['period']['start']) > '0')
				{
					$current_period_start = date('Y-m-d H:i:s', strtotime($invoice_data['period']['start']));
				}
				else
				{
					$current_period_start = "0000-00-00 00:00:00";
				}

				if(date("Y", strtotime($invoice_data['period']['end'])) != "1970" && strlen($invoice_data['period']['end']) > '0')
				{
					$current_period_end = date('Y-m-d H:i:s', strtotime($invoice_data['period']['end']));
				}
				else
				{
					$current_period_end = "0000-00-00 00:00:00";
				}


				//invoice number and prefix
				$hospiz_invoice_number = $hospiz_invoices->get_next_invoice_number($post['client']['id'], true);
				$prefix = $hospiz_invoice_number['prefix'];
				$invoicenumber = $hospiz_invoice_number['invoicenumber'];

				//insert invoice
				$ins_inv = new HospizInvoices();
				$ins_inv->invoice_start = $current_period_start;
				$ins_inv->invoice_end = $current_period_end;
				$ins_inv->start_active = $current_period_start;
				$ins_inv->end_active = $current_period_end;
// 				$ins_inv->start_active = date('Y-m-d H:i:s', strtotime($invoice_data['first_active_day']));
// 				$ins_inv->end_active = date('Y-m-d H:i:s', strtotime($invoice_data['last_active_day']));
// 				$ins_inv->start_sapv = date('Y-m-d H:i:s', strtotime($invoice_data['first_sapv_day']));
// 				$ins_inv->end_sapv = date('Y-m-d H:i:s', strtotime($invoice_data['last_sapv_day']));
// 				$ins_inv->sapv_approve_date = date('Y-m-d H:i:s', strtotime($invoice_data['sapv_approve_date']));
// 				$ins_inv->sapv_approve_nr = $invoice_data['sapv_approve_nr'];
				$ins_inv->ipid =  $post['ipid'];
				$ins_inv->client = $clientid;
				$ins_inv->prefix = $prefix;
				$ins_inv->invoice_number = $invoicenumber;
				$ins_inv->invoice_total = Pms_CommonData::str2num($invoice_data['invoice_total']);
				$ins_inv->sub_invoice_total = Pms_CommonData::str2num($invoice_data['sub_invoice_total']);
				$ins_inv->type= $invoice_data['type']; 
				$ins_inv->status = '1'; // DRAFT - ENTWURF
				$ins_inv->address = (strlen($invoice_data['patient_address']) > '0') ? $invoice_data['patient_address'] : $invoice_data['address'];
				$ins_inv->footer = $post['footer'];
// 				$ins_inv->sapvid = $sapv_id;
				
				//new columns
				//$ins_inv->client_ik = $post['client_ik'];
				$ins_inv->first_name = $post['patientdetails']['first_name'];
				$ins_inv->last_name = $post['patientdetails']['last_name'];
				$ins_inv->birthdate = date('Y-m-d', strtotime($post['patientdetails']['birthd']));
				$ins_inv->insurance_no = $post['insurance_no'];
				$ins_inv->street = $post['patientdetails']['street'];
				//--
				
				$ins_inv->show_boxes = 'show_box_active,show_box_patient,show_box_sapv';               //ISPC-2747 pct.b Lore 27.11.2020
				
				$ins_inv->admissionid = $admission_id;
				$ins_inv->save();
				$inserted_id = $ins_inv->id;

				foreach($invoice_data['items'] as $k_shortcut_inv => $v_values_inv)
				{
					$invoice_items[] = array(
						'invoice' => $inserted_id,
						'client' => $clientid,
						'shortcut' => $k_shortcut_inv,
					    'special' => $v_values_inv['special'],
						'description' => $v_values_inv['description'],
						'qty' => $v_values_inv['qty'],
						'total' => Pms_CommonData::str2num($v_values_inv['total']),
						'price' => $v_values_inv['price']
					);
				}
				
				
				foreach($post['custom']['description'] as $k_cust_item => $v_cust_item)
				{
				    if(!empty($v_cust_item))
				    {
				        $invoice_items[] = array(
				            'invoice' => $inserted_id,
				            'client' => $clientid,
				            'shortcut' => "custom_hospiz_item",
				            'description' => $v_cust_item,
				            'qty' => $post['custom']['qty'][$k_cust_item],
				            'price' => $post['custom']['price'][$k_cust_item],
				            'total' => Pms_CommonData::str2num($post['custom']['total'][$k_cust_item]),
				            'special' => '0',
				            'custom' => '1'
				        );
				
				        $invoice_total += Pms_CommonData::str2num($post['custom']['total'][$k_cust_item]);
				    }
				}
				
				
				$inserted_ids[] = $inserted_id;

			if(count($invoice_items) > 0)
			{
				//insert many records with one query!!
				$collection = new Doctrine_Collection('HospizInvoiceItems');
				$collection->fromArray($invoice_items);
				$collection->save();
			}

			return $inserted_ids;
		}

		public function edit_invoice($invoice, $clientid, $post, $status)
		{
		    
// 		    print_R($post); exit;
			if($invoice)
			{
				//update initial invoice
				if($status == '4')
				{
					$update_invoice = Doctrine_Query::create()
						->update("HospizInvoices")
						->where('id ="' . $invoice . '"')
						->set('isdelete', '1')
						->andWhere('status != "3"')
						->andWhere('status != "5"');
					$update_invoice->execute();
				}

				if($status != '1' && $status != '4' && strlen($post['pdf']) == 0)
				{
					$update_invoice = Doctrine_Query::create()
						->update("HospizInvoices")
						->where('id ="' . $invoice . '"')
						->set('invoice_number', "'" . $post['invoice_number'] . "'")
						->set('prefix', "'" . $post['prefix'] . "'")
						->set('completed_date', "'" . date('Y-m-d H:i:s', strtotime($post['completed_date'])) . "'");
					$update_invoice->execute();
				}

				//update recipient && footer (html update issues)
				$update_invoice = Doctrine::getTable('HospizInvoices')->findOneById($invoice);
				$update_invoice->address = $post['invoice']['address'];
				$update_invoice->footer = $post['footer'];
				
				if($post['current_status'] == "1" )
				{
    				$update_invoice->invoice_total =  Pms_CommonData::str2num($post['invoice']['invoice_total']); 
    				$update_invoice->sub_invoice_total=  Pms_CommonData::str2num($post['invoice']['sub_invoice_total']);
				} 
				$update_invoice->save();

				if($post['current_status'] == "1" )
				{
    				// update ionvoice items 
    				
    			    $del_pv_items = Doctrine_Query::create()
    			    ->update("HospizInvoiceItems")
    			    ->set('isdelete', '1')
    			    ->where('invoice = "'.$invoice.'"');
    			    $deleted_items = $del_pv_items->execute();
    
    			    
    			    
    
    			    foreach($post['invoice']['items'] as $k_shortcut_inv => $v_values_inv)
    			    {
    			        $invoice_items[] = array(
    			            'invoice' => $invoice,
    			            'client' => $clientid,
    			            'shortcut' => $v_values_inv['shortcut'],
    			            'special' => $v_values_inv['special'],
    			            'custom' => $v_values_inv['custom'],
    			            'description' => $v_values_inv['description'],
    			            'qty' => $v_values_inv['qty'],
    			            'total' => Pms_CommonData::str2num($v_values_inv['total']),
    			            'price' => $v_values_inv['price']
    			        );
    			    }
    			    
    			    
    
    			    foreach($post['custom']['description'] as $k_cust_item => $v_cust_item)
    			    {
    			        if(!empty($v_cust_item))
    			        {
    			            $invoice_items[] = array(
    			                'invoice' => $invoice,
    			                'client' => $clientid,
    			                'shortcut' => "custom_hospiz_item",
    			                'description' => $v_cust_item,
    			                'qty' => $post['custom']['qty'][$k_cust_item],
    			                'price' => $post['custom']['price'][$k_cust_item],
    			                'total' => Pms_CommonData::str2num($post['custom']['total'][$k_cust_item]),
    			                'special' => '0',
    			                'custom' => '1'
    			            );
    			        }
    			    }
    			    
    			    if(count($invoice_items) > 0)
    			    {
    			        //insert many records with one query!!
    			        $collection = new Doctrine_Collection('HospizInvoiceItems');
    			        $collection->fromArray($invoice_items);
    			        $collection->save();
    			    }
			    
				}
				
				if($status != '0') //dont change status when is paid and edited
				{
					$update_invoice_status = Doctrine_Query::create()
						->update("HospizInvoices")
						->set('status', $status)
						->where('id ="' . $invoice . '"')
						->andWhere('status != "3"')
						->andWhere('status != "5"');

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
				->update("HospizInvoices")
				->set('status', '4')
				->set('isdelete', '1')
				->where('id ="' . $invoice . '"')
				->andWhere('status != "3"')
				->andWhere('status != "5"');
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

		public function ToggleStatusInvoices($iids, $status, $clientid = false)
		{
			//setStatus of multiple client invoices **
			$iids[] = "99999999999999999";

			if(count($iids) > 0)
			{
				/* ------------------- Status Client Invoice  START -------------- */
				$statusInvoices = Doctrine_Query::create()
					->update("HospizInvoices")
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
				$statusInvoices->whereIn('id', $iids)
					->andWhere('isdelete =0');
				$d = $statusInvoices->execute();

				//update invoicenumber
				if($status == '2' && $clientid)
				{
					$mp_invoice = new HospizInvoices();

					foreach($iids as $k_inv_nr => $v_inv_nr)
					{
						$mp_invoice_number = $mp_invoice->get_next_invoice_number($clientid);
 
						$prefix = $mp_invoice_number['prefix'];
						$invoicenumber = $mp_invoice_number['invoicenumber'];

						$update_inv = Doctrine_Core::getTable('HospizInvoices')->findOneByIdAndStatusAndIsdeleteAndPrefix($v_inv_nr, '2', '0', 'TEMP_');
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
			/* ------------------- Get Client Invoice Payments   START -------------- */
			$invoices = Doctrine_Query::create()
				->select("*, SUM(amount) as PaidAmount")
				->from('HospizInvoicePayments')
				->Where("invoice='" . $post['invoiceId'] . "'")
				->andWhere('isdelete = 0');
			$itemInvArray = $invoices->fetchArray();

			/* ------------------- Update Client Invoice   START -------------- */
			$lastPay = end($itemInvArray);

			$updateCI = Doctrine::getTable('HospizInvoices')->findOneById($post['invoiceId']);
			$curentInvoiceArr = $updateCI->toArray();

			if($post['paymentAmount'] == "0.00" && $curentInvoiceArr['invoice_total'] != '0.00' && $post['mark_as_paid'] == "1")
			{
				if($lastPay['PaidAmount'])
				{
					$paid_ammount = $lastPay['PaidAmount'];
				}
				else
				{
					$paid_ammount = "0.00";
				}

				//add full payment in case of mark as paid for non 0.00 invoice
				$post['paymentAmount'] = ($curentInvoiceArr['invoice_total'] - $paid_ammount);
			}

			//bccomp returns 0 if the two operands are equal, 1 if the left_operand is larger than the right_operand, -1 otherwise.
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
				$paid_value = Pms_CommonData::str2num($lastPay['PaidAmount'] + Pms_CommonData::str2num($post['paymentAmount']));
				$total_value = $curentInvoiceArr['invoice_total'];

				if(bccomp($paid_value, $total_value) == 0)
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

			/* ------------------- Add Invoice Payment START -------------- */
			$invPayment = new HospizInvoicePayments();
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
					->update("HospizInvoices")
					->set('isdelete', "1")
					->set('status', "4")
					->whereIn('id', $iids)
					->andWhere('isdelete =0')
					->andWhere('status != "3"')
					->andWhere('status != "5"');

				$d = $delInvoices->execute();
			}
		}

		public function archive_multiple_invoices($iids, $clientid)
		{
			$iids[] = "99999999999999999";
			$iids = array_values(array_unique($iids));

			if(count($iids) > 0)
			{
				$archive_invoices = Doctrine_Query::create()
					->update("HospizInvoices")
					->set('isarchived', "1")
					->whereIn('id', $iids)
					->andWhere('client = "' . $clientid . '"')
					->andWhere('isdelete ="0"');
				$archive = $archive_invoices->execute();
			}
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
		    
		    $ins_inv = new HospizInvoices();
		    $ins_inv->invoice_start = $invoice_start;
		    $ins_inv->invoice_end = $invoice_end;
		    $ins_inv->start_active = $start_active; //first product day in period
		    $ins_inv->end_active = $end_active; //last product day in period
		    $ins_inv->start_sapv = $start_sapv; //allready formated as db format
		    $ins_inv->end_sapv = $end_sapv; //allready formated as db format
		    $ins_inv->sapv_approve_date = $sapv_approve_date;
		    $ins_inv->sapv_approve_nr = $post['sapv_approve_nr'];
		    $ins_inv->ipid = $post['ipid'];
		    
		    $ins_inv->client = $post['clientid'];
		    $ins_inv->client_name = $post['client_ik'];
		    $ins_inv->prefix = $post['prefix'];
		    $ins_inv->invoice_number = $post['invoice_number'];
		    $ins_inv->invoice_total = $post['invoice_total'];
		    $ins_inv->address = $post['address'];
		    $ins_inv->footer = $post['footer'];
		    
		    $ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020
		    $ins_inv->custom_invoice = 'custom_invoice';      //ISPC-2747 pct.b Lore 27.11.2020
		    
		    $ins_inv->first_name = $post['first_name'];
		    $ins_inv->last_name = $post['last_name'];
		    $ins_inv->birthdate = $birthdate;
		    $ins_inv->street = $post['street'];
		    $ins_inv->patient_care = $post['patient_pflegestufe'];
		    $ins_inv->insurance_no = $post['insurance_no'];
		    $ins_inv->debtor_number = $post['debtor_number'];
		    $ins_inv->ppun = $post['ppun'];
		    $ins_inv->paycenter = $post['paycenter'];
		    
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
		            $collection = new Doctrine_Collection('HospizInvoiceItems');
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
		        
		        $ins_inv = Doctrine::getTable('HospizInvoices')->findOneById($invoice_id);
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
		        $ins_inv->start_sapv = $start_sapv; //allready formated as db format
		        $ins_inv->end_sapv = $end_sapv; //allready formated as db format
		        $ins_inv->sapv_approve_date = $sapv_approve_date;
		        $ins_inv->sapv_approve_nr = $post['sapv_approve_nr'];
		        $ins_inv->ipid = $post['ipid'];
		        
		        $ins_inv->client = $post['clientid'];
		        $ins_inv->client_name = $post['client_ik'];
		        $ins_inv->prefix = $post['prefix'];
		        $ins_inv->invoice_number = $post['invoice_number'];
		        $ins_inv->invoice_total = $post['invoice_total'];
		        $ins_inv->address = $post['address'];
		        $ins_inv->footer = $post['footer'];
		        
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
		                $collection = new Doctrine_Collection('HospizInvoiceItems');
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
		        ->update('HospizInvoiceItems')
		        ->set('isdelete', "1")
		        ->where('invoice = "' . $invoice . '"')
		        ->andWhere('isdelete = "0"');
		        $q_res = $q->execute();
		    }
		}
		
	}

?>