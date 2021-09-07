<?php

require_once("Pms/Form.php");

class Application_Form_Genehmigungs extends Pms_Form{

	public function insertGenehmigungs($post){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$stmb = new Genehmigungs();
		$stmb->ipid = $ipid;
		$stmb->mndiagnosis = $post['mndiagnosis'];
		$stmb->diagnosemit = $post['diagnosemit'];
		$stmb->metastasis =  join(",",$post['metastasis']);
		$stmb->sidediagnosis = $post['sidediagnosis'];
		$stmb->bishtherapie =  join(",",$post['bishtherapie']);
		$stmb->actuellmed = $post['actuellmed'];
		$stmb->art_der_verabreichung =  join(",",$post['art_der_verabreichung']);
		$stmb->andere = join(",",$post['andere']);
		$stmb->andere_txt = $post['andere_txt'];
		$stmb->besondere = join(",",$post['besondere']);
		$stmb->besondere_txt = $post['besondere_txt'];
		$stmb->k_index = $post['k_index'];
		$stmb->pflegestufe = $post['pflegestufe'];
		$stmb->st_pflegestufe = $post['st_pflegestufe'];
		$stmb->schsymptomatik = $post['schsymptomatik'];
		$stmb->lokalisation = $post['lokalisation'];
		$stmb->dyspnoe = $post['dyspnoe'];
		$stmb->hamoptoe = $post['hamoptoe'];
		$stmb->nyha = $post['nyha'];
		$stmb->respiratorische_txt = $post['respiratorische_txt'];
		$stmb->respiratorische_txt_chk = $post['respiratorische_txt_chk'];
		$stmb->quantitative = $post['quantitative'];
		$stmb->hirndrucksymptome = $post['hirndrucksymptome'];
		$stmb->spastik = $post['spastik'];
		$stmb->myoklonus = $post['myoklonus'];
		$stmb->muskelkampfe = $post['muskelkampfe'];
		$stmb->depression = $post['depression'];
		$stmb->psychotische_syndrome = $post['psychotische_syndrome'];
		$stmb->neurologische_txt = $post['neurologische_txt'];
		$stmb->neurologische_txt_chk = $post['neurologische_txt_chk'];
		$stmb->lokalisation_a_txt = $post['lokalisation_a_txt'];
		$stmb->lokalisation_a_chk = $post['lokalisation_a_chk'];
		$stmb->lokalisation_b_txt = $post['lokalisation_b_txt'];
		$stmb->lokalisation_b_chk = $post['lokalisation_b_chk'];
		$stmb->lokalisation_c_txt = $post['lokalisation_c_txt'];
		$stmb->lokalisation_c_chk = $post['lokalisation_c_chk'];
		$stmb->anorexie_kachexie = $post['anorexie_kachexie'];
		$stmb->mukositis = $post['mukositis'];
		$stmb->dysphagie = $post['dysphagie'];
		$stmb->erbrechen = $post['erbrechen'];
		$stmb->hamatemesis = $post['hamatemesis'];
		$stmb->ikterus = $post['ikterus'];
		$stmb->ileus = $post['ileus'];
		$stmb->aszites = $post['aszites'];
		$stmb->diarrhoe = $post['diarrhoe'];
		$stmb->fisteln = $post['fisteln'];
		$stmb->gastrointestinale_txt = $post['gastrointestinale_txt'];
		$stmb->gastrointestinale_txt_chk = $post['gastrointestinale_txt_chk'];
		$stmb->harnwegsinfekt = $post['harnwegsinfekt'];
		$stmb->dysurie = $post['dysurie'];
		$stmb->blasentenesmen = $post['blasentenesmen'];
		$stmb->hamaturie = $post['hamaturie'];
		$stmb->vaginale_blutung = $post['vaginale_blutung'];
		$stmb->urogenitale_txt = $post['urogenitale_txt'];
		$stmb->urogenitale_txt_chk = $post['urogenitale_txt_chk'];
		$stmb->besondere_erfordernisse = $post['besondere_erfordernisse'];
		$stmb->aktueller_versorgungsbedarf = $post['aktueller_versorgungsbedarf'];

		$stmb->save();

		$result = $stmb->id;

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("genehmigungs");
		$cust->recordid = $result;
		$cust->user_id = $userid;
		$cust->save();



		if($ins->id>0){
			return true;
		}else{
			return false;
		}
	}

	public function UpdateGenehmigungs($post){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;


		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$stmb = Doctrine::getTable('Genehmigungs')->find($post['genehmig_id']);

		$stmb->mndiagnosis = $post['mndiagnosis'];
		$stmb->diagnosemit = $post['diagnosemit'];
		$stmb->metastasis =  join(",",$post['metastasis']);
		$stmb->sidediagnosis = $post['sidediagnosis'];
		$stmb->bishtherapie =  join(",",$post['bishtherapie']);
		$stmb->actuellmed = $post['actuellmed'];
		$stmb->art_der_verabreichung =  join(",",$post['art_der_verabreichung']);
		$stmb->andere = join(",",$post['andere']);
		$stmb->andere_txt = $post['andere_txt'];
		$stmb->besondere = join(",",$post['besondere']);
		$stmb->besondere_txt = $post['besondere_txt'];
		$stmb->k_index = $post['k_index'];
		$stmb->pflegestufe = $post['pflegestufe'];
		$stmb->st_pflegestufe = $post['st_pflegestufe'];
		$stmb->schsymptomatik = $post['schsymptomatik'];
		$stmb->lokalisation = $post['lokalisation'];
		$stmb->dyspnoe = $post['dyspnoe'];
		$stmb->hamoptoe = $post['hamoptoe'];
		$stmb->nyha = $post['nyha'];
		$stmb->respiratorische_txt = $post['respiratorische_txt'];
		$stmb->respiratorische_txt_chk = $post['respiratorische_txt_chk'];
		$stmb->quantitative = $post['quantitative'];
		$stmb->hirndrucksymptome = $post['hirndrucksymptome'];
		$stmb->spastik = $post['spastik'];
		$stmb->myoklonus = $post['myoklonus'];
		$stmb->muskelkampfe = $post['muskelkampfe'];
		$stmb->depression = $post['depression'];
		$stmb->psychotische_syndrome = $post['psychotische_syndrome'];
		$stmb->neurologische_txt = $post['neurologische_txt'];
		$stmb->neurologische_txt_chk = $post['neurologische_txt_chk'];
		$stmb->lokalisation_a_txt = $post['lokalisation_a_txt'];
		$stmb->lokalisation_a_chk = $post['lokalisation_a_chk'];
		$stmb->lokalisation_b_txt = $post['lokalisation_b_txt'];
		$stmb->lokalisation_b_chk = $post['lokalisation_b_chk'];
		$stmb->lokalisation_c_txt = $post['lokalisation_c_txt'];
		$stmb->lokalisation_c_chk = $post['lokalisation_c_chk'];
		$stmb->anorexie_kachexie = $post['anorexie_kachexie'];
		$stmb->mukositis = $post['mukositis'];
		$stmb->dysphagie = $post['dysphagie'];
		$stmb->erbrechen = $post['erbrechen'];
		$stmb->hamatemesis = $post['hamatemesis'];
		$stmb->ikterus = $post['ikterus'];
		$stmb->ileus = $post['ileus'];
		$stmb->aszites = $post['aszites'];
		$stmb->diarrhoe = $post['diarrhoe'];
		$stmb->fisteln = $post['fisteln'];
		$stmb->gastrointestinale_txt = $post['gastrointestinale_txt'];
		$stmb->gastrointestinale_txt_chk = $post['gastrointestinale_txt_chk'];
		$stmb->harnwegsinfekt = $post['harnwegsinfekt'];
		$stmb->dysurie = $post['dysurie'];
		$stmb->blasentenesmen = $post['blasentenesmen'];
		$stmb->hamaturie = $post['hamaturie'];
		$stmb->vaginale_blutung = $post['vaginale_blutung'];
		$stmb->urogenitale_txt = $post['urogenitale_txt'];
		$stmb->urogenitale_txt_chk = $post['urogenitale_txt_chk'];
		$stmb->besondere_erfordernisse = $post['besondere_erfordernisse'];
		$stmb->aktueller_versorgungsbedarf = $post['aktueller_versorgungsbedarf'];

		$stmb->save();


		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt("Genehmigungsformular  wurde editiert");
		$cust->recordid = $post['genehmig_id'];
		$cust->user_id = $userid;
		$cust->save();
	}

}

?>