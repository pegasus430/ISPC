<?php

require_once("Pms/Form.php");

class Application_Form_DoctorLetterZapv extends Pms_Form {

	public function validate($post)
	{

		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['status']))
		{
			$this->error_message['status'] = $Tr->translate('error_status');
			$error = 2;
		}

		if($error == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function InsertData($post)
	{
			
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$cust = new DoctorLetterZapv();
		$cust->ipid = $ipid;
		$cust->client = $clientid;
		$cust->subject = Pms_CommonData::aesEncrypt($post['subject']);
		$cust->address = Pms_CommonData::aesEncrypt($post['address']);
		$cust->letter_date = date('Y-m-d H:i:s', strtotime($post['letter_date']));
		$cust->letter_docfax = Pms_CommonData::aesEncrypt($post['letter_docfax']);
		$cust->letter_username = Pms_CommonData::aesEncrypt($post['letter_username']);
		$cust->content1 = Pms_CommonData::aesEncrypt($post['content']);
		$cust->footer = Pms_CommonData::aesEncrypt($post['footer']);
		$cust->patientmaster_chk = $post['patientmaster_chk'];
		$cust->verord_status = $post['verord_status'];
		$cust->sapv_periods = Pms_CommonData::aesEncrypt($post['sapv_periods']);
		$cust->main_diagnosis = Pms_CommonData::aesEncrypt($post['main_diagnosis']);
		$cust->complex_symptom = implode(', ', $post['complex_symptom']);
		$cust->symptomatics_str = Pms_CommonData::aesEncrypt($post['symptomatics_str']);
		$cust->medication = Pms_CommonData::aesEncrypt($post['medication_list']);
		$cust->medi_action = implode(', ', $post['action']);
		$cust->measures_str = Pms_CommonData::aesEncrypt($post['measures_str']);
		$cust->status = $post['status'];
		$cust->lettertype = $post['lettertype'];
		$cust->signature = $post['signature'][0];
		$cust->save();

		if($post['status'] == 1)
		{
			$this->inserttoDocs($post, $cust->id);
		}
		return $cust;
	}

	public function UpdateData($post)
	{
		$cust = Doctrine::getTable('DoctorLetterZapv')->find($_GET['lid']);
		$cust->subject = Pms_CommonData::aesEncrypt($post['subject']);
		$cust->address = Pms_CommonData::aesEncrypt($post['address']);
		$cust->letter_date = date('Y-m-d H:i:s', strtotime($post['letter_date']));
		$cust->letter_docfax = Pms_CommonData::aesEncrypt($post['letter_docfax']);
		$cust->letter_username = Pms_CommonData::aesEncrypt($post['letter_username']);
		$cust->content1 = Pms_CommonData::aesEncrypt($post['content']);
		$cust->footer = Pms_CommonData::aesEncrypt($post['footer']);
		$cust->patientmaster_chk = $post['patientmaster_chk'];
		$cust->verord_status = $post['verord_status'];
		$cust->sapv_periods = Pms_CommonData::aesEncrypt($post['sapv_periods']);
		$cust->main_diagnosis = Pms_CommonData::aesEncrypt($post['main_diagnosis']);
		$cust->complex_symptom = implode(', ', $post['complex_symptom']);
		$cust->symptomatics_str = Pms_CommonData::aesEncrypt($post['symptomatics_str']);
		$cust->medication = Pms_CommonData::aesEncrypt($post['medication_list']);
		$cust->medi_action = implode(', ', $post['action']);
		$cust->measures_str = Pms_CommonData::aesEncrypt($post['measures_str']);
		$cust->status = $post['status'];
		$cust->signature = $post['signature'][0];
		$cust->save();

		if($post['status'] == 1)
		{
			$this->inserttoDocs($post, $cust->id);
		}
		return $cust;
	}

	private function inserttoDocs($post, $idss)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$dlSession = new Zend_Session_Namespace('doctorLetterSession');
		$dlSession->tmpstmp = Pms_CommonData::uniqfolder(PDF_PATH);
		$dlSession->idsss = $idss;

		$ipid = Pms_CommonData::getIpid($decid);
		$cust = new PatientFileUpload();
		$cust->title = Pms_CommonData::aesEncrypt($post['subject']);
		$cust->ipid = $ipid;
		$cust->file_name = Pms_CommonData::aesEncrypt($dlSession->tmpstmp . "/doctorletter" . $idss . ".pdf");
		$cust->file_type = Pms_CommonData::aesEncrypt("pdf");
		$cust->system_generated = "1";
		$cust->save();

		if($post['lettertype'] == '23')
		{
			$tabname = 'drletter_erstverordnung_zapv';
		}
		else if($post['lettertype'] == '24')
		{
			$tabname = 'drletter_folgeverordnung_zapv';
		}

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s", time());
		$cust->course_type = Pms_CommonData::aesEncrypt("K");
		$cust->course_title = Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt($tabname);
		$cust->recordid = $idss;
		$cust->user_id = $userid;
		$cust->save();
	}

	public function test($func)
	{

		$func();
	}

}

?>