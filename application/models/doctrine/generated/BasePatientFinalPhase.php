<?php
/*
 * ISPC-2790 Lore 12.01.2021
 */

Doctrine_Manager::getInstance()->bindComponent('PatientFinalPhase', 'IDAT');


abstract class BasePatientFinalPhase extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_final_phase');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));			
			$this->hasColumn('death_discussed_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('death_discussed_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('undertaker_informed_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('undertaker_informed_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('coffin_chosen_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('coffin_chosen_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('how_was_informed_opt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('how_was_informed_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('care_child_opt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('care_child_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('laying_out_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('laying_out_text', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('memento_desired_opt', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('memento_desired_text', 'string', 255, array('type' => 'string', 'length' => 255));

		}

		function setUp()
		{
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());

			

		}

	}

?>