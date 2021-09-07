<?php

	Doctrine_Manager::getInstance()->bindComponent('PriceShInternal', 'SYSDAT');

	class PriceShInternal extends BasePriceShInternal {

		public function get_prices($list, $clientid, $shortcuts = false, $default_price_list = false)
		{
			$verordnets = Pms_CommonData::getSapvCheckBox(true);
			foreach($verordnets as $type => $vv_sh)
			{
				$sh_types[$vv_sh] = $type;
			}

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
				->from('PriceShInternal')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('shortcut ASC');
			$res = $query->fetchArray();

			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['shortcut']] = $v_res;
				}

				return $res_prices;
			}
			else if($shortcuts)
			{
				//set default value
				foreach($shortcuts as $k_s => $v_s)
				{
					$res_default[$v_s]['shortcut'] = $v_s;
					if($default_price_list)
					{
						$res_default[$v_s]['price'] = $default_price_list[$v_s];
					}
					else
					{
						$res_default[$v_s]['price'] = '0.00';
					}
				}

				return $res_default;
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
				->from('PriceShInternal')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('shortcut ASC');
			$res = $query->fetchArray();

			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['list']][$v_res['shortcut']] = $v_res;
					$res_list_ids[] = $v_res['list'];
				}

				$res_list_ids = array_values(array_unique($res_list_ids));

				asort($res_list_ids);
				asort($list_ids);

				$empty_price_list = array_diff($list_ids, $res_list_ids);


				foreach($empty_price_list as $key_pl => $v_pl)
				{
					//set default value for empty lists
					foreach($shortcuts as $k_s => $v_s)
					{
						$res_prices[$v_pl][$v_s]['shortcut'] = $v_s;
						if(count($default_price_list['sh']) > 0)
						{
							$res_prices[$v_pl][$v_s]['price'] = $default_price_list['sh_internal'][$v_s];
						}
						else
						{
							$res_prices[$v_pl][$v_s]['price'] = '0.00';
						}
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
						if(count($default_price_list) > 0)
						{
							$res_prices[$v_pl][$v_s]['price'] = $default_price_list['sh_internal'][$v_s];
						}
						else
						{
							$res_prices[$v_pl][$v_s]['price'] = '0.00';
						}
					}
				}

				return $res_prices;
			}
		}

	}

?>