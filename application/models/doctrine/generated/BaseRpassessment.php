<?php

	abstract class BaseRpassessment extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('rpassessment');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_health_insurance', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('rp_pat_last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_pat_first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_pat_birthd', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('rp_pat_address', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_pat_zip_city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_pat_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_pat_epid', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('rp_insurance_number', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_sex', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('rp_start_date_erst', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('rp_date_erst', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('rp_sapv_erst', 'string', 50, array('type' => 'string', 'length' => 50));

			$this->hasColumn('rp_start_date_folge', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('rp_date_folge', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('rp_sapv_folge', 'string', 50, array('type' => 'string', 'length' => 50));

			$this->hasColumn('rp_vat_representative', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_info_dependant', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('rp_sapv_team', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_hausarzt_details', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_doc_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('rp_doctor_user', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_home_care', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('rp_last_day_sapv', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));

			$this->hasColumn('rp_icd_values', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_patient_located', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_patient_supervised', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_other_supervisor_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_last_hosp_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('rp_last_hosp_name', 'string', 255, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_hosp_dis_report', 'integer', 1, array('type' => 'integer', 'length' => 1));

			
			
			$this->hasColumn('rp_main_diagnosis', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('rp_side_diagnosis', 'text', NULL, array('type' => 'text', 'length' => NULL));
			
			$this->hasColumn('rp_death_image', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_disease_phase', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_other_phase_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_curative_treatment', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_tumor_direct_therapy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_tumor_indication', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_care_is_not_enought', 'text', null);
			$this->hasColumn('rp_sapv_rl', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('rp_pronounced_pain_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_pronounced_pain_symptoms_more', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('rp_pronounced_resp_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_pronounced_resp_symptoms_more', 'text', NULL, array('type' => 'text', 'length' => NULL));


			$this->hasColumn('rp_pronounced_gastro_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_pronounced_gastro_symptoms_more', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('rp_pronounced_uro_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_pronounced_uro_symptoms_more', 'text', NULL, array('type' => 'text', 'length' => NULL));
			
			$this->hasColumn('rp_pronounced_ulcerative_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_pronounced_ulcerative_symptoms_more', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('rp_pronounced_neuro_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_pronounced_neuro_symptoms_more', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('rp_other_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_other_symptoms_more', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('rp_symptom_factor', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_somatic_factor', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_psychological_factor', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_social_factor', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('rp_spiritual_factor', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('rp_reqires_sapv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('rp_facts_advice', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_advice_other_more', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('rp_involved_options', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_involved_options_more', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('rp_care_needs', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_need_other_more', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('rp_treatment_plan_providers', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_tp_other_more', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('rp_treatment_plan', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_effort_estimated', 'string', 50, array('type' => 'string', 'length' => 50));

			$this->hasColumn('rp_sapv_reg', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('rp_sapv_reg_more', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('sapv_support_to', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sapv_support_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));

			$this->hasColumn('iscompleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('completed_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));

			$this->hasColumn('isclosed', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>