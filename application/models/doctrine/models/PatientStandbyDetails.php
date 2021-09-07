<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientStandbyDetails', 'IDAT');

	class PatientStandbyDetails extends BasePatientStandbyDetails {

		public function get_all_standby_details($ipid)
		{
			$loc = Doctrine_Query::create()
			->select("*")
			->from('PatientStandbyDetails')
			->where("ipid='" . $ipid . "'")
			->orderBy("date ASC");
			$disarr = $loc->fetchArray();
		
			if($disarr)
			{
				foreach($disarr as $k=>$sdata){
					$standby_arr[$k] = $sdata;
					$standby_arr[$k]['status_period'] = "standby";
				}
				
				
				return $standby_arr;
			}
		}
		
		public function get_patient_standby_details_all_sorted($ipid)
		{
			$loc = Doctrine_Query::create()
			->select("*")
			->from('PatientStandbyDetails')
			->where("ipid='" . $ipid . "'")
			->orderBy("id, date ASC");
			$disarr = $loc->fetchArray();
		
			if($disarr)
			{
				foreach($disarr as $k=>$sdata){
					$standby_arr[$k] = $sdata;
					$standby_arr[$k]['status_period'] = "standby";
				}
				
				return $standby_arr;
			}
		}
		
		
	}

?>