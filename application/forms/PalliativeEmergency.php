<?php

require_once("Pms/Form.php");

class Application_Form_PalliativeEmergency extends Pms_Form{

	public function insertPalliativeEmergency($post){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$post_data = $post['palliative'];

		$stmb = new PalliativeEmergency();
		$stmb->ipid = $ipid;


		$stmb->pflege_ppd_details = $post_data['pflege_ppd_details'];
		$stmb->assigned_details = $post_data['assigned_details'];
		$stmb->family_doc_details = $post_data['family_doc_details'];
		$stmb->pflege_details = $post_data['pflege_details'];
		$stmb->diagnosen =  $post_data['diagnosen'];
		$stmb->amb_hospiz_details = $post_data['amb_hospiz_details'];
		$stmb->seelsorge = $post_data['seelsorge'];
		$stmb->hospize_app = $post_data['hospize_app'];
		$stmb->kv_emergency_call = $post_data['kv_emergency_call'];
		$stmb->palliative_problem_a = $post_data['palliative_problem_a'];
		$stmb->kv_rescue = $post_data['kv_rescue'];
		$stmb->palliative_problem_b = $post_data['palliative_problem_b'];
		$stmb->technical_emergency = $post_data['technical_emergency'];
		$stmb->palliative_problem_c = $post_data['palliative_problem_c'];
		$stmb->palliative_problem_d = $post_data['palliative_problem_d'];
		$stmb->last_hospital_stay = $post_data['last_hospital_stay'];
		$stmb->feature_a = $post_data['feature_a'];
		$stmb->last_hospital_name = $post_data['last_hospital_name'];
		$stmb->feature_b = $post_data['feature_b'];
		$stmb->feature_c = $post_data['feature_c'];
		$stmb->living_will = $post_data['living_will'];
		$stmb->life_prolonging_all_mesures = $post_data['life_prolonging_all_mesures'];
		$stmb->cnt_legal = $post_data['cnt_legal'];
		$stmb->life_prolonging_no_resuscitation = $post_data['life_prolonging_no_resuscitation'];
		$stmb->care_document = $post_data['care_document'];
		$stmb->life_prolonging_no_ventilation = $post_data['life_prolonging_no_ventilation'];
		$stmb->living_will_more = $post_data['living_will_more'];
		$stmb->life_prolonging_no_icu = $post_data['life_prolonging_no_icu'];
		$stmb->patient_diagno_edu = $post_data['patient_diagno_edu'];
		$stmb->life_prolonging_no_hospital = $post_data['life_prolonging_no_hospital'];
		$stmb->member_diagno_edu = $post_data['member_diagno_edu'];
		$stmb->life_prolonging_palliative = $post_data['life_prolonging_palliative'];
		$stmb->cnt_first_details = $post_data['cnt_first_details'];
		$stmb->bedarf_medication = $post_data['bedarf_medication'];
		$stmb->cnt_legal_details = $post_data['cnt_legal_details'];
		$stmb->schmerzp_medication = $post_data['schmerzp_medication'];

		$stmb->new_instance  = '1';
		$stmb->save();

		$result = $stmb->id;

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s", time());
		$cust->course_type = Pms_CommonData::aesEncrypt("K");
		$cust->course_title = Pms_CommonData::aesEncrypt("Palliativ - Notfallbogen  hinzugefügt.");
		$cust->recordid = $result;
		$cust->tabname = Pms_CommonData::aesEncrypt("PalliativeEmergency_form");
		$cust->user_id = $userid;
		$cust->save();


		if($result > 0){
			return true;
		}else{
			return false;
		}
	}


	public function UpdatePalliativeEmergency($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

			
		$stmb = Doctrine::getTable('PalliativeEmergency')->find($post['ple_id']);
		$stmb->pflege_ppd_details = $post_data['pflege_ppd_details'];
		$stmb->assigned_details = $post_data['assigned_details'];
		$stmb->family_doc_details = $post_data['family_doc_details'];
		$stmb->pflege_details = $post_data['pflege_details'];
		$stmb->diagnosen =  $post_data['diagnosen'];
		$stmb->amb_hospiz_details = $post_data['amb_hospiz_details'];
		$stmb->seelsorge = $post_data['seelsorge'];
		$stmb->hospize_app = $post_data['hospize_app'];
		$stmb->kv_emergency_call = $post_data['kv_emergency_call'];
		$stmb->palliative_problem_a = $post_data['palliative_problem_a'];
		$stmb->kv_rescue = $post_data['kv_rescue'];
		$stmb->palliative_problem_b = $post_data['palliative_problem_b'];
		$stmb->technical_emergency = $post_data['technical_emergency'];
		$stmb->palliative_problem_c = $post_data['palliative_problem_c'];
		$stmb->palliative_problem_d = $post_data['palliative_problem_d'];
		$stmb->last_hospital_stay = $post_data['last_hospital_stay'];
		$stmb->feature_a = $post_data['feature_a'];
		$stmb->last_hospital_name = $post_data['last_hospital_name'];
		$stmb->feature_b = $post_data['feature_b'];
		$stmb->feature_c = $post_data['feature_c'];
		$stmb->living_will = $post_data['living_will'];
		$stmb->life_prolonging_all_mesures = $post_data['life_prolonging_all_mesures'];
		$stmb->cnt_legal = $post_data['cnt_legal'];
		$stmb->life_prolonging_no_resuscitation = $post_data['life_prolonging_no_resuscitation'];
		$stmb->care_document = $post_data['care_document'];
		$stmb->life_prolonging_no_ventilation = $post_data['life_prolonging_no_ventilation'];
		$stmb->living_will_more = $post_data['living_will_more'];
		$stmb->life_prolonging_no_icu = $post_data['life_prolonging_no_icu'];
		$stmb->patient_diagno_edu = $post_data['patient_diagno_edu'];
		$stmb->life_prolonging_no_hospital = $post_data['life_prolonging_no_hospital'];
		$stmb->member_diagno_edu = $post_data['member_diagno_edu'];
		$stmb->life_prolonging_palliative = $post_data['life_prolonging_palliative'];
		$stmb->cnt_first_details = $post_data['cnt_first_details'];
		$stmb->bedarf_medication = $post_data['bedarf_medication'];
		$stmb->cnt_legal_details = $post_data['cnt_legal_details'];
		$stmb->schmerzp_medication = $post_data['schmerzp_medication'];

		$stmb->save();

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s",time());
		$cust->course_type=Pms_CommonData::aesEncrypt("K");
		$cust->course_title=Pms_CommonData::aesEncrypt(" Palliativ - Notfallbogen  wurde editiert");
		$cust->recordid = $post['ple_id'];
		$cust->tabname = Pms_CommonData::aesEncrypt("PalliativeEmergency_form");
		$cust->user_id = $userid;
		$cust->save();


	}

}

?>