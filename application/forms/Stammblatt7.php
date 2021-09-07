<?php

require_once("Pms/Form.php");

class Application_Form_Stammblatt7 extends Pms_Form{

	public function insertStammblatt7($post){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		$Qur = Doctrine_Query::create()
		->update('Stammblatt7')
		->set("isdelete","1")
		->where("ipid='".$ipid."'");
		$Qur->execute();
		

		$stmb = new Stammblatt7();
		$stmb->ipid = $ipid;
		$stmb->zuzahlung= $post['zuzahlung'];
		$stmb->comments = $post['comments'];
		
		$stmb->pattel= $post['pattel'];// 1234567890
		$stmb->pathandy= $post['pathandy'];// pathandy
		$stmb->loctel= $post['loctel'];// 1234567890
		$stmb->lochandy= $post['lochandy'];// lochandy
		$stmb->locfax= $post['locfax'];// locfax
		$stmb->cntpers1tel= $post['cntpers1tel'];// 1234567890
		$stmb->cntpers1handy= $post['cntpers1handy'];// cntpers1handy
		$stmb->cntpers2tel= $post['cntpers2tel'];// 1234567890
		$stmb->cntpers2handy= $post['cntpers2handy'];// cntpers2handy
		$stmb->cntpers3tel= $post['cntpers3tel'];// 1234567890
		$stmb->cntpers3handy= $post['cntpers3handy'];// cntpers3handy
		$stmb->zuzahlung= $post['zuzahlung'];// 0
		$stmb->healthinsurance_comment= $post['healthinsurance_comment'];// healthinsurance_comment
		$stmb->hausarzt_tel= $post['hausarzt_tel'];// 1234567890
		$stmb->hausarzt_fax= $post['hausarzt_fax'];// hausarzt_fax
		$stmb->facharzt_tel= $post['facharzt_tel'];// 1234567890
		$stmb->facharzt_fax= $post['facharzt_fax'];// facharzt_fax
		$stmb->pflegedienst_tel= $post['pflegedienst_tel'];// 1234567890
		$stmb->pflegedienst_fax= $post['pflegedienst_fax'];// pflegedienst_fax
		$stmb->pflegedienst_comment= $post['pflegedienst_comment'];// pflegedienst_comment
		
		
		$stmb->save();

		if($stmb->id>0){

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->tabname = Pms_CommonData::aesEncrypt("Stammblatt7");
			$cust->user_id = $userid;
			$cust->done_name = Pms_CommonData::aesEncrypt("Stammblatt7");
			$cust->save();
		}		
		
		
		if($stmb->id>0){
			return true;
		}else{
			return false;
		}
	}
}

?>