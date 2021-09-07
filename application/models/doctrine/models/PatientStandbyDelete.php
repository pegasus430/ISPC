<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientStandbyDelete', 'IDAT');

	class PatientStandbyDelete extends BasePatientStandbyDelete {
		
		public function get_patient_standby_fall($ipids) 
		{
			
			
			if(!is_array($ipids)) {
				
				$ipidsarr[] = $ipids;
				
			}
			$ipidsarr[] = '999999999';
			
			
			$patientfall = Doctrine_Query::create()
			->select('*')
			->from('PatientStandbyDelete p')
			->whereIn('p.ipid', $ipids);
			
			$patientfall_data = $patientfall->fetchArray();
			
			if(!empty($patientfall_data)) {
				return $patientfall_data;
			}
		}
		 
		
		
	}

?>