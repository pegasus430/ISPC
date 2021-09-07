<?php

require_once("Pms/Form.php");

class Application_Form_Invoices extends Pms_Form {

	public function validate($post) {

	}

	public function InsertData($post) {
		/* ------------------- Settingup Variables Used In This Form START-------------- */
		$logininfo	=	new Zend_Session_Namespace('Login_Info');
		$clientid		=	$logininfo->clientid;
		$userid		=	$logininfo->userid;

		/* ------------------- Create Invoice START-------------- */
		if($post['status'] == '0')
		{
			$client_invoice = new ClientInvoices();
			$client_invoice_number = $client_invoice->get_next_invoice_number($clientid);

			$post['prefix'] = $client_invoice_number['prefix'];
			$post['invoicenumber'] = $client_invoice_number['invoicenumber'];
		}

		$inv = new ClientInvoices();
		$inv->userid = $userid;
		$inv->clientid = $clientid;
		$inv->ipid = $post['patientipid'];
		$inv->epid = $post['patientepid'];
		$inv->prefix = $post['prefix'];
		$inv->rnummer = $post['invoicenumber'];
		$inv->status = $post['status'];
		$inv->invoiceTotal = Pms_CommonData::str2num($post['invoiceamount']);
		$inv->isDelete = 0;
		$inv->completedDate = date("Y-m-d H:i:s", strtotime($post['letterdate']));
		if(!empty($post['date21'])){
			$inv->dueDate = date("Y-m-d H:i:s", strtotime($post['date21']));
		} else{
			$inv->dueDate = date("Y-m-d H:i:s", strtotime("+21 days"));
		}
		
		$inv->save();

		$invoiceId = $inv->id;


		/* ------------------- Create Invoice Items START-------------- */
		$item=1;
		foreach($post as $fieldName=>$fieldValue){
			if($fieldName != 'patientipid'){
				$invItems = new InvoiceItems();
				$invItems->clientid = $clientid;
				$invItems->invoiceId = $invoiceId;
				$invItems->itemLabel = $fieldName;
				if(is_int($fieldValue)){
					$invItems->itemValue = $fieldValue;
				} else {
					$invItems->itemString = $fieldValue;
				}
				$invItems->sortOrder = $item;
				$invItems->sortOrder = $item;
				$invItems->isDelete = 0;
				$invItems->save();
				$item++;
			}
		}
		
		return $invoiceId;
	}

	public function UpdateData($post)
	{
		/* ------------------- Settingup Variables Used In This Form START-------------- */
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		if($post['invoiceid'] > 0)
		{
			/* ------------------- Delete Invoice Items   START-------------- */
			$q = Doctrine_Query::create()
			->delete('InvoiceItems ii')
			->where('ii.invoiceId = "' . $post['invoiceid'] . '"')
			->andWhere('ii.clientid = "' . $clientid . '"');
			$qexec = $q->execute();

			/* ------------------- Update Client Invoice   START-------------- */
			$updateCI = Doctrine::getTable('ClientInvoices')->findOneById($post['invoiceid']);
			if($post['status'] == '0' && $post['prefix'] == 'TEMP_')
			{
				$invoices = new ClientInvoices();
				$invoice_number_arr = $invoices->get_next_invoice_number($clientid);

				$post['prefix'] = $invoice_number_arr['prefix'];
				$post['invoicenumber'] = $invoice_number_arr['invoicenumber'];
			}

			$updateCI->prefix = $post['prefix'];
			$updateCI->rnummer = $post['invoicenumber'];
			$updateCI->ipid = $post['patientipid'];
			$updateCI->status = $post['status'];
			$updateCI->invoiceTotal = Pms_CommonData::str2num($post['invoiceamount']);
			$updateCI->isDelete = 0;
			$updateCI->completedDate = date("Y-m-d H:i:s", strtotime($post['letterdate']));
			
			if(!empty($post['date21'])){
				$updateCI->dueDate = date("Y-m-d H:i:s", strtotime($post['date21']));
			} else{
				$updateCI->dueDate = date("Y-m-d H:i:s", strtotime("+21 days"));
			}
			$updateCI->save();

			/* ------------------- Add New Invoice Items   START-------------- */
			$item = 1;
			foreach($post as $fieldName=> $fieldValue)
			{
				if($fieldName != 'patientipid' && $fieldName != 'invoiceid')
				{
					$invItems = new InvoiceItems();
					$invItems->clientid = $clientid;
					$invItems->invoiceId = $post['invoiceid'];
					$invItems->itemLabel = $fieldName;
					if(is_int($fieldValue))
					{
						$invItems->itemValue = $fieldValue;
					}
					else
					{
						$invItems->itemString = $fieldValue;
					}
					$invItems->sortOrder = $item;
					$invItems->isDelete = 0;
					$invItems->save();
					$item++;
				}
			}
		}
	}

	public function UpdateIpidData($invoiceid, $p_ipid){
		if(strlen($invoiceid)>0 && strlen($p_ipid)>0){
			$updateipid = Doctrine_Query::create()
			->update('ClientInvoices')
			->set('ipid', '"'.$p_ipid.'"')
			->where('id = "'.$invoiceid.'"');

			$u = $updateipid->execute();

			return $u;
		}
	}
	public function DeleteInvoice($iid){


		if($iid >0 ){
			/* ------------------- Delete Client Invoice  START-------------- */
			$delInvoice = Doctrine_Query::create()
			->update("ClientInvoices")
			->set('status', "3")
			->where('id = "'.$iid.'"' );
			$d = $delInvoice->execute();


			/* ------------------- Delete Client Invoice Items  START-------------- */
			$delInvoiceItems = Doctrine_Query::create()
			->update("InvoiceItems")
			->set('isDelete', "1")
			->where('invoiceId = "'.$iid.'"');
			$di = $delInvoiceItems->execute();
		}
		return $d;
	}

	public function DeleteInvoices($iids) {
		$iids[] = "99999999999999999";


		if (count($iids) > 0) {
			/* ------------------- Delete Client Invoice  START-------------- */
			$delInvoices = Doctrine_Query::create()
			->update("ClientInvoices")
			->set('status', "3")
			->whereIn('id', $iids)
			->andWhere('isDelete =0');
			$d = $delInvoices->execute();

			/* ------------------- Delete Client Invoice Items  START-------------- */
			$delInvoicesItems = Doctrine_Query::create()
			->update("InvoiceItems")
			->set('isDelete', "1")
			->whereIn('invoiceId', $iids)
			->andWhere('isDelete =0');
			$di = $delInvoicesItems->execute();
		}
	}
	public function ToggleStatusInvoices($iids, $status, $paid = true, $clientid = false) {
		//		setStatus of multiple client invoices **
		$iids[] = "99999999999999999";

		if (count($iids) > 0) {
			/* ------------------- Status Client Invoice  START-------------- */
			$statusInvoices = Doctrine_Query::create()
			->update("ClientInvoices")
			->set('status', "'" . $status . "'");
			if ($status == "2" && $paid === true) {
				$statusInvoices->set('paidDate', "NOW()");
			}
			//reset paid date if invoice is running out of payments (user deletes them)
			if($status == "0") {
				$statusInvoices->set('paidDate', "'0000-00-00 00:00:00'");
				$statusInvoices->set('completedDate', "NOW()");
			}

			$statusInvoices->whereIn('id', $iids)
			->andWhere('isDelete =0');

			$d = $statusInvoices->execute();

			/* ------------------- Insert Paid Client Invoice  START-------------- */
			if($status == "2" && $paid === true)  {
				/* ------------------- Get Invoice Amount Client Invoice  START-------------- */
				$paInvoices = Doctrine_Query::create()
				->select("id, invoiceTotal")
				->from("ClientInvoices")
				->whereIn('id', $iids)
				->andWhere('isDelete =0');
				$invPa = $paInvoices->fetchArray();

				foreach($invPa as $invoiceKey=>$invoiceValue){
					$finalInvoicePa[$invoiceValue['id']] = $invoiceValue['invoiceTotal'];
				}
				//					get invoices allready paid amount
				$invoices = Doctrine_Query::create()
				->select("*, SUM(amount) as PaidAmount")
				->from('InvoicePayments')
				->WhereIn("invoiceId",  $iids)
				->andWhere('isDelete = 0');

				$itemInvPaidArray = $invoices->fetchArray();
				/* ------------------- Get Invoice Amount Client Invoice  START-------------- */
				//mapping
				foreach($itemInvPaidArray as $kInv=>$vInv){
					$finalPaidInvoices[$vInv['invoiceId']] = $vInv['PaidAmount'];
				}
				//				final array mapping
				foreach($finalInvoicePa as $kPaInvoice=>$vPaInvoice){
					if($vPaInvoice > $finalPaidInvoices[$kPaInvoice]){
						$finalToPayInvoices[$kPaInvoice] =  str_replace(",", "" ,number_format(($vPaInvoice - $finalPaidInvoices[$kPaInvoice]),"2"));
					}
				}

				foreach($finalToPayInvoices as $keyInvoiceToPay=>$valueInvoiceToPay){
					/* ------------------- Add Invoice Payment START-------------- */
					$invPayment = new InvoicePayments();
					$invPayment->invoiceId = $keyInvoiceToPay;
					$invPayment->amount = Pms_CommonData::str2num($valueInvoiceToPay); //to be sure we have no commas
					$invPayment->comment = "Quick Paid";
					$invPayment->paidDate = date("Y-m-d H:i:s", strtotime("now"));
					$invPayment->isDelete = "0";
					$invPayment->save();
				}
			}

			if($status == '0' && $clientid)
			{
				$client_invoice = new ClientInvoices();

				foreach($iids as $k_inv_nr=> $v_inv_nr)
				{

					$client_invoice_number = $client_invoice->get_next_invoice_number($clientid);

					$prefix = $client_invoice_number['prefix'];
					$invoicenumber = $client_invoice_number['invoicenumber'];
					if($v_inv_nr != '99999999999999999')
					{
						$upd_invoice = Doctrine_Query::create()
						->update("ClientInvoices")
						->set('prefix', "'" . $prefix . "'")
						->set('rnummer', "'" . $invoicenumber . "'")
						->where('id="' . $v_inv_nr . '"')
						->andWhere('prefix="TEMP_"');
						$status_inv_exec = $upd_invoice->execute();

						if($status_inv_exec)
						{
							$upd_invoice_items = Doctrine_Query::create()
							->update("InvoiceItems")
							->set('itemString', "'" . $prefix . "'")
							->where('invoiceId="' . $v_inv_nr . '"')
							->andWhere('itemLabel = "prefix"');
							$status_inv = $upd_invoice_items->execute();

							$upd_invoice_items = Doctrine_Query::create()
							->update("InvoiceItems")
							->set('itemString', "'" . $invoicenumber . "'")
							->where('invoiceId="' . $v_inv_nr . '"')
							->andWhere('itemLabel = "invoicenumber"');
							$status_inv_exec = $upd_invoice_items->execute();
						}
					}
				}
			}
		}
	}

	public function SubmitPayment($post){
		/* ------------------- Get Client Invoice Payments   START-------------- */
		$invoices = Doctrine_Query::create()
		->select("*, SUM(amount) as PaidAmount")
		->from('InvoicePayments')
		->Where("invoiceId='" . $post['invoiceId'] . "'")
		->andWhere('isDelete = 0');
		$itemInvArray = $invoices->fetchArray();

		/* ------------------- Update Client Invoice   START-------------- */
		$lastPay = end($itemInvArray);
		$updateCI = Doctrine::getTable('ClientInvoices')->findOneById($post['invoiceId']);
		$curentInvoiceArr = $updateCI->toArray();
		if (empty($itemInvArray[0]['id']))
		{
			if (bccomp($curentInvoiceArr['invoiceTotal'], Pms_CommonData::str2num($post['paymentAmount'])) == 0)
			{
				$status = "2"; //completed
			}
			else if (bccomp($curentInvoiceArr['invoiceTotal'], Pms_CommonData::str2num($post['paymentAmount'])) == 1)
			{
				$status = "1"; //partial
			}
		}
		else
		{
			if (bccomp(($lastPay['PaidAmount'] + Pms_CommonData::str2num($post['paymentAmount'])), $curentInvoiceArr['invoiceTotal']) == 0)
			{
				$status = "2"; //completed
			}
			else
			{
				$status = "1"; //partial
			}
		}
		$updateCI->status = $status;
		$updateCI->paidDate = date("Y-m-d H:i:s", strtotime($post['paymentDate']));
		$updateCI->save();

		/* ------------------- Add Invoice Payment START-------------- */
		$invPayment = new InvoicePayments();
		$invPayment->invoiceId = $post['invoiceId'];
		$invPayment->amount = Pms_CommonData::str2num($post['paymentAmount']);
		$invPayment->comment = $post['paymentComment'];
		$invPayment->paidDate = date("Y-m-d H:i:s", strtotime($post['paymentDate']));
		$invPayment->isDelete = "0";
		$invPayment->save();
	}

	public function insertSapvPrices($post)
	{

		/* ------------------- Settingup Variables Used In This Form START-------------- */
		$logininfo	=	new Zend_Session_Namespace('Login_Info');
		$clientid		=	$logininfo->clientid;

		if (!empty($post))
		{
			$q = Doctrine_Query::create()
			->delete('ClientSapvPrices csp')
			->where('csp.client = "' . $clientid . '"');
			$qexec = $q->execute();

			foreach ($post['price'] as $k_vv => $v_price)
			{
				$price = new ClientSapvPrices();
				$price->client = $clientid;
				$price->sapv_type = $k_vv;
				$price->price = Pms_CommonData::str2num($v_price);
				$price->save();
			}
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
	    ->update("ClientInvoices")
	    ->set('isarchived', "1")
	    ->whereIn('id', $iids)
	    ->andWhere('clientid = "' . $clientid . '"')
	    ->andWhere('isdelete ="0"');
	    $archive = $archive_invoices->execute();
	}
	/**
	 * ISPC-2312 Ancuta 07.12.2020
	 * @param unknown $iids
	 */
	public function delete_multiple_invoices($iids)
	{
	    $iids[] = "99999999999999999";
	    
	    if(count($iids) > 0)
	    {
	        $delInvoices = Doctrine_Query::create()
	        ->update("ClientInvoices")
	        ->set('isdelete', "1")
	        ->set('status', "3")
	        ->whereIn('id', $iids)
	        ->andWhere('isdelete =0');
	        
	        $d = $delInvoices->execute();
	    }
	}
}
?>