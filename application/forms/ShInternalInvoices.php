<?php

	require_once("Pms/Form.php");

	class Application_Form_ShInternalInvoices extends Pms_Form {

		public function insert_invoice($master_data)
		{
			$Tr = new Zend_View_Helper_Translate();
			$sh_invoices = new ShInternalInvoices();

			foreach($master_data['invoices'] as $inv_userid => $v_invoice_data)
			{
				$sh_invoices_number = $sh_invoices->get_next_invoice_number($master_data['client']['id'], true);
				$prefix = $sh_invoices_number['prefix'];
				$invoicenumber = $sh_invoices_number['invoicenumber'];

				$inserted_id = '';
				$invoice_total = '';
				$invoice_items = array();

				$format = 'Y-m-d H:i:s';

				$invoice_start = $master_data['users'][$inv_userid]['invoice_data']['period']['start'];
				$invoice_end = $master_data['users'][$inv_userid]['invoice_data']['period']['end'];

				$ins_inv = new ShInternalInvoices();
				$ins_inv->user = $master_data['users'][$inv_userid]['invoice_data']['user'];
				$ins_inv->invoice_start = date($format, strtotime($invoice_start));
				$ins_inv->invoice_end = date($format, strtotime($invoice_end));
				$ins_inv->invoiced_month = $master_data['invoiced_month'];
				$ins_inv->client = $master_data['client']['id'];
				$ins_inv->prefix = $prefix;
				$ins_inv->invoice_number = $invoicenumber;
				$ins_inv->invoice_total = '';
				
				$ins_inv->show_boxes = 'show_box_active,show_box_patient,show_box_sapv';               //ISPC-2747 pct.b Lore 27.11.2020
				
				$ins_inv->recipient = $master_data['recipient'][$inv_userid];
				$ins_inv->status = '1'; // DRAFT - ENTWURF
				$ins_inv->save();

				$inserted_id = $ins_inv->id;
				if($inserted_id)
				{
					foreach($v_invoice_data['items'] as $k_short => $v_short)
					{
						$invoice_items[] = array(
							'invoice' => $inserted_id,
							'client' => $master_data['client']['id'],
							'shortcut' => '',
							'description' => $Tr->translate('shortcut_name_' . $v_short['shortcut']),
							'qty' => $v_short['qty'],
							'price' => $master_data['invoices'][$inv_userid]['pricelist'][0][$v_short['shortcut']]['price'],
							'total' => ($v_short['qty'] * $master_data['invoices'][$inv_userid]['pricelist'][0][$v_short['shortcut']]['price']),
							'custom' => $v_short['custom'],
							'isdelete' => '0',
						);

						$invoice_total += (Pms_CommonData::str2num($master_data['invoices'][$inv_userid]['pricelist'][0][$v_short['shortcut']]['price']) * $v_short['qty']);
					}

					$collection = new Doctrine_Collection('ShInternalInvoiceItems');
					$collection->fromArray($invoice_items);
					$collection->save();

					$update_invoice = Doctrine_Query::create()
						->update("ShInternalInvoices")
						->set('invoice_total', "'" . Pms_CommonData::str2num($invoice_total) . "'")
						->where('id ="' . $inserted_id . '"')
						->execute();
					//get inserted ids in case of pdf print when generated
					$inserted_ids[] = $inserted_id;
				}
			}

			return $inserted_ids;
		}

		public function edit_invoice($invoice, $clientid, $post, $status)
		{
			if($invoice)
			{
				//update initial invoice
				if($status == '4')
				{
					$update_invoice = Doctrine_Query::create()
						->update("ShInternalInvoices")
						->where('id ="' . $invoice . '"')
						->set('isdelete', '1')
						->andWhere('status != "3"')
						->andWhere('status != "5"');
					$update_invoice->execute();
				}

				if($status != '1' && $status != '4' && strlen($post['pdf']) == 0)
				{
					$update_invoice = Doctrine_Query::create()
						->update("ShInternalInvoices")
						->where('id ="' . $invoice . '"')
						->set('invoice_number', "'" . $post['invoice_number'] . "'")
						->set('prefix', "'" . $post['prefix'] . "'")
						->set('completed_date', "'" . date('Y-m-d H:i:s', strtotime($post['completed_date'])) . "'");
					$update_invoice->execute();
				}

				//update recipient & comment (footer) (html update issues)
				$update_invoice = Doctrine::getTable('ShInternalInvoices')->findOneById($invoice);
				$update_invoice->recipient = $post['recipient'];
				$update_invoice->comment = $post['comment'];
				$update_invoice->save();

				//insert all form items
				if($_POST['readonlyitems'] == "0")
				{
					//mark as deleted all items
					$this->delete_all_items($invoice);

					$new_invoice_total = '';
					foreach($post['custom'] as $k_item => $v_custom)
					{
						if(strlen(trim(rtrim($post['shortcut'][$k_item]))) != "0" || strlen(trim(rtrim($post['description'][$k_item]))) != '0')
						{
							$new_invoice_items[] = array(
								'invoice' => $invoice,
								'client' => $clientid,
								'shortcut' => $post['shortcut'][$k_item],
								'description' => $post['description'][$k_item],
								'qty' => $post['qty'][$k_item],
								'price' => Pms_CommonData::str2num($post['price'][$k_item]),
								'total' => Pms_CommonData::str2num($post['total'][$k_item]),
								'custom' => $v_custom,
								'isdelete' => '0',
							);
						}
					}


					//update invoice total
					if($new_invoice_items)
					{
						$collection = new Doctrine_Collection('ShInternalInvoiceItems');
						$collection->fromArray($new_invoice_items);
						$collection->save();

						$new_invoice_total = $post['invoice_total'];

						//update invoice total
						$update_invoice = Doctrine_Query::create()
							->update("ShInternalInvoices")
							->set('invoice_total', "'" . $new_invoice_total . "'")
							->where('id ="' . $invoice . '"')
							->execute();
					}
				}

				if($status != '0') //dont change status when is paid and edited
				{
					$update_invoice_status = Doctrine_Query::create()
						->update("ShInternalInvoices")
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
				->update("ShInternalInvoices")
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
			//		setStatus of multiple client invoices **
			$iids[] = "99999999999999999";

			if(count($iids) > 0)
			{
				/* ------------------- Status Client Invoice  START -------------- */
				$statusInvoices = Doctrine_Query::create()
					->update("ShInternalInvoices")
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
					$mp_invoice = new ShInternalInvoices();

					foreach($iids as $k_inv_nr => $v_inv_nr)
					{
						$mp_invoice_number = $mp_invoice->get_next_invoice_number($clientid);

						$prefix = $mp_invoice_number['prefix'];
						$invoicenumber = $mp_invoice_number['invoicenumber'];

						$update_inv = Doctrine_Core::getTable('ShInternalInvoices')->findOneByIdAndStatusAndIsdeleteAndPrefix($v_inv_nr, '2', '0', 'TEMP_');
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
				->from('ShInternalInvoicePayments')
				->Where("invoice='" . $post['invoiceId'] . "'")
				->andWhere('isdelete = 0');
			$itemInvArray = $invoices->fetchArray();

			/* ------------------- Update Client Invoice   START -------------- */
			$lastPay = end($itemInvArray);

			$updateCI = Doctrine::getTable('ShInternalInvoices')->findOneById($post['invoiceId']);
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
			$invPayment = new ShInternalInvoicePayments();
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
					->update("ShInternalInvoices")
					->set('isdelete', "1")
					->set('status', "4")
					->whereIn('id', $iids)
					->andWhere('isdelete =0')
					->andWhere('status != "3"')
					->andWhere('status != "5"');
				$d = $delInvoices->execute();
			}
		}

		private function delete_all_items($invoice)
		{
			if($invoice)
			{
				$del_inv_items = Doctrine_Query::create()
					->update("ShInternalInvoiceItems")
					->set('isdelete', "1")
					->where('invoice = "' . $invoice . '"')
					->execute();
			}
		}

		public function archive_multiple_invoices($iids, $clientid)
		{
			$iids[] = "99999999999999999";
			$iids = array_values(array_unique($iids));

			if(count($iids) > 0)
			{
				$archive_invoices = Doctrine_Query::create()
					->update("ShInternalInvoices")
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
		    
		    $ins_inv = new ShInternalInvoices();
		    $ins_inv->invoice_start = $invoice_start;
		    $ins_inv->invoice_end = $invoice_end;
		    $ins_inv->user = $post['user'];
		    
		    $ins_inv->client = $post['clientid'];
		    $ins_inv->client_name = $post['client_ik'];
		    $ins_inv->prefix = $post['prefix'];
		    $ins_inv->invoice_number = $post['invoice_number'];
		    $ins_inv->invoice_total = $post['invoice_total'];
		    
		    $ins_inv->completed_date = date('Y-m-d H:i:s');
		    $ins_inv->recipient = $post['header'];
		    
		    $ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020
		    $ins_inv->custom_invoice = 'custom_invoice';      //ISPC-2747 pct.b Lore 27.11.2020
		    $ins_inv->start_active = $start_active; //first product day in period
		    $ins_inv->end_active = $end_active; //last product day in period
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
		    $ins_inv->footer = $post['footer'];
		    
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
		            $collection = new Doctrine_Collection('ShInternalInvoiceItems');
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
		        
		        $ins_inv = Doctrine::getTable('ShInternalInvoices')->findOneById($invoice_id);
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
		        $ins_inv->user = $post['user'];
		        
		        $ins_inv->client = $post['clientid'];
		        $ins_inv->client_name = $post['client_ik'];
		        $ins_inv->prefix = $post['prefix'];
		        $ins_inv->invoice_number = $post['invoice_number'];
		        $ins_inv->invoice_total = $post['invoice_total'];
		        
		        $ins_inv->completed_date = date('Y-m-d H:i:s');
		        $ins_inv->recipient = $post['header'];
		        
		        $ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020
		        $ins_inv->start_active = $start_active; //first product day in period
		        $ins_inv->end_active = $end_active; //last product day in period
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
		        $ins_inv->footer = $post['footer'];
		        
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
		                $collection = new Doctrine_Collection('ShInternalInvoiceItems');
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
		        ->update('ShInternalInvoiceItems')
		        ->set('isdelete', "1")
		        ->where('invoice = "' . $invoice . '"')
		        ->andWhere('isdelete = "0"');
		        $q_res = $q->execute();
		    }
		}
		
	}

?>