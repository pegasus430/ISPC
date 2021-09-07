<?php

	abstract class BasePatientDrugPlanShare extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_drugplan_share');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('drugplan_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		}

	}

?>