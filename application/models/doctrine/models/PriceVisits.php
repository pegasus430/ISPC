<?php

	Doctrine_Manager::getInstance()->bindComponent('PriceVisits', 'SYSDAT');

	class PriceVisits extends BasePriceVisits {

		public function get_prices($list, $clientid, $shortcuts = false)
		{
		    $default_dta  = Pms_CommonData::get_nd_dta_default_ids();
		    
			if(is_array($list))
			{
				$list_ids = $list;
			}
			else
			{
				$list_ids[] = $list;
			}
			$list_ids[] = '99999999';

			$query = Doctrine_Query::create()
				->select("*")
				->from('PriceVisits')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('shortcut ASC');
			$res = $query->fetchArray();

			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['shortcut']] = $v_res;
					if(empty($v_res['dta_id'])){
					    $res_prices[$v_res['shortcut']]['dta_id'] = $default_dta['nd_dta']['visits'][$v_res['shortcut']];
					}
				}

				return $res_prices;
			}
			else if($shortcuts)
			{
				//set default value
				foreach($shortcuts as $k_s => $v_s)
				{
					$res_default[$v_s]['shortcut'] = $v_s;
					$res_default[$v_s]['price'] = '0.00';
					$res_default[$v_s]['t_start'] = '0';
					$res_default[$v_s]['t_end'] = '0';
					$res_default[$v_s]['dta_id'] = $default_dta['nd_dta']['visits'][$v_s];
					$res_default[$v_s]['dta_price'] = '0.00';
				}

				return $res_default;
			}
		}

		public function get_multiple_list_price($list, $clientid, $shortcuts)
		{
		    $default_dta  = Pms_CommonData::get_nd_dta_default_ids();
		    
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
				->from('PriceVisits')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('shortcut ASC');
			$res = $query->fetchArray();

			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['list']][$v_res['shortcut']] = $v_res;
					if(empty($v_res['dta_id'])){
					    $res_prices[$v_res['list']][$v_res['shortcut']]['dta_id'] = $default_dta['nd_dta']['visits'][$v_res['shortcut']];
					}
					$res_list_ids[] = $v_res['list'];
				}

				$res_list_ids = array_values(array_unique($res_list_ids));

				//sort both ids array
				asort($res_list_ids);
				asort($list_ids);

				$empty_price_list = array_diff($list_ids, $res_list_ids);

				foreach($empty_price_list as $key_pl => $v_pl)
				{
					//set default value for empty lists
					foreach($shortcuts as $k_s => $v_s)
					{
						$res_prices[$v_pl][$v_s]['shortcut'] = $v_s;
						$res_prices[$v_pl][$v_s]['price'] = '0.00';
						$res_prices[$v_pl][$v_s]['t_start'] = '0';
						$res_prices[$v_pl][$v_s]['t_end'] = '0';
						$res_prices[$v_pl][$v_s]['dta_id'] = $default_dta['nd_dta']['visits'][$v_s];
						$res_prices[$v_pl][$v_s]['dta_price'] = '0.00';
					}
				}

				return $res_prices;
			}
			else
			{
				//in case of finding nothing
				foreach($list_ids as $key_pl => $v_pl)
				{
					//set default value for empty lists
					foreach($shortcuts as $k_s => $v_s)
					{
						$res_prices[$v_pl][$v_s]['shortcut'] = $v_s;
						$res_prices[$v_pl][$v_s]['list'] = $v_pl;
						$res_prices[$v_pl][$v_s]['price'] = '0.00';
						$res_prices[$v_pl][$v_s]['t_start'] = '0';
						$res_prices[$v_pl][$v_s]['t_end'] = '0';
						$res_prices[$v_pl][$v_s]['dta_id'] = $default_dta['nd_dta']['visits'][$v_s];
						$res_prices[$v_pl][$v_s]['dta_price'] = '0.00';
					}
				}

				return $res_prices;
			}
		}

	}

?>