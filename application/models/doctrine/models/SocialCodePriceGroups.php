<?php

	Doctrine_Manager::getInstance()->bindComponent('SocialCodePriceGroups', 'SYSDAT');

	class SocialCodePriceGroups extends BaseSocialCodePriceGroups {

		public function get_prices($list, $clientid, $group_details = array())
		{
			$socialcodegroups = new SocialCodeGroups();
			$grouplist = $socialcodegroups->getCientSocialCodeGroups($clientid);

			foreach($grouplist as $key => $gr)
			{
				$group_details[$gr['id']] = $gr;
			}


			$query = Doctrine_Query::create()
				->select("*")
				->from('SocialCodePriceGroups')
				->where("clientid='" . $clientid . "'")
				->andWhere('list = "' . $list . '"');
			$res = $query->fetchArray();

			if($res)
			{
				if(!empty($group_details[0]))
				{
					$res_prices[$group_details[0]['id']]['price'] = '0';
					$res_prices[$group_details[0]['id']]['groupname'] = $group_details[0]['groupname'];
					$res_prices[$group_details[0]['id']]['groupshortcut'] = $group_details[0]['groupshortcut'];
				}

				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['groupid']]['price'] = $v_res['price'];
					$res_prices[$v_res['groupid']]['groupname'] = $group_details[$v_res['groupid']]['groupname'];
					$res_prices[$v_res['groupid']]['groupshortcut'] = $group_details[$v_res['groupid']]['groupshortcut'];
				}

				return $res_prices;
			}
			else if($group_details)
			{
				//set default value
				foreach($group_details as $k_s => $v_s)
				{
					$res_default[$k_s]['groupname'] = $v_s['groupname'];
					$res_default[$k_s]['groupshortcut'] = $v_s['groupshortcut'];
					$res_default[$k_s]['price'] = '0.00';
				}

				return $res_default;
			}
		}

	}

?>