<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanDosage', 'MDAT');

	class PatientDrugPlanDosage extends BasePatientDrugPlanDosage {

	    public function get_patient_drugplan_dosage($ipid,$drug_plan_id  = false)
	    {
	        $drugs = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanDosage')
	        ->where("ipid = '" . $ipid . "'")
	        ->andWhere("isdelete = '0'");
	        if($drug_plan_id){
	           $drugs->andWhere("drugplan_id = '".$drug_plan_id."' ");
	        }
	        $drugs->orderBy("dosage_time_interval ASC");
	        $drugsarray = $drugs->fetchArray();
	    
	        if($drugsarray)
	        {
                foreach($drugsarray as $k_drug => $v_drug)
                {
                	//ISPC-2329 Carmen 13.01.2020
                    //$time = date("H:i",strtotime($v_drug['dosage_time_interval']));
                	$time = substr($v_drug['dosage_time_interval'], 0, 5);
                    $drugs_dosage_array[$v_drug['drugplan_id']][$time] = $v_drug['dosage'];
                }
	    
	            return $drugs_dosage_array;
	        }
	    }
	    /**
	     * //TODO-3624 Ancuta 23.11.2020
	     * @param unknown $ipid
	     * @param boolean $drug_plan_id
	     * @return unknown
	     */
	    public function get_patient_drugplan_dosage_concentration($ipid,$drug_plan_id  = false)
	    {
	        $drugs = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanDosage')
	        ->where("ipid = '" . $ipid . "'")
	        ->andWhere("isdelete = '0'");
	        if($drug_plan_id){
	           $drugs->andWhere("drugplan_id = '".$drug_plan_id."' ");
	        }
	        $drugs->orderBy("dosage_time_interval ASC");
	        $drugsarray = $drugs->fetchArray();
	    
	        if($drugsarray)
	        {
	            $drugs_dosage_concentration_array = array();
                foreach($drugsarray as $k_drug => $v_drug)
                {
                	//ISPC-2329 Carmen 13.01.2020
                    //$time = date("H:i",strtotime($v_drug['dosage_time_interval']));
                	$time = substr($v_drug['dosage_time_interval'], 0, 5);
                	$drugs_dosage_concentration_array[$v_drug['drugplan_id']][$time] = $v_drug['dosage_concentration'];
                }
	    
                return $drugs_dosage_concentration_array;
	        }
	    }
	    
	    public function get_all_patient_drugplan_dosage($ipid,$drug_plan_id  = false)
	    {
	        $drugs = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanDosage')
	        ->where("ipid = '" . $ipid . "'")
	        ->andWhere("isdelete = '0'");
	        if($drug_plan_id){
	           $drugs->andWhere("drugplan_id = '".$drug_plan_id."' ");
	        }
	        $drugs->orderBy("dosage_time_interval ASC");
	        $drugsarray = $drugs->fetchArray();
	        
	        if($drugsarray)
	        {
	            return $drugsarray;
	        }
	    }
	     
	}

?>