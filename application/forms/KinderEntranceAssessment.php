<?php

require_once("Pms/Form.php");

class Application_Form_KinderEntranceAssessment extends Pms_Form{

	public function validate ( $post )
	{
		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$val = new Pms_Validation();

		if (!$val->isdate(end($post['completeddate'])))
		{
			$this->error_message['completed_date_error'] = $Tr->translate('completed_date_err');
		}

		if ($error == 0)
		{
			return true;
		}

		return false;
	}

	public function insertKinderEntranceAssessment($post, $ipid, $status = '0')
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		
		
		$entr = new KinderEntranceAssessment();
		$entr->ipid = $ipid;
		$entr->status = $status;
		if($status == "1"){
			$entr->completed_date = date("Y-m-d H:i", strtotime($post['completed_date']));
		}

		$entr->sapv_date = date("Y-m-d H:i", strtotime($post['sapv_date']));;
		$entr->first_form = $post['first_form'];
		$entr->living_situation = $post['living_situation'];
		$entr->stage_requested = $post['stage_requested'];
		$entr->stage_hardship = $post['stage_hardship'];
		$entr->custody = $post['custody'];
		$entr->evn = $post['evn'];
		$entr->therapy = $post['therapy'];
		$entr->pain_symptom = $post['pain_symptom'];
		$entr->pain_localisation = $post['pain_localisation'];
		$entr->pain_symptom_2 = $post['pain_symptom_2'];
		$entr->pain_localisation_2 = $post['pain_localisation_2'];
		$entr->pain_symptom_3 = $post['pain_symptom_3'];
		$entr->pain_localisation_3 = $post['pain_localisation_3'];
		$entr->pain_symptom_4 = $post['pain_symptom_4'];
		$entr->pain_localisation_4 = $post['pain_localisation_4'];
		$entr->distress = $post['distress'];
		$entr->hemoptysis = $post['hemoptysis'];
		$entr->airway_obstruction = $post['airway_obstruction'];
		$entr->respiratory_other_text = $post['respiratory_other_text'];
		$entr->respiratory_other = $post['respiratory_other'];
		$entr->disorder = $post['disorder'];
		$entr->intracranial_pressure = $post['intracranial_pressure'];
		$entr->restlessness = $post['restlessness'];
		$entr->spasticity = $post['spasticity'];
		$entr->cerebral_seizures = $post['cerebral_seizures'];
		$entr->developmental_disorder = $post['developmental_disorder'];
		$entr->autoaggression = $post['autoaggression'];
		$entr->insomnia = $post['insomnia'];
		$entr->depressive = $post['depressive'];
		$entr->neuroligical_other_text = $post['neuroligical_other_text'];
		$entr->neuroligical_other = $post['neuroligical_other'];
		$entr->wound_a_text = $post['wound_a_text'];
		$entr->wound_a = $post['wound_a'];
		$entr->anorexia = $post['anorexia'];
		$entr->mucositis = $post['mucositis'];
		$entr->dysphagia = $post['dysphagia'];
		$entr->throw_up = $post['throw_up'];
		$entr->hematemesis = $post['hematemesis'];
		$entr->icterus = $post['icterus'];
		$entr->ileus = $post['ileus'];
		$entr->ascites = $post['ascites'];
		$entr->diarrhea = $post['diarrhea'];
		$entr->constipation = $post['constipation'];
		$entr->gastrointestinal_other_text = $post['gastrointestinal_other_text'];
		$entr->gastrointestinal_other = $post['gastrointestinal_other'];
		$entr->urinary = $post['urinary'];
		$entr->dysuria = $post['dysuria'];
		$entr->hematuria = $post['hematuria'];
		$entr->voiding_dysfunction = $post['voiding_dysfunction'];
		$entr->urogenital_other_text = $post['urogenital_other_text'];
		$entr->urogenital_other = $post['urogenital_other'];
		$entr->last_hospital = $post['last_hospital'];
		$entr->last_hospital_period = $post['last_hospital_period'];
		$entr->adm_oral = $post['adm_oral'];
		$entr->adm_peg = $post['adm_peg'];
		$entr->adm_iv = $post['adm_iv'];
		$entr->adm_sc = $post['adm_sc'];
		$entr->adm_im = $post['adm_im'];
		$entr->adm_infusion = $post['adm_infusion'];
		$entr->adm_inhalation = $post['adm_inhalation'];
		$entr->adm_schmerzpumpe = $post['adm_schmerzpumpe'];
		$entr->adm_port = $post['adm_port'];
		$entr->adm_broviak = $post['adm_broviak'];
		
		$entr->required_antibiosis = $post['required_antibiosis'];
		$entr->required_kg = $post['required_kg'];
		$entr->required_enteral_nutrition = $post['required_enteral_nutrition'];
		$entr->required_o2_ventilation = $post['required_o2_ventilation'];
		$entr->required_parental_nutrition = $post['required_parental_nutrition'];
		
		$entr->required_pain_therapy = $post['required_pain_therapy'];
		$entr->required_antiepileptic_therapy = $post['required_antiepileptic_therapy'];
		$entr->required_muscle_relaxants = $post['required_muscle_relaxants'];
		
		$entr->required_free_a = $post['required_free_a'];
		$entr->required_free_a_text = $post['required_free_a_text'];
		$entr->required_free_b = $post['required_free_b'];
		$entr->required_free_b_text = $post['required_free_b_text'];
		$entr->required_free_c = $post['required_free_c'];
		$entr->required_free_c_text = $post['required_free_c_text'];
		
		$entr->administer_oxigen = $post['administer_oxigen'];
		$entr->colostomy = $post['colostomy'];
		$entr->urostomy = $post['urostomy'];
		$entr->tracheostomy = $post['tracheostomy'];
		$entr->warehousing = $post['warehousing'];
		$entr->wound_treatment = $post['wound_treatment'];
		$entr->case_history = $post['case_history'];
		$entr->social_environment = $post['social_environment'];
		$entr->psychosocial_interventions = $post['psychosocial_interventions'];
		$entr->other_text = $post['other_text'];
		
		
		$entr->KinderEntranceAssessmentSorrowfully->ipid = $ipid; 
		
		$sorrowfully = array("distress", "hemoptysis", "airway_obstruction", "respiratory_other", "disorder", "intracranial_pressure", 
				"restlessness", "spasticity", "cerebral_seizures", "developmental_disorder", "autoaggression", 
				"insomnia", "depressive", "wound_a", "anorexia", "mucositis", "dysphagia", "throw_up", 
				"hematemesis", "icterus", "ileus", "ascites", "diarrhea", "constipation", "gastrointestinal_other", 
				"urinary", "dysuria", "hematuria", "voiding_dysfunction", "urogenital_other");
		foreach($sorrowfully as $v){
			if(!isset($post['sorrowfully'][$v])){
				$entr->KinderEntranceAssessmentSorrowfully->$v = 0;
			}else{
				$entr->KinderEntranceAssessmentSorrowfully->$v = 1;
			}
		}
		
		
		$entr->save();
		
				
		

		if($post['completed'] == '0')
		{
			if ($mode != "live" && empty($post['btnnewassessment']))
			{
				$custcourse = new PatientCourse();
				$custcourse->ipid = $ipid;
				$custcourse->course_date = date("Y-m-d H:i:s", time());
				$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
				$comment = "Assessment Formular wurde angelegt";
				$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
				$custcourse->user_id = $userid;
				$custcourse->tabname = Pms_CommonData::aesEncrypt('kinder_entrance_assessment_new');
				$custcourse->save();
			}
			else if($mode != "live")
			{
				$custcourse = new PatientCourse();
				$custcourse->ipid = $ipid;
				$custcourse->course_date = date("Y-m-d H:i:s", time());
				$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
				$comment = "Neues Assessment wurde gestartet.";
				$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
				$custcourse->user_id = $userid;
				$custcourse->tabname = Pms_CommonData::aesEncrypt('kinder_entrance_assessment_new');
				$custcourse->save();
			}
		}
		else
		{

		}


		if ($entr->id > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	public function updateKinderEntranceAssessment($post, $ipid, $status = 0)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$last = $post['id'];
		
		if(!empty($last)){
			$entr = Doctrine::getTable('KinderEntranceAssessment')->findOneByIdAndIpid($last, $ipid);
			$entr->sapv_date = date("Y-m-d H:i", strtotime($post['sapv_date']));;
			$entr->first_form = $post['first_form'];

			// added last
			$entr->status = $status;
			if($status == "1"){
				$entr->completed_date = date("Y-m-d H:i", strtotime($post['completed_date']));
			}

			$entr->living_situation = $post['living_situation'];
			$entr->stage_requested = $post['stage_requested'];
			$entr->stage_hardship = $post['stage_hardship'];
			$entr->custody = $post['custody'];
			$entr->evn = $post['evn'];
			$entr->therapy = $post['therapy'];
			$entr->pain_symptom = $post['pain_symptom'];
			$entr->pain_localisation = $post['pain_localisation'];
			$entr->pain_symptom_2 = $post['pain_symptom_2'];
			$entr->pain_localisation_2 = $post['pain_localisation_2'];
			$entr->pain_symptom_3 = $post['pain_symptom_3'];
			$entr->pain_localisation_3 = $post['pain_localisation_3'];
			$entr->pain_symptom_4 = $post['pain_symptom_4'];
			$entr->pain_localisation_4 = $post['pain_localisation_4'];
			$entr->distress = $post['distress'];
			$entr->hemoptysis = $post['hemoptysis'];
			$entr->airway_obstruction = $post['airway_obstruction'];
			$entr->respiratory_other_text = $post['respiratory_other_text'];
			$entr->respiratory_other = $post['respiratory_other'];
			$entr->disorder = $post['disorder'];
			$entr->intracranial_pressure = $post['intracranial_pressure'];
			$entr->restlessness = $post['restlessness'];
			$entr->spasticity = $post['spasticity'];
			$entr->cerebral_seizures = $post['cerebral_seizures'];
			$entr->developmental_disorder = $post['developmental_disorder'];
			$entr->autoaggression = $post['autoaggression'];
			$entr->insomnia = $post['insomnia'];
			$entr->depressive = $post['depressive'];
			$entr->neuroligical_other_text = $post['neuroligical_other_text'];
			$entr->neuroligical_other = $post['neuroligical_other'];
			$entr->wound_a_text = $post['wound_a_text'];
			$entr->wound_a = $post['wound_a'];
			$entr->anorexia = $post['anorexia'];
			$entr->mucositis = $post['mucositis'];
			$entr->dysphagia = $post['dysphagia'];
			$entr->throw_up = $post['throw_up'];
			$entr->hematemesis = $post['hematemesis'];
			$entr->icterus = $post['icterus'];
			$entr->ileus = $post['ileus'];
			$entr->ascites = $post['ascites'];
			$entr->diarrhea = $post['diarrhea'];
			$entr->constipation = $post['constipation'];
			$entr->gastrointestinal_other_text = $post['gastrointestinal_other_text'];
			$entr->gastrointestinal_other = $post['gastrointestinal_other'];
			$entr->urinary = $post['urinary'];
			$entr->dysuria = $post['dysuria'];
			$entr->hematuria = $post['hematuria'];
			$entr->voiding_dysfunction = $post['voiding_dysfunction'];
			$entr->urogenital_other_text = $post['urogenital_other_text'];
			$entr->urogenital_other = $post['urogenital_other'];
			$entr->last_hospital = $post['last_hospital'];
			$entr->last_hospital_period = $post['last_hospital_period'];
			$entr->adm_oral = $post['adm_oral'];
			$entr->adm_peg = $post['adm_peg'];
			$entr->adm_iv = $post['adm_iv'];
			$entr->adm_sc = $post['adm_sc'];
			$entr->adm_im = $post['adm_im'];
			$entr->adm_infusion = $post['adm_infusion'];
			$entr->adm_inhalation = $post['adm_inhalation'];
			$entr->adm_schmerzpumpe = $post['adm_schmerzpumpe'];
			$entr->adm_port = $post['adm_port'];
			$entr->adm_broviak = $post['adm_broviak'];
			
			$entr->required_antibiosis = $post['required_antibiosis'];
			$entr->required_kg = $post['required_kg'];
			$entr->required_enteral_nutrition = $post['required_enteral_nutrition'];
			$entr->required_o2_ventilation = $post['required_o2_ventilation'];
			$entr->required_parental_nutrition = $post['required_parental_nutrition'];
			
			$entr->required_pain_therapy = $post['required_pain_therapy'];
			$entr->required_antiepileptic_therapy = $post['required_antiepileptic_therapy'];
			$entr->required_muscle_relaxants = $post['required_muscle_relaxants'];
			
			$entr->required_free_a = $post['required_free_a'];
			$entr->required_free_a_text = $post['required_free_a_text'];
			$entr->required_free_b = $post['required_free_b'];
			$entr->required_free_b_text = $post['required_free_b_text'];
			$entr->required_free_c = $post['required_free_c'];
			$entr->required_free_c_text = $post['required_free_c_text'];
			
			$entr->administer_oxigen = $post['administer_oxigen'];
			$entr->colostomy = $post['colostomy'];
			$entr->urostomy = $post['urostomy'];
			$entr->tracheostomy = $post['tracheostomy'];
			$entr->warehousing = $post['warehousing'];
			$entr->wound_treatment = $post['wound_treatment'];
			$entr->case_history = $post['case_history'];
			$entr->social_environment = $post['social_environment'];
			$entr->psychosocial_interventions = $post['psychosocial_interventions'];
			$entr->other_text = $post['other_text'];
			
			$entr->KinderEntranceAssessmentSorrowfully->ipid = $ipid;
			
			$sorrowfully = array("distress", "hemoptysis", "airway_obstruction", "respiratory_other", "disorder", "intracranial_pressure",
					"restlessness", "spasticity", "cerebral_seizures", "developmental_disorder", "autoaggression",
					"insomnia", "depressive", "neuroligical_other", "wound_a", "anorexia", "mucositis", "dysphagia", "throw_up",
					"hematemesis", "icterus", "ileus", "ascites", "diarrhea", "constipation", "gastrointestinal_other",
					"urinary", "dysuria", "hematuria", "voiding_dysfunction", "urogenital_other");
			foreach($sorrowfully as $v){
				if(!isset($post['sorrowfully'][$v])){
					$entr->KinderEntranceAssessmentSorrowfully->$v = 0;
				}else{
					$entr->KinderEntranceAssessmentSorrowfully->$v = 1;
				}
			}
			
			$entr->save();
		}
	}

	public function set_completed_entrance_assessment($post, $ipid, $status = 0)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$last = $post['id'];


		if(!empty($last)){
			$entr = Doctrine::getTable('KinderEntranceAssessment')->findOneByIdAndIpid($last, $ipid);
			$entr->status = $status;
			if($status == "1"){
				$entr->completed_date = date("Y-m-d H:i", strtotime($post['completed_date']));
			}

			$entr->save();

		}
	}
}
?>