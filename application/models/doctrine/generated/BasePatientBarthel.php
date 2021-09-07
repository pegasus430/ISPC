<?php

	abstract class BasePatientBarthel extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_barthel');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('total_score', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>