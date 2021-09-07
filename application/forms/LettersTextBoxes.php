<?php

require_once("Pms/Form.php");

class Application_Form_LettersTextBoxes extends Pms_Form {

	public function insertData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$stmb = new LettersTextBoxes();
		$stmb->clientid = $clientid;
		$stmb->greetings = $post['greetings'];
		$stmb->sapv_invoice_footer = nl2br($post['sapv_invoice_footer']);
		$stmb->sgbv_invoice_footer = nl2br($post['sgbv_invoice_footer']);
		$stmb->nd_invoice_footer = nl2br($post['nd_invoice_footer']);
		$stmb->erstverordnung_footer = nl2br($post['erstverordnung_footer']);
		$stmb->folgeverordnung_footer = nl2br($post['folgeverordnung_footer']);
		$stmb->save();

		if($ins->id > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function UpdateData($post, $item_id)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		
		$let = new LettersTextBoxes();
		$columns = $let->getTable()->getColumns();
		$columns_names= array_keys($columns);
		
		
// 		$stmb = $let->find($post['item_id']);
		
		

		$stmb = $let->getTable()->find($post['item_id']);
// 		$stmb = Doctrine::getTable('LettersTextBoxes')->find($post['item_id']);
		foreach($post as $field=>$value){
			if(in_array($field,$columns_names) && isset($post[$field])){
				$stmb->{$field} = nl2br($value);
			}
		}
		$stmb->save();
// 		$stmb->greetings = $post['greetings'];
// 		$stmb->sapv_invoice_footer = nl2br($post['sapv_invoice_footer']);
// 		$stmb->sgbv_invoice_footer = nl2br($post['sgbv_invoice_footer']);
// 		$stmb->erstverordnung_footer = nl2br($post['erstverordnung_footer']);
// 		$stmb->folgeverordnung_footer = nl2br($post['folgeverordnung_footer']);
// 		$stmb->save();
	}

}

?>