<?php
require_once("Pms/Form.php");
class Application_Form_MdkSapvQuestionnaire extends Pms_Form
{

	public function validate ( $post )
	{

	}

	public function insertSapvQuestionnaire ( $post, $ipid, $userid )
	{
		$ins = new MdkSapvQuestionnaire();
		$ins->ipid = $ipid;
		$ins->no_sapv_data = $post['no_sapv_data'];
		$ins->sapv_data_exists = $post['sapv_data_exists'];
		if(!empty($post['sapv_date']))
		{
			$ins->sapv_date = date('Y-m-d H:i:s', strtotime($post['sapv_date']));
		} else {
			$ins->sapv_date = "0000-00-00 00:00:00";
		}
		$ins->patient_health_insurance = $post['patient_health_insurance'];
		$ins->beantragt = $post['beantragt'];
		$ins->familydoctor = $post['familydoctor'];
		$ins->palliativarzt = $post['palliativarzt'];
		$ins->pflegedienst = $post['pflegedienst'];
		$ins->contactperson = $post['contactperson'];
		$ins->hospizdienst = $post['hospizdienst'];
		$ins->diagno_main = htmlspecialchars($post['diagno_main']);
		$ins->diagno_meta = htmlspecialchars($post['diagno_meta']);
		$ins->diagno_side = htmlspecialchars($post['diagno_side']);
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
		$ins->med_fest_text = htmlspecialchars($post['med_fest_text']);
		$ins->med_bedarf = $post['med_bedarf'];
		$ins->med_bedarf_text = htmlspecialchars($post['med_bedarf_text']);
        //ISPC-2765,Elena,26.01.2021
        $ins->med_vernebelung = $post['med_vernebelung'];
        $ins->chk_angst = $post['chk_angst'];
        $ins->chk_haut = $post['chk_haut'];
        $ins->chk_depression = $post['chk_depression'];
        $ins->chk_durchfall = $post['chk_durchfall'];
        $ins->chk_obstipation = $post['chk_obstipation'];
        $ins->chk_uebelkeit = $post['chk_uebelkeit'];
        $ins->chk_erbrechen = $post['chk_erbrechen'];
        $ins->chk_dyspnoe = $post['chk_dyspnoe'];
        $ins->chk_schmerzen = $post['chk_schmerzen'];
        $ins->vital = htmlspecialchars($post['chk_durchfall']);
        $ins->psycho = htmlspecialchars($post['psycho']);
        $ins->haut = htmlspecialchars($post['haut']);

        $ins->metastasen = $post['metastasen'];
        $ins->metastasen_text = htmlspecialchars($post['metastasen_text']);
        $ins->bzrr = $post['bzrr'];
        $ins->social_support_needed = htmlspecialchars($post['social_support_needed']);

        $ins->az = htmlspecialchars($post['az']);
        $ins->ez = htmlspecialchars($post['ez']);

        $ins->mdk_ort = htmlspecialchars($post['mdk_ort']);

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
		if(!empty($post['mdk_date']))
		{
			$ins->mdk_date = date('Y-m-d H:i:s', strtotime($post['mdk_date']));
		} else {
			$ins->mdk_date = "0000-00-00 00:00:00";
		}
		$ins->mdk_sapv_team = htmlspecialchars($post['mdk_sapv_team']);
		$ins->mdk_sapv_pallarz = htmlspecialchars($post['mdk_sapv_pallarz']);
		if(!empty($post['stampuser']))
		{
			$ins->stampuser = htmlspecialchars($post['stampuser']);
		}
		$ins->isdelete = '0';
		$ins->save();

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
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$upd = Doctrine::getTable('MdkSapvQuestionnaire')->findOneByIdAndIpid($qid, $ipid);
		$upd->no_sapv_data = $post['no_sapv_data'];
		$upd->sapv_data_exists = $post['sapv_data_exists'];
		if(!empty($post['sapv_date']))
		{
			$upd->sapv_date = date('Y-m-d H:i:s', strtotime($post['sapv_date']));
		} else {
			$upd->sapv_date = "0000-00-00 00:00:00";
		}
		$upd->patient_health_insurance = $post['patient_health_insurance'];
		$upd->beantragt = $post['beantragt'];
		$upd->familydoctor = $post['familydoctor'];
		$upd->palliativarzt = $post['palliativarzt'];
		$upd->pflegedienst = $post['pflegedienst'];
		$upd->contactperson = $post['contactperson'];
		$upd->hospizdienst = $post['hospizdienst'];
		$upd->diagno_main = htmlspecialchars($post['diagno_main']);
		$upd->diagno_meta = htmlspecialchars($post['diagno_meta']);
		$upd->diagno_side = htmlspecialchars($post['diagno_side']);
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
		$upd->med_fest_text = htmlspecialchars($post['med_fest_text']);
		$upd->med_bedarf = $post['med_bedarf'];
		$upd->med_bedarf_text = htmlspecialchars($post['med_bedarf_text']);

        //ISPC-2765,Elena,26.01.2021
        $upd->med_vernebelung = $post['med_vernebelung'];
        $upd->chk_angst = $post['chk_angst'];
        $upd->chk_haut = $post['chk_haut'];
        $upd->chk_depression = $post['chk_depression'];
        $upd->chk_durchfall = $post['chk_durchfall'];
        $upd->chk_obstipation = $post['chk_obstipation'];
        $upd->chk_uebelkeit = $post['chk_uebelkeit'];
        $upd->chk_erbrechen = $post['chk_erbrechen'];
        $upd->chk_dyspnoe = $post['chk_dyspnoe'];
        $upd->chk_schmerzen = $post['chk_schmerzen'];
        $upd->vital = htmlspecialchars($post['chk_durchfall']);
        $upd->psycho = htmlspecialchars($post['psycho']);
        $upd->haut = htmlspecialchars($post['haut']);

        $upd->metastasen = $post['metastasen'];
        $upd->metastasen_text = htmlspecialchars($post['metastasen_text']);
        $upd->bzrr = $post['bzrr'];
        $upd->social_support_needed = htmlspecialchars($post['social_support_needed']);

        $upd->az = htmlspecialchars($post['az']);
        $upd->ez = htmlspecialchars($post['ez']);

        $upd->mdk_ort = htmlspecialchars($post['mdk_ort']);

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
		if(!empty($post['mdk_date']))
		{
			$upd->mdk_date = date('Y-m-d H:i:s', strtotime($post['mdk_date']));
		} else {
			$upd->mdk_date = "0000-00-00 00:00:00";
		}
		$upd->mdk_sapv_team = htmlspecialchars($post['mdk_sapv_team']);
		$upd->mdk_sapv_pallarz = htmlspecialchars($post['mdk_sapv_pallarz']);
		if(!empty($post['stampuser']))
		{
			$upd->stampuser = htmlspecialchars($post['stampuser']);
		}
		
		$upd->save();

		return true;
	}
}
?>