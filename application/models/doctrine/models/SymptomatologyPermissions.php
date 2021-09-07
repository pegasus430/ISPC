<?php

	Doctrine_Manager::getInstance()->bindComponent('SymptomatologyPermissions', 'SYSDAT');

	class SymptomatologyPermissions extends BaseSymptomatologyPermissions {

		public function getClientSymptomatology($clientid)
		{
			$q = Doctrine_Query::create()
				->select('*, ss.name as set_name')
				->from("SymptomatologyPermissions sp")
				->leftJoin('sp.SymptomatologySets ss')
				->where("clientid='" . $clientid . "'")
				->orderBy("setorder");
			$setarr = $q->fetchArray();

			if(!empty($setarr))
			{
				foreach($setarr as $set)
				{
					$retset[$set['setid']] = $set;
				}

				return $retset;
			}
			else
			{
				return false;
			}
		}

	}

?>