<?php

require_once("Pms/Form.php");

class Application_Form_PatientReferredBy extends Pms_Form
{
	public function validate($post)
	{

		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['referred_name'])){
			$this->error_message['referred_name']=$Tr->translate('referredname_error'); $error=1;
		}

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if($clientid<1)
		{
			$this->error_message['client_id']=$Tr->translate('client_error'); $error=2;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
	  
		$cust = new PatientReferredBy();
		$cust->referred_name = $post['referred_name'];
		$cust->clientid = $post['clientid'];
		$cust->save();
		return $cust;
	}

	public function UpdateData($post)
	{
		$cust = Doctrine::getTable('PatientReferredBy')->find($_GET['id']);
		$cust->referred_name = $post['referred_name'];
		$cust->save();
		return $cust;
	}
}
?>