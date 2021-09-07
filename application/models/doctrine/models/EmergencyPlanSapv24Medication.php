<?php

	Doctrine_Manager::getInstance()->bindComponent('EmergencyPlanSapv24Medication', 'MDAT');

	class EmergencyPlanSapv24Medication extends BaseEmergencyPlanSapv24Medication {
		
	    public function get_emergency_plan_sapv24_medication($planid){
	    	
	        $drop = Doctrine_Query::create()
	        ->select('*')
	        ->from('EmergencyPlanSapv24Medication')
	        ->where("planid='" . $planid . "' and isdelete='0'");
	        $droparray = $drop->fetchArray();

	        if($droparray){
	           $resulted_array = $droparray;
	           return $resulted_array;
	        } else{
	           return false;
	        }
	        
	        
	    }
	}
?>