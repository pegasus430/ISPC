<?php
require_once("Pms/Form.php");
class Application_Form_BreRecordingReport extends Pms_Form
{

	public function insert_data ( $post )
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$done_date = date('Y-m-d H:i:s', time());

		$insert = new BreRecordingReport();
		$insert->ipid = $ipid;
		$insert->curent_medical_history = $post['curent_medical_history'];
		$insert->social_history = $post['social_history'];
		$insert->priority_items_1 = $post['priority_items'][0];
		$insert->priority_items_2 = $post['priority_items'][1];
		$insert->priority_items_3 = $post['priority_items'][2];
		$insert->priority_items_4 = $post['priority_items'][3];
		$insert->priority_items_5 = $post['priority_items'][4];
		$insert->priority_items_6 = $post['priority_items'][5];
		$insert->living_will_option = $post['living_will_option'];
		$insert->attorney_power_option = $post['attorney_power_option'];
		$insert->contact_person_id = $post['contact_person_id'];
		$insert->contact_person_name = $post['contact_person_name'];
		$insert->contact_person_lastname = $post['contact_person_lastname'];
		$insert->resuscitation = $post['resuscitation'];
		$insert->causal_therapy = $post['causal_therapy'];
		$insert->other_therapy = $post['other_therapy'];
		$insert->other_therapy_more = $post['other_therapy_more'];

		$insert->body_condition_inconspicuous = $post['body_condition']['inconspicuous'];
		$insert->body_condition_red_az = $post['body_condition']['red_az'];
		$insert->body_condition_kachekt = $post['body_condition']['kachekt'];
		$insert->body_condition_fear = $post['body_condition']['fear'];
		$insert->body_condition_constipation = $post['body_condition']['constipation'];
		$insert->body_condition_morning_sickness = $post['body_condition']['morning_sickness'];
		$insert->body_condition_more = $post['body_condition_more'];

		$insert->consciousness_inconspicuous = $post['consciousness']['inconspicuous'];
		$insert->consciousness_tarnished = $post['consciousness']['tarnished'];
		$insert->consciousness_restless = $post['consciousness']['restless'];
		$insert->consciousness_stupor = $post['consciousness']['stupor'];
		$insert->consciousness_coma = $post['consciousness']['coma'];
		$insert->consciousness_slowed = $post['consciousness']['slowed'];
		$insert->consciousness_disorientation = $post['consciousness']['disorientation'];
		$insert->consciousness_more = $post['consciousness']['more'];
		$insert->consciousness_more_text = $post['consciousness']['more_text'];

		$insert->skin_inconspicuous = $post['skin']['inconspicuous'];
		$insert->skin_pale = $post['skin']['pale'];
		$insert->skin_cyanotic = $post['skin']['cyanotic'];
		$insert->skin_ikter = $post['skin']['ikter'];
		$insert->skin_dry = $post['skin']['dry'];
		$insert->skin_hemoragy = $post['skin']['hemoragy'];
		$insert->skin_more_text = $post['skin']['more_text'];

		$insert->edema_uleg = $post['edema']['u_leg'];
		$insert->edema_oleg = $post['edema']['o_leg'];
		$insert->edema_hands = $post['edema']['hands'];
		$insert->edema_eyelids = $post['edema']['eyelids'];
		$insert->edema_face = $post['edema']['face'];
		$insert->edema_moving = $post['edema']['moving'];
		$insert->edema_more_text = $post['edema']['more_text'];

		$insert->physical_exam_skin_mucous_membran = $post['physical_exam']['skin_mucous_membran'];
		$insert->physical_exam_skin_mucous_membran_more = $post['physical_exam']['skin_mucous_membran_more'];
		$insert->physical_exam_heart = $post['physical_exam']['heart'];
		$insert->physical_exam_heart_more = $post['physical_exam']['heart_more'];
		$insert->physical_exam_lungs = $post['physical_exam']['lungs'];
		$insert->physical_exam_lungs_more = $post['physical_exam']['lungs_more'];
		$insert->physical_exam_abdomen = $post['physical_exam']['abdomen'];
		$insert->physical_exam_abdomen_more = $post['physical_exam']['abdomen_more'];
		$insert->physical_exam_musculo_skeletal = $post['physical_exam']['musculo_skeletal'];
		$insert->physical_exam_musculo_skeletal_more = $post['physical_exam']['musculo_skeletal_more'];
		$insert->physical_exam_neurological = $post['physical_exam']['neurological'];
		$insert->physical_exam_neurological_more = $post['physical_exam']['neurological_more'];
		$insert->human = $post['human'];
		$insert->other_findings = $post['other_findings'];
		$insert->save();
		$result = $insert->id;

		$comment = "Aufnahmebericht wurde erstellt";

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s", time());
		$cust->course_type = Pms_CommonData::aesEncrypt("K");
		$cust->course_title = Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt("bre_recording_report");
		$cust->recordid = $result;
		$cust->user_id = $userid;
		$cust->done_date = $done_date;
		$cust->save();

		//add befund
		if ($post['physical_exam']['skin_mucous_membran'] == 1 && strlen($post['physical_exam']['skin_mucous_membran_more']) != 0)
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("B");
			$cust->course_title = Pms_CommonData::aesEncrypt("Haut/Schleimhäute: " . htmlspecialchars(addslashes($post['physical_exam']['skin_mucous_membran_more'])));
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->save();
		}

		if ($post['physical_exam']['heart'] == 1 && strlen($post['physical_exam']['heart_more']) != 0)
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("B");
			$cust->course_title = Pms_CommonData::aesEncrypt("Herz: " . htmlspecialchars(addslashes($post['physical_exam']['heart_more'])));
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->save();
		}

		if ($post['physical_exam']['lungs'] == 1 && strlen($post['physical_exam']['lungs_more']) != 0)
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("B");
			$cust->course_title = Pms_CommonData::aesEncrypt("Lunge: " . htmlspecialchars(addslashes($post['physical_exam']['lungs_more'])));
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->save();
		}

		if ($post['physical_exam']['abdomen'] == 1 && strlen($post['physical_exam']['abdomen_more']) != 0)
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("B");
			$cust->course_title = Pms_CommonData::aesEncrypt("Abdomen: " . htmlspecialchars(addslashes($post['physical_exam']['abdomen_more'])));
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->save();
		}

		if ($post['physical_exam']['musculo_skeletal'] == 1 && strlen($post['physical_exam']['musculo_skeletal_more']) != 0)
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("B");
			$cust->course_title = Pms_CommonData::aesEncrypt("Bewegungsapparat: " . htmlspecialchars(addslashes($post['physical_exam']['musculo_skeletal_more'])));
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->save();
		}

		if ($post['physical_exam']['neurological'] == 1 && strlen($post['physical_exam']['neurological_more']) != 0)
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("B");
			$cust->course_title = Pms_CommonData::aesEncrypt("Neurologische Untersuchung: " . htmlspecialchars(addslashes($post['physical_exam']['neurological_more'])));
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->save();
		}

		if ($stmb->id > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function update_data ( $post, $fid )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$done_date = date('Y-m-d H:i:s', time());


		$upd = Doctrine::getTable('BreRecordingReport')->findOneByIdAndIpid($fid, $ipid);
		$upd->curent_medical_history = $post['curent_medical_history'];
		$upd->social_history = $post['social_history'];
		$upd->priority_items_1 = $post['priority_items'][0];
		$upd->priority_items_2 = $post['priority_items'][1];
		$upd->priority_items_3 = $post['priority_items'][2];
		$upd->priority_items_4 = $post['priority_items'][3];
		$upd->priority_items_5 = $post['priority_items'][4];
		$upd->priority_items_6 = $post['priority_items'][5];
		$upd->living_will_option = $post['living_will_option'];
		$upd->attorney_power_option = $post['attorney_power_option'];
		$upd->contact_person_id = $post['contact_person_id'];
		$upd->contact_person_name = $post['contact_person_name'];
		$upd->contact_person_lastname = $post['contact_person_lastname'];
		$upd->resuscitation = $post['resuscitation'];
		$upd->causal_therapy = $post['causal_therapy'];
		$upd->other_therapy = $post['other_therapy'];
		$upd->other_therapy_more = $post['other_therapy_more'];

		$upd->body_condition_inconspicuous = $post['body_condition']['inconspicuous'];
		$upd->body_condition_red_az = $post['body_condition']['red_az'];
		$upd->body_condition_kachekt = $post['body_condition']['kachekt'];
		$upd->body_condition_fear = $post['body_condition']['fear'];
		$upd->body_condition_constipation = $post['body_condition']['constipation'];
		$upd->body_condition_morning_sickness = $post['body_condition']['morning_sickness'];
		$upd->body_condition_more = $post['body_condition_more'];

		$upd->consciousness_inconspicuous = $post['consciousness']['inconspicuous'];
		$upd->consciousness_tarnished = $post['consciousness']['tarnished'];
		$upd->consciousness_restless = $post['consciousness']['restless'];
		$upd->consciousness_stupor = $post['consciousness']['stupor'];
		$upd->consciousness_coma = $post['consciousness']['coma'];
		$upd->consciousness_slowed = $post['consciousness']['slowed'];
		$upd->consciousness_disorientation = $post['consciousness']['disorientation'];
		$upd->consciousness_more = $post['consciousness']['more'];
		$upd->consciousness_more_text = $post['consciousness']['more_text'];

		$upd->skin_inconspicuous = $post['skin']['inconspicuous'];
		$upd->skin_pale = $post['skin']['pale'];
		$upd->skin_cyanotic = $post['skin']['cyanotic'];
		$upd->skin_ikter = $post['skin']['ikter'];
		$upd->skin_dry = $post['skin']['dry'];
		$upd->skin_hemoragy = $post['skin']['hemoragy'];
		$upd->skin_more_text = $post['skin']['more_text'];

		$upd->edema_uleg = $post['edema']['u_leg'];
		$upd->edema_oleg = $post['edema']['o_leg'];
		$upd->edema_hands = $post['edema']['hands'];
		$upd->edema_eyelids = $post['edema']['eyelids'];
		$upd->edema_face = $post['edema']['face'];
		$upd->edema_moving = $post['edema']['moving'];
		$upd->edema_more_text = $post['edema']['more_text'];

		$upd->physical_exam_skin_mucous_membran = $post['physical_exam']['skin_mucous_membran'];
		$upd->physical_exam_skin_mucous_membran_more = $post['physical_exam']['skin_mucous_membran_more'];
		$upd->physical_exam_heart = $post['physical_exam']['heart'];
		$upd->physical_exam_heart_more = $post['physical_exam']['heart_more'];
		$upd->physical_exam_lungs = $post['physical_exam']['lungs'];
		$upd->physical_exam_lungs_more = $post['physical_exam']['lungs_more'];
		$upd->physical_exam_abdomen = $post['physical_exam']['abdomen'];
		$upd->physical_exam_abdomen_more = $post['physical_exam']['abdomen_more'];
		$upd->physical_exam_musculo_skeletal = $post['physical_exam']['musculo_skeletal'];
		$upd->physical_exam_musculo_skeletal_more = $post['physical_exam']['musculo_skeletal_more'];
		$upd->physical_exam_neurological = $post['physical_exam']['neurological'];
		$upd->physical_exam_neurological_more = $post['physical_exam']['neurological_more'];
		$upd->human = $post['human'];
		$upd->other_findings = $post['other_findings'];
		$upd->save();


		//form edited
		$comment_edit = "Aufnahmebericht wurde editiert";

		$custcourse = new PatientCourse();
		$custcourse->ipid = $ipid;
		$custcourse->course_date = date("Y-m-d H:i:s", time());
		$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
		$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment_edit));
		$custcourse->user_id = $userid;
		$custcourse->recordid = $fid;
		$custcourse->tabname = Pms_CommonData::aesEncrypt(addslashes('bre_recording_report'));
		$custcourse->save();

		return true;
	}

}
?>