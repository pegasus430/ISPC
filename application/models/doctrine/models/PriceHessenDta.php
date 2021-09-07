<?php

	Doctrine_Manager::getInstance()->bindComponent('PriceHessenDta', 'SYSDAT');

	class PriceHessenDta extends BasePriceHessenDta {

		public function get_prices($list, $clientid, $shortcuts = false, $default_price_list = false, $default_dta_ids = false)
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
				->from('PriceHessenDta')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('id, shortcut ASC');
			$res = $query->fetchArray();

			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['list_type']][$v_res['shortcut']] = $v_res;
				}

				//append default pricelist section if not saved in db
				//(when adding new prices to a price list which is curently saved in db)
				foreach($default_price_list as $k_list => $v_list_values)
				{
					if(empty($res_prices[$k_list]))
					{
						foreach($v_list_values as $k_shortcut => $v_value)
						{
							$short['list_type'] = $k_list;
							$short['shortcut'] = $k_shortcut;
							$short['price'] = $v_value;
							$short['dta_id'] = $default_dta_ids[$k_shortcut];

							$res_prices[$k_list][$k_shortcut] = $short;
						}
					}
				}

				return $res_prices;
			}
			else if($shortcuts)
			{
//				set default value
				foreach($shortcuts as $k_s => $v_s)
				{
					foreach($v_s as $k_short => $v_short)
					{
						$res_default[$k_s][$v_short]['shortcut'] = $v_short;
						$res_default[$k_s][$v_short]['dta_id'] = $default_dta_ids[$k_s][$v_short];

						if($default_price_list)
						{
							$res_default[$k_s][$v_short]['price'] = $default_price_list[$k_s][$v_short];
						}
						else
						{
							$res_default[$k_s][$v_short]['price'] = '0.00';
						}
					}
				}

				return $res_default;
			}
		}

		public function get_multiple_list_price($list, $clientid, $shortcuts)
		{
			$res_prices = false;
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();
			$default_dta_ids = Pms_CommonData::get_he_dta_default_ids();

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
				->from('PriceHessenDta')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('shortcut ASC');
			$res = $query->fetchArray();

			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['list']]['he_dta'][$v_res['list_type']][$v_res['shortcut']] = $v_res;
					$res_list_ids[] = $v_res['list'];
				}

				$res_list_ids = array_values(array_unique($res_list_ids));

				//sort both ids array
				$empty_price_list = array_diff($list_ids, $res_list_ids);

				foreach($empty_price_list as $key_pl => $v_pl)
				{
					//set default value for empty lists
					foreach($shortcuts as $k_s => $v_s)
					{
					    foreach($v_s as $k_vs => $v_vs)
					    {
    						$res_prices[$v_pl]['he_dta'][$k_s][$v_vs]['shortcut'] = $v_vs;
    						$res_prices[$v_pl]['he_dta'][$k_s][$v_vs]['dta_id'] = $default_dta_ids['hessen_dta'][$k_s][$v_vs];
    						    
    						if(count($default_price_list['hessen_dta']) > 0)
    						{
    							$res_prices[$v_pl]['he_dta'][$k_s][$v_vs]['price'] = $default_price_list['hessen_dta'][$k_s][$v_vs];
    						}
    						else
    						{
    							$res_prices[$v_pl]['he_dta'][$k_s][$v_vs]['price'] = '0.00';
    						}
					    }
					}
				}
			}
			else
			{
				//in case of finding nothing
				foreach($list_ids as $key_pl => $v_pl)
				{
					//set default value for empty lists
					foreach($shortcuts as $k_s => $v_s)
					{
						foreach($v_s as $k_vs => $v_vs)
						{

							$res_prices[$v_pl]['he_dta'][$k_s][$v_vs]['shortcut'] = $v_vs;
							$res_prices[$v_pl]['he_dta'][$k_s][$v_vs]['list'] = $v_pl;
							$res_prices[$v_pl]['he_dta'][$k_s][$v_vs]['list_type'] = $k_s;
							$res_prices[$v_pl]['he_dta'][$k_s][$v_vs]['dta_id'] = $default_dta_ids['hessen_dta'][$k_s][$v_vs];

							if(count($default_price_list) > 0)
							{
								$res_prices[$v_pl]['he_dta'][$k_s][$v_vs]['price'] = $default_price_list['hessen_dta'][$k_s][$v_vs];
							}
							else
							{
								$res_prices[$v_pl]['he_dta'][$v_vs]['price'] = '0.00';
							}
						}
					}
				}
			}

			return $res_prices;
		}

	}

?>