<?php

	Doctrine_Manager::getInstance()->bindComponent('PricePerformance', 'SYSDAT');

	class PricePerformance extends BasePricePerformance {

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
				->from('PricePerformance')
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
		
		public function get_pricesbylocation_type($list, $clientid, $shortcuts = false, $default_price_list = false,$bw_location_types = false)
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
				->from('PricePerformance')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('shortcut ASC');
			$res = $query->fetchArray();

			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['location_type']][$v_res['shortcut']] = $v_res;
				}

				/* foreach($bw_location_types as $loctype_id => $loctype_name){
				
					if( empty($res_prices[$loctype_id]) ){
						
						foreach($shortcuts as $k_s => $v_s)
						{
						
							$res_prices[$loctype_id][$v_s]['shortcut'] = $v_s;
							if($default_price_list)
							{
								$res_prices[$loctype_id][$v_s]['price'] = $default_price_list[$loctype_id][$v_s];
							}
							else
							{
								$res_prices[$loctype_id][$v_s]['price'] = '0.00';
							}
						}
					}
				} */
				
				return $res_prices;
			}
			else if($shortcuts)
			{
				//set default value
				
				foreach($bw_location_types as $loctype_id => $loctype_name){
					foreach($shortcuts as $k_s => $v_s)
					{
						$res_default[$loctype_id][$v_s]['shortcut'] = $v_s;
						if($default_price_list)
						{
							$res_default[$loctype_id][$v_s]['price'] = $default_price_list[$loctype_id][$v_s];
						}
						else
						{
							$res_default[$loctype_id][$v_s]['price'] = '0.00';
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
				->from('PricePerformance')
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
						if(count($default_price_list['performance']) > 0)
						{
							$res_prices[$v_pl][$v_s]['price'] = $default_price_list['performance'][$v_s];
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
							$res_prices[$v_pl][$v_s]['price'] = $default_price_list['performance'][$v_s];
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


		public function get_multiple_list_pricebylocation($list, $clientid, $shortcuts)
		{
			$default_price_list = Pms_CommonData::get_default_price_shortcuts();
			$bw_location_types =  Pms_CommonData::get_default_bw_price_location_types();
			
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
				->from('PricePerformance')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $list_ids)
				->orderBy('shortcut ASC');

			$res = $query->fetchArray();
// 			foreach($bw_location_types as $loctype_id => $loctype_name){
			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['list']][$v_res['location_type']][$v_res['shortcut']] = $v_res;
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
					foreach($bw_location_types as $loctype_id => $loctype_name)
					{
						foreach($shortcuts as $k_s => $v_s)
						{
							$res_prices[$v_pl][$loctype_id][$v_s]['shortcut'] = $v_s;
							if(count($default_price_list['performancebylocation'][$loctype_id]) > 0)
							{
								$res_prices[$v_pl][$loctype_id][$v_s]['price'] = $default_price_list['performancebylocation'][$loctype_id][$v_s];
							}
							else
							{
								$res_prices[$v_pl][$loctype_id][$v_s]['price'] = '0.00';
							}
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
					foreach($bw_location_types as $loctype_id => $loctype_name)
					{
						foreach($shortcuts as $k_s => $v_s)
						{
							$res_prices[$v_pl][$loctype_id][$v_s]['shortcut'] = $v_s;
							$res_prices[$v_pl][$loctype_id][$v_s]['list'] = $v_pl;
							if(count($default_price_list['performancebylocation'][$loctype_id]) > 0)
							{
								$res_prices[$v_pl][$loctype_id][$v_s]['price'] = $default_price_list['performancebylocation'][$loctype_id][$v_s];
							}
							else
							{
								$res_prices[$v_pl][$loctype_id][$v_s]['price'] = '0.00';
							}
						}
					}
				}

				return $res_prices;
			}
		}

	}

?>