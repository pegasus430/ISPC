<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoiceSDayProducts', 'SYSDAT');

	class InternalInvoicesDayProducts extends BaseInternalInvoicesDayProducts {

		public function get_list_products($list = false, $client = false)
		{
			$dp = Doctrine_Query::create()
				->select("*")
				->from('InternalInvoicesDayProducts')
				->where("list='" . $list . "'")
				->andWhere("client='" . $client . "'")
				->andWhere('isdelete=0');
			$day_products = $dp->fetchArray();

			if($day_products)
			{
				foreach($day_products as $k_prod => $v_prod)
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

		public function get_multiple_list_products($lists = false, $client = false)
		{
			if($lists && $client)
			{
				$dp = Doctrine_Query::create()
					->select("*")
					->from('InternalInvoicesDayProducts')
					->whereIn('list', $lists)
					->andWhere("client='" . $client . "'")
					->andWhere('isdelete=0');
				$day_products = $dp->fetchArray();

				if($day_products)
				{
					foreach($day_products as $k_prod => $v_prod)
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