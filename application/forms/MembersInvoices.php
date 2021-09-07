<?php

require_once("Pms/Form.php");

class Application_Form_MembersInvoices extends Pms_Form 
{

		public function insert_invoice($master_data)
		{
			$Tr = new Zend_View_Helper_Translate();
			$members_invoices = new MembersInvoices();

			foreach($master_data['invoices'] as $inv_member_id => $v_invoice_data)
			{
				$members_invoices_number = $members_invoices->get_next_invoice_number($master_data['client']['id'], true);
				$prefix = $members_invoices_number['prefix'];
				$invoicenumber = $members_invoices_number['invoicenumber'];

				$inserted_id = '';
				$invoice_total = '';
				$invoice_items = array();

				$format = 'Y-m-d H:i:s';

				$invoice_start = $master_data['members'][$inv_member_id]['invoice_data']['period']['start'];
				$invoice_end = $master_data['members'][$inv_member_id]['invoice_data']['period']['end'];

				$membership_start = $master_data['members'][$inv_member_id]['invoice_data']['membership_period']['start'];
				$membership_end = $master_data['members'][$inv_member_id]['invoice_data']['membership_period']['end'];

				$ins_inv = new MembersInvoices();
				$ins_inv->member = $master_data['members'][$inv_member_id]['invoice_data']['member'];
				$ins_inv->invoice_start = date($format, strtotime($invoice_start));
				$ins_inv->invoice_end = date($format, strtotime($invoice_end));
				$ins_inv->membership_start = date($format, strtotime($membership_start));
				$ins_inv->membership_end = date($format, strtotime($membership_end));
				$ins_inv->membership_data = $master_data['membership_data'];
				$ins_inv->invoiced_month = $master_data['invoiced_month'];
				$ins_inv->client = $master_data['client']['id'];
				$ins_inv->prefix = $prefix;
				$ins_inv->invoice_number = $invoicenumber;
				$ins_inv->invoice_total = '';
				$ins_inv->recipient = $master_data['recipient'][$inv_member_id];
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
							'shortcut' => $v_short['shortcut'],
							'description' => $v_short['description'],
							'qty' => $v_short['qty'],
							'price' => $v_short['price'],
							'total' => ($v_short['qty'] * $v_short['price']),
							'custom' => $v_short['custom'],
							'isdelete' => '0',
						);

						$invoice_total += (Pms_CommonData::str2num($v_short['price']) * $v_short['qty']);
					}

					$collection = new Doctrine_Collection('MembersInvoiceItems');
					$collection->fromArray($invoice_items);
					$collection->save();

					$update_invoice = Doctrine_Query::create()
						->update("MembersInvoices")
						->set('invoice_total', "'" . Pms_CommonData::str2num($invoice_total) . "'")
						->where('id ="' . $inserted_id . '"')
						->execute();
					
					//create payments for invoices with sepa-settings(=installments=payments)
					/*
					if (!empty($v_invoice_data['payments'])) {
						$inv_payments_array = array();
						foreach($v_invoice_data['payments'] as $row) { 
							$inv_payments_array[] = array(
									'invoice' => $inserted_id,
									'amount' => Pms_CommonData::str2num($row['amount']),
									'status' => 'created',
									'comment' => '',
									'scheduled_due_date' => date("Y-m-d H:i:s", strtotime($row['payment_date'])),
									'paid_date' => null,
									'isdelete' => "0"
			
							);
							
						}
						//add to dbf
						$mip_obj = new MembersInvoicePayments();
						$mip_obj->set_new_collection($inv_payments_array);
					}
					*/
				
				
					//get inserted ids in case of pdf print when generated
					$inserted_ids[] = $inserted_id;
				}
			}

			return $inserted_ids;
		}
		
		public function auto_insert_invoice($master_data_multiple)
		{
			$Tr = new Zend_View_Helper_Translate();
			$members_invoices = new MembersInvoices();
			$inserted_ids =  array();
			
			foreach($master_data_multiple as $k =>$master_data)
			{     
			     foreach($master_data['invoices'] as $inv_member_id => $v_invoice_data)
    			{
    				$members_invoices_number = $members_invoices->get_next_invoice_number($master_data['members'][$inv_member_id]['invoice_data']['client'], true);
    				$prefix = $members_invoices_number['prefix'];
    				$invoicenumber = $members_invoices_number['invoicenumber'];
    
    				$inserted_id = '';
    				$invoice_total = '';
    				$invoice_items = array();
    
    				$format = 'Y-m-d H:i:s';
    
    				$invoice_start = $master_data['members'][$inv_member_id]['invoice_data']['period']['start'];
    				$invoice_end = $master_data['members'][$inv_member_id]['invoice_data']['period']['end'];
    
    				$membership_start = $master_data['members'][$inv_member_id]['invoice_data']['membership_period']['start'];
    				$membership_end = $master_data['members'][$inv_member_id]['invoice_data']['membership_period']['end'];
    
    				$ins_inv = new MembersInvoices();
    				$ins_inv->member = $master_data['members'][$inv_member_id]['invoice_data']['member'];
    				$ins_inv->invoice_start = date($format, strtotime($invoice_start));
    				$ins_inv->invoice_end = date($format, strtotime($invoice_end));
    				$ins_inv->membership_start = date($format, strtotime($membership_start));
    				$ins_inv->membership_end = date($format, strtotime($membership_end));
    				$ins_inv->membership_data = $master_data['members'][$inv_member_id]['invoice_data']['membership_data']; 
    				$ins_inv->invoiced_month = $master_data['members'][$inv_member_id]['invoice_data']['invoiced_month']; 
    				$ins_inv->client = $master_data['members'][$inv_member_id]['invoice_data']['client'];
    				$ins_inv->prefix = $prefix;
    				$ins_inv->invoice_number = $invoicenumber;
    				$ins_inv->invoice_total = '';
    				$ins_inv->recipient = $master_data['recipient'][$inv_member_id];
    				$ins_inv->status = '1'; // DRAFT - ENTWURF
    				$ins_inv->save();
    
    				$inserted_id = $ins_inv->id;
    				if($inserted_id)
    				{
    					foreach($v_invoice_data['items'] as $k_short => $v_short)
    					{
    						$invoice_items[] = array(
    							'invoice' => $inserted_id,
    							'client' => $master_data['members'][$inv_member_id]['invoice_data']['client'],
    							'shortcut' => $v_short['shortcut'],
    							'description' => $v_short['description'],
    							'qty' => $v_short['qty'],
    							'price' => $v_short['price'],
    							'total' => ($v_short['qty'] * $v_short['price']),
    							'custom' => $v_short['custom'],
    							'isdelete' => '0',
    						);
    
    						$invoice_total += (Pms_CommonData::str2num($v_short['price']) * $v_short['qty']);
    					}
    
    					$collection = new Doctrine_Collection('MembersInvoiceItems');
    					$collection->fromArray($invoice_items);
    					$collection->save();
    
    					$update_invoice = Doctrine_Query::create()
    						->update("MembersInvoices")
    						->set('invoice_total', "'" . Pms_CommonData::str2num($invoice_total) . "'")
    						->where('id ="' . $inserted_id . '"')
    						->execute();
    					//get inserted ids in case of pdf print when generated
    					$inserted_ids[] = $inserted_id;
    				}
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
						->update("MembersInvoices")
						->where('id ="' . $invoice . '"')
						->set('isdelete', '1')
						->andWhere('status != "3"')
						->andWhere('status != "5"');
					$update_invoice->execute();
				}

				if($status != '1' && $status != '4' && strlen($post['pdf']) == 0)
				{
					$update_invoice = Doctrine_Query::create()
						->update("MembersInvoices")
						->where('id ="' . $invoice . '"')
						->set('invoice_number', "'" . $post['invoice_number'] . "'")
						->set('prefix', "'" . $post['prefix'] . "'")
						->set('completed_date', "'" . date('Y-m-d H:i:s', strtotime($post['completed_date'])) . "'");
					$update_invoice->execute();
				}

				//update recipient & comment (footer) (html update issues)
				$update_invoice = Doctrine::getTable('MembersInvoices')->findOneById($invoice);
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
						$collection = new Doctrine_Collection('MembersInvoiceItems');
						$collection->fromArray($new_invoice_items);
						$collection->save();

						$new_invoice_total = $post['invoice_total'];

						//update invoice total
						$update_invoice = Doctrine_Query::create()
							->update("MembersInvoices")
							->set('invoice_total', "'" . $new_invoice_total . "'")
							->where('id ="' . $invoice . '"')
							->execute();
					}
				}

				if($status != '0') //dont change status when is paid and edited
				{
					$update_invoice_status = Doctrine_Query::create()
						->update("MembersInvoices")
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
				->update("MembersInvoices")
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
// 			$iids[] = "99999999999999999";

			if(is_array($iids) && count($iids) > 0)
			{
				/* ------------------- Status Client Invoice  START -------------- */
				$statusInvoices = Doctrine_Query::create()
					->update("MembersInvoices")
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
					$mp_invoice = new MembersInvoices();

					foreach($iids as $k_inv_nr => $v_inv_nr)
					{
						$mp_invoice_number = $mp_invoice->get_next_invoice_number($clientid);

						$prefix = $mp_invoice_number['prefix'];
						$invoicenumber = $mp_invoice_number['invoicenumber'];

						$update_inv = Doctrine_Core::getTable('MembersInvoices')->findOneByIdAndStatusAndIsdeleteAndPrefix($v_inv_nr, '2', '0', 'TEMP_');
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

		public function submit_payment_old($post)
		{
			/* ------------------- Get Client Invoice Payments   START -------------- */
			$invoices = Doctrine_Query::create()
				->select("*, SUM(amount) as PaidAmount")
				->from('MembersInvoicePayments')
				->Where("invoice = ?", $post['invoiceId'] )
				->andWhere('isdelete = 0');
			$itemInvArray = $invoices->fetchArray();

			/* ------------------- Update Client Invoice   START -------------- */
			$lastPay = end($itemInvArray);

			$updateCI = Doctrine::getTable('MembersInvoices')->findOneById($post['invoiceId']);
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
			$invPayment = new MembersInvoicePayments();
			$invPayment->invoice = $post['invoiceId'];
			$invPayment->amount = Pms_CommonData::str2num($post['paymentAmount']);
			$invPayment->comment = $post['paymentComment'];
			$invPayment->paid_date = date("Y-m-d H:i:s", strtotime($post['paymentDate']));
			$invPayment->isdelete = "0";
			$invPayment->save();
		}

		public function submit_payment($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$updateCI = Doctrine::getTable('MembersInvoices')->find($post['invoiceId']);	
						
			if (! $updateCI instanceof MembersInvoices || $updateCI->client != $logininfo->clientid)
			{
				//somenthing is not ok
				//is this a cronjob?
				$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
				if($controller != 'cron') {
					//this is not your client?!
					$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
					$redirector->gotoSimpleAndExit('previlege','error');
				}
			}

			
			//get allready paid amount
			$itemInvArray = MembersInvoicePayments::getInvoicesPaymentsSum (array($post['invoiceId']));
			$allready_PaidAmount = ! empty($itemInvArray[$post['invoiceId']]['paid_sum']) ? $itemInvArray[$post['invoiceId']]['paid_sum'] : 0;
						
			/* ------------------- Update Client Invoice   START -------------- */
			$curentInvoiceArr = $updateCI->toArray();
		
			if($post['paymentAmount'] == "0.00" && $curentInvoiceArr['invoice_total'] != '0.00' && $post['mark_as_paid'] == "1")
			{
				//add full payment in case of mark as paid for non 0.00 invoice
				$post['paymentAmount'] = ($curentInvoiceArr['invoice_total'] - $allready_PaidAmount);
				
			}
		
			//bccomp returns 0 if the two operands are equal, 1 if the left_operand is larger than the right_operand, -1 otherwise.
			if(empty($itemInvArray[$post['invoiceId']]['paid_sum'])) {
				
				if(bccomp($curentInvoiceArr['invoice_total'], Pms_CommonData::str2num($post['paymentAmount'])) == 0) {
						$status = "3"; //completed
				}
				elseif(bccomp($curentInvoiceArr['invoice_total'], Pms_CommonData::str2num($post['paymentAmount'])) == 1) {
					$status = "5"; //partial
				}
			}
			else {
				
				$paid_value = Pms_CommonData::str2num($allready_PaidAmount + Pms_CommonData::str2num($post['paymentAmount']));
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
			$invPayment = new MembersInvoicePayments();
			
			//find if we have unpaid items to mark as paid
			$unpaid_payments = $invPayment->get_unpaid(array($post['invoiceId']));
			
			if ( ! empty($unpaid_payments[$post['invoiceId']])) {		
				$total_unpaid = array_sum(array_column($unpaid_payments[$post['invoiceId']], 'amount'));
			}
			
			if ($total_unpaid == $post['paymentAmount']) {
				//mark this payments as paid 
				
				$unpaid_ids_arr = array_column($unpaid_payments[$post['invoiceId']], 'id');
				
				$invPayment->set_status_paid($unpaid_ids_arr, null, $post['paymentDate']);
				
			} else {
				//insert as new payment
				
				$inv_payment = array(
						'invoice' => $post['invoiceId'],
						'amount' => Pms_CommonData::str2num($post['paymentAmount']),
						'status' => 'paid',
						'comment' => $post['paymentComment'],
						'paid_date' => date("Y-m-d H:i:s", strtotime($post['paymentDate'])),
						'isdelete' => "0"
				);
				$last_payment_id = $invPayment->set_new_record($inv_payment);
				
			}

			return $last_payment_id;
		}
		
		public function delete_multiple_invoices($iids)
		{
			$iids[] = "99999999999999999";

			if(count($iids) > 0)
			{
				$delInvoices = Doctrine_Query::create()
					->update("MembersInvoices")
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
					->update("MembersInvoiceItems")
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
					->update("MembersInvoices")
					->set('isarchived', "1")
					->whereIn('id', $iids)
					->andWhere('client = "' . $clientid . '"')
					->andWhere('isdelete ="0"');
				$archive = $archive_invoices->execute();
			}
		}

		
		public function sepafiles_markaspaid( $post, $clientid = 0)
		{
			
			$post['date'] = trim($post['date']) != "" ? $post['date'] : date("Y-m-d");
			
			$payment_comment = htmlentities($post['comment'] , ENT_QUOTES | ENT_HTML401 , "UTF-8");
			$payment_date = date('Y-m-d H:i:s', strtotime($post['date']));
			
			$msx = new MembersSepaXml;
			$sepa_xml = $msx->get_sepa_files_by_id($post['id'] , $clientid);
			
			
			if ( empty($sepa_xml)) {
				//we don't have this id or !=clientid  
				return false;
			}
			
			if ( $sepa_xml['batchid'] > 0 ) {
				//from 2017 we introduced batchid to group multiple invoices from same file
				$all_sepa_xml = $msx->get_sepa_files_by_batchid($sepa_xml['batchid'] , $clientid);
				
			} else {
				//we group by ftp_file
				$all_sepa_xml = $msx->get_sepa_files_by_ftp_file($sepa_xml['ftp_file'], $clientid);
			}
			$sepa_ids = array_column($all_sepa_xml, 'id');
			
			$invoice_ids_all = array();
			$payments_ids = array();
			foreach ($all_sepa_xml as $onefile){
				
				if ((int)$onefile['paymentid'] > 0) {
					//mark-as-paid single payment
					$payments_ids[] = $onefile['paymentid'];
				}
				else {
					//mark-as-paid the invoice, and append new payment to the system
					if ($onefile['invoiceid'] > 0) {
						$invoice_ids_all[] = $onefile['invoiceid'];
					}
					
					if ( ($invoiceid_extra_arr = unserialize($onefile['invoiceid_extra'])) !== false) {
											
						$invoice_ids_all = array_merge($invoice_ids_all, $invoiceid_extra_arr);				
					}
				}
			}
			
			//this sets status=3 to all our sepa
			$msx->set_status_and_comment(3, $payment_comment, $sepa_ids, $clientid);
			
			if (! empty($payments_ids)) { 
				//mark-as-paid single payment
				$this->update_payments("markaspaid", $payments_ids, $clientid , array("comment" => $payment_comment, "paid_date" => $payment_date));
			}
			
			if (! empty($invoice_ids_all)) {
				
				//mark-as-paid the invoice, and append new payment to the system
				
				$sepa_invoice_ids_all = array_values(array_unique($invoice_ids_all));
				
						
	
				$invoices_2_markaspaid = Doctrine_Query::create()
				->select('id')
				->from("MembersInvoices mi1")
				->whereIn('id', $sepa_invoice_ids_all)
				->andWhere(" client = ? " , $clientid )
				->andWhere(" (status = 2 OR status = 5) ")
				->andWhere(" storno = 0 ")
				->andWhere(" isdelete = 0 ")
				->andWhere(" isarchived = 0 ")
				->andWhere('id NOT IN (SELECT mi2.record_id from MembersInvoices AS mi2 WHERE mi2.client = ? AND mi2.storno = 1 AND mi2.isdelete = 0)', $clientid)
				->fetchArray();
				
				
				foreach ($invoices_2_markaspaid as $invoice) {
					$submit_payment = array(
							"invoiceId" => $invoice['id'],
							"paymentAmount" => "0.00",
							"paymentComment" => $payment_comment,
							"paymentDate" => $payment_date,
							"mark_as_paid" => "1",
							
					);
					$this->submit_payment( $submit_payment );
					
					
				}
			}
			
			return count($invoices_2_markaspaid);
		}
				
		//TODO-1024 bugfix
		public function sepafiles_delete( $post, $clientid = 0)
		{
		
			$res = false;
			
			//marked as paid xml cannot be deleted ... status=3
 			$msx = Doctrine::getTable('MembersSepaXml')->findOneByIdAndClientid($post['id'], $clientid);

			if ( ! ($msx instanceof MembersSepaXml) || empty($msx)) {
				//we don't have this id or !=clientid
				return false;
			}

			if ( $msx->batchid > 0 ) {
				//from 2017 we introduced batchid to group multiple invoices from same file
				$all_sepa_xml = MembersSepaXml::get_sepa_files_by_batchid($msx->batchid , $clientid);
					
			} else {
				//we group by ftp_file
				$all_sepa_xml = MembersSepaXml::get_sepa_files_by_ftp_file($msx->ftp_file, $clientid);
			}
		
			$sepa_ids = array_column($all_sepa_xml, 'id');

			if (count($sepa_ids) == 1 && $sepa_ids[0] == $post['id']) {
				//one single row to delete
				if($msx->status != 3) {
					$msx->isdelete = 1;
					$msx->save();
					$res = true;
				}
			} else {
				//multiple rows to delete
				$logininfo = new Zend_Session_Namespace('Login_Info');
				
				$msx_del = Doctrine_Query::create()
				->update("MembersSepaXml")
				->set('isdelete', '1')			
				->set('change_date', 'NOW()')
				->set('change_user', '?',  $logininfo->userid)
				->whereIn('id', $sepa_ids)
				->andwhere('clientid = ?', $clientid)
				->andwhere('status !=3')
				->execute();
				$res = true;
				
			}
	
			return $res;
				
		}
		
		
		public function create_payments($invoice_ids = array(), $clientid = 0) 
		{
			
			if ( empty($invoice_ids)) {
				return;
			}
			
			$payments_data = array();
			$members_invoices = new MembersInvoices();
			$invoices_arr = $members_invoices->get_client_invoices($clientid , array("id" => array("whereIn" => "id", "params" => $invoice_ids)));
			if ( ! empty($invoices_arr)) {
			
				$members_arr = array_column($invoices_arr, 'member');
			
				//fetch sepa-settings =  installmennts-settings = payment-setings
				$member_sepa_settings = MembersSepaSettings::get_member_settings( $members_arr , $clientid);
				foreach ($invoices_arr as $v_inv)
				{
					foreach ($member_sepa_settings[$v_inv['member']] as $member_settings)
					{
						if ($member_settings['member2membershipsid'] == $v_inv['membership_data'] || $member_settings['howoften'] == 'annually') {
							$when_day = $member_settings['when_day'];
							$when_month = $member_settings['when_month'];
							$when_year = date("Y");
			
							$issue_payment_date = false;
							switch($member_settings['howoften'])
							{
								case "monthly":{
			
									$issue_payment_date =  date('Y-m-d', strtotime($when_year . "-" . sprintf('%02d', $when_month) . "-" . sprintf('%02d', $when_day)));
									$amount = $member_settings['amount'];
			
								}
								break;
								case "quarterly":{
			
									$current_q = Pms_CommonData::get_dates_of_quarter ($when_month, $when_year, "d.m.Y" );
									$issue_payment_date =  date('Y-m-d', strtotime( "{$when_day} days", strtotime( $current_q['start']) ) );
									$amount = $member_settings['amount'];
								}
								break;
								case "annually":{
			
									$issue_payment_date =  date('Y-m-d', strtotime($when_year . "-" . sprintf('%02d', $when_month) . "-" . sprintf('%02d', $when_day)));
			
									$amount = $v_inv['invoice_total']; 
								}
								break;
			
							}
			
							if (strtotime($v_inv['selected_period'][$v_usr]['membership_start']) > strtotime($issue_payment_date)) {
								$issue_payment_date = date( 'Y-m-d', strtotime( date( 'Y' , strtotime("+1 Year", strtotime($v_inv['selected_period'][$v_usr]['membership_start']))) . "-" . date( 'm-d', strtotime($issue_payment_date) )   ));
							}
							//firts we should make the settings-year =  invoice-year ... so for quarter[0] we should have correct date if leap year, and month[february] would have 28 or 29 if user entered 28,29,30 or 31
							$payments_data['all'][] =
							$payments_data [$v_inv['id']] [] = array(
									'amount' => $amount,
									'payment_date' => $issue_payment_date,
									'status' => "installment",
									'Invoice' => $v_inv,
							);
						}
					}
						
					
					//add into payments also the member invoices that have no sepa-settings ?
					if ( ! isset($payments_data[$v_inv['id']])) {
						$payments_data['all'][] =
						$payments_data [$v_inv['id']] [] = array(
								'amount' => $v_inv['invoice_total'],
								'payment_date' => date( 'Y-m-d'),
								'status' => "created",
								'Invoice' => $v_inv,
						);
					}
				}
			}//end installments=payments			
			
			if (!empty($payments_data['all'])) 
			{

				$inv_payments_array = array();
				
				foreach($payments_data['all'] as $row) {
					$inv_payments_array[] = array(
							'invoice' => $row['Invoice']['id'],
							'amount' => Pms_CommonData::str2num($row['amount']),
							'status' => $row['status'],
							'comment' => '',
							'scheduled_due_date' => date("Y-m-d H:i:s", strtotime($row['payment_date'])),
							'paid_date' => null,
							'isdelete' => "0"
		
					);
						
				}
				
				//add to dbf
				$mip_obj = new MembersInvoicePayments();
				$mip_obj->set_new_collection($inv_payments_array);
			}
		}
	
		//missleading function name... it only changes status=paid or deletes
		public function update_payments($action = "markaspaid|delete", $payments_ids = array(), $clientid = 0 , $params = array()) 
		{

			$all_actions = array("markaspaid", "delete"); // this actions are in my view
			
			if ( empty($payments_ids)) {
				return;
			}
			
			if (!in_array($action, $all_actions)) {
				return; //incorrect action, define yours
			}


			$payment_comment = $params['comment'];
			$payment_paid_date = $params['paid_date'];
				
			
			$pay_ids = array();
			$all_ids = array();
			$inv_ids = array();
			
			$mip_obj = new MembersInvoicePayments();
			
			//verify ownership of the payment
			$payments_arr = $mip_obj->get_client_payments($clientid , array("id" => array("whereIn" => "id", "params" => $payments_ids)));
				
			foreach ($payments_arr['order_by_status'] as $row) 
			{
				if ($row['status'] != 'paid') {
					$pay_ids[] = $row['id'];		
				}
				$inv_ids[] = $row['invoice'];
				$all_ids[] = $row['id'];
			}
			
			if ( ! empty($pay_ids)) {
				
				switch($action) {
					case"markaspaid":{
						// PAID !
						$mip_obj->set_status_paid($pay_ids, $payment_comment, $payment_paid_date); //change the payment status to paid
					}break;
					case"delete":{
						// delete as single!
						foreach ($all_ids as $row) {
							$mip_obj->delete_row( $row , $payment_comment); //delete the payment
						}
					}break;
				}	
				
				//re-calulate invoice total to see if is paid in full
				$invoiceid_3 = array();//completed
				$invoiceid_5 = array();//partial
				$allready_paid = $mip_obj->getInvoicesPaymentsSum($inv_ids);
				foreach ($allready_paid as $invoiceid => $sum) 
				{
					$paid_value = $sum['paid_sum'];
					$total_value = $payments_arr['grop_by_invoice'][$invoiceid][0]['MembersInvoices']['invoice_total'];
					
					if(bccomp($paid_value, $total_value) == 0) {
						$status = "3"; //completed
						$invoiceid_3[] = $invoiceid; 
					}
					else {
						$status = "5"; //partial
						$invoiceid_5[] = $invoiceid;
					}
				}
				
				if ( ! empty($invoiceid_3)) {
					$this->ToggleStatusInvoices($invoiceid_3, "3");
				}
				
				if ( ! empty($invoiceid_5)) {
					$this->ToggleStatusInvoices($invoiceid_5, "5");
				}
				
				$InvoicesWithoutPayments = $mip_obj->find_InvoicesWithoutPayments($inv_ids);
				if ( ! empty($InvoicesWithoutPayments)) {
					$this->ToggleStatusInvoices($InvoicesWithoutPayments, "2");
				}
				
				
				
			}
			
		}
		
		
	/**
	 * fn is taken from the InvoicenewController and modified
	 * 
	 * @param unknown $params
	 * @param string $clientid
	 * @return multitype:
	 */
	public function generatemembersinvoice($params , $clientid = null)
	{
	    //initialize used models
	    $p_list = new PriceList();
	    $price_memberships_model = new PriceMemberships();
	    $members = new Member();
// 	    $members_invoices_form = new Application_Form_MembersInvoices();
// 	    $members_invoices_invoices = new MembersInvoices();
// 	    $members_invoices_invoices_items = new MembersInvoiceItems();
	    $clientid = is_null($clientid) && ! is_null($this->logininfo) ? $this->logininfo->clientid : $clientid;
	
	    if (empty($clientid)) {
	        return;//you have no $clientid
	    }
	    
	    $used_members = $params['members'];
	    $all_inserted_invoices = array(); //holds the return, id's of generated temp invoices
	
	    $all_membership_data =  array();	
	    $all_member_details  = array();
	    $master_data = array();
	    $master_data['client']['id'] = $clientid;
	    if($params['auto_generate'] == '1')
	    {
	        foreach($used_members as $k_usr => $v_usr)
	        {
	            
	            foreach($params['selected_period'][$v_usr] as $k=>$inv_data) {
	                $item_details = array();
	                $recipient = array();
	                // get client memberships data
// 	                $membership_data = Memberships::membership_details($clientid,$inv_data['membership']);
	                $membership_data = array();
	                if ( ! isset($all_membership_data[$clientid][$inv_data['membership']])) {
	                    
	                    $membership_data = Memberships::membership_details($clientid,$inv_data['membership']);
	                    $all_membership_data[$clientid][$inv_data['membership']] = $membership_data;//push in the mian array, memberships are considered cleintid+id unique
	                    
	                } else {
	                  
	                    $membership_data = $all_membership_data[$clientid][$inv_data['membership']];
	                }
	                
	                	
	                $curent_period[$v_usr]['start'] = $inv_data['int_start_s'];
	                $curent_period[$v_usr]['end'] = $inv_data['int_end_s'];
	                	
	                $membership_period[$v_usr]['start'] = $inv_data['membership_start'];
	                $membership_period[$v_usr]['end'] = $inv_data['membership_end'];
	
	                $curent_period_days[$v_usr] = $inv_data['days'];
	
	                $master_data[$k]['members'][$v_usr]['invoice_data']['client'] = $clientid;
	                $master_data[$k]['members'][$v_usr]['invoice_data']['membership_data'] = $inv_data['membership_data'];
	                $master_data[$k]['members'][$v_usr]['invoice_data']['invoiced_month'] =  date('Y-m-d H:i:s', strtotime($inv_data['int_start_s']));
	                	
	                $master_data[$k]['members'][$v_usr]['invoice_data']['member'] = $v_usr;
	                $master_data[$k]['members'][$v_usr]['invoice_data']['period'] = $curent_period[$v_usr];
	                //     					$master_data[$k]['members'][$v_usr]['invoice_data']['membership_period'] = $membership_period[$v_usr];
	                $master_data[$k]['members'][$v_usr]['invoice_data']['membership_period'] = $curent_period[$v_usr];
	
// 	                $members = new Member();
// 	                $member_details = $members->getMemberDetails($v_usr);
	                if ( ! isset($all_member_details[$clientid][$v_usr])) {
	                     
    	                $member_details = $members->getMemberDetails($v_usr);
	                    $all_member_details[$clientid][$v_usr] = $member_details;//push in the mian array, memberships are considered cleintid+id unique
	                     
	                } else {
	                     
	                    $member_details = $all_member_details[$clientid][$v_usr];
	                }
	                

	                
	                $recipient_title = trim(rtrim($member_details[$v_usr]['title']));

	                $recipient_company = "";
	                if($member_details[$v_usr]['type'] == "company" && ! empty($member_details[$v_usr]['member_company'])){
    	                $recipient_company = trim(rtrim($member_details[$v_usr]['member_company']));
	                }
	                
	                $recipient_name = "";
	                if( ! empty($member_details[$v_usr]['first_name']) ||  ! empty($member_details[$v_usr]['last_name'])){
    	                $recipient_name = trim(rtrim($member_details[$v_usr]['first_name'])) . ' ' . trim(rtrim($member_details[$v_usr]['last_name']));
	                }
	                $recipient_street = trim(rtrim($member_details[$v_usr]['street1']));
	                $recipient_zip = trim(rtrim($member_details[$v_usr]['zip']));
	                $recipient_city = trim(rtrim($member_details[$v_usr]['city']));
	
	                 
	                if($recipient_company)
	                {
	                    $recipient[$v_usr][] = $recipient_company;
	                }
	                
	                if($recipient_name)
	                {
	                    $recipient[$v_usr][] = $recipient_name;
	                }
	
	                if($recipient_street)
	                {
	                    $recipient[$v_usr][] = $recipient_street;
	                }
	
	
	                if($recipient_zip || $recipient_city)
	                {
	                    $recipient_blocks = array();
	
	                    if($recipient_zip)
	                    {
	                        $recipient_blocks[] = $recipient_zip;
	                    }
	
	                    if($recipient_city)
	                    {
	                        $recipient_blocks[] = $recipient_city;
	                    }
	
	                    $recipient[$v_usr][] = implode(" ", $recipient_blocks);
	                }
// 	print_R($recipient);
	                $master_data[$k]['recipient'][$v_usr] = implode("<br />", $recipient[$v_usr]);
	
	                if(!array_key_exists($v_usr, $master_price_list))
	                {
	                    $master_price_list[$v_usr] = $p_list->get_client_list_period(date('Y-m-d', strtotime($curent_period[$v_usr]['start'])), date('Y-m-d', strtotime($curent_period[$v_usr]['end'])));
	                }
	                $current_pricelist = $master_price_list[$v_usr][0];
	                	
	                if($current_pricelist)
	                {
// 	                    $price_memberships_model = new PriceMemberships();
	                    $price_memberships = $price_memberships_model->get_prices($current_pricelist['id'], $clientid);
	                }
	                
// 	                $item_details[$v_usr]['description'] = $price_memberships[$membership_data['id']]['membership'];
// 	                $item_details[$v_usr]['shortcut'] = $price_memberships[$membership_data['id']]['shortcut'];
	                $item_details[$v_usr]['description'] = $membership_data['membership'];
	                $item_details[$v_usr]['shortcut'] = $membership_data['shortcut'];
	                
	                
	                $item_details[$v_usr]['qty'] = "1";
	                	
	                if($inv_data['membership_price'] != "0.00"){
	                    $item_details[$v_usr]['price'] = $inv_data['membership_price']; // get custom price
	                } else{
	                    $item_details[$v_usr]['price'] = $price_memberships[$membership_data['id']]['price'];
	                }
	                	
	                $item_details[$v_usr]['custom'] = "0";
	
	                $master_data[$k]['invoices'][$v_usr]['items'][] = $item_details[$v_usr];
	                $master_data[$k]['invoices'][$v_usr]['pricelist'] = $current_pricelist;
	            }
// 	            $inserted_invoices = $members_invoices_form->auto_insert_invoice($master_data);
	            
	        }
            
	        
	       $inserted_invoices = $this->auto_insert_invoice($master_data);
	
	    }
	    return $inserted_invoices;
	}
	
	
}
?>