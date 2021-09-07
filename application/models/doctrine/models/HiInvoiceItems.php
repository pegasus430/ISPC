<?php

	Doctrine_Manager::getInstance()->bindComponent('HiInvoiceItems', 'SYSDAT');

	class HiInvoiceItems extends BaseHiInvoiceItems {

		public function getInvoicesItems($invoices)
		{
			$invoice_items = Doctrine_Query::create()
				->select('*')
				->from('HiInvoiceItems')
				->whereIn('invoice', $invoices)
				->andWhere('isdelete = 0')              //ISPC-2747 Lore 02.12.2020
				->orderBy('id ASC');

			$invoice_items_res = $invoice_items->fetchArray();

			if($invoice_items_res)
			{
				foreach($invoice_items_res as $k_invoice_item => $v_invoice_item)
				{
					$invoice_items_arr[$v_invoice_item['invoice']][] = $v_invoice_item;
				}

				return $invoice_items_arr;
			}
			else
			{
				return false;
			}
		}

	}

?>