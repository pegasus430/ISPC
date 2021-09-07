<?php

// require_once 'Pms/Triggers.php';

class application_Triggers_WriteCommentToCourseDoc extends Pms_Triggers
{

	public function triggerWriteCommentToCourseDoc($event,$inputs,$fieldname,$fieldid,$eventid,$gpost,$gmods=NULL)
	{

		$Tr = new Zend_View_Helper_Translate();

		if($fieldname=="frmasignusertopatient") // course doc entry for assign patient
		{
			$inputs['dataset'];
			$epid = $event->getinvoker()->epid;

			$dbuserid = $event->getinvoker()->userid;

			$ipid = Pms_CommonData::getIpidFromEpid($epid);

			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;

			$userdata = Pms_CommonData::getUserDataById($dbuserid);

			if(strlen($inputs['dataset'])>0)
			{
				$comment = str_replace("#userfirstname",$userdata[0]['first_name'],$inputs['dataset']);
				$comment = str_replace("#userlastname",$userdata[0]['last_name'],$comment);
				$comment = str_replace("#epid",$epid,$comment);
			}
			else
			{
				$comment = "".$userdata[0]['first_name']." ".$userdata[0]['last_name']." wurde diesem Patienten zugewiesen";
			}

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type=Pms_CommonData::aesEncrypt("K");
			$cust->course_title=Pms_CommonData::aesEncrypt($comment);
			$cust->user_id = $userid;
			$cust->save();

		}

		if($fieldname=="frmpatientcontact")
		{
			$ipid = $event->getinvoker()->ipid;

			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;


			if(strlen($inputs['dataset'])>0)
			{
				$comment = $this->setTriggerPlaceHolders($event,$inputs['dataset'],$fieldname);
			}
			else
			{
				$comment = "Kontaktperson ".$_POST['cnt_first_name']." ".$_POST['cnt_last_name']." wurde eingetragen.\nTelefon ".$_POST['cnt_phone']."\nStreet ".$_POST['cnt_street1']."";
			}

			if($comment)
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s",time());
				$cust->course_type=Pms_CommonData::aesEncrypt("K");
				$cust->course_title=Pms_CommonData::aesEncrypt($comment);
				$cust->user_id = $userid;
				$cust->save();
			}

		}

		if($fieldname=="familydoc_id")
		{
			$ipid = $event->getinvoker()->ipid;
			$epid  = Pms_CommonData::getEpid($ipid);

			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$doctorid = $_POST['hidd_docid'];

			if($eventid==1)//update
			{
				if($_POST['old_doctor_firstname']<>"")
				{

					if(strlen($inputs['dataset'])>0)
					{
						$cs = new FamilyDoctor();
						$csarr = $cs->getFamilyDoc($doctorid);

						$comment.= "Hausarzt ".$_POST['old_doctor_firstname']." ".$_POST['old_doctor_lastname']." wurde bei diesem Patienten ausgetragen.";
						if($csarr[0]["first_name"]<>""){
							$comment.="\nNeu eingetragen ist ".$csarr[0]["first_name"]." ".$csarr[0]["last_name"]." "	;
						}
					}
				} else {
					$dn = explode(",", $_POST['familydoc_id']);
					$comment = "Hausarzt ".$dn[1]." ".$dn[0]." eingetragen.";
				}

				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s",time());
				$cust->course_type=Pms_CommonData::aesEncrypt("K");
				$cust->course_title=Pms_CommonData::aesEncrypt($comment);
				$cust->user_id = $userid;
				$cust->save();
			}

			if($eventid==2) // insert
			{
			}
		}
		elseif($fieldname=="company_name" || $fieldname=="insurance_no" || $fieldname=="insurance_status")
		{
			$ipid=$event->getinvoker()->ipid;
			$new_compname=$event->getinvoker()->company_name;
			$new_compname = Pms_CommonData::aesDecrypt($new_compname);

			$epid  = Pms_CommonData::getEpid($ipid);

			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type=Pms_CommonData::aesEncrypt("K");
			if($eventid==1)
			{
				if($fieldname=="insurance_no" || $fieldname=="insurance_status")
				{
					if(strlen($inputs['dataset'])>0)
					{
						$comment = $inputs['dataset'];
					}
					else
					{
						$comment = "Versicherungsdaten angepasst";
					}
					$cust->course_title=Pms_CommonData::aesEncrypt($comment);
				}
			}
			if($fieldname=="company_name")
			{
				if($_POST['old_company_name']=="")
				{
					if(strlen($inputs['dataset'])>0)
					{
						$comment = str_replace("#oldcompname",$_POST['old_company_name'],$inputs['dataset']);
						$comment = str_replace("#newcompname",$new_compname,$comment);
						$cust->course_title=Pms_CommonData::aesEncrypt($comment);
					}
					else
					{
						$cust->course_title=Pms_CommonData::aesEncrypt("Krankenkasse von ".$new_compname);
					}
				}
				else
				{
					if(strlen($inputs['dataset'])>0)
					{
						$comment = str_replace("#oldcompname",$_POST['old_company_name'],$inputs['dataset']);
						$comment = str_replace("#newcompname",$new_compname,$comment);
						$ctitle=utf8_encode($comment);
					}
					else
					{
						$ctitle=utf8_encode("Krankenkasse von ".$_POST['old_company_name']." auf ".$new_compname." ge�ndert");
					}


					$cust->course_title=Pms_CommonData::aesEncrypt($ctitle);
				}
			}
			$cust->user_id = $userid;
			$cust->save();
		}
		elseif($fieldname=="diagnosis_id"  && !isset($_POST["clone"]))
		{//ISPC-2614 Ancuta 12.08.2020
			$ipid = $event->getinvoker()->ipid;

			$diagnoID = $event->getinvoker()->diagnosis_id;

			$new_tblname = Pms_CommonData::aesDecrypt($event->getinvoker()->tabname);
			
			if($diagnoID > 0)
			{
				if($new_tblname == "diagnosis")
				{
					$ds = new Diagnosis();
					$dds = $ds->getDiagnosisData($diagnoID);

					if($dds[0]['icd_primary']!=="")
					{
						$icd = $dds[0]['icd_primary'].' | ';
					}

					$diagnosis =$icd.$dds[0]['description'];
					$ctype = "D";
				}
				
				if($new_tblname == "diagnosis_freetext")
				{
					$comm = "";
					$ds = new DiagnosisText();
					$ddx = $ds->getDiagnosisTextData($diagnoID);

					if($ddx[0]['icd_primary']!=="")
					{
						$icd = $ddx[0]['icd_primary'].' | ';
					}

					if($ddx[0]['free_desc']!=="")
					{
						$comm = "|".$ddx[0]['free_desc'];
					}

					$diagnosis = $icd.$ddx[0]['free_name'].$comm;
					$ctype = "D";
				}
				
				if($new_tblname == "diagnosis_icd")
				{
					$ds = new DiagnosisIcd();
					$ddx = $ds->getDiagnosisDataById($diagnoID);
					if($ddx[0]['icd_primary']!=="")
					{
						$icd = $ddx[0]['icd_primary'].' | ';
					}
					$diagnosis = $icd.$ddx[0]['description'];
					$ctype = "H";
				}
			}

			$diagnosis_type = $event->getinvoker()->diagnosis_type_id;

			$dt = new DiagnosisType();
			$dtarr = $dt->getDiagnosisTypesById($diagnosis_type);
			
			if($dtarr[0]['abbrevation'] == "HD")
			{
				$ctype = "H";
			}
			else if($dtarr[0]['abbrevation'] == "HS")
			{
				$ctype = "HS";
			}


			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;


			$epid  = Pms_CommonData::getEpid($ipid);

			if($diagnosis!="|" && $diagnoID>0)
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s",time());
				$cust->course_type=Pms_CommonData::aesEncrypt($ctype);
				$cust->course_title=Pms_CommonData::aesEncrypt(addslashes($diagnosis));
				$cust->user_id = $userid;
				$cust->isstandby = $_POST['isstandby'];
				$cust->save();

				//				The tree steps to hell
				if($cust->id)
				{
					// 1. get client shortcuts!
					$courses = new Courseshortcuts();
					$shortcut_id = $courses->getShortcutIdByLetter($ctype, $clientid);


					// 2. check if shortcut is shared
					$patient_share = new PatientsShare();
					$shared_data = $patient_share->check_shortcut($ipid, $shortcut_id);

					if($shared_data)
					{
						foreach($shared_data[$shortcut_id] as $shared)
						{
							// 3. salve to other patients
							$cust = new PatientCourse();
							$cust->ipid = $shared; //target ipid
							$cust->course_date = date("Y-m-d H:i:s", time());
							$cust->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
							$cust->course_type = Pms_CommonData::aesEncrypt($ctype);
							$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($diagnosis));
							$cust->user_id = $userid;
							$cust->source_ipid = $ipid;
							$cust->save();
						}
					}
				}
			}

			foreach($meta_titles as $key=>$val)
			{

			}
		}
		elseif($fieldname=="location_id" && isset($_POST['location_id']))
		{
 
			$Tr = new Zend_View_Helper_Translate();
			if(is_array($_POST['location_id']) && sizeof($_POST['location_id']) > 0){
				foreach($_POST['location_id'] as $key => $val){
					if($key >= 0 && empty($_POST['lid'][$key])){
						$ipid=$event->getinvoker()->ipid;
						$location_id = $_POST['location_id'][$key] ; // edited for multiple location entries


						$locid  = substr($location_id, 0, 4);
						if($locid == "8888")
						{
							$indice = substr($location_id, -1);
							$cnt = $indice -1;
							$cpr = new ContactPersonMaster();
							$cprarr = $cpr->getPatientContact($ipid,false);

							$z=1;
							$cnt_number=1;
							$location_arr = array();
							foreach($cprarr as $k=>$value){
								if($value['isdelete']==0){
									$location_arr[$value['ipid']]['8888'.$z] = 'bei Kontaktperson '.$cnt_number.' ('.$value['cnt_last_name'].', '.$value['cnt_first_name'].')';
									$cnt_number++;
								}
								$z++;
							}
							$location = $location_arr[$ipid][$location_id];
						}
						else
						{
							$ls = new Locations();
							$larr = $ls->getLocationbyId($location_id);
							$location = $larr[0]['location'];
						}

						$logininfo= new Zend_Session_Namespace('Login_Info');
						$userid = $logininfo->userid;
						$pl= new PatientLocation();
						if($_POST['reason'][$key]>0)
						{
							if($_POST['reason'][$key] == '4'){
								$reason_txt = "&raquo;".$_POST['reason_txt'][$key];
							} else{
								$reason_txt = "";
							}
							$plarr = $pl->getReasons();
							$location.= "\nEinweisungsgrund: ".$plarr[$_POST['reason'][$key]].$reason_txt;
						}
						if($_POST['hospdoc'][$key]>0)
						{
							$docarr = $pl->getHospDocs();
							$location.= "\nEingewiesen von: ".$docarr[$_POST['hospdoc'][$key]];
						}
						if($_POST['transport'][$key]>0)
						{
							$docarr = $pl->getTransports();
							$location.= "\nTransportmittel auswählen: ".$docarr[$_POST['transport'][$key]];
						}

						$loccomment = $Tr->translate('patientlocation')." : ".$location;

						$cust = new PatientCourse();
						$cust->ipid = $ipid;
						$cust->course_date = date("Y-m-d H:i:s",time());
						$cust->course_type=Pms_CommonData::aesEncrypt("K");
						$cust->course_title=Pms_CommonData::aesEncrypt($loccomment);
						$cust->user_id = $userid;
						$cust->isstandby = $_POST['isstandby'];
						$cust->save();
					}
					//TODO-3595 Ancuta 21.12.2020
					elseif( ! empty($_POST['lid'][$key]) && !empty($_POST['existing_data'][$_POST['lid'][$key]])  ){
					    
					       $ls = new Locations();
					       $larr = $ls->getLocationbyId($_POST['location_id'][$key]);
				           
					       $line_id = $_POST['lid'][$key];
					       $exisitng_data[$line_id] = array(); 
					       $exisitng_data[$line_id] = $_POST['existing_data'][$line_id];
					    
				           $line_changed = 0;
					       $str = "";
					       if($exisitng_data[$line_id]['location_id'] != $_POST['location_id'][$key] && $_POST['location_id'][$key] != 0 ){
					           $line_changed++;
					           $str .= " \n [".$Tr->translate('pl_location_was changed')." ";
					           $str .= $exisitng_data[$line_id]['master_location']['location'].' -> '.$larr[0]['location'];
					           $str .= "]\n";
					       }
					       
					       
					       if(date('d.m.Y',strtotime($exisitng_data[$line_id]['valid_from'])) != $_POST['valid_from'][$key]){
					           $line_changed++;
					           $str .= " \n [".$Tr->translate('pl_start_date_was_edited')." ";
					           $str .= date('d.m.Y',strtotime($exisitng_data[$line_id]['valid_from'])).' -> '.$_POST['valid_from'][$key];
					           $str .= "]\n";
					       }

					       
					       if(date('d.m.Y',strtotime($exisitng_data[$line_id]['valid_till'])) != $_POST['valid_till'][$key] && !empty($_POST['valid_till'][$key])){
					           $line_changed++;
					           $str .= "  [".$Tr->translate('pl_end_date_was_edited')." ";
					           if($exisitng_data[$line_id]['valid_till'] != "0000-00-00 00:00:00"){
    					           $str .= date('d.m.Y',strtotime($exisitng_data[$line_id]['valid_till'])).' -> '.$_POST['valid_till'][$key];
					           } else{
    					           $str .= ' '.$_POST['valid_till'][$key];
					           }
					           $str .= "]\n";
					       }
					       
					       if($line_changed == 0){
					           continue;
					       }
					       
    						$ipid=$event->getinvoker()->ipid;
    						$location_id = $_POST['location_id'][$key] ; // edited for multiple location entries
    
    						$locid  = substr($location_id, 0, 4);
    						if($locid == "8888")
    						{
    							$indice = substr($location_id, -1);
    							$cnt = $indice -1;
    							$cpr = new ContactPersonMaster();
    							$cprarr = $cpr->getPatientContact($ipid,false);
    
    							$z=1;
    							$cnt_number=1;
    							$location_arr = array();
    							foreach($cprarr as $k=>$value){
    								if($value['isdelete']==0){
    									$location_arr[$value['ipid']]['8888'.$z] = 'bei Kontaktperson '.$cnt_number.' ('.$value['cnt_last_name'].', '.$value['cnt_first_name'].')';
    									$cnt_number++;
    								}
    								$z++;
    							}
    							$location = $location_arr[$ipid][$location_id];
    						}
    						else
    						{
    							$ls = new Locations();
    							$larr = $ls->getLocationbyId($location_id);
    							$location = $larr[0]['location'];
    						}
    
    						$logininfo= new Zend_Session_Namespace('Login_Info');
    						$userid = $logininfo->userid;
    						$pl= new PatientLocation();
    						if($_POST['reason'][$key]>0)
    						{
    							if($_POST['reason'][$key] == '4'){
    								$reason_txt = "&raquo;".$_POST['reason_txt'][$key];
    							} else{
    								$reason_txt = "";
    							}
    							$plarr = $pl->getReasons();
    							$location.= "\nEinweisungsgrund: ".$plarr[$_POST['reason'][$key]].$reason_txt;
    						}
    						if($_POST['hospdoc'][$key]>0)
    						{
    							$docarr = $pl->getHospDocs();
    							$location.= "\nEingewiesen von: ".$docarr[$_POST['hospdoc'][$key]];
    						}
    						if($_POST['transport'][$key]>0)
    						{
    							$docarr = $pl->getTransports();
    							$location.= "\nTransportmittel auswählen: ".$docarr[$_POST['transport'][$key]];
    						}
    
    						$loccomment = $Tr->translate('patientlocation_edit')." : ".$location;
    
    						$cust = new PatientCourse();
    						$cust->ipid = $ipid;
    						$cust->course_date = date("Y-m-d H:i:s",time());
    						$cust->course_type=Pms_CommonData::aesEncrypt("K");
    						$cust->course_title=Pms_CommonData::aesEncrypt($loccomment.$str);
    						$cust->user_id = $userid;
    						$cust->isstandby = $_POST['isstandby'];
    						$cust->save();
					    
					}
					// --
				}
				unset($_POST['location_id']);
			}
		}
		elseif ($fieldname=="frmpatientdrugplan" && !empty($_POST['verlauf_edit']) && $event->getinvoker()->edit_type == 'P' && (empty($_POST['skip_trigger']) || $_POST['skip_trigger'] == '0') ) //verlauf edit
		{
// 		    print_r("PRIMA");exit;
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$gmod = $event->getinvoker();
				
			$meds = Doctrine::getTable('Medication')->find($gmod->medication_master_id);
			if ($meds)
			{
				$medarr = $meds->toArray();
				$medname = $medarr['name'];
			}
			$ipid = $event->getinvoker()->ipid;

			$shortcut = 'P';
			$isdelete = $event->getinvoker()->isdelete;
			$meddosage = $event->getinvoker()->dosage;
			$recordid = $event->getinvoker()->id;
			$event_type = $event->getinvoker()->edit_type;
			$scheduled= $event->getinvoker()->scheduled;
			$old_med_values = $event->getinvoker()->_lastModified;
			$create_date = $event->getinvoker()->create_date;
				
			//ISPC - 2366
			if(empty($_POST['delete'][$recordid]))
			{
				/*==============Medication get Invoker comments===================*/
				if($event->getinvoker()->comments)
				{
					$comment = " | ".$event->getinvoker()->comments;
				}
				else
				{
					$comment ="";
				}

				/*==============Medication get Invoker medication_change==========*/
				if($event->getinvoker()->medication_change)
				{
					$medication_change = date('d.m.Y',strtotime($event->getinvoker()->medication_change));
				}
				else
				{
					$medication_change ="";
				}

				/*==============Medication get System info==========*/
				$epid  = Pms_CommonData::getEpid($ipid);


				/*==============Medication get Invoker - medication details==========*/
				if(strlen($meddosage)>0)
				{
					if(trim($meddosage) != "")
					{
						$attach = $medname."  |  ".$meddosage." ".$comment." | ".$medication_change;
					}
					else 
					{
						$attach = $medname." ".$comment." | ".$medication_change;
					}
				}
				else
				{
					$attach = $medname." ".$comment." | ".$medication_change;
				}

				/*====================== Medication change date =====================*/
				if($old_med_values['medication_change']) {
					if($old_med_values['medication_change'] != "0000-00-00 00:00:00"){
						$old_med_medication_change = " | ".date('d.m.Y',strtotime($old_med_values['medication_change']));
					} elseif($old_med_values['medication_change'] == "0000-00-00 00:00:00" && $old_med_values['change_date'] != "0000-00-00 00:00:00" ){
						$old_med_medication_change = " | ".date('d.m.Y',strtotime($old_med_values['change_date']));
					} elseif($old_med_values['medication_change'] == "0000-00-00 00:00:00" && $old_med_values['change_date'] == "0000-00-00 00:00:00" ) {
						$old_med_medication_change = " | ".date('d.m.Y',strtotime($create_date));
					}

				} elseif (!array_key_exists('medication_change', $old_med_values)) {
					$old_med_medication_change = " | ".$medication_change;
				}

				/*====================== Medication name =====================*/
				if($old_med_values['medication_master_id']) {
					$oldmeds = Doctrine::getTable('Medication')->find($old_med_values['medication_master_id']);
					if($oldmeds){
						$oldmedarr = $oldmeds->toArray();
						$old_med_name = $prefix.$oldmedarr['name']." | ";
					}
				} elseif(!array_key_exists('medication_master_id', $old_med_values)) {
					$old_med_name = $prefix.$medname." | ";
				}

				/*====================== Medication comments =====================*/
				if($old_med_values['comments']) {
					$old_med_comments = " | ".$old_med_values['comments'];
				} elseif(!array_key_exists('comments', $old_med_values)) {
					$old_med_comments = $comment;
				}

				/*====================== Medication dosage=====================*/
				if($old_med_values['dosage']) {
					$old_med_dosage = $old_med_values['dosage'];
				} elseif(!array_key_exists('dosage', $old_med_values)) {
					$old_med_dosage = $meddosage;
				}

				
				
				
				if($old_med_values['days_interval']) {
					$old_days_interval = " | ".$old_med_values['days_interval'];
					
				} 
				
				$old_entry = "";
				
				if($scheduled == "1")
				{
				    $old_entry = $old_med_name.$old_days_interval;
				}
				else
				{
				    
    				if(strlen($old_med_dosage)>0){
    					$old_entry = $old_med_name.$old_med_dosage." ".$old_med_comments.$old_med_medication_change;
    				} else	{
    					//$old_entry = $old_med_name.$old_med_comments.$old_med_medication_change;
    					if(strlen($old_med_comments)>0)
    					{
    						$old_entry = $old_med_name.ltrim($old_med_comments, " | ").$old_med_medication_change;
    					}
    					else 
    					{
    						$old_entry = $old_med_name.ltrim($old_med_medication_change, " | ");
    					}
    				}
				}
				
				
				
				//add edit label
				if (!empty($old_entry) && $old_entry != "Schmerzpumpe ")
				{
					$attach = 'Änderung: ' . $old_entry . '  -> ' . $attach.'';
				}

				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt($shortcut);
				$cust->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
				$cust->recordid = $recordid;
				$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
				$cust->user_id = $userid;
				$cust->save();
				$medname='';
			}
			else
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt($shortcut);
				if ($isdelete == "1")
				{
					$cust->course_title = Pms_CommonData::aesEncrypt($medname . " wurde abgesetzt.");
				}
				$cust->user_id = $userid;
				
				$cust->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_deleted"); //i have added this, so we can better group in verlauf
				
				if (isset($_GET['_deleted_medis_contactform_cid']) && ! empty((int)$_GET['_deleted_medis_contactform_cid'])) {
				    //this is a delete performed from inside a contactform that has the medi-block
				    $cust->done_id = (int)$_GET['_deleted_medis_contactform_cid'];
				    $cust->done_name = Pms_CommonData::aesEncrypt(ContactForms::PatientCourse_DONE_NAME);
				}
				
				$cust->save();
				
				if (isset($_GET['_deleted_medis_contactform_cid']) && empty((int)$_GET['_deleted_medis_contactform_cid'])) {
				    //save to session ... because this is a new contactform.. so we can post back after you save this new cf
				    
				    $session_prop = isset($_GET['_cfhiddennounce']) && ! empty($_GET['_cfhiddennounce']) ? $_GET['_cfhiddennounce'] : null;
				    
				    if ($session_prop) {
				        
    				    $deleted_medis_contactform_cid_patientcourse_id = new Zend_Session_Namespace('deleted_medis_contactform_cid_patientcourse_id');
    				    
				        if (! isset($deleted_medis_contactform_cid_patientcourse_id->$session_prop) || ! is_array($deleted_medis_contactform_cid_patientcourse_id->$session_prop)) {
    				        $deleted_medis_contactform_cid_patientcourse_id->$session_prop = [];
				            
				        } 
    				    array_push($deleted_medis_contactform_cid_patientcourse_id->$session_prop, $cust->id);
				    }
				}
				

				$isdelete = '';
				$medname = '';
			}
		}
		elseif($fieldname=="frmpatientdrugplan" && empty($_POST['verlauf_edit']) && (isset($_POST['medication']) || isset($_POST['medication_block'])  || isset($_POST['bid']) || isset($_POST['add']['medication']) || isset($_POST['add_schmerze']['medication']) || isset($_POST['add']['bid'])) && (empty($_POST['skip_trigger']) || $_POST['skip_trigger'] == '0')  )
		{
// 		    print_r("A DOUA");exit;
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;


			$gmod=$event->getinvoker();

			//$meddosage = $event->getinvoker()->dosage;
			$recordid = $event->getinvoker()->id;
			$isbedarfs = $event->getinvoker()->isbedarfs;
			$iscrisis = $event->getinvoker()->iscrisis;
			$isivmed = $event->getinvoker()->isivmed;
			$isschmerzpumpe = $event->getinvoker()->isschmerzpumpe;
			$ispumpe = $event->getinvoker()->ispumpe;//ISPC-2833 Ancuta 01.03.2021
			$treatment_care = $event->getinvoker()->treatment_care;
			$isnutrition = $event->getinvoker()->isnutrition;
			$isintubated = $event->getinvoker()->isintubated; // ISPC-2176 
			$scheduled = $event->getinvoker()->scheduled;
			$old_med_values = $event->getinvoker()->_lastModified;
			$create_date = $event->getinvoker()->create_date;
			if($isschmerzpumpe == 1)
			{
				//TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
				$meddosage = round(str_replace(",", ".", $event->getinvoker()->dosage), 3);				
				//$meddosage24 = round($event->getinvoker()->dosage*24, 2);
				//--
			}
			else
			{
				$meddosage = $event->getinvoker()->dosage;
			}
			
			//TODO- Ancuta 23.02.2021
			$modules = new Modules();
			$isschmerzpumpe_unit_dosage = $modules->checkModulePrivileges("240", $clientid);
			
			
			
			if(empty($meddosage) || $meddosage == '0')// TODO-2603 Ancuta - 17.10.2019
			{
				$meddosage = "";
			}

			//ISPC-2110 p.4
			if ($isbedarfs == 1 && ! empty($gmod->dosage_interval) && is_string($meddosage)) {
			    $meddosage .= " Intervall: ".$gmod->dosage_interval;             //TODO-3243 Lore 26.06.2020
			}
			
			if($treatment_care == 1){
				$meds= Doctrine::getTable('MedicationTreatmentCare')->find($gmod->medication_master_id);
			} elseif($isnutrition == 1){
				$meds= Doctrine::getTable('Nutrition')->find($gmod->medication_master_id);
			} else{
				$meds= Doctrine::getTable('Medication')->find($gmod->medication_master_id);
			}
			
			if($meds)
			{
				$medarr = $meds->toArray();
				$medname = $medarr['name'];
			}
			$ipid=$event->getinvoker()->ipid;


			//ISPC-2329 pct.v,x)
/* 			$extra_m = new PatientDrugPlanExtra();
			$extra_data = $extra_m->get_patient_drugplan_extra($ipid, $clientid,$recordid);
            $old_unit = !empty($extra_data[$recordid]['unit']) ? $extra_data[$recordid]['unit'] : '';
            $indication = !empty($extra_data[$recordid]['indication']['name']) ? " | ".$extra_data[$recordid]['indication']['name'] : '';
            dd($extra_data, $recordid,$event->getinvoker()->toArray() ); */
			
			/*==============Medication get Shortcut ===================*/
			//ISPC-2833 Ancuta 01.03.2021 - add ispumpe condition 
			$prefix = "";
			if($isivmed == 0 && $isbedarfs == 1 && $isschmerzpumpe == 0 && $treatment_care == 0  && $scheduled == 0  && $ispumpe == 0)
			{
				$shortcut = "N";
			}
			elseif($isivmed == 1 && $isbedarfs == 0 && $isschmerzpumpe == 0 && $treatment_care == 0  && $scheduled == 0 && $ispumpe == 0)
			{
				$shortcut = "I";
			}
			elseif($isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 1 && $treatment_care == 0  && $scheduled == 0 && $ispumpe == 0)
			{
				$shortcut = "Q";
				$prefix = "Schmerzpumpe ";
			}
			elseif($isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 0 && $treatment_care == 0  && $scheduled == 0 && $ispumpe == 1 )
			{
				$shortcut = "Q";
				$prefix = "Perfusor/Pumpe  ";
			}
			elseif($isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 0 && $treatment_care == 1  && $scheduled == 0 && $ispumpe == 0)
			{
				$shortcut = "BP";
			}
			elseif($isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 0 && $treatment_care == 0 && $scheduled == 1  && $ispumpe == 0)
			{
				$shortcut = "MI";
			}
			elseif($iscrisis == 1 && $isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 0 && $treatment_care == 0 && $scheduled == 0  && $ispumpe == 0)
			{
				$shortcut = "KM";
			}
			elseif($isintubated == 1 && $iscrisis == 0 &&  $isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 0 && $treatment_care == 0 && $scheduled == 0  && $ispumpe == 0)
			{
				$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
			}
			else
			{
				$shortcut = "M";
			}
				
			/*==============Medication get Invoker comments===================*/
			if($event->getinvoker()->comments)
			{
				$comment = " | ".$event->getinvoker()->comments;
			}
			else
			{
				$comment ="";
			}
				
			/*==============Medication get Invoker medication_change==========*/
			if($event->getinvoker()->medication_change)
			{
				$medication_change = date('d.m.Y',strtotime($event->getinvoker()->medication_change));
			}
			else
			{
				$medication_change ="";
			}
			
				
			/*==============Medication get Invoker days interval==========*/
			if($event->getinvoker()->days_interval)
			{
				$days_interval = "  |  ".$event->getinvoker()->days_interval;
			}
			else
			{
				$days_interval = "";
			}
			//Carmen 17.08.2020 - error class not found
			/*if($_POST['add_sets'] != "1")
			{*/
			if(get_class($gmod->_table->getConnection()) != '')
			{
			//--				
				$rfc = new ReflectionClass(get_class($gmod->_table->getConnection()));
				foreach($rfc->getProperties() as $pr)
				{
					$pr->setAccessible(true);
					if($pr->getName() == 'tables')
					{
						$arr[$pr->getName()] = $pr->getValue($gmod->_table->getConnection());
					}
					$pr->setAccessible(false);
				}
				//Carmen 17.08.2020 - error class not found
				if($arr['tables']['Client']->getConnection() != '')
				{
				//--
					$crf = new ReflectionClass($arr['tables']['Client']->getConnection());
					foreach($crf->getProperties() as $prc)
					{
						$prc->setAccessible(true);
						if($prc->getName() == 'tables')
						{
							$arrc[$prc->getName()] = $prc->getValue($arr['tables']['Client']->getConnection());
						}
						$prc->setAccessible(false);
					}
					//Carmen 17.08.2020 - error class not found
					if($arrc['tables']['PatientDrugPlanExtra'] != "")
					{
					//--
						$crff = new ReflectionClass($arrc['tables']['PatientDrugPlanExtra']);
						
						foreach($crff->getProperties() as $prcf)
						{
							$prcf->setAccessible(true);
						
							if($prcf->getName() == '_identityMap')
							{
								$arrcf[$prcf->getName()] = $prcf->getValue($arrc['tables']['PatientDrugPlanExtra']);
							}
							$prcf->setAccessible(false);
						}
						
						foreach($arrcf['_identityMap'] as $extraobj)
						{
							$crp = new ReflectionClass($extraobj);
							foreach($crp->getProperties() as $prp)
							{
								$prp->setAccessible(true);
								$arrcfp[$prp->getName()] = $prp->getValue($extraobj);
								$prp->setAccessible(false);
							}
							
						}
			//Carmen 17.08.2020 - error class not found
					}
				}
			}
			//--
			
			//UNIT
			$medication_unit = MedicationUnit::client_medication_unit($clientid);
			$client_medication_extra = array();
			$client_medication_extra['unit'][0] = "i.E.";//TODO-3829 Ancuta 23.02.2021
			foreach($medication_unit as $k=>$unit){
				$client_medication_extra['unit'][$unit['id']] = $unit['unit'];
			}
			//INDICATIONS
			$medication_indications = MedicationIndications::client_medication_indications($clientid);
				
			foreach($medication_indications as $k=>$indication){
				$client_medication_extra['indication'][$indication['id']]['name'] = $indication['indication'];
				$client_medication_extra['indication'][$indication['id']]['color'] = $indication['indication_color'];
			}
			//var_dump($client_medication_extra['indication'][$arrcfp['_oldValues']['indication']]['name']); exit;
			$medindication = "";
			$oldvaluesindication = " | ";
			//Carmen 17.08.2020 - error class not found
			if(!empty($arrcfp) && in_array('indication', $arrcfp['_lastModified']))
			{
			//--
			    $medindication = !empty($client_medication_extra['indication'][$arrcfp['_data']['indication']]['name']) ? $client_medication_extra['indication'][$arrcfp['_data']['indication']]['name'] : "";
			    $oldvaluesindication = !empty($client_medication_extra['indication'][$arrcfp['_oldValues']['indication']]['name']) ? ' | '.$client_medication_extra['indication'][$arrcfp['_oldValues']['indication']]['name']." | " : " | ";
			}

			//TODO-3829 Ancuta 08.02.2021 + 23.02.2021
			$medunit = "";
			$oldvaluesunit = "";
			
			$unit_dosage = "";
			$old_unit_dosage = "";
			$unit_dosage_24h = "";
			$old_unit_dosage_24h = "";

			if(!empty($arrcfp) )
			{
			    if( array_key_exists('unit', $arrcfp['_data'])){
    			    $medunit = $client_medication_extra['unit'][$arrcfp['_data']['unit']];
			    }
			    
			    if( in_array('unit', $arrcfp['_lastModified'])){
				    $oldvaluesunit = $client_medication_extra['unit'][$arrcfp['_oldValues']['unit']];
			    } else{
			        if( array_key_exists('unit', $arrcfp['_data'])){
			            $oldvaluesunit = $medunit;
			        }
			    }
			    
			  
			    //TODO-3829 Ancuta 23.02.2021
			    if( array_key_exists('unit_dosage', $arrcfp['_data'])){
			        $unit_dosage = $arrcfp['_data']['unit_dosage'];
			    }
			    if( in_array('unit_dosage', $arrcfp['_lastModified'])){
			        $old_unit_dosage = $arrcfp['_oldValues']['unit_dosage'];
			    } else{
			        if( array_key_exists('unit_dosage', $arrcfp['_data'])){
			            $old_unit_dosage = $unit_dosage;
			        }
			    }
			    
			    if( array_key_exists('unit_dosage_24h', $arrcfp['_data'])){
			        $unit_dosage_24h = $arrcfp['_data']['unit_dosage_24h'];
			    }
			    if( in_array('unit_dosage_24h', $arrcfp['_lastModified'])){
			        $old_unit_dosage_24h = $arrcfp['_oldValues']['unit_dosage_24h'];
			    } else{
			        if( array_key_exists('unit_dosage_24h', $arrcfp['_data'])){
			            $old_unit_dosage_24h = $unit_dosage_24h;
			        }
			    }
			    //-- 
			    
			}
			//--
			//TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
			if(!empty($arrcfp) && in_array('dosage_24h_manual', $arrcfp['_lastModified']))
			{
				$old_med_dosage24h = $arrcfp['_oldValues']['dosage_24h_manual'];
			}
			//--
			
			/*==============Medication get System info==========*/
			$epid  = Pms_CommonData::getEpid($ipid);
			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
				
			/*==============Medication get Invoker - medication details==========*/
	 

			/*====================== Medication change date =====================*/
			if($old_med_values['medication_change']) {
				if($old_med_values['medication_change'] != "0000-00-00 00:00:00"){
					$old_med_medication_change = " | ".date('d.m.Y',strtotime($old_med_values['medication_change']));
				} elseif($old_med_values['medication_change'] == "0000-00-00 00:00:00" && $old_med_values['change_date'] != "0000-00-00 00:00:00" ){
					$old_med_medication_change = " | ".date('d.m.Y',strtotime($old_med_values['change_date']));
				} elseif($old_med_values['medication_change'] == "0000-00-00 00:00:00" && $old_med_values['change_date'] == "0000-00-00 00:00:00" ) {
					$old_med_medication_change = " | ".date('d.m.Y',strtotime($create_date));
				}

			} elseif (!array_key_exists('medication_change', $old_med_values)) {
				if(strlen(trim($medication_change)) > '0' && $medication_change != "0000-00-00 00:00:00")
				{
					$old_med_medication_change = " | ".$medication_change;
				}
				else
				{
					$old_med_medication_change = "";
				}
			}
			
			/*====================== Medication name =====================*/
			if($old_med_values['medication_master_id']) {
				if($treatment_care == 1){
					$oldmeds = Doctrine::getTable('MedicationTreatmentCare')->find($old_med_values['medication_master_id']);
				} elseif($isnutrition == 1){
					$oldmeds = Doctrine::getTable('Nutrition')->find($old_med_values['medication_master_id']);
				} else{
					$oldmeds = Doctrine::getTable('Medication')->find($old_med_values['medication_master_id']);
				}
				if($oldmeds){
					$oldmedarr = $oldmeds->toArray();
					$old_med_name = $prefix.$oldmedarr['name']." | ";
				}
			} elseif(!array_key_exists('medication_master_id', $old_med_values)) {
				$old_med_name = $prefix.$medname." | ";
			}
			
			/*====================== Medication comments =====================*/
			if($old_med_values['comments']) {
				$old_med_comments = " | ".$old_med_values['comments'];
			} elseif(!array_key_exists('comments', $old_med_values)) {
				$old_med_comments = $comment;
			}

			/*====================== Medication dosage=====================*/
			if($old_med_values['dosage']) {
				$old_med_dosage = $old_med_values['dosage'];
				//$old_med_dosage24h = $old_med_dosage*24; //TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
			} elseif(!array_key_exists('dosage', $old_med_values)) {
				$old_med_dosage = $meddosage;
				//$old_med_dosage24h = $old_med_dosage*24; //TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
			}
			
			/*====================== Medication days interval=====================*/
			if($old_med_values['days_interval']) {
				$old_med_days_interval = $old_med_values['days_interval'];
			}
			
			$old_entry = "";
			
		    if($scheduled == 1)
		    {
		        $old_entry = $old_med_name.$old_med_days_interval;
		    } 
		    elseif($treatment_care == 1)
		    {
		        $old_entry = $old_med_name.$old_med_comments;
		    } 
		    elseif($isschmerzpumpe == 1)
		    {
		        if($old_med_name != "")
		        {
    		        if($isschmerzpumpe_unit_dosage){
    		            
    		            if( (strlen ($old_med_dosage) > 0 || strlen($old_unit_dosage) >0) ){
    		                if($oldvaluesunit == "ml"){
    		                    
    		                    $old_med_dosage = round(str_replace(",", ".", $old_med_dosage), 3); //TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
    		                    $old_med_dosage24h = $old_med_dosage24h; //TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
    		                    $old_entry = $old_med_name.$oldvaluesindication.$old_med_dosage.$oldvaluesunit."/h |".$old_med_dosage24h.$oldvaluesunit."/24h |".$old_med_comments.$old_med_medication_change;
    		                } else{
    		                    //TODO-3923 Ancuta 05.03.2021
    		                    if(strlen($old_unit_dosage) > 0 ){
            		                $old_entry = $old_med_name.$oldvaluesindication.$old_unit_dosage.$oldvaluesunit."/h |".$old_unit_dosage_24h.$oldvaluesunit."/24h |".$old_med_comments.$old_med_medication_change;
    		                    } else{
    		                        $old_entry = $old_med_name.$oldvaluesindication.$old_med_dosage.$oldvaluesunit."/h |".$old_med_dosage24h.$oldvaluesunit."/24h |".$old_med_comments.$old_med_medication_change;
    		                    }
    		                }
    		            } else	{
                           $old_entry = $old_med_name.$oldvaluesindication.$old_med_comments.$old_med_medication_change;
    		            }
    		            
    		        } else {
    		            
    		            if(strlen($old_med_dosage)>0){
    		                
                            $old_med_dosage = round(str_replace(",", ".", $old_med_dosage), 3); //TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
                            $old_med_dosage24h = $old_med_dosage24h; //TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
                            $old_entry = $old_med_name.$oldvaluesindication.$old_med_dosage.$oldvaluesunit."/h |".$old_med_dosage24h.$oldvaluesunit."/24h |".$old_med_comments.$old_med_medication_change;
                            
    		            } else	{
                            $old_entry = $old_med_name.$oldvaluesindication.$old_med_comments.$old_med_medication_change;
    		            }
    		        }
		        }
		    }
		    else
		    {
    			if(strlen($old_med_dosage)>0) {
    			    if($old_med_name != "")
    			    {
                        $old_entry = $old_med_name.$oldvaluesindication.$old_med_dosage."|".$old_med_comments.$old_med_medication_change;
    			    }
    			} else {
					if($old_med_name != "")
					{
			   	 		$old_entry = $old_med_name.$oldvaluesindication.$old_med_comments.$old_med_medication_change;
					}
			    }
			}
			
			/*==============Medication get Invoker - medication details==========*/
			if($old_entry == "")
			{
				$medindication = "";
			}
			
            //INDICATIONS
            $medication_indicationss = MedicationIndications::client_medication_indications($clientid);
            foreach($medication_indicationss as $k=>$indication){
                $client_medication_extras['indication'][$indication['id']]['name'] = $indication['indication'];
                $client_medication_extras['indication'][$indication['id']]['color'] = $indication['indication_color'];
            }
            
            if($isbedarfs  == 1){
                $post_line = 0 ; 
                if(isset($_POST['medication_block']['isbedarfs'])){
                    foreach($_POST['medication_block']['isbedarfs'] as $column=>$rows){
                        if($column == 'medication'){
                            $post_line = array_search($medname, $rows);
                            if($post_line){
                                $indication_id = trim($_POST['medication_block']['isbedarfs']['indication'][$post_line]);
                                $medindication = !empty($client_medication_extras['indication'][$indication_id]['name']) ? ' | '.$client_medication_extras['indication'][$indication_id]['name'] : "";
                            }
                        }
                    }
                    
                }
            }
            
            //DOSAGE24H+INDICATION
            //TODO-3670 ISPC: SH_Travebogen-Rot_Gruen medipump patient Carmen 28.01.2021
            if($isschmerzpumpe  == 1){
            	$post_line = 0;
            	if(isset($_POST['medication_block']['isschmerzpumpe'])){
            		foreach($_POST['medication_block']['isschmerzpumpe'][1] as $column => $rows)
            		{
            			if($column == 'medication'){
            				$post_line = array_search($medname, $rows);
            				if($post_line){
            					$meddosage24 = trim($_POST['medication_block']['isschmerzpumpe'][1]['dosage_24h'][$post_line]);
            					$indication_id = trim($_POST['medication_block']['isschmerzpumpe'][1]['indication'][$post_line]);
            					$medindication = !empty($client_medication_extras['indication'][$indication_id]['name']) ? ' | '.$client_medication_extras['indication'][$indication_id]['name'] : "";
            					
								//TODO-3829 Ancuta 08.02.2021
            					$unit_id = trim($_POST['medication_block']['isschmerzpumpe'][1]['unit'][$post_line]);
            					$medunit = $client_medication_extra['unit'][$unit_id];
								//--
								
								//TODO-3829 Ancuta 23.02.2021
            					$unit_dosage = trim($_POST['medication_block']['isschmerzpumpe'][1]['unit_dosage'][$post_line]);
            					$unit_dosage_24h = trim($_POST['medication_block']['isschmerzpumpe'][1]['unit_dosage_24h'][$post_line]);
            					//-- 
            				}
            			}
            		}
            	}
            }
            //--
			if($scheduled == 1){
				 if($_POST['administrate_drug'] == "1"){
				 //$attach = $medname." wurde verabreicht.";
				     $attach = "Das Intervall des Medikaments ".$medname." wurde zurückgesetzt."; //ISPC-2329 p.w (10.09.2019)  //TODO-3243 Lore 26.06.2020
				 } else{
				 $attach = $prefix.$medname.$days_interval;
				
				 }
			 }
			 elseif($isschmerzpumpe ==1)
			 {
			     if ($isschmerzpumpe_unit_dosage) { // Module 240 // TODO-3829
                    if (strlen($meddosage) > 0 || strlen($unit_dosage) > 0) {
                        if($medunit == "ml"){
                            $attach = $prefix . $medname . ' ' . $medindication . ' | ' . $meddosage . $medunit . '/h | ' . $meddosage24 . $medunit . '/24h ' . $comment . ' | ' . $medication_change;
                        }
                        else
                        {
                            //TODO-3923 Ancuta 05.03.2021
                            if(strlen($unit_dosage) > 0 ){
                               $attach = $prefix . $medname . ' ' . $medindication . ' | ' . $unit_dosage . $medunit . '/h | ' . $unit_dosage_24h . $medunit . '/24h ' . $comment . ' | ' . $medication_change;
                            } else{
                                $attach = $prefix . $medname . ' ' . $medindication . ' | ' . $meddosage . $medunit . '/h | ' . $meddosage24 . $medunit . '/24h ' . $comment . ' | ' . $medication_change;
                            }
                        }
                        
                    } else {
                        $attach = $prefix . $medname . ' ' . $medindication . ' | ' . $comment . ' | ' . $medication_change;
                    }
                } else {
                    if (strlen($meddosage) > 0) {
                        $attach = $prefix . $medname . ' ' . $medindication . ' | ' . $meddosage . $medunit . '/h | ' . $meddosage24 . $medunit . '/24h ' . $comment . ' | ' . $medication_change;
                    } else {
                        $attach = $prefix . $medname . ' ' . $medindication . ' | ' . $comment . ' | ' . $medication_change;
                    }
                }
            }
			 else
			 {
				 if($_POST['administrate_drug'] == "1")
				 {
					 //$attach = $medname." wurde verabreicht.";
				     $attach = "Das Intervall des Medikaments ".$medname." wurde zurückgesetzt."; //ISPC-2329 p.w (10.09.2019)  //TODO-3243 Lore 26.06.2020
				 }
				 else
				 {
				  
					 if(strlen($meddosage)>0)
					 {
						 if($medindication != '')
						 {
						     $attach = $prefix.$medname.'  |  '.$medindication. ' | '.$meddosage.' '.$comment.' | '.$medication_change;
						 }
						 else
						 {
							 $attach = $prefix.$medname.'  |  '.$meddosage.' '.$comment.' | '.$medication_change;
						 }
					 }
					 else
					 {
						 if($medindication != '')
						 {
					 	    $attach = $prefix.$medname.' '.$medindication. ' | '.$comment.' | '.$medication_change;
						 }
						 else
						 {
					 	    $attach = $prefix.$medname.' '.$comment.' | '.$medication_change;
						 }
					 }
				}
			 } 
			 
			/*====================== Medication Edit - Verlauf entry =====================*/
			if(!empty($old_entry) && $old_entry != "Schmerzpumpe " && !$_POST['bid']){
				if($_POST['administrate_drug'] == "1"){
    				$attach = $attach;
				} else {
    				$attach = 'Änderung: '.$old_entry.' -> ' . $attach;
//     				$attach = "Änderung: \n".$old_entry."\n->\n " . $attach."\n";
				}
			}
			else if($_POST['bid'] > '0')
			{
				if (strlen(trim($event->getinvoker()->dosage)) > 0)
				{
					$attach = $medname ." | ". trim($event->getinvoker()->dosage) . " | " . $event->getinvoker()->comments;
				}
				else
				{
					$attach = $medname;
					if(strlen($comment)>0)
					{
					    $attach = $attach . " | " . $comment;
					}
				}
			}
			
			if($_POST['skip_verlauf'] == "1"){
			    $attach =""; // do not write in verlauf
			}

			if(!empty($attach))
			{
				if($eventid==1 || $eventid==2) // insert
				{

					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s",time());
					$cust->course_type=Pms_CommonData::aesEncrypt($shortcut);
					$cust->tabname=Pms_CommonData::aesEncrypt("patient_drugplan");
					$cust->recordid=$recordid;
					$cust->isstandby = $_POST['isstandby'];
					$cust->course_title=Pms_CommonData::aesEncrypt(addslashes($attach));
					$cust->user_id = $userid;
					if($gmod->medication_change !=  "0000-00-00 00:00:00")
					{
						$cust->done_date = date('Y-m-d', strtotime($gmod->medication_change)).' '.date('H:i:s', time());
					}
					else
					{
						$cust->done_date = date('Y-m-d H:i:s', time());
					}
					$cust->save();

					//				The tree steps to hell
					if($cust->id)
					{
						// 1. get client shortcuts!
						$courses = new Courseshortcuts();
						$shortcut_id = $courses->getShortcutIdByLetter($shortcut, $clientid);


						// 2. check if shortcut is shared
						$patient_share = new PatientsShare();
						$shared_data = $patient_share->check_shortcut($ipid, $shortcut_id);

						if($shared_data)
						{
							foreach($shared_data[$shortcut_id] as $shared)
							{
								// 3. salve to other patients
								$cust = new PatientCourse();
								$cust->ipid = $shared; //target ipid
								$cust->course_date = date("Y-m-d H:i:s", time());
								$cust->tabname = Pms_CommonData::aesEncrypt("patient_drugplan");
								$cust->course_type = Pms_CommonData::aesEncrypt($shortcut);
								$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
								$cust->user_id = $userid;
								$cust->source_ipid = $ipid;
								if(strlen($gmod->medication_change) > '0')
								{
									$cust->done_date = date('Y-m-d H:i:s', strtotime($gmod->medication_change)).' '.date('H:i:s', time());
								}
								$cust->save();
							}
						}
					}
				}
			}

		}
		elseif($fieldname == 'frmpatientdrugplan' && $eventid==1 && empty($_POST['verlauf_edit']) && (empty($_POST['skip_trigger']) || $_POST['skip_trigger'] == '0') )
		{
// 		    print_r("A TREIA");exit; 
			$gmod=$event->getinvoker();
			$isbedarfs = $event->getinvoker()->isbedarfs;
			$iscrisis = $event->getinvoker()->iscrisis;
			$isivmed = $event->getinvoker()->isivmed;
			$isschmerzpumpe = $event->getinvoker()->isschmerzpumpe;
			$ispumpe = $event->getinvoker()->ispumpe; //ISPC-2833 Ancuta 01.03.2021
			$treatment_care = $event->getinvoker()->treatment_care;
			$isnutrition = $event->getinvoker()->isnutrition;
			$scheduled = $event->getinvoker()->scheduled;
			$isintubated = $event->getinvoker()->isintubated; // ISPC-2176


			$prefix = "";

			if($isivmed == 0 && $isbedarfs == 1 && $isschmerzpumpe == 0  && $treatment_care == 0 && $scheduled == 0 && $ispumpe == 0)
			{
				$shortcut = "N";
			}
			elseif($isivmed == 1 && $isbedarfs == 0 && $isschmerzpumpe == 0  && $treatment_care == 0 && $scheduled == 0 && $ispumpe == 0)
			{
				$shortcut = "I";
			}
			elseif($isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 1  && $treatment_care == 0 && $scheduled == 0 && $ispumpe == 0)
			{
				$shortcut = "Q";
				$prefix = "Schmerzpumpe ";
			}
			elseif($isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 0  && $treatment_care == 0 && $scheduled == 0 && $ispumpe == 1)
			{
				$shortcut = "Q";
				$prefix = "Perfusor/Pumpe ";
			}
			elseif($isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 0  && $treatment_care == 1 && $scheduled == 0 && $ispumpe == 0)
			{
				$shortcut = "BP";
			}
			elseif($isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 0  && $treatment_care == 0 && $scheduled == 1 && $ispumpe == 0)
			{
				$shortcut = "MI";
			}
			elseif($iscrisis == 1 && $isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 0 && $treatment_care == 0 && $scheduled == 0 && $ispumpe == 0 )
			{
				$shortcut = "KM";
			}
			elseif($isintubated == 1 && $iscrisis == 0 && $isivmed == 0 && $isbedarfs == 0 && $isschmerzpumpe == 0 && $treatmen_care == 0 && $scheduled == 0  && $ispumpe == 0)
			{
				$shortcut = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
			}
			else
			{
				$shortcut = "M";
			}

			$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../public/log/medication_delete.log');
			$log = new Zend_Log($writer);

			if($gmod->isdelete==1)
			{
				if($treatment_care == 1){
					$meds= Doctrine::getTable('MedicationTreatmentCare')->find($gmod->medication_master_id);
				} elseif($isnutrition == 1){
					$meds= Doctrine::getTable('Nutrition')->find($gmod->medication_master_id);
				} else{
					$meds= Doctrine::getTable('Medication')->find($gmod->medication_master_id);
				}
				
				if($meds)
				{
					$medarr = $meds->toArray();
					$medname = $prefix.$medarr['name'];

				}
				$ipid=$event->getinvoker()->ipid;
				$meddosage = $event->getinvoker()->dosage;

				$epid  = Pms_CommonData::getEpid($ipid);

				$logininfo= new Zend_Session_Namespace('Login_Info');
				$userid = $logininfo->userid;

				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s",time());
				$cust->course_type=Pms_CommonData::aesEncrypt($shortcut);
				if($_REQUEST['act'] == "del"){
					$cust->course_title=Pms_CommonData::aesEncrypt($medname." wurde abgesetzt.");
					
					$cust->tabname = Pms_CommonData::aesEncrypt("patient_drugplan_deleted"); //i have added this, so we can better group in verlauf
					
				} elseif($_REQUEST['act'] == "rnw") {
					$cust->course_title=Pms_CommonData::aesEncrypt($medname." wurde wieder angesetzt.");
					
					$cust->tabname = Pms_CommonData::aesEncrypt("patient_drugplan"); //i have added this, so we can better group in verlauf
				}

				$cust->user_id = $userid;
				
			    
				
				if (isset($_GET['_deleted_medis_contactform_cid']) && ! empty((int)$_GET['_deleted_medis_contactform_cid'])) {
				    //this is a delete performed from inside a contactform that has the medi-block
				    $cust->done_id = (int)$_GET['_deleted_medis_contactform_cid'];
				    $cust->done_name = Pms_CommonData::aesEncrypt(ContactForms::PatientCourse_DONE_NAME);
				}
				
				$cust->save();
				
				if (isset($_GET['_deleted_medis_contactform_cid']) && empty((int)$_GET['_deleted_medis_contactform_cid'])) {
				    //save to session ... because this is a new contactform.. so we can post back after you save this new cf
				    
				    $session_prop = isset($_GET['_cfhiddennounce']) && ! empty($_GET['_cfhiddennounce']) ? $_GET['_cfhiddennounce'] : null;
				    
				    if ($session_prop) {
				        
    				    $deleted_medis_contactform_cid_patientcourse_id = new Zend_Session_Namespace('deleted_medis_contactform_cid_patientcourse_id');
    				    
				        if (! isset($deleted_medis_contactform_cid_patientcourse_id->$session_prop) || ! is_array($deleted_medis_contactform_cid_patientcourse_id->$session_prop)) {
    				        $deleted_medis_contactform_cid_patientcourse_id->$session_prop = [];
				            
				        } 
    				    array_push($deleted_medis_contactform_cid_patientcourse_id->$session_prop, $cust->id);
				    }
				}
				
				$log->info($gmod->isdelete.'-'.($gmod->medication_master_id).'---'.$medarr['name']." - ".$ipid." S- ".$shortcut." - ".$_REQUEST['act']);

			} else {				
				if($treatment_care == 1){
					$meds= Doctrine::getTable('MedicationTreatmentCare')->find($gmod->medication_master_id);
				}elseif($isnutrition == 1){
					$meds= Doctrine::getTable('Nutrition')->find($gmod->medication_master_id);
				} else{
					$meds= Doctrine::getTable('Medication')->find($gmod->medication_master_id);
				}
				
				if($meds)
				{
					$medarr = $meds->toArray();
					$medname = $prefix.$medarr['name'];
				}
				$ipid=$event->getinvoker()->ipid;
				$meddosage = $event->getinvoker()->dosage;

				$epid  = Pms_CommonData::getEpid($ipid);

				$logininfo= new Zend_Session_Namespace('Login_Info');
				$userid = $logininfo->userid;

				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s",time());
				$cust->course_type=Pms_CommonData::aesEncrypt($shortcut);
				$cust->course_title=Pms_CommonData::aesEncrypt($medname." wurde wieder angesetzt.");



				
				if (isset($_GET['_deleted_medis_contactform_cid'])) {
				    
				    $cust->tabname = Pms_CommonData::aesEncrypt("patient_drugplan"); //i have added this, so we can better group in verlauf
				    
				    if ( ! empty((int)$_GET['_deleted_medis_contactform_cid'])) {
    				    //this is a delete performed from inside a contactform that has the medi-block
    				    $cust->done_id = (int)$_GET['_deleted_medis_contactform_cid'];
    				    $cust->done_name = Pms_CommonData::aesEncrypt(ContactForms::PatientCourse_DONE_NAME);
				    }
				}
				
				$cust->save();
				
				if (isset($_GET['_deleted_medis_contactform_cid']) && empty((int)$_GET['_deleted_medis_contactform_cid'])) {
				    //save to session ... because this is a new contactform.. so we can post back after you save this new cf
				
				    $session_prop = isset($_GET['_cfhiddennounce']) && ! empty($_GET['_cfhiddennounce']) ? $_GET['_cfhiddennounce'] : null;
				
				    if ($session_prop) {
				
				        $deleted_medis_contactform_cid_patientcourse_id = new Zend_Session_Namespace('deleted_medis_contactform_cid_patientcourse_id');
				
				        if (! isset($deleted_medis_contactform_cid_patientcourse_id->$session_prop) || ! is_array($deleted_medis_contactform_cid_patientcourse_id->$session_prop)) {
				            $deleted_medis_contactform_cid_patientcourse_id->$session_prop = [];
				
				        }
				        array_push($deleted_medis_contactform_cid_patientcourse_id->$session_prop, $cust->id);
				    }
				}
				
				
				$cust->user_id = $userid;
				$cust->save();
			}
		}
		elseif($fieldname=="fdoc_caresalone" && isset($_POST['fdoc_caresalone']))
		{
			$ipid=$event->getinvoker()->ipid;
			$epid  = Pms_CommonData::getEpid($ipid);

			$fdocval = $event->getinvoker()->fdoc_caresalone;

			if($fdocval==1)
			{
				if(strlen($inputs['dataset'])>0)
				{
					$comment = $this->setTriggerPlaceHolders($event,$inputs['dataset'],$fieldname);
				}
				else
				{
					$comment  = "!! Hausarzt versorgt alleine !!";
				}

				$logininfo= new Zend_Session_Namespace('Login_Info');
				$userid = $logininfo->userid;

				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s",time());
				$cust->course_type=Pms_CommonData::aesEncrypt("K");
				$cust->course_title=Pms_CommonData::aesEncrypt($comment);
				$cust->isstandby = $_POST['isstandby'];
				$cust->user_id = $userid;
				$cust->save();
			}
			unset($_POST['fdoc_caresalone']);
		}
		elseif($fieldname=="frmdoctorletter")
		{
			if($_POST['status']==1)
			{
				$ipid=$event->getinvoker()->ipid;
				$epid  = Pms_CommonData::getEpid($ipid);

				$logininfo= new Zend_Session_Namespace('Login_Info');
				$userid = $logininfo->userid;

				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s",time());
				$cust->course_type=Pms_CommonData::aesEncrypt("K");
				$cust->course_title=Pms_CommonData::aesEncrypt("Arztbrief verfasst");
				$cust->user_id = $userid;
				$cust->save();
			}
		}
		elseif ($fieldname == "frmpatientsymptomatology")
		{
			$post = $_POST;
			foreach ($post['sym_value'] as $k_sym => $v_sym)
			{
				if (strlen(trim($v_sym)) == '0')
				{
					unset($_POST['sym_value'][$k_sym]);
					unset($_POST['symptom'][$k_sym]);
				}
			}

			foreach ($post['symptom'] as $k_sym => $v_sym)
			{
				if (!empty($post['sym_coment'][$k_sym]))
				{
					$_SESSION['sym_coment'][$v_sym] = $post['sym_coment'][$k_sym];
				}
			}
			$ipid = $event->getinvoker()->ipid;

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			$inputval = $event->getinvoker()->input_value;
			$symptomid = $event->getinvoker()->symptomid;
			$setid = $event->getinvoker()->setid;
			$entry_date = $event->getinvoker()->entry_date;

			$custom_description = $event->getinvoker()->custom_description;
			if (!empty($_SESSION['sym_coment']))
			{
				$coursearr['second_value'] = $_SESSION['sym_coment'][$symptomid];
			}
			$coursearr['input_value'] = $inputval;
			$coursearr['symptid'] = $symptomid;
			$coursearr['setid'] = $setid;
			$coursearr['entry'] = $setid;
			$coursearr['custom_description'] = $custom_description;

			if (!isset($_SESSION['finalcourse']))
			{
				$_SESSION['finalcourse'] = array();
			}

			if (!isset($_SESSION['symids']))
			{
				$_SESSION['symids'] = array();
			}

			//get set data
			if (!isset($_SESSION['all_sym']))
			{
				$_SESSION['all_sym'] = array();
			}

			$patsymval = new SymptomatologyValues();
			$patsymvalarr = $patsymval->getSymptpomatologyValues($setid, false);

			if ($patsymvalarr)
			{
				foreach ($patsymvalarr as $k_symarr => $v_symarr)
				{
					array_push($_SESSION['all_sym'], $v_symarr);
				}

				$_SESSION['all_sym'] = array_values(array_unique($_SESSION['all_sym']));
			}
			else
			{
				$sm = new SymptomatologyMaster();
				$symarrayayayayaya = $sm->getSymptpomatology($clientid);

				foreach ($symarrayayayayaya as $key_sym => $val_sym)
				{
					array_push($_SESSION['all_sym'], $key_sym);
				}

				$_SESSION['all_sym'] = array_values(array_unique($_SESSION['all_sym']));
			}

			array_push($_SESSION['symids'], $event->getinvoker()->id); //gathered inserted symptom id array
			if ((count($_SESSION['finalcourse']) < count($post['input_value'])) || (count($_SESSION['finalcourse']) < count($post['sym_value']) && $coursearr['input_value'] >= '0'))
			{
				array_push($_SESSION['finalcourse'], $coursearr);
			}

			if ((count($_POST['input_value']) == count($_SESSION['finalcourse']) && (count($_SESSION['symids']) == count($_SESSION['all_sym']) )) ||
					( count($_POST['sym_value']) == count($_SESSION['finalcourse']) && (count($_SESSION['symids']) == count($_SESSION['all_sym']) ))
			)
			{
				foreach ($_SESSION['finalcourse'] as $fkey => $fval)
				{
					if (strlen(trim($fval['input_value'])) > '0' && $post['iskvno'] != 1)
					{
						$finalcourse[$fkey] = $fval;
					}
				}
				if (sizeof($finalcourse) > 0)
				{

					$input_array = serialize($finalcourse);
					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("S");
					$cust->isserialized = 1;
					$cust->user_id = $userid;
					$cust->recorddata = serialize($_SESSION['symids']);
					$cust->course_title = Pms_CommonData::aesEncrypt($input_array);
					$cust->done_name = Pms_CommonData::aesEncrypt('symptomatology');
					$cust->done_date = $entry_date;
					$cust->save();
					//	 The tree steps to hell
					if ($cust->id)
					{
						// 1. get client shortcuts!
						$courses = new Courseshortcuts();
						$shortcut_id = $courses->getShortcutIdByLetter('S', $clientid);

						// 2. check if shortcut is shared
						$patient_share = new PatientsShare();
						$shared_data = $patient_share->check_shortcut($ipid, $shortcut_id);

						if ($shared_data)
						{
							foreach ($shared_data[$shortcut_id] as $shared)
							{
								// 3. salve to other patients
								$cust = new PatientCourse();
								$cust->ipid = $shared; //target ipid
								$cust->course_date = date("Y-m-d H:i:s", time());
								$cust->tabname = Pms_CommonData::aesEncrypt('patient_drugplan');
								$cust->course_type = Pms_CommonData::aesEncrypt('S');
								$cust->course_title = Pms_CommonData::aesEncrypt($input_array);
								$cust->isserialized = 1;
								$cust->user_id = $userid;
								$cust->done_name = Pms_CommonData::aesEncrypt('symptomatology');
								$cust->done_date = $entry_date;
								$cust->source_ipid = $ipid;
								$cust->save();
							}
						}
					}
				}
				unset($_SESSION['all_sym']);
				unset($_SESSION['symids']);
				unset($_SESSION['finalcourse']);
				unset($_SESSION['sym_coment']);
			}
		}
		elseif($fieldname=="frmpatientdischarge" && !isset($_POST["clone"]))
		{//ISPC-2614 + TODO-3481 Ancuta 06.10.2020 Added $_POST["clone"]  conndition to bypass trigger! 
			$ipid = $event->getinvoker()->ipid;
			$epid  = Pms_CommonData::getEpid($ipid);
			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;

			$loca = Doctrine::getTable('DischargeLocation')->find($_POST['discharge_location']);
			if($loca)
			{
				$locaarray = $loca->toArray();
			}
			$loctext = $locaarray[location];
			$loctext = Pms_CommonData::aesDecrypt($loctext);

			$dm  = new DischargeMethod();
			$dmarr = $dm->getDischargeMethodById($_POST['discharge_method']);

			$abbr = $dmarr[0][abbr];
			$dismethod = $dmarr[0][description];

			if($eventid==1)
			{
				if($gmods['discharge_date'])
				{
					$discharge_date = date("d.m.Y",strtotime($gmods['discharge_date'])).' '.$_POST['rec_timeh'].':'.$_POST['rec_timem'];
				}
			}

			if(strlen($inputs['dataset'])>0 && $eventid==2)
			{
				$thedischargedate = $_POST['discharge_date']." ".$_POST['rec_timeh'].":".$_POST['rec_timem'];

				$comment = str_replace("#dischargedate",$thedischargedate,$inputs['dataset']);
				$comment = str_replace("#dischargemethod",$dismethod,$comment);
				//ISPC-2645 Carmen 24.07.2020
				$comment = str_replace("#dischargelocation",$loctext,$comment);
				//--
				$comment = str_replace("#dischargecomment",$_POST['discharge_comment'],$comment);
				$comment = str_replace("#epid",$epid,$comment);
			}
			else
			{
				if($eventid==1)
				{
					if($gmods['discharge_date'])
					{
						$comment="Entlassungsdatum wurde auf"." ".$discharge_date." geändert \n";
					}

					if($gmods['discharge_method'])
					{
						$comment.="Entlassungsart : ".$dismethod."\n ";
					}

					if($gmods['discharge_location'])
					{
						$comment.="Entlassungsort : ".$loctext."\n ";
					}
					if($gmods['discharge_comment'])
					{
						$comment.="Entlassung Kommentar : ".$_POST['discharge_comment']."\n ";
					}
					
					if($gmods['death_wish'])
					{
					    $wish = array("1"=>"ja","2"=>"nein","3"=>"unbekannt");
					    
						$comment.="Sterbeort Wunsch : ".$wish[$_POST['death_wish']];
					}
				}
				else
				{
					//ISPC-2645 Carmen 24.07.2020
					$comment="Patient wurde am ".$_POST['discharge_date']." ".$_POST['rec_timeh'].":".$_POST['rec_timem']." entlassen \n Entlassungsart : ".$dismethod."\n Entlassungsort : ".$loctext."\n ".$_POST['discharge_comment'];
					//--
				}
			}
			
			if(strlen($comment) > 0 ){
    			$pc = new PatientCourse();
    			$pc->ipid = $ipid;
    			$pc->course_date = date("Y-m-d H:i:s",time());
    			$pc->course_type=Pms_CommonData::aesEncrypt("K");
    			$pc->course_title=Pms_CommonData::aesEncrypt($comment);
    			$pc->tabname=Pms_CommonData::aesEncrypt("discharge");
    			$pc->user_id = $userid;
    			$pc->recordid = $event->getinvoker()->id;
    			$pc->save();
			}
		}
		elseif($fieldname=="frmpatientfileupload" && $eventid==2)
		{
			$ipid = $event->getinvoker()->ipid;
			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$title  = $event->getinvoker()->title;

			$docid = $event->getinvoker()->id;


			$ctitle = "<a href='patient/patientfileupload?docid='".$file_name."'></a>";

			if($_POST['fileuploads']==1) // to check file is uploaded from patientfiles and not from forms.
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s",time());
				$cust->course_type=Pms_CommonData::aesEncrypt("K");
				$cust->user_id = $userid;
				$cust->recordid = $docid;
				$cust->tabname=Pms_CommonData::aesEncrypt("fileupload");
				$cust->course_title=$title;
				$cust->save();
			}
		}
	}

	private function setTriggerPlaceHolders($event,$message,$formname)
	{
		if($formname=="frmpatientcontact")
		{
			$placeholder['contactfirstname'] = Pms_CommonData::aesDecrypt($event->getinvoker()->cnt_first_name);
			$placeholder['contactlastname'] = Pms_CommonData::aesDecrypt($event->getinvoker()->cnt_last_name);
			$placeholder['contactphone'] = Pms_CommonData::aesDecrypt($event->getinvoker()->cnt_phone);
			$placeholder['contactaddress'] = Pms_CommonData::aesDecrypt($event->getinvoker()->cnt_street1);
		}
		if($formname=="frmpatient")
		{
			$placeholder['patientfirstname'] = Pms_CommonData::aesDecrypt($event->getinvoker()->first_name);
			$placeholder['patientlastname'] = Pms_CommonData::aesDecrypt($event->getinvoker()->last_name);
			$placeholder['patientaddress'] = Pms_CommonData::aesDecrypt($event->getinvoker()->street1);
			$placeholder['patientzip'] = Pms_CommonData::aesDecrypt($event->getinvoker()->zip);
			$placeholder['patientcity'] = Pms_CommonData::aesDecrypt($event->getinvoker()->city);
			$placeholder['patientphone'] = Pms_CommonData::aesDecrypt($event->getinvoker()->phone);
			$placeholder['patientmobile'] = Pms_CommonData::aesDecrypt($event->getinvoker()->mobile);
			$placeholder['patientbirthdate'] = Pms_CommonData::aesDecrypt($event->getinvoker()->birthd);
			$placeholder['patientgender'] = Pms_CommonData::aesDecrypt($event->getinvoker()->sex);
			$placeholder['patientadmissiondate'] = Pms_CommonData::aesDecrypt($event->getinvoker()->admission_date);
		}

		foreach($placeholder as $key=>$val)
		{
			$message = str_replace("#".$key."",$val,$message);
		}
		return $message;
	}

	public function createFormPatientContactPerson()
	{
		return $this->view->render("trigger/formtriggerinputs/CourseDocumentation/addText.html");
	}

	public function createFormPatient()
	{
		return $this->view->render("trigger/formtriggerinputs/CourseDocumentation/addText.html");
	}

	public function createFormAssignusertoPatient()
	{
		return $this->view->render("trigger/formtriggerinputs/CourseDocumentation/addText.html");
	}
	public function createFormPatientHealthInsurance()
	{
		return $this->view->render("trigger/formtriggerinputs/CourseDocumentation/addText.html");
	}
	public function createFormPatientDischarge()
	{
		return $this->view->render("trigger/formtriggerinputs/CourseDocumentation/addText.html");
	}
}
?>