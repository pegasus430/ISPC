<?php

	Doctrine_Manager::getInstance()->bindComponent('DiagnosisMeta', 'SYSDAT');

	class DiagnosisMeta extends BaseDiagnosisMeta {

		public function getDiagnosisMetaData($isdrop = 0)
		{

			$Tr = new Zend_View_Helper_Translate();
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisMeta')
				->where('isdelete=0')
				->orderBy('meta_title ASC');
			$dr = $drugs->execute();

			if($dr)
			{
				$diagnosisarray = $dr->toArray();
			}

			if($isdrop == 1)
			{
				$locations = array("" => $Tr->translate('selectmetadiagnosis'));

				foreach($diagnosisarray as $location)
				{
					$locations[$location[id]] = $location[meta_title];
				}

				return $locations;
			}
			else
			{
				return $diagnosisarray;
			}
		}

		public function getDiagnosisMetaDataById($did)
		{

			$drugs = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisMeta')
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