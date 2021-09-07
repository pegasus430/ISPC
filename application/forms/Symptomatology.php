<?php

require_once("Pms/Form.php");

class Application_Form_Symptomatology extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['entry_date'])){
			$this->error_message['entry_date']=$Tr->translate('entrydate_error'); $error=1;
		}
		if(!$val->isstring($post['input_value'])){
			$this->error_message['input_value']=$Tr->translate('inputvalue_error'); $error=2;
		}
		if(!$val->isstring($post['critical_value'])){
			$this->error_message['critical_value']=$Tr->translate('criticalvalue_error'); $error=3;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		$ent_date = explode(".",$post['entry_date']);
		$res = new Symptomatology();
		$res->ipid = $post['ipid'];
		$res->symptomid =$post['symptomid'];
		$res->entry_date = $ent_date[2].".".$ent_date[1].".".$ent_date[0];
		$res->input_value = $post['input_value'];
		$res->critical_value = $post['critical_value'];
		$res->save();
			
	}

	public function UpdateData($post)
	{
		$a_date = split("/",$post['birthd']);
		$cust = Doctrine::getTable('PatientMaster')->find($_GET['id']);
		$cust->first_name = $post['first_name'];
		$cust->middle_name = $post['middle_name'];
		$cust->last_name = $post['last_name'];
		$cust->street1 = $post['street1'];
		$cust->street2 = $post['street2'];
		$cust->zip = $post['zip'];
		$cust->city = $post['city'];
		$cust->title = $post['title'];
		$cust->phone =$post['phone'];
		$cust->mobile =$post['mobile'];
		$cust->birthd = $a_date[2]."-".$a_date[0]."-".$a_date[1];
		$cust->sex =$post['sex'];
		$cust->nation =$post['nation'];
		$cust->fdoc_caresalone =$post['fdoc_caresalone'];
		$cust->save();
	}



}

?>