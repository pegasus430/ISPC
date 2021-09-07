<?php

require_once("Pms/Form.php");

class Application_Form_User2admission extends Pms_Form {

	public function InsertData ($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$user2adm = new User2admission();
		$user2adm->ipid = $post['ipid'];
		$user2adm->date = $post['date'];
		$user2adm->admission_type = $post['admission_type'];
		$user2adm->admission_status = $post['admission_status'];
		$user2adm->user_id = $post['user_id'];
		$user2adm->user_type = $post['user_type'];
		$user2adm->save();

		return true;
	}
}
?>