<?php

require_once("Pms/Form.php");

class Application_Form_PatientStammblattsapv extends Pms_Form{

	public function insertPatientStammblattsapv($post){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$Qur = Doctrine_Query::create()
		->delete('PatientStammblattsapv')
		->where("ipid='".$ipid."'");
		$Qur->execute();

		$stmb = new PatientStammblattsapv();
		$stmb->ipid = $ipid;
		$stmb->patientenverfugung= join(",",$post['patientenverfugung']); ;
		$stmb->betreuung= join(",",$post['betreuung']); ;
		$stmb->betreuer= $post['betreuer'];
		$stmb->betreuer_tel = $post['betreuer_tel'];
		$stmb->betreuer_fax = $post['betreuer_fax'];
		$stmb->vorsorgevollmacht= join(",",$post['vorsorgevollmacht']); ;
		$stmb->bevollmachtigter= $post['bevollmachtigter'];
		$stmb->bevollmachtigter_tel= $post['bevollmachtigter_tel'];
		$stmb->bevollmachtigter_fax= $post['bevollmachtigter_fax'];
		$stmb->behandlungswunsch = $post['behandlungswunsch'];
		$stmb->allergien= $post['allergien'];
		$stmb->save();

		if($ins->id>0){
			return true;
		}else{
			return false;
		}
	}
}

?>