<?php

require_once("Pms/Form.php");

class Application_Form_Medication extends Pms_Form {

	public function validate($post) 
	{
		$error = 0;
		$val = new Pms_Validation();
		$Tr = new Zend_View_Helper_Translate();
		if (!$val->isstring($post['name'])) {
			$this->error_message['name'] = $Tr->translate('entername');
			$error = 1;
		}
		
		if ( trim($post['pzn']) != '' ) {
			
			if ( ! $val->integer(trim($post['pzn']))) {
				$this->error_message['pzn'] = $Tr->translate('pzn must be numeric');
				$error = 1;
			} elseif( ! $this->_validatePZN(trim($post['pzn']))) {
				
				$this->error_message['pzn'] = $Tr->translate('pzn is not valid');
				$error = 1;
			}

		}
		
		if ($error == 0) {
			return true;
		}

		return false;
	}

	private function _validatePZN ( $pzn )
	{
		$result = false;
		
			
		//add leading 0
		$pzn = str_pad($pzn, 8, "0", STR_PAD_LEFT);
		
		$pzn_arr = str_split( (string)$pzn );
		
		if(count($pzn_arr) != 8) {
			$result = false;
			
		} else {
			
			$control_sum = $pzn_arr[7];
			unset($pzn_arr[7]);
			
			$total_sum = 0;
			
			foreach($pzn_arr as $k=>$digit) {
				$total_sum += ($k+1) * $digit;		 
			}
			
			if( $total_sum % 11 == $control_sum) {
				$result = true;
			} 
		}

		return $result;
		
	}
	
	
	public function InsertData($post) {

		$logininfo = new Zend_Session_Namespace('Login_Info');

		$med = new Medication();
		$med->name = $post['name'];
		$med->pzn = $post['pzn'];
        $med->is_btm = $post['is_btm'];//ISPC-2912,Elena,25.05.2021
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
		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		$med = Doctrine::getTable('Medication')->find($_GET['id']);
		if ($med instanceof Medication && $med->clientid ==  $logininfo->clientid) {
			$med->name = $post['name'];
			$med->pzn = $post['pzn'];
			
            $med->is_btm = $post['is_btm'];//ISPC-2912,Elena,25.05.2021
			
			//this 2 added for future 
			if (isset($post['source'])) {
				$med->source = $post[$key]['source'];
			}
			if (isset($post['dbf_id'])) {
				$med->dbf_id = $post[$key]['dbf_id'];
			}		
			$med->description = $post['description'];
			$med->package_size = $post['package_size'];
			$med->amount_unit = str_replace(",", ".", $post['amount_unit']);
			$med->price = str_replace(",", ".", $post['price']);
			$med->manufacturer = $post['manufacturer'];
			$med->package_amount = str_replace(",", ".", $post['package_amount']);
			$med->clientid = $logininfo->clientid;
			$med->change_date = date("Y-m-d H:m:i", time());
			$med->change_user = $logininfo->userid;
			$med->comment = $post['comment'];
			if ($post['clientid'] > 0) {
				$med->clientid = $post['clientid'];
			}
			$med->save();
			return true;
		} else {
			return false;
		}
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

			$med = new Medication();
			$med->name = $medication;
			$med->extra = 1;
			$med->clientid = $logininfo->clientid;
			if (isset($post[$key])) {
				$med->pzn = $post[$key]['pzn'];
				$med->source = $post[$key]['source'];
                $med->is_btm = $post[$key]['is_btm'];//ISPC-2912,Elena,25.05.2021
				$med->dbf_id = $post[$key]['dbf_id'];
			}
			
			$med->save();
			$medc[$key] = $med;
		}
		return $medc;
	}

}

?>