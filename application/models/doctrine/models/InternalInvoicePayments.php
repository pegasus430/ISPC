<?php

	//

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoicePayments', 'SYSDAT');

	class InternalInvoicePayments extends BaseInternalInvoicePayments {

		public function delete_invoice_payment($payment)
		{
			if($payment > 0)
			{
				$delInvoicePayment = Doctrine_Query::create()
					->update("InternalInvoicePayments")
					->set('isdelete', "1")
					->where('id = "' . $payment . '"');
				$d = $delInvoicePayment->execute();

				if($d)
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

		public function getInvoicePayments($invoice)
		{
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoicePayments')
				->Where("invoice='" . $invoice . "'")
				->andWhere('isdelete = 0');
			$itemInvArray = $invoices->fetchArray();
			
			return $itemInvArray;
		}

		public function getInvoicesPaymentsSum($invoiceids)
		{
			$invoiceids[] = "99999999999999";

			$invoices = Doctrine_Query::create()
				->select("*, SUM(amount) as paid_sum")
				->from('InternalInvoicePayments')
				->WhereIn("invoice", $invoiceids)
				->andWhere('isdelete = 0')
				->groupBy('invoice');
			$items_array = $invoices->fetchArray();
			
			foreach($items_array as $kpay => $vpay)
			{
				$final_invoice_items[$vpay['invoice']]['paid_sum'] = $vpay['paid_sum'];
			}
			
			return $final_invoice_items;
		}

	}

?>