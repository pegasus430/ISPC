<?php

	require_once("Pms/Form.php");

	class Application_Form_PatientDayStructure extends Pms_Form {
		
		public function insert($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$cform = new ContactForms();
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$stmb = new PatientDayStructure();
			$stmb->ipid = $post['ipid'];
			$stmb->save();
 
			if($stmb->id > 0)
			{
				$result =  $stmb->id;
			}
			
			$tab_name = "daystructure_form";
			$comment = 'Tagesstrukturplan wurde hinzugefügt'; 
			
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt($tab_name);
			$cust->recordid = $result;
			$cust->user_id = $userid;
			$cust->done_date = date("Y-m-d H:i:s", time());
			$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
			$cust->done_id = $result;
			$cust->save();
 
			  
			if($stmb->id > 0)
			{
				return $stmb->id;
			}
			else
			{
				return false;
			}
		}


		public function update($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
		
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
	 
			$update = Doctrine::getTable('PatientDayStructure')->findOneById($post['form_id']);
			$update->change_user = $userid;
			$update->change_date = date('Y-m-d H:i:s');
			$update->save();
 
			$tab_name = "daystructure_form";
			$comment = 'Tagesstrukturplan  wurde editiert';
				
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt($tab_name);
			$cust->recordid = $post['form_id'];
			$cust->user_id = $userid;
			$cust->done_date = date("Y-m-d H:i:s", time());
			$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
			$cust->done_id = $post['form_id'];
			$cust->save();
			
			return true;
		}
 
 

	}

?>