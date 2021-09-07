<?php

require_once("Pms/Form.php");

class Application_Form_RosterFileUpload extends Pms_Form {

	public function validate($post) {

		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$val = new Pms_Validation();

		if (strlen($_SESSION['filename']) < 1) {
			$this->error_message['filename'] = $Tr->translate("uploadcsvfile");
			$error = 2;
		}

		if ($error == 0) {
			return true;
		}

		return false;
	}

	public function InsertData($post) {
		if (strlen($post['title']) < 1) {
			$fl = explode(".", $_SESSION['filetitle']);

			$post['title'] = $fl[0];
		}
		$cust = new RosterFileUpload();
		$cust->title = Pms_CommonData::aesEncrypt($post['title']);
		$cust->clientid = $post['clientid'];
		$cust->file_name = Pms_CommonData::aesEncrypt(addslashes($_SESSION['filename'])); //$post['fileinfo']['filename']['name'];
		$cust->file_type = Pms_CommonData::aesEncrypt($post['filetype']);
		$cust->save();
		return $cust;
	}

	public function deleteFile($dids) {
		$fluplod = Doctrine::getTable('RosterFileUpload')->find($dids);
		$fluplod->isdeleted = 1;
		$fluplod->save();
	}

}

?>