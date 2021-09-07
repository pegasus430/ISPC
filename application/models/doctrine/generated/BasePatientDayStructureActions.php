<?php

	abstract class BasePatientDayStructureActions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_day_structure_actions');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('form_id', 'integer', 11, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('start', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('end', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('description', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('measures', 'text', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>