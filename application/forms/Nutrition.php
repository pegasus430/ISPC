<?php

require_once("Pms/Form.php");

class Application_Form_Nutrition extends Pms_Form {

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

		$med = new Nutrition();
		$med->name = $post['name'];
		$med->pzn = $post['pzn'];
		$med->description = $post['description'];
		$med->package_size = $post['package_size'];
		$med->amount_unit = str_replace(",", ".", $post['amount_unit']);
		$med->price = str_replace(",", ".", $post['price']);
		$med->manufacturer = $post['manufacturer'];
		$med->package_amount = str_replace(",", ".", $post['package_amount']);
		$med->comment = $post['comment'];
		if ($post['clientid'] > 0) {
			$med->clientid = $post['clientid'];
		} else {
			$med->clientid = $logininfo->clientid;
		}

		$med->save();
		return $med;
	}

	public function UpdateData($post) {
		$med = Doctrine::getTable('Nutrition')->find($_GET['id']);
		$med->name = $post['name'];
		$med->pzn = $post['pzn'];
		$med->description = $post['description'];
		$med->package_size = $post['package_size'];
		$med->amount_unit = str_replace(",", ".", $post['amount_unit']);
		$med->price = str_replace(",", ".", $post['price']);
		$med->manufacturer = $post['manufacturer'];
		$med->package_amount = str_replace(",", ".", $post['package_amount']);
		$med->clientid = $logininfo->clientid;
		$med->change_date = date("Y-m-d H:i:s", time());
		$med->change_user = $logininfo->userid;
		$med->comment = $post['comment'];
		if ($post['clientid'] > 0) {
			$med->clientid = $post['clientid'];
		}
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

			$med = new Nutrition();
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