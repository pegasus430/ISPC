<?php

	Doctrine_Manager::getInstance()->bindComponent('PriceMedipumps', 'SYSDAT');

	class PriceMedipumps extends BasePriceMedipumps {

		public function get_prices($list, $clientid, $medipump_details = array())
		{
			$medipumps = new Medipumps();
			$medipumpslist = $medipumps->getMedipumps($clientid);

			foreach($medipumpslist as $key => $med)
			{
				$medipump_details[$med['id']] = $med;
			}

			$query = Doctrine_Query::create()
				->select("*")
				->from('PriceMedipumps')
				->where("clientid='" . $clientid . "'")
				->andWhere('list = "' . $list . '"');
			$res = $query->fetchArray();

			if($res)
			{
				if(!empty($medipump_details[0]))
				{
					$res_prices[$medipump_details[0]['id']]['medipump'] = $medipump_details[0]['medipump'];
					$res_prices[$medipump_details[0]['id']]['shortcut'] = $medipump_details[0]['shortcut'];
					$res_prices[$medipump_details[0]['id']]['price_first'] = '0';
					$res_prices[$medipump_details[0]['id']]['first_start'] = '0';
					$res_prices[$medipump_details[0]['id']]['first_end'] = '0';
					$res_prices[$medipump_details[0]['id']]['price_follow'] = '0';
					$res_prices[$medipump_details[0]['id']]['follow_start'] = '0';
					$res_prices[$medipump_details[0]['id']]['follow_end'] = '0';
				}

				foreach($res as $k_res => $v_res)
				{
					$res_prices[$v_res['medipump']]['medipump'] = $medipump_details[$v_res['medipump']]['medipump'];
					$res_prices[$v_res['medipump']]['shortcut'] = $medipump_details[$v_res['medipump']]['shortcut'];
					$res_prices[$v_res['medipump']]['price_first'] = $v_res['price_first'];
					$res_prices[$v_res['medipump']]['first_start'] = $v_res['first_start'];
					$res_prices[$v_res['medipump']]['first_end'] = $v_res['first_end'];
					$res_prices[$v_res['medipump']]['price_follow'] = $v_res['price_follow'];
					$res_prices[$v_res['medipump']]['follow_start'] = $v_res['follow_start'];
					$res_prices[$v_res['medipump']]['follow_end'] = $v_res['follow_end'];
				}

				return $res_prices;
			}
			else if($medipump_details)
			{
				//set default value
				foreach($medipump_details as $k_s => $v_s)
				{
					$res_default[$k_s]['medipump'] = $v_s['medipump'];
					$res_default[$k_s]['shortcut'] = $v_s['shortcut'];
					$res_default[$k_s]['price_first'] = '0.00';
					$res_default[$k_s]['first_start'] = '0';
					$res_default[$k_s]['first_end'] = '0';
					$res_default[$k_s]['price_follow'] = '0.00';
					$res_default[$k_s]['follow_start'] = '0';
					$res_default[$k_s]['follow_end'] = '0';
				}

				return $res_default;
			}
		}

	}

?>