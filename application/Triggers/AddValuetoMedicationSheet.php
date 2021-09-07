<?php

// require_once 'Pms/Triggers.php';

class application_Triggers_AddValuetoMedicationSheet extends Pms_Triggers
{

	public function triggerAddValuetoMedicationSheet($event,$inputs,$fieldname,$fieldid,$eventid,$gpost)
	{
		
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
	
		if(Modules::checkModulePrivileges("115", $clientid)) {
			$alien = $event->getinvoker ()->alien;
			if($alien == 1) {
				$notfromsync = false; //this came from syncing, no change here
			} else {
				$notfromsync = true;
			}
		} else {
			$notfromsync = true;
		}
		
		if($fieldname=="course_type" && isset($_POST["course_title"])  && $notfromsync )
		{
			$course_type=$event->getinvoker()->course_type;
			$courseid = $event->getinvoker()->id; // for update entryid from course doc
			$source_ipid = $event->getinvoker()->source_ipid;
			$medwirk = ""; //ISPC-2329
			$medatc = ""; //ISPC-2554 pct.3 Carmen 31.03.2020
			$meddosageformcode = ''; //ISPC-2554
			$medunitval = ''; //ISPC-2554
			$meddosageform = ''; //ISPC-2554
			$medunit = ''; //ISPC-2554
			
			$compStr = Pms_Commondata::aesEncrypt("M");
			$compStr2 = Pms_Commondata::aesEncrypt("N");
			$compStr3 = Pms_Commondata::aesEncrypt("I");
			$compStr4 = Pms_Commondata::aesEncrypt("BP");

			if(($course_type==$compStr || $course_type==$compStr2 || $course_type==$compStr3 || $course_type==$compStr4) && !$source_ipid)
			{
				if($course_type==$compStr){
					$isbedarfs=0;$isivmed=0;$treatment_care=0;
				}
				if($course_type==$compStr2){
					$isbedarfs=1;$isivmed=0;$treatment_care=0;
				}
				if($course_type==$compStr3){
					$isivmed=1;$isbedarfs=0;$treatment_care=0;
				}
				if($course_type==$compStr4){
					$isivmed=0;$isbedarfs=0;$treatment_care=1;
				}


				$master_user_details = new User();
				$users_details_arr = $master_user_details->getUserDetails($userid);
				$users_details = $users_details_arr[0];
				$user_name = $users_details['first_name'].' '.$users_details['last_name'];
				 
				
				$approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid,true);
				
				
				$modules = new Modules();

				if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
				{
				    $acknowledge = "1";
				}
				else
				{
				    $acknowledge = "0";
				}
				
				$course_title=$event->getinvoker()->course_title;
				$ipid=$event->getinvoker()->ipid;

				$course_title_new = Pms_Commondata::aesDecrypt($course_title);

				$course_titlearr = explode("|",$course_title_new);
				if($course_type==$compStr4){
					$mednamem = $course_titlearr[0];
					$medcomment = $course_titlearr[1];
					
				} else{
					
					$mednamem = $course_titlearr[0];
					$meddosagee = $course_titlearr[1];
					$medcomment = $course_titlearr[2];
					$medwirk = $course_titlearr[3]; //ISPC-2329
					$medatc = $course_titlearr[4]; //ISPC-2554 pct.3 Carmen 31.03.2020
					$meddosageformcode = $course_titlearr[7]; //ISPC-2554
					$medunitval = $course_titlearr[8]; //ISPC-2554

					$med_pzn = $course_titlearr[5]; //ISPC-2329 Ancuta 03-04.04.2020 PZN
					$med_dbf_id = $course_titlearr[6]; //ISPC-2329 Ancuta 03-04.04.2020 dbf_id
				}

				//ISPC-2329 Ancuta 03-04.04.2020
				$new_course_title = $course_title_new;
				if(strlen($mednamem) > 0 ){
				    $new_course_title = $mednamem;
				}
				if(strlen($meddosagee) > 0 ){
				    $new_course_title .= '|'.$meddosagee;
				}
				if(strlen($medcomment) > 0 ){
				    $new_course_title .= '|'. $medcomment;
				}
				//-- 
				
				if($treatment_care =="1"){
					$med = new MedicationTreatmentCare();
					$med->name = $mednamem;
					$med->extra = 1;
					$med->clientid = $clientid;
					$med->save();
				} else{
					$med = new Medication();
					$med->name = $mednamem;
					//ISPC-2329 Ancuta 03-04.04.2020 PZN+source and dbf_id
					$med->pzn = $med_pzn;
					$med->source = 'mmi_notreceipt_dropdown';
					$med->dbf_id = $med_dbf_id;
					// --
					$med->extra = 1;
					$med->clientid = $clientid;
					$med->save();
				}

				//ISPC-2554 Carmen 14.05.2020
				//if($modules->checkModulePrivileges("87", $clientid))//mmi activated
				if($meddosageformcode != '' || $medunitval != '')//mmi activated
				{				
					$dosageformmmi = MedicationDosageformMmiTable::getInstance()->getfrommmi();
				
					$medication_dosage_forms = MedicationDosageform::client_medication_dosage_form($clientid,true); // retrive all- incliding extra
					$medication_df_mmicode = array_filter(array_unique(array_column($medication_dosage_forms, 'mmi_code', 'id')));
					
					if(in_array($meddosageformcode, $medication_df_mmicode))
					{
						$meddosageform = array_search($meddosageformcode, $medication_df_mmicode);
					}
					elseif($meddosageformcode != '') 
					{
						$data['clientid'] = $clientid;
						$data['isfrommmi'] = '1';
						$data['mmi_code'] = $meddosageformcode;
						$data['extra'] = '1';
						$data['dosage_form'] = $dosageformmmi[$data['mmi_code']]['dosageform_name'];
						$dosagecustentity = MedicationDosageformTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'mmi_code'), array($clientid, $meddosageformcode), $data);
						if($dosagecustentity)
						{
							$meddosageform = $dosagecustentity->id;
						}
					}
					
					
					$medication_unit = MedicationUnit::client_medication_unit($clientid);
					$medication_unit_vals = array_filter(array_unique(array_column($medication_unit, 'unit', 'id')));
					
					if(in_array($medunitval, $medication_unit_vals))
					{
						$medunit = array_search($medunitval, $medication_unit_vals);
					}
					elseif($medunitval != '')
					{
					$data['clientid'] = $clientid;
							$data['unit'] = strtolower($medunitval);
							$unitcustentity = MedicationUnitTable::getInstance()->createIfNotExistsOneBy(array('clientid', 'unit'), array($clientid, strtolower($medunitval)), $data);
							if($unitcustentity)
							{
								$medunit = $unitcustentity->id;
							}
					}
				}

				if(!empty($_POST['skip_trigger']) && $_POST['skip_trigger'] == '1')
				{

					if(!in_array($userid,$approval_users)){
					
    				    $attach="";
    				    $ins_pat_drug_plan= new PatientDrugPlan();
    				    $ins_pat_drug_plan->medication_master_id = $med->id;
    				    $ins_pat_drug_plan->ipid = $ipid;
    				    $ins_pat_drug_plan->isbedarfs = $isbedarfs;
    				    $ins_pat_drug_plan->isivmed = $isivmed;
    				    $ins_pat_drug_plan->treatment_care= $treatment_care;
    				    $ins_pat_drug_plan->dosage = $meddosagee;
    				    $ins_pat_drug_plan->comments = $medcomment;
    				    $ins_pat_drug_plan->medication_change = date("Y-m-d H:i:s",time());
    				    $ins_pat_drug_plan->save();
    				    $inserted_id = $ins_pat_drug_plan->id;
    				    
    				    $cust = new PatientDrugPlanAlt();
    				    $cust->ipid = $ipid;
    				    $cust->drugplan_id = $inserted_id;
    				    $cust->dosage = $meddosagee;
    				    $cust->medication_master_id = $med->id;
    				    $cust->isbedarfs = $isbedarfs;
    				    $cust->isivmed = $isivmed;
    				    $cust->treatment_care = $treatment_care;
    				    $cust->comments = $medcomment;
    				    $cust->medication_change = date("Y-m-d H:i:s",time());
    				    $cust->status = "new";
    				    $cust->save();
    				    $recordid = $cust->id;
    				    //ISPC-2329
    				    $medse= new PatientDrugPlanExtra();
    				    $medse->ipid = $ipid;
    				    $medse->drugplan_id = $inserted_id;
    				    $medse->drug = $medwirk;
    				    //ISPC-2554
    				    $medse->dosage_form = $meddosageform;
    				    $medse->unit = $medunit;
    				    //--
    				    $medse->save();
    				    
    				    $custe= new PatientDrugPlanExtraAlt();
    				    $custe->ipid = $ipid;
    				    $custe->drugplan_id = $inserted_id;
    				    $custe->drugplan_id_alt = $recordid;
    				    $custe->drug = $medwirk;
    				    //ISPC-2554
    				    $medse->dosage_form = $meddosageform;
    				    $medse->unit = $medunit;
    				    //--
    				    $custe->save();
    				    //ISPC-2329
    				    // 
    				    
    				    // NEW ENTRY
    				    if($isivmed == '0' && $isbedarfs == '1' && $treatment_care == '0')
    				    {
    				        $shortcut = "N";
    				    }
    				    elseif($isivmed == '1' && $isbedarfs == '0' && $treatment_care == '0')
    				    {
    				        $shortcut = "I";
    				    }
    				    elseif($isivmed == '0' && $isbedarfs == '0' && $treatment_care == '1')
    				    {
    				        $shortcut = "BP";
    				    }
    				    else
    				    {
    				        $shortcut = "M";
    				    }
    				    
    				    if($shortcut == "M"){
       				        /* ================ PATIENT TIME SCHEME ======================= */
    				        $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid);
    				        
    				        if($patient_time_scheme['patient']){
    				            $set = 0;
    				            foreach($patient_time_scheme['patient']  as $int_id=>$int_data)
    				            {
    				                $dosage_settings[$set] = $int_data;
    				                $set++;
    				            }
    				        }
    				        else
    				        {
    				            $setc= 0;
    				            foreach($patient_time_scheme['client']  as $int_id=>$int_data)
    				            {
    				                $dosage_settings[$setc] = $int_data;
    				                $setc++;
    				            }
    				        }

                            $post_dosage = explode('-',$meddosagee);
    				        
                            if(is_array($post_dosage)){
                                if(count($post_dosage ) > count($dosage_settings))
                                {
                                    $post_dosage = array();
                                    $post_dosage[0] = "! ALTE DOSIERUNG!";
                                    $post_dosage[1] = $meddosagee;
                                }
                            } 
                            else
                            {
                                $post_dosage[0] = "! ALTE DOSIERUNG!";
                                $post_dosage[1] = $meddosagee;
                                
                            }
        
                            foreach($dosage_settings as $k => $dosage_time)
                            {
        				        // insert in patient dosage
                                $insert_pdd = new PatientDrugPlanDosage();
                                $insert_pdd->ipid = $ipid;
                                $insert_pdd->drugplan_id = $inserted_id;
                                $insert_pdd->dosage = $post_dosage[$k];
                                $insert_pdd->dosage_time_interval = $dosage_time.":00";
                                $insert_pdd->save();
                            
        				        //  insert in patient dosage
                                $cust_pdd = new PatientDrugPlanDosageAlt();
                                $cust_pdd->ipid = $ipid;
                                $cust_pdd->drugplan_id_alt = $recordid;
                                $cust_pdd->drugplan_id = $inserted_id;
                                $cust_pdd->dosage = $post_dosage[$k];
                                $cust_pdd->dosage_time_interval = $dosage_time.":00";
                                $cust_pdd->save();
                            
                            }
    				    }
    				    
    				    
    				    // new name
    				    $new_medication_name = $mednamem;
    				    
    				    // new dosage
    				    $new_medication_dosage = $meddosagee;
    				    
    				    // new comments
    				    if(strlen($medcomment) > 0){
    				        $new_medication_comments = ' | '.$medcomment;
    				    }
    				    
    				     
    				    if(strlen($new_medication_dosage)>0)
    				    {
    				        $new_entry = $prefix.$new_medication_name."  |  ".$new_medication_dosage. $new_medication_comments;
    				    }
    				    else
    				    {
    				        $new_entry = $prefix.$new_medication_name.$ $new_medication_comments;
    				    }
    				    
    
       				    $attach = 'OHNE FREIGABE: ' .  $new_entry.'';
    				    $csdoc = Doctrine::getTable('PatientCourse')->find($courseid);
//     				    $csdoc->course_type = Pms_CommonData::aesEncrypt("K");
    				    $csdoc->course_type = Pms_CommonData::aesEncrypt($shortcut);
    				    $csdoc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
    				    $csdoc->recordid = $recordid;
    				    $csdoc->tabname= Pms_CommonData::aesEncrypt("patient_drugplan_alt_new_verlauf");
    				    $csdoc->save();
	   				    
    				    // SEND MESSAGE
    				    $text  = "";
    				    $text .= "Patient %patient_name% \n ";
    				    $text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
    				    $text .=  "neue Medikation:  " .  $new_entry." \n ";
    				    
    				    $mess = Messages::medication_acknowledge_messages($ipid, $text);
    				    
    				    // create todo
    				    $todo_text  = "";
    				    $todo_text .= "Patient %patient_name% <br/>";
    				    $todo_text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
    				    $todo_text .=  "neue Medikation:  " .  $new_entry." <br/>";
    				    
    				    $todos = Messages::medication_acknowledge_todo($ipid, $todo_text, $inserted_id, $recordid);
	   				} 
	   				else
	   				{
	   				    	
	   				    $meds= new PatientDrugPlan();
                        $meds->medication_master_id = $med->id;
                        $meds->ipid = $ipid;
						$meds->isbedarfs = $isbedarfs;
						$meds->isivmed = $isivmed;
						$meds->treatment_care = $treatment_care;
                        $meds->dosage = $meddosagee;
                        $meds->comments = $medcomment;
                        $meds->save();
                        $inserted_id = $meds->id; //ISPC-2554 pct.3
                        
                        //ISPC-2329
                        $medse= new PatientDrugPlanExtra();
                        $medse->ipid = $ipid;
                        $medse->drugplan_id = $meds->id;
                        $medse->drug = $medwirk;
                        //ISPC-2554
                        $medse->dosage_form = $meddosageform;
                        $medse->unit = $medunit;
                        //--
                        $medse->save();
                        //ISPC-2329
                        
                        $csdoc = Doctrine::getTable('PatientCourse')->find($courseid);
                        $csdoc->recordid = $meds->id;
                        $csdoc->course_title = Pms_CommonData::aesEncrypt(addslashes($new_course_title));//ISPC-2329 Ancuta 03-04.04.2020 UPDATE- patientcourse- clean, drug, atc and pzn
                        $csdoc->tabname=Pms_CommonData::aesEncrypt("patient_drugplan");
                        $csdoc->save();
					}
    				    
				} 
				else
				{
					$meds= new PatientDrugPlan();
					$meds->medication_master_id = $med->id;
                    $meds->ipid = $ipid;
                    $meds->isbedarfs = $isbedarfs;
                    $meds->isivmed = $isivmed;
                    $meds->treatment_care = $treatment_care;
                    $meds->dosage = $meddosagee;
                    $meds->comments = $medcomment;
                    $meds->save();
                    $inserted_id = $meds->id; //ISPC-2554 pct.3
                    
                    //ISPC-2329
                    $medse= new PatientDrugPlanExtra();
                    $medse->ipid = $ipid;
                    $medse->drugplan_id = $meds->id;
                    $medse->drug = $medwirk;
                    //ISPC-2554
                    $medse->dosage_form = $meddosageform;
                    $medse->unit = $medunit;
                    //--
                    $medse->save();
                    //ISPC-2329
                    
                    $csdoc = Doctrine::getTable('PatientCourse')->find($courseid);
                    $csdoc->recordid = $meds->id;
                    $csdoc->course_title = Pms_CommonData::aesEncrypt(addslashes($new_course_title));//ISPC-2329 Ancuta 03-04.04.2020 UPDATE- patientcourse- clean, drug, atc and pzn
                    $csdoc->tabname=Pms_CommonData::aesEncrypt("patient_drugplan");
                    $csdoc->save();
				    
				}
				
				//ISPC-2554 pct.3 Carmen 31.03.2020
				$medatcarr = (array)json_decode($medatc);
				if(!empty($medatcarr))
				{
					$data_atc['ipid'] = $ipid;
					$data_atc['drugplan_id'] = $inserted_id;
					$data_atc['medication_master_id'] = $med->id;
					$data_atc['atc_code'] = $medatcarr['atc_code'];
					$data_atc['atc_description'] = $medatcarr['atc_description'];
					$data_atc['atc_groupe_code'] = $medatcarr['atc_groupe_code'];
					$data_atc['atc_groupe_description'] = $medatcarr['atc_groupe_description'];
					
					$medatcentity = PatientDrugPlanAtcTable::getInstance()->createIfNotExistsOneBy(array('ipid', 'drugplan_id'), array($ipid, $inserted_id), $data_atc);
				}
				//--
				
				
			}
		}
	}
}
?>