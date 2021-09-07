<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoiceItemsPeriod', 'SYSDAT');

	class InternalInvoiceItemsPeriod extends BaseInternalInvoiceItemsPeriod {

		public function get_items_period($invoices_ids, $items = false)
		{
			if(is_array($invoices_ids))
			{
				$invoices_ids[] = '9999999999999';
			}
			else
			{
				$invoices_ids = array($invoices_ids);
			}
			$q = Doctrine_Query::create()
				->select('invoice, item, from_date, till_date')
				->from('InternalInvoiceItemsPeriod')
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
					$q_res_arr[$v_res['item']]['from_date'][] = $v_res['from_date'];
					$q_res_arr[$v_res['item']]['till_date'][] = $v_res['till_date'];
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