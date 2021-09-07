<?php

	abstract class BaseDiagnosisIcd extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('diagnosis_icd');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('description', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('icd_primary', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>