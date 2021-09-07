<?php

	Doctrine_Manager::getInstance()->bindComponent('RlpInvoiceItems', 'SYSDAT');

	class RlpInvoiceItems extends BaseRlpInvoiceItems {

		public function getInvoicesItems($invoices, $separate_custom = false)
		{
			$invoice_items = Doctrine_Query::create()
				->select('*')
				->from('RlpInvoiceItems')
				->whereIn('invoice', $invoices)
				->andWhere('isdelete="0"')
				->orderBy('id ASC');
			$invoice_items_res = $invoice_items->fetchArray();

			if($invoice_items_res)
			{ 
				
				foreach($invoice_items_res as $k_invoice_item => $v_invoice_item)
				{
					$invoice_items_arr[$v_invoice_item['invoice']][$v_invoice_item['shortcut']][] = $v_invoice_item;
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