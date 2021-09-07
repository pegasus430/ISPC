<?php


	abstract class BaseSapvEvaluationMsp1 extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sapv_evaluation_msp1');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('form_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('health_insurance_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('health_insurance_kassen_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client_bsnr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('client_iknumber', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patient_epid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patient_gender', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('patient_age', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patient_zip', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('first_admission', 'datetime', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('main_diagnosis', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('has_side_diagno1', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('side_diagno1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('has_side_diagno2', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('side_diagno2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('has_side_diagno3', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('side_diagno3', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('first_sapv_types', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('first_contact', 'integer', 2, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('living_conditions', 'integer', 1, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('living_conditions_other', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('extra_nursing_care', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('estimaded_prognosis', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('akps', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('akps_old', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('assessment_disease', 'integer', 1, array('type' => 'integer', 'length' => 1));
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