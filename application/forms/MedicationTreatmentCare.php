<?php

require_once("Pms/Form.php");

class Application_Form_MedicationTreatmentCare extends Pms_Form {

	public function validate($post) {
		$error = 0;
		$val = new Pms_Validation();
		$Tr = new Zend_View_Helper_Translate();
		if (!$val->isstring($post['name'])) {
			$this->error_message['name'] = $Tr->translate('entername');
			$error = 1;
		}
		if ($error == 0) {
			return true;
		}

		return false;
	}

	public function InsertData($post) {

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$med = new MedicationTreatmentCare();
		$med->name = $post['name'];
		$med->clientid = $logininfo->clientid;

		$med->save();
		return $med;
	}

	public function UpdateData($post) {
		$med = Doctrine::getTable('MedicationTreatmentCare')->find($_GET['id']);
		$med->name = $post['name'];
		$med->save();
	}

	public function InsertNewData($post) {
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$medc = array();
		foreach ($post['newmedication'] as $key => $val) {
			$pcarr = explode("|", $val);
			if (count($pcarr) > 0) {
				$medication = $pcarr[0];
			} else {
				$medication = $val;
			}

			$med = new MedicationTreatmentCare();
			$med->name = $medication;
			$med->extra = 1;
			$med->clientid = $logininfo->clientid;
			$med->save();
			$medc[$key] = $med;
		}
		return $medc;
	}

}

?>