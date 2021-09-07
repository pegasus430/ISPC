<?php
require_once("Pms/Form.php");
class Application_Form_WlAnlage7 extends Pms_Form
{

	public function insert_data( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$stmb = new WlAnlage7();
		$stmb->ipid = $ipid;
		$stmb->client_fax = $post['client_fax'];
		$stmb->death_date = $post['death_date'];
		$stmb->has_hospital_treatment = $post['has_hospital_treatment'];
		$stmb->dead_in_hospital = $post['dead_in_hospital'];
		$stmb->nursing = $post['nursing'];
		$stmb->service_phone = $post['service_phone'];
		$stmb->service_phone_amount = $post['service_phone_amount'];
		$stmb->service_visit = $post['service_visit'];
		$stmb->service_visit_amount = $post['service_visit_amount'];
		$stmb->rated = $post['rated'];
		$stmb->save();

		$result = $stmb->id;

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("wl_anlage7");
		$cust->recordid = $result;
		$cust->user_id = $userid;
		$cust->done_date = date("Y-m-d H:i:s",time());
		$cust->done_name = Pms_CommonData::aesEncrypt("wl_anlage7_form");
		$cust->done_id = $result;
		$cust->save();



		if ($stmb->id > 0)
		{
			return $stmb->id;
		}
		else
		{
			return false;
		}
	}

	public function update_data($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$stmb = Doctrine::getTable('WlAnlage7')->find($post['anlage7_form_id']);
		$stmb->client_fax = $post['client_fax'];
		$stmb->death_date = $post['death_date'];
		$stmb->has_hospital_treatment = $post['has_hospital_treatment'];
		$stmb->dead_in_hospital = $post['dead_in_hospital'];
		$stmb->nursing = $post['nursing'];
		$stmb->service_phone = $post['service_phone'];
		$stmb->service_phone_amount = $post['service_phone_amount'];
		$stmb->service_visit = $post['service_visit'];
		$stmb->service_visit_amount = $post['service_visit_amount'];
		$stmb->rated = $post['rated'];
		$stmb->save();

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt("Anlage 7 wurde editiert");
		$cust->recordid = $post['anlage7_form_id'];
		$cust->user_id = $userid;
		$cust->save();

	}
}
?>