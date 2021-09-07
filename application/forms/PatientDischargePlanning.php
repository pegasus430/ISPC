<?

class Application_Form_PatientDischargePlanning extends Pms_Form{


	public function InsertData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$final_plan_date = date('Y-m-d H:i:s', strtotime($post['plan_date']." ".$post['start_time'].":00")) ;

		if(!empty($post['expected_discharge_date'])){
			$final_ex_discharge_date = date('Y-m-d H:i:s', strtotime($post['expected_discharge_date'])) ;
		} else {
			$final_ex_discharge_date = "0000-00-00 00:00:00";
		}

		$frm = new PatientDischargePlanning();
		$frm->ipid = $ipid;
		$frm->start_time = $post['start_time'];
		$frm->end_time = $post['end_time'];
		$frm->plan_date  = $final_plan_date ;
		$frm->expected_discharge_date  = $final_ex_discharge_date;
		$frm->driving_time = $post['driving_time'];
		$frm->driving_distance = $post['driving_distance'];
		$frm->location = $post['location'];
		$frm->location_phone = $post['location_phone'];
		$frm->location_fax = $post['location_fax'];
		$frm->user_details = $post['user_details'];
		$frm->user_phone = $post['user_phone'];
		$frm->interview_with_patient  = $post['interview_with_patient'];
		$frm->interview_with_nurse  = $post['interview_with_nurse'];
		$frm->interview_with_doctor  = $post['interview_with_doctor'];
		$frm->interview_with_contact  = $post['interview_with_contact'];


		$frm->interview_details = $post['interview_details'];
		$frm->interview_contact = $post['interview_contact'];
		$frm->provide_care = $post['provide_care'];
		$frm->provide_care_details = $post['provide_care_details'];
		$frm->existing_care_service = $post['existing_care_service'];
		$frm->existing_care_service_text = $post['existing_care_service_text'];
		$frm->new_care_service = $post['new_care_service'];
		$frm->new_care_service_text = $post['new_care_service_text'];
		$frm->care_application_set = $post['care_application_set'];
		$frm->care_application_provided = $post['care_application_provided'];
		$frm->care_application_upgrade = $post['care_application_upgrade'];

		$frm->another_service = $post['another_service'];
		$frm->another_service_text = $post['another_service_text'];
		$frm->inofficial_comment = $post['inofficial_comment'];
		$frm->official_comment = $post['official_comment'];
		$frm->save();

		$result = $frm->id;

		$tab_name = 'discharge_planning';

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s", time());
		$cust->course_type = Pms_CommonData::aesEncrypt("F");
		$cust->course_title = Pms_CommonData::aesEncrypt($comment);
		$cust->tabname = Pms_CommonData::aesEncrypt($tab_name);
		$cust->recordid = $result;
		$cust->user_id = $userid;
		$cust->done_date = date('Y-m-d H:i:s', strtotime($post['plan_date'].' '.$post['start_time'].':'.date('s', time())));
		$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
		$cust->done_id = $result;
		$cust->save();

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s", time());
		$cust->course_type = Pms_CommonData::aesEncrypt("K");
		$cust->course_title = Pms_CommonData::aesEncrypt($post['start_time'] . ' - ' . $post['end_time'] . '  ' . $post['plan_date']);
		$cust->user_id = $userid;
		$cust->done_date = date('Y-m-d H:i:s', strtotime($post['plan_date'].' '.$post['start_time'].':'.date('s', time())));
		$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
		$cust->done_id = $result;
		$cust->save();


		if (!empty($post['driving_time']) & $post['driving_time'] != "--")
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt("Fahrtzeit / km: " . $post['driving_time']." /  ".$post['driving_distance']);
			$cust->user_id = $userid;
			$cust->done_date = date('Y-m-d H:i:s', strtotime($post['plan_date'].' '.$post['start_time'].':'.date('s', time())));
			$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
			$cust->done_id = $result;
			$cust->save();
		}

		if (!empty($post['official_comment']) && $post['official_comment'] != "")
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($post['official_comment']);
			$cust->user_id = $userid;
			$cust->done_date = date('Y-m-d H:i:s', strtotime($post['plan_date'].' '.$post['start_time'].':'.date('s', time())));
			$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
			$cust->done_id = $result;
			$cust->save();
		}


		if (!empty($post['inofficial_comment']) && $post['inofficial_comment'] != "")
		{
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("XK");
			$cust->course_title = Pms_CommonData::aesEncrypt($post['inofficial_comment']);
			$cust->user_id = $userid;
			$cust->done_date = date('Y-m-d H:i:s', strtotime($post['plan_date'].' '.$post['start_time'].':'.date('s', time())));
			$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
			$cust->done_id = $result;
			$cust->save();
		}


		return $result;
	}


	public function UpdateData($post,$plan_id)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);



		$final_plan_date = date('Y-m-d H:i:s', strtotime($post['plan_date']." ".$post['start_time'].":00")) ;



		if(!empty($post['expected_discharge_date'])){
			$final_ex_discharge_date = date('Y-m-d H:i:s', strtotime($post['expected_discharge_date'])) ;
		} else {
			$final_ex_discharge_date = "0000-00-00 00:00:00";
		}

		$frm = Doctrine::getTable('PatientDischargePlanning')->find($plan_id);
		$frm->start_time = $post['start_time'];
		$frm->end_time = $post['end_time'];
		$frm->plan_date  = $final_plan_date ;
		$frm->expected_discharge_date  = $final_ex_discharge_date;
		$frm->driving_time = $post['driving_time'];
		$frm->driving_distance = $post['driving_distance'];
		$frm->location = $post['location'];
		$frm->location_phone = $post['location_phone'];
		$frm->location_fax = $post['location_fax'];
		$frm->user_details = $post['user_details'];
		$frm->user_phone = $post['user_phone'];
		$frm->interview_with_patient  = $post['interview_with_patient'];
		$frm->interview_with_nurse  = $post['interview_with_nurse'];
		$frm->interview_with_doctor  = $post['interview_with_doctor'];
		$frm->interview_with_contact  = $post['interview_with_contact'];

		$frm->interview_details = $post['interview_details'];
		$frm->interview_contact = $post['interview_contact'];
		$frm->provide_care = $post['provide_care'];
		$frm->provide_care_details = $post['provide_care_details'];
		$frm->existing_care_service = $post['existing_care_service'];
		$frm->existing_care_service_text = $post['existing_care_service_text'];
		$frm->new_care_service = $post['new_care_service'];
		$frm->new_care_service_text = $post['new_care_service_text'];
		$frm->care_application_set = $post['care_application_set'];
		$frm->care_application_provided = $post['care_application_provided'];
		$frm->care_application_upgrade = $post['care_application_upgrade'];

		$frm->another_service = $post['another_service'];
		$frm->another_service_text = $post['another_service_text'];
		$frm->inofficial_comment = $post['inofficial_comment'];
		$frm->official_comment = $post['official_comment'];
		$frm->save();

		$done_date = date('Y-m-d H:i:s', strtotime($post['plan_date'] . ' ' . $post['start_time'] . ':00'));
			

		$cust = new PatientCourse();
		$cust->ipid = $ipid;
		$cust->course_date = date("Y-m-d H:i:s", time());
		$cust->course_type = Pms_CommonData::aesEncrypt("K");
		$cust->course_title = Pms_CommonData::aesEncrypt("Entlassungsplanung vom " . date('d.m.Y H:i', strtotime($done_date)) . " wurde editiert");
		$cust->user_id = $userid;
		$cust->save();
	}
}

?>