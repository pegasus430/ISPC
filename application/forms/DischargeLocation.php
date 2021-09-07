<?php

require_once("Pms/Form.php");

class Application_Form_DischargeLocation extends Pms_Form
{
	public function validate($post)
	{
		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
		if(!$val->isstring($post['location'])){
			$this->error_message['location']=$Tr->translate('enterlocation'); $error=1;
		}

		if($_GET['id']<1)
		{
			$user = Doctrine_Query::create()
			->select("*,AES_DECRYPT(location,'".Zend_Registry::get('salt')."')")
			->from('DischargeLocation')
			->where("location = '".$post['location']."'");
		 $userexec =  $user->execute();

		 if(count($userexec->toArray())>0){
		 	echo $this->error_message['location'] = $Tr->translate("locationalreadyexists");$error=7;
		 }
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		$location = new DischargeLocation();
		$location->location = Pms_CommonData::aesEncrypt($post['location']);
		if($post['clientid']>0)
		{
			$location->clientid = $post['clientid'];
		}else{
			$location->clientid = $logininfo->clientid;
		}
		$location->type = $post['type'];
		$location->save();

	}

	public function UpdateData($post)
	{
		 
		$location = Doctrine::getTable('DischargeLocation')->find($_GET['id']);
		$location->location = Pms_CommonData::aesEncrypt($post['location']);
		if($post['clientid']>0)
		{
			$location->clientid = $post['clientid'];
		}
		$location->type = $post['type'];
		$location->save();

	}
	 

}

?>