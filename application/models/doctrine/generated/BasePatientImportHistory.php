<?php

	abstract class BasePatientImportHistory extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_import_history');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('user', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('client', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('date', 'date', NULL, array('type' => 'integer', 'length' => NULL));
			$this->hasColumn('xml_string', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('imported_patients', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
		}

	}

?>