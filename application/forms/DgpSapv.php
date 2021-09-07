<?php

require_once("Pms/Form.php");

class Application_Form_DgpSapv extends Pms_Form{
    /* ISPC-1775,ISPC-1678 */
	public function insertDgpSapv($post, $ipid){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$datum_der_erfassung1 =explode(".",$post['datum_der_erfassung1']);
		$datum_der_erfassung2 =explode(".",$post['datum_der_erfassung2']);
		$entlasung_date =explode(".",$post['entlasung_date']);

		$stmb = new DgpSapv();
		$stmb->ipid = $ipid;
		$stmb->identifiknr  = $post['identifiknr'];
		$stmb->sapv  = $post['sapv'];
		$stmb->verordnung_datum  = $post['verordnung_datum'];
		$stmb->art_der_erordnung  = $post['art_der_erordnung'];
		$stmb->verordnung_durch  = $post['verordnung_durch'];
		$stmb->ubernahme_aus  = $post['ubernahme_aus'];
		$stmb->arztlich  = join(",",$post['arztlich']);
		$stmb->arztlich_more  = $post['arztlich_more'];
		$stmb->pflegerisch  = join(",",$post['pflegerisch']);
		$stmb->ambulanter_hospizdienst  = join(",",$post['ambulanter_hospizdienst']);
		$stmb->weitere_professionen  = join(",",$post['weitere_professionen']);
		$stmb->weitere_professionen_more  = $post['weitere_professionen_more'];
		$stmb->regel_km  = $post['regel_km'];
		$stmb->anzahl_der_teambes  = $post['anzahl_der_teambes'];
		$stmb->krankenhause  = $post['krankenhause'];
		$stmb->end_date_sapv  = $post['end_date_sapv'];
		$stmb->sapvteam  = $post['sapvteam'];
		$stmb->versorgungsstufe  = $post['versorgungsstufe'];
		$stmb->datum_der_erfassung2 = $datum_der_erfassung2[2]."-".$datum_der_erfassung2[1]."-".$datum_der_erfassung2[0];
		$stmb->grund_einweisung  = $post['grund_einweisung'];
		$stmb->pcteam  = $post['pcteam'];
		$stmb->therapieende  = $post['therapieende'];
		$stmb->save();
		$result = $stmb->id;

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("dgpsapvform");
		$cust->recordid = $result;
		$cust->user_id = $userid;
		$cust->save();



		if($ins->id>0){
			return true;
		}else{
			return false;
		}
	}

	

	public function insert_minimal_dgp_sapv($post, $ipid){
		
	    $logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$stmb = new DgpSapv();
		$stmb->ipid = $ipid;
		$stmb->sapv  = $post['sapv'];
		$stmb->verordnung_datum  = $post['verordnung_datum'];
		$stmb->art_der_erordnung  = $post['art_der_erordnung'];
		$stmb->verordnung_durch  = $post['verordnung_durch'];
		$stmb->ubernahme_aus  = $post['ubernahme_aus'];
		$stmb->therapieende  = $post['therapieende'];
		$stmb->grund_einweisung  = $post['grund_einweisung'];
		
		$stmb->pcteam = $post['pcteam'];
		$stmb->arztlich  = join(",",$post['arztlich']);
		$stmb->arztlich_more  = $post['arztlich_more'];
		$stmb->pflegerisch  = join(",",$post['pflegerisch']);
		$stmb->ambulanter_hospizdienst  = join(",",$post['ambulanter_hospizdienst']);
		$stmb->weitere_professionen  = join(",",$post['weitere_professionen']);
		$stmb->weitere_professionen_more  = $post['weitere_professionen_more'];
		
		$stmb->save();
		
		$result = $stmb->id;
		
		if($post['course'] == "1")
		{
    		$cust = new PatientCourse();
    		$cust->ipid = $ipid;
    		$cust->course_date = date("Y-m-d H:i:s",time());
    		$cust->course_type=Pms_CommonData::aesEncrypt("K");
    		$cust->course_title=Pms_CommonData::aesEncrypt($comment);
    // 		$cust->tabname = Pms_CommonData::aesEncrypt("dgpsapvform");
    		$cust->tabname = Pms_CommonData::aesEncrypt("new_hospiz_register");
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

	
	
	public function UnsertDgpSapv($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		$stmb = Doctrine::getTable('DgpSapv')->find($post['dgp_sapv_id']);
		$datum_der_erfassung1 =explode(".",$post['datum_der_erfassung1']);
		$datum_der_erfassung2 =explode(".",$post['datum_der_erfassung2']);
		$entlasung_date =explode(".",$post['entlasung_date']);
		$stmb->identifiknr  = $post['identifiknr'];
		$stmb->sapv  = $post['sapv'];
		$stmb->verordnung_datum  = $post['verordnung_datum'];
		$stmb->art_der_erordnung  = $post['art_der_erordnung'];
		$stmb->verordnung_durch  = $post['verordnung_durch'];
		$stmb->ubernahme_aus  = $post['ubernahme_aus'];
		$stmb->arztlich  = join(",",$post['arztlich']);
		$stmb->arztlich_more  = $post['arztlich_more'];
		$stmb->pflegerisch  = join(",",$post['pflegerisch']);
		$stmb->ambulanter_hospizdienst  = join(",",$post['ambulanter_hospizdienst']);
		$stmb->weitere_professionen  = join(",",$post['weitere_professionen']);
		$stmb->weitere_professionen_more  = $post['weitere_professionen_more'];
		$stmb->regel_km  = $post['regel_km'];
		$stmb->anzahl_der_teambes  = $post['anzahl_der_teambes'];
		$stmb->krankenhause  = $post['krankenhause'];
		$stmb->end_date_sapv  = $post['end_date_sapv'];
		$stmb->sapvteam  = $post['sapvteam'];
		$stmb->versorgungsstufe  = $post['versorgungsstufe'];
		$stmb->datum_der_erfassung2 = $datum_der_erfassung2[2]."-".$datum_der_erfassung2[1]."-".$datum_der_erfassung2[0];
		$stmb->grund_einweisung  = $post['grund_einweisung'];
		$stmb->pcteam  = $post['pcteam'];
		$stmb->therapieende  = $post['therapieende'];
		$stmb->save();

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt("DGP-Kerndatensatz - Formular \"SAPV-Struktur-Modul\" wurde editiert");
		$cust->recordid = $post['dgp_sapv_id'];
		$cust->user_id = $userid;
		$cust->save();


	}

	
	
	public function update_minimal_dgp_sapv($post,$ipid)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$stmb = Doctrine::getTable('DgpSapv')->find($post['dgp_sapv_id']);
		$stmb->sapv  = $post['sapv'];
		$stmb->verordnung_datum  = $post['verordnung_datum'];
		$stmb->art_der_erordnung  = $post['art_der_erordnung'];
		$stmb->verordnung_durch  = $post['verordnung_durch'];
		$stmb->ubernahme_aus  = $post['ubernahme_aus'];
		$stmb->therapieende  = $post['therapieende'];
		$stmb->grund_einweisung  = $post['grund_einweisung'];
		
		$stmb->pcteam = $post['pcteam'];
		$stmb->arztlich  = join(",",$post['arztlich']);
		$stmb->arztlich_more  = $post['arztlich_more'];
		$stmb->pflegerisch  = join(",",$post['pflegerisch']);
		$stmb->ambulanter_hospizdienst  = join(",",$post['ambulanter_hospizdienst']);
		$stmb->weitere_professionen  = join(",",$post['weitere_professionen']);
		$stmb->weitere_professionen_more  = $post['weitere_professionen_more'];
		
		
		$stmb->save();

		if($post['course'] == "1")
		{
    		$cust = new PatientCourse();
    		$cust->ipid = $ipid;
    		$cust->course_date = date("Y-m-d H:i:s",time());
    		$cust->course_type=Pms_CommonData::aesEncrypt("K");
    		$cust->course_title=Pms_CommonData::aesEncrypt("DGP-Kerndatensatz - Formular \"SAPV-Struktur-Modul\" wurde editiert");
    		$cust->recordid = $post['dgp_sapv_id'];
    		$cust->tabname = Pms_CommonData::aesEncrypt("new_hospiz_register");
    		$cust->user_id = $userid;
    		$cust->save();
		}


	}

}

?>