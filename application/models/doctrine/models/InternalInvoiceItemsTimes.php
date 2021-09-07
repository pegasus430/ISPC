<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoiceItemsTimes', 'SYSDAT');

	class InternalInvoiceItemsTimes extends BaseInternalInvoiceItemsTimes {

		public function get_items_times($invoices, $items = false)
		{
			if(is_array($invoices))
			{
				$invoices_ids[] = '9999999999999';
			}
			else
			{
				$invoices_ids = array($invoices);
			}
			$q = Doctrine_Query::create()
				->select('invoice, item, start_hours, end_hours')
				->from('InternalInvoiceItemsTimes')
				->whereIn('invoice', $invoices_ids);
			if($items)
			{
				$q->andWhereIn('item', $items);
			}
			$q_res = $q->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$q_res_arr[$v_res['item']]['start_hours'][] = $v_res['start_hours'];
					$q_res_arr[$v_res['item']]['end_hours'][] = $v_res['end_hours'];
				}

				return $q_res_arr;
			}
			else
			{
				return false;
			}
		}

	}

?>