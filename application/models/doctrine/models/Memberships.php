<?php

	Doctrine_Manager::getInstance()->bindComponent('Memberships', 'SYSDAT');

	class Memberships extends BaseMemberships {

		public function get_memberships($clientid = 0)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Memberships')
				->where("clientid = ?", $clientid)
				->andWhere("isdelete='0'");
			$medics = $medic->fetchArray();

			if($medics)
			{
				return $medics;
			}
		}

		public function membership_details($clientid = 0 , $membership = 0)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Memberships')
				->where("id = ?", $membership)
				->andWhere("clientid = ?", $clientid);
			$medics = $medic->fetchArray();


			if($medics)
			{
				$medipump = $medics[0];
			}
			return $medipump;
		}

	}

?>