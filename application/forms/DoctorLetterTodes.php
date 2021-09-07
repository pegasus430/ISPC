<?php

require_once("Pms/Form.php");

class Application_Form_DoctorLetterTodes extends Pms_Form
{
	public function validate($post)
	{

		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['subject'])){
			$this->error_message['subject']=$Tr->translate('subject_error'); $error=1;
		}
		if(!$val->isstring($post['status'])){
			$this->error_message['status']=$Tr->translate('error_status'); $error=2;
		}

		if($error==0)
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

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$cust = new DoctorLetterTodes();
		$cust->letter_date = $post['letter_date'];
		$cust->subject = Pms_CommonData::aesEncrypt($post['subject']);
		$cust->ipid = $ipid;
		$cust->selectedchecks = $post['selectedchecks'];
		$cust->content1 = Pms_CommonData::aesEncrypt($post['content1']);
		$cust->content2 = Pms_CommonData::aesEncrypt($post['content2']);
		$cust->address = Pms_CommonData::aesEncrypt($post['address']);
		$cust->status = $post['status'];
		$cust->lettertype = $post['lettertype'];
		$cust->letter_docfax = $post['letter_docfax'];
		$cust->letter_username = $post['letter_username'];
		$cust->sapv_drop = $post['sapv_drop'];
		$cust->beginn_sapv = $post['beginn_sapv'];
		$cust->save();

		if($post['status']==1)
		{
			$this->inserttoDocs($post,$ids =$cust->id);
		}
		return $cust;
	}

	public function UpdateData($post)
	{
		$cust = Doctrine::getTable('DoctorLetterTodes')->find($_GET['lid']);
		$cust->letter_date = $post['letter_date'];
		$cust->subject = Pms_CommonData::aesEncrypt($post['subject']);
		$cust->content1 = Pms_CommonData::aesEncrypt($post['content1']);
		$cust->content2 = Pms_CommonData::aesEncrypt($post['content2']);
		$cust->selectedchecks = $post['selectedchecks'];
		$cust->address = Pms_CommonData::aesEncrypt($post['address']);
		$cust->status = $post['status'];
		$cust->letter_username = $post['letter_username'];

		$cust->sapv_drop = $post['sapv_drop'];
		$cust->beginn_sapv = $post['beginn_sapv'];
		$cust->save();

		if($post['status']==1)
		{
			$this->inserttoDocs($post,$ids =$cust->id);
		}
		return $cust;
	}
	 
	private function inserttoDocs($post,$idss)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
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
		$cust->file_name = Pms_CommonData::aesEncrypt($dlSession->tmpstmp."/doctorletter".$idss.".pdf");
		$cust->file_type = Pms_CommonData::aesEncrypt("pdf");
		$cust->system_generated = "1";
		$cust->save();
		 
		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("drlettertodes");
		$cust->recordid = $idss;
		$cust->user_id = $userid;
		$cust->save();

	}

	public function test($func){

		$func();

	}


}

?>