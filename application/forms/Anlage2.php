<?php
require_once("Pms/Form.php");

class Application_Form_Anlage2 extends Pms_Form{

	public function insert_anlage2($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$ins = new Anlage2();
		$ins->ipid = $ipid;
		
		$ins->admission_date = date('Y-m-d',strtotime($post['admission_date']));
		$ins->admission = $post['admission'];
		$ins->location = $post['location'];
		$ins->patient_care = $post['patient_care'];
		$ins->members_included= $post['members_included'];
		$ins->icd_diagnosis= $post['icd_diagnosis'];
		if ($post['diagnosis_date'] > 0)
		{
			$dateadm = explode(".", $post['diagnosis_date']);
			$diagnosisdate = date("Y-m-d", mktime(0, 0, 0, 01, $dateadm[0], $dateadm[1]));
		}
		$ins->diagnosis_date = $diagnosisdate;
		
		if(count($post['participants']) >0 )
		{
			$ins->participants = implode(',' ,$post['participants']);
		}
		
		$ins->pain_therapy = $post['pain_therapy'];
		if(count($post['name_therapy']) >0 )
		{
			$ins->name_therapy = implode(',' ,$post['name_therapy']);
		}
		$ins->pain_level = $post['pain_level'];
		if(count($post['medication_form']) >0 )
		{
			$ins->medication_form = implode(',' ,$post['medication_form']);
		}
		
		$ins->wound_therapy = $post['wound_therapy'];
		
		//print_r($post);exit;
		$ins->save();
		$id = $ins->id;

		if($id > 0)
		{
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "Anlage 2 Formular  hinzugefügt";
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->tabname = Pms_CommonData::aesEncrypt('anlage2_form');
			$custcourse->recordid = $id;
			$custcourse->done_name = Pms_CommonData::aesEncrypt('anlage2_form');
			$custcourse->done_id = $id;
			$custcourse->save();
				
			return $id;
		}
		else
			
		{
			return false;
		}
	}
	
	public function update_anlage2($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
	
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
	
		$upd = Doctrine::getTable('Anlage2')->findOneById($post['saved_id']);
			
		$upd->admission_date = date('Y-m-d',strtotime($post['admission_date']));
		$upd->admission = $post['admission'];
		$upd->location = $post['location'];
		$upd->patient_care = $post['patient_care'];
		$upd->members_included= $post['members_included'];
		$upd->icd_diagnosis= $post['icd_diagnosis'];
		if ($post['diagnosis_date'] > 0)
		{
			$dateadm = explode(".", $post['diagnosis_date']);
			$diagnosisdate = date("Y-m-d", mktime(0, 0, 0, 01, $dateadm[0], $dateadm[1]));
		}
		$upd->diagnosis_date = $diagnosisdate;
		if(count($post['participants']) >0 )
		{
			$upd->participants = implode(',' ,$post['participants']);
		}
	
		$upd->pain_therapy = $post['pain_therapy'];
		if(count($post['name_therapy']) >0 )
		{
			$upd->name_therapy = implode(',' ,$post['name_therapy']);
		}
		$upd->pain_level = $post['pain_level'];
		if(count($post['medication_form']) >0 )
		{
			$upd->medication_form = implode(',' ,$post['medication_form']);
		}
	
		$upd->wound_therapy = $post['wound_therapy'];
	
		//print_r($post);exit;
		$upd->save();
		
		
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "Anlage 2 Formular  wurde editiert";
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->tabname = Pms_CommonData::aesEncrypt('anlage2_form');
			$custcourse->recordid = $post['saved_id'];
			$custcourse->done_name = Pms_CommonData::aesEncrypt('anlage2_form');
			$custcourse->done_id = $post['saved_id'];
			$custcourse->save();
		
	}
}