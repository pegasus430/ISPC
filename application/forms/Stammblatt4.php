<?php

require_once("Pms/Form.php");

class Application_Form_Stammblatt4 extends Pms_Form{

	public function insertStammblatt4($post){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		$Qur = Doctrine_Query::create()
		->update('Stammblatt4')
		->set("isdelete","1")
		->where("ipid='".$ipid."'");
		$Qur->execute();
		
// 		$Qur = Doctrine_Query::create()
// 		->delete('Stammblatt4')
// 		->where("ipid='".$ipid."'");
// 		$Qur->execute();

		$stmb = new Stammblatt4();
		$stmb->ipid = $ipid;
		$stmb->familienstand=  $post['familienstand'];
		$stmb->wohnsituation= join(",",$post['wohnsituation']);
		$stmb->allergien= $post['allergien'];
		$stmb->zuzahlung= $post['zuzahlung'];
		$stmb->pflegestufe= join(",",$post['pflegestufe']);
		$stmb->patientenverfugung= $post['patientenverfugung'];
		$stmb->vorsorgevollmacht= $post['vorsorgevollmacht'];
		$stmb->bevollmachtigter= $post['bevollmachtigter'];
		$stmb->bevollmachtigter_tel= $post['bevollmachtigter_tel'];
		$stmb->betreuung= $post['betreuung'];
		$stmb->betreuer= $post['betreuer'];
		$stmb->betreuer_handy= $post['betreuer_handy'];
		$stmb->betreuer_tel = $post['betreuer_tel'];
		$stmb->betreuer_fax = $post['betreuer_fax'];
		$stmb->erstkontakt_am = $post['erstkontakt_am'];
		$stmb->erstkontakt_durch = $post['erstkontakt_durch'];
		$stmb->ambulant = $post['ambulant'];
		$stmb->stationar = $post['stationar'];
		//$stmb->ecog = join(",",$post['ecog']);
		$stmb->religion = $post['religion'];
		//$stmb->genogramm = $post['genogramm'];
		$stmb->comments = $post['comments'];
		$stmb->save();

		if($stmb->id>0){

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->tabname = Pms_CommonData::aesEncrypt("Stammblatt4");
			$cust->user_id = $userid;
			$cust->done_name = Pms_CommonData::aesEncrypt("Stammblatt4");
			$cust->done_id = $result;
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