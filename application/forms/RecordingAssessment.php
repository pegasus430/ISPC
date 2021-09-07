<?php

require_once("Pms/Form.php");

class Application_Form_RecordingAssessment extends Pms_Form{

	public function insertRecordingAssessment($post, $ipid)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;

		$ins = new RecordingAssessment();
		$ins->ipid = $ipid;


		$ins->orientierung_voll= join(",",$post['orientierung_voll']);
		$ins->orientierung_teilweise= join(",",$post['orientierung_teilweise']);
		$ins->orientierung_schwer= join(",",$post['orientierung_schwer']);
		$ins->orientierung_desorientiert= join(",",$post['orientierung_desorientiert']);
		$ins->who = $post['who'];
		$ins->save();
			
		$result = $ins->id;
		$custcourse = new PatientCourse();
		$custcourse->ipid = $ipid;
		$custcourse->course_date = date("Y-m-d H:i:s", time());
		$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
		$comment = "Aufnahmeassessment Formular wurde angelegt";
		$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
		$custcourse->user_id = $userid;
		$custcourse->tabname = Pms_CommonData::aesEncrypt('recording_assesment');
		$custcourse->recordid = $result;
		$custcourse->save();

		if ($ins->id > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	public function updateRecordingAssessment($post, $ipid)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$last = $post['recording_assessment_id'];


		if(!empty($last)){
			$ins = Doctrine::getTable('RecordingAssessment')->findOneByIdAndIpid($last, $ipid);
				
			$ins->orientierung_voll= join(",",$post['orientierung_voll']);
			$ins->orientierung_teilweise= join(",",$post['orientierung_teilweise']);
			$ins->orientierung_schwer= join(",",$post['orientierung_schwer']);
			$ins->orientierung_desorientiert= join(",",$post['orientierung_desorientiert']);
				
			$ins->who = $post['who'];

			$ins->diagnosen = $post['diagnosen'];
			$ins->save();

			//formular editat
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "Aufnahmeassessment Formular wurde editiert.";
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->save();
				
		}
	}
}
?>