<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoiceItems', 'SYSDAT');

	class InternalInvoiceItems extends BaseInternalInvoiceItems {

		public function getInvoicesItems($invoices)
		{
			$invoice_items = Doctrine_Query::create()
				->select('*')
				->from('InternalInvoiceItems')
				->whereIn('invoice', $invoices)
				->andWhere('isdelete = "0"')
				->orderBy('id ASC');

			$invoice_items_res = $invoice_items->fetchArray();

			if($invoice_items_res)
			{
				$items_ids[] = '999999999999';
				foreach($invoice_items_res as $k_invoice_item => $v_invoice_item)
				{
					$items_ids[] = $v_invoice_item['id'];
					$invoice_items_arr[$v_invoice_item['invoice']][] = $v_invoice_item;
				}

				$items_periods = new InternalInvoiceItemsPeriod();
				$inv_items_periods = $items_periods->get_items_period($invoices);
				
				$items_times = new InternalInvoiceItemsTimes();
				$inv_items_times = $items_times->get_items_times($invoices);

				foreach($invoice_items_arr as $k_invoice_id => $v_invoice_items)
				{
					foreach($v_invoice_items as $k_inv => $v_inv_item)
					{
						$invoice_items_arr[$k_invoice_id][$k_inv]['periods'] = $inv_items_periods[$v_inv_item['id']];
						
						if(!empty($inv_items_times[$v_inv_item['id']]['start_hours']))
						{
							$invoice_items_arr[$k_invoice_id][$k_inv]['start_hours'] = $inv_items_times[$v_inv_item['id']]['start_hours'];
							$invoice_items_arr[$k_invoice_id][$k_inv]['end_hours'] = $inv_items_times[$v_inv_item['id']]['end_hours'];
						}
					}
				}
				
//				print_r($inv_items_periods);
//				print_r($inv_items_times);
//				print_r($invoice_items_arr);
//				exit;

				return $invoice_items_arr;
			}
			else
			{
				return false;
			}
		}

	}

?>