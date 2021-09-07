<?php
Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanExtra', 'MDAT');

class PatientDrugPlanExtra extends BasePatientDrugPlanExtra
{

    public function get_patient_drugplan_extra($ipid, $clientid, $drugplan_id = false)
    {
        $drugs = Doctrine_Query::create()->select('*')
            ->from('PatientDrugPlanExtra')
            ->where("ipid = ?", $ipid)
            ->andWhere("isdelete = '0'");
        if($drugplan_id){
            $drugs ->andWhere("drugplan_id = '".$drugplan_id."'");
        }
        $drugsarray = $drugs->fetchArray();
        
        //@claudiu: return was added here so you don't make extra 4 queries
        if (empty($drugsarray)) {
            return;
        }
        
        // get details for unit
        $units_array = MedicationUnit::client_medication_unit($clientid);
        foreach ($units_array as $ku => $unit_value) {
            $unit[$unit_value['id']] = $unit_value['unit'];
        }

        // for ISPC-2247 changes were made to this page (Ancuta 06.11.2018) 
        // get details for dosage_form
        $dosage_form_array = MedicationDosageform::client_medication_dosage_form($clientid, true);// added second param true to includ extra for custom options from sets
        foreach ($dosage_form_array as $ku => $ds_value) {
            $dosage_form[$ds_value['id']] = $ds_value['dosage_form'];
        }
        
        // get details for type
        $types_array = MedicationType::client_medication_types($clientid,true);// added second param true to includ extra for custom options from sets
        foreach ($types_array as $kt => $type_value) {
            $type[$type_value['id']] = $type_value['type'];
        }
        
        // get details for indication
        $indications_array = MedicationIndications::client_medication_indications($clientid);

        foreach ($indications_array as $ki => $ind_value) {
            $indication[$ind_value['id']]['name'] = $ind_value['indication'];
            $indication[$ind_value['id']]['color'] = $ind_value['indication_color'];
        }
        
        // get details for packaging:: ISPC-2176 p6
        $packaging_array = PatientDrugPlanExtra::intubated_packaging();
        
        // get details for escalation ISPC-2247
        $medication_escalation = PatientDrugPlanExtra::getMedicationEscalation();
        
        
        if ($drugsarray) {
            $extra_details = array();
            foreach ($drugsarray as $k_drug => $v_drug) {
                
                $extra_details[$v_drug['drugplan_id']]['drug'] = $v_drug['drug'];
                $extra_details[$v_drug['drugplan_id']]['unit'] = $unit[$v_drug['unit']];
                $extra_details[$v_drug['drugplan_id']]['unit_id'] = $v_drug['unit'];
 
                $extra_details[$v_drug['drugplan_id']]['dosage_24h_manual'] = $v_drug['dosage_24h_manual'];             //ISPC-2684 Lore 12.10.2020
                $extra_details[$v_drug['drugplan_id']]['unit_dosage'] = $v_drug['unit_dosage'];             //ISPC-2684 Lore 08.10.2020
                $extra_details[$v_drug['drugplan_id']]['unit_dosage_24h'] = $v_drug['unit_dosage_24h'];     //ISPC-2684 Lore 08.10.2020
                
                $extra_details[$v_drug['drugplan_id']]['type'] = $type[$v_drug['type']];
                $extra_details[$v_drug['drugplan_id']]['type_id'] = $v_drug['type'];
                $extra_details[$v_drug['drugplan_id']]['indication'] = $indication[$v_drug['indication']];
                $extra_details[$v_drug['drugplan_id']]['indication_id'] = $v_drug['indication'];
                $extra_details[$v_drug['drugplan_id']]['importance'] = $v_drug['importance'];
                
                $extra_details[$v_drug['drugplan_id']]['dosage_form'] = $dosage_form[$v_drug['dosage_form']];
                $extra_details[$v_drug['drugplan_id']]['dosage_form_id'] = $v_drug['dosage_form'];
                $extra_details[$v_drug['drugplan_id']]['concentration'] = $v_drug['concentration'];
                
                $extra_details[$v_drug['drugplan_id']]['packaging'] = $v_drug['packaging'];
                $extra_details[$v_drug['drugplan_id']]['packaging_name'] = $packaging_array[$v_drug['packaging']];
                $extra_details[$v_drug['drugplan_id']]['kcal'] = $v_drug['kcal'];
                $extra_details[$v_drug['drugplan_id']]['volume'] = $v_drug['volume'];
                
                $extra_details[$v_drug['drugplan_id']]['escalation'] = $medication_escalation[$v_drug['escalation']];
                $extra_details[$v_drug['drugplan_id']]['escalation_id'] = $v_drug['escalation'];
                
                
                //ISPC-2833 Ancuta 02.03.2021
                $extra_details[$v_drug['drugplan_id']]['overall_dosage_h'] = $v_drug['overall_dosage_h'];
                $extra_details[$v_drug['drugplan_id']]['overall_dosage_24h'] = $v_drug['overall_dosage_24h'];
                $extra_details[$v_drug['drugplan_id']]['overall_dosage_pump'] = $v_drug['overall_dosage_pump'];
                $extra_details[$v_drug['drugplan_id']]['drug_volume'] = $v_drug['drug_volume'];
                $extra_details[$v_drug['drugplan_id']]['unit2ml'] = $v_drug['unit2ml'];
                $extra_details[$v_drug['drugplan_id']]['concentration_per_drug'] = $v_drug['concentration_per_drug'];
                $extra_details[$v_drug['drugplan_id']]['bolus_per_med'] = $v_drug['bolus_per_med'];
                //--
                
 
            }
            
            return $extra_details;
        }
    }

    /**
     * @Ancuta
     * Copy of fn get_patient_drugplan_extra 
     * 04.09.2019
     * @param unknown $ipids
     * @param unknown $clientid
     * @return void|unknown
     */
    public function get_patients_drugplan_extra($ipids, $clientid )
    {
        if(empty($ipids)){
            return;
        }
        if(!is_array($ipids)){
            $ipids = array($ipids);
        }
        
        $drugs = Doctrine_Query::create()->select('*')
            ->from('PatientDrugPlanExtra')
            ->whereIn("ipid", $ipids)
            ->andWhere("isdelete = '0'");
        $drugsarray = $drugs->fetchArray();
        
        //@claudiu: return was added here so you don't make extra 4 queries
        if (empty($drugsarray)) {
            return;
        }
        
        // get details for unit
        $units_array = MedicationUnit::client_medication_unit($clientid);
        foreach ($units_array as $ku => $unit_value) {
            $unit[$unit_value['id']] = $unit_value['unit'];
        }

        // for ISPC-2247 changes were made to this page (Ancuta 06.11.2018) 
        // get details for dosage_form
        $dosage_form_array = MedicationDosageform::client_medication_dosage_form($clientid, true);// added second param true to includ extra for custom options from sets
        foreach ($dosage_form_array as $ku => $ds_value) {
            $dosage_form[$ds_value['id']] = $ds_value['dosage_form'];
        }
        
        // get details for type
        $types_array = MedicationType::client_medication_types($clientid,true);// added second param true to includ extra for custom options from sets
        foreach ($types_array as $kt => $type_value) {
            $type[$type_value['id']] = $type_value['type'];
        }
        
        // get details for indication
        $indications_array = MedicationIndications::client_medication_indications($clientid);

        foreach ($indications_array as $ki => $ind_value) {
            $indication[$ind_value['id']]['name'] = $ind_value['indication'];
            $indication[$ind_value['id']]['color'] = $ind_value['indication_color'];
        }
        
        // get details for packaging:: ISPC-2176 p6
        $packaging_array = PatientDrugPlanExtra::intubated_packaging();
        
        // get details for escalation ISPC-2247
        $medication_escalation = PatientDrugPlanExtra::getMedicationEscalation();
        
        
        
        if ($drugsarray) {
            foreach ($drugsarray as $k_drug => $v_drug) {
                
                $extra_details[$v_drug['drugplan_id']]['drug'] = $v_drug['drug'];
                $extra_details[$v_drug['drugplan_id']]['unit'] = $unit[$v_drug['unit']];
                $extra_details[$v_drug['drugplan_id']]['unit_id'] = $v_drug['unit'];
                
                $extra_details[$v_drug['drugplan_id']]['dosage_24h_manual'] = $v_drug['dosage_24h_manual'];  //ISPC-2684 Lore 12.10.2020
                $extra_details[$v_drug['drugplan_id']]['unit_dosage'] = $v_drug['unit_dosage'];             //ISPC-2684 Lore 08.10.2020
                $extra_details[$v_drug['drugplan_id']]['unit_dosage_24h'] = $v_drug['unit_dosage_24h'];     //ISPC-2684 Lore 08.10.2020
                
                $extra_details[$v_drug['drugplan_id']]['type'] = $type[$v_drug['type']];
                $extra_details[$v_drug['drugplan_id']]['type_id'] = $v_drug['type'];
                $extra_details[$v_drug['drugplan_id']]['indication'] = $indication[$v_drug['indication']];
                $extra_details[$v_drug['drugplan_id']]['indication_id'] = $v_drug['indication'];
                $extra_details[$v_drug['drugplan_id']]['importance'] = $v_drug['importance'];
                
                $extra_details[$v_drug['drugplan_id']]['dosage_form'] = $dosage_form[$v_drug['dosage_form']];
                $extra_details[$v_drug['drugplan_id']]['dosage_form_id'] = $v_drug['dosage_form'];
                $extra_details[$v_drug['drugplan_id']]['concentration'] = $v_drug['concentration'];
                
                $extra_details[$v_drug['drugplan_id']]['packaging'] = $v_drug['packaging'];
                $extra_details[$v_drug['drugplan_id']]['packaging_name'] = $packaging_array[$v_drug['packaging']];
                $extra_details[$v_drug['drugplan_id']]['kcal'] = $v_drug['kcal'];
                $extra_details[$v_drug['drugplan_id']]['volume'] = $v_drug['volume'];
                
                $extra_details[$v_drug['drugplan_id']]['escalation'] = $medication_escalation[$v_drug['escalation']];
                $extra_details[$v_drug['drugplan_id']]['escalation_id'] = $v_drug['escalation'];
                
            }
            
            return $extra_details;
        }
    }

    
    public function get_patient_all_drugplan_extra($ipid, $drugplan_id = false)
    {
        $drugs = Doctrine_Query::create()->select('*')
            ->from('PatientDrugPlanExtra')
            ->where("ipid = '" . $ipid . "'")
            ->andWhere("isdelete = '0'");
        if($drugplan_id){
            $drugs ->andWhere("drugplan_id = '".$drugplan_id."'");
        }

        $drugsarray = $drugs->fetchArray();
        
        
        if ($drugsarray) {
            foreach ($drugsarray as $k_drug => $v_drug) {
            
                $extra_details[$v_drug['drugplan_id']] = $v_drug;
            
            }
            
            return $extra_details;
        }
    }
    
    

    public function intubated_packaging(){
    	$Tr = new Zend_View_Helper_Translate();

   		$intubated_medication_lang = $Tr->translate('intubated_medication_lang');
    	
    	$pack_array = array(
    			"0" => $intubated_medication_lang['empty_select'],
    			"1" => $intubated_medication_lang['one_chamber_bag'],
    			"2" => $intubated_medication_lang['two_chamber_bag'],
    			"3" => $intubated_medication_lang['three_chamber_bag'],
    			"7" => $intubated_medication_lang['seven_chamber_bag'],
    			"8" => $intubated_medication_lang['eight_chamber_bag'],
    			"9" => $intubated_medication_lang['nine_chamber_bag']
    	);
    		
    		
    	return $pack_array;
    		
    }
    
    /**
     * ISPC-2247
     * 02.11.2018 @Ancuta
     *
     * @return array
     */
    public static function getMedicationEscalation()
    {
        $Tr = new Zend_View_Helper_Translate();
         
         
        $escalation = array(
            "1"=>$Tr->translate('escalation_1'),
            "2"=>$Tr->translate('escalation_2'),
            "3"=>$Tr->translate('escalation_3')
        );
         
        return $escalation;
    }
    
}

?>