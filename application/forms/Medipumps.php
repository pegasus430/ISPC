<?php

require_once("Pms/Form.php");

class Application_Form_Medipumps extends Pms_Form {

	public function validate($post) {
		$error = 0;
		$val = new Pms_Validation();
		$Tr = new Zend_View_Helper_Translate();
		if (!$val->isstring($post['medipump'])) {
			$this->error_message['medipump'] = $Tr->translate('medipump');
			$error = 1;
		}
		if (!$val->isstring($post['shortcut'])) {
			$this->error_message['shortcut'] = $Tr->translate('shortcut');
			$error = 1;
		}

		if ($error == 0) {
			return true;
		}

		return false;
	}

	public function InsertData($post) {

		$logininfo = new Zend_Session_Namespace('Login_Info');

		$med = new Medipumps();
		$med->medipump = $post['medipump'];
		$med->shortcut = $post['shortcut'];
		$med->clientid = $logininfo->clientid;
		$med->save();
		return $med;
	}


	public function UpdateData($post,$medipump) {
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$med = Doctrine::getTable('Medipumps')->find($medipump);
		$med->medipump = $post['medipump'];
		$med->shortcut = $post['shortcut'];
		$med->clientid = $logininfo->clientid;
		$med->save();
	}


}

?>