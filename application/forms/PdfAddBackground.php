<?php

require_once("Pms/Form.php");

class Application_Form_PdfAddBackground extends Pms_Form
{

	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$error = 0;
		$val = new Pms_Validation();
		if(!$val->integer($post['client']) || $post['client'] <= 0){
			$this->error_message['client']=$Tr->translate('selectclient'); $error++;
		}
		if(!$val->integer($post['pdf_type']) || $post['pdf_type'] <= 0){
			$this->error_message['pdf_type']=$Tr->translate('selectpdf_type'); $error++;
		}
		if(empty($_FILES['image']['name']) || $_FILES['image']['error'] != 0 || !is_uploaded_file($_FILES['image']['tmp_name'])){
			$this->error_message['uploadimage']=$Tr->translate('uploadimageerror');
			$error++;
		}

		if($error == 0)
		{
		 return true;
		}

		return false;
	}

	public function validate_user_form($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$error = 0;
		$val = new Pms_Validation();
		if(!$val->integer($post['pdf_type']) || $post['pdf_type'] <= 0){
			$this->error_message['pdf_type']=$Tr->translate('selectpdf_type'); $error++;
		}
		if(empty($_FILES['image']['name']) || $_FILES['image']['error'] != 0 || !is_uploaded_file($_FILES['image']['tmp_name'])){
			$this->error_message['uploadimage']=$Tr->translate('uploadimageerror');
			$error++;
		}

		if($error == 0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post, $filename)
	{
		$pdfbg = new PdfBackgrounds();
		$pdfbg->client = $post['client'];
		$pdfbg->pdf_type = $post['pdf_type'];
		$pdfbg->filename = $filename;
		$pdfbg->date_added = time();
		$pdfbg->save();

		return $pdfbg->id;
	}

	public function InsertUserData($post, $filename)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$pdfbg = new UserPdfBackgrounds();
		$pdfbg->client = $clientid;
		$pdfbg->user = $userid;
		$pdfbg->pdf_type = $post['pdf_type'];
		$pdfbg->filename = $filename;
		$pdfbg->date_added = time();
		$pdfbg->save();

		return $pdfbg->id;
	}

	 
}

?>