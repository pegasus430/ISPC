<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoicesSpecificProducts', 'SYSDAT');

	class InternalInvoicesSpecificProducts extends BaseInternalInvoicesSpecificProducts {

		public function get_list_products($list = false, $client = false)
		{
			if($list && $client)
			{
				$sp = Doctrine_Query::create()
					->select("*, IF(range_type = 'km', km_range_start, range_start) as range_start, IF(range_type = 'km', km_range_end, range_end) as range_end")
					->from('InternalInvoicesSpecificProducts')
					->where("list='" . $list . "'")
					->andWhere("client='" . $client . "'")
					->andWhere('isdelete=0');
				$specific_products = $sp->fetchArray();

				if($specific_products)
				{
					foreach($specific_products as $k_prod => $v_prod)
					{
						$products_list[$v_prod['id']] = $v_prod;
					}

					return $products_list;
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

		public function get_multiple_list_products($lists = false, $client = false)
		{
			if($lists && $client)
			{
				$sp = Doctrine_Query::create()
					->select("*")
					->from('InternalInvoicesSpecificProducts')
					->whereIn('list', $lists)
					->andWhere("client='" . $client . "'")
					->andWhere('isdelete=0');
				$specific_products = $sp->fetchArray();

				if($specific_products)
				{
					foreach($specific_products as $k_prod => $v_prod)
					{
						$products_list[$v_prod['list']][$v_prod['id']] = $v_prod;
					}

					return $products_list;
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