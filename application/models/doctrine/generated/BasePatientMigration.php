<?php
	abstract class BasePatientMigration extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_migration');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('foreign_migration', 'integer', 1, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('foreign_migration_from', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>