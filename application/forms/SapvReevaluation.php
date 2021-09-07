<?php

require_once("Pms/Form.php");

class Application_Form_SapvReevaluation extends Pms_Form {

	public function insertSapvData($post, $ipid) {

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$epid = Pms_CommonData::getEpid($ipid);

		$Q = Doctrine_Query::create()
		->delete('SapvReevaluation')
		->where("ipid='" . $ipid . "'");
		$Q->execute();

		$ins = new SapvReevaluation();
		$ins->clientid = $clientid;
		$ins->ipid = $ipid;
		$ins->epid = $post['epid'];
		$ins->hi_company_name = $post['hi_company_name'];
		$ins->kassen_nr = $post['kassen_nr'];
		$ins->betriebsstatten_nr = $post['betriebsstatten_nr'];
		$ins->institutskennzeichen = $post['institutskennzeichen'];
		$ins->admisioncycle = $post['admisioncycle'];
		$ins->gender = $post['gender'];
		$ins->age = $post['age'];
		$ins->beginSapvFall = date("Y-m-d H:i:s", strtotime($post['beginSapvFall']));
		$ins->icddiagnosis = $post['icddiagnosis'];
		$ins->icdNDdiagnosis = $post['icdNDdiagnosis'];
		$ins->firstSapvMaxbe = $post['firstSapvMaxbe'];
		$ins->firstSapvMaxko = $post['firstSapvMaxko'];
		$ins->firstSapvMaxtv = $post['firstSapvMaxtv'];
		$ins->firstSapvMaxvv = $post['firstSapvMaxvv'];
		$ins->erstsapv = $post['erstsapv'];
		$ins->weideraufnahme = $post['weideraufnahme'];
		$ins->stathospiz = $post['stathospiz'];
		$ins->kranken = $post['kranken'];
		$ins->palliativ = $post['palliativ'];
		$ins->statpflege = $post['statpflege'];
		$ins->ambhospizdienst = $post['ambhospizdienst'];
		$ins->ambpflege = $post['ambpflege'];
		$ins->harzt = $post['harzt'];
		$ins->farzt = $post['farzt'];
		$ins->patange = $post['patange'];
		$ins->beratung = $post['beratung'];
		$ins->alone = $post['alone'];
		$ins->house_of_relatives = $post['house_of_relatives'];
		$ins->hospiz = $post['hospiz'];
		$ins->nursingfacility = $post['nursingfacility'];
		$ins->curentlivingother = $post['curentlivingother'];
		$ins->curentlivingmore = $post['curentlivingmore'];
		$ins->stagekeine = $post['stagekeine'];
		$ins->stageone = $post['stageone'];
		$ins->stagetwo = $post['stagetwo'];
		$ins->stagethree = $post['stagethree'];
		$ins->beantragt = $post['beantragt'];
		$ins->nbeantragt = $post['nbeantragt'];
		$ins->expectationkeine = $post['expectationkeine'];
		$ins->expectationsonstiges = $post['expectationsonstiges'];
		$ins->expectation = $post['expectation'];
		$ins->preabilitation = $post['preabilitation'];
		$ins->symptomrelief = $post['symptomrelief'];
		$ins->nohospital = $post['nohospital'];
		$ins->nolifeenxendingmeasures = $post['nolifeenxendingmeasures'];
		$ins->leftalone = $post['leftalone'];
		$ins->activeparticipation = $post['activeparticipation'];
		$ins->treatmentscopeexpectation = $post['treatmentscopeexpectation'];
		$ins->patientexpectationsapv = $post['patientexpectationsapv'];
		$ins->painsymptoms = $post['painsymptoms'];
		$ins->gastrointestinalsymptoms = $post['gastrointestinalsymptoms'];
		$ins->psychsymptoms = $post['psychsymptoms'];
		$ins->urogenitalsymptoms = $post['urogenitalsymptoms'];
		$ins->ulztumor = $post['ulztumor'];
		$ins->cardiacsymptoms = $post['cardiacsymptoms'];
		$ins->ethicalconflicts = $post['ethicalconflicts'];
		$ins->acutecrisispat = $post['acutecrisispat'];
		$ins->paliatifpflege = $post['paliatifpflege'];
		$ins->privatereferencesupport = $post['privatereferencesupport'];
		$ins->sociolegalproblems = $post['sociolegalproblems'];
		$ins->securelivingenvironment = $post['securelivingenvironment'];
		$ins->coordinationcare = $post['coordinationcare'];
		$ins->complexeventsmore = $post['complexeventsmore'];
		$ins->otherrequirements = $post['otherrequirements'];
		$ins->actualconductedbe = $post['actualconductedbe'];
		$ins->actualconductedko = $post['actualconductedko'];
		$ins->actualconductedtv = $post['actualconductedtv'];
		$ins->actualconductedvv = $post['actualconductedvv'];
		$ins->endSapvFall = date("Y-m-d H:i:s", strtotime($post['endSapvFall']));
		$ins->stabilization = $post['stabilization'];
		$ins->causaltherapy = $post['causaltherapy'];
		$ins->regulationexpiration = $post['regulationexpiration'];
		$ins->laying = $post['laying'];
		$ins->deceased = $post['deceased'];
		$ins->noneedsapv = $post['noneedsapv'];
		$ins->sapvterminationother = $post['sapvterminationother'];
		$ins->sapvstatusja = $post['sapvstatusja'];
		$ins->sapvstatusnein = $post['sapvstatusnein'];
		$ins->sapvstatuspartialy = $post['sapvstatuspartialy'];
		$ins->sapvstatusunknown = $post['sapvstatusunknown'];
		$ins->stagelastkeine = $post['stagelastkeine'];
		$ins->stagelastone = $post['stagelastone'];
		$ins->stagelasttwo = $post['stagelasttwo'];
		$ins->stagelastthree = $post['stagelastthree'];
		$ins->lastbeantragt = $post['lastbeantragt'];
		$ins->nlastbeantragt = $post['nlastbeantragt'];
		$ins->homedead = $post['homedead'];
		$ins->heimdead = $post['heimdead'];
		$ins->hospizdead = $post['hospizdead'];
		$ins->palliativdead = $post['palliativdead'];
		$ins->krankendead = $post['krankendead'];
		$ins->deathwishja = $post['deathwishja'];
		$ins->deathwishnein = $post['deathwishnein'];
		$ins->deathwishunknown = $post['deathwishunknown'];
		$ins->bereitschft = $post['bereitschft'];
		$ins->allhospitaldays = $post['allhospitaldays'];
		$ins->besuche = $post['besuche'];
		$ins->hospitalwithNotarz = $post['hospitalwithNotarz'];
		$ins->hospitalwithoutNotarz = $post['hospitalwithoutNotarz'];
		$ins->export_ready = $post['export_ready'];
		$ins->save();
	}

}

?>