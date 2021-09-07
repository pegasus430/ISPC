<?php

	Doctrine_Manager::getInstance()->bindComponent('PriceXbdtActions', 'SYSDAT');

	class PriceXbdtActions extends BasePriceXbdtActions {

		public function get_prices($list, $clientid, $action_list = array(), $display_shortcut = false)
		{
			$query = Doctrine_Query::create()
				->select("*")
				->from('PriceXbdtActions')
				->where("clientid='" . $clientid . "'")
				->andWhere('list = "' . $list . '"');
			$res = $query->fetchArray();


			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['action_id']]['price'] = $v_res['price'];
					if($display_shortcut)
					{
						$res_prices[$v_res['action_id']]['shortcut'] = $v_res['shortcut'];
					}
				}

				return $res_prices;
			}
			else if($action_list)
			{
				return $action_list;
			}
		}

		
		public function get_multiple_list_price($list, $clientid)
		{
			if(is_array($list))
			{
				$list_ids = $list;
			}
			else
			{
				$list_ids[] = $list;
			}

			if(empty($list_ids))
			{
				$list_ids[] = '99999999';
			}

			$query = Doctrine_Query::create()
				->select("*")
				->from('PriceXbdtActions')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('shortcut ASC');
			$res = $query->fetchArray();

			foreach($res as $k_res => $v_res)
			{
				$res_prices[$v_res['list']][$v_res['action_id']] = $v_res;
			}

			return $res_prices;
		}

	}

?>