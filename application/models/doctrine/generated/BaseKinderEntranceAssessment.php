<?php

	abstract class BaseKinderEntranceAssessment extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('kinder_entrance_assessment');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('completed_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('first_form', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sapv_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('living_situation', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('stage_requested', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('stage_hardship', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('custody', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('evn', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('therapy', 'text', NULL, array('type' => 'text', 'length' => NULL));
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
			$this->hasColumn('airway_obstruction', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('respiratory_other_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('respiratory_other', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('disorder', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('intracranial_pressure', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('restlessness', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('spasticity', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('cerebral_seizures', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('developmental_disorder', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('autoaggression', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('insomnia', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('depressive', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('neuroligical_other_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('neuroligical_other', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('wound_a_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wound_a', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('anorexia', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('mucositis', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('dysphagia', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('throw_up', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('hematemesis', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('icterus', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('ileus', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('ascites', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('diarrhea', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('constipation', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('gastrointestinal_other_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('gastrointestinal_other', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('urinary', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('dysuria', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('hematuria', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('voiding_dysfunction', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('urogenital_other_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('urogenital_other', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('last_hospital', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_hospital_period', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('adm_oral', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_peg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_iv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_sc', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_im', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_infusion', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_inhalation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_schmerzpumpe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_port', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('adm_broviak', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_antibiosis', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_kg', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_enteral_nutrition', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_o2_ventilation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_parental_nutrition', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_pain_therapy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_antiepileptic_therapy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_muscle_relaxants', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_free_a', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_free_a_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('required_free_b', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_free_b_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('required_free_c', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('required_free_c_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('administer_oxigen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('colostomy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('urostomy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('tracheostomy', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('warehousing', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wound_treatment', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('case_history', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('social_environment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('psychosocial_interventions', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('other_text', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			$this->hasOne('KinderEntranceAssessmentSorrowfully', array(
					'local' => 'id',
					'foreign' => 'assessment_id'));
			
			
		}

	}

?>