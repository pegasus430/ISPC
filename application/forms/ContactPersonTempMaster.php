<?php

require_once("Pms/Form.php");

class Application_Form_ContactPersonTempMaster extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['cnt_phone']) && !$val->isstring($post['cnt_mobile'])){
			$this->error_message['cnt_phone']=$Tr->translate('phone_error'); $error=9;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
	  

		$a_date = explode(".",$post['cnt_birthd']);

		$cust = new ContactPersonTempMaster();
		$cust->sessionid = session_id();
		$cust->cnt_first_name = $post['cnt_first_name'];
		$cust->cnt_middle_name = $post['cnt_middle_name'];
		$cust->cnt_last_name = $post['cnt_last_name'];
		$cust->cnt_street1 = $post['cnt_street1'];
		$cust->cnt_street2 = $post['cnt_street2'];
		$cust->cnt_zip = $post['cnt_zip'];
		$cust->cnt_city = $post['cnt_city'];
		$cust->cnt_title = $post['cnt_title'];
		$cust->cnt_hatversorgungsvollmacht = $post['cnt_hatversorgungsvollmacht'];
		// added 16-09-2013
		$cust->cnt_legalguardian = $post['cnt_legalguardian'];
		$cust->notify_funeral = $post['notify_funeral'];
		$cust->quality_control = $post['quality_control'];
		$cust->cnt_kontactnumber = $post['cnt_kontactnumber'];

		$cust->cnt_salutation = $post['cnt_salutation'];
		$cust->cnt_phone =$post['cnt_phone'];
		$cust->cnt_mobile =$post['cnt_mobile'];
		$cust->cnt_email =$post['cnt_email'];
		$cust->cnt_birthd = $a_date[2]."-".$a_date[1]."-".$a_date[0];
		$cust->cnt_sex =$post['cnt_sex'];
		$cust->cnt_nation =$post['cnt_nation'];
		$cust->cnt_custody =$post['cnt_custody'];
		$cust->cnt_familydegree_id =$post['cnt_familydegree_id'];
		$cust->cnt_comment =$post['cnt_comment'];
		$cust->save();
		return $cust;

	}

	public function UpdateData($post)
	{
		$a_date = explode(".",$post['cnt_birthd']);
		 
		$cust = Doctrine::getTable('ContactPersonMaster')->find($_GET['id']);
		$cust->cnt_first_name = $post['cnt_first_name'];
		$cust->cnt_middle_name = $post['cnt_middle_name'];
		$cust->cnt_last_name = $post['cnt_last_name'];
		$cust->cnt_street1 = $post['cnt_street1'];
		$cust->cnt_street2 = $post['cnt_street2'];
		$cust->cnt_zip = $post['cnt_zip'];
		$cust->cnt_city = $post['cnt_city'];
		$cust->cnt_title = $post['cnt_title'];
		$cust->cnt_hatversorgungsvollmacht = $post['cnt_hatversorgungsvollmacht'];
		// added 16-09-2013
		$cust->cnt_legalguardian = $post['cnt_legalguardian'];
		$cust->notify_funeral = $post['notify_funeral'];
		$cust->quality_control = $post['quality_control'];
		$cust->cnt_kontactnumber = $post['cnt_kontactnumber'];

		$cust->cnt_salutation = $post['cnt_salutation'];
		$cust->cnt_phone =$post['cnt_phone'];
		$cust->cnt_mobile =$post['cnt_mobile'];
		$cust->cnt_email =$post['cnt_email'];
		$cust->cnt_birthd = $a_date[2]."-".$a_date[1]."-".$a_date[0];
		$cust->cnt_sex =$post['cnt_sex'];
		$cust->cnt_nation =$post['cnt_nation'];
		$cust->cnt_custody =$post['cnt_custody'];
		$cust->cnt_familydegree_id =$post['cnt_familydegree_id'];
		$cust->cnt_comment =$post['cnt_comment'];
		$cust->save();
		return $cust;
	}
	 



}

?>