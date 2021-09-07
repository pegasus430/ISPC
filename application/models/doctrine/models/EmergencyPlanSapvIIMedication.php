<?php
//ISPC-2736 Lore 12.11.2020

	Doctrine_Manager::getInstance()->bindComponent('EmergencyPlanSapvIIMedication', 'MDAT');

	class EmergencyPlanSapvIIMedication extends BaseEmergencyPlanSapvIIMedication {
		
	    public function get_emergency_plan_sapv_ii_medication($planid){
	    	
	        $drop = Doctrine_Query::create()
	        ->select('*')
	        ->from('EmergencyPlanSapvIIMedication')
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