<?php

	Doctrine_Manager::getInstance()->bindComponent('HeInvoiceItemsPeriod', 'SYSDAT');

	class HeInvoiceItemsPeriod extends BaseHeInvoiceItemsPeriod {

		public function get_items_period($invoices, $items = false)
		{
			if(is_array($invoices))
			{
				$invoices_ids = $invoices;
				$invoices_ids[] = '9999999999999';
			}
			else
			{
				$invoices_ids = array($invoices);
			}
			$q = Doctrine_Query::create()
				->select('invoice, item, paid, from_date, till_date')
				->from('HeInvoiceItemsPeriod')
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
					$q_res_arr[$v_res['invoice']][$v_res['item']]['from_date'][] = $v_res['from_date'];
					$q_res_arr[$v_res['invoice']][$v_res['item']]['till_date'][] = $v_res['till_date'];
					$q_res_arr[$v_res['invoice']][$v_res['item']]['paid_periods'][] = $v_res['paid'];
				}

				return $q_res_arr;
			}
			else
			{
				return false;
			}
		}

		public function get_flatrate_items_period($invoices)
		{
			if(is_array($invoices))
			{
				$invoices_ids = $invoices;
				$invoices_ids[] = '9999999999999';
			}
			else
			{
				$invoices_ids = array($invoices);
			}

			$flatrate_shortcuts = array('pv1pp', 'ph1pp', 'pv1', 'ph1');

			$q_items = Doctrine_Query::create()
				->select('*')
				->from('HeInvoiceItems')
				->whereIn('invoice', $invoices_ids)
				->andWhere('isdelete = "0"')
				->andWhereIn('shortcut', $flatrate_shortcuts);
			$q_items_res = $q_items->fetchArray();

			if($q_items_res)
			{
				$items_ids[] = '9999999999';
				foreach($q_items_res as $k_item => $v_item)
				{
					$items_arr[$v_item['id']] = $v_item;
					$items_ids[] = $v_item['id'];
				}


				$q = Doctrine_Query::create()
					->select('invoice, item, from_date, till_date')
					->from('HeInvoiceItemsPeriod')
					->whereIn('invoice', $invoices_ids)
					->andWhereIn('item', $items_ids);
				$q_res = $q->fetchArray();

				if($q_res)
				{
					foreach($q_res as $k_res => $v_res)
					{
						if(!in_array($v_res['from_date'], $q_res_arr[$items_arr[$v_res['item']]['shortcut']]['from_date']) )
						{
							$q_res_arr[$items_arr[$v_res['item']]['shortcut']]['from_date'][] = $v_res['from_date'];
							$q_res_arr[$items_arr[$v_res['item']]['shortcut']]['till_date'][] = $v_res['till_date'];
						}
					}
//					print_r($q_res_arr);exit;

					return $q_res_arr;
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

	}

?>