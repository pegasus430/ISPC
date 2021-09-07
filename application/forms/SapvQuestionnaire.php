<?php
require_once("Pms/Form.php");
class Application_Form_SapvQuestionnaire extends Pms_Form
{

	public function validate ( $post )
	{

	}

	public function insertSapvQuestionnaire ( $post, $ipid, $userid )
	{
		$ins = new SapvQuestionnaire();
		$ins->ipid = $ipid;
		$ins->no_sapv_data = $post['no_sapv_data'];
		$ins->sapv_data_exists = $post['sapv_data_exists'];
		if(!empty($post['sapv_date']))
		{
			$ins->sapv_date = date('Y-m-d H:i:s', strtotime($post['sapv_date']));
		} else {
			$ins->sapv_date = "0000-00-00 00:00:00";
		}
		$ins->beantragt = $post['beantragt'];
		$ins->palliativarzt = $post['palliativarzt'];
		$ins->diagno_text = htmlspecialchars($post['diagno_text']);
		$ins->operativ = $post['operativ'];
		$ins->operativ_date = htmlspecialchars($post['operativ_date']);

		$ins->chemo = $post['chemo'];
		$ins->chemo_date = htmlspecialchars($post['chemo_date']);

		$ins->radiatio = $post['radiatio'];
		$ins->radiatio_date = htmlspecialchars($post['radiatio_date']);
		$ins->hospital_period = htmlspecialchars($post['hospital_period']);
		$ins->hospital_location = htmlspecialchars($post['hospital_location']);
		$ins->symptom_control = htmlspecialchars($post['symptom_control']);
		$ins->med_oral = $post['med_oral'];
		$ins->med_iv = $post['med_iv'];
		$ins->med_im = $post['med_im'];
		$ins->med_sc = $post['med_sc'];
		$ins->med_peg = $post['med_peg'];
		$ins->med_infusion = $post['med_infusion'];
		$ins->med_pca_pumpe = $post['med_pca_pumpe'];
		$ins->med_inhalation = $post['med_inhalation'];
		$ins->med_fest = $post['med_fest'];
		$ins->med_bedarf = $post['med_bedarf'];
		$ins->kg = $post['kg'];
		$ins->lymphdrainage = $post['lymphdrainage'];
		$ins->chemotherapie = $post['chemotherapie'];
		$ins->radiatio_needed = $post['radiatio_needed'];
		$ins->atemtherapie = $post['atemtherapie'];
		$ins->sauerstoffgabe = $post['sauerstoffgabe'];
		$ins->urostoma = $post['urostoma'];
		$ins->anuspraeter = $post['anuspraeter'];
		$ins->tracheostoma = $post['tracheostoma'];
		$ins->lagerung = $post['lagerung'];
		$ins->ablaufsonde = $post['ablaufsonde'];
		$ins->wound_treatment = $post['wound_treatment'];
		$ins->wound_treatment_description = htmlspecialchars($post['wound_treatment_description']);
		$ins->family_social_environment = htmlspecialchars($post['family_social_environment']);
		$ins->isdelete = '0';
		$ins->save();

		//added new
		$custcourse = new PatientCourse();
		$custcourse->ipid = $ipid;
		$custcourse->course_date = date("Y-m-d H:i:s", time());
		$custcourse->course_type = Pms_CommonData::aesEncrypt("F");
		$comment = "SAPV Fragebogen Formular wurde angelegt.";
		$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
		$custcourse->user_id = $userid;
		$custcourse->recordid = $ins->id;
		$custcourse->tabname = Pms_CommonData::aesEncrypt(addslashes('sapv_questionnaire'));
		$custcourse->save();

		if ($ins->id > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function updateSapvQuestionnaire($post, $qid, $ipid, $userid)
	{
		$upd = Doctrine::getTable('SapvQuestionnaire')->findOneByIdAndIpid($qid, $ipid);
		$upd->no_sapv_data = $post['no_sapv_data'];
		$upd->sapv_data_exists = $post['sapv_data_exists'];
		if(!empty($post['sapv_date']))
		{
			$upd->sapv_date = date('Y-m-d H:i:s', strtotime($post['sapv_date']));
		} else {
			$upd->sapv_date = "0000-00-00 00:00:00";
		}
		$upd->beantragt = $post['beantragt'];
		$upd->palliativarzt = $post['palliativarzt'];
		$upd->diagno_text = htmlspecialchars($post['diagno_text']);
		$upd->operativ = $post['operativ'];
		$upd->operativ_date = htmlspecialchars($post['operativ_date']);

		$upd->chemo = $post['chemo'];
		$upd->chemo_date = htmlspecialchars($post['chemo_date']);
		$upd->radiatio = $post['radiatio'];
		$upd->radiatio_date = htmlspecialchars($post['radiatio_date']);
		$upd->hospital_period = htmlspecialchars($post['hospital_period']);
		$upd->hospital_location = htmlspecialchars($post['hospital_location']);
		$upd->symptom_control = htmlspecialchars($post['symptom_control']);
		$upd->med_oral = $post['med_oral'];
		$upd->med_iv = $post['med_iv'];
		$upd->med_im = $post['med_im'];
		$upd->med_sc = $post['med_sc'];
		$upd->med_peg = $post['med_peg'];
		$upd->med_infusion = $post['med_infusion'];
		$upd->med_pca_pumpe = $post['med_pca_pumpe'];
		$upd->med_inhalation = $post['med_inhalation'];
		$upd->med_fest = $post['med_fest'];
		$upd->med_bedarf = $post['med_bedarf'];
		$upd->kg = $post['kg'];
		$upd->lymphdrainage = $post['lymphdrainage'];
		$upd->chemotherapie = $post['chemotherapie'];
		$upd->radiatio_needed = $post['radiatio_needed'];
		$upd->atemtherapie = $post['atemtherapie'];
		$upd->sauerstoffgabe = $post['sauerstoffgabe'];
		$upd->urostoma = $post['urostoma'];
		$upd->anuspraeter = $post['anuspraeter'];
		$upd->tracheostoma = $post['tracheostoma'];
		$upd->lagerung = $post['lagerung'];
		$upd->ablaufsonde = $post['ablaufsonde'];
		$upd->wound_treatment = $post['wound_treatment'];
		$upd->wound_treatment_description = htmlspecialchars($post['wound_treatment_description']);
		$upd->family_social_environment = htmlspecialchars($post['family_social_environment']);
		$upd->save();

		//formular editat
		$custcourse = new PatientCourse();
		$custcourse->ipid = $ipid;
		$custcourse->course_date = date("Y-m-d H:i:s", time());
		$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
		$comment = "SAPV Fragebogen wurde editiert";
		$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
		$custcourse->user_id = $userid;
		$custcourse->recordid = $qid;
		$custcourse->tabname = Pms_CommonData::aesEncrypt(addslashes('sapv_questionnaire'));
		$custcourse->save();

		return true;
	}
}
?>