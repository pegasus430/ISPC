<?php

require_once("Pms/Form.php");

class Application_Form_Ruhen extends Pms_Form{

	public function insertRuhen($post){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		$stmb = new Ruhen();
		$stmb->ipid = $ipid;
		$stmb->palliativzentrum = $post['palliativzentrum'];
		$stmb->einweisung = $post['einweisung'];
		$stmb->r_name = $post['r_name'];
		$stmb->r_address = $post['r_address'];

		if(!empty($post['begindate'])) {
			$stmb->begindate = date('Y-m-d H:i:s', strtotime(date('d-m-Y',strtotime($post['begindate']))));
		}else{
			$stmb->begindate= "";
		}
		if(!empty($post['enddate'])) {
			$stmb->enddate = date('Y-m-d H:i:s', strtotime(date('d-m-Y',strtotime($post['enddate']))));
		}else{
			$stmb->enddate= "";
		}

		$stmb->save();
		$result = $stmb->id;

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("ruhen");
		$cust->recordid = $result;
		$cust->user_id = $userid;
		$cust->save();

		if(!empty($post['begindate'])){
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type=Pms_CommonData::aesEncrypt("K");
			$cust->course_title=Pms_CommonData::aesEncrypt('Beginn des Ruhens der Teilnahme ab dem '.$post['begindate']);
			$cust->recordid = $result;
			$cust->user_id = $userid;
			$cust->save();
		}

		if($ins->id>0){
			return true;
		}else{
			return false;
		}
	}

	public function UpdateRuhen($post){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$stmb = Doctrine::getTable('Ruhen')->find($post['ruhen_id']);

		$begindate = explode(".",$post['begindate']);
		$enddate = explode(".",$post['enddate']);
		$stmb->palliativzentrum = $post['palliativzentrum'];
		$stmb->einweisung = $post['einweisung'];
		$stmb->r_name = $post['r_name'];
		$stmb->r_address = $post['r_address'];
		$stmb->begindate =  $begindate[2]."-".$begindate[1]."-".$begindate[0].' '.date('H').':'.date('i').':'.date('i');
		$stmb->enddate =  $enddate[2]."-".$enddate[1]."-".$enddate[0].' '.date('H').':'.date('i').':'.date('i');
		$stmb->save();


		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt("Ruhen der Teilnahme wurde editiert");
		$cust->recordid = $post['ruhen_id'];
		$cust->user_id = $userid;
		$cust->save();
	}

}

?>