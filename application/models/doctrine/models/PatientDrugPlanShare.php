<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanShare', 'MDAT');

	class PatientDrugPlanShare extends BasePatientDrugPlanShare { 
	    
	    
	  public  function get_shared($ipids){
	        
	        if(!is_array($ipids)){
	            $ipids = array($ipids);
	        }
	        
	        $share_drug_src = Doctrine_Query::create()
	        ->select("*")
	        ->from('PatientDrugPlanShare')
	        ->whereIn('ipid',$ipids);
	        $share_drug_src_arra = $share_drug_src->fetchArray();
	        
	        foreach($share_drug_src_arra as $k=>$s)
	        {
	            $shared[$s['ipid']][] = $s['drugplan_id'];
	        }
	        
	        return $shared;
	        
	    }
	    
	    
	}

?>