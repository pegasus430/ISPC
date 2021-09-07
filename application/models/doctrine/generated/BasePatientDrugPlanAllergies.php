<?php

	abstract class BasePatientDrugPlanAllergies extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_drugplan_allergies');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('allergies_comment', 'text', NULL, array('type' => 'string', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>