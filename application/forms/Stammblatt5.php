<?php

require_once("Pms/Form.php");

class Application_Form_Stammblatt5 extends Pms_Form{

	public function insertStammblatt5($post){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		$Qur = Doctrine_Query::create()
		->delete('Stammblatt5')
		->where("ipid='".$ipid."'");
		$Qur->execute();

		$stmb = new Stammblatt5();
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
		$stmb->ecog = join(",",$post['ecog']);
		$stmb->religion = $post['religion'];
		$stmb->genogramm = $post['genogramm'];
		$stmb->main_diagnosis = $post['main_diagnosis'];
		//ISPC-1790 
		$stmb->cntpers_1_tel = $post['cntpers_1_tel'];
		$stmb->cntpers_1_handy = $post['cntpers_1_handy'];
		$stmb->cntpers_2_tel = $post['cntpers_2_tel'];
		$stmb->cntpers_2_handy = $post['cntpers_2_handy'];
		$stmb->hausarzt_tel = $post['hausarzt_tel'];
		$stmb->hausarzt_fax = $post['hausarzt_fax'];
		$stmb->pflegedienst_tel = $post['pflegedienst_tel'];
		$stmb->pflegedienst_fax = $post['pflegedienst_fax'];
		$stmb->apotheke_tel = $post['apotheke_tel'];
		$stmb->apotheke_fax = $post['apotheke_fax'];
		$stmb->pathandy = $post['pathandy'];
		$stmb->pattel = $post['pattel'];
		
		// ISPC-2590 Andrei 22.05.2020
		$stmb->cntpers_1_hatversorgungsvollmacht = $post['cntpers_1_hatversorgungsvollmacht'];
		$stmb->cntpers_1_legalguardian = $post['cntpers_1_legalguardian'];
		$stmb->cntpers_2_hatversorgungsvollmacht = $post['cntpers_2_hatversorgungsvollmacht'];
		$stmb->cntpers_2_legalguardian = $post['cntpers_2_legalguardian'];
		
		$stmb->save();


		if($stmb->id>0){
			return true;
		}else{
			return false;
		}
	}

	public function UpdateStammblatt5($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

			
		$stmb = Doctrine::getTable('Stammblatt5')->find($post['mdk_id']);

		$pflegeversicherung  = implode(",",$post['pflegeversicherung']);
		$stmb->cntpers1 = $post['cntpers1'];
		$stmb->cntpers2 = $post['cntpers2'];
		$stmb->pflegeperson = $post['pflegeperson'];
		$stmb->pflege_benefits = $post['pflege_benefits'];
		$stmb->maindiagnosis = $post['maindiagnosis'];
		$stmb->ambulante = $post['ambulante'];
		$stmb->kurative = $post['kurative'];
		$stmb->behandlungsansatz = $post['behandlungsansatz'];
		$stmb->aufklarung = $post['aufklarung'];
		$stmb->livingwill = $post['livingwill'];
		$stmb->livingwill_txt = $post['livingwill_txt'];
		$stmb->palliativer = $post['palliativer'];
		$stmb->palliativer_txt = $post['palliativer_txt'];
		$stmb->erfolgen = $post['erfolgen'];
		$stmb->erfolgen_txt = $post['erfolgen_txt'];
		$stmb->schem_symptomatik = $post['schem_symptomatik'];
		$stmb->extreme_symptome = $post['extreme_symptome'];
		$stmb->extreme_symptome_txt = $post['extreme_symptome_txt'];
		$stmb->psychosoziale_a = $post['psychosoziale_a'];
		$stmb->psychosoziale_a_txt = $post['psychosoziale_a_txt'];
		$stmb->psychosoziale_b = $post['psychosoziale_b'];
		$stmb->psychosoziale_b_txt = $post['psychosoziale_b_txt'];
		$stmb->psychosoziale_c = $post['psychosoziale_c'];
		$stmb->psychosoziale_c_txt = $post['psychosoziale_c_txt'];
		$stmb->angehorige = $post['angehorige'];
		$stmb->angehorige_txt = $post['angehorige_txt'];
		$stmb->krakenpflege  = $post['krakenpflege'];
		$stmb->krakenpflege_txt  = $post['krakenpflege_txt'];
		$stmb->liegen_sapv  = $post['liegen_sapv'];
		$stmb->liegen_sapv_txt  = $post['liegen_sapv_txt'];
		$stmb->medizinische_txt  = $post['medizinische_txt'];
		$stmb->weitere  = $post['weitere'];
		$stmb->weitere_txt  = $post['weitere_txt'];
		$stmb->sonstiges  = $post['sonstiges'];
		$stmb->main_diagnosis  = $post['main_diagnosis'];

		$stmb->save();




		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt("Stammblatt wurde editiert");
		$cust->recordid = $post['mdk_id'];
		$cust->user_id = $userid;
		$cust->save();


	}

}

?>