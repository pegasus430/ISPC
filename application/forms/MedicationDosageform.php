<?php

require_once("Pms/Form.php");

class Application_Form_MedicationDosageform extends Pms_Form {

	public function validate($post) {
		$error = 0;
		$val = new Pms_Validation();
		$Tr = new Zend_View_Helper_Translate();
		
		
		if (!$val->isstring($post['dosage_form'])) {
			$this->error_message['dosage_form'] = $Tr->translate('entermedicationdosageform');
			$error = 1;
		}
		
		if ($error == 0) {
			return true;
		}

		return false;
	}

	public function insert($post) {

		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		$med = new MedicationDosageform();
		$med->dosage_form = $post['dosage_form'];
		$med->clientid = $logininfo->clientid;
		$med->save();
		
		return $med;
	}

	public function update($post) {
	    
		$med = Doctrine::getTable('MedicationDosageform')->find($_GET['id']);
		$med->dosage_form = $post['dosage_form'];
		$med->save();
		
	}

	public function InsertNewData($post) {
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$medc = array();
		foreach ($post['newmedication'] as $key => $val) {
			$pcarr = explode("|", $val);
			if (count($pcarr) > 0) {
				$dosageform = $pcarr[0];
			} else {
				$dosageform = $val;
			}

			$med = new MedicationDosageform();
			$med->dosage_form = $dosageform;
			$med->extra = 1;
			$med->clientid = $logininfo->clientid;
			$med->save();
			$medc[$key] = $med;
		}
		return $medc;
	}

}

?>