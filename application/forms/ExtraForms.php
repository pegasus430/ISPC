<?php

class Application_Form_ExtraForms extends Pms_Form{


	public function validate($post)
	{

		$error=0;
		$val = new Pms_Validation();
		$Tr = new Zend_View_Helper_Translate();
		if(!$val->isstring($post['formname'])){
			$this->error_message['formname']="<br>".$Tr->translate('enterformname'); $error=2;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}


	public function InsertData($post)
	{
		$frm = new ExtraForms();
		$frm->formname = $post['formname'];
		$frm->save();
	  


		if(is_array($_POST['clientid']))
		{

			foreach($_POST['clientid'] as $key=>$val)
			{
				$frmclient = new ExtraFormsClient();
				$frmclient->formid= $frm->id;
				$frmclient->clientid = $val;
				$frmclient->save();
			}
				
		}

	}


	public function UpdateData($post)
	{


		$frm = Doctrine_Core::getTable('ExtraForms')->find($_GET['id']);
		$frm->formname = $post['formname'];
		$frm->save();
	  

		$q = Doctrine_Query::create()
		->delete("ExtraFormsClient")
		->where("formid = ?", $_GET['id']);
		$q->execute();
			
		if(is_array($_POST['clientid']))
		{
			foreach($_POST['clientid'] as $key=>$val)
			{
				$frmclient = new ExtraFormsClient();
				$frmclient->formid= $frm->id;
				$frmclient->clientid = $val;
				$frmclient->save();
			}
				
		}

	}

}

?>