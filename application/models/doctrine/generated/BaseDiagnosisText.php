<?php

	abstract class BaseDiagnosisText extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('diagnosis_freetext');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('sys_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('icd_primary', 'string', 255, array('type' => 'integer', 'length' => 255));
			$this->hasColumn('free_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('free_desc', 'text', NULL, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>