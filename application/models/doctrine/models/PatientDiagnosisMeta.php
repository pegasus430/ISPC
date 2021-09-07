<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDiagnosisMeta', 'MDAT');

	class PatientDiagnosisMeta extends BasePatientDiagnosisMeta {

		public function getPatientDiagnosismeta($ipid)
		{
			$loc = Doctrine_Query::create()
				->select("*")
				->from('PatientDiagnosisMeta')
				->where("ipid=?", $ipid);
			$locat = $loc->execute();


			if(locat)
			{
				$disarr = $locat->toArray();
				return $disarr;
			}
		}

		public function getPatientDiagnosismetaFromTab($ipid)
		{
			$loc = Doctrine_Query::create()
				->select("*")
				->from('PatientDiagnosisMeta')
				->where("ipid='" . $ipid . "'")
				->andWhere("diagnoid=0");
			$locat = $loc->execute();

			if(locat)
			{
				$disarr = $locat->toArray();
				return $disarr;
			}
		}

		public function getPatientDiagnosismetaByDiagnoid($did, $ipid)
		{
			$loc = Doctrine_Query::create()
				->select("*")
				->from('PatientDiagnosisMeta')
				->where("diagnoid='" . $did . "'")
				->andWhere("ipid='" . $ipid . "'");
			$locat = $loc->execute();

			if(locat)
			{
				$disarr = $locat->toArray();
				return $disarr;
			}
		}

		public function get_multiple($ipids)
		{
			if(empty($ipids)){
				return false;
			}
			
			$loc = Doctrine_Query::create()
			->select("*")
			->from('PatientDiagnosisMeta')
			->whereIn("ipid", $ipids)
			->andWhere("metaid != 0");
			$locat = $loc->execute();
		
			if(locat)
			{
				$disarr = $locat->toArray();
				return $disarr;
				
			} else {
				return false;
			}
		}
		
	}

?>