<?php

// require_once 'Pms/Triggers.php';

class application_Triggers_AddValuetoDiagnosis extends Pms_Triggers
{

	public function triggerAddValuetoDiagnosis($event,$inputs,$fieldname,$fieldid,$eventid,$gpost)
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
		
		
		$source_ipid = $event->getinvoker()->source_ipid;
		
		

		if($fieldname=="course_type" && isset($_POST["course_title"]) && !$source_ipid && $notfromsync)
		{
			$course_type=$event->getinvoker()->course_type;
			$course_type = Pms_CommonData::aesDecrypt($course_type);

			$course_title=$event->getinvoker()->course_title;
			$course_title = Pms_CommonData::aesDecrypt($course_title);
			$ipid=$event->getinvoker()->ipid;
			
			$skip_diagnosis = false;
			if($course_type == 'HS')
			{
				//check if patient has HS
				if(PatientDiagnosis::check_hs_diagnosis($clientid, $ipid))
				{
					//cancel diagnosis insert
					$skip_diagnosis = true;
				}
				else
				{
					//continue
					$skip_diagnosis = false;
				}
			}
			else
			{
				$skip_diagnosis = false;
			}
			
			if($skip_diagnosis === false)
			{
				$course_title =explode("|",$course_title);
				if(count($course_title) == 3 && !empty($course_title[0])){
					$id_icd = $course_title[0];
					$icd = $course_title[1];
					$text = $course_title[2];
				} else if(count($course_title) == 3 && empty($course_title[0])) {
					$id_icd = "0";
					$icd = $course_title[1];
					$text = $course_title[2];
				}
				$res = new DiagnosisText();
				$res->clientid = $clientid;
				$res->free_name = $text;
				$res->icd_primary = $icd;
				$res->save();
				$id = $res->id;

				if($course_type=="H")
				{
					$abb = "'HD'";
					$dg = new DiagnosisType();
					$darr = $dg->getDiagnosisTypes($clientid,$abb);
					$diagnotype = $darr[0]['id'];
				}
				else if($course_type=="HS")
				{
					$abb = "'HS'";
					$dg = new DiagnosisType();
					$darr = $dg->getDiagnosisTypes($clientid,$abb);
					$diagnotype = $darr[0]['id'];
				}
				else
				{
					$abb = "'ND'";
					$dg = new DiagnosisType();
					$darr = $dg->getDiagnosisTypes($clientid,$abb);
					$diagnotype = $darr[0]['id'];
				}



				$cust = new PatientDiagnosis();
				$cust->ipid = $ipid;
				$cust->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
				$cust->diagnosis_id = $id;
				if($id_icd != 0){
					$cust->icd_id = $id_icd;
				}
				$cust->diagnosis_type_id =$diagnotype;
				$cust->save();
			}
		}
	}
}
?>