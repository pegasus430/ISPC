<?php

require_once("Pms/Form.php");

class Application_Form_Formone extends Pms_Form
{
	public function insertFormone($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$Qur = Doctrine_Query::create()
		->update('Formone')
		->set('valid_till',"'".date("Y-m-d H:i:s",time())."'")
		->where("ipid='".$ipid."'");
		$Qur->execute();


		$stmb = new Formone();
		$stmb->ipid = $ipid;

		$stmb->company_name = $post['client_name'];
		$stmb->insurance_no = $post['kassen_nr'];
		$stmb->betriebsstatten_nr = $post['betriebsstatten_nr'];
		$stmb->institutskennzeichen = $post['institutskennzeichen'];
		$stmb->gender = $post['gender'];

		$stmb->grundkrankheit = $post['icddiagnosis'];
		$stmb->folgende_mabnahme_wurden_durch = join(",",$post['folgende_mabnahme_wurden_durch']);
		$stmb->verordnet =  join(",",$post['verordnet']);
		$stmb->erstkontakt = join(",",$post['erstkontakt']);
		$stmb->wohnsituation =  join(",",$post['wohnsituation']);
		$stmb->wohnsituation_other = $post['othertext'];
		$stmb->pflegestufe = join(",",$post['pflegestufe']);
		$stmb->wunsch_des_pat_zu_beginn = $post['wunsch_des_pat_zu_beginn'];
		$stmb->wunsch_identisch_mit_sapv_behandlungsziel = $post['sapv_behandlungszie_ja'];
		$stmb->av_beratung = $post['beratung_en_one'];
		$stmb->av_koordination = $post['koordination_one'];
		$stmb->av_teilversorgung = $post['teilversorgung_one'];
		$stmb->av_vollversorgung = $post['vollversorgung_one'];
		$stmb->betreuungsrelevante_nebendiagnosen = join(",",$post['betreuungsrelevante_nebendiagnosen']);
		$stmb->betreuungsrelevante_nebendiagnosen_textone = $post['betreuungsrelevante_nebendiagnosen_txtone'];
		$stmb->komplexes_symptomgeschehen = join(",",$post['komplexes_symptomgeschehen']);
		$stmb->komplexes_symptomgeschehen_other = $post['sonstiges_txt'];
		$stmb->weiteres_komplexes_geschehen = join(",",$post['weiteres_komplexes_geschehen']);
		$stmb->am_patient = $post['am_patient'];
		$stmb->fur_angehorige = $post['fur_angehorige'];
		$stmb->systemische_tatigkeiten = $post['systemische_tatigkeiten'];
		$stmb->betreuungsnetz = join(",",$post['betreuungsnetz']);
		$stmb->pat_wunsch_erfullt = $post['pat_wunsch_erfullt'];
		$stmb->beendigung_der_sapv_wegen = join(",",$post['beendigung_der_sapv_wegen']);
		$stmb->beendigung_der_sapv_wegen_other = $post['sonstigestwotxt'];
		$stmb->pflegestufe_b_abschluss = join(",",$post['pfl_egestufe_b_abschluss']);
		$stmb->zusatzliche_angaben_bei_verstorbene = join(",",$post['zusatzliche_angaben_bei_verstorbenen']);
		$stmb->ggf_weitere_angaben = $post['ggf_weitere_angaben'];
		$stmb->sterbeort_n_wunsch = $post['sterbeort_n_wunsch'];
		$stmb->behandlungsdauer_in_tagen = $post['behandlungsdauer_in_tagen'];
		$stmb->besuche_pc_team_gesamt = $post['besuche_pc_team_gesamt'];
		$stmb->notarzteinsatze = join(",",$post['notarzteinsatze']);
		$stmb->kh_einweisungen = join(",",$post['einweisungen']);
		$stmb->anfahrtsweg_in_km = join(",",$post['anfahrtsweg_in_km']);
		$stmb->valid_from = date("Y-m-d H:i:s", strtotime($post['savedate']));
		$stmb->save();

		$result = $stmb->id;

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("formone");
		$cust->recordid = $result;
		$cust->user_id = $userid;
		$cust->save();

		if($stmb->id>0)
		{
			return true;
		}else{
			return false;
		}


	}
}

?>