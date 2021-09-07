<?php
require_once("Pms/Form.php");
class Application_Form_OrderAdmission extends Pms_Form
{
	public function InsertData ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$orderer = new OrderAdmission();
		$orderer->orderer = $post['orderer'];
		$orderer->clientid = $logininfo->clientid;
		$orderer->save();

		return true;
	}

	public function UpdateData ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		if ($_GET['ordererid'] > 0)
		{
			$cid = $_GET['ordererid'];
		}

		$orderer = Doctrine::getTable('OrderAdmission')->find($cid);
		$orderer->orderer = $post['orderer'];
		$orderer->save();

	}


}
?>