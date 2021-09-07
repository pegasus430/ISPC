<?php

	abstract class BaseEntranceAssessment extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('entrance_assessment');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('completed_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('first_form', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sapv_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('living_with_child_age', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('living_other', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('living_other_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('stage_requested', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('stage_hardship', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('cared_for', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('cared_for_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tumor_brain', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tumor_lung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tumor_liver', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tumor_bone', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tumor_other', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tumor_other_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('therapy_op', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('therapy_radio', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('therapy_chemo', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('therapy_other', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('therapy_other_text', 'string', 255, array('type' => 'string', 'length' => 255));

			$this->hasColumn('pain_symptom', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('pain_localisation', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('pain_symptom_2', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('pain_localisation_2', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('pain_symptom_3', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('pain_localisation_3', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('pain_symptom_4', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('pain_localisation_4', 'text', NULL, array('type' => 'text', 'length' => NULL));

			$this->hasColumn('distress', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('hemoptysis', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('nyha', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('respiratory_other_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('respiratory_other', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('disorder', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('intracranial_pressure', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('myoclonic', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('muscle_cramps', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('depression', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('psychotic_syndromes', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('neuroligical_other_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('neuroligical_other', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('wound_tumor_a_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wound_tumor_a', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('wound_tumor_b_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wound_tumor_b', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('wound_tumor_c_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wound_tumor_c', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('anorexia', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('mucositis', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('dysphagia', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('throw_up', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('hematemesis', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('icterus', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('ileus', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('ascites', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('diarrhea', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('fistulas', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('gastrointestinal_other_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('gastrointestinal_other', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('urinary', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('dysuria', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('blasentenesmen', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('hematuria', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('vaginal_bleeding', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('urogenital_other_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('urogenital_other', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('last_hospital', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_hospital_period', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('adm_oral', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_iv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_sc', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_im', 'integer', 1, array('type' => 'integer', 'length' => 1));
			// ISPC-2397
			$this->hasColumn('adm_transdermal', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_infusion', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_inhalation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_schmerzpumpe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_port', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_nasogastric_tube', 'integer', 1, array('type' => 'integer', 'length' => 1)); //ISPC - 2289
			$this->hasColumn('required_chemo', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_antibiosis', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_kg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_enteral_nutrition', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_radiatio', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_lymphatic', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_o2_ventilation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_parental_nutrition', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('administer_oxigen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('colostomy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('urostomy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tracheostomy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('warehousing', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wound_treatment', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('other_text', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('psychosocial_care', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('social_environment', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('psychosocial_interventions', 'text', NULL, array('type' => 'string', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>