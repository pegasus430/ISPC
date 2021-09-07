<?php

	Doctrine_Manager::getInstance()->bindComponent('MedipumpsInvoiceItemsNew', 'SYSDAT');

	class MedipumpsInvoiceItemsNew extends BaseMedipumpsInvoiceItemsNew {

		public function getInvoicesItems($invoices)
		{
			$invoice_items = Doctrine_Query::create()
				->select('*')
				->from('MedipumpsInvoiceItemsNew')
				->whereIn('invoice', $invoices)
				->andWhere('isdelete = 0')              //ISPC-2747 Lore 02.12.2020
				->orderBy('id ASC');
			$invoice_items_res = $invoice_items->fetchArray();

			if($invoice_items_res)
			{
				foreach($invoice_items_res as $k_invoice_item => $v_invoice_item)
				{
					$v_invoice_item['total'] = ($v_invoice_item['qty']*$v_invoice_item['price']);
					
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