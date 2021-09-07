<?php

require_once("Pms/Form.php");

class Application_Form_Locations extends Pms_Form
{
	public function validate($post)
	{
		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
		if(!$val->isstring($post['location'])){
			$this->error_message['location']=$Tr->translate('enterlocation'); $error=1;
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
		$location = new Locations();
		$location->location = Pms_CommonData::aesEncrypt($post['location']);
		$location->client_id = $logininfo->clientid;
		$location->location_type = $post['location_type'];
		$location->street = $post['street'];
		$location->zip = $post['zip'];
		$location->city = $post['city'];
		$location->phone1 = $post['phone1'];
		$location->phone2 = $post['phone2'];
		$location->fax = $post['fax'];
		$location->email = $post['email'];
		$location->comment = $post['comment'];
		$location->location_type = $post['location_type'];

		$location->save();
	}

	public function UpdateData($post)
	{
		$location = Doctrine::getTable('Locations')->find($_GET['id']);
		$location->location = Pms_CommonData::aesEncrypt($post['location']);
		$location->location_type = $post['location_type'];
		$location->street = $post['street'];
		$location->zip = $post['zip'];
		$location->city = $post['city'];
		$location->phone1 = $post['phone1'];
		$location->phone2 = $post['phone2'];
		$location->fax = $post['fax'];
		$location->email = $post['email'];
		$location->comment = $post['comment'];
		$location->save();
	}
}

?>