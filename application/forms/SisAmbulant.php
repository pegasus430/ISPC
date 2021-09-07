<?php
require_once("Pms/Form.php");

class Application_Form_SisAmbulant extends Pms_Form{

	public function insert($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$ins = new SisAmbulant();
		$ins->ipid = $ipid;
		$ins->clientid = $clientid;
		$ins->dependent_person = $post['dependent_person'];
		$ins->save();
		$id = $ins->id;

		if($id > 0)
		{
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "SIS - ambulant Formular  hinzugefügt";
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->tabname = Pms_CommonData::aesEncrypt('ambulant_sis_form');
			$custcourse->recordid = $id;
			$custcourse->done_name = Pms_CommonData::aesEncrypt('ambulant_sis_form');
			$custcourse->done_id = $id;
			$custcourse->save();
				
			return $id;
		}
		else
			
		{
			return false;
		}
	}
	
	public function update($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
	
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
	
		$upd = Doctrine::getTable('SisAmbulant')->findOneById($post['form_id']);
		$upd->dependent_person = $post['dependent_person'];
		$upd->save();
		
		
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "SIS - ambulant Formular  wurde editiert";
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->tabname = Pms_CommonData::aesEncrypt('ambulant_sis_form');
			$custcourse->recordid = $post['form_id'];
			$custcourse->done_name = Pms_CommonData::aesEncrypt('ambulant_sis_form');
			$custcourse->done_id = $post['saved_id'];
			$custcourse->save();
		
	}
}