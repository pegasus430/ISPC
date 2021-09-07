<?php

	Doctrine_Manager::getInstance()->bindComponent('DiagnosisIcd', 'SYSDAT');

	class DiagnosisIcd extends BaseDiagnosisIcd {

		public function getDiagnosisData($isdrop)
		{
			$Tr = new Zend_View_Helper_Translate();
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisIcd')
				->orderBy("description ASC");
			$dr = $drugs->execute();

			if($dr)
			{
				$diagnoarray = $dr->toArray();
			}

			if($isdrop == 1)
			{
				$locations = array("" => $Tr->translate('selectdiagnosis'));

				foreach($diagnoarray as $location)
				{
					$locations[$location[id]] = $location[description];
				}
				return $locations;
			}
			else
			{
				return $diagnoarray;
			}
		}

		public function getDiagnosisDataById($did)
		{
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisIcd')
				->where("id='" . $did . "'");
			$dr = $drugs->execute();

			if($dr)
			{
				$diagnoarray = $dr->toArray();
				return $diagnoarray;
			}
		}

	}

?>