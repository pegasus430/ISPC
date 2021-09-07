<?php

// require_once 'Pms/Triggers.php';

class application_Triggers_WritePatientHeaderText extends Pms_Triggers
{

	public function triggerWritePatientHeaderText($eventid,$ipid)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$qpa = Doctrine_Query::create()
		->select('f.*,t.*,s.*,k.*')
		->from('FieldTrigger f')
		->innerjoin("f.TriggerTriggers t")
		->innerjoin("f.TriggerForms s")
		->innerjoin("f.TriggerFields k")
		->where("f.formid=11 and f.event=3 and f.isdelete=0 and f.fieldid=32 and f.clientid=".$clientid);
		$result = $qpa->execute(array(),Doctrine_Core::HYDRATE_ARRAY);


		if(count($result)>0)
		{
			$qpa1 = Doctrine_Query::create()
			->select("*,AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,
					AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title")
					->from('PatientCourse')
					->where("ipid='".$ipid."' and course_type='".addslashes(Pms_CommonData::aesEncrypt('C'))."' and wrong = 0");
				
			$qp1 = $qpa1->execute();
				
			if($qp1)
			{
				$newarr1=$qp1->toArray();
			}
				
			$inputs = unserialize($result[0]['inputs']);

				
			$ctitle = "";
			$quama = "";
				
			for($i=0;$i<count($newarr1);$i++)
			{
				$userarr = Pms_CommonData::getUserDataById($newarr1[$i]['user_id']);
				$username = $userarr[0]['last_name'].",".$userarr[0]['first_name'];

				$createdate = date('d.m.Y',strtotime($newarr1[$i]['course_date']));
				$title= str_replace("<","&lt;",$newarr1[$i]['course_title']);
				$title= str_replace(">","&gt;",$title);
					
				if(strlen($inputs['dataset'])>0)
				{
					$pm = new PatientMaster();
					$patarr  = $pm->getMasterData(0,0,0,$ipid);

					$userdata = Pms_CommonData::getUserDataById($receiver_id);
					$patarr['userfirstname'] = $userarr[0]['first_name'];
					$patarr['userlastname'] = $userarr[0]['last_name'];
					$patarr['title'] = $title;
					$patarr['createdate'] = $createdate;

					$ctitle.= nl2br($quama.$this->setTriggerPlaceHolders($patarr,$inputs['dataset']));
				}
				else
				{
					$ctitle.= $quama.$username." (".$createdate.") : <br>".$title;
						
				}
				$quama = " <br><br> ";
			}
				
			return $ctitle;
		}
		else
		{
			return "";
		}
	}

	public function createFormPatientDetails()
	{
		return $this->view->render("trigger/formtriggerinputs/CourseDocumentation/addText.html");
	}

	private function setTriggerPlaceHolders($patarr,$message)
	{
		$placeholder['patientfirstname'] = $patarr['first_name'];
		$placeholder['patientlastname'] = $patarr['last_name'];
		$placeholder['patientaddress'] = $patarr['street1'];
		$placeholder['patientzip'] = $patarr['zip'];
		$placeholder['patientcity'] = $patarr['city'];
		$placeholder['patientphone'] = $patarr['phone'];
		$placeholder['patientmobile'] = $patarr['mobile'];
		$placeholder['patientbirthdate'] = $patarr['birthd'];
		$placeholder['patientgender'] = $patarr['sex'];
		$placeholder['patientadmissiondate'] = $patarr['admission_date'];
		$placeholder['userfirstname'] = $patarr['userfirstname'];
		$placeholder['userlastname'] = $patarr['userlastname'];
		$placeholder['title'] = $patarr['title'];
		$placeholder['createdate'] = $patarr['createdate'];

		foreach($placeholder as $key=>$val)
		{
			$message = str_replace("#".$key."",$val,$message);
		}
		return $message;
	}

}

?>


