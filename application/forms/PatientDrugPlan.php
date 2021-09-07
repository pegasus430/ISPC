<?php

require_once("Pms/Form.php");
class Application_Form_PatientDrugPlan extends Pms_Form{
	public function validate($post)
	{

	}
	//Changes for ISPC-1848 F
	public function InsertData($post)
	{ // Used in patientmaster add - Aufnahme
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_CommonData::getIpid($decid);
	    
	    
	    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		$change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		
		$modules = new Modules();
		if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
		{
		    $acknowledge = "1";
		    if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA')
		    {
		        
		        $allow_change = "1";
		    }
		    else
		    {
		        $allow_change = "0";
		    }
		} 
		else
		{
		    $acknowledge = "0";
		}

		
		if($acknowledge == "1" && !in_array($userid,$approval_users))//Medication acknowledge
		{
		    if($allow_change == "1")
            {
    	        // get user details
    	        $master_user_details = new User();
    	        $users_details_arr = $master_user_details->getUserDetails($userid);
    	        $users_details = $users_details_arr[0];
    	        $user_name = $users_details['first_name'].' '.$users_details['last_name'];
    	         
    	        // get patient details
    	        $patient_details = PatientMaster::get_multiple_patients_details(array( $post['ipid']));
    	        $patient_name = $patient_details[ $post['ipid']]['first_name'] . ', ' . $patient_details[ $post['ipid']]['last_name'];
    	        
    	        
    	        foreach ($post['medication'] as $key => $val)
    	        {
    	            if (strlen($post['medication'][$key]) > 0)
    	            {
    	                if ($post['hidd_medication'][$key] == "")
    	                {
    	                    $post['hidd_medication'][$key] = $post['newhidd_medication'][$key];
    	                }
        				$ins_pat_drug_plan = new PatientDrugPlan();
        				//ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
        				$pc_listener = $ins_pat_drug_plan->getListener()->get('IntenseMedicationConnectionListener');
        				$pc_listener->setOption('disabled', true);
        				//--
        				$ins_pat_drug_plan->ipid = $post['ipid'];
        				$ins_pat_drug_plan->dosage = $post['dosage'][$key];
        				$ins_pat_drug_plan->dosage_interval = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
        				$ins_pat_drug_plan->medication_master_id = $post['hidd_medication'][$key];
        				$ins_pat_drug_plan->isbedarfs = $post['isbedarfs'];
        				$ins_pat_drug_plan->iscrisis = $post['iscrisis'];
        				$ins_pat_drug_plan->isivmed = $post['isivmed'];
        				$ins_pat_drug_plan->treatment_care = $post['treatment_care'];
        				$ins_pat_drug_plan->isnutrition = $post['isnutrition'];
        				$ins_pat_drug_plan->comments = $post['comments'][$key];
        				$ins_pat_drug_plan->medication_change = date('Y-m-d 00:00:00');
        				$ins_pat_drug_plan->save();
        				
        				$inserted_id = $ins_pat_drug_plan->id;
        				
        				$cust = new PatientDrugPlanAlt();
        				$cust->ipid = $post['ipid'];
        				$cust->drugplan_id = $inserted_id;
        				$cust->dosage = $post['dosage'][$key];
        				$cust->dosage_interval = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
        				$cust->medication_master_id = $post['hidd_medication'][$key];
        				$cust->isbedarfs = $post['isbedarfs'];
        				$cust->iscrisis = $post['iscrisis'];
        				$cust->isivmed = $post['isivmed'];
        				$cust->treatment_care = $post['treatment_care'];
        				$cust->isnutrition = $post['isnutrition'];
        				$cust->isintubated = $post['isintubated'];
        				$cust->verordnetvon = $post['verordnetvon'][$key];
        				$cust->comments = $post['comments'][$key];
        				$cust->medication_change = date('Y-m-d 00:00:00');
        				$cust->status = "new";
        				$cust->save();
        				$recordid = $cust->id;	            	
    
        				
        				// NEW ENTRY
        				// new name
    
        				
        				if( $post['treatment_care'] == 1 )
        				{
        				    $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($post['hidd_medication'][$key]);
        				}
        				elseif( $post['isnutrition'] == 1 )
        				{
        				    $new_med = Doctrine::getTable('Nutrition')->find($post['hidd_medication'][$key]);
        				}
        				else
        				{
        				    $new_med = Doctrine::getTable('Medication')->find($post['hidd_medication'][$key]);
        				}
        				
        				$new_medication_name[$key] = $new_med->name;
        				
        				// new dosage
        				$new_medication_dosage[$key] = $post['dosage'][$key];
        				
        				// new comments
        				$new_medication_comments[$key] = $post['comments'][$key];
        				
        				// new change date
       				    $medication_change_date_str[$key]= date("d.m.Y",time());
        				
        				if(strlen($new_medication_dosage[$key])>0)
        				{
        				    $new_entry[$key] = $prefix.$new_medication_name[$key]."  |  ".$new_medication_dosage[$key]." | ". $new_medication_comments[$key]." | ".$medication_change_date_str[$key];
        				}
        				else
        				{
        				    $new_entry[$key] = $prefix.$new_medication_name[$key]." | ".$new_medication_comments[$key]." | ".$medication_change_date_str[$key];
        				}
        				
        				// NEW ENTRY
        				if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				{
        				    $shortcut = "N";
        				}
        				elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				{
        				    $shortcut = "I";
        				}
        				elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 )
        				{
        				    $shortcut = "Q";
        				    $prefix = "Schmerzpumpe ";
        				}
        				elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1 )
        				{
        				    $shortcut = "BP";
        				}
        				elseif($post['iscrisis'] == 1 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				{
        					$shortcut = "KM";
        				}
        				elseif($post['isintubated'] == 1 && $post['iscrisis'] == 0 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				{
        					$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
        				}
        				else
        				{
        				    $shortcut = "M";
        				}
        				
        				$attach = 'OHNE FREIGABE: ' .  $new_entry[$key].'';
        				$insert_pc = new PatientCourse();
        				$insert_pc->ipid = $post['ipid'];
        				$insert_pc->course_date = date("Y-m-d H:i:s", time());
        				$insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
        				$insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_edit_med");
        				$insert_pc->recordid = $recordid;
        				$insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        				$insert_pc->user_id = $userid;
        				$insert_pc->save();
        				
        				// SEND MESSAGE
        				$text  = "";
        				$text .= "Patient ".$patient_name." \n ";
        				$text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
        				$text .= "neue Medikation: " .  $new_entry[$key]." \n ";
        				
        				$mess = Messages::medication_acknowledge_messages( $post['ipid'], $text);
        				
        				// CREATE TODO
        				$text_todo  = "";
        				$text_todo .= "Patient ".$patient_name." <br/>";
        				$text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
        				$text_todo .= "neue Medikation: " .  $new_entry[$key]." <br/>";
        				    				
        				$todos = Messages::medication_acknowledge_todo($post['ipid'], $text_todo, $inserted_id, $recordid);
    	            }
    	        }
    		}
    		else
    		{
    		    $misc = "Medication change  Permission Error -  Insert from Admission";
    		    PatientPermissions::MedicationLogRightsError(false,$misc);    
    		}
	    }
	    else
	    {
    		foreach ($post['medication'] as $key => $val)
    		{
    			if (strlen($post['medication'][$key]) > 0)
    			{
    				if ($post['hidd_medication'][$key] == "")
    				{
    					$post['hidd_medication'][$key] = $post['newhidd_medication'][$key];
    				}
    
    				$comments = $post['comments'][$key];
    				$dosage = $post['dosage'][$key];
    				$dosage_interval = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
    				
    
    				$cust = new PatientDrugPlan();
    				//ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
    				$pc_listener = $cust->getListener()->get('IntenseMedicationConnectionListener');
    				$pc_listener->setOption('disabled', true);
    				//--
    				$cust->ipid = $post['ipid'];
    				$cust->dosage = $dosage;
    				$cust->dosage_interval = $dosage_interval;
    				$cust->comments = $comments;
    				$cust->isbedarfs = $post['isbedarfs'];
    				$cust->iscrisis = $post['iscrisis'];
    				$cust->isivmed = $post['isivmed'];
    				$cust->treatment_care = $post['treatment_care'];
    				$cust->isnutrition = $post['isnutrition'];
    				$cust->isintubated = $post['isintubated'];
    				$cust->medication_master_id = $post['hidd_medication'][$key];
    				$cust->medication_change = date('Y-m-d 00:00:00');
    				$cust->save();
    				$inserted_id = $cust->id;

    				// this is for  Medication acknowledge
    				if(in_array($userid,$approval_users) && $acknowledge == "1" ){
    				    // NEW ENTRY
    				    if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    				    {
    				        $shortcut = "N";
    				    }
    				    elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    				    {
    				        $shortcut = "I";
    				    }
    				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 )
    				    {
    				        $shortcut = "Q";
    				        $prefix = "Schmerzpumpe ";
    				    }
    				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1 )
    				    {
    				        $shortcut = "BP";
    				    }
    				    elseif($post['iscrisis'] == 1 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    				    {
    				    	$shortcut = "KM";
    				    }
    				    elseif($post['isintubated'] == 1 && $post['iscrisis'] == 0 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    				    {
    				    	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
    				    }
    				    else
    				    {
    				        $shortcut = "M";
    				    }
    				    // new name
    				    if( $post['treatment_care'] == 1 )
    				    {
    				        $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($post['hidd_medication'][$key]);
    				    }
    				    elseif( $post['isnutrition'] == 1 )
    				    {
    				        $new_med = Doctrine::getTable('Nutrition')->find($post['hidd_medication'][$key]);
    				    }
    				    else
    				    {
    				        $new_med = Doctrine::getTable('Medication')->find($post['hidd_medication'][$key]);
    				    }
    				    $new_medication_name[$key] = $new_med->name;
    				    
    				    // new dosage
    				    $new_medication_dosage[$key] = $post['dosage'][$key];
    				    
    				    // new comments
    				    $new_medication_comments[$key] = $post['comments'][$key];
    				    
    				    // new change date
    				    $medication_change_date_str[$key]= date("d.m.Y",time());
    				    
    				    if(strlen($new_medication_dosage[$key])>0)
    				    {
    				        $new_entry[$key] = $prefix.$new_medication_name[$key]."  |  ".$new_medication_dosage[$key]." | ". $new_medication_comments[$key]." | ".$medication_change_date_str[$key];
    				    }
    				    else
    				    {
    				        $new_entry[$key] = $prefix.$new_medication_name[$key]." | ".$new_medication_comments[$key]." | ".$medication_change_date_str[$key];
    				    }
    				    

    				    $attach = $new_entry[$key].'';
    				    $insert_pc = new PatientCourse();
    				    $insert_pc->ipid = $post['ipid'];
    				    $insert_pc->course_date = date("Y-m-d H:i:s", time());
    				    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
    				    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
    				    $insert_pc->recordid = $inserted_id;
    				    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
    				    $insert_pc->user_id = $userid;
    				    $insert_pc->save();
    				    
    				    
    				}
    				
    				
    				
    			}
    		}
	    }
	}

	public function UpdateData($post)
	{
		$meds = Doctrine::getTable('PatientDrugPlan')->find($_GET['mid']);
		$meds->medication_master_id = $post['hidd_medication'];
		$meds->dosage = $post['dosage'];
		
		if (isset($post['dosage_interval'])) {
		    $meds->dosage_interval = $post['dosage_interval'];
		}
		
		$meds->comments = $post['comments'];
		$meds->save();
	}

	public function UpdateMultiData($post)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		
		$approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		$change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		
		$modules = new Modules();
		if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
		{
		    $acknowledge = "1";
		    if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA')
		    {
		        
		        $allow_change = "1";
		    }
		    else
		    {
		        $allow_change = "0";
		    }
		} 
		else
		{
		    $acknowledge = "0";
		}

		
		if($acknowledge == "1" && !in_array($userid,$approval_users))//Medication acknowledge
		{
		    
		    if($allow_change == "1")
        	{
    		    // get user details
    		    $master_user_details = new User();
    		    $users_details_arr = $master_user_details->getUserDetails($userid);
    		    $users_details = $users_details_arr[0];
                $user_name = $users_details['first_name'].' '.$users_details['last_name'];
                 
    		    // get patient details
                $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
                $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
                
                
    		    if ($post['isschmerzpumpe'] == 1)
    		    {
    		        //insert cocktail procedure
    		        $mc = new PatientDrugPlanAltCocktails();
    		        $mc->userid = $userid;
    		        $mc->clientid = $clientid;
    		        $mc->ipid = $ipid;
    		        $mc->description = $post['cocktailDescription'];
    		        $mc->bolus = $post['bolus'];
    		        $mc->max_bolus = $post['max_bolus'];
    		        $mc->flussrate = $post['flussrate'];
    		        if(isset($post['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
                        $mc->flussrate_type = $post['flussrate_type'];        //ISPC-2684 Lore 08.10.2020
    		        }
    		        $mc->sperrzeit = $post['sperrzeit'];
    		        $mc->save();
    		        //get cocktail id
    		        $cocktailId = $mc->id;
    		    }
    		    
    		    foreach ($post['hidd_medication'] as $i => $med_item)
    		    {
    		        $update_medication[$i] = "0";
    		    
    		        if ($post['hidd_medication'][$i] > 0)
    		        {
    		          $medid = $post['hidd_medication'][$i];
    		        }
    		        else
    		        {
    		        	$medid = $post['newhidd_medication'][$i];
    		        }
    		    
    		        if (empty($post['verordnetvon'][$i]))
    		        {
    		        	$post['verordnetvon'][$i] = 0;
    		        }
    		    
    		    
    		        $cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$i]);
    		        
    		        if($cust)
    		        {
        				if ((strtotime(date('d.m.Y',strtotime($cust->medication_change))) != strtotime($post['medication_change'][$i])
        				        || $cust->dosage != $post['dosage'][$i] 
        				        || $cust->medication_master_id != $medid 
        				        || $cust->verordnetvon != $post['verordnetvon'][$i] 
        				        || $cust->comments != $post['comments'][$i]
        				        || (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval'])
        				    ) 
        				    && $post['edited'][$i] == '1'
        				)
        				{ //check to update only what's modified
    
        				    $update_medication[$i] = "1";
    
        				    if(!empty($post['medication_change'][$i])){
        				        //check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
        				        if ($cust->dosage != $post['dosage'][$i] 
        				            || $cust->medication_master_id != $medid 
        				            || $cust->verordnetvon != $post['verordnetvon'][$i] 
        				            || $cust->comments != $post['comments'][$i]
        				            || (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval'])
    				            ) {
        				            if ($post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
        				                $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
        				            } elseif ($post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
        				                $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
        				            } elseif ($post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
        				                $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
        				            } else {
        				                $medication_change_date[$i] = date('Y-m-d 00:00:00');
        				            }
    
        				            // if no medication details were modified - check in the "last edit date" was edited
        				        } else if(
        				            ( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
        				            ( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
        				            ( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
        				        {
    
        				            $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    
        				        } else if(
        				            ( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
        				            ( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
        				            ( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) )
        				        )
        				        {
        				            $update_medication[$i] = "0";
        				        }
    
        				        // if "last edit date was edited - save current date"
        				    } else {
        				        $medication_change_date[$i] = date('Y-m-d 00:00:00');
        				    }
        				}
        				else {
        				    $update_medication[$i] = "0";
        				}
    
        				// ================= Update patient drugplan item ====================
        				if($update_medication[$i] == "1"){
        				    
        				    if( $cust->isivmed == 0 &&  $cust->isbedarfs == 1 &&  $cust->isschmerzpumpe == 0 &&  $cust->treatment_care == 0 )
        				    {
        				        $shortcut = "N";
        				    }
        				    elseif($cust->isivmed  == 1 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
        				    {
        				        $shortcut = "I";
        				    }
        				    elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 1 &&  $cust->treatment_care== 0 )
        				    {
        				        $shortcut = "Q";
        				        $prefix = "Schmerzpumpe ";
        				    }
        				    elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 1 )
        				    {
        				        $shortcut = "BP";
        				    }
        				    elseif($cust->iscrisis == 1 && $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
        				    {
        				    	$shortcut = "KM";
        				    }
        				    elseif($cust->isintubated == 1 && $cust->iscrisis == 0 && $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
        				    {// ISPC-2176
        				    	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
        				    }
        				    else
        				    {
        				        $shortcut = "M";
        				    }
     
        				    $clear = $this->update_pdpa($ipid, $post['drid'][$i]);
        				    
        				    
        				    $insert_at = new PatientDrugPlanAlt();
        				    $insert_at->ipid = $ipid;
        				    $insert_at->drugplan_id = $post['drid'][$i];
        				    $insert_at->dosage = $post['dosage'][$i];
        				    $insert_at->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
        				    $insert_at->medication_master_id = $medid;
        				    $insert_at->isbedarfs =$cust->isbedarfs;
        				    $insert_at->iscrisis =$cust->iscrisis;
        				    $insert_at->isivmed = $cust->isivmed;
        				    if ($cust->isschmerzpumpe == 1)
        				    {
        				        $insert_at->isschmerzpumpe = $cust->isschmerzpumpe;
        				        $insert_at->cocktailid = $cocktailId;
        				    }
        				    $insert_at->treatment_care = $cust->treatment_care;
        				    $insert_at->isnutrition = $cust->isnutrition;
        				    $insert_at->isintubated = $cust->isintubated; // ISPC-2176
        				    
        				    $insert_at->verordnetvon = $post['verordnetvon'][$i];
        				    $insert_at->comments = $post['comments'][$i];
        				    $insert_at->medication_change = $medication_change_date[$i];
        				    $insert_at->status = "edit";
        				    $insert_at->save();
        				    $insertedIds[] = $insert_at->id;
        				    $recordid = $insert_at->id;
        				    
							// Maria:: Migration ISPC to CISPC 08.08.2020        				    
        				    // TODO-2785 Lore 10.01.2020
        				    // OLD ENTRY
        				    // old medication name
        				    
							//TODO-2785 Lore 18.02.2020
        				    //$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
        				    //$old_med_name[$i] = $old_med->name;
        				    
        				    if( $cust->treatment_care == 1 )
        				    {
        				        $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id);
        				    }
        				    elseif( $cust->isnutrition == 1 )
        				    {
        				        $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id);
        				    }
        				    else
        				    {
        				        $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
        				    }
        				    $old_med_name[$i] = $old_med->name;
        				    //.  TODO-2785 Lore 10.01.2020 
        				    
        				    // old dosage
        				    if($cust->dosage) {
        				        $old_med_dosage[$i] = $cust->dosage;
        				    }
        				    
        				    // old comment
        				    if($cust->comments ){
        				        $old_med_comments[$i] = $cust->comments." | ";
        				    }
        				     
        				    //  old medication date
        				    if($cust->medication_change != "0000-00-00 00:00:00")
        				    {
        				        $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->medication_change));
        				    }
        				    else
        				    {
        				        if($cust->change_date != "0000-00-00 00:00:00")
        				        {
        				            $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->change_date));
        				        }
        				        else
        				        {
        				            $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->create_date));
        				        }
        				    }
        				    
        				    if(strlen($old_med_dosage[$i])>0){
        				        $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
        				    } else	{
        				        $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
        				    }
        				    
        				    // NEW ENTRY
        				    // new name
        				    if( $post['treatment_care'] == 1 )
        				    {
        				        $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
        				    }
        				    elseif( $post['isnutrition'] == 1 )
        				    {
        				        $new_med = Doctrine::getTable('Nutrition')->find($medid);
        				    }
        				    else
        				    {
        				        $new_med = Doctrine::getTable('Medication')->find($medid);
        				    }
        				    
        				    $new_medication_name[$i] = $new_med->name;
        				    
        				    // new dosage
        				    $new_medication_dosage[$i] = $post['dosage'][$i];
        				    
        				    // new comments
        				    $new_medication_comments[$i] = $post['comments'][$i];
        				    
        				    // new change date
        				    if($medication_change_date[$i] != "0000-00-00 00:00:00"){
        				        $medication_change_date_str[$i] = date("d.m.Y", strtotime($medication_change_date[$i]));
        				    }
        				    else
        				    {
        				        $medication_change_date_str[$i]="";
        				    }
        				    
        				    if(strlen($new_medication_dosage[$i])>0)
        				    {
        				        $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        				    }
        				    else
        				    {
        				        $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        				    }
        				    
        				    $attach = 'OHNE FREIGABE: ' . $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
        				    $insert_pc = new PatientCourse();
        				    $insert_pc->ipid = $ipid;
        				    $insert_pc->course_date = date("Y-m-d H:i:s", time());
        				    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
//         				    $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
        				    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_edit_med");
        				    $insert_pc->recordid = $recordid;
        				    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        				    $insert_pc->user_id = $userid;
        				    $insert_pc->save();
    
        				    // SEND MESSAGE
        				    $text  = "";
        				    $text .= "Patient ".$patient_name." \n ";
        				    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
        				    $text .= $old_entry[$i] . "  -> " .  $new_entry[$i]." \n ";
        				    $mess = Messages::medication_acknowledge_messages($ipid,$text);
        				    
        				    // CREATE TODO
        				    $text_todo  = "";
        				    $text_todo .= "Patient ".$patient_name." <br/>";
        				    $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
        				    $text_todo .= $old_entry[$i] . "  -> " .  $new_entry[$i]." <br/>";
        				    
        				    $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $post['drid'][$i], $recordid);
        				    
        				    
        				}
    		        }
    		        else
    		        {//insert new
        				if($medid > '0')
        				{
        				    
        				    if ($post['isschmerzpumpe'] == 1)
        				    {
        				        $cocktailId[$key] = $cocktailId;
        				    }
        				    
        				    if($post['done_date'])
        				    {
        				        $medication_change[$key] = date('Y-m-d H:i:s', strtotime($post['done_date']));
        				    }
        				    else
        				    {
        				        $medication_change[$key] = date('Y-m-d 00:00:00');
        				    }
        				    
        				    $ins_pat_drug_plan = new PatientDrugPlan();
        				    $ins_pat_drug_plan->ipid = $ipid;
        				    $ins_pat_drug_plan->dosage = $post['dosage'][$i];
        				    $ins_pat_drug_plan->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
        				    $ins_pat_drug_plan->medication_master_id = $medid;
        				    $ins_pat_drug_plan->isbedarfs = $post['isbedarfs'];
        				    $ins_pat_drug_plan->iscrisis = $post['iscrisis'];
        				    $ins_pat_drug_plan->isivmed = $post['isivmed'];
        				    $ins_pat_drug_plan->treatment_care = $post['treatment_care'];
        				    $ins_pat_drug_plan->isnutrition = $post['isnutrition'];
        				    $ins_pat_drug_plan->isintubated = $post['isintubated'];// ISPC-2176
        				    $ins_pat_drug_plan->verordnetvon = $post['verordnetvon'][$i];
        				    $ins_pat_drug_plan->comments = $post['comments'][$i];
        				    if(!empty($post['medication_change'][$i])){
        				        $ins_pat_drug_plan->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
        				    } else{
        				        $ins_pat_drug_plan->medication_change = date('Y-m-d 00:00:00');
        				    }
        				    $ins_pat_drug_plan->save();
        				    
        				    $inserted_id = $ins_pat_drug_plan->id;
        				    
        				    $cust = new PatientDrugPlanAlt();
        				    $cust->ipid = $ipid;
        				    $cust->drugplan_id = $inserted_id;
        				    $cust->dosage = $post['dosage'][$i];
        				    $cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
        				    $cust->medication_master_id = $medid;
        				    $cust->isbedarfs = $post['isbedarfs'];
        				    $cust->iscrisis = $post['iscrisis'];
        				    $cust->isivmed = $post['isivmed'];
        				    $cust->treatment_care = $post['treatment_care'];
        				    $cust->isnutrition = $post['isnutrition'];
        				    $cust->isintubated = $post['isintubated'];// ISPC-2176
        				    $cust->verordnetvon = $post['verordnetvon'][$i];
        				    $cust->comments = $post['comments'][$i];
        				    if(!empty($post['medication_change'][$i])){
        				        $cust->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
        				    } else{
        				        $cust->medication_change = date('Y-m-d 00:00:00');
        				    }
        				    $cust->status = "new";
        				    $cust->save();
        				    
        				    $recordid = $cust->id;
        				    
    
        				    
        				    // NEW ENTRY
        				    if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				    {
        				        $shortcut = "N";
        				    }
        				    elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				    {
        				        $shortcut = "I";
        				    }
        				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 )
        				    {
        				        $shortcut = "Q";
        				        $prefix = "Schmerzpumpe ";
        				    }
        				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1 )
        				    {
        				        $shortcut = "BP";
        				    }
        				    elseif($post['iscrisis'] == 1 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				    {
        				    	$shortcut = "KM";
        				    }
        				    elseif($post['isintubated'] == 1 && $post['iscrisis'] == 0 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				    {// ISPC-2176
        				    	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
        				    }
        				    else
        				    {
        				        $shortcut = "M";
        				    }
        				    
        				    
        				    // new name
        				    if( $post['treatment_care'] == 1 )
        				    {
        				        $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
        				    }
        				    elseif( $post['isnutrition'] == 1 )
        				    {
        				        $new_med = Doctrine::getTable('Nutrition')->find($medid);
        				    }
        				    else
        				    {
        				        $new_med = Doctrine::getTable('Medication')->find($medid);
        				    }
        				    
        				    
        				    $new_medication_name[$i] = $new_med->name;
        				    
        				    // new dosage
        				    $new_medication_dosage[$i] = $post['dosage'][$i];
        				    
        				    // new comments
        				    $new_medication_comments[$i] = $post['comments'][$i];
        				    
        				    // new change date
        				    
        				    if(!empty($post['medication_change'][$i])){
        				        $medication_change_date_str[$i] = date('d.m.Y', strtotime($post['medication_change'][$i]));
        				    } else{
        				        $medication_change_date_str[$i] = date('d.m.Y',time());
        				    }
        				    
        				    if(strlen($new_medication_dosage[$i])>0)
        				    {
        				        $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        				    }
        				    else
        				    {
        				        $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        				    }
        				    
        				    $attach = 'OHNE FREIGABE: ' .  $new_entry[$i].'';
        				    $insert_pc = new PatientCourse();
        				    $insert_pc->ipid = $ipid;
        				    $insert_pc->course_date = date("Y-m-d H:i:s", time());
        				    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
//         				    $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
        				    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_new_med");
        				    $insert_pc->recordid = $recordid;
        				    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        				    $insert_pc->user_id = $userid;
        				    $insert_pc->save();
        				    
        				    
    
        				    // SEND MESSAGE
        				    $text  = "";
        				    $text .= "Patient ".$patient_name." \n ";
        				    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
        				    $text .=  "neue Medikation:  " .  $new_entry[$i]." \n ";
        				    $mess = Messages::medication_acknowledge_messages($ipid,$text);
        				    
        				    // CREATE TODO
        				    $text_todo  = "";
        				    $text_todo .= "Patient ".$patient_name." <br/>";
        				    $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
        				    $text_todo .=  "neue Medikation:  " .  $new_entry[$i]." <br/>";
        				    
        				    $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $inserted_id, $recordid);
        				}
    		        }
    		    }
    		}
    		else
    		{
    		    $misc = "Medication change  Permission Error - Update Multiple ";
    		    PatientPermissions::MedicationLogRightsError(false,$misc);
    		}
		}
		else
		{ // !!!!!!!!!! VERSION WITH NO - Medical acknowledge function !!!!!!!!!!!
		    
		    
    		foreach ($post['hidd_medication'] as $i => $med_item)
    		{
    			$update_medication[$i] = "0";
    
    			if ($post['hidd_medication'][$i] > 0)
    			{
    				$medid = $post['hidd_medication'][$i];
    			}
    			else
    			{
    				$medid = $post['newhidd_medication'][$i];
    			}
    
    			if (empty($post['verordnetvon'][$i]))
    			{
    				$post['verordnetvon'][$i] = 0;
    			}
    
    				
    			$cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$i]);
    			if($cust){
    				if (( strtotime(date('d.m.Y',strtotime($cust->medication_change))) != strtotime($post['medication_change'][$i]) 
    				        || $cust->dosage != $post['dosage'][$i] 
    				        || $cust->medication_master_id != $medid 
    				        || $cust->verordnetvon != $post['verordnetvon'][$i] 
    				        || $cust->comments != $post['comments'][$i]
    				        || (isset($post[$i]['dosage_interval'] ) && $cust->dosage_interval != $post[$i]['dosage_interval'])
    				    ) 
    				    && $post['edited'][$i] == '1'
    				)
    				{ //check to update only what's modified
    
    					$update_medication[$i] = "1";
    
    					if(!empty($post['medication_change'][$i])){
    						//check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
    						if ($cust->dosage != $post['dosage'][$i] 
    						    || $cust->medication_master_id != $medid 
    						    || $cust->verordnetvon != $post['verordnetvon'][$i] 
    						    || $cust->comments != $post['comments'][$i]
    						    || (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval'])
    						    )
    						{
    							if ($post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
    								$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    							} elseif ($post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
    								$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    							} elseif ($post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
    								$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    							} else {
    								$medication_change_date[$i] = date('Y-m-d 00:00:00');
    							}
    
    							// if no medication details were modified - check in the "last edit date" was edited
    						} else if(
    								( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
    								( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
    								( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
    						{
    
    							$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    
    						} else if(
    								( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
    								( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
    								( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) )
    						)
    						{
    							$update_medication[$i] = "0";
    						}
    
    						// if "last edit date was edited - save current date"
    					} else {
    						$medication_change_date[$i] = date('Y-m-d 00:00:00');
    					}
    				}
    				else {
    					$update_medication[$i] = "0";
    				}
    				/* ================= Save in patient drugplan history ====================*/
    				if(	$cust->dosage != $post['dosage'][$i] 
    				    || $cust->medication_master_id != $medid 
    				    || $cust->verordnetvon != $post['verordnetvon'][$i] 
    				    || $cust->comments != $post['comments'][$i]
						|| (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval']) 
    				)
    				{
						//TODO-2785 Lore 18.02.2020
    					//$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
    				    if( $cust->treatment_care == 1 )
    				    {
    				        $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id);
    				    }
    				    elseif( $cust->isnutrition == 1 )
    				    {
    				        $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id);
    				    }
    				    else
    				    {
    				        $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
    				    }
    					$medication_old_medication_name[$i] = $old_med->name;
    					$medication_old_medication_id[$i] =  $old_med->id;
    
    					$history = new PatientDrugPlanHistory();
    					$history->ipid = $ipid;
    					$history->pd_id = $cust->id;
    					$history->pd_medication_master_id = $cust->medication_master_id ;
    					$history->pd_medication_name = $medication_old_medication_name[$i] ;
    					$history->pd_medication =  $cust->medication;
    					$history->pd_dosage = $cust->dosage;
    					$history->pd_dosage_interval = isset($cust->dosage_interval) ? $cust->dosage_interval : null;
    					$history->pd_dosage_product = isset($cust->dosage_product) ? $cust->dosage_product : null;
    					$history->pd_comments = $cust->comments ;
    					$history->pd_isbedarfs = $cust->isbedarfs;
    					$history->pd_iscrisis = $cust->iscrisis;
    					$history->pd_isivmed = $cust->isivmed;
    					$history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
    					$history->pd_cocktailid= $cust->cocktailid;
    					$history->pd_treatment_care = $cust->treatment_care;
    					$history->pd_isnutrition = $cust->isnutrition;
    					$history->pd_isintubated = $cust->isintubated; // ISPC-2176
    					$history->pd_edit_type = $cust->edit_type;
    					$history->pd_verordnetvon = $cust->verordnetvon;
    					$history->pd_medication_change = $cust->medication_change;
    					$history->pd_create_date = $cust->create_date;
    					$history->pd_create_user = $cust->create_user;
    					$history->pd_change_date = $cust->change_date;
    					$history->pd_change_user = $cust->change_user;
    					$history->pd_isdelete = $cust->isdelete;
    					$history->pd_days_interval_technical = isset($cust->days_interval_technical) ? $cust->days_interval_technical : null;
    					$history->save();
    				}
    
    				/* ================= Update patient drugplan item ====================*/
    				if($update_medication[$i] == "1"){
//     					$cust->dosage.' '.
//     							$cust->ipid = $ipid;
//     					$cust->dosage = $post['dosage'][$i];
//     					$cust->medication_master_id = $medid;
//     					$cust->verordnetvon = $post['verordnetvon'][$i];
//     					$cust->comments = $post['comments'][$i];
//     					$cust->medication_change = $medication_change_date[$i];
//     					$cust->save();
    					
    					
    					// IF An approval user edits a medications that is not approved yet - all data is marcked as inactive for this medication
    					if(in_array($userid,$approval_users)  && $acknowledge == "1" )
    					{
    					    $clear = $this->update_pdpa($ipid, $post['drid'][$i]);
    					
        					if( $cust->isivmed == 0 &&  $cust->isbedarfs == 1 &&  $cust->isschmerzpumpe == 0 &&  $cust->treatment_care == 0 )
        					{
        					    $shortcut = "N";
        					}
        					elseif($cust->isivmed  == 1 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
        					{
        					    $shortcut = "I";
        					}
        					elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 1 &&  $cust->treatment_care== 0 )
        					{
        					    $shortcut = "Q";
        					    $prefix = "Schmerzpumpe ";
        					}
        					elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 1 )
        					{
        					    $shortcut = "BP";
        					}
        					elseif($cust->iscrisis  == 1 && $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
        					{
        						$shortcut = "KM";
        					}
        					elseif($cust->isintubated  == 1 && $cust->iscrisis  == 0 && $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
        					{// ISPC-2176
        						$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
        					}
        					
        					
        					else
        					{
        					    $shortcut = "M";
        					}
        					
        					// OLD ENTRY
        					// old medication name
							//TODO-2785 Lore 18.02.2020
        					//$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
        					if( $cust->treatment_care == 1 )
        					{
        					    $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id);
        					}
        					elseif( $cust->isnutrition == 1 )
        					{
        					    $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id);
        					}
        					else
        					{
        					    $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
        					}
        					$old_med_name[$i] = $old_med->name;
        					
        					// old dosage
        					if($cust->dosage) {
        					    $old_med_dosage[$i] = $cust->dosage;
        					}
        					
        					// old comment
        					if($cust->comments ){
        					    $old_med_comments[$i] = $cust->comments." | ";
        					}
        						
        					//  old medication date
        					if($cust->medication_change != "0000-00-00 00:00:00")
        					{
        					    $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->medication_change));
        					}
        					else
        					{
        					    if($cust->change_date != "0000-00-00 00:00:00")
        					    {
        					        $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->change_date));
        					    }
        					    else
        					    {
        					        $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->create_date));
        					    }
        					}
        					
        					if(strlen($old_med_dosage[$i])>0){
        					    $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
        					} else	{
        					    $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
        					}
        					
        					// NEW ENTRY
        					// new name
        					if( $post['treatment_care'] == 1 )
        					{
        					    $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
        					}
        					elseif( $post['isnutrition'] == 1 )
        					{
        					    $new_med = Doctrine::getTable('Nutrition')->find($medid);
        					}
        					else
        					{
        					    $new_med = Doctrine::getTable('Medication')->find($medid);
        					}
        					
        					$new_medication_name[$i] = $new_med->name;
        					
        					// new dosage
        					$new_medication_dosage[$i] = $post['dosage'][$i];
        					
        					// new comments
        					$new_medication_comments[$i] = $post['comments'][$i];
        					
        					// new change date
        					if($medication_change_date[$i] != "0000-00-00 00:00:00"){
        					    $medication_change_date_str[$i] = date("d.m.Y", strtotime($medication_change_date[$i]));
        					}
        					else
        					{
        					    $medication_change_date_str[$i]="";
        					}
        					
        					if(strlen($new_medication_dosage[$i])>0)
        					{
        					    $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        					}
        					else
        					{
        					    $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        					}
        					
        					$attach = 'Änderung: ' . $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
        					$insert_pc = new PatientCourse();
        					$insert_pc->ipid = $ipid;
        					$insert_pc->course_date = date("Y-m-d H:i:s", time());
        				    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
        					$insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_edit_med");
        					$insert_pc->recordid = $post['drid'][$i];
        					$insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        					$insert_pc->user_id = $userid;
        					$insert_pc->save();
    					}
    					
    					$cust->dosage.' '.
    					    $cust->ipid = $ipid;
    					$cust->dosage = $post['dosage'][$i];
    					$cust->medication_master_id = $medid;
    					$cust->verordnetvon = $post['verordnetvon'][$i];
    					$cust->comments = $post['comments'][$i];
    					$cust->medication_change = $medication_change_date[$i];
    					$cust->save();
    						
    					
    				}
    			}
    			else
    			{//insert new
    				if($medid > '0')
    				{
    					$cust = new PatientDrugPlan();
    					$cust->ipid = $ipid;
    					$cust->dosage = $post['dosage'][$i];
    					$cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
    					$cust->medication_master_id = $medid;
    					$cust->isbedarfs = $post['isbedarfs'];
    					$cust->iscrisis = $post['iscrisis'];
    					$cust->isivmed = $post['isivmed'];
    					$cust->treatment_care = $post['treatment_care'];
    					$cust->isnutrition = $post['isnutrition'];
    					$cust->isintubated = $post['isintubated'];// ISPC-2176
    					$cust->verordnetvon = $post['verordnetvon'][$i];
    					$cust->comments = $post['comments'][$i];
    					if(!empty($post['medication_change'][$i])){
    						$cust->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    					} else{
    						$cust->medication_change = date('Y-m-d 00:00:00');
    					}
    					$cust->save();
    					
    					$inserted_id = $cust->id;

    					
    					
    					// this is for  Medication acknowledge
    					if(in_array($userid,$approval_users)  && $acknowledge == "1" ){
    					    // NEW ENTRY
    					    if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    					    {
    					        $shortcut = "N";
    					    }
    					    elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    					    {
    					        $shortcut = "I";
    					    }
    					    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 )
    					    {
    					        $shortcut = "Q";
    					        $prefix = "Schmerzpumpe ";
    					    }
    					    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1 )
    					    {
    					        $shortcut = "BP";
    					    }
    					    elseif($post['iscrisis'] == 1 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    					    {
    					    	$shortcut = "KM";
    					    }
    					    elseif($post['isintubated'] == 1 && $post['iscrisis'] == 0 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    					    {// ISPC-2176
    					    	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
    					    }
    					    else
    					    {
    					        $shortcut = "M";
    					    }
    					    // new name
    					    if( $post['treatment_care'] == 1 )
    					    {
    					        $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
    					    }
    					    elseif( $post['isnutrition'] == 1 )
    					    {
    					        $new_med = Doctrine::getTable('Nutrition')->find($medid);
    					    }
    					    else
    					    {
    					        $new_med = Doctrine::getTable('Medication')->find($medid);
    					    }
    					    
    					    $new_medication_name[$i] = $new_med->name;
    					
    					    // new dosage
    					    $new_medication_dosage[$i] = $post['dosage'][$i];
    					
    					    // new comments
    					    $new_medication_comments[$i] = $post['comments'][$i];
    					
    					    // new change date
    					    $medication_change_date_str[$i]= date("d.m.Y",time());
    					
    					    if(strlen($new_medication_dosage[$i])>0)
    					    {
    					        $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
    					    }
    					    else
    					    {
    					        $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
    					    }
    					
    					
    					    $attach = $new_entry[$i].'';
    					    $insert_pc = new PatientCourse();
    					    $insert_pc->ipid = $ipid;
    					    $insert_pc->course_date = date("Y-m-d H:i:s", time());
    					    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
    					    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
    					    $insert_pc->recordid = $inserted_id;
    					    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
    					    $insert_pc->user_id = $userid;
    					    $insert_pc->save();
    					
    					
    					}
    					
    				}
    			}
    		}
		}
	}

	public function InsertMultiData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		
		$approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		$change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		
		
		$modules = new Modules();
		if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
		{
		    $acknowledge = "1";
		    if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA')
		    {
		        
		        $allow_change = "1";
		    }
		    else
		    {
		        $allow_change = "0";
		    }
		} 
		else
		{
		    $acknowledge = "0";
		}

		
		if($acknowledge == "1" && !in_array($userid,$approval_users))//Medication acknowledge
		{
		    if($allow_change == "1")
    		{
    		    // get user details
    		    $master_user_details = new User();
    		    $users_details_arr = $master_user_details->getUserDetails($userid);
    		    $users_details = $users_details_arr[0];
    		    $user_name = $users_details['first_name'].' '.$users_details['last_name'];
    		     
    		    // get patient details
    		    $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
    		    $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
    		    
    		    
    		    
    		    if ($post['isschmerzpumpe'] == 1)
    		    {
    		        //insert cocktail procedure
    		        $mc = new PatientDrugPlanCocktails();
    		        $mc->userid = $userid;
    		        $mc->clientid = $clientid;
    		        $mc->ipid = $ipid;
    		        $mc->description = $post['cocktailDescription'];
    		        $mc->bolus = $post['bolus'];
    		        $mc->max_bolus = $post['max_bolus'];
    		        $mc->flussrate = $post['flussrate'];
    		        if(isset($post['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
                        $mc->flussrate_type = $post['flussrate_type'];        //ISPC-2684 Lore 08.10.2020
    		        }
    		        $mc->sperrzeit = $post['sperrzeit'];
    		        $mc->save();
    		        //get cocktail id
    		        $cocktailId = $mc->id;
    		        
    		        
    		        
    		        //get cocktail id
    		        $cocktail_id = $mc->id;
    		         
    		        // insert in cocktail alt
    		        $inser_calt =  new PatientDrugPlanAltCocktails();
    		        $inser_calt->ipid = $ipid;
    		        $inser_calt->userid = $userid;
    		        $inser_calt->clientid = $clientid;
    		        $inser_calt->drugplan_cocktailid = $cocktail_id;
    		        $inser_calt->description = $post['cocktailDescription'];
    		        $inser_calt->bolus = $post['bolus'];
    		        $inser_calt->max_bolus = $post['max_bolus'];
    		        $inser_calt->flussrate = $post['flussrate'];
    		        if(isset($post['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
    		          $inser_calt->flussrate_type = $post['flussrate_type'];        //ISPC-2684 Lore 08.10.2020
    		        }
    		        $inser_calt->sperrzeit = $post['sperrzeit'];
    		        $inser_calt->pumpe_type = $post['pumpe_type'];
    		        $inser_calt->pumpe_medication_type = $post['pumpe_medication_type'];
    		        $inser_calt->carrier_solution = $post['carrier_solution'];
    		        $inser_calt->status = "new";
    		        $inser_calt->save();
    		        
    		        $recordid_cocktail_alt = $inser_calt->id;
    		        
    		        $new_entry = "Kommentar: " . $post['cocktailDescription']."";
//     		        $new_entry .= "\nApplikationsweg: " .$post['pumpe_medication_type'];
    		        //$new_entry .= "\nFlussrate: " . $post['flussrate'];
    		        //ISPC-2684 Lore 08.10.2020
    		        if(!empty($post['flussrate_type'])){
    		            $new_entry .= "\nFlussrate_simple"."(".$post['flussrate_type']."): " . $post['flussrate'];
    		        }else{
    		            $new_entry .= "\nFlussrate: " . $post['flussrate'];
    		        }
//     		        $new_entry .= "\nTrägerlösung: " .$post['carrier_solution'];
    		        
//     		        if($post['pumpe_type'] == "pca")
//     		        {
    		            $new_entry .= "\nBolus: " .$post['bolus'];
    		            $new_entry .= "\nMax Bolus: " .$post['max_bolus'];
    		            $new_entry .= "\nSperrzeit: " .$post['sperrzeit'];
//     		        }
    		        
    		        $attach = "OHNE FREIGABE: " .  $new_entry."";
    		        $insert_pc = new PatientCourse();
    		        $insert_pc->ipid = $ipid;
    		        $insert_pc->course_date = date("Y-m-d H:i:s", time());
    		        $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
    		        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
    		        $insert_pc->recordid = $recordid_cocktail_alt;
    		        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
    		        $insert_pc->user_id = $userid;
    		        $insert_pc->save();
    		        
    		        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    		        // SEND MESSAGE
    		        $text  = "";
    		        $text .= "Patient ".$patient_name." \n ";
    		        $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
    		        $text .=  "neue Medikation:  " .  $new_entry." \n ";
    		        
    		        $mess = Messages::medication_acknowledge_messages($ipid, $text);
    		        
    		        // CREATE TODO
    		        
    		        $text_todo  = "";
    		        $text_todo .= "Patient ".$patient_name." <br/>";
    		        $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
    		        $text_todo .=  "neue Medikation:  " . str_replace("\n","<br/>",$new_entry)."  <br/>";
    		        
    		        $todos = Messages::pump_medication_acknowledge_todo($ipid, $text_todo, $cocktail_id, $recordid_cocktail_alt);
    		         
    		        
    		        
    		        
    		    }
    		    
    		    foreach ($post['hidd_medication'] as $key => $val)
    		    {
    		        if ($post['hidd_medication'][$key] > 0)
    		        {
                        $medid = $post['hidd_medication'][$key];
    		        }
    		        else
    		        {
    		        	$medid = $post['newhidd_medication'][$key];
    		        }
    		    
    		        if ($medid > 0)
    		        {
    		            
    		            if ($post['isschmerzpumpe'] == 1)
    		            {
    		               $cocktailId[$key] = $cocktailId;
    		            }
    		            
    		            if($post['done_date'])
    		            {
    		                $medication_change[$key] = date('Y-m-d H:i:s', strtotime($post['done_date']));
    		            }
    		            else
    		            {
    		                $medication_change[$key] = date('Y-m-d 00:00:00');
    		            }
     
                        $ins_pat_drug_plan = new PatientDrugPlan();
                        $ins_pat_drug_plan->ipid = $ipid;
                        $ins_pat_drug_plan->dosage = $post['dosage'][$key];
                        $ins_pat_drug_plan->dosage_interval = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
                        $ins_pat_drug_plan->medication_master_id = $medid;
                        $ins_pat_drug_plan->isbedarfs = $post['isbedarfs'];
                        $ins_pat_drug_plan->iscrisis = $post['iscrisis'];
                        $ins_pat_drug_plan->isivmed = $post['isivmed'];
                        if ($post['isschmerzpumpe'] == 1)
                        {
                            $ins_pat_drug_plan->isschmerzpumpe = $post['isschmerzpumpe'];
                            $ins_pat_drug_plan->cocktailid = $cocktailId;
                        }
                        //ISPC-2871,Elena,12.04.2021 // ISPC-2781 ?!!!
                        if(!empty($post['pumpe_id'])){
							$ins_pat_drug_plan->pumpe_id = intval($post['pumpe_id']);
							$ins_pat_drug_plan->dosage = $post['dosage'];
						}
                        $ins_pat_drug_plan->treatment_care = $post['treatment_care'];
                        $ins_pat_drug_plan->isnutrition = $post['isnutrition'];
                        $ins_pat_drug_plan->isintubated = $post['isintubated'];// ISPC-2176
                        
                        $ins_pat_drug_plan->verordnetvon = $post['verordnetvon'][$key];
                        $ins_pat_drug_plan->comments = $post['comments'][$key];
                        
                        if($post['done_date'])
                        {
                            $ins_pat_drug_plan->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
                        }
                        else
                        {
                            $ins_pat_drug_plan->medication_change = date('Y-m-d 00:00:00');
                        }
                        
                        $ins_pat_drug_plan->save();
                        $inserted_id = $ins_pat_drug_plan->id;
    		            
                        
                        
        				$cust = new PatientDrugPlanAlt();
        				$cust->ipid = $ipid;
        				$cust->drugplan_id = $inserted_id;
        				$cust->dosage = $post['dosage'][$key];
        				$cust->dosage_interval = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
        				$cust->medication_master_id = $medid;
        				$cust->isbedarfs = $post['isbedarfs'];
        				$cust->iscrisis = $post['iscrisis']; //ispc 1823
        				$cust->isivmed = $post['isivmed'];
        				if ($post['isschmerzpumpe'] == 1)
        				{
        				    $cust->isschmerzpumpe = $post['isschmerzpumpe'];
        				    $cust->cocktailid = $cocktailId;
        				}
        				$cust->treatment_care = $post['treatment_care'];
        				$cust->isnutrition = $post['isnutrition'];
        				$cust->isintubated = $post['isintubated'];// ISPC-2176
    
        				$cust->verordnetvon = $post['verordnetvon'][$key];
        				$cust->comments = $post['comments'][$key];
       				    $cust->medication_change =  $medication_change[$key];
       				    $cust->status = "new";
        				$cust->save();
        				$recordid = $cust->id;
        				$insertedIds[] = $cust->id;
    
        				
        				// NEW ENTRY
    
        				if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				{
        				    $shortcut = "N";
        				}
        				elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				{
        				    $shortcut = "I";
        				}
        				elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 )
        				{
        				    $shortcut = "Q";
        				    $prefix = "Schmerzpumpe ";
        				}
        				elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1 )
        				{
        				    $shortcut = "BP";
        				}
        				elseif($post['iscrisis'] == 1 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				{
        					$shortcut = "KM";
        				}
        				elseif($post['isintubated'] == 1 && $post['iscrisis'] == 0 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				{// ISPC-2176
        					$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
        				}
        				else
        				{
        				    $shortcut = "M";
        				}
        				
        				
        				// new name
        				if( $post['treatment_care'] == 1 )
        				{
            				$new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
        				} 
        				elseif( $post['isnutrition'] == 1 )
        				{
            				$new_med = Doctrine::getTable('Nutrition')->find($medid);
        				} 
        				else
        				{
            				$new_med = Doctrine::getTable('Medication')->find($medid);
        				}
        				
        				
        				
        				$new_medication_name[$key] = $new_med->name;
        				
        				// new dosage
        				$new_medication_dosage[$key] = $post['dosage'][$key];
        				
        				// new comments
        				if(strlen($post['comments'][$key]) > 0){
            				$new_medication_comments[$key] = ' | '.$post['comments'][$key];
        				}
        				
        				// new change date
       				    $medication_change_date_str[$key] = date('d.m.Y', strtotime($medication_change[$key]));
    
       				    
       				    $new_entry = array();
        				if(strlen($new_medication_dosage[$key])>0)
        				{
        				    $new_entry[$key] = $prefix.$new_medication_name[$key]."  |  ".$new_medication_dosage[$key]. $new_medication_comments[$key]." | ".$medication_change_date_str[$key];
        				}
        				else
        				{
        				    $new_entry[$key] = $prefix.$new_medication_name[$key].$new_medication_comments[$key]." | ".$medication_change_date_str[$key];
        				}
        				
        				$attach = 'OHNE FREIGABE: ' .  $new_entry[$key].'';
        				$insert_pc = new PatientCourse();
        				$insert_pc->ipid = $ipid;
        				$insert_pc->course_date = date("Y-m-d H:i:s", time());
        				$insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
//         				$insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
        				$insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_new_med");
        				$insert_pc->recordid = $recordid;
        				$insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        				$insert_pc->user_id = $userid;
        				$insert_pc->save();
        				
        				// SEND MESSAGE
        				$text  = "";
        				$text .= "Patient ".$patient_name." \n ";
        				$text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
        				$text .=  "neue Medikation:  " .  $new_entry[$key]." \n ";
        				
        				$mess = Messages::medication_acknowledge_messages($ipid, $text);
        				
        				// create todo
        				$todo_text  = "";
        				$todo_text .= "Patient ".$patient_name." <br/>";
        				$todo_text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
        				$todo_text .=  "neue Medikation:  " .  $new_entry[$key]." <br/>";
        				
        				$todos = Messages::medication_acknowledge_todo($ipid, $todo_text, $inserted_id, $recordid);
        				
    		        }
    		    }
            }
            else
            {
                $misc = "Medication change  Permission Error - Insert multiple";
    		    PatientPermissions::MedicationLogRightsError(false,$misc);    
            }
        }
		else
		{ // !!!!!!!!!! VERSION WITH NO - Medical acknowledge function !!!!!!!!!!!
		    
		    
    		if ($post['isschmerzpumpe'] == 1)
    		{
    			//insert cocktail procedure
    			$mc = new PatientDrugPlanCocktails();
    			$mc->userid = $userid;
    			$mc->clientid = $clientid;
    			$mc->ipid = $ipid;
    			$mc->description = $post['cocktailDescription'];
    			$mc->bolus = $post['bolus'];
    			$mc->max_bolus = $post['max_bolus'];
    			$mc->flussrate = $post['flussrate'];
    			if(isset($post['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
                    $mc->flussrate_type = $post['flussrate_type'];        //ISPC-2684 Lore 08.10.2020
    			}
    			$mc->sperrzeit = $post['sperrzeit'];
    			$mc->save();
    			//get cocktail id
    			$cocktailId = $mc->id;
    		}
    
    		foreach ($post['hidd_medication'] as $key => $val)
    		{
    			if ($post['hidd_medication'][$key] > 0)
    			{
    				$medid = $post['hidd_medication'][$key];
    			}
    			else
    			{
    				$medid = $post['newhidd_medication'][$key];
    			}
    
    			if ($medid > 0)
    			{
    				$cust = new PatientDrugPlan();
    				$cust->ipid = $ipid;
    				$cust->dosage = $post['dosage'][$key];
    				$cust->dosage_interval = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
    				$cust->medication_master_id = $medid;
    				$cust->isbedarfs = $post['isbedarfs'];
        			$cust->iscrisis = $post['iscrisis']; //ispc 1823
    				$cust->isivmed = $post['isivmed'];
    				if ($post['isschmerzpumpe'] == 1)
    				{
    					$cust->isschmerzpumpe = $post['isschmerzpumpe'];
    					$cust->cocktailid = $cocktailId;
    				}
    				$cust->treatment_care = $post['treatment_care'];
    				$cust->isnutrition = $post['isnutrition'];
    				$cust->isintubated = $post['isintubated']; // ISPC-2176
    				
    				$cust->verordnetvon = $post['verordnetvon'][$key];
    				$cust->comments = $post['comments'][$key];
    				
    				if($post['done_date'])
    				{
    					$cust->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
    				}
    				else
    				{
    					$cust->medication_change = date('Y-m-d 00:00:00');
    				}
    				
    				$cust->save();
    				$inserted_id = $cust->id;
    				$insertedIds[] = $cust->id;
    				
    				
    				
    				
    				// this is for  Medication acknowledge
    				if(in_array($userid,$approval_users) && $acknowledge == "1" ){
    				    // NEW ENTRY
    				    if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    				    {
    				        $shortcut = "N";
    				    }
    				    elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    				    {
    				        $shortcut = "I";
    				    }
    				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 )
    				    {
    				        $shortcut = "Q";
    				        $prefix = "Schmerzpumpe ";
    				    }
    				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1 )
    				    {
    				        $shortcut = "BP";
    				    }
    				    elseif($post['iscrisis'] == 1 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    				    {
    				    	$shortcut = "KM";
    				    }
    				    elseif($post['isintubated'] == 1 && $post['iscrisis'] == 0 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    				    {// ISPC-2176
    				    	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
    				    }
    				    else
    				    {
    				        $shortcut = "M";
    				    }
    				    // new name
    				    if( $post['treatment_care'] == 1 )
    				    {
    				        $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
    				    }
    				    elseif( $post['isnutrition'] == 1 )
    				    {
    				        $new_med = Doctrine::getTable('Nutrition')->find($medid);
    				    }
    				    else
    				    {
    				        $new_med = Doctrine::getTable('Medication')->find($medid);
    				    }
    				    
    				    $new_medication_name[$key] = $new_med->name;
    				
    				    // new dosage
    				    $new_medication_dosage[$key] = $post['dosage'][$key];
    				
    				    // new comments
    				    $new_medication_comments[$key] = $post['comments'][$key];
    				
    				    // new change date
    				    $medication_change_date_str[$key]= date("d.m.Y",time());
    				
    				    if(strlen($new_medication_dosage[$key])>0)
    				    {
    				        $new_entry[$key] = $prefix.$new_medication_name[$key]."  |  ".$new_medication_dosage[$key]." | ". $new_medication_comments[$key]." | ".$medication_change_date_str[$key];
    				    }
    				    else
    				    {
    				        $new_entry[$key] = $prefix.$new_medication_name[$key]." | ".$new_medication_comments[$key]." | ".$medication_change_date_str[$key];
    				    }
    				
    				    $attach = $new_entry[$key].'';
    				    $insert_pc = new PatientCourse();
    				    $insert_pc->ipid = $ipid;
    				    $insert_pc->course_date = date("Y-m-d H:i:s", time());
    				    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
    				    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
    				    $insert_pc->recordid = $inserted_id;
    				    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
    				    $insert_pc->user_id = $userid;
    				    $insert_pc->save();
    				}
    			}
    		}
    		
		}
	}

	public function UpdateBedarfsMultiData($post)
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		$change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		
		$modules = new Modules();
		
		if($modules->checkModulePrivileges("111", $clientid) && !in_array($userid,$approval_users))//Medication acknowledge
		{

		    $insert_at = new PatientDrugPlanAlt();
		    $insert_at->ipid = $ipid;
		    $insert_at->drugplan_id = $post['drid'][$i];
		    $insert_at->dosage = $post['dosage'][$i];
		    $insert_at->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
		    $insert_at->medication_master_id = $medid;
		    $insert_at->isbedarfs = "1";
		    //$insert_at->iscrisis = "0"; //ispc 1823 ??
		    $insert_at->isivmed = "0";
	        $insert_at->isschmerzpumpe = "0";
		    $insert_at->treatment_care = "0";
		    $insert_at->isnutrition = "0";
		    $insert_at->isintubated = "0";
		    $insert_at->iscrisis = "0";
		    $insert_at->verordnetvon = $post['verordnetvon'][$i];
		    $insert_at->comments = $post['comments'][$i];
		    $insert_at->status = "new";
		    $insert_at->save();
		    
		}
		else
		{ // !!!!!!!!!! VERSION WITH NO - Medical acknowledge function !!!!!!!!!!!
		    
    		for ($i = 1; $i <= sizeof($post['hidd_medication']); $i++)
    		{
    			if ($post['hidd_medication'][$i] > 0)
    			{
    				$medid = $post['hidd_medication'][$i];
    			}
    			else
    			{
    				$medid = $post['newhidd_medication'][$i];
    			}
    			
    			$cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$i]);
    			$cust->ipid = $ipid;
    			$cust->dosage = $post['dosage'][$i];
    			$cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
    			$cust->medication_master_id = $medid;
    			$cust->verordnetvon = $post['verordnetvon'][$i];
    			$cust->comments = $post['comments'][$i];
    			$cust->save();
    		}
		}
	}

	public function UpdateSchmerzepumpeMultiData($post)
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

	    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		$change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		
		$modules = new Modules();
		if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
		{
		    $acknowledge = "1";
		    if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA')
		    {
		        
		        $allow_change = "1";
		    }
		    else
		    {
		        $allow_change = "0";
		    }
		} 
		else
		{
		    $acknowledge = "0";
		}

		
		if($acknowledge == "1" && !in_array($userid,$approval_users))//Medication acknowledge
		{
		    if($allow_change == "1")
    		{
    		    // get user details
    		    $master_user_details = new User();
    		    $users_details_arr = $master_user_details->getUserDetails($userid);
    		    $users_details = $users_details_arr[0];
    		    $user_name = $users_details['first_name'].' '.$users_details['last_name'];
    		    
    		    // get patient details
    		    $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
    		    $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
    		    
    		    
    		    foreach ($post['hidd_medication'] as $keym => $valm)
    		    {
    		        $update_sh_medication[$keym] = "0";
    		    
    		        if ($post['hidd_medication'][$keym] > 0)
    		        {
    		        	$medid = $post['hidd_medication'][$keym];
    		        }
    		        else
    		        {
    		          $medid = $post['newhidd_medication'][$keym];
    		        }
    		    
    		        if ($post['drid'][$keym] > 0)
    		        {
        				$cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$keym]);
        				if ($cust){
        				    if (strtotime( date('d.m.Y',strtotime($cust->medication_change))) != strtotime($post['medication_change'][$keym]) 
        				        || $cust->dosage != $post['dosage'][$keym] 
        				        || $cust->medication_master_id != $medid 
        				        || $cust->verordnetvon != $post['verordnetvon'][$keym]
        				        || (isset($post[$keym]['dosage_interval']) && $cust->dosage_interval != $post[$keym]['dosage_interval']) 
        				        )
        				    {//check to update only what's modified
        
        				        $update_sh_medication[$keym] = "1";
        
        				        if(!empty($post['medication_change'][$keym])){
        				            //check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
        				            if ($cust->dosage != $post['dosage'][$keym] 
        				                || $cust->medication_master_id != $medid 
        				                || $cust->verordnetvon != $post['verordnetvon'][$keym]
        				                || (isset($post[$keym]['dosage_interval']) && $cust->dosage_interval != $post[$keym]['dosage_interval'])
        				                )
        				            {
        
        				                if ($post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
        				                    $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
        				                } elseif ($post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
        				                    $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
        				                } elseif ($post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
        				                    $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
        				                } else {
        				                    $medication_change_date[$keym] = date('Y-m-d 00:00:00');
        				                }
        
        				                // if no medication details were modified - check in the "last edit date" was edited
        				            } else if(
        				                ( $post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
        				                ( $post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
        				                ( $post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->create_date))))) )
        				            {
        
        				                $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
        
        				            } else if(
        				                ( $post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
        				                ( $post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
        				                ( $post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
        				            {
        
        				                $update_sh_medication[$keym] = "0";
        				            }
        
        				            // if "last edit date was edited - save current date"
        				        } else {
        				            $medication_change_date[$keym] = date('Y-m-d 00:00:00');
        				        }
        				    } else{
        				        $update_sh_medication[$keym] = "0";
        				    }
        				    // ================= Save in patient drugplan history ==================== 
        				    if(		$cust->dosage != $post['dosage'][$i] ||
        				        $cust->medication_master_id != $medid ||
        				        $cust->verordnetvon != $post['verordnetvon'][$i]){
        
                                 // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!   
        				        /* $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
        				        $medication_old_medication_name[$i] = $old_med->name;
        				        $medication_old_medication_id[$i] =  $old_med->id;
        
        				        $cocktail = Doctrine::getTable('PatientDrugPlanCocktails')->find($cust->cocktailid);
        				        $history = new PatientDrugPlanHistory();
        				        $history->ipid = $ipid;
        				        $history->pd_id = $cust->id;
        				        $history->pd_medication_master_id = $cust->medication_master_id ;
        				        $history->pd_medication_name = $medication_old_medication_name[$i] ;
        				        $history->pd_medication =  $cust->medication;
        				        $history->pd_dosage = $cust->dosage;
        				        $history->pd_comments = $cust->comments ;
        				        $history->pd_isbedarfs = $cust->isbedarfs;
        				        $history->pd_isivmed = $cust->isivmed;
        				        $history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
        				        $history->pd_cocktailid= $cust->cocktailid;
        				        $history->pd_treatment_care = $cust->treatment_care;
        				        $history->pd_isnutrition = $cust->isnutrition;
        				        $history->pd_cocktail_comment = $cocktail->description ;
        				        $history->pd_cocktail_bolus = $cocktail->bolus;
        				        $history->pd_cocktail_flussrate =$cocktail->flussrate;
        				        $history->pd_cocktail_sperrzeit =$cocktail->sperrzeit;
        				        $history->pd_edit_type = $cust->edit_type;
        				        $history->pd_verordnetvon = $cust->verordnetvon;
        				        $history->pd_medication_change = $cust->medication_change;
        				        $history->pd_create_date = $cust->create_date;
        				        $history->pd_create_user = $cust->create_user;
        				        $history->pd_change_date = $cust->change_date;
        				        $history->pd_change_user = $cust->change_user;
        				        $history->pd_isdelete = $cust->isdelete;
        				        $history->save(); */
        				    }
        
        				    // ================= Update patient drugplan item====================
        				    
        				    if($update_sh_medication[$keym] == "1"){
        				        
        				        $insert_at = new PatientDrugPlanAlt();
        				        $insert_at->ipid = $ipid;
        				        $insert_at->drugplan_id = $post['drid'][$keym];
        				        $insert_at->dosage = $post['dosage'][$keym];
        				        $insert_at->dosage_interval = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
        				        $insert_at->medication_master_id = $medid;
        				        $insert_at->isbedarfs = "0";
        				        //$insert_at->iscrisis = "0"; //ispc 1823 ??
        				        $insert_at->isivmed = "0";
        				        if ($cust->isschmerzpumpe == 1)
        				        {
        				            $insert_at->isschmerzpumpe = $cust->isschmerzpumpe;
        				            $insert_at->cocktailid = $cust->cocktailid;
        				        }
        				        
        				        $insert_at->treatment_care = "0";
        				        $insert_at->isnutrition = "0";
        				        $insert_at->isintubated = "0";//ISPC-2176
        				        $insert_at->verordnetvon = $post['verordnetvon'][$keym];
        				        $insert_at->comments = $post['comments'][$keym];
        				        $insert_at->medication_change = $medication_change_date[$keym];
        				        $insert_at->status = "edit";
        				        
        				        $insert_at->save();
        				        $insertedIds[] = $insert_at->id;
        				        
        				        $recordid = $insert_at->id;
        				        
        				        // OLD ENTRY
                                // old medication name
        				        $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
        				        $old_med_name[$keym] = $old_med->name;
        				        
                                // old dosage
        				        if($cust->dosage) {
        				            $old_med_dosage[$keym] = $cust->dosage;
        				        }
        				        
                                // old comment
        				        if($cust->comments ){
        				            $old_med_comments[$keym] = $cust->comments." | ";
        				        }
        				                                                 
                                //  old medication date
        				        if($cust->medication_change != "0000-00-00 00:00:00")
        				        {
        				            $old_med_medication_change[$keym] =  date('d.m.Y',strtotime($cust->medication_change));
        				        }
        				        else
        				        {
        				            if($cust->change_date != "0000-00-00 00:00:00")
        				            {
        				                $old_med_medication_change[$keym] =  date('d.m.Y',strtotime($cust->change_date));
        				            }
        				            else
        				            {
        				                $old_med_medication_change[$keym] =  date('d.m.Y',strtotime($cust->create_date));
        				            }
        				        }
                                
        				        if(strlen($old_med_dosage[$keym])>0){
        				            $old_entry[$keym] = 'Schmerzpumpe '.$old_med_name[$keym].$old_med_dosage[$keym]." ".$old_med_comments[$keym].$old_med_medication_change[$keym];
        				        } else	{
        				            $old_entry[$keym] = 'Schmerzpumpe '.$old_med_name[$keym].$old_med_comments[$keym].$old_med_medication_change[$keym];
        				        }
        
        				        // NEW ENTRY
        				        // new name
        				        $new_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
        				        $new_medication_name[$keym] = $new_med->name;
        				        
        				        // new dosage
        				        $new_medication_dosage[$keym] = $post['dosage'][$keym];
        				        
        				        // new comments
        				        $new_medication_comments[$keym] = $post['comments'][$keym];
        				        
        		                // new change date
        				        if($medication_change_date[$keym] != "0000-00-00 00:00:00"){
        				            $medication_change_date_str[$keym] = date("d.m.Y", strtotime($medication_change_date[$keym]));
        				        }
        				        else
        				        {
        				            $medication_change_date_str[$keym]="";
        				        }
        				        
        				        if(strlen($new_medication_dosage[$keym])>0)
        				        {
        				            $new_entry[$keym] = 'Schmerzpumpe '.$new_medication_name[$keym]."  |  ".$new_medication_dosage[$keym]." | ". $new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
        				        }
        				        else
        				        {
        				            $new_entry[$keym] = 'Schmerzpumpe '.$new_medication_name[$keym]." | ".$new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
        				        }
        				        
        				        $attach = 'OHNE FREIGABE: ' . $old_entry[$keym] . '  -> ' .  $new_entry[$keym].'';
        				        $insert_pc = new PatientCourse();
        				        $insert_pc->ipid = $ipid;
        				        $insert_pc->course_date = date("Y-m-d H:i:s", time());
        				        $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
        				        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
        				        $insert_pc->recordid = $recordid;
        				        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        				        $insert_pc->user_id = $userid;
        				        $insert_pc->save();
        				        
        				        // SEND MESSAGE
        				        $text  = "";
        				        $text .= "Patient ".$patient_name." \n ";
        				        $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
        				        $text .= '' . $old_entry[$keym] . '  -> ' .  $new_entry[$keym].'<br/>';
        				        
        				        $mess = Messages::medication_acknowledge_messages($ipid, $text);
        				        
        				        
        				        // CREATE TODO
        				        $text_todo  = "";
        				        $text_todo .= "Patient ".$patient_name." <br/> ";
        				        $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/> ";
        				        $text_todo .= '' . $old_entry[$keym] . '  -> ' .  $new_entry[$keym].' <br/>';
        				        
        				        $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $post['drid'][$keym], $recordid);
        				    }
        				}
    		        }
    		        else if (!empty($post['medication'][$keym]))
    		        {
    
    		            
    		            $cust = new PatientDrugPlan();
    		            $cust->ipid = $ipid;
    		            $cust->dosage = $post['dosage'][$keym];
    		            $cust->dosage_interval = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
    		            $cust->medication_master_id = $medid;
    		            $cust->verordnetvon = $post['verordnetvon'][$keym];
    		            
    		            // medication_change
    		            if(!empty($post['medication_change'][$keym]))
    		            {
    		                $cust->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
    		            }
    		            elseif(!empty($post['done_date']))
    		            {
    		                $cust->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
    		            }
    		            else
    		            {
    		                $cust->medication_change = date('Y-m-d 00:00:00');
    		            }
    		            
    		            $cust->isbedarfs = 0;
    		            //$cust->iscrisis = 0; //ispc 1823 ??
    		            $cust->isivmed = 0;
    		            $cust->isschmerzpumpe = 1;
    		            $cust->cocktailid = $post['cocktailhid'];
    		            $cust->save();
    		            $inserted_id = $cust->id;
    
    		            
    		            $insert_at = new PatientDrugPlanAlt();
    		            $insert_at->ipid = $ipid;
    		            $insert_at->drugplan_id = $inserted_id;
    		            $insert_at->dosage = $post['dosage'][$keym];
    		            $insert_at->dosage_interval = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
    		            $insert_at->medication_master_id = $medid;
    		            $insert_at->isbedarfs = "0";
    		            //$insert_at->iscrisis = "0"; //ispc 1823 ??
    		            $insert_at->isivmed = "0";
    		            if ($cust->isschmerzpumpe == 1)
    		            {
    		                $insert_at->isschmerzpumpe = $cust->isschmerzpumpe;
    		                $insert_at->cocktailid = $cust->cocktailid;
    		            }
    		            
    		            $insert_at->treatment_care = "0";
    		            $insert_at->isnutrition = "0";
    		            $insert_at->isintubated = "0"; // ISPC-2176
    		            $insert_at->verordnetvon = $post['verordnetvon'][$keym];
    		            $insert_at->comments = $post['comments'][$keym];
    		            $insert_at->medication_change = $medication_change_date[$keym];
    		            $insert_at->status = "new";
    		            $insert_at->save();
    		            $insertedIds[] = $insert_at->id;
    		            
    		            $recordid = $insert_at->id;
    		          
    		            
    		            // NEW ENTRY
    		            // new name
    		            $new_med = Doctrine::getTable('Medication')->find($medid);
    		            $new_medication_name[$keym] = $new_med->name;
    
    		            
    		            // new dosage
    		            $new_medication_dosage[$keym] = $post['dosage'][$keym];
    		            
    		            // new comments
    		            $new_medication_comments[$keym] = $post['comments'][$keym];
    		            
    		            // new change date
    	                $medication_change_date_str[$keym]= date("d.m.Y",time());
    		            
    	                
    		            if(strlen($new_medication_dosage[$keym])>0)
    		            {
    		                $new_entry[$keym] = 'Schmerzpumpe '.$new_medication_name[$keym]."  |  ".$new_medication_dosage[$keym]." | ". $new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
    		            }
    		            else
    		            {
    		                $new_entry[$keym] = 'Schmerzpumpe '.$new_medication_name[$keym]." | ".$new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
    		            }
    		            
    		            
    // 		            $attach = 'OHNE FREIGABE:   -> ' .  $new_entry[$keym].'';
    // 		            $insert_pc = new PatientCourse();
    // 		            $insert_pc->ipid = $ipid;
    // 		            $insert_pc->course_date = date("Y-m-d H:i:s", time());
    // 		            $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
    // 		            $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
    // 		            $insert_pc->recordid = $recordid;
    // 		            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
    // 		            $insert_pc->user_id = $userid;
    // 		            $insert_pc->save();
    
    		            // SEND MESSAGE
    		            $text  = "";
    		            $text .= "Patient ".$patient_name." \n ";
    		            $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
    		            $text .=  "neue Medikation:  " .  $new_entry[$keym]." \n ";
    		            
    		            $mess = Messages::medication_acknowledge_messages($ipid, $text);
    		            
    		            // CREATE TODO
    		            
    		            $text_todo  = "";
    		            $text_todo .= "Patient ".$patient_name." <br/>";
    		            $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
    		            $text_todo .=  "neue Medikation:  " .  $new_entry[$keym]."  <br/>";
    		            
    		            $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $inserted_id, $recordid);
    		        }
    		    }
    		    
    		    
    		    //update cocktailid
    		    $cocktail = Doctrine::getTable('PatientDrugPlanCocktails')->find($cust->cocktailid);
    		    
    		    if(
    		        $post['cocktailDescription'] != $cocktail->description ||
    		        $post['bolus'] != $cocktail->bolus ||
    		        $post['max_bolus'] != $cocktail->max_bolus ||
    		        $post['flussrate'] != $cocktail->flussrate ||
    		        $post['flussrate_type'] != $cocktail->flussrate_type ||       //ISPC-2684 Lore 08.10.2020
    		        $post['sperrzeit'] != $cocktail->sperrzeit
    		        )
    		    {
                    $inser_calt =  new PatientDrugPlanAltCocktails();
                    $inser_calt->ipid = $ipid;
                    $inser_calt->userid = $userid;
                    $inser_calt->clientid = $clientid;
                    $inser_calt->drugplan_cocktailid = $cust->cocktailid;
                    $inser_calt->description = $post['cocktailDescription'];
                    $inser_calt->bolus = $post['bolus'];
                    $inser_calt->max_bolus = $post['max_bolus'];
                    $inser_calt->flussrate = $post['flussrate'];
                    if(isset($post['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
                        $inser_calt->flussrate_type = $post['flussrate_type'];        //ISPC-2684 Lore 08.10.2020
                    }
                    $inser_calt->sperrzeit = $post['sperrzeit'];
                    $inser_calt->pumpe_type = $post['pumpe_type'];
                    $inser_calt->pumpe_medication_type = $post['pumpe_medication_type'];
                    $inser_calt->carrier_solution = $post['carrier_solution'];
                    $inser_calt->status = "edit";
                    $inser_calt->save();
                    
                    $recordid_cocktail_alt = $inser_calt->id;
                    
                    $old_entry="";
                    $old_entry = "Kommentar: " . $cocktail->description."";
                    $old_entry .= "\nApplikationsweg: " .$cocktail->pumpe_medication_type;
                    //$old_entry .= "\nFlussrate: " . $cocktail->flussrate;
                    //ISPC-2684 Lore 08.10.2020
                    if(!empty($cocktail->flussrate_type)){
                        $old_entry .= "\nFlussrate_simple"."(".$cocktail->flussrate_type."): " . $cocktail->flussrate;
                    }else{
                        $old_entry .= "\nFlussrate: " . $cocktail->flussrate;
                    }
                    //.
                    $old_entry .= "\nTrägerlösung: " .$cocktail->carrier_solution;
                    
                    if($cocktail->pumpe_type == "pca")
                    {
                        $old_entry .= "\nBolus: " .$cocktail->bolus;
                        $old_entry .= "\nMax Bolus: " .$cocktail->max_bolus;
                        $old_entry .= "\nSperrzeit: " .$cocktail->sperrzeit;
                    }
                    
                    
                    $new_entry="";
                    $new_entry = "Kommentar: " . $post['cocktailDescription']."";
                    $new_entry .= "\nApplikationsweg: " .$post['pumpe_medication_type'];
                    //$new_entry .= "\nFlussrate: " . $post['flussrate'];
                    //ISPC-2684 Lore 08.10.2020
                    if(!empty($post['flussrate_type'])){
                        $new_entry .= "\nFlussrate_simple"." (".$post['flussrate_type']."): " . $post['flussrate'];
                    }else{
                        $new_entry .= "\nFlussrate: " . $post['flussrate'];
                    }
                    //.
                    $new_entry .= "\nTrägerlösung: " .$post['carrier_solution'];
                    
                    if($post['pumpe_type'] == "pca")
                    {
                        $new_entry .= "\nBolus: " .$post['bolus'];
                        $new_entry .= "\nMax Bolus: " .$post['max_bolus'];
                        $new_entry .= "\nSperrzeit: " .$post['sperrzeit'];
                    }
                    
                    $attach = "OHNE FREIGABE:" . $old_entry . "  \n -> \n  " .  $new_entry."";
                    $insert_pc = new PatientCourse();
                    $insert_pc->ipid = $ipid;
                    $insert_pc->course_date = date("Y-m-d H:i:s", time());
                    $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
                    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
                    $insert_pc->recordid = $recordid_cocktail_alt;
                    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                    $insert_pc->user_id = $userid;
                    $insert_pc->save();
                    
                    
                    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                    // SEND MESSAGE
                    $text  = "";
                    $text .= "Patient ".$patient_name." \n ";
                    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                    $text .= '' . $old_entry . '  -> ' .  $new_entry.'<br/>';
                    
                    $mess = Messages::medication_acknowledge_messages($ipid, $text);
                    
                    
                    // CREATE TODO
                    $text_todo  = "";
                    $text_todo .= "Patient ".$patient_name." <br/> ";
                    $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/> ";
                    $text_todo .= '' . str_replace("\n","<br/>",$old_entry) . '  -> ' .  str_replace("\n","<br/>",$new_entry).' <br/>';
                    
                    $todos = Messages::pump_medication_acknowledge_todo($ipid, $text_todo, $cust->cocktailid, $recordid_cocktail_alt);                    
                    
    		    }
        	} 
    		else
    		{
    		    $misc = "Medication change  Permission Error - Update schmertpumpe";
    		    PatientPermissions::MedicationLogRightsError(false,$misc);
    		} 
		}
		else
		{
		
    		foreach ($post['hidd_medication'] as $keym => $valm)
    		{
    			$update_sh_medication[$keym] = "0";
    
    			if ($post['hidd_medication'][$keym] > 0)
    			{
    				$medid = $post['hidd_medication'][$keym];
    			}
    			else
    			{
    				$medid = $post['newhidd_medication'][$keym];
    			}
    
    			if ($post['drid'][$keym] > 0)
    			{
    				$cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$keym]);
    				if ($cust){
    					if (strtotime( date('d.m.Y',strtotime($cust->medication_change))) != strtotime($post['medication_change'][$keym]) 
    					    || $cust->dosage != $post['dosage'][$keym] 
    					    || $cust->medication_master_id != $medid 
    					    || $cust->verordnetvon != $post['verordnetvon'][$keym]
    					    || (isset($post[$keym]['dosage_interval']) && $cust->dosage_interval != $post[$keym]['dosage_interval'])
    					    )
    					{//check to update only what's modified
    
    						$update_sh_medication[$keym] = "1";
    
    						if(!empty($post['medication_change'][$keym])){
    							//check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
    							if ($cust->dosage != $post['dosage'][$keym] ||
    									$cust->medication_master_id != $medid ||
    									$cust->verordnetvon != $post['verordnetvon'][$keym]
    									|| (isset($post[$keym]['dosage_interval']) && $cust->dosage_interval != $post[$keym]['dosage_interval'])
    							    )
    							{
    
    								if ($post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
    									$medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
    								} elseif ($post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
    									$medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
    								} elseif ($post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
    									$medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
    								} else {
    									$medication_change_date[$keym] = date('Y-m-d 00:00:00');
    								}
    
    								// if no medication details were modified - check in the "last edit date" was edited
    							} else if(
    									( $post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
    									( $post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
    									( $post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->create_date))))) )
    							{
    
    								$medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
    
    							} else if(
    									( $post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
    									( $post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
    									( $post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
    							{
    
    								$update_sh_medication[$keym] = "0";
    							}
    
    							// if "last edit date was edited - save current date"
    						} else {
    							$medication_change_date[$keym] = date('Y-m-d 00:00:00');
    						}
    					} else{
    						$update_sh_medication[$keym] = "0";
    					}
    					/* ================= Save in patient drugplan history ====================*/
    					if(		$cust->dosage != $post['dosage'][$i] ||
    							$cust->medication_master_id != $medid ||
    							$cust->verordnetvon != $post['verordnetvon'][$i]
    					    || (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval'])
    					    ){
    
    
    						$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
    						$medication_old_medication_name[$i] = $old_med->name;
    						$medication_old_medication_id[$i] =  $old_med->id;
    
    						$cocktail = Doctrine::getTable('PatientDrugPlanCocktails')->find($cust->cocktailid);
    						$history = new PatientDrugPlanHistory();
    						$history->ipid = $ipid;
    						$history->pd_id = $cust->id;
    						$history->pd_medication_master_id = $cust->medication_master_id ;
    						$history->pd_medication_name = $medication_old_medication_name[$i] ;
    						$history->pd_medication =  $cust->medication;
    						$history->pd_dosage = $cust->dosage;
    						$history->pd_dosage_interval = isset($cust->dosage_interval) ? $cust->dosage_interval : null;
    						$history->pd_dosage_product = isset($cust->dosage_product) ? $cust->dosage_product : null;
    						$history->pd_comments = $cust->comments ;
    						$history->pd_isbedarfs = $cust->isbedarfs;
    						$history->pd_iscrisis = $cust->iscrisis;//ispc 1823
    						$history->pd_isivmed = $cust->isivmed;
    						$history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
    						$history->pd_cocktailid= $cust->cocktailid;
    						$history->pd_treatment_care = $cust->treatment_care;
    						$history->pd_isnutrition = $cust->isnutrition;
    						$history->pd_isintubated = $cust->isintubated; //ISPC-2176
    						$history->pd_cocktail_comment = $cocktail->description ;
    						$history->pd_cocktail_bolus = $cocktail->bolus;
    						$history->pd_cocktail_max_bolus = $cocktail->max_bolus;
    						$history->pd_cocktail_flussrate =$cocktail->flussrate;
    						$history->pd_cocktail_sperrzeit =$cocktail->sperrzeit;
    						$history->pd_edit_type = $cust->edit_type;
    						$history->pd_verordnetvon = $cust->verordnetvon;
    						$history->pd_medication_change = $cust->medication_change;
    						$history->pd_create_date = $cust->create_date;
    						$history->pd_create_user = $cust->create_user;
    						$history->pd_change_date = $cust->change_date;
    						$history->pd_change_user = $cust->change_user;
    						$history->pd_isdelete = $cust->isdelete;
    						$history->save();
    					}
    
    					/* ================= Update patient drugplan item====================*/
    					if($update_sh_medication[$keym] == "1"){
    						$cust->ipid = $ipid;
    						$cust->dosage = $post['dosage'][$keym];
    						$cust->dosage_interval = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
    						$cust->medication_master_id = $medid;
    						$cust->verordnetvon = $post['verordnetvon'][$keym];
    						$cust->medication_change= $medication_change_date[$keym];
    						$cust->save();
    						
    						
    						// IF An approval user edits a medications that is not approved yet - all data is marcked as inactive for this medication
    						if(in_array($userid,$approval_users) && $acknowledge == "1" )
    						{
    						    $clear = $this->update_pdpa($ipid, $post['drid'][$keym]);
    						}
    							
    						
    					}
    
    				}
    			}
    			else if (!empty($post['medication'][$keym]))
    			{
    				
    				$cust = new PatientDrugPlan();
    				$cust->ipid = $ipid;
    				$cust->dosage = $post['dosage'][$keym];
    				$cust->dosage_interval = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
    				$cust->medication_master_id = $medid;
    				$cust->verordnetvon = $post['verordnetvon'][$keym];
    
    				// medication_change
    				if(!empty($post['medication_change'][$keym]))
    				{
    					$cust->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
    				}
    				elseif(!empty($post['done_date']))
    				{
    					$cust->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
    				}
    				else
    				{
    					$cust->medication_change = date('Y-m-d 00:00:00');
    				}
    
    				$cust->isbedarfs = 0;
    				//$cust->iscrisis = 0; //ipsc 1823 ??
    				$cust->isivmed = 0;
    				$cust->isintubated = 0; //ISPC-2176
    				$cust->isschmerzpumpe = 1;
    				$cust->cocktailid = $post['cocktailhid'];
    				$cust->save();
    			}
    		}
    		//update cocktailid
    		$cust = Doctrine::getTable('PatientDrugPlanCocktails')->find($post['cocktailhid']);
    		$cust->ipid = $ipid;
    		$cust->userid = $userid;
    		$cust->clientid = $clientid;
    		$cust->description = $post['cocktailDescription'];
    		$cust->bolus = $post['bolus'];
    		$cust->max_bolus = $post['max_bolus'];
    		$cust->flussrate = $post['flussrate'];
    		if(isset($post['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
    		  $cust->flussrate_type = $post['flussrate_type'];      //ISPC-2684 Lore 08.10.2020
    		}
    		$cust->sperrzeit = $post['sperrzeit'];
    		$cust->save();
		
		}
	}

	public function UpdateFromAdmissionData($post)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    
	    $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		$change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		
		$modules = new Modules();
		if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
		{
		    $acknowledge = "1";
		    if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA')
		    {
		        
		        $allow_change = "1";
		    }
		    else
		    {
		        $allow_change = "0";
		    }
		} 
		else
		{
		    $acknowledge = "0";
		}

		
		if($acknowledge == "1" && !in_array($userid,$approval_users))//Medication acknowledge
		{
		    if($allow_change == "1")
            {
    	        // get user details
    	        $master_user_details = new User();
    	        $users_details_arr = $master_user_details->getUserDetails($userid);
    	        $users_details = $users_details_arr[0];
    	        $user_name = $users_details['first_name'].' '.$users_details['last_name'];
    	        
    	        // get patient details
    	        $patient_details = PatientMaster::get_multiple_patients_details(array( $post['ipid']));
    	        $patient_name = $patient_details[ $post['ipid']]['first_name'] . ', ' . $patient_details[ $post['ipid']]['last_name'];
    	         
    	        
    	        foreach ($post['medication'] as $i => $value)
        		{
        			$update_medication[$i] = "0";
        			
        			if (strlen($post['medication'][$i]) > 0)
        			{
        				if ($post['drid'][$i] > 0)
        				{
        
        					if ($post['hidd_medication'][$i] == "")
        					{
        						$post['hidd_medication'][$i] = $post['newhidd_medication'][$i];
        					}
        					
        					$cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$i]);
        					if($cust){
        						if ($cust->dosage != $post['dosage'][$i] 
        						    ||	$cust->medication_master_id != $post['hidd_medication'][$i] 
        						    ||	$cust->comments != $post['comments'][$i]
        						    || (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval']) 
        						    )
        						{
        							$update_medication[$i] = "1";
        						} else{
        							$update_medication[$i] = "0";
        						}
        						
        						/* ================= Update patient drugplan item ====================*/
        						if($update_medication[$i] == "1"){
         						    
        						    $clear = $this->update_pdpa($post['ipid'], $post['drid'][$i]);
        						    
        						    $insert_at = new PatientDrugPlanAlt();
        						    $insert_at->ipid =  $post['ipid'];
        						    $insert_at->drugplan_id = $post['drid'][$i];
        						    $insert_at->dosage = $post['dosage'][$i];
        						    $insert_at->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
        						    $insert_at->medication_master_id = $post['hidd_medication'][$i];
        						    $insert_at->comments = $post['comments'][$i];
        						    $insert_at->medication_change = date('Y-m-d 00:00:00');
        						    $insert_at->status = "edit";
        						    $insert_at->save();
        						    $insertedIds[] = $insert_at->id;
        						    $recordid = $insert_at->id;
        						    
        						    
        						    
        						    // OLD ENTRY
        						    // old medication name
        						    $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
        						    $old_med_name[$i] = $old_med->name;
        						    
        						    // old dosage
        						    if($cust->dosage) {
        						        $old_med_dosage[$i] = $cust->dosage;
        						    }
        						    
        						    // old comment
        						    if($cust->comments ){
        						        $old_med_comments[$i] = $cust->comments." | ";
        						    }
        						    	
        						    //  old medication date
        						    if($cust->medication_change != "0000-00-00 00:00:00")
        						    {
        						        $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->medication_change));
        						    }
        						    else
        						    {
        						        if($cust->change_date != "0000-00-00 00:00:00")
        						        {
        						            $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->change_date));
        						        }
        						        else
        						        {
        						            $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->create_date));
        						        }
        						    }
        						    
        						    if(strlen($old_med_dosage[$i])>0){
        						        $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
        						    } else	{
        						        $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
        						    }
        						    
        						    // NEW ENTRY
        						    // new name
        						    $new_med = Doctrine::getTable('Medication')->find($post['hidd_medication'][$i]);
        						    $new_medication_name[$i] = $new_med->name;
        						    
        						    // new dosage
        						    $new_medication_dosage[$i] = $post['dosage'][$i];
        						    
        						    // new comments
        						    $new_medication_comments[$i] = $post['comments'][$i];
        						    
        						    // new change date
        						    if($medication_change_date[$i] != "0000-00-00 00:00:00"){
        						        $medication_change_date_str[$i] = date("d.m.Y", strtotime($medication_change_date[$i]));
        						    }
        						    else
        						    {
        						        $medication_change_date_str[$i]=date("d.m.Y",time());
        						    }
        						    
        						    if(strlen($new_medication_dosage[$i])>0)
        						    {
        						        $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        						    }
        						    else
        						    {
        						        $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        						    }
        						    
        						    if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        						    {
        						        $shortcut = "N";
        						    }
        						    elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        						    {
        						        $shortcut = "I";
        						    }
        						    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 )
        						    {
        						        $shortcut = "Q";
        						        $prefix = "Schmerzpumpe ";
        						    }
        						    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1 )
        						    {
        						        $shortcut = "BP";
        						    }
        						    elseif($post['iscrisis'] == 1 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        						    {
        						    	$shortcut = "KM";
        						    }
        						    elseif($post['isintubated'] == 1 && $post['iscrisis'] == 0 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        						    {// ISPC-2176
        						    	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
        						    }        						    
        						    else
        						    {
        						        $shortcut = "M";
        						    }
        						    
        						    $attach = 'OHNE FREIGABE: ' . $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
        						    $insert_pc = new PatientCourse();
        						    $insert_pc->ipid =  $post['ipid'];
        						    $insert_pc->course_date = date("Y-m-d H:i:s", time());
//         						    $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
        						    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
        						    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_edit_med");
        						    $insert_pc->recordid = $recordid;
        						    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        						    $insert_pc->user_id = $userid;
        						    $insert_pc->save();
        						    
        						    // SEND MESSAGE
        						    $text  = "";
        						    $text .= "Patient ".$patient_name." \n ";
        						    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
        						    $text .= '' . $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
        						    $mess = Messages::medication_acknowledge_messages($post['ipid'],$text);
        						    
        						    
        						    // CREATE TODO
        						    $text_todo  = "";
        						    $text_todo .= "Patient ".$patient_name." <br/>";
        						    $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
        						    $text_todo .= '' . $old_entry[$i] . '  -> ' .  $new_entry[$i].' <br/>';    					
        						    	    
        						    $todos = Messages::medication_acknowledge_todo($post['ipid'], $text_todo, $post['drid'][$i], $recordid);
        						    
        						}
        					}
        				}
        				else
        				{
        				    if ($post['hidd_medication'][$i] == "")
        				    {
        				        $post['hidd_medication'][$i] = $post['newhidd_medication'][$i];
        				    }
        				        
        				    $medication_change[$i] = date('Y-m-d 00:00:00');
        				    
        				    $ins_pat_drug_plan = new PatientDrugPlan();
        				    $ins_pat_drug_plan->ipid = $post['ipid'];
        				    $ins_pat_drug_plan->dosage = $post['dosage'][$i];
        				    $ins_pat_drug_plan->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
        				    $ins_pat_drug_plan->comments = $post['comments'][$i];
        				    $ins_pat_drug_plan->medication_master_id = $post['hidd_medication'][$i];
        				    $ins_pat_drug_plan->isbedarfs = $post['isbedarfs'];
        				    $ins_pat_drug_plan->iscrisis = $post['iscrisis'];
        				    $ins_pat_drug_plan->isintubated = $post['isintubated'];
        				    $ins_pat_drug_plan->medication_change = date('Y-m-d 00:00:00');
        				    $ins_pat_drug_plan->save();
        				    $inserted_id = $ins_pat_drug_plan->id;
        				    
        				    
        				    $cust = new PatientDrugPlanAlt();
        				    $cust->ipid = $post['ipid'];
        				    $cust->drugplan_id = $inserted_id;
        				    $cust->dosage = $post['dosage'][$i];
        				    $cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
        				    $cust->medication_master_id = $post['hidd_medication'][$i];
        				    $cust->isbedarfs = $post['isbedarfs'];
        				    $cust->iscrisis = $post['iscrisis'];
        				    $cust->isintubated = $post['isintubated'];
        				    $cust->comments = $post['comments'][$i];
       				        $cust->medication_change = date('Y-m-d 00:00:00');
        				    $cust->status = "new";
        				    $cust->save();
        				    $recordid = $cust->id;
        				    
        				    
        				    // NEW ENTRY
        				    if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				    {
        				        $shortcut = "N";
        				    }
        				    elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				    {
        				        $shortcut = "I";
        				    }
        				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 )
        				    {
        				        $shortcut = "Q";
        				        $prefix = "Schmerzpumpe ";
        				    }
        				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1 )
        				    {
        				        $shortcut = "BP";
        				    }
        				    elseif($post['iscrisis'] == 1 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				    {
        				    	$shortcut = "KM";
        				    }
        				    elseif($post['isintubated'] == 1 && $post['iscrisis'] == 0 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				    {// ISPC-2176
        				    	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
        				    }        				    
        				    else
        				    {
        				        $shortcut = "M";
        				    }
        				    
        				    
        				    // new name
        				    $new_med = Doctrine::getTable('Medication')->find($post['hidd_medication'][$i]);
        				    $new_medication_name[$i] = $new_med->name;
        				    
        				    // new dosage
        				    $new_medication_dosage[$i] = $post['dosage'][$i];
        				    
        				    // new comments
        				    $new_medication_comments[$i] = $post['comments'][$i];
        				    
        				    // new change date
       				        $medication_change_date_str[$i] = date('d.m.Y',time());
    
       				        
        				    if(strlen($new_medication_dosage[$i])>0)
        				    {
        				        $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        				    }
        				    else
        				    {
        				        $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        				    }
        				    
        				    $attach = 'OHNE FREIGABE: ' .  $new_entry[$i].'';
        				    $insert_pc = new PatientCourse();
        				    $insert_pc->ipid = $ipid;
        				    $insert_pc->course_date = date("Y-m-d H:i:s", time());
//         				    $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
        				    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
        				    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_new_med");
        				    $insert_pc->recordid = $recordid;
        				    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        				    $insert_pc->user_id = $userid;
        				    $insert_pc->save();
    
        				    
        				    // SEND MESSAGE
        				    $text  = "";
        				    $text .= "Patient ".$patient_name." \n ";
        				    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
        				    $text .= 'neue Medikation: ' .  $new_entry[$i].'';
        				    $mess = Messages::medication_acknowledge_messages($post['ipid'],$text);
    
        				    // CREATE TODO
        				    $text_todo  = "";
        				    $text_todo .= "Patient ".$patient_name." <br/>";
        				    $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
        				    $text_todo .= 'neue Medikation: ' .  $new_entry[$i].' <br/>';
        				        				    
        				    $todos = Messages::medication_acknowledge_todo($post['ipid'], $text_todo, $inserted_id, $recordid);
        				}
        			}
        		}
    		} 
    		else
    		{
                $misc = "Medication change  Permission Error -  Update from Admission";
    		    PatientPermissions::MedicationLogRightsError(false,$misc);
    		}
	    }
	    else 
	    { // !!!!!!!!!! VERSION WITH NO - Medical acknowledge function !!!!!!!!!!!
            
	        foreach ($post['medication'] as $i => $value)
    		{
    			$update_medication[$i] = "0";
    			
    			if (strlen($post['medication'][$i]) > 0)
    			{
    				if ($post['drid'][$i] > 0)
    				{
    
    					if ($post['hidd_medication'][$i] == "")
    					{
    						$post['hidd_medication'][$i] = $post['newhidd_medication'][$i];
    					}
    					
    					$cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$i]);
    					if($cust){
    						if ($cust->dosage != $post['dosage'][$i] 
    						    ||	$cust->medication_master_id != $post['hidd_medication'][$i] 
    						    ||	$cust->comments != $post['comments'][$i]
    						    || (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval'])
    						    
    						    )
    						{
    							$update_medication[$i] = "1";
    						} else{
    							$update_medication[$i] = "0";
    						}
    						
    						/* ================= Update patient drugplan item ====================*/
    						if($update_medication[$i] == "1"){
//     							$cust->ipid = $post['ipid'];
//     							$cust->dosage = $post['dosage'][$i];
//     							$cust->medication_master_id = $post['hidd_medication'][$i];
//     							$cust->comments = $post['comments'][$i];
//     							$cust->medication_change = date('Y-m-d 00:00:00');
//     							$cust->save();
    							
    							
    							// IF An approval user edits a medications that is not approved yet - all data is marcked as inactive for this medication
    							if(in_array($userid,$approval_users) && $acknowledge == "1" )
    							{
    							    $clear = $this->update_pdpa($post['ipid'],  $post['drid'][$i] );
    							        	
    							        if( $cust->isivmed == 0 &&  $cust->isbedarfs == 1 &&  $cust->isschmerzpumpe == 0 &&  $cust->treatment_care == 0 )
    							        {
    							            $shortcut = "N";
    							        }
    							        elseif($cust->isivmed  == 1 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
    							        {
    							            $shortcut = "I";
    							        }
    							        elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 1 &&  $cust->treatment_care== 0 )
    							        {
    							            $shortcut = "Q";
    							            $prefix = "Schmerzpumpe ";
    							        }
    							        elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 1 )
    							        {
    							            $shortcut = "BP";
    							        }
    							        elseif($cust->iscrisis == 1 &&  $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
    							        {
    							        	$shortcut = "KM";
    							        }
    							        elseif($cust->isintubated == 1 &&  $cust->iscrisis == 0 &&  $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
    							        {// ISPC-2176
    							        	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
    							        }
    							        else
    							        {
    							            $shortcut = "M";
    							        }
    							         
    							        // OLD ENTRY
    							        // old medication name
    							        $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
    							        $old_med_name[$i] = $old_med->name;
    							         
    							        // old dosage
    							        if($cust->dosage) {
    							            $old_med_dosage[$i] = $cust->dosage;
    							        }
    							         
    							        // old comment
    							        if($cust->comments ){
    							            $old_med_comments[$i] = $cust->comments." | ";
    							        }
    							    
    							        //  old medication date
    							        if($cust->medication_change != "0000-00-00 00:00:00")
    							        {
    							            $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->medication_change));
    							        }
    							        else
    							        {
    							            if($cust->change_date != "0000-00-00 00:00:00")
    							            {
    							                $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->change_date));
    							            }
    							            else
    							            {
    							                $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->create_date));
    							            }
    							        }
    							         
    							        if(strlen($old_med_dosage[$i])>0){
    							            $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
    							        } else	{
    							            $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
    							        }
    							         
    							        // NEW ENTRY
    							        // new name
    							        $new_med = Doctrine::getTable('Medication')->find($post['hidd_medication'][$i]);
    							        $new_medication_name[$i] = $new_med->name;
    							         
    							        // new dosage
    							        $new_medication_dosage[$i] = $post['dosage'][$i];
    							         
    							        // new comments
    							        $new_medication_comments[$i] = $post['comments'][$i];
    							         
    							        // new change date
    							        if($medication_change_date[$i] != "0000-00-00 00:00:00"){
    							            $medication_change_date_str[$i] = date("d.m.Y", strtotime($medication_change_date[$i]));
    							        }
    							        else
    							        {
    							            $medication_change_date_str[$i]="";
    							        }
    							         
    							        if(strlen($new_medication_dosage[$i])>0)
    							        {
    							            $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
    							        }
    							        else
    							        {
    							            $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
    							        }
    							         
    							        $attach = 'Änderung: ' . $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
    							        $insert_pc = new PatientCourse();
    							        $insert_pc->ipid = $post['ipid'];
    							        $insert_pc->course_date = date("Y-m-d H:i:s", time());
    							        $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
    							        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
    							        $insert_pc->recordid = $post['drid'][$i];
    							        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
    							        $insert_pc->user_id = $userid;
    							        $insert_pc->save();
    							    
    							}
    							
    							$cust->ipid = $post['ipid'];
    							$cust->dosage = $post['dosage'][$i];
    							$cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
    							$cust->medication_master_id = $post['hidd_medication'][$i];
    							$cust->comments = $post['comments'][$i];
    							$cust->medication_change = date('Y-m-d 00:00:00');
    							$cust->save();    							
    							
    						}
    					}
    				}
    				else
    				{
    
    					if ($post['hidd_medication'][$i] == "")
    					{
    						$post['hidd_medication'][$i] = $post['newhidd_medication'][$i];
    					}
     
    					
    					$cust = new PatientDrugPlan();
    					$cust->ipid = $post['ipid'];
    					$cust->dosage = $post['dosage'][$i];
    					$cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
    					$cust->comments = $post['comments'][$i];
    					$cust->medication_master_id = $post['hidd_medication'][$i];
    					$cust->isbedarfs = $post['isbedarfs'];
    					$cust->iscrisis = $post['iscrisis'];
    					$cust->isintubated = $post['isintubated'];
    					$cust->medication_change = date('Y-m-d 00:00:00');
    					$cust->save();
    					$inserted_id = $cust->id;
    					
    					// this is for  Medication acknowledge
    					if(in_array($userid,$approval_users) && $acknowledge == "1" ){
    					    // NEW ENTRY
    					    if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    					    {
    					        $shortcut = "N";
    					    }
    					    elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    					    {
    					        $shortcut = "I";
    					    }
    					    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 )
    					    {
    					        $shortcut = "Q";
    					        $prefix = "Schmerzpumpe ";
    					    }
    					    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1 )
    					    {
    					        $shortcut = "BP";
    					    }
    					    elseif($post['iscrisis'] == 1 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    					    {
    					    	$shortcut = "KM";
    					    }
    					    elseif($post['isintubated'] == 1 && $post['iscrisis'] == 0 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
    					    {// ISPC-2176
    					    	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
    					    }    					    
    					    else
    					    {
    					        $shortcut = "M";
    					    }
    					    
    					    // new name
   					        $new_med = Doctrine::getTable('Medication')->find($post['hidd_medication'][$i]);
    					    $new_medication_name[$i] = $new_med->name;
    					
    					    
    					    // new dosage
    					    $new_medication_dosage[$i] = $post['dosage'][$i];
    					
    					    // new comments
    					    $new_medication_comments[$i] = $post['comments'][$i];
    					
    					    // new change date
    					    $medication_change_date_str[$i]= date("d.m.Y",time());
    					
    					    if(strlen($new_medication_dosage[$i])>0)
    					    {
    					        $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
    					    }
    					    else
    					    {
    					        $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
    					    }
    					
    					
    					    $attach = $new_entry[$i].'';
    					    $insert_pc = new PatientCourse();
    					    $insert_pc->ipid = $post['ipid'];
    					    $insert_pc->course_date = date("Y-m-d H:i:s", time());
    					    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
    					    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
    					    $insert_pc->recordid = $inserted_id;
    					    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
    					    $insert_pc->user_id = $userid;
    					    $insert_pc->save();
    					
    					
    					}
    					
    					
    					
    				}
    			}
    			
    		}
		
	    }
	}
	
	public function UpdateFromAdmissionData_old($post)
	{
		foreach ($post['medication'] as $i => $value)
		{
			if (strlen($post['medication'][$i]) > 0)
			{
				if ($post['drid'][$i] > 0)
				{
	
					if ($post['hidd_medication'][$i] == "")
					{
						$post['hidd_medication'][$i] = $post['newhidd_medication'][$i];
					}
					$cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$i]);
					$cust->dosage = $post['dosage'][$i];
					$cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
					$cust->medication_master_id = $post['hidd_medication'][$i];
					$cust->comments = $post['comments'][$i];
					$cust->isbedarfs = $post['isbedarfs'];
					$cust->iscrisis = $post['iscrisis'];
					$cust->medication_change = date('Y-m-d 00:00:00');
					$cust->save();
				}
				else
				{
	
					if ($post['hidd_medication'][$i] == "")
					{
						$post['hidd_medication'][$i] = $post['newhidd_medication'][$i];
					}
	
					$cust = new PatientDrugPlan();
					$cust->ipid = $post['ipid'];
					$cust->dosage = $post['dosage'][$i];
					$cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
					$cust->comments = $post['comments'][$i];
					$cust->medication_master_id = $post['hidd_medication'][$i];
					$cust->isbedarfs = $post['isbedarfs'];
					$cust->iscrisis = $post['iscrisis'];
					$cust->medication_change = date('Y-m-d 00:00:00');
					$cust->save();
				}
			}
		}
	}
	
	public function InsertNewData($post)
	{
	    // NOT USED !!!!
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
		 
		if (strlen($post['hidd_medication']) > 0)
		{
			$meds = new PatientDrugPlan();
			$meds->medication_master_id = $post['hidd_medication'];
			$meds->ipid = $post['ipid'];
			$meds->dosage = $post['dosage'];
			$meds->dosage_interval = isset($post['dosage_interval']) ? $post['dosage_interval'] : null;;
			$meds->isbedarfs = $post['isbedarfs'];
			$meds->iscrisis = $post['iscrisis'];
			$meds->comments = $post['comments'];
			$meds->verordnetvon = $post['verordnetvon'];
			$meds->medication_change = date('Y-m-d 00:00:00');
			$meds->save();
		}

		if (strlen($post['newhidd_medication']) > 0)
		{
			$pcarr = explode("|", $post['medication']);
			if (count($pcarr) > 0)
			{
				$dosage = $pcarr[1];
			}
			else
			{
				$dosage = $post['dosage'];
			}
			$meds = new PatientDrugPlan();
			$meds->medication_master_id = $post['newhidd_medication'];
			$meds->ipid = $post['ipid'];
			$meds->dosage = $post['dosage'];
			$meds->dosage_interval = isset($post['dosage_interval']) ? $post['dosage_interval'] : null;
			$meds->isbedarfs = $post['isbedarfs'];
			$meds->iscrisis = $post['iscrisis'];
			$meds->verordnetvon = $post['verordnetvon'];
			$meds->comments = $post['comments'];
			$meds->medication_change = date('Y-m-d 00:00:00');
			$meds->save();
		}
	}

	public function UpdateMultiDataMedicationsVerlauf($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$decid = Pms_Uuid::decrypt($_REQUEST['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		
        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
		$change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
		
		$modules = new Modules();
		if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
		{
		    $acknowledge = "1";
		    if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA')
		    {
		        
		        $allow_change = "1";
		    }
		    else
		    {
		        $allow_change = "0";
		    }
		} 
		else
		{
		    $acknowledge = "0";
		}

		
		if($acknowledge == "1" && !in_array($userid,$approval_users))//Medication acknowledge
		{
		    if($allow_change == "1")
    		{
    		    // get user details
    		    $master_user_details = new User();
    		    $users_details_arr = $master_user_details->getUserDetails($userid);
    		    $users_details = $users_details_arr[0];
    		    $user_name = $users_details['first_name'].' '.$users_details['last_name'];
    		     
    		    // get patient details
    		    $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
    		    $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
    		    
    		    
    		    
    		    
    		    foreach ($post['id_medication'] as $i => $med_item)
    		    {
    		        if ($post['course_type'][$i] == 'P')
    		        {
                        if (empty($post['delete'][$post['drid'][$i]]))
        				{
        				    $update_medication[$i] = "0";
        				    //medication master medi name changed -> new entry in medi master
        				    $cust_mm = Doctrine::getTable('Medication')->find($med_item);
        				    
        				    if (trim($post['medication'][$i]) != trim($cust_mm->name))
        				    {
        				        $med = new Medication();
        				        $med->name = $post['medication'][$i];
        				        $med->extra = 1;
        				        $med->clientid = $clientid;
        				        $med->save();
        				        $med_master_id[$i] = $med->id;
        				    }
        				    
        				    if($post['drid'][$i]){// check if any medication is in post
        				        //edit patient drug plan and edit medication_master id if medi name is changed
        				        $cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$i]);
        				        if($cust)
        				        {
        				        if (
        				            strtotime(date('d.m.Y',strtotime($cust->medication_change))) != strtotime($post['medication_change'][$i]) ||
        				            $cust->dosage != $post['dosage'][$i] ||
        				            $post['medication'][$i] != $cust_mm->name ||
        				            $cust->comments != $post['comments'][$i]
        				            || (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval'])
        				            
        				            )
        				        { //check to update only if something was changed (dosage or comment or medi master name and id)
        				            $update_medication[$i] = "1";
  
    
        				            //medication-change code of hell
        				            if(!empty($post['medication_change'][$i])){
    
        				                //check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
        				                if ($cust->dosage != $post['dosage'][$i] ||
        				                    $post['medication'][$i] != $cust_mm->name ||
        				                    $cust->comments != $post['comments'][$i]
        				                    || (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval']) 
        				                    )
        				                {
        				                    if ($post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
        				                        $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
        				                    } elseif ($post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
        				                        $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
        				                    } elseif ($post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
        				                        $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
        				                    } else {
        				                        $medication_change_date[$i] = date('Y-m-d 00:00:00');
        				                    }
    
        				                    // if no medication details were modified - check in the "last edit date" was edited
        				                } else if(
        				                    ( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
        				                    ( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
        				                    ( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
        				                {
    
        				                    $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    
        				                } else if(
        				                    ( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
        				                    ( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
        				                    ( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) )
        				                )
        				                {
        				                    $update_medication[$i] = "0";
        				                }
        				                // if "last edit date was edited - save current date"
        				            } else {
        				                $medication_change_date[$i] = date('Y-m-d 00:00:00');
        				            }
        			 
        				            if($update_medication[$i] == "1")
        				            {
                                        $clear_existing_alt = $this->update_pdpa($ipid, $cust->id);
        				                
        				                $insert_at = new PatientDrugPlanAlt();
        				                $insert_at->ipid = $ipid;
        				                $insert_at->medication =$post['medication'][$i];
        				                $insert_at->drugplan_id = $cust->id;
        				                $insert_at->dosage = $post['dosage'][$i];
        				                $insert_at->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
        				                
        				                if (!empty($med_master_id[$i]))
        				                {
        				                    $insert_at->medication_master_id = $med_master_id[$i];
        				                } 
        				                else
        				                {
        				                    $insert_at->medication_master_id = $cust->medication_master_id;
        				                }
        				                $insert_at->isbedarfs = $cust->isbedarfs;
        				                $insert_at->iscrisis = $cust->iscrisis;
        				                $insert_at->isivmed = $cust->isivmed;
        				                $insert_at->isintubated = $cust->isintubated;
        				                $insert_at->comments = $post['comments'][$i];
        				                $insert_at->edit_type = $post['course_type'][$i];;
        				                $insert_at->medication_change = $medication_change_date[$i];
        				                $insert_at->status = "edit";
        				                $insert_at->save();
        				                $recordid = $insert_at->id;

        				                // OLD ENTRY
        				                // old medication name
        				                $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
        				                $old_med_name[$i] = $old_med->name;
        				                
        				                // old dosage
        				                if($cust->dosage) {
        				                    $old_med_dosage[$i] = $cust->dosage;
        				                }
        				                
        				                // old comment
        				                if($cust->comments ){
        				                    $old_med_comments[$i] = $cust->comments." | ";
        				                }
        				                	
        				                //  old medication date
        				                if($cust->medication_change != "0000-00-00 00:00:00")
        				                {
        				                    $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->medication_change));
        				                }
        				                else
        				                {
        				                    if($cust->change_date != "0000-00-00 00:00:00")
        				                    {
        				                        $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->change_date));
        				                    }
        				                    else
        				                    {
        				                        $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->create_date));
        				                    }
        				                }
        				                
        				                if(strlen($old_med_dosage[$i])>0){
        				                    $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
        				                } else	{
        				                    $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
        				                }
        				                
        				                // NEW ENTRY
        				                // new name
        				                if (!empty($med_master_id[$i]))
        				                {
	        				                $new_med = Doctrine::getTable('Medication')->find($med_master_id[$i]);
        				                } 
        				                else
        				                {
	        				                $new_med = Doctrine::getTable('Medication')->find( $cust->medication_master_id );
        				                }
        				                $new_medication_name[$i] = $new_med->name;
        				                
        				                // new dosage
        				                $new_medication_dosage[$i] = $post['dosage'][$i];
        				                
        				                // new comments
        				                $new_medication_comments[$i] = $post['comments'][$i];
        				                
        				                // new change date
        				                if($medication_change_date[$i] != "0000-00-00 00:00:00"){
        				                    $medication_change_date_str[$i] = date("d.m.Y", strtotime($medication_change_date[$i]));
        				                }
        				                else
        				                {
        				                    $medication_change_date_str[$i]="";
        				                }
        				                
        				                if(strlen($new_medication_dosage[$i])>0)
        				                {
        				                    $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        				                }
        				                else
        				                {
        				                    $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
        				                }
        				                
        				                if($cust->isivmed == "0" && $cust->isbedarfs == "1")
        				                {
        				                    $shortcut = "N";
        				                }
        				                elseif($cust->isivmed == "1" && $cust->isbedarfs == "0")
        				                {
        				                    $shortcut = "I";
        				                }
        				                elseif($cust->iscrisis == "1")
        				                {
        				                	$shortcut = "KM";
        				                }
        				                elseif($cust->isintubated == "1")
        				                {
        				                	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
        				                }
        				                else
        				                {
        				                    $shortcut = "M";
        				                }
        				                
        				                $attach = 'OHNE FREIGABE: ' . $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
        				                $insert_pc = new PatientCourse();
        				                $insert_pc->ipid =  $ipid;
        				                $insert_pc->course_date = date("Y-m-d H:i:s", time());
//         				                $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
        				                $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
        				                $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_edit_med");
        				                $insert_pc->recordid = $recordid;
        				                $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        				                $insert_pc->user_id = $userid;
        				                $insert_pc->save();
        				                
        				                // SEND MESSAGE
        				                $text  = "";
        				                $text .= "Patient ".$patient_name." \n ";
        				                $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
        				                $text .= $old_entry[$i] . "  -> " .  $new_entry[$i]." \n ";
        				                
        				                $mess = Messages::medication_acknowledge_messages($ipid, $text);
        				                
        				                
        				                // CREATE TODO
        				                $text_todo  = "";
        				                $text_todo .= "Patient ".$patient_name." <br/>";
        				                $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
        				                $text_todo .= $old_entry[$i] . "  -> " .  $new_entry[$i]." <br/>";
        				                
        				                $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $cust->id, $recordid);
        				            }
        				        }
        				    }
        				        
        				    }
        				}
        				else
        				{
        				    $cust_del = Doctrine::getTable('PatientDrugPlan')->find($post['delete'][$post['drid'][$i]]);
        				    if ($cust_del->id)
        				    {
    							// DELETE from verlauf

        				        $insert_at = new PatientDrugPlanAlt();
        				        $insert_at->ipid = $ipid;
        				        $insert_at->drugplan_id = $cust_del->id;
        				        $insert_at->dosage = $cust_del->dosage;
        				        $insert_at->dosage_interval = isset($cust_del->dosage_interval) ? $cust_del->dosage_interval : null;
        				        $insert_at->medication_master_id = $cust_del->medication_master_id;
        				        $insert_at->isbedarfs =$cust_del->isbedarfs;
        				        $insert_at->iscrisis =$cust_del->iscrisis;
        				        $insert_at->isivmed = $cust_del->isivmed;
        				        $insert_at->isschmerzpumpe = $cust_del->isschmerzpumpe;
        				        $insert_at->cocktailid = $cust_del->cocktailid;
        				        $insert_at->treatment_care = $cust_del->treatment_care;
        				        $insert_at->isnutrition = $cust_del->isnutrition;
        				        $insert_at->isintubated = $cust_del->isintubated;
        				        $insert_at->verordnetvon = $cust_del->verordnetvon;
        				        $insert_at->comments = $cust_del->comments;
        				        $insert_at->medication_change =  $cust_del->medication_change;
        				        $insert_at->status =  "delete";
        				        $insert_at->save();
        				        $recordid = $insert_at->id;
        				        
        				        // OLD ENTRY
        				        // old medication name
        				        $old_med = Doctrine::getTable('Medication')->find($cust_del->medication_master_id );
        				        $old_med_name = $old_med->name;
        				        	
        				        // old dosage
        				        if($mod->dosage) {
        				        	$old_med_dosage = $mod->dosage;
        				        }
        				        	
        				        // old comment
        				        if($cust_del->comments ){
        				        	$old_med_comments = $cust_del->comments." | ";
        				        }
        				        
        				        //  old medication date
        				        if($cust_del->medication_change != "0000-00-00 00:00:00")
        				        {
        				        	$old_med_medication_change =  date('d.m.Y',strtotime($cust_del->medication_change));
        				        }
        				        else
        				        {
        				        	if($cust_del->change_date != "0000-00-00 00:00:00")
        				        	{
        				        		$old_med_medication_change =  date('d.m.Y',strtotime($cust_del->change_date));
        				        	}
        				        	else
        				        	{
        				        		$old_med_medication_change =  date('d.m.Y',strtotime($cust_del->create_date));
        				        	}
        				        }
        				        	
        				        if(strlen($old_med_dosage)>0){
        				        	$old_entry = $prefix.$old_med_name.' | '.$old_med_dosage." | ".$old_med_comments.$old_med_medication_change;
        				        } else	{
        				        	$old_entry = $prefix.$old_med_name.' | '.$old_med_comments.$old_med_medication_change;
        				        }
        				        
        				        // SEND MESSAGE
        				        $text  = "";
        				        $text .= "Patient ".$patient_name." \n ";
        				        $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
        				        $text .= "Löschung: ".$old_entry . "   \n ";
        				        $mess = Messages::medication_acknowledge_messages($ipid,$text);
        				         
        				        // CREATE TODO
        				        
        				        $text_todo  = "";
        				        $text_todo .= "Patient ".$patient_name." <br/>";
        				        $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
        				        $text_todo .= "Löschung: ".$old_entry . "   <br/>";
        				        
        				        $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $mid, $recordid);
        				    }
        				}
    		        }
    		    }
    		} 
    		else
    		{
    		    $misc = "Medication change  Permission Error - Update from verlauf";
    		    PatientPermissions::MedicationLogRightsError(false,$misc);   
    		}
		}
		else 
    	{ // !!!!!!!!!! VERSION WITH NO - Medical acknowledge function !!!!!!!!!!!
    	    
    		foreach ($post['id_medication'] as $i => $med_item)
    		{
    			if ($post['course_type'][$i] == 'P')
    			{
    				if (empty($post['delete'][$post['drid'][$i]]))
    				{
    					$update_medication[$i] = "0";
    					//medication master medi name changed -> new entry in medi master
    					$cust_mm = Doctrine::getTable('Medication')->find($med_item);
    					if ($post['medication'][$i] != $cust_mm->name)
    					{
    						$med = new Medication();
    						$med->name = $post['medication'][$i];
    						$med->extra = 1;
    						$med->clientid = $clientid;
    						$med->save();
    						$med_master_id = $med->id;
    					}
    
    					if($post['drid'][$i]){// check if any medication is in post
    						//edit patient drug plan and edit medication_master id if medi name is changed
    						$cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$i]);
    							
    						if (
    								strtotime(date('d.m.Y',strtotime($cust->medication_change))) != strtotime($post['medication_change'][$i]) ||
    								$cust->dosage != $post['dosage'][$i] ||
    								$post['medication'][$i] != $cust_mm->name ||
    								$cust->comments != $post['comments'][$i]
    						    || (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval'])
    						    )
    						{ //check to update only if something was changed (dosage or comment or medi master name and id)
    							$update_medication[$i] = "1";
    
    							//medication-change code of hell
    							if(!empty($post['medication_change'][$i])){
    
    								//check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
    								if ($cust->dosage != $post['dosage'][$i] ||
    										$post['medication'][$i] != $cust_mm->name ||
    										$cust->comments != $post['comments'][$i]
    								    || (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval'])
    								    )
    								{
    									if ($post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
    										$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    									} elseif ($post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
    										$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    									} elseif ($post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
    										$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    									} else {
    										$medication_change_date[$i] = date('Y-m-d 00:00:00');
    									}
    
    									// if no medication details were modified - check in the "last edit date" was edited
    								} else if(
    										( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
    										( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
    										( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
    								{
    
    									$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    
    								} else if(
    										( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
    										( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
    										( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) )
    								)
    								{
    									$update_medication[$i] = "0";
    								}
    								// if "last edit date was edited - save current date"
    							} else {
    								$medication_change_date[$i] = date('Y-m-d 00:00:00');
    							}
    							/* ================= Save in patient drugplan history ====================*/
    							if(	$cust->dosage != $post['dosage'][$i] ||
    									$cust->medication_master_id != $medid ||
    									$cust->verordnetvon != $post['verordnetvon'][$i] ||
    									$cust->comments != $post['comments'][$i]
    							    || (isset($post[$i]['dosage_interval']) && $cust->dosage_interval != $post[$i]['dosage_interval'])
    							    ){
    
    
    								$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
    								$medication_old_medication_name[$i] = $old_med->name;
    								$medication_old_medication_id[$i] =  $old_med->id;
    
    								$history = new PatientDrugPlanHistory();
    								$history->ipid = $ipid;
    								$history->pd_id = $cust->id;
    								$history->pd_medication_master_id = $cust->medication_master_id ;
    								$history->pd_medication_name = $medication_old_medication_name[$i] ;
    								$history->pd_medication =  $cust->medication;
    								$history->pd_dosage = $cust->dosage;
    								$history->pd_dosage_interval = isset($cust->dosage_interval) ? $cust->dosage_interval : null;
    								$history->pd_dosage_product = isset($cust->dosage_product) ? $cust->dosage_product : null;
    								$history->pd_comments = $cust->comments ;
    								$history->pd_isbedarfs = $cust->isbedarfs;
    								$history->pd_iscrisis = $cust->iscrisis;
    								$history->pd_isivmed = $cust->isivmed;
    								$history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
    								$history->pd_cocktailid = $cust->cocktailid;
    								$history->pd_treatment_care = $cust->treatment_care;
    								$history->pd_isnutrition = $cust->isnutrition;
    								$history->pd_isintubated = $cust->isintubated;
    								$history->pd_edit_type = $cust->edit_type;
    								$history->pd_verordnetvon = $cust->verordnetvon;
    								$history->pd_medication_change = $cust->medication_change;
    								$history->pd_create_date = $cust->create_date;
    								$history->pd_create_user = $cust->create_user;
    								$history->pd_change_date = $cust->change_date;
    								$history->pd_change_user = $cust->change_user;
    								$history->pd_isdelete = $cust->isdelete;
    								$history->pd_days_interval_technical = isset($cust->days_interval_technical) ? $cust->days_interval_technical : null;
    								$history->save();
    							}
    
    							if($update_medication[$i] == "1"){
    
//     								if (!empty($med_master_id))
//     								{
//     									$cust->medication_master_id = $med_master_id;
//     								}
//     								$cust->medication_change= $medication_change_date[$i];
//     								$cust->dosage = $post['dosage'][$i];
//     								$cust->comments = $post['comments'][$i];
//     								$cust->edit_type = $post['course_type'][$i];
//     								$cust->save();
    								
    								// IF An approval user edits a medications that is not approved yet - all data is marcked as inactive for this medication
    								if(in_array($userid,$approval_users) && $acknowledge == "1" )
    								{
    								    $clear =  $this->update_pdpa($ipid,  $post['drid'][$i] );
    								    
    								    
    								    // OLD ENTRY
    								    // old medication name
    								    $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
    								    $old_med_name[$i] = $old_med->name;
    								    
    								    // old dosage
    								    if($cust->dosage) {
    								    	$old_med_dosage[$i] = $cust->dosage;
    								    }
    								    
    								    // old comment
    								    if($cust->comments ){
    								    	$old_med_comments[$i] = $cust->comments." | ";
    								    }
    								     
    								    //  old medication date
    								    if($cust->medication_change != "0000-00-00 00:00:00")
    								    {
    								    	$old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->medication_change));
    								    }
    								    else
    								    {
    								    	if($cust->change_date != "0000-00-00 00:00:00")
    								    	{
    								    		$old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->change_date));
    								    	}
    								    	else
    								    	{
    								    		$old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->create_date));
    								    	}
    								    }
    								    
    								    if(strlen($old_med_dosage[$i])>0){
    								    	$old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
    								    } else	{
    								    	$old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
    								    }
    								    
    								    // NEW ENTRY
    								    // new name
    								    if (!empty($med_master_id[$i]))
    								    {
    								    	$new_med = Doctrine::getTable('Medication')->find($med_master_id[$i]);
    								    }
    								    else
    								    {
    								    	$new_med = Doctrine::getTable('Medication')->find( $cust->medication_master_id );
    								    }
    								    $new_medication_name[$i] = $new_med->name;
    								    
    								    // new dosage
    								    $new_medication_dosage[$i] = $post['dosage'][$i];
    								    
    								    // new comments
    								    $new_medication_comments[$i] = $post['comments'][$i];
    								    
    								    // new change date
    								    if($medication_change_date[$i] != "0000-00-00 00:00:00"){
    								    	$medication_change_date_str[$i] = date("d.m.Y", strtotime($medication_change_date[$i]));
    								    }
    								    else
    								    {
    								    	$medication_change_date_str[$i]="";
    								    }
    								    
    								    if(strlen($new_medication_dosage[$i])>0)
    								    {
    								    	$new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
    								    }
    								    else
    								    {
    								    	$new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
    								    }
    								    
    								    if($cust->isivmed == "0" && $cust->isbedarfs == "1")
    								    {
    								    	$shortcut = "N";
    								    }
    								    elseif($cust->isivmed == "1" && $cust->isbedarfs == "0")
    								    {
    								    	$shortcut = "I";
    								    }
    								    elseif($cust->iscrisis == "1")
    								    {
    								    	$shortcut = "KM";
    								    }
    								    elseif($cust->isintubated == "1")
    								    {
    								    	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
    								    }
    								    else
    								    {
    								    	$shortcut = "M";
    								    }
    								    
    								    $attach = 'Änderung: ' . $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
    								    $insert_pc = new PatientCourse();
    								    $insert_pc->ipid =  $ipid;
    								    $insert_pc->course_date = date("Y-m-d H:i:s", time());
    								    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
    								    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_edit_med");
    								    $insert_pc->recordid = $post['drid'][$i];
    								    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
    								    $insert_pc->user_id = $userid;
    								    $insert_pc->save();
    								    
    								}
    								
    								if (!empty($med_master_id))
    								{
    								    $cust->medication_master_id = $med_master_id;
    								}
    								$cust->medication_change= $medication_change_date[$i];
    								$cust->dosage = $post['dosage'][$i];
    								$cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
    								$cust->comments = $post['comments'][$i];
    								$cust->edit_type = $post['course_type'][$i];
    								$cust->save();    								
    								
    							}
    						}
    					}
    				}
    				else
    				{
    					$cust_del = Doctrine::getTable('PatientDrugPlan')->find($post['delete'][$post['drid'][$i]]);
    					if ($cust_del->id)
    					{
    
    						/* ================= Save in patient drugplan history ====================*/
    						$old_med = Doctrine::getTable('Medication')->find($cust_del->medication_master_id );
    						$medication_old_medication_name[$i] = $old_med->name;
    						$medication_old_medication_id[$i] =  $old_med->id;
    						$history = new PatientDrugPlanHistory();
    						$history->ipid = $ipid;
    						$history->pd_id = $cust_del->id;
    						$history->pd_medication_master_id = $cust_del->medication_master_id ;
    						$history->pd_medication_name = $medication_old_medication_name[$i] ;
    						$history->pd_medication =  $cust_del->medication;
    						$history->pd_dosage = $cust_del->dosage;
    						$history->pd_dosage_interval = isset($cust_del->dosage_interval) ? $cust_del->dosage_interval : null;
    						$history->pd_dosage_product = isset($cust_del->dosage_product) ? $cust_del->dosage_product : null;
    						$history->pd_comments = $cust_del->comments ;
    						$history->pd_isbedarfs = $cust_del->isbedarfs;
    						$history->pd_iscrisis = $cust_del->iscrisis;
    						$history->pd_isivmed = $cust_del->isivmed;
    						$history->pd_isschmerzpumpe = $cust_del->isschmerzpumpe;
    						$history->pd_cocktailid = $cust_del->cocktailid;
    						$history->pd_treatment_care = $cust_del->treatment_care;
    						$history->pd_isnutrition = $cust_del->isnutrition;
    						$history->pd_isintubated = $cust_del->isintubated;
    						$history->pd_edit_type = $cust_del->edit_type;
    						$history->pd_verordnetvon = $cust_del->verordnetvon;
    						$history->pd_medication_change = $cust_del->medication_change;
    						$history->pd_create_date = $cust_del->create_date;
    						$history->pd_create_user = $cust_del->create_user;
    						$history->pd_change_date = $cust_del->change_date;
    						$history->pd_change_user = $cust_del->change_user;
    						$history->pd_isdelete = $cust_del->isdelete;
    						$history->pd_days_interval_technical = isset($cust_del->days_interval_technical) ? $cust_del->days_interval_technical : null;
    						$history->save();
    
    						$cust_del->isdelete = '1';
    						$cust_del->edit_type = $post['course_type'][$i];
    						$cust_del->save();
    						
    						// IF An approval user edits a medications that is not approved yet - all data is marcked as inactive for this medication
    						if(in_array($userid,$approval_users) && $acknowledge == "1" )
    						{
       						    $clear = $this->update_pdpa($ipid, $cust_del->id);
       						    

       						    // OLD ENTRY
       						    // old medication name
       						    $old_med = Doctrine::getTable('Medication')->find($cust_del->medication_master_id);
       						    $old_med_name[$i] = $old_med->name;
       						    
       						    // old dosage
       						    if($cust_del->dosage) {
       						    	$old_med_dosage[$i] = $cust_del->dosage;
       						    }
       						    
       						    // old comment
       						    if($cust_del->comments ){
       						    	$old_med_comments[$i] = $cust_del->comments." | ";
       						    }
       						    	
       						    //  old medication date
       						    if($cust_del->medication_change != "0000-00-00 00:00:00")
       						    {
       						    	$old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust_del->medication_change));
       						    }
       						    else
       						    {
       						    	if($cust_del->change_date != "0000-00-00 00:00:00")
       						    	{
       						    		$old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust_del->change_date));
       						    	}
       						    	else
       						    	{
       						    		$old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust_del->create_date));
       						    	}
       						    }
       						    
       						    if(strlen($old_med_dosage[$i])>0){
       						    	$old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
       						    } else	{
       						    	$old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
       						    }
       						    
       						    if($cust->isivmed == "0" && $cust->isbedarfs == "1")
       						    {
       						    	$shortcut = "N";
       						    }
       						    elseif($cust->isivmed == "1" && $cust->isbedarfs == "0")
       						    {
       						    	$shortcut = "I";
       						    }
       						    elseif($cust->iscrisis == "1")
       						    {
       						    	$shortcut = "KM";
       						    }
       						    else
       						    {
       						    	$shortcut = "M";
       						    }
       						    
       						    $attach =  $old_entry[$i] . ' wurde abgesetzt';
       						    $insert_pc = new PatientCourse();
       						    $insert_pc->ipid =  $ipid;
       						    $insert_pc->course_date = date("Y-m-d H:i:s", time());
       						    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
       						    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_edit_med");
       						    $insert_pc->recordid = $post['drid'][$i];
       						    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
       						    $insert_pc->user_id = $userid;
       						    $insert_pc->save();
       						    
    						} 
    					}
    				}
    			}
    		}
    	}
	}
	
	
	public function update_pdpa($ipid,$drugplan_id){
	    $dr_ids = PatientDrugPlanAlt::get_drugplan_alt($ipid,$drugplan_id);
	    
	    if($dr_ids){
	        
            $remove = Doctrine_Query::create()
            ->update('PatientDrugPlanAlt')
            ->set('inactive', 1)
            ->set('change_date', '"'.date("Y-m-d H:i:s",time()).'"')
            //->set('change_source', 'online')
            ->where("ipid ='" . $ipid . "'")
            ->andWhereIn("id",$dr_ids)
            ->andWhereNotIn('change_source' , array('offline'))
            ;
            $remove->execute();
	    }
	}
	
	public function update_pdpca($ipid = '', $cocktail_id = array()) {
	    
	    $dr_ids = PatientDrugPlanAltCocktails::get_drug_cocktails_alt($ipid, $cocktail_id, false);

	    if($dr_ids){
	        
            $remove = Doctrine_Query::create()
            ->update('PatientDrugPlanAltCocktails')
            ->set('inactive', 1)
            ->set('change_date', '"'.date("Y-m-d H:i:s",time()).'"')
            ->where("ipid ='" . $ipid . "'")
            ->andWhereIn("id",$dr_ids);
            $remove->execute();
	    }
	}
	
	
	
	public function  apply_medication_change($ipid,$userid,$post)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;

	    // get user details
	    $master_user_details = new User();
	    $users_details_arr = $master_user_details->getUserDetails($userid);
	    $users_details = $users_details_arr[0];
	    
	    
        // get data from patient drugplan alt
        $drugplan_id = $post['recordid'];
        $alt_id = $post['alt_id'];
        $alt_data_array = PatientDrugPlanAlt::get_drugplan_alt($ipid,$drugplan_id,$alt_id,true);  
   
        //ISPC-2829  Ancuta 07.04.2021 ELSA EFA - Medikation
        $all_alt_data_array = array();
        foreach($alt_data_array as $pdi => $palt_data){
            if(!empty($palt_data['related_alt_id']) && !empty($palt_data['related_drugplan_id'])){
                
                $alt_drugs_q = Doctrine_Query::create()
                ->select('*')
                ->from('PatientDrugPlanAlt indexby id')
                ->where("id = ?", $palt_data['related_alt_id'])
                ->andWhere("drugplan_id = ?", $palt_data['related_drugplan_id'])
                ->andWhere("isdelete = '0'");
                $efa_alt_drugs_array = $alt_drugs_q->fetchArray();
                
                if(!empty($efa_alt_drugs_array)){
                    $all_alt_data_array = array_merge($alt_data_array,$efa_alt_drugs_array);
                }
            }
        }
        if(empty($all_alt_data_array)){
            $all_alt_data_array = $alt_data_array;
        }
        
        $final_alt_data_array = array();
        foreach($all_alt_data_array as $k => $d){
            $final_alt_data_array[$d['id']] = $d;
        }
        // --
              
        $approve = "0";
        $decline = "0";
        if($final_alt_data_array) //ISPC-2829  Ancuta 07.04.2021 - changed array.
        {
            foreach($final_alt_data_array as $alt_id => $alt_data)
            {
                $cust_alt = Doctrine::getTable('PatientDrugPlanAlt')->find($alt_data['id']);
                
                if($cust_alt)
                {
                    //ISPC-2829  Ancuta 07.04.2021
                    $ipid = $alt_data['ipid'];
                    // --
                    
                    //  get dosage data
                    $alt_dosage  = PatientDrugPlanDosageAlt::get_patient_drugplan_dosage_alt_all($ipid,array($alt_data['id']));
                    $alt_dosage_full  = PatientDrugPlanDosageAlt::get_patient_drugplan_dosage_alt_all($ipid,array($alt_data['id']),true);//TODO-3624 Ancuta 23.11.2020
                
                    // get extra data
                    $alt_extra  = PatientDrugPlanExtraAlt::get_patient_drugplan_extra_alt_all($ipid,array($alt_data['id']));
                    
                    if($post['action'] == "approve")
                    {
                        $approve = "1";
                        $decline = "0";
                        $cust_alt->approved = "1";
                        $cust_alt->approval_user = $userid;
                        $cust_alt->approval_date = date("Y-m-d H:i:s",time());
                        $cust_alt->save();
                    }
                    else
                    {
                        $approve = "0";
                        $decline = "1";
                        
                        if($cust_alt->status == "delete" || $cust_alt->status == "edit"  || $cust_alt->status == "renew"){
                            $cust_alt->inactive = "1";
                        }
                        $cust_alt->declined = "1";
                        $cust_alt->decline_user = $userid;
                        $cust_alt->decline_date = date("Y-m-d H:i:s",time());
                        $cust_alt->save();
                    }
                }
                
                // get all active todos where the triger by =  "medacknowledge-$drugplan_id"
                //todos
                $todo_q = Doctrine_Query::create()
                ->select("*")
                ->from('ToDos')
                ->where('client_id="' . $clientid . '"')
                ->andWhere('isdelete="0"')
                ->andWhere('iscompleted="0"')
                ->andWhere('triggered_by="medacknowledge-'.$drugplan_id.'"')
                ->orderBy('create_date DESC');
                $todo_array = $todo_q->fetchArray();
                
                foreach($todo_array as $k=>$todo_data)
                {
                    $todos_ids[] = $todo_data['id'];
                    $todo = Doctrine::getTable('ToDos')->find($todo_data['id']);
                    if($todo)
                    {
                        $todo->iscompleted = '1';
                        $todo->complete_user = $userid;
                        $todo->complete_date = date("Y-m-d H:i:s", time());
                        $todo->save();
                    }
                }
 
                
 
                if($approve == "1")
                { // INSET CHANGES IN PATIENTDRUGPLAN
                
                    $alt_id = $alt_data['id'];
                    $cust = Doctrine::getTable('PatientDrugPlan')->find($alt_data['drugplan_id']);
                    
                    if($cust)
                    {
                        
                        // ================= Save in patient drugplan history ====================
                        
                        if(	$cust->dosage != $alt_data['dosage'] ||
                            $cust->medication_master_id != $medid ||
                            $cust->verordnetvon != $alt_data['verordnetvon'] ||
                            $cust->comments != $alt_data['comments']
                            || (isset($cust->dosage_interval) && isset($alt_data['dosage_interval']) &&  $cust->dosage_interval != $alt_data['dosage_interval'])
                            || (isset($cust->days_interval_technical) && isset($alt_data['days_interval_technical']) &&  $cust->days_interval_technical != $alt_data['days_interval_technical'])
                            || (isset($cust->dosage_product) && isset($alt_data['dosage_product']) &&  $cust->dosage_product != $alt_data['dosage_product'])
                            
                            )
                        {
							//TODO-2785 Lore 18.02.2020
                            //$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                            if( $cust->treatment_care == 1 )
                            {
                                $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id);
                            }
                            elseif( $cust->isnutrition == 1 )
                            {
                                $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id);
                            }
                            else
                            {
                                $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
                            }
                            $medication_old_medication_name = $old_med->name;
                            $medication_old_medication_id =  $old_med->id;
                    
                            $history = new PatientDrugPlanHistory();
                            $history->ipid = $ipid;
                            $history->pd_id = $cust->id;
                            $history->pd_medication_master_id = $cust->medication_master_id ;
                            $history->pd_medication_name = $medication_old_medication_name ;
                            $history->pd_medication =  $cust->medication;
                            $history->pd_dosage = $cust->dosage;
                            $history->pd_dosage_interval = isset($cust->dosage_interval) ? $cust->dosage_interval : null;
                            $history->pd_dosage_product = isset($cust->dosage_product) ? $cust->dosage_product : null;
                            $history->pd_comments = $cust->comments ;
                            $history->pd_isbedarfs = $cust->isbedarfs;
                            $history->pd_iscrisis = $cust->iscrisis;
                            $history->pd_isivmed = $cust->isivmed;
                            $history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
                            $history->pd_cocktailid= $cust->cocktailid;
                            $history->pd_treatment_care = $cust->treatment_care;
                            $history->pd_isnutrition = $cust->isnutrition;
                            $history->pd_isintubated = $cust->isintubated;// ISPC-2176
                            $history->pd_edit_type = $cust->edit_type;
                            $history->pd_verordnetvon = $cust->verordnetvon;
                            $history->pd_medication_change = $cust->medication_change;
                            $history->pd_create_date = $cust->create_date;
                            $history->pd_create_user = $cust->create_user;
                            $history->pd_change_date = $cust->change_date;
                            $history->pd_change_user = $cust->change_user;
                            $history->pd_isdelete = $cust->isdelete;
                            $history->pd_days_interval_technical = isset($cust->days_interval_technical) ? $cust->days_interval_technical : null;
                            $history->save();
                            
                            $history_id =$history->id; 
                            

                            $dosage_history_array[$cust->id] = PatientDrugPlanDosage::get_all_patient_drugplan_dosage($ipid,$cust->id);
                            
                            if(!empty($dosage_history_array[$cust->id]))
                            {
                                // add dosage to - dosage history
                                foreach($dosage_history_array[$cust->id] as $k=>$dv)
                                {
                                    $history_pd = new PatientDrugPlanDosageHistory();
                                    $history_pd->ipid = $ipid;
                                    $history_pd->pdd_id = $dv['id'];
                                    $history_pd->history_id = $history_id;
                                    $history_pd->pdd_drugplan_id = $dv['drugplan_id'];
                                    $history_pd->pdd_dosage = $dv['dosage'];
                                    //TODO-3624 Ancuta 23.11.2020
                                    $history_pd->pdd_dosage_full = $dv['dosage_full'];
                                    $history_pd->pdd_dosage_concentration = $dv['dosage_concentration'];
                                    $history_pd->pdd_dosage_concentration_full = $dv['dosage_concentration_full'];
                                    //--
                                    $history_pd->pdd_dosage_time_interval =  $dv['dosage_time_interval'];
                                    $history_pd->pdd_isdelete	= $dv['isdelete'];
                                    $history_pd->pdd_create_user = $dv['create_user'];
                                    $history_pd->pdd_create_date = $dv['create_date'];
                                    $history_pd->pdd_change_user = $dv['change_user'];
                                    $history_pd->pdd_change_date = $dv['change_date'];
                                    $history_pd->save();
                                }
                            }
                            
                            $drugplan_extra_array = PatientDrugPlanExtra::get_patient_all_drugplan_extra($ipid,$cust->id);
                            
                            if(!empty($drugplan_extra_array[$cust->id]))
                            {
                                $drugplan_extra_data = $drugplan_extra_array[$cust->id];
                            
                                $history_pde = new PatientDrugPlanExtraHistory();
                                $history_pde->ipid = $ipid;
                                $history_pde->pde_id = $drugplan_extra_data['id'];
                                $history_pde->history_id = $history_id;
//                                 $history_pde->pde_drugplan_id = $dv['drugplan_id'];;
                                $history_pde->pde_drugplan_id = $cust->id;
                                $history_pde->pde_drug = $drugplan_extra_data['drug'];
                                
                                $history_pde->pde_dosage_24h_manual = $drugplan_extra_data['dosage_24h_manual'];    //ISPC-2684 Lore 05.10.2020
                                $history_pde->pde_unit_dosage = $drugplan_extra_data['unit_dosage'];                //ISPC-2684 Lore 05.10.2020
                                $history_pde->pde_unit_dosage_24h = $drugplan_extra_data['unit_dosage_24h'];        //ISPC-2684 Lore 05.10.2020
                                
                                $history_pde->pde_unit = $drugplan_extra_data['unit'];
                                $history_pde->pde_type = $drugplan_extra_data['type'];
                                $history_pde->pde_indication = trim($drugplan_extra_data['indication']);
                                $history_pde->pde_importance = $drugplan_extra_data['importance'];
                                $history_pde->pde_dosage_form = $drugplan_extra_data['dosage_form'];
                                $history_pde->pde_concentration = $drugplan_extra_data['concentration'];
                                // ISPC-2176 p6
                                $history_pde->pde_packaging = $drugplan_extra_data['packaging'];
                                $history_pde->pde_kcal = $drugplan_extra_data['kcal'];
                                $history_pde->pde_volume = $drugplan_extra_data['volume'];
                                //--                        
                                // ISPC-2247        
                                $history_pde->pde_escalation = $drugplan_extra_data['escalation'];
                                //--
                                $history_pde->pde_isdelete	= $drugplan_extra_data['isdelete'];
                                $history_pde->pde_create_user = $drugplan_extra_data['create_user'];
                                $history_pde->pde_create_date = $drugplan_extra_data['create_date'];
                                $history_pde->pde_change_user = $drugplan_extra_data['change_user'];
                                $history_pde->pde_change_date = $drugplan_extra_data['change_date'];
                                $history_pde->save();
                            }
                        }
                
                        // ================= Update patient drugplan item ====================
                        $cust->ipid = $ipid;
                        $cust->dosage = $alt_data['dosage'];
                        $cust->dosage_interval = isset($alt_data['dosage_interval']) ? $alt_data['dosage_interval'] : null;
                        $cust->medication_master_id = $alt_data['medication_master_id'];
                        $cust->verordnetvon = $alt_data['verordnetvon'];
                        $cust->comments = $alt_data['comments'];
                        $cust->isbedarfs = $alt_data['isbedarfs'];
                        $cust->iscrisis = $alt_data['iscrisis'];
                        $cust->isivmed = $alt_data['isivmed'];
                        $cust->isschmerzpumpe = $alt_data['isschmerzpumpe'];
                        $cust->cocktailid = $alt_data['cocktailid'];
                        $cust->treatment_care = $alt_data['treatment_care'];
                        $cust->isnutrition = $alt_data['isnutrition'];
                        $cust->isintubated = $alt_data['isintubated'];// ISPC-2176
                        
                        if($alt_data['status'] == "delete")
                        {
                            $cust->isdelete = '1';
                        } 
                        elseif($alt_data['status'] == "renew")
                        {
                            $cust->isdelete = '0';
                        }
                        else 
                        {
                            $cust->isdelete = '0';
                        }
                        $cust->medication_change = $alt_data['medication_change'];
                        /*
                         * @cla
                         * when you approve a drug, 
                         * we consider changed_user to be the one that initiate the change, not the one that is now approving this
                         * we leave change_date to be set by the behaviour
                         */
                        //$cust->create_date = $alt_data['create_date'];
                        //$cust->create_user = $alt_data['create_user'];
                        //$cust->change_date = $alt_data['change_date'];
                        //$cust->change_user = $alt_data['change_user'];
                        $cust->change_user = $alt_data['create_user'];
                        
                        $cust->dosage_product = isset($alt_data['dosage_product']) ? $alt_data['dosage_product'] : null;
                        $cust->days_interval_technical = isset($alt_data['days_interval_technical']) ? $alt_data['days_interval_technical'] : null;
                        
                        /*ISPC-1999 @cla*/
                        $cust->change_source = $alt_data['change_source'];
                        
                        $cust->save();
                        
                        $existing_drugplan_id = $cust->id;
                         
                        //update dosage (isdelete = 1)
                        // insert the new dosage
                        if(!empty($alt_dosage[$alt_id][$existing_drugplan_id])){
                            
                            // clear dosage
                            $clear_dosage = $this->clear_dosage($ipid, $existing_drugplan_id);
                            
                            foreach($alt_dosage[$alt_id][$existing_drugplan_id] as $time => $dosage_value)
                            {
                                //  insert new lines
                                $cust_pdd = new PatientDrugPlanDosage();
                                $cust_pdd->ipid = $ipid;
                                $cust_pdd->drugplan_id = $existing_drugplan_id;
                                $cust_pdd->dosage = $dosage_value;
                                //TODO-3624 Ancuta 23.11.2020
                                $cust_pdd->dosage_full = $alt_dosage_full[$alt_id][$existing_drugplan_id][$time]['dosage_full'];
                                $cust_pdd->dosage_concentration = $alt_dosage_full[$alt_id][$existing_drugplan_id][$time]['dosage_concentration'];
                                $cust_pdd->dosage_concentration_full = $alt_dosage_full[$alt_id][$existing_drugplan_id][$time]['dosage_concentration_full'];
                                //-- 
                                $cust_pdd->dosage_time_interval = $time.":00";
                                $cust_pdd->save();
                            }
                        }
                        
                        // update extra data
                        if(!empty($alt_extra))
                        {
                            
                            $drugs = Doctrine_Query::create()
                            ->select('id')
                            ->from('PatientDrugPlanExtra')
                            ->where("ipid = '" . $ipid . "'")
                            ->andWhere("drugplan_id = '" . $existing_drugplan_id . "'")
                            ->andWhere("isdelete = '0'")
                            ->orderBy("create_date DESC")
                            ->limit(1);
                            $drugs_array = $drugs->fetchArray();
                        
                            if(!empty($drugs_array)){
                                $existing_extra_id =$drugs_array[0]['id'];
                        
                                $update_pde = Doctrine::getTable('PatientDrugPlanExtra')->find($existing_extra_id);
                                
                                $update_pde->drug = $alt_extra[$alt_id][$existing_drugplan_id]['drug'];
                                $update_pde->unit = $alt_extra[$alt_id][$existing_drugplan_id]['unit_id'];
                                
                                $update_pde->dosage_24h_manual = $alt_extra[$alt_id][$existing_drugplan_id]['dosage_24h_manual'];   //ISPC-2684 Lore 05.10.2020
                                $update_pde->unit_dosage = $alt_extra[$alt_id][$existing_drugplan_id]['unit_dosage'];            //ISPC-2684 Lore 05.10.2020
                                $update_pde->unit_dosage_24h = $alt_extra[$alt_id][$existing_drugplan_id]['unit_dosage_24h'];    //ISPC-2684 Lore 05.10.2020
                                
                                $update_pde->type = $alt_extra[$alt_id][$existing_drugplan_id]['type_id'];
                                $update_pde->indication = $alt_extra[$alt_id][$existing_drugplan_id]['indication_id'];
                                $update_pde->importance = $alt_extra[$alt_id][$existing_drugplan_id]['importance'];
                                $update_pde->dosage_form = $alt_extra[$alt_id][$existing_drugplan_id]['dosage_form_id'];
                                $update_pde->concentration = $alt_extra[$alt_id][$existing_drugplan_id]['concentration'];
                                // ISPC-2176 p6
                                $update_pde->packaging = $alt_extra[$alt_id][$existing_drugplan_id]['packaging'];
                                $update_pde->kcal = $alt_extra[$alt_id][$existing_drugplan_id]['kcal'];
                                $update_pde->volume = $alt_extra[$alt_id][$existing_drugplan_id]['volume'];
                                //--
                                // ISPC-2247
                                $update_pde->escalation = $alt_extra[$alt_id][$existing_drugplan_id]['escalation'];
                                //--
                                $update_pde->save();
                            }
                            else
                            {
                                // add extra data
                                $cust_pde = new PatientDrugPlanExtra();
                                $cust_pde->ipid = $ipid;
                                $cust_pde->drugplan_id = $existing_drugplan_id;
                                $cust_pde->drug = $alt_extra[$alt_id][$existing_drugplan_id]['drug'];
                                $cust_pde->unit = $alt_extra[$alt_id][$existing_drugplan_id]['unit_id'];
                                
                                $cust_pde->dosage_24h_manual = $alt_extra[$alt_id][$existing_drugplan_id]['dosage_24h_manual'];  //ISPC-2684 Lore 05.10.2020
                                $cust_pde->unit_dosage = $alt_extra[$alt_id][$existing_drugplan_id]['unit_dosage'];            //ISPC-2684 Lore 05.10.2020
                                $cust_pde->unit_dosage_24h = $alt_extra[$alt_id][$existing_drugplan_id]['unit_dosage_24h'];    //ISPC-2684 Lore 05.10.2020
                                
                                $cust_pde->type = $alt_extra[$alt_id][$existing_drugplan_id]['type_id'];
                                $cust_pde->indication = $alt_extra[$alt_id][$existing_drugplan_id]['indication_id'];
                                $cust_pde->importance = $alt_extra[$alt_id][$existing_drugplan_id]['importance'];
                                $cust_pde->dosage_form = $alt_extra[$alt_id][$existing_drugplan_id]['dosage_form_id'];
                                $cust_pde->concentration = $alt_extra[$alt_id][$existing_drugplan_id]['concentration'];
                                // ISPC-2176 p6
                                $cust_pde->packaging = $alt_extra[$alt_id][$existing_drugplan_id]['packaging'];
                                $cust_pde->kcal = $alt_extra[$alt_id][$existing_drugplan_id]['kcal'];
                                $cust_pde->volume = $alt_extra[$alt_id][$existing_drugplan_id]['volume'];
                                //--
                                // ISPC-2247
                                $cust_pde->escalation = $alt_extra[$alt_id][$existing_drugplan_id]['escalation'];
                                //--                                
                                $cust_pde->save();
                            }
                        
                        }
                        else
                        {
                            // add extra data
                            $cust_pde = new PatientDrugPlanExtra();
                            $cust_pde->ipid = $ipid;
                            $cust_pde->drugplan_id = $existing_drugplan_id;
                            $cust_pde->drug = $alt_extra[$alt_id][$existing_drugplan_id]['drug'];
                            $cust_pde->unit = $alt_extra[$alt_id][$existing_drugplan_id]['unit_id'];
                            
                            $cust_pde->dosage_24h_manual = $alt_extra[$alt_id][$existing_drugplan_id]['dosage_24h_manual'];  //ISPC-2684 Lore 05.10.2020
                            $cust_pde->unit_dosage = $alt_extra[$alt_id][$existing_drugplan_id]['unit_dosage'];              //ISPC-2684 Lore 05.10.2020
                            $cust_pde->unit_dosage_24h = $alt_extra[$alt_id][$existing_drugplan_id]['unit_dosage_24h'];      //ISPC-2684 Lore 05.10.2020
                            
                            $cust_pde->type = $alt_extra[$alt_id][$existing_drugplan_id]['type_id'];
                            $cust_pde->indication = $alt_extra[$alt_id][$existing_drugplan_id]['indication_id'];
                            $cust_pde->importance = $alt_extra[$alt_id][$existing_drugplan_id]['importance'];
                            $cust_pde->dosage_form = $alt_extra[$alt_id][$existing_drugplan_id]['dosage_form_id'];
                            $cust_pde->concentration = $alt_extra[$alt_id][$existing_drugplan_id]['concentration'];
                            // ISPC-2176 p6
                            $cust_pde->packaging = $alt_extra[$alt_id][$existing_drugplan_id]['packaging'];
                            $cust_pde->kcal = $alt_extra[$alt_id][$existing_drugplan_id]['kcal'];
                            $cust_pde->volume = $alt_extra[$alt_id][$existing_drugplan_id]['volume'];
                            //--
                            // ISPC-2247
                            $cust_pde->escalation = $alt_extra[$alt_id][$existing_drugplan_id]['escalation'];
                            //--                            
                            $cust_pde->save();
                        }
                        
                        
                        
                        //-------------------------                        
                        // VERLAUF ENTRY
                        //-------------------------                        
                        if($alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs'] == 1 &&   $alt_data['isschmerzpumpe'] == 0 &&  $alt_data['treatment_care'] == 0  && $alt_data['scheduled'] == 0 )
                        {
                            $shortcut = "N";
                        }
                        elseif($alt_data['isivmed']  == 1 && $alt_data['isbedarfs'] == 0 && $alt_data['isschmerzpumpe']  == 0 && $alt_data['treatment_care'] == 0  && $alt_data['scheduled'] == 0  )
                        {
                            $shortcut = "I";
                        }
                        elseif($alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs'] == 0 &&  $alt_data['isschmerzpumpe']  == 1 &&  $alt_data['treatment_care'] == 0   && $alt_data['scheduled'] == 0  )
                        {
                            $shortcut = "Q";
                            $prefix = "Schmerzpumpe ";
                        }
                        elseif($alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs']== 0 &&  $alt_data['isschmerzpumpe']  == 0 && $alt_data['treatment_care'] == 1 && $alt_data['scheduled'] == 0  )
                        {
                            $shortcut = "BP";
                        }
                        elseif($alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs']== 0 &&  $alt_data['isschmerzpumpe']  == 0 && $alt_data['treatment_care'] == 0 && $alt_data['scheduled'] == 1 )
                        {
                            $shortcut = "MI";
                        }
                        elseif($alt_data['iscrisis'] == 1 &&  $alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs']== 0 &&  $alt_data['isschmerzpumpe']  == 0 && $alt_data['treatment_care'] == 0 && $alt_data['scheduled'] == 0 )
                        {
                        	$shortcut = "KM";
                        }
                        elseif($alt_data['isintubated'] == 1 &&  $alt_data['iscrisis'] == 0 &&  $alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs']== 0 &&  $alt_data['isschmerzpumpe']  == 0 && $alt_data['treatment_care'] == 0 && $alt_data['scheduled'] == 0 )
                        {
                        	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
                        }
                        else
                        {
                            $shortcut = "M";
                        }
                        // OLD ENTRY
                        // old medication name
						//TODO-2785 Lore 18.02.2020
                        //$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                        if( $cust->treatment_care == 1 )
                        {
                            $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id);
                        }
                        elseif( $cust->isnutrition == 1 )
                        {
                            $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id);
                        }
                        else
                        {
                            $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
                        }
                        $old_med_name = $old_med->name;
                        
                        // old dosage
                        if($cust->dosage) {
                            $old_med_dosage = $cust->dosage;
                        }
                        
                        // old comment
                        if($cust->comments ){
                            $old_med_comments = $cust->comments." | ";
                        }
                        	
                        //  old medication date
                        if($cust->medication_change != "0000-00-00 00:00:00")
                        {
                            $old_med_medication_change =  date('d.m.Y',strtotime($cust->medication_change));
                        }
                        else
                        {
                            if($cust->change_date != "0000-00-00 00:00:00")
                            {
                                $old_med_medication_change =  date('d.m.Y',strtotime($cust->change_date));
                            }
                            else
                            {
                                $old_med_medication_change =  date('d.m.Y',strtotime($cust->create_date));
                            }
                        }


                        // old comment
                        if($cust->days_interval ){
                            $old_med_days_interval = " | ".$cust->days_interval;
                        }
                         
                        
                        $old_med_days_interval_technical = '';
                        if($cust->days_interval_technical ){
                            $old_med_days_interval_technical = " | ".$cust->days_interval_technical;
                        }
                        
                        
                        
                        if($cust->scheduled == 1)
                        {
                            $old_entry = $prefix.$old_med_name.$old_med_days_interval . $old_med_days_interval_technical ;
                        }
                        else
                        {
                            if(strlen($old_med_dosage)>0){
                                $old_entry = $prefix.$old_med_name.' | '.$old_med_dosage." | ".$old_med_comments.$old_med_medication_change;
                            } else	{
                                $old_entry = $prefix.$old_med_name.' | '.$old_med_comments.$old_med_medication_change;
                            }
                        
                        }
                        // NEW ENTRY
                        // new name
                        if( $alt_data['treatment_care'] == 1 )
                        {
                            $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($alt_data['medication_master_id'] );
                        }
                        elseif( $alt_data['isnutrition'] == 1 )
                        {
                            $new_med = Doctrine::getTable('Nutrition')->find($alt_data['medication_master_id'] );
                        }
                        else
                        {
                            $new_med = Doctrine::getTable('Medication')->find($alt_data['medication_master_id'] );
                        }
                        
                        $new_medication_name = $new_med->name;
                        
                        // new dosage
                        $new_medication_dosage = $alt_data['dosage'];
                        
                        // new comments
                        $new_medication_comments = $alt_data['comments'];
                        
                        // new change date
                        if($alt_data['medication_change'] != "0000-00-00 00:00:00"){
                            $medication_change_date_str = date("d.m.Y", strtotime($alt_data['medication_change']));
                        }
                        else
                        {
                            $medication_change_date_str="";
                        }
                        
                        // new days interval
                        if(strlen($alt_data['days_interval']) > 0 )
                        {
                            $new_medication_days_interval = " | ".$alt_data['days_interval'];
                        } 
                        else
                        {
                            $new_medication_days_interval = "";
                            
                        }
                        
                        if(strlen($alt_data['days_interval_technical']) > 0 )
                        {
                            $new_medication_days_interval_technical = " | ".$alt_data['days_interval_technical'];
                        }
                        else
                        {
                            $new_medication_days_interval_technical = "";
                        
                        }
                        
                        
                        if( $alt_data['scheduled'] == 1 )
                        {
                            $new_entry = $prefix.$new_medication_name.$new_medication_days_interval . $new_medication_days_interval_technical;
                        }
                        else
                        {
                        
                            if(strlen($new_medication_dosage)>0)
                            {
                                $new_entry = $prefix.$new_medication_name."  |  ".$new_medication_dosage." | ". $new_medication_comments." | ".$medication_change_date_str;
                            }
                            else
                            {
                                $new_entry = $prefix.$new_medication_name." | ".$new_medication_comments." | ".$medication_change_date_str;
                            }
                        }
                        
                        $user_name = $users_details['first_name'].' '.$users_details['last_name'];

                        if($alt_data['status'] == "new")
                        {
                            $course_entry = 'Benutzer '.$user_name.' bestätigte die neue Medikation: '.$new_entry.' ';
                            $med_course_entry =  $new_entry.' ';
                        } 
                        elseif($alt_data['status'] == "delete")
                        {
                            $course_entry = 'Benutzer '.$user_name.' bestätigte die Löschung der Medikation: '.$old_entry.' ';
                            $med_course_entry =  $old_entry.'  wurde abgesetzt';
                        }
                        elseif($alt_data['status'] == "renew")
                        {
                            $course_entry = 'Benutzer '.$user_name.' bestätigte das Wiederansetzen von: '.$old_entry.' ';
                            $med_course_entry = $old_entry.' wurde wieder angesetzt.';// !!!!!!!!!!!!
                        }
                        else
                        {
                           $course_entry = 'Benutzer '.$user_name.' bestätigte die Änderung der Medikation: '.$old_entry.' -->  '.$new_entry.' ';
                           $med_course_entry = 'Änderung: '.$old_entry.' -->  '.$new_entry.' ';
                        }
                        
                        
                        $insert_pc = new PatientCourse();
                        $insert_pc->ipid = $ipid;
                        $insert_pc->course_date = date("Y-m-d H:i:s", time());
//                         $insert_pc->course_type = Pms_CommonData::aesEncrypt('K');
                        $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
                        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_apprrove");
                        $insert_pc->recordid = $recordid;
                        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($course_entry));
                        $insert_pc->user_id = $userid;
                        $insert_pc->save();
                        

                        $insert_pc = new PatientCourse();
                        $insert_pc->ipid = $ipid;
                        $insert_pc->course_date = date("Y-m-d H:i:s", time());
//                         $insert_pc->course_type = Pms_CommonData::aesEncrypt('K');
                        $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
                        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_apprrove");
                        $insert_pc->recordid = $alt_data['drugplan_id'];
                        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($med_course_entry));
                        $insert_pc->user_id = $userid;
                        $insert_pc->save();
                        
                        
                        
                        
                        
                    }
                } // If approve = 1 END
                else
                { // if decline = 1

                    
                    $cust = Doctrine::getTable('PatientDrugPlan')->find($alt_data['drugplan_id']);
                    
                    if($cust)
                    {
                        //-------------------------
                        // VERLAUF ENTRY
                        //-------------------------
                        
                        if($alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs'] == 1 &&   $alt_data['isschmerzpumpe'] == 0 &&  $alt_data['treatment_care'] == 0 && $alt_data['scheduled'] == 0 )
                        {
                            $shortcut = "N";
                        }
                        elseif($alt_data['isivmed']  == 1 && $alt_data['isbedarfs'] == 0 && $alt_data['isschmerzpumpe']  == 0 && $alt_data['treatment_care'] == 0 && $alt_data['scheduled'] == 0 )
                        {
                            $shortcut = "I";
                        }
                        elseif($alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs'] == 0 &&  $alt_data['isschmerzpumpe']  == 1 &&  $alt_data['treatment_care'] == 0 && $alt_data['scheduled'] == 0)
                        {
                            $shortcut = "Q";
                            $prefix = "Schmerzpumpe ";
                        }
                        elseif($alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs']== 0 &&  $alt_data['isschmerzpumpe']  == 0 && $alt_data['treatment_care'] == 1 && $alt_data['scheduled'] == 0)
                        {
                            $shortcut = "BP";
                        }
                        elseif($alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs']== 0 &&  $alt_data['isschmerzpumpe']  == 0 && $alt_data['treatment_care'] == 0  && $alt_data['scheduled'] == 1 )
                        {
                            $shortcut = "MI";
                        }
                        elseif($alt_data['iscrisis'] == 1 &&  $alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs']== 0 &&  $alt_data['isschmerzpumpe']  == 0 && $alt_data['treatment_care'] == 0  && $alt_data['scheduled'] == 0 )
                        {
                        	$shortcut = "KM";
                        }
                        elseif($alt_data['isintubated'] == 1 &&  $alt_data['iscrisis'] == 0 &&  $alt_data['isivmed'] == 0 &&  $alt_data['isbedarfs']== 0 &&  $alt_data['isschmerzpumpe']  == 0 && $alt_data['treatment_care'] == 0  && $alt_data['scheduled'] == 0 )
                        {
                        	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
                        }
                        else
                        {
                            $shortcut = "M";
                        }
                        
                        // OLD ENTRY
                        // old medication name
						//TODO-2785 Lore 18.02.2020
                        //$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                        if( $cust->treatment_care == 1 )
                        {
                            $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id);
                        }
                        elseif( $cust->isnutrition == 1 )
                        {
                            $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id);
                        }
                        else
                        {
                            $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
                        }
                        $old_med_name = $old_med->name;
                        
                        // old dosage
                        if($cust->dosage) {
                            $old_med_dosage = $cust->dosage;
                        }
                        
                        // old comment
                        if($cust->comments ){
                            $old_med_comments = $cust->comments." | ";
                        }
                         
                        //  old medication date
                        if($cust->medication_change != "0000-00-00 00:00:00")
                        {
                            $old_med_medication_change =  date('d.m.Y',strtotime($cust->medication_change));
                        }
                        else
                        {
                            if($cust->change_date != "0000-00-00 00:00:00")
                            {
                                $old_med_medication_change =  date('d.m.Y',strtotime($cust->change_date));
                            }
                            else
                            {
                                $old_med_medication_change =  date('d.m.Y',strtotime($cust->create_date));
                            }
                        }
                        

                        // old days_interval
                        if($cust->days_interval ){
                            $old_med_days_interval = " | ".$cust->days_interval;
                        }                     
                        $old_med_days_interval_technical = '';
                        if($cust->days_interval_technical ){
                            $old_med_days_interval_technical = " | ".$cust->days_interval_technical;
                        }                        
                        
                        if($cust->scheduled == "1")
                        {
                               $old_entry = $prefix.$old_med_name.$old_med_days_interval . $old_med_days_interval_technical;
                        }
                        else
                        {
                            if(strlen($old_med_dosage)>0)
                            {
                                $old_entry = $prefix.$old_med_name.' | '.$old_med_dosage." | ".$old_med_comments.$old_med_medication_change;
                            } 
                            else	
                            {
                                $old_entry = $prefix.$old_med_name.' | '.$old_med_comments.$old_med_medication_change;
                            }
                        }
                        
                        // NEW ENTRY
                        // new name
                        if( $alt_data['treatment_care'] == 1 )
                        {
                            $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($alt_data['medication_master_id'] );
                        }
                        elseif( $alt_data['isnutrition'] == 1 )
                        {
                            $new_med = Doctrine::getTable('Nutrition')->find($alt_data['medication_master_id'] );
                        }
                        else
                        {
                            $new_med = Doctrine::getTable('Medication')->find($alt_data['medication_master_id'] );
                        }
                        $new_medication_name = $new_med->name;
                        
                        // new dosage
                        $new_medication_dosage = $alt_data['dosage'];
                        
                        // new comments
                        $new_medication_comments = $alt_data['comments'];
                        
                            // new change date
                        if($alt_data['medication_change'] != "0000-00-00 00:00:00")
                        {
                            $medication_change_date_str = date("d.m.Y", strtotime($alt_data['medication_change']));
                        }
                        else
                        {
                            $medication_change_date_str="";
                        }
                        
                        // new comments
                        if(strlen($alt_data['days_interval']) > 0){
                            $new_medication_days_interval = " | ".$alt_data['days_interval'];
                        }
                        $new_medication_days_interval_technical = '';
                        if(strlen($alt_data['days_interval_technical']) > 0){
                            $new_medication_days_interval_technical = " | ".$alt_data['days_interval_technical'];
                        }
                        
                        
                        
                        if( $alt_data['scheduled'] == 1 )
                        {
                            $new_entry = $prefix.$new_medication_name.$new_medication_days_interval . $new_medication_days_interval_technical;
                        }
                        else
                        {
                            
                            if(strlen($new_medication_dosage)>0)
                            {
                                $new_entry = $prefix.$new_medication_name."  |  ".$new_medication_dosage." | ". $new_medication_comments." | ".$medication_change_date_str;
                            }
                            else
                            {
                                $new_entry = $prefix.$new_medication_name." | ".$new_medication_comments." | ".$medication_change_date_str;
                            }
                        }                         
                        
                        $user_name = $users_details['first_name'].' '.$users_details['last_name'];
                
                        if($alt_data['status'] == "new")
                        {
                            $course_entry = 'Benutzer '.$user_name.' lehnte die neue Medikation ab: '.$new_entry.' ';
                        }
                        elseif($alt_data['status'] == "delete")
                        {
                            $course_entry = 'Benutzer '.$user_name.' lehnt die Löschung der Medikation ab: '.$old_entry.' ';
                        }
                        elseif($alt_data['status'] == "renew")
                        {
                            $course_entry = 'Benutzer '.$user_name.' verweigerte das Wiederansetzen von: '.$old_entry.' '; // !!!!!!!!!!!!
                        }
                        else
                        {
                            $course_entry = ' Benutzer '.$user_name.' lehnte die Änderung der Medikation ab: '.$old_entry.' -->  '.$new_entry.' ';
                        }
                        
                        $insert_pc = new PatientCourse();
                        $insert_pc->ipid = $ipid;
                        $insert_pc->course_date = date("Y-m-d H:i:s", time());
                        $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
//                         $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
                        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_denied");
                        $insert_pc->recordid = $recordid;
                        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($course_entry));
                        $insert_pc->user_id = $userid;
                        $insert_pc->save();
                    }
                } 
            }
        }
        // write in patient course 
    }
	
	public function  apply_pump_medication_change($ipid,$userid,$post)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    // get user details
	    $master_user_details = new User();
	    $users_details_arr = $master_user_details->getUserDetails($userid);
	    $users_details = $users_details_arr[0];
	    
        // get data from patient drugplan alt cocktails
        $cocktailid = $post['recordid']; // cocktailid
        $alt_id = $post['alt_id'];
        
        $alt_data_array = PatientDrugPlanAltCocktails::get_drug_cocktail_details_alt($ipid,$cocktailid,$alt_id);
          
 
        $approve = "0";
        $decline = "0";
        if($alt_data_array)
        {
            foreach($alt_data_array as $alt_id => $alt_data)
            {
                $cust_alt = Doctrine::getTable('PatientDrugPlanAltCocktails')->find($alt_data['id']);
                
                if($cust_alt)
                {
                    if($post['action'] == "approve")
                    {
                        $approve = "1";
                        $decline = "0";
                        $cust_alt->approved = "1";
                        $cust_alt->approval_user = $userid;
                        $cust_alt->approval_date = date("Y-m-d H:i:s",time());
                        $cust_alt->save();
                    }
                    else
                    {
                        $approve = "0";
                        $decline = "1";
                        
                        if($cust_alt->status == "delete" || $cust_alt->status == "edit"  || $cust_alt->status == "renew"){
                            $cust_alt->inactive = "1";
                        }
                        $cust_alt->declined = "1";
                        $cust_alt->decline_user = $userid;
                        $cust_alt->decline_date = date("Y-m-d H:i:s",time());
                        $cust_alt->save();
                    }
                }
                
                // get all active todos where the triger by =  "pumpmedacknowledge-$cocktailid"
                //todos
                $todo_q = Doctrine_Query::create()
                ->select("*")
                ->from('ToDos')
                ->where('client_id="' . $clientid . '"')
                ->andWhere('isdelete="0"')
                ->andWhere('iscompleted="0"')
                ->andWhere('triggered_by="pumpmedacknowledge-'.$cocktailid.'"')
                ->orderBy('create_date DESC');
                $todo_array = $todo_q->fetchArray();
                
                foreach($todo_array as $k=>$todo_data)
                {
                    $todos_ids[] = $todo_data['id'];
                    $todo = Doctrine::getTable('ToDos')->find($todo_data['id']);
                    if($todo)
                    {
                        $todo->iscompleted = '1';
                        $todo->complete_user = $userid;
                        $todo->complete_date = date("Y-m-d H:i:s", time());
                        $todo->save();
                    }
                }
 
                if($approve == "1")
                { // INSET CHANGES IN PATIENTDRUGPLAN
                
                    $alt_id = $alt_data['id'];
                    $cust = Doctrine::getTable('PatientDrugPlanCocktails')->find($alt_data['drugplan_cocktailid']);
                    
                    if($cust)
                    {
                        $old_entry = "";
                        $old_entry = "Kommentar: " .  $cust->description."";
                        $old_entry .= "\nApplikationsweg: " .$cust->pumpe_medication_type;
                        //$old_entry .= "\nFlussrate: " . $cust->flussrate;
                        //ISPC-2684 Lore 08.10.2020
                        if(!empty($cust->flussrate_type)){
                            $old_entry .= "\nFlussrate_simple"." (".$cust->flussrate_type."): " . $cust->flussrate;
                        }else{
                            $old_entry .= "\nFlussrate: " . $cust->flussrate;
                        }
                        //.
                        $old_entry .= "\nTrägerlösung: " .$cust->carrier_solution;
                        
                        if($cust->pumpe_type == "pca")
                        {
                            $old_entry .= "\nBolus: " . $cust->bolus;
                            $old_entry .= "\nMax Bolus: " . $cust->max_bolus;
                            $old_entry .= "\nSperrzeit: " .$cust->sperrzeit;
                        }
                        
                        
                        // ================= Update patient drugplan cocktail item ====================
                        $cust->description = $alt_data['description'];
                        $cust->bolus = $alt_data['bolus'];
                        $cust->max_bolus = $alt_data['max_bolus'];
                        $cust->flussrate = $alt_data['flussrate'];
                        $cust->flussrate_type = $alt_data['flussrate_type'];            //ISPC-2684 Lore 08.10.2020
                        $cust->sperrzeit = $alt_data['sperrzeit'];
                        $cust->carrier_solution = $alt_data['carrier_solution'];
                        $cust->pumpe_medication_type = $alt_data['pumpe_medication_type'];
                        $cust->pumpe_type = $alt_data['pumpe_type'];
                        $cust->save();
                        
                        $existing_drugplan_id = $cust->id;

                        $new_entry = "";
                        $new_entry = "Kommentar: " . $alt_data['description']."";
                        $new_entry .= "\nApplikationsweg: " .$alt_data['pumpe_medication_type'];
                        //$new_entry .= "\nFlussrate: " . $alt_data['flussrate'];
                        //ISPC-2684 Lore 08.10.2020
                        if(!empty($alt_data['flussrate_type'])){
                            $new_entry .= "\nFlussrate_simple"." (".$alt_data['flussrate_type']."): " . $alt_data['flussrate'];
                        }else{
                            $new_entry .= "\nFlussrate: " . $alt_data['flussrate'];
                        }
                        //.
                        $new_entry .= "\nTrägerlösung: " .$alt_data['carrier_solution'];
                        
                        if($alt_data['pumpe_type'] == "pca")
                        {
                            $new_entry .= "\nBolus: " .$alt_data['bolus'];
                            $new_entry .= "\nMax Bolus: " .$alt_data['max_bolus'];
                            $new_entry .= "\nSperrzeit: " .$alt_data['sperrzeit'];
                        }
                        
                        
                        $user_name = $users_details['first_name'].' '.$users_details['last_name'];

                        if($alt_data['status'] == "new")
                        {
                            $course_entry = 'Benutzer '.$user_name.' bestätigte die neue Medikation: '.$new_entry.' ';
                            $med_course_entry =  $new_entry.' ';
                        } 
                        elseif($alt_data['status'] == "delete")
                        {
                            $course_entry = 'Benutzer '.$user_name.' bestätigte die Löschung der Medikation: '.$old_entry.' ';
                            $med_course_entry =  $old_entry.'  wurde abgesetzt';
                        }
                        elseif($alt_data['status'] == "renew")
                        {
                            $course_entry = 'Benutzer '.$user_name.' bestätigte das Wiederansetzen von: '.$old_entry.' ';
                            $med_course_entry = $old_entry.' wurde wieder angesetzt.';// !!!!!!!!!!!!
                        }
                        else
                        {
                           $course_entry = 'Benutzer '.$user_name.' bestätigte die Änderung der Medikation: '.$old_entry.' -->  '.$new_entry.' ';
                           $med_course_entry = 'Änderung: '.$old_entry.' -->  '.$new_entry.' ';
                        }
                        
    
                    
                        
                        $insert_pc = new PatientCourse();
                        $insert_pc->ipid = $ipid;
                        $insert_pc->course_date = date("Y-m-d H:i:s", time());
                        $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
                        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_apprrove");
                        $insert_pc->recordid = $recordid;
                        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes("Q"));
                        $insert_pc->user_id = $userid;
                        $insert_pc->save();
                        

                        $insert_pc = new PatientCourse();
                        $insert_pc->ipid = $ipid;
                        $insert_pc->course_date = date("Y-m-d H:i:s", time());
                        $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
                        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_apprrove");
                        $insert_pc->recordid = $alt_data['drugplan_id'];
                        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($med_course_entry));
                        $insert_pc->user_id = $userid;
                        $insert_pc->save();
                    }
                } // If approve = 1 END
                else
                { // if decline = 1

                    $cust = Doctrine::getTable('PatientDrugPlanCocktails')->find($alt_data['drugplan_cocktailid']);
                    if($cust)
                    {
                        //-------------------------
                        // VERLAUF ENTRY
                        //-------------------------
                        
                        // OLD ENTRY
                        $old_entry = "";
                        $old_entry = "Kommentar: " .  $cust->description."";
                        $old_entry .= "\nApplikationsweg: " .$cust->pumpe_medication_type;
                        //$old_entry .= "\nFlussrate: " . $cust->flussrate;
                        //ISPC-2684 Lore 08.10.2020
                        if(!empty($cust->flussrate_type)){
                            $old_entry .= "\nFlussrate_simple"." (".$cust->flussrate_type."): " . $cust->flussrate;
                        }else{
                            $old_entry .= "\nFlussrate: " . $cust->flussrate;
                        }
                        //.
                        $old_entry .= "\nTrägerlösung: " .$cust->carrier_solution;
                        
                        if($cust->pumpe_type == "pca")
                        {
                            $old_entry .= "\nBolus: " . $cust->bolus;
                            $old_entry .= "\nMax Bolus: " . $cust->max_bolus;
                            $old_entry .= "\nSperrzeit: " .$cust->sperrzeit;
                        }
                        
                        // NEW ENTRY
                        $new_entry = "";
                        $new_entry = "Kommentar: " . $alt_data['description']."";
                        $new_entry .= "\nApplikationsweg: " .$alt_data['pumpe_medication_type'];
                        //$new_entry .= "\nFlussrate: " . $alt_data['flussrate'];
                        //ISPC-2684 Lore 08.10.2020
                        if(!empty($alt_data['flussrate_type'])){
                            $new_entry .= "\nFlussrate_simple"." (".$alt_data['flussrate_type']."): " . $alt_data['flussrate'];
                        }else{
                            $new_entry .= "\nFlussrate: " . $alt_data['flussrate'];
                        }
                        //.
                        $new_entry .= "\nTrägerlösung: " .$alt_data['carrier_solution'];
                        
                        if($alt_data['pumpe_type'] == "pca")
                        {
                            $new_entry .= "\nBolus: " .$alt_data['bolus'];
                            $new_entry .= "\nMax Bolus: " .$alt_data['max_bolus'];
                            $new_entry .= "\nSperrzeit: " .$alt_data['sperrzeit'];
                        }
                        
     
                        
                        $user_name = $users_details['first_name'].' '.$users_details['last_name'];
                
                        if($alt_data['status'] == "new")
                        {
                            $course_entry = 'Benutzer '.$user_name.' lehnte die neue Medikation ab: '.$new_entry.' ';
                        }
                        elseif($alt_data['status'] == "delete")
                        {
                            $course_entry = 'Benutzer '.$user_name.' lehnt die Löschung der Medikation ab: '.$old_entry.' ';
                        }
                        elseif($alt_data['status'] == "renew")
                        {
                            $course_entry = 'Benutzer '.$user_name.' verweigerte das Wiederansetzen von: '.$old_entry.' '; // !!!!!!!!!!!!
                        }
                        else
                        {
                            $course_entry = ' Benutzer '.$user_name.' lehnte die Änderung der Medikation ab: '.$old_entry.' -->  '.$new_entry.' ';
                        }
                        
                        $insert_pc = new PatientCourse();
                        $insert_pc->ipid = $ipid;
                        $insert_pc->course_date = date("Y-m-d H:i:s", time());
                        $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
                        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_denied");
                        $insert_pc->recordid = $recordid;
                        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($course_entry));
                        $insert_pc->user_id = $userid;
                        $insert_pc->save();
                    }
                } 
            }
        }
        // write in patient course 
    }
    
    // ############################################
    // ####### New medication Page ###############
    // ############################################

    public function update_multiple_data($post,$post_ipid = false){ // ISPC 1624 - 08.03.2016
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
    
        if($post_ipid){
            $ipid = $post_ipid;
        }
        $post_dosage_interval = array();
//         print_R($post); exit;
        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
        $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
        
        //ISPC-2554 pct.3 Carmen 27.03.2020
        $atcdet = PatientDrugPlanAtcTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
        $atcindex = 0;
        $toupdate = array();
        $todelete = array();
        //--
    
        $modules = new Modules();
        if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
        {
            $acknowledge = "1";
            if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA')
            {
    
                $allow_change = "1";
            }
            else
            {
                $allow_change = "0";
            }
        }
        else
        {
            $acknowledge = "0";
        }
        
        //ISPC-2829 Ancuta  18.03.2021
        if($post['efaoption']== '1'){  
            $acknowledge = "1";
            $allow_change = "1";
            $change_users[] = $userid;
            
            
            // get the source ipid  - of this patient for NEW medis to be added 
            
 
            
            $p_share_obj = new PatientsShare();
            $p_share_info= $p_share_obj->get_connection_by_ipid($ipid,true);
            if(!empty($p_share_info)){
                foreach($p_share_info as $k=>$share_info){
                    if($share_info['copy_meds'] != "1" ){
                        unset($p_share_info[$k]);
                    }
                }
            }
        }
        // --
        
        // get source patient - and share medications
        
        
        //ISPC-2554 pct.1 Carmen 03.04.2020
        if($modules->checkModulePrivileges("87", $clientid))//mmi activated
        {	        
	        $dosageformmmi = MedicationDosageformMmiTable::getInstance()->getfrommmi();	        
        }
        //--

        //ISPC-2797 Ancuta 17.02.2021
        $elsa_planned_medis = "0";
        if($modules->checkModulePrivileges("250", $clientid)){
            $elsa_planned_medis = "1";
        } 
        if($acknowledge == "1" && !in_array($userid,$approval_users))//Medication acknowledge
        {
    
            if($allow_change == "1")
            {
                // get user details
                $master_user_details = new User();
                $users_details_arr = $master_user_details->getUserDetails($userid);
                $users_details = $users_details_arr[0];
                $user_name = $users_details['first_name'].' '.$users_details['last_name'];
                 
                // get patient details
                $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
                $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
    
                foreach ($post['hidd_medication'] as $i => $med_item)
                {
                    $update_medication[$i] = "0";
    
                    if ($post['hidd_medication'][$i] > 0)
                    {
                        $medid = $post['hidd_medication'][$i];
                    }
                    else
                    {
                        $medid = $post['newhidd_medication'][$i];
                    }
    
                    if (empty($post['verordnetvon'][$i]))
                    {
                        $post['verordnetvon'][$i] = 0;
                    }
    
                    //ISPC-2554 Carmen pct.3 27.03.2020
                    $medmasterid = $medid;
                    //--
                    
                    // DOSAJE
                    $post_dosaje[$i] = "";
                    $post_dosage_interval[$i] = null;
 
                    //ISPC-2524 pct.2)  Lore 16.01.2020
                    if( (isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1") 
                        || (isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1") ){
                        $post['dosage'][$i] = array();
                        //TODO-3624 Ancuta 23.11.2020
                        $post['dosage_full'][$i] = array();
                        $post['dosage_concentration'][$i] = array();
                        $post['dosage_concentration_full'][$i] = array();
                        //--
                        $post['dosage_interval'][$i] = null;
                        $post['dosage_unit'][$i] = "";
                        $post['has_interval'][$i] = 0;
                        $post['days_interval'][$i] = array();
                        $post['unit'][$i] = "";
                        $post['type'][$i] = "";
                        $post['dosage_form'][$i] = "";
                        $post['concentration'][$i] = "";
                    }
                    //.
                    
                    if($post['dosage']) //TODO-2982 Carmen 19.03.2020
                    {
	                    if(is_array($post['dosage'][$i]))
	                    { // NEW style
	                        foreach ($post['dosage'][$i] as $time => $dosage_value)
	                        {
	                            if(strlen($dosage_value) == 0){
	                                // $dosage_value = " / ";
	                                $dosage_value = "";
	                            }
	                        
	                            $old_dosage_array[$i][] = $dosage_value;
	                        }
	                        $post_dosaje[$i] = implode("-",$old_dosage_array[$i]);
	                        
	                        // PATIENT TIME SCHEME
	                        $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid);
	                        
	                        if(empty($patient_time_scheme['patient'])){
	                            // insert in patient time scheme
	                        
	                            foreach ($post['dosage'][$i] as $time => $dosage_value)
	                            {
	                                $insert_pc = new PatientDrugPlanDosageIntervals();
	                                $insert_pc->ipid = $ipid;
	                                $insert_pc->time_interval = $time.":00";
	                                $insert_pc->save();
	                            }
	                        }
	                        
	                    }
	                    else
	                    { // OLD style
	                        $post_dosaje[$i] = $post['dosage'][$i];
	                        $post_dosage_interval[$i] = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
	                        
	                    }
                    } //--
                    
                    $cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$i]);
    
                    if($cust)
                    {
                    	
                    	//ISPC-2554 Carmen pct.3 27.03.2020
                    	$drugplanid = $cust->id;
                    	//--
                        // get dosage
                        $dosage_array  = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid,$post['drid'][$i]);
                        
                        // get extra data
                        $extra_data = array();
                        $extra_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$post['drid'][$i]);
                        $extra_data = $extra_array[$post['drid'][$i]]; 
                        
                        // Get existing data and form a string
                        $existing_string = "";
                        if($cust->treatment_care != "1"){
                            $existing_string .= $cust->dosage;
                        }
                        
                        $existing_string .= $cust->medication_master_id;
                        $existing_string .= $cust->verordnetvon;
                        $existing_string .= $cust->comments;
                        
                        if($cust->treatment_care != "1" && $cust->scheduled != "1")
                        {
                            $existing_string .= $extra_data['drug'];
                            $existing_string .= $extra_data['unit_id'] ? $extra_data['unit_id']:"0";
                            
                            $existing_string .= $extra_data['unit_dosage'];        //ISPC-2684 Lore 08.10.2020
                            $existing_string .= $extra_data['unit_dosage_24'];     //ISPC-2684 Lore 08.10.2020
                            
                            $existing_string .= $extra_data['type_id'] ? $extra_data['type_id']:"0";
                            $existing_string .= $extra_data['indication'] ? trim($extra_data['indication']):"0";
                            $existing_string .= $extra_data['dosage_form'] ? $extra_data['dosage_form']:"0";
                            $existing_string .= $extra_data['concentration'];
                            if($cust->has_interval == "1")
                            {
                                $existing_string .= $cust->days_interval;
                                if($cust->administration_date !="0000-00-00 00:00:00")
                                {
                                    $existing_string .= date("d.m.Y",strtotime($cust->administration_date));
                                } else {
                                    $existing_string .= "00.00.0000";
                                }
                            }
                            
                            if ($cust->isintubated == 1){ // ISPC-2176
                                $existing_string .= $extra_data['packaging'] ? $extra_data['packaging'] : "0"; ;
                                $existing_string .= $extra_data['kcal'];
                                $existing_string .= $extra_data['volume'];
                            }
                            
                        }
                        elseif($cust->scheduled == "1")
                        {
                            $existing_string .= $extra_data['indication'] ? trim($extra_data['indication']):"0";
                            $existing_string .= $extra_data['days_interval'];
                            $existing_string .= ! empty($extra_data['days_interval_technical']) ? $extra_data['days_interval_technical'] : '';
                            $existing_string .= $extra_data['administration_date'];
                        }
                        
                        if ($cust->isbedarfs == 1 || $cust->iscrisis == 1 || ($cust->isbedarfs == 0 && $cust->iscrisis == 0 && $cust->isschmerzpumpe == 0 && $cust->treatment_care == 0 && $cust->isnutrition == 0 && $cust->isintubated == 0 && $cust->scheduled == 0)){ // ISPC-2247+TODO-3247 Carmen 03.07.2020
                            $existing_string .= $extra_data['escalation'];
                        }
                        
                        $existing_string_no_importance = $existing_string; 
                        $existing_string .= $extra_data['importance'];
                        
                        
                        $existing_date = strtotime(date('d.m.Y',strtotime($cust->medication_change)));
                        
                        // Get posted data and form a string
                        $post_string = "";
                        if($cust->treatment_care != "1"){
                            $post_string .= $post_dosaje[$i];
                        }
                        
                        $post_string .= $medid;
                        $post_string .= $post['comments'][$i];
                        $post_string .= $post['verordnetvon'][$i];
                        
 
                        if($cust->treatment_care != "1" && $cust->scheduled != "1")
                        {
                            $post_string .= $post['drug'][$i];
                            $post_string .= $post['unit'][$i];
                            
                            $post_string .= $post['dosage_24h'][$i];        //ISPC-2684 Lore 05.10.2020
                            $post_string .= $post['unit_dosage'][$i];        //ISPC-2684 Lore 05.10.2020
                            $post_string .= $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                            
                            $post_string .= $post['type'][$i];
                            $post_string .= trim($post['indication'][$i]);
                            $post_string .= $post['dosage_form'][$i];
                            $post_string .= $post['concentration'][$i];
                            
                            if ($post['has_interval'][$i] == "1"){
                                $post_string .= $post['days_interval'][$i];
                                $post_string .= $post['administration_date'][$i];
                            }
                            
                            if ($cust->isintubated == 1){ // ISPC-2176
                                $post_string .= $post['packaging'][$i];
                                $post_string .= $post['kcal'][$i];
                                $post_string .= $post['volume'][$i];
                            }     

                            
                        }
                        elseif ($cust->scheduled == "1")
                        {
                            $post_string .= trim($post['indication'][$i]);
                            $post_string .= $post['days_interval'][$i];
                            $post_string .= ! empty($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : '';
                            $post_string .= $post['administration_date'][$i];
                        } 
                        
                        if ($cust->isbedarfs == 1 || $cust->iscrisis == 1 || ($cust->isbedarfs == 0 && $cust->iscrisis == 0 && $cust->isschmerzpumpe == 0 && $cust->treatment_care == 0 && $cust->isnutrition == 0 && $cust->isintubated == 0 && $cust->scheduled == 0)){ // ISPC-2247+TODO-3247 Carmen 03.07.2020
                            $post_string .= $post['escalation'][$i];
                        }
                        $post_string_no_importance = $post_string;
                        $post_string .= $post['importance'][$i];
                            
                        
                        $post_date = strtotime($post['medication_change'][$i]);
                        
                        if( ($existing_date != $post_date || $existing_string != $post_string ) && $post['edited'][$i] == '1' )
                        { //check to update only what's modified
    
                            if($existing_string_no_importance == $post_string_no_importance)
                            {
                                $skip_verlauf = "1"; 
                            } 
                            else 
                            {
                                $skip_verlauf = "0"; 
                            }
                            
                            $update_medication[$i] = "1";
    
                            if(!empty($post['medication_change'][$i])){
                                //check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
                                if ($existing_string != $post_string)
                                {
                                    if ($post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
                                        $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                                    } elseif ($post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
                                        $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                                    } elseif ($post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
                                        $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                                    } else {
                                        $medication_change_date[$i] = date('Y-m-d 00:00:00');
                                    }
    
                                    // if no medication details were modified - check in the "last edit date" was edited
                                } else if(
                                    ( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
                                    ( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
                                    ( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
                                {
    
                                    $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    
                                } else if(
                                    ( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
                                    ( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
                                    ( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) )
                                )
                                {
                                    $update_medication[$i] = "0";
                                }
    
                                // if "last edit date was edited - save current date"
                            } else {
                                $medication_change_date[$i] = date('Y-m-d 00:00:00');
                            }
                        }
                        else {
                            $update_medication[$i] = "0";
                        }

                        
                        // ================= Update patient drugplan item ====================
                        if($update_medication[$i] == "1"){
    
                            if( $cust->isivmed == 0 &&  $cust->isbedarfs == 1 &&  $cust->isschmerzpumpe == 0 &&  $cust->treatment_care == 0  && $cust->scheduled == 0 )
                            {
                                $shortcut = "N";
                            }
                            elseif($cust->isivmed  == 1 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0  && $cust->scheduled == 0 )
                            {
                                $shortcut = "I";
                            }
                            elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 1 &&  $cust->treatment_care== 0  && $cust->scheduled == 0 )
                            {
                                $shortcut = "Q";
                                $prefix = "Schmerzpumpe ";
                            }
                            elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 1 && $cust->scheduled == 0 )
                            {
                                $shortcut = "BP";
                            }
                            elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 && $cust->scheduled == 1 )
                            {
                                $shortcut = "MI";
                            }
                            elseif($cust->iscrisis  == 1 &&  $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 && $cust->scheduled == 0 )
                            {
                            	$shortcut = "KM";
                            }
                            elseif($cust->isintubated  == 1 && $cust->iscrisis  == 0 &&  $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 && $cust->scheduled == 0 )
                            {
                            	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
                            }
                            else
                            {
                                $shortcut = "M";
                            }
                             
                            //ISPC-2524 pct.2)  Lore 15.01.2020
                            if(isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1" ){
                                $shortcut = "N";
                            }
                            if(isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1" ) {
                                $shortcut = "M";
                            }
                            //.
                            
                            
                            $clear = $this->update_pdpa($ipid, $post['drid'][$i]);
    
    
                            $insert_at = new PatientDrugPlanAlt();
                            $insert_at->ipid = $ipid;
                            $insert_at->drugplan_id = $post['drid'][$i];
                            $insert_at->dosage = $post_dosaje[$i];
                            $insert_at->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
                            $insert_at->medication_master_id = $medid;
                            $insert_at->isbedarfs =$cust->isbedarfs;
                            $insert_at->iscrisis =$cust->iscrisis;
                            $insert_at->isivmed = $cust->isivmed;
                            if ($cust->isschmerzpumpe == 1)
                            {
                                $insert_at->isschmerzpumpe = $cust->isschmerzpumpe;
                                $insert_at->cocktailid = $cocktailId;
                            }

                            $insert_at->treatment_care = $cust->treatment_care;
                            $insert_at->isnutrition = $cust->isnutrition;
                            $insert_at->isintubated = $cust->isintubated;
    
                            $insert_at->scheduled = $cust->scheduled;
                            if(isset($post['has_interval'][$i])){
                                $insert_at->has_interval = $post['has_interval'][$i];
                            }
                            else
                            {
                                $insert_at->has_interval = "0";
                            }
                            $insert_at->days_interval = $post['days_interval'][$i];
                            
                            $insert_at->days_interval_technical = ! empty($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : null;
                            $insert_at->dosage_product = ! empty($post['dosage_product'][$i]) ? $post['dosage_product'][$i] : null;
                            
                            if(strlen($post['administration_date'][$i]) > 0 ){
                                $insert_at->administration_date = date("Y-m-d 00:00:00",strtotime($post['administration_date'][$i]));
                            } else{
                                $insert_at->administration_date = "0000-00-00 00:00:00";
                            }
                                
                            $insert_at->verordnetvon = $post['verordnetvon'][$i];
                            $insert_at->comments = $post['comments'][$i];
                            $insert_at->medication_change = $medication_change_date[$i];
                            $insert_at->status = "edit";
                            
                            
                            //ISPC-2524 pct.2)  Lore 15.01.2020
                            if(isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1" ){
                                $insert_at->isbedarfs = 0 ;
                            }
                            if(isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1" ){
                                $insert_at->isbedarfs = 1 ;
                            }
                            if( (isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1") || (isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1") ){
                                
                                $was_moved = "medication was moved ";
                                
                                $cust_trans = new PatientDrugplanTransition();
                                $cust_trans->ipid = $ipid;
                                $cust_trans->drugplan_id = $post['drid'][$i];
                                if(isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1" ){
                                    $cust_trans->transition = "bedarf_to_activ" ;
                                    $was_moved .= "from bedarf to active";
                                }
                                if(isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1" ){
                                    $cust_trans->transition = "activ_to_bedarf" ;
                                    $was_moved .= "from active to bedarf";
                                }
                                $cust_trans->save();
                                
                                $history_tr = new PatientDrugPlanHistory();
                                $history_tr->ipid = $ipid;
                                $history_tr->pd_id = $cust->id;
                                $history_tr->pd_medication_master_id = $cust->medication_master_id ;
                                $history_tr->pd_medication_name = $was_moved ;
                                $history_tr->istransition = "1" ;
                                $history_tr->pd_create_date = $cust->create_date;
                                $history_tr->pd_create_user = $cust->create_user;
                                $history_tr->pd_change_date = $cust->change_date;
                                $history_tr->pd_change_user = $cust->change_user;
                                $history_tr->save();
                            }
                            //.
                            
                            
                            $insert_at->save();
                            $insertedIds[] = $insert_at->id;
                            $recordid = $insert_at->id;
    
                            // add in dosage alt
                            
                            if(is_array($post['dosage'][$i]))
                            { // NEW style
                
                                foreach ($post['dosage'][$i] as $time => $dosage_value)
                                {
                                    //  insert new lines
                                    $cust_pdd = new PatientDrugPlanDosageAlt();
                                    $cust_pdd->ipid = $ipid;
                                    $cust_pdd->drugplan_id_alt = $recordid;
                                    $cust_pdd->drugplan_id = $post['drid'][$i];
                                    $cust_pdd->dosage = $dosage_value;
                                    $cust_pdd->dosage_time_interval = $time.":00";
                                    $cust_pdd->save();
                                }
                            }
                            
                            // add extra data
                            $cust_pde = new PatientDrugPlanExtraAlt();
                            $cust_pde->ipid = $ipid;
                            $cust_pde->drugplan_id_alt = $recordid;
                            $cust_pde->drugplan_id = $post['drid'][$i];
                            $cust_pde->drug = $post['drug'][$i];
                            
                            $cust_pde->dosage_24h_manual = $post['dosage_24h'][$i];       //ISPC-2684 Lore 05.10.2020
                            $cust_pde->unit_dosage = $post['unit_dosage'][$i];            //ISPC-2684 Lore 05.10.2020
                            $cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                            
                            //$cust_pde->unit = $post['unit'][$i]; //ISPC-2554
                            $cust_pde->type = $post['type'][$i];
                            $cust_pde->indication = trim($post['indication'][$i]);
                            $cust_pde->importance = $post['importance'][$i];
                            
                            //ISPC-2554 pct.1 Carmen 03.04.2020
                            if(substr($post['dosage_form'][$i], 0, 3) == 'mmi')
                            {
                            	$data['clientid'] = $clientid;
                            	$data['isfrommmi'] = '1';
                            	$data['mmi_code'] = substr($post['dosage_form'][$i], 4);
                            	$data['extra'] = '1';
                            	$data['dosage_form'] = $dosageformmmi[$data['mmi_code']]['dosageform_name'];
                            	$dosagecustentity = MedicationDosageformTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'mmi_code'), array($clientid, substr($post['dosage_form'][$i], 4)), $data);
                            	if($dosagecustentity)
                            	{
                            		$cust_pde->dosage_form = $dosagecustentity->id;
                            	}
                            }
                            else 
                            {
                            	$cust_pde->dosage_form = $post['dosage_form'][$i];
                            }
                            //--
                            //ISPC-2554 Carmen 12.05.2020
                            if(substr($post['unit'][$i], 0, 3) == 'mmi')
                            {
                            	$data['clientid'] = $clientid;
                            	$data['unit'] = substr($post['unit'][$i], 4);
                            	$unitcustentity = MedicationUnitTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'unit'), array($clientid, substr($post['unit'][$i], 4)), $data);
                            	if($unitcustentity)
                            	{
                            		$cust_pde->unit = $unitcustentity->id;
                            	}
                            }
                            else
                            {
                            	$cust_pde->unit = $post['unit'][$i];
                            }
                            //--
                            $cust_pde->concentration= $post['concentration'][$i];
                            // ISPC-2176 p6
                            $cust_pde->packaging= $post['packaging'][$i];
                            $cust_pde->kcal= $post['kcal'][$i];
                            $cust_pde->volume= $post['volume'][$i];
                           
                            // ISPC-2247
                            $cust_pde->escalation = $post['escalation'][$i];
                            //--
                            $cust_pde->save();
                            
                            
                            // TODO-2785 Lore 10.01.2020
                            // OLD ENTRY
                            // old medication name
                            
                            //$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                            //$old_med_name[$i] = $old_med->name;
							//TODO-2785 Lore 18.02.2020
                            
                            if( $cust->treatment_care == 1 )
                            {
                                $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id);
                            }
                            elseif( $cust->isnutrition == 1 )
                            {
                                $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id);
                            }
                            else
                            {
                                $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
                            }
                            $old_med_name[$i] = $old_med->name;
                            //.  TODO-2785 Lore 10.01.2020 
    
                            // old dosage
                            if($cust->dosage) {
                                $old_med_dosage[$i] = $cust->dosage;
                            }
    
                            // old comment
                            if($cust->comments ){
                                $old_med_comments[$i] = $cust->comments." | ";
                            }
                            	
                            //  old medication date
                            if($cust->medication_change != "0000-00-00 00:00:00")
                            {
                                $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->medication_change));
                            }
                            else
                            {
                                if($cust->change_date != "0000-00-00 00:00:00")
                                {
                                    $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->change_date));
                                }
                                else
                                {
                                    $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->create_date));
                                }
                            }
                            
                            // old comment
                            if($cust->days_interval ){
                                $old_med_days_interval[$i] = " | ".$cust->days_interval;
                            }
                            $old_med_days_interval_technical[$i] = '';
                            if($cust->days_interval_technical ){
                                $old_med_days_interval_technical[$i] = " | ".$cust->days_interval_technical;
                            }
                                                      
                            if($cust->scheduled == "1"){
                                
                                    $old_entry[$i] = $prefix.$old_med_name[$i].$old_med_days_interval[$i] .  $old_med_days_interval_technical[$i];
                            }
                            else
                            {
                                if(strlen($old_med_dosage[$i])>0){
                                    $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
                                } else	{
                                    $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
                                }
                            }
    
                            // NEW ENTRY
                            // new name
                            if( $post['treatment_care'] == 1 )
                            {
                                $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
                            }
                            elseif( $post['isnutrition'] == 1 )
                            {
                                $new_med = Doctrine::getTable('Nutrition')->find($medid);
                            }
                            else
                            {
                                $new_med = Doctrine::getTable('Medication')->find($medid);
                            }
    
                            $new_medication_name[$i] = $new_med->name;
    
                            // new dosage
                            $new_medication_dosage[$i] = $post['dosage'][$i];
    
                            // new comments
                            $new_medication_comments[$i] = $post['comments'][$i];
    
                            // new change date
                            if($medication_change_date[$i] != "0000-00-00 00:00:00"){
                                $medication_change_date_str[$i] = date("d.m.Y", strtotime($medication_change_date[$i]));
                            }
                            else
                            {
                                $medication_change_date_str[$i]="";
                            }
    
                            // new days_interval
                            $new_medication_days_interval[$i] = " | ".$post['days_interval'][$i];
                            $new_medication_days_interval_technical[$i] = ! empty($post['days_interval_technical'][$i]) ? " | ".$post['days_interval_technical'][$i] : '';
                            
                            if($cust->scheduled == "1")
                            {
                                $new_entry[$i] = $prefix.$new_medication_name[$i].$new_medication_days_interval[$i] . $new_medication_days_interval_technical[$i];
                            }
                            else
                            {
                            
                                if(strlen($new_medication_dosage[$i])>0)
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                                else
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                            }
    
//                             if($skip_verlauf  == 0 ){ 
                                $attach = 'OHNE FREIGABE: ' . $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
                                $insert_pc = new PatientCourse();
                                $insert_pc->ipid = $ipid;
                                $insert_pc->course_date = date("Y-m-d H:i:s", time());
                                $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
    //                             $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
                                $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_edit_med");
                                $insert_pc->recordid = $recordid;
                                $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                                $insert_pc->user_id = $userid;
                                $insert_pc->save();
        
                                // SEND MESSAGE
                                $text  = "";
                                $text .= "Patient ".$patient_name." \n ";
                                $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                                $text .= $old_entry[$i] . "  -> " .  $new_entry[$i]." \n ";
                                $mess = Messages::medication_acknowledge_messages($ipid,$text);
        
                                // CREATE TODO
                                $text_todo  = "";
                                $text_todo .= "Patient ".$patient_name." <br/>";
                                $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
                                $text_todo .= $old_entry[$i] . "  -> " .  $new_entry[$i]." <br/>";
        
                                $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $post['drid'][$i], $recordid);
//                             }

                                //ISPC-2829 Ancuta 06.04.2021
                                if($post['efaoption']== '1' && !empty($cust->source_drugplan_id) && !empty($cust->source_ipid)){
                                    // get client of source ipid
                                    $patient = Doctrine_Query::create()
                                    ->select('*')
                                    ->from('EpidIpidMapping')
                                    ->where("ipid =?", $cust->source_ipid);
                                    $patient_details = $patient->fetchArray();
                                    
                                    if( empty($patient_details)){
                                        
                                    }
                                    $source_client = $patient_details['0']['clientid'];
                                    
                                    $clear = $this->update_pdpa($cust->source_ipid, $cust->source_drugplan_id);
            
            
                                    $insert_at = new PatientDrugPlanAlt();
                                    $insert_at->ipid = $cust->source_ipid;
                                    $insert_at->drugplan_id = $cust->source_drugplan_id;
                                    $insert_at->dosage = $post_dosaje[$i];
                                    $insert_at->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
                                    $insert_at->medication_master_id = $medid;
                                    $insert_at->isbedarfs =$cust->isbedarfs;
                                    $insert_at->iscrisis =$cust->iscrisis;
                                    $insert_at->isivmed = $cust->isivmed;
                                    if ($cust->isschmerzpumpe == 1)
                                    {
                                        $insert_at->isschmerzpumpe = $cust->isschmerzpumpe;
                                        $insert_at->cocktailid = $cocktailId;
                                    }
        
                                    $insert_at->treatment_care = $cust->treatment_care;
                                    $insert_at->isnutrition = $cust->isnutrition;
                                    $insert_at->isintubated = $cust->isintubated;
            
                                    $insert_at->scheduled = $cust->scheduled;
                                    if(isset($post['has_interval'][$i])){
                                        $insert_at->has_interval = $post['has_interval'][$i];
                                    }
                                    else
                                    {
                                        $insert_at->has_interval = "0";
                                    }
                                    $insert_at->days_interval = $post['days_interval'][$i];
                                    
                                    $insert_at->days_interval_technical = ! empty($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : null;
                                    $insert_at->dosage_product = ! empty($post['dosage_product'][$i]) ? $post['dosage_product'][$i] : null;
                                    
                                    if(strlen($post['administration_date'][$i]) > 0 ){
                                        $insert_at->administration_date = date("Y-m-d 00:00:00",strtotime($post['administration_date'][$i]));
                                    } else{
                                        $insert_at->administration_date = "0000-00-00 00:00:00";
                                    }
                                        
                                    $insert_at->verordnetvon = $post['verordnetvon'][$i];
                                    $insert_at->comments = $post['comments'][$i];
                                    $insert_at->medication_change = $medication_change_date[$i];
                                    $insert_at->status = "edit";
                                    
                                    
                                    //ISPC-2524 pct.2)  Lore 15.01.2020
                                    if(isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1" ){
                                        $insert_at->isbedarfs = 0 ;
                                    }
                                    if(isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1" ){
                                        $insert_at->isbedarfs = 1 ;
                                    }
                                    
                                    $insert_at->related_drugplan_id = $post['drid'][$i];
                                    $insert_at->related_alt_id = $recordid;
                                    
                                    
                                    
                                    if( (isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1") || (isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1") ){
                                        
                                        $was_moved = "medication was moved ";
                                        
                                        $cust_trans = new PatientDrugplanTransition();
                                        $cust_trans->ipid = $cust->source_ipid;
                                        $cust_trans->drugplan_id = $cust->source_drugplan_id;
                                        if(isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1" ){
                                            $cust_trans->transition = "bedarf_to_activ" ;
                                            $was_moved .= "from bedarf to active";
                                        }
                                        if(isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1" ){
                                            $cust_trans->transition = "activ_to_bedarf" ;
                                            $was_moved .= "from active to bedarf";
                                        }
                                        $cust_trans->save();
                                        
                                        $history_tr = new PatientDrugPlanHistory();
                                        $history_tr->ipid = $cust->source_ipid;
                                        $history_tr->pd_id = $cust->id;
                                        $history_tr->pd_medication_master_id = $cust->medication_master_id ;
                                        $history_tr->pd_medication_name = $was_moved ;
                                        $history_tr->istransition = "1" ;
                                        $history_tr->pd_create_date = $cust->create_date;
                                        $history_tr->pd_create_user = $cust->create_user;
                                        $history_tr->pd_change_date = $cust->change_date;
                                        $history_tr->pd_change_user = $cust->change_user;
                                        $history_tr->save();
                                    }
                                    //.
                                    
                                    
                                    $insert_at->save();
                                    $insertedIds[] = $insert_at->id;
                                    $recordid_efa = $insert_at->id;
            
                                    // add in dosage alt
                                    
                                    if(is_array($post['dosage'][$i]))
                                    { // NEW style
                        
                                        foreach ($post['dosage'][$i] as $time => $dosage_value)
                                        {
                                            //  insert new lines
                                            $cust_pdd = new PatientDrugPlanDosageAlt();
                                            $cust_pdd->ipid = $cust->source_ipid;
                                            $cust_pdd->drugplan_id_alt = $recordid_efa;
                                            $cust_pdd->drugplan_id = $cust->source_drugplan_id;
                                            $cust_pdd->dosage = $dosage_value;
                                            $cust_pdd->dosage_time_interval = $time.":00";
                                            $cust_pdd->save();
                                        }
                                    }
                                    
                                    // add extra data
                                    $cust_pde = new PatientDrugPlanExtraAlt();
                                    $cust_pde->ipid = $cust->source_ipid;
                                    $cust_pde->drugplan_id_alt = $recordid_efa;
                                    $cust_pde->drugplan_id = $cust->source_drugplan_id;
                                    $cust_pde->drug = $post['drug'][$i];
                                    
                                    $cust_pde->dosage_24h_manual = $post['dosage_24h'][$i];       //ISPC-2684 Lore 05.10.2020
                                    $cust_pde->unit_dosage = $post['unit_dosage'][$i];            //ISPC-2684 Lore 05.10.2020
                                    $cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                                    
                                    //$cust_pde->unit = $post['unit'][$i]; //ISPC-2554
                                    $cust_pde->type = $post['type'][$i];
                                    $cust_pde->indication = trim($post['indication'][$i]);
                                    $cust_pde->importance = $post['importance'][$i];
                                    
                                    //ISPC-2554 pct.1 Carmen 03.04.2020
                                    if(substr($post['dosage_form'][$i], 0, 3) == 'mmi')
                                    {
                                    	$data['clientid'] = $source_client;
                                    	$data['isfrommmi'] = '1';
                                    	$data['mmi_code'] = substr($post['dosage_form'][$i], 4);
                                    	$data['extra'] = '1';
                                    	$data['dosage_form'] = $dosageformmmi[$data['mmi_code']]['dosageform_name'];
                                    	$dosagecustentity = MedicationDosageformTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'mmi_code'), array($source_client, substr($post['dosage_form'][$i], 4)), $data);
                                    	if($dosagecustentity)
                                    	{
                                    		$cust_pde->dosage_form = $dosagecustentity->id;
                                    	}
                                    }
                                    else 
                                    {
                                    	$cust_pde->dosage_form = $post['dosage_form'][$i];
                                    }
                                    //--
                                    //ISPC-2554 Carmen 12.05.2020
                                    if(substr($post['unit'][$i], 0, 3) == 'mmi')
                                    {
                                    	$data['clientid'] = $source_client;
                                    	$data['unit'] = substr($post['unit'][$i], 4);
                                    	$unitcustentity = MedicationUnitTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'unit'), array($source_client, substr($post['unit'][$i], 4)), $data);
                                    	if($unitcustentity)
                                    	{
                                    		$cust_pde->unit = $unitcustentity->id;
                                    	}
                                    }
                                    else
                                    {
                                    	$cust_pde->unit = $post['unit'][$i];
                                    }
                                    //--
                                    $cust_pde->concentration= $post['concentration'][$i];
                                    // ISPC-2176 p6
                                    $cust_pde->packaging= $post['packaging'][$i];
                                    $cust_pde->kcal= $post['kcal'][$i];
                                    $cust_pde->volume= $post['volume'][$i];
                                   
                                    // ISPC-2247
                                    $cust_pde->escalation = $post['escalation'][$i];
                                    //--
                                    $cust_pde->save();
                                    
                                    
                                    // TODO-2785 Lore 10.01.2020
                                    // OLD ENTRY
                                    // old medication name
                                    
                                    //$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                                    //$old_med_name[$i] = $old_med->name;
        							//TODO-2785 Lore 18.02.2020
                                    
                                    if( $cust->treatment_care == 1 )
                                    {
                                        $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id);
                                    }
                                    elseif( $cust->isnutrition == 1 )
                                    {
                                        $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id);
                                    }
                                    else
                                    {
                                        $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
                                    }
                                    $old_med_name[$i] = $old_med->name;
                                    //.  TODO-2785 Lore 10.01.2020 
            
                                    // old dosage
                                    if($cust->dosage) {
                                        $old_med_dosage[$i] = $cust->dosage;
                                    }
            
                                    // old comment
                                    if($cust->comments ){
                                        $old_med_comments[$i] = $cust->comments." | ";
                                    }
                                    	
                                    //  old medication date
                                    if($cust->medication_change != "0000-00-00 00:00:00")
                                    {
                                        $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->medication_change));
                                    }
                                    else
                                    {
                                        if($cust->change_date != "0000-00-00 00:00:00")
                                        {
                                            $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->change_date));
                                        }
                                        else
                                        {
                                            $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->create_date));
                                        }
                                    }
                                    
                                    // old comment
                                    if($cust->days_interval ){
                                        $old_med_days_interval[$i] = " | ".$cust->days_interval;
                                    }
                                    $old_med_days_interval_technical[$i] = '';
                                    if($cust->days_interval_technical ){
                                        $old_med_days_interval_technical[$i] = " | ".$cust->days_interval_technical;
                                    }
                                                              
                                    if($cust->scheduled == "1"){
                                        
                                            $old_entry[$i] = $prefix.$old_med_name[$i].$old_med_days_interval[$i] .  $old_med_days_interval_technical[$i];
                                    }
                                    else
                                    {
                                        if(strlen($old_med_dosage[$i])>0){
                                            $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
                                        } else	{
                                            $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
                                        }
                                    }
            
                                    // NEW ENTRY
                                    // new name
                                    if( $post['treatment_care'] == 1 )
                                    {
                                        $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
                                    }
                                    elseif( $post['isnutrition'] == 1 )
                                    {
                                        $new_med = Doctrine::getTable('Nutrition')->find($medid);
                                    }
                                    else
                                    {
                                        $new_med = Doctrine::getTable('Medication')->find($medid);
                                    }
            
                                    $new_medication_name[$i] = $new_med->name;
            
                                    // new dosage
                                    $new_medication_dosage[$i] = $post['dosage'][$i];
            
                                    // new comments
                                    $new_medication_comments[$i] = $post['comments'][$i];
            
                                    // new change date
                                    if($medication_change_date[$i] != "0000-00-00 00:00:00"){
                                        $medication_change_date_str[$i] = date("d.m.Y", strtotime($medication_change_date[$i]));
                                    }
                                    else
                                    {
                                        $medication_change_date_str[$i]="";
                                    }
            
                                    // new days_interval
                                    $new_medication_days_interval[$i] = " | ".$post['days_interval'][$i];
                                    $new_medication_days_interval_technical[$i] = ! empty($post['days_interval_technical'][$i]) ? " | ".$post['days_interval_technical'][$i] : '';
                                    
                                    if($cust->scheduled == "1")
                                    {
                                        $new_entry[$i] = $prefix.$new_medication_name[$i].$new_medication_days_interval[$i] . $new_medication_days_interval_technical[$i];
                                    }
                                    else
                                    {
                                    
                                        if(strlen($new_medication_dosage[$i])>0)
                                        {
                                            $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                        }
                                        else
                                        {
                                            $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                        }
                                    }
            
        //                             if($skip_verlauf  == 0 ){ 
                                        $attach = 'OHNE FREIGABE: ' . $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
                                        $insert_pc = new PatientCourse();
                                        $insert_pc->ipid = $cust->source_ipid;
                                        $insert_pc->course_date = date("Y-m-d H:i:s", time());
                                        $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
            //                             $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
                                        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_edit_med");
                                        $insert_pc->recordid = $recordid_efa;
                                        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                                        $insert_pc->user_id = $userid;
                                        $insert_pc->save();
                
                                        // SEND MESSAGE
                                        $text  = "";
                                        $text .= "Patient ".$patient_name." \n ";
                                        $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                                        $text .= $old_entry[$i] . "  -> " .  $new_entry[$i]." \n ";
                                        $mess = Messages::medication_acknowledge_messages($cust->source_ipid,$text);
                
                                        // CREATE TODO
                                        $text_todo  = "";
                                        $text_todo .= "Patient ".$patient_name." <br/>";
                                        $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
                                        $text_todo .= $old_entry[$i] . "  -> " .  $new_entry[$i]." <br/>";
                
                                        $todos = Messages::medication_acknowledge_todo($cust->source_ipid, $text_todo, $cust->source_drugplan_id, $recordid_efa);
        //                             }
                                    
                                    
                                }
                                
                                
                        }
                    }
                    else
                    {//insert new
                        if($medid > '0')
                        {
    
                            if ($post['isschmerzpumpe'] == 1)
                            {
                                $cocktailId[$key] = $cocktailId;
                            }
    
                            if($post['done_date'])
                            {
                                $medication_change[$key] = date('Y-m-d H:i:s', strtotime($post['done_date']));
                            }
                            else
                            {
                                $medication_change[$key] = date('Y-m-d 00:00:00');
                            }
    
                            $ins_pat_drug_plan = new PatientDrugPlan();
                            $ins_pat_drug_plan->ipid = $ipid;
                            if($post['dosage']) //TODO-2982 Carmen 19.03.2020
                            {
                            	$ins_pat_drug_plan->dosage = $post_dosaje[$i];
                            }
                            $ins_pat_drug_plan->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
                            $ins_pat_drug_plan->medication_master_id = $medid;
                            $ins_pat_drug_plan->isbedarfs = $post['isbedarfs'];
                            $ins_pat_drug_plan->iscrisis = $post['iscrisis'];
                            $ins_pat_drug_plan->isivmed = $post['isivmed'];
                            $ins_pat_drug_plan->treatment_care = $post['treatment_care'];
                            $ins_pat_drug_plan->isnutrition = $post['isnutrition'];
                            $ins_pat_drug_plan->isintubated = $post['isintubated']; //ISPC-2176
                            $ins_pat_drug_plan->scheduled = $post['scheduled'];
                            
                            if(isset($post['has_interval'][$i])){
                                $ins_pat_drug_plan->has_interval = $post['has_interval'][$i];
                            } else {
                                $ins_pat_drug_plan->has_interval = "0";
                            }
                            
                            $ins_pat_drug_plan->days_interval = $post['days_interval'][$i];
                            
                            $ins_pat_drug_plan->days_interval_technical = ! empty($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : null;
                            $ins_pat_drug_plan->dosage_product = ! empty($post['dosage_product'][$i]) ? $post['dosage_product'][$i] : null;
                            
                            if(!empty($post['administration_date'][$i])){
                                $ins_pat_drug_plan->administration_date = date('Y-m-d 00:00:00', strtotime($post['administration_date'][$i]));
                            } else{
                                $ins_pat_drug_plan->administration_date = "0000-00-00 00:00:00";
                            }
                            
                            $ins_pat_drug_plan->verordnetvon = $post['verordnetvon'][$i];
                            $ins_pat_drug_plan->comments = $post['comments'][$i];
                            if(!empty($post['medication_change'][$i])){
                                $ins_pat_drug_plan->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                            } else{
                                $ins_pat_drug_plan->medication_change = date('Y-m-d 00:00:00');
                            }
                            $ins_pat_drug_plan->save();
    
                            $inserted_id = $ins_pat_drug_plan->id;
    
                            $inserted_id_alt = $inserted_id;
                            //ISPC-2554 Carmen pct.3 27.03.2020
                            $drugplanid = $inserted_id;
                            //--
                            // Insert dosage
                            if(is_array($post['dosage'][$i]))
                            { // NEW style
                            
                                foreach ($post['dosage'][$i] as $time => $dosage_value)
                                {
                                    //  insert new lines
                                    $insert_pdd = new PatientDrugPlanDosage();
                                    $insert_pdd->ipid = $ipid;
                                    $insert_pdd->drugplan_id = $inserted_id;
                                    $insert_pdd->dosage = $dosage_value;
                                    $insert_pdd->dosage_time_interval = $time.":00";
                                    $insert_pdd->save();
                                }
                            }
                            
                            // Insert extra data
                            $insert_pde = new PatientDrugPlanExtra();
                            $insert_pde->ipid = $ipid;
                            $insert_pde->drugplan_id = $inserted_id;
                            $insert_pde->drug = $post['drug'][$i];
                            
                            $insert_pde->dosage_24h_manual = $post['dosage_24h'][$i];            //ISPC-2684 Lore 05.10.2020
                            $insert_pde->unit_dosage = $post['unit_dosage'][$i];            //ISPC-2684 Lore 05.10.2020
                            $insert_pde->unit_dosage_24h = $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                            
                            //$insert_pde->unit = $post['unit'][$i]; //ISPC-2554
                            $insert_pde->type = $post['type'][$i];
                            $insert_pde->indication = trim($post['indication'][$i]);
                            $insert_pde->importance = $post['importance'][$i];
                            //ISPC-2554 pct.1 Carmen 03.04.2020
                            if(substr($post['dosage_form'][$i], 0, 3) == 'mmi')
                            {
                            	$data['clientid'] = $clientid;
                            	$data['isfrommmi'] = '1';
                            	$data['mmi_code'] = substr($post['dosage_form'][$i], 4);
                            	$data['extra'] = '1';
                            	$data['dosage_form'] = $dosageformmmi[$data['mmi_code']]['dosageform_name'];
                            	$dosagecustentity = MedicationDosageformTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'mmi_code'), array($clientid, substr($post['dosage_form'][$i], 4)), $data);
                            	if($dosagecustentity)
                            	{
                            		$dosage_form_med = $dosagecustentity->id;
                            	}
                            }
                            else
                            {
                            	$dosage_form_med = $post['dosage_form'][$i];                            	
                            }
                            $insert_pde->dosage_form = $dosage_form_med;
                            //--
                            //ISPC-2554 Carmen 12.05.2020
                            if(substr($post['unit'][$i], 0, 3) == 'mmi')
                            {
                            	$data['clientid'] = $clientid;
                            	$data['unit'] = substr($post['unit'][$i], 4);
                            	$unitcustentity = MedicationUnitTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'unit'), array($clientid, substr($post['unit'][$i], 4)), $data);
                            	if($unitcustentity)
                            	{
                            		$unit_med = $unitcustentity->id;
                            	}
                            }
                            else
                            {
                            	$unit_med = $post['unit'][$i];
                            }
                            $insert_pde->unit = $unit_med;
                            //--
                            $insert_pde->concentration = $post['concentration'][$i];
                            // ISPC-2176 p6
                            $insert_pde->packaging= $post['packaging'][$i];
                            $insert_pde->kcal= $post['kcal'][$i];
                            $insert_pde->volume= $post['volume'][$i];
                            
                            $insert_pde->escalation = $post['escalation'][$i];
                            
                            $insert_pde->save();
                             
                            
                            
                            
                            
                            
                            $cust = new PatientDrugPlanAlt();
                            $cust->ipid = $ipid;
                            $cust->drugplan_id = $inserted_id;
                            $cust->dosage = $post_dosaje[$i];
                            $cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
                            $cust->medication_master_id = $medid;
                            $cust->isbedarfs = $post['isbedarfs'];
                            $cust->iscrisis = $post['iscrisis'];
                            $cust->isivmed = $post['isivmed'];
                            $cust->treatment_care = $post['treatment_care'];
                            $cust->isnutrition = $post['isnutrition'];
                            $cust->isintubated = $post['isintubated'];//ISPC-2176
                            
                            $cust->scheduled = $post['scheduled'];
                            if(isset($post['has_interval'][$i])){
                                $cust->has_interval = $post['has_interval'][$i];
                            } else {
                                $cust->has_interval = "0";
                            }
                            $cust->days_interval = $post['days_interval'][$i];
                            
                            $cust->days_interval_technical = ! empty($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : null;
                            $cust->dosage_product = ! empty($post['dosage_product'][$i]) ? $post['dosage_product'][$i] : null;
                            
                            if(!empty($post['administration_date'][$i])){
                                $cust->administration_date = date('Y-m-d 00:00:00', strtotime($post['administration_date'][$i]));
                            } else{
                                $cust->administration_date = "0000-00-00 00:00:00";
                            }
                                
                            $cust->verordnetvon = $post['verordnetvon'][$i];
                            $cust->comments = $post['comments'][$i];
                            if(!empty($post['medication_change'][$i])){
                                $cust->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                            } else{
                                $cust->medication_change = date('Y-m-d 00:00:00');
                            }
                            $cust->status = "new";
                            $cust->save();
    
                            $recordid = $cust->id;
                            $recordid_alt = $recordid;
                            
                            if(is_array($post['dosage'][$i]))
                            { // NEW style
                                foreach ($post['dosage'][$i] as $time => $dosage_value)
                                {
                                    //  insert new lines
                                    $cust_pdd = new PatientDrugPlanDosageAlt();
                                    $cust_pdd->ipid = $ipid;
                                    $cust_pdd->drugplan_id_alt = $recordid;
                                    $cust_pdd->drugplan_id = $inserted_id;
                                    $cust_pdd->dosage = $dosage_value;
                                    $cust_pdd->dosage_time_interval = $time.":00";
                                    $cust_pdd->save();
                                }
                            }
                            
                            // add extra data
                            $cust_pde = new PatientDrugPlanExtraAlt();
                            $cust_pde->ipid = $ipid;
                            $cust_pde->drugplan_id_alt = $recordid;
                            $cust_pde->drugplan_id = $inserted_id;
                            $cust_pde->drug = $post['drug'][$i];
                            
                            $cust_pde->dosage_24h_manual = $post['dosage_24h'][$i];       //ISPC-2684 Lore 05.10.2020
                            $cust_pde->unit_dosage = $post['unit_dosage'][$i];            //ISPC-2684 Lore 05.10.2020
                            $cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                            
                            //$cust_pde->unit = $post['unit'][$i]; //ISPC-2554
                            $cust_pde->type = $post['type'][$i];
                            $cust_pde->indication = trim($post['indication'][$i]);
                            $cust_pde->importance = $post['importance'][$i];
                            //ISPC-2554 pct.1 Carmen 03.04.2020
                            $cust_pde->dosage_form = $dosage_form_med;
                            //--    
                            //ISPC-2554 Carmen 12.05.2020
                            $cust_pde->unit = $unit_med;
                            //--
                            $cust_pde->concentration = $post['concentration'][$i];
                            // ISPC-2176 p6
                            $cust_pde->packaging= $post['packaging'][$i];
                            $cust_pde->kcal= $post['kcal'][$i];
                            $cust_pde->volume= $post['volume'][$i];
                            
                            $cust_pde->save();
    
    
                            // NEW ENTRY
                            if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0  && $post['scheduled'] == 0)
                            {
                                $shortcut = "N";
                            }
                            elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0  && $post['scheduled'] == 0)
                            {
                                $shortcut = "I";
                            }
                            elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 && $post['scheduled'] == 0 )
                            {
                                $shortcut = "Q";
                                $prefix = "Schmerzpumpe ";
                            }
                            elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1  && $post['scheduled'] == 0  )
                            {
                                $shortcut = "BP";
                            }
                            elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0  && $post['scheduled'] == 1 )
                            {
                                $shortcut = "MI";
                            }
                            elseif($post['iscrisis'] == 1 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0  && $post['scheduled'] == 0 )
                            {
                            	$shortcut = "KM";
                            }
                            elseif($post['isintubated'] == 1 && $post['iscrisis'] == 0 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0  && $post['scheduled'] == 0 )
                            {
                            	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
                            }
                            else
                            {
                                $shortcut = "M";
                            }
    
    
                            // new name
                            if( $post['treatment_care'] == 1 )
                            {
                                $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
                            }
                            elseif( $post['isnutrition'] == 1 )
                            {
                                $new_med = Doctrine::getTable('Nutrition')->find($medid);
                            }
                            else
                            {
                                $new_med = Doctrine::getTable('Medication')->find($medid);
                            }
    
    
                            $new_medication_name[$i] = $new_med->name;
    
                            // new dosage
                            //$new_medication_dosage[$i] = $post['dosage'][$i];
                            $new_medication_dosage[$i] = $post_dosaje[$i];
    
                            // new comments
                            $new_medication_comments[$i] = $post['comments'][$i];
    
                            
                            // new days interval
                            $new_medication_days_interval[$i] = " | ".$post['days_interval'][$i];
    
                            
                            $new_medication_days_interval_technical[$i] = ! empty($post['days_interval_technical'][$i]) ? " | ".$post['days_interval_technical'][$i] : '';
    
                            
                            
                            // new change date
    
                            if(!empty($post['medication_change'][$i])){
                                $medication_change_date_str[$i] = date('d.m.Y', strtotime($post['medication_change'][$i]));
                            } else{
                                $medication_change_date_str[$i] = date('d.m.Y',time());
                            }
                            
                            if($post['scheduled'] == 1 )
                            {
                                $new_entry[$i] = $prefix.$new_medication_name[$i].$new_medication_days_interval[$i] . $new_medication_days_interval_technical[$i];
                            }
                            else
                            {
                                if(strlen($new_medication_dosage[$i])>0)
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                                else
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                            }
    
                            
                            $attach = 'OHNE FREIGABE: ' .  $new_entry[$i].'';
                            $insert_pc = new PatientCourse();
                            $insert_pc->ipid = $ipid;
                            $insert_pc->course_date = date("Y-m-d H:i:s", time());
                            $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
//                             $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
                            $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_new_med");
                            $insert_pc->recordid = $recordid;
                            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                            $insert_pc->user_id = $userid;
                            $insert_pc->save();
    
    
                            // SEND MESSAGE
                            $text  = "";
                            $text .= "Patient ".$patient_name." \n ";
                            $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                            $text .=  "neue Medikation:  " .  $new_entry[$i]." \n ";
                            $mess = Messages::medication_acknowledge_messages($ipid,$text);
    
                            // CREATE TODO
                            $text_todo  = "";
                            $text_todo .= "Patient ".$patient_name." <br/>";
                            $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
                            $text_todo .=  "neue Medikation:  " .  $new_entry[$i]." <br/>";
    
                            $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $inserted_id, $recordid);
                            
                            
                            
                            
                            
                            if($post['efaoption']== '1' && !empty($p_share_info)){
                                
                                foreach($p_share_info as $k=>$pi_share){
                                    
                                            if ($post['isschmerzpumpe'] == 1)
                                            {
                                                $cocktailId[$key] = $cocktailId;
                                            }
                    
                                            if($post['done_date'])
                                            {
                                                $medication_change[$key] = date('Y-m-d H:i:s', strtotime($post['done_date']));
                                            }
                                            else
                                            {
                                                $medication_change[$key] = date('Y-m-d 00:00:00');
                                            }
                    
                                            $ins_pat_drug_plan = new PatientDrugPlan();
                                            $ins_pat_drug_plan->ipid = $pi_share['source'];
                                            if($post['dosage']) //TODO-2982 Carmen 19.03.2020
                                            {
                                            	$ins_pat_drug_plan->dosage = $post_dosaje[$i];
                                            }
                                            $ins_pat_drug_plan->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
                                            $ins_pat_drug_plan->medication_master_id = $medid;
                                            $ins_pat_drug_plan->isbedarfs = $post['isbedarfs'];
                                            $ins_pat_drug_plan->iscrisis = $post['iscrisis'];
                                            $ins_pat_drug_plan->isivmed = $post['isivmed'];
                                            $ins_pat_drug_plan->treatment_care = $post['treatment_care'];
                                            $ins_pat_drug_plan->isnutrition = $post['isnutrition'];
                                            $ins_pat_drug_plan->isintubated = $post['isintubated']; //ISPC-2176
                                            $ins_pat_drug_plan->scheduled = $post['scheduled'];
                                            
                                            if(isset($post['has_interval'][$i])){
                                                $ins_pat_drug_plan->has_interval = $post['has_interval'][$i];
                                            } else {
                                                $ins_pat_drug_plan->has_interval = "0";
                                            }
                                            
                                            $ins_pat_drug_plan->days_interval = $post['days_interval'][$i];
                                            
                                            $ins_pat_drug_plan->days_interval_technical = ! empty($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : null;
                                            $ins_pat_drug_plan->dosage_product = ! empty($post['dosage_product'][$i]) ? $post['dosage_product'][$i] : null;
                                            
                                            if(!empty($post['administration_date'][$i])){
                                                $ins_pat_drug_plan->administration_date = date('Y-m-d 00:00:00', strtotime($post['administration_date'][$i]));
                                            } else{
                                                $ins_pat_drug_plan->administration_date = "0000-00-00 00:00:00";
                                            }
                                            
                                            $ins_pat_drug_plan->verordnetvon = $post['verordnetvon'][$i];
                                            $ins_pat_drug_plan->comments = $post['comments'][$i];
                                            if(!empty($post['medication_change'][$i])){
                                                $ins_pat_drug_plan->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                                            } else{
                                                $ins_pat_drug_plan->medication_change = date('Y-m-d 00:00:00');
                                            }
                                            $ins_pat_drug_plan->save();
                    
                                            $inserted_id = $ins_pat_drug_plan->id;
                    
                                            //ISPC-2554 Carmen pct.3 27.03.2020
                                            $drugplanid = $inserted_id;
                                            //--
                                            // Insert dosage
                                            if(is_array($post['dosage'][$i]))
                                            { // NEW style
                                            
                                                foreach ($post['dosage'][$i] as $time => $dosage_value)
                                                {
                                                    //  insert new lines
                                                    $insert_pdd = new PatientDrugPlanDosage();
                                                    $insert_pdd->ipid = $pi_share['source'];
                                                    $insert_pdd->drugplan_id = $inserted_id;
                                                    $insert_pdd->dosage = $dosage_value;
                                                    $insert_pdd->dosage_time_interval = $time.":00";
                                                    $insert_pdd->save();
                                                }
                                            }
                                            
                                            // Insert extra data
                                            $insert_pde = new PatientDrugPlanExtra();
                                            $insert_pde->ipid = $pi_share['source'];
                                            $insert_pde->drugplan_id = $inserted_id;
                                            $insert_pde->drug = $post['drug'][$i];
                                            
                                            $insert_pde->dosage_24h_manual = $post['dosage_24h'][$i];            //ISPC-2684 Lore 05.10.2020
                                            $insert_pde->unit_dosage = $post['unit_dosage'][$i];            //ISPC-2684 Lore 05.10.2020
                                            $insert_pde->unit_dosage_24h = $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                                            
                                            //$insert_pde->unit = $post['unit'][$i]; //ISPC-2554
                                            $insert_pde->type = $post['type'][$i];
                                            $insert_pde->indication = trim($post['indication'][$i]);
                                            $insert_pde->importance = $post['importance'][$i];
                                            //ISPC-2554 pct.1 Carmen 03.04.2020
                                            if(substr($post['dosage_form'][$i], 0, 3) == 'mmi')
                                            {
                                            	$data['clientid'] = $pi_share['source_client'];
                                            	$data['isfrommmi'] = '1';
                                            	$data['mmi_code'] = substr($post['dosage_form'][$i], 4);
                                            	$data['extra'] = '1';
                                            	$data['dosage_form'] = $dosageformmmi[$data['mmi_code']]['dosageform_name'];
                                            	$dosagecustentity = MedicationDosageformTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'mmi_code'), array($pi_share['source_client'], substr($post['dosage_form'][$i], 4)), $data);
                                            	if($dosagecustentity)
                                            	{
                                            		$dosage_form_med = $dosagecustentity->id;
                                            	}
                                            }
                                            else
                                            {
                                            	$dosage_form_med = $post['dosage_form'][$i];                            	
                                            }
                                            $insert_pde->dosage_form = $dosage_form_med;
                                            //--
                                            //ISPC-2554 Carmen 12.05.2020
                                            if(substr($post['unit'][$i], 0, 3) == 'mmi')
                                            {
                                            	$data['clientid'] = $pi_share['source_client'];
                                            	$data['unit'] = substr($post['unit'][$i], 4);
                                            	$unitcustentity = MedicationUnitTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'unit'), array($pi_share['source_client'], substr($post['unit'][$i], 4)), $data);
                                            	if($unitcustentity)
                                            	{
                                            		$unit_med = $unitcustentity->id;
                                            	}
                                            }
                                            else
                                            {
                                            	$unit_med = $post['unit'][$i];
                                            }
                                            $insert_pde->unit = $unit_med;
                                            //--
                                            $insert_pde->concentration = $post['concentration'][$i];
                                            // ISPC-2176 p6
                                            $insert_pde->packaging= $post['packaging'][$i];
                                            $insert_pde->kcal= $post['kcal'][$i];
                                            $insert_pde->volume= $post['volume'][$i];
                                            
                                            $insert_pde->escalation = $post['escalation'][$i];
                                            
                                            $insert_pde->save();
                                             
                                            
                                            
                                            
                                            
                                            
                                            $cust = new PatientDrugPlanAlt();
                                            $cust->ipid = $pi_share['source'];
                                            $cust->drugplan_id = $inserted_id;
                                            $cust->dosage = $post_dosaje[$i];
                                            $cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
                                            $cust->medication_master_id = $medid;
                                            $cust->isbedarfs = $post['isbedarfs'];
                                            $cust->iscrisis = $post['iscrisis'];
                                            $cust->isivmed = $post['isivmed'];
                                            $cust->treatment_care = $post['treatment_care'];
                                            $cust->isnutrition = $post['isnutrition'];
                                            $cust->isintubated = $post['isintubated'];//ISPC-2176
                                            
                                            $cust->scheduled = $post['scheduled'];
                                            if(isset($post['has_interval'][$i])){
                                                $cust->has_interval = $post['has_interval'][$i];
                                            } else {
                                                $cust->has_interval = "0";
                                            }
                                            $cust->days_interval = $post['days_interval'][$i];
                                            
                                            $cust->days_interval_technical = ! empty($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : null;
                                            $cust->dosage_product = ! empty($post['dosage_product'][$i]) ? $post['dosage_product'][$i] : null;
                                            
                                            if(!empty($post['administration_date'][$i])){
                                                $cust->administration_date = date('Y-m-d 00:00:00', strtotime($post['administration_date'][$i]));
                                            } else{
                                                $cust->administration_date = "0000-00-00 00:00:00";
                                            }
                                                
                                            $cust->verordnetvon = $post['verordnetvon'][$i];
                                            $cust->comments = $post['comments'][$i];
                                            if(!empty($post['medication_change'][$i])){
                                                $cust->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                                            } else{
                                                $cust->medication_change = date('Y-m-d 00:00:00');
                                            }
                                            $cust->status = "new";
                    
                                            $cust->related_drugplan_id = $inserted_id_alt;
                                            $cust->related_alt_id = $recordid_alt;
                                            
                                            $cust->save();
                                            
                                            $recordid = $cust->id;
                                            
                                            if(is_array($post['dosage'][$i]))
                                            { // NEW style
                                                foreach ($post['dosage'][$i] as $time => $dosage_value)
                                                {
                                                    //  insert new lines
                                                    $cust_pdd = new PatientDrugPlanDosageAlt();
                                                    $cust_pdd->ipid = $pi_share['source'];
                                                    $cust_pdd->drugplan_id_alt = $recordid;
                                                    $cust_pdd->drugplan_id = $inserted_id;
                                                    $cust_pdd->dosage = $dosage_value;
                                                    $cust_pdd->dosage_time_interval = $time.":00";
                                                    $cust_pdd->save();
                                                }
                                            }
                                            
                                            // add extra data
                                            $cust_pde = new PatientDrugPlanExtraAlt();
                                            $cust_pde->ipid = $pi_share['source'];
                                            $cust_pde->drugplan_id_alt = $recordid;
                                            $cust_pde->drugplan_id = $inserted_id;
                                            $cust_pde->drug = $post['drug'][$i];
                                            
                                            $cust_pde->dosage_24h_manual = $post['dosage_24h'][$i];       //ISPC-2684 Lore 05.10.2020
                                            $cust_pde->unit_dosage = $post['unit_dosage'][$i];            //ISPC-2684 Lore 05.10.2020
                                            $cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                                            
                                            //$cust_pde->unit = $post['unit'][$i]; //ISPC-2554
                                            $cust_pde->type = $post['type'][$i];
                                            $cust_pde->indication = trim($post['indication'][$i]);
                                            $cust_pde->importance = $post['importance'][$i];
                                            //ISPC-2554 pct.1 Carmen 03.04.2020
                                            $cust_pde->dosage_form = $dosage_form_med;
                                            //--    
                                            //ISPC-2554 Carmen 12.05.2020
                                            $cust_pde->unit = $unit_med;
                                            //--
                                            $cust_pde->concentration = $post['concentration'][$i];
                                            // ISPC-2176 p6
                                            $cust_pde->packaging= $post['packaging'][$i];
                                            $cust_pde->kcal= $post['kcal'][$i];
                                            $cust_pde->volume= $post['volume'][$i];
                                            
                                            $cust_pde->save();
                    
                    
                                            // NEW ENTRY
                                            if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0  && $post['scheduled'] == 0)
                                            {
                                                $shortcut = "N";
                                            }
                                            elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0  && $post['scheduled'] == 0)
                                            {
                                                $shortcut = "I";
                                            }
                                            elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 && $post['scheduled'] == 0 )
                                            {
                                                $shortcut = "Q";
                                                $prefix = "Schmerzpumpe ";
                                            }
                                            elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1  && $post['scheduled'] == 0  )
                                            {
                                                $shortcut = "BP";
                                            }
                                            elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0  && $post['scheduled'] == 1 )
                                            {
                                                $shortcut = "MI";
                                            }
                                            elseif($post['iscrisis'] == 1 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0  && $post['scheduled'] == 0 )
                                            {
                                            	$shortcut = "KM";
                                            }
                                            elseif($post['isintubated'] == 1 && $post['iscrisis'] == 0 && $post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0  && $post['scheduled'] == 0 )
                                            {
                                            	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
                                            }
                                            else
                                            {
                                                $shortcut = "M";
                                            }
                    
                    
                                            // new name
                                            if( $post['treatment_care'] == 1 )
                                            {
                                                $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
                                            }
                                            elseif( $post['isnutrition'] == 1 )
                                            {
                                                $new_med = Doctrine::getTable('Nutrition')->find($medid);
                                            }
                                            else
                                            {
                                                $new_med = Doctrine::getTable('Medication')->find($medid);
                                            }
                    
                    
                                            $new_medication_name[$i] = $new_med->name;
                    
                                            // new dosage
                                            //$new_medication_dosage[$i] = $post['dosage'][$i];
                                            $new_medication_dosage[$i] = $post_dosaje[$i];
                    
                                            // new comments
                                            $new_medication_comments[$i] = $post['comments'][$i];
                    
                                            
                                            // new days interval
                                            $new_medication_days_interval[$i] = " | ".$post['days_interval'][$i];
                    
                                            
                                            $new_medication_days_interval_technical[$i] = ! empty($post['days_interval_technical'][$i]) ? " | ".$post['days_interval_technical'][$i] : '';
                    
                                            
                                            
                                            // new change date
                    
                                            if(!empty($post['medication_change'][$i])){
                                                $medication_change_date_str[$i] = date('d.m.Y', strtotime($post['medication_change'][$i]));
                                            } else{
                                                $medication_change_date_str[$i] = date('d.m.Y',time());
                                            }
                                            
                                            if($post['scheduled'] == 1 )
                                            {
                                                $new_entry[$i] = $prefix.$new_medication_name[$i].$new_medication_days_interval[$i] . $new_medication_days_interval_technical[$i];
                                            }
                                            else
                                            {
                                                if(strlen($new_medication_dosage[$i])>0)
                                                {
                                                    $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                                }
                                                else
                                                {
                                                    $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                                }
                                            }
                    
                                            
                                            $attach = 'OHNE FREIGABE: ' .  $new_entry[$i].'';
                                            $insert_pc = new PatientCourse();
                                            $insert_pc->ipid = $pi_share['source'];
                                            $insert_pc->course_date = date("Y-m-d H:i:s", time());
                                            $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
                //                             $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
                                            $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_new_med");
                                            $insert_pc->recordid = $recordid;
                                            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                                            $insert_pc->user_id = $userid;
                                            $insert_pc->save();
                    
                    
                                            // SEND MESSAGE
                                            $text  = "";
                                            $text .= "Patient ".$patient_name." \n ";
                                            $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                                            $text .=  "neue Medikation:  " .  $new_entry[$i]." \n ";
                                            $mess = Messages::medication_acknowledge_messages($pi_share['source'],$text);
                    
                                            // CREATE TODO
                                            $text_todo  = "";
                                            $text_todo .= "Patient ".$patient_name." <br/>";
                                            $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
                                            $text_todo .=  "neue Medikation:  " .  $new_entry[$i]." <br/>";
                    
                                            $todos = Messages::medication_acknowledge_todo($pi_share['source'], $text_todo, $inserted_id, $recordid);
                                            
                                
                                
                                    
                                }
                                
                            }
                            
                            
                            
                        }
                    }

					//ISPC-2554 pct.3 Carmen 27.03.2020
               		$atcarr = (array)json_decode(html_entity_decode($post[$i]['atc']));
              	
	               	$atcid = array_search($drugplanid, array_column($atcdet, 'drugplan_id'));
	              	
	               	if($atcid !== false)
	               	{
	               		if($atcdet[$atcid]['atc_code'] != $atcarr['atc_code'])
	               		{
	               			$todelete[] = $atcdet[$atcid]['id'];
	               			
	               			$toupdate[$atcindex]['ipid'] = $ipid;
	               			$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
	               			$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
	               			$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
	               			$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
	               			$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
	               			$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
	               			$atcindex++;
	               		}
	               	}
	              	else 
	               	{               
		               if(!empty($atcarr))
		               {
			               $toupdate[$atcindex]['ipid'] = $ipid;
			               $toupdate[$atcindex]['drugplan_id'] = $drugplanid;
			               $toupdate[$atcindex]['medication_master_id'] = $medmasterid;
			               $toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
               			   $toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
               			   $toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
               			   $toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
			               $atcindex++;
		               }
               		}
               		//--
                }
              
            }
            else
            {
                $misc = "Medication change  Permission Error - Update Multiple ";
                PatientPermissions::MedicationLogRightsError(false,$misc);
            }
        }
        else
        { // !!!!!!!!!! VERSION WITH NO - Medical acknowledge function !!!!!!!!!!!
            $extra_array="";
        
            foreach ($post['hidd_medication'] as $i => $med_item)
            {
                $update_medication[$i] = "0";
    
                if ($post['hidd_medication'][$i] > 0)
                {
                    $medid = $post['hidd_medication'][$i];
                }
                else
                {
                    $medid = $post['newhidd_medication'][$i];
                }
    
                if (empty($post['verordnetvon'][$i]))
                {
                    $post['verordnetvon'][$i] = 0;
                }
                
                //ISPC-2554 Carmen pct.3 27.03.2020
                $medmasterid = $medid;
                //--
                
                // DOSAJE
                $post_dosaje[$i] = "";
                $post_dosage_interval[$i] = null;
                
                //ISPC-2524 pct.2)  Lore 15.01.2020
                if( ( isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1") 
                    || (isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1")  ){
                    $post['dosage'][$i] = array(); 
                    //TODO-3624 Ancuta 23.11.2020
                    $post['dosage_full'][$i] = array(); 
                    $post['dosage_concentration'][$i] = array();
                    $post['dosage_concentration_full'][$i] = array();
                    //-- 
                    $post['dosage_interval'][$i] = null;
                    $post['dosage_unit'][$i] = "";
                    $post['has_interval'][$i] = 0;
                    $post['days_interval'][$i] = array();
                    $post['unit'][$i] = "";
                    $post['type'][$i] = "";
                    $post['dosage_form'][$i] = "";
                    $post['concentration'][$i] = "";
                }
                
                if($post['dosage']) //TODO-2982 Carmen 19.03.2020
                {
	                if(is_array($post['dosage'][$i]))
	                { // NEW style
	                    foreach ($post['dosage'][$i] as $time => $dosage_value)
	                    {
	                        if(strlen($dosage_value) == 0){
	                            // $dosage_value = " / ";
	                            $dosage_value = "";
	                        }
	                    
	                        $old_dosage_array[$i][] = $dosage_value;
	                    }
	                    $post_dosaje[$i] = implode("-",$old_dosage_array[$i]);
	                    
	                    
	
	                    // PATIENT TIME SCHEME
	                    $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid);
	                    
	                    if(empty($patient_time_scheme['patient'])){
	                        // insert in patient time scheme
	                    
	                        foreach ($post['dosage'][$i] as $time => $dosage_value)
	                        {
	                            $insert_pc = new PatientDrugPlanDosageIntervals();
	                            $insert_pc->ipid = $ipid;
	                            $insert_pc->time_interval = $time.":00";
	                            $insert_pc->save();
	                        }
	                    }     
	                }
	                else
	                { // OLD style
	                    $post_dosaje[$i] = $post['dosage'][$i];
	                    $post_dosage_interval[$i] = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
	                    
	                }
                } //--

               
                
                $cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$i]);
                if($cust){
                	//ISPC-2554 Carmen pct.3 27.03.2020
                	$drugplanid = $cust->id;
                	//--
                    $existing_string = "";
                    $post_string = "";
                    // get dosage
                    $dosage_array  = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid,$post['drid'][$i]);
                   
                    // get extra data
                    $extra_data = "";
                    $extra_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$post['drid'][$i]);
                    $extra_data = $extra_array[$post['drid'][$i]]; 

                    // Get existing data and form a string
                    $existing_string = "";
                    if($cust->treatment_care != "1"){
                        $existing_string .= $cust->dosage;
                        //    TODO-2612 ISPC: Changing Medicationtime Ancuta - 30.10.2019
                        if(!empty($post['existing_dosage_array'][$post['drid'][$i]])){
                            $existing_string .= implode("-",array_keys($post['existing_dosage_array'][$post['drid'][$i]]));
                        }
                        //--
                    }
                    
                    $existing_string .= $cust->medication_master_id;
                    $existing_string .= $cust->comments;
                    $existing_string .= $cust->verordnetvon;
                    
                    if($cust->treatment_care != "1" && $cust->scheduled != "1")
                    {
                        $existing_string .= $extra_data['drug'];
                        
                        $existing_string .= $extra_data['dosage_24h_manual'];   //ISPC-2684 Lore 05.10.2020
                        $existing_string .= $extra_data['unit_dosage'];         //ISPC-2684 Lore 05.10.2020
                        $existing_string .= $extra_data['unit_dosage_24h'];     //ISPC-2684 Lore 05.10.2020
                        
                        $existing_string .= $extra_data['unit_id'] ? $extra_data['unit_id'] : "0"; ;
                        $existing_string .= $extra_data['type_id'] ? $extra_data['type_id'] : "0"; ;                      
                        $existing_string .= $extra_data['indication'] ? trim($extra_data['indication_id']) : "0"; ;
                        $existing_string .= $extra_data['dosage_form'] ? $extra_data['dosage_form'] : "0"; ;
                        $existing_string .= $extra_data['concentration'];
                        if($cust->has_interval == "1"){
                            $existing_string .= $cust->days_interval;
                            if($cust->administration_date != "0000-00-00 00:00:00"){
                                $existing_string .= date("d.m.Y",strtotime($cust->administration_date));
                            } else{
                                $existing_string .= "00.00.0000";
                            }
                        }
                        
                        if($cust->isintubated == 1){ // ISPC-2176
                            $existing_string .= $extra_data['packaging'] ? $extra_data['packaging'] : "0"; ;
                            $existing_string .= $extra_data['kcal'];
                            $existing_string .= $extra_data['volume'];
                        }
                        
                        
                        
                    }
                    else if($cust->scheduled == "1")
                    {
                        $existing_string .= $extra_data['indication'] ? trim($extra_data['indication']) : "0"; ;
                        $existing_string .= $cust->days_interval;
                        $existing_string .= ! empty($cust->days_interval_technical) ? $cust->days_interval_technical : '';
                        $existing_string .= ! empty($cust->dosage_product) ? $cust->dosage_product : '';
                        if($cust->administration_date != "0000-00-00 00:00:00"){
                            $existing_string .= date("d.m.Y",strtotime($cust->administration_date));
                        } else{
                            $existing_string .= "00.00.0000";
                        }
                    }
                    
                    if( ($cust->isbedarfs == 1 || $cust->iscrisis == 1) && ! empty($cust->dosage_interval)){
                        $existing_string .= $cust->dosage_interval; //added here to be before $existing_string_no_importance 
                    }
                    //TODO-3247 Carmen 01.07.2020
                    if($cust->isbedarfs == 1 || $cust->iscrisis == 1 || ($cust->isbedarfs == 0 && $cust->iscrisis == 0 && $cust->isschmerzpumpe == 0 && $cust->treatment_care == 0 && $cust->isnutrition == 0 && $cust->isintubated == 0 && $cust->scheduled == 0))
                    {
                    	$existing_string .= $extra_data['escalation'];
                    }
                    //--
                    $existing_string_no_importance = $existing_string;

                    //ISPC-2329 pct.f) Lore 27.08.2019
                    $existing_string .= $extra_data['importance'];
                    $existing_date = strtotime(date('d.m.Y',strtotime($cust->medication_change)));
                    
                    
                    
                    // Get posted data and form a string                    
                    $post_string = "";
                    if($cust->treatment_care != "1"){
                        $post_string .= $post_dosaje[$i];
                        
                        //    TODO-2612 ISPC: Changing Medicationtime Ancuta - 30.10.2019
                        if(!empty($post['dosage'][$i]) && is_array($post['dosage'][$i])){
                            $post_string .= implode("-",array_keys($post['dosage'][$i]));
                        }
                        //--
                    }
                    
                    
                    $post_string .= $medid;
                    $post_string .= $post['comments'][$i];
                    $post_string .= $post['verordnetvon'][$i];
                    if($cust->treatment_care != "1" && $cust->scheduled != "1")
                    {
                        $post_string .= $post['drug'][$i];                        
                        $post_string .= $post['unit'][$i];
                        
                        $post_string .= $post['dosage_24h'][$i];         //ISPC-2684 Lore 12.10.2020
                        $post_string .= $post['unit_dosage'][$i];        //ISPC-2684 Lore 05.10.2020
                        $post_string .= $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                        
                        $post_string .= $post['type'][$i];                         
                        $post_string .= trim($post['indication'][$i]);                       
                        $post_string .= $post['dosage_form'][$i];
                        $post_string .= $post['concentration'][$i];
                        if($post['has_interval'][$i] == "1"){
                            $post_string .= $post['days_interval'][$i];
                            $post_string .= $post['administration_date'][$i];
                        }
                        if ($cust->isintubated == 1){ // ISPC-2176
                            $post_string .= $post['packaging'][$i];
                            $post_string .= $post['kcal'][$i];
                            $post_string .= $post['volume'][$i];
                        }
                    }
                    elseif($cust->scheduled == "1")
                    {
                        $post_string .= trim($post['indication'][$i]);                      
                        $post_string .= $post['days_interval'][$i];
                        $post_string .= ! empty($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : '';
                        $post_string .= ! empty($post['dosage_product'][$i]) ? $post['dosage_product'][$i] : '';
                        $post_string .= $post['administration_date'][$i];
                    }
                    if(($cust->isbedarfs == 1 || $cust->iscrisis == 1) && ! empty($post[$i]['dosage_interval'])) {
                        $post_string .= $post[$i]['dosage_interval'];//added here to be before $post_string_no_importance
                    }
                    //TODO-3247 Carmen 01.07.2020
                    if($cust->isbedarfs == 1 || $cust->iscrisis == 1 || ($cust->isbedarfs == 0 && $cust->iscrisis == 0 && $cust->isschmerzpumpe == 0 && $cust->treatment_care == 0 && $cust->isnutrition == 0 && $cust->isintubated == 0 && $cust->scheduled == 0))
                    {
                    	$post_string .= $post['escalation'][$i];
                    }
                    //--
                    $post_string_no_importance = $post_string;
                    //ISPC-2329 pct.f) Lore 27.08.2019
                    $post_string .= $post['importance'][$i];
                    
                    $post_date = strtotime($post['medication_change'][$i]);
                    
                    
                    
// var_dump($existing_string);
// var_dump($existing_string_no_importance);
// var_dump($post_string);
// var_dump($post_string_no_importance);

//                     var_dump(($existing_date != $post_date || $existing_string != $post_string ) && $post['edited'][$i] == '1');
// var_dump($existing_string_no_importance == $post_string_no_importance);
//            exit;         
                    if( ($existing_date != $post_date || $existing_string != $post_string ) && $post['edited'][$i] == '1' )
                    { //check to update only what's modified
                        
                        if($existing_string_no_importance == $post_string_no_importance){
                            $_POST['skip_verlauf'] = "1";
                        } else{
                            $_POST['skip_verlauf'] = "0";
                        }
                        
                        $update_medication[$i] = "1";
    
                        if(!empty($post['medication_change'][$i])){
                            //check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
                            if (  $existing_string != $post_string )
                            {
                                if ($post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
                                    $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                                } elseif ($post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
                                    $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                                } elseif ($post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
                                    $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                                } else {
                                    $medication_change_date[$i] = date('Y-m-d 00:00:00');
                                }
    
                                // if no medication details were modified - check in the "last edit date" was edited
                            } else if(
                                ( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
                                ( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
                                ( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
                            {
    
                                $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
    
                            } else if(
                                ( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
                                ( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
                                ( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) )
                            )
                            {
                                $update_medication[$i] = "0";
                            }
    
                            // if "last edit date was edited - save current date"
                        } else {
                            $medication_change_date[$i] = date('Y-m-d 00:00:00');
                        }
                    }    
                    //ISPC-2524 pct.2)  Lore 16.01.2020
                    elseif( (isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1")  
                         || (isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1")   
                         && $post['edited'][$i] == '1' ) 
                    {
                        $update_medication[$i] = "1";
                    }
                    //.
                    else {
                        $update_medication[$i] = "0";
                    }
                    
                    /* ================= Save in patient drugplan history ====================*/
                    if($update_medication[$i] == "1")
                    {
                        
						//TODO-2785 Lore 18.02.2020
                        //$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                        if( $cust->treatment_care == 1 )
                        {
                            $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id);
                        }
                        elseif( $cust->isnutrition == 1 )
                        {
                            $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id);
                        }
                        else
                        {
                            $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
                        }
                        $medication_old_medication_name[$i] = $old_med->name;
                        $medication_old_medication_id[$i] =  $old_med->id;
    
                        $history = new PatientDrugPlanHistory();
                        $history->ipid = $ipid;
                        $history->pd_id = $cust->id;
                        $history->pd_medication_master_id = $cust->medication_master_id ;
                        $history->pd_medication_name = $medication_old_medication_name[$i] ;
                        $history->pd_medication =  $cust->medication;
                        $history->pd_dosage = $cust->dosage;
                        $history->pd_dosage_interval = isset($cust->dosage_interval) ? $cust->dosage_interval : null;
                        $history->pd_dosage_product = isset($cust->dosage_product) ? $cust->dosage_product : null;
                        $history->pd_comments = $cust->comments ;
                        $history->pd_isbedarfs = $cust->isbedarfs;
                        $history->pd_iscrisis = $cust->iscrisis;
                        $history->pd_isivmed = $cust->isivmed;
                        $history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
                        $history->pd_cocktailid= $cust->cocktailid;
                        $history->pd_treatment_care = $cust->treatment_care;
                        $history->pd_isnutrition = $cust->isnutrition;
                        $history->pd_isintubated = $cust->isintubated; // ISPC-2176
                        $history->pd_scheduled = $cust->scheduled;
                        $history->pd_has_interval = $cust->has_interval;
                        $history->pd_days_interval = $cust->days_interval;
                        $history->pd_days_interval_technical = isset($cust->days_interval_technical) ? $cust->days_interval_technical : null;
                        $history->pd_administration_date = $cust->administration_date;
                        $history->pd_edit_type = $cust->edit_type;
                        $history->pd_verordnetvon = $cust->verordnetvon;
                        $history->pd_medication_change = $cust->medication_change;
                        $history->pd_create_date = $cust->create_date;
                        $history->pd_create_user = $cust->create_user;
                        $history->pd_change_date = $cust->change_date;
                        $history->pd_change_user = $cust->change_user;
                        $history->pd_isdelete = $cust->isdelete;
                        $history->save();
                        $history_id = $history->id;
                        
                        $dosage_history_array[$post['drid'][$i]] = PatientDrugPlanDosage::get_all_patient_drugplan_dosage($ipid,$post['drid'][$i]);
                        
                        //if(is_array($post['dosage'][$i]))
                        if(is_array($post['dosage'][$i]) || is_array($post['dosage_concentration'][$i]))
                        {
                            if(!empty($dosage_history_array[$post['drid'][$i]]))
                            {
                                // add dosage to - dosage history
                                foreach($dosage_history_array[$post['drid'][$i]] as $k=>$dv)
                                {
                                    $history_pd = new PatientDrugPlanDosageHistory();
                                    $history_pd->ipid = $ipid;
                                    $history_pd->pdd_id = $dv['id'];
                                    $history_pd->history_id = $history_id;
                                    $history_pd->pdd_drugplan_id = $dv['drugplan_id'];
                                    $history_pd->pdd_dosage = $dv['dosage'];
                                    //TODO-3624 Ancuta 23.11.2020
                                    $history_pd->pdd_dosage_full = $dv['dosage_full'];
                                    $history_pd->pdd_dosage_concentration = $dv['dosage_concentration'];
                                    $history_pd->pdd_dosage_concentration_full = $dv['dosage_concentration_full'];
                                    //--                                    
                                    $history_pd->pdd_dosage_time_interval =  $dv['dosage_time_interval'];
                                    $history_pd->pdd_isdelete	= $dv['isdelete'];
                                    $history_pd->pdd_create_user = $dv['create_user'];
                                    $history_pd->pdd_create_date = $dv['create_date'];
                                    $history_pd->pdd_change_user = $dv['change_user'];
                                    $history_pd->pdd_change_date = $dv['change_date'];
                                    $history_pd->save();
                                }
                            }
                        }
                        
                        $drugplan_extra_array = PatientDrugPlanExtra::get_patient_all_drugplan_extra($ipid,$post['drid'][$i]);
                        
                        if(!empty($drugplan_extra_array[$post['drid'][$i]]))
                        {
                            $drugplan_extra_data = $drugplan_extra_array[$post['drid'][$i]];
                        
                            $history_pde = new PatientDrugPlanExtraHistory();
                            $history_pde->ipid = $ipid;
                            $history_pde->pde_id = $drugplan_extra_data['id'];
                            $history_pde->history_id = $history_id;
//                             $history_pde->pde_drugplan_id = $dv['drugplan_id'];;
                            $history_pde->pde_drugplan_id = $post['drid'][$i];
                            $history_pde->pde_drug = $drugplan_extra_data['drug'];
                            $history_pde->pde_unit = $drugplan_extra_data['unit'];
                            
                            $history_pde->pde_dosage_24h_manual = $drugplan_extra_data['dosage_24h_manual'];    //ISPC-2684 Lore 12.10.2020
                            $history_pde->pde_unit_dosage = $drugplan_extra_data['unit_dosage'];            //ISPC-2684 Lore 05.10.2020
                            $history_pde->pde_unit_dosage_24h = $drugplan_extra_data['unit_dosage_24h'];    //ISPC-2684 Lore 05.10.2020
                            
                            $history_pde->pde_type = $drugplan_extra_data['type'];
                            $history_pde->pde_indication = trim($drugplan_extra_data['indication']);
                            $history_pde->pde_importance = $drugplan_extra_data['importance'];
                            
                            $history_pde->pde_dosage_form = $drugplan_extra_data['dosage_form'];
                            $history_pde->pde_concentration = $drugplan_extra_data['concentration'];
                            // ISPC-2176
                            $history_pde->pde_packaging = $drugplan_extra_data['packaging'];
                            $history_pde->pde_kcal = $drugplan_extra_data['kcal'];
                            $history_pde->pde_volume = $drugplan_extra_data['volume'];
                            
                            // ISPC-2247
                            $history_pde->pde_escalation = $drugplan_extra_data['escalation'];
                            
                            $history_pde->pde_isdelete	= $drugplan_extra_data['isdelete'];
                            
                            $history_pde->pde_create_user = $drugplan_extra_data['create_user'];
                            $history_pde->pde_create_date = $drugplan_extra_data['create_date'];
                            $history_pde->pde_change_user = $drugplan_extra_data['change_user'];
                            $history_pde->pde_change_date = $drugplan_extra_data['change_date'];
                            $history_pde->save();
                        }
                    }

                    /* ================= Update patient drugplan item ====================*/
                    if($update_medication[$i] == "1")
                    {
                        
//                        $cust->dosage = $post_dosaje[$i];
//                        $cust->ipid = $ipid;
//                        $cust->medication_master_id = $medid;
//                        $cust->verordnetvon = $post['verordnetvon'][$i];
//                        $cust->comments = $post['comments'][$i];
//                        $cust->medication_change = $medication_change_date[$i];
//                        $cust->save();
                        
                        
                        if(is_array($post['dosage'][$i]))
                        { // NEW style
                            if(!empty($dosage_array)){
                                // clear dosage
                                $clear_dosage = $this->clear_dosage($ipid, $post['drid'][$i]);
                            }
                            foreach ($post['dosage'][$i] as $time => $dosage_value)
                            {
                                //  insert new lines
                                $cust_pdd = new PatientDrugPlanDosage();
                                $cust_pdd->ipid = $ipid;
                                $cust_pdd->drugplan_id = $post['drid'][$i];
                                $cust_pdd->dosage = $dosage_value;
                                //TODO-3624 Ancuta 23.11.2020
                                $cust_pdd->dosage_full = $post['dosage_full'][$i][$time];
                                $cust_pdd->dosage_concentration = $post['dosage_concentration'][$i][$time];
                                $cust_pdd->dosage_concentration_full = $post['dosage_concentration_full'][$i][$time];
                                //--
                                $cust_pdd->dosage_time_interval = $time.":00";
                                $cust_pdd->save();
                            }
                        }
                        //TODO-3972 Lore 24.03.2021
                        else {
                            if(isset($post['existing_dosage_array_new_dosage'][$post['drid'][$i]])){
                                foreach ($post['existing_dosage_array_new_dosage'][$post['drid'][$i]] as $time => $dosage_value)
                                {
                                    //  insert new lines
                                    $cust_pdd = new PatientDrugPlanDosage();
                                    $cust_pdd->ipid = $ipid;
                                    $cust_pdd->drugplan_id = $post['drid'][$i];
                                    $cust_pdd->dosage = $dosage_value;
                                    //TODO-3624 Ancuta 23.11.2020
                                    $cust_pdd->dosage_full = $post['dosage_full'][$i][$time];
                                    $cust_pdd->dosage_concentration = $post['dosage_concentration'][$i][$time];
                                    $cust_pdd->dosage_concentration_full = $post['dosage_concentration_full'][$i][$time];
                                    //--
                                    $cust_pdd->dosage_time_interval = $time.":00";
                                    $cust_pdd->save();
                                }
                            }
                        }
                        //.
                        
                        // update extra data
                        if(!empty($extra_array))
                        {
                            $drugs = Doctrine_Query::create()
                            ->select('id')
                            ->from('PatientDrugPlanExtra')
                            ->where("ipid = '" . $ipid . "'")
                            ->andWhere("drugplan_id = '" . $post['drid'][$i] . "'")
                            ->andWhere("isdelete = '0'")
                            ->orderBy("create_date DESC")
                            ->limit(1);
                            $drugs_array = $drugs->fetchArray();
                            
                            if(!empty($drugs_array)){
                                $existing_extra_id =$drugs_array[0]['id'];
                                
                                $update_pde = Doctrine::getTable('PatientDrugPlanExtra')->find($existing_extra_id);
                                $update_pde->drug = $post['drug'][$i];
                                
                                $update_pde->dosage_24h_manual = $post['dosage_24h'][$i];       //ISPC-2684 Lore 12.10.2020
                                $update_pde->unit_dosage = $post['unit_dosage'][$i];            //ISPC-2684 Lore 05.10.2020
                                $update_pde->unit_dosage_24h = $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                                
                                //$update_pde->unit = $post['unit'][$i]; //ISPC-2554
                                $update_pde->type = $post['type'][$i];
                                $update_pde->indication = trim($post['indication'][$i]);
                                $update_pde->importance = $post['importance'][$i];
                                //ISPC-2554 pct.1 Carmen 03.04.2020
                                if(substr($post['dosage_form'][$i], 0, 3) == 'mmi')
                                {
                                	$data['clientid'] = $clientid;
                                	$data['isfrommmi'] = '1';
                                	$data['mmi_code'] = substr($post['dosage_form'][$i], 4);
                                	$data['extra'] = '1';
                                	$data['dosage_form'] = $dosageformmmi[$data['mmi_code']]['dosageform_name'];
                                	$dosagecustentity = MedicationDosageformTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'mmi_code'), array($clientid, substr($post['dosage_form'][$i], 4)), $data);
                                	if($dosagecustentity)
                                	{
                                		$update_pde->dosage_form = $dosagecustentity->id;
                                	}
                                }
                                else
                                {
                                	$update_pde->dosage_form = $post['dosage_form'][$i];
                                }
                                //--
                                //ISPC-2554 Carmen 12.05.2020
                                if(substr($post['unit'][$i], 0, 3) == 'mmi')
                                {
                                	$data['clientid'] = $clientid;
                                	$data['unit'] = substr($post['unit'][$i], 4);
                                	$unitcustentity = MedicationUnitTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'unit'), array($clientid, substr($post['unit'][$i], 4)), $data);
                                	if($unitcustentity)
                                	{
                                		$update_pde->unit = $unitcustentity->id;
                                	}
                                }
                                else
                                {
                                	$update_pde->unit = $post['unit'][$i];
                                }
                                //--
                                $update_pde->concentration = $post['concentration'][$i];
                                // ISPC-2176
                                $update_pde->packaging = $post['packaging'][$i];
                                $update_pde->kcal = $post['kcal'][$i];
                                $update_pde->volume = $post['volume'][$i];
                                
                                // ISPC-2247
                                $update_pde->escalation = $post['escalation'][$i];
                                
                                
                                $update_pde->save();
                            }
 
                        } 
                        else
                        {
                            // add extra data
                            $cust_pde = new PatientDrugPlanExtra();
                            $cust_pde->ipid = $ipid;
                            $cust_pde->drugplan_id = $post['drid'][$i];
                            $cust_pde->drug = $post['drug'][$i];
                            
                            $cust_pde->dosage_24h_manual = $post['dosage_24h'][$i];            //ISPC-2684 Lore 12.10.2020
                            $cust_pde->unit_dosage = $post['unit_dosage'][$i];            //ISPC-2684 Lore 05.10.2020
                            $cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                            
                            //$cust_pde->unit = $post['unit'][$i]; //ISPC-2554
                            $cust_pde->type = $post['type'][$i];
                            $cust_pde->indication = trim($post['indication'][$i]);
                            $cust_pde->importance = $post['importance'][$i];
                            //ISPC-2554 pct.1 Carmen 03.04.2020
                            if(substr($post['dosage_form'][$i], 0, 3) == 'mmi')
                            {
                            	$data['clientid'] = $clientid;
                            	$data['isfrommmi'] = '1';
                            	$data['mmi_code'] = substr($post['dosage_form'][$i], 4);
                            	$data['extra'] = '1';
                            	$data['dosage_form'] = $dosageformmmi[$data['mmi_code']]['dosageform_name'];
                            	$dosagecustentity = MedicationDosageformTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'mmi_code'), array($clientid, substr($post['dosage_form'][$i], 4)), $data);
                            	if($dosagecustentity)
                            	{
                            		$cust_pde->dosage_form = $dosagecustentity->id;
                            	}
                            }
                            else
                            {
                            	$cust_pde->dosage_form = $post['dosage_form'][$i];
                            }
                            //--
                            //ISPC-2554 Carmen 12.05.2020
                            if(substr($post['unit'][$i], 0, 3) == 'mmi')
                            {
                            	$data['clientid'] = $clientid;
                            	$data['unit'] = substr($post['unit'][$i], 4);
                            	$unitcustentity = MedicationUnitTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'unit'), array($clientid, substr($post['unit'][$i], 4)), $data);
                            	if($unitcustentity)
                            	{
                            		$cust_pde->unit = $unitcustentity->id;
                            	}
                            }
                            else
                            {
                            	$cust_pde->unit = $post['unit'][$i];
                            }
                            //--
                            $cust_pde->concentration = $post['concentration'][$i];
                            // ISPC-2176
                            $cust_pde->packaging = $post['packaging'][$i];
                            $cust_pde->kcal = $post['kcal'][$i];
                            $cust_pde->volume = $post['volume'][$i];
                            // ISPC-2247
                            $cust_pde->escalation = $post['escalation'][$i];
                            // -- 
                            
                            $cust_pde->save();
                        }
                        	
                        // IF An approval user edits a medications that is not approved yet - all data is marcked as inactive for this medication
                        if(in_array($userid,$approval_users) && $acknowledge == "1" )
                        {
                            $clear = $this->update_pdpa($ipid, $post['drid'][$i]);
                            	
                            if( $cust->isivmed == 0 &&  $cust->isbedarfs == 1 &&  $cust->isschmerzpumpe == 0 &&  $cust->treatment_care == 0  &&  $cust->scheduled == 0 )
                            {
                                $shortcut = "N";
                            }
                            elseif($cust->isivmed  == 1 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0  &&  $cust->scheduled == 0 )
                            {
                                $shortcut = "I";
                            }
                            elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 1 &&  $cust->treatment_care== 0  &&  $cust->scheduled == 0 )
                            {
                                $shortcut = "Q";
                                $prefix = "Schmerzpumpe ";
                            }
                            elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 1  &&  $cust->scheduled == 0 )
                            {
                                $shortcut = "BP";
                            }
                            elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0  &&  $cust->scheduled == 1 )
                            {
                                $shortcut = "MI"; // Intervall Medis 
                            }
                            elseif($cust->iscrisis  == 1 &&  $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0  &&  $cust->scheduled == 0 )
                            {
                            	$shortcut = "KM";
                            }
                            elseif($cust->isintubated  == 1 && $cust->iscrisis  == 0 &&  $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0  &&  $cust->scheduled == 0 )
                            {
                            	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
                            }
                            
                            else
                            {
                                $shortcut = "M";
                            }
                             
                            
                            //ISPC-2524 pct.2)  Lore 15.01.2020                            
                            if(isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1" ){
                                $shortcut = "N";
                            }
                            if(isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1" ){
                                $shortcut = "M";
                            }
                           
                            
                            
                            // OLD ENTRY
                            // old medication name
                            // $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                            
                            $old_med_name[$i] = "";
                            if( $cust->treatment_care == 1 )
                            {
                            	$old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id );
                            }
                            elseif(  $cust->isnutrition == 1 )
                            {
                            	$old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id );
                            }
                            else
                            {
                            	$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                            }
                            $old_med_name[$i] = $old_med->name;
                             
                            // old dosage
                            if($cust->dosage) {
                                $old_med_dosage[$i] = $cust->dosage;
                            }
                             
                            // old comment
                            if($cust->comments ){
                                $old_med_comments[$i] = $cust->comments." | ";
                            }
    
                            //  old medication date
                            if($cust->medication_change != "0000-00-00 00:00:00")
                            {
                                $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->medication_change));
                            }
                            else
                            {
                                if($cust->change_date != "0000-00-00 00:00:00")
                                {
                                    $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->change_date));
                                }
                                else
                                {
                                    $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->create_date));
                                }
                            }
                             
                            if($cust->scheduled == 1)
                            {
                                    // old days interval
                                    if($cust->days_interval ){
                                        $old_med_days_interval[$i] = $cust->days_interval;
                                    }
                                    
                                    $old_med_days_interval_technical[$i] = $cust->days_interval_technical ? ' | '.$cust->days_interval_technical : '';
                                    
                                    $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_days_interval[$i] . $old_med_days_interval_technical[$i];
                            } 
                            else
                            {
                                if(strlen($old_med_dosage[$i])>0){
                                    $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
                                } else	{
                                    $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
                                }
                            }
                             
                            // NEW ENTRY
                            // new name
                            if( $post['treatment_care'] == 1 )
                            {
                                $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
                            }
                            elseif( $post['isnutrition'] == 1 )
                            {
                                $new_med = Doctrine::getTable('Nutrition')->find($medid);
                            }
                            else
                            {
                                $new_med = Doctrine::getTable('Medication')->find($medid);
                            }
                             
                            $new_medication_name[$i] = $new_med->name;
                             
                            // new dosage
                            //$new_medication_dosage[$i] = $post['dosage'][$i];
                            $new_medication_dosage[$i] =  $post_dosaje[$i];
                             
                            // new comments
                            $new_medication_comments[$i] = $post['comments'][$i];
                             
                            // new change date
                            if($medication_change_date[$i] != "0000-00-00 00:00:00"){
                                $medication_change_date_str[$i] = date("d.m.Y", strtotime($medication_change_date[$i]));
                            }
                            else
                            {
                                $medication_change_date_str[$i]="";
                            }
                             
                            
                            if($cust->scheduled == 1)
                            {
                                // new interval
                                $new_medication_days_interval[$i] = $post['days_interval'][$i];
                                $new_medication_days_interval_technical[$i] = ! empty($post['days_interval_technical'][$i]) ? " | ". $post['days_interval_technical'][$i] : '';
                                
                                $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_days_interval[$i] . $new_medication_days_interval_technical[$i];
                            }
                            else
                            {
                                if(strlen($new_medication_dosage[$i])>0)
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                                else
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                            }
                             
                            $attach = 'Änderung: ' . $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
                            $insert_pc = new PatientCourse();
                            $insert_pc->ipid = $ipid;
                            $insert_pc->course_date = date("Y-m-d H:i:s", time());
                            $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
                            $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_edit_med");
                            $insert_pc->recordid = $post['drid'][$i];
                            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                            $insert_pc->user_id = $userid;
                            $insert_pc->save();
                            
                            //TODO-2850  ISPC: Todo is not marked as ready
                            // check  todos and remove them if
                            if(in_array($userid,$approval_users)){
                                // find all todos - not completed, and remove
                                
                                $text_todo ="";
                                $todos = Messages::remove_medication_acknowledge_todo($ipid, $text_todo, $post['drid'][$i]);
                               
                            }
                            // --
                            

                            //ISPC-2829 Ancuta 07.04.2021
                            //clear al alt - where curent drugplan id is source_drugplan_id  
                            $drgs_plans_q = Doctrine_Query::create()
                            ->select('id,ipid')
                            ->from('PatientDrugPlan')
                            ->where("source_ipid = ?" , $ipid )
                            ->andWhere("source_drugplan_id = ?",$post['drid'][$i])
                            ->andWhere("isdelete = '0'")
                            ->orderBy("create_date DESC");
                            $existing_plan_array = $drgs_plans_q->fetchArray();
                            
                            if(!empty($existing_plan_array)){
                                foreach($existing_plan_array as $k=>$pl){
                                    $clear = $this->update_pdpa($pl['ipid'], $pl['id']);
                                    
                                    
                                    $cust_alt = Doctrine::getTable('PatientDrugPlan')->find($pl['id']);
                                    if($cust_alt){
                                        
                                        if(is_array($post['dosage'][$i]))
                                        { // NEW style
                                            if(!empty($dosage_array)){
                                                // clear dosage
                                                $clear_dosage = $this->clear_dosage($pl['ipid'], $pl['id']);
                                            }
                                            foreach ($post['dosage'][$i] as $time => $dosage_value)
                                            {
                                                //  insert new lines
                                                $cust_pdd = new PatientDrugPlanDosage();
                                                $cust_pdd->ipid = $pl['ipid'];
                                                $cust_pdd->drugplan_id = $pl['id'];
                                                $cust_pdd->dosage = $dosage_value;
                                                $cust_pdd->dosage_full = $post['dosage_full'][$i][$time];
                                                $cust_pdd->dosage_concentration = $post['dosage_concentration'][$i][$time];
                                                $cust_pdd->dosage_concentration_full = $post['dosage_concentration_full'][$i][$time];
                                                $cust_pdd->dosage_time_interval = $time.":00";
                                                $cust_pdd->save();
                                            }
                                        }
                                        else {
                                            if(isset($post['existing_dosage_array_new_dosage'][$pl['id']])){
                                                foreach ($post['existing_dosage_array_new_dosage'][$pl['id']] as $time => $dosage_value)
                                                {
                                                    //  insert new lines
                                                    $cust_pdd = new PatientDrugPlanDosage();
                                                    $cust_pdd->ipid = $pl['ipid'];
                                                    $cust_pdd->drugplan_id = $pl['id'];
                                                    $cust_pdd->dosage = $dosage_value;
                                                    $cust_pdd->dosage_full = $post['dosage_full'][$i][$time];
                                                    $cust_pdd->dosage_concentration = $post['dosage_concentration'][$i][$time];
                                                    $cust_pdd->dosage_concentration_full = $post['dosage_concentration_full'][$i][$time];
                                                    $cust_pdd->dosage_time_interval = $time.":00";
                                                    $cust_pdd->save();
                                                }
                                            }
                                        }
                                        
                                        
                                        if($post['dosage'])  
                                        {
                                            $cust_alt->dosage = $post_dosaje[$i];
                                        }
                                        $cust_alt->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
                                        $cust_alt->ipid = $pl['ipid'];
                                        $cust_alt->medication_master_id = $medid;
                                        $cust_alt->verordnetvon = $post['verordnetvon'][$i];
                                        $cust_alt->comments = $post['comments'][$i];
                                        
                                        if(strlen($post['source_ipid'][$i]) > 0  && $post['source_ipid'][$i] != $pl['ipid'] ){
                                            $cust_alt->source_ipid = $post['source_ipid'][$i];
                                            
                                            if(strlen($post['source_drugplan_id'][$i]) > 0 ){
                                                $cust_alt->source_drugplan_id = $post['source_drugplan_id'][$i];
                                            }
                                        }
                                        
                                        $cust_alt->medication_change = $medication_change_date[$i];
                                        
                                        if(isset($post['has_interval'][$i])){
                                            $cust_alt->has_interval = $post['has_interval'][$i];
                                        } else {
                                            $cust_alt->has_interval = "0";
                                        }
                                        $cust_alt->days_interval = $post['days_interval'][$i];
                                        $cust_alt->days_interval_technical = ! empty($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : null;
                                        $cust_alt->dosage_product = ! empty($post['dosage_product'][$i]) ? $post['dosage_product'][$i] : null;
                                        if(strlen($post['administration_date'][$i] > 0 ))
                                        {
                                            $cust_alt->administration_date = date('Y-m-d 00:00:00', strtotime($post['administration_date'][$i]));
                                        }
                                        else
                                        {
                                            $cust_alt->administration_date = "0000-00-00 00:00:00";
                                        }
                                        
                                        if(isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1"){
                                            $cust_alt->isbedarfs = 0 ;
                                        }
                                        if(isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1"){
                                            $cust_alt->isbedarfs = 1 ;
                                        }
                                        if((isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1")
                                            || (isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1") ){
                                                
                                                $was_moved = "medication was moved ";
                                                
                                                $cust_trans = new PatientDrugplanTransition();
                                                $cust_trans->ipid = $pl['ipid'];
                                                $cust_trans->drugplan_id = $pl['id'];
                                                if(isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1" ){
                                                    $cust_trans->transition = "bedarf_to_activ" ;
                                                    $was_moved .= "from bedarf to active";
                                                }
                                                if(isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1" ){
                                                    $cust_trans->transition = "activ_to_bedarf" ;
                                                    $was_moved .= "from active to bedarf";
                                                }
                                                $cust_trans->save();
                                                
                                                $history_tr = new PatientDrugPlanHistory();
                                                $history_tr->ipid = $ipid;
                                                $history_tr->pd_id = $cust_alt->id;
                                                $history_tr->pd_medication_master_id = $cust_alt->medication_master_id ;
                                                $history_tr->pd_medication_name = $was_moved ;
                                                $history_tr->istransition = "1" ;
                                                $history_tr->pd_create_date = $cust_alt->create_date;
                                                $history_tr->pd_create_user = $cust_alt->create_user;
                                                $history_tr->pd_change_date = $cust_alt->change_date;
                                                $history_tr->pd_change_user = $cust_alt->change_user;
                                                $history_tr->save();
                                        }
                                        //.
                                        
                                        // ISPC-2797 Ancuta 18.02.2021
                                        if($elsa_planned_medis &&  (isset($post['planned'][$i]['action'])  && $post['planned'][$i]['action'] == "add" && !empty($post['planned'][$i]['action_date'])) ) {
                                            $_POST['skip_trigger'] = 1;
                                        }
                                        //--
                                        $cust_alt->save();
                                    }
                                    
                                    
                                }
                                
                            }
                            //
                            
                            
                            
                        }
                        if($post['dosage']) //TODO-2982 Carmen 19.03.2020
                		{
                        	$cust->dosage = $post_dosaje[$i];
                		}
                        $cust->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;                        
                        $cust->ipid = $ipid;
                        $cust->medication_master_id = $medid;
                        $cust->verordnetvon = $post['verordnetvon'][$i];
                        $cust->comments = $post['comments'][$i];
                        
                        if(strlen($post['source_ipid'][$i]) > 0  && $post['source_ipid'][$i] != $ipid ){
                            $cust->source_ipid = $post['source_ipid'][$i];
                            
                            if(strlen($post['source_drugplan_id'][$i]) > 0 ){
                                $cust->source_drugplan_id = $post['source_drugplan_id'][$i];
                            }
                        }
                        
                        $cust->medication_change = $medication_change_date[$i];

                        if(isset($post['has_interval'][$i])){
                            $cust->has_interval = $post['has_interval'][$i];
                        } else {
                            $cust->has_interval = "0";
                        }
                        
//                         if($cust->scheduled == "1")
//                         {
                        $cust->days_interval = $post['days_interval'][$i];
                        
                        $cust->days_interval_technical = ! empty($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : null;
                        $cust->dosage_product = ! empty($post['dosage_product'][$i]) ? $post['dosage_product'][$i] : null;
                        
                        if(strlen($post['administration_date'][$i] > 0 ))
                        {
                            $cust->administration_date = date('Y-m-d 00:00:00', strtotime($post['administration_date'][$i]));
                        } 
                        else
                        {
                            $cust->administration_date = "0000-00-00 00:00:00";
                        }
//                         }                        

                        //ISPC-2524 pct.2)  Lore 15.01.2020
                        if(isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1"){
                            $cust->isbedarfs = 0 ;
                        }
                        if(isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1"){
                            $cust->isbedarfs = 1 ;
                        }
                        if((isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1") 
                            || (isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1") ){                 
                            
                            $was_moved = "medication was moved ";
                            
                            $cust_trans = new PatientDrugplanTransition();
                            $cust_trans->ipid = $ipid;
                            $cust_trans->drugplan_id = $post['drid'][$i];
                            if(isset($post['move_bedarf_to_actual'][$i]) && $post['move_bedarf_to_actual'][$i] == "1" ){
                                $cust_trans->transition = "bedarf_to_activ" ;
                                $was_moved .= "from bedarf to active";
                            }
                            if(isset($post['move_actual_to_bedarf'][$i]) && $post['move_actual_to_bedarf'][$i] == "1" ){
                                $cust_trans->transition = "activ_to_bedarf" ;
                                $was_moved .= "from active to bedarf";
                            }
                            $cust_trans->save();
                            
                            $history_tr = new PatientDrugPlanHistory();
                            $history_tr->ipid = $ipid;
                            $history_tr->pd_id = $cust->id;
                            $history_tr->pd_medication_master_id = $cust->medication_master_id ;
                            $history_tr->pd_medication_name = $was_moved ;
                            $history_tr->istransition = "1" ;
                            $history_tr->pd_create_date = $cust->create_date;
                            $history_tr->pd_create_user = $cust->create_user;
                            $history_tr->pd_change_date = $cust->change_date;
                            $history_tr->pd_change_user = $cust->change_user;
                            $history_tr->save();
                        }
                        //.
                        
                        // ISPC-2797 Ancuta 18.02.2021
                        if($elsa_planned_medis &&  (isset($post['planned'][$i]['action'])  && $post['planned'][$i]['action'] == "add" && !empty($post['planned'][$i]['action_date'])) ) {
                            $_POST['skip_trigger'] = 1;
                        }
                        //--
                        $cust->save();
                        // ISPC-2797 Ancuta 18.02.2021
                        if($elsa_planned_medis &&  (isset($post['planned'][$i]['action'])  && $post['planned'][$i]['action'] == "add" && !empty($post['planned'][$i]['action_date'])) ) {
                            $_POST['skip_trigger'] = 0;
                        }
                        //--
                        
                        
                        
                        
                        if($elsa_planned_medis &&  (isset($post['planned'][$i]['action'])  && $post['planned'][$i]['action'] == "add" && !empty($post['planned'][$i]['action_date'])) ) {
                            $shortcut = "K";
                            
                            // OLD ENTRY
                            // old medication name
                            // $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                            
                            $old_med_name[$i] = "";
                            if( $cust->treatment_care == 1 )
                            {
                                $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id );
                            }
                            elseif(  $cust->isnutrition == 1 )
                            {
                                $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id );
                            }
                            else
                            {
                                $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                            }
                            $old_med_name[$i] = $old_med->name;
                            
                            // old dosage
                            if($cust->dosage) {
                                $old_med_dosage[$i] = $cust->dosage;
                            }
                            
                            // old comment
                            if($cust->comments ){
                                $old_med_comments[$i] = $cust->comments." | ";
                            }
                            
                            //  old medication date
                            if($cust->medication_change != "0000-00-00 00:00:00")
                            {
                                $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->medication_change));
                            }
                            else
                            {
                                if($cust->change_date != "0000-00-00 00:00:00")
                                {
                                    $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->change_date));
                                }
                                else
                                {
                                    $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->create_date));
                                }
                            }
                            
                            if($cust->scheduled == 1)
                            {
                                // old days interval
                                if($cust->days_interval ){
                                    $old_med_days_interval[$i] = $cust->days_interval;
                                }
                                
                                $old_med_days_interval_technical[$i] = $cust->days_interval_technical ? ' | '.$cust->days_interval_technical : '';
                                
                                $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_days_interval[$i] . $old_med_days_interval_technical[$i];
                            }
                            else
                            {
                                if(strlen($old_med_dosage[$i])>0){
                                    $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
                                } else	{
                                    $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
                                }
                            }
                            
                            // NEW ENTRY
                            // new name
                            if( $post['treatment_care'] == 1 )
                            {
                                $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
                            }
                            elseif( $post['isnutrition'] == 1 )
                            {
                                $new_med = Doctrine::getTable('Nutrition')->find($medid);
                            }
                            else
                            {
                                $new_med = Doctrine::getTable('Medication')->find($medid);
                            }
                            
                            $new_medication_name[$i] = $new_med->name;
                            
                            // new dosage
                            //$new_medication_dosage[$i] = $post['dosage'][$i];
                            $new_medication_dosage[$i] =  $post_dosaje[$i];
                            
                            // new comments
                            $new_medication_comments[$i] = $post['comments'][$i];
                            
                            // new change date
                            if($medication_change_date[$i] != "0000-00-00 00:00:00"){
                                $medication_change_date_str[$i] = date("d.m.Y", strtotime($medication_change_date[$i]));
                            }
                            else
                            {
                                $medication_change_date_str[$i]="";
                            }
                            
                            
                            if($cust->scheduled == 1)
                            {
                                // new interval
                                $new_medication_days_interval[$i] = $post['days_interval'][$i];
                                $new_medication_days_interval_technical[$i] = ! empty($post['days_interval_technical'][$i]) ? " | ". $post['days_interval_technical'][$i] : '';
                                
                                $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_days_interval[$i] . $new_medication_days_interval_technical[$i];
                            }
                            else
                            {
                                if(strlen($new_medication_dosage[$i])>0)
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                                else
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                            }
                            
                            
                            $attach =  $this->translate('Planned medication was edited: '). $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
                            $insert_pc = new PatientCourse();
                            $insert_pc->ipid = $ipid;
                            $insert_pc->course_date = date("Y-m-d H:i:s", time());
                            $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
                            $insert_pc->tabname = Pms_CommonData::aesEncrypt("PLANNED_medication_edited");
                            $insert_pc->recordid = $post['drid'][$i];
                            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                            $insert_pc->user_id = $userid;
                            $insert_pc->save();
                        }
                        
                        
                        
                        
                        
                        
                    }
                    // ISPC-2797 Ancuta 18.02.2021
                    // for existing meds -  if we have - add to future - then  do not add to verlauf as M shortcut 
                    if($elsa_planned_medis){
                        if( isset($post['planned'][$i]['action']) && !empty($post['planned'][$i]['action_date']) ) {
                        // if plan exists for this medi - update 
                        
                            $drgs_plans_q = Doctrine_Query::create()
                            ->select('id')
                            ->from('PatientDrugplanPlanning')
                            ->where("ipid = ?" , $ipid )
                            ->andWhere("drugplan_id = ?",$post['drid'][$i])
                            ->andWhere("status = 'active'")
                            ->andWhere("isdelete = '0'")
                            ->orderBy("create_date DESC")
                            ->limit(1);
                            $existing_plan_array = $drgs_plans_q->fetchArray();
                            
                            if(!empty($existing_plan_array)){
                                $existing_extra_id = $existing_plan_array[0]['id'];
                                
                                $update_pde = Doctrine::getTable('PatientDrugplanPlanning')->find($existing_extra_id);
                                $update_pde->action_date =  date('Y-m-d 00:00:00', strtotime($post['planned'][$i]['action_date']));
                                $update_pde->save();
                            }
                            else{
                                $insert_plan = new PatientDrugplanPlanning();
                                $insert_plan->ipid = $ipid;
                                $insert_plan->drugplan_id = $post['drid'][$i];
                                $insert_plan->status = 'active';
                                $insert_plan->action = $post['planned'][$i]['action'];
                                $insert_plan->action_date =  date('Y-m-d 00:00:00', strtotime($post['planned'][$i]['action_date']));
                                $insert_plan->save();
                            }
                            
                        //else add new plan 
//                             $plan_add_update[$post['drid'][$i]] =PatientDrugplanPlanningTable::getInstance()->findOrCreateOneBy(
//                             ['ipid','drugplan_id','status' ],
//                             [$ipid, $post['drid'][$i],'status'],
//                             [
//                                 'ipid'         => $ipid,
//                                 'drugplan_id'  => $post['drid'][$i],
//                                 'status'       => 'active',
//                                 'action'       => $post['planned'][$i]['action'],
//                                 'action_date'  => date('Y-m-d 00:00:00', strtotime($post['planned'][$i]['action_date']))
//                             ]
//                             );
                        }
                        
                        /*
                        if($plan_add_update[$post['drid'][$i]] && $update_medication[$i] == "1"){
                            $plan_course=""; 
                            if($post['planned'][$i]['action'] == 'add'){
                                $plan_course = 'ANSETZEN AM | START: '.date('d.m.Y', strtotime($post['planned'][$i]['action_date']));
                            } else{
                                $plan_course = 'ABSETZEN AM | STOP: '.date('d.m.Y', strtotime($post['planned'][$i]['action_date']));
                            }
                            $insert_pc = new PatientCourse();
                            $insert_pc->ipid = $ipid;
                            $insert_pc->course_date = date("Y-m-d H:i:s", time());
                            $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
                            $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_planned");
                            $insert_pc->recordid = $post['drid'][$i];
                            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($plan_course));
                            $insert_pc->user_id = $userid;
                            $insert_pc->save();
                        } */
                    }
                }
                else
                {//insert new
                    if($medid > '0')
                    {
                    	
                        $cust_new = new PatientDrugPlan();
                        //ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
                        $pc_listener = $cust_new->getListener()->get('IntenseMedicationConnectionListener');
                        $pc_listener->setOption('disabled', true);
                        //--
                        // ISPC-2797 Ancuta 18.02.2021
                        if($elsa_planned_medis && (isset($post['planned'][$i]['action']) && !empty($post['planned'][$i]['action_date'])) && strtotime($post['planned'][$i]['action_date']) > strtotime(date('d.m.Y')) ) {
                            $_POST['skip_trigger'] = 1;
                        }
                        //--
                        
                        $cust_new->ipid = $ipid;
                        $cust_new->dosage = $post_dosaje[$i];
                        $cust_new->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
                        $cust_new->medication_master_id = $medid;
                        $cust_new->isbedarfs = $post['isbedarfs'];
                        $cust_new->iscrisis = $post['iscrisis'];
                        $cust_new->isivmed = $post['isivmed'];
                        $cust_new->treatment_care = $post['treatment_care'];
                        $cust_new->isnutrition = $post['isnutrition'];
                        $cust_new->isintubated = $post['isintubated']; // ISPC-2176
                        $cust_new->scheduled = $post['scheduled'];
                        
                        if(isset($post['has_interval'][$i])){
                            $cust_new->has_interval = $post['has_interval'][$i];
                        }
                        else
                        {
                            $cust_new->has_interval = "0";
                        }
                        $cust_new->days_interval = $post['days_interval'][$i];
                        
                        $cust_new->days_interval_technical = isset($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : null;
                        $cust_new->dosage_product = isset($post['dosage_product'][$i]) ? $post['dosage_product'][$i] : null;
                        
                        if(!empty($post['administration_date'][$i]))
                        {
                            $cust_new->administration_date = date('Y-m-d 00:00:00', strtotime($post['administration_date'][$i]));
                        }
                        
                        $cust_new->verordnetvon = $post['verordnetvon'][$i];
                        $cust_new->comments = $post['comments'][$i];
                        if(!empty($post['medication_change'][$i])){
                            $cust_new->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                        } else{
                            $cust_new->medication_change = date('Y-m-d 00:00:00');
                        }
                        
                        
                        if(!empty($post['source_ipid'][$i]) &&  $post['source_ipid'][$i] != $ipid ){
                            
                            $cust_new->source_ipid = $post['source_ipid'][$i];
                            
                            if(!empty($post['source_drugplan_id'][$i])){
                                
                                $cust_new->source_drugplan_id = $post['source_drugplan_id'][$i];
                            }
                        }
                        
                        $cust_new->save();
                        
                        // ISPC-2797 Ancuta 18.02.2021
                        if($elsa_planned_medis &&  (isset($post['planned'][$i]['action']) && !empty($post['planned'][$i]['action_date']) && strtotime($post['planned'][$i]['action_date']) > strtotime(date('d.m.Y')) ) ) {
                            $_POST['skip_trigger'] = 0;
                        }
                        //--
                        
                        $inserted_id = $cust_new->id;
                        //ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
                        $pc_listener->setOption('disabled', false);
                        //--
                        
                        
                        //ISPC-2554 Carmen pct.3 27.03.2020
                        $drugplanid = $inserted_id;
                        //--
                        
                        // Insert dosage
                        if(is_array($post['dosage'][$i]))
                        { // NEW style
                        
                            foreach ($post['dosage'][$i] as $time => $dosage_value)
                            {
                                //  insert new lines
                                $insert_pdd = new PatientDrugPlanDosage();
                                $insert_pdd->ipid = $ipid;
                                $insert_pdd->drugplan_id = $inserted_id;
                                //TODO-3624 Ancuta 23.11.2020
                                $insert_pdd->dosage = $dosage_value;
                                $insert_pdd->dosage_full = $post['dosage_full'][$i][$time];
                                $insert_pdd->dosage_concentration = $post['dosage_concentration'][$i][$time];
                                $insert_pdd->dosage_concentration_full = $post['dosage_concentration_full'][$i][$time];
                                //--
                                $insert_pdd->dosage_time_interval = $time.":00";
                                $insert_pdd->save();
                            }
                            
                        }
                      
                        // Insert extra data
                        $insert_pde = new PatientDrugPlanExtra();
                        $insert_pde->ipid = $ipid;
                        $insert_pde->drugplan_id = $inserted_id;
                        $insert_pde->drug = $post['drug'][$i];
                        
                        $insert_pde->dosage_24h_manual = $post['dosage_24h'][$i];            //ISPC-2684 Lore 12.10.2020
                        $insert_pde->unit_dosage = $post['unit_dosage'][$i];            //ISPC-2684 Lore 05.10.2020
                        $insert_pde->unit_dosage_24h = $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                        
                        //$insert_pde->unit = $post['unit'][$i]; //ISPC-2554
                        $insert_pde->type = $post['type'][$i];
                        
                        $insert_pde->indication = trim($post['indication'][$i]);
                        $insert_pde->importance = $post['importance'][$i];
                        
                        //ISPC-2554 pct.1 Carmen 03.04.2020
                        if(substr($post['dosage_form'][$i], 0, 3) == 'mmi')
                        {
                        	$data['clientid'] = $clientid;
                        	$data['isfrommmi'] = '1';
                        	$data['mmi_code'] = substr($post['dosage_form'][$i], 4);
                        	$data['dosage_form'] = $dosageformmmi[$data['mmi_code']]['dosageform_name'];
                        	$data['extra'] = '1';
                        	$dosagecustentity = MedicationDosageformTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'mmi_code'), array($clientid, substr($post['dosage_form'][$i], 4)), $data);
                        	if($dosagecustentity)
                        	{
                        		$insert_pde->dosage_form = $dosagecustentity->id;
                        	}
                        }
                        else
                        {
                        	$insert_pde->dosage_form = $post['dosage_form'][$i];
                        }
                        //--
                        //ISPC-2554 Carmen 12.05.2020
                        if(substr($post['unit'][$i], 0, 3) == 'mmi')
                        {
                        	$data['clientid'] = $clientid;
                        	$data['unit'] = substr($post['unit'][$i], 4);
                        	$unitcustentity = MedicationUnitTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'unit'), array($clientid, substr($post['unit'][$i], 4)), $data);
                        	if($unitcustentity)
                        	{
                        		$unit_med = $unitcustentity->id;
                        	}
                        }
                        else
                        {
                        	$unit_med = $post['unit'][$i];
                        }
                        $insert_pde->unit = $unit_med;
                        //--
                        $insert_pde->concentration = $post['concentration'][$i];
                        
                        // ISPC-2176
                        $insert_pde->packaging = $post['packaging'][$i];
                        $insert_pde->kcal = $post['kcal'][$i];
                        $insert_pde->volume = $post['volume'][$i];
                        // ISPC-2247
                        $insert_pde->escalation = $post['escalation'][$i];
                        
                        $insert_pde->save();
                        

						//TODO-3729 Ancuta 28.01.2021 :: Comented here - and moved bellow - as else for  approval user
						/*
                        //ISPC-2614 Ancuta :: Hack to skip verlauf  and trigger listner 
                        $_POST['skip_trigger'] = 1;
                        $cust_new->comments = $post['comments'][$i].' ';
                        $cust_new->save();
                        $_POST['skip_trigger'] = 0;
                        //--
						*/
                        

                        // this is for  Medication acknowledge
                        if(in_array($userid,$approval_users) && $acknowledge == "1" ){
                            
                            // NEW ENTRY
                            if($post['isbedarfs'] == 1)
                            {
                                $shortcut = "N";
                            }
                            elseif($post['isivmed'] == 1)
                            {
                                $shortcut = "I";
                            }
                            elseif($post['isschmerzpumpe'] == 1)
                            {
                                $shortcut = "Q";
                                $prefix = "Schmerzpumpe ";
                            }
                            elseif($post['treatment_care'] == 1 )
                            {
                                $shortcut = "BP";
                            }
                            elseif($post['scheduled'] == 1 )
                            {
                                $shortcut = "MI";
                            }
                            elseif($post['iscrisis'] == 1 )
                            {
                            	$shortcut = "KM";
                            }
                            elseif($post['isintubated'] == 1 )
                            {
                            	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;;
                            }
                            else
                            {
                                $shortcut = "M";
                            }
 
                            // new name
                            if( $post['treatment_care'] == 1 )
                            {
                                $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
                            }
                            elseif( $post['isnutrition'] == 1 )
                            {
                                $new_med = Doctrine::getTable('Nutrition')->find($medid);
                            }
                            else
                            {
                                $new_med = Doctrine::getTable('Medication')->find($medid);
                            }
                            	
                            $new_medication_name[$i] = $new_med->name;
                            	
                            // new dosage
                            $new_medication_dosage[$i] = $post_dosaje[$i];
                            	
                            // new comments
                            $new_medication_comments[$i] = $post['comments'][$i];
                            	
                            // new change date
                            $medication_change_date_str[$i]= date("d.m.Y",time());
                            
                            
                            if($post['scheduled'] == 1 )
                            {
                                // new interval
                                $new_medication_days_interval[$i] = $post['days_interval'][$i];
                                $new_medication_days_interval_technical[$i] = ! empty($post['days_interval_technical'][$i]) ? " | ".$post['days_interval_technical'][$i] : '';

                                $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_days_interval[$i] . $new_medication_days_interval_technical[$i];
                            }
                            else
                            {
                                if(strlen($new_medication_dosage[$i])>0)
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                                else
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                            }
                            	
                            	
                            $attach = $new_entry[$i].'';
                            $insert_pc = new PatientCourse();
                            $insert_pc->ipid = $ipid;
                            $insert_pc->course_date = date("Y-m-d H:i:s", time());
                            $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
                            $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
                            $insert_pc->recordid = $inserted_id;
                            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                            $insert_pc->user_id = $userid;
                            $insert_pc->save();
                        } 
   						//TODO-3729 Ancuta 28.01.2021 :: Moved the code here - so approval users do not dounle the verlauf entries 
                        else
                        {
                            //ISPC-2614 Ancuta :: Hack to skip verlauf  and trigger listner
                            $_POST['skip_trigger'] = 1;
                            $cust_new->comments = $post['comments'][$i].' ';
                            $cust_new->save();
                            $_POST['skip_trigger'] = 0;
                            //--
                        }
						// --- 
						
                        
                        // ISPC-2797 Ancuta 18.02.2021
                        if($elsa_planned_medis && ( isset($post['planned'][$i]['action']) && !empty($post['planned'][$i]['action_date']) && strtotime($post['planned'][$i]['action_date']) > strtotime(date('d.m.Y')) ) ) {
                            // new name
                            if( $post['treatment_care'] == 1 )
                            {
                                $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
                            }
                            elseif( $post['isnutrition'] == 1 )
                            {
                                $new_med = Doctrine::getTable('Nutrition')->find($medid);
                            }
                            else
                            {
                                $new_med = Doctrine::getTable('Medication')->find($medid);
                            }
                            
                            $new_medication_name[$i] = $new_med->name;
                            
                            // new dosage
                            $new_medication_dosage[$i] = $post_dosaje[$i];
                            
                            // new comments
                            $new_medication_comments[$i] = $post['comments'][$i];
                            
                            // new change date
                            $medication_change_date_str[$i]= date("d.m.Y",time());
                            
                            
                            if($post['scheduled'] == 1 )
                            {
                                // new interval
                                $new_medication_days_interval[$i] = $post['days_interval'][$i];
                                $new_medication_days_interval_technical[$i] = ! empty($post['days_interval_technical'][$i]) ? " | ".$post['days_interval_technical'][$i] : '';
                                
                                $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_days_interval[$i] . $new_medication_days_interval_technical[$i];
                            }
                            else
                            {
                                if(strlen($new_medication_dosage[$i])>0)
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                                else
                                {
                                    $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                                }
                            }
                            
                            
                            $attach = 'ANSETZEN AM | START: '.date('d.m.Y', strtotime($post['planned'][$i]['action_date']));
                            $attach .= "\n".$new_entry[$i].'';
                            
                            $insert_pc = new PatientDrugplanPlanning();
                            $insert_pc->ipid = $ipid;
                            $insert_pc->drugplan_id = $inserted_id;
                            $insert_pc->status = 'active';
                            $insert_pc->action = 'add';
                            $insert_pc->action_date = date('Y-m-d 00:00:00', strtotime($post['planned'][$i]['action_date']));
                            $insert_pc->save();
                            
                            $insert_pc = new PatientCourse();
                            $insert_pc->ipid = $ipid;
                            $insert_pc->course_date = date("Y-m-d H:i:s", time());
                            $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
                            $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_planned");
                            $insert_pc->recordid = $inserted_id;
                            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                            $insert_pc->user_id = $userid;
                            $insert_pc->save();
                        }
                        //-- 
                    }
                    
                    
                    
                }

                
                
                //ISPC-2554 pct.3 Carmen 27.03.2020
               	$atcarr = (array)json_decode(html_entity_decode($post[$i]['atc']));
               		
	            $atcid = array_search($drugplanid, array_column($atcdet, 'drugplan_id'));
	              	
	            if($atcid !== false)
	            {
               		if($atcdet[$atcid]['atc_code'] != $atcarr['atc_code'])
               		{
               			$todelete[] = $atcdet[$atcid]['id'];
               			
               			$toupdate[$atcindex]['ipid'] = $ipid;
               			$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
               			$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
               			$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
               			$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
               			$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
               			$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
               			$atcindex++;
               		}
	             }
	             else 
	             {               
		            if(!empty($atcarr))
		            {
			             $toupdate[$atcindex]['ipid'] = $ipid;
			             $toupdate[$atcindex]['drugplan_id'] = $drugplanid;
			             $toupdate[$atcindex]['medication_master_id'] = $medmasterid;
			             $toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
               			 $toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
               			 $toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
               			 $toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
			             $atcindex++;
		            }
               	}
                //--                
            }
        }
        //var_dump($todelete);exit;
        //ISPC-2554 pct.3 Carmen 27.03.2020
        if(!empty($todelete))
        {
	        $querydel =  PatientDrugPlanAtcTable::getInstance()->createQuery('atc')
	    	->delete()   	
    		->whereIn('atc.id', $todelete);
    		$querydel->execute();
    	}
    	
    	
    	if(!empty($toupdate))
    	{
	    	$atccollection = new Doctrine_Collection('PatientDrugPlanAtc');
	    	$atccollection->fromArray($toupdate);
	    	$atccollection->save();
    	}
    }
    
    
    
    // ############################################
    // #######ISPC-2507 ###############
    // ############################################

    /**
     * @author Ancuta
     * ISPC-2507 10.02.2020
     * @param unknown $post
     * @param boolean $post_ipid
     */
    public function update_multiple_data_pharma_request($post,$post_ipid = false, $client_requests_reasons = array() ,$process_request = false){
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;

        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);

        if ($post_ipid) {
            $ipid = $post_ipid;
        }

        $post_dosage_interval = array();

        //TODO-3462
        $send_messages4not_accepted_requests = false;
        $modules = new Modules();
        if($modules->checkModulePrivileges("242", $clientid))
        {
            $send_messages4not_accepted_requests = true;            
        }
        
        // get user details
        $master_user_details = new User();
        $users_details_arr = $master_user_details->getUserDetails($userid);
        $users_details = $users_details_arr[0];
        $user_name = $users_details['first_name'] . ' ' . $users_details['last_name'];

        // get patient details
        $patient_details = PatientMaster::get_multiple_patients_details(array(
            $ipid
        ));
        $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
        
        $post_dosaje = array();
        $post_dosaje = array();
        foreach ($post['hidd_medication'] as $i => $med_item) {
            $update_medication[$i] = "0";

            if ($post['hidd_medication'][$i] > 0) {
                $medid = $post['hidd_medication'][$i];
            } else {
                $medid = $post['newhidd_medication'][$i];
            }

            if (empty($post['verordnetvon'][$i])) {
                $post['verordnetvon'][$i] = 0;
            }

            // DOSAJE
            $post_dosaje[$i] = "";
            $post_dosage_interval[$i] = null;
            if (is_array($post['dosage'][$i])) { // NEW style
                foreach ($post['dosage'][$i] as $time => $dosage_value) {
                    if (strlen($dosage_value) == 0) {
                        // $dosage_value = " / ";
                        $dosage_value = "";
                    }

                    $old_dosage_array[$i][] = $dosage_value;
                }
                $post_dosaje[$i] = implode("-", $old_dosage_array[$i]);

                // PATIENT TIME SCHEME
                $patient_time_scheme = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid, $clientid);

                if (empty($patient_time_scheme['patient'])) {
                    // insert in patient time scheme

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!                   
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!                   
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!                   
// HERE MUST CHANGE
                    foreach ($post['dosage'][$i] as $time => $dosage_value) {
                        $insert_pc = new PatientDrugPlanDosageIntervals();
                        $insert_pc->ipid = $ipid;
                        $insert_pc->time_interval = $time . ":00";
                        $insert_pc->save();
                    }
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!                   
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!                   
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!                   
                }
            } else { // OLD style
                $post_dosaje[$i] = $post['dosage'][$i];
                $post_dosage_interval[$i] = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
            }

            
            
            // --------
            // FIRST:: we mark as deleted the drugplan added for this request
            // --------
            // remove ONLY id user can edit  == pharma user 
            if($process_request !== true && isset($post['has_request_change'][$i]) && $post['has_request_change'][$i] == 1){
                
                // check if medication exists for curent request and mark as deleted- to add new
                $saved_to_request = Doctrine_Query::create()
                ->select("*")
    			->from('PharmaPatientDrugplan')
    			->where("ipid= ?", $ipid )
    			->andWhere("drugplan_id= ?", $post['drid'][$i] )
    			->andWhere("pharma_request_id= ?", $post['request_id'] )
    			->andWhere("pharma_med_type= ?", $post['pharma_med_type'][$i] )
        		->fetchArray();
        		if(!empty($saved_to_request)){
        		    $delete_ids = array();
        		    foreach ($saved_to_request as $k=>$m_line){
        		        $delete_ids[] = $m_line['id'];
        		    }
        		    if(! empty($delete_ids)){
            		    $q1 = Doctrine_Query::create()
            		    ->update('PharmaPatientDrugplan')
            		    ->set('isdelete', "?", '1')
            		    ->whereIn('id', $delete_ids)
            		    ->andWhere('ipid = ?', $ipid)
            		    ->andwhere("drugplan_id = ?",$post['drid'][$i] )
            		    ->andwhere("pharma_request_id = ?",$post['request_id'])
            		    ->execute();	
            		    
            		    $q2 = Doctrine_Query::create()
            		    ->update('PharmaPatientDrugplanExtra')
            		    ->set('isdelete', "?", '1')
            		    ->whereIn('pharma_drugplan_id', $delete_ids)
            		    ->andWhere('ipid = ?', $ipid)
            		    ->andwhere("drugplan_id = ?",$post['drid'][$i] )
            		    ->execute();	
            		    
            		    $q3 = Doctrine_Query::create()
            		    ->update('PharmaPatientDrugplanDosage')
            		    ->set('isdelete', "?", '1')
            		    ->whereIn('pharma_drugplan_id', $delete_ids)
            		    ->andWhere('ipid = ?', $ipid)
            		    ->andwhere("drugplan_id = ?",$post['drid'][$i] )
            		    ->execute();	
     	
        		      }
        		}
            }
                
            if (
                ($process_request === false && isset($post['has_request_change'][$i]) && $post['has_request_change'][$i] == 1)
    		    || 
                ($process_request === true && isset($post['has_request_change'][$i]) && $post['has_request_change'][$i] == 1 &&  isset($post['status'][$i]) && !empty($post['status'][$i]))
    		    ) {

                $pharma_drug_plan = new PharmaPatientDrugplan();
                $pharma_drug_plan->ipid = $ipid;
                $pharma_drug_plan->pharma_med_type=  $post['pharma_med_type'][$i];
                $pharma_drug_plan->drugplan_id = $post['drid'][$i];
                $pharma_drug_plan->pharma_request_id= $post['request_id'];
                $pharma_drug_plan->dosage = $post_dosaje[$i];
                $pharma_drug_plan->dosage_interval = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
                $pharma_drug_plan->medication_master_id = $medid;
                $pharma_drug_plan->medication  = $post['medication'][$i];
                $pharma_drug_plan->isbedarfs = $post['isbedarfs'];
                $pharma_drug_plan->iscrisis = $post['iscrisis'];
                $pharma_drug_plan->isivmed = $post['isivmed'];
                $pharma_drug_plan->treatment_care = $post['treatment_care'];
                $pharma_drug_plan->isnutrition = $post['isnutrition'];
                $pharma_drug_plan->isintubated = $post['isintubated']; // ISPC-2176
                $pharma_drug_plan->scheduled = $post['scheduled'];

                if (isset($post['has_interval'][$i])) {
                    $pharma_drug_plan->has_interval = $post['has_interval'][$i];
                } else {
                    $pharma_drug_plan->has_interval = "0";
                }

                $pharma_drug_plan->days_interval = $post['days_interval'][$i];

                $pharma_drug_plan->days_interval_technical = ! empty($post['days_interval_technical'][$i]) ? $post['days_interval_technical'][$i] : null;
                $pharma_drug_plan->dosage_product = ! empty($post['dosage_product'][$i]) ? $post['dosage_product'][$i] : null;

                if (! empty($post['administration_date'][$i])) {
                    $pharma_drug_plan->administration_date = date('Y-m-d 00:00:00', strtotime($post['administration_date'][$i]));
                } else {
                    $pharma_drug_plan->administration_date = "0000-00-00 00:00:00";
                }

                $pharma_drug_plan->verordnetvon = $post['verordnetvon'][$i];
                $pharma_drug_plan->comments = $post['comments'][$i];
                if (! empty($post['medication_change'][$i])) {
                    $pharma_drug_plan->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                } else {
                    $pharma_drug_plan->medication_change = date('Y-m-d 00:00:00');
                }
//                 $pharma_drug_plan->request_reason = $post['request_reason'][$i]; // get posted reasons, and  save text
//                 $pharma_drug_plan->request_reason_ids = $post['request_reason']; // 
//                 $pharma_drug_plan->request_comment = $post['request_comment'];
                $pharma_drug_plan->save();
                
                $pharma_drugplan_id = $pharma_drug_plan->id;

                // Insert extra data
                $pharma_drug_plan_extra = new PharmaPatientDrugplanExtra();
                $pharma_drug_plan_extra->ipid = $ipid;
                $pharma_drug_plan_extra->pharma_drugplan_id = $pharma_drugplan_id;
                $pharma_drug_plan_extra->drugplan_id = $post['drid'][$i];
                $pharma_drug_plan_extra->drug = $post['drug'][$i];
                $pharma_drug_plan_extra->unit = $post['unit'][$i];
                $pharma_drug_plan_extra->type = $post['type'][$i];
                $pharma_drug_plan_extra->indication = trim($post['indication'][$i]);
                $pharma_drug_plan_extra->importance = $post['importance'][$i];
                $pharma_drug_plan_extra->dosage_form = $post['dosage_form'][$i];
                $pharma_drug_plan_extra->concentration = $post['concentration'][$i];
                $pharma_drug_plan_extra->packaging = $post['packaging'][$i];
                $pharma_drug_plan_extra->kcal = $post['kcal'][$i];
                $pharma_drug_plan_extra->volume = $post['volume'][$i];
                $pharma_drug_plan_extra->escalation = $post['escalation'][$i];
                $pharma_drug_plan_extra->save();

                
                if (is_array($post['dosage'][$i])) { // NEW style
                    foreach ($post['dosage'][$i] as $time => $dosage_value) {
                        // insert new lines
                        $pharma_drug_plan_dosage = new PharmaPatientDrugplanDosage();
                        $pharma_drug_plan_dosage->ipid = $ipid;
                        $pharma_drug_plan_dosage->pharma_drugplan_id = $pharma_drugplan_id;
                        $pharma_drug_plan_dosage->drugplan_id = $post['drid'][$i];
                        $pharma_drug_plan_dosage->dosage = $dosage_value;
                        $pharma_drug_plan_dosage->dosage_time_interval = $time . ":00";
                        $pharma_drug_plan_dosage->save();
                    }
                }
                
                
                
                
                // save data per medication
                
                if($process_request === true){
                
                    if(isset($post['status'][$i])){
                    
                        // Doctor is processing the request
                        $Not_processed_request = Doctrine_Query::create()
                        ->select("*")
                        ->from('PharmaPatientRequests')
                        ->where("ipid= ?", $ipid )
                        ->andWhere("drugplan_id= ?", $post['drid'][$i] )
                        ->andWhere("request_id= ?", $post['request_id'] )
                        ->andWhere("processed = ?", "no" )
                        ->andWhere("isdelete = ?", "0" )
                        ->fetchArray();
                        

                        if(!empty($Not_processed_request)){
                            foreach($Not_processed_request as $k=>$pp_rec_data){
                                $cust = Doctrine::getTable('PharmaPatientRequests')->find($pp_rec_data['id']);
                                $cust->processed = "yes";
                                $cust->processed_by  = $userid;
                                $cust->processed_date= date('Y-m-d H:i:s');
                                $cust->processed_status= $post['status'][$i];
                                $cust->processed_deny_comment= $post['status_comment'][$i];
                                $cust->processed_medi_line = $pharma_drugplan_id;
                                $cust->save();
                            }
                        } 
                        
                        
                        // marck as processed the  request id - if ALL PharmaPatientRequests are 
//                         PharmaPatientDrugplanRequests
                        // check if all medi request from request are processed  
                        $not_processed_request = array();
                        $not_processed_request = Doctrine_Query::create()
                        ->select("*")
                        ->from('PharmaPatientRequests')
                        ->where("ipid = ?", $ipid )
                        ->andWhere("drugplan_id= ?", $post['drid'][$i] )
                        ->andWhere("request_id= ?", $post['request_id'] )
                        ->andWhere("processed = 'no' "  )
                        ->andWhere("isdelete = 0" )
                        ->fetchArray();
                        
                        if(empty($not_processed_request)){
                            // this means that all medi requests are processed so we can marck the reques id as process 
                            
//                             $cust = Doctrine::getTable('PharmaPatientDrugplanRequests')->findOneBy('id',$post['request_id']);
//                             if($cust){
                                
//                                 $cust->processed = "yes";
//                                 $cust->save();
//                             }
                            
        /*                     $request_processed = Doctrine_Query::create()
                            ->select("*")
                            ->from('PharmaPatientDrugplanRequests')
                            ->where("ipid= ?", $ipid )
                            ->andWhere("id = ?", $post['request_id'] )
                            ->andWhere("processed = ?", "no" )
                            ->andWhere("isdelete = ?", "0" )
                            ->fetchArray();
         */                    
                        } else{
                            // update todo!!!!
                            
                            
                        }
                        
                        //TODO-3462 Ancuta 19.10.2020
                        //if a medication is not accepted by family doctor please send a message to  -  specific users 
						if($send_messages4not_accepted_requests){
     
                            $not_accepted = array();
                            
                            if($post['status'][$i] == 'dont_agree'){
                                
                       
                                $text  = "";
                                $text .= "Patient ".$patient_name." \n ";
                                $text .= "Der Benutzer ".$user_name." hat die Empfehlung der Apotheke nicht angemommen. \n ";
                                $text .= "Medikation:  " .  $post['medication'][$i]." \n ";
                                $text .= "Grund:  " .  $post['status_comment'][$i]." \n ";
                                
                                $not_accepted = array (
                                    'ipid' => $ipid,
                                    'request_id' =>$post['request_id'],
                                    'drugplan_id' =>$post['drid'][$i],
                                    'status_comment' => $post['status_comment'][$i],
                                    'pharma_drugplan_id' => $pharma_drugplan_id,                            
                                    'text' => $text,                            
                                );
                                
                                Messages::medication_pharma_request_messages($ipid, $not_accepted);
                            }
                        }
                    }
                    
                    
                }
                else
                {
                    
                    $line_reasons = array();
                    $reason_texts = array();
                    $reason_texts[$i] = array();
                    if(!empty($post['request_reason'][$i])){
                        foreach($post['request_reason'][$i] as $k=>$reas_id){
                            $line_reasons[$reas_id][] = $client_requests_reasons[$reas_id];
                            $reason_texts[$i][] = $client_requests_reasons[$reas_id];
                        }
                    }
                    // find data  ipid, userid, request id NOT processed
                    
                    $Not_processed_request = Doctrine_Query::create()
                    ->select("*")
                    ->from('PharmaPatientRequests')
                    ->where("ipid= ?", $ipid )
                    ->andWhere("drugplan_id= ?", $post['drid'][$i] )
                    ->andWhere("request_id= ?", $post['request_id'] )
                    ->andWhere("processed = ?", "no" )
                    ->andWhere("isdelete = ?", "0" )
                    ->fetchArray();
                    
                    if(!empty($Not_processed_request)){
                        foreach($Not_processed_request as $k=>$pp_rec_data){
                            $cust = Doctrine::getTable('PharmaPatientRequests')->find($pp_rec_data['id']);
                            $cust->isdelete = 1;
                            $cust->save();
                        }
                    } 
                    
                    //insert new                 
                    $pp_request = new PharmaPatientRequests();
                    $pp_request->ipid = $ipid;
                    $pp_request->drugplan_id = $post['drid'][$i];
                    $pp_request->request_id = $post['request_id'];
                    $pp_request->request_reason = !empty($line_reasons) ? serialize($line_reasons) : "";
                    $pp_request->request_comment = $post['request_comment'][$i];
                    $pp_request->request_user = $userid;
                    $pp_request->request_date = date('Y-m-d H:i:s');
                    $pp_request->request_medi_line = $pharma_drugplan_id;
                    $pp_request->save();
                    
                    // Changes on 11.03.2020:: Start 
                    $shortcut="MK";
                    $tabname = "pharma_request_drugplan_id-".$post['drid'][$i];
                    
                    $custom_request_text[$i] = "";
                    $custom_request_text[$i] .= $post['medication'][$i].":";
                    if(!empty($reason_texts[$i])){
                        $custom_request_text[$i] .= " Grund: ".implode(",", $reason_texts[$i]);
                    }
                    if(!empty($post['request_comment'][$i])){
                        $custom_request_text[$i] .= " Kommentar der Apotheke: ".$post['request_comment'][$i];
                    }
                    
                    // check if course was added for this drugplan and request id 
                    $request_courses = Doctrine_Query::create()
                    ->select("*, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
                    ->from('PatientCourse')
                    ->where('ipid =?',$ipid)
                    ->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = ?",$shortcut)
                    ->andWhere("recordid = ?",$post['request_id'])
                    ->andWhere("wrong = 0")
                    ->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = ? ",$tabname)
                    ->orderBy('course_date ASC')
                    ->fetchArray();
                    
                    $existing_courses = array();
                    $update_courses = array();
                    foreach($request_courses as $k => $rec_course){
                        if($rec_course['course_title'] == $custom_request_text[$i]){
                            $existing_courses[$i][] = $rec_course['id'];
                        } else{
                            $update_courses[$i][] = $rec_course['id'];
                        }
                    }
                    
                    
                    if(empty($request_courses)  || (empty($existing_courses[$i]) && !empty($update_courses[$i])) ){
                        $insert_pc = new PatientCourse();
                        $insert_pc->ipid = $ipid;
                        $insert_pc->course_date = date("Y-m-d H:i:s", time());
                        $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
                        $insert_pc->tabname = Pms_CommonData::aesEncrypt($tabname);
                        $insert_pc->recordid = $post['request_id'];
                        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($custom_request_text[$i]));
                        $insert_pc->user_id = $userid;
                        $insert_pc->save();
                    }
                    
                    // Changes on 11.03.2020:: END 
                        
                }
         
            }
        }
    }
   
    public function update_multiple_data_pharma_custom_request($post,$post_ipid = false, $client_requests_reasons = array() ,$process_request = false){

//         dd(func_get_args());
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;

        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);

        if ($post_ipid) {
            $ipid = $post_ipid;
        }
        
        foreach($post['custom_request'] as $k =>$cr_data){
            
            if(!isset($cr_data['custom_id']) && empty ( $cr_data['request_reason'] ) &&  empty ( $cr_data['comments'] ) ) {
                continue;
            }
            
            $line_reasons = array();
            $reasons_names = array();
            if(!empty($cr_data['request_reason'])){
                foreach($cr_data['request_reason'] as $k=>$reas_id){
                    $line_reasons[$reas_id][] = $client_requests_reasons[$reas_id];
                    $reasons_names[] = $client_requests_reasons[$reas_id];
                }
            }
        
            if(isset($cr_data['custom_id'])){
                // UPDATE EXISTING
                $cust = Doctrine::getTable('PharmaPatientRequests')->find($cr_data['custom_id']);
                if($cust){
                    // update if pharma user
                    if($process_request !== true )
                    {
                        $cust->request_reason = !empty($line_reasons) ? serialize($line_reasons) : "";
                        $cust->request_comment = $cr_data['comments'];
                        $cust->request_minutes = $cr_data['minutes'];
                    }
                    // process if doctor user
                    if($process_request === true && isset($cr_data['status']))
                    {
                        $cust->processed = "yes";
                        $cust->processed_by  = $userid;
                        $cust->processed_date= date('Y-m-d H:i:s');
                        $cust->processed_status= $cr_data['status'];
                        $cust->processed_deny_comment= $cr_data['status_comment'];
                    }
                    $cust->save();
                }
            } 
            else
            {
                
                if(!empty ( $cr_data['request_reason'] ) ||  empty ($cr_data['comments'])) 
                {
                
                    //insert new
                    $pp_request = new PharmaPatientRequests();
                    $pp_request->ipid = $ipid;
                    $pp_request->custom = "yes";
                    $pp_request->request_id = $cr_data['request_id'];
                    $pp_request->request_reason = !empty($line_reasons) ? serialize($line_reasons) : "";
                    $pp_request->request_comment = $cr_data['comments'];
                    $pp_request->request_minutes = $cr_data['minutes'];
                    $pp_request->request_user = $userid;
                    $pp_request->request_date = date('Y-m-d H:i:s');
                    $pp_request->save();
                    $inserted_id = $pp_request->id;
                    
                    

                    // Changes on 11.03.2020:: Start 
                    $shortcut ="MK";
                    $tabname = "pharma_custom_request-".$cr_data['request_id'];
                    
                    $custom_request_text = "";
                    if(!empty($reasons_names)){
                        $custom_request_text .= " Grund: ".implode(", ", $reasons_names);
                        $custom_request_text .= ";";
                    }
                    if(!empty($cr_data['comments'])){
                        $custom_request_text .= " Kommentar der Apotheke: ".$cr_data['comments'];
                    }
                    $insert_pc = new PatientCourse();
                    $insert_pc->ipid = $ipid;
                    $insert_pc->course_date = date("Y-m-d H:i:s", time());
                    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
                    $insert_pc->tabname = Pms_CommonData::aesEncrypt($tabname);
                    $insert_pc->recordid = $cr_data['request_id'];
                    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($custom_request_text));
                    $insert_pc->user_id = $userid;
                    $insert_pc->save();
                    // Changes on 11.03.2020:: END 
                 /*    
                    $shortcut = "DG";
                    $custom_request_text = "";
                    $custom_request_text .= "Grund der Empfehlung: ";
                    $custom_request_text .= !empty($reasons_names) ? implode(',',$reasons_names) : " - ";
                    $custom_request_text .= " | ";
                    $custom_request_text .= "Kommentar der Apotheke: ";
                    $custom_request_text .= !empty($cr_data['comments']) ? $cr_data['comments'] : " - ";
                    $custom_request_text .= " | ";
                    $custom_request_text .= "Req_time_needed: ";
                    $custom_request_text .= !empty($cr_data['minutes']) ? $cr_data['minutes'] : " - ";
                    
                    $insert_pc = new PatientCourse();
                    $insert_pc->ipid = $ipid;
                    $insert_pc->course_date = date("Y-m-d H:i:s", time());
                    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
                    $insert_pc->tabname = Pms_CommonData::aesEncrypt("pharma_custom_request");
                    $insert_pc->recordid = $inserted_id;
                    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($custom_request_text));
                    $insert_pc->user_id = $userid;
                    $insert_pc->save(); */
                }
                
            }
        }
    }
    
    
    
    public function update_multiple_data_deletedmeds($post){ // ISPC 1624 - 08.03.2016
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        $extra_array="";
        foreach ($post['hidd_medication'] as $i => $med_item)
        {
            $update_medication[$i] = "0";

            if ($post['hidd_medication'][$i] > 0)
            {
                $medid = $post['hidd_medication'][$i];
            }
            else
            {
                $medid = $post['newhidd_medication'][$i];
            }

            if (empty($post['verordnetvon'][$i]))
            {
                $post['verordnetvon'][$i] = 0;
            }

            // DOSAJE
            $post_dosaje[$i] = "";
            $post_dosage_interval[$i] = null;
            
            if(is_array($post['dosage'][$i]))
            { // NEW style
                foreach ($post['dosage'][$i] as $time => $dosage_value)
                {
                    if(strlen($dosage_value) == 0){
                        // $dosage_value = " / ";
                        $dosage_value = "";
                    }
                
                    $old_dosage_array[$i][] = $dosage_value;
                }
                $post_dosaje[$i] = implode("-",$old_dosage_array[$i]);
                
                

                // PATIENT TIME SCHEME
                $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid);
                
                if(empty($patient_time_scheme['patient'])){
                    // insert in patient time scheme
                
                    foreach ($post['dosage'][$i] as $time => $dosage_value)
                    {
                        $insert_pc = new PatientDrugPlanDosageIntervals();
                        $insert_pc->ipid = $ipid;
                        $insert_pc->time_interval = $time.":00";
                        $insert_pc->save();
                    }
                }     
            }
            else
            { // OLD style
                $post_dosaje[$i] = $post['dosage'][$i];
                $post_dosage_interval[$i] = isset($post[$i]['dosage_interval']) ? $post[$i]['dosage_interval'] : null;
            }

            
            
            $cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$i]);
            if($cust){
                
                // get dosage
                $dosage_array  = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid,$post['drid'][$i]);
                
                // get extra data
                $extra_data = "";
                $extra_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$post['drid'][$i]);
                $extra_data = $extra_array[$post['drid'][$i]]; 

                // Get existing data and form a string
                $existing_string = "";
                if($cust->treatment_care != "1"){
                    $existing_string .= $cust->dosage;
                }
                
                $existing_string .= $cust->medication_master_id;
                $existing_string .= $cust->verordnetvon;
                $existing_string .= $cust->comments;
                
                if ($cust->treatment_care != "1" && $cust->scheduled !="1" ){
                    $existing_string .= $extra_data['drug'];
                    $existing_string .= $extra_data['unit_id'];
                    
                    $existing_string .= $extra_data['dosage_24h_manual'];   //ISPC-2684 Lore 12.10.2020
                    $existing_string .= $extra_data['unit_dosage'];        //ISPC-2684 Lore 05.10.2020
                    $existing_string .= $extra_data['unit_dosage_24h'];    //ISPC-2684 Lore 05.10.2020
                    
                    $existing_string .= $extra_data['type_id'];
                    $existing_string .= trim($extra_data['indication']);
                    $existing_string .= $extra_data['importance'];
                    $existing_string .= $extra_data['dosage_form'];
                    $existing_string .= $extra_data['concentration'];
                    if ($cust->isintubated == "1") {
                        $existing_string .= $extra_data['packaging'];
                        $existing_string .= $extra_data['kcal'];
                        $existing_string .= $extra_data['volume'];
                    }
                    
                }elseif($cust->scheduled == "1") {
                    
                }
                
                $existing_date = strtotime(date('d.m.Y',strtotime($cust->medication_change)));
                
                // Get posted data and form a string                    
                $post_string = "";
                if($cust->treatment_care != "1"){
                    $post_string .= $post_dosaje[$i];
                }
                
                $post_string .= $medid;
                $post_string .= $post['comments'][$i];
                $post_string .= $post['verordnetvon'][$i];
                
                if($cust->treatment_care != "1"){
                    $post_string .= $post['drug'][$i];
                    $post_string .= $post['unit'][$i];
                    
                    $post_string .= $post['dosage_24h'][$i];         //ISPC-2684 Lore 12.10.2020
                    $post_string .= $post['unit_dosage'][$i];        //ISPC-2684 Lore 05.10.2020
                    $post_string .= $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                    
                    $post_string .= $post['type'][$i];
                    $post_string .= trim($post['indication'][$i]);
                    $post_string .= $post['importance'][$i];
                    $post_string .= $post['dosage_form'][$i];
                    $post_string .= $post['concentration'][$i];
                    if ($cust->isintubated == "1") {
                        $post_string .= $post['packaging'][$i];
                        $post_string .= $post['kcal'][$i];
                        $post_string .= $post['volume'][$i];
                    }
                }
                $post_date = strtotime($post['medication_change'][$i]);
                
                if( ($existing_date != $post_date || $existing_string != $post_string ) && $post['edited'][$i] == '1' )
                { //check to update only what's modified

                    $update_medication[$i] = "1";

                    if(!empty($post['medication_change'][$i])){
                        //check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
                        if (  $existing_string != $post_string )
                        {
                            if ($post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
                                $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                            } elseif ($post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
                                $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                            } elseif ($post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
                                $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
                            } else {
                                $medication_change_date[$i] = date('Y-m-d 00:00:00');
                            }

                            // if no medication details were modified - check in the "last edit date" was edited
                        } else if(
                            ( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
                            ( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
                            ( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
                        {

                            $medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));

                        } else if(
                            ( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
                            ( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
                            ( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) )
                        )
                        {
                            $update_medication[$i] = "0";
                        }

                        // if "last edit date was edited - save current date"
                    } else {
                        $medication_change_date[$i] = date('Y-m-d 00:00:00');
                    }
                }
                else {
                    $update_medication[$i] = "0";
                }
                /* ================= Save in patient drugplan history ====================*/
                if($update_medication[$i] == "1")
                {
                    
					//TODO-2785 Lore 18.02.2020
                    if( $cust->treatment_care == 1 )
                    {
                        $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id);
                    }
                    elseif( $cust->isnutrition == 1 )
                    {
                        $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id);
                    }
                    else
                    {
                        $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
                    }                   
                    //$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                    $medication_old_medication_name[$i] = $old_med->name;
                    $medication_old_medication_id[$i] =  $old_med->id;

                    $history = new PatientDrugPlanHistory();
                    $history->ipid = $ipid;
                    $history->pd_id = $cust->id;
                    $history->pd_medication_master_id = $cust->medication_master_id ;
                    $history->pd_medication_name = $medication_old_medication_name[$i] ;
                    $history->pd_medication =  $cust->medication;
                    $history->pd_dosage = $cust->dosage;
                    $history->pd_dosage_interval = isset($cust->dosage_interval) ? $cust->dosage_interval : null;
                    $history->pd_dosage_product = isset($cust->dosage_product) ? $cust->dosage_product : null;
                    $history->pd_comments = $cust->comments ;
                    $history->pd_isbedarfs = $cust->isbedarfs;
                    $history->pd_iscrisis = $cust->iscrisis;
                    $history->pd_isivmed = $cust->isivmed;
                    $history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
                    $history->pd_cocktailid= $cust->cocktailid;
                    $history->pd_treatment_care = $cust->treatment_care;
                    $history->pd_isnutrition = $cust->isnutrition;
                    $history->pd_isintubated = $cust->isintubated; // ISPC-2176
                    $history->pd_edit_type = $cust->edit_type;
                    $history->pd_verordnetvon = $cust->verordnetvon;
                    $history->pd_medication_change = $cust->medication_change;
                    $history->pd_create_date = $cust->create_date;
                    $history->pd_create_user = $cust->create_user;
                    $history->pd_change_date = $cust->change_date;
                    $history->pd_change_user = $cust->change_user;
                    $history->pd_isdelete = $cust->isdelete;
                    $history->pd_days_interval_technical = isset($cust->days_interval_technical) ? $cust->days_interval_technical : null;
                    $history->save();
                    $history_id = $history->id;
                    
                    $dosage_history_array[$post['drid'][$i]] = PatientDrugPlanDosage::get_all_patient_drugplan_dosage($ipid,$post['drid'][$i]);
                    
                    if(is_array($post['dosage'][$i]))
                    {
                        if(!empty($dosage_history_array[$post['drid'][$i]]))
                        {
                            // add dosage to - dosage history
                            foreach($dosage_history_array[$post['drid'][$i]] as $k=>$dv)
                            {
                                $history_pd = new PatientDrugPlanDosageHistory();
                                $history_pd->ipid = $ipid;
                                $history_pd->pdd_id = $dv['id'];
                                $history_pd->history_id = $history_id;
                                $history_pd->pdd_drugplan_id = $dv['drugplan_id'];
                                $history_pd->pdd_dosage = $dv['dosage'];
                                //TODO-3624 Ancuta 23.11.2020
                                $history_pd->pdd_dosage_full = $dv['dosage_full'];
                                $history_pd->pdd_dosage_concentration = $dv['dosage_concentration'];
                                $history_pd->pdd_dosage_concentration_full = $dv['dosage_concentration_full'];
                                //-- 
                                $history_pd->pdd_dosage_time_interval =  $dv['dosage_time_interval'];
                                $history_pd->pdd_isdelete	= $dv['isdelete'];
                                $history_pd->pdd_create_user = $dv['create_user'];
                                $history_pd->pdd_create_date = $dv['create_date'];
                                $history_pd->pdd_change_user = $dv['change_user'];
                                $history_pd->pdd_change_date = $dv['change_date'];
                                $history_pd->save();
                            }
                        }
                    }
                    
                    $drugplan_extra_array = PatientDrugPlanExtra::get_patient_all_drugplan_extra($ipid,$post['drid'][$i]);
                    
                    if(!empty($drugplan_extra_array[$post['drid'][$i]]))
                    {
                        $drugplan_extra_data = $drugplan_extra_array[$post['drid'][$i]];
                    
                        $history_pde = new PatientDrugPlanExtraHistory();
                        $history_pde->ipid = $ipid;
                        $history_pde->pde_id = $drugplan_extra_data['id'];
                        $history_pde->history_id = $history_id;
//                         $history_pde->pde_drugplan_id = $dv['drugplan_id'];;
                        $history_pde->pde_drugplan_id = $post['drid'][$i];
                        $history_pde->pde_drug = $drugplan_extra_data['drug'];
                        $history_pde->pde_unit = $drugplan_extra_data['unit'];
                        
                        $history_pde->pde_dosage_24h_manual = $drugplan_extra_data['dosage_24h_manual'];    //ISPC-2684 Lore 12.10.2020
                        $history_pde->pde_unit_dosage = $drugplan_extra_data['unit_dosage'];            //ISPC-2684 Lore 05.10.2020
                        $history_pde->pde_unit_dosage_24h = $drugplan_extra_data['unit_dosage_24h'];    //ISPC-2684 Lore 05.10.2020
                        
                        $history_pde->pde_type = $drugplan_extra_data['type'];
                        $history_pde->pde_indication = trim($drugplan_extra_data['indication']);
                        $history_pde->pde_importance = $drugplan_extra_data['importance'];
                        
                        $history_pde->pde_dosage_form = $drugplan_extra_data['dosage_form'];
                        $history_pde->pde_concentration = $drugplan_extra_data['concentration'];
                        
                        //ISPC-2176
                        $history_pde->pde_packaging = $drugplan_extra_data['packaging'];
                        $history_pde->pde_kcal = $drugplan_extra_data['kcal'];
                        $history_pde->pde_volume = $drugplan_extra_data['volume'];
                        //--                        
                        
                        $history_pde->pde_isdelete	= $drugplan_extra_data['isdelete'];
                        
                        $history_pde->pde_create_user = $drugplan_extra_data['create_user'];
                        $history_pde->pde_create_date = $drugplan_extra_data['create_date'];
                        $history_pde->pde_change_user = $drugplan_extra_data['change_user'];
                        $history_pde->pde_change_date = $drugplan_extra_data['change_date'];
                        $history_pde->save();
                    }
                }


                /* ================= Update patient drugplan item ====================*/
                if($update_medication[$i] == "1")
                {
                    $cust->dosage = $post_dosaje[$i];
                    $cust->dosage_interval = isset($post_dosage_interval[$i]) ? $post_dosage_interval[$i] : null;
                    $cust->ipid = $ipid;
                    $cust->medication_master_id = $medid;
                    $cust->verordnetvon = $post['verordnetvon'][$i];
                    $cust->comments = $post['comments'][$i];
                    $cust->medication_change = $medication_change_date[$i];
                    $cust->save();
                    
                    
                    if(is_array($post['dosage'][$i]))
                    { // NEW style
                        if(!empty($dosage_array)){
                            // clear dosage
                            $clear_dosage = $this->clear_dosage($ipid, $post['drid'][$i]);
                        }
                        foreach ($post['dosage'][$i] as $time => $dosage_value)
                        {
                            //  insert new lines
                            $cust_pdd = new PatientDrugPlanDosage();
                            $cust_pdd->ipid = $ipid;
                            $cust_pdd->drugplan_id = $post['drid'][$i];
                            $cust_pdd->dosage = $dosage_value;
                            //TODO-3624 Ancuta 23.11.2020
                            $cust_pdd->dosage_full = $post['dosage_full'][$i][$time];
                            $cust_pdd->dosage_concentration = $post['dosage_concentration'][$i][$time];
                            $cust_pdd->dosage_concentration_full = $post['dosage_concentration_full'][$i][$time];
                            //--
                            $cust_pdd->dosage_time_interval = $time.":00";
                            $cust_pdd->save();
                        }
                    }
                    
                    
                    // update extra data
                    if(!empty($extra_array))
                    {
                        $drugs = Doctrine_Query::create()
                        ->select('id')
                        ->from('PatientDrugPlanExtra')
                        ->where("ipid = '" . $ipid . "'")
                        ->andWhere("drugplan_id = '" . $post['drid'][$i] . "'")
                        ->andWhere("isdelete = '0'")
                        ->orderBy("create_date DESC")
                        ->limit(1);
                        $drugs_array = $drugs->fetchArray();
                        
                        if(!empty($drugs_array)){
                            $existing_extra_id =$drugs_array[0]['id'];
                            
                            $update_pde = Doctrine::getTable('PatientDrugPlanExtra')->find($existing_extra_id);
                            $update_pde->drug = $post['drug'][$i];
                            $update_pde->unit = $post['unit'][$i];
                            
                            $update_pde->dosage_24h_manual = $post['dosage_24h'][$i];       //ISPC-2684 Lore 12.10.2020
                            $update_pde->unit_dosage = $post['unit_dosage'][$i];            //ISPC-2684 Lore 05.10.2020
                            $update_pde->unit_dosage_24h = $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                            
                            $update_pde->type = $post['type'][$i];
                            $update_pde->indication = trim($post['indication'][$i]);
                            $update_pde->importance = $post['importance'][$i];
                            $update_pde->dosage_form = $post['dosage_form'][$i];
                            $update_pde->concentration = $post['concentration'][$i];
                            //ISPC-2176
                            $update_pde->packaging = $post['packaging'][$i];
                            $update_pde->kcal = $post['kcal'][$i];
                            $update_pde->volume= $post['volume'][$i];
                            //--
                            $update_pde->save();
                        }

                    } 
                    else
                    {
                        // add extra data
                        $cust_pde = new PatientDrugPlanExtra();
                        $cust_pde->ipid = $ipid;
                        $cust_pde->drugplan_id = $post['drid'][$i];
                        $cust_pde->drug = $post['drug'][$i];
                        $cust_pde->unit = $post['unit'][$i];
                        
                        $cust_pde->dosage_24h_manual = $post['dosage_24h'][$i];            //ISPC-2684 Lore 12.10.2020
                        $cust_pde->unit_dosage = $post['unit_dosage'][$i];            //ISPC-2684 Lore 05.10.2020
                        $cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$i];    //ISPC-2684 Lore 05.10.2020
                        
                        $cust_pde->type = $post['type'][$i];
                        $cust_pde->indication = trim($post['indication'][$i]);
                        $cust_pde->importance = $post['importance'][$i];
                        $cust_pde->dosage_form = $post['dosage_form'][$i];
                        $cust_pde->concentration = $post['concentration'][$i];
                        //ISPC-2176
                        $cust_pde->packaging = $post['packaging'][$i];
                        $cust_pde->kcal = $post['kcal'][$i];
                        $cust_pde->volume= $post['volume'][$i];
                        //--
                        $cust_pde->save();
                    }
                    	
                    $clear = $this->update_pdpa($ipid, $post['drid'][$i]);
                    	
                    if( $cust->isivmed == 0 &&  $cust->isbedarfs == 1 &&  $cust->isschmerzpumpe == 0 &&  $cust->treatment_care == 0 )
                    {
                        $shortcut = "N";
                    }
                    elseif($cust->isivmed  == 1 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
                    {
                        $shortcut = "I";
                    }
                    elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 1 &&  $cust->treatment_care== 0 )
                    {
                        $shortcut = "Q";
                        $prefix = "Schmerzpumpe ";
                    }
                    elseif($cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 1 )
                    {
                        $shortcut = "BP";
                    }
                    elseif($cust->iscrisis  == 1 &&  $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
                    {
                    	$shortcut = "KM";
                    }
                    elseif($cust->isintubated  == 1 && $cust->iscrisis  == 0 &&  $cust->isivmed  == 0 &&  $cust->isbedarfs == 0 &&  $cust->isschmerzpumpe  == 0 && $cust->treatment_care == 0 )
                    {
                    	$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
                    }
                    
                    else
                    {
                        $shortcut = "M";
                    }
                     
                    // OLD ENTRY
                    // old medication name
 					//TODO-2785 Lore 18.02.2020
                   
                    if( $cust->treatment_care == 1 )
                    {
                        $old_med = Doctrine::getTable('MedicationTreatmentCare')->find($cust->medication_master_id);
                    }
                    elseif( $cust->isnutrition == 1 )
                    {
                        $old_med = Doctrine::getTable('Nutrition')->find($cust->medication_master_id);
                    }
                    else
                    {
                        $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
                    }
                    //$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                    $old_med_name[$i] = $old_med->name;
                     
                    // old dosage
                    if($cust->dosage) {
                        $old_med_dosage[$i] = $cust->dosage;
                    }
                     
                    // old comment
                    if($cust->comments ){
                        $old_med_comments[$i] = $cust->comments." | ";
                    }

                    //  old medication date
                    if($cust->medication_change != "0000-00-00 00:00:00")
                    {
                        $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->medication_change));
                    }
                    else
                    {
                        if($cust->change_date != "0000-00-00 00:00:00")
                        {
                            $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->change_date));
                        }
                        else
                        {
                            $old_med_medication_change[$i] =  date('d.m.Y',strtotime($cust->create_date));
                        }
                    }
                     
                    if(strlen($old_med_dosage[$i])>0){
                        $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_dosage[$i]." | ".$old_med_comments[$i].$old_med_medication_change[$i];
                    } else	{
                        $old_entry[$i] = $prefix.$old_med_name[$i].' | '.$old_med_comments[$i].$old_med_medication_change[$i];
                    }
                     
                    // NEW ENTRY
                    // new name
                    if( $post['treatment_care'] == 1 )
                    {
                        $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
                    }
                    elseif( $post['isnutrition'] == 1 )
                    {
                        $new_med = Doctrine::getTable('Nutrition')->find($medid);
                    }
                    else
                    {
                        $new_med = Doctrine::getTable('Medication')->find($medid);
                    }
                     
                    $new_medication_name[$i] = $new_med->name;
                     
                    // new dosage
                    $new_medication_dosage[$i] = $post['dosage'][$i];
                     
                    // new comments
                    $new_medication_comments[$i] = $post['comments'][$i];
                     
                    // new change date
                    if($medication_change_date[$i] != "0000-00-00 00:00:00"){
                        $medication_change_date_str[$i] = date("d.m.Y", strtotime($medication_change_date[$i]));
                    }
                    else
                    {
                        $medication_change_date_str[$i]="";
                    }
                     
                    if(strlen($new_medication_dosage[$i])>0)
                    {
                        $new_entry[$i] = $prefix.$new_medication_name[$i]."  |  ".$new_medication_dosage[$i]." | ". $new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                    }
                    else
                    {
                        $new_entry[$i] = $prefix.$new_medication_name[$i]." | ".$new_medication_comments[$i]." | ".$medication_change_date_str[$i];
                    }
                     
//                     $attach = 'Änderung: ' . $old_entry[$i] . '  -> ' .  $new_entry[$i].'';
//                     $insert_pc = new PatientCourse();
//                     $insert_pc->ipid = $ipid;
//                     $insert_pc->course_date = date("Y-m-d H:i:s", time());
//                     $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
//                     $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_edit_med");
//                     $insert_pc->recordid = $post['drid'][$i];
//                     $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
//                     $insert_pc->user_id = $userid;
//                     $insert_pc->save();
                }
            }
        }
    }


    public function update_schmerzpumpe_data($post)
    {
    	//ISPC - 2329 punctul o - saved dosage depends on dosage_24h 
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
    
        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
        $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
    
        //ISPC-2554 pct.3 Carmen 27.03.2020
        $atcdet = PatientDrugPlanAtcTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
        $atcindex = 0;
        $toupdate = array();
        $todelete = array();
        //--
        $modules = new Modules();
        if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
        {
            $acknowledge = "1";
            if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA')
            {
    
                $allow_change = "1";
            }
            else
            {
                $allow_change = "0";
            }
        }
        else
        {
            $acknowledge = "0";
        }
    
    
        if($acknowledge == "1" && !in_array($userid,$approval_users))//Medication acknowledge
        {
            if($allow_change == "1")
            {
                // get user details
                $master_user_details = new User();
                $users_details_arr = $master_user_details->getUserDetails($userid);
                $users_details = $users_details_arr[0];
                $user_name = $users_details['first_name'].' '.$users_details['last_name'];
    
                // get patient details
                $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
                $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
    
                if ($post['isschmerzpumpe'] == 1 && empty($post['cocktail']['id']))
                {
                    //insert cocktail procedure
                    $mc = new PatientDrugPlanCocktails();
                    $mc->userid = $userid;
                    $mc->clientid = $clientid;
                    $mc->ipid = $ipid;
                    $mc->description = $post['cocktail']['description'];
                    $mc->bolus = $post['cocktail']['bolus'];
                    $mc->max_bolus = $post['cocktail']['max_bolus'];
                    $mc->flussrate = $post['cocktail']['flussrate'];
                    if(isset( $post['cocktail']['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
                        $mc->flussrate_type = $post['cocktail']['flussrate_type'];          //ISPC-2684 Lore 02.10.2020
                    }
                    
                    $mc->sperrzeit = $post['cocktail']['sperrzeit'];
                    $mc->pumpe_type = $post['cocktail']['pumpe_type'];
                    $mc->pumpe_medication_type = $post['cocktail']['pumpe_medication_type'];
                    $mc->carrier_solution = $post['cocktail']['carrier_solution'];
                    
                    if(!empty($post['cocktail']['source_ipid'])){
                        $mc->source_cocktailid = $post['cocktail']['source_cocktailid'];
                        $mc->source_ipid = $post['cocktail']['source_ipid'];
                    }
                    $mc->save();
                    
                    //get cocktail id
                    $cocktail_id = $mc->id;
                     
                    // insert in cocktail alt
                    $inser_calt =  new PatientDrugPlanAltCocktails();
                    $inser_calt->ipid = $ipid;
                    $inser_calt->userid = $userid;
                    $inser_calt->clientid = $clientid;
                    $inser_calt->drugplan_cocktailid = $cocktail_id;
                    $inser_calt->description = $post['cocktail']['description'];
                    $inser_calt->bolus = $post['cocktail']['bolus'];
                    $inser_calt->max_bolus = $post['cocktail']['max_bolus'];
                    $inser_calt->flussrate = $post['cocktail']['flussrate'];
                    if(isset($post['cocktail']['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
                        $inser_calt->flussrate_type = $post['cocktail']['flussrate_type'];          //ISPC-2684 Lore 02.10.2020
                    }
                    $inser_calt->sperrzeit = $post['cocktail']['sperrzeit'];
                    $inser_calt->pumpe_type = $post['cocktail']['pumpe_type'];
                    $inser_calt->pumpe_medication_type = $post['cocktail']['pumpe_medication_type'];
                    $inser_calt->carrier_solution = $post['cocktail']['carrier_solution'];
                    $inser_calt->status = "new";
                    $inser_calt->save();
                    
                    $recordid_cocktail_alt = $inser_calt->id;

                    $new_entry = "Kommentar: " . $post['cocktail']['description']."";
                    $new_entry .= "\nApplikationsweg: " .$post['cocktail']['pumpe_medication_type'];
                    //$new_entry .= "\nFlussrate: " . $post['cocktail']['flussrate'];
                    //ISPC-2684 Lore 08.10.2020
                    if(isset($post['cocktail']['flussrate_type']) && !empty($post['cocktail']['flussrate_type'])){
                        $new_entry .= "\nFlussrate "." (".trim($post['cocktail']['flussrate_type'])."): " . $post['cocktail']['flussrate'];
                    }else{
                        $new_entry .= "\nFlussrate: " . $post['cocktail']['flussrate'];
                    }
                    //.
                    $new_entry .= "\nTrägerlösung: " .$post['cocktail']['carrier_solution'];
                    
                    if($post['cocktail']['pumpe_type'] == "pca")
                    {
                        $new_entry .= "\nBolus: " .$post['cocktail']['bolus'];
                        $new_entry .= "\nMax Bolus: " .$post['cocktail']['max_bolus'];
                        $new_entry .= "\nSperrzeit: " .$post['cocktail']['sperrzeit'];
                    }
                    
                    $attach = "OHNE FREIGABE: " .  $new_entry."";
                    $insert_pc = new PatientCourse();
                    $insert_pc->ipid = $ipid;
                    $insert_pc->course_date = date("Y-m-d H:i:s", time());
                    $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
                    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
                    $insert_pc->recordid = $recordid_cocktail_alt;
                    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                    $insert_pc->user_id = $userid;
                    $insert_pc->save();
                    
                    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                    // SEND MESSAGE
                    $text  = "";
                    $text .= "Patient ".$patient_name." \n ";
                    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                    $text .=  "neue Medikation:  " .  $new_entry." \n ";
                    
                    $mess = Messages::medication_acknowledge_messages($ipid, $text);
                    
                    // CREATE TODO
                    
                    $text_todo  = "";
                    $text_todo .= "Patient ".$patient_name." <br/>";
                    $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
                    $text_todo .=  "neue Medikation:  " . str_replace("\n","<br/>",$new_entry)."  <br/>";
                    
                    $todos = Messages::pump_medication_acknowledge_todo($ipid, $text_todo, $cocktail_id, $recordid_cocktail_alt);
                     
                
                    foreach ($post['hidd_medication'] as $key => $val)
                    {
                        if ($post['hidd_medication'][$key] > 0)
                        {
                            $medid = $post['hidd_medication'][$key];
                        }
                        else
                        {
                            $medid = $post['newhidd_medication'][$key];
                        }
                
               
                        if ($medid > 0)
                        {
                        	//ISPC-2554 Carmen pct.3 27.03.2020
                        	$medmasterid = $medid;
                        	//--
                        	
            				$cust = new PatientDrugPlan();
            				$cust->ipid = $ipid;
            				
            				//ISPC-2684 Lore 12.10.2020
            				$modules = new Modules();
            				if($modules->checkModulePrivileges("240", $clientid)){
            				    $cust->dosage  = $post['dosage'][$key];
            				}else {
            				    if($post['dosage_24h'][$key])
            				    {
            				        //TODO-2592 Ancuta 10.10.2019
            				        $cust->dosage  = str_replace(',', '.' , $post['dosage_24h'][$key])/24;
            				    }
            				    else
            				    {
            				        $cust->dosage  = $post['dosage'][$key];
            				    }
            				}

            				            				
           				    $cust->dosage_interval  = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
            				$cust->medication_master_id = $medid;
            				$cust->isbedarfs = "0";
            				//$cust->iscrisis = "0"; // ispc 1823
            				$cust->isivmed = "0";
            				if ($post['isschmerzpumpe'] == 1)
            				{
            				    $cust->isschmerzpumpe = 1;
            				    $cust->cocktailid = $cocktail_id;
            				}
            				$cust->treatment_care = "0";
            				$cust->isnutrition = "0";
    
            				$cust->verordnetvon = $post['verordnetvon'][$key];
            				$cust->comments = $post['comments'][$key];
            				if($post['done_date'])
            				{
            				    $cust->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
            				}
            				else
            				{
            				    $cust->medication_change = date('Y-m-d 00:00:00',time());
            				}
            				$cust->save();
            				$inserted_id = $cust->id;
            				$insertedIds[] = $cust->id;
            				//ISPC-2554 Carmen pct.3 27.03.2020
            				$drugplanid = $inserted_id;
            				//--
    
            				// Insert extra data
            				$insert_pde = new PatientDrugPlanExtra();
            				$insert_pde->ipid = $ipid;
            				$insert_pde->drugplan_id = $inserted_id;
            				$insert_pde->drug = $post['drug'][$key];
            				$insert_pde->unit = $post['unit'][$key];
            				
            				$insert_pde->dosage_24h_manual = $post['dosage_24h'][$i];            //ISPC-2684 Lore 12.10.2020
            				$insert_pde->unit_dosage = $post['unit_dosage'][$key];            //ISPC-2684 Lore 05.10.2020
            				$insert_pde->unit_dosage_24h = $post['unit_dosage_24h'][$key];    //ISPC-2684 Lore 05.10.2020
            				
            				$insert_pde->type = $post['type'][$key];
            				$insert_pde->indication = trim($post['indication'][$key]);
            				$insert_pde->importance = $post['importance'][$key];
            				$insert_pde->dosage_form = $post['dosage_form'][$key];
            				$insert_pde->concentration  = $post['concentration'][$key];
            				//ISPC-2176
            				$insert_pde->packaging = $post['packaging'][$key];
            				$insert_pde->kcal= $post['kcal'][$key];
            				$insert_pde->volume = $post['volume'][$key];
            				//--
            				
            				$insert_pde->save();
                
                            // insert in alt
            				$insert_at = new PatientDrugPlanAlt();
            				$insert_at->ipid = $ipid;
            				$insert_at->drugplan_id = $inserted_id;
            				//ISPC-2684 Lore 12.10.2020
            				$modules = new Modules();
            				if($modules->checkModulePrivileges("240", $clientid)){
            				    $insert_at->dosage = $post['dosage'][$key];
            				}else {
            				    if($post['dosage_24h'][$key])
            				    {
            				        //TODO-2592 Ancuta 10.10.2019
            				        $insert_at->dosage  = str_replace(',', '.' , $post['dosage_24h'][$key])/24;
            				    }
            				    else
            				    {
            				        $insert_at->dosage = $post['dosage'][$key];
            				    }
            				}

            				$insert_at->dosage_interval = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
            				$insert_at->medication_master_id = $medid;
            				$insert_at->isbedarfs = "0";
            				//$insert_at->iscrisis = "0"; //ispc 1823 ??
            				$insert_at->isivmed = "0";
            				if ($cust->isschmerzpumpe == 1)
            				{
            				    $insert_at->isschmerzpumpe = $cust->isschmerzpumpe;
            				    $insert_at->cocktailid = $cust->cocktailid;
            				}
            				
            				$insert_at->treatment_care = "0";
            				$insert_at->isnutrition = "0";
            				$insert_at->verordnetvon = $post['verordnetvon'][$key];
            				$insert_at->comments = $post['comments'][$key];
            				$insert_at->medication_change = date('Y-m-d 00:00:00',time());;
            				$insert_at->status = "new";
            				$insert_at->save();
            				$insertedIds[] = $insert_at->id;
            				
            				$recordid = $insert_at->id;
            				
            				// add extra data
            				$cust_pde = new PatientDrugPlanExtraAlt();
            				$cust_pde->ipid = $ipid;
            				$cust_pde->drugplan_id_alt = $recordid;
            				$cust_pde->drugplan_id = $inserted_id;
            				$cust_pde->drug = $post['drug'][$key];
            				$cust_pde->unit = $post['unit'][$key];
            				
            				$cust_pde->dosage_24h_manual = $post['dosage_24h'][$key];            //ISPC-2684 Lore 12.10.2020
            				$cust_pde->unit_dosage = $post['unit_dosage'][$key];            //ISPC-2684 Lore 05.10.2020
            				$cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$key];    //ISPC-2684 Lore 05.10.2020
            				
            				$cust_pde->type = $post['type'][$key];
            				$cust_pde->indication = trim($post['indication'][$key]);
            				$cust_pde->importance = $post['importance'][$key];
            				$cust_pde->dosage_form = $post['dosage_form'][$key];
            				$cust_pde->concentration= $post['concentration'][$key];
            				//ISPC-2176
            				$cust_pde->packaging = $post['packaging'][$key];
            				$cust_pde->kcal= $post['kcal'][$key];
            				$cust_pde->volume = $post['volume'][$key];
            				//--
            				$cust_pde->save();
            				
            				
                        	// this is for  Medication acknowledge
            				if(!in_array($userid,$approval_users)  && $acknowledge == "1" ){
            				    // NEW ENTRY
            				    if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
            				    {
            				        $shortcut = "N";
            				    }
            				    elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
            				    {
            				        $shortcut = "I";
            				    }
            				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 )
            				    {
            				        $shortcut = "Q";
            				        $prefix = "Schmerzpumpe ";
            				    }
            				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1 )
            				    {
            				        $shortcut = "BP";
            				    }
            				    else
            				    {
            				        $shortcut = "M";
            				    }
            				    // new name
            				    if( $post['treatment_care'] == 1 )
            				    {
            				        $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
            				    }
            				    elseif( $post['isnutrition'] == 1 )
            				    {
            				        $new_med = Doctrine::getTable('Nutrition')->find($medid);
            				    }
            				    else
            				    {
            				        $new_med = Doctrine::getTable('Medication')->find($medid);
            				    }
    
            				    $new_medication_name[$key] = $new_med->name;
    
            				    // new dosage
            				    $new_medication_dosage[$key] = $post['dosage'][$key];
    
            				    // new comments
            				    $new_medication_comments[$key] = $post['comments'][$key];
    
            				    // new change date
            				    $medication_change_date_str[$key]= date("d.m.Y",time());
    
            				    $new_entry = "";
            				    if(strlen($new_medication_dosage[$key])>0)
            				    {
            				        $new_entry = $prefix.$new_medication_name[$key]."  |  ".$new_medication_dosage[$key]." | ". $new_medication_comments[$key]." | ".$medication_change_date_str[$key];
            				    }
            				    else
            				    {
            				        $new_entry = $prefix.$new_medication_name[$key]." | ".$new_medication_comments[$key]." | ".$medication_change_date_str[$key];
            				    }
    
//             				    $attach = $new_entry[$key].'';
//             				    $insert_pc = new PatientCourse();
//             				    $insert_pc->ipid = $ipid;
//             				    $insert_pc->course_date = date("Y-m-d H:i:s", time());
//             				    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
//             				    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
//             				    $insert_pc->recordid = $inserted_id;
//             				    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
//             				    $insert_pc->user_id = $userid;
//             				    $insert_pc->save();
            				    
            				    

            				    $attach = 'OHNE FREIGABE: ' .  $new_entry.'';
            				    $insert_pc = new PatientCourse();
            				    $insert_pc->ipid = $ipid;
            				    $insert_pc->course_date = date("Y-m-d H:i:s", time());
            				    $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
            				    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
            				    $insert_pc->recordid = $recordid;
            				    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
            				    $insert_pc->user_id = $userid;
            				    $insert_pc->save();
 
            				    
            				    
            				    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            				    // SEND MESSAGE
            				    $text  = "";
            				    $text .= "Patient ".$patient_name." \n ";
            				    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
            				    $text .=  "neue Medikation:  " .  $new_entry." \n ";
            				    
            				    $mess = Messages::medication_acknowledge_messages($ipid, $text);
            				    
            				    // CREATE TODO
            				    $text_todo  = "";
            				    $text_todo .= "Patient ".$patient_name." <br/>";
            				    $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
            				    $text_todo .=  "neue Medikation:  " .  $new_entry ."  <br/>";
            				    
            				    $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $inserted_id, $recordid);
            				    
            				}
                        }
                        
                        //ISPC-2554 pct.3 Carmen 27.03.2020
                        $atcarr = (array)json_decode(html_entity_decode($post[$key]['atc']));
                       
                        $atcid = array_search($drugplanid, array_column($atcdet, 'drugplan_id'));
                        
                        if($atcid !== false)
                        {
                        	if($atcdet[$atcid]['atc_code'] != $atcarr['atc_code'])
                        	{
                        		$todelete[] = $atcdet[$atcid]['id'];
                        		 
                        		$toupdate[$atcindex]['ipid'] = $ipid;
                        		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                        		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                        		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                        		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                        		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                        		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                        		$atcindex++;
                        	}
                        }
                        else
                        {
                        	if(!empty($atcarr))
                        	{
                        		$toupdate[$atcindex]['ipid'] = $ipid;
                        		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                        		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                        		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                        		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                        		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                        		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                        		$atcindex++;
                        	}
                        }
                        //--
                    }
                    
                }
                else
                {
                    // UPDATE MEDICATION  - ALONG WITH COCKTAIL DETAILS
                    foreach ($post['hidd_medication'] as $keym => $valm)
                    {
                        $update_sh_medication[$keym] = "0";
        
                        if ($post['hidd_medication'][$keym] > 0)
                        {
                            $medid = $post['hidd_medication'][$keym];
                        }
                        else
                        {
                            $medid = $post['newhidd_medication'][$keym];
                        }
        
                        //ISPC-2554 Carmen pct.3 27.03.2020
                        $medmasterid = $medid;
                        //--
                        
                        if ($post['drid'][$keym] > 0)
                        {
                            $cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$keym]);
                            if ($cust){
                            	//ISPC-2554 Carmen pct.3 27.03.2020
                            	$drugplanid = $cust->id;
                            	//--
								//TODO-3652 Ancuta 03.12.2020
                            	$new_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
                            	$existing_medication_name  = $new_med->name;
                            	//--
                                // get exra data
                                $extra_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$post['drid'][$keym]);
                                $extra_data = $extra_array[$post['drid'][$keym]];
                                
                                $existing_string = "";
                                $existing_string .= trim($existing_medication_name);//TODO-3652 Ancuta 03.12.2020
                                $existing_string .= trim($cust->dosage);
                                $existing_string .= trim($cust->medication_master_id);
                                $existing_string .= trim($cust->verordnetvon);
                                $existing_string .= trim($extra_data['drug']);
                                
                                $existing_string .= trim($extra_data['dosage_24h_manual']);   //ISPC-2684 Lore 12.10.2020
                                $existing_string .= trim($extra_data['unit_dosage']);           //ISPC-2684 Lore 05.10.2020
                                $existing_string .= trim($extra_data['unit_dosage_24h']);       //ISPC-2684 Lore 05.10.2020
                                
                                $existing_string .= trim($extra_data['unit_id']);
                                $existing_string .= trim($extra_data['dosage_form_id']);
                                $existing_string .= trim($extra_data['concentration']);
                                $existing_string .= trim($extra_data['indication_id']);
                                $existing_date = strtotime(date('d.m.Y',strtotime($cust->medication_change)));
                                
//                                 print_r("\n"); 
//                                 print_r($existing_string); 
//                                 print_r("\n"); 
                                
                                
                                $post_string = "";
                                $post_string .= trim($post['medication'][$keym]);//TODO-3652 Ancuta 03.12.2020
                                $post_string .= trim($post['dosage'][$keym]);
                                $post_string .= trim($medid);
                                
                                if(strlen($post['verordnetvon'][$keym]) == 0){
                                    $post_string .= 0;
                                } else{
                                    $post_string .= trim($post['verordnetvon'][$keym]);
                                }
                                $post_string .= trim($post['drug'][$keym]);
                                $post_string .= trim($post['unit'][$keym]);
                                
                                $post_string .= trim($post['dosage_24h'][$keym]);  //ISPC-2684 Lore 12.10.2020
                                $post_string .= trim($post['unit_dosage'][$keym]);           //ISPC-2684 Lore 05.10.2020
                                $post_string .= trim($post['unit_dosage_24h'][$keym]);       //ISPC-2684 Lore 05.10.2020
                                
                                $post_string .= trim($post['dosage_form'][$keym]);
                                $post_string .= trim($post['concentration'][$keym]);
                                $post_string .= trim($post['indication'][$keym]);
                                $post_date = strtotime($post['medication_change'][$keym]);
                                
                            if( ($existing_string != $post_string || $existing_date != $post_date)  && $post['edited'][$keym] == "1")
                            {//check to update only what's modified
                                
//                                 if (strtotime( date('d.m.Y',strtotime($cust->medication_change))) != strtotime($post['medication_change'][$keym]) ||
//                                     $cust->dosage != $post['dosage'][$keym] ||
//                                     $cust->medication_master_id != $medid ||
//                                     $cust->verordnetvon != $post['verordnetvon'][$keym])
        
                                    $update_sh_medication[$keym] = "1";
        
                                    if(!empty($post['medication_change'][$keym])){
                                        //check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
										//TODO-3652 Ancuta 03.12.2020 - added check by med name
                                        if (
                                            trim($existing_medication_name) != trim($post['medication'][$keym]) ||
                                            $cust->dosage != $post['dosage'][$keym] ||
                                            $cust->medication_master_id != $medid ||
                                            $cust->verordnetvon != $post['verordnetvon'][$keym])
                                        {
        
                                            if ($post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
                                                $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
                                            } elseif ($post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
                                                $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
                                            } elseif ($post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
                                                $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
                                            } else {
                                                $medication_change_date[$keym] = date('Y-m-d 00:00:00');
                                            }
        
                                            // if no medication details were modified - check in the "last edit date" was edited
                                        } else if(
                                            ( $post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
                                            ( $post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
                                            ( $post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->create_date))))) )
                                        {
        
                                            $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
        
                                        } else if(
                                            ( $post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
                                            ( $post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
                                            ( $post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
                                        {
        
                                            $update_sh_medication[$keym] = "0";
                                        }
        
                                        // if "last edit date was edited - save current date"
                                    } else {
                                        $medication_change_date[$keym] = date('Y-m-d 00:00:00');
                                    }
                                } else{
                                    $update_sh_medication[$keym] = "0";
                                }
        
                                // ================= Update patient drugplan item====================
        
                                if($update_sh_medication[$keym] == "1"){
                                    
                                    $this->update_pdpa($ipid,$post['drid'][$keym]);
                                    
                                    $insert_at = new PatientDrugPlanAlt();
                                    $insert_at->ipid = $ipid;
                                    $insert_at->drugplan_id = $post['drid'][$keym];
                                    //ISPC-2684 Lore 12.10.2020
                                    $modules = new Modules();
                                    if($modules->checkModulePrivileges("240", $clientid)){
                                        $insert_at->dosage = $post['dosage'][$keym];
                                    }else {
                                        if($post['dosage_24h'][$keym])
                                        {
                                            $insert_at->dosage = str_replace(',', '.' , $post['dosage_24h'][$keym])/24;
                                        }
                                        else
                                        {
                                            $insert_at->dosage = $post['dosage'][$keym];
                                        }
                                    }

                                    $insert_at->dosage_interval = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
                                    $insert_at->medication_master_id = $medid;
                                    $insert_at->medication = $post['medication'][$keym]; //TODO-3652 Ancuta 03.12.2020
                                    $insert_at->isbedarfs = "0";
                                    //$insert_at->iscrisis = "0"; // ispc 1823 ??
                                    $insert_at->isivmed = "0";
                                    if ($cust->isschmerzpumpe == 1)
                                    {
                                        $insert_at->isschmerzpumpe = $cust->isschmerzpumpe;
                                        $insert_at->cocktailid = $cust->cocktailid;
                                    }
        
                                    $insert_at->treatment_care = "0";
                                    $insert_at->isnutrition = "0";
                                    $insert_at->verordnetvon = $post['verordnetvon'][$keym];
                                    $insert_at->comments = $post['comments'][$keym];
                                    $insert_at->medication_change = $medication_change_date[$keym];
                                    $insert_at->status = "edit";
        
                                    $insert_at->save();
                                    $insertedIds[] = $insert_at->id;
        
                                    $recordid = $insert_at->id;
        
                                    // add extra data
                                    $cust_pde = new PatientDrugPlanExtraAlt();
                                    $cust_pde->ipid = $ipid;
                                    $cust_pde->drugplan_id_alt = $recordid;
                                    $cust_pde->drugplan_id = $post['drid'][$keym];
                                    $cust_pde->drug = $post['drug'][$keym];
                                    
                                    $cust_pde->dosage_24h_manual = $post['dosage_24h'][$keym];            //ISPC-2684 Lore 12.10.2020
                                    $cust_pde->unit_dosage = $post['unit_dosage'][$keym];            //ISPC-2684 Lore 05.10.2020
                                    $cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$keym];    //ISPC-2684 Lore 05.10.2020
                                    
                                    $cust_pde->unit = $post['unit'][$keym];
                                    $cust_pde->type = $post['type'][$keym];
                                    $cust_pde->indication = trim($post['indication'][$keym]);
                                    $cust_pde->importance = $post['importance'][$keym];
                                    $cust_pde->dosage_form = $post['dosage_form'][$keym];
                                    $cust_pde->concentration= $post['concentration'][$keym];
                                    $cust_pde->save();
                                    
                                    
                                    // OLD ENTRY
                                    // old medication name
                                    $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                                    $old_med_name[$keym] = $old_med->name;
        
                                    // old dosage
                                    if($cust->dosage) {
                                        $old_med_dosage[$keym] = $cust->dosage;
                                    }
        
                                    // old comment
                                    if($cust->comments ){
                                        $old_med_comments[$keym] = $cust->comments." | ";
                                    }
                                     
                                    //  old medication date
                                    if($cust->medication_change != "0000-00-00 00:00:00")
                                    {
                                        $old_med_medication_change[$keym] =  date('d.m.Y',strtotime($cust->medication_change));
                                    }
                                    else
                                    {
                                        if($cust->change_date != "0000-00-00 00:00:00")
                                        {
                                            $old_med_medication_change[$keym] =  date('d.m.Y',strtotime($cust->change_date));
                                        }
                                        else
                                        {
                                            $old_med_medication_change[$keym] =  date('d.m.Y',strtotime($cust->create_date));
                                        }
                                    }
        
                                    if(strlen($old_med_dosage[$keym])>0){
                                        $old_entry[$keym] = 'Schmerzpumpe '.$old_med_name[$keym].'|'.$old_med_dosage[$keym].' |'.$old_med_comments[$keym].$old_med_medication_change[$keym];
                                    } else	{
                                        $old_entry[$keym] = 'Schmerzpumpe '.$old_med_name[$keym].'|'.$old_med_comments[$keym].$old_med_medication_change[$keym];
                                    }
                                    
                                    // NEW ENTRY
                                    // new name
                                    $new_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                                    $new_medication_name[$keym] = $new_med->name;
        
                                    // new dosage
                                    $new_medication_dosage[$keym] = $post['dosage'][$keym];
                                    
                                    // new comments
                                    $new_medication_comments[$keym] = $post['comments'][$keym];
        
                                    // new change date
                                    if($medication_change_date[$keym] != "0000-00-00 00:00:00"){
                                        $medication_change_date_str[$keym] = date("d.m.Y", strtotime($medication_change_date[$keym]));
                                    }
                                    else
                                    {
                                        $medication_change_date_str[$keym]="";
                                    }
        
                                    if(strlen($new_medication_dosage[$keym])>0)
                                    {
                                        $new_entry[$keym] = 'Schmerzpumpe '.$new_medication_name[$keym]."  |  ".$new_medication_dosage[$keym]." | ". $new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
                                    }
                                    else
                                    {
                                        $new_entry[$keym] = 'Schmerzpumpe '.$new_medication_name[$keym]." | ".$new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
                                    }
        
                                    $attach = 'OHNE FREIGABE: ' . $old_entry[$keym] . '  -> ' .  $new_entry[$keym].'';
                                    $insert_pc = new PatientCourse();
                                    $insert_pc->ipid = $ipid;
                                    $insert_pc->course_date = date("Y-m-d H:i:s", time());
                                    $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
                                    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
                                    $insert_pc->recordid = $recordid;
                                    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                                    $insert_pc->user_id = $userid;
                                    $insert_pc->save();
        
                                    // SEND MESSAGE
                                    $text  = "";
                                    $text .= "Patient ".$patient_name." \n ";
                                    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                                    $text .= '' . $old_entry[$keym] . '  -> ' .  $new_entry[$keym].'<br/>';
        
                                    $mess = Messages::medication_acknowledge_messages($ipid, $text);
        
        
                                    // CREATE TODO
                                    $text_todo  = "";
                                    $text_todo .= "Patient ".$patient_name." <br/> ";
                                    $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/> ";
                                    $text_todo .= '' . $old_entry[$keym] . '  -> ' .  $new_entry[$keym].' <br/>';
        
                                    $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $post['drid'][$keym], $recordid);
                                }
                            }
                        }
                        else if (!empty($post['medication'][$keym]))
                        {
                            
                            $cust = new PatientDrugPlan();
                            $cust->ipid = $ipid;
                            //ISPC-2684 Lore 12.10.2020
                            $modules = new Modules();
                            if($modules->checkModulePrivileges("240", $clientid)){
                                $cust->dosage = $post['dosage'][$keym];
                            }else {
                                if($post['dosage_24h'][$keym])
                                {
                                    $cust->dosage = str_replace(',', '.', $post['dosage_24h'][$keym])/24;
                                }
                                else
                                {
                                    $cust->dosage = $post['dosage'][$keym];
                                }
                            }

                            $cust->dosage_interval = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
                            $cust->medication_master_id = $medid;
                            $cust->verordnetvon = $post['verordnetvon'][$keym];
        
                            // medication_change
                            if(!empty($post['medication_change'][$keym]))
                            {
                                $cust->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
                            }
                            elseif(!empty($post['done_date']))
                            {
                                $cust->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
                            }
                            else
                            {
                                $cust->medication_change = date('Y-m-d 00:00:00');
                            }
        
                            $cust->isbedarfs = 0;
                            //$cust->iscrisis = 0; //ispc 1823 ??
                            $cust->isivmed = 0;
                            $cust->isschmerzpumpe = 1;
//                             $cust->cocktailid = $post['cocktailhid'];
                            $cust->cocktailid = $post['cocktail']['id'];
                            $cust->save();
                            $inserted_id = $cust->id;
                            
                            //ISPC-2554 Carmen pct.3 27.03.2020
                            $drugplanid = $inserted_id;
                            //--
    
                            // Insert extra data
                            $insert_pde = new PatientDrugPlanExtra();
                            $insert_pde->ipid = $ipid;
                            $insert_pde->drugplan_id = $inserted_id;
                            $insert_pde->drug = $post['drug'][$keym];
                            $insert_pde->unit = $post['unit'][$keym];
                            
                            $insert_pde->dosage_24h_manual = $post['dosage_24h'][$keym];            //ISPC-2684 Lore 12.10.2020
                            $insert_pde->unit_dosage = $post['unit_dosage'][$keym];            //ISPC-2684 Lore 05.10.2020
                            $insert_pde->unit_dosage_24h = $post['unit_dosage_24h'][$keym];    //ISPC-2684 Lore 05.10.2020
                            
                            $insert_pde->type = $post['type'][$keym];
                            $insert_pde->indication = trim($post['indication'][$keym]);
                            $insert_pde->importance = $post['importance'][$keym];
                            $insert_pde->dosage_form = $post['dosage_form'][$keym];
                            $insert_pde->concentration = $post['concentration'][$keym];
                            $insert_pde->save();
        
                            
                            
                            // INSERT IN ALT 
                            $insert_at = new PatientDrugPlanAlt();
                            $insert_at->ipid = $ipid;
                            $insert_at->drugplan_id = $inserted_id;
                            //ISPC-2684 Lore 12.10.2020
                            $modules = new Modules();
                            if($modules->checkModulePrivileges("240", $clientid)){
                                $insert_at->dosage = $post['dosage'][$keym];
                            }else {
                                if($post['dosage_24h'][$keym])
                                {
                                    $insert_at->dosage = $post['dosage_24h'][$keym]/24;
                                }
                                else
                                {
                                    $insert_at->dosage = $post['dosage'][$keym];
                                }
                            }

                            $insert_at->dosage_interval = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
                            $insert_at->medication_master_id = $medid;
                            $insert_at->isbedarfs = "0";
                            //$insert_at->iscrisis = "0"; //ispc 1823 ??
                            $insert_at->isivmed = "0";
                            $insert_at->isschmerzpumpe = "1";
                            $insert_at->cocktailid = $post['cocktail']['id'];
                            $insert_at->treatment_care = "0";
                            $insert_at->isnutrition = "0";
                            $insert_at->verordnetvon = $post['verordnetvon'][$keym];
                            $insert_at->comments = $post['comments'][$keym];
                            $insert_at->medication_change = $medication_change_date[$keym];
                            $insert_at->status = "new";
                            $insert_at->save();
                            $recordid = $insert_at->id;
    
                            // add extra data
                            $cust_pde = new PatientDrugPlanExtraAlt();
                            $cust_pde->ipid = $ipid;
                            $cust_pde->drugplan_id_alt = $recordid;
                            $cust_pde->drugplan_id = $inserted_id;
                            $cust_pde->drug = $post['drug'][$keym];
                            $cust_pde->unit = $post['unit'][$keym];
                            
                            $cust_pde->dosage_24h_manual = $post['dosage_24h'][$keym];            //ISPC-2684 Lore 12.10.2020
                            $cust_pde->unit_dosage = $post['unit_dosage'][$keym];            //ISPC-2684 Lore 05.10.2020
                            $cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$keym];    //ISPC-2684 Lore 05.10.2020
                            
                            $cust_pde->type = $post['type'][$keym];
                            $cust_pde->indication = trim($post['indication'][$keym]);
                            $cust_pde->importance = $post['importance'][$keym];
                            $cust_pde->dosage_form = $post['dosage_form'][$keym];
                            $cust_pde->concentration= $post['concentration'][$keym];
                            $cust_pde->save();
        
                            // NEW ENTRY
                            // new name
                            $new_med = Doctrine::getTable('Medication')->find($medid);
                            $new_medication_name[$keym] = $new_med->name;
        
        
                            // new dosage
                            $new_medication_dosage[$keym] = $post['dosage'][$keym];
                            
        
                            // new comments
                            $new_medication_comments[$keym] = $post['comments'][$keym];
        
                            // new change date
                            $medication_change_date_str[$keym]= date("d.m.Y",time());
        
                            $new_entry="";
                            if(strlen($new_medication_dosage[$keym])>0)
                            {
                                $new_entry = 'Schmerzpumpe '.$new_medication_name[$keym]."  |  ".$new_medication_dosage[$keym]." | ". $new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
                            }
                            else
                            {
                                $new_entry = 'Schmerzpumpe '.$new_medication_name[$keym]." | ".$new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
                            }
        
        
        		            $attach = 'OHNE FREIGABE:   -> ' .  $new_entry.'';
        		            $insert_pc = new PatientCourse();
        		            $insert_pc->ipid = $ipid;
        		            $insert_pc->course_date = date("Y-m-d H:i:s", time());
        		            $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
        		            $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
        		            $insert_pc->recordid = $recordid;
        		            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        		            $insert_pc->user_id = $userid;
        		            $insert_pc->save();
        
                            // SEND MESSAGE
                            $text  = "";
                            $text .= "Patient ".$patient_name." \n ";
                            $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                            $text .=  "neue Medikation:  " .  $new_entry." \n ";
        
                            $mess = Messages::medication_acknowledge_messages($ipid, $text);
        
                            // CREATE TODO
        
                            $text_todo  = "";
                            $text_todo .= "Patient ".$patient_name." <br/>";
                            $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
                            $text_todo .=  "neue Medikation:  " .  $new_entry."  <br/>";
        
                            $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $inserted_id, $recordid);
                        }
                        
                        //ISPC-2554 pct.3 Carmen 27.03.2020
                        $atcarr = (array)json_decode(html_entity_decode($post[$keym]['atc']));
                       
                        $atcid = array_search($drugplanid, array_column($atcdet, 'drugplan_id'));
                       
                        if($atcid !== false)
                        {
                        	if($atcdet[$atcid]['atc_code'] != $atcarr['atc_code'])
                        	{
                        		$todelete[] = $atcdet[$atcid]['id'];
                        		 
                        		$toupdate[$atcindex]['ipid'] = $ipid;
                        		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                        		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                        		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                        		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                        		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                        		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                        		$atcindex++;
                        	}
                        }
                        else
                        {
                        	if(!empty($atcarr))
                        	{
                        		$toupdate[$atcindex]['ipid'] = $ipid;
                        		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                        		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                        		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                        		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                        		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                        		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                        		$atcindex++;
                        	}
                        }
                        //--
                    }
                    
                    //update cocktailid
                    $cocktail = Doctrine::getTable('PatientDrugPlanCocktails')->find($post['cocktail']['id']);
                    if(
                        $post['cocktail']['description'] != $cocktail->description ||
                        $post['cocktail']['bolus'] != $cocktail->bolus ||
                        $post['cocktail']['max_bolus'] != $cocktail->max_bolus ||
                        $post['cocktail']['flussrate'] != $cocktail->flussrate ||
                        $post['cocktail']['sperrzeit'] != $cocktail->sperrzeit ||
                        $post['cocktail']['pumpe_medication_type'] != $cocktail->pumpe_medication_type ||
                        $post['cocktail']['carrier_solution'] != $cocktail->carrier_solution
                    )
                    {
                        
//                         $cocktail->description = $post['cocktail']['description'];
//                         $cocktail->bolus = $post['cocktail']['bolus'];
//                         $cocktail->flussrate = $post['cocktail']['flussrate'];
//                         $cocktail->sperrzeit = $post['cocktail']['sperrzeit'];
//                         $cocktail->pumpe_medication_type = $post['cocktail']['pumpe_medication_type'];
//                         $cocktail->carrier_solution = $post['cocktail']['carrier_solution'];
//                         $cocktail->save();
                        
                        $inser_calt =  new PatientDrugPlanAltCocktails();
                        $inser_calt->ipid = $ipid;
                        $inser_calt->userid = $userid;
                        $inser_calt->clientid = $clientid;
                        $inser_calt->drugplan_cocktailid = $post['cocktail']['id'];
                        $inser_calt->description = $post['cocktail']['description'];
                        $inser_calt->bolus = $post['cocktail']['bolus'];
                        $inser_calt->max_bolus = $post['cocktail']['max_bolus'];
                        $inser_calt->flussrate = $post['cocktail']['flussrate'];
                        if(isset($post['cocktail']['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
                            $inser_calt->flussrate_type = $post['cocktail']['flussrate_type'];   //ISPC-2684 Lore 02.10.2020
                        }
                        
                        $inser_calt->sperrzeit = $post['cocktail']['sperrzeit'];
                        $inser_calt->pumpe_type = $post['cocktail']['pumpe_type'];
                        $inser_calt->pumpe_medication_type = $post['cocktail']['pumpe_medication_type'];
                        $inser_calt->carrier_solution = $post['cocktail']['carrier_solution'];
                        $inser_calt->status = "edit";
                        $inser_calt->save();
        
                        $recordid_cocktail_alt = $inser_calt->id;
                        
                        $old_entry="";
                        $old_entry = "Kommentar: " . $cocktail->description."";
                        $old_entry .= "\nApplikationsweg: " .$cocktail->pumpe_medication_type;
                        //$old_entry .= "\nFlussrate: " . $cocktail->flussrate;
                        //ISPC-2684 Lore 08.10.2020
                        if(!empty($cocktail->flussrate_type) && $cocktail->flussrate_type != NULL){
                            $old_entry .= "\nFlussrate "." (".$cocktail->flussrate_type."): " . $cocktail->flussrate;
                        }else{
                            $old_entry .= "\nFlussrate: " . $cocktail->flussrate;
                        }
                        //.
                        $old_entry .= "\nTrägerlösung: " .$cocktail->carrier_solution;
                        
                        if($cocktail->pumpe_type == "pca")
                        {
                            $old_entry .= "\nBolus: " .$cocktail->bolus;
                            $old_entry .= "\nMax Bolus: " .$cocktail->max_bolus;
                            $old_entry .= "\nSperrzeit: " .$cocktail->sperrzeit;
                        }
                        
                        
                        $new_entry="";
                        $new_entry = "Kommentar: " . $post['cocktail']['description']."";
                        $new_entry .= "\nApplikationsweg: " .$post['cocktail']['pumpe_medication_type'];
                        //$new_entry .= "\nFlussrate: " . $post['cocktail']['flussrate'];
                        //ISPC-2684 Lore 08.10.2020
                        if(!empty($post['cocktail']['flussrate_type'])){
                            $new_entry .= "\nFlussrate_simple"." (".$post['cocktail']['flussrate_type']."): " . $post['cocktail']['flussrate'];
                        }else{
                            $new_entry .= "\nFlussrate: " . $post['cocktail']['flussrate'];
                        }
                        //.
                        $new_entry .= "\nTrägerlösung: " .$post['cocktail']['carrier_solution'];
                        
                        if($post['cocktail']['pumpe_type'] == "pca")
                        {
                            $new_entry .= "\nBolus: " .$post['cocktail']['bolus'];
                            $new_entry .= "\nMax Bolus: " .$post['cocktail']['max_bolus'];
                            $new_entry .= "\nSperrzeit: " .$post['cocktail']['sperrzeit'];
                        }
                        
                        
                        
                        
                        $attach = "OHNE FREIGABE:" . $old_entry . "  \n -> \n  " .  $new_entry."";
                        $insert_pc = new PatientCourse();
                        $insert_pc->ipid = $ipid;
                        $insert_pc->course_date = date("Y-m-d H:i:s", time());
                        $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
                        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
                        $insert_pc->recordid = $recordid_cocktail_alt;
                        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                        $insert_pc->user_id = $userid;
                        $insert_pc->save();

                        
                        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        // SEND MESSAGE
                        $text  = "";
                        $text .= "Patient ".$patient_name." \n ";
                        $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                        $text .= '' . $old_entry . '  -> ' .  $new_entry.'<br/>';
                        
                        $mess = Messages::medication_acknowledge_messages($ipid, $text);
                        
                        
                        // CREATE TODO
                        $text_todo  = "";
                        $text_todo .= "Patient ".$patient_name." <br/> ";
                        $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/> ";
                        $text_todo .= '' . str_replace("\n","<br/>",$old_entry) . '  -> ' .  str_replace("\n","<br/>",$new_entry).' <br/>';
                        
                        $todos = Messages::pump_medication_acknowledge_todo($ipid, $text_todo, $post['cocktail']['id'], $recordid_cocktail_alt);
                    }
                }
            }
            else
            {
                $misc = "Medication change  Permission Error - Update schmertpumpe";
                PatientPermissions::MedicationLogRightsError(false,$misc);
            }
        }
        else
        {
            // INSERT NEW COCKTAIl WITH MEDICATIONS
        	
            if ($post['isschmerzpumpe'] == 1 && empty($post['cocktail']['id']))
            {
                //insert cocktail procedure
                $mc = new PatientDrugPlanCocktails();
                $mc->userid = $userid;
                $mc->clientid = $clientid;
                $mc->ipid = $ipid;
                $mc->description = $post['cocktail']['description'];
                $mc->bolus = $post['cocktail']['bolus'];
                $mc->max_bolus = $post['cocktail']['max_bolus'];
                $mc->flussrate = $post['cocktail']['flussrate'];
                if(isset($post['cocktail']['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
                    $mc->flussrate_type = $post['cocktail']['flussrate_type'];      //ISPC-2684 Lore 02.10.2020
                }
                $mc->sperrzeit = $post['cocktail']['sperrzeit'];
                $mc->pumpe_type = $post['cocktail']['pumpe_type'];
                $mc->pumpe_medication_type = $post['cocktail']['pumpe_medication_type'];
                $mc->carrier_solution = $post['cocktail']['carrier_solution'];
                if(!empty($post['cocktail']['source_ipid'])){
                    $mc->source_cocktailid = $post['cocktail']['source_cocktailid'];
                    $mc->source_ipid = $post['cocktail']['source_ipid'];
                }
                $mc->save();
            
                //get cocktail id
                $cocktail_id = $mc->id;

                foreach ($post['hidd_medication'] as $key => $val)
                {
                    if ($post['hidd_medication'][$key] > 0)
                    {
                        $medid = $post['hidd_medication'][$key];
                    }
                    else
                    {
                    	$medid = $post['newhidd_medication'][$key];
                    }

                    
                    if ($medid > 0)
                    {
                    	//ISPC-2554 Carmen pct.3 27.03.2020
                    	$medmasterid = $medid;
                    	//--
        				$cust = new PatientDrugPlan();
        				$cust->ipid = $ipid;
        				if(is_array($post['dosage'][$key]))
        				{ // NEW style
        				
        				foreach ($post['dosage'][$key] as $time => $dosage_value)
        				{
        				    if(strlen($dosage_value) ==  0){
                                //$dosage_value = " / ";
        				        $dosage_value = "";
        				    }
       				        $old_dosage_array[$key][] = $dosage_value;
        				}
        				    $cust->dosage = implode("-",$old_dosage_array[$key]);;
        				}
        				else
        				{ // OLD style
        				    //ISPC-2684 Lore 12.10.2020
        				    $modules = new Modules();
        				    if($modules->checkModulePrivileges("240", $clientid)){
        				        $cust->dosage  = $post['dosage'][$key];
        				    }else {
        				        //TODO-3624 Ancuta  10.12.2020 - Save dosaje as it comes - no calculation 
        				        
        				        $cust->dosage  = $post['dosage'][$key];
        				        /* 
        				        if($post['dosage_24h'][$key])
        				        {
        				            $cust->dosage  = str_replace(',', '.', $post['dosage_24h'][$key])/24;
        				        }
        				        else
        				        {
        				            $cust->dosage  = $post['dosage'][$key];
        				        } */
        				    }

        				    $cust->dosage_interval  = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
        				}
        				
        				$cust->medication_master_id = $medid;
        				$cust->isbedarfs = $post['isbedarfs'];
        				$cust->iscrisis = $post['iscrisis'];
        				$cust->isivmed = $post['isivmed'];
        				if ($post['isschmerzpumpe'] == 1)
        				{
        				    $cust->isschmerzpumpe = $post['isschmerzpumpe'];
        				    $cust->cocktailid = $cocktail_id;
        				}
        				$cust->treatment_care = $post['treatment_care'];
        				$cust->isnutrition = $post['isnutrition'];
    
        				$cust->verordnetvon = $post['verordnetvon'][$key];
        				$cust->comments = $post['comments'][$key];
    
        				if($post['done_date'])
        				{
        				    $cust->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
        				}
        				else
        				{
        				    $cust->medication_change = date('Y-m-d 00:00:00');
        				}
        				


        				if(!empty($post['source_ipid'][$key]) &&  $post['source_ipid'][$key] != $ipid ){
        				
        				    $cust->source_ipid = $post['source_ipid'][$key];
        				
        				    if(!empty($post['source_drugplan_id'][$key])){
        				
        				        $cust->source_drugplan_id = $post['source_drugplan_id'][$key];
        				    }
        				}
        				
        				$cust->save();
        				$inserted_id = $cust->id;
        				$insertedIds[] = $cust->id;
        				//ISPC-2554 Carmen pct.3 27.03.2020
        				$drugplanid = $inserted_id;
        				//--
        				
        				// Insert dosage
        				if(is_array($post['dosage'][$key]))
        				{ // NEW style
            				foreach ($post['dosage'][$key] as $time => $dosage_value)
            				{
            				    //  insert new lines
            				    $insert_pdd = new PatientDrugPlanDosage();
            				    $insert_pdd->ipid = $ipid;
            				    $insert_pdd->drugplan_id = $inserted_id;
            				    $insert_pdd->dosage = $dosage_value;
            				    //TODO-3624 Ancuta 23.11.2020
            				    $insert_pdd->dosage_full = $post['dosage_full'][$key][$time];
            				    $insert_pdd->dosage_concentration = $post['dosage_concentration'][$key][$time];
            				    $insert_pdd->dosage_concentration_full = $post['dosage_concentration_full'][$key][$time];
            				    //--
            				    $insert_pdd->dosage_time_interval = $time.":00";
            				    $insert_pdd->save();
            				}
        				}
        				
        				// Insert extra data
        				$insert_pde = new PatientDrugPlanExtra();
        				$insert_pde->ipid = $ipid;
        				$insert_pde->drugplan_id = $inserted_id;
        				$insert_pde->drug = $post['drug'][$key];
        				$insert_pde->unit = $post['unit'][$key];
        				
        				$insert_pde->dosage_24h_manual = $post['dosage_24h'][$key];            //ISPC-2684 Lore 12.10.2020
        				$insert_pde->unit_dosage = $post['unit_dosage'][$key];            //ISPC-2684 Lore 05.10.2020
        				$insert_pde->unit_dosage_24h = $post['unit_dosage_24h'][$key];    //ISPC-2684 Lore 05.10.2020
        				
        				$insert_pde->type = $post['type'][$key];
        				$insert_pde->indication = trim($post['indication'][$key]);
        				$insert_pde->importance = $post['importance'][$key];
        				
        				$insert_pde->dosage_form = $post['dosage_form'][$key];
        				$insert_pde->concentration  = $post['concentration'][$key];
        				$insert_pde->save();
        				
        				
    
    
        				// this is for  Medication acknowledge
        				if(in_array($userid,$approval_users) && $acknowledge == "1" ){
        				    // NEW ENTRY
        				    if($post['isivmed'] == 0 && $post['isbedarfs'] == 1 && $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				    {
        				        $shortcut = "N";
        				    }
        				    elseif($post['isivmed'] == 1 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 0 )
        				    {
        				        $shortcut = "I";
        				    }
        				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 1 && $post['treatment_care'] == 0 )
        				    {
        				        $shortcut = "Q";
        				        $prefix = "Schmerzpumpe ";
        				    }
        				    elseif($post['isivmed'] == 0 && $post['isbedarfs'] == 0 &&  $post['isschmerzpumpe'] == 0 && $post['treatment_care'] == 1 )
        				    {
        				        $shortcut = "BP";
        				    }
        				    else
        				    {
        				        $shortcut = "M";
        				    }
        				    // new name
        				    if( $post['treatment_care'] == 1 )
        				    {
        				        $new_med = Doctrine::getTable('MedicationTreatmentCare')->find($medid);
        				    }
        				    elseif( $post['isnutrition'] == 1 )
        				    {
        				        $new_med = Doctrine::getTable('Nutrition')->find($medid);
        				    }
        				    else
        				    {
        				        $new_med = Doctrine::getTable('Medication')->find($medid);
        				    }
    
        				    $new_medication_name[$key] = $new_med->name;
    
        				    // new dosage
        				    $new_medication_dosage[$key] = $post['dosage'][$key];
        				    
    
        				    // new comments
        				    $new_medication_comments[$key] = $post['comments'][$key];
    
        				    // new change date
        				    $medication_change_date_str[$key]= date("d.m.Y",time());
    
        				    if(strlen($new_medication_dosage[$key])>0)
        				    {
        				        $new_entry[$key] = $prefix.$new_medication_name[$key]."  |  ".$new_medication_dosage[$key]." | ". $new_medication_comments[$key]." | ".$medication_change_date_str[$key];
        				    }
        				    else
        				    {
        				        $new_entry[$key] = $prefix.$new_medication_name[$key]." | ".$new_medication_comments[$key]." | ".$medication_change_date_str[$key];
        				    }
    
        				    $attach = $new_entry[$key].'';
        				    $insert_pc = new PatientCourse();
        				    $insert_pc->ipid = $ipid;
        				    $insert_pc->course_date = date("Y-m-d H:i:s", time());
        				    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
        				    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
        				    $insert_pc->recordid = $inserted_id;
        				    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        				    $insert_pc->user_id = $userid;
        				    $insert_pc->save();
        				    
							//TODO-2850  ISPC: Todo is not marked as ready
        				    $text_todo = "";
        				    $todos = Messages::remove_pump_medication_acknowledge_todo($ipid, $text_todo, $cocktail_id, $recordid_cocktail_alt);
        				    
        				    
        				    
        				    
        				}
                    }
                    //ISPC-2554 pct.3 Carmen 27.03.2020
                    $atcarr = (array)json_decode(html_entity_decode($post[$key]['atc']));
                     
                    $atcid = array_search($drugplanid, array_column($atcdet, 'drugplan_id'));
                    
                    if($atcid !== false)
                    {
                    	if($atcdet[$atcid]['atc_code'] != $atcarr['atc_code'])
                    	{
                    		$todelete[] = $atcdet[$atcid]['id'];
                    		 
                    		$toupdate[$atcindex]['ipid'] = $ipid;
                    		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                    		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                    		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                    		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                    		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                    		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                    		$atcindex++;
                    	}
                    }
                    else
                    {
                    	if(!empty($atcarr))
                    	{
                    		$toupdate[$atcindex]['ipid'] = $ipid;
                    		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                    		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                    		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                    		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                    		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                    		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                    		$atcindex++;
                    	}
                    }
                    //--
                }                
            }
            else
            {
                // UPDATE MEDICATION  - ALONG WITH COCKTAIL DETAILS
                foreach ($post['hidd_medication'] as $keym => $valm)
                {
                    $update_sh_medication[$keym] = "0";
        
                    if ($post['hidd_medication'][$keym] > 0)
                    {
                        $medid = $post['hidd_medication'][$keym];
                    }
                    else
                    {
                        $medid = $post['newhidd_medication'][$keym];
                    }
                    //ISPC-2554 Carmen pct.3 27.03.2020
                    $medmasterid = $medid;
                    //--
                    
                    // DOSAJE
                    $post_dosaje[$keym] = "";
                    $post_dosage_interval[$keym] = null;
                    
                    if(is_array($post['dosage'][$keym]))
                    { // NEW style
                        foreach ($post['dosage'][$keym] as $time => $dosage_value)
                        {
                            if(strlen($dosage_value) == 0){
                                // $dosage_value = " / ";
                                $dosage_value = "";
                            }
                        
                            $old_dosage_array[$keym][] = $dosage_value;
                        }
                        $post_dosaje[$keym] = implode("-",$old_dosage_array[$keym]);
                    }
                    else
                    { // OLD style
                        //ISPC-2684 Lore 12.10.2020
                        $modules = new Modules();
                        if($modules->checkModulePrivileges("240", $clientid)){
                            $post_dosaje[$keym] = $post['dosage'][$keym];
                        }else {

                            //TODO-3624 Ancuta  10.12.2020 - Save dosaje as it comes - no calculation
                            $post_dosaje[$keym] = $post['dosage'][$keym];
                            /* 
                            if($post['dosage_24h'][$keym])
                            {
                                $post_dosaje[$keym] = str_replace(',', '.', $post['dosage_24h'][$keym])/24;
                            }
                            else
                            {
                                $post_dosaje[$keym] = $post['dosage'][$keym];
                            } */
                        }

                        $post_dosage_interval[$keym] = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
                    }
                    
                    
                    if ($post['drid'][$keym] > 0)
                    {
                        $cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$keym]);
                        
                        // get exra data
                        $extra_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$post['drid'][$keym]);
                        $extra_data = $extra_array[$post['drid'][$keym]];
                        
                        
                        if ($cust){
                        	//ISPC-2554 Carmen pct.3 27.03.2020
                        	$drugplanid = $cust->id;
                        	//--
                            $existing_string = "";
                            $existing_string .= trim($cust->dosage);                            
                            $existing_string .= trim($cust->medication_master_id);
                            $existing_string .= trim($cust->verordnetvon);
                            $existing_string .= trim($extra_data['drug']);
                            
                            $existing_string .= trim($extra_data['dosage_24h_manual']);   //ISPC-2684 Lore 12.10.2020
                            $existing_string .= trim($extra_data['unit_dosage']);        //ISPC-2684 Lore 05.10.2020
                            $existing_string .= trim($extra_data['unit_dosage_24h']);    //ISPC-2684 Lore 05.10.2020
                            
                            $existing_string .= trim($extra_data['unit_id']);
                            $existing_string .= trim($extra_data['dosage_form_id']);
                            $existing_string .= trim($extra_data['concentration']);
                            $existing_string .= trim($extra_data['indication_id']);
                            $existing_date = strtotime(date('d.m.Y',strtotime($cust->medication_change)));
                            
                            
                            $post_string = "";
                            $post_string .= trim($post['dosage'][$keym]);
                            $post_string .= trim($medid);
                            
                            if(strlen($post['verordnetvon'][$keym]) == 0){
                                $post_string .= 0;
                            } else{
                                $post_string .= trim($post['verordnetvon'][$keym]);
                            }
                            $post_string .= trim($post['drug'][$keym]);
                            $post_string .= trim($post['unit'][$keym]);
                            
                            $post_string .= trim($post['dosage_24h'][$keym]);  //ISPC-2684 Lore 12.10.2020
                            $post_string .= trim($post['unit_dosage'][$keym]);        //ISPC-2684 Lore 05.10.2020
                            $post_string .= trim($post['unit_dosage_24h'][$keym]);    //ISPC-2684 Lore 05.10.2020
                            
                            $post_string .= trim($post['dosage_form'][$keym]);
                            $post_string .= trim($post['concentration'][$keym]);
                            $post_string .= trim($post['indication'][$keym]);
                            $post_date = strtotime($post['medication_change'][$keym]);
                            
                            
                            if( ($existing_string != $post_string || $existing_date != $post_date)  && $post['edited'][$keym] == "1")
                            {//check to update only what's modified
                                
                                $update_sh_medication[$keym] = "1";
        
                                if(!empty($post['medication_change'][$keym])){
                                    //check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
                                    if ($existing_string != $post_string)
                                    {
                                        if ($post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
                                            $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
                                        } elseif ($post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
                                            $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
                                        } elseif ($post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
                                            $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
                                        } else {
                                            $medication_change_date[$keym] = date('Y-m-d 00:00:00');
                                        }
        
                                        // if no medication details were modified - check in the "last edit date" was edited
                                    } else if(
                                        ( $post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
                                        ( $post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
                                        ( $post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->create_date))))) )
                                    {
                                        $medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
        
                                    } else if(
                                        ( $post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
                                        ( $post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
                                        ( $post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
                                    {
        
                                        $update_sh_medication[$keym] = "0";
                                    }
        
                                    // if "last edit date was edited - save current date"
                                } else {
                                    $medication_change_date[$keym] = date('Y-m-d 00:00:00');
                                }
                            } else{
                                $update_sh_medication[$keym] = "0";
                            }
                            /* ================= Save in patient drugplan history ====================*/
                            
                            
                            if( $update_sh_medication[$keym] == "1")                                
                            {
                                $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                                $medication_old_medication_name[$keym] = $old_med->name;
                                $medication_old_medication_id[$keym] =  $old_med->id;
        
                                $cocktail = Doctrine::getTable('PatientDrugPlanCocktails')->find($cust->cocktailid);
                                $history = new PatientDrugPlanHistory();
                                $history->ipid = $ipid;
                                $history->pd_id = $cust->id;
                                $history->pd_medication_master_id = $cust->medication_master_id ;
                                $history->pd_medication_name = $medication_old_medication_name[$keym] ;
                                $history->pd_medication =  $cust->medication;
                                $history->pd_dosage = $cust->dosage;
                                $history->pd_dosage_interval = isset($cust->dosage_interval) ? $cust->dosage_interval : null;
                                $history->pd_dosage_product = isset($cust->dosage_product) ? $cust->dosage_product : null;
                                $history->pd_comments = $cust->comments ;
                                $history->pd_isbedarfs = $cust->isbedarfs;
                                $history->pd_iscrisis = $cust->iscrisis;
                                $history->pd_isivmed = $cust->isivmed;
                                $history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
                                $history->pd_cocktailid= $cust->cocktailid;
                                $history->pd_treatment_care = $cust->treatment_care;
                                $history->pd_isnutrition = $cust->isnutrition;
                                $history->pd_cocktail_comment = $cocktail->description ;
                                $history->pd_cocktail_bolus = $cocktail->bolus;
                                $history->pd_cocktail_max_bolus = $cocktail->max_bolus;
                                $history->pd_cocktail_flussrate =$cocktail->flussrate;
                                $history->pd_cocktail_sperrzeit =$cocktail->sperrzeit;
                                $history->pd_edit_type = $cust->edit_type;
                                $history->pd_verordnetvon = $cust->verordnetvon;
                                $history->pd_medication_change = $cust->medication_change;
                                $history->pd_create_date = $cust->create_date;
                                $history->pd_create_user = $cust->create_user;
                                $history->pd_change_date = $cust->change_date;
                                $history->pd_change_user = $cust->change_user;
                                $history->pd_isdelete = $cust->isdelete;
                                $history->pd_days_interval_technical = isset($cust->days_interval_technical) ? $cust->days_interval_technical : null;
                                $history->save();
                                
                                $history_id =$history->id; 
                                
                                $extra_history_array = PatientDrugPlanExtra::get_patient_all_drugplan_extra($ipid,$post['drid'][$keym]);
                                
                                if(!empty($extra_history_array))
                                {
                                    foreach($extra_history_array as $extra_id =>$extra_data){
                                
                                        $history_pde = new PatientDrugPlanExtraHistory();
                                        $history_pde->ipid = $ipid;
                                        $history_pde->pde_id = $extra_data['id'];
                                        $history_pde->history_id = $history_id;
                                
//                                         $history_pde->pde_drugplan_id = $dv['drugplan_id'];;
                                        $history_pde->pde_drugplan_id = $post['drid'][$keym];;
                                        $history_pde->pde_drug = $extra_data['drug'];
                                        $history_pde->pde_unit = $extra_data['unit'];
                                        
                                        $history_pde->pde_dosage_24h_manual = $extra_data['dosage_24h_manual'];    //ISPC-2684 Lore 12.10.2020
                                        $history_pde->pde_unit_dosage = $extra_data['unit_dosage'];            //ISPC-2684 Lore 05.10.2020
                                        $history_pde->pde_unit_dosage_24h = $extra_data['unit_dosage_24h'];    //ISPC-2684 Lore 05.10.2020
                                        
                                        $history_pde->pde_type = $extra_data['type'];
                                        $history_pde->pde_indication = trim($extra_data['indication']);
                                        $history_pde->pde_importance = $extra_data['importance'];
                                        
                                        $history_pde->pde_dosage_form = $extra_data['dosage_form'];
                                        $history_pde->pde_concentration = $extra_data['concentration'];

                                        $history_pde->pde_isdelete	= $extra_data['isdelete'];
                                
                                        $history_pde->pde_create_user = $extra_data['create_user'];
                                        $history_pde->pde_create_date = $extra_data['create_date'];
                                        $history_pde->pde_change_user = $extra_data['change_user'];
                                        $history_pde->pde_change_date = $extra_data['change_date'];
                                        $history_pde->save();
                                    }
                                }
                                
                                /*
                                TODO-1037 medication new - it is not possible to change medication name 
                                */
                                if ( $post['medication'][$keym] != $old_med->name 
                                		|| $post[$keym]['pzn'] != $old_med->pzn
                                		|| $post[$keym]['source'] != $old_med->source
                                		|| $post[$keym]['dbf_id'] != $old_med->dbf_id ) 
                                {
                                	
                                	$medication_obj =  new Medication();
                                	$medication_new_id = $medication_obj->set_new_record(array(
                                			'clientid' => $clientid,
                                			'name' => $post['medication'][$keym],
                                			'pzn' => $post[$keym]['pzn'],
                                			'source' => $post[$keym]['source'],
                                			'dbf_id' => $post[$keym]['dbf_id'],
                                			'unit' => $post['unit'][$keym],
                                	
                                			)
                                	);
                                	
                                	$medid = $medication_new_id;
                                	
                                	//ISPC-2554 Carmen pct.3 27.03.2020
                                	$medmasterid = $medid;
                                	//--
                                }
                                
                            }

                            /* ================= Update patient drugplan item====================*/
                            if($update_sh_medication[$keym] == "1"){
                            	
                            	//TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
                            	// update extra data
                            	if(!empty($extra_data))
                            	{
                            	
                            		$existing_extra_id = 0;
                            		 
                            		$extra_drugs_q = Doctrine_Query::create()
                            		->select('id')
                            		->from('PatientDrugPlanExtra')
                            		->where("ipid = '" . $ipid . "'")
                            		->andWhere("drugplan_id = '" . $post['drid'][$keym] . "'")
                            		->andWhere("isdelete = '0'")
                            		->orderBy("create_date DESC")
                            		->limit(1);
                            		$extra_drugs_array = $extra_drugs_q->fetchArray();
                            	
                            	
                            		if(!empty($extra_drugs_array)){
                            			$existing_extra_id = $extra_drugs_array[0]['id'];
                            	
                            			$update_pde = Doctrine::getTable('PatientDrugPlanExtra')->find($existing_extra_id);
                            			$update_pde->drug = $post['drug'][$keym];
                            			$update_pde->unit = $post['unit'][$keym];
                            	
                            			$update_pde->dosage_24h_manual = $post['dosage_24h'][$keym];       //ISPC-2684 Lore 12.10.2020
                            			$update_pde->unit_dosage = $post['unit_dosage'][$keym];            //ISPC-2684 Lore 05.10.2020
                            			$update_pde->unit_dosage_24h = $post['unit_dosage_24h'][$keym];    //ISPC-2684 Lore 05.10.2020
                            	
                            			$update_pde->type = $post['type'][$keym];
                            			$update_pde->indication = trim($post['indication'][$keym]);
                            			$update_pde->importance = $post['importance'][$keym];
                            			$update_pde->dosage_form = $post['dosage_form'][$keym];
                            			$update_pde->concentration = $post['concentration'][$keym];
                            			$update_pde->save();
                            		}
                            	}
                            	else
                            	{
                            		// add extra data
                            		$cust_pde = new PatientDrugPlanExtra();
                            		$cust_pde->ipid = $ipid;
                            		$cust_pde->drugplan_id = $post['drid'][$keym];
                            		$cust_pde->drug = $post['drug'][$keym];
                            		$cust_pde->unit = $post['unit'][$keym];
                            	
                            		$cust_pde->dosage_24h_manual = $post['dosage_24h'][$keym];            //ISPC-2684 Lore 12.10.2020
                            		$cust_pde->unit_dosage = $post['unit_dosage'][$keym];            //ISPC-2684 Lore 05.10.2020
                            		$cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$keym];    //ISPC-2684 Lore 05.10.2020
                            	
                            		$cust_pde->type = $post['type'][$keym];
                            		$cust_pde->indication = trim($post['indication'][$keym]);
                            		$cust_pde->importance = $post['importance'][$keym];
                            		$cust_pde->dosage_form = $post['dosage_form'][$keym];
                            		$cust_pde->concentration = $post['concentration'][$keym];
                            		$cust_pde->save();
                            	}
                            	//--
                                
                                $cust->ipid = $ipid;
                                $cust->dosage = $post_dosaje[$keym]; // this is defined at the begining of the function
                                $cust->dosage_interval = isset($post_dosage_interval[$keym]) ? $post_dosage_interval[$keym] : null; // this is defined at the begining of the function
                                $cust->medication_master_id = $medid;
                                $cust->verordnetvon = $post['verordnetvon'][$keym];
                                $cust->medication_change= $medication_change_date[$keym];

                                if(strlen($post['source_ipid'][$keym]) > 0  && $post['source_ipid'][$keym] != $ipid ){
                                    $cust->source_ipid = $post['source_ipid'][$keym];
                                
                                    if(strlen($post['source_drugplan_id'][$keym]) > 0 ){
                                        $cust->source_drugplan_id = $post['source_drugplan_id'][$keym];
                                    }
                                }
                                
                                $cust->save();
                                
                                //ISPC-2554 Carmen pct.3 27.03.2020
                                $drugplanid = $cust->id;
                                //--
                               /* 
                                * TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
                                *  // update extra data
                                if(!empty($extra_data))
                                {
                                
                                    $existing_extra_id = 0;
                                     
                                    $extra_drugs_q = Doctrine_Query::create()
                                    ->select('id')
                                    ->from('PatientDrugPlanExtra')
                                    ->where("ipid = '" . $ipid . "'")
                                    ->andWhere("drugplan_id = '" . $post['drid'][$keym] . "'")
                                    ->andWhere("isdelete = '0'")
                                    ->orderBy("create_date DESC")
                                    ->limit(1);
                                    $extra_drugs_array = $extra_drugs_q->fetchArray();
                                    
                                    
                                    if(!empty($extra_drugs_array)){
                                        $existing_extra_id = $extra_drugs_array[0]['id'];
                                    
                                        $update_pde = Doctrine::getTable('PatientDrugPlanExtra')->find($existing_extra_id);
                                        $update_pde->drug = $post['drug'][$keym];
                                        $update_pde->unit = $post['unit'][$keym];
                                        
                                        $update_pde->dosage_24h_manual = $post['dosage_24h'][$keym];       //ISPC-2684 Lore 12.10.2020
                                        $update_pde->unit_dosage = $post['unit_dosage'][$keym];            //ISPC-2684 Lore 05.10.2020
                                        $update_pde->unit_dosage_24h = $post['unit_dosage_24h'][$keym];    //ISPC-2684 Lore 05.10.2020
                                        
                                        $update_pde->type = $post['type'][$keym];
                                        $update_pde->indication = trim($post['indication'][$keym]);
                                        $update_pde->importance = $post['importance'][$keym];
                                        $update_pde->dosage_form = $post['dosage_form'][$keym];
                                        $update_pde->concentration = $post['concentration'][$keym];
                                        $update_pde->save();
                                    }
                                }
                                else
                                {
                                    // add extra data
                                    $cust_pde = new PatientDrugPlanExtra();
                                    $cust_pde->ipid = $ipid;
                                    $cust_pde->drugplan_id = $post['drid'][$keym];
                                    $cust_pde->drug = $post['drug'][$keym];
                                    $cust_pde->unit = $post['unit'][$keym];
                                    
                                    $cust_pde->dosage_24h_manual = $post['dosage_24h'][$keym];            //ISPC-2684 Lore 12.10.2020
                                    $cust_pde->unit_dosage = $post['unit_dosage'][$keym];            //ISPC-2684 Lore 05.10.2020
                                    $cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$keym];    //ISPC-2684 Lore 05.10.2020
                                    
                                    $cust_pde->type = $post['type'][$keym];
                                    $cust_pde->indication = trim($post['indication'][$keym]);
                                    $cust_pde->importance = $post['importance'][$keym];
                                    $cust_pde->dosage_form = $post['dosage_form'][$keym];
                                    $cust_pde->concentration = $post['concentration'][$keym];
                                    $cust_pde->save();
                                } */
                                
                                // IF An approval user edits a medications that is not approved yet - all data is marcked as inactive for this medication
                                if(in_array($userid,$approval_users) && $acknowledge == "1" )
                                {
                                    $clear = $this->update_pdpa($ipid, $post['drid'][$keym]);
                                }
                            }
                        }
                    }
                    else if (!empty($post['medication'][$keym]))
                    {
        
                        $insert_new_sch = new PatientDrugPlan();
                        $insert_new_sch->ipid = $ipid;
                        $insert_new_sch->dosage = $post_dosaje[$keym];
                        $insert_new_sch->dosage_interval = isset($post_dosage_interval[$keym]) ? $post_dosage_interval[$keym] : null;
                        $insert_new_sch->medication_master_id = $medid;
                        $insert_new_sch->verordnetvon = $post['verordnetvon'][$keym];
                        // medication_change
                        if(!empty($post['medication_change'][$keym]))
                        {
                            $insert_new_sch->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
                        }
                        elseif(!empty($post['done_date']))
                        {
                            $insert_new_sch->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
                        }
                        else
                        {
                            $insert_new_sch->medication_change = date('Y-m-d 00:00:00');
                        }
        
                        $insert_new_sch->isbedarfs = 0;
                        //$insert_new_sch->iscrisis = 0; //ispc 1823 ??
                        $insert_new_sch->isivmed = 0;
                        $insert_new_sch->isschmerzpumpe = 1;
                        $insert_new_sch->cocktailid = $post['cocktail']['id'];
                        

                        if(strlen($post['source_ipid'][$keym]) > 0  && $post['source_ipid'][$keym] != $ipid ){
                            $insert_new_sch->source_ipid = $post['source_ipid'][$keym];
                        
                            if(strlen($post['source_drugplan_id'][$keym]) > 0 ){
                                $insert_new_sch->source_drugplan_id = $post['source_drugplan_id'][$keym];
                            }
                        }
                        
                        $insert_new_sch->save();
                        $schm_id = $insert_new_sch->id;
                        //ISPC-2554 Carmen pct.3 27.03.2020
                        $drugplanid = $schm_id;
                        //--
                        // add extra data
                        $insert_new_sch_pde = new PatientDrugPlanExtra();
                        $insert_new_sch_pde->ipid = $ipid;
                        $insert_new_sch_pde->drugplan_id = $schm_id;
                        $insert_new_sch_pde->drug = $post['drug'][$keym];
                        $insert_new_sch_pde->unit = $post['unit'][$keym];
                        
                        $insert_new_sch_pde->dosage_24h_manual = $post['dosage_24h'][$keym];            //ISPC-2684 Lore 12.10.2020
                        $insert_new_sch_pde->unit_dosage = $post['unit_dosage'][$keym];            //ISPC-2684 Lore 05.10.2020
                        $insert_new_sch_pde->unit_dosage_24h = $post['unit_dosage_24h'][$keym];    //ISPC-2684 Lore 05.10.2020
                        
                        $insert_new_sch_pde->indication = trim($post['indication'][$keym]);
                        $insert_new_sch_pde->importance = $post['importance'][$keym];
                        $insert_new_sch_pde->dosage_form = $post['dosage_form'][$keym];
                        $insert_new_sch_pde->concentration = $post['concentration'][$keym];
                        $insert_new_sch_pde->save();
                    }
                    
                    //ISPC-2554 pct.3 Carmen 27.03.2020
                    $atcarr = (array)json_decode(html_entity_decode($post[$keym]['atc']));
                     
                    $atcid = array_search($drugplanid, array_column($atcdet, 'drugplan_id'));
                    
                    if($atcid !== false)
                    {
                    	if($atcdet[$atcid]['atc_code'] != $atcarr['atc_code'])
                    	{
                    		$todelete[] = $atcdet[$atcid]['id'];
                    		 
                    		$toupdate[$atcindex]['ipid'] = $ipid;
                    		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                    		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                    		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                    		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                    		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                    		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                    		$atcindex++;
                    	}
                    }
                    else
                    {
                    	if(!empty($atcarr))
                    	{
                    		$toupdate[$atcindex]['ipid'] = $ipid;
                    		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                    		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                    		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                    		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                    		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                    		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                    		$atcindex++;
                    	}
                    }
                    //--
                }
                
                if(in_array($userid,$approval_users) && $acknowledge == "1" ){
                    $this->update_pdpca($ipid,array($post['cocktail']['id']));
                }
                
                //update cocktailid
                if(!empty($post['cocktail']['id'])){
                    
                    $cust_update_cocktail = Doctrine::getTable('PatientDrugPlanCocktails')->find($post['cocktail']['id']);
                    $cust_update_cocktail->ipid = $ipid;
                    $cust_update_cocktail->userid = $userid;
                    $cust_update_cocktail->clientid = $clientid;
                    $cust_update_cocktail->description = $post['cocktail']['description'];
                    $cust_update_cocktail->bolus = $post['cocktail']['bolus'];
                    $cust_update_cocktail->max_bolus = $post['cocktail']['max_bolus'];
                    $cust_update_cocktail->flussrate = $post['cocktail']['flussrate'];
                    if(isset($post['cocktail']['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
                        $cust_update_cocktail->flussrate_type = $post['cocktail']['flussrate_type'];    //ISPC-2684 Lore 02.10.2020
                    }
                    
                    $cust_update_cocktail->sperrzeit = $post['cocktail']['sperrzeit'];
                    $cust_update_cocktail->pumpe_type = $post['cocktail']['pumpe_type'];
                    $cust_update_cocktail->pumpe_medication_type = $post['cocktail']['pumpe_medication_type'];
                    $cust_update_cocktail->carrier_solution = $post['cocktail']['carrier_solution'];
                    $cust_update_cocktail->save();
                }
                
                
                 //TODO-2850+TODO-3620  ISPC: Todo is not marked as ready / Ancuta 20.11.2020
                if(in_array($userid,$approval_users) && $acknowledge == "1" ){
                    $text_todo = "";
                    $todos = Messages::remove_pump_medication_acknowledge_todo($ipid, $text_todo, $post['cocktail']['id']);
                }
                
                
            
            }
        }
        
        //ISPC-2554 pct.3 Carmen 27.03.2020
        if(!empty($todelete))
        {
        	$querydel =  PatientDrugPlanAtcTable::getInstance()->createQuery('atc')
        	->delete()
        	->whereIn('atc.id', $todelete);
        	$querydel->execute();
        }
         
         
        if(!empty($toupdate))
        {
        	$atccollection = new Doctrine_Collection('PatientDrugPlanAtc');
        	$atccollection->fromArray($toupdate);
        	$atccollection->save();
        }
    }
    
    /**
     * ISPC-2833 Ancuta 01.03.2021
     * copy of  fn  update_schmerzpumpe_data
     * @param unknown $post
     */
    public function update_ispumpe_data($post)
    {
    	//ISPC - 2329 punctul o - saved dosage depends on dosage_24h 
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
    
        $approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
        $change_users = MedicationChangeUsers::get_medication_change_users($clientid,true);
    
        //ISPC-2554 pct.3 Carmen 27.03.2020
        $atcdet = PatientDrugPlanAtcTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
        $atcindex = 0;
        $toupdate = array();
        $todelete = array();
        //--
        $modules = new Modules();
        if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
        {
            $acknowledge = "1";
            if(in_array($userid,$change_users) || in_array($userid,$approval_users) || $logininfo->usertype == 'SA')
            {
    
                $allow_change = "1";
            }
            else
            {
                $allow_change = "0";
            }
        }
        else
        {
            $acknowledge = "0";
        }
    
    
        if($acknowledge == "1" && !in_array($userid,$approval_users))//Medication acknowledge
        {
            if($allow_change == "1")
            {
                // get user details
                $master_user_details = new User();
                $users_details_arr = $master_user_details->getUserDetails($userid);
                $users_details = $users_details_arr[0];
                $user_name = $users_details['first_name'].' '.$users_details['last_name'];
    
                // get patient details
                $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
                $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
    
                if ($post['ispumpe'] == 1 && empty($post['ispumpe_pumpe']['id']))
                {
                    //insert cocktail procedure
                    $mc = new PatientDrugplanPumpe();
                    $mc->userid = $userid;
                    $mc->clientid = $clientid;
                    $mc->ipid = $ipid;
                    $mc->overall_volume = $post['ispumpe_pumpe']['overall_volume'];
                    $mc->run_rate = $post['ispumpe_pumpe']['run_rate'];
                    $mc->used_liquid = $post['ispumpe_pumpe']['used_liquid'];
                    $mc->pat_weight = $post['ispumpe_pumpe']['pat_weight'];
                    $mc->overall_drug_volume = $post['ispumpe_pumpe']['overall_drug_volume'];
                    $mc->liquid_amount = $post['ispumpe_pumpe']['liquid_amount'];
                    $mc->overall_running_time = $post['ispumpe_pumpe']['overall_running_time'];
                    $mc->min_running_time = $post['ispumpe_pumpe']['min_running_time'];
                    $mc->bolus = $post['ispumpe_pumpe']['bolus'];
                    $mc->max_bolus_day = $post['ispumpe_pumpe']['max_bolus_day'];
                    $mc->max_bolus_after = $post['ispumpe_pumpe']['max_bolus_after'];
                    $mc->next_bolus = $post['ispumpe_pumpe']['next_bolus'];
                    $mc->pumpe_medication_type = $post['ispumpe_pumpe']['pumpe_medication_type'];
                    
                    
                    if(!empty($post['ispumpe_pumpe']['source_ipid'])){
                        $mc->source_pumpe_id = $post['ispumpe_pumpe']['source_pumpe_id'];
                        $mc->source_ipid = $post['ispumpe_pumpe']['source_ipid'];
                    }
                    $mc->save();
                    
                    //get cocktail id
                    $pumpe_id = $mc->id;
                     
                    // insert in cocktail alt
                    $inser_calt =  new PatientDrugplanPumpeAlt();
                    $inser_calt->ipid = $ipid;
                    $inser_calt->userid = $userid;
                    $inser_calt->clientid = $clientid;
                    $inser_calt->drugplan_pumpe_id = $pumpe_id;
                    $inser_calt->overall_volume = $post['ispumpe_pumpe']['overall_volume'];
                    $inser_calt->run_rate = $post['ispumpe_pumpe']['run_rate'];
                    $inser_calt->used_liquid = $post['ispumpe_pumpe']['used_liquid'];
                    $inser_calt->pat_weight = $post['ispumpe_pumpe']['pat_weight'];
                    $inser_calt->overall_drug_volume = $post['ispumpe_pumpe']['overall_drug_volume'];
                    $inser_calt->liquid_amount = $post['ispumpe_pumpe']['liquid_amount'];
                    $inser_calt->overall_running_time = $post['ispumpe_pumpe']['overall_running_time'];
                    $inser_calt->min_running_time = $post['ispumpe_pumpe']['min_running_time'];
                    $inser_calt->bolus = $post['ispumpe_pumpe']['bolus'];
                    $inser_calt->max_bolus_day = $post['ispumpe_pumpe']['max_bolus_day'];
                    $inser_calt->max_bolus_after = $post['ispumpe_pumpe']['max_bolus_after'];
                    $inser_calt->next_bolus = $post['ispumpe_pumpe']['next_bolus'];
                    $inser_calt->pumpe_medication_type = $post['ispumpe_pumpe']['pumpe_medication_type'];
                    $inser_calt->status = "new";
                    $inser_calt->save();
                    
                    $recordid_cocktail_alt = $inser_calt->id;

                    $course_cocktail_entry="";
                    $course_cocktail_entry = "Zielvolumen Pumpe (ml): " . $post['ispumpe_pumpe']['overall_volume']."";
                    $course_cocktail_entry .= "\ngewünschte Laufrate (ml/h): " .$post['ispumpe_pumpe']['run_rate'];
                    $course_cocktail_entry .= "\nTrägerlösung: " .$post['ispumpe_pumpe']['used_liquid'];
                    $course_cocktail_entry .= "\nGewicht (kg): " .$post['ispumpe_pumpe']['pat_weight'];
                    $course_cocktail_entry .= "\nLaufzeit (ohne Bolus) in h: " .$post['ispumpe_pumpe']['overall_running_time'];
                    $course_cocktail_entry .= "\nLaufzeit min. (mit Bolus): " .$post['ispumpe_pumpe']['min_running_time'];
                    $course_cocktail_entry .= "\nBolusmenge (in ml): " .$post['ispumpe_pumpe']['bolus'];
                    $course_cocktail_entry .= "\nmax Bolus pro Tag: " .$post['ispumpe_pumpe']['max_bolus_day'];
                    $course_cocktail_entry .= "\nmax Bolus hintereiander: " .$post['ispumpe_pumpe']['max_bolus_after'];
                    $course_cocktail_entry .= "\nSperrzeit (in Min.): " .$post['ispumpe_pumpe']['next_bolus'];
                    
                    $attach = "OHNE FREIGABE: " .  $course_cocktail_entry."";
                    $insert_pc = new PatientCourse();
                    $insert_pc->ipid = $ipid;
                    $insert_pc->course_date = date("Y-m-d H:i:s", time());
                    $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
                    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
                    $insert_pc->recordid = $recordid_cocktail_alt;
                    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                    $insert_pc->user_id = $userid;
                    $insert_pc->save();
                    
                    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                    // SEND MESSAGE
                    $text  = "";
                    $text .= "Patient ".$patient_name." \n ";
                    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                    $text .=  "neue Medikation:  " .  $new_entry." \n ";
                    
                    $mess = Messages::medication_acknowledge_messages($ipid, $text);
                    
                    // CREATE TODO
                    
                    $text_todo  = "";
                    $text_todo .= "Patient ".$patient_name." <br/>";
                    $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
                    $text_todo .=  "neue Medikation:  " . str_replace("\n","<br/>",$new_entry)."  <br/>";
                    
                    $todos = Messages::ispumpe_pump_medication_acknowledge_todo($ipid, $text_todo, $pumpe_id, $recordid_cocktail_alt);
                     
                
                    foreach ($post['hidd_medication'] as $key => $val)
                    {
                        if ($post['hidd_medication'][$key] > 0)
                        {
                            $medid = $post['hidd_medication'][$key];
                        }
                        else
                        {
                            $medid = $post['newhidd_medication'][$key];
                        }
                
               
                        if ($medid > 0)
                        {
                        	//ISPC-2554 Carmen pct.3 27.03.2020
                        	$medmasterid = $medid;
                        	//--
                        	
            				$cust = new PatientDrugPlan();
            				$cust->ipid = $ipid;
            				
            				//ISPC-2684 Lore 12.10.2020
            				$modules = new Modules();
            				if($modules->checkModulePrivileges("240", $clientid)){
            				    $cust->dosage  = $post['dosage'][$key];
            				}else {
            				    if($post['dosage_24h'][$key])
            				    {
            				        //TODO-2592 Ancuta 10.10.2019
            				        $cust->dosage  = str_replace(',', '.' , $post['dosage_24h'][$key])/24;
            				    }
            				    else
            				    {
            				        $cust->dosage  = $post['dosage'][$key];
            				    }
            				}

            				            				
           				    $cust->dosage_interval  = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
            				$cust->medication_master_id = $medid;
            				$cust->isbedarfs = "0";
            				$cust->isivmed = "0";
           				    $cust->isschmerzpumpe = 0;
            				if ($post['ispumpe'] == 1)
            				{
            				    $cust->ispumpe = 1;
            				    $cust->pumpe_id = $pumpe_id;
            				}
            				$cust->treatment_care = "0";
            				$cust->isnutrition = "0";
            				$cust->verordnetvon = $post['verordnetvon'][$key];
            				$cust->comments = $post['comments'][$key];
            				if($post['done_date'])
            				{
            				    $cust->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
            				}
            				else
            				{
            				    $cust->medication_change = date('Y-m-d 00:00:00',time());
            				}
            				$cust->save();
            				$inserted_id = $cust->id;
            				$insertedIds[] = $cust->id;
            				
            				$drugplanid = $inserted_id;
    
            				// Insert extra data
            				$insert_pde = new PatientDrugPlanExtra();
            				$insert_pde->ipid = $ipid;
            				$insert_pde->drugplan_id = $inserted_id;
            				$insert_pde->drug = $post['drug'][$key];
            				$insert_pde->unit = $post['unit'][$key];
            				$insert_pde->type = $post['type'][$key];
            				$insert_pde->indication = trim($post['indication'][$key]);
            				$insert_pde->importance = $post['importance'][$key];
            				$insert_pde->dosage_form = $post['dosage_form'][$key];
            				$insert_pde->concentration= $post['concentration'][$key];
            				$insert_pde->overall_dosage_h = $post['overall_dosage_h'][$key];
            				$insert_pde->overall_dosage_24h = $post['overall_dosage_24h'][$key];
            				$insert_pde->overall_dosage_pump = $post['overall_dosage_pump'][$key];
            				$insert_pde->drug_volume = $post['drug_volume'][$key];
            				$insert_pde->unit2ml = $post['unit2ml'][$key];
            				$insert_pde->concentration_per_drug = $post['concentration_per_drug'][$key];
            				$insert_pde->bolus_per_med = $post['bolus_per_med'][$key];
            				            				
            				$insert_pde->save();
                
                            // insert in alt
            				$insert_at = new PatientDrugPlanAlt();
            				$insert_at->ipid = $ipid;
            				$insert_at->drugplan_id = $inserted_id;
    				        $insert_at->dosage = $post['dosage'][$key];
            				$insert_at->dosage_interval = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
            				$insert_at->medication_master_id = $medid;
            				$insert_at->isbedarfs = "0";
            				$insert_at->isivmed = "0";
        				    $insert_at->isschmerzpumpe = $cust->isschmerzpumpe;
        				    $insert_at->cocktailid = $cust->cocktailid;
            				if ($cust->ispumpe == 1)
            				{
            				    $insert_at->ispumpe = $cust->ispumpe;
            				    $insert_at->pumpe_id = $cust->pumpe_id;
            				}
            				
            				$insert_at->treatment_care = "0";
            				$insert_at->isnutrition = "0";
            				$insert_at->verordnetvon = $post['verordnetvon'][$key];
            				$insert_at->comments = $post['comments'][$key];
            				$insert_at->medication_change = date('Y-m-d 00:00:00',time());;
            				$insert_at->status = "new";
            				$insert_at->save();
            				$insertedIds[] = $insert_at->id;
            				
            				$recordid = $insert_at->id;
            				
            				// add extra data
            				$cust_pde = new PatientDrugPlanExtraAlt();
            				$cust_pde->ipid = $ipid;
            				$cust_pde->drugplan_id_alt = $recordid;
            				$cust_pde->drugplan_id = $inserted_id;
            				$cust_pde->drug = $post['drug'][$key];
            				
            				
            				$cust_pde->dosage_24h_manual = $post['dosage_24h'][$keym];  
            				$cust_pde->unit_dosage = $post['unit_dosage'][$keym];
            				$cust_pde->unit_dosage_24h = $post['unit_dosage_24h'][$keym];
            				
            				$cust_pde->unit = $post['unit'][$keym];
            				$cust_pde->type = $post['type'][$keym];
            				$cust_pde->indication = trim($post['indication'][$keym]);
            				$cust_pde->importance = $post['importance'][$keym];
            				$cust_pde->dosage_form = $post['dosage_form'][$keym];
            				$cust_pde->concentration= $post['concentration'][$keym];
            				
            				
            				
            				$cust_pde->overall_dosage_h = $post['overall_dosage_h'][$key];
            				$cust_pde->overall_dosage_24h = $post['overall_dosage_24h'][$key];
            				$cust_pde->overall_dosage_pump = $post['overall_dosage_pump'][$key];
            				$cust_pde->drug_volume = $post['drug_volume'][$key];
            				$cust_pde->unit2ml = $post['unit2ml'][$key];
            				$cust_pde->concentration_per_drug = $post['concentration_per_drug'][$key];
            				$cust_pde->bolus_per_med = $post['bolus_per_med'][$key];
            				$cust_pde->save();
            				
            				
                        	// this is for  Medication acknowledge
            				if(!in_array($userid,$approval_users)  && $acknowledge == "1" ){
            				    // NEW ENTRY
            				   if($post['ispumpe'] == 1 )
            				    {
            				        $shortcut = "Q";
            				        $prefix = "Perfusor/Pumpe ";
            				    }
            				     
            				    else
            				    {
            				        $shortcut = "M";
            				    }
            				    // new name
          				        $new_med = Doctrine::getTable('Medication')->find($medid);
            				    $new_medication_name[$key] = $new_med->name;
            				    // new dosage
            				    $new_medication_dosage[$key] = $post['dosage'][$key];
            				    // new comments
            				    $new_medication_comments[$key] = $post['comments'][$key];
            				    // new change date
            				    $medication_change_date_str[$key]= date("d.m.Y",time());
    
            				    $new_entry = "";
            				    if(strlen($new_medication_dosage[$key])>0)
            				    {
            				        $new_entry = $prefix.$new_medication_name[$key]."  |  ".$new_medication_dosage[$key]." | ". $new_medication_comments[$key]." | ".$medication_change_date_str[$key];
            				    }
            				    else
            				    {
            				        $new_entry = $prefix.$new_medication_name[$key]." | ".$new_medication_comments[$key]." | ".$medication_change_date_str[$key];
            				    }

            				    $attach = 'OHNE FREIGABE: ' .  $new_entry.'';
            				    $insert_pc = new PatientCourse();
            				    $insert_pc->ipid = $ipid;
            				    $insert_pc->course_date = date("Y-m-d H:i:s", time());
            				    $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
            				    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
            				    $insert_pc->recordid = $recordid;
            				    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
            				    $insert_pc->user_id = $userid;
            				    $insert_pc->save();
 
            				    
            				    
            				    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            				    // SEND MESSAGE
            				    $text  = "";
            				    $text .= "Patient ".$patient_name." \n ";
            				    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
            				    $text .=  "neue Medikation:  " .  $new_entry." \n ";
            				    
            				    $mess = Messages::medication_acknowledge_messages($ipid, $text);
            				    
            				    // CREATE TODO
            				    $text_todo  = "";
            				    $text_todo .= "Patient ".$patient_name." <br/>";
            				    $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
            				    $text_todo .=  "neue Medikation:  " .  $new_entry ."  <br/>";
            				    
            				    $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $inserted_id, $recordid);
            				}
                        }
                        
                        //ISPC-2554 pct.3 Carmen 27.03.2020
                        $atcarr = (array)json_decode(html_entity_decode($post[$key]['atc']));
                       
                        $atcid = array_search($drugplanid, array_column($atcdet, 'drugplan_id'));
                        
                        if($atcid !== false)
                        {
                        	if($atcdet[$atcid]['atc_code'] != $atcarr['atc_code'])
                        	{
                        		$todelete[] = $atcdet[$atcid]['id'];
                        		 
                        		$toupdate[$atcindex]['ipid'] = $ipid;
                        		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                        		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                        		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                        		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                        		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                        		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                        		$atcindex++;
                        	}
                        }
                        else
                        {
                        	if(!empty($atcarr))
                        	{
                        		$toupdate[$atcindex]['ipid'] = $ipid;
                        		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                        		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                        		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                        		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                        		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                        		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                        		$atcindex++;
                        	}
                        }
                        //--
                    }
                    
                }
                else
                {
                    // UPDATE MEDICATION  - ALONG WITH COCKTAIL DETAILS
                    foreach ($post['hidd_medication'] as $keym => $valm)
                    {
                        $update_sh_medication[$keym] = "0";
        
                        if ($post['hidd_medication'][$keym] > 0)
                        {
                            $medid = $post['hidd_medication'][$keym];
                        }
                        else
                        {
                            $medid = $post['newhidd_medication'][$keym];
                        }
        
                        //ISPC-2554 Carmen pct.3 27.03.2020
                        $medmasterid = $medid;
                        //--
                        
                        if ($post['drid'][$keym] > 0)
                        {
                            $cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$keym]);
                            if ($cust){
                            	//ISPC-2554 Carmen pct.3 27.03.2020
                            	$drugplanid = $cust->id;
                            	//--
								//TODO-3652 Ancuta 03.12.2020
                            	$new_med = Doctrine::getTable('Medication')->find($cust->medication_master_id);
                            	$existing_medication_name  = $new_med->name;
                            	//--
                                // get exra data
                                $extra_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$post['drid'][$keym]);
                                $extra_data = $extra_array[$post['drid'][$keym]];
                                
                                $existing_string = "";
                                $existing_string .= trim($existing_medication_name);//TODO-3652 Ancuta 03.12.2020
                                $existing_string .= trim($cust->dosage);
                                $existing_string .= trim($cust->medication_master_id);
                                $existing_string .= trim($extra_data['drug']);
                                $existing_string .= trim($extra_data['overall_dosage_h']);
                                $existing_string .= trim($extra_data['overall_dosage_24h']);
                                $existing_string .= trim($extra_data['overall_dosage_pump']);
                                $existing_string .= trim($extra_data['drug_volume']);
                                $existing_string .= trim($extra_data['unit2ml']);
                                $existing_string .= trim($extra_data['concentration_per_drug']);
                                $existing_string .= trim($extra_data['bolus_per_med']);
                                $existing_string .= trim($extra_data['unit_id']);
                                $existing_string .= trim($extra_data['indication_id']);
//                                 $existing_date = strtotime(date('d.m.Y',strtotime($cust->medication_change)));
           
                                
                                $post_string = "";
                                $post_string .= trim($post['medication'][$keym]);//TODO-3652 Ancuta 03.12.2020
                                $post_string .= trim($post['dosage'][$keym]);
                                $post_string .= trim($medid);
                                $post_string .= trim($post['drug'][$keym]);
                                $post_string .= trim($post['unit'][$keym]);
                                
                                $post_string .= trim($post['dosage_24h'][$keym]);  //ISPC-2684 Lore 12.10.2020
                                $post_string .= trim($post['unit_dosage'][$keym]);           //ISPC-2684 Lore 05.10.2020
                                $post_string .= trim($post['unit_dosage_24h'][$keym]);       //ISPC-2684 Lore 05.10.2020
                                $post_string .= trim($post['indication'][$keym]);
//                                 $post_date = strtotime($post['medication_change'][$keym]);
                                
                            if( ($existing_string != $post_string)  && $post['edited'][$keym] == "1")
                            {//check to update only what's modified
                                    $update_sh_medication[$keym] = "1";
                                    $medication_change_date[$keym] = date('Y-m-d 00:00:00');
                                    
                            } else{
                                $update_sh_medication[$keym] = "0";
                            }
        
                                // ================= Update patient drugplan item====================
        
                                if($update_sh_medication[$keym] == "1"){
                                    
                                    $this->update_pdpa($ipid,$post['drid'][$keym]);
                                    
                                    $insert_at = new PatientDrugPlanAlt();
                                    $insert_at->ipid = $ipid;
                                    $insert_at->drugplan_id = $post['drid'][$keym];
                                    $insert_at->dosage = $post['dosage'][$keym];
                                    $insert_at->medication_master_id = $medid;
                                    $insert_at->medication = $post['medication'][$keym]; //TODO-3652 Ancuta 03.12.2020
                                    $insert_at->isbedarfs = "0";
                                    $insert_at->isivmed = "0";
                                    $insert_at->isschmerzpumpe = "0";
                                    if ($cust->ispumpe == 1)
                                    {
                                        $insert_at->ispumpe = $cust->ispumpe;
                                        $insert_at->pumpe_id = $cust->pumpe_id;
                                    }
                                    $insert_at->treatment_care = "0";
                                    $insert_at->isnutrition = "0";
                                    $insert_at->medication_change = $medication_change_date[$keym];
                                    $insert_at->status = "edit";
                                    $insert_at->save();
                                    $insertedIds[] = $insert_at->id;
        
                                    $recordid = $insert_at->id;
        
                                    // add extra data
                                    $cust_pde = new PatientDrugPlanExtraAlt();
                                    $cust_pde->ipid = $ipid;
                                    $cust_pde->drugplan_id_alt = $recordid;
                                    $cust_pde->drugplan_id = $post['drid'][$keym];
                                    $cust_pde->drug = $post['drug'][$keym];
                                    $cust_pde->unit = $post['unit'][$keym];
                                    $cust_pde->type = $post['type'][$keym];
                                    $cust_pde->indication = trim($post['indication'][$keym]);
                                    $cust_pde->importance = $post['importance'][$keym];
                                    $cust_pde->overall_dosage_h = $post['overall_dosage_h'][$key];
                                    $cust_pde->overall_dosage_24h = $post['overall_dosage_24h'][$key];
                                    $cust_pde->overall_dosage_pump = $post['overall_dosage_pump'][$key];
                                    $cust_pde->drug_volume = $post['drug_volume'][$key];
                                    $cust_pde->unit2ml = $post['unit2ml'][$key];
                                    $cust_pde->concentration_per_drug = $post['concentration_per_drug'][$key];
                                    $cust_pde->bolus_per_med = $post['bolus_per_med'][$key];
                                    $cust_pde->save();
                                    
                                    
                                    // OLD ENTRY
                                    // old medication name
                                    $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                                    $old_med_name[$keym] = $old_med->name;
        
                                    // old dosage
                                    if($cust->dosage) {
                                        $old_med_dosage[$keym] = $cust->dosage;
                                    }
        
                                    // old comment
                                    if($cust->comments ){
                                        $old_med_comments[$keym] = $cust->comments." | ";
                                    }
                                     
                                    //  old medication date
                                    if($cust->medication_change != "0000-00-00 00:00:00")
                                    {
                                        $old_med_medication_change[$keym] =  date('d.m.Y',strtotime($cust->medication_change));
                                    }
                                    else
                                    {
                                        if($cust->change_date != "0000-00-00 00:00:00")
                                        {
                                            $old_med_medication_change[$keym] =  date('d.m.Y',strtotime($cust->change_date));
                                        }
                                        else
                                        {
                                            $old_med_medication_change[$keym] =  date('d.m.Y',strtotime($cust->create_date));
                                        }
                                    }
        
                                    if(strlen($old_med_dosage[$keym])>0){
                                        $old_entry[$keym] = 'Perfusor/Pumpe '.$old_med_name[$keym].'|'.$old_med_dosage[$keym].' |'.$old_med_comments[$keym].$old_med_medication_change[$keym];
                                    } else	{
                                        $old_entry[$keym] = 'Perfusor/Pumpe '.$old_med_name[$keym].'|'.$old_med_comments[$keym].$old_med_medication_change[$keym];
                                    }
                                    
                                    // NEW ENTRY
                                    // new name
                                    $new_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                                    $new_medication_name[$keym] = $new_med->name;
        
                                    // new dosage
                                    $new_medication_dosage[$keym] = $post['dosage'][$keym];
                                    
                                    // new comments
                                    $new_medication_comments[$keym] = $post['comments'][$keym];
        
                                    // new change date
                                    if($medication_change_date[$keym] != "0000-00-00 00:00:00"){
                                        $medication_change_date_str[$keym] = date("d.m.Y", strtotime($medication_change_date[$keym]));
                                    }
                                    else
                                    {
                                        $medication_change_date_str[$keym]="";
                                    }
        
                                    if(strlen($new_medication_dosage[$keym])>0)
                                    {
                                        $new_entry[$keym] = 'Perfusor/Pumpe '.$new_medication_name[$keym]."  |  ".$new_medication_dosage[$keym]." | ". $new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
                                    }
                                    else
                                    {
                                        $new_entry[$keym] = 'Perfusor/Pumpe '.$new_medication_name[$keym]." | ".$new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
                                    }
        
                                    $attach = 'OHNE FREIGABE: ' . $old_entry[$keym] . '  -> ' .  $new_entry[$keym].'';
                                    $insert_pc = new PatientCourse();
                                    $insert_pc->ipid = $ipid;
                                    $insert_pc->course_date = date("Y-m-d H:i:s", time());
                                    $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
                                    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
                                    $insert_pc->recordid = $recordid;
                                    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                                    $insert_pc->user_id = $userid;
                                    $insert_pc->save();
        
                                    // SEND MESSAGE
                                    $text  = "";
                                    $text .= "Patient ".$patient_name." \n ";
                                    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                                    $text .= '' . $old_entry[$keym] . '  -> ' .  $new_entry[$keym].'<br/>';
        
                                    $mess = Messages::medication_acknowledge_messages($ipid, $text);
        
        
                                    // CREATE TODO
                                    $text_todo  = "";
                                    $text_todo .= "Patient ".$patient_name." <br/> ";
                                    $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/> ";
                                    $text_todo .= '' . $old_entry[$keym] . '  -> ' .  $new_entry[$keym].' <br/>';
        
                                    $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $post['drid'][$keym], $recordid);
                                }
                            }
                        }
                        else if (!empty($post['medication'][$keym]))
                        {
                            
                            $cust = new PatientDrugPlan();
                            $cust->ipid = $ipid;
                            $cust->dosage = $post['dosage'][$keym];
                            $cust->dosage_interval = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
                            $cust->medication_master_id = $medid;
                            $cust->verordnetvon = $post['verordnetvon'][$keym];
        
                            // medication_change
                            if(!empty($post['medication_change'][$keym]))
                            {
                                $cust->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
                            }
                            elseif(!empty($post['done_date']))
                            {
                                $cust->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
                            }
                            else
                            {
                                $cust->medication_change = date('Y-m-d 00:00:00');
                            }
        
                            $cust->isbedarfs = 0;
                            $cust->isivmed = 0;
                            $cust->isschmerzpumpe = 0;
                            $cust->ispumpe = 1;
                            $cust->pumpe_id = $post['ispumpe_pumpe']['id'];
                            $cust->save();
                            $inserted_id = $cust->id;

                            
                            //ISPC-2554 Carmen pct.3 27.03.2020
                            $drugplanid = $inserted_id;
                            //--
    
                            // Insert extra data
                            $insert_pde = new PatientDrugPlanExtra();
                            $insert_pde->ipid = $ipid;
                            $insert_pde->drugplan_id = $inserted_id;
                            $insert_pde->drug = $post['drug'][$keym];
                            $insert_pde->unit = $post['unit'][$keym];
               
                            $insert_pde->type = $post['type'][$keym];
                            $insert_pde->indication = trim($post['indication'][$keym]);
                            $insert_pde->importance = $post['importance'][$keym];
                            $insert_pde->dosage_form = $post['dosage_form'][$keym];
                            $insert_pde->concentration = $post['concentration'][$keym];
                            
                            $insert_pde->overall_dosage_h = $post['overall_dosage_h'][$keym];
                            $insert_pde->overall_dosage_24h = $post['overall_dosage_24h'][$keym];
                            $insert_pde->overall_dosage_pump = $post['overall_dosage_pump'][$keym];
                            $insert_pde->drug_volume = $post['drug_volume'][$keym];
                            $insert_pde->unit2ml = $post['unit2ml'][$keym];
                            $insert_pde->concentration_per_drug = $post['concentration_per_drug'][$keym];
                            $insert_pde->bolus_per_med = $post['bolus_per_med'][$keym];
                            
                            $insert_pde->save();
        
                            
                            
                            // INSERT IN ALT 
                            $insert_at = new PatientDrugPlanAlt();
                            $insert_at->ipid = $ipid;
                            $insert_at->drugplan_id = $inserted_id;
                            $insert_at->dosage = $post['dosage'][$keym];
                            $insert_at->dosage_interval = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
                            $insert_at->medication_master_id = $medid;
                            $insert_at->isbedarfs = "0";
                            $insert_at->isivmed = "0";
                            $insert_at->isschmerzpumpe = "0";
                            $insert_at->ispumpe = "1";
                            $insert_at->pumpe_id = $post['ispumpe_pumpe']['id'];
                            $insert_at->treatment_care = "0";
                            $insert_at->isnutrition = "0";
                            $insert_at->verordnetvon = $post['verordnetvon'][$keym];
                            $insert_at->comments = $post['comments'][$keym];
                            $insert_at->medication_change = $medication_change_date[$keym];
                            $insert_at->status = "new";
                            $insert_at->save();
                            $recordid = $insert_at->id;
    
                            // add extra data
                            $cust_pde = new PatientDrugPlanExtraAlt();
                            $cust_pde->ipid = $ipid;
                            $cust_pde->drugplan_id_alt = $recordid;
                            $cust_pde->drugplan_id = $inserted_id;
                            $cust_pde->drug = $post['drug'][$keym];
                            $cust_pde->unit = $post['unit'][$keym];
                            $cust_pde->type = $post['type'][$keym];
                            $cust_pde->indication = trim($post['indication'][$keym]);
                            $cust_pde->importance = $post['importance'][$keym];
                            $cust_pde->dosage_form = $post['dosage_form'][$keym];
                            $cust_pde->concentration= $post['concentration'][$keym];
                            $cust_pde->overall_dosage_h = $post['overall_dosage_h'][$keym];
                            $cust_pde->overall_dosage_24h = $post['overall_dosage_24h'][$keym];
                            $cust_pde->overall_dosage_pump = $post['overall_dosage_pump'][$keym];
                            $cust_pde->drug_volume = $post['drug_volume'][$keym];
                            $cust_pde->unit2ml = $post['unit2ml'][$keym];
                            $cust_pde->concentration_per_drug = $post['concentration_per_drug'][$keym];
                            $cust_pde->bolus_per_med = $post['bolus_per_med'][$keym];
                            
                            $cust_pde->save();
        
                            // NEW ENTRY
                            // new name
                            $new_med = Doctrine::getTable('Medication')->find($medid);
                            $new_medication_name[$keym] = $new_med->name;
                            // new dosage
                            $new_medication_dosage[$keym] = $post['dosage'][$keym];
                            // new comments
                            $new_medication_comments[$keym] = $post['comments'][$keym];
                            // new change date
                            $medication_change_date_str[$keym]= date("d.m.Y",time());
        
                            $new_entry="";
                            if(strlen($new_medication_dosage[$keym])>0)
                            {
                                $new_entry = 'Schmerzpumpe '.$new_medication_name[$keym]."  |  ".$new_medication_dosage[$keym]." | ". $new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
                            }
                            else
                            {
                                $new_entry = 'Schmerzpumpe '.$new_medication_name[$keym]." | ".$new_medication_comments[$keym]." | ".$medication_change_date_str[$keym];
                            }
        
        
        		            $attach = 'OHNE FREIGABE:   -> ' .  $new_entry.'';
        		            $insert_pc = new PatientCourse();
        		            $insert_pc->ipid = $ipid;
        		            $insert_pc->course_date = date("Y-m-d H:i:s", time());
        		            $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
        		            $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
        		            $insert_pc->recordid = $recordid;
        		            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        		            $insert_pc->user_id = $userid;
        		            $insert_pc->save();
        
                            // SEND MESSAGE
                            $text  = "";
                            $text .= "Patient ".$patient_name." \n ";
                            $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                            $text .=  "neue Medikation:  " .  $new_entry." \n ";
        
                            $mess = Messages::medication_acknowledge_messages($ipid, $text);
        
                            // CREATE TODO
        
                            $text_todo  = "";
                            $text_todo .= "Patient ".$patient_name." <br/>";
                            $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
                            $text_todo .=  "neue Medikation:  " .  $new_entry."  <br/>";
        
                            $todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $inserted_id, $recordid);
                        }
                        
                        //ISPC-2554 pct.3 Carmen 27.03.2020
                        $atcarr = (array)json_decode(html_entity_decode($post[$keym]['atc']));
                       
                        $atcid = array_search($drugplanid, array_column($atcdet, 'drugplan_id'));
                       
                        if($atcid !== false)
                        {
                        	if($atcdet[$atcid]['atc_code'] != $atcarr['atc_code'])
                        	{
                        		$todelete[] = $atcdet[$atcid]['id'];
                        		 
                        		$toupdate[$atcindex]['ipid'] = $ipid;
                        		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                        		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                        		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                        		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                        		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                        		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                        		$atcindex++;
                        	}
                        }
                        else
                        {
                        	if(!empty($atcarr))
                        	{
                        		$toupdate[$atcindex]['ipid'] = $ipid;
                        		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                        		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                        		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                        		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                        		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                        		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                        		$atcindex++;
                        	}
                        }
                        //--
                    }
                    
                    //update pumpe_id
                    $cocktail = Doctrine::getTable('PatientDrugplanPumpe')->find($post['ispumpe_pumpe']['id']);
                    if(
                        $post['ispumpe_pumpe']['overall_volume'] != $cocktail->overall_volume ||
                        $post['ispumpe_pumpe']['run_rate'] != $cocktail->run_rate ||
                        $post['ispumpe_pumpe']['used_liquid'] != $cocktail->used_liquid ||
                        $post['ispumpe_pumpe']['pat_weight'] != $cocktail->pat_weight ||
                        $post['ispumpe_pumpe']['overall_drug_volume'] != $cocktail->overall_drug_volume ||
                        $post['ispumpe_pumpe']['liquid_amount'] != $cocktail->liquid_amount ||
                        $post['ispumpe_pumpe']['overall_running_time'] != $cocktail->overall_running_time ||
                        $post['ispumpe_pumpe']['min_running_time'] != $cocktail->min_running_time ||
                        $post['ispumpe_pumpe']['bolus'] != $cocktail->bolus ||
                        $post['ispumpe_pumpe']['max_bolus_day'] != $cocktail->max_bolus_day ||
                        $post['ispumpe_pumpe']['max_bolus_after'] != $cocktail->max_bolus_after ||
                        $post['ispumpe_pumpe']['next_bolus'] != $cocktail->next_bolus 
                    )
                    {
                        
                        
                        $inser_calt =  new PatientDrugplanPumpeAlt();
                        $inser_calt->ipid = $ipid;
                        $inser_calt->userid = $userid;
                        $inser_calt->clientid = $clientid;
                        $inser_calt->drugplan_pumpe_id = $post['ispumpe_pumpe']['id'];
                        $inser_calt->overall_volume = $post['ispumpe_pumpe']['overall_volume'];
                        $inser_calt->run_rate = $post['ispumpe_pumpe']['run_rate'];
                        $inser_calt->used_liquid = $post['ispumpe_pumpe']['used_liquid'];
                        $inser_calt->pat_weight = $post['ispumpe_pumpe']['pat_weight'];
                        $inser_calt->overall_drug_volume = $post['ispumpe_pumpe']['overall_drug_volume'];
                        $inser_calt->liquid_amount = $post['ispumpe_pumpe']['liquid_amount'];
                        $inser_calt->overall_running_time = $post['ispumpe_pumpe']['overall_running_time'];
                        $inser_calt->min_running_time = $post['ispumpe_pumpe']['min_running_time'];
                        $inser_calt->bolus = $post['ispumpe_pumpe']['bolus'];
                        $inser_calt->max_bolus_day = $post['ispumpe_pumpe']['max_bolus_day'];
                        $inser_calt->max_bolus_after = $post['ispumpe_pumpe']['max_bolus_after'];
                        $inser_calt->next_bolus = $post['ispumpe_pumpe']['next_bolus'];
                        $inser_calt->pumpe_medication_type = $post['ispumpe_pumpe']['pumpe_medication_type'];
                        $inser_calt->status = "edit";
                        $inser_calt->save();
        
                        $recordid_cocktail_alt = $inser_calt->id;
                        
                        $old_entry="";
                        $old_entry = "Zielvolumen Pumpe (ml): " . $cocktail->overall_volume."";
                        $old_entry .= "\ngewünschte Laufrate (ml/h): " .$cocktail->run_rate;
                        $old_entry .= "\nTrägerlösung: " .$cocktail->used_liquid;
                        $old_entry .= "\nGewicht (kg): " .$cocktail->pat_weight;
                        $old_entry .= "\nLaufzeit (ohne Bolus) in h: " .$cocktail->overall_running_time;
                        $old_entry .= "\nLaufzeit min. (mit Bolus): " .$cocktail->min_running_time;
                        $old_entry .= "\nBolusmenge (in ml): " .$cocktail->bolus;
                        $old_entry .= "\nmax Bolus pro Tag: " .$cocktail->max_bolus_day;
                        $old_entry .= "\nmax Bolus hintereiander: " .$cocktail->max_bolus_after;
                        $old_entry .= "\nSperrzeit (in Min.): " .$cocktail->next_bolus;
                    
                        
                        $new_entry="";
                        $new_entry = "Zielvolumen Pumpe (ml): " . $post['ispumpe_pumpe']['overall_volume']."";
                        $new_entry .= "\ngewünschte Laufrate (ml/h): " .$post['ispumpe_pumpe']['run_rate'];
                        $new_entry .= "\nTrägerlösung: " .$post['ispumpe_pumpe']['used_liquid'];
                        $new_entry .= "\nGewicht (kg): " .$post['ispumpe_pumpe']['pat_weight'];
                        $new_entry .= "\nLaufzeit (ohne Bolus) in h: " .$post['ispumpe_pumpe']['overall_running_time'];
                        $new_entry .= "\nLaufzeit min. (mit Bolus): " .$post['ispumpe_pumpe']['min_running_time'];
                        $new_entry .= "\nBolusmenge (in ml): " .$post['ispumpe_pumpe']['bolus'];
                        $new_entry .= "\nmax Bolus pro Tag: " .$post['ispumpe_pumpe']['max_bolus_day'];
                        $new_entry .= "\nmax Bolus hintereiander: " .$post['ispumpe_pumpe']['max_bolus_after'];
                        $new_entry .= "\nSperrzeit (in Min.): " .$post['ispumpe_pumpe']['next_bolus'];
                        
                        
                        $attach = "OHNE FREIGABE:" . $old_entry . "  \n -> \n  " .  $new_entry."";
                        $insert_pc = new PatientCourse();
                        $insert_pc->ipid = $ipid;
                        $insert_pc->course_date = date("Y-m-d H:i:s", time());
                        $insert_pc->course_type = Pms_CommonData::aesEncrypt("Q");
                        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt");
                        $insert_pc->recordid = $recordid_cocktail_alt;
                        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                        $insert_pc->user_id = $userid;
                        $insert_pc->save();

                        
                        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                        // SEND MESSAGE
                        $text  = "";
                        $text .= "Patient ".$patient_name." \n ";
                        $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
                        $text .= '' . $old_entry . '  -> ' .  $new_entry.'<br/>';
                        
                        $mess = Messages::medication_acknowledge_messages($ipid, $text);
                        
                        
                        // CREATE TODO
                        $text_todo  = "";
                        $text_todo .= "Patient ".$patient_name." <br/> ";
                        $text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/> ";
                        $text_todo .= '' . str_replace("\n","<br/>",$old_entry) . '  -> ' .  str_replace("\n","<br/>",$new_entry).' <br/>';
                        
                        $todos = Messages::pump_medication_acknowledge_todo($ipid, $text_todo, $post['ispumpe_pumpe']['id'], $recordid_cocktail_alt);
                    }
                }
            }
            else
            {
                $misc = "Medication change  Permission Error - Update schmertpumpe";
                PatientPermissions::MedicationLogRightsError(false,$misc);
            }
        }
        else
        {
            // INSERT NEW COCKTAIl WITH MEDICATIONS
        	

            if ($post['ispumpe'] == 1 && empty($post['ispumpe_pumpe']['id']))
            {
                
                //insert cocktail procedure
                $mc = new PatientDrugplanPumpe();
                $mc->userid = $userid;
                $mc->clientid = $clientid;
                $mc->ipid = $ipid;
                $mc->overall_volume = $post['ispumpe_pumpe']['overall_volume'];
                $mc->run_rate = $post['ispumpe_pumpe']['run_rate'];
                $mc->used_liquid = $post['ispumpe_pumpe']['used_liquid'];
                $mc->pat_weight = $post['ispumpe_pumpe']['pat_weight'];
                $mc->overall_drug_volume = $post['ispumpe_pumpe']['overall_drug_volume'];
                $mc->liquid_amount = $post['ispumpe_pumpe']['liquid_amount'];
                $mc->overall_running_time = $post['ispumpe_pumpe']['overall_running_time'];
                $mc->min_running_time = $post['ispumpe_pumpe']['min_running_time'];
                $mc->bolus = $post['ispumpe_pumpe']['bolus'];
                $mc->max_bolus_day = $post['ispumpe_pumpe']['max_bolus_day'];
                $mc->max_bolus_after = $post['ispumpe_pumpe']['max_bolus_after'];
                $mc->next_bolus = $post['ispumpe_pumpe']['next_bolus'];
                $mc->pumpe_medication_type = $post['ispumpe_pumpe']['pumpe_medication_type'];
                if(!empty($post['ispumpe_pumpe']['source_ipid'])){
                    $mc->source_pumpe_id = $post['ispumpe_pumpe']['source_pumpe_id'];
                    $mc->source_ipid = $post['ispumpe_pumpe']['source_ipid'];
                }
                $mc->save();
            
                //get cocktail id
                $pumpe_id = $mc->id;

                foreach ($post['hidd_medication'] as $key => $val)
                {
                    if ($post['hidd_medication'][$key] > 0)
                    {
                        $medid = $post['hidd_medication'][$key];
                    }
                    else
                    {
                    	$medid = $post['newhidd_medication'][$key];
                    }

                    
                    if ($medid > 0)
                    {
                    	//ISPC-2554 Carmen pct.3 27.03.2020
                    	$medmasterid = $medid;
                    	//--
        				$cust = new PatientDrugPlan();
        				$cust->ipid = $ipid;
        				if(is_array($post['dosage'][$key]))
        				{ // NEW style
        				
        				foreach ($post['dosage'][$key] as $time => $dosage_value)
        				{
        				    if(strlen($dosage_value) ==  0){
        				        $dosage_value = "";
        				    }
       				        $old_dosage_array[$key][] = $dosage_value;
        				}
        				    $cust->dosage = implode("-",$old_dosage_array[$key]);;
        				}
        				else
        				{ // OLD style
        				    $cust->dosage  = $post['dosage'][$key];
        				    $cust->dosage_interval  = isset($post[$key]['dosage_interval']) ? $post[$key]['dosage_interval'] : null;
        				}
        				
        				$cust->medication_master_id = $medid;
        				$cust->isbedarfs = $post['isbedarfs'];
        				$cust->iscrisis = $post['iscrisis'];
        				$cust->isivmed = $post['isivmed'];
    				    $cust->isschmerzpumpe = 0;
    				    $cust->cocktailid = 0;
        				if ($post['ispumpe'] == 1)
        				{
        				    $cust->ispumpe = $post['ispumpe'];
        				    $cust->pumpe_id = $pumpe_id;
        				}
        				$cust->treatment_care = $post['treatment_care'];
        				$cust->isnutrition = $post['isnutrition'];
    
        				$cust->verordnetvon = $post['verordnetvon'][$key];
        				$cust->comments = $post['comments'][$key];
    
        				if($post['done_date'])
        				{
        				    $cust->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
        				}
        				else
        				{
        				    $cust->medication_change = date('Y-m-d 00:00:00');
        				}
        				


        				if(!empty($post['source_ipid'][$key]) &&  $post['source_ipid'][$key] != $ipid ){
        				
        				    $cust->source_ipid = $post['source_ipid'][$key];
        				
        				    if(!empty($post['source_drugplan_id'][$key])){
        				
        				        $cust->source_drugplan_id = $post['source_drugplan_id'][$key];
        				    }
        				}
        				
        				$cust->save();
        				$inserted_id = $cust->id;
        				$insertedIds[] = $cust->id;
        				//ISPC-2554 Carmen pct.3 27.03.2020
        				$drugplanid = $inserted_id;
        				//--
        				
        				// Insert dosage
        				if(is_array($post['dosage'][$key]))
        				{ // NEW style
            				foreach ($post['dosage'][$key] as $time => $dosage_value)
            				{
            				    //  insert new lines
            				    $insert_pdd = new PatientDrugPlanDosage();
            				    $insert_pdd->ipid = $ipid;
            				    $insert_pdd->drugplan_id = $inserted_id;
            				    $insert_pdd->dosage = $dosage_value;
            				    //TODO-3624 Ancuta 23.11.2020
            				    $insert_pdd->dosage_full = $post['dosage_full'][$key][$time];
            				    $insert_pdd->dosage_concentration = $post['dosage_concentration'][$key][$time];
            				    $insert_pdd->dosage_concentration_full = $post['dosage_concentration_full'][$key][$time];
            				    //--
            				    $insert_pdd->dosage_time_interval = $time.":00";
            				    $insert_pdd->save();
            				}
        				}
        				
        				// Insert extra data
        				$insert_pde = new PatientDrugPlanExtra();
        				$insert_pde->ipid = $ipid;
        				$insert_pde->drugplan_id = $inserted_id;
        				$insert_pde->drug = $post['drug'][$key];
        				$insert_pde->unit = $post['unit'][$key];
        				$insert_pde->overall_dosage_h = $post['overall_dosage_h'][$key];
        				$insert_pde->overall_dosage_24h = $post['overall_dosage_24h'][$key];
        				$insert_pde->overall_dosage_pump = $post['overall_dosage_pump'][$key];
        				$insert_pde->drug_volume = $post['drug_volume'][$key];
        				$insert_pde->unit2ml = $post['unit2ml'][$key];
        				$insert_pde->bolus_per_med = $post['bolus_per_med'][$key];
        				$insert_pde->type = $post['type'][$key];
        				$insert_pde->indication = trim($post['indication'][$key]);
        				$insert_pde->importance = $post['importance'][$key];
        				$insert_pde->dosage_form = $post['dosage_form'][$key];
        				$insert_pde->concentration  = $post['concentration'][$key];
        				$insert_pde->save();
        				
        				
    
    
        				// this is for  Medication acknowledge
        				if(in_array($userid,$approval_users) && $acknowledge == "1" ){
        				    // NEW ENTRY

        				    if($post['ispumpe'] == 1 )
        				    {
        				        $shortcut = "Q";
        				        $prefix = "Perfusor/Pumpe ";
        				    }
        				    else
        				    {
        				        $shortcut = "M";
        				    }
       				        $new_med = Doctrine::getTable('Medication')->find($medid);
        				    $new_medication_name[$key] = $new_med->name;
        				    // new dosage
        				    $new_medication_dosage[$key] = $post['dosage'][$key];
        				    // new comments
        				    $new_medication_comments[$key] = $post['comments'][$key];
        				    // new change date
        				    $medication_change_date_str[$key]= date("d.m.Y",time());
    
        				    if(strlen($new_medication_dosage[$key])>0)
        				    {
        				        $new_entry[$key] = $prefix.$new_medication_name[$key]."  |  ".$new_medication_dosage[$key]." | ". $new_medication_comments[$key]." | ".$medication_change_date_str[$key];
        				    }
        				    else
        				    {
        				        $new_entry[$key] = $prefix.$new_medication_name[$key]." | ".$new_medication_comments[$key]." | ".$medication_change_date_str[$key];
        				    }
    
        				    $attach = $new_entry[$key].'';
        				    $insert_pc = new PatientCourse();
        				    $insert_pc->ipid = $ipid;
        				    $insert_pc->course_date = date("Y-m-d H:i:s", time());
        				    $insert_pc->course_type = Pms_CommonData::aesEncrypt($shortcut);
        				    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
        				    $insert_pc->recordid = $inserted_id;
        				    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
        				    $insert_pc->user_id = $userid;
        				    $insert_pc->save();
        				    
							//TODO-2850  ISPC: Todo is not marked as ready
        				    $text_todo = "";
        				    $todos = Messages::remove_pump_medication_acknowledge_todo($ipid, $text_todo, $pumpe_id, $recordid_cocktail_alt);
        				    
        				}
                    }
                    //ISPC-2554 pct.3 Carmen 27.03.2020
                    $atcarr = (array)json_decode(html_entity_decode($post[$key]['atc']));
                     
                    $atcid = array_search($drugplanid, array_column($atcdet, 'drugplan_id'));
                    
                    if($atcid !== false)
                    {
                    	if($atcdet[$atcid]['atc_code'] != $atcarr['atc_code'])
                    	{
                    		$todelete[] = $atcdet[$atcid]['id'];
                    		 
                    		$toupdate[$atcindex]['ipid'] = $ipid;
                    		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                    		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                    		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                    		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                    		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                    		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                    		$atcindex++;
                    	}
                    }
                    else
                    {
                    	if(!empty($atcarr))
                    	{
                    		$toupdate[$atcindex]['ipid'] = $ipid;
                    		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                    		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                    		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                    		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                    		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                    		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                    		$atcindex++;
                    	}
                    }
                    //--
                }                
            }
            else
            {
                // UPDATE MEDICATION  - ALONG WITH COCKTAIL DETAILS
                foreach ($post['hidd_medication'] as $keym => $valm)
                {
                    $update_sh_medication[$keym] = "0";
        
                    if ($post['hidd_medication'][$keym] > 0)
                    {
                        $medid = $post['hidd_medication'][$keym];
                    }
                    else
                    {
                        $medid = $post['newhidd_medication'][$keym];
                    }
                    //ISPC-2554 Carmen pct.3 27.03.2020
                    $medmasterid = $medid;
                    //--
                    
                    // DOSAJE
                    $post_dosaje[$keym] = "";
                    $post_dosage_interval[$keym] = null;
                    
                    if(is_array($post['dosage'][$keym]))
                    { // NEW style
                        foreach ($post['dosage'][$keym] as $time => $dosage_value)
                        {
                            if(strlen($dosage_value) == 0){
                                // $dosage_value = " / ";
                                $dosage_value = "";
                            }
                        
                            $old_dosage_array[$keym][] = $dosage_value;
                        }
                        $post_dosaje[$keym] = implode("-",$old_dosage_array[$keym]);
                    }
                    else
                    { // OLD style
                        $post_dosaje[$keym] = $post['dosage'][$keym];
                        $post_dosage_interval[$keym] = isset($post[$keym]['dosage_interval']) ? $post[$keym]['dosage_interval'] : null;
                    }
                    
                    if ($post['drid'][$keym] > 0)
                    {
                        $cust = Doctrine::getTable('PatientDrugPlan')->find($post['drid'][$keym]);
                        
                        // get exra data
                        $extra_array = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid,$post['drid'][$keym]);
                        $extra_data = $extra_array[$post['drid'][$keym]];
                        
                        if ($cust){
                        	//ISPC-2554 Carmen pct.3 27.03.2020
                        	$drugplanid = $cust->id;
                        	//--
                            $existing_string = "";
                            $existing_string .= trim($cust->dosage);                            
                            $existing_string .= trim($cust->medication_master_id);
                            $existing_string .= trim($extra_data['drug']);
                            $existing_string .= trim($extra_data['overall_dosage_h']);   
                            $existing_string .= trim($extra_data['overall_dosage_24h']);        
                            $existing_string .= trim($extra_data['overall_dosage_pump']);    
                            $existing_string .= trim($extra_data['drug_volume']);    
                            $existing_string .= trim($extra_data['unit2ml']);    
                            $existing_string .= trim($extra_data['bolus_per_med']);    
                            $existing_string .= trim($extra_data['unit_id']);
                            $existing_string .= trim($extra_data['indication_id']);
                            //$existing_date = strtotime(date('d.m.Y',strtotime($cust->medication_change)));
  
                            
                            $post_string = "";
                            $post_string .= trim($post['dosage'][$keym]);
                            $post_string .= trim($medid);
                            $post_string .= trim($post['drug'][$keym]);
                            
                            $post_string .= trim($post['overall_dosage_h'][$keym]);
                            $post_string .= trim($post['overall_dosage_24h'][$keym]);
                            $post_string .= trim($post['overall_dosage_pump'][$keym]);
                            $post_string .= trim($post['drug_volume'][$keym]);
                            $post_string .= trim($post['unit2ml'][$keym]);
                            $post_string .= trim($post['bolus_per_med'][$keym]);
                            $post_string .= trim($post['unit'][$keym]);
                            $post_string .= trim($post['indication'][$keym]);
                            
                            //$post_date = strtotime($post['medication_change'][$keym]);
                            

//                             if( ($existing_string != $post_string)  && $post['edited'][$keym] == "1")
                            if( ($existing_string != $post_string) )
                            {//check to update only what's modified
                                $update_sh_medication[$keym] = "1";
                                $medication_change_date[$keym] = date('Y-m-d 00:00:00');
                           
                            } else{
                                $update_sh_medication[$keym] = "0";
                            }
                            /* ================= Save in patient drugplan history ====================*/
                            
                            
                            if( $update_sh_medication[$keym] == "1")                                
                            {
                                $old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
                                $medication_old_medication_name[$keym] = $old_med->name;
                                $medication_old_medication_id[$keym] =  $old_med->id;
        
//                                 $cocktail = Doctrine::getTable('PatientDrugplanPumpe')->find($cust->pumpe_id);
                                $history = new PatientDrugPlanHistory();
                                $history->ipid = $ipid;
                                $history->pd_id = $cust->id;
                                $history->pd_medication_master_id = $cust->medication_master_id ;
                                $history->pd_medication_name = $medication_old_medication_name[$keym] ;
                                $history->pd_medication =  $cust->medication;
                                $history->pd_dosage = $cust->dosage;
                                $history->pd_dosage_interval = isset($cust->dosage_interval) ? $cust->dosage_interval : null;
                                $history->pd_dosage_product = isset($cust->dosage_product) ? $cust->dosage_product : null;
                                $history->pd_comments = $cust->comments ;
                                $history->pd_isbedarfs = $cust->isbedarfs;
                                $history->pd_iscrisis = $cust->iscrisis;
                                $history->pd_isivmed = $cust->isivmed;
                                $history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
                                $history->pd_cocktailid= $cust->cocktailid;
                                $history->pd_ispumpe = $cust->ispumpe;
                                $history->pd_pumpe_id= $cust->pumpe_id;
                                $history->pd_treatment_care = $cust->treatment_care;
                                $history->pd_isnutrition = $cust->isnutrition;
                                
//                                 $history->pd_cocktail_comment = $cocktail->description ;
//                                 $history->pd_cocktail_bolus = $cocktail->bolus;
//                                 $history->pd_cocktail_max_bolus = $cocktail->max_bolus;
//                                 $history->pd_cocktail_flussrate =$cocktail->flussrate;
//                                 $history->pd_cocktail_sperrzeit =$cocktail->sperrzeit;
                                
                                $history->pd_edit_type = $cust->edit_type;
                                $history->pd_verordnetvon = $cust->verordnetvon;
                                $history->pd_medication_change = $cust->medication_change;
                                $history->pd_create_date = $cust->create_date;
                                $history->pd_create_user = $cust->create_user;
                                $history->pd_change_date = $cust->change_date;
                                $history->pd_change_user = $cust->change_user;
                                $history->pd_isdelete = $cust->isdelete;
                                $history->pd_days_interval_technical = isset($cust->days_interval_technical) ? $cust->days_interval_technical : null;
                                $history->save();
                                
                                $history_id =$history->id; 
                                
                                $extra_history_array = PatientDrugPlanExtra::get_patient_all_drugplan_extra($ipid,$post['drid'][$keym]);
                                
                                if(!empty($extra_history_array))
                                {
                                    foreach($extra_history_array as $extra_id =>$extra_data){
                                
                                        $history_pde = new PatientDrugPlanExtraHistory();
                                        $history_pde->ipid = $ipid;
                                        $history_pde->pde_id = $extra_data['id'];
                                        $history_pde->history_id = $history_id;
                                
                                        $history_pde->pde_drugplan_id = $post['drid'][$keym];;
                                        $history_pde->pde_drug = $extra_data['drug'];
                                        $history_pde->pde_unit = $extra_data['unit'];
                                        
                                        $history_pde->pde_overall_dosage_h = $extra_data['overall_dosage_h']; 
                                        $history_pde->pde_overall_dosage_24h = $extra_data['overall_dosage_24h'];  
                                        $history_pde->pde_overall_dosage_pump= $extra_data['overall_dosage_pump'];
                                        
                                        $history_pde->pde_drug_volume = $extra_data['drug_volume']; 
                                        $history_pde->pde_unit2ml = $extra_data['unit2ml']; 
                                        $history_pde->pde_bolus_per_med = $extra_data['bolus_per_med']; 
                                        
                                        $history_pde->pde_type = $extra_data['type'];
                                        $history_pde->pde_indication = trim($extra_data['indication']);
                                        $history_pde->pde_importance = $extra_data['importance'];
                                        
                                        $history_pde->pde_dosage_form = $extra_data['dosage_form'];
                                        $history_pde->pde_concentration = $extra_data['concentration'];

                                        $history_pde->pde_isdelete	= $extra_data['isdelete'];
                                
                                        $history_pde->pde_create_user = $extra_data['create_user'];
                                        $history_pde->pde_create_date = $extra_data['create_date'];
                                        $history_pde->pde_change_user = $extra_data['change_user'];
                                        $history_pde->pde_change_date = $extra_data['change_date'];
                                        $history_pde->save();
                                    }
                                }
                                
                                /*
                                TODO-1037 medication new - it is not possible to change medication name 
                                */
                                if ( $post['medication'][$keym] != $old_med->name 
                                		|| $post[$keym]['pzn'] != $old_med->pzn
                                		|| $post[$keym]['source'] != $old_med->source
                                		|| $post[$keym]['dbf_id'] != $old_med->dbf_id ) 
                                {
                                	
                                	$medication_obj =  new Medication();
                                	$medication_new_id = $medication_obj->set_new_record(array(
                                			'clientid' => $clientid,
                                			'name' => $post['medication'][$keym],
                                			'pzn' => $post[$keym]['pzn'],
                                			'source' => $post[$keym]['source'],
                                			'dbf_id' => $post[$keym]['dbf_id'],
                                			'unit' => $post['unit'][$keym],
                                	
                                			)
                                	);
                                	
                                	$medid = $medication_new_id;
                                	
                                	//ISPC-2554 Carmen pct.3 27.03.2020
                                	$medmasterid = $medid;
                                	//--
                                }
                                
                            }

                            /* ================= Update patient drugplan item====================*/
                            if($update_sh_medication[$keym] == "1"){
                            	
                            	//TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
                            	// update extra data
                            	if(!empty($extra_data))
                            	{
                            	
                            		$existing_extra_id = 0;
                            		 
                            		$extra_drugs_q = Doctrine_Query::create()
                            		->select('id')
                            		->from('PatientDrugPlanExtra')
                            		->where("ipid = '" . $ipid . "'")
                            		->andWhere("drugplan_id = '" . $post['drid'][$keym] . "'")
                            		->andWhere("isdelete = '0'")
                            		->orderBy("create_date DESC")
                            		->limit(1);
                            		$extra_drugs_array = $extra_drugs_q->fetchArray();
                            	
                            	
                            		if(!empty($extra_drugs_array)){
                            			$existing_extra_id = $extra_drugs_array[0]['id'];
                            	
                            			$update_pde = Doctrine::getTable('PatientDrugPlanExtra')->find($existing_extra_id);
                            			$update_pde->drug = $post['drug'][$keym];
                            			$update_pde->unit = $post['unit'][$keym];
                            			$update_pde->overall_dosage_h = $post['overall_dosage_h'][$keym];
                            			$update_pde->overall_dosage_24h = $post['overall_dosage_24h'][$keym];
                            			$update_pde->overall_dosage_pump = $post['overall_dosage_pump'][$keym];
                            			$update_pde->drug_volume = $post['drug_volume'][$keym];
                            			$update_pde->unit2ml = $post['unit2ml'][$keym];
                            			$update_pde->bolus_per_med = $post['bolus_per_med'][$keym];
                            			$update_pde->indication = trim($post['indication'][$keym]);
                            			$update_pde->importance = $post['importance'][$keym];
                            			$update_pde->save();
                            			
          
                            		}
                            	}
                            	else
                            	{
                            		// add extra data
                            		$cust_pde = new PatientDrugPlanExtra();
                            		$cust_pde->ipid = $ipid;
                            		$cust_pde->drugplan_id = $post['drid'][$keym];
                            		$cust_pde->drug = $post['drug'][$keym];
                            		$cust_pde->unit = $post['unit'][$keym];
                            		$cust_pde->overall_dosage_h = $post['overall_dosage_h'][$keym];
                            		$cust_pde->overall_dosage_24h = $post['overall_dosage_24h'][$keym];
                            		$cust_pde->overall_dosage_pump = $post['overall_dosage_pump'][$keym];
                            		$cust_pde->drug_volume = $post['drug_volume'][$keym];
                            		$cust_pde->unit2ml = $post['unit2ml'][$keym];
                            		$cust_pde->bolus_per_med = $post['bolus_per_med'][$keym];
                            		$cust_pde->type = $post['type'][$keym];
                            		$cust_pde->indication = trim($post['indication'][$keym]);
                            		$cust_pde->importance = $post['importance'][$keym];
                            		$cust_pde->save();
                            	}
                            	//--
                                
                                $cust->ipid = $ipid;
                                $cust->dosage = $post_dosaje[$keym]; // this is defined at the begining of the function
                                $cust->dosage_interval = isset($post_dosage_interval[$keym]) ? $post_dosage_interval[$keym] : null; // this is defined at the begining of the function
                                $cust->medication_master_id = $medid;
//                                 $cust->verordnetvon = $post['verordnetvon'][$keym];
                                $cust->medication_change= $medication_change_date[$keym];

                                if(strlen($post['source_ipid'][$keym]) > 0  && $post['source_ipid'][$keym] != $ipid ){
                                    $cust->source_ipid = $post['source_ipid'][$keym];
                                
                                    if(strlen($post['source_drugplan_id'][$keym]) > 0 ){
                                        $cust->source_drugplan_id = $post['source_drugplan_id'][$keym];
                                    }
                                }
                                $cust->save();
                                
                                $drugplanid = $cust->id;
                                
                                // IF An approval user edits a medications that is not approved yet - all data is marcked as inactive for this medication
                                if(in_array($userid,$approval_users) && $acknowledge == "1" )
                                {
                                    $clear = $this->update_pdpa($ipid, $post['drid'][$keym]);
                                }
                            }
                        }
                    }
                    else if (!empty($post['medication'][$keym]))
                    {
        
                        $insert_new_sch = new PatientDrugPlan();
                        $insert_new_sch->ipid = $ipid;
                        $insert_new_sch->dosage = $post_dosaje[$keym];
                        $insert_new_sch->dosage_interval = isset($post_dosage_interval[$keym]) ? $post_dosage_interval[$keym] : null;
                        $insert_new_sch->medication_master_id = $medid;
//                         $insert_new_sch->verordnetvon = $post['verordnetvon'][$keym];
                        // medication_change
                        if(!empty($post['medication_change'][$keym]))
                        {
                            $insert_new_sch->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
                        }
                        elseif(!empty($post['done_date']))
                        {
                            $insert_new_sch->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
                        }
                        else
                        {
                            $insert_new_sch->medication_change = date('Y-m-d 00:00:00');
                        }
        
                        $insert_new_sch->isbedarfs = 0;
                        $insert_new_sch->isivmed = 0;
                        $insert_new_sch->isschmerzpumpe = 0;
                        $insert_new_sch->cocktailid = 0;
                        $insert_new_sch->ispumpe = 1;
                        $insert_new_sch->pumpe_id = $post['ispumpe_pumpe']['id'];
                        if(strlen($post['source_ipid'][$keym]) > 0  && $post['source_ipid'][$keym] != $ipid ){
                            $insert_new_sch->source_ipid = $post['source_ipid'][$keym];
                        
                            if(strlen($post['source_drugplan_id'][$keym]) > 0 ){
                                $insert_new_sch->source_drugplan_id = $post['source_drugplan_id'][$keym];
                            }
                        }
                        
                        $insert_new_sch->save();
                        $schm_id = $insert_new_sch->id;
                        //ISPC-2554 Carmen pct.3 27.03.2020
                        $drugplanid = $schm_id;
                        //--
                        // add extra data
                        $insert_new_sch_pde = new PatientDrugPlanExtra();
                        $insert_new_sch_pde->ipid = $ipid;
                        $insert_new_sch_pde->importance = $post['importance'][$keym];
                        $insert_new_sch_pde->drugplan_id = $schm_id;
                        $insert_new_sch_pde->drug = $post['drug'][$keym];
                        $insert_new_sch_pde->unit = $post['unit'][$keym];
                        $insert_new_sch_pde->indication = trim($post['indication'][$keym]);
                        $insert_new_sch_pde->overall_dosage_h = $post['overall_dosage_h'][$keym];
                        $insert_new_sch_pde->overall_dosage_24h = $post['overall_dosage_24h'][$keym];
                        $insert_new_sch_pde->overall_dosage_pump = $post['overall_dosage_pump'][$keym];
                        $insert_new_sch_pde->drug_volume = $post['drug_volume'][$keym];
                        $insert_new_sch_pde->unit2ml = $post['unit2ml'][$keym];
                        $insert_new_sch_pde->bolus_per_med = $post['bolus_per_med'][$keym];
                        $insert_new_sch_pde->save();
                    }
                    
                    //ISPC-2554 pct.3 Carmen 27.03.2020
                    $atcarr = (array)json_decode(html_entity_decode($post[$keym]['atc']));
                     
                    $atcid = array_search($drugplanid, array_column($atcdet, 'drugplan_id'));
                    
                    if($atcid !== false)
                    {
                    	if($atcdet[$atcid]['atc_code'] != $atcarr['atc_code'])
                    	{
                    		$todelete[] = $atcdet[$atcid]['id'];
                    		 
                    		$toupdate[$atcindex]['ipid'] = $ipid;
                    		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                    		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                    		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                    		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                    		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                    		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                    		$atcindex++;
                    	}
                    }
                    else
                    {
                    	if(!empty($atcarr))
                    	{
                    		$toupdate[$atcindex]['ipid'] = $ipid;
                    		$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
                    		$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
                    		$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
                    		$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
                    		$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
                    		$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
                    		$atcindex++;
                    	}
                    }
                    //--
                }
                
                if(in_array($userid,$approval_users) && $acknowledge == "1" ){
                    $this->update_pdpca($ipid,array($post['ispumpe_pumpe']['id']));
                }
                
                //update pumpe
                $cust_update_cocktail = Doctrine::getTable('PatientDrugplanPumpe')->find($post['ispumpe_pumpe']['id']);
                $cust_update_cocktail->ipid = $ipid;
                $cust_update_cocktail->userid = $userid;
                $cust_update_cocktail->clientid = $clientid;
                
                $cust_update_cocktail->overall_volume = $post['ispumpe_pumpe']['overall_volume'];
                $cust_update_cocktail->run_rate = $post['ispumpe_pumpe']['run_rate'];
                $cust_update_cocktail->used_liquid = $post['ispumpe_pumpe']['used_liquid'];
                $cust_update_cocktail->pat_weight = $post['ispumpe_pumpe']['pat_weight'];
                $cust_update_cocktail->overall_drug_volume = $post['ispumpe_pumpe']['overall_drug_volume'];
                $cust_update_cocktail->liquid_amount = $post['ispumpe_pumpe']['liquid_amount'];
                $cust_update_cocktail->overall_running_time = $post['ispumpe_pumpe']['overall_running_time'];
                $cust_update_cocktail->min_running_time = $post['ispumpe_pumpe']['min_running_time'];
                $cust_update_cocktail->bolus = $post['ispumpe_pumpe']['bolus'];
                $cust_update_cocktail->max_bolus_day = $post['ispumpe_pumpe']['max_bolus_day'];
                $cust_update_cocktail->max_bolus_after = $post['ispumpe_pumpe']['max_bolus_after'];
                $cust_update_cocktail->next_bolus = $post['ispumpe_pumpe']['next_bolus'];
                $cust_update_cocktail->pumpe_medication_type = $post['ispumpe_pumpe']['pumpe_medication_type'];

                $cust_update_cocktail->save();
                
                
                 //TODO-2850+TODO-3620  ISPC: Todo is not marked as ready / Ancuta 20.11.2020
                if(in_array($userid,$approval_users) && $acknowledge == "1" ){
                    $text_todo = "";
                    $todos = Messages::remove_ispumpe_pump_medication_acknowledge_todo($ipid, $text_todo, $post['ispumpe_pumpe']['id']);
                }
            }
        }
        
        //ISPC-2554 pct.3 Carmen 27.03.2020
        if(!empty($todelete))
        {
        	$querydel =  PatientDrugPlanAtcTable::getInstance()->createQuery('atc')
        	->delete()
        	->whereIn('atc.id', $todelete);
        	$querydel->execute();
        }
         
         
        if(!empty($toupdate))
        {
        	$atccollection = new Doctrine_Collection('PatientDrugPlanAtc');
        	$atccollection->fromArray($toupdate);
        	$atccollection->save();
        }
    }
    // --
    
    
    public function clear_dosage($ipid,$drugplan_id)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        if(!empty($ipid) && !empty($drugplan_id))
        {
            $loc = Doctrine_Query::create()
            ->update("PatientDrugPlanDosage")
            ->set('isdelete', "1")
            ->set('change_date', '"'.date("Y-m-d H:i:s", time()).'"')
            ->set('change_user', $userid)
            ->where("drugplan_id = '" .$drugplan_id . "'")
            ->andWhere("ipid = '" .$ipid . "'");
            $loc->execute();        
        }
    }
    
    public function clear_dosage_alt($ipid,$drugplan_id_alt)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        if(!empty($ipid) && !empty($drugplan_id_alt))
        {
            $loc = Doctrine_Query::create()
            ->update("PatientDrugPlanDosage")
            ->set('isdelete', "1")
            ->set('change_date', '"'.date("Y-m-d H:i:s", time()).'"')
            ->set('change_user', $userid)
            ->where("drugplan_id_alt = '" .$drugplan_id_alt . "'")
            ->andWhere("ipid = '" .$ipid . "'");
            $loc->execute();        
        }
    }

    
    public function save_medication($ipid =  '' , $data = array())
    {
    	$logininfo = $this->logininfo;
    	
    	if(empty($ipid) || empty($data)) {
    		return;
    	}

    	if( is_null($logininfo) || ! isset($data['clientid']) ||  ! isset($data['userid']) ){
    		$logininfo = new Zend_Session_Namespace('Login_Info');
    		$data['clientid'] = $logininfo->clientid;
    		$data['userid'] = $logininfo->userid;
    	}
    	
    	$patient_medication_form = new Application_Form_Medication();
    	$patient_medication_isnutrition_form = new Application_Form_Nutrition();
    	$patient_medication_tr_form = new Application_Form_MedicationTreatmentCare();
    	
    	$modules = new Modules();
    	$acknowledge = "0";
    	if($modules->checkModulePrivileges("111", $data['clientid']))//Medication acknowledge
    	{
    		$acknowledge = "1";
    	}    	
    	
    	//TODO-3582 + TODO-3579 Ancuta 11.11.2020
    	$approval_users = MedicationApprovalUsers::get_medication_approval_users($data['clientid'],true);
    	$change_users = MedicationChangeUsers::get_medication_change_users($data['clientid'],true);
		// --    	

    	/*===============================*/
    	$medic = new PatientDrugPlan();
    	$smparr = $medic->getSchmerzpumpeMedication(0,0,$ipid);
    	
    	foreach($smparr as $smpMedication)
    	{
    		$smpMedicationArr[] = $smpMedication['cocktailid'];
    	}
    	$smpMedicationArray = array_values(array_unique($smpMedicationArr));
    	
    	$cocktails = new PatientDrugPlanCocktails();
    	$cocktails = $cocktails->getDrugCocktails($smpMedicationArray);
    	 
    	
    	
    	/*===============================*/
    	/* update dosages hours:minutes  Dosierung from top of the table*/
    	/*===============================*/
    	if (!empty($data['interval'])) {
    		$dosage_column_inputs_array = array(
    				'interval' => $data['interval'],
    				'deleted_intervals_ids' => $data['deleted_intervals_ids'],
    				'ipid' => $ipid
    		);
    		$drugplan_intervals_form = new Application_Form_PatientDrugPlanDosageIntervals();
    			
    		if($drugplan_intervals_form->validate_v2($dosage_column_inputs_array))
    		{
    			$drugplan_intervals_form->insert_data($dosage_column_inputs_array);
    		}
    		else
    		{
    		}
    	}
    	
    	foreach($data['medication_block'] as $type => $med_values)
    	{
    		if($type == "isschmerzpumpe")
    		{
    			foreach($med_values as $pumpe_number=>$sch_med_values)
    			{
    				$sch_post_data = $sch_med_values;
    				foreach($sch_med_values['medication'] as $amedikey => $amedi)
    				{
    					if(strlen($amedi) > 0 && empty($sch_med_values['hidd_medication'][$amedikey]) && !empty($sch_med_values['drid'][$amedikey]) && !empty($sch_med_values['medication'][$amedikey]))
    					{
    	
    						$sch_post_data['newmids'][$amedikey] = $sch_med_values['drid'][$amedikey];
    						$sch_post_data['newmedication'][$amedikey] = $amedi;
    					}
    	
    					if(strlen($amedi) > 0 && (!empty($sch_med_values['hidd_medication'][$amedikey]) && empty($sch_med_values['drid'][$amedikey]) && !empty($sch_med_values['medication'][$amedikey])))
    					{
    						$sch_post_data['newmids'][$amedikey] = $sch_med_values['hidd_medication'][$amedikey];
    						$sch_post_data['newmedication'][$amedikey] = $amedi;
    					}
    	
    					if(strlen($amedi) > 0 && (empty($sch_med_values['hidd_medication'][$amedikey]) && empty($sch_med_values['drid'][$amedikey]) && !empty($sch_med_values['medication'][$amedikey])))
    					{
    						$sch_post_data['newmedication'][$amedikey] = $amedi;
    					}
    				}
    	
    				if(is_array($sch_post_data['newmedication']))
    				{
    					$dts = $patient_medication_form->InsertNewData($sch_post_data);
    					foreach($dts as $key => $dt)
    					{
    						$sch_post_data['newhidd_medication'][$key] = $dt->id;
    					}
    				}
    				$sch_post_data[$type] =  "1";
    				$sch_post_data['ipid'] =  $ipid;
    				if($acknowledge =="1")
    				{
    					$sch_post_data['skip_trigger'] = "1";
    				}
    	
    	
    				// save data for each pumpe
    				$this->update_schmerzpumpe_data($sch_post_data);
    	
    	
    				//find out edited/added medis
    				foreach($sch_med_values['medication'] as $k_meds => $v_meds)
    				{
    					$cust = Doctrine::getTable('PatientDrugPlan')->find($sch_med_values['drid'][$k_meds]);
    					$list = true; //list curent medi
    	
    					if($cust)
    					{
    						$post_sch[$k_meds]= "";
    						$existing_sch[$k_meds]= "";
    	
    						$post_sch[$k_meds] = trim($sch_med_values['dosage'][$k_meds]);
    						$post_sch[$k_meds] .= trim($sch_med_values['hidd_medication'][$k_meds]);
    						$post_sch[$k_meds] .= trim($sch_med_values['verordnetvon'][$k_meds]);
    	
    	
    						$existing_sch[$k_meds] = trim($cust->dosage);
    						$existing_sch[$k_meds] .= trim($cust->medication_master_id);
    						$existing_sch[$k_meds] .= trim($cust->verordnetvon);
    	
    	
    						/* if($cust->dosage != $sch_med_values['dosage'][$k_meds] ||
    						 $cust->medication_master_id != $sch_med_values['hidd_medication'][$k_meds] ||
    						$cust->verordnetvon != $sch_med_values['verordnetvon'][$k_meds]) */
    	
    						if($sch_med_values['edited'][$k_meds] == "1" && $post_sch[$k_meds]!= $existing_sch[$k_meds])
    						{
    							$list = false; //don`t list curent medi
    						}
    					}
    	
    					if(!array_key_exists($k_meds, $sch_post_data['newmedication']) && $list) //new medis
    					{
    						$meds[$sch_med_values['cocktail']['id']][] = $v_meds . " | " . $sch_med_values['dosage'][$k_meds] . "\n";
    					}
    				}
    	
					//TODO-3582 + TODO-3579 Ancuta 10.11.2020
    				if($list && ( $acknowledge == 0  ||  in_array($data['userid'],$approval_users)))
    				{
    					$current_sh_ck = $sch_med_values['cocktail']['id'];
    	
    					// check if current cocktail is differnt from post
    					$post_sch_cocktail[$current_sh_ck] ="";
    					$post_sch_cocktail[$current_sh_ck] .= trim($sch_med_values['cocktail']['description']);
    					$post_sch_cocktail[$current_sh_ck] .= trim($sch_med_values['cocktail']['pumpe_medication_type']);
    					$post_sch_cocktail[$current_sh_ck] .= trim($sch_med_values['cocktail']['flussrate']);
    					if(isset($sch_med_values['cocktail']['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020
        					$post_sch_cocktail[$current_sh_ck] .= trim($sch_med_values['cocktail']['flussrate_type']);     //ISPC-2684 Lore 08.10.2020
    					}
    					$post_sch_cocktail[$current_sh_ck] .= trim($sch_med_values['cocktail']['carrier_solution']);
    	
    					if($sch_med_values['cocktail']['pumpe_type'] == "pca") {
    						$post_sch_cocktail[$current_sh_ck] .=trim($sch_med_values['cocktail']['bolus']);
    						$post_sch_cocktail[$current_sh_ck] .= trim($sch_med_values['cocktail']['sperrzeit']);
    					}
    	
    	
    					$existing_sch_cocktail[$current_sh_ck] ="";
    					$existing_sch_cocktail[$current_sh_ck] .= trim($cocktails[$current_sh_ck]['description']);
    					$existing_sch_cocktail[$current_sh_ck] .= trim($cocktails[$current_sh_ck]['pumpe_medication_type']);
    					$existing_sch_cocktail[$current_sh_ck] .= trim($cocktails[$current_sh_ck]['flussrate']);
    					if(isset($sch_med_values['cocktail']['flussrate_type'])){//TODO-3582 + TODO-3579 Ancuta 09.11.2020 - add flusarte type - only id this e
        					$existing_sch_cocktail[$current_sh_ck] .= trim($cocktails[$current_sh_ck]['flussrate_type']);      //ISPC-2684 Lore 08.10.2020
    					}
    					$existing_sch_cocktail[$current_sh_ck] .= trim($cocktails[$current_sh_ck]['carrier_solution']);
    	
    					if($cocktails[$current_sh_ck]['pumpe_type'] == "pca") {
    						$existing_sch_cocktail[$current_sh_ck] .=trim($cocktails[$current_sh_ck]['bolus']);
    						$existing_sch_cocktail[$current_sh_ck] .= trim($cocktails[$current_sh_ck]['sperrzeit']);
    					}
    	
    	
    					if( $existing_sch_cocktail[$current_sh_ck] != $post_sch_cocktail[$current_sh_ck]){
    	
    	
    						$course_cocktail_entry ="";
    						$course_cocktail_entry .= "Kommentar: " . $sch_med_values['cocktail']['description'];
    						$course_cocktail_entry .= "\nApplikationsweg: " . $sch_med_values['cocktail']['pumpe_medication_type'];
    						//$course_cocktail_entry .= "\nFlussrate: " . $sch_med_values['cocktail']['flussrate'];
    						//ISPC-2684 Lore 08.10.2020
    						if(!empty($sch_med_values['cocktail']['flussrate_type'])){
    						    $course_cocktail_entry .= "\nFlussrate_simple"." (".$sch_med_values['cocktail']['flussrate_type']."): " . $sch_med_values['cocktail']['flussrate'];
    						}else{
    						    $course_cocktail_entry .= "\nFlussrate: " . $sch_med_values['cocktail']['flussrate'];
    						}
    						$course_cocktail_entry .= "\nTrägerlösung: " . $sch_med_values['cocktail']['carrier_solution'];
    	
    						if($sch_med_values['cocktail']['pumpe_type'] == "pca") {
    							$course_cocktail_entry .= "\nBolus: " . $sch_med_values['cocktail']['bolus'];
    							$course_cocktail_entry .= "\nSperrzeit: " . $sch_med_values['cocktail']['sperrzeit'] ;
    						}
    	
    						$cust = new PatientCourse();
    						$cust->ipid = $ipid;
    						$cust->course_date = date("Y-m-d H:i:s", time());
    						$cust->course_type = Pms_CommonData::aesEncrypt("Q");
    						//      							                    $cust->course_title = Pms_CommonData::aesEncrypt(addslashes(implode('', $meds).$course_cocktail_entry));
    						$cust->course_title = Pms_CommonData::aesEncrypt(addslashes(implode('', $meds[$sch_med_values['cocktail']['id']]).$course_cocktail_entry));
    						$cust->user_id = $data['userid'];
    						$cust->save();
    					}
    				}
    			}
    	
    	
    		}
    		else
    		{
    			$post_data = $med_values;
    			foreach($med_values['medication'] as $amedikey => $amedi)
    			{
    				if(strlen($amedi) > 0 && empty($med_values['hidd_medication'][$amedikey]) && !empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey]))
    				{
    	
    					$post_data['newmids'][$amedikey] = $med_values['drid'][$amedikey];
    					$post_data['newmedication'][$amedikey] = $amedi;
    				}
    	
    				if(strlen($amedi) > 0 && (!empty($med_values['hidd_medication'][$amedikey]) && empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey])))
    				{
    					$post_data['newmids'][$amedikey] = $med_values['hidd_medication'][$amedikey];
    					$post_data['newmedication'][$amedikey] = $amedi;
    				}
    	
    				if(strlen($amedi) > 0 && (empty($med_values['hidd_medication'][$amedikey]) && empty($med_values['drid'][$amedikey]) && !empty($med_values['medication'][$amedikey])))
    				{
    					$post_data['newmedication'][$amedikey] = $amedi;
    				}
    			}
    	
    			if(is_array($post_data['newmedication']))
    			{
    				if($type == 'treatment_care')
    				{
    					$dts = $patient_medication_tr_form->InsertNewData($post_data);
    				}
    				elseif ($type == 'isnutrition')
    				{
    					$dts = $patient_medication_isnutrition_form->InsertNewData($post_data);
    				}
    				else
    				{
    					$dts = $patient_medication_form->InsertNewData($post_data);
    				}
    	
    				foreach($dts as $key => $dt)
    				{
    					$post_data['newhidd_medication'][$key] = $dt->id;
    				}
    			}
    	
    			$post_data[$type] =  "1";
    			$post_data['ipid'] =  $ipid;
    			if($acknowledge =="1")
    			{
    				$post_data['skip_trigger'] = "1";
    			}
    			$_POST['add_sets'] = "1";
    			// save medication changes
    			$this->update_multiple_data($post_data);
    		}
    	
    	} // END foreach
    	
    	
    	
    }
    
    
    
    /**
     * @author Ancuta
     * ISPC-2507
     * @param number $clientid
     * @param string $ipid
     * @param unknown $request_id
     * @param number $request_user
     */
    public function send_pharma_drugplan_request_todos($clientid = 0 ,$ipid='',$request_id,$request_user =  0, $update_exisitng = false ){
        if(empty($clientid) || empty($ipid) || empty($request_id) || empty($request_user)){
            return;
        }
        
        
        $usergroup = new Usergroup();
        $user = new User();
        $MasterGroups = array("4");
        $master_group_ids = $usergroup->getUserGroups($MasterGroups);
        
        foreach($master_group_ids as $key => $value)
        {
            $groups_id[$value['groupmaster']] = $value['id'];
            $group_info[$value['id']]['master'] = $value['groupmaster'];
        }
        
        
        $users_array = $user->getClientsUsers(array($clientid));
        $users = array();
        foreach($users_array as $user_val)
        {
            $user_details[$user_val['id']] = $user_val;
            
            if($group_info[$user_val['groupid']]['master'] == '4')
            {
                $users ['doctor'][] = $user_val ['id'];
            }
        }
        
        $qpa = new PatientQpaMapping();
        $assigned_users = array();
        $assigned_users = $qpa->get_assigned_userid(array('ipids'=>array($ipid) ));
        
        $assigned_doctors = array();
        if(!empty($assigned_users['ipids'][$ipid])){
            foreach($assigned_users['ipids'][$ipid] as $assigned_user){
                if(in_array($assigned_user,$users ['doctor'])){
                    $assigned_doctors[] =  $assigned_user;
                }
            }
        }
    
        if(empty($assigned_doctors)){
            return;
        }
        
        
        // firs mark as sent the request 
        $request_data =Doctrine::getTable('PharmaPatientDrugplanRequests')->findOneBy(id,$request_id);
        if($request_data){
            $request_data->status = 'sent';
            $request_data->sent_to = serialize($assigned_doctors);
            $request_data->save();
        }
        
        // get patient details
        $patient_details = PatientMaster::get_multiple_patients_details(array($ipid));
        $patient_name = $patient_details[$ipid]['first_name'] . ', ' . $patient_details[$ipid]['last_name'];
        

        // create todos 
        $todo_data = array();
        $request2received = array();
        $todo_identifier = "";
        $todo_message = 'Bitte prüfen Sie die Vorschläge der Apotheke für die Medikation des Patienten %patient_name%. %pharmarequest%,';
        $link = '<u><b><a href="patientmedication/edit?id='.Pms_Uuid::encrypt($patient_details[$ipid]['id']).'&phrid='.$request_id.'" >BITTE HIER KLICKEN, UM DIE ÄNDERUNGEN ZU SEHEN</a></b><u>';
        

        
        //select all existing todos sent for this request - do not send again
        //pharma_patient_drugplan_request
        $pending_requests = array();
        $link_update = "";
        $todo_message_update= "";
        if($update_exisitng=== true){
            // check if requests are still pending - use the same function as the icon 
            $icon_obj = new IconsPatient();
            $pending_requests = $icon_obj->get_pharma_drugplan_request(array($ipid));
            
            //$todo_message_update = 'UPDATE:: Bitte prüfen Sie die Änderung der Apotheke an der Medikation des Patienten %patient_name%. %pharmarequest%,';
//             $todo_message_update = 'Bitte prüfen Sie die Änderung der Apotheke an der Medikation des Patienten %patient_name%. %pharmarequest%,';
            //$link_update= '<u><b><a href="patientmedication/edit?id='.Pms_Uuid::encrypt($patient_details[$ipid]['id']).'&phrid='.$request_id.'" >BITTE HIER KLICKEN, UM DIE ÄNDERUNGEN ZU SEHEN</a></b><u><br/> REQUESTS ARE PENDING';
            
            
            $todo_message_update = 'UPDATE:: Bitte prüfen Sie die Änderung der Apotheke an der Medikation des Patienten %patient_name%. Bitte %pharmarequest%,  um die Änderung zu sehen.';
            $link_update= '<u><b><a href="patientmedication/edit?id='.Pms_Uuid::encrypt($patient_details[$ipid]['id']).'&phrid='.$request_id.'" >HIER KLICKEN</a></b><u>';
        }
        
        
        foreach($assigned_doctors as $user_id)
        {
            $todo_message = str_replace('%patient_name%', $patient_name, $todo_message);
            $todo_message = str_replace('%pharmarequest%', $link, $todo_message);
            $todo_identifier = "pharma_patient_drugplan_request";
            
            $ident = $ipid.$todo_identifier.$user_id.$request_id;
            
            $todo_data[$ident] = array(
                'client_id' => $clientid,
                'user_id' => $user_id,
                'group_id' => '0',
                'ipid' => $ipid,
                'todo' => $todo_message,
                'triggered_by' => $todo_identifier,
                'record_id' => $request_id,
                'isdelete' => '0',
                'iscompleted' => '0',
                'patient_step_identification' => '0',
                "create_date" => date('Y-m-d H:i:s', time()),
                "until_date" => date('Y-m-d H:i:s', time())
            );
            $req_ident = $ipid.$request_id.$user_id;
            
            $request2received[$req_ident] = array(
                'clientid'=>$clientid,
                'doctor_id'=>$user_id,
                'request_id'=>$request_id,
                'request_user'=>$request_user,
                'ipid'=>$ipid
            );
        }
        
        if($todo_data && !empty($todo_data))
        {
            $record_keys = array_values(array_unique(array_keys($todo_data)));
            
            // get existing todos
            $existing_todos_q = Doctrine_Query::create()
            ->select("CONCAT(ipid,triggered_by,user_id,record_id) as key_value, id")
            ->from('ToDos')
            ->where('isdelete = 0')
            ->andWhere('iscompleted = 0')
            ->andWhere('ipid = ?',$ipid)
            ->andWhere('triggered_by != ""')
            ->andWhere('client_id = ?', $clientid)
            ->andWhereIn('CONCAT(ipid,triggered_by,user_id,record_id)', $record_keys);
            $existing_todos_res = $existing_todos_q->fetchArray();
            
//             dd($pending_requests,$existing_todos_res);
            if($existing_todos_res)
            {
                foreach($existing_todos_res as $k => $v)
                {
                    unset($todo_data[$v['key_value']]);
                    
                    if($update_exisitng=== true && !empty($pending_requests)){
                     //update existing
                        $todo_message_update = str_replace('%patient_name%', $patient_name, $todo_message_update);
                        $todo_message_update = str_replace('%pharmarequest%', $link_update, $todo_message_update);
                        
                        $todoline = Doctrine::getTable('ToDos')->find($v['id']);
                        if($todoline){
                            
                            $todoline->todo = $todo_message_update;
                            $todoline->save();
                        }
                    }
                    
                }
            }
            
            $collection = new Doctrine_Collection('ToDos');
            $collection->fromArray($todo_data);
            $collection->save();
        }
    
        if(!empty($request2received)){
            
            $req_record_keys = array_values(array_unique(array_keys($request2received)));
            
            
           
            
            $existing_reqs_q = Doctrine_Query::create()
            ->select("CONCAT(ipid,request_id,doctor_id) as key_value, id")
            ->from('PharmaRequestsReceived')
            ->where('clientid = ?', $clientid)
            ->andWhereIn('CONCAT(ipid,request_id,doctor_id)', $req_record_keys);
            $existing_reqs_res = $existing_reqs_q->fetchArray();
    
            if($existing_reqs_res)
            {
                foreach($existing_reqs_res as $k => $v)
                {
                    unset($request2received[$v['key_value']]);
                }
            }
            
            $collection = new Doctrine_Collection('PharmaRequestsReceived');
            $collection->fromArray($request2received);
            $collection->save();
        }
        
    }
    
    
    
    
    //#ISPC-2512PatientCharts
    public function save_dosage_interaction  ($ipid =  null , $data =  array(), $subaction = null)
    {
    	$userid = $this->logininfo->userid;
    	
        if (empty($ipid) || empty($data) || empty($data['dosage_status'])) {
            return;
        }
        /* if($data['dosage_date'] != "")
        {
            $data['dosage_date'] = date('Y-m-d', strtotime($data['dosage_date']));
        }
        else
        {
            $data['dosage_date'] = date('Y-m-d');
        } */
        
        if($data['dosage_time'] != "")
        {
        	$data['dosage_time'] = $data['dosage_time'] . ":00";
        }
        else
        {
        	$data['dosage_time'] = '00:00:00';
        }
       
        if($data['dosage_date'] != "")
        {
        	$data['dosage_date'] = date('Y-m-d H:i:s', strtotime($data['dosage_date'] . ' ' . $data['dosage_time']));
        }
        else
        {
        	$data['dosage_date'] = '0000-00-00 00:00:00';
        }
        
        /* if($data['dosage_time_interval'] != "")
        {
            //$data['dosage_time_interval'] = date('H:i:s', strtotime($data['dosage_time_interval'].':00'));
        	$data['dosage_time_interval'] = $data['dosage_time_interval'].':00';
        } else {
            $data['dosage_time_interval'] = "00:00:00";
        } */

        $data['documented_date'] = date('Y-m-d H:i:s');
        
        $data['ipid'] = $ipid;
       
        if($subaction)
        {
        	if($data['dosage_date'] != '0000-00-00 00:00:00')
        	{
        	    if($data['entry_id'] && $data['entry_id'] != 0 ) {
            		$querydel =  PatientDrugPlanDosageGivenTable::getInstance()->createQuery('gdd')
            		->delete()
            		->where('gdd.ipid = ?', $ipid)
            		->andWhere('gdd.drugplan_id = ?', $data['drugplan_id'])
            		->andWhere('gdd.cocktail_id = ?', $data['cocktail_id'])
            	    ->andWhere('gdd.id  = ?', $data['entry_id'])
            		->execute();
        	    } else {
            		$querydel =  PatientDrugPlanDosageGivenTable::getInstance()->createQuery('gdd')
            		->delete()
            		->where('gdd.ipid = ?', $ipid)
            		->andWhere('gdd.drugplan_id = ?', $data['drugplan_id'])
            		->andWhere('gdd.dosage_time_interval = ?', $data['dosage_time_interval'])
            		->andWhere('gdd.drugplan_id = ?', $data['drugplan_id'])
            		->andWhere('gdd.cocktail_id = ?', $data['cocktail_id'])
            		->andWhere('gdd.dosage_date = ?', $data['dosage_date'])
            		->execute();
        	    }
        		
        	}
        	if($data['dosage_time_interval'] == "")
        	{
        		$comment = $this->translate('Given was undocumented for medication ') . $data['medication_name'];
        	}
        	else
        	{
        	    //TODO-4142 Ancuta 24.05.2021
        		$comment = $this->translate('Given was undocumented for medication ') . $data['medication_name'] . ': '. $this->translate('dosage') . ' ' . $data['dosage'] . $this->translate(' and time ') . substr($data['dosage_time'], 0, 5);
        		//$comment = $this->translate('Given was undocumented for medication ') . $data['medication_name'] . ': '. $this->translate('dosage') . ' ' . $data['dosage'] . $this->translate(' and time ') . substr($data['dosage_time_interval'], 0, 5);
        	}
        }
        else
        {
        //$entity = PatientDrugPlanDosageGivenTable::getInstance()->createIfNotExistsOneBy(
        //ISPC-2583 Lore 27.04.2020
            if($data['entry_id'] && $data['entry_id'] != 0 ){
                $entity = PatientDrugPlanDosageGivenTable::getInstance()->findOrCreateOneBy(
                array('ipid', 'drugplan_id', 'cocktail_id', 'id' ),
                array($ipid, $data['drugplan_id'], $data['cocktail_id'], $data['entry_id']), 
                $data);
                
            } else{
                
            
                $entity = PatientDrugPlanDosageGivenTable::getInstance()->findOrCreateOneBy(
                array('ipid', 'drugplan_id', 'cocktail_id', 'dosage_time_interval', 'dosage_date'),
                array($ipid, $data['drugplan_id'], $data['cocktail_id'], $data['dosage_time_interval'], $data['dosage_date']), 
                $data);
            }
            
            //ISPC-2547 Lore 27.05.2020
            $status_comm = '';
            if(!empty($data['cocktail_id']) && $data['cocktail_id'] != 0) {
                if ( $data['dosage_status'] == 'given'){
                    $status_comm = 'Ein Bolus wurde gegeben: '. $data['dosage'];
                }
                if ( $data['dosage_status'] == 'not_given'){
                    $status_comm = 'Kein Bolus wurde gegeben.';
                }
                if ( $data['dosage_status'] == 'given_different_dosage'){
                    $status_comm = 'Ein außerordentlicher Bolus wurde gegeben: '. $data['dosage'];
                }
                if ( $data['dosage_status'] == 'not_taken_by_patient'){
                    $status_comm = 'Bolus verweigert';
                }
                
                $comment = $this->translate('Given was documented for medication ') ."\n" . $data['medication_name']."\n" .$status_comm ;
                
            } else {

                if($data['dosage_time_interval'] == "" || $data['dosage_time_interval'] == "00:00:00")
		        {
		            $comment = $this->translate('Given was documented for medication ') ."\n" . $data['medication_name'] ."\n" . 'Status: '.$this->translate($data['dosage_status']) ."\n" . $this->translate('dosage') . ': ' . $data['dosage'] ."\n" . $this->translate(' and time ') . substr($data['dosage_time'], 0, 5);
		        }
		        else
		        {
					//TODO-4142 Ancuta 24.05.2021
					//$comment = $this->translate('Given was documented for medication ') ."\n" . $data['medication_name'] ."\n" . 'Status: '.$this->translate($data['dosage_status']) ."\n" . $this->translate('dosage') . ': ' . $data['dosage'] ."\n" . $this->translate(' and time ') . substr($data['dosage_time_interval'], 0, 5);
		            $comment = $this->translate('Given was documented for medication ') ."\n" . $data['medication_name'] ."\n" . 'Status: '.$this->translate($data['dosage_status']) ."\n" . $this->translate('dosage') . ': ' . $data['dosage'] ."\n" . $this->translate(' and time ') . substr($data['dosage_time'], 0, 5);
					//--
		        }
			}
        }
        
        $cust = new PatientCourse();
        $cust->ipid = $ipid;
        $cust->course_date = date("Y-m-d H:i:s", time());
        $cust->course_type = Pms_CommonData::aesEncrypt("MG");         //ISPC-2547 Lore 26.03.2020
        $cust->course_title = Pms_CommonData::aesEncrypt($comment);
        $cust->user_id = $userid;
        $cust->save();
 
        return $entity;
    }
    
    
    /**
     * #ISPC-2512PatientCharts
     * @param array $values
     * @param unknown $elementsBelongTo
     * @return Zend_Form
     */
    public function create_dosage_interaction ($values =  array() , $elementsBelongTo = null)
    {
        // 	    dd($values);
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName , "create_form_isValid");
        
        $this->mapSaveFunction($__fnName , "save_dosage_interaction");
        
        /* 
        $subform = new Zend_Form_SubForm();
        $subform->clearDecorators()
        ->setDecorators( array(
            'FormElements',
            array('HtmlTag',array('tag'=>'div', 'class' => 'dosage_interaction')),
        ));
        
        $subform->setLegend('dosage_interaction');
        $subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
        
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        $subform->addElement('hidden', 'drugplan_id', array(
            'label'        => null,
            'value'        => ! empty($values['drugplan_id']) ? $values['drugplan_id'] : '',
            'required'     => false,
            'readonly'     => true,
            'filters'      => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'colspan' => 2,
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'class'    => 'dontPrint',
                )),
            ),
        ));
        
        
        $subform->addElement('note', 'medication_name', array(
            'label'        => false,
            'value'        => ! empty($values['medication_name']) ? $values['medication_name'] : '',
            'decorators' =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'h1',
                    'class' => 'medChartModal ',
                )) 
            ),
            
        ));
        
        $statuses = array(
            'given'=>'given',
            'not_given'=>'not_given',
            'given_different_dosage'=>'given_different_dosage',
            'not_taken_by_patient'=>'not_taken_by_patient'
           );
        
        $subform->addElement('radio', 'dosage_status', array(
            'value'        => ! empty($values['dosage_status']) ? $values['dosage_status'] : null,
            'multiOptions' => $statuses,
            'separator'    => '',
            'label'        => self::translate('Dosage_Status: '),
            'class'        => ' medChartModal',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'div',
                    'class' => 'di_inputs  di_radios',
                )),
                array('Label', array(
                    'tag' => 'div',
                    'tagClass'=>'di_labels  '
                ))
            ),
        ));
 
        
        
        $subform->addElement('text', 'not_given_reason', array(
            'label'        => self::translate('not_given_reason'),
            'value'        => ! empty($values['not_given_reason']) ? $values['not_given_reason'] : '',
            'required'     => true,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'class'        => 'di_labels not_given_reason',
            'decorators' =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'div',
                    'class' => 'di_inputs',
                )),
                array('Label', array(
                    'tag' => 'div',
                    'tagClass'=>''
                ))
            ),
            
        ));
 
        
        
        $subform->addElement('text', 'dosage_date', array(
            'label'        => self::translate('dosage_date'),
            'value'        => ! empty($values['dosage_date']) ? date('d.m.Y',strtotime($values['dosage_date'])) : date('d.m.Y'),
            'required'     => true,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'class'        => 'di_labels dosageInteractionReadonly',
            'readonly'     => 'true',
            'decorators' =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'div',
                    'class' => 'di_inputs',
                )),
                array('Label', array(
                    'tag' => 'div',
                    'tagClass'=>''
                ))
            ),
            
        ));
 
        $subform->addElement('text', 'dosage_time_interval', array(
            'label'        => self::translate('dosage_time_interval'),
            'value'        => ! empty($values['dosage_time_interval']) ? date('H:i',strtotime($values['dosage_time_interval'])) : '',
            'required'     => true,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'class'        => 'di_labels dosageInteractionReadonly',
            'readonly'     => 'true',
            'decorators' =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'div',
                    'class' => 'di_inputs',
                )),
                array('Label', array(
                    'tag' => 'div',
                    'tagClass'=>''
                ))
            ),
            
        ));
        
        $subform->addElement('text', 'dosage', array(
            'label'        => self::translate('dosage'),
            'value'        => ! empty($values['dosage']) ? $values['dosage'] : '',
            'required'     => true,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'class'        => 'di_labels ',
            'decorators' =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'div',
                    'class' => 'di_inputs',
                )),
                array('Label', array(
                    'tag' => 'div',
                    'tagClass'=>''
                ))
            ),
            
        ));
        
        $subform->addElement('textarea', 'documented_info', array(
            'label'        => self::translate('comment'),
            'value'        => ! empty($values['documented_info']) ? $values['documented_info'] : '',
            'required'     => true,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'class'        => 'di_labels ',
            'decorators' =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'div',
                    'class' => 'di_inputs',
                )),
                array('Label', array(
                    'tag' => 'div',
                    'tagClass'=>''
                ))
            ),
            'rows' => 3,
            'cols' => 50,
            
            
        ));
        
        
        
         */
        
        
        
        
        
        
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($values['medication_name']);
        $subform->setAttrib("class", "dosage_interaction");
        
        
        
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
        
 
        
        
        
        /* start with the hidden fields */
        $subform->addElement('hidden', 'drugplan_id', array(
            'value'        => $values['drugplan_id'] ? $values['drugplan_id'] : 0 ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                // 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                // 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
            
            ),
            ));
        
        $subform->addElement('hidden', 'cocktail_id', array(
        		'value'        => $values['cocktail_id'] ? $values['cocktail_id'] : 0 ,
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
        		array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
        
        		),
        		));
    
		//ISPC-2871,Elena,12.04.2021
		$subform->addElement('hidden', 'pumpe_id', array(
			'value'        => (isset($values['pumpe_id'])) ? $values['pumpe_id'] : 0,
			'required'     => true,
			'separator'    => '<br/>',
			'label'        => false,
			'class'        => ' medChartModal',
			'readonly'     => 'true',
			'decorators'   => array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
				array('Label', array('tag' => 'td')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
			),
		));
        if( ($values['cocktail_id'] && $values['cocktail_id']!=0) || ($values['pumpe_id'] && $values['pumpe_id']!=0) ){//ISPC-2871,Elena,12.04.2021

            $subform->addElement('note', 'dosage_status_info', array(
                'value'        => self::translate('given'),
                'required'     => true,
                'separator'    => '<br/>',
                'label'        => self::translate('Dosage_Status:'),
                'class'        => ' medChartModal',
                'readonly'     => 'true',
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
            
            
            $subform->addElement('hidden', 'dosage_status', array(
                'value'        => 'given',
                'required'     => true,
                'separator'    => '<br/>',
                'label'        => false,
                'class'        => ' medChartModal',
                'readonly'     => 'true',
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
            
        } else{
            
            $statuses = array(
                'given'=>'given',
                'not_given'=>'not_given',
                'given_different_dosage'=>'given_different_dosage',
                'not_taken_by_patient'=>'not_taken_by_patient'
            );
            
            $subform->addElement('radio', 'dosage_status', array(
                'value'        => ! empty($values['dosage_status']) ? $values['dosage_status'] : 'given', // TODO-3828 Ancuta 09.02.2021 
                'multiOptions' => $statuses,
                'required'     => true,
                'separator'    => '<br/>',
                'label'        => self::translate('Dosage_Status:'),
                'class'        => ' medChartModal',
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
				 // TODO-3828 Ancuta 09.02.2021 pct 4
                'onChange' => 'if (this.value == "not_given") {$(".not_given_reason", $(this).parents(\'table\')).removeClass(\'display_none\'); } else { $(".not_given_reason", $(this).parents(\'table\')).addClass(\'display_none\');} 
if (this.value == "not_taken_by_patient"){ $(".dosage_tr", $(this).parents(\'table\')).addClass(\'display_none\'); } else  { $(".dosage_tr", $(this).parents(\'table\')).removeClass(\'display_none\'); }
if (this.value == "given_different_dosage"){ $(".dosage_tr input", $(this).parents(\'table\')).prop(\'readonly\',false);$(".dosage_tr input", $(this).parents(\'table\')).prop(\'disabled\',false); } else  { $(".dosage_tr input", $(this).parents(\'table\')).prop(\'readonly\',true);  $(".dosage_tr input", $(this).parents(\'table\')).prop(\'disabled\',true); }',
                
            ));
        }


        $display_none="";
        $display_none = $values['dosage_status'] == 'not_given' ? '' : "display_none";
        $subform->addElement('text', 'not_given_reason', array(
            'value'        => ! empty($values['not_given_reason']) ? $values['not_given_reason'] : '',
            'label'        => self::translate('not_given_reason'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'class'         =>'w400',
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class'=>"not_given_reason {$display_none}" )),
            ),
        ));
        $subform->addElement('text', 'dosage_date', array(
            'value'        => ! empty($values['dosage_date']) ? date('d.m.Y',strtotime($values['dosage_date'])) : date('d.m.Y'),
            'label'        => self::translate('dosage_date'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'readonly'     => 'true',
            'class'         =>'di_inputReadonly',
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
            ),
        ));
    
        if($values['time_schedule'] == '1'){
        	$dosage_time = (! empty($values['dosage_date'])) ? substr($values['dosage_date'], 11, 5) : (! empty($values['dosage_time_interval']) ? substr($values['dosage_time_interval'], 0, 5) : '');
        	if(!$dosage_time)
        	{
        		$dosage_time = $values['dosage_time_interval'];
        	}
        }
        else 
        {
        	$dosage_time = (! empty($values['dosage_date'])) ? substr($values['dosage_date'], 11, 5) : (! empty($values['dosage_time_interval']) ? substr($values['dosage_time_interval'], 0, 5) : '');
        }
//         $subform->addElement('text', 'dosage_time', array(
//         		//'label'        => self::translate('clock:'),
//         		'value'        => $dosage_time,
//         		'required'     => true,
//         		'filters'      => array('StringTrim'),
//         		'validators'   => array('NotEmpty'),
//         		'class'        => 'time option_time',
//         		'decorators' =>   array(
//         				'ViewHelper',
//         				array('Errors'),
//         				array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
//         				//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
//         				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
//         		),
//         ));
        
        $subform->addElement('text', 'dosage_time', array(
            'value'        => $dosage_time,
            'label'        => self::translate('dosage_time_interval'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'readonly'     => 'true',
            'class'        => 'time option_time',
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        if($values['time_schedule'] == '1'){
            $subform->addElement('text', 'dosage_time_interval', array(
                //'value'        => ! empty($values['dosage_time_interval']) ? date('H:i',strtotime($values['dosage_time_interval'])) : '',
            		'value'        => ! empty($values['dosage_time_interval']) ? substr($values['dosage_time_interval'], 0, 5) : '',
                'label'        => self::translate('dosage_time_interval'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'readonly'     => 'true',
                'class'         =>'di_inputReadonly',
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr','style'=>'display: none')),
                ),
            ));
        }
        
        if($values['time_schedule'] != '1'){
        	if(is_array($values['dosage']))
        	{
        		$values['dosage'] = implode('-', array_column($values['dosage'], 'dosage'));
        	}
        }
     
/*         $subform->addElement('text', 'dosage', array(
            'value'        => ! empty($values['dosage']) ? $values['dosage'] : '',
            'label'        => $values['medication_type'] == 'Q' ?  self::translate('documented_bolus') : self::translate('documented_dosage'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        )); */
        
        //ISPC-2583 pct.7,8 Lore 22.05.2020
        
        $display_dosage_none="";
        $display_dosage_none = $values['dosage_status'] == 'not_taken_by_patient' ? 'display_none' : "";
        // TODO-3828 Ancuta 09.02.2021 pct 4
        if($values['dosage_status'] == 'given_different_dosage'){
            $subform->addElement('text', 'dosage', array(
                'value'        => ! empty($values['dosage']) ? $values['dosage'] : '',
                'label'        => $values['medication_type'] == 'Q' ?  self::translate('documented_bolus') : self::translate('documented_dosage'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
                    array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true,'class'=>"dosage_tr  {$display_dosage_none}")),
                ),
            ));
        } else{
            
            $subform->addElement('text', 'dosage', array(
                'value'        => ! empty($values['dosage']) ? $values['dosage'] : '',
                'label'        => ($values['medication_type'] == 'Q' || !(empty($values['pumpe_id']))) ?  self::translate('documented_bolus') : self::translate('documented_dosage'),//ISPC-2871,Elena,12.04.2021
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'readonly'     => true,
                'disabled'     => true,
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
                    array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true,'class'=>"dosage_tr  {$display_dosage_none}")),
                ),
            ));
        }
        //-- 
        
        $subform->addElement('note', 'unit_name', array(
            'value'        => ! empty($values['dosage_unit']) ? $values['dosage_unit'] : ' ',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'class'        => 'unit_name',
            'decorators' =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
            ),
        ));
        
        $subform->addElement('textarea', 'documented_info', array(
            'value'        => ! empty($values['documented_info']) ? $values['documented_info'] : '',
            'label'        => self::translate('documented_comment'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','style'=>'vertical-align:top')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'rows'         => 3,
            'cols'         => 50,
        ));
 
        
        
        $subform->addElement('hidden', 'entry_id', array(
            'value'        => ! empty($values['entry_id']) ? $values['entry_id'] : '0',
            'label'        => false,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','style'=>'vertical-align:top')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
 
        
        
        
        
        
        return $this->filter_by_block_name($subform, $__fnName);
    }
    
    
    
    
    public function create_dosage_interaction_bulk ($values =  array() , $elementsBelongTo = null)
    {
        // 	    dd($values);
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName , "create_form_isValid");
        
        $this->mapSaveFunction($__fnName , "save_dosage_interaction");
        
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($values['medication_name']);
        $subform->setAttrib("class", "dosage_interaction");
        
        
        
        
        $elementsBelongTo = 'data['.$values['drugplan_id'].']';
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
//         dd($elementsBelongTo); 
 
        
        
        
        /* start with the hidden fields */
        $subform->addElement('text', 'drugplan_id', array(
            'value'        => $values['drugplan_id'] ? $values['drugplan_id'] : 0 ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                // 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                // 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
            
            ),
            ));
        
        $subform->addElement('hidden', 'cocktail_id', array(
        		'value'        => $values['cocktail_id'] ? $values['cocktail_id'] : 0 ,
        		'required'     => false,
        		'filters'      => array('StringTrim'),
        		'decorators'   => array(
        				'ViewHelper',
        				array('Errors'),
        				// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
        				// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
        		array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
        
        		),
        		));
  
        //Lore 17.06.2020 medication_name in verlauf from bulk_medication in charts 
        $subform->addElement('hidden', 'medication_name', array(
            'value'        => $values['medication_name'] ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                // 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                // 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
            
            ),
            ));
        //.
        $statuses = array(
            'given'=>'given',
            'not_given'=>'not_given',
            'given_different_dosage'=>'given_different_dosage',
            'not_taken_by_patient'=>'not_taken_by_patient'
        );
        
        $subform->addElement('radio', 'dosage_status', array(
            'value'        => ! empty($values['dosage_status']) ? $values['dosage_status'] : null,
            'multiOptions' => $statuses,
            'required'     => true,
            'separator'    => '&nbsp;',
            'label'        => self::translate('Dosage_Status:'),
            'class'        => ' medChartModal',
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => 'if (this.value == "not_given") {$(".not_given_reason", $(this).parents(\'table\')).removeClass(\'display_none\');} else {$(".not_given_reason", $(this).parents(\'table\')).addClass(\'display_none\'); if (this.value == "not_taken_by_patient"){$(".dosage_tr", $(this).parents(\'table\')).addClass(\'display_none\');} else  {  $(".dosage_tr", $(this).parents(\'table\')).removeClass(\'display_none\');}}',
            
        ));
        
        $display_none="";
        $display_none = $values['dosage_status'] == 'not_given' ? '' : "display_none";
        $subform->addElement('text', 'not_given_reason', array(
            'value'        => ! empty($values['not_given_reason']) ? $values['not_given_reason'] : '',
            'label'        => self::translate('not_given_reason'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'class'         =>'w400',
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class'=>"not_given_reason {$display_none}" )),
            ),
        ));
        $subform->addElement('hidden', 'dosage_date', array(
            'value'        => ! empty($values['dosage_date']) ? date('d.m.Y',strtotime($values['dosage_date'])) : date('d.m.Y'),
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'readonly'     => 'true',
            'class'         =>'di_inputReadonly',
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
            ),
        ));
    
        if($values['time_schedule'] == '1'){
        	$dosage_time = (! empty($values['dosage_date'])) ? substr($values['dosage_date'], 11, 5) : (! empty($values['dosage_time_interval']) ? substr($values['dosage_time_interval'], 0, 5) : '');
        	if(!$dosage_time)
        	{
        		$dosage_time = $values['dosage_time_interval'];
        	}
        }
        else 
        {
        	$dosage_time = (! empty($values['dosage_date'])) ? substr($values['dosage_date'], 11, 5) : (! empty($values['dosage_time_interval']) ? substr($values['dosage_time_interval'], 0, 5) : '');
        }
//         $subform->addElement('text', 'dosage_time', array(
//         		//'label'        => self::translate('clock:'),
//         		'value'        => $dosage_time,
//         		'required'     => true,
//         		'filters'      => array('StringTrim'),
//         		'validators'   => array('NotEmpty'),
//         		'class'        => 'time option_time',
//         		'decorators' =>   array(
//         				'ViewHelper',
//         				array('Errors'),
//         				array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
//         				//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
//         				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
//         		),
//         ));
        
        $subform->addElement('text', 'dosage_time', array(
            'value'        => $dosage_time,
            'label'        => self::translate('dosage_time_interval'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'readonly'     => 'true',
            'class'        => 'time option_time',
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        if($values['time_schedule'] == '1'){
            $subform->addElement('text', 'dosage_time_interval', array(
                //'value'        => ! empty($values['dosage_time_interval']) ? date('H:i',strtotime($values['dosage_time_interval'])) : '',
            		'value'        => ! empty($values['dosage_time_interval']) ? substr($values['dosage_time_interval'], 0, 5) : '',
                'label'        => self::translate('dosage_time_interval'),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'readonly'     => 'true',
                'class'         =>'di_inputReadonly',
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr','style'=>'display: none')),
                ),
            ));
        }
        
        if($values['time_schedule'] != '1'){
        	if(is_array($values['dosage']))
        	{
        		$values['dosage'] = implode('-', array_column($values['dosage'], 'dosage'));
        	}
        }
     
/*         $subform->addElement('text', 'dosage', array(
            'value'        => ! empty($values['dosage']) ? $values['dosage'] : '',
            'label'        => $values['medication_type'] == 'Q' ?  self::translate('documented_bolus') : self::translate('documented_dosage'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        )); */
        
        //ISPC-2583 pct.7,8 Lore 22.05.2020
        
        $display_dosage_none="";
        $display_dosage_none = $values['dosage_status'] == 'not_taken_by_patient' ? 'display_none' : "";
    
        $subform->addElement('text', 'dosage', array(
            'value'        => ! empty($values['dosage']) ? $values['dosage'] : '',
            'label'        => $values['medication_type'] == 'Q' ?  self::translate('documented_bolus') : self::translate('documented_dosage'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true,'class'=>"dosage_tr  {$display_dosage_none}")),
            ),
        ));
        
        $subform->addElement('note', 'unit_name', array(
            'value'        => ! empty($values['dosage_unit']) ? $values['dosage_unit'] : ' ',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'class'        => 'unit_name',
            'decorators' =>   array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
            ),
        ));
        
        $subform->addElement('textarea', 'documented_info', array(
            'value'        => ! empty($values['documented_info']) ? $values['documented_info'] : '',
            'label'        => self::translate('documented_comment'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','style'=>'vertical-align:top')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'rows'         => 3,
            'cols'         => 50,
        ));
 
        
        
        return $this->filter_by_block_name($subform, $__fnName);
    }
    
    
    
}
?>
