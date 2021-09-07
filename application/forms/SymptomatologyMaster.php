<?php

require_once("Pms/Form.php");

class Application_Form_SymptomatologyMaster extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['sym_description'])){
			$this->error_message['sym_description']=$Tr->translate("enterdescription"); $error=4;
		}

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if($clientid<1)
		{
			$this->error_message['message']="Select client"; $error=4;
		}
			
		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$split = explode(".",$post['entry_date']);
		$post_entry_date = $split['2']."-".$split['1']."-".$split['0'];

		$res = new SymptomatologyMaster();
		$res->sym_description = $post['sym_description'];
		if($post['clientid']>0)
		{
			$res->clientid = $post['clientid'];
		}else{
			$res->clientid = $logininfo->clientid;
		}
		$res->min_alert = $post['min_alert'];
		$res->max_alert = $post['max_alert'];
		$res->alert_color = $post['alert_color'];
		$res->entry_date = $post_entry_date;
		$res->input_value = $post['input_value'];
		$res->critical_value = $post['critical_value'];
		$res->save();
		return $res;
	}

	public function UpdateData($post)
	{
		$split = explode(".",$post['entry_date']);
		$post_entry_date = $split['2']."-".$split['1']."-".$split['0'];

		$res = Doctrine::getTable('SymptomatologyMaster')->find($_GET['id']);
		$res->sym_description = $post['sym_description'];
		if($post['clientid']>0)
		{
			$res->clientid = $post['clientid'];
		}
		$res->min_alert = $post['min_alert'];
		$res->max_alert = $post['max_alert'];
		$res->alert_color = $post['alert_color'];
		$res->entry_date = $post_entry_date;
		$res->input_value = $post['input_value'];
		$res->critical_value = $post['critical_value'];
		$res->save();
		return $res;
	}
}
?>