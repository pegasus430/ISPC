<?php

	Doctrine_Manager::getInstance()->bindComponent('EmergencyPlanSapv', 'MDAT');

	class EmergencyPlanSapv extends BaseEmergencyPlanSapv {
		
	    public function get_emergency_plan_sapv($ipid){
	        
	        $drop = Doctrine_Query::create()
	        ->select('*')
	        ->from('EmergencyPlanSapv')
	        ->where("ipid='" . $ipid . "'");
	        $droparray = $drop->fetchArray();

	        if($droparray){
	           $resulted_array = $droparray[0];
	           return $resulted_array;
	        } else{
	           return false;
	        }
	        
	        
	    }
	}
?>