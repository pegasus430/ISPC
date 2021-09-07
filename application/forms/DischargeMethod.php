<?php

require_once("Pms/Form.php");

class Application_Form_DischargeMethod extends Pms_Form
{
	public function validate($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$Tr = new Zend_View_Helper_Translate();
		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['abbr'])){
			$this->error_message['abbr']=$Tr->translate('enterdischargemethod'); $error=1;
		}

		if($_GET['id']<1)
		{
			$user = Doctrine_Query::create()
			->select("*")
			->from('DischargeMethod')
			->where("abbr='".$post['abbr']."' and clientid = '".$clientid."' and isdelete=0");
		 $userexec =  $user->execute();

		 if(count($userexec->toArray())>0){
		 	echo $this->error_message['abbr'] = $Tr->translate("dischargemethodalreadyexists");$error=7;
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
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$location = new DischargeMethod();
		$location->abbr = $post['abbr'];
		$location->description = $post['description'];
		$location->anlage_6_report = $post['anlage_6_report'];
		$location->clientid = $logininfo->clientid;
		// ISPC-2219 27.07.2018 - Ancuta
		$location->status_laying = $post['status_laying'];
		$location->status_stabilization = $post['status_stabilization'];
		//--
		$location->save();

	}

	public function UpdateData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		 
		$location = Doctrine::getTable('DischargeMethod')->find($_GET['id']);
		$location->abbr = $post['abbr'];
		$location->description = $post['description'];
		$location->anlage_6_report = $post['anlage_6_report'];
		$location->clientid = $logininfo->clientid;
		// ISPC-2219 27.07.2018 - Ancuta
		$location->status_laying = $post['status_laying'];
		$location->status_stabilization = $post['status_stabilization'];
		//--
		
		$location->save();

	}
	 

}

?>