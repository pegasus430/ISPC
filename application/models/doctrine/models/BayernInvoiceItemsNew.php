<?php

	Doctrine_Manager::getInstance()->bindComponent('BayernInvoiceItemsNew', 'SYSDAT');

	class BayernInvoiceItemsNew extends BaseBayernInvoiceItemsNew {

		public function getInvoicesItems($invoices, $separate_custom = false)
		{
			$Tr = new Zend_View_Helper_Translate();

			$invoice_items = Doctrine_Query::create()
				->select('*')
				->from('BayernInvoiceItemsNew')
				->whereIn('invoice', $invoices)
				->andWhere('isdelete="0"')
				->orderBy('id ASC');
			$invoice_items_res = $invoice_items->fetchArray();

			if($invoice_items_res)
			{
				foreach($invoice_items_res as $k_invoice_item => $v_invoice_item)
				{

					if($v_invoice_item['custom'] == "1")
					{
						$invoice_items_arr[$v_invoice_item['invoice']]['c_' . $v_invoice_item['shortcut']] = $v_invoice_item;
						$invoice_items_arr[$v_invoice_item['invoice']]['c_' . $v_invoice_item['shortcut']]['name'] = stripslashes($v_invoice_item['name']);
						$invoice_items_arr[$v_invoice_item['invoice']]['c_' . $v_invoice_item['shortcut']]['shortcut_total'] = ($v_invoice_item['qty'] * $v_invoice_item['price']);
						$invoice_items_arr[$v_invoice_item['invoice']]['c_' . $v_invoice_item['shortcut']]['total'] =  Pms_CommonData::str2num($v_invoice_item['qty'] * $v_invoice_item['price']);
					}
					else
					{
						$invoice_items_arr[$v_invoice_item['invoice']][$v_invoice_item['shortcut']] = $v_invoice_item;
						if(empty($v_invoice_item['name']))
						{
							$invoice_items_arr[$v_invoice_item['invoice']][$v_invoice_item['shortcut']]['name'] = stripslashes($Tr->translate('shortcut_name_bayern_' . substr($v_invoice_item['shortcut'], 0, -1)));
						}
						else
						{
							$invoice_items_arr[$v_invoice_item['invoice']][$v_invoice_item['shortcut']]['name'] = $v_invoice_item['name'];
						}
						$invoice_items_arr[$v_invoice_item['invoice']][$v_invoice_item['shortcut']]['shortcut_total'] = ($v_invoice_item['qty'] * $v_invoice_item['price']);
						$invoice_items_arr[$v_invoice_item['invoice']][$v_invoice_item['shortcut']]['total'] = Pms_CommonData::str2num($v_invoice_item['qty'] * $v_invoice_item['price']);
					}
				}

				return $invoice_items_arr;
			}
			else
			{
				return false;
			}
		}

		public function get_overall_completed_items($invoices_array)
		{
			$invoice_items = Doctrine_Query::create()
				->select('sum(qty) as paid_items')
				->from('BayernInvoiceItemsNew')
				->whereIn('invoice', $invoices_array)
				->andWhere('isdelete = "0" ')
				->andWhere('custom = "0" ');
			$invoice_items_res = $invoice_items->fetchArray();

			if($invoice_items_res)
			{
				$invoice_items_arr = $invoice_items_res[0]['paid_items'];

				return $invoice_items_arr;
			}
			else
			{
				return false;
			}
		}

		public function get_multi_pat_overall_completed_items($invoices_array)
		{
			$invoice_items = Doctrine_Query::create()
				->select('invoice, sum(qty) as paid_items')
				->from('BayernInvoiceItemsNew')
				->whereIn('invoice', $invoices_array)
				->andWhere('isdelete = "0" ')
				->andWhere('custom = "0" ')
				->groupBy('invoice');
			$invoice_items_res = $invoice_items->fetchArray();

			if($invoice_items_res)
			{
				foreach($invoice_items_res as $k_res => $v_res)
				{
					$invoice_items_arr[$v_res['invoice']] = $v_res;
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