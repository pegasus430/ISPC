<?php

	require_once("Pms/Form.php");

	class Application_Form_HospizQuestionnaire extends Pms_Form {

		public function validate($post)
		{
			
		}

		public function insertHospizQuestionnaire($post, $ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			
			$ins = new HospizQuestionnaire();
			$ins->ipid = $ipid;

			$ins->initial_application = $post['initial_application'];
			$ins->renew_application = $post['renew_application'];

			if(!empty($post['application_date']))
			{
				$ins->application_date = date('Y-m-d H:i:s', strtotime($post['application_date']));
			}
			else
			{
				$ins->application_date = date('Y-m-d H:i:s');
			}

			if(!empty($post['admission_date']))
			{
				$ins->admission_date = date('Y-m-d H:i:s', strtotime($post['admission_date']));
			}
			else
			{
				$ins->admission_date = date('Y-m-d H:i:s');
			}

			$ins->diagnostic_details = htmlspecialchars($post['diagnostic_details']);
			$ins->metastasen_details = htmlspecialchars($post['metastasen_details']);
			$ins->metastasen_options = $post['metastasen_options'];
			$ins->findings_az = htmlspecialchars($post['findings_az']);
			$ins->findings_ez = htmlspecialchars($post['findings_ez']);
			$ins->findings_height_weight = htmlspecialchars($post['findings_height_weight']);
			$ins->findings_skin_condition = htmlspecialchars($post['findings_skin_condition']);
			$ins->mental_disorders = htmlspecialchars($post['mental_disorders']);
			$ins->operativ = $post['operativ'];
			$ins->operativ_details = htmlspecialchars($post['operativ_details']);
			$ins->chemo = $post['chemo'];
			$ins->chemo_details = htmlspecialchars($post['chemo_details']);
			$ins->radiatio = $post['radiatio'];
			$ins->radiatio_details = htmlspecialchars($post['radiatio_details']);
			$ins->symptom_schmerzen = $post['symptom_schmerzen'];
			$ins->symptom_dyspnoe = $post['symptom_dyspnoe'];
			$ins->symptom_erbrechen = $post['symptom_erbrechen'];
			$ins->symptom_ubelkeit = $post['symptom_ubelkeit'];
			$ins->symptom_obstipation = $post['symptom_obstipation'];
			$ins->symptom_durchfalle = $post['symptom_durchfalle'];
			$ins->symptom_depression = $post['symptom_depression'];
			$ins->symptom_angste = $post['symptom_angste'];
			$ins->symptom_haut = $post['symptom_haut'];
			$ins->symptom_control = htmlspecialchars($post['symptom_control']);
			$ins->med_oral = $post['med_oral'];
			$ins->med_iv = $post['med_iv'];
			$ins->med_im = $post['med_im'];
			$ins->med_sc = $post['med_sc'];
			$ins->med_infusion = $post['med_infusion'];
			$ins->med_nebuliser = $post['med_nebuliser'];
			$ins->med_inhalation = $post['med_inhalation'];
			$ins->med_details = htmlspecialchars($post['med_details']);
			$ins->kg = $post['kg'];
			$ins->lymphdrainage = $post['lymphdrainage'];
			$ins->chemotherapie = $post['chemotherapie'];
			$ins->radiatio_needed = $post['radiatio_needed'];
			$ins->atemtherapie = $post['atemtherapie'];
			$ins->sauerstoffgabe = $post['sauerstoffgabe'];
			$ins->bzrr_control = $post['bzrr_control'];
			$ins->urostoma = $post['urostoma'];
			$ins->anuspraeter = $post['anuspraeter'];
			$ins->tracheostoma = $post['tracheostoma'];
			$ins->wound_treatment = $post['wound_treatment'];
			$ins->family_social_environment = htmlspecialchars($post['family_social_environment']);
			$ins->required_psychosocial_support = htmlspecialchars($post['required_psychosocial_support']);
			$ins->user = $post['user'];
			$ins->isdelete = '0';

			//ISPC-2647 Lore 05.08.2020
			$ins->hospiz_nord = $post['hospiz_nord'];
			
			$ins->save();


			//added new
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("F");
			$comment = "Hospizbedarfsbogen Formular wurde angelegt.";
			//ISPC-2647 Lore 05.08.2020
			$tabname = Pms_CommonData::aesEncrypt(addslashes('hospiz_questionnaire'));
			if($post['hospiz_nord'] == 1){
			    $comment = "Hospizbedarfsbogen Nord Formular wurde angelegt.";
			    $tabname = Pms_CommonData::aesEncrypt(addslashes('hospiz_questionnaire_nord'));
			}
			//.
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->recordid = $ins->id;
			//$custcourse->tabname = Pms_CommonData::aesEncrypt(addslashes('hospiz_questionnaire'));
			$custcourse->tabname = $tabname;         //ISPC-2647 Lore 05.08.2020
			
			$custcourse->save();

			if($ins->id > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function updateHospizQuestionnaire($post, $qid, $ipid, $userid = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			
			$upd = Doctrine::getTable('HospizQuestionnaire')->findOneByIdAndIpid($qid, $ipid);

			$upd->initial_application = $post['initial_application'];
			$upd->renew_application = $post['renew_application'];


			if(!empty($post['application_date']))
			{
				$upd->application_date = date('Y-m-d H:i:s', strtotime($post['application_date']));
			}
			else
			{
				$upd->application_date = date('Y-m-d H:i:s');
			}

			if(!empty($post['admission_date']))
			{
				$upd->admission_date = date('Y-m-d H:i:s', strtotime($post['admission_date']));
			}
			else
			{
				$upd->admission_date = date('Y-m-d H:i:s');
			}

			$upd->diagnostic_details = htmlspecialchars($post['diagnostic_details']);
			$upd->metastasen_details = htmlspecialchars($post['metastasen_details']);
			$upd->metastasen_options = $post['metastasen_options'];
			$upd->findings_az = htmlspecialchars($post['findings_az']);
			$upd->findings_ez = htmlspecialchars($post['findings_ez']);
			$upd->findings_height_weight = htmlspecialchars($post['findings_height_weight']);
			$upd->findings_skin_condition = htmlspecialchars($post['findings_skin_condition']);
			$upd->mental_disorders = htmlspecialchars($post['mental_disorders']);
			$upd->operativ = $post['operativ'];
			$upd->operativ_details = htmlspecialchars($post['operativ_details']);
			$upd->chemo = $post['chemo'];
			$upd->chemo_details = htmlspecialchars($post['chemo_details']);
			$upd->radiatio = $post['radiatio'];
			$upd->radiatio_details = htmlspecialchars($post['radiatio_details']);
			$upd->symptom_schmerzen = $post['symptom_schmerzen'];
			$upd->symptom_dyspnoe = $post['symptom_dyspnoe'];
			$upd->symptom_erbrechen = $post['symptom_erbrechen'];
			$upd->symptom_ubelkeit = $post['symptom_ubelkeit'];
			$upd->symptom_obstipation = $post['symptom_obstipation'];
			$upd->symptom_durchfalle = $post['symptom_durchfalle'];
			$upd->symptom_depression = $post['symptom_depression'];
			$upd->symptom_angste = $post['symptom_angste'];
			$upd->symptom_haut = $post['symptom_haut'];
			$upd->symptom_control = htmlspecialchars($post['symptom_control']);
			$upd->med_oral = $post['med_oral'];
			$upd->med_iv = $post['med_iv'];
			$upd->med_im = $post['med_im'];
			$upd->med_sc = $post['med_sc'];
			$upd->med_infusion = $post['med_infusion'];
			$upd->med_nebuliser = $post['med_nebuliser'];
			$upd->med_inhalation = $post['med_inhalation'];
			$upd->med_details = htmlspecialchars($post['med_details']);
			$upd->kg = $post['kg'];
			$upd->lymphdrainage = $post['lymphdrainage'];
			$upd->chemotherapie = $post['chemotherapie'];
			$upd->radiatio_needed = $post['radiatio_needed'];
			$upd->atemtherapie = $post['atemtherapie'];
			$upd->sauerstoffgabe = $post['sauerstoffgabe'];
			$upd->bzrr_control = $post['bzrr_control'];
			$upd->urostoma = $post['urostoma'];
			$upd->anuspraeter = $post['anuspraeter'];
			$upd->tracheostoma = $post['tracheostoma'];
			$upd->wound_treatment = $post['wound_treatment'];
			$upd->family_social_environment = htmlspecialchars($post['family_social_environment']);
			$upd->required_psychosocial_support = htmlspecialchars($post['required_psychosocial_support']);
			$upd->user = $post['user'];
			$upd->isdelete = '0';
			
			//ISPC-2647 Lore 05.08.2020
			$upd->hospiz_nord = $post['hospiz_nord'];
			
			$upd->save();

			//formular editat
			$custcourse = new PatientCourse();
			$custcourse->ipid = $ipid;
			$custcourse->course_date = date("Y-m-d H:i:s", time());
			$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
			$comment = "Hospizbedarfsbogen wurde editiert";
			//ISPC-2647 Lore 05.08.2020
			$tabname = Pms_CommonData::aesEncrypt(addslashes('hospiz_questionnaire'));
			if($post['hospiz_nord'] == 1){
			    $comment = "Hospizbedarfsbogen Nord Formular wurde editiert.";
			    $tabname = Pms_CommonData::aesEncrypt(addslashes('hospiz_questionnaire_nord'));
			}
			//.
			$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
			$custcourse->user_id = $userid;
			$custcourse->recordid = $qid;
			//$custcourse->tabname = Pms_CommonData::aesEncrypt(addslashes('hospiz_questionnaire'));
			$custcourse->tabname = $tabname;         //ISPC-2647 Lore 05.08.2020
			
			$custcourse->save();

			return true;
		}

	}

?>