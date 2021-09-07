<?php
require_once("Pms/Form.php");
class Application_Form_PatientQpaMapping extends Pms_Form
{
	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$med = new PatientQpaMapping();
		$med->name = $post['name'];
		$med->pzn = $post['pzn'];
		$med->description = $post['description'];
		$med->package_size = $post['package_size'];
		$med->amount_unit = str_replace(",",".",$post['amount_unit']);
		$med->price = str_replace(",",".",$post['price']);
		$med->manufacturer = $post['manufacturer'];
		$med->package_amount = str_replace(",",".",$post['package_amount']);
		$med->clientid = $logininfo->clientid;
		$med->save();
	}

	public function UpdateData($post)
	{
		$med = Doctrine::getTable('Medication')->find($_GET['id']);
		$med->name = $post['name'];
		$med->pzn = $post['pzn'];
		$med->description = $post['description'];
		$med->package_size = $post['package_size'];
		$med->amount_unit = str_replace(",",".",$post['amount_unit']);
		$med->price = str_replace(",",".",$post['price']);
		$med->manufacturer = $post['manufacturer'];
		$med->package_amount = str_replace(",",".",$post['package_amount']);
		$med->clientid = $logininfo->clientid;
		$med->change_date = date("Y-m-d H:i:s",time());
		$med->change_user = $logininfo->userid;
		$med->save();
	}
}
?>