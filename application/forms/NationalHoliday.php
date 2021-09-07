<?php
require_once("Pms/Form.php");
class Application_Form_NationalHoliday extends Pms_Form
{
	public function validate($post)
	{

		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();

		if(!$val->isstring($post['holiday'])){
			$this->error_message['holiday']=$Tr->translate('err_holiday');
			$error=1;
		}

		if(!$val->isdate($post['date'])){
			$this->error_message['date']=$Tr->translate('must_be_valid_date');
			$error=2;
		}

		if(count($post['region'])<1){
			$this->error_message['region']=$Tr->translate('selectatleastone_region');
			$error=3;
		}

		if($error==0)
		{
			return true;
		}

		return false;

	}


}
?>