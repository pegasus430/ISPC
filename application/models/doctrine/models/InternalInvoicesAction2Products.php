<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoicesAction2Products', 'SYSDAT');

	class InternalInvoicesAction2Products extends BaseInternalInvoicesAction2Products {

		public function get_actions2products($products = false,$list = false, $client = false)
		{
		    //var_dump($products && $list && $client);
			if($products && $list && $client)
			{
				$sp = Doctrine_Query::create()
					->select("*")
					->from('InternalInvoicesAction2Products')
					->whereIn('product_id', $products)
					->andWhere("list = '" . $list . "'")
					->andWhere("client = '" . $client . "'")
					->andWhere('isdelete = 0');
				$specific_products = $sp->fetchArray();

				if($specific_products)
				{
					foreach($specific_products as $k_prod => $v_prod)
					{
						$products_list[$v_prod['product_id']][] = $v_prod['action_id'];
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

		public function get_multiple_list_products($products = false,$lists = false, $client = false)
		{
			if($lists && $client)
			{
				$sp = Doctrine_Query::create()
					->select("*")
					->from('InternalInvoicesAction2Products')
					->whereIn('list', $lists)
					->andWhereIn('product_id', $products)
					->andWhere("client='" . $client . "'")
					->andWhere('isdelete=0');
				$specific_products = $sp->fetchArray();

				if($specific_products)
				{
				    $added_data = array();
				    $search_key = 0;
					foreach($specific_products as $k_prod => $v_prod)
					{
					    $search_key = $v_prod['list'].$v_prod['product_id'].$v_prod['action_id'];
					    if(!in_array($search_key,$added_data)){
	       					$products_list[$v_prod['list']][$v_prod['product_id']][] = $v_prod;
    						$added_data[] = $search_key;
					    }
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