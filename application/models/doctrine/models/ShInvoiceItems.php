<?php

	Doctrine_Manager::getInstance()->bindComponent('ShInvoiceItems', 'SYSDAT');

	class ShInvoiceItems extends BaseShInvoiceItems {

		public function getInvoicesItems($invoices)
		{
			$invoice_items = Doctrine_Query::create()
				->select('*')
				->from('ShInvoiceItems')
				->whereIn('invoice', $invoices)
				->andWhere('isdelete = "0"')
				->orderBy('id ASC');

			$invoice_items_res = $invoice_items->fetchArray();

			if($invoice_items_res)
			{
				foreach($invoice_items_res as $k_invoice_item => $v_invoice_item)
				{
					$v_invoice_item['description'] = html_entity_decode($v_invoice_item['description'], ENT_QUOTES, 'UTF-8');
					$v_invoice_item['shortcut'] = html_entity_decode($v_invoice_item['shortcut'], ENT_QUOTES, 'UTF-8');
					
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