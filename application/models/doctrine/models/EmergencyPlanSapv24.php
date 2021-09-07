<?php

	Doctrine_Manager::getInstance()->bindComponent('EmergencyPlanSapv24', 'MDAT');

	class EmergencyPlanSapv24 extends BaseEmergencyPlanSapv24 {
		
	    public function get_emergency_plan_sapv24($ipid){
	        
	        $drop = Doctrine_Query::create()
	        ->select('*')
	        ->from('EmergencyPlanSapv24')
	        ->where("ipid= ? and isdelete= ?", array($ipid, 0));
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