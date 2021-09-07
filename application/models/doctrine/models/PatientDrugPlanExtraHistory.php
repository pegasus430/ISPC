<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanExtraHistory', 'MDAT');

	class PatientDrugPlanExtraHistory extends BasePatientDrugPlanExtraHistory {
	    
	    public function drugplanid_extra_history($ipid, $drugplan_id)
	    {
	        $drugs = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanExtraHistory')
	        ->where("ipid = '" . $ipid . "'")
	        ->andWhere("pde_drugplan_id = '" . $drugplan_id . "'")
	        ->orderBy("id ASC");
	        $drugs_array = $drugs->fetchArray();
	    
	        if($drugs_array)
	        {
	            return $drugs_array;
	        }
	        else
	        {
	            return false;
	        }
	    }
	}

?>