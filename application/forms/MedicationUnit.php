<?php

require_once("Pms/Form.php");

class Application_Form_MedicationUnit extends Pms_Form {

	public function validate($post) {
		$error = 0;
		$val = new Pms_Validation();
		$Tr = new Zend_View_Helper_Translate();
		if (!$val->isstring($post['unit'])) {
			$this->error_message['unit'] = $Tr->translate('entermedicationunit');
			$error = 1;
		}
		if ($error == 0) {
			return true;
		}

		return false;
	}

	public function insert($post) {

		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		$med = new MedicationUnit();
		$med->unit = $post['unit'];
		$med->clientid = $logininfo->clientid;
		$med->save();
		
		return $med;
	}

	public function update($post) {
	    
		$med = Doctrine::getTable('MedicationUnit')->find($_GET['id']);
		$med->unit = $post['unit'];
		$med->save();
		
	}

	public function InsertNewData($post) {
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$medc = array();
		foreach ($post['newmedication'] as $key => $val) {
			$pcarr = explode("|", $val);
			if (count($pcarr) > 0) {
				$unit = $pcarr[0];
			} else {
				$unit = $val;
			}

			$med = new MedicationUnit();
			$med->unit = $unit;
			$med->extra = 1;
			$med->clientid = $logininfo->clientid;
			$med->save();
			$medc[$key] = $med;
		}
		return $medc;
	}

}

?>