<?php

	require_once("Pms/Form.php");

	class Application_Form_RpAssessment extends Pms_Form {

		public function insert_rp_assessment($post, $ipid, $new = false)
		{
			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
		
			$ins = new Rpassessment();
			$ins->ipid = $ipid;
			$ins->rp_pat_last_name = $post['rp_pat_last_name'];
			$ins->rp_pat_first_name = $post['rp_pat_first_name'];
			
			if(!empty($post['rp_pat_birthd']) && date('Y', strtotime($post['rp_pat_birthd'])) != '1970')
			{
				$ins->rp_pat_birthd = date('Y-m-d H:i:s', strtotime($post['rp_pat_birthd']));
			}
			$ins->rp_pat_address = $post['rp_pat_address'];
			$ins->rp_pat_zip_city = $post['rp_pat_zip_city'];
			$ins->rp_pat_phone = $post['rp_pat_phone'];
			$ins->rp_pat_epid = $post['rp_pat_epid'];
			
			
			$ins->rp_health_insurance = $post['rp_health_insurance'];
			$ins->rp_insurance_number = $post['rp_insurance_number'];
			$ins->rp_sex = $post['rp_sex'];

			//erst
			if(!empty($post['rp_start_date_erst']) && date('Y', strtotime($post['rp_start_date_erst'])) != '1970')
			{
				$ins->rp_start_date_erst = date('Y-m-d H:i:s', strtotime($post['rp_start_date_erst']));
			}
			else
			{
				$ins->rp_start_date_erst = '0000-00-00 00:00:00';
			}
			
			if(!empty($post['rp_date_erst']) && date('Y', strtotime($post['rp_date_erst'])) != '1970')
			{
				$ins->rp_date_erst = date('Y-m-d H:i:s', strtotime($post['rp_date_erst']));
			}
			else
			{
				$ins->rp_date_erst = '0000-00-00 00:00:00';
			}

			if(count($post['rp_sapv_erst']) > 0)
			{
				$ins->rp_sapv_erst = implode(',', $post['rp_sapv_erst']);
			}
			else
			{
				$ins->rp_sapv_erst = '';
			}

			//folge
			if(!empty($post['rp_start_date_folge']) && date('Y', strtotime($post['rp_start_date_folge'])) != '1970')
			{
				$ins->rp_start_date_folge = date('Y-m-d H:i:s', strtotime($post['rp_start_date_folge']));
			}
			else
			{
				$ins->rp_start_date_folge = '0000-00-00 00:00:00';
			}
			
			if(!empty($post['rp_date_folge']) && date('Y', strtotime($post['rp_date_folge'])) != '1970')
			{
				$ins->rp_date_folge = date('Y-m-d H:i:s', strtotime($post['rp_date_folge']));
			}
			else
			{
				$ins->rp_date_folge = '0000-00-00 00:00:00';
			}

			if(count($post['rp_sapv_folge']) > 0)
			{
				$ins->rp_sapv_folge = implode(',', $post['rp_sapv_folge']);
			}
			else
			{
				$ins->rp_sapv_folge = '';
			}

			$ins->rp_vat_representative = $post['rp_vat_representative'];
			$ins->rp_info_dependant = $post['rp_info_dependant'];

			$ins->rp_sapv_team = $post['rp_sapv_team'];
			$ins->rp_hausarzt_details = $post['rp_hausarzt_details'];
			if(!empty($post['rp_doc_id']))
			{
				$ins->rp_doc_id = $post['rp_doc_id'];
			}
			$ins->rp_doctor_user = $post['rp_doctor_user'];
			$ins->rp_home_care = $post['rp_home_care'];
			
			if(!empty($post['rp_last_day_sapv']) && date('Y', strtotime($post['rp_last_day_sapv'])) != '1970')
			{
				$ins->rp_last_day_sapv = date('Y-m-d H:i:s', strtotime($post['rp_last_day_sapv']));
			}
			else
			{
				$ins->rp_last_day_sapv = '0000-00-00 00:00:00';
			}

			//ICD10
			$ins->rp_icd_values = $post['rp_icd_values'];

			if(count($post['rp_patient_located']) > 0)
			{
				$ins->rp_patient_located = implode(',', $post['rp_patient_located']);
			}
			else
			{
				$ins->rp_patient_located = '';
			}

			if(count($post['rp_patient_supervised']) > 0)
			{
				$ins->rp_patient_supervised = implode(',', $post['rp_patient_supervised']);
			}
			else
			{
				$ins->rp_patient_supervised = '';
			}
			
			$ins->rp_other_supervisor_more = $post['rp_other_supervisor_more'];

			if(date('Y', strtotime($post['rp_last_hosp_date'])) != '1970')
			{
				$ins->rp_last_hosp_date = date('Y-m-d H:i:s', strtotime($post['rp_last_hosp_date']));
			}
			else
			{
				$ins->rp_last_hosp_date = '0000-00-00 00:00:00';
			}

			$ins->rp_last_hosp_name = $post['rp_last_hosp_name'];
			$ins->rp_hosp_dis_report = $post['rp_hosp_dis_report'];
			
			$ins->rp_main_diagnosis = $post['rp_main_diagnosis'];
			$ins->rp_side_diagnosis = $post['rp_side_diagnosis'];

			if(count($post['rp_death_image']) > 0)
			{
				$ins->rp_death_image = implode(',', $post['rp_death_image']);
			}
			else
			{
				$ins->rp_death_image = '';
			}

			if(count($post['rp_disease_phase']) > 0)
			{
				$ins->rp_disease_phase = implode(',', $post['rp_disease_phase']);
			}
			else
			{
				$ins->rp_disease_phase = '';
			}

			$ins->rp_other_phase_more = $post['rp_other_phase_more'];

			$ins->rp_curative_treatment = $post['rp_curative_treatment'];
			$ins->rp_tumor_direct_therapy = $post['rp_tumor_direct_therapy'];

			$ins->rp_tumor_indication = $post['rp_tumor_indication'];
			$ins->rp_care_is_not_enought = $post['rp_care_is_not_enought'];

			$ins->rp_sapv_rl = $post['rp_sapv_rl'];
			if(strlen($post['rp_pronounced_pain_symptoms']))
			{
				$ins->rp_pronounced_pain_symptoms = $post['rp_pronounced_pain_symptoms'];
			}
			else
			{
				$ins->rp_pronounced_pain_symptoms = '0';
			}

			$ins->rp_pronounced_pain_symptoms_more = $post['rp_pronounced_pain_symptoms_more'];


			if(strlen($post['rp_pronounced_resp_symptoms']))
			{
				$ins->rp_pronounced_resp_symptoms = $post['rp_pronounced_resp_symptoms'];
			}
			else
			{
				$ins->rp_pronounced_resp_symptoms = '0';
			}

			$ins->rp_pronounced_resp_symptoms_more = $post['rp_pronounced_resp_symptoms_more'];

			if(strlen($post['rp_pronounced_gastro_symptoms']))
			{
				$ins->rp_pronounced_gastro_symptoms = $post['rp_pronounced_gastro_symptoms'];
			}
			else
			{
				$ins->rp_pronounced_gastro_symptoms = '0';
			}

			$ins->rp_pronounced_gastro_symptoms_more = $post['rp_pronounced_gastro_symptoms_more'];


			if(strlen($post['rp_pronounced_uro_symptoms']))
			{
				$ins->rp_pronounced_uro_symptoms = $post['rp_pronounced_uro_symptoms'];
			}
			else
			{
				$ins->rp_pronounced_uro_symptoms = '0';
			}

			$ins->rp_pronounced_uro_symptoms_more = $post['rp_pronounced_uro_symptoms_more'];

			if(strlen($post['rp_pronounced_ulcerative_symptoms']))
			{
				$ins->rp_pronounced_ulcerative_symptoms = $post['rp_pronounced_ulcerative_symptoms'];
			}
			else
			{
				$ins->rp_pronounced_ulcerative_symptoms = '0';
			}

			$ins->rp_pronounced_ulcerative_symptoms_more = $post['rp_pronounced_ulcerative_symptoms_more'];


			if(strlen($post['rp_pronounced_neuro_symptoms']))
			{
				$ins->rp_pronounced_neuro_symptoms = $post['rp_pronounced_neuro_symptoms'];
			}
			else
			{
				$ins->rp_pronounced_neuro_symptoms = '0';
			}

			$ins->rp_pronounced_neuro_symptoms_more = $post['rp_pronounced_neuro_symptoms_more'];


			if(strlen($post['rp_other_symptoms']))
			{
				$ins->rp_other_symptoms = $post['rp_other_symptoms'];
			}
			else
			{
				$ins->rp_other_symptoms = '0';
			}

			$ins->rp_other_symptoms_more = $post['rp_other_symptoms_more'];


			if(count($post['rp_symptom_factor']) > 0)
			{
				$ins->rp_symptom_factor = implode(',', $post['rp_symptom_factor']);
			}
			else
			{
				$ins->rp_symptom_factor = '';
			}

			$ins->rp_somatic_factor = $post['rp_somatic_factor'];
			$ins->rp_psychological_factor = $post['rp_psychological_factor'];
			$ins->rp_social_factor = $post['rp_social_factor'];
			$ins->rp_spiritual_factor = $post['rp_spiritual_factor'];

			$ins->rp_reqires_sapv = $post['rp_reqires_sapv'];

			if(count($post['rp_facts_advice']) > 0)
			{
				$ins->rp_facts_advice = implode(',', $post['rp_facts_advice']);
			}
			else
			{
				$ins->rp_facts_advice = '';
			}
			$ins->rp_advice_other_more = $post['rp_advice_other_more'];


			if(count($post['rp_involved_options']) > 0)
			{
				$ins->rp_involved_options = implode(',', $post['rp_involved_options']);
			}
			else
			{
				$ins->rp_involved_options = '';
			}
			$ins->rp_involved_options_more = $post['rp_involved_options_more'];


			if(count($post['rp_care_needs']) > 0)
			{
				$ins->rp_care_needs = implode(',', $post['rp_care_needs']);
			}
			else
			{
				$ins->rp_care_needs = '';
			}
			$ins->rp_need_other_more = $post['rp_need_other_more'];


			if(count($post['rp_treatment_plan_providers']) > 0)
			{
				$ins->rp_treatment_plan_providers = implode(',', $post['rp_treatment_plan_providers']);
			}
			else
			{
				$ins->rp_treatment_plan_providers = '';
			}
			$ins->rp_tp_other_more = $post['rp_tp_other_more'];

			if(count($post['rp_treatment_plan']) > 0)
			{
				$ins->rp_treatment_plan = implode(',', $post['rp_treatment_plan']);
			}
			else
			{
				$ins->rp_treatment_plan = '';
			}

			if(count($post['rp_effort_estimated']) > 0)
			{
				$ins->rp_effort_estimated = implode(',', $post['rp_effort_estimated']);
			}
			else
			{
				$ins->rp_effort_estimated = '';
			}

			if(count($post['rp_sapv_reg']) > 0)
			{
				$ins->rp_sapv_reg = implode(',', $post['rp_sapv_reg']);
			}
			else
			{
				$ins->rp_sapv_reg = '';
			}

			$ins->rp_sapv_reg_more = $post['rp_sapv_reg_more'];
			$ins->sapv_support_to = $post['sapv_support_to'];

			if(date('Y', strtotime($post['sapv_support_date'])) != '1970')
			{
				$ins->sapv_support_date = date('Y-m-d H:i:s', strtotime($post['sapv_support_date']));
			}
			else
			{
				$ins->sapv_support_date = '0000-00-00 00:00:00';
			}

			//set completed date only if is marked as iscomplete
			if($post['iscompleted'][0] == '1')
			{
				if(date('Y', strtotime($post['completed_date'][0])) != '1970')
				{
					$ins->completed_date = date('Y-m-d H:i:s', strtotime($post['completed_date'][0]));
				}

				$ins->iscompleted = $post['iscompleted'][0];
			}
			$ins->save();


			if($ins->id > 0)
			{
				if($post['iscompleted'] != '1')
				{
					$custcourse = new PatientCourse();
					$custcourse->ipid = $ipid;
					$custcourse->course_date = date("Y-m-d H:i:s", time());
					$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
					$comment = "RP-Assessment Formular wurde angelegt";
					$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
					$custcourse->user_id = $userid;
					$custcourse->tabname = Pms_CommonData::aesEncrypt('new_rp_assesment');
					$custcourse->save();
				}

				return true;
			}
			else
			{
				return false;
			}
		}

		public function update_rp_assessment($post, $ipid)
		{
			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
		
			if(!empty($post['fid']))
			{
				$ins = Doctrine::getTable('Rpassessment')->findOneByIdAndIpid($post['fid'], $ipid);
				
				$ins->rp_pat_last_name = $post['rp_pat_last_name'];
				$ins->rp_pat_first_name = $post['rp_pat_first_name'];

				if(!empty($post['rp_pat_birthd']) && date('Y', strtotime($post['rp_pat_birthd'])) != '1970')
				{
					$ins->rp_pat_birthd = date('Y-m-d H:i:s', strtotime($post['rp_pat_birthd']));
				}
				$ins->rp_pat_address = $post['rp_pat_address'];
				$ins->rp_pat_zip_city = $post['rp_pat_zip_city'];
				$ins->rp_pat_phone = $post['rp_pat_phone'];
				$ins->rp_pat_epid = $post['rp_pat_epid'];
			
				$ins->rp_health_insurance = $post['rp_health_insurance'];
				$ins->rp_insurance_number = $post['rp_insurance_number'];
				$ins->rp_sex = $post['rp_sex'];

				//erst
				if(!empty($post['rp_start_date_erst']) && date('Y', strtotime($post['rp_start_date_erst'])) != '1970')
				{
					$ins->rp_start_date_erst = date('Y-m-d H:i:s', strtotime($post['rp_start_date_erst']));
				}
				else
				{
					$ins->rp_start_date_erst = '0000-00-00 00:00:00';
				}
				
				if(!empty($post['rp_date_erst']) && date('Y', strtotime($post['rp_date_erst'])) != '1970')
				{
					$ins->rp_date_erst = date('Y-m-d H:i:s', strtotime($post['rp_date_erst']));
				}
				else
				{
					$ins->rp_date_erst = '0000-00-00 00:00:00';
				}

				if(count($post['rp_sapv_erst']) > 0)
				{
					$ins->rp_sapv_erst = implode(',', $post['rp_sapv_erst']);
				}
				else
				{
					$ins->rp_sapv_erst = '';
				}

				//folge
				if(!empty($post['rp_start_date_folge']) && date('Y', strtotime($post['rp_start_date_folge'])) != '1970')
				{
					$ins->rp_start_date_folge = date('Y-m-d H:i:s', strtotime($post['rp_start_date_folge']));
				}
				else
				{
					$ins->rp_start_date_folge = '0000-00-00 00:00:00';
				}
				
				if(!empty($post['rp_date_folge']) && date('Y', strtotime($post['rp_date_folge'])) != '1970')
				{
					$ins->rp_date_folge = date('Y-m-d H:i:s', strtotime($post['rp_date_folge']));
				}
				else
				{
					$ins->rp_date_folge = '0000-00-00 00:00:00';
				}

				if(count($post['rp_sapv_folge']) > 0)
				{
					$ins->rp_sapv_folge = implode(',', $post['rp_sapv_folge']);
				}
				else
				{
					$ins->rp_sapv_folge = '';
				}

				$ins->rp_vat_representative = $post['rp_vat_representative'];
				$ins->rp_info_dependant = $post['rp_info_dependant'];

				$ins->rp_sapv_team = $post['rp_sapv_team'];
				$ins->rp_hausarzt_details = $post['rp_hausarzt_details'];
				if(!empty($post['rp_doc_id']))
				{
					$ins->rp_doc_id = $post['rp_doc_id'];
				}
				$ins->rp_doctor_user = $post['rp_doctor_user'];
				$ins->rp_home_care = $post['rp_home_care'];
				
				if(!empty($post['rp_last_day_sapv']) && date('Y', strtotime($post['rp_last_day_sapv'])) != '1970')
				{
					$ins->rp_last_day_sapv = date('Y-m-d H:i:s', strtotime($post['rp_last_day_sapv']));
				}
				else
				{
					$ins->rp_last_day_sapv = '0000-00-00 00:00:00';
				}

				//ICD10
				$ins->rp_icd_values = $post['rp_icd_values'];

				if(count($post['rp_patient_located']) > 0)
				{
					$ins->rp_patient_located = implode(',', $post['rp_patient_located']);
				}
				else
				{
					$ins->rp_patient_located = '';
				}

				if(count($post['rp_patient_supervised']) > 0)
				{
					$ins->rp_patient_supervised = implode(',', $post['rp_patient_supervised']);
				}
				else
				{
					$ins->rp_patient_supervised = '';
				}
				
				$ins->rp_other_supervisor_more = $post['rp_other_supervisor_more'];

				if(date('Y', strtotime($post['rp_last_hosp_date'])) != '1970')
				{
					$ins->rp_last_hosp_date = date('Y-m-d H:i:s', strtotime($post['rp_last_hosp_date']));
				}
				else
				{
					$ins->rp_last_hosp_date = '0000-00-00 00:00:00';
				}

				$ins->rp_last_hosp_name = $post['rp_last_hosp_name'];
				$ins->rp_hosp_dis_report = $post['rp_hosp_dis_report'];

				$ins->rp_main_diagnosis = $post['rp_main_diagnosis'];
				$ins->rp_side_diagnosis = $post['rp_side_diagnosis'];
			
				if(count($post['rp_death_image']) > 0)
				{
					$ins->rp_death_image = implode(',', $post['rp_death_image']);
				}
				else
				{
					$ins->rp_death_image = '';
				}

				if(count($post['rp_disease_phase']) > 0)
				{
					$ins->rp_disease_phase = implode(',', $post['rp_disease_phase']);
				}
				else
				{
					$ins->rp_disease_phase = '';
				}

				$ins->rp_other_phase_more = $post['rp_other_phase_more'];

				$ins->rp_curative_treatment = $post['rp_curative_treatment'];
				$ins->rp_tumor_direct_therapy = $post['rp_tumor_direct_therapy'];

				$ins->rp_tumor_indication = $post['rp_tumor_indication'];
				$ins->rp_care_is_not_enought = $post['rp_care_is_not_enought'];

				$ins->rp_sapv_rl = $post['rp_sapv_rl'];
				if(strlen($post['rp_pronounced_pain_symptoms']))
				{
					$ins->rp_pronounced_pain_symptoms = $post['rp_pronounced_pain_symptoms'];
				}
				else
				{
					$ins->rp_pronounced_pain_symptoms = '0';
				}

				$ins->rp_pronounced_pain_symptoms_more = $post['rp_pronounced_pain_symptoms_more'];


				if(strlen($post['rp_pronounced_resp_symptoms']))
				{
					$ins->rp_pronounced_resp_symptoms = $post['rp_pronounced_resp_symptoms'];
				}
				else
				{
					$ins->rp_pronounced_resp_symptoms = '0';
				}

				$ins->rp_pronounced_resp_symptoms_more = $post['rp_pronounced_resp_symptoms_more'];

				if(strlen($post['rp_pronounced_gastro_symptoms']))
				{
					$ins->rp_pronounced_gastro_symptoms = $post['rp_pronounced_gastro_symptoms'];
				}
				else
				{
					$ins->rp_pronounced_gastro_symptoms = '0';
				}

				$ins->rp_pronounced_gastro_symptoms_more = $post['rp_pronounced_gastro_symptoms_more'];


				if(strlen($post['rp_pronounced_uro_symptoms']))
				{
					$ins->rp_pronounced_uro_symptoms = $post['rp_pronounced_uro_symptoms'];
				}
				else
				{
					$ins->rp_pronounced_uro_symptoms = '0';
				}

				$ins->rp_pronounced_uro_symptoms_more = $post['rp_pronounced_uro_symptoms_more'];

				if(strlen($post['rp_pronounced_ulcerative_symptoms']))
				{
					$ins->rp_pronounced_ulcerative_symptoms = $post['rp_pronounced_ulcerative_symptoms'];
				}
				else
				{
					$ins->rp_pronounced_ulcerative_symptoms = '0';
				}

				$ins->rp_pronounced_ulcerative_symptoms_more = $post['rp_pronounced_ulcerative_symptoms_more'];


				if(strlen($post['rp_pronounced_neuro_symptoms']))
				{
					$ins->rp_pronounced_neuro_symptoms = $post['rp_pronounced_neuro_symptoms'];
				}
				else
				{
					$ins->rp_pronounced_neuro_symptoms = '0';
				}

				$ins->rp_pronounced_neuro_symptoms_more = $post['rp_pronounced_neuro_symptoms_more'];


				if(strlen($post['rp_other_symptoms']))
				{
					$ins->rp_other_symptoms = $post['rp_other_symptoms'];
				}
				else
				{
					$ins->rp_other_symptoms = '0';
				}

				$ins->rp_other_symptoms_more = $post['rp_other_symptoms_more'];


				if(count($post['rp_symptom_factor']) > 0)
				{
					$ins->rp_symptom_factor = implode(',', $post['rp_symptom_factor']);
				}
				else
				{
					$ins->rp_symptom_factor = '';
				}

				$ins->rp_somatic_factor = $post['rp_somatic_factor'];
				$ins->rp_psychological_factor = $post['rp_psychological_factor'];
				$ins->rp_social_factor = $post['rp_social_factor'];
				$ins->rp_spiritual_factor = $post['rp_spiritual_factor'];

				$ins->rp_reqires_sapv = $post['rp_reqires_sapv'];

				if(count($post['rp_facts_advice']) > 0)
				{
					$ins->rp_facts_advice = implode(',', $post['rp_facts_advice']);
				}
				else
				{
					$ins->rp_facts_advice = '';
				}
				$ins->rp_advice_other_more = $post['rp_advice_other_more'];


				if(count($post['rp_involved_options']) > 0)
				{
					$ins->rp_involved_options = implode(',', $post['rp_involved_options']);
				}
				else
				{
					$ins->rp_involved_options = '';
				}
				$ins->rp_involved_options_more = $post['rp_involved_options_more'];


				if(count($post['rp_care_needs']) > 0)
				{
					$ins->rp_care_needs = implode(',', $post['rp_care_needs']);
				}
				else
				{
					$ins->rp_care_needs = '';
				}
				$ins->rp_need_other_more = $post['rp_need_other_more'];


				if(count($post['rp_treatment_plan_providers']) > 0)
				{
					$ins->rp_treatment_plan_providers = implode(',', $post['rp_treatment_plan_providers']);
				}
				else
				{
					$ins->rp_treatment_plan_providers = '';
				}
				$ins->rp_tp_other_more = $post['rp_tp_other_more'];

				if(count($post['rp_treatment_plan']) > 0)
				{
					$ins->rp_treatment_plan = implode(',', $post['rp_treatment_plan']);
				}
				else
				{
					$ins->rp_treatment_plan = '';
				}

				if(count($post['rp_effort_estimated']) > 0)
				{
					$ins->rp_effort_estimated = implode(',', $post['rp_effort_estimated']);
				}
				else
				{
					$ins->rp_effort_estimated = '';
				}

				if(count($post['rp_sapv_reg']) > 0)
				{
					$ins->rp_sapv_reg = implode(',', $post['rp_sapv_reg']);
				}
				else
				{
					$ins->rp_sapv_reg = '';
				}

				$ins->rp_sapv_reg_more = $post['rp_sapv_reg_more'];
				$ins->sapv_support_to = $post['sapv_support_to'];

				if(date('Y', strtotime($post['sapv_support_date'])) != '1970')
				{
					$ins->sapv_support_date = date('Y-m-d H:i:s', strtotime($post['sapv_support_date']));
				}
				else
				{
					$ins->sapv_support_date = '0000-00-00 00:00:00';
				}

				if($post['iscompleted'][$post['fid']] == '1' || date('Y', strtotime($post['completed_date'][$post['fid']])) != '1970')
				{
					if(date('Y', strtotime($post['completed_date'][$post['fid']])) != '1970')
					{
						$ins->completed_date = date('Y-m-d H:i:s', strtotime($post['completed_date'][$post['fid']]));
					}

					if($post['iscompleted'][$post['fid']] == '1')
					{
						$ins->iscompleted = $post['iscompleted'][$post['fid']];
					}
				}
				
				$ins->save();
				
				if($post['newvalue'] == '1')
				{
					$custcourse = new PatientCourse();
					$custcourse->ipid = $ipid;
					$custcourse->course_date = date("Y-m-d H:i:s", time());
					$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
					$comment = "Neues RP-Assessment wurde gestartet.";
					$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
					$custcourse->user_id = $userid;
					$custcourse->tabname = Pms_CommonData::aesEncrypt('new_rp_assesment');
					$custcourse->save();
				}
				
				if(empty($post['iscompleted']) && empty($post['newvalue']))
				{
					//formular editat -- not completed or new
					$custcourse = new PatientCourse();
					$custcourse->ipid = $ipid;
					$custcourse->course_date = date("Y-m-d H:i:s", time());
					$custcourse->course_type = Pms_CommonData::aesEncrypt("K");
					$comment = "RP-Assessment Formular wurde editiert.";
					$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
					$custcourse->user_id = $userid;
					$custcourse->save();
				}
			}
		}
		
		public function close_forms($ipid)
		{
			$upd = Doctrine_Query::create()
				->update('Rpassessment')
				->set('isclosed', '1')
				->where('ipid LIKE "'.$ipid.'" ')
				->andWhere('isclosed = "0"')
				->andWhere('iscompleted = "1"');
			$upd_res = $upd->execute();
			
		}
	}

?>