<?php

require_once("Pms/Form.php");

class Application_Form_PatientHospizCourse extends Pms_Form
{
	public function validate($post)
	{

		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();

		if((strlen($post['course_long']) == 0))
		{
			$this->error_message['hospiz']=$Tr->translate('hospizcourserror'); $error=1;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$epid  = Pms_CommonData::getEpid($ipid);

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;


		if(strlen($post['course_long'])>0)
		{
			$cust = new PatientHospizCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_short=Pms_CommonData::aesEncrypt(addslashes($post['course_short']));
			$cust->course_long=Pms_CommonData::aesEncrypt(addslashes($post['course_long']));
			$cust->user_id = $userid;
			$cust->save();


			if(strlen($post['course_short'])>0) {
				$custcourse = new PatientCourse();
				$custcourse->ipid = $ipid;
				$custcourse->course_date = date("Y-m-d H:i:s",time());
				$custcourse->course_type=Pms_CommonData::aesEncrypt("K");
				$custcourse->course_title=Pms_CommonData::aesEncrypt(addslashes('Besuch Hospiz / Hospizverein'."\n".$post['course_short']));
				$custcourse->user_id = $userid;
				$custcourse->save();
			}
		}
	}

	public function UpdateWrongEntry($post)
	{
		$exarr  = explode(",",$post['ids']);

		foreach( $exarr as $key=>$value)
		{
			$cust = Doctrine::getTable('PatientCourse')->find($value);
			$cust->wrong = $post['val'];

			if($post['val']==1)
			{
				$cust->wrongcomment = $post['comment'];
			}
			else
			{
				$cust->wrongcomment = "";
			}
			$cust->save();
		}
		 
		return $cust;
	}

	public function UpdateData($post)
	{

	}
	 
	public function InsertDiagnosisData($post)
	{
		 
		$epid  = Pms_CommonData::getEpid($post['ipid']);
			
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
			
		for($i=1;$i<=sizeof($post['diagnosis']);$i++)
		{
			$cust = new PatientCourse();
			$cust->ipid = $post['ipid'];
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type="D";
			$cust->course_title=$post['diagnosis'][$i];
			$cust->user_id = $userid;
			$cust->save();
		}
			
	}
	 


}

?>