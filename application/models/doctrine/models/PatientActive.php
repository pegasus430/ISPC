<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientActive', 'IDAT');

	class PatientActive extends BasePatientActive {
		
		public function get_patient_fall($ipids, $order = false) 
		{
			$ipidsarr = array();
			
			if(!is_array($ipids)) {				
				$ipidsarr[] = $ipids;				
			}
			else 
			{
				$ipidsarr = $ipids;
			}
			
			$patientfall_data = array();
			if(!$ipidsarr)
			{
				return $patientfall_data;
			}			
			
			$patientfall = Doctrine_Query::create()
			->select('*')
			->from('PatientActive p')
			->whereIn('p.ipid', $ipidsarr);
			if($order){
				$patientfall->orderBy($order);
			}
			$patientfall_data = $patientfall->fetchArray();
			
			//if(!empty($patientfall_data)) {
				return $patientfall_data;
			//}
		}
		
	}

?>