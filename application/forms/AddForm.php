<?php

require_once("Pms/Form.php");

class Application_Form_AddForm extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error=0;

		$val = new Pms_Validation();
		if(!$val->isstring($post['formname'])){
			$this->error_message['formname']=$Tr->translate('formname_error'); $error=33;
		}


		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
			
		$f = new TriggerForms();
		$f->formname = $post['formname'];
		$f->save();
			
			

	}
}

?>