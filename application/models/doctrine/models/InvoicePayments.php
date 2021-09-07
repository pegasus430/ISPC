<?php

	Doctrine_Manager::getInstance()->bindComponent('InvoicePayments', 'SYSDAT');

	class InvoicePayments extends BaseInvoicePayments {

		public function getInvoicePaymentSum($invoiceid)
		{
			$invoices = Doctrine_Query::create()
				->select("*, SUM(amount)")
				->from('InvoicePayments')
				->Where("invoiceId='" . $invoiceid . "'")
				->andWhere('isDelete = 0');
			$itemInvArray = $invoices->fetchArray();

			return $itemInvArray;
		}

		public function deleteInvoicePayment($paymentid)
		{
			if($paymentid > 0)
			{
				$delInvoicePayment = Doctrine_Query::create()
					->update("InvoicePayments")
					->set('isDelete', "1")
					->where('id = "' . $paymentid . '"');
				$d = $delInvoicePayment->execute();
			}
			return $d;
		}

		public function getInvoicePayments($invoiceid)
		{
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('InvoicePayments')
				->Where("invoiceId='" . $invoiceid . "'")
				->andWhere('isDelete = 0');
			$itemInvArray = $invoices->fetchArray();

			return $itemInvArray;
		}

		public function getInvoicesPaymentsSum($invoiceids)
		{
			$invoiceids[] = "99999999999999";

			$invoices = Doctrine_Query::create()
				->select("*, SUM(amount) as paidAmount")
				->from('InvoicePayments')
				->WhereIn("invoiceId", $invoiceids)
				->andWhere('isDelete = 0')
				->groupBy('invoiceId');

			$itemInvsArray = $invoices->fetchArray();
			foreach($itemInvsArray as $kpay => $vpay)
			{
				$finalInvArray[$vpay['invoiceId']]['paidAmount'] = $vpay['paidAmount'];
			}
			
			return $finalInvArray;
		}

	}

?>