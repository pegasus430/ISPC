<?php

require_once("Pms/Form.php");

class Application_Form_AddFields extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$error=0;

		$val = new Pms_Validation();
		if(!$val->isstring($post['fieldname'])){
			$this->error_message['fieldname']=$Tr->translate('fieldname_error'); $error=33;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		$f = new TriggerFields();
		$f->fieldname = $post['fieldname'];
		$f->formid = $post['formid'];
		$f->save();
	}
}

?>