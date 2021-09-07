<?php
require_once("Pms/Form.php");
class Application_Form_SapvExportHistory extends Pms_Form
{
	public function insert_sapv_export_master($xml)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$stmb = new SapvExportHistory();
		$stmb->client = $clientid;
		$stmb->parent = '0';
		$stmb->xml = $xml;
		$stmb->save();

		return $stmb->id;
	}
}
?>