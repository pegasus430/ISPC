<?php

	Doctrine_Manager::getInstance()->bindComponent('InternalInvoicesActionProducts', 'SYSDAT');

	class InternalInvoicesActionProducts extends BaseInternalInvoicesActionProducts {

		public function get_list_products($list = false, $client = false)
		{
			if($list && $client)
			{
				$sp = Doctrine_Query::create()
					->select("*, IF(range_type = 'km', km_range_start, range_start) as range_start, IF(range_type = 'km', km_range_end, range_end) as range_end")
					->from('InternalInvoicesActionProducts')
					->where("list='" . $list . "'")
					->andWhere("client='" . $client . "'")
					->andWhere('isdelete=0');
				$specific_products = $sp->fetchArray();

				if($specific_products)
				{
				    foreach($specific_products as $k_prod => $v_prod)
					{
						$products_ids[] = $v_prod['id'];
					}
                    
					
					
					$actions2products = InternalInvoicesAction2Products::get_actions2products($products_ids,$list, $client);
					
				    foreach($specific_products as $k_prod => $v_prod)
					{
						$products_list[$v_prod['id']] = $v_prod;
						$products_list[$v_prod['id']]['actions'] = $actions2products[$v_prod['id']];
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
					->from('InternalInvoicesActionProducts')
					->whereIn('list', $lists)
					->andWhere("client='" . $client . "'")
					->andWhere('isdelete=0');
				$specific_products = $sp->fetchArray();

				if($specific_products)
				{
				    
				    foreach($specific_products as $k_prod => $v_prod)
				    {
				        $products_ids[] = $v_prod['id'];
				    }
				    
				    	
				    	
				    $actions2products = InternalInvoicesAction2Products::get_multiple_list_products($products_ids,$lists, $client);
				    
					foreach($specific_products as $k_prod => $v_prod)
					{
						$products_list[$v_prod['list']][$v_prod['id']] = $v_prod;
						$products_list[$v_prod['list']][$v_prod['id']]['actions'] = $actions2products[$v_prod['list']][$v_prod['id']];
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