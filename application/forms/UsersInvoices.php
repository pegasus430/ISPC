<?php

require_once("Pms/Form.php");

class Application_Form_UsersInvoices extends Pms_Form {

	public function validate($post) {

	}

	public function edit_invoice ( $invoice, $post, $status )
	{

		if ($invoice)
		{
			//update initial invoice
			$update_invoice = Doctrine::getTable('UserInvoices')->findOneById($invoice);
			$update_invoice->prefix = $post['invoice']['prefix'];
			$update_invoice->invoice_subnumber = $post['invoice']['invoice_subnumber'];
			$update_invoice->recipient = $post['invoice']['recipient'];
			$update_invoice->user_address = $post['invoice']['user_address'];
			$update_invoice->user_bank_details = $post['invoice']['user_bank_details'];
			$update_invoice->ikuser = $post['invoice']['ikuser'];
			$update_invoice->invoice_total = $post['invoice']['invoice_total'];
			$update_invoice->save();

			if ($status != '0')
			{
				$update_invoice_status = Doctrine_Query::create()
				->update("UserInvoices")
				->set('status', $status)
				->where('id ="' . $invoice . '"')
				->andWhere('status != "3"');
				$update_invoice_status->execute();
			}

			if ($update_invoice)
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
		->update("UserInvoices")
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

	public function ToggleStatusInvoices ( $iids, $status )
	{
		//		setStatus of multiple client invoices **
		$iids[] = "99999999999999999";

		if (count($iids) > 0)
		{
			/* -------------------Status Client Invoice  START-------------- */
			$statusInvoices = Doctrine_Query::create()
			->update("UserInvoices")
			->set('status', "'" . $status . "'");
			if ($status == "3") //paid
			{
				$statusInvoices->set('paid_date', "NOW()");
			}
			//reset paid date if invoice is running out of payments (user deletes them)
			if ($status == "1" || $status == "2") //draft or unpaid
			{
				$statusInvoices->set('paid_date', "'0000-00-00 00:00:00'");
			}

			if ($status == "4") //delete
			{
				$statusInvoices->set('isdelete',"1");
			}

			$statusInvoices->whereIn('id', $iids)
			->andWhere('isdelete =0');

			$d = $statusInvoices->execute();
		}
	}

	public function submit_payment($post){
		/* -------------------Get Client Invoice Payments   START-------------- */
		$invoices = Doctrine_Query::create()
		->select("*, SUM(amount) as PaidAmount")
		->from('UserInvoicePayments')
		->Where("invoice='" . $post['invoiceId'] . "'")
		->andWhere('isdelete = 0');
		$itemInvArray = $invoices->fetchArray();

		/* -------------------Update Client Invoice   START-------------- */
		$lastPay = end($itemInvArray);
		$updateCI = Doctrine::getTable('UserInvoices')->findOneById($post['invoiceId']);
		$curentInvoiceArr = $updateCI->toArray();
		if (empty($itemInvArray[0]['id']))
		{
			if (bccomp($curentInvoiceArr['invoice_total'], Pms_CommonData::str2num($post['paymentAmount'])) == 0)
			{
				$status = "3"; //completed
			}
			else if (bccomp($curentInvoiceArr['invoice_total'], Pms_CommonData::str2num($post['paymentAmount'])) == 1)
			{
				$status = "5"; //partial
			}
		}
		else
		{

			if (bccomp(($lastPay['PaidAmount'] + Pms_CommonData::str2num($post['paymentAmount'])), $curentInvoiceArr['invoice_total']) == 0)
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

		/* -------------------Add Invoice Payment START-------------- */
		$invPayment = new UserInvoicePayments();
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
	    
	    $ins_inv = new UserInvoices();
	    $ins_inv->invoice_start = $invoice_start;
	    $ins_inv->invoice_end = $invoice_end;

	    $ins_inv->ipid = $post['ipid'];
	    
	    $ins_inv->client = $post['clientid'];
	    $ins_inv->userid = $post['user'];
	    
	    $ins_inv->prefix = $post['prefix'];
	    $ins_inv->invoice_number = $post['invoice_number'];
	    $ins_inv->invoice_total = $post['invoice_total'];
	    $ins_inv->user_address = $post['address'];
	    
	    $ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020
	    $ins_inv->custom_invoice = 'custom_invoice';      //ISPC-2747 pct.b Lore 27.11.2020
	    
	    $ins_inv->status = '1'; // DRAFT - ENTWURF
	    
	    //ISPC-2747 pct.b Lore 27.11.2020
	    $ins_inv->start_active = $start_active; //first product day in period
	    $ins_inv->end_active = $end_active; //last product day in period
	    $ins_inv->start_sapv = $start_sapv; //allready formated as db format
	    $ins_inv->end_sapv = $end_sapv; //allready formated as db format
	    $ins_inv->sapv_approve_date = $sapv_approve_date;
	    $ins_inv->sapv_approve_nr = $post['sapv_approve_nr'];
	    $ins_inv->first_name = $post['first_name'];
	    $ins_inv->last_name = $post['last_name'];
	    $ins_inv->birthdate = $birthdate;
	    $ins_inv->beneficiary_address = $post['street1'];
	    $ins_inv->patient_care = $post['patient_pflegestufe'];
	    $ins_inv->insurance_no = $post['insurance_no'];
	    $ins_inv->street = $post['street'];
	    
	    $ins_inv->debtor_number = $post['debtor_number'];
	    $ins_inv->ppun = $post['ppun'];
	    $ins_inv->paycenter = $post['paycenter'];
	    $ins_inv->ikuser = $post['client_ik'];
	    $ins_inv->footer = $post['footer'];
	    
	    $ins_inv->completed_date = date('Y-m-d H:i:s');
	    
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
	            $collection = new Doctrine_Collection('UserInvoiceItems');
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
	        
	        $ins_inv = Doctrine::getTable('UserInvoices')->findOneById($invoice_id);
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
	        $ins_inv->ikuser = $post['client_ik'];
	        $ins_inv->prefix = $post['prefix'];
	        $ins_inv->invoice_number = $post['invoice_number'];
	        $ins_inv->invoice_total = $post['invoice_total'];
	        $ins_inv->user_address = $post['address'];
	        $ins_inv->footer = $post['footer'];
	        $ins_inv->street = $post['street'];
	        
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
	                $collection = new Doctrine_Collection('UserInvoiceItems');
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
	        ->update('UserInvoiceItems')
	        ->set('isdelete', "1")
	        ->where('invoice = "' . $invoice . '"')
	        ->andWhere('isdelete = "0"');
	        $q_res = $q->execute();
	    }
	}
	
	
}
?>