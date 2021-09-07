<?php

require_once("Pms/Form.php");

class Application_Form_EntranceAssessment extends Pms_Form{

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

	public function insertEntranceAssessment($post, $ipid, $status = '0')
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;

		$entr = new EntranceAssessment();
		$entr->ipid = $ipid;
		$entr->status = $status;
		if($status == "1"){
			$entr->completed_date = date("Y-m-d H:i", strtotime($post['completed_date']));
		}

		$entr->sapv_date = date("Y-m-d H:i", strtotime($post['sapv_date']));;
		$entr->first_form = $post['first_form'];

		$entr->living_with_child_age = $post['living_with_child_age'];
		$entr->living_other = $post['living_other'];
		$entr->living_other_text = $post['living_other_text'];

		$entr->stage_requested = $post['stage_requested'];
		$entr->stage_hardship = $post['stage_hardship'];

		$entr->cared_for = $post['cared_for'];
		$entr->cared_for_text = $post['cared_for_text'];
		$entr->tumor_brain = $post['tumor_brain'];
		$entr->tumor_lung = $post['tumor_lung'];
		$entr->tumor_liver = $post['tumor_liver'];
		$entr->tumor_bone = $post['tumor_bone'];
		$entr->tumor_other = $post['tumor_other'];
		$entr->tumor_other_text = $post['tumor_other_text'];
		$entr->therapy_op = $post['therapy_op'];
		$entr->therapy_radio = $post['therapy_radio'];
		$entr->therapy_chemo = $post['therapy_chemo'];
		$entr->therapy_other = $post['therapy_other'];
		$entr->therapy_other_text = $post['therapy_other_text'];
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
		$entr->nyha = $post['nyha'];
		$entr->respiratory_other_text = $post['respiratory_other_text'];
		$entr->respiratory_other = $post['respiratory_other'];
		$entr->disorder = $post['disorder'];
		$entr->intracranial_pressure = $post['intracranial_pressure'];
		$entr->myoclonic = $post['myoclonic'];
		$entr->muscle_cramps = $post['muscle_cramps'];
		$entr->depression = $post['depression'];
		$entr->psychotic_syndromes = $post['psychotic_syndromes'];
		$entr->neuroligical_other_text = $post['neuroligical_other_text'];
		$entr->neuroligical_other = $post['neuroligical_other'];
		$entr->wound_tumor_a_text = $post['wound_tumor_a_text'];
		$entr->wound_tumor_a = $post['wound_tumor_a'];
		$entr->wound_tumor_b_text = $post['wound_tumor_b_text'];
		$entr->wound_tumor_b = $post['wound_tumor_b'];
		$entr->wound_tumor_c_text = $post['wound_tumor_c_text'];
		$entr->wound_tumor_c = $post['wound_tumor_c'];
		$entr->anorexia = $post['anorexia'];
		$entr->mucositis = $post['mucositis'];
		$entr->dysphagia = $post['dysphagia'];
		$entr->throw_up = $post['throw_up'];
		$entr->hematemesis = $post['hematemesis'];
		$entr->icterus = $post['icterus'];
		$entr->ileus = $post['ileus'];
		$entr->ascites = $post['ascites'];
		$entr->diarrhea = $post['diarrhea'];
		$entr->fistulas = $post['fistulas'];
		$entr->gastrointestinal_other_text = $post['gastrointestinal_other_text'];
		$entr->gastrointestinal_other = $post['gastrointestinal_other'];
		$entr->urinary = $post['urinary'];
		$entr->dysuria = $post['dysuria'];
		$entr->blasentenesmen = $post['blasentenesmen'];
		$entr->hematuria = $post['hematuria'];
		$entr->vaginal_bleeding = $post['vaginal_bleeding'];
		$entr->urogenital_other_text = $post['urogenital_other_text'];
		$entr->urogenital_other = $post['urogenital_other'];
		$entr->last_hospital = $post['last_hospital'];
		$entr->last_hospital_period = $post['last_hospital_period'];
		$entr->adm_oral = $post['adm_oral'];
		$entr->adm_iv = $post['adm_iv'];
		$entr->adm_sc = $post['adm_sc'];
		$entr->adm_im = $post['adm_im'];
		$entr->adm_transdermal = $post['adm_transdermal'];
		$entr->adm_infusion = $post['adm_infusion'];
		$entr->adm_inhalation = $post['adm_inhalation'];
		$entr->adm_schmerzpumpe = $post['adm_schmerzpumpe'];
		$entr->adm_port = $post['adm_port'];
		$entr->adm_nasogastric_tube = $post['adm_nasogastric_tube']; //ISPC - 2289
		$entr->required_chemo = $post['required_chemo'];
		$entr->required_antibiosis = $post['required_antibiosis'];
		$entr->required_kg = $post['required_kg'];
		$entr->required_enteral_nutrition = $post['required_enteral_nutrition'];
		$entr->required_radiatio = $post['required_radiatio'];
		$entr->required_lymphatic = $post['required_lymphatic'];
		$entr->required_o2_ventilation = $post['required_o2_ventilation'];
		$entr->required_parental_nutrition = $post['required_parental_nutrition'];
		$entr->administer_oxigen = $post['administer_oxigen'];
		$entr->colostomy = $post['colostomy'];
		$entr->urostomy = $post['urostomy'];
		$entr->tracheostomy = $post['tracheostomy'];
		$entr->warehousing = $post['warehousing'];
		$entr->wound_treatment = $post['wound_treatment'];
		$entr->other_text = $post['other_text'];
		$entr->psychosocial_care = $post['psychosocial_care'];
		$entr->social_environment = $post['social_environment'];
		$entr->psychosocial_interventions = $post['psychosocial_interventions'];
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
				$custcourse->tabname = Pms_CommonData::aesEncrypt('new_kvno_assesment');
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
				$custcourse->tabname = Pms_CommonData::aesEncrypt('new_kvno_assesment');
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


	public function updateEntranceAssessment($post, $ipid, $status = 0)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$last = $post['id'];


		if(!empty($last)){
			$entr = Doctrine::getTable('EntranceAssessment')->findOneByIdAndIpid($last, $ipid);
			$entr->sapv_date = date("Y-m-d H:i", strtotime($post['sapv_date']));;
			$entr->first_form = $post['first_form'];

			// added last
			$entr->status = $status;
			if($status == "1"){
				$entr->completed_date = date("Y-m-d H:i", strtotime($post['completed_date']));
			}


			$entr->living_with_child_age = $post['living_with_child_age'];
			$entr->living_other = $post['living_other'];
			$entr->living_other_text = $post['living_other_text'];

			$entr->stage_requested = $post['stage_requested'];
			$entr->stage_hardship = $post['stage_hardship'];


			$entr->cared_for = $post['cared_for'];
			$entr->cared_for_text = $post['cared_for_text'];
			$entr->tumor_brain = $post['tumor_brain'];
			$entr->tumor_lung = $post['tumor_lung'];
			$entr->tumor_liver = $post['tumor_liver'];
			$entr->tumor_bone = $post['tumor_bone'];
			$entr->tumor_other = $post['tumor_other'];
			$entr->tumor_other_text = $post['tumor_other_text'];
			$entr->therapy_op = $post['therapy_op'];
			$entr->therapy_radio = $post['therapy_radio'];
			$entr->therapy_chemo = $post['therapy_chemo'];
			$entr->therapy_other = $post['therapy_other'];
			$entr->therapy_other_text = $post['therapy_other_text'];
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
			$entr->nyha = $post['nyha'];
			$entr->respiratory_other_text = $post['respiratory_other_text'];
			$entr->respiratory_other = $post['respiratory_other'];
			$entr->disorder = $post['disorder'];
			$entr->intracranial_pressure = $post['intracranial_pressure'];
			$entr->myoclonic = $post['myoclonic'];
			$entr->muscle_cramps = $post['muscle_cramps'];
			$entr->depression = $post['depression'];
			$entr->psychotic_syndromes = $post['psychotic_syndromes'];
			$entr->neuroligical_other_text = $post['neuroligical_other_text'];
			$entr->neuroligical_other = $post['neuroligical_other'];
			$entr->wound_tumor_a_text = $post['wound_tumor_a_text'];
			$entr->wound_tumor_a = $post['wound_tumor_a'];
			$entr->wound_tumor_b_text = $post['wound_tumor_b_text'];
			$entr->wound_tumor_b = $post['wound_tumor_b'];
			$entr->wound_tumor_c_text = $post['wound_tumor_c_text'];
			$entr->wound_tumor_c = $post['wound_tumor_c'];
			$entr->anorexia = $post['anorexia'];
			$entr->mucositis = $post['mucositis'];
			$entr->dysphagia = $post['dysphagia'];
			$entr->throw_up = $post['throw_up'];
			$entr->hematemesis = $post['hematemesis'];
			$entr->icterus = $post['icterus'];
			$entr->ileus = $post['ileus'];
			$entr->ascites = $post['ascites'];
			$entr->diarrhea = $post['diarrhea'];
			$entr->fistulas = $post['fistulas'];
			$entr->gastrointestinal_other_text = $post['gastrointestinal_other_text'];
			$entr->gastrointestinal_other = $post['gastrointestinal_other'];
			$entr->urinary = $post['urinary'];
			$entr->dysuria = $post['dysuria'];
			$entr->blasentenesmen = $post['blasentenesmen'];
			$entr->hematuria = $post['hematuria'];
			$entr->vaginal_bleeding = $post['vaginal_bleeding'];
			$entr->urogenital_other_text = $post['urogenital_other_text'];
			$entr->urogenital_other = $post['urogenital_other'];
			$entr->last_hospital = $post['last_hospital'];
			$entr->last_hospital_period = $post['last_hospital_period'];
			$entr->adm_oral = $post['adm_oral'];
			$entr->adm_iv = $post['adm_iv'];
			$entr->adm_sc = $post['adm_sc'];
			$entr->adm_im = $post['adm_im'];
			$entr->adm_transdermal = $post['adm_transdermal'];// Maria:: Migration ISPC to CISPC 08.08.2020	
			$entr->adm_infusion = $post['adm_infusion'];
			$entr->adm_inhalation = $post['adm_inhalation'];
			$entr->adm_schmerzpumpe = $post['adm_schmerzpumpe'];
			$entr->adm_port = $post['adm_port'];
			$entr->adm_nasogastric_tube = $post['adm_nasogastric_tube']; //ISPC - 2289
			$entr->required_chemo = $post['required_chemo'];
			$entr->required_antibiosis = $post['required_antibiosis'];
			$entr->required_kg = $post['required_kg'];
			$entr->required_enteral_nutrition = $post['required_enteral_nutrition'];
			$entr->required_radiatio = $post['required_radiatio'];
			$entr->required_lymphatic = $post['required_lymphatic'];
			$entr->required_o2_ventilation = $post['required_o2_ventilation'];
			$entr->required_parental_nutrition = $post['required_parental_nutrition'];
			$entr->administer_oxigen = $post['administer_oxigen'];
			$entr->colostomy = $post['colostomy'];
			$entr->urostomy = $post['urostomy'];
			$entr->tracheostomy = $post['tracheostomy'];
			$entr->warehousing = $post['warehousing'];
			$entr->wound_treatment = $post['wound_treatment'];
			$entr->other_text = $post['other_text'];
			$entr->psychosocial_care = $post['psychosocial_care'];
			$entr->social_environment = $post['social_environment'];
			$entr->psychosocial_interventions = $post['psychosocial_interventions'];
			$entr->save();
		}
	}

	public function set_completed_entrance_assessment($post, $ipid, $status = 0)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$last = $post['id'];


		if(!empty($last)){
			$entr = Doctrine::getTable('EntranceAssessment')->findOneByIdAndIpid($last, $ipid);
			$entr->status = $status;
			if($status == "1"){
				$entr->completed_date = date("Y-m-d H:i", strtotime($post['completed_date']));
			}

			$entr->save();

		}
	}
}
?>