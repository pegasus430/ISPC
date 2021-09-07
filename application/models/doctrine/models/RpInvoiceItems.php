<?php

	Doctrine_Manager::getInstance()->bindComponent('RpInvoiceItems', 'SYSDAT');

	class RpInvoiceItems extends BaseRpInvoiceItems {

		public function getInvoicesItems($invoices, $separate_custom = false)
		{
			$invoice_items = Doctrine_Query::create()
				->select('*')
				->from('RpInvoiceItems')
				->whereIn('invoice', $invoices)
				->andWhere('isdelete="0"')
				->orderBy('id ASC');
			$invoice_items_res = $invoice_items->fetchArray();

			if($invoice_items_res)
			{
				foreach($invoice_items_res as $k_invoice_item => $v_invoice_item)
				{
				    //ISPC-2747 Lore 08.12.2020
				    $v_invoice_item['qty_gr']['p_custom']   = $v_invoice_item['qty'];
				    $v_invoice_item['price_gr']['p_custom'] = $v_invoice_item['price'];
				    $v_invoice_item['total']['p_custom']    = $v_invoice_item['total_custom'];
				    //.
				    
				    $v_invoice_item['qty_gr']['p_home'] = $v_invoice_item['qty_home'];
					$v_invoice_item['qty_gr']['p_nurse'] = $v_invoice_item['qty_nurse'];
					$v_invoice_item['qty_gr']['p_hospiz'] = $v_invoice_item['qty_hospiz'];

					$v_invoice_item['price_gr']['p_home'] = $v_invoice_item['price_home'];
					$v_invoice_item['price_gr']['p_nurse'] = $v_invoice_item['price_nurse'];
					$v_invoice_item['price_gr']['p_hospiz'] = $v_invoice_item['price_hospiz'];

					$v_invoice_item['total']['p_home'] = $v_invoice_item['total_home'];
					$v_invoice_item['total']['p_nurse'] = $v_invoice_item['total_nurse'];
					$v_invoice_item['total']['p_hospiz'] = $v_invoice_item['total_hospiz'];


					$invoice_items_arr[$v_invoice_item['invoice']][$v_invoice_item['shortcut']] = $v_invoice_item;

					$invoice_items_ids[] = $v_invoice_item['id'];
					$invoice_id2shortcut[$v_invoice_item['id']] = $v_invoice_item['shortcut'];
				}

				return $invoice_items_arr;
			}
			else
			{
				return false;
			}
		}

		private function array_sort($array, $on = NULL, $order = SORT_ASC)
		{
			$new_array = array();
			$sortable_array = array();

			if(count($array) > 0)
			{
				foreach($array as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $k2 => $v2)
						{
							if($k2 == $on)
							{
								if($on == 'date' || $on == 'discharge_date' || $on == 'from_date' || $on = 'from')
								{
									$sortable_array[$k] = strtotime($v2);
								}
								else
								{
									$sortable_array[$k] = ucfirst($v2);
								}
							}
						}
					}
					else
					{
						if($on == 'date' || $on == 'from_date' || $on = 'from')
						{
							$sortable_array[$k] = strtotime($v);
						}
						else
						{
							$sortable_array[$k] = ucfirst($v);
						}
					}
				}

				switch($order)
				{
					case 'SORT_ASC':
//						asort($sortable_array);
						$sortable_array = Pms_CommonData::a_sort($sortable_array);
						break;
					case 'SORT_DESC':
//						arsort($sortable_array);
						$sortable_array = Pms_CommonData::ar_sort($sortable_array);
						break;
				}

				foreach($sortable_array as $k => $v)
				{
					$new_array[$k] = $array[$k];
				}
			}

			return $new_array;
		}

	}

?>