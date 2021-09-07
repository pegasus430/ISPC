<?php

	abstract class BasePatientBarthelValues extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_barthel_values');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('form', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('section', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('value', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>