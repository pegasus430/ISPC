<?php

	Doctrine_Manager::getInstance()->bindComponent('SocialCodeBonuses', 'SYSDAT');

	class SocialCodeBonuses extends BaseSocialCodeBonuses {

		public function getCientSocialCodeBonuses($clientid)
		{
			$bonuss_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeBonuses')
				->where("clientid=" . $clientid . "")
				->andWhere('isdelete = 0');
			$bonussarray = $bonuss_sql->fetchArray();

			if($bonussarray)
			{
				return $bonussarray;
			}
		}

		public function getSocialCodeBonus($bonusid)
		{
			$bonus_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeBonuses')
				->where("id = '" . $bonusid . "'")
				->andWhere('isdelete = 0');
			$bonus_details = $bonus_sql->fetchArray();

			if($bonus_details)
			{
				return $bonus_details;
			}
		}

		public function getSocialCodeBonusName($clientid)
		{
			$bonus_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeBonuses')
				->where("clientid = '" . $clientid . "'")
				->andWhere('isdelete = 0');
			$bonus_details = $bonus_sql->fetchArray();

			foreach($bonus_details as $key => $bonus)
			{
				$bonusname[$bonus['id']] = $bonus['bonusname'];
			}

			if($bonus_details)
			{
				return $bonusname;
			}
		}

	}

?>