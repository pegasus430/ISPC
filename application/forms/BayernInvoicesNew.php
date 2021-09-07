<?php

	require_once("Pms/Form.php");

	class Application_Form_BayernInvoicesNew extends Pms_Form {

		public function validate($post)
		{

		}

		public function edit_invoice($invoice, $clientid, $post, $status)
		{

			if($invoice)
			{
				//update initial invoice
				$updateinv = Doctrine::getTable('BayernInvoicesNew')->findOneById($invoice);
				$updateinv->address = $post['invoice']['address'];
				$updateinv->footer = $post['footer'];
				$updateinv->save();

				if($status == '4')
				{
					$update_invoice = Doctrine_Query::create()->update("BayernInvoicesNew");
					$update_invoice->set('isdelete', '1');
					//dont delete paid and partialy paid
					$update_invoice->where('id = "' . $invoice . '"');
					$update_invoice->andWhere('status != "3"');
					$update_invoice->andWhere('status != "5"');
					$update_invoice->execute();
				}

				if($status != '1' && $status != '4' && $status != '0' && strlen($post['pdf']) == 0)
				{
					$update_invoice = Doctrine_Query::create()->update("BayernInvoicesNew");
					$update_invoice->set('invoice_number', "'" . $post['invoice_number'] . "'");
					$update_invoice->set('prefix', "'" . $post['prefix'] . "'");
					$update_invoice->set('completed_date', "'" . date('Y-m-d H:i:s', strtotime($post['completed_date'])) . "'");
					$update_invoice->where('id = "' . $invoice . '"');
					$update_invoice->execute();
				}

				if($status != '0') //dont change status when is paid and edited
				{
					$update_invoice_status = Doctrine_Query::create()
						->update("BayernInvoicesNew")
						->set('status', $status)
						->where('id ="' . $invoice . '"')
						->andWhere('status != "3"')
						->andWhere('status != "5"');
					$update_invoice_status->execute();
				}

				//add custom items
				if(count($post['price']) > '0')
				{
					foreach($post['price'] as $k_item => $v_price)
					{
						if(strlen($v_price) > '0' && strlen($post['total'][$k_item]) > '0' && strlen($post['qty'][$k_item]) > '0')
						{
							$items[] = array(
								'invoice' => $invoice,
								'client' => $clientid,
								'name' => $post['name'][$k_item],
								'shortcut' => $post['shortcut'][$k_item],
								'qty' => $post['qty'][$k_item],
								'price' => Pms_CommonData::str2num($v_price),
								'total' => Pms_CommonData::str2num($post['total'][$k_item]),
								'custom' => $post['custom'][$k_item],
								'isdelete' => '0',
							);
							$invoice_total += Pms_CommonData::str2num($post['total'][$k_item]);
						}
					}

					if(count($items) > '0')
					{
						$this->delete_items($invoice);

						$insert_items = new Doctrine_Collection('BayernInvoiceItemsNew');
						$insert_items->fromArray($items);
						$insert_items->save();

						$update_invoice_status = Doctrine_Query::create()
							->update("BayernInvoicesNew")
							->set('invoice_total', '"' . Pms_CommonData::str2num($invoice_total) . '"')
							->where('id ="' . $invoice . '"')
							->andWhere('isdelete = "0"');
						$uis = $update_invoice_status->execute();
					}
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

		private function delete_items($invoice)
		{
			if($invoice)
			{
				$q = Doctrine_Query::create()
					->update('BayernInvoiceItemsNew')
					->set('isdelete', "1")
					->where('invoice = "' . $invoice . '"')
					->andWhere('isdelete = "0"');
				$q_res = $q->execute();
			}
		}

		public function delete_invoice($invoice)
		{
			$update_invoice = Doctrine_Query::create()
				->update("BayernInvoicesNew")
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

		public function ToggleStatusInvoices($iids, $status, $clientid = false)
		{
			//		setStatus of multiple client invoices **
			$iids[] = "99999999999999999";

			if(count($iids) > 0)
			{
				/* ------------------ Status Client Invoice START ------------- */
				$statusInvoices = Doctrine_Query::create()
					->update("BayernInvoicesNew")
					->set('status', "'" . $status . "'");
				if($status == "3") //paid
				{
					$statusInvoices->set('paid_date', "NOW()");
				}
				if($status == "2") //completed
				{
					$statusInvoices->set('completed_date', "NOW()");
				}
				//reset paid date if invoice is running out of payments (user deletes them)
				if($status == "1" || $status == "2") //draft or unpaid
				{
					$statusInvoices->set('paid_date', "'0000-00-00 00:00:00'");
				}
				if($status == "4") //delete
				{
					$statusInvoices->set('isdelete', "1");
				}
				$statusInvoices->whereIn('id', $iids)->andWhere('isdelete =0');
				$d = $statusInvoices->execute();
				/* -----------------  Status Client Invoice END --------------- */


				//generate new rechnung number for completed invoices
				if($status == '2' && $clientid)
				{
					$bayern_invoices = new BayernInvoicesNew();
					$high_invoice_nr = $bayern_invoices->get_next_invoice_number($clientid);
					$prefix = $high_invoice_nr['prefix'];
					$invoicenumber = $high_invoice_nr['invoicenumber'];

					$incr = '0';
					foreach($iids as $k_id => $v_id)
					{
						$invoices_numbers[$v_id] = ($invoicenumber + $incr);
						$incr++;
					}

					foreach($invoices_numbers as $k_inv_nr => $v_inv_nr)
					{
						$invoice_nr_update = Doctrine_Query::create()
							->update("BayernInvoicesNew")
							->set('prefix', "'" . $prefix . "'")
							->set('invoice_number', "'" . $v_inv_nr . "'")
							->where('status ="2"')
							->andWhere('id = "' . $k_inv_nr . '"')
							->andWhere('prefix = "TEMP_"')
							->andWhere('isdelete = "0"');
						$invoice_nr_exec = $invoice_nr_update->execute();
					}
				}
			}
		}

		public function submit_payment($post)
		{
			/* -------------------- Get Client Invoice Payments------------------------ */
			$invoices = Doctrine_Query::create()
				->select("*, SUM(amount) as PaidAmount")
				->from('BayernInvoicePaymentsNew')
				->Where("invoice='" . $post['invoiceId'] . "'")
				->andWhere('isdelete = 0');
			$itemInvArray = $invoices->fetchArray();

			/* --------------------- Update Client Invoice----------------------------- */
			$lastPay = end($itemInvArray);

			$updateCI = Doctrine::getTable('BayernInvoicesNew')->findOneById($post['invoiceId']);
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

//			bccomp returns 0 if the two operands are equal, 1 if the left_operand is larger than the right_operand, -1 otherwise.
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

			/* ---------------------------- Add Invoice Payment START ------------------ */
			$invPayment = new BayernInvoicePaymentsNew();
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
					->update("BayernInvoicesNew")
					->set('isdelete', "1")
					->set('status', "4")
					->whereIn('id', $iids)
					->andWhere('isdelete =0')
					->andWhere('status != "3"')
					->andWhere('status != "5"');

				$d = $delInvoices->execute();
			}
		}

		public function insert_invoice($master_data)
		{
			$bay_invoices = new BayernInvoicesNew();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			foreach($master_data['invoices'] as $k_ipid => $v_invoice_data)
			{
				$bay_invoices_number = $bay_invoices->get_next_invoice_number($master_data['client']['id'], true);
				$prefix = $bay_invoices_number['prefix'];
				$invoicenumber = $bay_invoices_number['invoicenumber'];

				$inserted_id = '';
				$invoice_total = '';
				$invoice_items = array();

				$format = 'Y-m-d H:i:s';

				$invoice_start = $master_data['patients'][$k_ipid]['invoice_data']['period']['start'];
				$invoice_end = $master_data['patients'][$k_ipid]['invoice_data']['period']['end'];

				if(strlen($master_data['patients'][$k_ipid]['invoice_data']['first_active_day']) > '0')
				{
					$start_active = date($format, strtotime($master_data['patients'][$k_ipid]['invoice_data']['first_active_day']));
				}
				else
				{
					$start_active = '';
				}

				if(strlen($master_data['patients'][$k_ipid]['invoice_data']['last_active_day']))
				{
					$end_active = date($format, strtotime($master_data['patients'][$k_ipid]['invoice_data']['last_active_day']));
				}
				else
				{
					$end_active = '';
				}

				$sapv_id = $master_data['patients'][$k_ipid]['invoice_data']['sapv']['id'];
				$admission_id = $master_data['patients'][$k_ipid]['invoice_data']['admissionid'];

				if($master_data['patients'][$k_ipid]['invoice_data']['sapv']['verordnungam'] != '0000-00-00 00:00:00')
				{
					$start_sapv = date($format, strtotime($master_data['patients'][$k_ipid]['invoice_data']['sapv']['verordnungam']));
				}
				else
				{
					$start_sapv = '';
				}

				if($master_data['patients'][$k_ipid]['invoice_data']['sapv']['verordnungbis'] != '0000-00-00 00:00:00')
				{
					$end_sapv = date($format, strtotime($master_data['patients'][$k_ipid]['invoice_data']['sapv']['verordnungbis']));
				}
				else
				{
					$end_sapv = '';
				}

				if($master_data['patients'][$k_ipid]['invoice_data']['sapv']['status'] == '1' &&
					$master_data['patients'][$k_ipid]['invoice_data']['sapv']['verorddisabledate'] != '0000-00-00 00:00:00' &&
					$master_data['patients'][$k_ipid]['invoice_data']['sapv']['verorddisabledate'] != '1970-01-01 00:00:00'
				)
				{
					$end_sapv = date($format, strtotime($master_data['patients'][$k_ipid]['invoice_data']['sapv']['verorddisabledate']));
				}

				if($master_data['patients'][$k_ipid]['invoice_data']['sapv']['approved_date'] != '0000-00-00 00:00:00')
				{
					$sapv_approve_date = date($format, strtotime($master_data['patients'][$k_ipid]['invoice_data']['sapv']['approved_date']));
				}
				else
				{
					$sapv_approve_date = '';
				}

				$sapv_approve_nr = $master_data['patients'][$k_ipid]['invoice_data']['sapv']['approved_number'];

				$ins_inv = new BayernInvoicesNew();
				$ins_inv->invoice_start = date($format, strtotime($invoice_start));
				$ins_inv->invoice_end = date($format, strtotime($invoice_end));
				$ins_inv->invoiced_month = $master_data['invoiced_month'];
				$ins_inv->start_active = $start_active; //first product day in period
				$ins_inv->end_active = $end_active; //last product day in period
				$ins_inv->start_sapv = $start_sapv; //allready formated as db format
				$ins_inv->end_sapv = $end_sapv; //allready formated as db format
				$ins_inv->sapv_approve_date = $sapv_approve_date;
				$ins_inv->sapv_approve_nr = $sapv_approve_nr;
				$ins_inv->ipid = $k_ipid;
				$ins_inv->client = $master_data['client']['id'];
				$ins_inv->prefix = $prefix;
				$ins_inv->invoice_number = $invoicenumber;
				$ins_inv->invoice_total = '';
				$ins_inv->status = '1'; // DRAFT - ENTWURF
				$ins_inv->address = $master_data['patients'][$k_ipid]['invoice_data']['health_insurace_address'];
				$ins_inv->footer = $master_data['patients'][$k_ipid]['invoice_data']['footer'];
				
				$ins_inv->show_boxes = 'show_box_active,show_box_patient,show_box_sapv';               //ISPC-2747 pct.b Lore 27.11.2020
				
				$ins_inv->first_name = $master_data['patients'][$k_ipid]['details']['first_name'];
				$ins_inv->last_name = $master_data['patients'][$k_ipid]['details']['last_name'];
				$ins_inv->birthdate = $master_data['patients'][$k_ipid]['details']['birthd'];
				$ins_inv->patient_care = $master_data['patients'][$k_ipid]['invoice_data']['patient_pflegestufe'];
				$ins_inv->insurance_no = $master_data['patients'][$k_ipid]['invoice_data']['insurance_no'];

				$ins_inv->sapvid = $sapv_id;
				$ins_inv->admissionid = $admission_id;

				$ins_inv->debtor_number = $master_data['patients'][$k_ipid]['invoice_data']['debtor_number'];
				$ins_inv->ppun = $master_data['patients'][$k_ipid]['invoice_data']['ppun'];
				$ins_inv->paycenter = $master_data['patients'][$k_ipid]['invoice_data']['paycenter'];

				$ins_inv->isdelete = '0';
				$ins_inv->record_id = '0';
				$ins_inv->storno = '0';
				$ins_inv->save();
				$ins_id = $ins_inv->id;

				if($ins_id)
				{
					$invoice_items_arr = array();
					foreach($master_data['items_invoices'][$k_ipid] as $k_inv => $v_inv)
					{
						$invoice_items_arr[] = array(
							'invoice' => $ins_id,
							'client' => $clientid,
							'name' => $v_inv['name'],
							'shortcut' => $v_inv['shortcut'],
							'qty' => $v_inv['qty'],
							'price' => $v_inv['price'],
							'total' => $v_inv['shortcut_total'],
							'isdelete' => '0'
						);

						$invoice_total += (Pms_CommonData::str2num($v_inv['price']) * $v_inv['qty']);
					}

					if(count($invoice_items_arr) > 0)
					{
						//insert many records with one query!!
						$collection = new Doctrine_Collection('BayernInvoiceItemsNew');
						$collection->fromArray($invoice_items_arr);
						$collection->save();
					}

					$update_invoice = Doctrine_Query::create()
						->update("BayernInvoicesNew")
						->set('invoice_total', "'" . Pms_CommonData::str2num($invoice_total) . "'")
						->where('id ="' . $ins_id . '"')
						->execute();

					$inserted_ids[] = $ins_id;
				}
			}

			return $inserted_ids;
		}

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

		public function insert_custom_invoice($post)
		{
			$inserted_id = '';
			$invoice_total = '';
			$invoice_items = array();
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
			
			$ins_inv = new BayernInvoicesNew();
			$ins_inv->invoice_start = $invoice_start;
			$ins_inv->invoice_end = $invoice_end;
			$ins_inv->start_active = $start_active; //first product day in period
			$ins_inv->end_active = $end_active; //last product day in period
			$ins_inv->start_sapv = $start_sapv; //allready formated as db format
			$ins_inv->end_sapv = $end_sapv; //allready formated as db format
			$ins_inv->sapv_approve_date = $sapv_approve_date;
			$ins_inv->sapv_approve_nr = $post['sapv_approve_nr'];
			$ins_inv->ipid = $post['ipid'];
			$ins_inv->first_name = $post['first_name'];
			$ins_inv->last_name = $post['last_name'];
			$ins_inv->birthdate = $birthdate;
			$ins_inv->beneficiary_address = $post['street1'];
			$ins_inv->patient_care = $post['patient_pflegestufe'];
			$ins_inv->insurance_no = $post['insurance_no'];

			$ins_inv->client = $post['clientid'];
			$ins_inv->client_ik = $post['client_ik'];
			$ins_inv->prefix = $post['prefix'];
			$ins_inv->invoice_number = $post['invoice_number'];
			$ins_inv->invoice_total = $post['invoice_total'];
			$ins_inv->status = '1'; // DRAFT - ENTWURF
			$ins_inv->address = $post['address'];
			$ins_inv->footer = $post['footer'];

			$ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020
			
			$ins_inv->debtor_number = $post['debtor_number'];
			$ins_inv->ppun = $post['ppun'];
			$ins_inv->paycenter = $post['paycenter'];
			$ins_inv->tabname = 'paidback';
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
						'qty' => $post['qty'][$k_inv],
						'price' => $post['price'][$k_inv],
						'total' => $post['total'][$k_inv],
						'isdelete' => '0'
					);
				}

				if(count($invoice_items_arr) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('BayernInvoiceItemsNew');
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
				
				$ins_inv = Doctrine::getTable('BayernInvoicesNew')->findOneByIdAndTabname($invoice_id, "paidback");
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
				$ins_inv->first_name = $post['first_name'];
				$ins_inv->last_name = $post['last_name'];
				$ins_inv->birthdate = $birthdate;
				$ins_inv->beneficiary_address = $post['street1'];
				$ins_inv->patient_care = $post['patient_pflegestufe'];
				$ins_inv->insurance_no = $post['insurance_no'];
				$ins_inv->client = $post['clientid'];
				$ins_inv->client_ik = $post['client_ik'];
				$ins_inv->prefix = $post['prefix'];
				$ins_inv->invoice_number = $post['invoice_number'];
				$ins_inv->invoice_total = $post['invoice_total'];
				$ins_inv->address = $post['address'];
				$ins_inv->footer = $post['footer'];
				
				$ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020
				
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
							'qty' => $post['qty'][$k_inv],
							'price' => $post['price'][$k_inv],
							'total' => $post['total'][$k_inv],
							'isdelete' => '0'
						);
					}

					if(count($invoice_items_arr) > 0)
					{
						self::delete_items($ins_id);

						//insert many records with one query!!
						$collection = new Doctrine_Collection('BayernInvoiceItemsNew');
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

		public function archive_multiple_invoices($iids, $clientid)
		{
			$iids[] = "99999999999999999";
			$iids = array_values(array_unique($iids));

			if(count($iids) > 0)
			{
				$archive_invoices = Doctrine_Query::create()
					->update("BayernInvoicesNew")
					->set('isarchived', "1")
					->whereIn('id', $iids)
					->andWhere('client = "' . $clientid . '"')
					->andWhere('isdelete ="0"');
				$archive = $archive_invoices->execute();
			}
		}

	}

?>