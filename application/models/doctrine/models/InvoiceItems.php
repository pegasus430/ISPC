<?php

	Doctrine_Manager::getInstance()->bindComponent('InvoiceItems', 'SYSDAT');

	class InvoiceItems extends BaseInvoiceItems {

		public function getInvoiceItems($invoiceid, $clientid)
		{
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('InvoiceItems')
				->Where("invoiceId='" . $invoiceid . "'")
				->andWhere("clientid='" . $clientid . "'");
			$itemInvArray = $invoices->fetchArray();
			
			return $itemInvArray;
		}

		public function getMultipleInvoiceItems($invoicesid, $clientid)
		{
			$invoices = Doctrine_Query::create()
				->select("*")
				->from('InvoiceItems')
				->whereIn("invoiceId", $invoicesid)
				->andWhere("clientid='" . $clientid . "'");
			$itemInvArray = $invoices->fetchArray();

			foreach($itemInvArray as $kitem => $vitem)
			{
				$invoiceItemsArr[$vitem['invoiceId']][] = $vitem;
			}
			return $invoiceItemsArr;
		}

	}

?>