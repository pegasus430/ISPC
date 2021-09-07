<?php

	Doctrine_Manager::getInstance()->bindComponent('SapvverordnungPermissions', 'SYSDAT');

	class SapvverordnungPermissions extends BaseSapvverordnungPermissions {

		public function getClientSapvverordnungpermissions($clientid)
		{
			$q = Doctrine_Query::create()
				->select('*, ss.name as subdivision_name')
				->from("SapvverordnungPermissions sp")
				->leftJoin('sp.SapvverordnungSubdivisions ss')
				->where("clientid='" . $clientid . "'")
				->orderBy("subdiv_order");
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