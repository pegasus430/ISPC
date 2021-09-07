<?php
/*
 * ISPC-2791 Lore 13.01.2021
 */

Doctrine_Manager::getInstance()->bindComponent('PatientExcretionInfo', 'IDAT');


abstract class BasePatientExcretionInfo extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_excretion_info');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('general_opt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('independence_opt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('independence_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('incontinence_opt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('incontinence_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wears_diapers_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('wears_diapers_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('toilet_training_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('toilet_training_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('toilet_chair_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('abdominal_massages_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('bowel_movement_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('consistency_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('frequency_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('stimulate_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('intestinal_tube_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('digital_clearing_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('uses_templates_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('uses_templates_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('urine_bottle_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('urine_bottle_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('urine_condom_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('urine_condom_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('catheterization_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('catheterization_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('menstruation_opt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('menstruation_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vomit_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));

		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>