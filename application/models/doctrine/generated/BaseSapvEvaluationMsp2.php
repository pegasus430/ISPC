<?php


	abstract class BaseSapvEvaluationMsp2 extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sapv_evaluation_msp2');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('form_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
			$this->hasColumn('health_insurance_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('health_insurance_kassen_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client_bsnr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client_iknumber', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('sapv_completion', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pain_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('gastrointestinal_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('neurol_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('urogen_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wund_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('cardiac_symptoms', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ethical_conflicts', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('existential_crisis', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('palliative_care', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('reference_system', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('security_problems', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('curation', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('life_shortening', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('retrospektiv_other', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_discharge', 'datetime', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('sapv_termination', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('case_typing', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sapv_integration', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('latest_sapv', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('additional_information', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('additional_information_txt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('request_death', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('more_information_24', 'integer', 4, array('type' => 'integer', 'length' => 4));
			$this->hasColumn('intermittent_number', 'integer', 4, array('type' => 'integer', 'length' => 4));
			$this->hasColumn('visits_count', 'integer', 4, array('type' => 'integer', 'length' => 4));
			$this->hasColumn('emergency_intervention', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hospitalizations', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('first_kh', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('second_kh', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('third_kh', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			//ISPC-2655 Ancuta 21.09.2020 //TODO-3428
			$this->hasOne('SapvEvaluation', array(
			    'local' => 'form_id',
			    'foreign' => 'id'
			));
			//--
			
		}

	}

?>