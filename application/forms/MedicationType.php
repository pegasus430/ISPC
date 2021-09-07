<?php

require_once("Pms/Form.php");

class Application_Form_MedicationType extends Pms_Form {

	public function validate($post) {
		$error = 0;
		$val = new Pms_Validation();
		$Tr = new Zend_View_Helper_Translate();
		if (!$val->isstring($post['type'])) {
			$this->error_message['type'] = $Tr->translate('entermedicationtype');
			$error = 1;
		}
		if ($error == 0) {
			return true;
		}

		return false;
	}

	public function insert($post) {

		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		$med = new MedicationType();
		$med->type = $post['type'];
		$med->clientid = $logininfo->clientid;
		$med->save();
		
		return $med;
	}

	public function update($post) {
	    
		$med = Doctrine::getTable('MedicationType')->find($_GET['id']);
		$med->type = $post['type'];
		$med->save();
		
	}

	public function InsertNewData($post) {
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$medc = array();
		foreach ($post['newmedication'] as $key => $val) {
			$pcarr = explode("|", $val);
			if (count($pcarr) > 0) {
				$type = $pcarr[0];
			} else {
				$type = $val;
			}

			$med = new MedicationType();
			$med->type = $type;
			$med->extra = 1;
			$med->clientid = $logininfo->clientid;
			$med->save();
			$medc[$key] = $med;
		}
		return $medc;
	}

}

?>