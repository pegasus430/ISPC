<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanAlt', 'MDAT');

	class PatientDrugPlanAlt extends BasePatientDrugPlanAlt {

// 		public $triggerformid = 12;
// 		public $triggerformname = "frmpatientdrugplan";

		//Changes for ISPC-1848 F
	    public function get_patient_drugplan_alt($ipid, $type_condition=false,$special_case = false,$all = false){
	        
	        $drugs_alt = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanAlt')
	        ->where("ipid = '" . $ipid . "'")
	        ->andWhere("isdelete = 0")
	        ->andWhere('approved = 0')
	        ->andWhere('declined = "0" ');
	        $drugs_alt->andWhere("inactive = 0");
	        
	        if(!$all){
	            if($type_condition){
        	        $drugs_alt->andWhere($type_condition);
    	        }
    	        else
    	        {
        	        $drugs_alt->andWhere("isbedarfs = 0");
        	        $drugs_alt->andWhere("iscrisis = 0");
        	        $drugs_alt->andWhere("isivmed = 0");
        	        $drugs_alt->andWhere("isschmerzpumpe = 0");
        	        $drugs_alt->andWhere("isnutrition = 0");
        	        $drugs_alt->andWhere("treatment_care = 0");
    	        }
	        }
	        $drugs_alt->orderBy("id ASC");
	        $alt_meds  = $drugs_alt->fetchArray();
	        
	        $md_master_ids = array();
	        foreach($alt_meds as $k=>$alt_data){
	            $md_master_ids[]  = $alt_data['medication_master_id'];
	        }
	        
// 	        if(empty($md_master_ids)){
// 	            $md_master_ids[] = "99999999999999";
// 	        }
	        
	        $medarr1 = array();
	        if($special_case == "isnutrition" && !empty($md_master_ids))
	        {
	           $medarr1 = Nutrition::getMedicationNutritionById($md_master_ids);
	        } 
	        else if($special_case =="treatment_care" && !empty($md_master_ids))
	        {
	           $medarr1 = MedicationTreatmentCare::getMedicationTreatmentCareById($md_master_ids);
	        }
	        else if(!empty($md_master_ids))
	        {
	           $medarr1 = Medication::getMedicationById($md_master_ids);
	        }
	        
	        $med_arr1 = array();
	        foreach($medarr1 as $k_medarr1 => $v_medarr1)
	        {
	            $med_arr1[$v_medarr1['id']] = $v_medarr1;
	        }
	        $add_key= "0";
	        foreach($alt_meds as $k=>$alt_data){
	            
	            if($alt_data['approved'] == '0' ){
	                $non_approved['change'][$alt_data['drugplan_id']] = $alt_data;
	                $non_approved['change'][$alt_data['drugplan_id']]['alt_id'] = $alt_data['id'];
	                
	                $non_approved['change'][$alt_data['drugplan_id']]['name'] =  htmlentities($med_arr1[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8");
	        
	                if($alt_data['medication_change'] != "0000-00-00 00:00:00"){
	                    $non_approved['change'][$alt_data['drugplan_id']]['change_values']  = htmlentities($med_arr1[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8") . ' | ' . $alt_data['dosage'] . ' | ' . $alt_data['comments'].' | '.date("d.m.Y",strtotime($alt_data['medication_change']));
	                    $non_approved['change'][$alt_data['drugplan_id']]['medication_change_date'] =  date("d.m.Y",strtotime($alt_data['medication_change']));
	                } else{
	                    $non_approved['change'][$alt_data['drugplan_id']]['change_values']  = htmlentities($med_arr1[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8") . ' | ' . $alt_data['dosage'] . ' | ' . $alt_data['comments'];
	                    $non_approved['change'][$alt_data['drugplan_id']]['medication_change_date'] =  "";
	                }
	                if($alt_data['delete_date']){
	                    $non_approved['change'][$alt_data['drugplan_id']]['delete_date'] =  date("d.m.Y",strtotime($alt_data['delete_date']));
	                } else {
	                    $non_approved['change'][$alt_data['drugplan_id']]['delete_date'] =  $non_approved['change'][$alt_data['drugplan_id']]['medication_change_date'];
	                    
	                }
	                
	                
	                
	            }
	        }
	        return $non_approved;
	    }
	    
	    public static function get_patient_drugplan_alt_all($ipid){

	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	        
	        $drugs_alt = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanAlt')
	        ->where("ipid = ?", $ipid )
	        ->andWhere("isdelete = 0")
	        ->andWhere('approved = 0')
	        ->andWhere('declined = "0" ')
	        ->andWhere("inactive = 0")
	        ->orderBy("id ASC");
	        $alt_meds  = $drugs_alt->fetchArray();
	        
	        
	        $md_master_ids = array();
	        $alt_ids = array();
	        
	        foreach($alt_meds as $k=>$alt_data){
	            $md_master_ids[]  = $alt_data['medication_master_id'];
	            $alt_ids[] = $alt_data['id'];
	        }
	        
	        //flowerpower
// 	        if(empty($md_master_ids)){
// 	            $md_master_ids[] = "99999999999999";
// 	        }
	         
	        $medarr_nutrittion = array();
	        if ( ! empty($md_master_ids)) {
	            //this is not good, you fetch from all categories, not just from isnutrition (you make a unnecessary query)
                $medarr_nutrittion = Nutrition::getMedicationNutritionById($md_master_ids);
	        }
            	         
	        
            if(!empty($medarr_nutrittion))
            {
    	        foreach($medarr_nutrittion as $k_medarrn => $v_medarrn)
    	        {
    	            $med_nutrition[$v_medarrn['id']] = $v_medarrn;
    	        }
                
            }
            if ( ! empty($md_master_ids)) {
                //again like isnutrition, you should search only treatment_care (you make a unnecessary query)
                $medarr_treatment = MedicationTreatmentCare::getMedicationTreatmentCareById($md_master_ids);
            }
            $med_treatment =  array();
            if(!empty($medarr_treatment))
            {
    	        foreach($medarr_treatment as $k_medarrt => $v_medarrt)
    	        {
    	            $med_treatment[$v_medarrt['id']] = $v_medarrt;
    	        }
                
            }
            
            
            
            $medarr_master = Medication::getMedicationById($md_master_ids);
            
            $med_medication = array();
            if(!empty($medarr_master))
            {
    	        foreach($medarr_master as $k_medarrm => $v_medarrm)
    	        {
    	            $med_medication[$v_medarrm['id']] = $v_medarrm;
    	        }
            }
            
            // get non approved dosage
            
            
            $alt_dosage = PatientDrugPlanDosageAlt::get_patient_drugplan_dosage_alt_all($ipid,$alt_ids);
            
            
            // get non approved extra data
            $alt_extra = PatientDrugPlanExtraAlt::get_patient_drugplan_extra_alt_all($ipid,$alt_ids);
//             print_r($alt_extra); exit;
            
            
            $usr = new User();
            $user_details = $usr->getUserByClientid($clientid, '1', true);
           
            $non_approved = array();
	        $add_key= "0";
	        foreach($alt_meds as $k=>$alt_data){
	            
// 	            if ($alt_data['change_source'] == 'offline' ){
// 	                continue; //  //ispc-1999 offline are below... because we accept multiple changes not just one
// 	            }
	            if($alt_data['approved'] == '0' ){
	                
	                $extra_key = $alt_data['id'];
	                
	                $non_approved['change'][$alt_data['drugplan_id']][$extra_key] = $alt_data;
	                $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['alt_id'] = $alt_data['id'];
	                $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['created_by'] = $user_details[$alt_data['create_user']];
	                $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['prescribed_by'] = $user_details[$alt_data['verordnetvon']];
	                
	                if($alt_data['isnutrition'] == "1")
	                {
	                   $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['name'] =  htmlentities($med_nutrition[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8");
	                }
	                elseif($alt_data['treatment_care'] == "1")
	                { 
	                   $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['name'] =  htmlentities($med_treatment[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8");
	                } 
	                else
	                {
	                   $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['name'] =  htmlentities($med_medication[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8");
	                }
	        
	                
	                if(!empty($alt_dosage[$alt_data['id']][$alt_data['drugplan_id']])){
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage']  = $alt_dosage[$alt_data['id']][$alt_data['drugplan_id']];

	                    if(strlen($alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['concentration'])> 0  && $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['concentration'] != 0 ){
	                        foreach($alt_dosage[$alt_data['id']][$alt_data['drugplan_id']] as $dtime =>$dvalue)
	                        {
// 	                            $non_approved['change'][$alt_data['drugplan_id']]['dosage_concentration'][$dtime] =round($dvalue / $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['concentration'], 2)."".$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['dosage_form'];
	                            if(!empty($dvalue) && strlen($dvalue)> 0 )
	                            {
	                                $dosage_value = str_replace(',','.',$dvalue);
	                                $concentration_value = str_replace(',','.',$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['concentration']);
	                                 
	                                $result = $dosage_value / $concentration_value;
	                                if(!is_int ($result ))
	                                {
	                                    $result = round($result,4);
	                                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage_concentration'][$dtime] = number_format(  $result ,3,",",".")."".$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['dosage_form'];
	                                }
	                                else
	                                {
	                                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage_concentration'][$dtime] = $result."".$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['dosage_form'];;
	                                }
	                            }
	                            else
	                            {
	                                $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage_concentration'][$dtime] = "";
	                            }
	                            
	                        }
	                    }
	                    
	                } 
	                else
	                {
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage']  = $alt_data['dosage'];

	                    if(!empty( $alt_data['dosage']) && strlen( $alt_data['dosage'])> 0 )
	                    {
	                        $dosage_value = str_replace(',','.', $alt_data['dosage']);
	                        $concentration_value = str_replace(',','.',$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['concentration']);
	                    
	                        $result = $dosage_value / $concentration_value;
	                        if(!is_int ($result ))
	                        {
	                            $result = round($result,4);
	                            $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage_concentration'] = number_format(  $result ,3,",",".")."".$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['dosage_form'];
	                        }
	                        else
	                        {
	                            $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage_concentration'] = $result."".$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['dosage_form'];;
	                        }
	                    }
	                    else
	                    {
	                        $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage_concentration'] = "";
	                    }
	                    
	                    
	                    if($alt_data['isschmerzpumpe'] == "1"){

	                        if(!empty( $alt_data['dosage']) && strlen( $alt_data['dosage'])> 0 )
	                        {
	                            $dosage_value = str_replace(',','.', $alt_data['dosage']);
	                            $result_24h = $dosage_value * 24;
	                            if(!is_int ($result_24h ))
	                            {
	                                $result_24h = round($result_24h,4);
	                                $alt_data['dosage_24h'] = number_format(  $result_24h ,3,",",".");
	                             } 
	                             else
	                             {
	                                $alt_data['dosage_24h'] = $result_24h;
	                             }	                         
	                             
	                             
	                             $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage_24h'] = $alt_data['dosage_24h'];
	                        }
	                        
	                        if(!empty( $alt_data['dosage_24h']) && strlen( $alt_data['dosage_24h'])> 0 )
	                        {
	                            $concentration_value = "";
	                            $result = "";
	                            $dosage_24h_value= "";
	                            $dosage_24h_value = str_replace(',','.', $alt_data['dosage_24h']);
	                            $concentration_value = str_replace(',','.',$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['concentration']);
	                             
	                            $result = $dosage_24h_value / $concentration_value;
	                            if(!is_int ($result ))
	                            {
	                                $result = round($result,4);
	                                $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage_24h_concentration'] = number_format(  $result ,3,",",".")."".$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['dosage_form'];
	                            }
	                            else
	                            {
	                                $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage_24h_concentration'] = $result."".$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['dosage_form'];;
	                            }
	                        }
	                        else
	                        {
	                            $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage_24h_concentration'] = "";
	                        }
	                        
	                        
	                    }
	                }
	                
	                if($alt_data['medication_change'] != "0000-00-00 00:00:00"){
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['change_values']  = $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['name'] . ' | ' . $alt_data['dosage'] . ' | ' . $alt_data['comments'].' | '.date("d.m.Y",strtotime($alt_data['medication_change']));
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['medication_change_date'] =  date("d.m.Y",strtotime($alt_data['medication_change']));
	                } else{
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['change_values']  = $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['name'] . ' | ' . $alt_data['dosage'] . ' | ' . $alt_data['comments'];
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['medication_change_date'] =  "";
	                }

	                
	                if($alt_data['administration_date'] != "0000-00-00 00:00:00"){
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['administration_date'] =  date("d.m.Y",strtotime($alt_data['administration_date']));
	                } else{
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['administration_date'] =  "";
	                }
	                
	                if(!empty($alt_extra[$alt_data['id']][$alt_data['drugplan_id']])){
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['drug'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['drug'];
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['unit'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['unit'];
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['type'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['type'];
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['indication'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['indication'];
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['indication_color'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['indication_color'];
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['dosage_form'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['dosage_form'];
	                    
	                    if($alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['dosage_form'] && $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['unit']){
	                       $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['concentration'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['concentration'].' '.$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['unit'].'/'.$alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['dosage_form'];
	                    }
	                    else
	                    {
    	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['concentration'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['concentration'];
	                    }
	                    // ISPC-2176
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['packaging'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['packaging'];
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['packaging_name'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['packaging_name'];
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['kcal'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['kcal'];
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['volume'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['volume'];
	                    // ISPC-2247 
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['escalation_id'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['escalation_id'];
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['escalation'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['escalation_label'];
	                    
	                    $non_approved['change'][$alt_data['drugplan_id']][$extra_key]['importance'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['importance'];
	                    
	                }
	            }
	        }
	        

	        
	        //put the changed offline  as separate key $non_approved[change_offline], and leave just one [change] (not multiple like the offline)
	        //this foreach would not be needed if you allow multiple changes for online also
	        foreach ($non_approved['change'] as $k_drugplan => &$drugplan_changes) {
	            
	            $online_single_change =  null;
	            foreach ($drugplan_changes as $k_changeid => $changes) {
	                
	                if ($changes['change_source'] == 'offline') {
	                    $non_approved['change_offline'][$k_drugplan][$k_changeid] = $changes;	                    
	                } else {
	                    $online_single_change = $changes;
	                }
	            }	            
	            $drugplan_changes = $online_single_change;
	            
	            if (isset($non_approved['change_offline'][$k_drugplan])) {
	                //order by date
	                usort($non_approved['change_offline'][$k_drugplan], array(new Pms_Sorter('medication_change', $search_str), "_date_compare"));
	                 
	            }
	        }
	        return $non_approved;
	    }
	    
	    public function get_patient_drugplan_byipid($ipid,$drugplan_id){

	        $drugs_alt = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanAlt')
	        ->where("ipid = '" . $ipid . "'")
	        ->andwhere("drugplan_id= '" . $drugplan_id . "'")
	        ->andWhere("isdelete = 0")
	        ->andWhere('approved = 0')
	        ->andWhere('declined = "0" ');
	        $drugs_alt->andWhere("inactive = 0");
	        $drugs_alt->orderBy("id ASC");
	        $alt_meds  = $drugs_alt->fetchArray();
	        
	        foreach($alt_meds as $k=>$alt_data){
	            $md_master_ids[]  = $alt_data['medication_master_id'];
	            $alt_ids[] = $alt_data['id'];
	        }
	        
	        
	        if(empty($md_master_ids)){
	            $md_master_ids[] = "999999999";
	        }
	        
            $medarr_nutrittion = Nutrition::getMedicationNutritionById($md_master_ids);
            
            if(!empty($medarr_nutrittion))
            {
    	        foreach($medarr_nutrittion as $k_medarrn => $v_medarrn)
    	        {
    	            $med_nutrition[$v_medarrn['id']] = $v_medarrn;
    	        }
                
            }
            
            $medarr_treatment = MedicationTreatmentCare::getMedicationTreatmentCareById($md_master_ids);
            if(!empty($medarr_treatment))
            {
    	        foreach($medarr_treatment as $k_medarrt => $v_medarrt)
    	        {
    	            $med_treatment[$v_medarrt['id']] = $v_medarrt;
    	        }
                
            }
            
            $medarr_master = Medication::getMedicationById($md_master_ids);
            if(!empty($medarr_master))
            {
    	        foreach($medarr_master as $k_medarrm => $v_medarrm)
    	        {
    	            $med_medication[$v_medarrm['id']] = $v_medarrm;
    	        }
            }
	        
            // get non approved dosage
            
            
            $alt_dosage = PatientDrugPlanDosageAlt::get_patient_drugplan_dosage_alt_all($ipid,$alt_ids);
            
            // get non approved extra data
            $alt_extra = PatientDrugPlanExtraAlt::get_patient_drugplan_extra_alt_all($ipid,$alt_ids);
//             print_r($alt_extra); exit;
            
            
            
            
	        $add_key= "0";
	        foreach($alt_meds as $k=>$alt_data){
	            
	            if($alt_data['approved'] == '0' ){
	                $non_approved = $alt_data;
	                $non_approved['alt_id'] = $alt_data['id'];
	                
	                if($alt_data['isnutrition'] == "1")
	                {
	                   $non_approved['name'] =  htmlentities($med_nutrition[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8");
	                }
	                elseif($alt_data['treatment_care'] == "1")
	                { 
	                   $non_approved['name'] =  htmlentities($med_treatment[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8");
	                } 
	                else
	                {
	                   $non_approved['name'] =  htmlentities($med_medication[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8");
	                }
	        
	                
	                if(!empty($alt_dosage[$alt_data['id']][$alt_data['drugplan_id']])){
	                    $non_approved['dosage']  = $alt_dosage[$alt_data['id']][$alt_data['drugplan_id']];  
	                } 
	                else
	                {
	                    $non_approved['dosage']  = $alt_data['dosage'];  
	                }

	                
	                if($alt_data['medication_change'] != "0000-00-00 00:00:00"){
	                    $non_approved['change_values']  = $non_approved['name'] . ' | ' . $alt_data['dosage'] . ' | ' . $alt_data['comments'].' | '.date("d.m.Y",strtotime($alt_data['medication_change']));
	                    $non_approved['medication_change_date'] =  date("d.m.Y",strtotime($alt_data['medication_change']));
	                } else{
	                    $non_approved['change_values']  = $non_approved['name'] . ' | ' . $alt_data['dosage'] . ' | ' . $alt_data['comments'];
	                    $non_approved['medication_change_date'] =  "";
	                }
	                
	                if(!empty($alt_extra[$alt_data['id']][$alt_data['drugplan_id']])){
	                    $non_approved['drug'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['drug'];
	                    $non_approved['unit'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['unit'];
	                    $non_approved['type'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['type'];
	                    $non_approved['indication'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['indication'];
	                    $non_approved['indication_color'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['indication_color'];
	                    $non_approved['dosage_form'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['dosage_form'];
	                    $non_approved['concentration'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['concentration'];
	                    
	                    $non_approved['packaging'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['packaging'];
	                    $non_approved['packaging_name'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['packaging_name'];
	                    $non_approved['kcal'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['kcal'];
	                    $non_approved['volume'] =  $alt_extra[$alt_data['id']][$alt_data['drugplan_id']]['volume'];
	                }
	            }
	        }
	        
	        return $non_approved;
	    }
	    
	    public function get_patients_drugplan_alt($ipids, $type_condition=false,$special_case = false,$all = false)
	    {

	        if(!is_array($ipids))
	        {
	            $ipids = array($ipids);
	        }
	        else
	        {
	            $ipids = $ipids;
	        }
	        
	        $drugs_alt = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanAlt')
	        ->whereIn("ipid",$ipids)
	        ->andWhere("isdelete = 0")
	        ->andWhere('approved = 0')
	        ->andWhere('declined = "0" ');
	        $drugs_alt->andWhere("inactive = 0");
	        
	        if(!$all){
	            if($type_condition){
        	        $drugs_alt->andWhere($type_condition);
    	        }
    	        else
    	        {
        	        $drugs_alt->andWhere("isbedarfs = 0");
        	        $drugs_alt->andWhere("iscrisis = 0");
        	        $drugs_alt->andWhere("isivmed = 0");
        	        $drugs_alt->andWhere("isschmerzpumpe = 0");
        	        $drugs_alt->andWhere("isnutrition = 0");
        	        $drugs_alt->andWhere("treatment_care = 0");
        	        $drugs_alt->andWhere("scheduled = 0");
    	        }
	        }
	        $drugs_alt->orderBy("id ASC");
	        $alt_meds  = $drugs_alt->fetchArray();
	        
	        $md_master_ids = array();
	        foreach($alt_meds as $k=>$alt_data){
	            $md_master_ids[]  = $alt_data['medication_master_id'];
	        }
	        
// 	        if(empty($md_master_ids)){
// 	            $md_master_ids[] = "99999999999999";
// 	        }
	        $medarr1 = array();	        
	        if($special_case == "isnutrition" && !empty($md_master_ids))
	        {
	           $medarr1 = Nutrition::getMedicationNutritionById($md_master_ids);
	        } 
	        else if($special_case =="treatment_care" && !empty($md_master_ids))
	        {
	           $medarr1 = MedicationTreatmentCare::getMedicationTreatmentCareById($md_master_ids);
	        }
	        elseif(!empty($md_master_ids))
	        {
	           $medarr1 = Medication::getMedicationById($md_master_ids);
	        }
	        $med_arr1 = array();
	        foreach($medarr1 as $k_medarr1 => $v_medarr1)
	        {
	            $med_arr1[$v_medarr1['id']] = $v_medarr1;
	        }
	        $add_key= "0";
	        foreach($alt_meds as $k=>$alt_data){
	            
	            if($alt_data['approved'] == '0' ){
	                $non_approved[$alt_data['ipid']]['change'][$alt_data['drugplan_id']] = $alt_data;
	                $non_approved[$alt_data['ipid']]['change'][$alt_data['drugplan_id']]['alt_id'] = $alt_data['id'];
	                
	                $non_approved[$alt_data['ipid']]['change'][$alt_data['drugplan_id']]['name'] =  htmlentities($med_arr1[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8");
	        
	                if($alt_data['medication_change'] != "0000-00-00 00:00:00"){
	                    $non_approved[$alt_data['ipid']]['change'][$alt_data['drugplan_id']]['change_values']  = htmlentities($med_arr1[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8") . ' | ' . $alt_data['dosage'] . ' | ' . $alt_data['comments'].' | '.date("d.m.Y",strtotime($alt_data['medication_change']));
	                    $non_approved[$alt_data['ipid']]['change'][$alt_data['drugplan_id']]['medication_change_date'] =  date("d.m.Y",strtotime($alt_data['medication_change']));
	                } else{
	                    $non_approved[$alt_data['ipid']]['change'][$alt_data['drugplan_id']]['change_values']  = htmlentities($med_arr1[$alt_data['medication_master_id']]['name'], ENT_QUOTES, "UTF-8") . ' | ' . $alt_data['dosage'] . ' | ' . $alt_data['comments'];
	                    $non_approved[$alt_data['ipid']]['change'][$alt_data['drugplan_id']]['medication_change_date'] =  "";
	                }
	            }
	        }
	        return $non_approved;
	    }
	    
	    public function get_declined_patient_drugplan_alt($ipid, $type_condition=false,$special_case = false,$all = false){
	        
	        $drugs_alt = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanAlt')
	        ->where("ipid = '" . $ipid . "'")
	        ->andWhere("isdelete = 0")
	        ->andWhere('declined = 1');
	        $drugs_alt->andWhere("inactive = 0");
	        
	        if(!$all){
    	        if($type_condition){
        	        $drugs_alt->andWhere($type_condition);
    	        }
    	        else
    	        {
        	        $drugs_alt->andWhere("isbedarfs = 0");
        	        $drugs_alt->andWhere("iscrisis = 0");
        	        $drugs_alt->andWhere("isivmed = 0");
        	        $drugs_alt->andWhere("isschmerzpumpe = 0");
        	        $drugs_alt->andWhere("isnutrition = 0");
        	        $drugs_alt->andWhere("treatment_care = 0");
    	        }
	        }
	        $drugs_alt->orderBy("id ASC");
	        $alt_meds  = $drugs_alt->fetchArray();
	        
// 	        foreach($alt_meds as $k=>$alt_data){
// 	            $md_master_ids[]  = $alt_data['medication_master_id'];
// 	        }
	        
// 	        if(empty($md_master_ids)){
// 	            $md_master_ids[] = "99999999999999";
// 	        }
	        
// 	        if($special_case == "isnutrition")
// 	        {
// 	           $medarr1 = Nutrition::getMedicationNutritionById($md_master_ids);
// 	        } 
// 	        else if($special_case =="treatment_care")
// 	        {
// 	           $medarr1 = MedicationTreatmentCare::getMedicationTreatmentCareById($md_master_ids);
// 	        }
// 	        else
// 	        {
// 	           $medarr1 = Medication::getMedicationById($md_master_ids);
// 	        }
	        
// 	        foreach($medarr1 as $k_medarr1 => $v_medarr1)
// 	        {
// 	            $med_arr1[$v_medarr1['id']] = $v_medarr1;
// 	        }
// 	        $add_key= "0";
	        
	        $declined_drugplans_ids = array();
	        foreach($alt_meds as $k=>$alt_data){
	           $declined_drugplans_ids[] = $alt_data['drugplan_id'];
	        }
	        
	        return $declined_drugplans_ids;
	        
	    }
	    
	    
	    public function get_declined_patient_drugplan_alt_all($ipid){
	        
	        $drugs_alt = Doctrine_Query::create()
// 	        ->select('*')
	        ->select('id, drugplan_id')
	        ->from('PatientDrugPlanAlt')
// 	        ->where("ipid = '" . $ipid . "'")
	        ->where("ipid = ?", $ipid)
	        ->andWhere("isdelete = 0")
	        ->andWhere('declined = 1');
	        $drugs_alt->andWhere("inactive = 0");
	        $drugs_alt->orderBy("id ASC");
	        $alt_meds  = $drugs_alt->fetchArray();
	        
	        $declined_drugplans_ids = array();
	        foreach($alt_meds as $k=>$alt_data){
	           $declined_drugplans_ids[] = $alt_data['drugplan_id'];
	        }
	        return $declined_drugplans_ids;
	        
	    }
	    
	    
	    public function get_declined_patients_drugplan_alt($ipids, $type_condition=false,$special_case = false,$all = false)
	    {

	        if(!is_array($ipids))
	        {
	            $ipids = array($ipids);
	        }
	        else
	        {
	            $ipids = $ipids;
	        }
	     
	        
	        $drugs_alt = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanAlt')
	        ->whereIn("ipid",$ipids)
	        ->andWhere("isdelete = 0")
	        ->andWhere('declined = 1');
	        $drugs_alt->andWhere("inactive = 0");
	        
	        if(!$all){
    	        if($type_condition){
        	        $drugs_alt->andWhere($type_condition);
    	        }
    	        else
    	        {
        	        $drugs_alt->andWhere("isbedarfs = 0");
        	        $drugs_alt->andWhere("iscrisis = 0");
        	        $drugs_alt->andWhere("isivmed = 0");
        	        $drugs_alt->andWhere("isschmerzpumpe = 0");
        	        $drugs_alt->andWhere("isnutrition = 0");
        	        $drugs_alt->andWhere("treatment_care = 0");
    	        }
	        }
	        $drugs_alt->orderBy("id ASC");
	        $alt_meds  = $drugs_alt->fetchArray();
	        
// 	        $md_master_ids = array();
// 	        foreach($alt_meds as $k=>$alt_data){
// 	            $md_master_ids[]  = $alt_data['medication_master_id'];
// 	        }
	        
// 	        if(empty($md_master_ids)){
// 	            $md_master_ids[] = "99999999999999";
// 	        }
	        
// 	        if($special_case == "isnutrition")
// 	        {
// 	           $medarr1 = Nutrition::getMedicationNutritionById($md_master_ids);
// 	        } 
// 	        else if($special_case =="treatment_care")
// 	        {
// 	           $medarr1 = MedicationTreatmentCare::getMedicationTreatmentCareById($md_master_ids);
// 	        }
// 	        else
// 	        {
// 	           $medarr1 = Medication::getMedicationById($md_master_ids);
// 	        }
	        
// 	        foreach($medarr1 as $k_medarr1 => $v_medarr1)
// 	        {
// 	            $med_arr1[$v_medarr1['id']] = $v_medarr1;
// 	        }
// 	        $add_key= "0";
	        
	        $declined_drugplans_ids = array();
	        foreach($alt_meds as $k=>$alt_data){
	           $declined_drugplans_ids[$alt_data['ipid']][] = $alt_data['drugplan_id'];
	        }
	        
	        return $declined_drugplans_ids;
	    }
	    
	    
	    
	    
	    public function get_drugplan_alt($ipid,$drugplan_id = false,$alt_id = false,$full_details = false){
	         
	        $drugs_alt = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlanAlt')
	        ->where("ipid = '" . $ipid . "'")
	        ->andWhere("isdelete = 0");
	        $drugs_alt->andWhere("inactive = 0");
	        if($alt_id)
	        {
	            $drugs_alt->andWhere('id = "'.$alt_id.'" ');
	        }
	        if($drugplan_id)
	        {
	            $drugs_alt->andWhere('drugplan_id = "'.$drugplan_id.'" ');
	        }
	        $drugs_alt->orderBy("id ASC");
	        $alt_meds  = $drugs_alt->fetchArray();
	         
	        foreach($alt_meds as $k=>$alt_data)
	        {
	            
	            if($full_details)
	            {
    	            $drugplan_alt_data[$alt_data['id']]  = $alt_data;
	            } 
	            else
	            {
    	            $drugplan_alt_data[]  = $alt_data['id'];
	            }
	        }
	        
	        if($drugplan_alt_data)
	        {
    	        return $drugplan_alt_data;
	        }
	        else
	        {
    	        return false;
	        }
	         
	    }
	    
	    
		public function getPatientDrugPlanAlt($pid, $load_master = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanAlt')
				->where("ipid = '" . $ipid . "'")
				->andWhere("isdelete = '0'")
				->orderBy("id ASC");
			$drugsarray = $drugs->fetchArray();

			if($drugsarray)
			{
				if($load_master)
				{
					//extract the master_medications_array
					$medi_master_ids[] = '99999999';
					foreach($drugsarray as $k_drug => $v_drug)
					{
						$medi_master_ids[] = $v_drug['medication_master_id'];
					}

					//sorting
					$medi_master_ids = array_values(array_unique($medi_master_ids));

					//get the data from master medications array
					$med = new Medication();
					$medarr = $med->master_medications_get($medi_master_ids, false);

					foreach($drugsarray as $k_drug_plan => $v_drug_plan)
					{

						$drugsarray[$k_drug_plan]['medication'] = $medarr[$v_drug_plan['medication_master_id']];
					}
				}

				return $drugsarray;
			}
		}

		public function getMedicationPlan($pid, $allowivmedis = false, $allowschmerzpumpe = false)
		{

			if($allowivmedis)
			{
				$q = "isivmed = 1";
			}
			else
			{
				$q = "isivmed = 0";
			}
			if($allowschmerzpumpe)
			{
				$s = "isschmerzpumpe = 1";
			}
			else
			{
				$s = "isschmerzpumpe = 0";
			}
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanAlt')
				->where("ipid = '" . $ipid . "'")
				->andWhere("isdelete = 0")
				->andWhere("isbedarfs = 0")
				->andWhere("iscrisis = 0")
				->andWhere("treatment_care = 0")
				->andWhere("isnutrition = 0")
				->andWhere($q)
				->andWhere($s)
				->orderBy("id ASC");

			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				return $drugsarray;
			}
		}

		public function getbedarfMedication($pid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$drugs = Doctrine_Query::create()
				->select("*,'another' as type")
				->from('PatientDrugPlanAlt')
				->where("ipid = '" . $ipid . "'")
				->andWhere("isdelete = 0")
				->andWhere("isbedarfs = 1")
				->orderBy("id ASC");
			$drugs->getSqlQuery();

			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				return $drugsarray;
			}
		}
		public function getCrisiMedication($pid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);
		
			$drugs = Doctrine_Query::create()
			->select("*,'another' as type")
			->from('PatientDrugPlanAlt')
			->where("ipid = '" . $ipid . "'")
			->andWhere("isdelete = 0")
			->andWhere("iscrisis = 1")
			->orderBy("id ASC");
			$drugs->getSqlQuery();
		
			$dr = $drugs->execute();
		
			if($dr)
			{
				$drugsarray = $dr->toArray();
				return $drugsarray;
			}
		}

		public function getDeletedMedication($pid,$include_treatment_care = false,$include_nutrition = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$drugs = Doctrine_Query::create();
			$drugs->select('*');
			$drugs->from('PatientDrugPlanAlt');
			$drugs->where("ipid = '" . $ipid . "'");
			$drugs->andWhere("isdelete = 1");
			if(!$include_treatment_care){
				$drugs->andWhere("treatment_care = 0");
			}
			if(!$include_nutrition){
				$drugs->andWhere("isnutrition = 0");
			}
			$drugs->orderBy("id ASC");
			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				return $drugsarray;
			}
		}

		public function getivMedication($pid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$drugs = Doctrine_Query::create()
				->select("*,'another' as type")
				->from('PatientDrugPlanAlt')
				->where("ipid = '" . $ipid . "'")
				->andWhere("isdelete = 0")
				->andWhere("isivmed = 1")
				->orderBy("id ASC");
			$drugs->getSqlQuery();

			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				return $drugsarray;
			}
		}

		public function getSchmerzpumpeMedication($pid, $cid = 0)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$drugs = Doctrine_Query::create()
				->select("*,'another' as type")
				->from('PatientDrugPlanAlt d')
				->where("d.ipid = '" . $ipid . "'")
				->andWhere("d.isdelete = 0")
				->andWhere("d.treatment_care = 0")
				->andWhere("d.isnutrition = 0")
				->andWhere("d.isivmed = 0")
				->andWhere("d.isschmerzpumpe = 1")
				->leftJoin('d.PatientDrugPlanAltCocktails c')
				->andwhere('d.cocktailid = c.id')
				->andwhere('c.isdelete = 0')
				->orderBy("c.id, d.id ASC");
			if($cid != 0)
			{
				$drugs->andWhere("cocktailid = '" . $cid . "'");
			}

			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();

				return $drugsarray;
			}
		}

		public function getSchmerzpumpeMedicationall($pid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$drugs = Doctrine_Query::create()
				->select('*,"another" as type, c.description  as comments, c.bolus as bolus, c.flussrate as flussrate , c.sperrzeit as sperrzeit  ')
				->from('PatientDrugPlanAlt s')
				->leftJoin('s.PatientDrugPlanAltCocktails c')
				->where("s.ipid = '" . $ipid . "'")
				->andWhere("s.cocktailid = c.id")
				->andWhere("s.isdelete = 0")
				->andWhere("s.isivmed = 0")
				->andWhere("s.treatment_care = 0")
				->andWhere("s.isnutrition = 0")
				->andWhere("s.isschmerzpumpe = 1")
				->orderBy("s.cocktailid, s.id ASC");
			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $drugs->fetchArray();
				return $drugsarray;
			}
		}

		public function getPatientDrugPlanAltNoIV($pid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanAlt')
				->where("ipid = '" . $ipid . "'")
				->andWhere("isdelete = '0' and isivmed ='0' and isschmerzpumpe = 0 and treatment_care = 0 and isnutrition = 0")
				->orderBy("id ASC");

			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				return $drugsarray;
			}
		}

		public function getMedicationPlanCourse($pid, $pmid = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			if($pmid)
			{
				$q = 'AND id="' . $pmid . '"';
			}
			else
			{
				$q = '';
			}

			//get patient medis
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanAlt')
				->where("ipid = '" . $ipid . "' " . $q . "")
				->andWhere("isdelete = 0")
				->andWhere('isschmerzpumpe ="0"')
				->andWhere('treatment_care ="0"')
				->andWhere('isnutrition ="0"')
				->orderBy("id ASC");

			$drug_plans = $drugs->fetchArray();

			//extract the master_medications_array
			$medi_master_ids[] = '99999999';
			foreach($drug_plans as $k_drug => $v_drug)
			{
				$medi_master_ids[] = $v_drug['medication_master_id'];
			}

			//sorting
			asort($medi_master_ids, SORT_NUMERIC);
			$medi_master_ids = array_values(array_unique($medi_master_ids));

			//get the data from master medications array
			$med = new Medication();
			$medarr = $med->master_medications_get($medi_master_ids, false);

			//map the master medications data to data from drugplan
			foreach($drug_plans as $k_drugplan => $v_drugplan)
			{
				$patient_drugs[$k_drugplan] = $v_drugplan;
				$patient_drugs[$k_drugplan]['medi_name'] = $medarr[$v_drugplan['medication_master_id']];

				if($v_drugplan['medication_change'] != "0000-00-00 00:00:00")
				{
					$patient_drugs[$k_drugplan]['medi_change'] = date('d.m.Y', strtotime($v_drugplan['medication_change']));
					$patient_drugs[$k_drugplan]['medi_replace'] = "none";
				}
				else if($v_drugplan['medication_change'] == "0000-00-00 00:00:00" && $v_drugplan['change_date'] != "0000-00-00 00:00:00")
				{
					$patient_drugs[$k_drugplan]['medi_change'] = date('d.m.Y', strtotime($v_drugplan['change_date']));
					$patient_drugs[$k_drugplan]['medi_replace'] = "change";
				}
				else
				{
					$patient_drugs[$k_drugplan]['medi_change'] = date('d.m.Y', strtotime($v_drugplan['create_date']));
					$patient_drugs[$k_drugplan]['medi_replace'] = "create";
				}
			}

			return $patient_drugs;
		}

		public function getPatientAllDrugs($ipid)
		{
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanAlt')
				->where("ipid = '" . $ipid . "'")
				->orderBy("id ASC");

			$drugsarray = $drugs->fetchArray();

			if($drugsarray)
			{
				return $drugsarray;
			}
		}

		public function clone_records($ipid, $target_ipid, $target_client)
		{
			$cocktails = new PatientDrugPlanAltCocktails();
			$save_cocktails = $cocktails->clone_record($ipid, $target_ipid, $target_client);

			$medis = $this->getPatientAllDrugs($ipid);

			if($medis)
			{
				$master_medi = new Medication();
				$cloned_patient_medis_smp = array();
				foreach($medis as $k_medi => $v_medi)
				{
					$clone_master_medi = $master_medi->clone_record($v_medi['medication_master_id'], $target_client);

					if($clone_master_medi)
					{
						$ins_pmedi = new PatientDrugPlanAlt();
						$ins_pmedi->ipid = $target_ipid;
						$ins_pmedi->medication_master_id = $clone_master_medi;
						$ins_pmedi->medication = $v_medi['medication'];
						$ins_pmedi->dosage = $v_medi['dosage'];
						$ins_pmedi->pattern1 = $v_medi['pattern1'];
						$ins_pmedi->pattern2 = $v_medi['pattern2'];
						$ins_pmedi->pattern3 = $v_medi['pattern3'];
						$ins_pmedi->amount = $v_medi['amount'];
						$ins_pmedi->dosage_unit = $v_medi['dosage_unit'];
						$ins_pmedi->dosage_method = $v_medi['dosage_method'];
						$ins_pmedi->active_date = $v_medi['active_date'];
						$ins_pmedi->inactive_date = $v_medi['inactive_date'];
						$ins_pmedi->comments = $v_medi['comments'];
						$ins_pmedi->isbedarfs = $v_medi['isbedarfs'];
						$ins_pmedi->iscrisis = $v_medi['iscrisis'];
						$ins_pmedi->isivmed = $v_medi['isivmed'];
						$ins_pmedi->isschmerzpumpe = $v_medi['isschmerzpumpe'];

						if($v_medi['cocktailid'])
						{
							$ins_pmedi->cocktailid = $save_cocktails[$v_medi['cocktailid']];
						}

						$ins_pmedi->verordnetvon = $v_medi['verordnetvon'];
						$ins_pmedi->edit_type = $v_medi['edit_type'];
						$ins_pmedi->medication_change = $v_medi['medication_change'];
						$ins_pmedi->isdelete = $v_medi['isdelete'];
						$ins_pmedi->save();
					}
				}
			}
		}

		public function get_patient_drugplan($ipid, $current_period)
		{
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanAlt')
				->where("ipid = '" . $ipid . "'")
				->andWhere('DATE(medication_change) BETWEEN DATE("' . $current_period['start'] . '") AND DATE("' . $current_period['end'] . '") OR DATE(change_date) BETWEEN DATE("' . $current_period['start'] . '") AND DATE("' . $current_period['end'] . '") OR DATE(create_date) BETWEEN DATE("' . $current_period['start'] . '") AND DATE("' . $current_period['end'] . '")')
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
		
		

		public function get_treatment_care($pid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);
		
			$drugs = Doctrine_Query::create()
			->select("*,'another' as type")
			->from('PatientDrugPlanAlt')
			->where("ipid = '" . $ipid . "'")
			->andWhere("isdelete = 0")
			->andWhere("treatment_care = 1")
			->orderBy("id ASC");
			$drugs->getSqlQuery();
		
			$dr = $drugs->execute();
		
			if($dr)
			{
				$drugsarray = $dr->toArray();
				return $drugsarray;
			}
		}
		
		public function get_isnutrition($pid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);
		
			$drugs = Doctrine_Query::create()
			->select("*,'another' as type")
			->from('PatientDrugPlanAlt')
			->where("ipid = '" . $ipid . "'")
			->andWhere("isdelete = 0")
			->andWhere("isnutrition = 1")
			->orderBy("id ASC");
			$drugs->getSqlQuery();
		
			$dr = $drugs->execute();
		
			if($dr)
			{
				$drugsarray = $dr->toArray();
				return $drugsarray;
			}
		}

	}

?>