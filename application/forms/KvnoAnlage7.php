<?php

require_once("Pms/Form.php");

class Application_Form_KvnoAnlage7 extends Pms_Form{

	public function insertKvnoAnlage7($post, $ipid = null){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$datum_der_erfassung1 =explode(".",$post['datum_der_erfassung1']);
		$datum_der_erfassung2 =explode(".",$post['datum_der_erfassung2']);
		$entlasung_date =explode(".",$post['entlasung_date']);

		$stmb = new KvnoAnlage7();
		$stmb->ipid = $ipid;
		$stmb->wohnsituation = $post['wohnsituation'];
		$stmb->ecog = $post['ecog'];
		$stmb->datum_der_erfassung1 = $datum_der_erfassung1[2]."-".$datum_der_erfassung1[1]."-".$datum_der_erfassung1[0];
		$stmb->schmerzen = $post['schmerzen'];
		$stmb->ubelkeit = $post['ubelkeit'];
		$stmb->erbrechen = $post['erbrechen'];
		$stmb->luftnot = $post['luftnot'];
		$stmb->verstopfung = $post['verstopfung'];
		$stmb->swache = $post['swache'];
		$stmb->appetitmangel = $post['appetitmangel'];
		$stmb->mudigkeit = $post['mudigkeit'];
		$stmb->dekubitus = $post['dekubitus'];
		$stmb->hilfebedarf = $post['hilfebedarf'];
		$stmb->depresiv = $post['depresiv'];
		$stmb->angst = $post['angst'];
		$stmb->anspannung = $post['anspannung'];
		$stmb->desorientier = $post['desorientier'];
		$stmb->versorgung = $post['versorgung'];
		$stmb->umfelds = $post['umfelds'];
		$stmb->kontaktes = $post['kontaktes'];
		$stmb->who = $post['who'];
		$stmb->steroide = $post['steroide'];
		$stmb->chemotherapie = $post['chemotherapie'];
		$stmb->strahlentherapie = $post['strahlentherapie'];
		$stmb->aufwand_mit = $post['aufwand_mit'];
		$stmb->problem_besonders = $post['problem_besonders'];
		$stmb->problem_ausreichend = $post['problem_ausreichend'];
		$stmb->entlasung_date = $entlasung_date[2]."-".$entlasung_date[1]."-".$entlasung_date[0];
		$stmb->therapieende  = $post['therapieende'];
		$stmb->sterbeort  = $post['sterbeort'];
		$stmb->sterbeort_dgp  = $post['sterbeort_dgp'];
		$stmb->zufriedenheit_mit  = $post['zufriedenheit_mit'];
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
		$stmb->save();

		$result = $stmb->id;

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("kvnoanlage7form");
		$cust->recordid = $result;
		$cust->user_id = $userid;
		$cust->save();



		if($ins->id>0){
			return true;
		}else{
			return false;
		}
	}

	public function UnsertKvnoAnlage7($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		$stmb = Doctrine::getTable('KvnoAnlage7')->find($post['kvno_anlage_id']);
		$datum_der_erfassung1 =explode(".",$post['datum_der_erfassung1']);
		$datum_der_erfassung2 =explode(".",$post['datum_der_erfassung2']);
		$entlasung_date =explode(".",$post['entlasung_date']);
		$stmb->wohnsituation = $post['wohnsituation'];
		$stmb->ecog = $post['ecog'];
		$stmb->datum_der_erfassung1 = $datum_der_erfassung1[2]."-".$datum_der_erfassung1[1]."-".$datum_der_erfassung1[0];
		$stmb->schmerzen = $post['schmerzen'];
		$stmb->ubelkeit = $post['ubelkeit'];
		$stmb->erbrechen = $post['erbrechen'];
		$stmb->luftnot = $post['luftnot'];
		$stmb->verstopfung = $post['verstopfung'];
		$stmb->swache = $post['swache'];
		$stmb->appetitmangel = $post['appetitmangel'];
		$stmb->mudigkeit = $post['mudigkeit'];
		$stmb->dekubitus = $post['dekubitus'];
		$stmb->hilfebedarf = $post['hilfebedarf'];
		$stmb->depresiv = $post['depresiv'];
		$stmb->angst = $post['angst'];
		$stmb->anspannung = $post['anspannung'];
		$stmb->desorientier = $post['desorientier'];
		$stmb->versorgung = $post['versorgung'];
		$stmb->umfelds = $post['umfelds'];
		$stmb->kontaktes = $post['kontaktes'];
		$stmb->who = $post['who'];
		$stmb->steroide = $post['steroide'];
		$stmb->chemotherapie = $post['chemotherapie'];
		$stmb->strahlentherapie = $post['strahlentherapie'];
		$stmb->aufwand_mit = $post['aufwand_mit'];
		$stmb->problem_besonders = $post['problem_besonders'];
		$stmb->problem_ausreichend = $post['problem_ausreichend'];
		$stmb->entlasung_date = $entlasung_date[2]."-".$entlasung_date[1]."-".$entlasung_date[0];
		$stmb->therapieende  = $post['therapieende'];
		$stmb->sterbeort  = $post['sterbeort'];
		$stmb->sterbeort_dgp  = $post['sterbeort_dgp'];
		$stmb->zufriedenheit_mit  = $post['zufriedenheit_mit'];
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
		$stmb->save();

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt("Kvno Anlage 7 wurde editiert");
		$cust->recordid = $post['kvno_anlage_id'];
		$cust->user_id = $userid;
		$cust->save();


	}

}

?>