<?php

require_once("Pms/Form.php");

class Application_Form_ZapvAssessmentII extends Pms_Form{

	public function validate ( $post )
	{
		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$val = new Pms_Validation();

		if (!$val->isdate(end($post['done_date'])))
		{
			$this->error_message['completed_date_error'] = $Tr->translate('completed_date_err');
		}

		if ($error == 0)
		{
			return true;
		}

		return false;
	}

	public function insert_form_data($post, $ipid, $type, $status = 'active' )
	{
		//print_R($post); exit;

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;

		$Tr = new Zend_View_Helper_Translate();


		$zapv = new ZapvAssessmentII();
		$zapv->ipid = $ipid;
		$zapv->type = $type;
		$zapv->status = $status; // active ||  inactiv

		if(!empty($post['first_sapv_till']) && strlen($post['first_sapv_till'])>0){
			$zapv->first_sapv_till = date("Y-m-d H:i", strtotime($post['first_sapv_till']));
		}

		if(!empty($post['first_sapv_type'])){
			$zapv->first_sapv_type = implode(',',$post['first_sapv_type']);
		} else{
			$zapv->first_sapv_type = "";
		}

		if(!empty($post['latest_sapv_till']) && strlen($post['latest_sapv_till'])>0){
			$zapv->latest_sapv_till = date("Y-m-d H:i", strtotime($post['latest_sapv_till']));
		}


		if(!empty($post['latest_sapv_type'])){
			$zapv->latest_sapv_type = implode(',',$post['latest_sapv_type']);
		} else{
			$zapv->latest_sapv_type = "";
		}

		$zapv->diagnosis = $post['diagnosis'];
		$zapv->curative_treatment = $post['curative_treatment'];
		$zapv->after_sapvrl = $post['after_sapvrl'];
		
		$zapv->used_contact_forms = json_encode($post['used_contact_forms']);
		$zapv->advice_checked = $post['advice_checked'];
		$zapv->advice_description = $post['advice_description'];
		$zapv->advice_involved_persons= $post['advice_involved_persons'];

		if(!empty($post['providers'])){
			$zapv->providers = implode(',',$post['providers']);
		} else{
			$zapv->providers = "";
		}
		$zapv->providers_other= $post['providers_other'];

		if(!empty($post['treatment_plan'])){
			$zapv->treatment_plan = implode(',',$post['treatment_plan']);
		}else{
			$zapv->treatment_plan = "";
		}

		if(!empty($post['support_needs'])){
			$zapv->support_needs = implode(',',$post['support_needs']);
		}else{
			$zapv->support_needs = "";
		}
		if(!empty($post['sapv'])){
			$zapv->sapv = implode(',',$post['sapv']);
		}else{
			$zapv->sapv = "";
		}

		$zapv->sapv_requierments = $post['sapv_requierments'];

		if(!empty($post['sapv_requierments_until']) && strlen($post['sapv_requierments_until'])>0){
			$zapv->sapv_requierments_until =  date("Y-m-d H:i", strtotime($post['sapv_requierments_until']));
		}

		$zapv->sapv_end_date = $post['sapv_end_date'];

		if(!empty($post['reason_of_termination'])){
			$zapv->reason_of_termination = implode(',',$post['reason_of_termination']);
		}else{
			$zapv->reason_of_termination = "";
		}
		$zapv->other_messages = $post['other_messages'];

		if(!empty($post['done_date']) && strlen($post['done_date'])>0){
			$zapv->done_date =  date("Y-m-d H:i", strtotime($post['done_date']));
		}

		$zapv->done_by = $post['done_by'];
		$zapv->comments = $post['comments'];
		$zapv->save();

		if ($zapv->id > 0){
			// insert in symptomatics 
			if(!empty($post['symptom_group']))
			{
				foreach($post['symptom_group'] as $symp_group => $symp_description)
				{
					$symptom_group_arr[] = array(
						'ipid' => $ipid,
						'form_id' => $zapv->id,
						'symp_group' => $symp_group,
						'symp_description' => $symp_description
					);
				}
				
				if(count($symptom_group_arr) > 0)
				{
					//insert many records with one query!!
					$collection = new Doctrine_Collection('ZapvAssessmentIISymp');
					$collection->fromArray($symptom_group_arr);
					$collection->save();
				}
			}
		}
		

		$comment = $Tr->translate($type.'_zapv_assessment_ii_added');
		$tabname = "zapv_assessment_ii_".$type;

		$custcourse = new PatientCourse();
		$custcourse->triggerformid = 0;//force exit the PatientCourse dbf triggers
		$custcourse->ipid = $ipid;
		$custcourse->course_date = date("Y-m-d H:i:s", time());
		$custcourse->course_type = Pms_CommonData::aesEncrypt("F");
		$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
		$custcourse->user_id = $userid;
		$custcourse->recordid = $zapv->id;
		$custcourse->done_date = date("Y-m-d H:i:s", time());
		$custcourse->tabname = Pms_CommonData::aesEncrypt($tabname);
		$custcourse->save();


		if ($zapv->id > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public function update_form_data($form_id,$post, $ipid, $type, $status = 'active')
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$Tr = new Zend_View_Helper_Translate();
		if(!empty($form_id)){
			$zapv = Doctrine::getTable('ZapvAssessmentII')->findOneByIdAndIpid($form_id, $ipid);

			$zapv->status = $status; // active ||  inactiv

			if(!empty($post['first_sapv_till']) && strlen($post['first_sapv_till'])>0){
				$zapv->first_sapv_till = date("Y-m-d H:i", strtotime($post['first_sapv_till']));
			}

			if(!empty($post['first_sapv_type'])){
				$zapv->first_sapv_type = implode(',',$post['first_sapv_type']);
			} else{
				$zapv->first_sapv_type = "";
			}

			if(!empty($post['latest_sapv_till']) && strlen($post['latest_sapv_till'])>0){
				$zapv->latest_sapv_till = date("Y-m-d H:i", strtotime($post['latest_sapv_till']));
			}


			if(!empty($post['latest_sapv_type'])){
				$zapv->latest_sapv_type = implode(',',$post['latest_sapv_type']);
			} else{
				$zapv->latest_sapv_type = "";
			}

			$zapv->diagnosis = $post['diagnosis'];
			$zapv->curative_treatment = $post['curative_treatment'];
			$zapv->after_sapvrl = $post['after_sapvrl'];
			$zapv->used_contact_forms = json_encode($post['used_contact_forms']);
			$zapv->advice_checked = $post['advice_checked'];
			$zapv->advice_description = $post['advice_description'];
			$zapv->advice_involved_persons= $post['advice_involved_persons'];

			if(!empty($post['providers'])){
				$zapv->providers = implode(',',$post['providers']);
			} else{
				$zapv->providers = "";
			}
			$zapv->providers_other= $post['providers_other'];

			if(!empty($post['treatment_plan'])){
				$zapv->treatment_plan = implode(',',$post['treatment_plan']);
			}else{
				$zapv->treatment_plan = "";
			}

			if(!empty($post['support_needs'])){
				$zapv->support_needs = implode(',',$post['support_needs']);
			}else{
				$zapv->support_needs = "";
			}
			if(!empty($post['sapv'])){
				$zapv->sapv = implode(',',$post['sapv']);
			}else{
				$zapv->sapv = "";
			}

			$zapv->sapv_requierments = $post['sapv_requierments'];

			if(!empty($post['sapv_requierments_until']) && strlen($post['sapv_requierments_until'])>0){
				$zapv->sapv_requierments_until =  date("Y-m-d H:i", strtotime($post['sapv_requierments_until']));
			}

			$zapv->sapv_end_date = $post['sapv_end_date'];

			if(!empty($post['reason_of_termination'])){
				$zapv->reason_of_termination = implode(',',$post['reason_of_termination']);
			}else{
				$zapv->reason_of_termination = "";
			}
			$zapv->other_messages = $post['other_messages'];

			if(!empty($post['done_date']) && strlen($post['done_date'])>0){
				$zapv->done_date =  date("Y-m-d H:i", strtotime($post['done_date']));
			}
			$zapv->done_by = $post['done_by'];
			$zapv->comments = $post['comments'];
			$zapv->save();



			$comment = $Tr->translate($type.'_zapv_assessment_ii_updated');
			$tabname = "zapv_assessment_ii_update_".$type;

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt($tabname);
			$cust->recordid = $form_id;
			$cust->user_id = $userid;
			$cust->done_date = date("Y-m-d H:i:s", time());
			$cust->done_name = Pms_CommonData::aesEncrypt($tabname);
			$cust->done_id = $result;
			$cust->save();
		}
	}

	public function generate_new($form_id, $ipid, $type)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$Tr = new Zend_View_Helper_Translate();


		if(!empty($form_id)){
			$zapv = Doctrine::getTable('ZapvAssessmentII')->findOneByIdAndIpid($form_id, $ipid);
			$zapv->status = 'inactive';
			$zapv->save();

			$comment = $Tr->translate($type.'_zapv_assessment_ii_new_started');
			$tabname = "zapv_assessment_ii_new_".$type;

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt($tabname);
			$cust->recordid = $form_id;
			$cust->user_id = $userid;
			$cust->done_date = date("Y-m-d H:i:s", time());
			$cust->done_name = Pms_CommonData::aesEncrypt($tabname);
			$cust->done_id = $result;
			$cust->save();
		}
	}
}
?>