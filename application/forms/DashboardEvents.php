<?php
require_once("Pms/Form.php");
class Application_Form_DashboardEvents extends Pms_Form
{

	public function insert_dashboard_events ($data )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$collection = new Doctrine_Collection('DashboardEvents');
		$collection->fromArray($data);
		$collection->save();
	}


}
?>