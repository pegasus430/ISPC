<?php

	Doctrine_Manager::getInstance()->bindComponent('HealthInsuranceSubdivisions', 'SYSDAT');

	class HealthInsuranceSubdivisions extends BaseHealthInsuranceSubdivisions {

		public function getClientHealthInsuranceSubdivisions($company_id)
		{

			$hi2s = Doctrine_Query::create()
				->select("*")
				->from("HealthInsurance2Subdivisions")
				->where("company_id = ?", $company_id)
				->andWhere("isdelete = 0")
				->andWhere("patientonly = 0")
				->andWhere("onlyclients = 1");

			$hi2s_arr = $hi2s->fetchArray();

			if(!empty($hi2s_arr))
			{

				foreach($hi2s_arr as $skey => $subdiv_details)
				{
					$subdivizion_details[$subdiv_details['subdiv_id']] = $subdiv_details;
				}

				return $subdivizion_details;
			}
			else
			{

				return false;
			}
		}

	}

?>