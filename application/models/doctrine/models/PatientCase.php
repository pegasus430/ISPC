<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientCase', 'SYSDAT');

	class PatientCase extends BasePatientCase {

		public function getPatientCaseData($ipid)
		{
			$epid = Pms_CommonData::getEpid($ipid);
			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientCase')
				->where("epid = '" . $epid . "'");
			$dropexec = $drop->execute();
			if($dropexec)
			{
				$droparray = $dropexec->toArray();
				return $droparray;
			}
		}

	}

?>