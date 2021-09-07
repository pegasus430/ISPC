<?php

	Doctrine_Manager::getInstance()->bindComponent('HealthInsurancePermissions', 'SYSDAT');

	class HealthInsurancePermissions extends BaseHealthInsurancePermissions {

		public function getClientHealthInsurancePermissions($clientid = 0)
		{
			$q = Doctrine_Query::create()
				->select('*, hs.name as subdivision_name')
				->from("HealthInsurancePermissions  hp")
				->leftJoin('hp.HealthInsuranceSubdivisions hs')
				->where("clientid = ?", $clientid)
				->orderBy("subdiv_id ASC");
			$subdiv_arr = $q->fetchArray();

			if(!empty($subdiv_arr))
			{
				foreach($subdiv_arr as $subdiv)
				{
					$retsubdiv[$subdiv['subdiv_id']] = $subdiv;
				}

				return $retsubdiv;
			}
			else
			{
				return false;
			}
		}

	}

?>