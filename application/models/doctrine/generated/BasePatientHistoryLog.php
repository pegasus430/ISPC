<?php

	abstract class BasePatientHistoryLog extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_history_log');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('adm', 'dis','fall')));
			$this->hasColumn('details', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>