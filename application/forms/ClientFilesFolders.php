<?php

require_once("Pms/Form.php");

class Application_Form_ClientFilesFolders extends Pms_Form
{

	public function validatefolder($post)
	{

		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
		if(!$val->isstring($post['folder_name'])){
			$this->error_message['folder_name']=$Tr->translate("enterfoldername"); $error=2;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function validatecheckbox($post)
	{

		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
		if(count($post['msg_id'])<1){
			$this->error_message['msg_id']=$this->view->translate('selectatleastone'); $error=2;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}


	public function InsertFolderData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$folder = new ClientFilesFolders();
		$folder->clientid = $logininfo->clientid;
		$folder->folder_name = Pms_CommonData::aesEncrypt($post['folder_name']);
		$folder->save();

	}

	public function EditFolderData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$folder = Doctrine::getTable('ClientFilesFolders')->find($_GET['id']);
		$folder->folder_name = Pms_CommonData::aesEncrypt($post['folder_name']);
		$folder->save();
	}

}

?>