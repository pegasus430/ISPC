<?php
//ISPC-2736 Lore 12.11.2020

	Doctrine_Manager::getInstance()->bindComponent('EmergencyPlanSapvII', 'MDAT');

	class EmergencyPlanSapvII extends BaseEmergencyPlanSapvII {
		
	    public function get_emergency_plan_sapv_ii($ipid){
	        
	        $drop = Doctrine_Query::create()
	        ->select('*')
	        ->from('EmergencyPlanSapvII')
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