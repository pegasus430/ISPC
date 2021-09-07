<?php

require_once("Pms/Form.php");

class Application_Form_LocationsStations extends Pms_Form
{
	public function validate($post)
	{
		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
		if(!$val->isstring($post['station'])){
			$this->error_message['station']=$Tr->translate('enterstation'); $error=1;
		}

		if($error==0)
		{
		 return true;
		}
		return false;
	}

	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$station = new LocationsStations();
		$station->station = Pms_CommonData::aesEncrypt($post['station']);
		$station->client_id = $logininfo->clientid;
		$station->location_id = $post['location_id'];
		$station->phone1 = $post['phone1'];
		$station->phone2 = $post['phone2'];
		$station->save();
	}

	public function UpdateData($post)
	{
		$station = Doctrine::getTable('LocationsStations')->find($_GET['st_id']);
		$station->station = Pms_CommonData::aesEncrypt($post['station']);
		$station->location_id = $post['location_id'];
		$station->phone1 = $post['phone1'];
		$station->phone2 = $post['phone2'];
		$station->save();
	}
}

?>