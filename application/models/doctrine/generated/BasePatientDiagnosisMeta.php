<?php

	abstract class BasePatientDiagnosisMeta extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_diagnosismeta');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('metaid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('diagnoid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			//$this->actAs(new Trigger());
		}

	}

?>