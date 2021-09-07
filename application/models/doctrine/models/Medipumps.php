<?php

	Doctrine_Manager::getInstance()->bindComponent('Medipumps', 'SYSDAT');

	class Medipumps extends BaseMedipumps {

		public function getMedipumps($clientid)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Medipumps indexBy id')
				->where("clientid = '" . $clientid . "'")
				->andWhere("isdelete='0'");
			$medics = $medic->fetchArray();

			if($medics)
			{
				return $medics;
			}
		}

		public function medipump_details($clientid, $medipump)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Medipumps')
				->where("id = '" . $medipump . "'")
				->andWhere("clientid = '" . $clientid . "'");
			$medics = $medic->fetchArray();


			if($medics)
			{
				$medipump = $medics[0];
			}
			return $medipump;
		}

	}

?>