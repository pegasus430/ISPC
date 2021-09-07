<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanDosageHistory', 'MDAT');

	class PatientDrugPlanDosageHistory extends BasePatientDrugPlanDosageHistory {
	    
	    public function drugplanid_dosage_history($ipid, $drugplan_id)
	    {
	        $drugs = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanDosageHistory')
	        ->where("ipid = '" . $ipid . "'")
	        ->andWhere("pdd_drugplan_id = '" . $drugplan_id . "'")
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