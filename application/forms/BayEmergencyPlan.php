<?php

require_once("Pms/Form.php");

class Application_Form_BayEmergencyPlan extends Pms_Form
{
	public function insertBayEmergencyPlan($post)
	{  
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		
		$Qur = Doctrine_Query::create()
		->update('BayEmergencyPlan')
		->set("isdelete","1")
		->where("ipid='".$ipid."'");
		$Qur->execute();
		
		
		
		$bemp = new BayEmergencyPlan();
		$bemp->ipid = $ipid;
		$bemp->notfallplan = $post['notfallplan'];
		$bemp->pat_address = $post['pat_address'];
		$bemp->pat_phone = $post['pat_phone'];
		$bemp->hausarzt = $post['hausarzt'];
		$bemp->client_phone = $post['client_phone'];
		$bemp->client_cellphone = $post['client_cellphone'];
		$bemp->notarzt = $post['notarzt'];
		$bemp->mobil = $post['mobil'];
		$bemp->akuteblutungen_vompat = $post['akuteblutungen_vompat'];
		$bemp->akuteblutungen_vomart = $post['akuteblutungen_vomart'];
		$bemp->akuteblutungen_dosierung = $post['akuteblutungen_dosierung'];
		$bemp->akuteblutungen_24std = $post['akuteblutungen_24std'];
		$bemp->atemnot_vompat = $post['atemnot_vompat'];
		$bemp->atemnot_vomart = $post['atemnot_vomart'];
		$bemp->atemnot_dosierung = $post['atemnot_dosierung'];
		$bemp->atemnot_24std = $post['atemnot_24std'];
		$bemp->rasselatmung_vompat = $post['rasselatmung_vompat'];
		$bemp->rasselatmung_vomart = $post['rasselatmung_vomart'];
		$bemp->rasselatmung_dosierung = $post['rasselatmung_dosierung'];
		$bemp->rasselatmung_24std = $post['rasselatmung_24std'];
		$bemp->unruhe_vompat = $post['unruhe_vompat'];
		$bemp->unruhe_vomart = $post['unruhe_vomart'];
		$bemp->unruhe_dosierung = $post['unruhe_dosierung'];
		$bemp->unruhe_24std = $post['unruhe_24std'];
		$bemp->darmverschluss_vompat = $post['darmverschluss_vompat'];
		$bemp->darmverschluss_vomart = $post['darmverschluss_vomart'];
		$bemp->darmverschluss_dosierung = $post['darmverschluss_dosierung'];
		$bemp->darmverschluss_24std = $post['darmverschluss_24std'];
		$bemp->schmerzen_vompat = $post['schmerzen_vompat'];
		$bemp->schmerzen_vomart = $post['schmerzen_vomart'];
		$bemp->schmerzen_dosierung = $post['schmerzen_dosierung'];
		$bemp->schmerzen_24std = $post['schmerzen_24std'];
		$bemp->ubelkeit_vompat = $post['ubelkeit_vompat'];
		$bemp->ubelkeit_vomart = $post['ubelkeit_vomart'];
		$bemp->ubelkeit_dosierung = $post['ubelkeit_dosierung'];
		$bemp->ubelkeit_24std = $post['ubelkeit_24std'];
		$bemp->fieber_vompat = $post['fieber_vompat'];
		$bemp->fieber_vomart = $post['fieber_vomart'];
		$bemp->fieber_dosierung = $post['fieber_dosierung'];
		$bemp->fieber_24std = $post['fieber_24std'];
		$bemp->delir_vompat = $post['delir_vompat'];
		$bemp->delir_vomart = $post['delir_vomart'];
		$bemp->delir_dosierung = $post['delir_dosierung'];
		$bemp->delir_24std = $post['delir_24std'];
		$bemp->krampfanfall_vompat = $post['krampfanfall_vompat'];
		$bemp->krampfanfall_vomart = $post['krampfanfall_vomart'];
		$bemp->krampfanfall_dosierung = $post['krampfanfall_dosierung'];
		$bemp->krampfanfall_24std = $post['krampfanfall_24std'];
		
		$bemp->save();
		
		$result = $bemp->id;
		
		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s", time());
		$cust->course_type = Pms_CommonData::aesEncrypt("K");
		$comment = "Notfallplan BAYERN Formular wurde angelegt";
		$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
		$cust->tabname = Pms_CommonData::aesEncrypt("Bay_Emergency_form");
		$cust->done_name = Pms_CommonData::aesEncrypt("Bay_Emergency_form");
		$cust->done_id = $result;
		
		$cust->user_id = $userid;
		$cust->save();
		
		
		if($result > 0){
			return true;
		}else{
			return false;
		}
		
		
	}
	
	public function updateBayEmergencyPlan($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		
		$bemp = Doctrine::getTable('BayEmergencyPlan')->find($post['be_id']);
		
		$bemp->notfallplan = $post['notfallplan'];
		$bemp->pat_address = $post['pat_address'];
		$bemp->pat_phone = $post['pat_phone'];
		$bemp->hausarzt = $post['hausarzt'];
		$bemp->client_phone = $post['client_phone'];
		$bemp->client_cellphone = $post['client_cellphone'];
		$bemp->notarzt = $post['notarzt'];
		$bemp->mobil = $post['mobil'];
		$bemp->akuteblutungen_vompat = $post['akuteblutungen_vompat'];
		$bemp->akuteblutungen_vomart = $post['akuteblutungen_vomart'];
		$bemp->akuteblutungen_dosierung = $post['akuteblutungen_dosierung'];
		$bemp->akuteblutungen_24std = $post['akuteblutungen_24std'];
		$bemp->atemnot_vompat = $post['atemnot_vompat'];
		$bemp->atemnot_vomart = $post['atemnot_vomart'];
		$bemp->atemnot_dosierung = $post['atemnot_dosierung'];
		$bemp->atemnot_24std = $post['atemnot_24std'];
		$bemp->rasselatmung_vompat = $post['rasselatmung_vompat'];
		$bemp->rasselatmung_vomart = $post['rasselatmung_vomart'];
		$bemp->rasselatmung_dosierung = $post['rasselatmung_dosierung'];
		$bemp->rasselatmung_24std = $post['rasselatmung_24std'];
		$bemp->unruhe_vompat = $post['unruhe_vompat'];
		$bemp->unruhe_vomart = $post['unruhe_vomart'];
		$bemp->unruhe_dosierung = $post['unruhe_dosierung'];
		$bemp->unruhe_24std = $post['unruhe_24std'];
		$bemp->darmverschluss_vompat = $post['darmverschluss_vompat'];
		$bemp->darmverschluss_vomart = $post['darmverschluss_vomart'];
		$bemp->darmverschluss_dosierung = $post['darmverschluss_dosierung'];
		$bemp->darmverschluss_24std = $post['darmverschluss_24std'];
		$bemp->schmerzen_vompat = $post['schmerzen_vompat'];
		$bemp->schmerzen_vomart = $post['schmerzen_vomart'];
		$bemp->schmerzen_dosierung = $post['schmerzen_dosierung'];
		$bemp->schmerzen_24std = $post['schmerzen_24std'];
		$bemp->fieber_vompat = $post['fieber_vompat'];
		$bemp->fieber_vomart = $post['fieber_vomart'];
		$bemp->fieber_dosierung = $post['fieber_dosierung'];
		$bemp->fieber_24std = $post['fieber_24std'];
		$bemp->ubelkeit_vompat = $post['ubelkeit_vompat'];
		$bemp->ubelkeit_vomart = $post['ubelkeit_vomart'];
		$bemp->ubelkeit_dosierung = $post['ubelkeit_dosierung'];
		$bemp->ubelkeit_24std = $post['ubelkeit_24std'];
		$bemp->delir_vompat = $post['delir_vompat'];
		$bemp->delir_vomart = $post['delir_vomart'];
		$bemp->delir_dosierung = $post['delir_dosierung'];
		$bemp->delir_24std = $post['delir_24std'];
		$bemp->krampfanfall_vompat = $post['krampfanfall_vompat'];
		$bemp->krampfanfall_vomart = $post['krampfanfall_vomart'];
		$bemp->krampfanfall_dosierung = $post['krampfanfall_dosierung'];
		$bemp->krampfanfall_24std = $post['krampfanfall_24std'];
		$bemp->save();
		
		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt("Notfallplan BAYERN Formular  wurde editiert");
		$cust->recordid = $post['be_id'];
		$cust->tabname = Pms_CommonData::aesEncrypt("Bay_EmergencyPlan");
		$cust->user_id = $userid;
		$cust->save();
	}
}