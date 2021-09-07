<?
class Application_Form_Bedarfsmedication extends Pms_Form{

	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['title'])){
			$this->error_message['title']=$Tr->translate('title_error'); $error=1;
		}

		if($error==0)
		{
		 return true;
		}
		return false;
	}

	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
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
		// Maria:: Migration ISPC to CISPC 08.08.2020
		$atcindex = 0; //ISPC-2554
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
    	        
    	        
    	        
        		if($post['bid']>0)
        		{
        			$bm = new Bedarfsmedication();
        			$bmarr = $bm->getbedarfsmedication($post['bid']);
      
        			foreach($bmarr as $k=>$v)
        			{
        				
        				/*$ins_pat_drug_plan = new PatientDrugPlan();
        				$ins_pat_drug_plan->ipid = $post['ipid'];
        				$ins_pat_drug_plan->dosage = $v['dosage'];
        				$ins_pat_drug_plan->medication_master_id = $v['medication_id'];
        				$ins_pat_drug_plan->isbedarfs = 1;
        				$ins_pat_drug_plan->comments = $v['comments'];
        				$ins_pat_drug_plan->medication_change = date('Y-m-d 00:00:00');
        				$ins_pat_drug_plan->save();
        				$inserted_id = $ins_pat_drug_plan->id;
        				
        				$cust = new PatientDrugPlanAlt();
        				$cust->ipid = $post['ipid'];
        				$cust->drugplan_id = $inserted_id;
        				$cust->dosage = $v['dosage'];
        				$cust->medication_master_id = $v['medication_id'];
        				$cust->isbedarfs = '1';
        				$cust->comments = $v['comments'];
        				$cust->medication_change = date('Y-m-d 00:00:00');
        				$cust->status = "new";
        				$cust->save();
        				$recordid = $cust->id;*/
        				 
        				
        				$ins_pat_drug_plan = new PatientDrugPlan();
        				$ins_pat_drug_plan->ipid = $post['ipid'];
        				$ins_pat_drug_plan->dosage = $v['dosage'];
        				$ins_pat_drug_plan->medication_master_id = $v['medication_id'];
        				$ins_pat_drug_plan->isbedarfs = 1;
        				$ins_pat_drug_plan->comments = $v['comments'];
        				$ins_pat_drug_plan->verordnetvon = $v['verordnetvon']; //ISPC - 2124
        				$ins_pat_drug_plan->medication_change = date('Y-m-d 00:00:00');
        				$ins_pat_drug_plan->save();
        				$inserted_id = $ins_pat_drug_plan->id;
        				
        				$cust = new PatientDrugPlanAlt();
        				$cust->ipid = $post['ipid'];
        				$cust->drugplan_id = $inserted_id;
        				$cust->dosage = $v['dosage'];
        				$cust->medication_master_id = $v['medication_id'];
        				$cust->isbedarfs = '1';
        				$cust->comments = $v['comments'];
        				$cust->verordnetvon = $v['verordnetvon']; //ISPC - 2124
        				$cust->medication_change = date('Y-m-d 00:00:00');
        				$cust->status = "new";
        				$cust->save();
        				$recordid = $cust->id;
        				
        				//ISPC - 2124
        				$ins_pat_drug_plan_extra = new PatientDrugPlanExtra();
        				$ins_pat_drug_plan_extra->ipid = $post['ipid'];
        				$ins_pat_drug_plan_extra->drugplan_id = $inserted_id;
        				$ins_pat_drug_plan_extra->drug = $v['drug'];
        				$ins_pat_drug_plan_extra->unit = $v['unit'];
        				$ins_pat_drug_plan_extra->type = $v['type'];
        				$ins_pat_drug_plan_extra->drug = $v['drug'];
        				$ins_pat_drug_plan_extra->indication = $v['indication'];
        				$ins_pat_drug_plan_extra->importance = $v['importance'];        				
        				$ins_pat_drug_plan_extra->dosage_form = $v['dosage_form'];        				
        				$ins_pat_drug_plan_extra->concentration = $v['concentration'];
        				$ins_pat_drug_plan_extra->save();
        				$inserted_id_alt = $ins_pat_drug_plan_extra->id;
        				
        				$cust_extra = new PatientDrugPlanExtraAlt();
        				$cust_extra->ipid = $post['ipid'];
        				$cust_extra->drugplan_id = $inserted_id;
        				$cust_extra->drugplan_id_alt = $inserted_id_alt;
        				$cust_extra->drug = $v['drug'];
        				$cust_extra->unit = $v['unit'];
        				$cust_extra->type = $v['type'];
        				$cust_extra->drug = $v['drug'];
        				$cust_extra->indication = $v['indication'];
        				$cust_extra->importance = $v['importance'];
        				$cust_extra->dosage_form = $v['dosage_form'];
        				$cust_extra->concentration = $v['concentration'];
        				$cust_extra->save();
        				//ISPC - 2124
        				// Maria:: Migration ISPC to CISPC 08.08.2020
    					//ISPC-2554 pct.3 Carmen 07.04.2020
        				if($v['atc_code'] != '')
        				{
        					$toupdate[$atcindex]['ipid'] = $post['ipid'];
        					$toupdate[$atcindex]['drugplan_id'] = $inserted_id;
        					$toupdate[$atcindex]['medication_master_id'] = $v['medication_id'];
        					$toupdate[$atcindex]['atc_code'] = $v['atc_code'];
        					$toupdate[$atcindex]['atc_description'] = $v['atc_description'];
        					$toupdate[$atcindex]['atc_groupe_code'] = $v['atc_groupe_code'];
        					$toupdate[$atcindex]['atc_groupe_description'] = $v['atc_groupe_description'];
        					$atcindex++;
        				}        				
        				//--
        				// NEW ENTRY
        				
        				
        				// new name
        				$new_med = Doctrine::getTable('Medication')->find($v['medication_id']);
        				$new_medication_name = $new_med->name;
        				
        				// new dosage
        				$new_medication_dosage = $v['dosage'];
        				
        				// new comments
        				if(strlen($v['comments']) > 0){
        				    $new_medication_comments = ' | '.$v['comments'];
        				}
        				// new change date
        				$medication_change_date_str = date('d.m.Y', time());
        				
        					
        				if(strlen($new_medication_dosage)>0)
        				{
        				    $new_entry = $prefix.$new_medication_name."  |  ".$new_medication_dosage. $new_medication_comments." | ".$medication_change_date_str;
        				}
        				else
        				{
        				    $new_entry = $prefix.$new_medication_name.$new_medication_comments." | ".$medication_change_date_str;
        				}
    
    
        				$attach = 'OHNE FREIGABE: ' .  $new_entry.'';
        				$insert_pc = new PatientCourse();
        				$insert_pc->ipid = $ipid;
        				$insert_pc->course_date = date("Y-m-d H:i:s", time());
        				$insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
        				$insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_alt_new_med");
        				$insert_pc->recordid = $inserted_id;
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
        				$text_todo .= 'neue Medikation: ' .  $new_entry.' <br/>';
        				
        				$todos = Messages::medication_acknowledge_todo($post['ipid'], $text_todo, $inserted_id, $recordid);
        				
        			}//foreach $bmarr
        
        		}//if
    
    	    }
    		else
    		{
    		    $misc = "Medication change  Permission Error - Insert multiple BEDARF";
    		    PatientPermissions::MedicationLogRightsError(false,$misc);
    		}
	    }
		
		else
		{
    		if($post['bid']>0)
    		{
    			$bm = new Bedarfsmedication();
    			$bmarr = $bm->getbedarfsmedication($post['bid']);
    
    			foreach($bmarr as $k=>$v)
    			{
    				$cust = new PatientDrugPlan();
    				$cust->ipid = $post['ipid'];
    				$cust->dosage = $v['dosage'];
    				$cust->comments = $v['comments'];
    				$cust->verordnetvon = $v['verordnetvon']; //ISPC - 2124
    				$cust->isbedarfs = 1;
    				$cust->medication_master_id = $v['medication_id'];
    				$cust->save();
    				$inserted_id = $cust->id;
    				
    				//ISPC - 2124
    				$ins_pat_drug_plan_extra = new PatientDrugPlanExtra();
    				$ins_pat_drug_plan_extra->ipid = $post['ipid'];
    				$ins_pat_drug_plan_extra->drugplan_id = $inserted_id;
    				$ins_pat_drug_plan_extra->drug = $v['drug'];
    				$ins_pat_drug_plan_extra->unit = $v['unit'];
    				$ins_pat_drug_plan_extra->type = $v['type'];
    				$ins_pat_drug_plan_extra->drug = $v['drug'];
    				$ins_pat_drug_plan_extra->indication = $v['indication'];
    				$ins_pat_drug_plan_extra->importance = $v['importance'];
    				$ins_pat_drug_plan_extra->dosage_form = $v['dosage_form'];
    				$ins_pat_drug_plan_extra->concentration = $v['concentration'];
    				$ins_pat_drug_plan_extra->save();
    				$inserted_id_alt = $ins_pat_drug_plan_extra->id;
					// Maria:: Migration ISPC to CISPC 08.08.2020
    				//ISPC-2554 pct.3 Carmen 07.04.2020
    				if($v['atc_code'] != '')
    				{
    					$toupdate[$atcindex]['ipid'] = $post['ipid'];
    					$toupdate[$atcindex]['drugplan_id'] = $inserted_id;
    					$toupdate[$atcindex]['medication_master_id'] = $v['medication_id'];
    					$toupdate[$atcindex]['atc_code'] = $v['atc_code'];
    					$toupdate[$atcindex]['atc_description'] = $v['atc_description'];
    					$toupdate[$atcindex]['atc_groupe_code'] = $v['atc_groupe_code'];
    					$toupdate[$atcindex]['atc_groupe_description'] = $v['atc_groupe_description'];
    					$atcindex++;
    				}
    				//--
    				
    				// this is for  Medication acknowledge
    				if(in_array($userid,$approval_users) && $acknowledge == "1" ){
    				    // NEW ENTRY
   				        $shortcut = "N";
    				    
    				    // new name
    				     $new_med = Doctrine::getTable('Medication')->find($v['medication_id']);
    				    $new_medication_name = $new_med->name;
    				
    				    // new dosage
    				    $new_medication_dosage = $v['dosage'];
    				
    				    // new comments
    				    $new_medication_comments = $v['comments'];
    				
    				    // new change date
    				    $medication_change_date_str= date("d.m.Y",time());
    				
    				    if(strlen($new_medication_dosage)>0)
    				    {
    				        $new_entry = $prefix.$new_medication_name."  |  ".$new_medication_dosage." | ". $new_medication_comments." | ".$medication_change_date_str;
    				    }
    				    else
    				    {
    				        $new_entry = $prefix.$new_medication_name." | ".$new_medication_comments." | ".$medication_change_date_str;
    				    }
    				
    				    $attach = $new_entry.'';
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
    				
    				 
    			}//foreach $bmarr
    
    		}//if
		}
		// Maria:: Migration ISPC to CISPC 08.08.2020		
		//ISPC-2554 pct.3 Carmen 07.04.2020
		if(!empty($toupdate))
		{
			$atccollection = new Doctrine_Collection('PatientDrugPlanAtc');
			$atccollection->fromArray($toupdate);
			$atccollection->save();
		}
		//--
	}



}

?>