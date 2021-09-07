<?php
require_once("Pms/Form.php");
class Application_Form_Muster2b extends Pms_Form
{

	public function insert_data ( $ipid, $post )
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
			
		//checkboxes
		$post['physician_treatment'] = ($post['physician_treatment'] == '1' ? '1' : '0');
		$post['emergency'] = ($post['emergency'] == '1' ? '1' : '0');
		$post['accident_accidental'] = ($post['accident_accidental'] == '1' ? '1' : '0');
		$post['service_related_injury'] = ($post['service_related_injury'] == '1' ? '1' : '0');

		$insert_data = new Muster2b();
		$insert_data->ipid = $ipid;

		$insert_data->physician_treatment = $post['physician_treatment'];
		$insert_data->emergency = $post['emergency'];
		$insert_data->accident_accidental = $post['accident_accidental'];
		$insert_data->service_related_injury = $post['service_related_injury'];

		$insert_data->accessible_hospitals = $post['accessible_hospitals'];
		$insert_data->relevante_diagnoses = $post['relevante_diagnoses'];
		$insert_data->investigation_results = $post['investigation_results'];
		$insert_data->measures_to_date = $post['measures_to_date'];
		$insert_data->other_notes = $post['other_notes'];
		$insert_data->submitted_results = $post['submitted_results'];
		$insert_data->save();

		$id = $insert_data->id;


		if ($id)
		{
			$comment = "Formular Muster 2b hinzugefügt.";
			$patient_file = new PatientCourse();
			$patient_file->ipid = $ipid;
			$patient_file->course_date = date("Y-m-d H:i:s", time());
			$patient_file->course_type = Pms_CommonData::aesEncrypt("F");
			$patient_file->course_title = Pms_CommonData::aesEncrypt($comment);
			$patient_file->user_id = $logininfo->userid;
			$patient_file->done_name = Pms_CommonData::aesEncrypt('muster_2b_form');
			$patient_file->tabname = Pms_CommonData::aesEncrypt('muster_2b_form');
			$patient_file->done_id = $id;
			$patient_file->save();

			return $id;
		}
		else
		{
			return false;
		}
	}

	public function update_data ($ipid, $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		//checkboxes
		$post['physician_treatment'] = ($post['physician_treatment'] == '1' ? '1' : '0');
		$post['emergency'] = ($post['emergency'] == '1' ? '1' : '0');
		$post['accident_accidental'] = ($post['accident_accidental'] == '1' ? '1' : '0');
		$post['service_related_injury'] = ($post['service_related_injury'] == '1' ? '1' : '0');
		
		$update_data = Doctrine::getTable('Muster2b')->findOneById($post['saved_id']);
		
		$update_data->physician_treatment = $post['physician_treatment'];
		$update_data->emergency = $post['emergency'];
		$update_data->accident_accidental = $post['accident_accidental'];
		$update_data->service_related_injury = $post['service_related_injury'];
		
		$update_data->accessible_hospitals = $post['accessible_hospitals'];
		$update_data->relevante_diagnoses = $post['relevante_diagnoses'];
		$update_data->investigation_results = $post['investigation_results'];
		$update_data->measures_to_date = $post['measures_to_date'];
		$update_data->other_notes = $post['other_notes'];
		$update_data->submitted_results = $post['submitted_results'];
		$update_data->save();
		
		
			$comment = "Formular Muster 2b wurde editiert.";
			$patient_file = new PatientCourse();
			$patient_file->ipid = $ipid;
			$patient_file->course_date = date("Y-m-d H:i:s", time());
			$patient_file->course_type = Pms_CommonData::aesEncrypt("F");
			$patient_file->course_title = Pms_CommonData::aesEncrypt($comment);
			$patient_file->user_id = $logininfo->userid;
			$patient_file->done_name = Pms_CommonData::aesEncrypt('muster_2b_form');
			$patient_file->tabname = Pms_CommonData::aesEncrypt('muster_2b_form');
			$patient_file->done_id = $id;
			$patient_file->save();
		
			$id = $patient_file->id;

			return true;
		
		}

	public function mark_as_completed ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$cust = Doctrine::getTable('Muster2b')->findOneById($post['saved_id']);
		$cust->iscompleted = 1;
		$cust->completed_date= date("Y-m-d H:i:s", time());
		$cust->save();

		return true;
	}
}
?>