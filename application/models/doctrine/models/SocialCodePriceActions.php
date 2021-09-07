<?php

	Doctrine_Manager::getInstance()->bindComponent('SocialCodePriceActions', 'SYSDAT');

	class SocialCodePriceActions extends BaseSocialCodePriceActions {

		public function get_all_actions_price_list($list, $clientid, $action_details)
		{
			$socialcodeactions = new SocialCodeActions();
			$actionlist = $socialcodeactions->getAllCientSgbvActions($clientid);

			foreach($actionlist as $key => $gr)
			{
				$action_details[$gr['id']] = $gr;
			}

			$query = Doctrine_Query::create()
				->select("*")
				->from('SocialCodePriceActions')
				->where("clientid='" . $clientid . "'")
				->andWhere('list = "' . $list . '"');
			$res = $query->fetchArray();

			if($res)
			{
				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['actionid']]['id'] = $action_details[$v_res['actionid']]['id'];
					$res_prices[$v_res['actionid']]['action_name'] = $action_details[$v_res['actionid']]['action_name'];
					$res_prices[$v_res['actionid']]['internal_nr'] = $action_details[$v_res['actionid']]['internal_nr'];
					$res_prices[$v_res['actionid']]['price'] = $action_details[$v_res['actionid']]['price'];
					$res_prices[$v_res['actionid']]['groupid'] = $action_details[$v_res['actionid']]['groupid'];
					$res_prices[$v_res['actionid']]['night_bonus'] = $action_details[$v_res['actionid']]['night_bonus'];
					$res_prices[$v_res['actionid']]['nh_sunday_bonus'] = $action_details[$v_res['actionid']]['nh_sunday_bonus'];
					$res_prices[$v_res['actionid']]['multi_resistance_bonus'] = $action_details[$v_res['actionid']]['multi_resistance_bonus'];
				}


				return $res_prices;
			}
			else if($action_details)
			{
				//set default value
				foreach($action_details as $k_s => $v_s)
				{
					$res_default[$k_s]['id'] = $v_s['id'];
					$res_default[$k_s]['action_name'] = $v_s['action_name'];
					$res_default[$k_s]['internal_nr'] = $v_s['internal_nr'];
					$res_default[$k_s]['price'] = $v_s['price'];
					$res_default[$k_s]['groupid'] = $v_s['groupid'];
					$res_default[$k_s]['night_bonus'] = $v_s['night_bonus'];
					$res_default[$k_s]['nh_sunday_bonus'] = $v_s['nh_sunday_bonus'];
					$res_default[$k_s]['multi_resistance_bonus'] = $v_s['multi_resistance_bonus'];
				}

				return $res_default;
			}
		}

		public function get_actions_price_list($list = '', $clientid = 0, $action_details =  array())
		{
			$socialcodeactions = new SocialCodeActions();
			$actionlist = $socialcodeactions->getAllCientSgbvActions($clientid, true);


			foreach($actionlist as $key => $gr)
			{
				$action_details[$gr['id']] = $gr;
				$action_array[] = $gr['id'];
			}

			$query = Doctrine_Query::create()
				->select("*")
				->from('SocialCodePriceActions')
				->where("clientid= ?", $clientid)
				->andWhere('list = ?', $list)
				->orderBy('aorder ASC');
			$res = $query->fetchArray();
			foreach($res as $k_res => $v_res)
			{
				if(in_array($v_res['actionid'], $action_array))
				{
					$res_prices[$v_res['actionid']]['id'] = $action_details[$v_res['actionid']]['id'];
					$res_prices[$v_res['actionid']]['action_name'] = $action_details[$v_res['actionid']]['action_name'];
					$res_prices[$v_res['actionid']]['internal_nr'] = $action_details[$v_res['actionid']]['internal_nr'];
					$res_prices[$v_res['actionid']]['price'] = $action_details[$v_res['actionid']]['price'];
					$res_prices[$v_res['actionid']]['groupid'] = $action_details[$v_res['actionid']]['groupid'];
					$res_prices[$v_res['actionid']]['night_bonus'] = $action_details[$v_res['actionid']]['night_bonus'];
					$res_prices[$v_res['actionid']]['nh_sunday_bonus'] = $action_details[$v_res['actionid']]['nh_sunday_bonus'];
					$res_prices[$v_res['actionid']]['multi_resistance_bonus'] = $action_details[$v_res['actionid']]['multi_resistance_bonus'];
				}
			}


			return $res_prices;
		}

	}

?>