<?php

	Doctrine_Manager::getInstance()->bindComponent('HeInvoiceItems', 'SYSDAT');

	class HeInvoiceItems extends BaseHeInvoiceItems {

		public function getInvoicesItems($invoices, $separate_custom = false)
		{
			$invoice_items = Doctrine_Query::create()
				->select('*')
				->from('HeInvoiceItems')
				->whereIn('invoice', $invoices)
				->andWhere('isdelete="0"')
				->orderBy('from_date ASC');
			$invoice_items_res = $invoice_items->fetchArray();

			if($invoice_items_res)
			{
				foreach($invoice_items_res as $k_invoice_item => $v_invoice_item)
				{
					//split normal and custom items so we can sort only normal items by date(custom items have data = 0000-00-00..)
					if($v_invoice_item['custom'] == '0')
					{
						$invoice_items_arr[$v_invoice_item['invoice']][$v_invoice_item['shortcut']] = $v_invoice_item;
					}
					else
					{
//						$custom_invoice_items[$v_invoice_item['invoice']][$v_invoice_item['shortcut']] = $v_invoice_item;
						$custom_invoice_items[$v_invoice_item['invoice']][$v_invoice_item['id']] = $v_invoice_item;
					}

					//sort normal items
					$invoice_items_arr[$v_invoice_item['invoice']] = $this->array_sort($invoice_items_arr[$v_invoice_item['invoice']], 'from_date', SORT_ . strtoupper(ASC));

					$invoice_items_ids[] = $v_invoice_item['id'];
					$invoice_id2shortcut[$v_invoice_item['id']] = $v_invoice_item['shortcut'];
				}

				//merge normal and custom items
				if(count($custom_invoice_items[$v_invoice_item['invoice']]) > 0)
				{
//					$invoice_items_arr[$v_invoice_item['invoice']] = array_merge($invoice_items_arr[$v_invoice_item['invoice']], $custom_invoice_items[$v_invoice_item['invoice']]);
					foreach($custom_invoice_items[$v_invoice_item['invoice']] as $k_cust_item => $v_cust_item)
					{
						$invoice_items_arr[$v_invoice_item['invoice']][$k_cust_item] = $v_cust_item;
					}
				}

				if($invoice_items_ids)
				{
					$invoice_items_period = HeInvoiceItemsPeriod::get_items_period($invoices, $invoice_items_ids);

					if($invoice_items_period)
					{
						foreach($invoice_items_arr as $k_invoice_id => $v_invoice_items)
						{
							foreach($invoice_items_period[$k_invoice_id] as $k_i_period => $v_i_period)
							{
								$invoice_items_arr[$k_invoice_id][$invoice_id2shortcut[$k_i_period]]['from_date'] = $v_i_period['from_date'];
								$invoice_items_arr[$k_invoice_id][$invoice_id2shortcut[$k_i_period]]['till_date'] = $v_i_period['till_date'];
								$invoice_items_arr[$k_invoice_id][$invoice_id2shortcut[$k_i_period]]['paid_periods'] = $v_i_period['paid_periods'];
							}
						}
					}
				}

				return $invoice_items_arr;
			}
			else
			{
				return false;
			}
		}
		
		
		public function getInvoicesItems_DTA($clientid,$invoices, $separate_custom = false)
		{
			$invoice_items = Doctrine_Query::create()
				->select('*')
				->from('HeInvoiceItems')
				->whereIn('invoice', $invoices)
				->andWhere('isdelete="0"')
				->andWhere('client="'.$clientid.'"') 
				->orderBy('from_date ASC');
			$invoice_items_res = $invoice_items->fetchArray();

			if($invoice_items_res)
			{
				foreach($invoice_items_res as $k_invoice_item => $v_invoice_item)
				{
					//split normal and custom items so we can sort only normal items by date(custom items have data = 0000-00-00..)
					if($v_invoice_item['custom'] == '0')
					{
						$invoice_items_arr[$v_invoice_item['invoice']][$v_invoice_item['shortcut']] = $v_invoice_item;
					}
					else
					{
//						$custom_invoice_items[$v_invoice_item['invoice']][$v_invoice_item['shortcut']] = $v_invoice_item;
						$custom_invoice_items[$v_invoice_item['invoice']][$v_invoice_item['id']] = $v_invoice_item;
					}

					if($v_invoice_item['custom'] == '2')
					{
						$r_invoice_number = trim(str_replace('Rechnungen ','',$v_invoice_item['description']));
						$invoice_details = HeInvoices::get_invoicebyinvoice_number($clientid,$r_invoice_number);
						if(!empty($invoice_details)){
							$custom_invoice_items[$v_invoice_item['invoice']][$v_invoice_item['id']]['invoiced_date'] = $invoice_details['completed_date'];
						} else{
							$custom_invoice_items[$v_invoice_item['invoice']][$v_invoice_item['id']]['invoiced_date'] = "";
						}
					
					}
					
					
					//sort normal items
					$invoice_items_arr[$v_invoice_item['invoice']] = $this->array_sort($invoice_items_arr[$v_invoice_item['invoice']], 'from_date', SORT_ . strtoupper(ASC));

					$invoice_items_ids[] = $v_invoice_item['id'];
					$invoice_id2shortcut[$v_invoice_item['id']] = $v_invoice_item['shortcut'];

										
					//merge normal and custom items
					if(count($custom_invoice_items[$v_invoice_item['invoice']]) > 0)
					{
						foreach($custom_invoice_items[$v_invoice_item['invoice']] as $k_cust_item => $v_cust_item)
						{
							$invoice_items_arr[$v_invoice_item['invoice']][$k_cust_item] = $v_cust_item;
						}
					}
				}

				if($invoice_items_ids)
				{
					$invoice_items_period = HeInvoiceItemsPeriod::get_items_period($invoices, $invoice_items_ids);

					if($invoice_items_period)
					{
						foreach($invoice_items_arr as $k_invoice_id => $v_invoice_items)
						{
							foreach($invoice_items_period[$k_invoice_id] as $k_i_period => $v_i_period)
							{
								$invoice_items_arr[$k_invoice_id][$invoice_id2shortcut[$k_i_period]]['from_date'] = $v_i_period['from_date'];
								$invoice_items_arr[$k_invoice_id][$invoice_id2shortcut[$k_i_period]]['till_date'] = $v_i_period['till_date'];
								$invoice_items_arr[$k_invoice_id][$invoice_id2shortcut[$k_i_period]]['paid_periods'] = $v_i_period['paid_periods'];
							}
						}
					}
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