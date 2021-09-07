<?php

	abstract class BasePatientMedipumpsControl extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_medipumps_control');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('medipump', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('patient_medipump', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('value', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('start', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>