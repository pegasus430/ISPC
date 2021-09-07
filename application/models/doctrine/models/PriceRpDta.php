<?php

	Doctrine_Manager::getInstance()->bindComponent('PriceRpDta', 'SYSDAT');

	class PriceRpDta extends BasePriceRpDta {

		public function get_prices($list, $clientid, $shortcuts = false,  $location_types = false, $rp_sapv_types = false)
		{
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
				->from('PriceRpDta')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('id ASC');
			$res = $query->fetchArray();
			
			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['shortcut']][$v_res['location_type']][$v_res['sapv_type']] = $v_res;
				}
				
				return $res_prices;
				
			} else{

				foreach($shortcuts as $k_def => $v_sh)
				{
					foreach($location_types as $location_id => $location_data)
					{
						foreach($rp_sapv_types as $sk=>$sapv_id)
						{
							$res_prices[$v_sh][$location_id][$sapv_id]['dta_id'] = "";
							$res_prices[$v_sh][$location_id][$sapv_id]['dta_name'] = "";
							$res_prices[$v_sh][$location_id][$sapv_id]['dta_price'] = "0.00";
						}
					}
				
					$res_prices_sorted[$v_sh] = $res_prices[$v_sh];
				}
				
				return $res_prices_sorted;
				
			}
		}

		public function get_multiple_list_price($list, $clientid, $shortcuts)
		{
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();

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
				->from('PriceRpDta')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('id ASC');
			$res = $query->fetchArray();

			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['list']][$v_res['shortcut']][$v_res['location_type']][$v_res['sapv_type']] = $v_res;
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
						if(count($default_price_list['rp']) > 0)
						{
							$res_prices[$v_pl][$v_s]['price_dta'] = $default_price_list['rp'][$v_s]['price_dta'];
						}
						else
						{
							$res_prices[$v_pl][$v_s]['price_dta'] = '0.00';
						}
					}
				}

				return $res_prices;
			}
			else
			{
// 				print_r($default_price_list['rp_dta']);
				//in case of finding nothing
			/* 	foreach($list_ids as $key_pl => $v_pl)
				{
					//set default value for empty lists
					foreach($shortcuts as $k_s => $v_s)
					{
						$res_prices[$v_pl][$v_s]['shortcut'] = $v_s;
						$res_prices[$v_pl][$v_s]['list'] = $v_pl;
						if(count($default_price_list) > 0)
						{
							$res_prices[$v_pl][$v_s]['price_dta'] = $default_price_list['rp'][$v_s]['price_dta'];
						}
						else
						{
							$res_prices[$v_pl][$v_s]['price_dta'] = '0.00';
						}
					}
				} */
				$res_prices = $default_price_list['rp_dta'];
				return $res_prices;
			}
		}

	}

?>