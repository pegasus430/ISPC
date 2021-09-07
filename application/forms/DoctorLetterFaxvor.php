<?php

require_once("Pms/Form.php");

class Application_Form_DoctorLetterFaxvor extends Pms_Form
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
		$cust = new DoctorLetterFaxvor();
		$cust->letter_date = $post['letter_date'];
		$cust->subject = Pms_CommonData::aesEncrypt($post['subject']);
		$cust->ipid = $ipid;
		$cust->selectedchecks = $post['selectedchecks'];
		$cust->content1 = Pms_CommonData::aesEncrypt($post['content1']);
		$cust->content2 = Pms_CommonData::aesEncrypt($post['content2']);
		$cust->content3 = Pms_CommonData::aesEncrypt($post['content3']);
		$cust->address = Pms_CommonData::aesEncrypt($post['address']);
		$cust->status = $post['status'];
		$cust->lettertype = $post['lettertype'];

		$cust->sapv_arzt = $post['sapv_arzt'];
		$cust->sapv_ppflegekraft = $post['sapv_ppflegekraft'];
		$cust->telefonat = $post['telefonat'];
		$cust->telefonat_von = $post['telefonat_von'];
		$cust->telefonat_bis = $post['telefonat_bis'];
		$cust->letter_docfax = $post['letter_docfax'];
		$cust->km_doppelt = $post['km_doppelt'];
		$cust->fahrdauer = $post['fahrdauer'];

		$cust->save();

		if($post['status']==1)
		{
			$this->inserttoDocs($post,$ids =$cust->id);
		}
		return $cust;
	}

	public function UpdateData($post)
	{
		$cust = Doctrine::getTable('DoctorLetterFaxvor')->find($_GET['lid']);
		$cust->letter_date = $post['letter_date'];
		$cust->subject = Pms_CommonData::aesEncrypt($post['subject']);
		$cust->content1 = Pms_CommonData::aesEncrypt($post['content1']);
		$cust->content2 = Pms_CommonData::aesEncrypt($post['content2']);
		$cust->content3 = Pms_CommonData::aesEncrypt($post['content3']);
		$cust->selectedchecks = $post['selectedchecks'];
		$cust->address = Pms_CommonData::aesEncrypt($post['address']);
		$cust->status = $post['status'];
		$cust->letter_docfax = $post['letter_docfax'];


		$cust->sapv_arzt = $post['sapv_arzt'];
		$cust->sapv_ppflegekraft = $post['sapv_ppflegekraft'];
		$cust->telefonat = $post['telefonat'];
		$cust->telefonat_von = $post['telefonat_von'];
		$cust->telefonat_bis = $post['telefonat_bis'];
		$cust->km_doppelt = $post['km_doppelt'];
		$cust->fahrdauer = $post['fahrdauer'];

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
		$cust->tabname = Pms_CommonData::aesEncrypt("drletterfaxvor");
		$cust->recordid = $idss;
		$cust->user_id = $userid;
		$cust->save();
		 
	}
	 
	public function test($func){

		$func();

	}


}

?>