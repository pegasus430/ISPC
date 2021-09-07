<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanExtraAlt', 'MDAT');

	class PatientDrugPlanExtraAlt extends BasePatientDrugPlanExtraAlt {

	    
	    public static function get_patient_drugplan_extra_alt_all($ipid, $alt_ids = array()){
	        
	        if (empty($alt_ids) || empty($ipid) || ! is_array($alt_ids)){
	            return;
	        }
	        
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	        
	        /**
	         * why you first fetch :: client_medication_unit, client_medication_unit, client_medication_dosage_form, client_medication_indications ?
	         * you will retur this 4 only if you have PatientDrugPlanExtraAlt
	         * if (! empty($alt_meds)) {fetch the 4}
	         * foreach($alt_meds as $k=>$extra_det) {...
	         */
	        
	        //UNIT
	        $medication_unit = MedicationUnit::client_medication_unit($clientid);
	        
	        foreach($medication_unit as $k=>$unit){
	            $client_medication_extra['unit'][$unit['id']] = $unit['unit'];
	        }
	        
	        
	        //TYPE
	        $medication_types = MedicationType::client_medication_types($clientid,true);
	        foreach($medication_types as $k=>$type){
	            $client_medication_extra['type'][$type['id']] = $type['type'];
	        }
	        
	        //DOSAGE FORM
	        $medication_dosage_forms = MedicationDosageform::client_medication_dosage_form($clientid,true);
	        
	        foreach($medication_dosage_forms as $k=>$df){
	            $client_medication_extra['dosage_form'][$df['id']] = $df['dosage_form'];
	        }
	        
	        
	        //INDICATIONS
	        $medication_indications = MedicationIndications::client_medication_indications($clientid);
	        
	        foreach($medication_indications as $k=>$indication){
	            $client_medication_extra['indication'][$indication['id']]['name'] = $indication['indication'];
	            $client_medication_extra['indication'][$indication['id']]['color'] = $indication['indication_color'];
	        }
	        
	        
	        //Packaaging array ISPC-2176
	        $packaging_array = PatientDrugPlanExtra::intubated_packaging();
	        // Escalation - ISPC-2247 
	        $escalation_array = PatientDrugPlanExtra::getMedicationEscalation();
	        
// 	        if(empty($alt_ids)){
// 	            $alt_ids[] = "999999999";
// 	        }

	        $alt_meds = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanExtraAlt')
	        ->where("ipid = ?", $ipid )
	        ->andWhere("isdelete = 0")
	        ->andWhereIn("drugplan_id_alt", $alt_ids)
	        ->orderBy("id ASC")
	        ->fetchArray();

	        foreach($alt_meds as $k=>$extra_det)
	        {
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['drug'] =  htmlentities($extra_det['drug'], ENT_QUOTES, "UTF-8");
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['unit'] =   $client_medication_extra['unit'][$extra_det['unit']];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['unit_id'] =  $extra_det['unit'];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['type'] =   $client_medication_extra['type'][$extra_det['type']];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['type_id'] =   $extra_det['type'];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['indication'] =   $client_medication_extra['indication'][$extra_det['indication']]['name'];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['indication_id'] =   $extra_det['indication'];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['indication_color'] =   $client_medication_extra['indication'][$extra_det['indication']]['color'];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['dosage_form'] =   $client_medication_extra['dosage_form'][$extra_det['dosage_form']];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['dosage_form_id'] =   $extra_det['dosage_form'];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['concentration'] =   $extra_det['concentration'];
	            
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['packaging'] =   $extra_det['packaging'];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['packaging_name'] =   $packaging_array[$extra_det['packaging']];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['kcal'] =   $extra_det['kcal'];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['volume'] =   $extra_det['volume'];
	            
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['escalation'] =   $extra_det['escalation'];
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['escalation_name'] =   $escalation_array[$extra_det['escalation']];
	            
	            $extra_alt[$extra_det['drugplan_id_alt']][$extra_det['drugplan_id']]['importance'] =   $extra_det['importance'];
	        }
	         
	        return $extra_alt;
	    }
	    
	}

?>