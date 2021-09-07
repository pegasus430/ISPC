<?php

require_once("Pms/Form.php");

class Application_Form_MedicationIndications extends Pms_Form {

	public function validate($post) {
		$error = 0;
		$val = new Pms_Validation();
		$Tr = new Zend_View_Helper_Translate();
		if (!$val->isstring($post['indication'])) {
			$this->error_message['indication'] = $Tr->translate('entermedicationindication');
			$error = 1;
		}
		if ($error == 0) {
			return true;
		}

		return false;
	}

	public function insert($post) {

		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		$med = new MedicationIndications();
		$med->indication = $post['indication'];
		$med->indication_color = $post['indication_color'];
		$med->clientid = $logininfo->clientid;
		$med->save();
		
		return $med;
	}

	public function update($post) {
	    
		$med = Doctrine::getTable('MedicationIndications')->find($_GET['id']);
		$med->indication = $post['indication'];
		$med->indication_color = $post['indication_color'];
		$med->save();
		
	}

	public function InsertNewData($post) {
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$medc = array();
		foreach ($post['newmedication'] as $key => $val) {
			$pcarr = explode("|", $val);
			if (count($pcarr) > 0) {
				$indication = $pcarr[0];
			} else {
				$indication = $val;
			}

			$med = new MedicationIndications();
			$med->indication = $indication;
			$med->extra = 1;
			$med->clientid = $logininfo->clientid;
			$med->save();
			$medc[$key] = $med;
		}
		return $medc;
	}

}

?>