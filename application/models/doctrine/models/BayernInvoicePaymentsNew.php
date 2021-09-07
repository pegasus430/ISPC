<?php

	Doctrine_Manager::getInstance()->bindComponent('BayernInvoicePaymentsNew', 'SYSDAT');

	class BayernInvoicePaymentsNew extends BaseBayernInvoicePaymentsNew {

		public function delete_invoice_payment($payment)
		{
			if($payment > 0)
			{
				$delInvoicePayment = Doctrine_Query::create()
					->update("BayernInvoicePaymentsNew")
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
				->from('BayernInvoicePaymentsNew')
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
				->from('BayernInvoicePaymentsNew')
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
		
		public function get_invoice($invoiceid)
		{
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('BayernInvoicePaymentsNew')
				->andWhere('id = "' . $invoiceid . '"')
				->andWhere('isdelete = 0');
			$invoices_res = $invoices->fetchArray();

			if($invoices_res)
			{
				return true;
			}
			else
			{
				return $invoices;
			}
		}

	}

?>