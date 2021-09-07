<?php

	abstract class BaseDiagnosisType extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('diagnosis_type');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('abbrevation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('description', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>