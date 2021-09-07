<?php

require_once("Pms/Form.php");

class Application_Form_Anlage5Nie extends Pms_Form{
	
	public function insert_anlage5($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		
		$ins = new Anlage5Nie();
		$ins->ipid = $ipid;
		
		$ins->sapv_period = $post['sapv_period'];
		$ins->sapv_from = $post['sapv_from'];
		$ins->sapv_from = $post['sapv_from'];
		$ins->sapv_to = date('Y-m-d',strtotime($post['sapv_to']));
		if(count($post['anlage5_checkbox']) > 0 )
		{
			$ins->anlage5_checkbox = implode(',' ,$post['anlage5_checkbox']);
		}
		$ins->erst_daily = $post['erst_daily'];
		$ins->erst_wtl = $post['erst_wtl'];
		$ins->erst_time = $post['erst_time'];
		$ins->beratung_daily = $post['beratung_daily'];
		$ins->beratung_wtl = $post['beratung_wtl'];
		$ins->beratung_time = $post['beratung_time'];
		$ins->nurse1_daily = $post['nurse1_daily'];
		$ins->nurse1_wtl = $post['nurse1_wtl'];
		$ins->nurse1_time = $post['nurse1_time'];
		$ins->nurse2_daily = $post['nurse2_daily'];
		$ins->nurse2_wtl = $post['nurse2_wtl'];
		$ins->nurse2_time = $post['nurse2_time'];
		$ins->nurse3_daily = $post['nurse3_daily'];
		$ins->nurse3_wtl = $post['nurse3_wtl'];
		$ins->nurse3_time = $post['nurse3_time'];
		$ins->doctor1_daily = $post['doctor1_daily'];
		$ins->doctor1_wtl = $post['doctor1_wtl'];
		$ins->doctor1_time = $post['doctor1_time'];
		$ins->doctor2_daily = $post['doctor2_daily'];
		$ins->doctor2_wtl = $post['doctor2_wtl'];
		$ins->doctor2_time = $post['doctor2_time'];
		//print_r($post);exit;
		$ins->save();
		$id = $ins->id;
		
		if($id > 0)
		{
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "Anlage 5 Formular wurde angelegt";
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->tabname = Pms_CommonData::aesEncrypt('Anlage5Nie_form');
			$custcourse->save();
			
			return $id;
		}
		else 
		{
			return false;
		}
	}
	
	public function update_anlage5($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
	
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
	
		$update = Doctrine::getTable('Anlage5Nie')->findOneById($post['saved_id']);
		
		$update->sapv_period = $post['sapv_period'];
		$update->sapv_from = $post['sapv_from'];
		$update->sapv_to = $post['sapv_to'];
		if(count($post['anlage5_checkbox']) > 0 )
		{
			$update->anlage5_checkbox = implode(',' ,$post['anlage5_checkbox']);
		}
		$update->erst_daily = $post['erst_daily'];
		$update->erst_wtl = $post['erst_wtl'];
		$update->erst_time = $post['erst_time'];
		$update->beratung_daily = $post['beratung_daily'];
		$update->beratung_wtl = $post['beratung_wtl'];
		$update->beratung_time = $post['beratung_time'];
		$update->nurse1_daily = $post['nurse1_daily'];
		$update->nurse1_wtl = $post['nurse1_wtl'];
		$update->nurse1_time = $post['nurse1_time'];
		$update->nurse2_daily = $post['nurse2_daily'];
		$update->nurse2_wtl = $post['nurse2_wtl'];
		$update->nurse2_time = $post['nurse2_time'];
		$update->nurse3_daily = $post['nurse3_daily'];
		$update->nurse3_wtl = $post['nurse3_wtl'];
		$update->nurse3_time = $post['nurse3_time'];
		$update->doctor1_daily = $post['doctor1_daily'];
		$update->doctor1_wtl = $post['doctor1_wtl'];
		$update->doctor1_time = $post['doctor1_time'];
		$update->doctor2_daily = $post['doctor2_daily'];
		$update->doctor2_wtl = $post['doctor2_wtl'];
		$update->doctor2_time = $post['doctor2_time'];
		$update->save();
		
		$custcourse = new PatientCourse();
		$custcourse->ipid = $ipid;
		$custcourse->course_date = date("Y-m-d H:i:s", time());
		$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
		$comment = "Anlage 5 Formular wurde editiert.";
		$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
		$custcourse->user_id = $userid;
		$custcourse->save();
		
		return true;
	}
	
}