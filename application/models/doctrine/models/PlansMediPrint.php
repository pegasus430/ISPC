<?php

	Doctrine_Manager::getInstance()->bindComponent('PlansMediPrint', 'SYSDAT');

	class PlansMediPrint extends BasePlansMediPrint {

		public function get_plans_medi_print($clientid)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PlansMediPrint')
				->where('clientid = "' . $clientid . '"')
				->andWhere('isdelete = "0"')
				->orderBy('id ASC');
			$q_res = $q->fetchArray();

			if($q_res)
			{
				$client_groups[] = '9999999';
				foreach($q_res as $k_qres => $v_res)
				{
					$client_groups[] = $v_res['plansmedi_id'];
				}

				return $client_groups;
			}
		}

	}

?>