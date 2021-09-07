<?php

require_once("Pms/Form.php");

class Application_Form_Stammblatt extends Pms_Form
{
	public function insertStamblat($post)
	{
		//print_r($post);
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$Qur = Doctrine_Query::create()
		->delete('Stammblatt')
		->where("ipid='".$ipid."'");
		$Qur->execute();


		$stmb = new Stammblatt();
		$stmb->ipid = $ipid;
		$stmb->geschlecht = $post['geschlecht'];
		$stmb->familienstand = $post['familienstand'];
		$stmb->staatszugehorigkeit = $post['staatszugehorigkeit'];
		$stmb->staatszugehorigkeit_text = $post['staatszugehorigkeit_text'];
		$stmb->religionszugehorigkeit = $post['religionszugehorigkeit'];

		$stmb->relevante_diagnose = $post['relevante_Input'];
		$stmb->relevante_nebendiagnosen = $post['relevanteNebendia_Input'];

		$stmb->diagnosegruppe = $post['diagnosegruppe'];
		$stmb->primartumor = $post['primartumor'];
		$stmb->primartumor_text = $post['primartumor_other'];
		$stmb->metastasen = join(",",$post['metastasen']);
		$stmb->metastasen_text = $post['metastasen_other'];
		$stmb->nicht_tumor_erkrankungen = $post['nicht_tumor_erkrankungen'];
		$stmb->sapv_relevante_symptome = join(",",$post['sapv_relevante_symptome']);
		$stmb->sapv_verordnung_durch = $post['sapv_verordnung_durch'];
		$stmb->als = $post['als'];
		$stmb->vom = $post['vom'];
		$stmb->bis = $post['bis'];
		$stmb->schmerzen = $post['schmerzen'];
		$stmb->neuropat_schmerzen = $post['neuropat_schmerzen'];
		$stmb->viszerale_schmerzen = $post['viszerale_schmerzen'];
		$stmb->atemnot = $post['atemnot'];
		$stmb->reizhusten = $post['reizhusten'];
		$stmb->verschleimung = $post['verschleimung'];
		$stmb->aszites = $post['aszites'];
		$stmb->ubelkeit_erbrechen = $post['ubelkeit_erbrechen'];
		$stmb->bluterbrechen = $post['bluterbrechen'];
		$stmb->durchfall = $post['durchfall'];
		$stmb->obstipation = $post['obstipation'];
		$stmb->soor = $post['soor'];
		$stmb->schluckstorungen = $post['schluckstorungen'];
		$stmb->angst = $post['angst'];
		$stmb->depression = $post['depression'];
		$stmb->unruhe = $post['unruhe'];
		$stmb->desorientierung = $post['desorientierung'];
		$stmb->krampfanfalle = $post['krampfanfalle'];
		$stmb->lahmungen = $post['lahmungen'];
		$stmb->gangunsicherheit = $post['gangunsicherheit'];
		$stmb->schwindel = $post['schwindel'];
		$stmb->sensibilitatsstogg = $post['sensib'];
		$stmb->decubitus = $post['decubitus'];
		$stmb->exulcerationen = $post['exulcerationen'];
		$stmb->lymph_odeme = $post['lympth_odeme'];
		$stmb->harnverhalt = $post['harnverhalt'];
		$stmb->lebensqualitat = $post['lebensqualitat'];
		$stmb->sprachstorung = $post['sprachstorung'];
		$stmb->organisationsprob = $post['organisationsprob'];
		$stmb->finanz_probleme = $post['finanz_problem'];
		$stmb->fatique = $post['fatique'];
		$stmb->juckreiz = $post['juckreiz'];
		$stmb->kachexie = $post['kachexie'];
		$stmb->wohnsituation = join(",",$post['wohnsituation']);
		$stmb->pflegeversicherung = $post['pflegeversicherung'];
		$stmb->aktuelle_pflegerische_situation = $post['aktuelle_pflegerische_situation'];
		$stmb->hauptprobleme = $post['hauptprobleme'];
		$stmb->patientenwunsch = $post['patientenwunsch'];
		$stmb->wunschort_des_sterbens = $post['wunschort_des'];
		$stmb->sapv_ziel = $post['sapv_ziel'];
		$stmb->sapv_ziel_text = $post['sapv_ziel_other'];
		$stmb->vigilanz = $post['vigilanz'];
		$stmb->ernahrung_one = join(",",$post['ernahrung_one']);
		$stmb->orientierung = join(",",$post['orientierung']);
		$stmb->ernahrung_two = join(",",$post['ernahrung_two']);
		$stmb->ausscheidung = join(",",$post['ausscheidung']);
		$stmb->mobilitat = join(",",$post['mobilitat']);
		$stmb->kunstliche_ausgange = join(",",$post['kunstliche_ausgange']);
		$stmb->apparative_palliativmedizinische = join(",",$post['apparative_palliativ']);
		$stmb->apparative_palliativmedizinische_text = $post['apparative_palliativ_other'];
		$stmb->patientenverfugung = $post['patientenverfugung'];
		$stmb->patientenverfugung_vom = $post['vom_text'];
		$stmb->gesetzliche_vertretung = $post['gesetzliche_vertretung'];
		$stmb->gesetzliche_vertretung_text = $post['gesetzliche_vertretung_text'];

		$stmb->save();

	}
}

?>