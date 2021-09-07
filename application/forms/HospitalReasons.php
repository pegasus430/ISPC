<?php

require_once("Pms/Form.php");

class Application_Form_HospitalReasons extends Pms_Form {

	public function validate($post) {
		$error = 0;
		$val = new Pms_Validation();
		$Tr = new Zend_View_Helper_Translate();
		if (!$val->isstring($post['reason'])) {
			$this->error_message['reason'] = $Tr->translate('entername');
			$error = 1;
		}
		if ($error == 0) {
			return true;
		}

		return false;
	}

	public function InsertData($post) {

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$hospreason = new HospitalReasons();
		$hospreason->reason = $post['reason'];
		$hospreason->clientid = $logininfo->clientid;

		$hospreason->save();
		return $hospreason;
	}

	public function UpdateData($post) {
		$hospreason = Doctrine::getTable('HospitalReasons')->find($_GET['id']);
		$hospreason->reason = $post['reason'];
		$hospreason->save();
	}
}

?>