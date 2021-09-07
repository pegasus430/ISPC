<?php
Doctrine_Manager::getInstance()->bindComponent('SocialCodePriceBonuses', 'SYSDAT');
class SocialCodePriceBonuses extends BaseSocialCodePriceBonuses
{

	public function get_prices ( $list, $clientid, $bonus_details = array() )
	{
		$socialcodebonuss = new SocialCodeBonuses();
		$bonuslist = $socialcodebonuss->getCientSocialCodeBonuses($clientid);
		
		foreach($bonuslist as $key => $gr){
			$bonus_details[$gr['id']] = $gr;
		}

		$query = Doctrine_Query::create()
		    ->select("*")
		    ->from('SocialCodePriceBonuses')
		    ->where("clientid='" . $clientid . "'")
		    ->andWhere('list = "' . $list . '"');
		$res = $query->fetchArray();

		if ($res)
		{
			foreach ($res as $k_res => $v_res)
			{
				$res_prices[$v_res['bonusid']]['price'] = $v_res['price'];
				$res_prices[$v_res['bonusid']]['bonusname'] = $bonus_details[$v_res['bonusid']]['bonusname'];
				$res_prices[$v_res['bonusid']]['bonusshortcut'] = $bonus_details[$v_res['bonusid']]['bonusshortcut'];
			}
 
			return $res_prices;
		}
		else if ($bonus_details)
		{
			//set default value
			foreach ($bonus_details as $k_s => $v_s)
			{

				$res_default[$k_s]['bonusname'] = $v_s['bonusname'];
				$res_default[$k_s]['bonusshortcut'] = $v_s['bonusshortcut'];
				$res_default[$k_s]['price']= '0.00';
			}

			return $res_default;
		}



	}



}
?>