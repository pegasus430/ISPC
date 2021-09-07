<?php

	require_once("Pms/Form.php");

	class Application_Form_RpInvoices extends Pms_Form {

		//used in edit
		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$validate = new Pms_Validation();

			if($post['period_id'] == '0')
			{
				$this->error_message['invoice_vv'] = $Tr->translate('no_invoice_vv');
				return false;
			}

			if(!$validate->isdate($post['curent_sapv_from']))
			{
				$this->error_message['sapv_date_from'] = $Tr->translate('invalid_sapv_start_date');
				return false;
			}

			if(!$validate->isdate($post['curent_sapv_till']))
			{
				$this->error_message['sapv_date_till'] = $Tr->translate('invalid_sapv_end_date');
				return false;
			}

			if(!$validate->isdate($post['invoice_date_from']))
			{
				$this->error_message['invoice_date_from'] = $Tr->translate('invalid_invoice_start_date');
				return false;
			}

			if(!$validate->isdate($post['invoice_date_till']))
			{
				$this->error_message['invoice_date_till'] = $Tr->translate('invalid_invoice_end_date');
				return false;
			}

			if($validate->isdate($post['invoice_date_from']) && $validate->isdate($post['invoice_date_till']))
			{
				$start = strtotime($post['invoice_date_from']);
				$end = strtotime($post['invoice_date_till']);
			}

			if($validate->isdate($post['curent_sapv_from']) && $validate->isdate($post['curent_sapv_till']))
			{
				$start_sapv = strtotime($post['curent_sapv_from']);
				$end_sapv = strtotime($post['curent_sapv_till']);
			}

			if($start_sapv <= $end_sapv)
			{
				if($start <= $end)
				{
					return true;
				}
				else
				{
					$this->error_message['date'] = $Tr->translate('invalid_invoice_date_period');
					return false;
				}
			}
			else
			{
				$this->error_message['sapv_date'] = $Tr->translate('invalid_sapv_date_period');
				return false;
			}
		}

		public function create_invoice($clientid, $post)
		{
			$Tr = new Zend_View_Helper_Translate();

			$ins_inv = new RpInvoices();
			$ins_inv->ipid = $post['ipid'];
			$ins_inv->client = $clientid;

			$ins_inv->krankenkasse = $post['krankenkasse'];
			$ins_inv->patient_name = $post['patient_name'];
			$ins_inv->geb = $post['geb'];
			$ins_inv->kassen_nr = $post['kassen_nr'];
			$ins_inv->versicherten_nr = $post['versicherten_nr'];
			$ins_inv->ins_status = $post['status'];
			$ins_inv->betriebsstatten_nr = $post['betriebsstatten_nr'];
			$ins_inv->arzt_nr = $post['arzt_nr'];
			$ins_inv->topdatum = $post['topdatum'];
			$ins_inv->client_ik = $post['client_ik'];

			$ins_inv->invoice_start = date('Y-m-d H:i:s', strtotime($post['invoice_date_from']));
			$ins_inv->invoice_end = date('Y-m-d H:i:s', strtotime($post['invoice_date_till']));
			$ins_inv->main_diagnosis = $post['main_diagnosis'];

			$ins_inv->sapv_id = date('Y-m-d H:i:s', strtotime($post['sapv_id']));
			$ins_inv->sapv_start = date('Y-m-d H:i:s', strtotime($post['curent_sapv_from']));
			$ins_inv->sapv_end = date('Y-m-d H:i:s', strtotime($post['curent_sapv_till']));

			$ins_inv->prefix = $post['prefix'];
			$ins_inv->invoice_number = $post['invoice_number'];
			$ins_inv->invoice_total = Pms_CommonData::str2num($post['grand_total']);
			
			$ins_inv->show_boxes = 'show_box_active,show_box_patient,show_box_sapv';               //ISPC-2747 pct.b Lore 27.11.2020
			
			$ins_inv->status = '1'; // DRAFT - ENTWURF
			$ins_inv->stample = $post['stample'];

			if(!empty($post['sapv_erst']))
			{
				$ins_inv->sapv_erst = $post['sapv_erst'];
			}
			else
			{
				$ins_inv->sapv_erst = "0";
			}

			if(!empty($post['sapv_folge']))
			{
				$ins_inv->sapv_folge = $post['sapv_folge'];
			}
			else
			{
				$ins_inv->sapv_folge = "0";
			}

			//new columns
			//$ins_inv->client_ik = $post['client_ik'];
			$ins_inv->first_name = $post['patientdetails']['first_name'];
			$ins_inv->last_name = $post['patientdetails']['last_name'];
			//$ins_inv->birthdate = date('Y-m-d', strtotime($post['patientdetails']['birthd']));
			//$ins_inv->insurance_no = $post['insurance_no'];
			$ins_inv->street = $post['patientdetails']['street'];
			//--

			$ins_inv->date_delivery = $post['date_delivery'];
			$ins_inv->sig_date = $post['sig_date'];
			$ins_inv->bottom_signature = $post['bottom_signature'];

			$ins_inv->save();
			$inserted_id = $ins_inv->id;

			if($inserted_id)
			{
				foreach($post['items'] as $k_item => $v_item)
				{
					$description = $Tr->translate($k_item);
					$invoice_items[] = array(
						'invoice' => $inserted_id,
						'client' => $clientid,
						'shortcut' => $v_item['shortcut'],
						'description' => $description,
						'qty_home' => $v_item['qty_gr']['p_home'],
						'qty_nurse' => $v_item['qty_gr']['p_nurse'],
						'qty_hospiz' => $v_item['qty_gr']['p_hospiz'],
						'price_home' => Pms_CommonData::str2num($v_item['price_gr']['p_home']),
						'price_nurse' => Pms_CommonData::str2num($v_item['price_gr']['p_nurse']),
						'price_hospiz' => Pms_CommonData::str2num($v_item['price_gr']['p_hospiz']),
						'total_home' => Pms_CommonData::str2num($v_item['total']['p_home']),
						'total_nurse' => Pms_CommonData::str2num($v_item['total']['p_nurse']),
						'total_hospiz' => Pms_CommonData::str2num($v_item['total']['p_hospiz']),
						'isdelete' => '0',
					);
				}

				$collection = new Doctrine_Collection('RpInvoiceItems');
				$collection->fromArray($invoice_items);
				$collection->save();

				return true;
			}
			else
			{
				return false;
			}
		}

		public function edit_invoice($invoice, $clientid, $post, $status)
		{
			$Tr = new Zend_View_Helper_Translate();

			if($invoice)
			{

				$sapv_erst = "0";
				$sapv_folge = "0";

				if(!empty($post['sapv_erst']))
				{
					$sapv_erst = '1';
				}

				if(!empty($post['sapv_folge']))
				{
					$sapv_folge = '1';
				}


				//invoice start -- end
				$invoice_start = '0000-00-00 00:00:00';
				if(strlen($post['invoice_date_from']) > 0 && date('Y', strtotime($post['invoice_date_from'])) != '1970')
				{
					$invoice_start = date('Y-m-d H:i:s', strtotime($post['invoice_date_from']));
				}

				$invoice_end = '0000-00-00 00:00:00';
				if(strlen($post['invoice_date_till']) > 0 && date('Y', strtotime($post['invoice_date_till'])) != '1970')
				{
					$invoice_end = date('Y-m-d H:i:s', strtotime($post['invoice_date_till']));
				}

				//sapv start -- end
				$sapv_start = '0000-00-00 00:00:00';
				if(strlen($post['curent_sapv_from']) > 0 && date('Y', strtotime($post['curent_sapv_from'])) != '1970')
				{
					$sapv_start = date('Y-m-d H:i:s', strtotime($post['curent_sapv_from']));
				}

				$sapv_end = '0000-00-00 00:00:00';
				if(strlen($post['curent_sapv_till']) > 0 && date('Y', strtotime($post['curent_sapv_till'])) != '1970')
				{
					$sapv_end = date('Y-m-d H:i:s', strtotime($post['curent_sapv_till']));
				}

				//delivery date
				$date_delivery = '0000-00-00 00:00:00';
				if(strlen($post['date_delivery']) > 0 && date('Y', strtotime($post['date_delivery'])) != '1970')
				{
					$date_delivery = date('Y-m-d H:i:s', strtotime($post['date_delivery']));
				}

				//signature date
//				$date_signature = '0000-00-00 00:00:00';
//				if(strlen($post['sig_date'])>0 && date('Y', strtotime($post['sig_date'])) != '1970')
//				{
//					$date_signature = date('Y-m-d H:i:s', strtotime($post['sig_date']));
//				}
				//update initial invoice
				$update_invoice = Doctrine_Query::create()
					->update("RpInvoices")
					->set('krankenkasse', "'" . $post['krankenkasse'] . "'")
//					->set('stample', "'" . $post['stample'] . "'")
					->set('patient_name', "'" . $post['patient_name'] . "'")
					->set('geb', "'" . $post['geb'] . "'")
					->set('kassen_nr', "'" . $post['kassen_nr'] . "'")
					->set('versicherten_nr', "'" . $post['versicherten_nr'] . "'")
					->set('ins_status', "'" . $post['status'] . "'")
					->set('betriebsstatten_nr', "'" . $post['betriebsstatten_nr'] . "'")
					->set('arzt_nr', "'" . $post['arzt_nr'] . "'")
					->set('topdatum', "'" . $post['topdatum'] . "'")
					->set('client_ik', "'" . $post['client_ik'] . "'")
					->set('invoice_start', "'" . $invoice_start . "'")
					->set('invoice_end', "'" . $invoice_end . "'")
					->set('sapv_start', "'" . $sapv_start . "'")
					->set('sapv_end', "'" . $sapv_end . "'")
					->set('sapv_erst', $sapv_erst)
					->set('sapv_folge', $sapv_folge)
					->set('main_diagnosis', "'" . $post['main_diagnosis'] . "'")
					->set('date_delivery', "'" . $date_delivery . "'")
					->set('sig_date', "'" . $post['sig_date'] . "'")
					->set('bottom_signature', "'" . $post['bottom_signature'] . "'")
					->set('invoice_number', "'" . $post['invoice_number'] . "'")
					->set('prefix', "'" . $post['prefix'] . "'")
					->where('id ="' . $invoice . '"');

				if($status != '1' && $status != '4' && $status != '0' && strlen($post['pdf']) == 0)
				{
					$update_invoice->set('completed_date', "'" . date('Y-m-d H:i:s', strtotime($post['completed_date'])) . "'");
				}
				if($status == '4')
				{
					$update_invoice->set('isdelete', '1');
				}
				$update_invoice->execute();


				$update_invoice = Doctrine::getTable('RpInvoices')->findOneById($invoice);
				$update_invoice->stample = $post['stample'];
				$update_invoice->save();


				//insert invoice custom items
				foreach($post['items'] as $k_shortcut => $v_short_details)
				{

					$shortcut_description = $Tr->translate($k_shortcut);
					$invoice_items[] = array(
						'invoice' => $invoice,
						'client' => $clientid,
						'shortcut' => $k_shortcut,
						'description' => $shortcut_description,
						'qty_home' => $v_short_details['qty_gr']['p_home'],
						'qty_nurse' => $v_short_details['qty_gr']['p_nurse'],
						'qty_hospiz' => $v_short_details['qty_gr']['p_hospiz'],
						'price_home' => $v_short_details['price_gr']['p_home'],
						'price_nurse' => $v_short_details['price_gr']['p_nurse'],
						'price_hospiz' => $v_short_details['price_gr']['p_hospiz'],
						'total_home' => $v_short_details['total']['p_home'],
						'total_nurse' => $v_short_details['total']['p_nurse'],
						'total_hospiz' => $v_short_details['total']['p_hospiz'],
						'isdelete' => '0',
					);
				}

				$collection = new Doctrine_Collection('RpInvoiceItems');
				$collection->fromArray($invoice_items);
				$collection->save();

				if($status != '0') //dont change status when is paid and edited
				{
					$update_invoice_status = Doctrine_Query::create()
						->update("RpInvoices")
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
				->update("RpInvoices")
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

		public function delete_multiple_invoices($iids)
		{
			$iids[] = "99999999999999999";

			if(count($iids) > 0)
			{
				$delInvoices = Doctrine_Query::create()
					->update("RpInvoices")
					->set('isdelete', "1")
					->set('status', "4")
					->whereIn('id', $iids)
					->andWhere('isdelete =0');
				$d = $delInvoices->execute();
			}
		}

		public function ToggleStatusInvoices($iids, $status, $clientid = false)
		{
			//	setStatus of multiple client invoices **
			if(count($iids) > 0)
			{
				/* ---------------------- Status Client Invoice START ------------------- */
				$statusInvoices = Doctrine_Query::create()
					->update("RpInvoices")
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
					$rp_invoice = new RpInvoices();

					foreach($iids as $k_inv_nr => $v_inv_nr)
					{
						$rp_invoice_number = $rp_invoice->get_next_invoice_number($clientid);

						$prefix = $rp_invoice_number['prefix'];
						$invoicenumber = $rp_invoice_number['invoicenumber'];

						$update_inv = Doctrine_Core::getTable('RpInvoices')->findOneByIdAndStatusAndIsdeleteAndPrefix($v_inv_nr, '2', '0', 'TEMP_');
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
				->from('RpInvoicePayments')
				->Where("invoice='" . $post['invoiceId'] . "'")
				->andWhere('isdelete = 0');
			$itemInvArray = $invoices->fetchArray();

			/* --------------------------- Update Client Invoice   START ------------------- */
			$lastPay = end($itemInvArray);
			$updateCI = Doctrine::getTable('RpInvoices')->findOneById($post['invoiceId']);
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
			$invPayment = new RpInvoicePayments();
			$invPayment->invoice = $post['invoiceId'];
			$invPayment->amount = Pms_CommonData::str2num($post['paymentAmount']);
			$invPayment->comment = $post['paymentComment'];
			$invPayment->paid_date = date("Y-m-d H:i:s", strtotime($post['paymentDate']));
			$invPayment->isdelete = "0";
			$invPayment->save();
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
		    
 		    $ins_inv = new RpInvoices();
 		    $ins_inv->ipid = $post['ipid'];
 		    $ins_inv->client = $post['clientid'];
 		    $ins_inv->geb = $birthdate;
 		    $ins_inv->versicherten_nr = $post['insurance_no'];
 		    $ins_inv->client_ik = $post['client_ik'];
 		    $ins_inv->prefix = $post['prefix'];
 		    $ins_inv->invoice_number = $post['invoice_number'];
 		    $ins_inv->invoice_start = $invoice_start;
 		    $ins_inv->invoice_end = $invoice_end;
 		    $ins_inv->start_active = $start_active; //first product day in period
 		    $ins_inv->end_active = $end_active; //last product day in period
 		    $ins_inv->sapv_start = $start_sapv; //allready formated as db format
 		    $ins_inv->sapv_end = $end_sapv; //allready formated as db format
		    $ins_inv->sapv_approve_date = $sapv_approve_date;
		    $ins_inv->sapv_approve_nr = $post['sapv_approve_nr'];	    

		    $ins_inv->invoice_total = $post['invoice_total'];
		    $ins_inv->address = $post['address'];
		    $ins_inv->footer = $post['footer'];
		    
		    $ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020
		    $ins_inv->custom_invoice = 'custom_invoice';      //ISPC-2747 pct.b Lore 27.11.2020
		    $ins_inv->first_name = $post['first_name'];
		    $ins_inv->last_name = $post['last_name'];
		    $ins_inv->street = $post['street'];
		    $ins_inv->patient_care = $post['patient_pflegestufe'];
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
		                'total_custom' => $post['total'][$k_inv],
		                'custom' => '1',
                        'isdelete' => '0'
		            );
		        }
		        
		        if(count($invoice_items_arr) > 0)
		        {
		            //insert many records with one query!!
		            $collection = new Doctrine_Collection('RpInvoiceItems');
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
		        
		        $ins_inv = Doctrine::getTable('RpInvoices')->findOneById($invoice_id);
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
		        $ins_inv->sapv_start = $start_sapv; //allready formated as db format
		        $ins_inv->sapv_end = $end_sapv; //allready formated as db format
		        $ins_inv->sapv_approve_date = $sapv_approve_date;
		        $ins_inv->sapv_approve_nr = $post['sapv_approve_nr'];
		        $ins_inv->ipid = $post['ipid'];
		        $ins_inv->first_name = $post['first_name'];
		        $ins_inv->last_name = $post['last_name'];
		        $ins_inv->geb = $birthdate;
		        $ins_inv->patient_care = $post['patient_pflegestufe'];
		        $ins_inv->versicherten_nr = $post['insurance_no'];
		        
		        $ins_inv->client = $post['clientid'];
		        $ins_inv->client_ik = $post['client_ik'];
		        $ins_inv->prefix = $post['prefix'];
		        $ins_inv->invoice_number = $post['invoice_number'];
		        $ins_inv->invoice_total = $post['invoice_total'];
		        $ins_inv->address = $post['address'];
		        $ins_inv->footer = $post['footer'];
		        
		        $ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020
		        $ins_inv->first_name = $post['first_name'];
		        $ins_inv->last_name = $post['last_name'];
		        $ins_inv->street = $post['street'];
		        $ins_inv->patient_care = $post['patient_pflegestufe'];
		        $ins_inv->debtor_number = $post['debtor_number'];
		        $ins_inv->ppun = $post['ppun'];
		        $ins_inv->paycenter = $post['paycenter'];
		        
		        $ins_inv->debtor_number = $post['debtor_number'];
		        $ins_inv->ppun = $post['ppun'];
		        $ins_inv->paycenter = $post['paycenter'];
		        
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
		                    'total_custom' => $post['total'][$k_inv],
		                    'custom' => '1',
		                    'isdelete' => '0'
		                );
		            }
		            
		            if(count($invoice_items_arr) > 0)
		            {
		                self::delete_items($ins_id);
		                
		                //insert many records with one query!!
		                $collection = new Doctrine_Collection('RpInvoiceItems');
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
		        ->update('RpInvoiceItems')
		        ->set('isdelete', "1")
		        ->where('invoice = "' . $invoice . '"')
		        ->andWhere('isdelete = "0"');
		        $q_res = $q->execute();
		    }
		}
	
		/**
		 * ISPC-2313 Ancuta 07.12.2020
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
		    ->update("RpInvoices")
		    ->set('isarchived', "1")
		    ->whereIn('id', $iids)
		    ->andWhere('client = "' . $clientid . '"')
		    ->andWhere('isdelete ="0"');
		    $archive = $archive_invoices->execute();
		}
		
	}

?>