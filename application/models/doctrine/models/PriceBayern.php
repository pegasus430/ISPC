<?php

	Doctrine_Manager::getInstance()->bindComponent('PriceBayern', 'SYSDAT');

	class PriceBayern extends BasePriceBayern {

		public function get_prices($list, $clientid, $shortcuts = false, $default_price_list = false)
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
				->from('PriceBayern')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('shortcut ASC');
			$res = $query->fetchArray();

			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$shortcut_group = substr($v_res['shortcut'], 0, -1);
					$res_prices[$shortcut_group][$v_res['shortcut']] = $v_res;
				}
				
				foreach($default_price_list as $k_def => $v_def)
				{
					foreach($v_def as $k_v_def => $v_v_def)
					{
						if(!array_key_exists($k_v_def, $res_prices[$k_def]))
						{

							$res_prices[$k_def][$k_v_def]['shortcut'] = $k_v_def;
							$res_prices[$k_def][$k_v_def]['price'] = $v_v_def;
						}
					}
				}
				return $res_prices;
			}
			else if(count($shortcuts) > '0')
			{
				//set default value
				foreach($shortcuts as $k_s => $v_s)
				{
					foreach($v_s as $k_v_s => $v_v_s)
					{
						$res_default[$k_s][$v_v_s]['shortcut'] = $v_v_s;

						if($default_price_list)
						{
							$res_default[$k_s][$v_v_s]['price'] = $default_price_list[$k_s][$v_v_s];
						}
						else
						{
							$res_default[$k_s][$v_v_s]['price'] = '0.00';
						}
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
				->from('PriceBayern')
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
						if(count($default_price_list['bayern']) > 0)
						{
							$res_prices[$v_pl][$v_s]['price'] = $default_price_list['bayern'][$v_s];
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
						foreach($v_s as $k_v_s => $v_v_s)
						{
							$res_prices[$v_pl][$v_v_s]['shortcut'] = $v_v_s;
							$res_prices[$v_pl][$v_v_s]['list'] = $v_pl;
							if(count($default_price_list) > 0)
							{
								$res_prices[$v_pl][$v_v_s]['price'] = $default_price_list['bayern'][$k_s][$v_v_s];
							}
							else
							{
								$res_prices[$v_pl][$v_v_s]['price'] = '0.00';
							}
						}
					}
				}
				return $res_prices;
			}
		}

	}

?>