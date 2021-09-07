<?php
/*
 * ISPC-2792 Lore 15.01.2021
 */

Doctrine_Manager::getInstance()->bindComponent('PatientPersonalHygiene', 'IDAT');


abstract class BasePatientPersonalHygiene extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_personal_hygiene');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('maintenance_condition', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mucosal_texture', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('skin_texture', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('assessment_scale_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('assessment_scale_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pressure_ulcer_risk_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('pressure_ulcer_risk_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pressure_ulcer_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('pressure_ulcer_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('own_care_products_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('nail_care_allowed_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('basal_stimulation_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('basal_stimulation_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('habits_particularities', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mattress_opt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mattress_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tools_opt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dental_care_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('dental_care_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('braces_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('braces_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('oral_care_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('oral_care_text', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>