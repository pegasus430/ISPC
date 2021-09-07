<?php

	Doctrine_Manager::getInstance()->bindComponent('PriceMemberships', 'SYSDAT');

	class PriceMemberships extends BasePriceMemberships {

		public function get_prices($list, $clientid, $membership_details = array())
		{
			$memberships = new Memberships();
			$membershipslist = $memberships->get_memberships($clientid);

			foreach($membershipslist as $key => $med)
			{
				$membership_details[$med['id']] = $med;
			}

			$query = Doctrine_Query::create()
				->select("*")
				->from('PriceMemberships')
				->where("clientid='" . $clientid . "'")
				->andWhere('list = "' . $list . '"');
			$res = $query->fetchArray();

			if($res)
			{
				if(!empty($membership_details[0]))
				{
					$res_prices[$membership_details[0]['id']]['membership'] = $membership_details[0]['membership'];
					$res_prices[$membership_details[0]['id']]['shortcut'] = $membership_details[0]['shortcut'];
					$res_prices[$membership_details[0]['id']]['price'] = '0';
				}

				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['membership']]['membership'] = $membership_details[$v_res['membership']]['membership'];
					$res_prices[$v_res['membership']]['shortcut'] = $membership_details[$v_res['membership']]['shortcut'];
					$res_prices[$v_res['membership']]['price'] = $v_res['price'];
				}

				return $res_prices;
			}
			else if($membership_details)
			{
				//set default value
				foreach($membership_details as $k_s => $v_s)
				{
					$res_default[$k_s]['membership'] = $v_s['membership'];
					$res_default[$k_s]['shortcut'] = $v_s['shortcut'];
					$res_default[$k_s]['price'] = '0.00';
				}

				return $res_default;
			}
		}

	}

?>