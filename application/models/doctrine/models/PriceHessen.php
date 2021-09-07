<?php

	Doctrine_Manager::getInstance()->bindComponent('PriceHessen', 'SYSDAT');

	class PriceHessen extends BasePriceHessen {

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
				->from('PriceHessen')
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

	}

?>