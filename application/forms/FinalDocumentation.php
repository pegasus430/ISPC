<?php

require_once("Pms/Form.php");

class Application_Form_FinalDocumentation extends Pms_Form{

	public function insertFinalDocumentation($post, $ipid = ''){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$stmb = new FinalDocumentation();
		$stmb->ipid = $ipid;
		// block Einsatzart
		$stmb->im_hausliche_begleitung = $post['im_hausliche_begleitung']; //1
		$stmb->hospizappartement = $post['hospizappartement'];//2
		$stmb->einsatz_ausschlieblich = $post['einsatz_ausschlieblich'];//3
		$stmb->ausschlieblich_klinik = $post['ausschlieblich_klinik'];//4
		$stmb->ausschlieblich_telefonische = $post['ausschlieblich_telefonische'];//5
		//Quantitative Aufwandsverteilung
		$stmb->quantitative_patient = $post['quantitative_patient'];
		$stmb->angehorige = $post['angehorige'];
		$stmb->systemische_tatigkeiten = $post['systemische_tatigkeiten'];

		// block Erschwerte Bedingungen: für die häusliche Betreuung
		$stmb->symptome_bzw = $post['symptome_bzw']; //1
		$stmb->patient_lasst = $post['patient_lasst'];//2
		$stmb->angehorige_lassen = $post['angehorige_lassen'];//3
		$stmb->belastbarkeit_der = $post['belastbarkeit_der'];//4
		$stmb->angst_vor_sozialen = $post['angst_vor_sozialen'];//5
		$stmb->zusammenarbeit_mit_niedergelassenen = $post['zusammenarbeit_mit_niedergelassenen'];//6
		$stmb->zusammenarbeit_mit = $post['zusammenarbeit_mit'];//7
		$stmb->medizin = $post['medizin'];//8
		$stmb->wohnsituation = $post['wohnsituation'];//9
		$stmb->finanzielle_notlage = $post['finanzielle_notlage'];//10
		$stmb->kulturelle_unterschiede = $post['kulturelle_unterschiede'];//11
		$stmb->sprachbarrieren = $post['sprachbarrieren'];//12

		//Abschlussgrund
		$stmb->abschlussgrund = $post['abschlussgrund'];//1  radio
		$stmb->beendigung_der_begleitung = $post['beendigung_der_begleitung'];//1  radio
		$stmb->beendigung_der_begleitung_chk = $post['beendigung_der_begleitung_chk'];//2 checkbox
		$stmb->stabilisierung_des_gesundheitszustandes = $post['stabilisierung_des_gesundheitszustandes'];//2 checkbox


		//Todesdatum
		$stmb->todesdatum = $post['todesdatum'];//1 radio
		$stmb->death_date = $post['death_date'];
				
		//Ort des Sterbens
		$stmb->Wunsch = $post['Wunsch'];//1 radio
		$stmb->tatsachlich = $post['tatsachlich'];//1 radio

		//Pflegestufe
		$stmb->pflegestufe_beginn = $post['pflegestufe_beginn'];//1 radio
		$stmb->beantragt = $post['beantragt'];//2 radio
		$stmb->beantragt_am_txt = $post['beantragt_am_txt'];//text
		$stmb->abschluss = $post['abschluss'];//3 radio
		$stmb->abschluss_seit = $post['abschluss_seit'];//4 check
		$stmb->abschluss_seit_txt = $post['abschluss_seit_txt'];//text


		//Symptomentwicklung rückblickend
		$stmb->beginn_ausgepragte_schmerzsymptomatik = $post['beginn_ausgepragte_schmerzsymptomatik'];//1 check
		$stmb->abschluss_ausgepragte_schmerzsymptomatik = $post['abschluss_ausgepragte_schmerzsymptomatik'];//2 check

		$stmb->beginn_kardiale_symptomatik = $post['beginn_kardiale_symptomatik'];//3 check
		$stmb->abschluss_kardiale_symptomatik = $post['abschluss_kardiale_symptomatik'];//4 check

		$stmb->beginn_gastrointestinale_symptomatik = $post['beginn_gastrointestinale_symptomatik'];//5 check
		$stmb->abschluss_gastrointestinale_symptomatik = $post['abschluss_gastrointestinale_symptomatik'];//6 check

		$stmb->beginn_neurologische = $post['beginn_neurologische'];//7 check
		$stmb->abschluss_neurologische = $post['abschluss_neurologische'];//8 check

		$stmb->beginn_ulzerierende = $post['beginn_ulzerierende'];//9 check
		$stmb->abschluss_ulzerierende = $post['abschluss_ulzerierende'];//10 check

		$stmb->beginn_urogenitale_symptomatik = $post['beginn_urogenitale_symptomatik'];//11 check
		$stmb->abschluss_urogenitale_symptomatik = $post['abschluss_urogenitale_symptomatik'];//12 check

		$stmb->beginn_soziale_situation = $post['beginn_soziale_situation'];//13 check
		$stmb->abschluss_soziale_situation = $post['abschluss_soziale_situation'];//14 check

		$stmb->beginn_sonstiges = $post['beginn_sonstiges'];//15 check
		$stmb->abschluss_sonstiges = $post['abschluss_sonstiges'];//16 check

		$stmb->beginn_ethische_konflikte = $post['beginn_ethische_konflikte'];//17 check
		$stmb->abschluss_ethische_konflikte = $post['abschluss_ethische_konflikte'];//18 check

		$stmb->beginn_rechtliche_problematik = $post['beginn_rechtliche_problematik'];//19 check
		$stmb->abschluss_rechtliche_problematik = $post['abschluss_rechtliche_problematik'];//20 check

		$stmb->beginn_unterstutzung_bezugssystem = $post['beginn_unterstutzung_bezugssystem'];//21 check
		$stmb->abschluss_unterstutzung_bezugssystem = $post['abschluss_unterstutzung_bezugssystem'];//22 check

		$stmb->beginn_existentielle_krisen = $post['beginn_existentielle_krisen'];//23 check
		$stmb->abschluss_existentielle_krisen = $post['abschluss_existentielle_krisen'];//24 check

		//Betreuungsnetz / Kooperationspartner während der Begleitung

		$stmb->pcf = $post['pcf'];//1 check
		$stmb->palliativmediziner = $post['palliativmediziner'];//2 check
		$stmb->hausarzt = $post['hausarzt'];//3 check
		$stmb->facharzt = $post['facharzt'];//4 check

		$stmb->amb_hospizdienst = $post['amb_hospizdienst'];//5 check
		$stmb->pflegedienst = $post['pflegedienst'];//6 check
		$stmb->sozialstation = $post['sozialstation'];//7 check
		$stmb->sozialarbeit = $post['sozialarbeit'];//8 check

		$stmb->stationares_hospiz = $post['stationares_hospiz'];//9 check
		$stmb->krankenhaus = $post['krankenhaus'];//10 check
		$stmb->palliativstation = $post['palliativstation'];//11 check
		$stmb->stationare_pflege = $post['stationare_pflege'];//12 check

		$stmb->physiotherapie = $post['physiotherapie'];//13 check
		$stmb->psychologe = $post['psychologe'];//14 check
		$stmb->apotheke_sanitatshaus = $post['apotheke_sanitatshaus'];//15 check
		$stmb->weitere_berufe = $post['weitere_berufe'];//16 check

		$stmb->angehorige_grundpflege = $post['angehorige_grundpflege'];//17 check
		$stmb->angehorige_behandlungspflege = $post['angehorige_behandlungspflege'];//18 check

		$stmb->seelsorge = $post['seelsorge'];//19 check


		// Begleitungsprobleme
		$stmb->begleitungs_arzt = $post['begleitungs_arzt'];//1 check
		$stmb->begleitungs_pflegedienst = $post['begleitungs_pflegedienst'];//2 check
		$stmb->begleitungs_krankenkasse = $post['begleitungs_krankenkasse'];//3 check
		$stmb->begleitungs_pflegekasse = $post['begleitungs_pflegekasse'];//4 check
		$stmb->begleitungs_mdk = $post['begleitungs_mdk'];//5 check
		$stmb->begleitungs_homecare = $post['begleitungs_homecare'];//6 check
		$stmb->begleitungs_apotheke = $post['begleitungs_apotheke'];//7 check
		$stmb->begleitungs_klinikum = $post['begleitungs_klinikum'];//8 check
		$stmb->begleitungs_altenheim = $post['begleitungs_altenheim'];//9 check
		$stmb->begleitungs_hospiz = $post['begleitungs_hospiz'];//10 check
		$stmb->begleitungs_seelsorge = $post['begleitungs_seelsorge'];//11 check
		$stmb->begleitungs_seelsorge1 = $post['begleitungs_seelsorge1'];//12 check
		$stmb->new_instance  = '1';
		$stmb->save();


		$result = $stmb->id;

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s", time());
		$cust->course_type = Pms_CommonData::aesEncrypt("K");
		$cust->course_title = Pms_CommonData::aesEncrypt("Abschlussdokumentation hinzugefügt.");
		$cust->recordid = $result;
		$cust->tabname = Pms_CommonData::aesEncrypt("FinalDocumentation_form");
		$cust->user_id = $userid;
		$cust->save();


		if($result > 0){
			return true;
		}else{
			return false;
		}
	}


	public function UpdateFinalDocumentation($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

			
		$stmb = Doctrine::getTable('FinalDocumentation')->find($post['form_id']);

		$stmb->im_hausliche_begleitung = $post['im_hausliche_begleitung']; //1
		$stmb->hospizappartement = $post['hospizappartement'];//2
		$stmb->einsatz_ausschlieblich = $post['einsatz_ausschlieblich'];//3
		$stmb->ausschlieblich_klinik = $post['ausschlieblich_klinik'];//4
		$stmb->ausschlieblich_telefonische = $post['ausschlieblich_telefonische'];//5


		//Quantitative Aufwandsverteilung
		$stmb->quantitative_patient = $post['quantitative_patient'];
		$stmb->angehorige = $post['angehorige'];
		$stmb->systemische_tatigkeiten = $post['systemische_tatigkeiten'];

		// block Erschwerte Bedingungen: für die häusliche Betreuung
		$stmb->symptome_bzw = $post['symptome_bzw']; //1
		$stmb->patient_lasst = $post['patient_lasst'];//2
		$stmb->angehorige_lassen = $post['angehorige_lassen'];//3
		$stmb->belastbarkeit_der = $post['belastbarkeit_der'];//4
		$stmb->angst_vor_sozialen = $post['angst_vor_sozialen'];//5
		$stmb->zusammenarbeit_mit_niedergelassenen = $post['zusammenarbeit_mit_niedergelassenen'];//6
		$stmb->zusammenarbeit_mit = $post['zusammenarbeit_mit'];//7
		$stmb->medizin = $post['medizin'];//8
		$stmb->wohnsituation = $post['wohnsituation'];//9
		$stmb->finanzielle_notlage = $post['finanzielle_notlage'];//10
		$stmb->kulturelle_unterschiede = $post['kulturelle_unterschiede'];//11
		$stmb->sprachbarrieren = $post['sprachbarrieren'];//12

		//Abschlussgrund
		$stmb->abschlussgrund = $post['abschlussgrund'];//1  radio
		$stmb->beendigung_der_begleitung = $post['beendigung_der_begleitung'];//1  radio
		$stmb->beendigung_der_begleitung_chk = $post['beendigung_der_begleitung_chk'];//2 checkbox
		$stmb->stabilisierung_des_gesundheitszustandes = $post['stabilisierung_des_gesundheitszustandes'];//2 checkbox


		//Todesdatum
		$stmb->todesdatum = $post['todesdatum'];//1 radio
		$stmb->death_date = $post['death_date'];
		
		//Ort des Sterbens
		$stmb->Wunsch = $post['Wunsch'];//1 radio
		$stmb->tatsachlich = $post['tatsachlich'];//1 radio

		//Pflegestufe
		$stmb->pflegestufe_beginn = $post['pflegestufe_beginn'];//1 radio
		$stmb->beantragt = $post['beantragt'];//2 radio
		$stmb->beantragt_am_txt = $post['beantragt_am_txt'];//text
		$stmb->abschluss = $post['abschluss'];//3 radio
		$stmb->abschluss_seit = $post['abschluss_seit'];//4 check
		$stmb->abschluss_seit_txt = $post['abschluss_seit_txt'];//text


		//Symptomentwicklung rückblickend
		$stmb->beginn_ausgepragte_schmerzsymptomatik = $post['beginn_ausgepragte_schmerzsymptomatik'];//1 check
		$stmb->abschluss_ausgepragte_schmerzsymptomatik = $post['abschluss_ausgepragte_schmerzsymptomatik'];//2 check
		$stmb->beginn_kardiale_symptomatik = $post['beginn_kardiale_symptomatik'];//3 check
		$stmb->abschluss_kardiale_symptomatik = $post['abschluss_kardiale_symptomatik'];//4 check
		$stmb->beginn_gastrointestinale_symptomatik = $post['beginn_gastrointestinale_symptomatik'];//5 check
		$stmb->abschluss_gastrointestinale_symptomatik = $post['abschluss_gastrointestinale_symptomatik'];//6 check
		$stmb->beginn_neurologische = $post['beginn_neurologische'];//7 check
		$stmb->abschluss_neurologische = $post['abschluss_neurologische'];//8 check

		$stmb->beginn_ulzerierende = $post['beginn_ulzerierende'];//9 check
		$stmb->abschluss_ulzerierende = $post['abschluss_ulzerierende'];//10 check

		$stmb->beginn_urogenitale_symptomatik = $post['beginn_urogenitale_symptomatik'];//11 check
		$stmb->abschluss_urogenitale_symptomatik = $post['abschluss_urogenitale_symptomatik'];//12 check

		$stmb->beginn_soziale_situation = $post['beginn_soziale_situation'];//13 check
		$stmb->abschluss_soziale_situation = $post['abschluss_soziale_situation'];//14 check

		$stmb->beginn_sonstiges = $post['beginn_sonstiges'];//15 check
		$stmb->abschluss_sonstiges = $post['abschluss_sonstiges'];//16 check

		$stmb->beginn_ethische_konflikte = $post['beginn_ethische_konflikte'];//17 check
		$stmb->abschluss_ethische_konflikte = $post['abschluss_ethische_konflikte'];//18 check

		$stmb->beginn_rechtliche_problematik = $post['beginn_rechtliche_problematik'];//19 check
		$stmb->abschluss_rechtliche_problematik = $post['abschluss_rechtliche_problematik'];//20 check

		$stmb->beginn_unterstutzung_bezugssystem = $post['beginn_unterstutzung_bezugssystem'];//21 check
		$stmb->abschluss_unterstutzung_bezugssystem = $post['abschluss_unterstutzung_bezugssystem'];//22 check

		$stmb->beginn_existentielle_krisen = $post['beginn_existentielle_krisen'];//23 check
		$stmb->abschluss_existentielle_krisen = $post['abschluss_existentielle_krisen'];//24 check

		//Betreuungsnetz / Kooperationspartner während der Begleitung

		$stmb->pcf = $post['pcf'];//1 check
		$stmb->palliativmediziner = $post['palliativmediziner'];//2 check
		$stmb->hausarzt = $post['hausarzt'];//3 check
		$stmb->facharzt = $post['facharzt'];//4 check

		$stmb->amb_hospizdienst = $post['amb_hospizdienst'];//5 check
		$stmb->pflegedienst = $post['pflegedienst'];//6 check
		$stmb->sozialstation = $post['sozialstation'];//7 check
		$stmb->sozialarbeit = $post['sozialarbeit'];//8 check

		$stmb->stationares_hospiz = $post['stationares_hospiz'];//9 check
		$stmb->krankenhaus = $post['krankenhaus'];//10 check
		$stmb->palliativstation = $post['palliativstation'];//11 check
		$stmb->stationare_pflege = $post['stationare_pflege'];//12 check

		$stmb->physiotherapie = $post['physiotherapie'];//13 check
		$stmb->psychologe = $post['psychologe'];//14 check
		$stmb->apotheke_sanitatshaus = $post['apotheke_sanitatshaus'];//15 check
		$stmb->weitere_berufe = $post['weitere_berufe'];//16 check

		$stmb->angehorige_grundpflege = $post['angehorige_grundpflege'];//17 check
		$stmb->angehorige_behandlungspflege = $post['angehorige_behandlungspflege'];//18 check
		$stmb->seelsorge = $post['seelsorge'];//19 check

		// Begleitungsprobleme
		$stmb->begleitungs_arzt = $post['begleitungs_arzt'];//1 check
		$stmb->begleitungs_pflegedienst = $post['begleitungs_pflegedienst'];//2 check
		$stmb->begleitungs_krankenkasse = $post['begleitungs_krankenkasse'];//3 check
		$stmb->begleitungs_pflegekasse = $post['begleitungs_pflegekasse'];//4 check
		$stmb->begleitungs_mdk = $post['begleitungs_mdk'];//5 check
		$stmb->begleitungs_homecare = $post['begleitungs_homecare'];//6 check
		$stmb->begleitungs_apotheke = $post['begleitungs_apotheke'];//7 check
		$stmb->begleitungs_klinikum = $post['begleitungs_klinikum'];//8 check
		$stmb->begleitungs_altenheim = $post['begleitungs_altenheim'];//9 check
		$stmb->begleitungs_hospiz = $post['begleitungs_hospiz'];//10 check
		$stmb->begleitungs_seelsorge = $post['begleitungs_seelsorge'];//11 check
		$stmb->begleitungs_seelsorge1 = $post['begleitungs_seelsorge1'];//12 check

		$stmb->save();

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt("Abschlussdokumentation  wurde editiert");
		$cust->recordid = $post['form_id'];
		$cust->tabname = Pms_CommonData::aesEncrypt("FinalDocumentation_form");
		$cust->user_id = $userid;
		$cust->save();
	}

	public function NewInstanceFinalDocumentation()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$drop = Doctrine_Query::create()
		->update('FinalDocumentation')
		->set("new_instance",'0')
		->where("ipid LIKE '" . $ipid . "'");

		$drop->execute();


	}
}

?>