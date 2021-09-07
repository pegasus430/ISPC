<?php

	Doctrine_Manager::getInstance()->bindComponent('Anlage14ClientGroups', 'SYSDAT');

	class Anlage14ClientGroups extends BaseAnlage14ClientGroups {

		public function get_anlage14_client_groups($clientid)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('Anlage14ClientGroups')
				->where('clientid = "' . $clientid . '"')
				->andWhere('isdelete = "0"')
				->orderBy('id ASC');
			$q_res = $q->fetchArray();

			if($q_res)
			{
				$client_groups[] = '9999999';
				foreach($q_res as $k_qres => $v_res)
				{
					$client_groups[] = $v_res['groupid'];
				}

				return $client_groups;
			}
		}

	}

?>