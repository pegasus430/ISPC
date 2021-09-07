<?php
require_once("Pms/Form.php");

class Application_Form_Anlage3nordrhein extends Pms_Form{

	public function insert_anlage3($post)
	{//print_r($post);exit;
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$ins = new Anlage3();
		$ins->ipid = $ipid;
		
		$ins->discharge_date = date('Y-m-d',strtotime($post['discharge_date']));
		$ins->discharge_reason = $post['discharge_reason'];
		$ins->death_date = date('Y-m-d',strtotime($post['death_date']));
		$ins->checkbox_death = $post['checkbox_death'];
		$ins->discharge_location= $post['discharge_location'];
		$ins->members_included= $post['members_included'];
				
		
		$ins->save();
		$id = $ins->id;

		if($id > 0)
		{
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "Anlage 3 Formular wurde hinzugefügt";
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->tabname = Pms_CommonData::aesEncrypt('anlage3nordrhein_form');
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->recordid = $id;
			$custcourse->done_name = Pms_CommonData::aesEncrypt('anlage3nordrhein_form');
			$custcourse->done_id = $id;
			$custcourse->save();
				
			return $id;
		}
		else
		{
			return false;
		}
	}
	
	public function update_anlage3($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
	
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
	
		$upd = Doctrine::getTable('Anlage3')->findOneById($post['saved_id']);
			
		$upd->discharge_date = date('Y-m-d',strtotime($post['discharge_date']));
		$upd->discharge_reason = $post['discharge_reason'];
		$upd->death_date = date('Y-m-d',strtotime($post['death_date']));
		$upd->checkbox_death = $post['checkbox_death'];
		$upd->discharge_location= $post['discharge_location'];
		$upd->members_included= $post['members_included'];
		$upd->save();
				
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "Anlage 3 Formular wurde editiert.";
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->tabname = Pms_CommonData::aesEncrypt('anlage3nordrhein_form');
			$custcourse->recordid = $post['saved_id'];
			$custcourse->done_name = Pms_CommonData::aesEncrypt('anlage3nordrhein_form');
			$custcourse->done_id = $id;
			$custcourse->save();
		
	}
}