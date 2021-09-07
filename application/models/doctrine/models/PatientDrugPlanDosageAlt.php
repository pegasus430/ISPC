<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanDosageAlt', 'MDAT');

	class PatientDrugPlanDosageAlt extends BasePatientDrugPlanDosageAlt {
	    
	    //TODO-3624 Ancuta 23.11.2020 - added param new
	    public static function get_patient_drugplan_dosage_alt_all($ipid = '', $alt_ids = array(),$all_data = false){
	        
	        if(empty($alt_ids) || ! is_array($alt_ids)){
// 	            $alt_ids[] = "999999999";
	            return;     
	        }
	        
	        $drugs_alt = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanDosageAlt')
	        ->where("ipid = ? " , $ipid)
	        ->andWhere("isdelete = 0")
	        ->andWhereIn("drugplan_id_alt", $alt_ids)
	        ->orderBy("id ASC")
	        ->fetchArray();
	        
	        $dosage_alt = array();
	        
	        foreach($drugs_alt as $k=>$dosage_det){
	        	//ISPC-2329 Carmen 13.01.2020
	            //$time = date("H:i", strtotime($dosage_det['dosage_time_interval']));
	        	$time = substr($dosage_det['dosage_time_interval'], 0, 5);
	            $dosage_alt[$dosage_det['drugplan_id_alt']][$dosage_det['drugplan_id']][$time] = $dosage_det['dosage']; 
	            
	            if($all_data){
	               $dosage_alt[$dosage_det['drugplan_id_alt']][$dosage_det['drugplan_id']][$time] = $dosage_det; 
	            }
	        }
	          
	        return $dosage_alt;
	    }
	    
	}

?>